# HU-TUR-08 — Horario de atención fijo del sistema
Esta HU asegura que los turnos agendados respeten el horario de atención de la barbería, validando que un turno no comience antes de la apertura ni termine después del cierre.

## Criterios de aceptación (de product-backlog.md)
1. El sistema no permite crear turnos antes del horario de apertura ni después del horario de cierre (se tomará dinámicamente de horario_apertura y horario_cierre de la entidad Barberia, sin hardcodear).
2. El horario está fijo en la configuración del sistema para esta versión (no editable desde una pantalla).
3. Se contempla la atención por orden de llegada como una franja sin turno pre-asignado, pero dentro del mismo horario general (la validación aplica igual).
4. Si el turno cae exactamente en el límite (ej. fecha_hora_fin igual a horario_cierre en punto), debe permitirse — el corte es estricto solo para lo que excede el horario.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelo Barberia | pp/Models/Barberia.php | Entidad que tiene los campos horario_apertura y horario_cierre. |
| Migración Barberia | database/migrations/2026_09_09_161817_create_barberias_table.php | Los campos horario_apertura y horario_cierre ya existen en la base de datos con defaults '12:00' y '22:00'. |
| Action CalculateTurnoEndTimeAction | pp/Actions/Turnos/CalculateTurnoEndTimeAction.php | El cálculo de fecha de fin ya existe; la nueva validación se usará en conjunto con esta pieza. |
| Modelo Turno | N/A | Aún no existe (comprobado), por lo que se construirá una Action pura y reutilizable. |

## Cambios Propuestos (Proposed Changes)

#### [NEW] app/Actions/Turnos/ValidateTurnoBusinessHoursAction.php
Crearemos una Action que valida si las horas propuestas caen dentro del horario de la barbería.
- Recibirá: Barberia , Carbon , Carbon .
- Retornará: ool (	rue si es válido, alse si excede los límites).
- Lógica:
  - Tomará el string horario_apertura y horario_cierre de la Barberia (ej. "12:00").
  - Validará que $fechaHoraInicio sea mayor o igual a la fecha instanciada con el horario_apertura (en el mismo día que $fechaHoraInicio).
  - Validará que $fechaHoraFin sea menor o igual a la fecha instanciada con el horario_cierre (en el mismo día que $fechaHoraInicio).

#### [NEW] tests/Unit/Actions/ValidateTurnoBusinessHoursActionTest.php
- Prueba unitaria para turno válido dentro de horario (OK).
- Prueba unitaria para turno que empieza antes de la apertura (Rechazado).
- Prueba unitaria para turno que termina después del cierre (Rechazado).
- Prueba unitaria de caso de borde: inicio exacto en apertura (OK).
- Prueba unitaria de caso de borde: fin exacto en cierre (OK).

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Tests unitarios | Se crearán tests exhaustivos para los casos de éxito, rechazo y borde de la Action. |
| Reutilización de código | El cálculo quedará encapsulado en una Action limpia e independiente lista para ser usada por HU-TUR-01/02. |
| Formateo de código | Se ejecutará endor/bin/pint. |

## Preguntas Abiertas (Open Questions)
- ¿Prefieres que la Action retorne un booleano (	rue/alse) o que lance una excepción (ej. ValidationException) en caso de que el turno esté fuera de horario? (En el plan asumo retornar un booleano para mayor flexibilidad al momento de validar requests en HU-TUR-01).