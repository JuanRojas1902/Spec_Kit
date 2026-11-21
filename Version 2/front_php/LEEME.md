# Frontend de investigación — V2, primera integración

## Instalación
1. Haz una copia o commit del directorio original `front_php`.
2. Copia la carpeta `front_php` de esta entrega a la raíz de tu proyecto. Para evitar mezclar los dos enrutadores, usa esta carpeta como frontend separado en lugar de superponer los archivos originales. Conserva `api_investigacion`, `db` y el `docker-compose.yml` existente. No uses `docker compose down -v`.
3. Ejecuta desde la raíz `docker compose up -d --build front-php` (y `docker compose up -d mariadb api-facturas` si no están iniciados).
4. Abre http://localhost:8124. Usa las credenciales ya creadas en la API, no las de MariaDB.
5. Si falla el login, consulta `docker compose logs --tail=100 front-php api-facturas` sin compartir contraseñas o tokens.

## Archivos
- `front_php/Dockerfile`: PHP CLI 8.3, cURL, puerto 8024, publicación exclusiva de `publico`.
- `front_php/cliente_api.php`: solicitudes JSON cURL, token Bearer y tratamiento de respuestas.
- `front_php/config/recursos.php`: recursos, PK, FK, campos y operaciones declaradas.
- `front_php/rutas/autenticacion.php`: sesión, CSRF, permisos, autenticación vía API y cierre de sesión.
- `front_php/rutas/recursos.php`: lectura, creación, modificación, retiro y carga de catálogos FK.
- `front_php/publico/index.php`: único punto público, lista blanca de recursos y protección de rutas.
- `front_php/vistas/plantilla.php`: navegación y HTML común.
- `front_php/vistas/login.php`: formulario de acceso.
- `front_php/vistas/inicio.php`: tarjetas agrupadas.
- `front_php/vistas/lista.php`: datos, mensajes, acciones según rol.
- `front_php/vistas/formulario.php`: formularios reutilizables con selects y maestro-detalle.
- `front_php/vistas/detalle.php`: detalle en lectura.
- `front_php/vistas/error.php`: mensajes controlados.
- `front_php/publico/marca.css`: apariencia.
- `front_php/publico/bootstrap.min.css` y `bootstrap.bundle.min.js`: Bootstrap 5.3.3 aportado en los archivos de ejemplo. Se conservan sin editar.

## Qué se conoce y qué falta
- Se configuraron los 16 recursos que aparecen en tu `index.php` V2. Los catálogos, docentes y grupos tienen operaciones basadas en tu front, los siete puentes creación/retiro y semilleros lectura. No se han validado los endpoints contra un backend vivo.
- Se detectaron seis referencias adicionales: usuario, rol, rol_usuario (del ejemplo) y convocatoria, proyecto, convocatoria_proyecto (de tus vistas). No se sabe cuáles integran realmente las 21 tablas. Aparecen como pendientes para el administrador, no como CRUD ficticios.
- Login: se adoptó el contrato del ejemplo, POST `/api/login` con `username`, `password`, respuesta `datos.token`, `datos.usuario.roles`, `datos.expira_en` (segundos), rol `admin`. Si el backend no implementa esto, no habrá acceso; no se crea login falso. No se usa contraseña de MariaDB. El backend debe validar el token y permisos en toda ruta protegida.
- Docentes: tu formulario envía cédula, nombres y apellidos. Revisar si la API exige más campos o FK; no se copian automáticamente campos del ejemplo.
- Claves y tipos de datos: comprobar los nombres de las columnas puente, orden de PK compuestas, ID de área de conocimiento, campos de grupo y contratos PUT/PATCH. Se usó el tipo entero que aparece en tu JS/index y el detalle maestro de grupos que aparece en tu formulario. Verificar si el PUT de grupo conserva semilleros existentes y referencias antes de usar datos reales.
- Los selects consultan la API, no MariaDB. Si el servicio limita sus listados, se debe agregar paginación según su contrato para disponer de todos los registros. Los catálogos fallidos bloquean el guardado.
- Mantén las vistas anteriores como respaldo. Esta versión usa rutas `/?vista=...` y reemplaza las pantallas JS antiguas: no se necesitan sus llamadas directas a localhost:8111 ni el .htaccess de Apache.
- Se mantiene Docker Compose sin cambios; para producción usa HTTPS y secretos externos. No publiques las contraseñas de desarrollo del Compose.

## Pruebas de aceptación pendientes en tu equipo
1. Abrir `/` sin sesión: muestra el login. Probar credenciales erróneas y válidas.
2. Navegar por los 16 recursos configurados; confirmar las cinco tablas faltantes contra el SQL.
3. Como admin, crear un registro de prueba y editarlo/retirarlo donde la API lo permita. Como usuario de consulta, verificar que no puede modificar registros.
4. Crear una relación eligiendo dos nombres en los desplegables; verificar que el servidor guarda los IDs correspondientes.
5. Comprobar el maestro-detalle de grupo en una base de pruebas. Verificar que un error no produce un guardado parcial.
6. Cerrar sesión y comprobar que las rutas protegidas no se pueden consultar.

## Sustentación
Navegador → controlador frontal PHP → cliente cURL → API → MariaDB. PHP guarda el JWT en sesión y envía el token al backend. La API hace las reglas de negocio y los repositorios acceden a MariaDB. El navegador ve el nombre de una universidad, por ejemplo, pero su `option value` contiene el ID; la API recibe ese identificador en JSON. CSRF protege los formularios frente a peticiones cruzadas; la API debe comprobar los permisos reales.
