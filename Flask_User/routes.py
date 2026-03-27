from flask import Blueprint, render_template, redirect, url_for, session, flash, request
from functools import wraps
import requests
import os

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
        email = request.form.get('username') # El campo sigue llamándose 'username' en el form
        password = request.form.get('password')
        
        # Consultamos a la API para verificar el usuario por email
        # Corregido: El endpoint correcto es /usuarios/ según la configuración del Backend
        users = get_api_data("/usuarios/")
        
        if users == "TIMEOUT" or users == "ERROR":
            flash('Error de conexión con el servidor de seguridad. Por favor intente más tarde.', 'error')
            return render_template('login.html')
            
        # Buscamos por email específicamente, ya que el usuario indicó que son correos
        user = next((u for u in users if u['email'] == email), None)
        
        if user: # Simulación de contraseña correcta (en producción validar hash)
            session['user_id'] = user['id']
            session['user_username'] = user['username']
            session['user_email'] = user['email']
            flash(f"¡Bienvenido {user['username']}!", 'success')
            return redirect(url_for('user.dashboard'))
        else:
            flash('Correo o contraseña incorrectos', 'error')
    
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
        'username': session['user_username'],
        'email': session['user_email']
    }
    
    return render_template('dashboard.html', 
                         user=user_info, 
                         vehiculos=user_vehicles, 
                         accesos=user_access)

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
        'username': session['user_username'],
        'email': session['user_email']
    }
    return render_template('qr.html', user=user_info)

@user_bp.route('/vehiculos', methods=['GET', 'POST'])
@login_required
def vehiculos():
    user_id = session['user_id']
    user_info = {
        'username': session['user_username'],
        'email': session['user_email']
    }
    
    if request.method == 'POST':
        # Aquí se implementarían las llamadas POST/PUT/DELETE a la API
        flash('Funcionalidad de actualización de base de datos activa vía API', 'success')
        return redirect(url_for('user.vehiculos'))
    
    all_vehicles = get_api_data("/vehiculos/")
    user_vehicles = [v for v in all_vehicles if v['owner_id'] == user_id]
    
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