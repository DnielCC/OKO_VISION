from flask import Blueprint, render_template

# Creamos el Blueprint llamado 'user_bp'
user_bp = Blueprint('user', __name__, template_folder='templates')

@user_bp.route('/perfil')
def perfil():
    return render_template('perfil.html')