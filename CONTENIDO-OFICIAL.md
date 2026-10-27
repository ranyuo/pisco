# Contenido institucional de Pisco

Adaptación local consultada el 27 de octubre de 2026 desde https://www.iestppisco.edu.pe/vista/inicio.php. Se conserva la estructura PHP/MySQL, el menú, los módulos de administración, los formularios y los permisos del sistema. No se ha publicado esta adaptación ni se ha actualizado un hosting.

## Contenido incorporado

- Identidad, logotipo, dirección, teléfono, correo, horarios y redes enlazadas en el sitio institucional.
- Presentación, historia, misión, visión, valores y organización institucional.
- Cinco programas, sus perfiles, campos laborales y 193 unidades didácticas transcritas por módulo y semestre. Los créditos y horas no se inventan: se remite a las mallas oficiales descargables.
- 56 fichas de personas y 13 cargos publicados. Los nombres se conservan tal como aparecen en cada ficha de origen; puede haber variantes de nombre entre secciones. No se inventaron DNI, grados adicionales, CV ni correos no publicados.
- Las siete noticias que aparecen tanto en la portada como en el listado de noticias, tres eventos de portada y una convocatoria institucional con cinco documentos.
- Doce documentos de gestión, mallas y folletos disponibles. Los archivos válidos se sirven localmente; las descargas conservan los bytes originales y sus sumas SHA-256.
- Información histórica de Admisión 2026 y Matrícula 2025. Las consultas de becas enlazan la publicación histórica del instituto. Se retiraron certificados, reconocimientos, tasas, empleadores y cifras ficticias del contenido inicial.

El archivo `CONTENIDO-OFICIAL-FUENTES.json` registra las páginas y los archivos consultados, sus URL, tamaños y huellas de integridad. Las imágenes de demostración se sustituyeron por fotografías e imágenes institucionales disponibles, sin inventar escenas ni alterar a las personas.

## Incidencias de la fuente

- El sitio oficial rotula como Misión el texto que comienza «Al 2031…» y como Visión el que comienza «Somos una Institución…». Se conservaron esos rótulos; requieren confirmación institucional.
- La presentación contiene referencias históricas a «29 años» mientras la portada habla de 42. La adaptación usa la fecha de creación publicada (17/07/1984), sin copiar el contador antiguo.
- El prospecto enlazado desde Admisión 2026 tiene «2024» en el nombre del archivo. Se advierte esa denominación y no se presenta como un prospecto 2026 confirmado. El cronograma publicado se conserva como histórico.
- El Plan Anual de Trabajo 2026, la fotografía `presentacion/iestp.jpg` y la malla de Agropecuaria devolvieron 404. No se crearon sustitutos ficticios.
- Convenios, servicios complementarios y el supuesto listado de eventos devolvieron 404. Se conservaron las funciones del sistema; no se inventaron sus contenidos.
- Los enlaces de Manual de Organización y Funciones y Plan Estratégico remiten al mismo `pei.pdf` en la fuente. Se conservaron las referencias publicadas; el instituto debe confirmar sus documentos correctos.
- No se publican montos de matrícula como tasas vigentes: las páginas de programas muestran importes diferentes. Tampoco se tratan las tablas estadísticas con ceros de la fuente como cifras verificadas.
- No se confirmó una convocatoria de becas vigente. La información histórica de 2022–2024 permanece consultable en el enlace oficial; no se fabricaron identificadores de beneficiarios para el sistema.
- Las coordenadas anteriores eran de demostración. El mapa ahora busca la dirección institucional, sin presentar coordenadas precisas no verificadas.
- Correo saliente y accesos de los servicios externos mantienen sus limitaciones previas; importar contenido no acredita entrega de correo ni acceso a esos servicios.

## Instalaciones nuevas y existentes

Una instalación nueva carga `portal-private/data/portal.json`. Una instalación ya creada conserva su estado en MySQL: copiar el JSON por sí solo no actualiza su contenido.

Para una instalación existente, el responsable puede revisar primero:

```sh
php portal-private/import-official.php
```

La aplicación es explícita mediante `--apply`. El comando reemplaza las colecciones públicas indicadas en el script y la identidad institucional; conserva cuentas, contraseñas, sesiones, expedientes, reclamos, mensajes y registros académicos. Crea antes una copia del estado en `portal-private/backups/`, con permisos 0600. Rechaza la operación si hay docentes académicos vinculados a personas anteriores que requieren conciliación. No se ejecuta automáticamente desde una página web. Revisar ediciones públicas existentes antes de aplicarlo, porque esas colecciones se sustituyen.

En este entorno solo se aplicó sobre `pisco_test`. No se actuó sobre producción, no se ejecutó `git push` y no se publicó el entorno.

## Validación

- `tests/run.php`: 31 comprobaciones aprobadas en PHP 8.4/MySQL 8.4.
- `tests/claims-verification.php`: 11 comprobaciones aprobadas.
- `tests/browser-official-content.cjs`: Chromium recorre 17 rutas y los cinco programas; comprueba PDF originales, imágenes locales, identidad institucional, ausencia de certificados ficticios, vista móvil y errores JavaScript.
- Se probaron simulación y aplicación del importador en la base de pruebas.

Las pruebas antiguas denominadas `demo-*` describen la identidad ficticia anterior y no validan esta adaptación. Las suites restantes no se ejecutaron en esta revisión.
