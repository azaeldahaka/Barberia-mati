# HU-TUR-04 — Duración configurable por servicio
Esta historia establece la lógica base para calcular cuánto tiempo ocupará un servicio o combo en la agenda, permitiendo proyectar la hora de fin de un turno a partir de su hora de inicio.

## Criterios de aceptación (de `product-backlog.md`)
1. Cada servicio del catálogo (HU-SER-01) tiene un campo de duración en minutos.
2. Al agendar un turno, el sistema reserva el bloque exacto según la duración del servicio elegido.
3. Si se elige un combo, la duración es la suma (o un valor propio) definida para ese combo.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelo `ItemCatalogo` | `app/Models/ItemCatalogo.php` | Ya contiene el campo `duracion_minutos` (gracias a la refactorización de HU-SER-01) y agrupa tanto a Servicios como a Combos. |
| Action Pattern | `app/Actions/` | El proyecto utiliza Actions para encapsular la lógica de negocio en lugar de cargar los controladores o modelos. |

## Cambios Propuestos (Proposed Changes)

#### [NEW] app/Actions/Turnos/CalculateTurnoEndTimeAction.php
Crearemos una Action dedicada pura de lógica de negocio (aislada, como se solicitó para consumo futuro de HU-TUR-01/02).
- Recibirá un `ItemCatalogo` (servicio o combo) y un objeto fecha-hora de inicio (`Carbon`).
- Retornará un nuevo `Carbon` correspondiente a la fecha-hora de fin, sumando el campo `duracion_minutos` del catálogo a la fecha de inicio.
- Esto cubre tanto servicios individuales como combos porque, por diseño, la duración de ambos vive en `ItemCatalogo`.

#### [NEW] tests/Unit/Actions/CalculateTurnoEndTimeActionTest.php
- Probaremos que, dado un `ItemCatalogo` con duración de 30 minutos y una fecha `2026-09-10 14:00`, la acción retorna exactamente `2026-09-10 14:30`.
- Probaremos también con un Combo (simulando un `ItemCatalogo` de tipo `combo` con 45 minutos) para verificar explícitamente el criterio de aceptación #3.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Tests unitarios | Se creará un test unitario específico para el Action. |
| Reutilización de código | El cálculo quedará encapsulado en una Action limpia e independiente. |
| Formateo de código | Se ejecutará `vendor/bin/pint`. |

## Preguntas Abiertas (Open Questions)
- Como el cálculo es muy directo (sumar los minutos de duración del catálogo a una fecha de inicio) he optado por encapsularlo en una Action (`CalculateTurnoEndTimeAction`) para mantenerlo listo para ser inyectado en HU-TUR-01. ¿Estás de acuerdo con este enfoque o preferís que el cálculo resida como un método dentro del propio modelo `ItemCatalogo`?
