from fastapi import FastAPI
from app.data.db import engine, Base 
from app.data import database 
from fastapi import FastAPI
from app.data.db import engine, Base
from app.router import auto, users

Base.metadata.create_all(bind=engine)

#Instancia del servidor
app = FastAPI(
    title = "OkoVision API",
    description = "OKO VISION - CONTROL DE ACCESOS",
    version = "1.0.0"
)

app.include_router(users.router)
app.include_router(auto.car)