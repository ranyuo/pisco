# Adaptación de información de Pisco desde el prototipo

Fuente primaria: `index.html` aportado por el usuario, de 1.212.763 bytes. La huella SHA-256 del archivo y los hashes de las imágenes se registran en `portal-private/data/pisco-prototype-sources.json`.

Se extrajo el estado público final del prototipo en un navegador aislado con las conexiones externas bloqueadas. No se copiaron su HTML de presentación, CSS, JavaScript de funcionamiento, contraseñas de demostración, cuentas ni solicitudes. Las imágenes enlazadas desde el prototipo se descargaron después, conservando sus bytes.

## Información incorporada

| Origen del prototipo | Destino |
|---|---|
| Identidad y contacto | Nombre, logotipo, lemas, dirección de Av. Las Américas, teléfono 056 632122, correo institucional, horarios, redes y metadatos |
| Carrusel y páginas | Portada, presentación, historia, misión, visión, valores, organización, campus y textos legales |
| Cinco programas | APSTI, Administración de Servicios de Hostelería y Restaurantes, Contabilidad, Enfermería Técnica y Producción Agropecuaria |
| Directorios | 52 fichas profesionales y ocho cargos jerárquicos del prototipo, con sus contactos e imágenes |
| Publicaciones | Noticias de Pisco, eventos históricos, categorías y servicios; los ejemplos futuros no confirmados quedan en borrador |
| Imágenes | 70 recursos locales, incluidos logotipo, fotografías, carrusel y carreras |

Los datos proceden del prototipo suministrado: no se presentan como una verificación independiente de documentos, tarifas, licenciamiento o fechas institucionales.

## Qué se conserva cuando falta información

- Los módulos, diseño, rutas, búsqueda, formularios, roles y permisos son los del sistema existente.
- Las carreras coincidentes conservan las asignaturas complementarias que no figuran en el prototipo. La malla resultante es una propuesta de contenido a validar por Pisco; no se afirma que todos sus créditos u horas hayan sido confirmados con documentos oficiales de Pisco.
- Los otros cuatro programas del sistema base permanecen en borrador, con sus identificadores académicos, para mantener referencias anteriores. Solo los cinco programas de Pisco se publican como oferta educativa.
- Se conservan cinco documentos y 74 conceptos del tarifario existente. Los archivos siguen siendo los originales: no se reescriben sus PDF para aparentar que pertenecen a Pisco. Se identifican como referencias pendientes de validación; no se muestran cuentas bancarias heredadas como instrucciones de pago.
- Los antecedentes de becas y reconocimientos se conservan en los datos iniciales sin atribuir públicamente a Pisco beneficiarios o distinciones de otra institución. Una importación sobre una instalación existente conserva sus registros, en lugar de sustituirlos por ejemplos del prototipo.
- Las secciones sin información primaria, como inversiones o estadísticas, mantienen lo existente; no se incorporan las cifras aleatorias del prototipo.
- En instalaciones existentes, se conservan documentos, tarifas editadas, becas y otras secciones que el prototipo no aporta con respaldo real. Solo se anotan como referencia heredada los archivos que efectivamente provienen de `assets/chincha/`.

## Ejemplos y pendientes

El prototipo contiene generadores de documentos a partir de nombres y líneas de texto, pero no adjunta los PDF correspondientes. No se importaron esos generadores como documentación oficial. Las mallas y brochures disponibles en el sistema base se conservan como referencia; ASHR no recibe un PDF inventado.

Las inversiones, reconocimientos, estadísticas y registros operativos ficticios del prototipo no se importan como hechos institucionales. Las noticias de ejemplo y los eventos futuros sin respaldo quedan en borrador; las fechas, vacantes y montos de admisión y matrícula se conservan para revisión administrativa, sin anunciar automáticamente una inscripción abierta.

La fotografía enlazada por la reseña en `https://iestppisco.edu.pe/assets/img/presentacion/iestp.jpg` devolvió 404. Se utiliza el logotipo de Pisco como sustituto, sin atribuir a la institución una fotografía de otro campus.

Los permisos de publicación de un CV no se infieren de una ficha del prototipo. Se incorpora el directorio profesional y se mantiene sin publicar la información privada o los CV que no tengan autorización registrada en el sistema.

## Importación y preservación de datos

El flujo existente de `/actualizar.php` conserva autenticación administrativa, `install_key`, CSRF y respaldo privado. La clase mantiene su nombre interno histórico `ChinchaContent` por compatibilidad, pero valida e importa el paquete de Pisco.

No se importan usuarios, contraseñas, estudiantes, notas, expedientes, reclamos ni empresas del prototipo. Los IDs académicos ya existentes no se reasignan. Las personas antiguas vinculadas se conservan despublicadas y las unidades didácticas asignadas a docentes se retienen para evitar vínculos rotos. Los límites operativos de carga y los permisos no se sustituyen con los del prototipo: solo se actualizan sus textos y contactos institucionales.

## Validación local

- 274 validaciones de registros y relaciones sin fallos.
- Identidad, cinco carreras publicadas, directorios, archivos conservados y 70 imágenes verificados por existencia y SHA-256.
- 31 comprobaciones generales de formularios y sesiones aprobadas.
- 51 comprobaciones de perfiles y flujos del servidor aprobadas.
- 40 comprobaciones de importación aprobadas: permisos, respaldo, conservación de cuentas, trámites, tarifas editadas, límites operativos, IDs académicos y asignaciones docentes; repetición sin duplicar programas.
- Instalación desde una base de pruebas vacía, sin registros operativos ficticios.
- 19 comprobaciones del buscador adaptadas a las carreras y personas de Pisco, sin errores JavaScript.
- 53 comprobaciones de navegador, sin errores JavaScript, incluyendo páginas públicas, cinco carreras, directorio, intranet, edición, PDF y actualización desde `/actualizar.php`; escritorio y móvil a 390 px sin desbordamientos en las páginas comprobadas.

La validación se realizó en PHP 8.4, MySQL 8.4 y Chromium locales. No se modificaron archivos ni la base de Hostinger.
