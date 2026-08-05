#!/bin/bash
# Verificar usuarios en la base de datos y probar hash 12345678
echo "=== USUARIOS EN BASE DE DATOS ==="
docker exec oko_prod_db psql -U oko_admin -d oko_vision -c "
SELECT u.id, u.identificador, u.id_rol, p.nombre, p.apellidos, p.mail, substring(u.password,1,30) as password_hash
FROM usuarios u
JOIN personas p ON u.id_persona = p.id
ORDER BY u.id;
"