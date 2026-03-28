from fastapi import FastAPI
from app.data.db import engine, Base 
from app.data import database 
from app.router import auto, users, access

Base.metadata.create_all(bind=engine)

#Instancia del servidor
app = FastAPI(
    title = "OkoVision API",
    description = "OKO VISION - CONTROL DE ACCESOS",
    version = "1.0.0"
)

@app.get("/")
def root():
    return {"message": "OKO VISION API is running", "status": "active"}

app.include_router(users.router)
app.include_router(users.router, prefix="/users", include_in_schema=False)
app.include_router(auto.car)
app.include_router(auto.car, prefix="/vehicles", include_in_schema=False)
app.include_router(access.router)
app.include_router(access.router, prefix="/access-logs", include_in_schema=False)