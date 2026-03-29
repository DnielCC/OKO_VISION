from flask import Blueprint, render_template, redirect, url_for, session, flash, request
from functools import wraps
import requests
import os
import re

# Creamos el Blueprint llamado 'user_bp'
user_bp = Blueprint('user', __name__, template_folder='templates')

# Configuración de la API
API_URL = os.getenv("API_URL", "http://api_backend:8000")

def get_api_data(endpoint):
    try:
        # Se agrega timeout de 5 segundos para evitar que la petición se quede cargando
        response = requests.get(f"{API_URL}{endpoint}", timeout=5)
        if response.status_code == 200:
            return response.json()
        print(f"API returned status {response.status_code} for {endpoint}")
    except requests.exceptions.Timeout:
        print(f"Timeout connecting to API at {API_URL}")
        return "TIMEOUT"
    except Exception as e:
        print(f"Error connecting to API: {e}")
        return "ERROR"
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
    user_vehicles = [v for v in all_vehicles if v['owner_id'] == user_id]
    
    all_access = get_api_data("/accesos/")
    user_access = [a for a in all_access if a['user_id'] == user_id][:5]
    
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
                    flash('No se pudo actualizar el vehículo', 'error')
            elif action == 'delete':
                vehiculo_id = request.form.get('delete_id')
                r = requests.delete(f"{API_URL}/vehiculos/{vehiculo_id}", timeout=5)
                if r.status_code == 200:
                    flash('Vehículo eliminado', 'success')
                else:
                    flash('No se pudo eliminar el vehículo', 'error')
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
    user_access = [a for a in all_access if a['user_id'] == user_id]
    
    return render_template('historial.html', user=user_info, accesos=user_access)
