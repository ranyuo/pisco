<?php
declare(strict_types=1);
require dirname(__DIR__).'/portal-private/bootstrap.php';
$path=getenv('PORTAL_CONFIG_FILE');
if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Solo configuración de pruebas.');
$config=require $path;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base de pruebas.');
$db=portal_app()->db();$file='/tmp/pisco-content-upgrade-fixture.json';$mode=$argv[1]??'';
if($mode==='begin'){
 if(file_exists($file))throw new RuntimeException('Hay una prueba pendiente; restaura primero.');
 $row=$db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();
 $state=json_decode($row['data'],true,512,JSON_THROW_ON_ERROR);
 $snapshot=['row'=>$row,'accounts'=>$db->query('SELECT * FROM pisco_users ORDER BY id')->fetchAll()];
 file_put_contents($file,json_encode($snapshot,JSON_THROW_ON_ERROR));chmod($file,0600);
 unset($state['config']['actualizacionWebOficial']);$state['config']['inst']['nombre']='Contenido anterior de prueba';
 $state['personas'][]=['id'=>'legacy-test-person','nombres'=>'Persona anterior de prueba','apellidos'=>'','estado'=>'Publicado'];
 $state['academico']['docentes'][]=['id'=>99991,'per'=>'legacy-test-person','nom'=>'Persona anterior de prueba'];
 $snapshot['expected']=$state;file_put_contents($file,json_encode($snapshot,JSON_THROW_ON_ERROR));
 $db->prepare('UPDATE pisco_state SET data=? WHERE id=1')->execute([json_encode($state,JSON_THROW_ON_ERROR)]);
 echo json_encode(['key'=>$config['install_key']],JSON_THROW_ON_ERROR);exit;
}
$snapshot=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
if($mode==='restore'){
 $db->prepare('UPDATE pisco_state SET data=?,revision=? WHERE id=1')->execute([$snapshot['row']['data'],$snapshot['row']['revision']]);unlink($file);echo "Restaurado\n";exit;
}
$current=$db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();$state=json_decode($current['data'],true,512,JSON_THROW_ON_ERROR);
if($mode==='unchanged'){
 if($state!=$snapshot['expected']||(int)$current['revision']!==(int)$snapshot['row']['revision'])throw new RuntimeException('Solicitud rechazada modificó datos.');
}elseif($mode==='verify'){
 if(empty($state['config']['actualizacionWebOficial'])||!str_contains($state['config']['inst']['nombre'],'Pisco'))throw new RuntimeException('No se importó la identidad.');
 foreach(['mensajes','reclamos','expedientes','academico'] as $key){
  $before=$snapshot['expected'][$key];$after=$state[$key];
  if($key==='academico'){unset($before['cfg'],$after['cfg']);}
  if($before!=$after)throw new RuntimeException('Cambió información privada: '.$key);
 }
 if($db->query('SELECT * FROM pisco_users ORDER BY id')->fetchAll()!==$snapshot['accounts'])throw new RuntimeException('Cambiaron las cuentas.');
 $legacy=PortalPolicy::find($state['personas'],'id','legacy-test-person');if(($legacy['estado']??'')!=='Borrador')throw new RuntimeException('No se conservó la ficha vinculada.');
 $found=false;foreach(glob(dirname(__DIR__).'/portal-private/backups/antes-contenido-oficial-*.json') as $backup){$b=json_decode(file_get_contents($backup),true);if(($b['data']??null)==$snapshot['expected']){$found=true;if((fileperms($backup)&0777)!==0600)throw new RuntimeException('Permisos de respaldo incorrectos.');break;}}
 if(!$found)throw new RuntimeException('No se encontró respaldo del estado anterior.');
}else throw new RuntimeException('Modo no válido.');
echo "OK $mode\n";
