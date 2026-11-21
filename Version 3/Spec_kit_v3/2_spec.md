# 2_spec.md — Especificación funcional, versión 3

**Proyecto:** Sistema de Investigación · **Versión:** V3 (autenticación y autorización)  
**Fecha de documentación:** 21 de noviembre de 2026 · 
**Herencia:** V1 (catálogos) y V2 (relaciones, maestro-detalle y tablas puente). Se mantiene la constitución/metodología del curso.

> **QUÉ construir.** Este documento define alcance, reglas y aceptación; el [3_plan.md](3_plan.md) explica cómo construirlo. **No equivale a una certificación de que toda V3 esté terminada.**

## 1. Propósito

La V3 incorpora control de acceso al sistema de investigación existente. Las 16 rutas de recursos académicos de V1/V2 deben requerir autenticación. Habrá dos roles: `admin` (consulta y modifica según los verbos admitidos por cada recurso) y `consulta` (solo lee recursos académicos). La administración de usuarios y roles será exclusiva de `admin`.

Problemas que resuelve: impedir acceso anónimo; impedir que un usuario de consulta escriba mediante llamadas directas; almacenar contraseñas como hash; desactivar cuentas/roles sin esperar a que venza un token; separar la seguridad del frontend, de la API y de la base de datos.

## 2. Alcance

| Incluido V3 | No incluido o aplazado |
|---|---|
| Login con `POST /api/login`, JWT HS256 con expiración | Login público de autorregistro |
| Autenticación para `/api/*` salvo login y preflight CORS | OAuth, proveedores externos, restablecimiento por correo |
| Autorización `admin`/`consulta`, 401/403 | Permisos por fila o atributos (ABAC) |
| Sesión PHP del front y envío de Bearer a API | Frontend SPA / framework JS |
| Consulta administrativa de usuarios por ID/listado | Mostrar contraseña o `password_hash` en respuestas |
| Registro de usuario por ADMIN, rol inicial `consulta` | Creación automática de un administrador desde una página pública |
| Edición, desactivación y asignación/retiro de roles **como objetivo pendiente** | Eliminar físicamente cuentas o registros históricos |
| Vistas de gestión, regresión de V1/V2, preparación para Alwaysdata **pendientes** | Desplegar sin verificaciones mínimas de seguridad |

**Recursos académicos heredados (16):** `area_conocimiento`, `objetivo_desarrollo_sostenible`, `area_aplicacion`, `termino_clave`, `universidad`, `linea_investigacion`, `docente`, `grupo_investigacion`, `semillero`, `participa_grupo`, `participa_semillero`, `grupo_linea`, `semillero_linea`, `ac_linea`, `ods_linea`, `aa_linea`.

## 3. Roles y matriz de permisos

| Acción | Anónimo | `consulta` | `admin` |
|---|---:|---:|---:|
| `POST /api/login` | Sí | Sí | Sí |
| `GET` recursos académicos (si el recurso lo admite) | 401 | Sí | Sí |
| `POST`/`PUT`/`PATCH`/`DELETE` académico (solo verbos admitidos en V1/V2) | 401 | 403 | Sí |
| `GET /api/usuario` y `/api/usuario/{id}` | 401 | 403 | Sí |
| `POST /api/usuario` | 401 | 403 | Sí |
| Editar/desactivar usuarios; administrar roles (objetivo V3) | 401 | 403 | Sí |

**Reglas:** el servidor nunca confía solo en ocultar botones; `consulta` no puede escalar privilegios mandando un campo `rol` al registrar; una cuenta inactiva o sin rol activo no está autorizada; el sistema debe evitar eliminar al último administrador activo.

## 4. Requisitos funcionales

- **RF-V3-01 — Login:** recibir `username` y `password`; validar usuario activo y `password_verify`; emitir JWT con `sub`, `iat` y `exp` si tiene rol activo. Credenciales inválidas → 401; cuerpo inválido → 422.
- **RF-V3-02 — JWT:** validar firma HS256, expiración y `sub`; sin token o token inválido → 401. Secreto exclusivamente por variable de entorno.
- **RF-V3-03 — Autorización en cada solicitud:** obtener roles activos desde MariaDB; no confiar en roles guardados dentro del token. La revocación surte efecto en la petición siguiente.
- **RF-V3-04 — Regresión:** conservar los contratos de los 16 recursos de V1/V2, incluida la semántica maestro-detalle y las restricciones de verbos.
- **RF-V3-05 — Cuenta `consulta`:** puede ver catálogos y recursos de lectura; prohibido crear, editar y retirar. Si solicita escritura → 403.
- **RF-V3-06 — Cuenta `admin`:** acceso a las operaciones de cada recurso, sujeto a reglas de negocio y sus verbos existentes.
- **RF-V3-07 — Front:** sesión PHP, formulario de login, token CSRF para POST, JWT enviado a API por `cliente_api.php`, cierre de sesión; no mostrar credenciales en HTML o URL.
- **RF-V3-08 — Administración privada:** rutas de usuarios/roles reservadas a ADMIN incluso para GET.
- **RF-V3-09 — Consultar usuarios:** `GET /api/usuario` y `GET /api/usuario/{id}`; devolver `id`, `username`, `email`, `nombre_completo`, `activo`, `roles`, nunca `password_hash`.
- **RF-V3-10 — Crear usuario:** `POST /api/usuario`; campos `username`, `email`, `nombre_completo`, `password`; validación en servicio; hash; inserción de usuario y rol `consulta` en transacción; 201, 409 duplicado, 422 validación.
- **RF-V3-11 — Editar usuario [PENDIENTE]:** modificar campos permitidos sin sobrescribir contraseña si no se solicita explícitamente.
- **RF-V3-12 — Desactivar usuario [PENDIENTE]:** `activo=0`, sin borrado físico; cortar acceso a su siguiente petición.
- **RF-V3-13 — Roles [PENDIENTE]:** alta/retirada controlada de relaciones `rol_usuario`; impedir perder el último admin activo; evitar autorretiro inseguro.
- **RF-V3-14 — Vistas administrativas [PENDIENTE]:** listado, formularios, mensajes legibles, botones sujetos a rol, `htmlspecialchars` al renderizar, CSRF para mutaciones.
- **RF-V3-15 — Despliegue [EN PROCESO]:** PHP y MariaDB en Alwaysdata, configuración externa de secretos, URLs, reglas de enrutamiento y pruebas de login/rutas. No publicar copias `.sql`, `.env`, backups ni scripts de prueba.

## 5. Requisitos no funcionales

- **RNF-01:** PHP 8.3 puro, POO sin framework ni Composer; MariaDB; frontend PHP + Bootstrap descargado.
- **RNF-02:** capas: controlador HTTP, servicio negocio, repositorio SQL parametrizado; composición en `servicios/ensamblador.php`.
- **RNF-03:** contraseñas solo como hash `password_hash()`; `password_verify()` en login; nunca exponerse en JSON/logs.
- **RNF-04:** JWT firmado, vida corta (configuración actual: 3600 s); HTTPS obligatorio en producción; secreto largo y exclusivo.
- **RNF-05:** respuestas JSON coherentes; `401` para identidad no acreditada; `403` para permiso insuficiente; `422` validación; `409` conflictos; `500/503` problemas internos sin filtrar SQL.
- **RNF-06:** seguridad real en backend; la interfaz solo refleja permisos.
- **RNF-07:** `.env` fuera de Git, configuraciones distintas para desarrollo y producción, no subir respaldos ni dumps al directorio web.
- **RNF-08:** al menos dos roles y bloqueo de acciones administrativas para `consulta`.
- **RNF-09:** conservar los catálogos y las relaciones de V2 sin romper sus contratos.

## 6. Criterios de aceptación y estado a la fecha

| ID | Criterio verificable | Estado informado |
|---|---|---|
| A01 | Login de ADMIN emite JWT; front inicia sesión | ✅ Comprobado durante la construcción |
| A02 | `GET /api/area_conocimiento` sin token → 401 | ✅ Comprobado |
| A03 | `consulta` puede leer pero no escribir recurso académico (200/403) | ✅ Comprobado |
| A04 | `consulta` no puede abrir formulario de creación desde front (403) | ✅ Comprobado |
| A05 | ADMIN lista usuarios, CONSULTA → 403, anónimo → 401 | ✅ Comprobado por procedimiento de prueba |
| A06 | Usuario y correo tienen restricciones únicas en BD | ✅ Comprobación indicada como superada en paso 31; documentar evidencia |
| A07 | Repositorio, servicio, controlador y ruta de registro existen; POST sin JWT → 401 | ✅ Comprobado |
| A08 | Registro real: 201, rol consulta, login nuevo, 409 duplicado, 422 inválido, 403 consulta | ⏳ Prueba del paso 32 pendiente de confirmación |
| A09 | Editar/desactivar y proteger último ADMIN | ⏳ Pendiente |
| A10 | Vista de gestión de usuarios y roles completa | ⏳ Pendiente |
| A11 | Los 16 recursos y sus vistas pasan regresión V3 | ⏳ Revisión integral pendiente; catálogos con vistas reemplazadas sin validación integral |
| A12 | Alwaysdata despliega front y API con conexión, secretos y rutas seguras | ⏳ Pendiente; importación SQL presentó errores |

## 7. Definición de terminado

La V3 solo se declara **TERMINADA** cuando A01–A12 están en verde, el checklist ha sido validado, no quedan secretos expuestos, la regresión de V1/V2 pasa y se crea el commit/tag correspondiente. **No declarar este documento como cierre final hoy.**
