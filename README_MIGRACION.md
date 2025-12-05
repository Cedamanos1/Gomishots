# GOMISHOTS 2.0 - Migración de CSV a Base de Datos SQL

## 📋 Descripción
Este documento explica cómo migrar el sistema GomiShots de archivos CSV a base de datos SQL (MySQL o PostgreSQL).

## 🗂️ Archivos Actualizados

### Archivos PHP Modificados (ahora usan Base de Datos):
1. **bd.php** - Conexión a la base de datos (compatible con MySQL y PostgreSQL)
2. **login.php** - Sistema de autenticación con base de datos
3. **logout.php** - Registro de cierre de sesión en logs
4. **ingreso.php** - Gestión de ingresos con base de datos
5. **procesar_ingreso.php** - Procesamiento de ingresos
6. **modificar_i.php** - Modificación de ingresos con auditoría
7. **salidas.php** - Gestión de salidas con validación de stock
8. **almacen.php** - Visualización de stock desde base de datos
9. **dashboard.php** - Dashboard con gráficos (Chart.js)
10. **exportar_excel.php** - Exportación a Excel
11. **exportar_pdf.php** - Exportación a PDF
12. **generar_reporte_ingresos.php** - Generación de reportes CSV

### Archivos Nuevos:
- **schema.sql** - Esquema completo de la base de datos
- **migrar_datos.php** - Script de migración de CSV a SQL
- **README_MIGRACION.md** - Este archivo

## 🚀 Pasos de Instalación

### 1. Configurar Variables de Entorno

Crea un archivo `.env` o configura las variables de entorno:

```bash
# Para MySQL
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=gomishots
DB_USER=root
DB_PASSWORD=tu_password

# Para PostgreSQL (ej: Neon)
DB_CONNECTION=pgsql
DB_HOST=ep-xxxxx.region.aws.neon.tech
DB_PORT=5432
DB_NAME=gomishots
DB_USER=usuario
DB_PASSWORD=password
DB_SSLMODE=require
```

### 2. Crear la Base de Datos

#### Opción A: MySQL
```bash
mysql -u root -p
CREATE DATABASE gomishots CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gomishots;
SOURCE schema.sql;
```

#### Opción B: PostgreSQL / Neon
```bash
psql -h host -U usuario -d gomishots -f schema.sql
```

O desde la consola web de Neon, copia y pega el contenido de `schema.sql`.

### 3. Verificar Conexión

Crea un archivo temporal `test_conexion.php`:

```php
<?php
require 'bd.php';
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Conexión exitosa a la base de datos!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
```

Ejecuta: `php test_conexion.php`

### 4. Migrar Datos CSV (Opcional)

Si tienes datos existentes en CSV:

```bash
php migrar_datos.php
```

Este script:
- Lee los archivos `data/ingresos.csv` y `data/salidas.csv`
- Inserta los datos en las tablas correspondientes
- Mantiene los archivos CSV como respaldo
- Genera un log de la migración

### 5. Crear Usuario Administrador

Si no ejecutaste `schema.sql`, crea un usuario manualmente:

```php
<?php
require 'bd.php';

$username = 'admin';
$password = 'admin123'; // Cambia esto
$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash, rol) 
                       VALUES (:username, :password_hash, 'administrador')");
$stmt->execute([
    ':username' => $username,
    ':password_hash' => $password_hash
]);

echo "✅ Usuario creado: $username\n";
?>
```

### 6. Reemplazar Archivos PHP

Copia los archivos PHP actualizados al directorio de tu proyecto:

```bash
cp login.php logout.php ingreso.php procesar_ingreso.php modificar_i.php salidas.php almacen.php dashboard.php exportar_excel.php exportar_pdf.php generar_reporte_ingresos.php /ruta/a/tu/proyecto/
```

### 7. Configurar Permisos (Linux/Mac)

```bash
chmod 644 *.php
chmod 755 data/
```

## 📊 Estructura de la Base de Datos

### Tablas Principales:
- **usuarios** - Usuarios del sistema
- **productos** - Catálogo de productos
- **ingresos** - Registro de ingresos
- **salidas** - Registro de salidas
- **modificaciones** - Auditoría de cambios
- **logs_sistema** - Logs de eventos

## 🔐 Seguridad

### Mejoras de Seguridad Implementadas:
1. ✅ Contraseñas hasheadas con `password_hash()`
2. ✅ Protección CSRF con tokens
3. ✅ Prepared statements (previene SQL Injection)
4. ✅ Validación de datos de entrada
5. ✅ Sanitización con `htmlspecialchars()`
6. ✅ Registro de auditoría en tabla `modificaciones`
7. ✅ Logs de sistema en base de datos

## 📈 Ventajas de la Migración

### Antes (CSV):
- ❌ Riesgo de corrupción de archivos
- ❌ Sin control de concurrencia
- ❌ Búsquedas lentas
- ❌ Sin integridad referencial
- ❌ Backups manuales

### Después (SQL):
- ✅ Integridad de datos garantizada
- ✅ Control de transacciones
- ✅ Búsquedas rápidas con índices
- ✅ Relaciones entre tablas
- ✅ Backups automáticos
- ✅ Escalabilidad
- ✅ Auditoría completa

## 🧪 Pruebas

### 1. Probar Login
- Usuario: `admin`
- Contraseña: `admin123` (o la que configuraste)

### 2. Probar Funcionalidades
- [ ] Registrar ingreso
- [ ] Modificar ingreso
- [ ] Registrar salida
- [ ] Ver almacén
- [ ] Ver dashboard
- [ ] Exportar Excel
- [ ] Exportar PDF
- [ ] Generar reporte CSV

## 🐛 Solución de Problemas

### Error: "Access denied for user"
```bash
# Verificar credenciales en bd.php o variables de entorno
# Otorgar permisos al usuario:
GRANT ALL PRIVILEGES ON gomishots.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Error: "Table doesn't exist"
```bash
# Ejecutar el schema.sql nuevamente
mysql -u root -p gomishots < schema.sql
```

### Error: "Call to undefined function password_hash()"
```bash
# Actualizar PHP a versión 5.5 o superior
php -v
```

### PostgreSQL: DATE_FORMAT no existe
El `schema.sql` está preparado para MySQL. Para PostgreSQL, usa:
```sql
-- Reemplazar DATE_FORMAT por TO_CHAR
TO_CHAR(fecha, 'YYYY-MM') as mes
```

## 📝 Notas Adicionales

1. **Backup**: Antes de migrar, respalda tus archivos CSV
2. **Producción**: Cambia las contraseñas por defecto
3. **SSL**: En producción, usa conexiones SSL a la base de datos
4. **Performance**: Los índices están configurados para optimizar consultas frecuentes
5. **Logs**: Revisa regularmente la tabla `logs_sistema`

## 🔄 Rollback (Volver a CSV)

Si necesitas volver al sistema CSV:
1. Restaura los archivos PHP originales
2. Los datos están respaldados en la base de datos
3. Puedes exportar de SQL a CSV si es necesario

## 📞 Soporte

Para dudas o problemas:
1. Revisa los logs de error de PHP
2. Revisa la tabla `logs_sistema`
3. Verifica la configuración de `bd.php`

## ✅ Checklist de Migración

- [ ] Variables de entorno configuradas
- [ ] Base de datos creada
- [ ] Schema.sql ejecutado
- [ ] Conexión verificada
- [ ] Datos CSV migrados (si aplica)
- [ ] Usuario administrador creado
- [ ] Archivos PHP reemplazados
- [ ] Permisos configurados
- [ ] Login funcional
- [ ] Todas las funcionalidades probadas

---

**¡Migración completada! 🎉**

El sistema ahora usa base de datos SQL para mayor robustez, seguridad y escalabilidad.
