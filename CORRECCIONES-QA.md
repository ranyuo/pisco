# Correcciones de los tres hallazgos QA

## Cambios

- **Usuarios:** el alta respeta `estado: Inactivo` y `active: false/0`. Las altas sin estado explícito siguen activas. Crear y actualizar usan la misma interpretación del estado.
- **Páginas:** el panel permite crear páginas adicionales. Su URL es `#/paginas/<slug>`; si se omite el slug, se genera desde el título. Las páginas publicadas aparecen en Otras Páginas, el buscador y el mapa del sitio. La vista previa funciona también con borradores, que no se publican. Las rutas de las páginas institucionales existentes se conservan.
- **Documentos:** el título editorial `nombre` se conserva al guardar archivos, tanto por referencia como codificados. El nombre original, tipo, tamaño, URL e integridad se obtienen del servidor. La corrección incluye los documentos de licenciamiento.

## Validación local

Usar exclusivamente `PORTAL_CONFIG_FILE` apuntando a `config.test.php` y una base cuyo nombre termine en `_test`. `parity.php` reinicia datos de esa base.

```sh
php tests/parity.php
php tests/qa-reported-fixes.php
PISCO_BASE_URL=http://127.0.0.1:8086 node tests/browser-qa-reported-fixes.cjs
```

El servidor PHP debe usar esa misma configuración de pruebas. Las regresiones comprueban dos altas inactivas desde el panel, páginas publicadas y borradores, navegación, búsqueda, mapa del sitio, rutas institucionales y dos PDF con títulos personalizados. Se verifica que el adjunto permanezca privado antes de guardar y sea público al incorporarse a Licenciamiento.

También se ejecutaron las suites existentes `tests/run.php`, `tests/browser-search.cjs` y `tests/prototype-content.cjs`.

## Aplicación al sitio alojado

Estos cambios no se despliegan automáticamente al portal remoto. Actualizar los siguientes archivos, conservando sus rutas relativas:

- `portal-private/Accounts.php`
- `portal-private/Files.php`
- `portal-private/views/portal.php`

No requieren migrar el esquema ni reinstalar. Conservar la configuración privada y los datos existentes; no subir pruebas ni sus archivos generados al sitio. Recargar las pestañas del portal tras actualizar los archivos y limpiar la caché del hosting si sirve versiones antiguas.

Las páginas adicionales existentes que tengan un slug obtienen su ruta pública al aplicar la actualización. Los títulos de documentos que ya se sobrescribieron deben introducirse de nuevo: la actualización permite conservarlos, pero no puede reconstruir automáticamente el texto perdido. Las cuentas que se crearon activas anteriormente no se desactivan de forma masiva; la corrección respeta el estado de las altas nuevas.
