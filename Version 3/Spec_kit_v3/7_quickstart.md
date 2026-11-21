# 7_quickstart.md — Puesta en marcha y pruebas, versión 3

**Equipo de desarrollo:** Windows + PowerShell 5.1, Docker Compose.  
**Ruta local usada:** `C:\proyecto_investigacion\Version 2`  
**Servicios locales observados:** front `8124`, API `8126`, phpMyAdmin `8202`.

> Nunca pegar contraseñas, JWT, contenido de `.env` o SQL con usuarios reales en GitHub ni en reportes de error.

## 1. Requisitos

- Docker Desktop con Compose.
- PHP 8.3 dentro de los contenedores, extensiones PDO MySQL, cURL y JSON.
- Código V1/V2 y nuevas clases V3.
- `.env` privado con `JWT_SECRETO` y credenciales del servidor de base de datos; **no subir `.env` al repositorio**.
- Registros/roles `admin` y `consulta` activos, administradores provisionados previamente de forma segura.

## 2. Iniciar localmente

Desde PowerShell:

```powershell
cd "C:\proyecto_investigacion\Version 2"
docker compose up -d --build
docker compose ps
```

Verificar en navegador:

- Front: `http://localhost:8124/login`
- API: `http://localhost:8126/api/login` (solo admite POST; GET puede devolver 405)
- phpMyAdmin local: `http://localhost:8202`

## 3. Lint de los archivos fundamentales

```powershell
docker compose exec -T api-facturas php -l /var/www/html/index.php
docker compose exec -T api-facturas php -l /var/www/html/controladores/GuardiaAccesoV3.php
docker compose exec -T api-facturas php -l /var/www/html/controladores/ControladorUsuariosV3.php
docker compose exec -T api-facturas php -l /var/www/html/servicios/ServicioUsuariosV3.php
docker compose exec -T api-facturas php -l /var/www/html/repositorios/RepositorioUsuariosMariaDBV3.php
docker compose exec -T front-php php -l /var/www/html/index.php
```

`php -l` prueba sintaxis, **no funcionamiento**. En pasos anteriores hubo archivos vacíos de 0 bytes que pasaban lint; verificar tamaños y carga de clase.

## 4. Prueba de identidad y permisos sin contraseñas

```powershell
# API sin JWT: se espera HTTP 401.
curl.exe -sS -o NUL -w "%{http_code}" http://localhost:8126/api/area_conocimiento

# Administración de usuarios sin JWT: se espera HTTP 401.
curl.exe -sS -o NUL -w "%{http_code}" http://localhost:8126/api/usuario
```

Prueba manual del frontend: iniciar sesión como ADMIN, entrar a `/area_conocimiento`; abrir incógnito con cuenta CONSULTA, verificar que puede listar `/area_conocimiento` pero `/area_conocimiento/nuevo` muestra 403. **No pegar contraseñas ni JWT en documentación.**

Para comprobar 403 en API, ejecutar login mediante herramienta local segura y enviar `Authorization: Bearer <token>`; usar credenciales ficticias en BD de prueba. El código de HTTP de V3 se comprueba con la matriz de [6_contratos.md](6_contratos.md).

## 5. Pruebas de registro V3 (pendientes de acta)

Con token ADMIN de prueba:

1. `POST /api/usuario` con usuario nuevo único, correo válido, nombre y contraseña larga → 201 y `datos.id`.
2. `GET /api/usuario/{id}` → 200, `roles` incluye `consulta`, `activo=1`, sin `password` ni `password_hash`.
3. `POST /api/login` usando usuario nuevo y contraseña de prueba → 200.
4. Repetir el mismo POST → 409.
5. POST con correo inválido → 422.
6. Token CONSULTA + POST → 403.
7. Sin token + POST → 401.

**Estado:** se preparó el guion de pruebas del paso 32, pero el resultado completo no fue confirmado en el historial disponible. No registrar «aprobado» sin ejecutarlas.

## 6. Verificación de base de datos

En phpMyAdmin, seleccionar la BD local `investigacion` y ejecutar las consultas de [5_data_model.md](5_data_model.md). Comprobar que hay **19 tablas** y existen las tablas `usuario`, `rol`, `rol_usuario` con restricciones UNIQUE de `username` y `email`.

## 7. Evitar errores frecuentes de PowerShell

- En PowerShell 5.1 `curl` puede ser alias de `Invoke-WebRequest`; usar **`curl.exe`** para sintaxis de cURL.
- `php -r` con cadenas entrecomilladas puede fallar por propagación de comillas al contenedor. Para ejecutar un PHP corto, enviarlo por STDIN:

```powershell
$prueba = @'
<?php
echo "PHP STDIN correcto\n";
'@
$prueba | docker compose exec -T api-facturas php
```

- Si aparece «Se restauró el estado anterior», revisar respaldos, tamaño y archivos, no asumir que el código sigue escrito.

## 8. Catálogos y regresión V1/V2

Verificar como ADMIN y CONSULTA los seis catálogos, docente, grupo-detalle, semillero y siete puentes. Confirmar listados, formularios, errores y verbos admitidos. Cinco vistas de catálogo que mostraban «Sección en preparación» fueron reemplazadas manualmente; **no hay resultado documentado de la regresión completa posterior**.

## 9. Despliegue Alwaysdata — procedimiento de trabajo, no verificado

1. Crear base de datos MariaDB/MySQL de la cuenta y usuario con permisos.
2. En phpMyAdmin de Alwaysdata **seleccionar esa base** antes de importar. Si no se selecciona aparece `#1046 No database selected`.
3. Revisar el SQL de procedimientos V2: `CREATE DEFINER=investigacion@% PROCEDURE ...` quizá deba adaptarse para el usuario de Alwaysdata. No eliminar los procedimientos del maestro-detalle.
4. Si una importación quedó parcial, inspeccionar tablas antes de repetir; no sobrescribir sin respaldo.
5. Preparar y subir código sin `.env`, `.sql` privados, JWT, `*.bak*` ni scripts de pruebas.
6. Configurar en servidor `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `JWT_SECRETO` y URL API; renovar secretos respecto a local.
7. Elegir estructura de front/API y verificar reescritura de URL (archivos `.htaccess` o configuración equivalente del alojamiento). La existencia de dichos archivos en el checkout **todavía no se confirmó**.
8. Exigir HTTPS; validar login, 401 sin token, 403 CONSULTA en usuarios, y recorrido de catálogos.

**No declarar publicación operativa hasta probar conexiones, permisos y procedimientos.**

## 10. Evidencias a guardar

Capturas o transcripciones **sin secretos** de `docker compose ps`, lint, códigos HTTP 401/403/200/201/409/422 y listado de tablas (sin datos privados). Agregar referencias de commits y pruebas aprobadas al [9_Checklist.md](9_Checklist.md).
