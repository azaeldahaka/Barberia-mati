# Glosario de Datos — Sistema de Gestión de Turnos (Barbería)

**Versión:** 1.0
**Complementa a:** Diagrama Entidad-Relación (DER) — `DER_Barberia_v1.dot` / `.png` / `.svg`
**Fuente de verdad:** este archivo. Si se modifica, replicar los cambios en `Glosario_Datos_Barberia_v1.docx`.

**Nota de corrección (10/09/2026):** las claves primarias y foráneas de este glosario se documentaron originalmente como `uuid`. Durante la implementación de HU-TUR-01 se detectó que Cliente y Usuario ya existían en el código con `id` tipo `bigint` autoincremental (el default de Laravel), y no tenía sentido migrar retroactivamente HU-SEG-01/HU-CLI-01/HU-SER-01 ya cerradas. Se decidió adoptar `bigint` como estándar del proyecto y corregir este documento para que sea la fuente de verdad real. La arquitectura multi-tenant-ready (ADR-03) no depende del tipo de dato de la PK, solo de que `barberia_id` exista como FK — este cambio no la afecta.

## 1. Propósito de este documento

Este glosario describe cada entidad (tabla) del modelo de datos, su propósito de negocio, sus campos y sus relaciones con otras entidades. Complementa al DER: mientras el diagrama muestra la estructura visual, este documento explica el porqué de cada tabla y cada decisión de modelado.

Cada entidad indica con qué Historia de Usuario (HU) del Product Backlog se relaciona principalmente, para facilitar la trazabilidad entre el modelo de datos y el trabajo de desarrollo.

## 2. Entidades del modelo

### BARBERIA

Entidad raíz del modelo. Representa a una barbería como cliente del sistema. Es la base de la arquitectura multi-tenant-ready (ADR-03): aunque hoy existe un único registro (la barbería de Matías), toda tabla relevante del sistema queda relacionada con una Barbería específica desde el día 1.

**Relaciones clave:** es el punto de partida de casi todas las demás entidades (Usuario, Cliente, Servicio, Combo, Insumo, Turno).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único de la barbería. |
| `nombre` | string | Nombre comercial de la barbería. |
| `horario_apertura` | string | Hora de apertura del local (ej. "12:00"). Fija para la v1 (ADR-02). |
| `horario_cierre` | string | Hora de cierre del local (ej. "22:00"). Fija para la v1 (ADR-02). |

---

### USUARIO

Representa a una persona del staff que opera el sistema (hoy, únicamente Matías en su rol único de dueño/barbero/recepción — HU-SEG-02). Soporta login por Google o por usuario/contraseña propio (HU-SEG-01).

**Relaciones clave:** pertenece a una Barbería. Atiende Turnos.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del usuario. |
| `barberia_id` | bigint (FK) | Barbería a la que pertenece este usuario. |
| `nombre` | string | Nombre del usuario del staff. |
| `email` | string | Email usado para login (propio o vinculado a Google). |
| `password_hash` | string | Contraseña encriptada, si el usuario usa login propio (nulo si usa solo Google). |
| `google_id` | string | Identificador de la cuenta de Google, si el usuario usa ese método de login (nulo si usa solo contraseña propia). |

---

### CLIENTE

Representa a un cliente de la barbería. Se autoregistra desde la página pública (HU-CLI-01) para poder reservar turnos.

**Relaciones clave:** pertenece a una Barbería. Reserva Turnos. Tiene una Fidelización asociada.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del cliente. |
| `barberia_id` | bigint (FK) | Barbería en la que este cliente está registrado. |
| `nombre` | string | Nombre del cliente. |
| `apellido` | string | Apellido del cliente. |
| `telefono` | string | Teléfono/WhatsApp de contacto. Usado también para el recordatorio automático (HU-TUR-07). |

---

### FIDELIZACION

Lleva el conteo del programa de fidelización: cada 5 cortes acumulados, el 6to es gratis (HU-CLI-03). Se modela como una tabla separada de Cliente (no como un campo suelto) para poder extender el programa a futuro sin modificar la tabla Cliente.

**Relaciones clave:** pertenece a un Cliente (relación 1 a 1: cada cliente tiene un único registro de fidelización).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del registro de fidelización. |
| `cliente_id` | bigint (FK) | Cliente al que pertenece este contador. |
| `cortes_acumulados` | int | Cantidad de cortes pagados acumulados en el ciclo actual (de 0 a 5). Se reinicia a 0 cuando el cliente usa el corte gratis. |
| `ultima_actualizacion` | date | Fecha del último corte que modificó el contador. |

---

### ITEM_CATALOGO

Entidad unificadora que representa cualquier "cosa agendable y con precio": un Servicio suelto o un Combo. Existe para que la tabla Turno pueda referenciar un único ítem sin necesitar dos columnas (una para servicio y otra para combo). Es una decisión de simplicidad para el MVP.

**Relaciones clave:** es la base común de Servicio y Combo. Es referenciada por Turno.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del ítem de catálogo. |
| `barberia_id` | bigint (FK) | Barbería a la que pertenece este ítem. |
| `tipo` | string | Indica si el ítem es 'servicio' o 'combo'. |
| `nombre` | string | Nombre visible del ítem (ej. "Corte", "Combo Corte + Barba"). |
| `precio` | decimal | Precio de venta del ítem. Para combos, es un valor propio (HU-SER-02), no la suma de sus partes. |
| `duracion_minutos` | int | Duración estimada del ítem, usada para calcular el bloque de agenda (HU-TUR-04). |

---

### SERVICIO

Representa un servicio individual del catálogo (Corte, Barba, Cejas). Se gestiona mediante alta/baja/modificación (HU-SER-01).

**Relaciones clave:** extiende ("es un") ITEM_CATALOGO. Compone Combos a través de Combo_Servicio. Consume Insumos a través de Servicio_Insumo.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del servicio. |
| `item_catalogo_id` | bigint (FK) | Referencia al ítem de catálogo que contiene nombre, precio y duración de este servicio. |
| `cuenta_para_fidelizacion` | boolean | Indica si este servicio suma al contador de fidelización. Solo es verdadero para el servicio "Corte" (HU-CLI-03), evitando hardcodear el nombre del servicio en el código. |

---

### COMBO

Representa un paquete de servicios vendido a un precio propio (ej. Corte + Barba a $7.000, Barba + Cejas a $5.000 — HU-SER-02).

**Relaciones clave:** extiende ("es un") ITEM_CATALOGO. Incluye Servicios a través de Combo_Servicio.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del combo. |
| `item_catalogo_id` | bigint (FK) | Referencia al ítem de catálogo que contiene nombre, precio y duración de este combo. |

---

### COMBO_SERVICIO

Tabla intermedia (muchos a muchos) que define qué servicios componen cada combo. Por ejemplo, el Combo "Corte + Barba" está compuesto por los servicios "Corte" y "Barba". Permite que el sistema calcule automáticamente qué insumos descontar cuando se completa un turno de combo.

**Relaciones clave:** conecta Combo con Servicio.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único de la relación. |
| `combo_id` | bigint (FK) | Combo al que pertenece esta composición. |
| `servicio_id` | bigint (FK) | Servicio incluido dentro del combo. |

---

### INSUMO

Representa un insumo consumible de la barbería (filos de navaja, cuellitos protectores). Permite llevar stock y avisar cuando se llega a un mínimo definido (HU-SER-03).

**Relaciones clave:** pertenece a una Barbería. Es consumido por Servicios a través de Servicio_Insumo. Registra Movimientos de Stock.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del insumo. |
| `barberia_id` | bigint (FK) | Barbería a la que pertenece este insumo. |
| `nombre` | string | Nombre del insumo (ej. "Filo de navaja", "Cuellito descartable"). |
| `stock_actual` | int | Cantidad disponible actualmente. Se actualiza mediante Movimiento_Stock. |
| `stock_minimo` | int | Nivel mínimo definido por el dueño; al alcanzarlo o bajarlo, el sistema muestra un aviso. |

---

### SERVICIO_INSUMO

Tabla intermedia que define qué insumos consume cada servicio y en qué cantidad. Por ejemplo, el servicio "Barba" consume 1 unidad de "Filo de navaja" y 1 de "Cuellito". Es la base para el descuento automático de stock (confirmado por el usuario para esta versión).

**Relaciones clave:** conecta Servicio con Insumo.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único de la relación. |
| `servicio_id` | bigint (FK) | Servicio que consume el insumo. |
| `insumo_id` | bigint (FK) | Insumo consumido. |
| `cantidad_consumida` | int | Cantidad de ese insumo que se descuenta cada vez que se presta el servicio (normalmente 1). |

---

### TURNO

Entidad central del sistema: representa una reserva de horario, agendada por el staff (HU-TUR-01) o por el propio cliente (HU-TUR-02). Se muestra en la agenda (HU-TUR-03), puede cancelarse o reprogramarse (HU-TUR-05), y puede marcarse como inasistencia (HU-TUR-06).

**Relaciones clave:** pertenece a una Barbería, a un Cliente, a un Usuario (quien atiende) y a un Item_Catalogo (qué se agendó). Genera Movimientos de Stock al completarse.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del turno. |
| `barberia_id` | bigint (FK) | Barbería en la que se agenda el turno. |
| `cliente_id` | bigint (FK) | Cliente que reservó el turno. |
| `usuario_id` | bigint (FK) | Usuario (staff) que atiende el turno. |
| `item_catalogo_id` | bigint (FK) | Servicio o combo agendado en este turno. |
| `fecha_hora_inicio` | datetime | Momento de inicio del turno. |
| `fecha_hora_fin` | datetime | Momento de fin, calculado según la duración del ítem de catálogo (HU-TUR-04). |
| `estado` | string | Estado del turno: reservado, completado, cancelado o ausente (HU-TUR-06). |

---

### MOVIMIENTO_STOCK

Registra cada entrada o salida de stock de un insumo: descuentos automáticos por turnos completados, o reposiciones manuales cargadas por el dueño (HU-SER-03).

**Relaciones clave:** pertenece a un Insumo. Puede estar asociado a un Turno (si es un descuento automático) o no tenerlo (si es una reposición manual).

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint (PK) | Identificador único del movimiento. |
| `insumo_id` | bigint (FK) | Insumo afectado por el movimiento. |
| `turno_id` | bigint (FK) | Turno que generó el descuento automático (nulo si el movimiento es una reposición manual). |
| `cantidad` | int | Cantidad afectada por el movimiento (positiva para reposición, negativa para descuento). |
| `tipo_movimiento` | string | Tipo de movimiento: `descuento_por_servicio` o `reposicion_manual`. |
| `fecha` | datetime | Momento en que se registró el movimiento. |

## 3. Decisiones de modelado a tener en cuenta

- Toda tabla con datos propios de la barbería lleva `barberia_id` desde el día 1 (ADR-03), incluso hoy con una única barbería en uso.
- El patrón `Item_Catalogo` evita duplicar lógica de precio/duración entre Servicio y Combo, y simplifica la tabla Turno a una única referencia.
- El descuento de stock nunca se hardcodea por nombre de servicio: se resuelve siempre a través de `Servicio_Insumo`, incluso para combos (recorriendo `Combo_Servicio`).
- La fidelización usa un campo booleano en Servicio (`cuenta_para_fidelizacion`) en lugar de una lista fija de nombres, para que agregar o quitar servicios del programa no requiera cambios de código.
