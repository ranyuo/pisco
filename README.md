# Portal IESTP Pisco

Archivos para publicar en Hostinger:

- `portal-private/`: código PHP, vistas y datos. Va **fuera** de `public_html`, al mismo nivel.
- `public_html/`: raíz pública del dominio, con la página, la intranet, el CSS, el JavaScript y las imágenes.

El archivo `portal-private/config.php` (datos de la base de datos y clave de instalación) **no se guarda en GitHub**. Se crea a partir de `portal-private/config.example.php`.

Requiere PHP 8.2 o posterior con PDO MySQL, sesiones, Fileinfo y Zlib, y MySQL/MariaDB. No necesita Composer ni Node.

## Instalación nueva

1. **Base de datos:** en hPanel › *Bases de datos › Bases de datos MySQL*, crea una base y un usuario con su contraseña. Anota los tres datos.
2. **Descarga el código:** en GitHub, *Code › Download ZIP*.
3. **Sube y extrae:** con el *Administrador de archivos*, sube el ZIP a la carpeta del dominio (la que contiene `public_html`) y extráelo. GitHub mete todo en una carpeta `pisco-main/`; mueve `portal-private/` y `public_html/` desde ahí a la carpeta del dominio y reemplaza el `public_html` vacío.
4. **Configura:** copia `portal-private/config.example.php` como `portal-private/config.php` y completa:
   - `db_name`, `db_user` y `db_password`;
   - `install_key`: una clave privada aleatoria de 32 caracteres o más. Guárdala.
5. **Instala:** abre `https://tu-dominio/install.php`, escribe la `install_key` y crea la cuenta del administrador principal.
6. **Borra** `public_html/install.php`. Después de instalar ya no sirve; si lo vuelves a abrir indica que el portal ya está instalado.
7. **Permisos:** `public_html/assets/cache/` debe tener permisos **755**.
8. **Comprueba:**
   - Abre el sitio y entra a la intranet con la cuenta creada.
   - Revisa *Configuración*: datos institucionales y logo.
   - En *Mesa de partes › Áreas receptoras › Editar áreas*, carga las áreas reales con sus correos.
   - En *Usuarios y roles*, crea las cuentas del personal.
   - Publica desde la intranet el TUPA, los documentos de gestión y los reconocimientos oficiales de Pisco: el paquete inicial no los incluye.

**Opcional, respaldos automáticos:** en hPanel › *Avanzado › Trabajos cron*, crea una tarea diaria con `php /home/<usuario>/domains/<dominio>/portal-private/cron.php`. Luego actívala en la intranet, en *Copias de seguridad*.

## Actualizar una instalación existente

Respalda antes los archivos y la base en hPanel.

Sube `portal-private/` y `public_html/` encima de las carpetas actuales y acepta **sobrescribir**. **No borres las carpetas antes:** así se conservan `config.php`, `backups/` e `install.lock`. Después, borra `public_html/install.php`.

Subir archivos no reemplaza el contenido ya guardado en MySQL. Si la base aún tiene registros heredados de Chincha (TUPA, documentos, reconocimientos, becarios o los programas en borrador), elimínalos desde la intranet.

Borra también del servidor `public_html/actualizar.php`, `public_html/recuperar.php` y la carpeta `public_html/assets/chincha/` si siguen allí: ya no forman parte del sistema.

## Dónde se edita

- **CSS y JavaScript del portal:** `public_html/assets/app/` (`portal.css` y `portal-1.js` a `portal-4.js`).
- **Armazón de la página:** `portal-private/views/portal.php`.
- **Contenido inicial** que carga la instalación: `portal-private/data/portal.json`.
- **Imágenes:** `public_html/assets/pisco/`. Cada imagen grande tiene una copia `.webp` que el servidor entrega automáticamente a los navegadores compatibles.

El historial de pruebas, informes y herramientas de desarrollo sigue disponible en el historial de Git (commit `f17a193`).
