<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/bootstrap.php';
require __DIR__.'/OfficialContent.php';
if(!in_array('--apply',$argv,true)){echo "Simulación: se reemplazará el contenido público. Se conservan cuentas y trámites. Usa --apply para aplicar con respaldo protegido.\n";exit;}
OfficialContent::apply(portal_app()->db());
echo "Contenido oficial importado. Respaldo protegido en portal-private/backups. Cuentas y trámites conservados.\n";
