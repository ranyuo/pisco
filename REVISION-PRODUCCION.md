# Revisión de presentación y preparación para producción

Fecha: 28 de octubre de 2026. Revisión local con PHP 8.4, MySQL 8.4 y Chromium. No se ha desplegado ni modificado Hostinger.

## Mejoras

- Las páginas institucionales tienen una columna de lectura más clara, imágenes completas y espaciado consistente. El mensaje largo de Dirección permite ampliar el texto sin eliminar información. La reseña histórica mantiene todos sus textos y mejora la separación de sus hitos.
- Las cinco carreras muestran tres campos laborales y permiten desplegar el resto. La malla curricular se abre por módulos; el equipo docente muestra cuatro fichas iniciales y permite ampliar el resto.
- Los desplegables funcionan con teclado, conservan el foco visible y se abren al imprimir, recuperando después su estado anterior.
- Las imágenes de carreras y docentes conservan sus proporciones. Las cabeceras ya no amplían el escudo como si fuera una fotografía. Se mantienen los archivos originales de la fuente y sus datos.
- Se corrigió la validación del editor de carreras: un nombre de unidad puede repetirse en semestres distintos; la repetición dentro del mismo semestre sigue bloqueada.

## Verificación reproducible

Las pruebas con escrituras se ejecutaron únicamente en bases locales de prueba. No ejecutar estas pruebas sobre producción.

| Área | Resultado |
|---|---|
| Servidor | 370 comprobaciones aprobadas en 12 suites: formularios, permisos, sesiones, migración, CRUD académico, documentos, becas, publicación e importación |
| Instalación nueva | Instalación en base vacía, administrador, cinco carreras, edición persistente y ausencia de operaciones ficticias aprobadas |
| Portal público | 69 rutas recorridas; 6.351 apariciones de enlaces inventariadas; 65 recursos locales accesibles; sin rutas rotas ni errores JavaScript detectados |
| Adaptación de pantalla | Recorrido público a 320, 390, 768 y 1.440 píxeles sin desbordamiento horizontal de página |
| Presentación | Las cinco carreras pasan expansión por teclado, mallas, perfiles e impresión; el mensaje completo de Dirección conserva los bloques originales |
| Imágenes | 70 archivos originales comprobados con SHA-256 |
| Documentos | Nueve descargas de documentos y brochures con bytes idénticos al original; apertura del visor comprobada |
| Formularios y atención | Contacto, expediente con PDF, reclamo con PDF, atención administrativa y recuperación de adjuntos aprobados |
| Administración | 101 operaciones de creación, edición, papelera, restauración y eliminación; 11 casos de rechazo del servidor sin falsos mensajes de éxito |
| Respaldos | Creación, descarga, restauración, exportación de auditoría y programación diaria comprobadas localmente |

Scripts adicionales: `tests/browser-release-audit.cjs`, `tests/browser-presentation.cjs`, `tests/browser-release-contact.cjs`, `tests/browser-release-downloads.cjs`. Los informes JSON y capturas se generan en `tests/.results/` y no se suben al repositorio.

Los errores iniciales de la prueba de contacto eran de sincronización del navegador y de selector del botón; se corrigieron y el flujo completo pasó nuevamente. La revisión administrativa permitió descubrir y corregir el bloqueo real de las unidades didácticas por semestre.

## Límites y pendientes antes de publicar

- De 39 direcciones externas inventariadas, diez direcciones oficiales permitidas respondieron HTTP 200. Las otras 29 no se pudieron comprobar por la política de red del entorno, incluyendo servicios externos, redes sociales y enlaces para compartir. Esto no demuestra que fallen. No se enviaron mensajes ni publicaciones.
- Los documentos, tarifas y complementos heredados siguen identificados como referencias pendientes de validación para Pisco. La malla debe recibir validación institucional antes de presentarse como el plan oficial. Las convocatorias no confirmadas permanecen en borrador.
- El correo saliente mantiene las limitaciones del sistema base; la recepción interna sí se probó. No se promete entrega de correos externos.
- La configuración y comportamiento reales de Hostinger, HTTPS, permisos de archivos y límites del plan requieren una comprobación después de subir los archivos. Las pruebas locales no equivalen a una garantía de funcionamiento del 100% en un servidor distinto.

## Instalación nueva en Hostinger

1. Descarga la última versión de `main` y extrae el ZIP en tu equipo.
2. Sube el contenido de `public_html/` a la carpeta pública del dominio y `portal-private/` al directorio inmediatamente superior, al mismo nivel que `public_html`. Incluye todos los recursos `assets/pisco/` y `assets/chincha/`. No subas `tests/` ni sus evidencias.
3. Copia `portal-private/config.example.php` como `portal-private/config.php`. Configura el nombre y usuario completos de la base vacía creada en hPanel, contraseña, servidor MySQL indicado por Hostinger y una `install_key` privada aleatoria. No compartas esa clave ni subas el archivo a GitHub.
4. Activa PHP 8.2 o posterior con PDO MySQL y las extensiones indicadas en README. Abre `https://TU-DOMINIO/install.php`, utiliza tu clave y crea tu administrador. No importes `schema.sql` manualmente antes del instalador.
5. Comprueba inicio, las cinco carreras, contacto, intranet y una descarga. Verifica que un mensaje de contacto llegue al listado administrativo y realiza un respaldo privado.
6. Retira los instaladores y herramientas de actualización/recuperación cuando dejen de ser necesarios, siguiendo las instrucciones del sistema. Mantén `portal-private` fuera de la raíz pública.

Si esta base ya tiene una instalación con datos, sigue el procedimiento de actualización y respaldo de README en lugar de tratarla como base vacía.
