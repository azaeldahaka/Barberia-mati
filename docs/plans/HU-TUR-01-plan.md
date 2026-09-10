# HU-TUR-01 — Alta de turno por staff

Implementación de la funcionalidad para que el staff de la barbería pueda agendar turnos manualmente, verificando disponibilidad de horario y evitando superposiciones, reutilizando el catálogo de servicios/combos y el alta de clientes.

## Criterios de aceptación (de `product_backlog.md`)

1. El formulario permite buscar un cliente existente o cargar uno nuevo en el momento.
2. Solo se pueden seleccionar horarios libres según la duración del servicio elegido (ver HU-TUR-04).
3. El turno creado aparece inmediatamente en la agenda (HU-TUR-03).
4. No se permite crear un turno que se superponga con otro ya existente.

## Investigación del código existente

| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| `CreateClientAction` | `app/Actions/CreateClientAction.php` | Reutilizable para cargar un cliente nuevo desde el formulario si no se elige uno existente. |
| `CalculateTurnoEndTimeAction` | `app/Actions/Turnos/CalculateTurnoEndTimeAction.php` | Calcular la `fecha_hora_fin` al momento de guardar el turno. |
| `ValidateTurnoBusinessHoursAction` | `app/Actions/Turnos/ValidateTurnoBusinessHoursAction.php` | Validar que el turno entra en el horario de apertura y cierre de la barbería. |
| Modelos de Catálogo | `app/Models/ItemCatalogo.php`, `Service.php`, `Combo.php` | Para listar los servicios disponibles para el turno. |
| Modelos Base | `app/Models/Client.php`, `User.php`, `Barberia.php` | Relaciones para el modelo `Turno`. |

## Cambios Propuestos (Proposed Changes)

#### Backend — Migraciones/Modelos
- **[NEW]** `database/migrations/YYYY_MM_DD_HHMMSS_create_turnos_table.php`
  - Migración exacta según `glosario_datos.md`: `id` (uuid PK), `barberia_id` (foreignUuid), `cliente_id` (foreignId), `usuario_id` (foreignId), `item_catalogo_id` (foreignUuid), `fecha_hora_inicio` (dateTime), `fecha_hora_fin` (dateTime), `estado` (string con default 'reservado').
- **[NEW]** `app/Models/Turno.php`
  - Modelo con `HasUuids`, fillable, casts para las fechas (`datetime`), y las relaciones `barberia`, `cliente`, `usuario`, `itemCatalogo`.

#### Backend — Actions
- **[NEW]** `app/Actions/Turnos/CheckTurnoOverlapAction.php`
  - Recibe `barberia_id`, `fecha_hora_inicio`, `fecha_hora_fin`.
  - Retorna `bool` indicando si existe algún turno en esa barbería cuyo rango se superponga con el propuesto.

#### Backend — FormRequests/Controllers
- **[NEW]** `app/Http/Requests/StoreStaffTurnoRequest.php`
  - Valida `client_id` (opcional). Si es null, valida `first_name`, `last_name`, `phone` (requeridos).
  - Valida `item_catalogo_id` y `fecha_hora_inicio` (requeridos, fecha futura).
- **[NEW]** `app/Http/Controllers/TurnoController.php`
  - `create`: Retorna vista React con clientes e items del catálogo.
  - `store`: Llama a las actions para calcular fin, validar horario, verificar solapamiento, crear cliente (si no existe) y guardar el turno.
  - `index`: Retorna la vista (inicial) para la agenda para soportar HU-TUR-03.

#### Rutas
- **[MODIFY]** `routes/web.php`
  - Agregar rutas `resource` para turnos bajo el middleware `auth`.

#### Frontend — Páginas/Componentes React
- **[NEW]** `resources/js/Pages/Turnos/Create.jsx`
  - Formulario con selección de cliente existente o carga de uno nuevo.
  - Selección de ItemCatalogo y fecha/hora.
  - Envío a la ruta `turnos.store`.

#### Tests
- **[NEW]** `tests/Feature/Turno/CreateTurnoTest.php`
  - Tests validando que se pueda crear el turno.
  - Tests para la validación de superposición.
  - Tests para turno fuera de horario.

## Verificación de la Definition of Done (DoD)

| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Flujo completo y usable | Se puede registrar manualmente un turno desde el login de staff y validar superposiciones. |
| Reutilización de componentes | Se usarán Actions existentes y el modelo `ItemCatalogo`. |
| Estructura exacta | El modelo `Turno` se mapea 1:1 con `glosario_datos.md`. |

## Preguntas Abiertas (Open Questions)

- **Nota sobre el Glosario vs BD Actual:** El glosario especifica que `cliente_id` y `usuario_id` son UUIDs (FK), pero en la base de datos actual las tablas `clients` y `users` tienen PK numérico autoincremental (bigint). Implementaré las FKs usando el tipo correcto según la BD real (bigint) para evitar errores de restricción, manteniendo el resto de la tabla exactamente como el glosario pide. ¿Estás de acuerdo con proceder así?
