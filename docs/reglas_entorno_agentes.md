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

## Al entregar
Indicar siempre:
- Qué archivos se crearon/modificaron.
- Qué piezas quedan listas para que las reutilicen HU futuras (y dónde
  están, para que no se dupliquen).
- Cualquier alcance explícitamente dejado fuera de esta HU, y de qué HU
  posterior es responsabilidad — para evitar el tipo de sorpresa de
  alcance que ocurrió entre HU-SER-01 y HU-TUR-04.
