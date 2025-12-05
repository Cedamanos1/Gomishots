# 📁 ESTRUCTURA DE DIRECTORIOS - GOMISHOTS 2.0

## Organización de Archivos

```
tu-proyecto/
│
├── index.html                  ← Página principal (sin cambios)
├── ingresar.html              ← Actualizado para backend SQL
│
├── login.php                  ← Backend SQL (nuevo)
├── logout.php                 ← Backend SQL (nuevo)
├── inventario.php             ← Panel principal (sin cambios en HTML)
├── ingreso.php                ← Backend SQL (nuevo)
├── procesar_ingreso.php       ← Backend SQL (nuevo)
├── modificar_i.php            ← Backend SQL (nuevo)
├── salidas.php                ← Backend SQL (nuevo)
├── almacen.php                ← Backend SQL (nuevo)
├── dashboard.php              ← Backend SQL (nuevo)
├── exportar_excel.php         ← Backend SQL (nuevo)
├── exportar_pdf.php           ← Backend SQL (nuevo)
├── generar_reporte_ingresos.php ← Backend SQL (nuevo)
│
├── bd.php                     ← Conexión a base de datos
├── schema.sql                 ← Esquema de base de datos
├── migrar_datos.php           ← Script de migración (opcional)
├── crear_usuario.php          ← Script para crear usuarios
│
├── styles/                    ← Carpeta de estilos CSS
│   ├── styles.css            ← Estilos página principal
│   ├── ingresar.css          ← Estilos login
│   ├── inventario.css        ← Estilos panel inventario
│   ├── ingreso.css           ← Estilos ingreso/almacén
│   └── salida.css            ← Estilos salidas
│
├── img/                       ← Carpeta de imágenes
│   ├── disco.png
│   ├── oso.png
│   └── ositosparty.png
│
└── data/                      ← Carpeta para CSV antiguos (opcional)
    ├── ingresos.csv          ← Backup
    └── salidas.csv           ← Backup
```

## 📝 Cambios Realizados en Frontend

### 1. ingresar.html - ACTUALIZADO ✅
**Cambios:**
- Campo `name="usuario"` → `name="username"` (para coincidir con login.php)
- Campo `name="contrasena"` → `name="password"` (para coincidir con login.php)
- Actualizado mensaje de credenciales: `admin / admin123`
- Agregado manejo de error `sistema`

**Estilo:** ✅ Sin cambios

### 2. index.html - SIN CAMBIOS ✅
**Estado:** Perfecto, no necesita modificaciones
**Estilo:** ✅ Sin cambios

### 3. inventario.php - SIN CAMBIOS EN HTML ✅
**Estado:** Ya está actualizado en los archivos PHP que te pasé
**Estilo:** ✅ Sin cambios

## 🔗 Conexiones Frontend → Backend

### Flujo de Autenticación:
```
index.html
    ↓ (clic "INGRESAR")
ingresar.html
    ↓ (submit form)
login.php (valida con BD)
    ↓ (si éxito)
inventario.php (panel principal)
```

### Flujo de Navegación:
```
inventario.php
    ├→ ingreso.php (gestión de ingresos)
    ├→ salidas.php (gestión de salidas)
    ├→ almacen.php (ver stock)
    └→ dashboard.php (análisis)
```

## ⚙️ Configuración de Rutas CSS

Los archivos PHP ya tienen las rutas correctas a los CSS:
- `<link rel="stylesheet" href="styles/ingreso.css">`
- `<link rel="stylesheet" href="styles/inventario.css">`
- `<link rel="stylesheet" href="styles/salida.css">`

## 📋 Checklist de Implementación

### Paso 1: Estructura de Directorios
- [ ] Crear carpeta `styles/` en la raíz
- [ ] Mover todos los archivos `.css` a `styles/`
- [ ] Verificar que existe carpeta `img/` con las imágenes

### Paso 2: Archivos Frontend
- [ ] Subir `index.html` (sin cambios)
- [ ] Subir `ingresar.html` (ACTUALIZADO)

### Paso 3: Archivos Backend PHP
- [ ] Subir todos los archivos `.php` actualizados
- [ ] Configurar `bd.php` con credenciales de tu BD

### Paso 4: Base de Datos
- [ ] Ejecutar `schema.sql` en tu base de datos
- [ ] Verificar que se crearon las 6 tablas

### Paso 5: Verificación
- [ ] Acceder a `index.html` → debe verse bien ✅
- [ ] Clic en INGRESAR → debe ir a `ingresar.html` ✅
- [ ] Login con admin/admin123 → debe ir a `inventario.php` ✅
- [ ] Verificar que todos los botones funcionan ✅

## 🎨 Archivos CSS - Sin Cambios

Todos los archivos CSS permanecen EXACTAMENTE igual:
- ✅ styles.css (página principal)
- ✅ ingresar.css (login)
- ✅ inventario.css (panel)
- ✅ ingreso.css (ingresos/almacén)
- ✅ salida.css (salidas)

**NO se modificó NINGÚN estilo.**

## 🔧 Archivos de Configuración

### bd.php
Ya está configurado para usar variables de entorno o valores por defecto:
```php
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'gomishots';
$user     = getenv('DB_USER')     ?: 'root';
$pass     = getenv('DB_PASSWORD') ?: '';
```

## 🚀 Despliegue Rápido

### Servidor Local (XAMPP/WAMP/MAMP):
1. Copiar todos los archivos a `htdocs/gomishots/` o `www/gomishots/`
2. Crear base de datos en phpMyAdmin
3. Importar `schema.sql`
4. Acceder a `http://localhost/gomishots/`

### Servidor Linux:
1. Copiar archivos a `/var/www/html/gomishots/`
2. Configurar permisos: `chmod 644 *.php *.html`
3. Crear base de datos: `mysql -u root -p < schema.sql`
4. Acceder a `http://tu-dominio.com/`

## 📱 Responsive

Todos los estilos son 100% responsive:
- ✅ Desktop (1920px+)
- ✅ Laptop (1366px)
- ✅ Tablet (768px)
- ✅ Mobile (480px y menor)

## ⚠️ Notas Importantes

1. **Rutas de Imágenes:** Asegúrate de que la carpeta `img/` contenga:
   - disco.png
   - oso.png
   - ositosparty.png

2. **Rutas CSS:** Todos los CSS deben estar en `styles/`

3. **Credenciales por Defecto:**
   - Usuario: `admin`
   - Contraseña: `admin123`
   - **¡CÁMBIALAS EN PRODUCCIÓN!**

4. **Base de Datos:** 
   - El archivo `bd.php` está configurado para MySQL por defecto
   - También es compatible con PostgreSQL

## 🔗 Enlaces entre Páginas

### Desde index.html:
- "Ingresar" → `ingresar.html` ✅

### Desde ingresar.html:
- "Inicio" → `index.html` ✅
- Form submit → `login.php` ✅

### Desde inventario.php:
- "Ingreso" → `ingreso.php` ✅
- "Salidas" → `salidas.php` ✅
- "Almacén" → `almacen.php` ✅
- "Dashboard" → `dashboard.php` ✅
- "Cerrar Sesión" → `logout.php` ✅

Todos los enlaces ya están correctamente configurados en los archivos PHP.

## ✅ Resultado Final

Tu aplicación quedará así:
1. **Frontend:** Exactamente igual visualmente (0 cambios de estilo)
2. **Backend:** 100% SQL con todas las mejoras de seguridad
3. **Integración:** Perfecta conexión entre HTML y PHP

## 🎉 ¡Listo para Usar!

Todos los archivos están listos y conectados. Solo necesitas:
1. Subir archivos al servidor
2. Crear la base de datos
3. Configurar `bd.php`
4. ¡Empezar a usar!
