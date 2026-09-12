# Informe de Implementación y Correcciones — HU-TUR-02

**Historia de Usuario:** HU-TUR-02 — Reserva de turno por el cliente (autogestión)
**Rama:** `feature/HU-TUR-02`
**Estado:** Lista para revisión y Pull Request

Este documento resume la implementación inicial, los hallazgos en la fase de testing manual (bugs detectados) y cómo fueron corregidos en el código, para proveer contexto claro a quien revise el Pull Request.

---

## 1. Alcance Funcional Inicial
La HU-TUR-02 introduce la posibilidad de que los clientes se autogestionen la reserva de turnos desde una vista pública, sin intervención del staff. 

### Implementación Core
- **Ruta y UI Pública:** Se creó la página `Turnos/PublicCreate.jsx` accesible vía `/reservar`. Se integró el botón de acceso directo en el `Welcome.jsx` público.
- **Lógica de Slots Inteligente:** El frontend genera dinámicamente horarios seleccionables descartando los bloques que ya están ocupados en el día seleccionado.
- **Redirección a Autoregistro (HU-CLI-01):** Si un cliente navega y selecciona un turno sin estar logueado/registrado, la selección se retiene en sesión temporal (`pending_turno`), se lo envía a registrarse, y al finalizar su registro vuelve automáticamente a la pantalla de reserva para la confirmación de su turno precargado.
- **Asignación de Usuario (V1):** Dado que el sistema actualmente funciona para un único barbero (Matías), todo turno autogestionado es asignado sistemáticamente al usuario por defecto (`User::first()`), con comentarios `TODO` de refactorización previstos para cuando se implementen agendas de múltiples profesionales.

---

## 2. Testing Manual y Corrección de Bugs
Tras la primera implementación, se detectaron y resolvieron 3 bugs en el flujo de negocio:

### Bug 1: Superposición de turnos e ignorancia de buffer
**Síntoma:** Al agendar un turno, el siguiente horario disponible se mostraba solapado y no permitía tiempo de limpieza/preparación entre turnos.
**Solución:** 
- Se introdujo una constante `TURNO_BUFFER_MINUTOS = 5` en la lógica de backend (`CheckTurnoOverlapAction`) para blindar el margen entre reservas.
- Se refactorizó la lógica usando cálculos con `Carbon` (agnósticos al motor de DB) validando que `Fin A + Buffer <= Inicio B`.
- Se actualizó el generador de grilla en el frontend (`PublicCreate.jsx`) para que avance en bloques de 15 minutos en lugar de 30. Esto permite que, si un turno termina a las 17:40, la grilla valide el buffer (+5min) y libere exitosamente el slot de las 17:45.

### Bug 2: Falla de redirección post-confirmación
**Síntoma:** Tras reservar exitosamente, la UI se mantenía en la misma pantalla en lugar de concluir el flujo.
**Solución:** 
- En el controlador `PublicTurnoController`, se reemplazó el `back()` y `redirect()->route('public.turno.create')` por una redirección hacia el inicio `/`. Esto purga el contexto del formulario local de Inertia, impidiendo reservas accidentales por clicks dobles y obligando a refetchear datos limpios en la próxima reserva.

### Bug 3 (Crítico): Desfasaje de zona horaria en el almacenamiento
**Síntoma:** Turnos agendados a las 21:00 hs se visualizaban a las 18:00 hs en la agenda del staff.
**Análisis de Causa Raíz:**
1. `config/app.php` operaba en la zona por defecto `UTC`.
2. Las peticiones enviadas desde React no especificaban zona horaria (ej. `21:00`), siendo almacenadas como `21:00 UTC` por el backend de Laravel.
3. Al renderizarse en el dashboard a través de `new Date(string).toLocaleTimeString()`, el navegador interceptaba el sufijo ISO `Z` del payload JSON y le restaba las 3 horas correspondientes a la zona local de Argentina (UTC-3), resultando en `18:00`.
**Solución:**
- Se configuró la aplicación central en `America/Argentina/Buenos_Aires`.
- Se introdujo el método `serializeDate()` en el modelo `Turno` para despojar el sufijo ISO de las fechas (`Y-m-d H:i:s`), forzando al navegador cliente a interpretar todos los timestamps devueltos literalmente como hora local, aislando definitivamente a la barbería contra desfasajes inducidos por las configuraciones de navegadores remotos.

---

## 3. QA y Cobertura (Regression Tests)
Se construyó una suite automatizada dedicada para certificar las correcciones y evitar recurrencias, en `tests/Feature/TurnoBugRegressionTest.php`:
- `test_bug1_turnos_overlap_buffer_validation`: Valida que el motor de agendado respete estrictamente los 5 minutos de buffer.
- `test_bug2_redirects_and_updates_availability`: Verifica que someter el turno saque al usuario de la pantalla de reserva.
- `test_bug3_timezone_consistency`: Simula una reserva a las 21:00, verifica su correcta inserción `21:00` literal en base de datos local y su transmisión inalterable por JSON al panel de staff.

**Estado Final de Integración Local:**
- `php artisan test`: 57 Tests / 181 Aserciones aprobadas ✅
- `vendor/bin/pint`: Estilo y formateo validado ✅
- `npm run build`: Assets empaquetados exitosamente ✅

---
*Este reporte fue autogenerado para asistir en la revisión del código de la HU-TUR-02.*
