# 🚀 GUÍA RÁPIDA DE INICIO - GOMISHOTS 2.0

## ⚡ Inicio Rápido (15 minutos)

### Paso 1: Crear la Base de Datos (3 min)

```bash
# MySQL
mysql -u root -p
CREATE DATABASE gomishots;
USE gomishots;
SOURCE schema.sql;
EXIT;
```

### Paso 2: Configurar Conexión (2 min)

Edita `bd.php` o configura variables de entorno:

```php
// En bd.php, líneas 2-6:
$host     = 'localhost';        // Tu servidor
$dbname   = 'gomishots';        // Nombre de tu BD
$user     = 'root';             // Tu usuario
$pass     = 'tu_password';      // Tu contraseña
```

### Paso 3: Subir Archivos (2 min)

Reemplaza estos archivos en tu servidor:
```
✓ login.php
✓ logout.php
✓ ingreso.php
✓ procesar_ingreso.php
✓ modificar_i.php
✓ salidas.php
✓ almacen.php
✓ dashboard.php
✓ exportar_excel.php
✓ exportar_pdf.php
✓ generar_reporte_ingresos.php
```

### Paso 4: Crear Usuario (3 min)

**Opción A: Usuario por defecto (ya incluido en schema.sql)**
```
Usuario: admin
Contraseña: admin123
```

**Opción B: Crear tu propio usuario**
```bash
php crear_usuario.php
```

### Paso 5: Migrar Datos CSV (opcional, 3 min)

Si tienes datos existentes en CSV:
```bash
php migrar_datos.php
```

### Paso 6: Probar (2 min)

1. Abre tu navegador
2. Navega a `http://tu-servidor/login.php`
3. Inicia sesión con:
   - Usuario: `admin`
   - Contraseña: `admin123`
4. ¡Listo! 🎉

---

## 🔧 Configuración Avanzada

### Para MySQL:
```php
$driver = 'mysql';
$host = 'localhost';
$port = 3306;
```

### Para PostgreSQL (Neon, Supabase, etc):
```php
$driver = 'pgsql';
$host = 'ep-xxxxx.region.aws.neon.tech';
$port = 5432;
$sslmode = 'require';
```

---

## ✅ Verificar Instalación

### Test 1: Conexión a BD
```bash
php -r "require 'bd.php'; var_dump($pdo);"
# Debe mostrar: object(PDO)#1
```

### Test 2: Login
- Abre `login.php`
- Ingresa credenciales
- Debe redirigir a `inventario.php`

### Test 3: Funcionalidades
- [ ] Registrar ingreso
- [ ] Modificar ingreso  
- [ ] Registrar salida
- [ ] Ver almacén
- [ ] Ver dashboard
- [ ] Exportar Excel/PDF

---

## 🚨 Problemas Comunes

### "Access denied"
```sql
GRANT ALL PRIVILEGES ON gomishots.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### "Table doesn't exist"
```bash
mysql -u root -p gomishots < schema.sql
```

### "Could not connect"
- Verifica que MySQL/PostgreSQL esté corriendo
- Verifica credenciales en `bd.php`
- Verifica que la base de datos existe

---

## 📋 Archivos Entregados

### Esenciales:
- ✅ `schema.sql` - Estructura de la base de datos
- ✅ 11 archivos PHP actualizados
- ✅ `migrar_datos.php` - Script de migración
- ✅ `crear_usuario.php` - Crear usuarios

### Documentación:
- ✅ `README_MIGRACION.md` - Guía completa
- ✅ `RESUMEN_CAMBIOS.md` - Lista de cambios
- ✅ `INICIO_RAPIDO.md` - Esta guía

---

## 🎯 Siguiente Paso

### En Desarrollo:
Cambiar contraseña del admin:
```bash
php crear_usuario.php
# Crea un nuevo admin con tu contraseña
```

### En Producción:
1. Cambiar contraseñas
2. Configurar SSL en la BD
3. Configurar backups automáticos
4. Revisar permisos de archivos

---

## 💡 Características Nuevas

✅ Autenticación segura con hashing
✅ Auditoría completa de cambios
✅ Logs de sistema
✅ Múltiples usuarios con roles
✅ Validación de datos mejorada
✅ Protección CSRF
✅ Prevención de SQL Injection
✅ Dashboard con gráficos
✅ Exportación optimizada

---

## 📞 ¿Necesitas Ayuda?

1. Lee `README_MIGRACION.md` para más detalles
2. Revisa la tabla `logs_sistema` en la BD
3. Revisa los logs de PHP

---

**¡Sistema listo para usar!** 🚀

Tu aplicación ahora usa base de datos SQL y está lista para escalar.
