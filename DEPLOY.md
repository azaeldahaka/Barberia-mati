# Guia de Despliegue — Barberia de Matias
# Sistema de Gestion de Turnos (Laravel + React + Inertia)

Guia paso a paso para desplegar la aplicacion en internet usando:
- **Supabase** (base de datos PostgreSQL gratuita — los datos persisten)
- **Render** (servidor web gratuito — despliega con Docker)

---

## ANTES DE EMPEZAR — Lista de advertencias importantes

> **Cold Start (carga lenta el primer acceso del dia):**
> Render en el plan Free "apaga" el servidor si nadie lo usa durante 15 minutos.
> La primera vez que alguien entre despues de ese tiempo, puede tardar hasta 60 segundos
> en responder. Esto es normal, no es un bug.

> **Pausa de la base de datos (Supabase):**
> Si el sistema completo pasa 7 dias sin ninguna conexion, Supabase pausa la base de datos.
> Los datos NO se pierden. Para reactivarla, Matias tiene que entrar a
> https://supabase.com/dashboard/projects, hacer clic en el proyecto, y apretar "Restore".

---

## PASO 1 — Crear la base de datos en Supabase

1. Entra a https://supabase.com y crea una cuenta (podes usar GitHub o email).
2. Haz clic en **"New project"**.
3. Completa:
   - **Organization:** la que Supabase crea automaticamente con tu cuenta.
   - **Name:** `barberia-matias` (o cualquier nombre descriptivo).
   - **Database Password:** Elige una contrasena segura. **Guardala ahora**, la vas a necesitar despues.
   - **Region:** Elige **"South America (Sao Paulo)"** — es la mas cercana a Argentina disponible.
4. Haz clic en **"Create new project"** y espera ~2 minutos a que Supabase termine de aprovisionarlo.

### Obtener las credenciales de conexion

5. Una vez que el proyecto este listo, ve a **Project Settings** (el icono de engranaje, abajo a la izquierda).
6. Haz clic en **"Database"** en el menu lateral.
7. Baja hasta la seccion **"Connection string"** o **"Connection parameters"**.
8. Selecciona la pestana **"Transaction pooler"** (NO la que dice "Direct connection").
   - *Por que Transaction pooler? Porque el servidor de Render puede reiniciarse en cualquier momento.
     El pooler gestiona las conexiones de forma segura en esos casos.*
9. Copia estos valores (los vas a necesitar en el Paso 3):
   - **Host:** algo como `aws-0-sa-east-1.pooler.supabase.com`
   - **Port:** `6543`
   - **Database name:** `postgres`
   - **User:** algo como `postgres.jdoxifybmevryfughjal` (incluye el punto y el sufijo)
   - **Password:** la que creaste en el paso 3

**Nota:** Si ya tenes el proyecto creado (como el indicado mas arriba), los datos son:
- Host: `aws-0-us-west-2.pooler.supabase.com`
- Port: `6543`
- Database: `postgres`
- User: `postgres.jdoxifybmevryfughjal`

---

## PASO 2 — Preparar el APP_KEY de Laravel

El APP_KEY es una clave secreta que Laravel usa para encriptar sesiones y cookies.

Desde tu maquina local, ejecuta en la terminal:
```
php artisan key:generate --show
```

Copia el resultado (empieza con `base64:`). Lo vas a cargar en Render en el Paso 3.

---

## PASO 3 — Crear el servicio en Render

1. Entra a https://render.com y crea una cuenta (podes usar GitHub).
2. Haz clic en **"New +"** ? **"Web Service"**.
3. Conecta tu cuenta de GitHub y elige el repositorio `Barberia-matias`.
4. Completa la configuracion:
   - **Name:** `barberia-matias`
   - **Branch:** `master`
   - **Runtime:** Docker *(Render detecta el Dockerfile automaticamente)*
   - **Instance Type:** Free

### Cargar las variables de entorno en Render

En la seccion "Environment Variables" del servicio, agrega cada una de estas variables.
No uses comillas alrededor de los valores.

| Variable | Valor |
|---|---|
| `APP_NAME` | `Barberia Matias` |
| `APP_ENV` | `production` |
| `APP_KEY` | *el valor generado en el Paso 2* |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://barberia-matias.onrender.com` *(ajustar si Render usa otro nombre)* |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | `aws-0-us-west-2.pooler.supabase.com` |
| `DB_PORT` | `6543` |
| `DB_DATABASE` | `postgres` |
| `DB_USERNAME` | `postgres.jdoxifybmevryfughjal` |
| `DB_PASSWORD` | *la contrasena de Supabase del Paso 1* |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` |
| `LOG_LEVEL` | `error` |

### Variables de Google OAuth (login con Google)

| Variable | Valor |
|---|---|
| `GOOGLE_CLIENT_ID` | *el Client ID de Google Cloud Console* |
| `GOOGLE_CLIENT_SECRET` | *el NUEVO client secret (regenerado — ver mas abajo)* |
| `GOOGLE_REDIRECT_URI` | `https://barberia-matias.onrender.com/auth/google/callback` |

**ATENCION:** El GOOGLE_CLIENT_SECRET que se usaba antes fue comprometido.
Antes de cargar esta variable, debes haber regenerado el secret en Google Cloud Console
(APIs & Services ? Credentials ? tu OAuth Client ? Reset Secret).

### Variables de WhatsApp (dejar vacias si no esta lista la integracion)

| Variable | Valor |
|---|---|
| `WHATSAPP_TOKEN` | *(dejar vacio por ahora)* |
| `WHATSAPP_PHONE_ID` | *(dejar vacio por ahora)* |

5. Haz clic en **"Create Web Service"**.

---

## PASO 4 — Configurar Google Cloud Console

Para que el login con Google funcione en produccion, tenes que agregar la URL de Render
como "Authorized redirect URI" en Google Cloud Console.

1. Ve a https://console.cloud.google.com
2. Menu izquierdo ? APIs & Services ? Credentials
3. Haz clic en tu OAuth Client (`sistema-barberia-matias` o similar)
4. En "Authorized redirect URIs", agrega:
   `https://barberia-matias.onrender.com/auth/google/callback`
5. Haz clic en "Save".

*Nota: Tambien asegurate de agregar la URL en "Authorized JavaScript origins":*
   `https://barberia-matias.onrender.com`

---

## PASO 5 — Monitorear el despliegue

Una vez creado el servicio en Render, el build comienza automaticamente. Podes ver
los logs en tiempo real en el panel de Render. Las etapas son:

1. **"Building Docker image"** — instala Node, compila React/Vite, instala PHP/Composer
   *(puede tardar 5-10 minutos la primera vez)*
2. **"Deploying"** — sube el contenedor
3. **Primer arranque** — corre `php artisan migrate --force` y cachea la configuracion

Si algo falla, el mensaje de error aparece en los logs con fondo rojo.

---

## PASO 6 — QA post-despliegue

Una vez que Render marque el estado como **"Live"**:

1. Entra a la URL publica del servicio.
2. Proba el flujo completo de autoregistro de cliente.
3. Reserva un turno y verificalo en el dashboard de staff.
4. **Verificacion de timezone:** El turno debe aparecer con la hora exacta que reservaste,
   sin diferencias de horas. Si hay desfasaje, avisarme — hay una correccion especifica.
5. Proba el login con Google OAuth (necesita el secret regenerado y la URL configurada).

---

## Datos de los paneles

- **Supabase:** https://supabase.com/dashboard/projects
- **Render:** https://dashboard.render.com

---

## Rotacion de credenciales (para proximas veces)

Si en el futuro hay que rotar el client_secret de Google:
1. Google Cloud Console ? APIs & Services ? Credentials ? OAuth Client ? Reset Secret
2. Copiar el nuevo secret
3. En Render: Environment ? editar la variable GOOGLE_CLIENT_SECRET ? guardar
4. Render redespliega automaticamente

Si hay que cambiar la contrasena de Supabase:
1. Supabase ? Project Settings ? Database ? Reset database password
2. Actualizar DB_PASSWORD en Render
