# Informe de errores, correcciones y adaptación a otro instituto

**Fecha:** 28 de octubre de 2026 (Lima).  
**Sistema revisado:** portal público e intranet de desarrollo de IESTP Pisco, https://devpisco.aplicacion.io.  
**Repositorio y referencia:** ranyuo/pisco, rama dev, commit 6aaf5ce308fa49bbc7ec3f51b5ab34022df050f7.  
**Objetivo:** documentar los problemas confirmados, los cambios realizados y su aplicación en una copia del sistema perteneciente a otra institución.

## 1. Errores confirmados y solución

| Error | Síntoma observado | Causa identificada | Modificación realizada | Estado |
|---|---|---|---|---|
| Publicación de horarios no persistente | La interfaz anunciaba que el horario estaba publicado; al recargar volvía a borrador y el alumno no podía verlo. | El navegador recibía el mapa vacío de publicaciones como un arreglo `[]`. Las claves de texto de programa, ciclo, sección, turno y periodo se perdían al serializar ese arreglo. | Conversión de `publicados` a objeto en la inicialización y sincronización del módulo académico, antes de usar claves de texto. | Corregido y comprobado remotamente con administrador y alumno. |
| F01: categoría TUPA perdida en edición | Un concepto mostraba categoría en el listado, pero el editor presentaba «Seleccione». | El selector contenía seis categorías fijas distintas de las categorías guardadas. | El selector incorpora las categorías existentes, la del registro editado y las opciones generales sin repetir valores. | Corregido; categoría conservada al guardar y recargar. |
| F03: filtro de vigencia «Publ» | El filtro administrativo ofrecía «Publ» como si fuera un año. | Se tomaban los cuatro primeros caracteres del texto «Publicada en fuente institucional». Los intervalos se reducían al primer año. | Funciones compartidas para reconocer años e intervalos, ofrecer «Sin año especificado» y filtrar por todos los años comprendidos en el rango. Aplicadas en administración y portal público. | Corregido; probado 2026 dentro de 2024–2030 y 2027 dentro de 2026–2028. |
| Categorías TUPA duplicadas | Variantes de la misma categoría aparecían separadas y fragmentaban los filtros. | Diferencias de singular/plural y espacios en datos heredados. | Normalización de `6.- CONSTANCIA Y CERTIFICADOS` a `6.- CONSTANCIAS Y CERTIFICADOS`, y `12.-ALQUILERES` a `12.- ALQUILERES`. | Corregido en datos iniciales y datos existentes. |
| Errata del concepto TUPA 4.1 | «4.1 ATRÍCULA REGULAR» quedaba fuera de la búsqueda «MATRÍCULA». | Error de carga en la denominación. | Corrección a «4.1 MATRÍCULA REGULAR» solo cuando coinciden el código y el texto erróneo conocidos. No sustituye denominaciones personalizadas. | Corregido y comprobado en búsqueda pública. |
| F02: documentos heredados presentados como vigentes | «TUPA vigente» descargaba un PDF que identificaba a Chincha; otros documentos heredados también podían aparecer como vigentes. | Contenido documental conservado de la institución de origen y rótulos genéricos de vigencia. | Detección por ruta/procedencia y aviso de referencia. TUPA y resolución se rotulan como documentos de referencia; los documentos de gestión heredados muestran «Referencia pendiente». | Presentación corregida. La sustitución por los PDF oficiales de Pisco sigue pendiente. |

La corrección del último punto no convierte un documento de Chincha en un documento oficial de otra institución. No se retocaron los PDF originales para cambiarles el nombre del instituto.

## 2. Archivos modificados

| Archivo | Cambio |
|---|---|
| `portal-private/views/portal.php` | Corrección de mapas de publicaciones; selector TUPA; funciones de vigencia; filtros; rótulos de referencias documentales. |
| `portal-private/Content.php` | Archivo nuevo. Normalización conservadora de categorías y errata TUPA. |
| `portal-private/Portal.php` | Aplica la normalización al preparar el estado del portal; el siguiente guardado conserva los datos corregidos. |
| `portal-private/Schema.php` | Carga la clase nueva y aplica la normalización al actualizar una instalación existente. Solo cambia la revisión cuando hay cambios efectivos. |
| `portal-private/data/portal.json` | Corrige las categorías y la errata del paquete inicial. No contiene la base de datos real del hosting. |
| `tests/functional-fixes.php` | Comprueba normalización, conservación de valores personalizados, identificadores y montos, y actualización repetible sobre una base de pruebas. |
| `tests/browser-functional-fixes.cjs` | Regresión local de edición TUPA, filtros, documentos de referencia y publicación/consulta de horarios entre roles. |
| `README.md` | Instrucciones para ejecutar la regresión y actualizar datos existentes. |

**Conservación de información:** las correcciones no cambian identificadores, montos, cuentas, contraseñas ni la identidad institucional. La normalización es repetible y solo transforma las variantes conocidas. Los documentos de referencia conservan sus archivos y enlaces.

## 3. Validación efectuada

**Local:** 31 comprobaciones de servidor, 51 de paridad, 40 de importación, 7 de normalización/actualización y regresión de navegador. También se validaron 274 registros del paquete y la integridad de 70 imágenes. Son comprobaciones con alcances distintos; no equivalen a una certificación completa de todas las funciones.

**Después de actualizar Hostinger:** 14 comprobaciones satisfactorias de los casos ejecutados, incluyendo:

- Edición y guardado de un concepto TUPA existente sin pérdida de categoría.
- Ausencia de categorías duplicadas conocidas y corrección de la denominación 4.1.
- Filtros administrativos por rangos y textos sin año.
- Rótulos públicos de referencia para PDF heredados.
- Registro y publicación de concepto TUPA, noticia y documento QA.
- Filtro del documento nuevo por un año interior a su intervalo de vigencia.
- Descarga pública del PDF con SHA-256 coincidente con el archivo almacenado.
- Publicación de horario conservada tras recarga y lectura desde una cuenta de alumno.
- Envío de expediente y reclamo ficticios desde afuera con PDF.
- Atención interna del expediente, descarga del cargo, respuesta al reclamo y consulta pública con descarga de constancia.

Los registros QA quedaron cargados y, donde correspondía, publicados por autorización del propietario de la instancia de pruebas. No son información institucional real.

## 4. Hallazgos que no se resolvieron con estas correcciones

- **Documentos oficiales:** faltan los PDF aprobados que correspondan a la institución. Cambiar rótulos o subir un PDF QA no reemplaza este requisito.
- **Correo y boletín:** no se configuró ni certificó la entrega real de mensajes a una bandeja externa. Guardar una respuesta en el portal no acredita entrega de correo.
- **Tiempos de espera:** se observaron recargas/navegaciones intermitentes de hasta 45 segundos. Los flujos pasaron posteriormente desde sesiones nuevas; la causa no se determinó. Si ocurre en uso habitual, revisar red, registros y recursos del hosting.
- **Mapas y sistemas externos:** el límite WebGL y los bloqueos por CAPTCHA, Cloudflare o red no demuestran por sí mismos una falla del código del portal; necesitan pruebas específicas.
- **Datos operativos:** ausencia inicial de unidades didácticas, convocatorias publicadas o periodos actuales era falta de configuración o contenido. La carga de datos QA permitió comprobar los flujos, pero no completa los datos oficiales del instituto.
- **Cobertura:** no se certificaron todos los roles, límites de adjuntos, procesos de aprobación, restauraciones, dispositivos ni rendimiento. No se emitieron títulos o certificados académicos ficticios.

## 5. Cómo trasladar las correcciones a otra institución

**No subir directamente el ZIP anterior de Pisco a la otra web.** Incluye archivos completos y datos iniciales de Pisco. Aunque el sistema sea una copia, puede tener cambios posteriores, prefijos diferentes, identidad y contenido propios.

1. Respaldar los archivos y la base de datos de la otra web y preparar una copia de pruebas.
2. Comparar su versión con el commit `6aaf5ce`. Confirmar que usa los mismos módulos, estructura de datos y adaptación PHP/MySQL. Si difiere, trasladar los cambios por función en vez de reemplazar archivos enteros.
3. Incorporar la corrección del mapa de publicaciones, el selector de categorías y las funciones/filtros de vigencia. Son correcciones reutilizables del mismo sistema base.
4. Añadir `Content.php` y sus integraciones en `Portal.php` y `Schema.php`. Revisar que las variantes de categoría y la errata existan en esa instalación antes de incluir sus reglas; no normalizar catálogos diferentes a ciegas.
5. Adaptar los avisos documentales. En los cambios de Pisco se detectan `/assets/chincha/` y `iestpchincha.edu.pe`, y algunos mensajes mencionan Pisco. La otra web debe reconocer la procedencia real de sus archivos y mostrar el nombre de su propia institución. No basta con reemplazar el nombre en los títulos de los PDF.
6. Corregir únicamente los registros equivalentes de su paquete inicial. **No copiar `portal-private/data/portal.json` de Pisco**, pues contiene identidad, contenidos, programas, imágenes y configuración de esa institución.
7. Conservar el `config.php`, credenciales, base de datos, prefijos, adjuntos, usuarios, contraseñas, permisos, programas y datos existentes de la otra web. No hacer sustituciones globales de «pisco» sin revisar cada uso técnico.
8. Probar la actualización sobre la copia aislada. Si usa el mismo `actualizar.php`, ejecutarlo con su propia `install_key`, manteniendo desmarcada la importación del prototipo. Esa importación puede reemplazar contenido institucional y no es necesaria para estas correcciones.
9. Repetir la aceptación descrita abajo. Una publicación de horario perdida antes de corregir no reaparece sola: se debe volver a publicar.
10. Subir únicamente los archivos revisados al hosting de destino y repetir las comprobaciones en la web real. No copiar datos ni cuentas QA entre instituciones. Conservar el respaldo para una reversión coordinada de archivos y base si fuese necesaria.

## 6. Identidad y documentos que deben revisarse en la otra web

Nombre completo y abreviado; logotipo e imágenes; dirección, teléfono, correo y dominio; autoridades y docentes; programas de estudio; documentos legales; TUPA, resolución, UIT y tarifas; admisión, matrícula y becas; responsables de atención; textos legales; enlaces externos; metadatos SEO y configuración del correo.

Los importes, cuentas bancarias, fechas y documentos deben proceder de la otra institución. No reutilizar los de Pisco o Chincha como instrucciones oficiales.

## 7. Criterios mínimos de aceptación en la otra web

- Publicar un horario válido, recargar y entrar con el alumno del programa/ciclo/sección/turno correctos: debe ver su clase.
- Editar conceptos TUPA existentes y personalizados: conservar categoría, código, monto y requisitos tras guardar y recargar.
- Comprobar que las variantes equivalentes se unifiquen sin perder conceptos ni alterar montos.
- Buscar «MATRÍCULA»: el concepto equivalente debe aparecer si existe en ese catálogo.
- Filtrar documentos por un año simple, por un año interior a un rango y por «Sin año especificado»; no deben aparecer fragmentos como «Publ».
- Confirmar que archivos heredados de otra entidad se presenten como referencias y que los documentos oficiales correspondan a la institución de destino.
- Crear noticia y documento QA, publicarlos, abrirlos desde otra sesión y verificar la descarga del PDF.
- Enviar trámite y reclamo ficticios en una instancia de pruebas, comprobar recepción, atención y seguimiento público.
- Ejecutar la actualización dos veces: no debe duplicar registros ni modificar cuentas y contraseñas.

**Conclusión:** las correcciones de software confirmadas quedaron implementadas y verificadas en Pisco. Pueden reutilizarse en una copia compatible del sistema, con adaptación de los datos y mensajes propios de la otra institución. Los PDF oficiales, el correo y los demás pendientes indicados requieren trabajo o información adicional; no quedan resueltos por cambiar el nombre del instituto.
