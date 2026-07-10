from jose import jwt, JWTError
from app.security.auth import SECRET_KEY, ALGORITHM
import sys

if len(sys.argv) < 2:
    print("Uso: python debug_token.py <tu-token-aqui>")
    sys.exit(1)

token = sys.argv[1]

try:
    payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
    print("Decodificación exitosa del token!")
    print("Contenido del payload:", payload)
except JWTError as e:
    print("Error al decodificar el token:", str(e))
