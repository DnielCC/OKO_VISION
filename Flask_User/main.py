from flask import Flask, jsonify, request, g
from flask_cors import CORS
from routes import user_bp
import os
import time
import functools
from prometheus_client import (
    generate_latest, Counter, Histogram, Gauge, CONTENT_TYPE_LATEST, REGISTRY
)

# =============================
# Métricas Prometheus
# =============================
REQUEST_COUNT = Counter(
    'flask_http_requests_total',
    'Total de requests HTTP procesados',
    ['method', 'endpoint', 'status']
)
REQUEST_LATENCY = Histogram(
    'flask_http_request_duration_seconds',
    'Duración de requests HTTP en segundos',
    ['method', 'endpoint']
)
ACTIVE_REQUESTS = Gauge(
    'flask_http_active_requests',
    'Requests activos en este momento'
)
LOGIN_ATTEMPTS = Counter(
    'flask_login_attempts_total',
    'Intentos de login (exitosos/fallidos)',
    ['status']
)
API_AUTH_FAILURES = Counter(
    'flask_api_auth_failures_total',
    'Fallos de autenticación en API'
)


def create_app():
    app = Flask(__name__)

    # Configuración de seguridad
    app.config['SECRET_KEY'] = os.getenv(
        'SECRET_KEY',
        'oko_vision_flask_secret_key_2026_change_me_please'
    )
    app.config['JWT_SECRET_KEY'] = os.getenv(
        'JWT_SECRET_KEY',
        'oko-production-secret-key-2026-change-me-please'
    )
    app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024
    app.config['SESSION_COOKIE_SECURE'] = True
    app.config['SESSION_COOKIE_HTTPONLY'] = True
    app.config['SESSION_COOKIE_SAMESITE'] = 'Lax'

    # CORS seguro
    CORS(
        app,
        resources={r"/api/*": {"origins": "*"}},
        supports_credentials=True,
        expose_headers=["Authorization"]
    )

    # =============================
    # Middleware: Seguridad Headers + Métricas
    # =============================
    @app.before_request
    def _before_request():
        g.start_time = time.time()
        ACTIVE_REQUESTS.inc()

        # Headers de seguridad en cada response se configura via after_request

    @app.after_request
    def _after_request(response):
        try:
            request_latency = time.time() - getattr(g, 'start_time', time.time())
            endpoint = request.endpoint or 'unknown'
            status = str(response.status_code)

            REQUEST_COUNT.labels(
                method=request.method,
                endpoint=endpoint,
                status=status
            ).inc()
            REQUEST_LATENCY.labels(
                method=request.method,
                endpoint=endpoint
            ).observe(request_latency)
            ACTIVE_REQUESTS.dec()
        except Exception:
            pass

        # Headers de seguridad
        response.headers['X-Content-Type-Options'] = 'nosniff'
        response.headers['X-Frame-Options'] = 'SAMEORIGIN'
        response.headers['X-XSS-Protection'] = '1; mode=block'
        response.headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains'
        response.headers['Referrer-Policy'] = 'strict-origin-when-cross-origin'

        return response

    # Registrar blueprint publicado bajo /flask para convivir con Laravel en el gateway.
    app.register_blueprint(user_bp, url_prefix='/flask')

    # =============================
    # Endpoints de Salud
    # =============================
    @app.route('/api/health', methods=['GET'])
    def api_health():
        return jsonify({
            "status": "ok",
            "service": "OKO VISION User API (Flask)",
            "version": "2.0.0",
            "security": {
                "jwt_enabled": True,
                "rate_limited": True,
                "ssl_required": True
            },
            "endpoints": [
                "/api/auth/login",
                "/api/vehiculos/",
                "/api/accesos/",
                "/api/alerts/",
                "/api/usuarios/<id>",
            ]
        }), 200

    @app.route('/health', methods=['GET'])
    def root_health():
        return jsonify({
            "status": "ok",
            "service": "Flask User Portal",
            "version": "2.0.0"
        }), 200

    # =============================
    # Endpoint de Métricas Prometheus
    # =============================
    @app.route('/metrics', methods=['GET'])
    def metrics():
        from flask import Response
        return Response(
            generate_latest(REGISTRY),
            mimetype=CONTENT_TYPE_LATEST
        )

    # =============================
    # Manejo de Errores
    # =============================
    @app.errorhandler(404)
    def not_found(e):
        if request.path.startswith('/api/'):
            return jsonify({"detail": "Endpoint no encontrado"}), 404
        return jsonify({"error": "Not Found"}), 404

    @app.errorhandler(401)
    def unauthorized(e):
        return jsonify({"detail": "No autorizado"}), 401

    @app.errorhandler(403)
    def forbidden(e):
        return jsonify({"detail": "Prohibido"}), 403

    @app.errorhandler(500)
    def server_error(e):
        app.logger.exception("Error interno")
        return jsonify({"detail": f"Error interno"}), 500

    return app


if __name__ == '__main__':
    app = create_app()
    app.run(host='0.0.0.0', port=5000, debug=False)
