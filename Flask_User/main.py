from flask import Flask
from routes import user_bp  

def create_app():
    app = Flask(__name__)
    
    # Configuración necesaria para sesiones y flash messages
    app.config['SECRET_KEY'] = 'tu_clave_secreta_aqui'
    
    # Registrar el blueprint de rutas de usuario
    app.register_blueprint(user_bp)

    return app

if __name__ == '__main__':
    app = create_app()
    app.run(port=8080, debug=True)