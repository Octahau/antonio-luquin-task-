# Configuración de Google OAuth para Task Manager

## Requisitos previos

1. Tener una cuenta de Google
2. Acceso a [Google Cloud Console](https://console.cloud.google.com/)

## Pasos para configurar Google OAuth

### 1. Crear un proyecto en Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Nombra tu proyecto (ej: "Task Manager Auth")

### 2. Habilitar la API de Google+ 

1. En el menú lateral, ve a "APIs y servicios" > "Biblioteca"
2. Busca "Google+ API" y haz clic en ella
3. Haz clic en "Habilitar"

### 3. Configurar OAuth consent screen

1. Ve a "APIs y servicios" > "Pantalla de consentimiento OAuth"
2. Selecciona "Externo" (a menos que tengas una cuenta de Google Workspace)
3. Completa la información requerida:
   - **Nombre de la aplicación**: Task Manager
   - **Email de soporte**: tu email
   - **Email de contacto del desarrollador**: tu email
4. Agrega los siguientes **dominios autorizados**:
   - Para desarrollo: `localhost:5173`
   - Para producción: tu dominio (ej: `tudominio.com`)

### 4. Crear credenciales OAuth 2.0

1. Ve a "APIs y servicios" > "Credenciales"
2. Haz clic en "Crear credenciales" > "OD cliente de OAuth"
3. Selecciona "Aplicación web"
4. Configura los siguientes campos:
   - **Nombre**: Task Manager Web Client
   - **URIs de redirección autorizados**:
     - Para desarrollo: `http://localhost:5173`
     - Para desarrollo backend: `http://localhost:8000/api/auth/google/callback`
     - Para producción: `https://tudominio.com` y `https://tudominio.com/api/auth/google/callback`

### 5. Configurar variables de entorno

#### Backend (.env)

Agrega estas variables al archivo `.env` del backend:

```env
GOOGLE_CLIENT_ID=tu_client_id_aqui
GOOGLE_CLIENT_SECRET=tu_client_secret_aqui
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback
```

#### Frontend (.env)

Crea un archivo `.env` en el directorio `frontend` con:

```env
VITE_GOOGLE_CLIENT_ID=tu_client_id_aqui
```

**Nota**: Usa el mismo `CLIENT_ID` que configuraste en el backend.

### 6. Obtener las credenciales

Después de crear las credenciales OAuth 2.0, Google te proporcionará:

- **Client ID**: Algo como `123456789-abcdefg.apps.googleusercontent.com`
- **Client Secret**: Una cadena larga de caracteres

### 7. Configuración adicional

#### Para desarrollo local

Asegúrate de que tu frontend esté corriendo en `http://localhost:5173` y tu backend en `http://localhost:8000`.

#### Para producción

1. Actualiza las URIs de redirección en Google Cloud Console
2. Actualiza las variables de entorno con los URLs de producción
3. Asegúrate de que tu dominio esté verificado en Google Cloud Console

## Verificación

Una vez configurado correctamente:

1. Al hacer clic en "Continuar con Google" en el login o registro
2. Deberías ver la pantalla de consentimiento de Google
3. Después de autorizar, deberías ser redirigido de vuelta a la aplicación y estar logueado

## Troubleshooting

### Error: "This app isn't verified"

- Esto es normal durante el desarrollo
- Haz clic en "Advanced" y luego "Go to [app name] (unsafe)"
- En producción, necesitarás verificar tu aplicación con Google

### Error: "redirect_uri_mismatch"

- Verifica que las URIs de redirección en Google Cloud Console coincidan exactamente con las URLs de tu aplicación
- Asegúrate de incluir tanto HTTP como HTTPS según corresponda

### Error: "invalid_client"

- Verifica que el `GOOGLE_CLIENT_ID` en las variables de entorno sea correcto
- Asegúrate de que el Client ID corresponda al tipo de aplicación correcto (Web application)

## Seguridad

- **Nunca** expongas tu `CLIENT_SECRET` en el frontend
- Mantén las credenciales seguras y no las incluyas en control de versiones
- Usa variables de entorno para todas las configuraciones sensibles
