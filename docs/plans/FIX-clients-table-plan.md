# Fix: Error "no such table: clients"

## Investigación del problema
El error `SQLSTATE[HY000]: General error: 1 no such table: clients` ocurre durante la validación de los datos (específicamente en la regla `unique:clients,phone` dentro del `StoreClientRequest`). 
Esto se debe a que la migración que crea la tabla `clients` fue generada exitosamente, pero no se ha ejecutado en el entorno de desarrollo local. Los tests pasaron previamente porque el trait `RefreshDatabase` de PHPUnit se encarga de ejecutar las migraciones de forma automática en la base de datos de pruebas, pero esto no afecta a la base de datos de desarrollo (`database.sqlite`).

## Cambios Propuestos (Proposed Changes)

### Backend — Migraciones
- No hay que modificar ningún archivo, la migración `2026_09_09_184617_create_clients_table` ya existe y es correcta.

### Acciones a ejecutar
- Ejecutar el comando `php artisan migrate` en la terminal para aplicar las migraciones pendientes en la base de datos `database.sqlite`.

## Verificación
- Tras ejecutar el comando, se debe poder registrar un cliente desde el navegador sin recibir el error 500.

## Preguntas Abiertas (Open Questions)
Ninguna. La solución es directa. ¿Procedo a ejecutar el comando de migración?
