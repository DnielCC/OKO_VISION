#!/usr/bin/env python3
"""
Servidor simple para demostrar las vistas del sistema OKO VISION
Este archivo permite visualizar las plantillas sin necesidad de instalar Flask
"""

import http.server
import socketserver
import os
import urllib.parse
from pathlib import Path

class TemplateHandler(http.server.SimpleHTTPRequestHandler):
    def do_GET(self):
        # Parse the path
        parsed_path = urllib.parse.urlparse(self.path)
        path = parsed_path.path.lstrip('/')
        
        # Default to login.html
        if path == '' or path == 'login':
            path = 'templates/login.html'
        elif path == 'dashboard':
            path = 'templates/dashboard.html'
        elif path == 'perfil':
            path = 'templates/perfil.html'
        elif path == 'qr':
            path = 'templates/qr.html'
        elif path == 'vehiculos':
            path = 'templates/vehiculos.html'
        elif path == 'historial':
            path = 'templates/historial.html'
        elif path.startswith('templates/'):
            # Keep the path as is for template files
            pass
        else:
            # Try to serve static files or default to login
            if os.path.exists(path):
                pass
            else:
                path = 'templates/login.html'
        
        # Check if file exists
        if os.path.exists(path):
            self.serve_file(path)
        else:
            self.send_error(404, "File not found")
    
    def serve_file(self, file_path):
        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Determine content type
            if file_path.endswith('.html'):
                content_type = 'text/html; charset=utf-8'
            elif file_path.endswith('.css'):
                content_type = 'text/css; charset=utf-8'
            elif file_path.endswith('.js'):
                content_type = 'application/javascript'
            else:
                content_type = 'text/plain'
            
            self.send_response(200)
            self.send_header('Content-type', content_type)
            self.send_header('Content-length', str(len(content.encode('utf-8'))))
            self.end_headers()
            self.wfile.write(content.encode('utf-8'))
        except Exception as e:
            self.send_error(500, f"Error serving file: {e}")

def main():
    PORT = 8080
    
    # Change to the Flask_User directory
    os.chdir('/home/dnts/Desktop/OKO_VISION/Flask_User')
    
    print(f"""
╔══════════════════════════════════════════════════════════════╗
║                    OKO VISION - Sistema Usuario               ║
╠══════════════════════════════════════════════════════════════╣
║  Servidor iniciado en: http://localhost:{PORT}               ║
║                                                              ║
║  Rutas disponibles:                                          ║
║  • http://localhost:{PORT}/              → Login              ║
║  • http://localhost:{PORT}/login        → Login              ║
║  • http://localhost:{PORT}/dashboard    → Dashboard          ║
║  • http://localhost:{PORT}/perfil       → Perfil             ║
║  • http://localhost:{PORT}/qr           → QR de Acceso       ║
║  • http://localhost:{PORT}/vehiculos    → Mis Vehículos      ║
║  • http://localhost:{PORT}/historial    → Historial          ║
║                                                              ║
║  Usuarios de prueba:                                         ║
║  • alumno1 / 123456                                          ║
║  • alumno2 / 123456                                          ║
║  • alumno3 / 123456                                          ║
║                                                              ║
║  Presiona Ctrl+C para detener el servidor                     ║
╚══════════════════════════════════════════════════════════════╝
    """)
    
    with socketserver.TCPServer(("", PORT), TemplateHandler) as httpd:
        try:
            httpd.serve_forever()
        except KeyboardInterrupt:
            print("\n🛑 Servidor detenido por el usuario")
            httpd.shutdown()

if __name__ == "__main__":
    main()
