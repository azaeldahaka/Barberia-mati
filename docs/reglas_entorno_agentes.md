# Reglas de entorno — pegar al inicio de todo prompt a un agente de desarrollo

**Proyecto:** Sistema de Gestión de Turnos — Barbería
**Stack:** Laravel + React + Inertia.js + PostgreSQL

## Entorno de ejecución
- El desarrollador trabaja en **PowerShell (Windows)**.
- `&&` **no es un separador de comandos válido** en este entorno y rompe con
  error de parser ("El token '&&' no es un separador de instrucciones
  válido en esta versión").
- Para ejecutar varios comandos:
  - Preferido: un comando por línea, confirmando el resultado de cada uno
    antes de continuar con el siguiente (más seguro cuando un comando
    depende de que el anterior haya funcionado).
  - Alternativa si hace falta encadenar en una sola línea: separar con `;`
    — pero tener en cuenta que `;` en PowerShell ejecuta el siguiente
    comando **aunque el anterior falle**, a diferencia de `&&` en bash.
    No usar `;` para secuencias donde el éxito del paso previo es
    condición necesaria para el siguiente (ej. migraciones encadenadas)
    sin verificar el resultado en el medio.

## Control de versiones
- Crear una rama por HU: `feature/HU-XXX-NN` (ej. `feature/HU-TUR-08`).
- Si una HU requiere corregir trabajo de una HU anterior antes de poder
  avanzar (dependencia rota), el commit de esa corrección debe quedar
  claramente separado y explícito en el mensaje (ej. "fix(HU-SER-01):
  corrige estructura de Service para alinear con glosario_datos.md"), para
  que el historial sea trazable y no se mezcle con el desarrollo de la HU
  actual.

## Fuente de verdad del proyecto
Antes de escribir código, revisar siempre:
- `product_backlog.md` — narrativa y criterios de aceptación de la HU.
- `glosario_datos.md` — estructura de datos correcta (entidades, campos,
  relaciones). Es la referencia de modelado, no lo que ya esté en código.
- `sprint_1_backlog.md` — orden de desarrollo y dependencias entre HU.
- `Decisiones_Arquitectura_v1.docx` — ADRs que puedan aplicar a la HU.

Estos `.md` son la fuente de verdad. Si algo en el código existente
contradice al glosario o al backlog, el `.md` tiene prioridad — avisar
antes de continuar en lugar de crear una implementación paralela o asumir
que el código existente está bien.

## Verificación de dependencias antes de empezar
Antes de codear, confirmar que las HU previas en el orden del sprint ya
están implementadas y **respetan la estructura del glosario** (no alcanza
con que "algo" exista con ese nombre — tiene que coincidir con los campos
y relaciones definidos). Si se detecta una dependencia incompleta o mal
implementada, detenerse y avisar con el detalle del problema antes de
construir sobre una base incorrecta.

## QA esperado antes de dar una HU por cerrada
- Tests unitarios/feature cubriendo los criterios de aceptación, incluyendo
  casos de borde relevantes.
- `php artisan test`
- `vendor/bin/pint`
- `npm run build` (si el cambio toca frontend)

## Definition of Done — Navegabilidad frontend (obligatorio)

**Regla de oro, agregada tras la lección aprendida de HU-TUR-01:** una HU que
crea un nuevo módulo, vista principal, panel o índice interactivo **no está
completa** solo porque el código, las rutas y los tests funcionan. También
tiene que ser **alcanzable por el usuario real haciendo clic desde la
interfaz**, sin que nadie tenga que escribir la URL a mano.

Antes de dar una HU por cerrada, si creaste una vista nueva:
- Agregá el enlace correspondiente en los layouts/menús globales (ej.
  `AuthenticatedLayout.jsx`, sidebar, menú móvil) — tanto en la versión de
  escritorio como en la de mobile si el proyecto las tiene separadas.
- Volvé a correr `npm run build` después de tocar el layout.
- Confirmá en tu entrega que hiciste este paso explícitamente (no asumas
  que "se sobreentiende").

## Definition of Done — Zona horaria en fechas/horas (obligatorio)

**Regla de oro, agregada tras la lección aprendida de HU-TUR-02 (bug
crítico):** un turno reservado a las 21:00 apareció en el dashboard del
staff a las 18:00. Causa raíz combinada: `config/app.php` con timezone
`UTC` por defecto, el modelo serializando fechas con indicador `Z` (UTC),
y el navegador (en Argentina, UTC-3) restando 3 horas al convertir a hora
local. Ninguno de esos tres puntos por separado rompía nada visible en
desarrollo — la combinación sí.

Toda HU que cree, muestre o calcule sobre fechas/horas (turnos, reportes
por período, recordatorios de WhatsApp con horario, etc.) debe verificar
explícitamente, antes de cerrarse:
- `config/app.php` tiene la timezone del negocio configurada
  (`America/Argentina/Buenos_Aires` o la que corresponda), no el default
  `UTC`.
- La hora que el usuario ingresa en el frontend, la que viaja al backend,
  la que se guarda en base, y la que se vuelve a mostrar en cualquier
  pantalla (incluida una distinta a la que la originó) son la misma hora
  local — sin conversiones implícitas del navegador ni de Carbon/Eloquent
  en el medio.
- Si el modelo serializa fechas a JSON (Eloquent lo hace por defecto), el
  formato de salida no debe traer un indicador de UTC (`Z` o `+00:00`)
  salvo que el sistema esté genuinamente diseñado para trabajar en UTC de
  punta a punta — no es el caso de este proyecto (ver `serializeDate` en
  el modelo `Turno` como referencia de la solución ya aplicada).
- Incluir al menos un test de regresión explícito que cree un registro
  con una hora específica y verifique que esa misma hora se lea igual
  desde donde sea que se consuma — este tipo de bug es silencioso y puede
  reaparecer sin aviso en una HU no relacionada.

## Al entregar
Indicar siempre:
- Qué archivos se crearon/modificaron.
- Qué piezas quedan listas para que las reutilicen HU futuras (y dónde
  están, para que no se dupliquen).
- Cualquier alcance explícitamente dejado fuera de esta HU, y de qué HU
  posterior es responsabilidad — para evitar el tipo de sorpresa de
  alcance que ocurrió entre HU-SER-01 y HU-TUR-04.
