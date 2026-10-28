# Corrección del buscador público

El buscador comparaba la consulta como una sola frase. «mesa partes» y «arquitectura ti» no encontraban los contenidos correspondientes. Tampoco indexaba directamente secciones como Admisión, Matrícula o Becas, ni el código APSTI, y omitía el contenido completo de las noticias.

Ahora combina las palabras de la consulta sin exigir su orden, tolera mayúsculas, tildes y espacios repetidos, e incluye códigos de programas, perfiles, campos laborales y contenido de noticias publicadas. Prioriza la coincidencia en el título y ofrece enlaces directos a las secciones principales. Los resultados de docentes respetan la visibilidad del directorio y las revocaciones de publicación; no se indexan DNI, teléfonos, borradores ni noticias futuras.

Se mantienen el diseño y los tres formularios existentes: cabecera, menú móvil y página de resultados. Para consultas demasiado cortas se indica un mínimo de dos letras o números.

## Aplicar en Hostinger

1. Descarga la versión actual de `main` de `ranyuo/chincha`.
2. Conserva una copia del archivo anterior y reemplaza únicamente `portal-private/views/portal.php` en el hosting, en la carpeta privada que está al lado de `public_html`.
3. Limpia la caché del sitio y recarga con Ctrl+F5. Prueba «mesa partes», «arquitectura ti», «APSTI», «admisión» y «matrícula».

No requiere reinstalar, importar contenido ni modificar la base de datos o `config.php`.

## Verificación

`node tests/browser-search.cjs`, con Playwright y Chromium disponibles y el servidor local de pruebas iniciado: 19 comprobaciones aprobadas, incluidos los formularios reales de escritorio y móvil, enlaces, prioridad, resultados vacíos y exclusión de contenido no público. El servidor utilizado es local; esto no acredita que el hosting ya tenga el archivo actualizado.
