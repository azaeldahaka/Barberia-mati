# HU-SER-01 — ABM de servicios
Esta historia de usuario permite al dueño administrar el catálogo de servicios (alta, modificación y baja), definiendo el nombre, la duración y el precio de cada uno.

## Criterios de aceptación (de `Product_Backlog_Barberia_v1.md`)
1. Cada servicio tiene nombre, duración (minutos) y precio.
2. El precio es único por servicio (no varía por barbero).
3. No se puede eliminar un servicio que ya tiene turnos históricos asociados (se desactiva en su lugar).

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Autenticación | `routes/web.php` | Rutas protegidas bajo el middleware `auth` para asegurar que solo usuarios logueados accedan al ABM. |
| Componentes UI | `resources/js/Components/` | Reutilización de `Modal.jsx`, `TextInput.jsx`, `PrimaryButton.jsx`, `DangerButton.jsx` y `InputError.jsx` (estándar de Breeze) para las vistas. |
| Actions | `app/Actions/` | Patrón de diseño donde se encapsula la lógica de negocio (creación, edición y baja/desactivación de servicios) según la DoD. |

## Cambios Propuestos (Proposed Changes)

### Backend — Migraciones/Modelos
#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_create_services_table.php`
- Columnas: `id`, `name` (string, unique), `duration_minutes` (integer), `price` (decimal, 8, 2), `is_active` (boolean, default true), `timestamps`.
*(Nota: usamos `is_active` o SoftDeletes para cumplir con el criterio 3 "se desactiva en su lugar" sin perder el registro).*

#### [NEW] `app/Models/Service.php`
- Campos fillable: `name`, `duration_minutes`, `price`, `is_active`.
- Casts: `is_active` (boolean), `price` (decimal:2).

### Backend — Actions
#### [NEW] `app/Actions/Services/CreateServiceAction.php`
- Encapsula la creación del servicio.

#### [NEW] `app/Actions/Services/UpdateServiceAction.php`
- Encapsula la modificación del servicio.

#### [NEW] `app/Actions/Services/DeactivateServiceAction.php`
- Encapsula la lógica de desactivación/eliminación. Verifica si el servicio tiene turnos (a futuro) y decide si eliminar o desactivar (en v1, podemos simplemente desactivarlo, o usar un SoftDelete explícito).

### Backend — FormRequests/Controllers
#### [NEW] `app/Http/Requests/Services/StoreServiceRequest.php`
- Reglas: `name` (required, string, unique), `duration_minutes` (required, integer, min:1), `price` (required, numeric, min:0).

#### [NEW] `app/Http/Requests/Services/UpdateServiceRequest.php`
- Reglas: `name` (required, string, unique except current), `duration_minutes` (required, integer, min:1), `price` (required, numeric, min:0).

#### [NEW] `app/Http/Controllers/ServiceController.php`
- Métodos: `index` (devuelve `Inertia::render`), `store`, `update`, `destroy` (invocando los Actions).

### Rutas
#### [MODIFY] `routes/web.php`
- Añadir recurso de rutas para `services` bajo el middleware `auth`:
  `Route::resource('services', ServiceController::class)->except(['create', 'show', 'edit']);`

### Frontend — Páginas/Componentes React
#### [NEW] `resources/js/Pages/Services/Index.jsx`
- Vista principal que lista los servicios en una tabla.
- Incluye el layout principal (Authenticated).
- Botones para "Nuevo Servicio", "Editar" y "Eliminar".

#### [NEW] `resources/js/Pages/Services/Partials/ServiceFormModal.jsx`
- Componente Modal reutilizable para la creación y edición.
- Utiliza `useForm` de Inertia.js para el manejo del estado y errores.

#### [NEW] `resources/js/Pages/Services/Partials/DeleteServiceModal.jsx`
- Modal de confirmación para eliminar/desactivar un servicio.

### Tests
#### [NEW] `tests/Feature/ServiceTest.php`
- Testear acceso a la ruta (solo autenticados).
- Testear creación exitosa (valida redirección y base de datos).
- Testear validaciones (`name` único, `duration_minutes` > 0, `price` >= 0).
- Testear actualización de servicio.
- Testear eliminación/desactivación.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Criterios de aceptación verificados | Se comprobará manualmente la tabla, las validaciones de UI/backend, y la función de eliminar/desactivar. |
| Validaciones lado servidor | Los `FormRequest` garantizarán que no se creen servicios con nombres duplicados o duraciones/precios inválidos. |
| Writes encapsulados en Actions | El Controller delegará la persistencia a las clases dentro de `app/Actions/Services/`. |
| Integración a rama principal | Se realizará el Push a la rama `feature/HU-SER-01` dejando listo para el PR. |

## Preguntas Abiertas (Open Questions)
1. **Control de Versiones:** El intento de crear la rama `feature/HU-SER-01` falló. Me denegaste el permiso de ejecutar el comando `git checkout -b feature/HU-SER-01`. ¿Deseas que lo intente de nuevo, ya la creaste tú, o quieres que utilicemos una rama diferente?
2. **Desactivación vs Soft Deletes:** Para cumplir el criterio "se desactiva en su lugar", propongo usar un campo `is_active` (booleano) o la funcionalidad `SoftDeletes` de Laravel. ¿Qué opción prefieres?
