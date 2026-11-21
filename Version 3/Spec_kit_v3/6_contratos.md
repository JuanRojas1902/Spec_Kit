# 6_contratos.md — Contratos HTTP y pantallas, versión 3

**API local:** `http://localhost:8126` · **Front local:** `http://localhost:8124`  
**Formato:** JSON UTF-8, salvo páginas HTML de frontend. Base en producción pendiente de configurar.

## 1. Autenticación

### `POST /api/login` — público

Solicitud:

```json
{"username":"admin","password":"<contraseña entregada por el usuario>"}
```

Éxito HTTP 200 (forma prevista por implementación):

```json
{
  "estado": 200,
  "mensaje": "Inicio de sesión correcto.",
  "datos": {
    "token": "<jwt-firmado>",
    "expira_en": 3600,
    "usuario": {
      "id": 1,
      "username": "admin",
      "email": "ejemplo@example.test",
      "nombre_completo": "Administrador",
      "roles": ["admin"]
    }
  }
}
```

La frase de `mensaje` puede variar. Errores: `401` credenciales incorrectas, cuenta inactiva o sin rol activo; `422` cuerpo inválido; errores internos no exponen hashes ni SQL. Nunca registrar/compartir tokens reales.

### Token Bearer

Para todo `/api/*` protegido:

```http
Authorization: Bearer <jwt>
```

JWT HS256 contiene ID (`sub`) y fechas (`iat`, `exp`). Los **roles activos se consultan en BD por petición**. Un token válido sin autorización suficiente devuelve 403; sin token, vencido o con cuenta inactiva, 401.

## 2. Matriz API

| Ruta | Método | Anónimo | `consulta` | `admin` | Estado V3 |
|---|---|---|---|---|---|
| `/api/login` | POST | Permitido | Permitido | Permitido | Implementado |
| `/api/area_conocimiento` y otros 15 recursos | GET/HEAD admitidos | 401 | Permitido | Permitido | Guardia implementada |
| Rutas de escritura académica admitidas en V1/V2 | POST/PUT/PATCH/DELETE | 401 | 403 | Según contrato V1/V2 | Guardia implementada |
| `/api/usuario` | GET | 401 | 403 | 200 | Listado probado |
| `/api/usuario/{id}` | GET | 401 | 403 | 200/404/422 | Consulta individual implementada |
| `/api/usuario` | POST | 401 | 403 | 201/409/422 | Implementado; prueba integral de 201 pendiente de confirmación |
| Edición/desactivación de usuario | por definir | 401 | 403 | Pendiente | **No habilitado** |
| Endpoints de roles y asignación | por definir | 401 | 403 | Pendiente | **No habilitado** |

**Regla:** el rol ADMIN no vuelve escribible un recurso que V2 definió de solo lectura (por ejemplo `semillero`).

## 3. `GET /api/usuario`

Exige `Authorization: Bearer ...` de ADMIN. Éxito 200:

```json
{
  "tabla": "usuario",
  "total": 2,
  "datos": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@example.test",
      "nombre_completo": "Administrador",
      "activo": 1,
      "roles": ["admin"]
    },
    {
      "id": 2,
      "username": "consulta_ejemplo",
      "email": "consulta@example.test",
      "nombre_completo": "Usuario de consulta",
      "activo": 1,
      "roles": ["consulta"]
    }
  ]
}
```

Los valores anteriores son ficticios. El hash y la contraseña **no deben aparecer nunca**. La respuesta de lista vacía usa `datos: []` y `total: 0` según este controlador.

## 4. `GET /api/usuario/{id}`

- ID positivo válido → HTTP 200, `{"tabla":"usuario","datos":{...}}`.
- ID inexistente → HTTP 404 con `estado`/`mensaje`.
- ID inválido → HTTP 422 con `errores`.
- CONSULTA → 403; anónimo → 401.

## 5. `POST /api/usuario` — exclusivo ADMIN

Solicitud válida de ejemplo:

```json
{
  "username": "usuario_prueba",
  "email": "usuario_prueba@example.test",
  "nombre_completo": "Usuario Prueba",
  "password": "<contraseña fuerte, no una contraseña de ejemplo literal>"
}
```

**No se aceptan** campos extras como `rol`, `activo`, `id` o `password_hash`. El usuario nuevo se registra activo y con rol `consulta`.

Éxito esperado HTTP 201:

```json
{
  "estado": 201,
  "mensaje": "Usuario registrado correctamente.",
  "datos": {"id": 3, "rol": "consulta"}
}
```

Errores:

| HTTP | Cuándo |
|---|---|
| 401 | JWT ausente/vencido, usuario inactivo |
| 403 | rol `consulta` |
| 415 | Content-Type no JSON |
| 413 | body mayor de 16 KiB |
| 422 | username/email/nombre/password inválidos, JSON inválido o campo extra |
| 409 | username o email ya registrado (incluye índice UNIQUE 1062) |
| 500/503 | fallo inesperado, sin exponer detalles internos |

Validaciones actuales en servicio: `username` `[A-Za-z0-9._-]` entre 3–50; correo validado y hasta 150 bytes; nombre entre 2–120 bytes; contraseña entre 12–72 bytes; `password_hash(PASSWORD_DEFAULT)`.

## 6. Pantallas de frontend

| Ruta front | Vista / acción | Estado |
|---|---|---|
| `/login` | Formulario usuario y contraseña + CSRF | Implementada |
| `/logout` POST | Destruir sesión y redirigir a login | Implementada |
| `/area_conocimiento` | Lista; ADMIN acciones; CONSULTA solo lee | Probada durante desarrollo |
| `/area_conocimiento/nuevo` | Formulario solo ADMIN | CONSULTA recibió 403 en prueba |
| `/objetivo_desarrollo_sostenible`, `/area_aplicacion`, `/termino_clave`, `/universidad`, `/linea_investigacion` | Catálogos V1 con vistas reemplazadas | Copia reportada; sin prueba integral posterior |
| `/usuarios` o ruta administrativa equivalente | Listado/edición/roles | **No definida ni habilitada aún** |

La API es autoridad final: ocultar botones es solo una ayuda visual. Formularios usan CSRF y salida escapada con `htmlspecialchars`.

## 7. Contratos heredados V1/V2

Los contratos académicos **no se vuelven a definir en V3**; se consultan en el `6_contratos.md` de V2. V3 añade `401/403` antes de la lógica propia de cada recurso. Los códigos `204`, `405`, `409` y los procedimientos almacenados del grupo se mantienen como en V2.

## 8. Contratos futuros — no exponer todavía

Para edición/desactivación y roles es necesario acordar rutas, cuerpos y reglas de idempotencia antes de publicarlos. Propuesta a especificar: actualización de campos permitidos, `activo=0` para desactivar, y altas/bajas de `rol_usuario` con transacción y protección del último ADMIN. **No afirmar que esas rutas ya funcionan.**
