from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.data.db import engine, Base 
from app.data import database 
from app.router import auto, users, access, personas, auth, ai

Base.metadata.create_all(bind=engine)

#Instancia del servidor
app = FastAPI(
    title = "OkoVision API",
    description = "OKO VISION - CONTROL DE ACCESOS",
    version = "1.0.0"
)

# Configurar CORS para permitir solicitudes desde cualquier origen
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.get("/")
def root():
    return {"message": "OKO VISION API is running", "status": "active"}

app.include_router(auth.router)
app.include_router(users.router)
app.include_router(users.router, prefix="/users", include_in_schema=False)
app.include_router(auto.vehiculos)
app.include_router(auto.vehiculos, prefix="/vehicles", include_in_schema=False)
app.include_router(access.router)
app.include_router(access.router, prefix="/access-logs", include_in_schema=False)
app.include_router(personas.router)
app.include_router(personas.router, prefix="/people", include_in_schema=False)
app.include_router(ai.router)