from flask import Blueprint, render_template, redirect, url_for, session, flash, request, jsonify, g, current_app
from functools import wraps
import requests
import os
import re
import secrets
import datetime
import json as _json
import jwt as pyjwt
import bcrypt

# Importar contadores de métricas (si existen)
try:
    from main import LOGIN_ATTEMPTS, API_AUTH_FAILURES
except Exception:
    LOGIN_ATTEMPTS = None
    API_AUTH_FAILURES = None


# Blueprint llamado 'user_bp'
user_bp = Blueprint('user', __name__, template_folder='templates')

# Configuración de la API
API_URL = os.getenv("API_URL", "http://api_backend:8000")

# ============================================================
# HERRAMIENTAS: Hashing + JWT
# ============================================================

def _hash_password(password: str) -> str:
    """Hash bcrypt de una contraseña (Nivel: Fuerte)"""
    pwd_bytes = password.encode("utf-8")[:72]
    salt = bcrypt.gensalt(rounds=12)
    return bcrypt.hashpw(pwd_bytes, salt).decode("utf-8")


def _check_password(password: str, hashed: str) -> bool:
    """Verifica una contraseña contra un hash bcrypt"""
    if not password or not hashed:
        return False
    try:
        return bcrypt.checkpw(password.encode("utf-8")[:72], hashed.encode("utf-8"))
    except (ValueError, TypeError):
        return False


def _create_jwt_token(user_data: dict, expires_min: int = 120) -> str:
    """Crea un JWT firmado para el usuario"""
    payload = user_data.copy()
    payload["sub"] = str(user_data.get("id", 0))
    payload["iat"] = datetime.datetime.utcnow()
    payload["exp"] = datetime.datetime.utcnow() + datetime.timedelta(minutes=expires_min)
    payload["jti"] = secrets.token_urlsafe(16)
    secret = current_app.config.get("JWT_SECRET_KEY", "oko-production-secret-key-2026-change-me-please")
    return pyjwt.encode(payload, secret, algorithm="HS256")


def _decode_jwt_token(token: str) -> dict:
    """Valida y decodifica un JWT"""
    if not token:
        return {}
    # Quitar "Bearer " si viene
    if token.lower().startswith("bearer "):
        token = token.split(None, 1)[1].strip()
    secret = current_app.config.get("JWT_SECRET_KEY", "oko-production-secret-key-2026-change-me-please")
    try:
        return pyjwt.decode(token, secret, algorithms=["HS256"])
    except (pyjwt.ExpiredSignatureError, pyjwt.InvalidTokenError):
        return {}


# ============================================================
# VALIDACIÓN FUERTE DE CONTRASEÑA + FORMULARIOS
# ============================================================
COMUNES = {
    "12345678","password","password123","qwerty","abc123",
    "11111111","123456789","1234567890","contraseña","contrasena",
    "admin123","letmein","welcome","monkey","dragon",
}

def validate_password(pwd: str):
    """Validador fuerte de contraseña. Devuelve (ok, message)"""
    if not pwd or not isinstance(pwd, str):
        return False, "Contraseña es obligatoria"
    if len(pwd) < 8:
        return False, "La contraseña debe tener al menos 8 caracteres"
    if len(pwd) > 64:
        return False, "La contraseña no debe exceder 64 caracteres"
    if re.search(r"\s", pwd):
        return False, "La contraseña no debe contener espacios"
    if not re.search(r"[A-Za-z]", pwd):
        return False, "Debe incluir al menos una letra"
    if not re.search(r"\d", pwd):
        return False, "Debe incluir al menos un número"
    if pwd.lower() in COMUNES:
        return False, "Contraseña demasiado común"
    return True, "OK"


def validate_email(email: str) -> bool:
    if not email or not isinstance(email, str):
        return False
    return bool(re.match(r"^[^\s@]+@[^\s@]+\.[^\s@]+$", email.strip()))


def validate_plate(plate: str) -> bool:
    if not plate:
        return False
    return bool(re.match(r"^[A-Za-z0-9\-]{3,15}$", plate.strip()))


# ============================================================
# HERRAMIENTAS HTTP
# ============================================================
def get_api_data(endpoint, token=None):
    try:
        headers = {}
        if token:
            headers["Authorization"] = f"Bearer {token}"
        response = requests.get(f"{API_URL}{endpoint}", timeout=7, headers=headers)
        if response.status_code == 200:
            data = response.json()
            if isinstance(data, list):
                return data
            if isinstance(data, dict) and "data" in data and isinstance(data["data"], list):
                return data["data"]
            if isinstance(data, dict):
                return [data]
        print(f"API returned status {response.status_code} for {endpoint}")
    except requests.exceptions.Timeout:
        print(f"Timeout connecting to API at {API_URL}")
    except Exception as e:
        print(f"Error connecting to API: {e}")
    return []


def _session_api_token() -> str | None:
    token = session.get("api_token")
    return token if isinstance(token, str) and token.strip() else None


def _session_auth_headers() -> dict:
    token = _session_api_token()
    return {"Authorization": f"Bearer {token}"} if token else {}


def _request_auth_headers() -> dict:
    auth = (request.headers.get("Authorization") or "").strip()
    return {"Authorization": auth} if auth else {}


def _request_bearer_token() -> str | None:
    auth = (request.headers.get("Authorization") or "").strip()
    if auth.lower().startswith("bearer "):
        token = auth.split(None, 1)[1].strip()
        return token or None
    return None


# ============================================================
# DECORADORES: login_required (WEB) y jwt_required (API)
# ============================================================
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not all(k in session for k in ("user_id", "user_username", "user_email")):
            return redirect(url_for('user.login'))
        return f(*args, **kwargs)
    return decorated_function


def jwt_required(f):
    """Valida JWT en Authorization header para endpoints /api/*"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        auth = request.headers.get("Authorization", "")
        decoded = _decode_jwt_token(auth)
        if not decoded or "sub" not in decoded:
            if API_AUTH_FAILURES is not None:
                API_AUTH_FAILURES.inc()
            return jsonify({"detail": "Token inválido o expirado"}), 401
        g.jwt_user = decoded
        return f(*args, **kwargs)
    return decorated_function


# ============================================================
# RESPUESTAS JSON NORMALIZADAS
# ============================================================
def _to_json(data, default=None):
    try:
        j = data.json()
    except Exception:
        return default if default is not None else []
    if isinstance(j, list):
        return j
    if isinstance(j, dict):
        if "data" in j and isinstance(j["data"], list):
            return j["data"]
        return j
    return default if default is not None else []


def _json_response(data, status=200, headers=None):
    resp = jsonify(data)
    resp.status_code = status
    resp.headers["Cache-Control"] = "no-store"
    resp.headers["Pragma"] = "no-cache"
    if headers:
        for k, v in headers.items():
            resp.headers[k] = v
    return resp


# ============================================================
# RUTAS WEB (SESSION)
# ============================================================
@user_bp.route('/')
def index():
    if 'user_id' in session:
        return redirect(url_for('user.dashboard'))
    return redirect(url_for('user.login'))


@user_bp.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email = (request.form.get('username') or '').strip()
        password = request.form.get('password') or ''

        if not validate_email(email):
            flash('Correo electrónico inválido', 'error')
            return render_template('login.html')
        if not password:
            flash('Contraseña es obligatoria', 'error')
            return render_template('login.html')

        try:
            response = requests.post(f"{API_URL}/auth/login", json={
                "email": email,
                "password": password
            }, timeout=7)

            if response.status_code == 200:
                if LOGIN_ATTEMPTS is not None:
                    LOGIN_ATTEMPTS.labels(status="success").inc()
                user = response.json()
                session['api_token'] = user.get('access_token')
                session['user_id'] = user['id']
                session['user_username'] = user.get('username', '')
                session['user_email'] = user.get('email', email)
                session['user_nombre'] = user.get('nombre', '')
                session['user_apellidos'] = user.get('apellidos', '')
                session.permanent = True
                flash(f"¡Bienvenido {user.get('username', email)}!", 'success')
                return redirect(url_for('user.dashboard'))
            elif response.status_code == 401:
                if LOGIN_ATTEMPTS is not None:
                    LOGIN_ATTEMPTS.labels(status="failed").inc()
                flash('Correo o contraseña incorrectos', 'error')
            else:
                if LOGIN_ATTEMPTS is not None:
                    LOGIN_ATTEMPTS.labels(status="error").inc()
                flash('Error inesperado de la API', 'error')
        except requests.exceptions.Timeout:
            flash('Error de conexión con el servidor.', 'error')
        except Exception as e:
            flash(f'Error: {str(e)}', 'error')

    return render_template('login.html')


@user_bp.route('/logout')
def logout():
    session.clear()
    flash('Has cerrado sesión correctamente', 'info')
    return redirect(url_for('user.login'))


@user_bp.route('/dashboard')
@login_required
def dashboard():
    user_id = session['user_id']
    token = _session_api_token()
    all_vehicles = get_api_data("/vehiculos/", token)
    user_vehicles = [v for v in all_vehicles if isinstance(v, dict) and v.get('owner_id') == user_id]
    all_access = get_api_data("/accesos/", token)
    user_access = [a for a in all_access if isinstance(a, dict) and a.get('user_id') == user_id][:5]
    user_info = {
        'nombre': (session.get('user_nombre', '') + ' ' + session.get('user_apellidos', '')).strip() or session.get('user_username', ''),
        'matricula': session.get('user_username', ''),
        'username': session.get('user_username', ''),
        'email': session.get('user_email', ''),
        'pwd_mask': '•' * int(session.get('pwd_len', 8))
    }
    return render_template('dashboard.html', user=user_info, vehiculos=user_vehicles, accesos=user_access)


@user_bp.route('/perfil/password', methods=['POST'])
@login_required
def actualizar_password():
    user_id = session['user_id']
    new_password = request.form.get('new_password')
    confirm_password = request.form.get('confirm_password')
    ok, msg = validate_password(new_password)
    if not ok:
        flash(msg, 'error')
        return redirect(url_for('user.dashboard'))
    if new_password != confirm_password:
        flash('Las contraseñas no coinciden', 'error')
        return redirect(url_for('user.dashboard'))
    try:
        r = requests.patch(
            f"{API_URL}/usuarios/{user_id}",
            json={"password": new_password},
            timeout=7,
            headers=_session_auth_headers(),
        )
        if r.status_code == 200:
            session['pwd_len'] = len(new_password)
            flash('Contraseña actualizada correctamente', 'success')
        else:
            try:
                detail = r.json().get('detail')
                msg = detail if isinstance(detail, str) else 'No se pudo actualizar'
            except Exception:
                msg = 'No se pudo actualizar'
            flash(msg, 'error')
    except requests.exceptions.Timeout:
        flash('Error de conexión', 'error')
    except Exception:
        flash('Error al procesar', 'error')
    return redirect(url_for('user.dashboard'))


@user_bp.route('/perfil')
@login_required
def perfil():
    user_info = {
        'username': session['user_username'],
        'email': session['user_email']
    }
    return render_template('perfil.html', user=user_info)


@user_bp.route('/generar-qr')
@login_required
def generar_qr():
    user_info = {
        'nombre': (session.get('user_nombre', '') + ' ' + session.get('user_apellidos', '')).strip() or session.get('user_username', ''),
        'matricula': session.get('user_username', ''),
        'username': session.get('user_username', ''),
        'email': session.get('user_email', '')
    }
    return render_template('qr.html', user=user_info)


@user_bp.route('/vehiculos', methods=['GET', 'POST'])
@login_required
def vehiculos():
    user_id = int(session['user_id'])
    user_info = {
        'username': session['user_username'],
        'email': session['user_email']
    }
    if request.method == 'POST':
        action = request.form.get('action')
        try:
            if action == 'create':
                marca = (request.form.get('marca') or '').strip()
                modelo = (request.form.get('modelo') or '').strip()
                if not marca or len(marca) < 2:
                    flash('Marca inválida (mínimo 2 caracteres)', 'error')
                    return redirect(url_for('user.vehiculos'))
                if not modelo:
                    flash('Modelo es obligatorio', 'error')
                    return redirect(url_for('user.vehiculos'))
                anio_raw = request.form.get('anio') or ''
                color = (request.form.get('color') or '').strip()[:30]
                tipo = (request.form.get('tipo') or '').strip().lower()[:20]
                try:
                    anio = int(anio_raw) if anio_raw else None
                    if anio is not None and (anio < 1950 or anio > datetime.date.today().year + 1):
                        flash('Año fuera de rango', 'error')
                        return redirect(url_for('user.vehiculos'))
                except (ValueError, TypeError):
                    anio = None
                payload = {"marca": marca, "modelo": modelo, "anio": anio, "color": color, "tipo": tipo, "owner_id": user_id}
                r = requests.post(
                    f"{API_URL}/vehiculos/",
                    json=payload,
                    timeout=7,
                    headers=_session_auth_headers(),
                )
                if r.status_code in (201, 200):
                    flash('Vehículo agregado', 'success')
                else:
                    detalle = None
                    try:
                        if r.headers.get('content-type','').startswith('application/json'):
                            detalle = r.json().get('detail')
                    except Exception:
                        pass
                    flash(detalle or 'No se pudo agregar el vehículo', 'error')
            elif action == 'edit':
                marca = (request.form.get('marca') or '').strip()
                modelo = (request.form.get('modelo') or '').strip()
                if not marca or len(marca) < 2:
                    flash('Marca inválida', 'error')
                    return redirect(url_for('user.vehiculos'))
                if not modelo:
                    flash('Modelo es obligatorio', 'error')
                    return redirect(url_for('user.vehiculos'))
                vid = request.form.get('vehiculo_id')
                if not vid:
                    flash('ID de vehículo faltante', 'error')
                    return redirect(url_for('user.vehiculos'))
                anio_raw = request.form.get('anio') or ''
                try:
                    anio = int(anio_raw) if anio_raw else None
                except (ValueError, TypeError):
                    anio = None
                color = (request.form.get('color') or '').strip()[:30]
                tipo = (request.form.get('tipo') or '').strip().lower()[:20]
                payload = {"marca": marca, "modelo": modelo, "anio": anio, "color": color, "tipo": tipo}
                r = requests.patch(
                    f"{API_URL}/vehiculos/{vid}",
                    json=payload,
                    timeout=7,
                    headers=_session_auth_headers(),
                )
                if r.status_code == 200:
                    flash('Vehículo actualizado', 'success')
                else:
                    detalle = None
                    try:
                        detalle = r.json().get('detail') if r.headers.get('content-type','').startswith('application/json') else None
                    except Exception:
                        pass
                    flash(detalle or 'No se pudo actualizar', 'error')
            elif action == 'delete':
                vid = request.form.get('delete_id')
                if not vid:
                    flash('ID faltante', 'error')
                    return redirect(url_for('user.vehiculos'))
                r = requests.delete(
                    f"{API_URL}/vehiculos/{vid}",
                    timeout=7,
                    headers=_session_auth_headers(),
                )
                if r.status_code == 200:
                    flash('Vehículo eliminado', 'success')
                else:
                    detalle = None
                    try:
                        detalle = r.json().get('detail') if r.headers.get('content-type','').startswith('application/json') else None
                    except Exception:
                        pass
                    flash(detalle or 'No se pudo eliminar', 'error')
        except requests.exceptions.Timeout:
            flash('Error de conexión', 'error')
        except Exception as e:
            flash(f'Error: {str(e)}', 'error')
        return redirect(url_for('user.vehiculos'))

    all_vehicles = get_api_data("/vehiculos/", _session_api_token())
    user_vehicles = [v for v in all_vehicles if isinstance(v, dict) and v.get('owner_id') == user_id]
    return render_template('vehiculos.html', user=user_info, vehiculos=user_vehicles)


@user_bp.route('/historial')
@login_required
def historial():
    user_id = session['user_id']
    user_info = {
        'username': session['user_username'],
        'email': session['user_email']
    }
    all_access = get_api_data("/accesos/", _session_api_token())
    user_access = [a for a in all_access if isinstance(a, dict) and a.get('user_id') == user_id]
    return render_template('historial.html', user=user_info, accesos=user_access)


# ============================================================
# ENDPOINTS JSON PARA APP MOVIL (Protegidos con JWT)
# ============================================================

# --- Autenticación: Login devuelve JWT real ---
@user_bp.route('/api/auth/login', methods=['POST'])
def api_login():
    """Login JWT para App Móvil. Devuelve token JWT firmado + info user."""
    payload = request.get_json(force=True, silent=True) or {}
    email = str(payload.get('email', '')).strip()
    password = str(payload.get('password', ''))

    if not validate_email(email):
        if LOGIN_ATTEMPTS is not None:
            LOGIN_ATTEMPTS.labels(status="invalid").inc()
        return _json_response({"detail": "Correo electrónico inválido"}, 400)
    if not password:
        return _json_response({"detail": "Contraseña es obligatoria"}, 400)

    try:
        r = requests.post(f"{API_URL}/auth/login", json={"email": email, "password": password}, timeout=10)
    except requests.exceptions.Timeout:
        if LOGIN_ATTEMPTS is not None:
            LOGIN_ATTEMPTS.labels(status="timeout").inc()
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        if LOGIN_ATTEMPTS is not None:
            LOGIN_ATTEMPTS.labels(status="error").inc()
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)

    if r.status_code != 200:
        if LOGIN_ATTEMPTS is not None:
            LOGIN_ATTEMPTS.labels(status="failed").inc()
        try:
            d = r.json()
            msg = d.get("detail", "Credenciales inválidas") if isinstance(d, dict) else "Credenciales inválidas"
        except Exception:
            msg = "Credenciales inválidas"
        return _json_response({"detail": msg}, r.status_code if 400 <= r.status_code < 599 else 401)

    if LOGIN_ATTEMPTS is not None:
        LOGIN_ATTEMPTS.labels(status="success").inc()

    api_user = r.json() if isinstance(r.json(), dict) else {}
    uid = api_user.get('id')
    if not uid:
        return _json_response({"detail": "Respuesta inválida del servidor"}, 502)

    user_info = {
        "id": uid,
        "username": api_user.get('username', ''),
        "email": api_user.get('email', ''),
        "nombre": api_user.get('nombre', ''),
        "apellidos": api_user.get('apellidos', ''),
        "id_rol": api_user.get('id_rol'),
        "id_persona": api_user.get('id_persona'),
        "id_carrera": api_user.get('id_carrera'),
        "id_departamento": api_user.get('id_departamento'),
        "activo": bool(api_user.get('activo', True)),
    }
    token = _create_jwt_token(user_info)
    return _json_response({
        "access_token": token,
        "token_type": "bearer",
        "expires_in": 7200,
        **user_info,
    })


# --- Endpoints protegidos con JWT a partir de aquí ---

@user_bp.route('/api/auth/me', methods=['GET'])
@jwt_required
def api_auth_me():
    """Devuelve la info del usuario actualmente autenticado."""
    return _json_response(dict(getattr(g, 'jwt_user', {})))


@user_bp.route('/api/auth/logout', methods=['POST'])
@jwt_required
def api_auth_logout():
    """Logout (placeholder - en stateless JWT es no-op de lado servidor)"""
    return _json_response({"status": "ok", "msg": "Sesión cerrada"})


# --- Vehículos ---
@user_bp.route('/api/vehiculos/', methods=['GET'])
@jwt_required
def api_vehiculos_list():
    data = get_api_data("/vehiculos/", _request_bearer_token())
    return _json_response(data)


@user_bp.route('/api/vehiculos/', methods=['POST'])
@jwt_required
def api_vehiculos_create():
    payload = request.get_json(force=True, silent=True) or {}

    # Validar campos antes de enviar
    marca = (payload.get('marca') or '').strip()
    modelo = (payload.get('modelo') or '').strip()
    if not marca or len(marca) < 2:
        return _json_response({"detail": "Marca inválida (mínimo 2 caracteres)"}, 400)
    if not modelo:
        return _json_response({"detail": "Modelo es obligatorio"}, 400)
    if payload.get('plate') and not validate_plate(payload.get('plate')):
        return _json_response({"detail": "Placa inválida (3-15 alfanumérico/guion)"}, 400)
    if not payload.get('owner_id'):
        payload['owner_id'] = int(getattr(g, 'jwt_user', {}).get('sub') or 0)

    try:
        r = requests.post(
            f"{API_URL}/vehiculos/",
            json=payload,
            timeout=7,
            headers=_request_auth_headers(),
        )
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


@user_bp.route('/api/vehiculos/<int:vid>/', methods=['PATCH', 'PUT'])
@jwt_required
def api_vehiculos_update(vid):
    payload = request.get_json(force=True, silent=True) or {}
    if 'marca' in payload and (len(str(payload['marca']).strip()) < 2):
        return _json_response({"detail": "Marca muy corta"}, 400)
    try:
        r = requests.patch(
            f"{API_URL}/vehiculos/{vid}/",
            json=payload,
            timeout=7,
            headers=_request_auth_headers(),
        )
        if r.status_code not in range(200, 300):
            r2 = requests.patch(
                f"{API_URL}/vehiculos/{vid}",
                json=payload,
                timeout=7,
                headers=_request_auth_headers(),
            )
            if r2.status_code in range(200, 300):
                r = r2
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


@user_bp.route('/api/vehiculos/<int:vid>/', methods=['DELETE'])
@jwt_required
def api_vehiculos_delete(vid):
    try:
        r = requests.delete(
            f"{API_URL}/vehiculos/{vid}/",
            timeout=7,
            headers=_request_auth_headers(),
        )
        if r.status_code not in range(200, 300):
            r2 = requests.delete(
                f"{API_URL}/vehiculos/{vid}",
                timeout=7,
                headers=_request_auth_headers(),
            )
            if r2.status_code in range(200, 300):
                r = r2
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "Eliminado"}, status)


# --- Accesos ---
@user_bp.route('/api/accesos/', methods=['GET'])
@jwt_required
def api_accesos_list():
    data = get_api_data("/accesos/", _request_bearer_token())
    return _json_response(data)


@user_bp.route('/api/accesos/', methods=['POST'])
@jwt_required
def api_accesos_create():
    payload = request.get_json(force=True, silent=True) or {}
    payload.setdefault("timestamp", datetime.datetime.utcnow().isoformat())
    payload.setdefault("method", "qr")

    # Validar user_id del body coincida con el del token (anti-spoofing)
    jwt_sub = getattr(g, 'jwt_user', {}).get('sub')
    if jwt_sub and payload.get('user_id') and int(payload['user_id']) != int(jwt_sub):
        return _json_response({"detail": "No puedes registrar accesos para otro usuario"}, 403)

    try:
        r = requests.post(
            f"{API_URL}/accesos/",
            json=payload,
            timeout=7,
            headers=_request_auth_headers(),
        )
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else payload, status)


# --- Alertas ---
@user_bp.route('/api/alerts/', methods=['GET'])
@jwt_required
def api_alerts_list():
    data = get_api_data("/alerts/", _request_bearer_token())
    return _json_response(data)


@user_bp.route('/api/alerts/<int:aid>', methods=['PATCH', 'PUT'])
@user_bp.route('/api/alerts/<int:aid>/', methods=['PATCH', 'PUT'])
@jwt_required
def api_alerts_update(aid):
    payload = request.get_json(force=True, silent=True) or {}
    try:
        r = requests.patch(
            f"{API_URL}/alerts/{aid}",
            json=payload,
            timeout=7,
            headers=_request_auth_headers(),
        )
        if r.status_code not in range(200, 300):
            r2 = requests.patch(
                f"{API_URL}/alerts/{aid}/",
                json=payload,
                timeout=7,
                headers=_request_auth_headers(),
            )
            if r2.status_code in range(200, 300):
                r = r2
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


# --- Usuarios (actualizar password / perfil) ---
@user_bp.route('/api/usuarios/<int:uid>', methods=['PATCH', 'PUT'])
@user_bp.route('/api/usuarios/<int:uid>/', methods=['PATCH', 'PUT'])
@jwt_required
def api_usuarios_update(uid):
    payload = request.get_json(force=True, silent=True) or {}
    jwt_sub = getattr(g, 'jwt_user', {}).get('sub')
    if jwt_sub and int(uid) != int(jwt_sub):
        return _json_response({"detail": "Solo puedes modificar tu propio perfil"}, 403)

    new_password = payload.get("password") or payload.get("new_password")
    if new_password:
        new_password = str(new_password)
        ok, msg = validate_password(new_password)
        if not ok:
            return _json_response({"detail": msg}, 400)
        confirm = payload.get("confirm_password")
        if confirm and confirm != new_password:
            return _json_response({"detail": "Las contraseñas no coinciden"}, 400)
        patch_payload = {"password": new_password}
    else:
        patch_payload = {k: v for k, v in payload.items() if k not in ("password", "new_password", "confirm_password", "id", "id_rol")}

    try:
        r = requests.patch(
            f"{API_URL}/usuarios/{uid}",
            json=patch_payload,
            timeout=7,
            headers=_request_auth_headers(),
        )
        if r.status_code not in range(200, 300):
            r2 = requests.patch(
                f"{API_URL}/usuarios/{uid}/",
                json=patch_payload,
                timeout=7,
                headers=_request_auth_headers(),
            )
            if r2.status_code in range(200, 300):
                r = r2
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)

    if r.status_code == 200:
        session.pop('pwd_len', None)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "Actualizado"}, status)


# --- Generación de QR payload para App Móvil ---
@user_bp.route('/api/qr/payload', methods=['GET'])
@jwt_required
def api_qr_payload():
    """Devuelve payload JSON firmado que la App puede renderizar como QR"""
    u = getattr(g, 'jwt_user', {})
    payload = {
        "t": "okovision_user",
        "uid": u.get("id") or u.get("sub"),
        "uname": (u.get("nombre", "") + " " + u.get("apellidos", "")).strip() or u.get("username", ""),
        "email": u.get("email", ""),
        "v": 1,
        "ts": int(datetime.datetime.utcnow().timestamp()),
    }
    # Incluir un pequeño signature para que el scanner lo pueda verificar
    try:
        secret = current_app.config.get("JWT_SECRET_KEY", "oko-production-secret-key-2026-change-me-please")
        sig_payload = f"{payload['uid']}.{payload['ts']}.{secret}"
        import hashlib
        payload["sig"] = hashlib.sha256(sig_payload.encode()).hexdigest()[:16]
    except Exception:
        pass
    return _json_response(payload)
