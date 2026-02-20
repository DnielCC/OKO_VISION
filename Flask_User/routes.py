from flask import Blueprint, render_template, redirect, url_for, session, flash, request
from functools import wraps

# Creamos el Blueprint llamado 'user_bp'
user_bp = Blueprint('user', __name__, template_folder='templates')

# Usuarios simulados (en producción usar base de datos)
USUARIOS = {
    'alumno1': {'password': '123456', 'nombre': 'Juan Pérez', 'matricula': 'A001'},
    'alumno2': {'password': '123456', 'nombre': 'María García', 'matricula': 'A002'},
    'alumno3': {'password': '123456', 'nombre': 'Carlos López', 'matricula': 'A003'}
}

# Datos simulados de vehículos y accesos
VEHICULOS = {
    'alumno1': [
        {'placa': 'ABC-123', 'marca': 'Toyota', 'modelo': 'Corolla', 'color': 'Blanco'},
        {'placa': 'XYZ-789', 'marca': 'Honda', 'modelo': 'Civic', 'color': 'Negro'}
    ],
    'alumno2': [
        {'placa': 'DEF-456', 'marca': 'Nissan', 'modelo': 'Sentra', 'color': 'Azul'}
    ],
    'alumno3': []
}

ACCESOS = {
    'alumno1': [
        {'fecha': '2025-02-20 08:30', 'tipo': 'Entrada', 'placa': 'ABC-123', 'estatus': 'Autorizado'},
        {'fecha': '2025-02-20 14:15', 'tipo': 'Salida', 'placa': 'ABC-123', 'estatus': 'Normal'},
        {'fecha': '2025-02-19 09:00', 'tipo': 'Entrada', 'placa': 'XYZ-789', 'estatus': 'Autorizado'}
    ],
    'alumno2': [
        {'fecha': '2025-02-20 07:45', 'tipo': 'Entrada', 'placa': 'DEF-456', 'estatus': 'Autorizado'}
    ],
    'alumno3': []
}

def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
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
        username = request.form.get('username')
        password = request.form.get('password')
        
        if username in USUARIOS and USUARIOS[username]['password'] == password:
            session['user_id'] = username
            session['user_nombre'] = USUARIOS[username]['nombre']
            session['user_matricula'] = USUARIOS[username]['matricula']
            flash('¡Bienvenido {}!'.format(USUARIOS[username]['nombre']), 'success')
            return redirect(url_for('user.dashboard'))
        else:
            flash('Usuario o contraseña incorrectos', 'error')
    
    return render_template('login.html')

@user_bp.route('/logout')
def logout():
    session.clear()
    flash('Has cerrado sesión correctamente', 'info')
    return redirect(url_for('user.login'))

@user_bp.route('/dashboard')
@login_required
def dashboard():
    username = session['user_id']
    user_info = USUARIOS[username]
    vehiculos = VEHICULOS.get(username, [])
    accesos_recientes = ACCESOS.get(username, [])[:5]  # Últimos 5 accesos
    
    return render_template('dashboard.html', 
                         user=user_info, 
                         vehiculos=vehiculos, 
                         accesos=accesos_recientes)

@user_bp.route('/perfil')
@login_required
def perfil():
    username = session['user_id']
    user_info = USUARIOS[username]
    return render_template('perfil.html', user=user_info)

@user_bp.route('/generar-qr')
@login_required
def generar_qr():
    username = session['user_id']
    user_info = USUARIOS[username]
    return render_template('qr.html', user=user_info)

@user_bp.route('/vehiculos', methods=['GET', 'POST'])
@login_required
def vehiculos():
    username = session['user_id']
    user_info = USUARIOS[username]
    vehiculos = VEHICULOS.get(username, [])
    
    if request.method == 'POST':
        # Verificar límite de vehículos
        if len(vehiculos) >= 2:
            flash('Ya has alcanzado el límite de 2 vehículos', 'error')
            return redirect(url_for('user.vehiculos'))
        
        # Obtener datos del formulario
        placa = request.form.get('placa', '').strip().upper()
        marca = request.form.get('marca', '').strip()
        modelo = request.form.get('modelo', '').strip()
        color = request.form.get('color', '').strip()
        
        # Validar campos
        if not all([placa, marca, modelo, color]):
            flash('Todos los campos son obligatorios', 'error')
            return redirect(url_for('user.vehiculos'))
        
        # Verificar que la placa no esté duplicada
        for existing_vehicle in vehiculos:
            if existing_vehicle['placa'] == placa:
                flash('Ya existe un vehículo con esa placa', 'error')
                return redirect(url_for('user.vehiculos'))
        
        # Agregar nuevo vehículo
        nuevo_vehiculo = {
            'placa': placa,
            'marca': marca,
            'modelo': modelo,
            'color': color
        }
        
        VEHICULOS[username].append(nuevo_vehiculo)
        flash('Vehículo agregado exitosamente', 'success')
        return redirect(url_for('user.vehiculos'))
    
    return render_template('vehiculos.html', user=user_info, vehiculos=vehiculos)

@user_bp.route('/historial')
@login_required
def historial():
    username = session['user_id']
    user_info = USUARIOS[username]
    accesos = ACCESOS.get(username, [])
    return render_template('historial.html', user=user_info, accesos=accesos)