# 9_Checklist.md — Checklist de cierre, versión 3

**Proyecto:** Sistema de Investigación · **Fecha:** 21/11/2026  
**Responsable de validación:** ______________________  
**Commit probado:** ______________________  
**Estado global:** **ABIERTA — V3 no terminada; publicación en Alwaysdata no verificada**.

> Una casilla marcada refleja una comprobación descrita durante el desarrollo, no una auditoría externa. Para cerrar V3 se requieren las pruebas pendientes.

## A. Metodología y arquitectura

- [x] PHP puro POO, sin framework/Composer, MariaDB y Bootstrap.
- [x] Repositorios y servicios V3 con interfaces e inyección.
- [x] Creación de clases concretas de V3 concentrada en `ensamblador.php`.
- [x] Consultas SQL del módulo de usuarios aisladas en repositorio.
- [ ] Revisión de dependencias/capas y pruebas unitarias completa.
- [ ] Guardar evidencia de compilación/sintaxis de todos los archivos después de los últimos cambios.

## B. Autenticación

- [x] `POST /api/login` implementado y login ADMIN probado.
- [x] Contraseña validada con `password_verify()`.
- [x] JWT HS256 firmado con expiración.
- [x] Roles activos consultados en BD en cada petición.
- [x] `GET /api/area_conocimiento` sin token → HTTP 401.
- [ ] Comprobar token alterado y token expirado con pruebas reproducibles.
- [ ] Comprobar revocación de roles y desactivación de cuenta contra token previo.
- [ ] Revisar seguridad de despliegue HTTPS, cookies y rate limiting.

## C. Autorización

- [x] ADMIN accede a recursos académicos según contrato heredado.
- [x] CONSULTA lee área de conocimiento, pero no puede escribir (200/403).
- [x] CONSULTA no abre `/area_conocimiento/nuevo` en el front (403).
- [x] `GET /api/usuario` sin token → 401; CONSULTA → 403; ADMIN → 200.
- [x] Se comprueba que `GET /api/usuario` no expone `password_hash`.
- [ ] Repasar 16 recursos y todas las vistas con ADMIN y CONSULTA.

## D. Gestión de usuarios

- [x] `usuario`, `rol`, `rol_usuario` disponibles en esquema de desarrollo.
- [x] Listado/consulta por ID de usuarios implementado.
- [x] Servicio de registro con validación, `password_hash`, repositorio simulado.
- [x] Repositorio de creación usa transacción para usuario+rol `consulta`.
- [x] Endpoint POST protegido; sin JWT → 401.
- [ ] Confirmar **prueba real de registro**: 201, login, 409, 422, 403 (paso 32).
- [ ] Editar usuario con validaciones sin alterar accidentalmente contraseña.
- [ ] Desactivación lógica y bloqueo de login de inactivos.
- [ ] Gestión de roles ADMIN con protección transaccional del último administrador activo.
- [ ] Pantallas Bootstrap de gestión de usuarios/roles.

## E. Continuidad de V1/V2

- [ ] Los seis catálogos listan y modifican conforme al rol y al contrato.
- [ ] Cinco vistas anteriormente «en preparación» muestran datos y formularios tras sustitución.
- [ ] Maestro-detalle `grupo_investigacion → semillero` y procedimientos pasan pruebas.
- [ ] Siete tablas puente, docente y semillero de solo lectura pasan regresión.
- [ ] API caída muestra aviso amigable sin exponer fallos internos.

## F. Seguridad y Alwaysdata

- [ ] Confirmar índices UNIQUE, FKs y permisos en base ya importada.
- [ ] Importar BD en Alwaysdata tras seleccionar destino y resolver definidores de procedimientos.
- [ ] No existe dump parcial duplicado ni información sensible en directorios públicos.
- [ ] `.env`/secretos privados, `JWT_SECRETO` rotado para producción, credenciales distintas de desarrollo.
- [ ] Identificar `.htaccess` existentes y comprobar rewrite en Alwaysdata.
- [ ] Front y API accesibles por HTTPS y con URLs configuradas correctamente.
- [ ] Validar 200/401/403 y operaciones autorizadas desde el dominio real.
- [ ] Proteger directorios de API, respaldos, logs, variables y SQL de acceso HTTP.

## G. Evidencias mínimas para firma

| Evidencia | Esperado | Estado |
|---|---|---|
| `docker compose ps` | API, front, MariaDB arriba | Por adjuntar |
| Lint y carga de clases V3 | Sin error y archivos no vacíos | Observado parcialmente; adjuntar |
| Login ADMIN | HTTP 200 + JWT válido | Observado |
| API sin JWT | HTTP 401 | Observado |
| CONSULTA escritura académica | HTTP 403 | Observado |
| ADMIN lectura de usuarios | HTTP 200 sin hash | Observado |
| Registro integral | 201 + 409 + 422 + 403 | Pendiente de confirmar |
| Último ADMIN protegido | No se puede desactivar/revocar | Pendiente |
| 16 recursos en ambos roles | Regresión completa | Pendiente |
| Alwaysdata | SQL importado, front/API HTTPS, login y permisos | Pendiente |

## H. Dictamen

**NO APROBADO PARA CIERRE V3** por tareas funcionales y pruebas pendientes. La versión puede mantenerse como **avance de implementación** en GitHub y usarse en un entorno privado de pruebas, pero no se debe afirmar que está completa ni que fue desplegada sin verificaciones.

**Firmas cuando se complete:**

- Desarrollo: ______________________ / Fecha: __________
- Validación: _______________________ / Fecha: __________
- Commit final / tag `v3`: _____________________________
