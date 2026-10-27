<?php
declare(strict_types=1);
require dirname(__DIR__).'/portal-private/bootstrap.php';
$p=getenv('PORTAL_CONFIG_FILE');if(!$p||!str_ends_with($p,'config.test.php'))throw new RuntimeException('Solo configuración de pruebas.');$config=require $p;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base de pruebas.');
$db=portal_app()->db();$original=$db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();$accounts=$db->query('SELECT * FROM pisco_users ORDER BY id')->fetchAll();
$patches=json_decode(file_get_contents(dirname(__DIR__).'/portal-private/data/content-corrections.json'),true,512,JSON_THROW_ON_ERROR);
try{
 $data=json_decode($original['data'],true,512,JSON_THROW_ON_ERROR);$data['licenciamiento']=[];unset($data['config']['correccionesContenido']);
 foreach($patches as $patch)foreach($data['programas'] as &$program)if($program['id']===$patch['id'])$program[$patch['campo']]=$patch['antes'];unset($program);
 $data['programas'][0]['resumen']='Texto modificado por el instituto que debe conservarse.';
 $db->prepare('UPDATE pisco_state SET data=? WHERE id=1')->execute([json_encode($data,JSON_THROW_ON_ERROR)]);
 PortalSchema::apply($db);$first=$db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();$after=json_decode($first['data'],true,512,JSON_THROW_ON_ERROR);
 if($after['programas'][0]['resumen']!==$data['programas'][0]['resumen'])throw new RuntimeException('Se sobrescribió una edición del usuario.');
 foreach($patches as $patch)foreach($after['programas'] as $program)if($program['id']===$patch['id']&&!($program['id']===$data['programas'][0]['id']&&$patch['campo']==='resumen')&&$program[$patch['campo']]!==$patch['despues'])throw new RuntimeException('No se aplicó una corrección pendiente.');
 if(($after['licenciamiento']['estado']??'')!=='Sin información verificada')throw new RuntimeException('Estado de licenciamiento incorrecto.');
 foreach(['academico','mensajes','reclamos','expedientes','certificados'] as $key)if($after[$key]!=$data[$key])throw new RuntimeException('Cambió información ajena a la corrección.');
 PortalSchema::apply($db);$second=$db->query('SELECT data,revision FROM pisco_state WHERE id=1')->fetch();if($first!==$second)throw new RuntimeException('La actualización no es idempotente.');
 if($accounts!==$db->query('SELECT * FROM pisco_users ORDER BY id')->fetchAll())throw new RuntimeException('Cambió una cuenta.');
 echo "OK correcciones selectivas, ediciones del usuario conservadas, estado desconocido explícito, cuentas y trámites intactos e idempotencia.\n";
}finally{$db->prepare('UPDATE pisco_state SET data=?,revision=? WHERE id=1')->execute([$original['data'],$original['revision']]);}
