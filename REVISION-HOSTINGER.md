# Correcciones de contenido y funcionamiento — 27/10/2026

Se revisó el código y se reprodujeron los flujos en PHP 8.4, MySQL 8.4 y Chromium locales. El acceso HTTP a `region.aplicacion.io` y `pisco.aplicacion.io` fue bloqueado por la política de red del entorno (403 durante CONNECT). Por ello esta revisión no acredita el comportamiento del hosting ni una comparación completa con la referencia solicitada. Los dominios quedaron añadidos al borrador de acceso; falta activarlos en la configuración del entorno.

## Fallas reproducidas y corregidas

- Las fichas oficiales no publican DNI, condición contractual ni todos los correos o especialidades. El editor exigía esos datos y bloqueaba el guardado. Ahora admite los campos desconocidos vacíos; los DNI informados se validan y siguen sin publicarse. Los nombres completos importados se muestran sin comas iniciales.
- El selector de coordinador podía descartar una referencia existente si la persona no figuraba entre los docentes asociados al programa. Conserva la selección actual y la actualización concilia el vínculo explícito con el programa.
- Los campos laborales de cuatro programas incluían asignaturas debido a un selector HTML incorrecto; el de APSTI estaba vacío. Se extrajeron de los encabezados de las tarjetas de campo laboral: 10 ámbitos en APSTI, 11 en ASHR, 12 en Contabilidad, 12 en Enfermería y 16 en Producción Agropecuaria. Las asignaturas permanecen en sus mallas.
- Cuatro resúmenes superaban el límite de 300 caracteres del editor. Se ajustaron como descripciones breves sin sustituir los perfiles profesionales ni las mallas.
- Versiones, vigencias, coordenadas, lugar de eventos, fechas de inicio y costos no verificados ya no son requisitos que obliguen a inventar información. Las coordenadas se validan como pareja; el mapa no muestra una ubicación precisa si no se ha confirmado.
- El licenciamiento desconocido estaba representado como una lista vacía, incompatible con su editor. Ahora se presenta como «Sin información verificada», sin atribuir un estado de licencia.
- Noticias largas y la etiqueta del inicio de clases desbordaban la pantalla en móvil. Se corrigieron el ajuste de palabras, anchos mínimos y etiquetas.
- «Publicado» ya no se presenta automáticamente como «Vigente» en los documentos. Se distinguen los metadatos no indicados.
- Las descargas oficiales informan fallos de carga; una referencia de archivo incompleta no genera un PDF de ejemplo. Se verificó que el mecanismo existente para PDF subidos ya conserva sus bytes, tanto en vista como en descarga.
- Las páginas auxiliares y de error identifican Pisco. Los fallos comunes de conexión MySQL indican revisar base, usuario/permisos, servidor o extensión PHP sin revelar contraseñas.

## Actualización de una instalación existente en Hostinger

1. Respalda archivos y MySQL desde hPanel.
2. Sube los archivos actualizados de `public_html/` y `portal-private/` a sus carpetas correspondientes. Conserva `portal-private/config.php` y los respaldos. Incluye todos los archivos de `portal-private/data/`, en particular `content-corrections.json`.
3. Abre `https://TU-DOMINIO/actualizar.php` y usa tu `install_key`. Este proceso respalda el estado anterior y las cuentas en la tabla privada `pisco_upgrade_backups` antes de actualizar el esquema.
4. La versión de esquema 5 corrige solamente los valores conocidos de la importación anterior que siguen intactos y registra la migración una sola vez. No reemplaza las ediciones públicas realizadas por el instituto. Conserva cuentas, contraseñas, archivos y trámites.
5. Verifica la web y el acceso administrativo. Elimina `actualizar.php` después de comprobarlos.

No reinstales, no importes `schema.sql` sobre la base existente y no vuelvas a ejecutar el importador completo de contenido para aplicar estas correcciones. `actualizar-contenido.php` reemplaza colecciones públicas completas y tiene una finalidad distinta.

Una instalación nueva carga directamente el contenido corregido desde `install.php`.

## Validación ejecutada

Las doce suites de servidor siguientes completaron 341 comprobaciones, usando datos sintéticos creados por las pruebas, nunca el hosting:

| Suite | Comprobaciones aprobadas |
|---|---:|
| run.php | 31 |
| claims-verification.php | 11 |
| parity.php | 51 |
| migration.php | 138 |
| academic-crud.php | 42 |
| regressions.php | 11 |
| quality-lookups.php | 8 |
| deep-contracts.php | 16 |
| deep-documents.php | 8 |
| deep-scholarships.php | 4 |
| deep-publication.php | 6 |
| roles-permissions.php | 15 |

También se comprobaron:

- Instalación en una base verdaderamente vacía, creación de administrador y carga de datos oficiales sin estudiantes ni certificados ficticios.
- Corrección selectiva de instalaciones anteriores, conservación de ediciones, cuentas y trámites e idempotencia de la actualización.
- Edición y recarga de las 56 fichas oficiales sin inventar DNI, correo ni condición laboral.
- Edición y recarga de los cinco programas, preservando mallas, ámbitos laborales y coordinadores.
- Botones públicos de vista y descarga de un PDF subido, comparando los bytes con el original.
- El actualizador web de contenido: CSRF, clave, confirmación, respaldo y bloqueo posterior.
- Recorrido final de 71 rutas públicas, enlaces internos y anchos de 320, 375, 768 y 1440 píxeles, sin errores JavaScript, enlaces internos rotos ni desbordamiento de página.
- Sintaxis PHP y comprobación de diferencias sin errores de espacios.

Las pruebas antiguas que dependían de personas, borradores o beneficiarios presentes en los datos de demostración ahora crean esos registros en sus fixtures. No se añadieron esos datos al contenido de producción para hacer pasar las pruebas.

## Pendiente

Comparar con `https://region.aplicacion.io/#/`, verificar el sitio real de Pisco y diagnosticar problemas específicos de Hostinger exige habilitar el acceso a ambos dominios. No se recibieron ejemplos concretos de los fallos del despliegue. No se cambiaron archivos ni la base de datos de Hostinger. Las limitaciones documentadas de correo saliente, documentos rotos en la fuente y fechas históricas continúan vigentes.
