# IESTP Pisco — Portal institucional

Sistema PHP/MySQL con la información primaria del prototipo `index.html` facilitado por el usuario. Se actualizan textos, imágenes, identidad institucional y cinco carreras de Pisco, conservando la estructura, las rutas, los módulos, los permisos y la persistencia del sistema copiado de Chincha.

[Mapeo del prototipo, información conservada y pendientes](MAPEO-PISCO-PROTOTIPO.md) · [Instalación y operación](INSTRUCCIONES.md) · [Descargar main](https://github.com/ranyuo/pisco/archive/refs/heads/main.zip)

## Instalación nueva

Requiere PHP 8.2 o posterior, PDO MySQL, sesiones, Fileinfo, Zlib y MySQL/MariaDB. No necesita Composer ni Node en Hostinger.

1. Sube `portal-private/` junto a `public_html/`, al mismo nivel. Incluye ambas carpetas de recursos: `public_html/assets/pisco/` y `public_html/assets/chincha/`; esta última contiene documentos y complementos conservados. No subas pruebas ni evidencias.
2. Copia `portal-private/config.example.php` a `config.php`. Configura una base vacía y una `install_key` privada aleatoria. No subas credenciales a GitHub.
3. Abre `/install.php` con HTTPS y crea tu administrador. Se carga la información de Pisco sin usuarios, alumnos, notas, empresas, certificados ni trámites ficticios.
4. Revisa el resultado y elimina los archivos de instalación, actualización y recuperación cuando dejen de ser necesarios.

## Instalación existente

Subir archivos no reemplaza el contenido ya guardado en MySQL.

1. Respalda los archivos y la base en hPanel. Conserva `portal-private/config.php` y los respaldos privados.
2. Sube los contenidos actualizados de ambas carpetas, incluidos todos los recursos y `portal-private/data/`.
3. Inicia sesión de administrador en la intranet. Abre `/actualizar.php`, introduce tu `install_key` y marca **«Importar los textos, imágenes y carreras de Pisco desde el prototipo»**.
4. La importación crea un respaldo privado y reemplaza las secciones aportadas por el prototipo. Conserva las secciones ausentes, las cuentas, contraseñas, solicitudes, registros académicos, IDs de programas y unidades didácticas ya asignadas. Los datos públicos que sí se importan reemplazan sus ediciones anteriores: revisa el respaldo antes de aplicarlo.
5. Comprueba el portal y elimina `actualizar.php`. Sin marcar la casilla se conserva la información existente.

Las tarifas y los archivos heredados se conservan como referencias pendientes de validación para Pisco, sin presentar las cuentas bancarias heredadas como instrucciones de pago. Las convocatorias y fechas de ejemplo del prototipo quedan en borrador. El correo saliente mantiene las limitaciones del sistema base.

[Revisión de presentación, resultados y pendientes para producción](REVISION-PRODUCCION.md)

## Validaciones

- `node tests/prototype-content.cjs`: identidad, cinco carreras publicadas, relaciones, ausencia de registros operativos ficticios y 70 imágenes locales con SHA-256.
- `php tests/demo-validation.php`: validación de los registros y relaciones del paquete.
- `php tests/run.php` y `php tests/parity.php`: formularios, sesiones, permisos y flujos del servidor.
- `php tests/chincha-import.php`: nombre histórico del test de importación; ahora valida Pisco, conservación de información y asignaciones anteriores.
- `php tests/demo-installation.php`: instalación en una base de pruebas vacía.
- `tests/browser-prototype.cjs`: páginas públicas, cinco carreras, directorio, intranet, edición, PDF y actualización web.

Las pruebas que modifican datos se ejecutan solo con `config.test.php` y bases `_test`. No se modificó Hostinger. `MAPEO-CHINCHA.md` documenta la procedencia histórica del sistema y los archivos complementarios, no la fuente primaria actual de Pisco.

## Regresión de las correcciones funcionales

En una base local exclusiva `_test`, ejecutar en este orden:

```sh
PORTAL_CONFIG_FILE=/ruta/portal-private/config.test.php php tests/parity.php
PORTAL_CONFIG_FILE=/ruta/portal-private/config.test.php php tests/functional-fixes.php
PORTAL_CONFIG_FILE=/ruta/portal-private/config.test.php php -S 127.0.0.1:8086 -t public_html
# En otra terminal, con Playwright y Chromium disponibles:
PISCO_BASE_URL=http://127.0.0.1:8086 node tests/browser-functional-fixes.cjs
```

Se comprueban publicación persistente de horarios y consulta del alumno, edición de categorías TUPA, filtros de vigencia por años/rangos, normalización de datos existentes y presentación de documentos referenciales. Estas pruebas modifican únicamente la base local de pruebas.

En una instalación existente, `actualizar.php` corrige las categorías y la errata heredada del TUPA sin reimportar contenido: mantener desmarcada la importación del prototipo. La lectura y el siguiente guardado también normalizan esos conceptos. Se conservan identificadores, montos y denominaciones personalizadas. Los archivos heredados continúan disponibles como referencias identificadas; deben sustituirse por los documentos oficiales aprobados de Pisco cuando la institución los proporcione.
