# HU-CLI-01 — Autoregistro de cliente
Permite a un cliente nuevo crear su perfil para poder reservar turnos posteriormente, sin necesidad de que el personal lo cargue manualmente.

## Criterios de aceptación (de `product-backlog.md`)
1. El formulario pide nombre, apellido y teléfono/WhatsApp como datos obligatorios.
2. No se permiten dos perfiles con el mismo número de teléfono.
3. Al finalizar el registro, el cliente puede reservar un turno inmediatamente (HU-TUR-02).

## Investigación del código existente
| Artefacto | Ubicación | Propósito / Patrón a reutilizar |
|---|---|---|
| Modelos de usuario | `app/Models/User.php` | El sistema ya maneja usuarios (staff), por lo que crearemos un modelo `Client` completamente separado para evitar mezclar clientes públicos con staff en la misma tabla. |
| FormRequests | `app/Http/Requests/` | Utilizaremos este patrón estándar para validar las reglas obligatorias y de unicidad del lado del servidor. |
| Componentes React | `resources/js/Components/` | Reutilizaremos los componentes genéricos de Input, Label, y Button de Inertia/Breeze (ej. `TextInput.jsx`, `PrimaryButton.jsx`) si existen, para mantener coherencia visual de Tailwind. |

## Cambios Propuestos (Proposed Changes)

### Backend — Migraciones/Modelos
#### [NEW] `database/migrations/xxxx_xx_xx_xxxxxx_create_clients_table.php`
- Campos: `id`, `first_name` (string), `last_name` (string), `phone` (string, unique), `timestamps`.

#### [NEW] `app/Models/Client.php`
- Fillable: `first_name`, `last_name`, `phone`.
- Representará la entidad del cliente público.

### Backend — Actions
#### [NEW] `app/Actions/CreateClientAction.php`
- Encapsula la lógica para persistir el nuevo cliente en la base de datos a partir del payload validado.

### Backend — FormRequests/Controllers
#### [NEW] `app/Http/Requests/StoreClientRequest.php`
- Reglas: `first_name` (required, string, max:255), `last_name` (required, string, max:255), `phone` (required, string, unique:clients,phone).

#### [NEW] `app/Http/Controllers/ClientRegistrationController.php`
- `create()`: Retorna `Inertia::render('Clients/Register')`.
- `store(StoreClientRequest $request, CreateClientAction $action)`: Delega a la action. Como el cliente debe poder reservar un turno inmediatamente, guardaremos su ID en la sesión (`session(['client_id' => $client->id])`) para identificarlo en la futura historia de reserva.

### Rutas
#### [MODIFY] `routes/web.php`
- `GET /registro-cliente` -> `ClientRegistrationController@create`
- `POST /registro-cliente` -> `ClientRegistrationController@store`

### Frontend — Páginas/Componentes React
#### [NEW] `resources/js/Pages/Clients/Register.jsx`
- Formulario con campos de Nombre, Apellido y Teléfono.
- Manejo de estado con `useForm` de Inertia para mostrar los errores de validación de backend (ej. si el teléfono ya está en uso).
- Botón para completar el registro.

### Tests
#### [NEW] `tests/Feature/ClientRegistrationTest.php`
- Verifica que se renderiza el formulario.
- Verifica que el registro falla con datos inválidos o teléfono duplicado.
- Verifica la creación correcta en BD y que la variable `client_id` quede en sesión.

## Verificación de la Definition of Done (DoD)
| Criterio de la DoD del sprint | Cómo se verificará en esta HU |
|---|---|
| Criterios de aceptación verificados | Se probará manual y automatizadamente la creación y restricción por teléfono duplicado. |
| Validaciones aplicadas del lado del servidor | Se usará `StoreClientRequest` verificando `unique:clients,phone`. |
| Write encapsulado en un Action | `CreateClientAction` será el responsable de persistir el modelo. |

## Preguntas Abiertas (Open Questions)
1. **Formato de Teléfono:** ¿Deseas aplicar alguna validación estricta por expresión regular para el teléfono (ej. que solo contenga números o un largo mínimo) o dejamos que sea un string libre por ahora?
2. **Redirección:** La HU dice "Al finalizar el registro, el cliente puede reservar un turno inmediatamente (HU-TUR-02)". Como HU-TUR-02 no está implementada todavía, ¿estás de acuerdo con redirigir temporalmente a la home (`/`) con un mensaje flash ("Registro exitoso")?
