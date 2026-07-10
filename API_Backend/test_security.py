import requests
import json

# URL base de la API (en Docker expone el puerto 8002)
BASE_URL = "http://localhost:8002"

def test_public_endpoint():
    """Prueba que el endpoint raíz es público"""
    print("\n=== Prueba de endpoint público (/) ===")
    response = requests.get(f"{BASE_URL}/")
    print(f"Status Code: {response.status_code}")
    print(f"Response: {response.json()}")
    assert response.status_code == 200, "El endpoint raíz debería ser público"

def test_protected_endpoint_without_token():
    """Prueba que un endpoint protegido devuelve 401 sin token"""
    print("\n=== Prueba de endpoint protegido sin token ===")
    endpoints = ["/usuarios/", "/vehiculos/", "/accesos/", "/personas/"]
    for endpoint in endpoints:
        response = requests.get(f"{BASE_URL}{endpoint}")
        print(f"Endpoint {endpoint}: Status Code {response.status_code}")
        assert response.status_code == 401, f"El endpoint {endpoint} debería requerir autenticación"

def test_login_and_access():
    """Prueba el flujo completo de login y acceso a endpoints protegidos"""
    print("\n=== Prueba de login y acceso con token ===")
    
    # Datos de prueba válidos (configurados en la BD)
    test_email = "ahortamtz@gmail.com"
    test_password = "123456"
    
    # Intento de login
    login_data = {"email": test_email, "password": test_password}
    print(f"Intentando login con: {test_email}")
    login_response = requests.post(f"{BASE_URL}/auth/login", json=login_data)
    print(f"Login Status Code: {login_response.status_code}")
    
    if login_response.status_code == 200:
        login_result = login_response.json()
        print(f"Login Response: {json.dumps(login_result, indent=2)}")
        token = login_result.get("access_token")
        
        if token:
            headers = {"Authorization": f"Bearer {token}"}
            
            # Prueba de acceso a endpoints protegidos
            endpoints = ["/usuarios/", "/vehiculos/", "/accesos/", "/personas/"]
            for endpoint in endpoints:
                response = requests.get(f"{BASE_URL}{endpoint}", headers=headers)
                print(f"Endpoint {endpoint}: Status Code {response.status_code}")
                assert response.status_code == 200, f"Debería poder acceder a {endpoint} con token válido"
        else:
            print("⚠️  No se recibió token en la respuesta de login")
    else:
        print(f"⚠️  Login falló. Asegúrate de tener un usuario con email {test_email} y contraseña {test_password} en tu BD.")
        print(f"Response: {login_response.text}")

if __name__ == "__main__":
    print("=== Iniciando pruebas de seguridad de la API ===")
    
    try:
        test_public_endpoint()
        test_protected_endpoint_without_token()
        test_login_and_access()
        print("\n=== Todas las pruebas completadas ===")
    except requests.exceptions.ConnectionError:
        print("\n⚠️  Error: No se pudo conectar a la API. Asegúrate de que el servidor esté corriendo en http://localhost:8002")
    except AssertionError as e:
        print(f"\n❌ Prueba fallida: {e}")
    except Exception as e:
        print(f"\n❌ Error inesperado: {e}")
