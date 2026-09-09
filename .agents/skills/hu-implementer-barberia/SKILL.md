---
name: hu-implementer-barberia
description: >-
  Activa esta skill cuando el usuario pida planificar o implementar una Historia de Usuario (HU-XXX) 
  para el proyecto de la Barbería — "implementa la HU-TUR-01", "arma el plan para la HU-XX". 
  Cubre la investigación del código base, redacción de un plan versionado bajo docs/plans/, y — 
  tras la aprobación explícita — su implementación de extremo a extremo siguiendo las convenciones 
  del proyecto (Laravel/React/Inertia) y el archivo agent-rules.md.
---

# Instrucciones de Ejecución:
El proceso consta de Tres Fases con un punto de aprobación explícito entre la Fase 1 y la 2. 
NUNCA saltees la Fase 1, aunque el usuario ordene "implementa la HU-XX directamente". El plan siempre se escribe y se aprueba primero.

## Fase 1 — Investigación y Plan
1. Ubicar la historia y sus restricciones
  - Buscar HU-XXX en `product-backlog.md` — es la única fuente de la verdad para los Criterios de Aceptación (Datos / Validaciones / Comportamiento / Verificación). Si no existe, detente y avisa; no inventes criterios.
  - Revisar `sprint-backlog-<n>.md`: confirmar que la HU está comprometida en el sprint activo y leer la Definition of Done (DoD).
  - Leer estrictamente `agent-rules.md` antes de proponer cualquier solución técnica.

2. Investigar el código existente (Obligatorio)
  El objetivo es que el diseño nuevo reutilice patrones existentes. Busca y lee:
  - Migraciones y modelos relacionados (`app/Models/`).
  - Actions existentes (`app/Actions/`) para ver cómo se están encapsulando las reglas de negocio (ej. validaciones de superposición de turnos).
  - FormRequests (`app/Http/Requests/`) para ver patrones de validación de servidor.
  - Páginas de React (`resources/js/Pages/`) y componentes (`resources/js/Components/`) para mantener consistencia con Tailwind CSS.
  - Rutas en `routes/web.php`.
  - ¡IMPORTANTE!: Verifica que no queden rastros de FilamentPHP o Livewire en el flujo que vas a tocar.

3. Escribir el plan
  Crear el archivo `docs/plans/HU-XXX-plan.md` con esta estructura exacta:

  # HU-XXX — <título>
  <1-2 líneas de contexto de negocio sobre qué construye esta historia>

  ## Criterios de aceptación (de `product-backlog.md`)
  <lista numerada, tal cual están en el backlog>

  ## Investigación del código existente
  <tabla: Artefacto | Ubicación | Propósito / Patrón a reutilizar>

  ## Cambios Propuestos (Proposed Changes)
  <Una subsección por capa: Backend — Migraciones/Modelos, Backend — Actions, Backend — FormRequests/Controllers, Rutas, Frontend — Páginas/Componentes React, Tests>
  Cada subsección debe usar `#### [NEW]` o `#### [MODIFY]` + la ruta del archivo, detallando métodos, props, y lógica a implementar.

  ## Verificación de la Definition of Done (DoD)
  <tabla: Criterio de la DoD del sprint | Cómo se verificará en esta HU>

  ## Preguntas Abiertas (Open Questions)
  <Preguntas reales que bloquean el desarrollo y necesitan decisión del usuario (ej. diseño de UI, alcance no definido). Si no hay, omitir.>

4. Presentar el plan y esperar aprobación
  Muestra un resumen del plan en el chat (no pegues el archivo entero si es muy largo) y ESPERA la confirmación explícita del usuario antes de tocar código. Si hay Preguntas Abiertas, resuélvelas en este paso.

## Fase 2 — Implementación (Solo tras aprobación explícita)
1. Ejecuta el plan estrictamente en orden: Backend → Rutas → Frontend React → Tests. Las Actions y Modelos deben existir antes de que los Controllers los consuman.
2. Cada archivo [NEW]/[MODIFY] se implementa según lo especificado. Si surge un bloqueador que obliga a desviarse del plan, avisa explícitamente en el chat (qué cambia y por qué). No te desvíes en silencio.
3. Si la implementación incluyó nuevas migraciones de base de datos (`database/migrations/`), **debes ejecutar** `php artisan migrate` localmente y, al finalizar, **informar explícitamente al usuario** que las migraciones fueron corridas o recordarle que debe correrlas en su entorno.
4. Al terminar la lógica, ejecuta los checks locales (simulación de CI):
   - `php artisan test` (si se crearon tests de Actions).
   - `vendor/bin/pint` (para formateo de PHP).
   - `npm run build` (para compilar Vite/React y verificar que no hay errores de sintaxis).
5. Verifica uno por uno los puntos de la tabla "Verificación de la Definition of Done" del plan. NO des la historia por terminada si falta aplicar validaciones del lado del servidor o encapsular lógica en un Action.


## Fase 3 — Informe de cierre para el Commit y PR
Al finalizar la Fase 2, genera en el chat el texto listo para que el usuario haga el commit y el Pull Request:

1. Mensaje de commit (Convencional y en español):
   <tipo>(<módulo>): <resumen imperativo, HU-XXX>
   <cuerpo: 2-3 líneas explicando qué se construyó y por qué, en términos de negocio>

2. Cuerpo del Pull Request (Markdown):
   ## HU-XXX — <título>

   ### Qué resuelve
   <1-3 líneas en términos de la historia de usuario>

   ### Criterios de aceptación
   - [x] <criterio 1> — <cómo se verificó>
   - [x] <criterio 2> — <cómo se verificó>

   ### Cambios Principales
   <Resumen por capa apuntando a los archivos [NEW]/[MODIFY] de docs/plans/HU-XXX-plan.md>

   ### QA y Checks
   <Resultado de Pint, Tests y npm run build>

   ### Notas
   <Desvíos del plan original, si los hubo>
