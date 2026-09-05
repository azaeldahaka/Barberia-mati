# Product Backlog - Barbería

Fuente de verdad del Product Backlog. Se edita acá, en markdown[cite: 5].

## Reglas del Backlog
- **El orden es la prioridad.** No hay campo de prioridad: para repriorizar se mueve el ítem[cite: 5].
- **Estimación:** Story points (escala Fibonacci)[cite: 5].
- **Criterios de aceptación:** Siguen la estructura Datos / Validaciones / Comportamiento / Verificación[cite: 5].

---

### HU-SEG-01: Autenticación del Personal
**Estimación:** 3 SP
**Como** miembro del staff, **necesito** iniciar sesión de forma segura, **para** poder acceder a las herramientas administrativas.
**Criterios de Aceptación:**
- **Datos:** Email y contraseña válidos o token de Google.
- **Validaciones:** Credenciales correctas; usuario debe existir en la base de datos.
- **Comportamiento:** Redirección al panel principal tras login exitoso. Soporte para OAuth de Google.
- **Verificación:** Intentar login con credenciales inválidas arroja error. Login exitoso crea sesión.

### HU-SEG-02: Modelo Extensible de Roles
**Estimación:** 2 SP
**Como** arquitecto de software, **necesito** diseñar la base de datos con soporte RBAC, **para** facilitar la incorporación de personal futuro.
**Criterios de Aceptación:**
- **Datos:** Tablas de `users`, `roles`, y `role_user` (o usar Spatie Permission).
- **Validaciones:** Ningún usuario puede quedar sin rol. 
- **Comportamiento:** El sistema asume el rol "Dueño" por defecto para la v1.

### HU-SER-01: Gestión de Servicios (ABM)
**Estimación:** 5 SP
**Como** administrador, **necesito** dar de alta, modificar y dar de baja los servicios, **para** mantener actualizado mi catálogo.
**Criterios de Aceptación:**
- **Datos:** Nombre (string), Duración en minutos (integer), Precio (decimal).
- **Validaciones:** Nombre único, duración > 0, precio >= 0.
- **Comportamiento:** CRUD completo en React + Inertia.
- **Verificación:** Crear un servicio lo lista inmediatamente en la tabla.

### HU-TUR-01: Alta manual de turno
**Estimación:** 5 SP
**Como** dueño, **necesito** registrar un turno seleccionando cliente, servicio y horario, **para** organizar la agenda.
**Criterios de Aceptación:**
- **Datos:** Cliente_id, Servicio_id, Fecha_Hora_Inicio.
- **Validaciones:** El horario no puede estar en el pasado ni colisionar con un turno existente.
- **Comportamiento:** Calcula el fin del turno sumando la duración del servicio seleccionado.

### HU-TUR-03: Visualización de agenda
**Estimación:** 8 SP
**Como** barbero, **necesito** visualizar la agenda diaria y semanal, **para** conocer mis próximos compromisos.
**Criterios de Aceptación:**
- **Datos:** Rango de fechas.
- **Validaciones:** Solo muestra turnos dentro del rango operativo (12:00 a 22:00).
- **Comportamiento:** Renderizado de calendario interactivo en React.