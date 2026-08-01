import time
from fastapi import FastAPI, Request, Response, status, Depends
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import PlainTextResponse
from fastapi.security import HTTPBearer
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.middleware.trustedhost import TrustedHostMiddleware
from sqlalchemy import text
from sqlalchemy.orm import Session

from app.data.db import engine, Base, get_db
from app.data import database
from app.router import auto, users, access, personas, auth, ai, alerts

# Métricas Prometheus
from prometheus_client import (
    generate_latest, CONTENT_TYPE_LATEST, REGISTRY,
    Counter, Histogram, Gauge
)

# Rate limiting (firewall a nivel API)
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded

Base.metadata.create_all(bind=engine)

# =========================================
# Métricas Prometheus
# =========================================
REQUEST_COUNT = Counter(
    "fastapi_http_requests_total",
    "Total de requests HTTP procesados",
    ["method", "endpoint", "status"]
)
REQUEST_LATENCY = Histogram(
    "fastapi_http_request_duration_seconds",
    "Duración de requests HTTP en segundos",
    ["method", "endpoint"]
)
ACTIVE_REQUESTS = Gauge(
    "fastapi_http_active_requests",
    "Requests activos en este momento"
)
AUTH_ATTEMPTS = Counter(
    "fastapi_auth_attempts_total",
    "Intentos de autenticación",
    ["status"]
)
DB_QUERIES = Counter(
    "fastapi_db_queries_total",
    "Consultas a base de datos"
)

# =========================================
# Rate Limiter (Firewall)
# =========================================
limiter = Limiter(key_func=get_remote_address, storage_uri="memory://")

# Instancia del servidor
app = FastAPI(
    title="OkoVision API",
    description="OKO VISION - CONTROL DE ACCESOS",
    version="2.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)
app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

# =========================================
# Seguridad: Trusted Hosts (anti host header attack)
# =========================================
app.add_middleware(TrustedHostMiddleware, allowed_hosts=["*"])

# =========================================
# CORS seguro
# =========================================
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
    allow_headers=["Authorization", "Content-Type", "Accept", "X-Requested-With"],
    expose_headers=["Content-Disposition"],
    max_age=3600,
)

# =========================================
# Middleware: Métricas + Seguridad Headers + Time
# =========================================
@app.middleware("http")
async def add_prometheus_metrics(request: Request, call_next):
    ACTIVE_REQUESTS.inc()
    start_time = time.time()
    method = request.method
    endpoint = request.scope.get("path", "unknown")

    try:
        response: Response = await call_next(request)
    except Exception as e:
        REQUEST_COUNT.labels(method=method, endpoint=endpoint, status="500").inc()
        raise
    finally:
        duration = time.time() - start_time
        ACTIVE_REQUESTS.dec()
        REQUEST_LATENCY.labels(method=method, endpoint=endpoint).observe(duration)
        # status code
        try:
            st = str(response.status_code)
        except Exception:
            st = "0"
        REQUEST_COUNT.labels(method=method, endpoint=endpoint, status=st).inc()

    # Headers de seguridad
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["X-XSS-Protection"] = "1; mode=block"
    response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    response.headers["Content-Security-Policy"] = "default-src 'self'"
    response.headers["Server"] = "OKO-VISION-GATEWAY"

    return response


# =========================================
# Endpoint de Métricas (Prometheus)
# =========================================
@app.get("/metrics", include_in_schema=False)
async def metrics_endpoint():
    return PlainTextResponse(
        content=generate_latest(REGISTRY).decode("utf-8"),
        media_type=CONTENT_TYPE_LATEST,
    )

# =========================================
# Health check público
# =========================================
@app.get("/")
@limiter.limit("10/second")
def root(request: Request):
    return {
        "message": "OKO VISION API is running",
        "status": "active",
        "version": "2.0.0",
        "security": {
            "jwt": True,
            "rate_limit": True,
            "cors": "restricted",
            "ssl_terminated_at_gateway": True,
        }
    }

@app.get("/health", tags=["Sistema"])
@limiter.limit("30/second")
def health_check(request: Request):
    return {
        "status": "healthy",
        "service": "OKO VISION API",
        "timestamp": time.time(),
        "database": "postgresql",
    }


# =========================================
# Incluir routers
# =========================================
app.include_router(auth.router)
app.include_router(users.router)
app.include_router(users.router, prefix="/users", include_in_schema=False)
app.include_router(auto.vehiculos)
app.include_router(auto.vehiculos, prefix="/vehicles", include_in_schema=False)
app.include_router(access.router)
app.include_router(access.router, prefix="/access-logs", include_in_schema=False)
app.include_router(alerts.router)
app.include_router(personas.router)
app.include_router(personas.router, prefix="/people", include_in_schema=False)
app.include_router(ai.router)

# =========================================
# Duplicado de routers con prefijo /api para compatibilidad Nginx
# =========================================
_prefix = "/api"
app.include_router(auth.router, prefix=_prefix, include_in_schema=False)
app.include_router(users.router, prefix=_prefix, include_in_schema=False)
app.include_router(users.router, prefix=f"{_prefix}/users", include_in_schema=False)
app.include_router(auto.vehiculos, prefix=_prefix, include_in_schema=False)
app.include_router(auto.vehiculos, prefix=f"{_prefix}/vehicles", include_in_schema=False)
app.include_router(access.router, prefix=_prefix, include_in_schema=False)
app.include_router(access.router, prefix=f"{_prefix}/access-logs", include_in_schema=False)
app.include_router(alerts.router, prefix=_prefix, include_in_schema=False)
app.include_router(personas.router, prefix=_prefix, include_in_schema=False)
app.include_router(personas.router, prefix=f"{_prefix}/people", include_in_schema=False)
app.include_router(ai.router, prefix=_prefix, include_in_schema=False)

@app.get(f"{_prefix}/health", tags=["Salud"], include_in_schema=False)
def api_health(db: Session = Depends(get_db)):
    try:
        db.execute(text("SELECT 1"))
        db_ok = True
    except Exception:
        db_ok = False
    return {
        "status": "healthy",
        "service": "OKO VISION API",
        "database": "postgresql" if db_ok else "unreachable",
    }
