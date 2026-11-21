# 8_tasks.md — Tareas de construcción, versión 3

**Convenciones:** `[x]` actividad implementada y observada durante el trabajo; `[ ]` pendiente o prueba sin acta de resultado. La documentación sigue la situación conocida al 21/11/2026. Para evitar confusión, una tarea codificada no se considera «finalizada» hasta probarla.

## Fase 0 — Preservar V1/V2

- [x] Mantener arquitectura PHP puro, MariaDB, Docker y Bootstrap local.
- [x] Conservar rutas de 16 recursos académicos y tabla de usuarios/roles.
- [ ] Ejecutar y registrar regresión completa de los 16 recursos bajo JWT.
- [ ] Revisar vistas catalogales sustituídas después de aparecer como «Sección en preparación».

## Fase 1 — Autenticación JWT

- [x] Crear `JwtHs256.php` con emitir/verificar HS256 y expiración.
- [x] Crear interfaz y repositorio de autenticación sobre `usuario`/`rol_usuario`/`rol`.
- [x] Crear servicio y controlador `POST /api/login`.
- [x] Preparar ADMIN activo y roles `admin` / `consulta`.
- [x] Pasar login ADMIN desde frontend.
- [ ] Revisar política de fuerza bruta y expiración de sesión para producción.

## Fase 2 — Guardia y permisos

- [x] Crear `GuardiaAccesoV3.php`.
- [x] Proteger `/api/*` excepto `/api/login` y preflight apropiado.
- [x] Comprobar sin token → 401 en recurso académico.
- [x] Comprobar CONSULTA GET → 200; POST → 403 en recurso académico.
- [x] Reservar rutas de usuarios/roles para ADMIN, incluso GET.
- [ ] Comprobar revocación efectiva de rol y cuenta desactivada.

## Fase 3 — Front con sesión V3

- [x] Crear `seguridad_v3.php` para sesión JWT/roles y login API.
- [x] Conectar `cliente_api.php` al Bearer de la sesión.
- [x] Sustituir login anterior por flujo de API en `index.php` y `login_v3.php`.
- [x] Aplicar CSRF a formularios POST y restricción de formularios/POST para CONSULTA.
- [x] Habilitar listado `area_conocimiento` con acciones exclusivas ADMIN.
- [ ] Ocultar todas las acciones de modificación en las otras vistas para CONSULTA.
- [ ] Comprobar todas las pantallas y formularios luego de reemplazar placeholders.

## Fase 4 — Administración: consultar usuarios

- [x] Reparar archivos que estaban vacíos (0 bytes) de `IRepositorioUsuariosV3.php` y `RepositorioUsuariosMariaDBV3.php`.
- [x] Crear `IServicioUsuariosV3.php` y `ServicioUsuariosV3.php`.
- [x] Crear `ControladorUsuariosV3.php` y verificar carga por STDIN (evitar `php -r` en PowerShell 5.1).
- [x] Conectar fábrica en `ensamblador.php` y rutas `GET /api/usuario`, `GET /api/usuario/{id}`.
- [x] Comprobar ADMIN 200, CONSULTA 403 y anónimo 401.
- [x] Excluir `password_hash` de respuestas.

## Fase 5 — Administración: crear usuarios

- [x] Crear método repositorio `crearConRolConsulta()` con transacción.
- [x] Incorporar método servicio `crear()` con validaciones y `password_hash()`.
- [x] Verificar `password_hash()` con repositorio simulado.
- [x] Revisar restricciones UNIQUE de `username` y `email` antes de habilitar POST (comprobación reportada como superada).
- [x] Conectar `POST /api/usuario` para ADMIN con 201/409/422, y 401 sin token.
- [ ] Ejecutar test real completo del paso 32 y guardar evidencia de 201/409/422/403 y login nuevo.
- [ ] Desactivar o borrar de forma controlada las cuentas de prueba tras terminar pruebas.

## Fase 6 — Administración pendiente

- [ ] Diseñar/implementar repositorio y servicio de edición de usuario.
- [ ] Diseñar/implementar desactivación lógica (`activo=0`).
- [ ] Proteger **último administrador activo** con operación transaccional.
- [ ] Diseñar/implementar asignar y quitar roles con control ADMIN.
- [ ] Comprobar revocación inmediata en siguiente solicitud.
- [ ] Crear vistas Bootstrap de listado, formulario y gestión de roles.
- [ ] Añadir errores amigables y navegación al módulo administrativo.

## Fase 7 — Validación y despliegue

- [ ] Probar login, roles, recuperación ante API caída y 16 recursos.
- [ ] Revisar CORS, CSRF, cookies HTTPS, secretos y registros de errores.
- [ ] Revisar `.gitignore`, excluir `.env`, backups y volcado SQL privado.
- [ ] Verificar presencia/función de `.htaccess` y configuración del hosting.
- [ ] Importar 19 tablas y procedimientos V2 en Alwaysdata; resolver #1046/DEFINER.
- [ ] Configurar BD/secretos/URL API en Alwaysdata y probar HTTPS.
- [ ] Probar como ADMIN/CONSULTA desde la URL pública.
- [ ] Firmar checklist y crear commit/tag de versión cuando esté realmente terminada.

## Orden corto recomendado para continuar

1. Cerrar la importación de BD en Alwaysdata sin pérdida de datos.
2. Confirmar qué rutas y archivos `.htaccess` requiere el alojamiento.
3. Configurar API/front HTTPS con secretos y accesos privados.
4. Probar flujos esenciales antes de exponer el sitio.
5. Terminar administración V3 y regresión antes de marcar versión cerrada.
