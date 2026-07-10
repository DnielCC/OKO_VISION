
import sys
import os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from fastapi.testclient import TestClient
from app.main import app
from jose import jwt, JWTError
from app.security.auth import SECRET_KEY, ALGORITHM

client = TestClient(app)

def test_login_flow():
    print("=== Paso 1: Iniciar sesión ===")
    login_data = {
        "email": "ahortamtz@gmail.com",
        "password": "123456"
    }
    login_response = client.post("/auth/login", json=login_data)
    
    print(f"Status code de login: {login_response.status_code}")
    print(f"Respuesta de login: {login_response.json()}")
    
    if login_response.status_code != 200:
        print("Login fallido!")
        return
    
    token = login_response.json()["access_token"]
    print(f"\nToken obtenido: {token}")
    
    print("\n=== Paso 2: Decodificar token ===")
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        print("Payload del token:", payload)
    except JWTError as e:
        print(f"Error al decodificar: {e}")
        return
    
    print("\n=== Paso 3: Acceder a ruta protegida ===")
    headers = {"Authorization": f"Bearer {token}"}
    response = client.get("/usuarios/", headers=headers)
    
    print(f"Status code: {response.status_code}")
    print(f"Respuesta: {response.json()}")

if __name__ == "__main__":
    test_login_flow()
