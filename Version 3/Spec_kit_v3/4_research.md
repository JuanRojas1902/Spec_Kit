# 4_research.md — Investigación y decisiones de diseño, versión 3

**Estado:** registro razonado de decisiones de la implementación V3. Cada decisión indica alternativa, elección, motivo y consecuencia; lo pendiente no se da por probado.

| ID | Problema / alternativas | Decisión V3 | Motivo y consecuencia |
|---|---|---|---|
| D-V3-01 | PHP puro vs Laravel/Slim | PHP 8.3 puro, POO sin Composer | Continuidad con la constitución; requiere router y seguridad propios. |
| D-V3-02 | Sistema nuevo vs extensión V1/V2 | Añadir V3 sin reescribir 16 recursos | Mantiene regresión y permite entregar por versiones. |
| D-V3-03 | Sesión exclusiva vs JWT para la API | API con JWT Bearer; front con sesión PHP | Separa la autenticación entre procesos. |
| D-V3-04 | JWT RSA/HS256 | HS256 con secreto de entorno | Menos infraestructura; secreto fuerte y confidencial obligatorio. |
| D-V3-05 | JWT perpetuo vs expiración | `exp` a 3600 s (`iat`, `sub`) | Menor ventana de uso de token comprometido; requiere nuevo login al expirar. |
| D-V3-06 | Guardar roles en JWT vs consultar BD | Consultar roles activos en cada petición | Revocación inmediata; mayor carga de lecturas en BD. |
| D-V3-07 | Autorización solo en front vs en API | Guardia en todas las rutas `/api/*` protegidas | Impide que una llamada directa evada permisos. |
| D-V3-08 | Una única cuenta vs varios usuarios | `usuario`, `rol`, `rol_usuario` | Facilita separar administración y lectura. |
| D-V3-09 | Roles detallados vs dos perfiles | Roles `admin` y `consulta` | Cumple alcance didáctico; ampliar roles requiere nueva especificación. |
| D-V3-10 | Acceso GET universal vs administración privada | Usuarios y roles solo para ADMIN, incluso GET | Protege información administrativa. |
| D-V3-11 | Respuesta única de fallo vs 401/403 | 401 identidad; 403 autorización | Contratos claros, permiten pruebas y mensajes precisos. |
| D-V3-12 | Contraseña reversible vs hash | `password_hash()` y `password_verify()` | No almacenar contraseña recuperable; migración de algoritmo posible. |
| D-V3-13 | Rol libre en JSON vs fijo en registro | ADMIN crea cuenta con rol inicial `consulta` | Evita escalamiento por campo `rol` enviado desde cliente. |
| D-V3-14 | Dos INSERT sin control vs transacción | INSERT `usuario` + `rol_usuario` en transacción | No deja una cuenta creada parcialmente. |
| D-V3-15 | Buscar duplicados en PHP vs UNIQUE BD | Ambos: comprobar y exigir índices UNIQUE | El índice cubre carreras entre solicitudes; 1062 → 409. |
| D-V3-16 | SQL en controlador vs repositorio | PDO parametrizado en repositorio | Responsabilidad única y pruebas con repositorios simulados. |
| D-V3-17 | Instanciación dispersa vs ensamblador | Construcción de objetos en `servicios/ensamblador.php` | Dependencias explícitas, sustitución para pruebas. |
| D-V3-18 | Sesión sin protección vs regeneración+CSRF | Regenerar sesión al login, CSRF en POST, cookies HttpOnly/SameSite | Reduce secuestro de sesión y solicitudes no autorizadas; HTTPS en producción. |
| D-V3-19 | Mostrar botones a todos vs rol de interfaz | `frontEsAdminV3()` controla UI; API manda | Mejor UX, sin considerar UI un límite de seguridad. |
| D-V3-20 | Borrado físico vs lógico | Desactivar cuentas `activo=0` (objetivo pendiente) | Conserva referencias; obliga a validar `activo` en login y guardia. |
| D-V3-21 | Permitir revocar cualquier admin vs proteger último | Regla pendiente: nunca perder el último admin activo | Evita quedar sin administrador; transacción/bloqueo para concurrencia. |
| D-V3-22 | Despliegue con Docker vs PHP administrado | Alwaysdata como destino; Docker solo desarrollo | Exige adaptar URLs, secretos, SQL con procedimientos y enrutamiento. |
| D-V3-23 | Declarar V3 completa vs estado verificable | Marcar cada criterio probado/pendiente | Evita confundir avances de código con calidad y despliegue validados. |

## Riesgos investigados

### Revocación y caducidad

El JWT actual representa identidad (`sub`), no una fotografía permanente de los roles. El servidor debe consultar que el usuario siga activo y tenga roles activos en cada petición; no debe confiar solo en el rol guardado en `$_SESSION['usuario_v3']`, que sirve para presentación.

### Límites de `PASSWORD_DEFAULT`

El servicio de registro implementado exige entre 12 y 72 bytes, decisión conservadora por la longitud efectiva de bcrypt en implementaciones actuales. Esta regla está en bytes, no caracteres Unicode; se deberá revisar si se cambia el algoritmo. Nunca registrar contraseñas ni mostrar el hash en respuestas.

### CORS y despliegue

En desarrollo existen cabeceras CORS amplias en el router. Antes del despliegue se debe reducir el origen permitido según la arquitectura final, revisar métodos y comprobar que credenciales no se expongan a otros dominios. `Access-Control-Allow-Origin: *` no es una política adecuada para habilitar cookies de terceros.

### Importación MariaDB/Alwaysdata

Se observó un intento de importación con `#1046 No database selected`, y un volcado que contiene `CREATE DEFINER=investigacion@% PROCEDURE ...`. La corrección consiste en seleccionar la BD de destino antes de importar y adaptar definidores/permisos de procedimientos si fuera necesario. El mensaje de análisis SQL de phpMyAdmin no prueba por sí solo que el procedimiento sea inválido.

### Rutas limpias y `.htaccess`

En el frontend existen rutas `/area_conocimiento`, `/objetivo_desarrollo_sostenible`, etc., administradas por `index.php`. Antes de publicar, comprobar si existen `.htaccess` y si el sitio de Alwaysdata los procesa; de otro modo configurar reglas equivalentes o elegir la modalidad de sitio apropiada. **La existencia de `.htaccess` no estaba confirmada.**

### Pruebas

- Test unitario sin MariaDB de `ServicioUsuariosV3::crear` con repo simulado: validación y `password_hash`.
- Test de guardia con anónimo y `consulta`, más rol revocado y cuenta inactiva.
- Tests HTTP `201/409/422/403` de registro con admin y datos de prueba; **no consta confirmación del paso 32**.
- Regresión de 16 recursos de V1/V2 con JWT; casos `204`, maestro-detalle y errores de integridad.

## Decisiones por aprobar o revisar antes de producción

- Política final de contraseñas, bloqueo por intentos de login, HTTPS, límites de tasa y duración de sesión.
- Diseño definitivo de endpoints de edición/desactivación/roles y protección transaccional del último administrador.
- Estructura de hosting (un dominio o dos sitios) y configuración de CORS/rewrite.
- Permisos sobre procedimientos almacenados y alternativa compatible con Alwaysdata.
