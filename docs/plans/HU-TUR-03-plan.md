# HU-TUR-03 — Vista de agenda diaria/semanal
Vista de calendario para la recepción de turnos, mostrando los turnos como bloques de tiempo exclusivos en un formato de grilla diaria o semanal.

## Criterios de aceptación (de `product_backlog.md`)
1. La vista muestra los turnos como bloques de tiempo exclusivos (sin superposición).
2. Cada bloque muestra al menos: nombre del cliente y servicio.
3. Se puede alternar entre vista diaria y semanal.
4. Los turnos marcados como "ausente" se distinguen visualmente de los confirmados.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelo `Turno` | `app/Models/Turno.php` | Tiene la propiedad `serializeDate()` lo que asegura que las fechas vienen como 'Y-m-d H:i:s' en hora local (introducida en HU-TUR-02, ya integrada). |
| Controller | `app/Http/Controllers/TurnoController.php` | El método `index()` actualmente carga los turnos. Se optimizará para filtrar por las fechas de la vista actual (diaria o semanal). |
| Vista Index | `resources/js/Pages/Turnos/Index.jsx` | Actualmente es un listado básico, se convertirá en la agenda tipo calendario requerida conservando el enlace de acceso ya configurado. |

## Cambios Propuestos (Proposed Changes)

#### [MODIFY] `app/Http/Controllers/TurnoController.php`
- Modificar el método `index` para que reciba parámetros opcionales `start` y `end` desde el request. Esto permitirá al backend entregar únicamente los turnos del día o semana que se está visualizando en lugar de todos los históricos. 

#### [MODIFY] `resources/js/Pages/Turnos/Index.jsx`
- Reemplazar la tabla actual por un layout de calendario diseñado a medida (sin dependencias pesadas como FullCalendar, usando tailwind y cálculos simples).
- Crear estados locales: `viewType` ('day' | 'week'), `currentDate` (Date base de navegación).
- Incorporar botones para avanzar/retroceder en el tiempo.
- Renderizar la grilla de horas operativas (12:00 a 22:00) y posicionar cada turno calculando el desplazamiento vertical proporcional a su `fecha_hora_inicio` y la altura en base a su duración hasta `fecha_hora_fin`.
- Aplicar un estilo diferencial (ej. borde punteado, fondo gris, opacidad reducida) a los turnos con `estado === 'ausente'` frente a los que tienen `estado === 'reservado'`.

#### [MODIFY] `tests/Feature/TurnoControllerTest.php` (o similar)
- Agregar un test feature que valide que el endpoint `TurnoController@index` filtra correctamente devolviendo solo los turnos en el rango temporal solicitado, respetando la estructura local de fechas.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Vista diaria/semanal | Se navegará en la UI alternando las vistas, y avanzando retrocediendo semanas |
| Turnos sin superposición | Validar visualmente que turnos contiguos (gracias al motor de validación de backend) no se pisen en la grilla. |
| Estilos "ausente" | Verificación visual en la UI para turnos cargados manualmente como ausentes. |
| Navegabilidad frontend | Se respetará la URL `/turnos` garantizando que el Layout redirija correctamente a la nueva vista |
| Zona horaria (timezone) | Test de regresión visual y automático para validar que un turno insertado a las 21:00 se renderiza en la franja de las 21:00 en el calendario. |

## Preguntas Abiertas (Open Questions)
No hay preguntas de bloqueo, he optado por crear una UI de calendario ligera con CSS/Tailwind nativo en vez de introducir librerías de terceros (dado que el proyecto es de un solo barbero sin escenarios complejos de superposición múltiple).
