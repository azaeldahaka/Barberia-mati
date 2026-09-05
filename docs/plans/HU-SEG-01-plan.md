# HU-SEG-01 — Autenticación del Personal
Implementación del sistema de autenticación utilizando Laravel Breeze (React/Inertia) para proveer un punto de acceso seguro, incluyendo el soporte para login tradicional y la infraestructura para OAuth (Google).

## Criterios de aceptación (de `product-backlog.md`)
1. **Datos:** Email y contraseña válidos o token de Google.
2. **Validaciones:** Credenciales correctas; usuario debe existir en la base de datos.
3. **Comportamiento:** Redirección al panel principal tras login exitoso. Soporte para OAuth de Google.
4. **Verificación:** Intentar login con credenciales inválidas arroja error. Login exitoso crea sesión.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelos de Rol y Usuario | `app/Models/Role.php`, `app/Models/User.php` | Verificar el rol del usuario durante/después del login (RBAC implementado en HU-SEG-02). |
| Asignación de Roles | `app/Actions/Roles/AssignDefaultRoleToUser.php` | Asignar rol por defecto cuando se cree un usuario (ej. via registro o Google OAuth). |

## Cambios Propuestos (Proposed Changes)

### Backend — Dependencias
#### [NEW] `composer.json`
- Instalar `laravel/breeze` y `laravel/socialite` (para Google OAuth).
- Ejecutar `php artisan breeze:install react`.

### Backend — Migraciones/Modelos
#### [MODIFY] `app/Models/User.php`
- Asegurar que esté listo para Laravel Socialite.
#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_add_google_id_to_users_table.php`
- Agregar la columna `google_id` (string, nullable) a la tabla `users`.

### Backend — Actions
#### [NEW] `app/Actions/Auth/LoginUserWithGoogle.php`
- Acción encargada de recibir la data de Socialite, buscar o crear al usuario, usar `AssignDefaultRoleToUser` si es nuevo, y loguearlo.

### Backend — FormRequests/Controllers
#### [NEW] `app/Http/Controllers/Auth/GoogleLoginController.php`
- Endpoints para redirigir a Google y manejar el callback.

### Rutas
#### [MODIFY] `routes/web.php`
- Integrar las rutas de Breeze autogeneradas.
- Agregar rutas para `/auth/google/redirect` y `/auth/google/callback`.

### Frontend — Páginas/Componentes React
#### [MODIFY] `resources/js/Pages/Auth/Login.jsx`
- Agregar el botón de "Iniciar sesión con Google".

### Tests
#### [NEW] `tests/Feature/Auth/GoogleLoginTest.php`
- Validar redirección y autenticación con cuenta de Google mockeada.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Criterios de aceptación verificados | Se probará el login con credenciales válidas/inválidas y el flujo de Google OAuth. |
| Validaciones aplicadas en servidor | Breeze ya provee el FormRequest de login con validación en servidor. |
| Write encapsulado en Action | El registro a través de Google utilizará una Action propia. |
| Código integrado a master | Se proporcionará el mensaje de commit y PR al final. |

## Preguntas Abiertas (Open Questions)
1. **OAuth Google:** ¿Configuramos Google Socialite ahora mismo (requiere agregar las credenciales Client ID y Secret en tu `.env`) o dejamos la estructura y el botón preparados pero desactivados temporalmente?
2. **Registro de usuarios:** Breeze incluye una ruta pública de registro (`/register`). Al ser un sistema para personal de barbería, ¿querés que desactivemos el registro libre (solo un admin puede crear usuarios) o lo dejamos abierto asignando el rol de Dueño/Staff por defecto según HU-SEG-02?
