from flask import Flask, jsonify, request
from flask_cors import CORS
from routes import user_bp  

def create_app():
    app = Flask(__name__)
    
    # Configuración necesaria para sesiones y flash messages
    app.config['SECRET_KEY'] = 'tu_clave_secreta_aqui'
    app.config['MAX_CONTENT_LENGTH'] = 16 * 1024 * 1024  # 16 MB
    
    # Habilitar CORS para la app móvil
    CORS(app, resources={r"/api/*": {"origins": "*"}}, supports_credentials=True)
    
    # Registrar el blueprint de rutas de usuario (web)
    app.register_blueprint(user_bp)

    # Endpoint de salud para la API JSON
    @app.route('/api/health', methods=['GET'])
    def api_health():
        return jsonify({
            "status": "ok",
            "service": "OKO VISION User API (Flask)",
            "version": "1.0.0",
            "endpoints": [
                "/api/auth/login",
                "/api/vehiculos/",
                "/api/accesos/",
                "/api/alerts/",
                "/api/usuarios/<id>",
            ]
        }), 200

    @app.errorhandler(404)
    def not_found(e):
        if request.path.startswith('/api/'):
            return jsonify({"detail": "Endpoint no encontrado"}), 404
        return jsonify({"error": "Not Found"}), 404

    @app.errorhandler(500)
    def server_error(e):
        return jsonify({"detail": f"Error interno: {str(e)}"}), 500

    return app

if __name__ == '__main__':
    app = create_app()
    app.run(host='0.0.0.0', port=5000, debug=True)