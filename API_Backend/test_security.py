import json
import os
from typing import Optional

import requests
from requests import Response
from requests.packages.urllib3.exceptions import InsecureRequestWarning


requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

BASE_URL = os.getenv("OKO_BASE_URL", "https://localhost")
API_PREFIX = f"{BASE_URL}/api"
MOBILE_PREFIX = f"{BASE_URL}/mobile-api"
TEST_EMAIL = os.getenv("OKO_TEST_EMAIL", "admin@okovision.com")
TEST_PASSWORD = os.getenv("OKO_TEST_PASSWORD", "12345678")


def _get(url: str, token: Optional[str] = None) -> Response:
    headers = {"Authorization": f"Bearer {token}"} if token else None
    return requests.get(url, headers=headers, timeout=15, verify=False)


def _post(url: str, payload: dict, token: Optional[str] = None) -> Response:
    headers = {"Authorization": f"Bearer {token}"} if token else {}
    headers["Content-Type"] = "application/json"
    return requests.post(url, headers=headers, json=payload, timeout=15, verify=False)


def test_public_endpoints():
    print("\n=== Prueba de endpoints públicos del gateway ===")
    endpoints = [
        ("Gateway /health", f"{BASE_URL}/health"),
        ("FastAPI /api/health", f"{API_PREFIX}/health"),
    ]
    for label, url in endpoints:
        response = _get(url)
        print(f"{label}: {response.status_code} -> {response.text[:160]}")
        assert response.status_code == 200, f"{label} debería responder 200"


def test_protected_endpoints_without_token():
    print("\n=== Prueba de endpoints protegidos sin token ===")
    endpoints = [
        "/usuarios/",
        "/vehiculos/",
        "/accesos/",
        "/personas/",
        "/alerts/",
    ]
    for endpoint in endpoints:
        response = _get(f"{API_PREFIX}{endpoint}")
        print(f"{endpoint}: {response.status_code}")
        assert response.status_code in {401, 403}, f"{endpoint} debería requerir autenticación"


def test_login_and_access():
    print("\n=== Prueba de login y acceso con token ===")
    login_payload = {"email": TEST_EMAIL, "password": TEST_PASSWORD}

    login_response = _post(f"{API_PREFIX}/auth/login", login_payload)
    print(f"FastAPI login: {login_response.status_code}")
    assert login_response.status_code == 200, (
        f"No fue posible iniciar sesión en FastAPI con {TEST_EMAIL}: {login_response.text[:220]}"
    )

    login_data = login_response.json()
    token = login_data.get("access_token")
    assert token, "La respuesta de login no incluyó access_token"

    protected_endpoints = [
        "/usuarios/",
        "/vehiculos/",
        "/accesos/",
        "/personas/",
        "/alerts/",
    ]
    for endpoint in protected_endpoints:
        response = _get(f"{API_PREFIX}{endpoint}", token=token)
        print(f"{endpoint}: {response.status_code}")
        assert response.status_code == 200, f"Debería poder acceder a {endpoint} con token válido"

    mobile_login = _post(f"{MOBILE_PREFIX}/auth/login", login_payload)
    print(f"Mobile login: {mobile_login.status_code}")
    assert mobile_login.status_code == 200, "El login móvil debería responder 200"
    print(json.dumps(mobile_login.json(), indent=2)[:500])


if __name__ == "__main__":
    print("=== Iniciando pruebas de seguridad de OKO VISION ===")
    print(f"BASE_URL={BASE_URL}")

    try:
        test_public_endpoints()
        test_protected_endpoints_without_token()
        test_login_and_access()
        print("\n=== Todas las pruebas completadas correctamente ===")
    except requests.exceptions.ConnectionError:
        print(
            "\n⚠️  Error: No se pudo conectar al gateway. "
            "Asegúrate de tener Docker levantado y el stack disponible en https://localhost"
        )
    except AssertionError as exc:
        print(f"\n❌ Prueba fallida: {exc}")
    except Exception as exc:
        print(f"\n❌ Error inesperado: {exc}")
