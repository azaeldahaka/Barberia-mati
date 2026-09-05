# Reglas de Desarrollo y Comportamiento del Agente (Antigravity)

## 1. Stack Tecnológico Estricto
- **Backend:** Laravel 12 (PHP 8.4+).
- **Frontend:** React + Inertia.js.
- **Estilos:** Tailwind CSS.
- **Base de Datos:** PostgreSQL.
- **PROHIBIDO:** Usar FilamentPHP, Livewire, o crear APIs REST independientes. Toda conexión front/back se hace vía Inertia.

## 2. Prevención de Alucinaciones
- No inventes paquetes de Composer ni dependencias de npm. Si necesitas un paquete extra, pregunta primero.
- Aplica validaciones SIEMPRE del lado del servidor en Laravel (FormRequests), no solo en la interfaz de React.
- Escribe código conciso, tipado (TypeScript si aplica, tipado fuerte en PHP) y limpio.

## 3. Flujo de Git (Git Flow) y Ramas
- Nunca trabajes directo en `master`.
- El formato estricto para crear ramas por Historia de Usuario es: `feature/HU-XXX-breve-descripcion`. Ejemplo: `feature/HU-TUR-01-alta-turno`.
- Los commits deben seguir el formato Conventional Commits (ej. `feat: implementa alta manual de turnos`, `fix: corrige validacion de bloque horario`).

## 4. Gestión del Trabajo
- Antes de codificar, lee siempre el `product-backlog.md` y el `sprint-backlog-1.md` para entender el alcance de la historia asignada.
- No modifiques el `product-backlog.md` a menos que se te indique explícitamente.