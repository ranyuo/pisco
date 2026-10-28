# Mapeo de contenido — IESTP Chincha

Fuente: https://www.iestpchincha.edu.pe/ · Consulta: 27/10/2026.

Se conservaron la estructura visual, las rutas, los módulos administrativos, los formularios, los roles y la persistencia PHP/MySQL. No se importaron el diseño ni el código de la web consultada.

| Fuente oficial | Destino en el sistema |
| --- | --- |
| Inicio y presentación | Identidad institucional, contacto, redes, logotipo, carrusel, bienvenida e historia; creación 13/01/1967, Ley 16377 y adecuación R.S. 131-83-ED |
| Ocho fichas de programas | APSTI, Construcción Civil, Contabilidad, Electricidad Industrial, Electrónica Industrial, Enfermería Técnica, Industrias Alimentarias y Producción Agropecuaria; perfiles, campos laborales, brochures y 316 unidades de las mallas publicadas |
| Plana docente y jerárquica | 52 personas; 11 cargos directivos y coordinaciones. Solo información profesional publicada; no se inventaron DNI, teléfonos, contratos ni autorizaciones de publicación de CV |
| Locales | Sede principal en Pueblo Nuevo y sede de Producción Agropecuaria en Chincha Baja; direcciones, teléfonos, correos y coordenadas publicadas |
| Blog | 16 noticias con sus fechas originales e imágenes |
| Becas | 30 beneficiarios publicados del período 2026-I; nombres y tipos de exoneración, sin DNI inventados |
| Documentos de Gestión | Cinco archivos originales: MPP, PEI 2024–2030, Reglamento Interno, Reglamento de Admisión y MPA |
| TUPA | 74 conceptos de 2026 con importes y números de cuenta publicados, resolución de aprobación y TUPA descargable |
| Servicios y enlaces | Biblioteca Virtual, Correo Institucional, Bolsa de Trabajo y enlaces educativos publicados |
| Admisión y matrícula | Requisitos y documentos disponibles; observaciones expresas sobre las fechas contradictorias de la fuente |

## Imágenes y documentos

Se conservaron localmente 119 archivos oficiales descargados. Se recodificaron 72 imágenes mediante WebP sin pérdida; se comprobó la igualdad exacta de los píxeles decodificados. Se ahorraron 49.554.813 bytes sin modificar rostros, logotipos, textos, resolución ni contenido documental. Los PDF originales conservan sus bytes.

El inventario `portal-private/data/chincha-sources.json` registra las URL de origen, los hashes y el tamaño de los archivos entregados; para las imágenes recodificadas conserva también el hash y tamaño originales. No es un rastreador automático ni realiza sincronizaciones al abrir el portal.

## Datos no confirmados y diferencias de la fuente

- **Admisión:** el encabezado dice 2026, pero una tabla de vacantes dice 2024 y el examen se describe como domingo 7 de abril sin año. No se anuncian vacantes ni fechas actuales con esos datos. El enlace al reglamento «2026» falló; se usa el reglamento disponible en Documentos de Gestión sin atribuirle ese año.
- **Matrícula:** la fuente mezcla «Matrícula Regular 2025» e inicio 01/04/2025 con cronogramas 09/03/2026 y 30/03/2026. Se conserva como orientación histórica, sin anunciar un proceso abierto.
- **Coordinadores:** algunas fichas de programas difieren de Plana Jerárquica. Se usa la página explícita de Plana Jerárquica para las coordinaciones actuales; no se mezclan ambos listados.
- **DNI y contactos:** la fuente no publica DNI de docentes ni becarios. Estos campos son opcionales en los directorios públicos y siguen validándose cuando se completan. Tres correos docentes malformados o nulos se dejaron vacíos, sin adivinarlos. Los teléfonos y domicilios personales no se importaron.
- **Sede de Chincha Baja:** su endpoint de fotografía devolvió HTTP 500. Se usa la imagen oficial del programa de Producción Agropecuaria como referencia visual, sin fabricar una fotografía de la sede.
- **Licenciamiento:** la fuente dice que actualizará la información una vez obtenida la licencia; no publica resolución ni vigencia. No se afirma que la institución esté licenciada.
- **Misión, visión y valores:** se transcriben desde las páginas 9 y 10 del PEI 2024–2030 escaneado, contrastando visualmente el documento. También se incorpora el código modular 0517664. La visión es una meta al 2030 y no acredita un licenciamiento actual. Los contactos del PEI de 2024 difieren de las páginas actuales; para el contacto se usan los publicados en la web consultada.
- **TUPA:** la fuente repite el código 12.3 para dos alquileres distintos. Se conservan ambos conceptos, montos y cuentas; el sistema distingue sus códigos como `12.3-61` y `12.3-71`, manteniendo `codigoFuente: 12.3`.
- **Oficinas internas:** se conserva la estructura de áreas del sistema, sin responsables ficticios. El único correo general verificado se usa como contacto central, no como correo propio de cada oficina. La institución debe asignar sus responsables, destinatarios, horarios y reglas operativas.
- No se inventan estadísticas, egresados destacados, convocatorias, inversiones, alumnos, calificaciones, certificados, empresas ni ofertas. Sus módulos siguen disponibles y empiezan sin los registros de prueba en instalaciones nuevas.

## Instalaciones existentes

La casilla adicional de `actualizar.php` importa el contenido bajo sesión de administrador, clave privada y CSRF. Guarda un respaldo privado antes de escribir y conserva las cuentas, contraseñas, registros académicos y solicitudes. No reasigna los IDs numéricos de los programas existentes. Repetirla no duplica programas. Sin marcarla, el actualizador mantiene el comportamiento anterior.

Los cambios están preparados para el repositorio; no se ha accedido ni modificado una base de producción ni un hosting externo.

## Comprobaciones

Se validan las relaciones y reglas de los datos, los archivos locales y sus hashes, los permisos del servidor, la importación y las páginas públicas y administrativas. Resultados en el entorno local PHP 8.4 / MariaDB 11.8:

- 270 validaciones de registros y relaciones, sin fallos.
- 119 archivos locales verificados por tamaño y SHA-256; comprobación del catálogo de ocho programas y de los seis semestres de cada malla.
- 31 comprobaciones generales del servidor y 51 de paridad de roles y flujos académicos aprobadas.
- 35 comprobaciones de importación, permisos, respaldo, repetibilidad, IDs académicos y protección de datos aprobadas.
- Instalación nueva verificada sin registros operativos ficticios; edición persistente visible en sesión pública.
- 56 comprobaciones de navegador: rutas públicas, ocho fichas de programas, fichas docentes, intranet, edición de noticia, descarga de PDF e importación desde `actualizar.php`; sin errores de JavaScript.
- Escritorio y móvil revisados; sin desbordamiento horizontal en las páginas comprobadas a 390 px. Las solicitudes a fuentes y mapas externos se bloquearon en las pruebas para comprobar que el contenido y las imágenes institucionales funcionan localmente.

Comandos de comprobación: `tests/demo-content.cjs`, `tests/demo-validation.php`, `tests/run.php`, `tests/parity.php`, `tests/chincha-import.php`, `tests/demo-installation.php` y `tests/browser-chincha.cjs`. Las pruebas que modifican registros utilizan exclusivamente bases locales desechables `_test`. La comprobación en un hosting externo queda pendiente de su despliegue.
