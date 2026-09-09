# Sprint 1 Backlog — Núcleo Mínimo Viable

**Sistema:** Gestión de Turnos — Barbería
**Versión:** 1.0
**Fuente de verdad:** este archivo. Si se modifica, replicar los cambios en `Backlog_Barberia_v1.xlsx` (hoja "Sprint 1 Backlog").

Orden de desarrollo sugerido según dependencias técnicas y de negocio. El objetivo del sprint es tener un flujo completo y usable: un cliente se registra, saca un turno, el dueño lo ve en su agenda y lo atiende.

Cada HU referencia su definición completa (narrativa + criterios de aceptación) en `product_backlog.md`.

## Orden de desarrollo

| Orden | ID | Módulo | Título | Prioridad | Por qué va en este orden |
|---|---|---|---|---|---|
| 1 | HU-SEG-01 | SEG | Autenticación del personal | Must | Base de acceso al sistema: sin login no se puede probar nada más. |
| 2 | HU-SEG-02 | SEG | Rol único operativo (dueño/barbero/recepción) | Must | Define el modelo de usuario/rol sobre el que corre todo lo demás (y sienta la base multi-tenant-ready, ADR-03). |
| 3 | HU-CLI-01 | CLI | Autoregistro de cliente | Must | Debe existir un cliente antes de poder agendarle un turno. |
| 4 | HU-SER-01 | SER | ABM de servicios | Must | Debe existir un servicio (con duración) antes de poder agendar un turno. **Incluye crear el modelo `Item_Catalogo`** (ver glosario_datos.md): Servicio no tiene nombre/precio/duración propios, los expone a través de Item_Catalogo. Toda HU posterior que toque Servicio o Combo (HU-TUR-04, HU-SER-02, HU-SER-03) asume que esta estructura ya está bien implementada. |
| 5 | HU-TUR-04 | TUR | Duración configurable por servicio | Must | Depende de que el servicio ya tenga duración cargada (HU-SER-01); habilita el cálculo de bloques de agenda. |
| 6 | HU-TUR-08 | TUR | Horario de atención fijo del sistema | Must | El horario fijo es una restricción base de la agenda; conviene tenerla antes de habilitar altas de turno. |
| 7 | HU-TUR-01 | TUR | Alta de turno por staff | Must | Alta de turno por staff — primer flujo completo de agendamiento. |
| 8 | HU-TUR-02 | TUR | Reserva de turno por el cliente (autogestión) | Must | Alta de turno por cliente — reutiliza la misma lógica de disponibilidad que HU-TUR-01. |
| 9 | HU-TUR-03 | TUR | Vista de agenda diaria/semanal | Must | Vista de agenda — necesita que ya existan turnos cargados (HU-TUR-01/02) para tener sentido probarla. |

## Notas

- El resto de las Historias de Usuario (cancelación, no-show, WhatsApp, fidelización, combos, stock, reportes) se planifican para Sprint 2 en adelante — ver la sección "Resumen por sprint sugerido" en `product_backlog.md`.
- Este orden asume desarrollo secuencial de un único desarrollador/agente. Si se paraleliza entre más de uno, HU-SEG-01/02 y HU-CLI-01/HU-SER-01 pueden avanzar en paralelo ya que no dependen entre sí; HU-TUR-04 en adelante sí requiere que las anteriores estén completas.
- **Lección aprendida (HU-SER-01):** la primera implementación de HU-SER-01 no siguió la estructura de `Item_Catalogo` definida en el glosario, lo que obligó a una corrección antes de poder retomar HU-TUR-04. Antes de empezar cualquier HU de este sprint, verificar que las HU previas no solo "existan" en el código sino que respeten la estructura exacta del glosario — no asumir que el orden numérico por sí solo garantiza una base correcta.
