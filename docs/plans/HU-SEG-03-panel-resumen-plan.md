# Plan de Implementación: Panel Resumen del Día (Refinamiento HU-SEG-03)

El objetivo de esta implementación es mostrar estadísticas básicas del día actual en el panel principal (Dashboard), adelantando parcialmente la HU-SEG-03.

## Hallazgos Previos (Aprobación Requerida)

1. **Estado actual del Dashboard:** Actualmente, el archivo `Dashboard.jsx` solo muestra un cuadro blanco con el texto "You're logged in!".
2. **Registro de precios en la base de datos:** Al revisar la migración de `turnos` y el modelo `Turno.php`, confirmé que **el precio no se guarda congelado en cada turno**. El sistema obtiene el precio de la relación con `Item_Catalogo`.
   > [!WARNING] Importante
   > Dado que el precio actual se toma de `Item_Catalogo`, si en el futuro cambias el precio de un servicio o combo, **el sistema recalculará los ingresos de turnos pasados con el nuevo precio**. Para evitar que se alteren los ingresos históricos, a futuro se debería considerar copiar el precio al momento de agendar el turno o crear una tabla de histórico de precios. Como se indicó, esto es solo un aviso y no se resolverá en esta rama.

## Criterios de Negocio y Estados

- **Cantidad de turnos del día:** Se contarán los turnos cuyo `estado` sea `reservado`, `completado` o `ausente`. Se excluyen los `cancelado` ya que no ocupan agenda ni implican asistencia.
- **Ingresos estimados del día:** Se sumarán los precios solo de los turnos en estado `reservado` y `completado`. Los turnos `ausente` y `cancelado` NO sumarán ingresos.
- **Servicio más solicitado:** Se agruparán los turnos del día (excepto cancelados) por el nombre del `Item_Catalogo` y se tomará el que tenga mayor cantidad. En caso de empate, se mostrará cualquiera de los ganadores.

## Cambios Propuestos

### Backend (Controlador y Consultas)
Para evitar la carga de turnos en memoria y hacer cálculos en PHP, utilizaremos consultas agregadas SQL a través de Eloquent, respetando la zona horaria del negocio configurada en Laravel (`America/Argentina/Buenos_Aires`).

#### [NEW] `app/Http/Controllers/DashboardController.php`
Crearemos un controlador invocable para reemplazar el Closure en `routes/web.php` y mantener el código limpio. Aquí ejecutaremos:
1. `cantidad_turnos_hoy`: `Turno::whereDate('fecha_hora_inicio', today())->whereIn('estado', ['reservado', 'completado', 'ausente'])->count()`
2. `ingresos_estimados_hoy`: Un `join` entre `turnos` e `item_catalogos` sumando el precio para los estados válidos.
3. `servicio_mas_solicitado`: Un `join` agrupando por nombre de servicio y ordenando por recuento descendente (limit 1).

Se pasarán estas variables a `Inertia::render('Dashboard', ...)`

#### [MODIFY] `routes/web.php`
Se cambiará la ruta `/dashboard` para usar el nuevo `DashboardController`.

### Frontend (React + Tailwind)
#### [MODIFY] `resources/js/Pages/Dashboard.jsx`
- Se reemplazará el contenido actual por una grilla de 3 tarjetas (Tailwind CSS) con los valores calculados.
- Se agregará un espacio reservado en la parte superior para el futuro botón de "Agendado rápido".
- Se implementará un **estado vacío** claro: Si `cantidad_turnos_hoy === 0`, en lugar de mostrar las 3 tarjetas con ceros, se mostrará un mensaje amigable: *"Sin turnos hoy"*.

## Plan de Verificación

### Pruebas Manuales y Evidencia
- Se iniciará sesión como staff y se verificará el panel vacío.
- Se crearán turnos para el día de hoy, comprobando que las tarjetas aparezcan con valores correctos.
- Se creará un turno "ausente" para validar que aumente la cantidad de turnos pero no los ingresos.
- Se creará un turno cerca de medianoche para comprobar que caiga en el día correcto usando la zona horaria del sistema.
- Se entregarán capturas o descripciones de evidencia al finalizar.

### Tests Automatizados
- Se creará/modificará un test para el dashboard en `tests/Feature/DashboardTest.php` o similar, asegurando que el controlador ejecute las consultas correctamente y devuelva las variables necesarias a Inertia.
