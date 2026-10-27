<?php
declare(strict_types=1);
require dirname(__DIR__).'/portal-private/bootstrap.php';
require dirname(__DIR__).'/portal-private/OfficialContent.php';
portal_headers();
header('Referrer-Policy: no-referrer');
$error='';$done=false;$closed=false;
try {
 $app=portal_app();
 $db=$app->db();
 $state=$db->query('SELECT data FROM pisco_state WHERE id=1')->fetchColumn();
 if(!$state)throw new PortalError(422,'Primero debe existir una instalación del portal.');
 $current=json_decode($state,true,512,JSON_THROW_ON_ERROR);
 $closed=!empty($current['config']['actualizacionWebOficial']);
 if($closed)http_response_code(410);
 elseif(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
  $app->checkCsrf(is_string($_POST['_csrf']??null)?$_POST['_csrf']:'');
  $config=require(getenv('PORTAL_CONFIG_FILE')?:dirname(__DIR__).'/portal-private/config.php');
  $key=$config['install_key']??'';
  if(!is_string($key)||strlen($key)<32||!is_string($_POST['owner_key']??null)||!hash_equals($key,$_POST['owner_key']))throw new PortalError(403,'La clave privada no coincide con install_key.');
  if(($_POST['confirm_replace']??'')!=='yes')throw new PortalError(422,'Confirma el reemplazo del contenido público para continuar.');
  // Comprueba que la subida incluye los archivos de esta versión.
  $manifest=json_decode(file_get_contents(dirname(__DIR__).'/portal-private/data/official-files.json'),true,512,JSON_THROW_ON_ERROR);
  foreach($manifest['archivos'] as $path=>$meta){
   if(!preg_match('#^assets/oficial/[a-f0-9]{16}\.[a-z0-9]+$#D',$path))throw new PortalError(422,'El registro de archivos no es válido.');
   $file=__DIR__.'/'.$path;
   if(!is_file($file)||!hash_equals($meta['sha256'],hash_file('sha256',$file)))throw new PortalError(422,'Faltan archivos oficiales o la subida está incompleta. Vuelve a subir public_html/assets/oficial antes de continuar.');
  }
  OfficialContent::apply($db,true);$done=true;
 }
}catch(Throwable $exception){
 if($exception instanceof PortalError){http_response_code($exception->status);$error=$exception->getMessage();}
 else{error_log('Actualización de contenido: '.get_class($exception));http_response_code(503);$error='No se pudo completar la actualización. Comprueba la conexión MySQL, los archivos subidos y los permisos de portal-private/backups. No compartas tu clave privada.';}
}
function e(string $value):string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Actualizar contenido de Pisco</title><link rel="stylesheet" href="/forms.css"></head><body><main>
<h1>Actualizar contenido de Pisco</h1>
<?php if($error):?><p class="notice" role="alert"><?=e($error)?></p><?php endif ?>
<?php if($done||$closed):?>
<section><p class="notice success" role="status"><?=$done?'Contenido oficial actualizado correctamente.':'Esta actualización ya fue aplicada y está bloqueada.'?></p><p>Elimina <strong>actualizar-contenido.php</strong> de public_html desde el Administrador de archivos de hPanel.</p><p>El respaldo del contenido anterior está en portal-private/backups. Las cuentas, contraseñas y trámites se conservaron.</p><a href="/">Revisar el portal</a></section>
<?php elseif(isset($app)):?>
<section><p>Este paso sustituye los datos públicos de prueba por el contenido oficial preparado: identidad, programas, personas, noticias, eventos y documentos. También reemplaza las ediciones públicas que hayas realizado.</p><p>Conserva cuentas, contraseñas, reclamos, expedientes, mensajes y registros académicos. Las fichas anteriores vinculadas a docentes académicos se conservan como borradores para mantener su relación.</p><p>Antes de continuar, guarda una copia de archivos y MySQL desde hPanel. Se creará además un respaldo del contenido anterior en la carpeta privada.</p>
<form method="post" autocomplete="off"><input type="hidden" name="_csrf" value="<?=e($_SESSION['csrf']??'')?>"><label>Clave privada de instalación (install_key)<input type="password" name="owner_key" required minlength="32" autocomplete="off"></label><label><input type="checkbox" name="confirm_replace" value="yes" required> Entiendo que se reemplazará el contenido público y ya guardé una copia de seguridad.</label><button type="submit">Respaldar y actualizar contenido oficial</button></form></section>
<?php endif ?>
</main></body></html>
