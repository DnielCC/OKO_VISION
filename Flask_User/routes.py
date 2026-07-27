from flask import Blueprint, render_template, redirect, url_for, session, flash, request, jsonify
from functools import wraps
import requests
import os
import re
import secrets
import datetime
import json as _json

# Creamos el Blueprint llamado 'user_bp'
user_bp = Blueprint('user', __name__, template_folder='templates')

# Configuración de la API
API_URL = os.getenv("API_URL", "http://api_backend:8000")

def get_api_data(endpoint):
    try:
        # Se agrega timeout de 5 segundos para evitar que la petición se quede cargando
        response = requests.get(f"{API_URL}{endpoint}", timeout=5)
        if response.status_code == 200:
            data = response.json()
            # Asegurarse de devolver una lista incluso si la API devuelve un dict
            if isinstance(data, list):
                return data
            elif isinstance(data, dict) and "data" in data and isinstance(data["data"], list):
                return data["data"]
            elif isinstance(data, dict):
                # Si es un dict único, devolverlo como lista de un elemento
                return [data]
        print(f"API returned status {response.status_code} for {endpoint}")
    except requests.exceptions.Timeout:
        print(f"Timeout connecting to API at {API_URL}")
    except Exception as e:
        print(f"Error connecting to API: {e}")
    # Siempre devolver una lista vacía en caso de error
    return []

def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session or 'user_username' not in session or 'user_email' not in session:
            # Si falta algún dato en la sesión, forzamos re-login
            return redirect(url_for('user.login'))
        return f(*args, **kwargs)
    return decorated_function

@user_bp.route('/')
def index():
    if 'user_id' in session:
        return redirect(url_for('user.dashboard'))
    return redirect(url_for('user.login'))

@user_bp.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        email = request.form.get('username') 
        password = request.form.get('password')
        
        try:
            # Login por medio de POST al endpoint de la API
            response = requests.post(f"{API_URL}/auth/login", json={
                "email": email,
                "password": password
            }, timeout=5)
            
            if response.status_code == 200:
                user = response.json()
                session['user_id'] = user['id']
                session['user_username'] = user['username']
                session['user_email'] = user['email']
                session['user_nombre'] = user.get('nombre', '')
                session['user_apellidos'] = user.get('apellidos', '')
                flash(f"¡Bienvenido {user['username']}!", 'success')
                return redirect(url_for('user.dashboard'))
            elif response.status_code == 401:
                flash('Correo o contraseña incorrectos', 'error')
            else:
                flash('Error inesperado de la API', 'error')
                
        except requests.exceptions.Timeout:
            flash('Error de conexión con el servidor. Por favor intente más tarde.', 'error')
            return render_template('login.html')
        except Exception as e:
            flash(f'Error: {e}', 'error')
            return render_template('login.html')
    
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
    
    # Obtener datos reales de la API
    all_vehicles = get_api_data("/vehiculos/")
    user_vehicles = [v for v in all_vehicles if isinstance(v, dict) and v.get('owner_id') == user_id]
    
    all_access = get_api_data("/accesos/")
    user_access = [a for a in all_access if isinstance(a, dict) and a.get('user_id') == user_id][:5]
    
    user_info = {
        'nombre': (session.get('user_nombre', '') + ' ' + session.get('user_apellidos', '')).strip() or session.get('user_username', ''),
        'matricula': session.get('user_username', ''),
        'username': session.get('user_username', ''),
        'email': session.get('user_email', ''),
        'pwd_mask': '•' * int(session.get('pwd_len', 8))
    }
    
    return render_template('dashboard.html', 
                         user=user_info, 
                         vehiculos=user_vehicles, 
                         accesos=user_access)

@user_bp.route('/perfil/password', methods=['POST'])
@login_required
def actualizar_password():
    user_id = session['user_id']
    new_password = request.form.get('new_password')
    confirm_password = request.form.get('confirm_password')
    if not new_password or new_password != confirm_password:
        flash('Las contraseñas no coinciden', 'error')
        return redirect(url_for('user.dashboard'))
    if len(new_password) < 8 or len(new_password) > 64:
        flash('La contraseña debe tener entre 8 y 64 caracteres', 'error')
        return redirect(url_for('user.dashboard'))
    if re.search(r"\s", new_password):
        flash('La contraseña no debe contener espacios', 'error')
        return redirect(url_for('user.dashboard'))
    if not re.search(r"[A-Za-z]", new_password) or not re.search(r"\d", new_password):
        flash('Debe incluir letras y números', 'error')
        return redirect(url_for('user.dashboard'))
    comunes = {"12345678","password","password123","qwerty","abc123","11111111","123456789"}
    if new_password.lower() in comunes:
        flash('La contraseña es demasiado común', 'error')
        return redirect(url_for('user.dashboard'))
    try:
        r = requests.patch(f"{API_URL}/usuarios/{user_id}", json={"password": new_password}, timeout=5)
        if r.status_code == 200:
            session['pwd_len'] = len(new_password)
            flash('Contraseña actualizada correctamente', 'success')
        else:
            try:
                detail = r.json().get('detail')
                msg = detail if isinstance(detail, str) else 'No se pudo actualizar la contraseña'
            except Exception:
                msg = 'No se pudo actualizar la contraseña'
            flash(msg, 'error')
    except requests.exceptions.Timeout:
        flash('Error de conexión con el servidor', 'error')
    except Exception:
        flash('Error al procesar la solicitud', 'error')
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
                payload = {
                    "marca": request.form.get('marca'),
                    "modelo": request.form.get('modelo'),
                    "anio": int(request.form.get('anio')) if request.form.get('anio') else None,
                    "color": request.form.get('color'),
                    "tipo": (request.form.get('tipo') or '').strip().lower(),
                    "owner_id": user_id
                }
                r = requests.post(f"{API_URL}/vehiculos/", json=payload, timeout=5)
                if r.status_code == 201 or r.status_code == 200:
                    flash('Vehículo agregado', 'success')
                else:
                    detalle = r.json().get('detail') if r.headers.get('content-type','').startswith('application/json') else None
                    flash(detalle or 'No se pudo agregar el vehículo', 'error')
            elif action == 'edit':
                vehiculo_id = request.form.get('vehiculo_id')
                payload = {
                    "marca": request.form.get('marca'),
                    "modelo": request.form.get('modelo'),
                    "anio": int(request.form.get('anio')) if request.form.get('anio') else None,
                    "color": request.form.get('color'),
                    "tipo": (request.form.get('tipo') or '').strip().lower()
                }
                r = requests.patch(f"{API_URL}/vehiculos/{vehiculo_id}", json=payload, timeout=5)
                if r.status_code == 200:
                    flash('Vehículo actualizado', 'success')
                else:
                    detalle = r.json().get('detail') if r.headers.get('content-type','').startswith('application/json') else None
                    flash(detalle or 'No se pudo actualizar el vehÃ­culo', 'error')
            elif action == 'delete':
                vehiculo_id = request.form.get('delete_id')
                r = requests.delete(f"{API_URL}/vehiculos/{vehiculo_id}", timeout=5)
                if r.status_code == 200:
                    flash('Vehículo eliminado', 'success')
                else:
                    detalle = r.json().get('detail') if r.headers.get('content-type','').startswith('application/json') else None
                    flash(detalle or 'No se pudo eliminar el vehÃ­culo', 'error')
        except requests.exceptions.Timeout:
            flash('Error de conexión con el servidor', 'error')
        except Exception as e:
            flash('Error al procesar la solicitud', 'error')
        return redirect(url_for('user.vehiculos'))
    
    all_vehicles = get_api_data("/vehiculos/")
    try:
        user_vehicles = [v for v in all_vehicles if v.get('owner_id') == user_id]
    except Exception:
        user_vehicles = []
    
    return render_template('vehiculos.html', user=user_info, vehiculos=user_vehicles)

@user_bp.route('/historial')
@login_required
def historial():
    user_id = session['user_id']
    user_info = {
        'username': session['user_username'],
        'email': session['user_email']
    }
    
    all_access = get_api_data("/accesos/")
    user_access = [a for a in all_access if isinstance(a, dict) and a.get('user_id') == user_id]
    
    return render_template('historial.html', user=user_info, accesos=user_access)


# =========================================================
# ENDPOINTS JSON PARA LA APP MOVIL (Flask como API propia)
# =========================================================

def _to_json(data, default=None):
    """Intenta extraer JSON de la respuesta de FastAPI, normalizando formato."""
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
    if headers:
        for k, v in headers.items():
            resp.headers[k] = v
    return resp


# ---------------- Autenticación ----------------

@user_bp.route('/api/auth/login', methods=['POST'])
def api_login():
    """Login devolviendo token de sesión simple + info de usuario."""
    payload = request.get_json(force=True, silent=True) or {}
    email = str(payload.get('email', '')).strip()
    password = str(payload.get('password', ''))

    if not email or not password:
        return _json_response({"detail": "Correo y contraseña son obligatorios"}, 400)

    try:
        r = requests.post(f"{API_URL}/auth/login", json={"email": email, "password": password}, timeout=7)
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado con el servidor"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)

    if r.status_code != 200:
        try:
            d = r.json()
            msg = d.get("detail", "Credenciales inválidas") if isinstance(d, dict) else "Credenciales inválidas"
        except Exception:
            msg = "Credenciales inválidas"
        return _json_response({"detail": msg}, r.status_code if 400 <= r.status_code < 599 else 401)

    user = r.json() if isinstance(r.json(), dict) else {}
    uid = user.get('id')
    if not uid:
        return _json_response({"detail": "Respuesta inválida del servidor"}, 502)

    # Generar token simple propio de Flask (opcional, app móvil lo almacena)
    access_token = "oko_" + secrets.token_hex(32)

    resp_user = {
        "id": uid,
        "username": user.get('username', ''),
        "email": user.get('email', ''),
        "nombre": user.get('nombre', ''),
        "apellidos": user.get('apellidos', ''),
        "id_rol": user.get('id_rol'),
        "id_persona": user.get('id_persona'),
        "id_carrera": user.get('id_carrera'),
        "id_departamento": user.get('id_departamento'),
        "activo": bool(user.get('activo', True)),
    }

    return _json_response({
        "access_token": access_token,
        "token_type": "bearer",
        **resp_user,
    })


# ---------------- Vehículos ----------------

@user_bp.route('/api/vehiculos/', methods=['GET'])
def api_vehiculos_list():
    data = get_api_data("/vehiculos/")
    return _json_response(data)


@user_bp.route('/api/vehiculos/', methods=['POST'])
def api_vehiculos_create():
    payload = request.get_json(force=True, silent=True) or {}
    try:
        r = requests.post(f"{API_URL}/vehiculos/", json=payload, timeout=7)
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


@user_bp.route('/api/vehiculos/<int:vid>/', methods=['PATCH', 'PUT'])
def api_vehiculos_update(vid):
    payload = request.get_json(force=True, silent=True) or {}
    try:
        r = requests.patch(f"{API_URL}/vehiculos/{vid}/", json=payload, timeout=7)
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


@user_bp.route('/api/vehiculos/<int:vid>/', methods=['DELETE'])
def api_vehiculos_delete(vid):
    try:
        r = requests.delete(f"{API_URL}/vehiculos/{vid}/", timeout=7)
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "Eliminado"}, status)


# ---------------- Accesos ----------------

@user_bp.route('/api/accesos/', methods=['GET'])
def api_accesos_list():
    data = get_api_data("/accesos/")
    return _json_response(data)


@user_bp.route('/api/accesos/', methods=['POST'])
def api_accesos_create():
    payload = request.get_json(force=True, silent=True) or {}
    # Asegurar campos
    payload.setdefault("timestamp", datetime.datetime.utcnow().isoformat())
    payload.setdefault("method", "qr")
    try:
        r = requests.post(f"{API_URL}/accesos/", json=payload, timeout=7)
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else payload, status)


# ---------------- Alertas ----------------

@user_bp.route('/api/alerts/', methods=['GET'])
def api_alerts_list():
    data = get_api_data("/alerts/")
    return _json_response(data)


@user_bp.route('/api/alerts/<int:aid>', methods=['PATCH', 'PUT'])
@user_bp.route('/api/alerts/<int:aid>/', methods=['PATCH', 'PUT'])
def api_alerts_update(aid):
    payload = request.get_json(force=True, silent=True) or {}
    try:
        r = requests.patch(f"{API_URL}/alerts/{aid}", json=payload, timeout=7)
        if r.status_code not in range(200, 300):
            r2 = requests.patch(f"{API_URL}/alerts/{aid}/", json=payload, timeout=7)
            if r2.status_code in range(200, 300):
                r = r2
    except requests.exceptions.Timeout:
        return _json_response({"detail": "Tiempo de espera agotado"}, 504)
    except Exception as e:
        return _json_response({"detail": f"Error de conexión: {str(e)}"}, 502)
    body = _to_json(r, {})
    status = r.status_code if 200 <= r.status_code < 599 else 502
    return _json_response(body if isinstance(body, dict) else {"detail": "OK"}, status)


# ---------------- Usuarios (actualizar password) ----------------

@user_bp.route('/api/usuarios/<int:uid>', methods=['PATCH', 'PUT'])
@user_bp.route('/api/usuarios/<int:uid>/', methods=['PATCH', 'PUT'])
def api_usuarios_update(uid):
    payload = request.get_json(force=True, silent=True) or {}
    new_password = payload.get("password") or payload.get("new_password")

    if new_password:
        new_password = str(new_password)
        if len(new_password) < 8 or len(new_password) > 64:
            return _json_response({"detail": "La contraseña debe tener entre 8 y 64 caracteres"}, 400)
        if re.search(r"\s", new_password):
            return _json_response({"detail": "La contraseña no debe contener espacios"}, 400)
        if not re.search(r"[A-Za-z]", new_password) or not re.search(r"\d", new_password):
            return _json_response({"detail": "Debe incluir letras y números"}, 400)
        comunes = {"12345678","password","password123","qwerty","abc123","11111111","123456789"}
        if new_password.lower() in comunes:
            return _json_response({"detail": "La contraseña es demasiado común"}, 400)
        patch_payload = {"password": new_password}
    else:
        patch_payload = {k: v for k, v in payload.items() if k not in ("password", "new_password", "confirm_password")}

    try:
        r = requests.patch(f"{API_URL}/usuarios/{uid}", json=patch_payload, timeout=7)
        if r.status_code not in range(200, 300):
            r2 = requests.patch(f"{API_URL}/usuarios/{uid}/", json=patch_payload, timeout=7)
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
