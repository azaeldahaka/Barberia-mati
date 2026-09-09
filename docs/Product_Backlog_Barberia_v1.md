# Product Backlog — Sistema de Gestión de Turnos (Barbería)

**Versión:** 1.0
**Basado en:** Documento de Alcance v2, Decisiones de Arquitectura v1 y DER v1
**Fuente de verdad de este contenido:** este archivo. Si se modifica, propagar los cambios también a `Product_Backlog_Barberia_v1.docx` y a la hoja "Product Backlog" de `Backlog_Barberia_v1.xlsx`.

## Cómo leer este backlog

Cada funcionalidad del Documento de Alcance es una Historia de Usuario (HU) con criterios de aceptación verificables. Prioridad según MoSCoW:

- **Must** — imprescindible para el MVP; sin esto el sistema no cumple su propósito mínimo.
- **Should** — importante, pero el sistema puede lanzarse sin esto si hace falta recortar tiempo.
- **Could** — deseable, candidato natural para una segunda iteración.

Las HU que dependen de una decisión de arquitectura (ver `Decisiones_Arquitectura_v1.docx`) indican su ADR relacionado.

Formato de cada HU:

```
### HU-XXX-NN · Título
- **Prioridad:** Must | Should | Could
- **ADR relacionado:** ADR-XX o "-"
- **Sprint sugerido:** N

**Como** <rol>, **quiero** <acción>, **para** <beneficio>.

**Criterios de aceptación:**
- ...
```

---

## Módulo de Turnos (TUR)

### HU-TUR-01 · Alta de turno por staff
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** dueño/recepción, **quiero** registrar manualmente un turno eligiendo cliente, servicio y horario disponible, **para** organizar la agenda cuando el cliente reserva por teléfono o en el local.

**Criterios de aceptación:**
- El formulario permite buscar un cliente existente o cargar uno nuevo en el momento.
- Solo se pueden seleccionar horarios libres según la duración del servicio elegido (ver HU-TUR-04).
- El turno creado aparece inmediatamente en la agenda (HU-TUR-03).
- No se permite crear un turno que se superponga con otro ya existente.

### HU-TUR-02 · Reserva de turno por el cliente (autogestión)
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** cliente, **quiero** reservar mi propio turno desde una página pública, **para** sacar un turno sin tener que llamar o ir personalmente.

**Criterios de aceptación:**
- La página pública muestra únicamente los horarios disponibles, ya bloqueados según turnos existentes.
- El cliente elige un servicio y ve la duración antes de confirmar.
- Si el cliente no tiene cuenta, se lo dirige al flujo de autoregistro (HU-CLI-01) antes de confirmar.
- Al confirmar, el turno queda visible en la agenda del staff en tiempo real.

### HU-TUR-03 · Vista de agenda diaria/semanal
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** dueño/recepción, **quiero** ver la agenda del día o de la semana en formato calendario, **para** saber en todo momento quién viene y a qué hora.

**Criterios de aceptación:**
- La vista muestra los turnos como bloques de tiempo exclusivos (sin superposición).
- Cada bloque muestra al menos: nombre del cliente y servicio.
- Se puede alternar entre vista diaria y semanal.
- Los turnos marcados como "ausente" se distinguen visualmente de los confirmados.

### HU-TUR-04 · Duración configurable por servicio
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** dueño, **quiero** que cada servicio tenga definida su propia duración estimada, **para** que el sistema calcule automáticamente cuánto bloque de agenda ocupa cada turno.

**Criterios de aceptación:**
- Cada servicio del catálogo (HU-SER-01) tiene un campo de duración en minutos.
- Al agendar un turno, el sistema reserva el bloque exacto según la duración del servicio elegido.
- Si se elige un combo, la duración es la suma (o un valor propio) definida para ese combo.

### HU-TUR-05 · Cancelación y reprogramación de turno
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 2

**Como** cliente o staff, **quiero** poder cancelar o reprogramar un turno ya agendado, **para** liberar el horario si algo cambia, sin perder el orden de la agenda.

**Criterios de aceptación:**
- Se puede cancelar/reprogramar solo si faltan 1 hora o más para el turno.
- Si faltan menos de 1 hora, el sistema no permite la acción y muestra un aviso claro.
- Al cancelar, el horario vuelve a quedar disponible inmediatamente para otro cliente.
- La reprogramación reutiliza el flujo de selección de horario disponible (HU-TUR-01/02).

### HU-TUR-06 · Registro de inasistencia (no-show)
- **Prioridad:** Should
- **ADR relacionado:** -
- **Sprint sugerido:** 2

**Como** dueño/recepción, **quiero** marcar un turno como "ausente" cuando el cliente no se presenta, **para** tener el dato registrado en el historial del cliente.

**Criterios de aceptación:**
- El turno pasa a estado "ausente" con un solo clic desde la agenda.
- No se aplica ninguna penalización automática al cliente (fuera de alcance en v1).
- El estado "ausente" queda visible en el historial de servicios del cliente (HU-CLI-02).

### HU-TUR-07 · Recordatorio automático por WhatsApp
- **Prioridad:** Should
- **ADR relacionado:** ADR-01
- **Sprint sugerido:** 3

**Como** cliente, **quiero** recibir un recordatorio por WhatsApp antes de mi turno, **para** no olvidarme del turno reservado.

**Criterios de aceptación:**
- El mensaje se envía con una anticipación configurable (ej. 2-3 horas antes, a definir en el sprint de implementación).
- El envío usa la API de WhatsApp Business a través de un proveedor BSP (ver ADR-01).
- Si el envío falla, el turno no se cancela ni se ve afectado — el fallo solo se registra en un log.

> **Nota:** depende de ADR-01. Antes de implementar, cotizar al menos 2 proveedores BSP (ej. Twilio, 360dialog) y completar la verificación de negocio ante Meta, que puede demorar días.

### HU-TUR-08 · Horario de atención fijo del sistema
- **Prioridad:** Must
- **ADR relacionado:** ADR-02
- **Sprint sugerido:** 1

**Como** dueño, **quiero** que el sistema respete el horario real de atención del local, **para** que no se puedan agendar turnos fuera del horario en que la barbería está abierta.

**Criterios de aceptación:**
- El sistema no permite crear turnos antes de las 12:00/12:30hs ni después de las 22:00hs.
- El horario está fijo en la configuración del sistema para esta versión (no editable desde una pantalla).
- Se contempla la atención por orden de llegada como una franja sin turno pre-asignado, pero dentro del mismo horario general.

> **Nota:** depende de ADR-02. El horario queda hardcodeado en esta v1; una futura iteración podrá agregar una pantalla de configuración.

---

## Módulo de Clientes (CLI)

### HU-CLI-01 · Autoregistro de cliente
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** cliente nuevo, **quiero** crear mi propio perfil desde la página pública, **para** poder reservar turnos sin que el barbero tenga que cargarme manualmente.

**Criterios de aceptación:**
- El formulario pide nombre, apellido y teléfono/WhatsApp como datos obligatorios.
- No se permiten dos perfiles con el mismo número de teléfono.
- Al finalizar el registro, el cliente puede reservar un turno inmediatamente (HU-TUR-02).

### HU-CLI-02 · Historial de servicios por cliente
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 2

**Como** dueño, **quiero** ver el historial de servicios de cada cliente, **para** saber qué se le hizo, cuándo, y llevar mejor el trato con el cliente habitual.

**Criterios de aceptación:**
- El perfil del cliente lista todos los turnos pasados: fecha, servicio y estado (asistió/ausente).
- El historial se actualiza automáticamente cuando se completa o marca un turno.
- El historial es visible desde la agenda al hacer clic en el turno de ese cliente.

### HU-CLI-03 · Programa de fidelización — 5to corte gratis
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 3

**Como** cliente, **quiero** que se me acumulen mis cortes para obtener el sexto gratis, **para** sentirme premiado por ser un cliente habitual.

**Criterios de aceptación:**
- El sistema cuenta únicamente los servicios de tipo "corte" (no barba, cejas ni combos).
- Al llegar al 5to corte pagado, el 6to queda marcado automáticamente como gratis para ese cliente.
- El beneficio es intransferible: no puede aplicarse a otro cliente ni acumularse entre distintas personas.
- Una vez usado el beneficio, el contador vuelve a cero y arranca un nuevo ciclo de 5.
- El staff puede ver cuántos cortes lleva acumulados un cliente desde su perfil.

---

## Módulo de Servicios (SER)

### HU-SER-01 · ABM de servicios
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** dueño, **quiero** dar de alta, modificar o eliminar los servicios que ofrezco, **para** mantener actualizado el catálogo (nombre, duración, precio) sin depender de un desarrollador.

**Criterios de aceptación:**
- Cada servicio tiene nombre, duración (minutos) y precio.
- El precio es único por servicio (no varía por barbero).
- No se puede eliminar un servicio que ya tiene turnos históricos asociados (se desactiva en su lugar).

### HU-SER-02 · Combos de servicios con precio propio
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 2

**Como** dueño, **quiero** definir combos de servicios con un precio distinto a la suma de sus partes, **para** ofrecer promociones como estrategia comercial.

**Criterios de aceptación:**
- Combo Corte + Barba disponible con precio fijo de $7.000.
- Combo Barba + Cejas disponible con precio fijo de $5.000.
- El precio del combo se carga como un valor propio en el sistema, no se calcula sumando los servicios individuales.
- Al agendar un combo, el turno ocupa el bloque de tiempo correspondiente a la duración total definida para ese combo.

### HU-SER-03 · Control de stock de insumos con aviso de mínimo
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 2

**Como** dueño, **quiero** que el sistema descuente stock de filos y cuellitos por cada servicio, y me avise cuando quede poco, **para** saber cuándo reponer sin llevar la cuenta a mano.

**Criterios de aceptación:**
- Cada servicio que usa navaja descuenta 1 filo y 1 cuellito del stock al completarse.
- El dueño puede definir un nivel mínimo de stock por insumo.
- El sistema muestra un aviso visible (ej. en el panel principal) cuando el stock de un insumo llega o baja del mínimo definido.
- El dueño puede cargar reposiciones de stock manualmente desde una pantalla simple.

---

## Módulo de Seguridad y Usuarios (SEG)

### HU-SEG-01 · Autenticación del personal
- **Prioridad:** Must
- **ADR relacionado:** -
- **Sprint sugerido:** 1

**Como** dueño/staff, **quiero** poder iniciar sesión con Google o con usuario y contraseña, **para** acceder al sistema de la forma que me resulte más cómoda.

**Criterios de aceptación:**
- El login soporta OAuth con Google.
- El login soporta usuario/contraseña propio del sistema, con hash seguro de contraseña.
- Tras iniciar sesión, se redirige directamente a la agenda del día.

### HU-SEG-02 · Rol único operativo (dueño/barbero/recepción)
- **Prioridad:** Must
- **ADR relacionado:** ADR-03
- **Sprint sugerido:** 1

**Como** dueño, **quiero** operar el sistema con un único usuario que concentre todas las funciones, **para** no tener que gestionar roles separados innecesarios mientras el negocio tiene un solo barbero.

**Criterios de aceptación:**
- Existe un único rol operativo en esta versión, con acceso a agenda, clientes, servicios y reportes.
- El modelo de permisos está diseñado para poder agregar roles diferenciados a futuro (ej. un segundo barbero con agenda propia) sin rediseñar el sistema.

> **Nota:** ligado a ADR-03: el modelo de datos y autorización ya contempla la relación con una entidad Barbería, preparando el terreno para roles múltiples y para el escenario multi-tenant a futuro.

### HU-SEG-03 · Panel de reportes e indicadores
- **Prioridad:** Should
- **ADR relacionado:** -
- **Sprint sugerido:** 3

**Como** dueño, **quiero** ver un panel con ingresos, turnos atendidos y servicios más solicitados, **para** tomar decisiones de negocio con datos reales en vez de estimaciones.

**Criterios de aceptación:**
- El panel muestra ingresos totales filtrables por día, semana y mes.
- El panel muestra cantidad de turnos atendidos en el período seleccionado.
- El panel muestra un ranking de los servicios más solicitados en el período.

---

## Resumen de HU por prioridad

| Prioridad | Cantidad | HU |
|---|---|---|
| Must | 12 | TUR-01, TUR-02, TUR-03, TUR-04, TUR-05, TUR-08, CLI-01, CLI-02, CLI-03, SER-01, SER-02, SER-03, SEG-01, SEG-02 |
| Should | 3 | TUR-06, TUR-07, SEG-03 |
| Could | 0 | — |

**Total:** 17 Historias de Usuario.
