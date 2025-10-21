# 📋 Task Manager - Sistema de Gestión de Tareas

Una aplicación web moderna de gestión de tareas desarrollada con **Laravel** y **Vue.js 3**, que incluye autenticación, control de acceso por roles y una interfaz responsive.

## 🚀 Características

- **Autenticación completa** con registro e inicio de sesión
- **Control de acceso por roles** (admin, editor, viewer)
- **CRUD de tareas** con permisos según el rol del usuario
- **Filtros por estado** (pendiente, en progreso, completada)
- **Interfaz moderna y responsive** con animaciones
- **Notificaciones toast** para feedback del usuario
- **API REST** completamente funcional

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 11** - Framework PHP
- **Laravel Sanctum** - Autenticación API
- **Spatie Laravel Permission** - Gestión de roles y permisos
- **MYSQL** - Base de datos (configurable)

### Frontend
- **Vue.js 3** - Framework JavaScript
- **Vue Router** - Enrutamiento
- **Pinia** - Gestión de estado
- **Axios** - Cliente HTTP
- **CSS3** - Estilos con glassmorphism

## 📁 Estructura del Proyecto

```
Task-AL/
├── backend/                 # API Laravel
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   └── Models/
│   ├── database/migrations/
│   ├── database/seeders/
│   └── routes/api.php
├── frontend/               # Aplicación Vue.js
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── stores/
│   │   └── services/
└── README.md
```

## 🔧 Instalación y Configuración

### Prerrequisitos

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 16.0
- **NPM** >= 8.0

### 1. Clonar el Repositorio

```bash
git clone https://github.com/Octahau/antonio-luquin-task-.git
cd Task-AL
```

### 2. Configurar Backend (Laravel)

```bash
# Navegar al directorio backend
cd backend

# Instalar dependencias de PHP
composer install

# Crear archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar base de datos (opcional - por defecto usa SQLite)
# Editar .env si quieres usar MySQL/PostgreSQL
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# Ejecutar migraciones
php artisan migrate

# Crear y poblar roles y permisos
php artisan db:seed --class=RoleSeeder

# Iniciar servidor de desarrollo
php artisan serve
```

El backend estará disponible en: `http://localhost:8000`

### 3. Configurar Frontend (Vue.js)

```bash
# En una nueva terminal, navegar al directorio frontend
cd frontend

# Instalar dependencias de Node.js
npm install

# Iniciar servidor de desarrollo
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`

## 👥 Usuarios de Prueba

Después de ejecutar las migraciones y seeders, puedes crear usuarios de prueba:

### Crear Usuario Admin
```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'name' => 'Administrador',
    'email' => 'admin@test.com',
    'password' => Hash::make('password'),
]);
$admin->assignRole('admin');

# Salir de tinker
exit
```

### Crear Usuario Editor
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$editor = User::create([
    'name' => 'Editor',
    'email' => 'editor@test.com',
    'password' => Hash::make('password'),
]);
$editor->assignRole('editor');
```

### Crear Usuario Viewer
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$viewer = User::create([
    'name' => 'Visualizador',
    'email' => 'viewer@test.com',
    'password' => Hash::make('password'),
]);
$viewer->assignRole('viewer');
```

**Credenciales de prueba:**
- **Admin**: `admin@test.com` / `password`
- **Editor**: `editor@test.com` / `password`
- **Viewer**: `viewer@test.com` / `password`

## 🎯 Roles y Permisos

### Admin
- ✅ Ver **todas** las tareas del sistema
- ✅ Crear, editar y eliminar **cualquier** tarea
- ✅ Asignar tareas a otros usuarios
- ✅ Acceso completo al sistema

### Editor
- ✅ Ver, crear, editar y eliminar **solo sus propias** tareas
- ❌ No puede ver tareas de otros usuarios
- ❌ No puede asignar tareas a otros

### Viewer
- ✅ Ver **solo sus propias** tareas
- ❌ No puede crear, editar ni eliminar tareas
- ❌ Acceso de solo lectura

## 🔌 API Endpoints

### Autenticación
- `POST /api/register` - Registrar usuario
- `POST /api/login` - Iniciar sesión
- `POST /api/logout` - Cerrar sesión
- `GET /api/user` - Obtener usuario actual
- `GET /api/users` - Listar usuarios (solo admin)

### Tareas
- `GET /api/tasks` - Listar tareas
- `GET /api/tasks/{id}` - Obtener tarea específica
- `POST /api/tasks` - Crear tarea
- `PUT /api/tasks/{id}` - Actualizar tarea
- `DELETE /api/tasks/{id}` - Eliminar tarea

### Parámetros de Query
- `GET /api/tasks?status=pending` - Filtrar por estado

## 🎨 Características de la Interfaz

- **Diseño glassmorphism** con efectos de blur y transparencias
- **Animaciones suaves** en todas las interacciones
- **Notificaciones toast** para feedback
- **Responsive design** compatible con móviles
- **Modales elegantes** para formularios
- **Filtros intuitivos** por estado de tarea

## 🚀 Ejecutar en Producción

### Backend
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend
npm run build
```

## 🐛 Solución de Problemas

### Error 419 (CSRF Token Mismatch)
Si encuentras este error, verifica que:
1. El middleware CSRF esté configurado correctamente
2. Los headers `X-Requested-With` estén incluidos
3. Las cookies estén habilitadas en el navegador

### Problemas de CORS
Asegúrate de que:
1. `APP_URL` en `.env` sea correcto
2. `sanctum.stateful` esté configurado para tu dominio
3. Las rutas estén definidas en `routes/api.php`

### Base de Datos
Para recrear la base de datos:
```bash
php artisan migrate:fresh --seed
```

## 📝 Desarrollo

### Estructura de Base de Datos

**Tabla Users:**
- `id`, `name`, `email`, `password`, `timestamps`

**Tabla Tasks:**
- `id`, `title`, `description`, `status`, `due_date`, `user_id`, `timestamps`

**Tabla de Roles:**
- `admin`, `editor`, `viewer`

### Estados de Tarea
- `pending` - Pendiente
- `in_progress` - En Progreso
- `completed` - Completada

## 📄 Licencia

Este proyecto es parte de un ejercicio técnico de desarrollo full-stack.

---
