# Sistema de Ficha de Vida del Bombero
## Cuerpo de Bomberos de Viña del Mar

Sistema integral para la gestión de fichas de vida de bomberos, que permite llevar un registro completo de cada voluntario desde su ingreso hasta su retiro.

---

## CARACTERÍSTICAS PRINCIPALES

### Módulos del Sistema:

1. **Datos Personales**
   - Información básica de filiación
   - Datos de contacto
   - Contactos de emergencia
   - Información médica básica

2. **Historial Institucional**
   - Fecha de ingreso
   - Historial de grados y promociones
   - Cargos desempeñados
   - Transferencias entre compañías

3. **Capacitación**
   - Cursos realizados
   - Certificaciones vigentes
   - Especializaciones
   - Instructorías

4. **Registro Disciplinario**
   - Amonestaciones (verbales/escritas)
   - Sanciones
   - Suspensiones
   - Estado de cumplimiento

5. **Reconocimientos**
   - Condecoraciones
   - Menciones honrosas
   - Notas de mérito
   - Actos de valor

6. **Servicios Operacionales**
   - Registro de servicios atendidos
   - Horas de servicio acumuladas
   - Tipos de emergencias
   - Estadísticas operacionales

7. **Equipamiento**
   - Equipo personal asignado
   - Equipos especiales
   - Estado de equipos
   - Historial de mantención

8. **Evaluaciones de Desempeño**
   - Evaluaciones periódicas
   - Puntajes por área
   - Planes de mejora
   - Observaciones

---

## REQUISITOS DEL SISTEMA

### Servidor:
- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.3 o superior
- Apache 2.4 o superior (con mod_rewrite)
- Espacio en disco: Mínimo 500MB (para base de datos y archivos)

### Extensiones PHP requeridas:
- PDO
- PDO_MySQL
- GD (para manejo de imágenes)
- mbstring
- fileinfo

---

## INSTALACIÓN

### 1. Copiar archivos al servidor

Copie todos los archivos del sistema a su directorio web (ej: /var/www/html/ficha_bombero/)

```bash
cd /var/www/html/
mkdir ficha_bombero
# Copiar todos los archivos a esta carpeta
```

### 2. Configurar permisos

```bash
cd /var/www/html/ficha_bombero
chmod -R 755 .
chmod -R 777 uploads/
mkdir logs
chmod 777 logs/
```

### 3. Crear la base de datos

```bash
mysql -u root -p
```

```sql
CREATE DATABASE ficha_bombero CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ficha_user'@'localhost' IDENTIFIED BY 'contraseña_segura';
GRANT ALL PRIVILEGES ON ficha_bombero.* TO 'ficha_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Importar el esquema de base de datos

```bash
mysql -u root -p ficha_bombero < sql/database.sql
```

### 5. Configurar conexión a base de datos

Edite el archivo `includes/config.php` y ajuste los parámetros:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'ficha_user');
define('DB_PASS', 'contraseña_segura');
define('DB_NAME', 'ficha_bombero');
```

También ajuste la URL del sitio:

```php
define('SITE_URL', 'http://su-dominio.cl/ficha_bombero');
```

### 6. Acceder al sistema

Abra su navegador y acceda a:

```
http://su-dominio.cl/ficha_bombero/
```

**Credenciales iniciales:**
- Usuario: `admin`
- Contraseña: `admin123`

**¡IMPORTANTE!** Cambie la contraseña del administrador inmediatamente después del primer acceso.

---

## CONFIGURACIÓN ADICIONAL

### Tamaño máximo de archivos

Para permitir archivos más grandes, edite su `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### Backup automático (recomendado)

Cree un script de backup diario:

```bash
nano /usr/local/bin/backup_ficha_bombero.sh
```

```bash
#!/bin/bash
FECHA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/ficha_bombero"
mkdir -p $BACKUP_DIR

# Backup base de datos
mysqldump -u ficha_user -pcontraseña_segura ficha_bombero | gzip > $BACKUP_DIR/db_$FECHA.sql.gz

# Backup archivos
tar -czf $BACKUP_DIR/files_$FECHA.tar.gz /var/www/html/ficha_bombero/uploads/

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
```

```bash
chmod +x /usr/local/bin/backup_ficha_bombero.sh
```

Agregue al crontab para ejecución diaria a las 2:00 AM:

```bash
crontab -e
```

```
0 2 * * * /usr/local/bin/backup_ficha_bombero.sh
```

---

## ESTRUCTURA DE DIRECTORIOS

```
ficha_bombero/
├── css/
│   └── style.css           # Estilos principales
├── includes/
│   ├── config.php          # Configuración de BD
│   ├── auth.php            # Sistema de autenticación
│   ├── header.php          # Header incluible
│   └── nav.php             # Navegación incluible
├── js/
│   └── main.js             # JavaScript principal
├── sql/
│   └── database.sql        # Esquema de base de datos
├── uploads/
│   ├── fotos/              # Fotos de bomberos
│   └── documentos/         # Documentos adjuntos
├── logs/                   # Logs del sistema
├── index.php               # Dashboard principal
├── login.php               # Página de login
├── logout.php              # Cerrar sesión
├── bomberos.php            # Listado de bomberos
├── ficha.php               # Ficha completa del bombero
└── README.md               # Este archivo
```

---

## NIVELES DE USUARIO

El sistema cuenta con 3 niveles de acceso:

1. **Consulta**: Solo puede ver información
2. **Oficial**: Puede ver y editar información
3. **Administrador**: Acceso completo, incluyendo gestión de usuarios

---

## SEGURIDAD

### Recomendaciones:

1. **Cambiar contraseñas por defecto** inmediatamente
2. **Usar HTTPS** en producción
3. **Backup regular** de base de datos y archivos
4. **Actualizar PHP y MySQL** regularmente
5. **Revisar logs** periódicamente
6. **Limitar acceso** por IP si es posible

### Cambiar contraseña de usuario:

```sql
UPDATE usuarios 
SET password = '$2y$10$nuevo_hash_de_password' 
WHERE username = 'admin';
```

Para generar un hash de password en PHP:

```php
<?php
echo password_hash('nueva_contraseña', PASSWORD_DEFAULT);
?>
```

---

## MANTENIMIENTO

### Limpiar logs antiguos (más de 90 días):

```sql
DELETE FROM auditoria WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Optimizar base de datos:

```sql
OPTIMIZE TABLE bomberos;
OPTIMIZE TABLE servicios;
OPTIMIZE TABLE cursos;
OPTIMIZE TABLE certificaciones;
```

---

## SOPORTE Y CONTACTO

Para soporte técnico o consultas sobre el sistema:

**Desarrollado para:**
Cuerpo de Bomberos de Viña del Mar
Inspectoría General de Comunicaciones

**Contacto:**
Antonio - Inspector General de Comunicaciones
Email: [email corporativo]

---

## LICENCIA

Este sistema es de uso exclusivo del Cuerpo de Bomberos de Viña del Mar.
Prohibida su reproducción o distribución sin autorización.

---

## REGISTRO DE CAMBIOS

### Versión 1.0.0 (Febrero 2026)
- Versión inicial del sistema
- Módulos completos de gestión
- Sistema de autenticación
- Dashboard con estadísticas
- Exportación e impresión de fichas
- Sistema de auditoría

---

## PRÓXIMAS MEJORAS PLANIFICADAS

- [ ] Generación de reportes en PDF
- [ ] Exportación a Excel
- [ ] Gráficos estadísticos avanzados
- [ ] Notificaciones automáticas por email
- [ ] Integración con sistema de emergencias
- [ ] App móvil complementaria
- [ ] API REST para integraciones

---

**Desarrollado con dedicación para servir mejor a quienes sirven a la comunidad.**

*Cuerpo de Bomberos de Viña del Mar - Chile*
