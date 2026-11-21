# 3_plan.md — Plan técnico, versión 3

> **CÓMO** implementar [2_spec.md](2_spec.md). Decisiones justificadas en [4_research.md](4_research.md). Estado: avance documentado; tareas pendientes en [8_tasks.md](8_tasks.md).

## 1. Stack y entorno

| Componente | Tecnología / decisión |
|---|---|
| API | PHP 8.3, POO, sin framework ni Composer; PDO, MariaDB |
| Frontend | PHP, Bootstrap local, plantillas en `front_php/vistas/` |
| Autenticación | JWT HS256 firmado, 3600 s; contraseña con `password_hash()` / `password_verify()` |
| Sesión del front | Cookie de sesión `HttpOnly`, `SameSite=Lax`, CSRF en formularios; JWT en sesión del servidor |
| Local | Windows PowerShell 5.1, Docker Compose |
| Servidor previsto | Alwaysdata; configuración y compatibilidad de rutas por confirmar |

**Configuración local utilizada en el desarrollo V3:** frontend `http://localhost:8124`, API `http://localhost:8126`, phpMyAdmin `http://localhost:8202`; servicio Docker de API `api-facturas`, servicio de frontend `front-php`. Los nombres y puertos pueden variar en otro checkout: comprobar `docker compose config`.

## 2. Arquitectura de ejecución

```text
Navegador
  │ GET/POST (sesión PHP, CSRF)
  ▼
front_php/index.php → front_php/seguridad_v3.php
  │                    └─ $_SESSION['jwt'] + rol de UI
  └─ front_php/cliente_api.php
           │ Authorization: Bearer <JWT>
           ▼
api_investigacion/index.php
  ├─ POST /api/login → ControladorAutenticacion → ServicioAutenticacion
  │                                               └─ RepositorioAutenticacionMariaDB
  └─ /api/* → GuardiaAccesoV3 (JWT + roles vigentes en BD)
                 │
                 ├─ Recursos académicos existentes de V1/V2
                 └─ ControladorUsuariosV3
                        └─ ServicioUsuariosV3 (IServicioUsuariosV3)
                               └─ RepositorioUsuariosMariaDBV3 (IRepositorioUsuariosV3)
                                      └─ MariaDB
```

## 3. Archivos principales

```text
Version 2/
├── docker-compose.yml
├── .env                              # LOCAL; no publicar
├── api_investigacion/
│   ├── index.php                     # router y guardia
│   ├── controladores/
│   │   ├── ControladorAutenticacion.php
│   │   ├── GuardiaAccesoV3.php
│   │   └── ControladorUsuariosV3.php
│   ├── servicios/
│   │   ├── JwtHs256.php
│   │   ├── IServicioAutenticacion.php
│   │   ├── ServicioAutenticacion.php
│   │   ├── IServicioUsuariosV3.php
│   │   ├── ServicioUsuariosV3.php
│   │   └── ensamblador.php
│   └── repositorios/
│       ├── IRepositorioAutenticacion.php
│       ├── RepositorioAutenticacionMariaDB.php
│       ├── IRepositorioUsuariosV3.php
│       └── RepositorioUsuariosMariaDBV3.php
├── front_php/
│   ├── index.php                     # rutas de recursos y login
│   ├── cliente_api.php               # propaga Bearer
│   ├── seguridad_v3.php              # sesión y roles visuales
│   ├── vistas/
│   │   ├── login_v3.php
│   │   ├── area_conocimiento_lista.php
│   │   └── ...                       # vistas de los demás recursos
│   └── publico/                      # Bootstrap, CSS, JS locales
└── spec_kit_v3/                      # los ocho documentos
```

Los nombres `Version 2/` y `api-facturas` provienen de la organización local; **no implican que el sistema sea únicamente V2**. `ensamblador.php` concentra la construcción de implementaciones concretas.

## 4. Flujos

### 4.1 Login

1. Front recibe credenciales por POST con CSRF.
2. Front llama a `POST /api/login` con JSON.
3. Controlador valida forma; servicio busca usuario activo; `password_verify` comprueba contraseña.
4. Repositorio obtiene roles actualmente activos; sin rol activo se rechaza.
5. `JwtHs256::emitir($usuarioId)` emite token firmado con `sub`, `iat`, `exp`; no se incluyen roles.
6. Front regenera ID de sesión, almacena token, hora de expiración y datos mínimos de usuario.
7. Cliente API envía `Authorization: Bearer ...` en cada solicitud posterior.

### 4.2 Recurso académico

1. `index.php` deja pasar `/api/login` y preflight `OPTIONS` según configuración.
2. En las demás rutas `/api/*`, `GuardiaAccesoV3` valida el JWT y consulta `usuario`, `rol_usuario`, `rol` para los roles activos.
3. Usuario anónimo/inactivo/token vencido → 401; rol `consulta` con mutación → 403.
4. Si se autoriza, se ejecuta la ruta original V1/V2 y se mantienen sus reglas (por ejemplo semillero de solo lectura).

### 4.3 Administración de usuarios

- `GET /api/usuario` → `ControladorUsuariosV3::procesarPeticion()` → servicio `listar()` → repositorio `listar()`; sin `password_hash` en `SELECT`.
- `GET /api/usuario/{id}` → validación de ID > 0, luego servicio y repositorio.
- `POST /api/usuario` → JSON de cuatro campos, servicio `crear()` → validaciones → `password_hash()` → repositorio `crearConRolConsulta()` en transacción; inserta usuario y asignación.
- `PUT/PATCH/DELETE` de usuarios, endpoints de roles y vistas administrativas: **por implementar**, con protección de último administrador.

## 5. Configuración e infraestructura

| Variable API | Finalidad |
|---|---|
| `DB_HOST` | Servidor MariaDB, local `mariadb` |
| `DB_NAME` | Base local `investigacion`; en Alwaysdata usar el nombre asignado |
| `DB_USER` | Usuario de BD de mínimo privilegio |
| `DB_PASS` | Contraseña de BD, solo entorno |
| `JWT_SECRETO` | Clave HMAC fuerte y distinta entre desarrollo y producción |

Se identificaron fallbacks de compatibilidad `DB_DSN_MARIADB`, `DB_USUARIO` y `DB_CLAVE` en la fábrica de usuarios V3. **Unificar antes de producción** para evitar configuraciones incongruentes. Front recibe `URL_API` apuntando al servicio interno local; en producción ajustar a la URL real y comprobar HTTPS y conectividad.

**No publicar** `.env`, archivos `*.bak*`, dumps `.sql`, repositorios Git privados de credenciales, scripts de prueba, o logs con tokens. `docker compose config` puede imprimir secretos: no publicar su salida sin censurar.

## 6. Orden de implementación

1. Mantener y verificar V1/V2 y la base 19 tablas.
2. Auth: clases `JwtHs256`, repo/servicio/controlador de login, `/api/login`.
3. Guardia de API: autenticación + autorización desde MariaDB en cada solicitud.
4. Front: login, sesión, cliente Bearer, logout, CSRF y bloqueo de formularios para `consulta`.
5. Consulta administrativa: repo + servicio + controlador y ensamblador.
6. Registro de usuario por `admin`: unicidad, hash, transacción, rol predeterminado.
7. **Pendiente:** probar POST con cuenta real, 201/409/422/403 sin imprimir secretos.
8. **Pendiente:** editar/desactivar cuentas; asignar/quitar rol; bloqueo del último admin.
9. **Pendiente:** vistas administrativas Bootstrap y botones/acciones de los catálogos.
10. **Pendiente:** pruebas integrales, seguridad y despliegue Alwaysdata.

## 7. Estrategia de despliegue a Alwaysdata (no completada)

1. Exportar SQL y verificar integridad; importar desde una base **seleccionada** en phpMyAdmin, sin aplicar dos veces el mismo dump.
2. Revisar `CREATE DEFINER=... PROCEDURE` de V2: el usuario definidor de Docker puede no existir en Alwaysdata. Corregir dump conservando la lógica del procedimiento y usando permisos apropiados.
3. Decidir configuración de sitios: raíz de front y raíz de API o enrutamiento por subrutas; comprobar soporte real de `.htaccess` en la modalidad de alojamiento.
4. Transferir solo código necesario, directorio privado para variables sensibles, sin `.env` público, dumps ni backups.
5. Configurar hostname/usuario/BD, `JWT_SECRETO` nuevo y URLs HTTPS.
6. Probar login ADMIN, solicitud 401 sin token, consulta 403 en admin, catálogos y procedimientos de V2.
7. Recién entonces considerar el sitio publicable; el estado actual **no lo acredita**.

## 8. Riesgos de integración

| Riesgo | Acción preventiva |
|---|---|
| `php -r` falla por comillas PowerShell 5.1 | Ejecutar scripts PHP por `STDIN` o archivo temporal, no depender de escapes frágiles |
| Un archivo existe con 0 bytes | Comprobar tamaño, no dar `php -l` como única prueba |
| Token válido pero rol revocado | Leer roles en BD en cada petición |
| UI oculta botones pero deja POST accesible | Bloqueo en API; el de front es adicional |
| Vistas de catálogos muestran «Sección en preparación» | Sustituir vistas, probar datos y permisos por recurso |
| Import SQL #1046 | Seleccionar BD antes de importar |
| `DEFINER` / procedimientos no importables | Adaptar dump y verificar `CALL` con usuario de Alwaysdata |
| Secretos en alojamiento público | Configuración externa, HTTPS, ignorar backups y `.sql` |
