from flask import Flask
from routes import user_bp  # Importamos el Blueprint

def create_app():
    app = Flask(__name__)

    # REGISTRAMOS EL BLUEPRINT
    app.register_blueprint(user_bp)

    return app

if __name__ == '__main__':
    app = create_app()
    app.run(port=8080, debug=True)