# HU-TUR-02 — Reserva de turno por el cliente (autogestión)
Esta historia permite a los clientes reservar sus propios turnos desde una página pública, validando disponibilidad y redirigiendo al registro (HU-CLI-01) si no tienen cuenta, para reducir la carga administrativa del staff.

## Criterios de aceptación (de `product_backlog.md`)
1. La página pública muestra únicamente los horarios disponibles, ya bloqueados según turnos existentes.
2. El cliente elige un servicio y ve la duración antes de confirmar.
3. Si el cliente no tiene cuenta, se lo dirige al flujo de autoregistro (HU-CLI-01) antes de confirmar.
4. Al confirmar, el turno queda visible en la agenda del staff en tiempo real.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelos | `app/Models/Turno.php`, `ItemCatalogo.php` | Persistencia y relaciones (duración, etc). |
| Actions (Validación) | `app/Actions/Turnos/CheckTurnoOverlapAction.php`, `ValidateTurnoBusinessHoursAction.php` | Lógica de validación de horarios y superposición a reutilizar. |
| Actions (Cálculo) | `app/Actions/Turnos/CalculateTurnoEndTimeAction.php` | Cálculo de fecha de fin en base al servicio. |
| Controlador Auth | `app/Http/Controllers/ClientRegistrationController.php` | Manejo de sesión `client_id` (HU-CLI-01) para identificar clientes. |

## Cambios Propuestos (Proposed Changes)

#### [NEW] `app/Http/Requests/StorePublicTurnoRequest.php`
- Request de validación para la creación pública de turnos. Valida `item_catalogo_id` y `fecha_hora_inicio`.

#### [NEW] `app/Http/Controllers/PublicTurnoController.php`
- `create(Request $request)`: Renderiza `Turnos/PublicCreate`. Acepta opcionalmente `date` (fecha seleccionada) y envía los `itemCatalogos` y los turnos/horarios ocupados de ese día para que el frontend pueda calcular y mostrar los slots disponibles (o los slots ya procesados desde el backend). Envía variable `has_client` (booleano según si existe `client_id` en la sesión).
- `store(StorePublicTurnoRequest $request)`:
  - Si `!session()->has('client_id')`, guarda la selección en sesión (`pending_turno`) y redirige a `client.register`.
  - Si existe cliente: recupera el usuario por defecto de la barbería para asignar a `usuario_id`, re-valida reglas de negocio (overlap, business hours, cálculo de fin), crea el turno, limpia `pending_turno` y redirige a una página de éxito (o a la raíz con mensaje).

#### [MODIFY] `app/Http/Controllers/ClientRegistrationController.php`
- En el método `store`, tras registrar y setear `client_id` en sesión, verificar si existe `pending_turno` en sesión. Si es así, redirigir de vuelta a `public.turno.create` para que el cliente pueda confirmar (con sus datos precargados).

#### [MODIFY] `routes/web.php`
- Agregar `Route::get('/reservar', [PublicTurnoController::class, 'create'])->name('public.turno.create');`
- Agregar `Route::post('/reservar', [PublicTurnoController::class, 'store'])->name('public.turno.store');`

#### [NEW] `resources/js/Pages/Turnos/PublicCreate.jsx`
- Página pública para clientes.
- Flujo visual: Seleccionar Servicio (muestra duración/precio) -> Seleccionar Fecha -> Seleccionar Horario (solo horas disponibles).
- Si `!has_client`, el botón dice "Ingresar/Registrar para Confirmar" y hace submit al backend, el cual redirigirá al registro. Si el usuario ya está registrado/en sesión, dice "Confirmar Reserva".

#### [MODIFY] `resources/js/Pages/Welcome.jsx`
- Agregar un botón "Reservar Turno" visible que lleve a `route('public.turno.create')` para cumplir con la Definition of Done de navegabilidad.

#### [NEW] `tests/Feature/PublicTurnoTest.php`
- Pruebas para: Reserva válida por cliente, Intento fuera de horario, Intento superpuesto, Cliente sin sesión redirigido al autoregistro.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Tests unitarios/feature | Se agregarán pruebas simulando solicitudes HTTP a `PublicTurnoController`. |
| QA tools (test, pint, build) | Se ejecutarán `php artisan test`, `vendor/bin/pint` y `npm run build`. |
| DoD Navegabilidad | Se agregará un enlace visible en `Welcome.jsx` que lleve a `/reservar`. |

## Preguntas Abiertas (Open Questions)
- El campo `usuario_id` en Turnos no es nulo según la migración actual. Se asume que le asignaremos el único usuario existente del sistema (Matías) como `usuario_id` en las reservas autogestionadas. ¿Es correcto este abordaje para v1?
