# HU-SEG-02 — Modelo Extensible de Roles
Diseñar la base de datos con soporte RBAC (Role-Based Access Control) para facilitar la incorporación de personal futuro, estableciendo las tablas de roles y su relación con los usuarios.

## Criterios de aceptación (de `product-backlog.md`)
1. Tablas de `users`, `roles`, y `role_user` (o usar Spatie Permission).
2. Ningún usuario puede quedar sin rol. 
3. El sistema asume el rol "Dueño" por defecto para la v1.

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Migración de usuarios | `database/migrations/0001_01_01_000000_create_users_table.php` | Servirá de referencia para crear las nuevas tablas `roles` y `role_user`. |
| Modelo `User` | `app/Models/User.php` | Aquí agregaremos la relación Many-to-Many (`roles()`) y un helper method para chequear roles. |

## Cambios Propuestos (Proposed Changes)

### Backend — Migraciones/Modelos

#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_create_barberias_table.php`
- Crear la tabla `barberias` (`id`, `nombre`, `timestamps`) según ADR-03.

#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_create_roles_tables.php`
- Crear la tabla `roles` (`id`, `name`, `timestamps`).
- Crear la tabla pivote `role_user` (`role_id`, `user_id` con llaves foráneas).

#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_add_barberia_id_to_users_table.php`
- Añadir el campo `barberia_id` a la tabla `users` (llave foránea nullable para MVP).

#### [NEW] `app/Models/Barberia.php`
- Modelo Eloquent para `Barberia` con `$fillable = ['nombre']`.

#### [NEW] `app/Models/Role.php`
- Modelo Eloquent para `Role` con `$fillable = ['name']`.
- Definir relación `public function users(): BelongsToMany`.

#### [MODIFY] `app/Models/User.php`
- Añadir relación `public function roles(): BelongsToMany` (hacia `Role`).
- Añadir relación `public function barberia(): BelongsTo` (hacia `Barberia`).
- Añadir método `public function hasRole(string $role): bool` para uso futuro.

### Backend — Actions

#### [NEW] `app/Actions/Roles/AssignDefaultRoleToUser.php`
- Action encargado de asegurar que se asigne el rol de "Dueño" y el tenant `Barberia` por defecto a los nuevos usuarios.

### Database — Seeders

#### [NEW] `database/seeders/RoleSeeder.php`
- Creará los roles iniciales ("Dueño" y "Barbero") para la base de datos.

### Tests

#### [NEW] `tests/Feature/Actions/AssignDefaultRoleToUserTest.php`
- Prueba unitaria/de integración para verificar que el Action efectivamente asigne el rol "Dueño".

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Criterios de aceptación verificados | Se correrá `php artisan test` para probar el Action y la base de datos. |
| Validaciones del lado del servidor | N/A en esta etapa (aún no hay forms), pero la tabla `role_user` no admitirá nulos. |
| Writes con lógica encapsulada en Actions | La lógica de "El sistema asume el rol Dueño por defecto" vivirá en el Action `AssignDefaultRoleToUser`. |
| Código en rama `feature/HU-XXX` | Ya he creado la rama `feature/HU-SEG-02-modelo-roles`. |

## Preguntas Abiertas (Open Questions)
> [!IMPORTANT]
> **Estrategia de asignación:**  
> Según los requerimientos, el rol "Dueño" es el default para la v1. Para cumplir con la regla del proyecto de encapsular lógica de negocio en Actions, propongo crear `AssignDefaultRoleToUser`. ¿Estás de acuerdo con esta aproximación o prefieres utilizar un Observer sobre el modelo `User` para que sea completamente automático a nivel base de datos?
