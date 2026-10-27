<?php
declare(strict_types=1);
require dirname(__DIR__).'/portal-private/bootstrap.php';
$p=getenv('PORTAL_CONFIG_FILE');if(!$p||!str_ends_with($p,'config.test.php'))throw new RuntimeException('Solo configuración de pruebas.');$config=require $p;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base de pruebas.');
$db=portal_app()->db();$name='pisco_fresh_'.bin2hex(random_bytes(6)).'_test';$db->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
try{
 $freshConfig=[...$config,'db_name'=>$name];$fresh=new PortalApp($freshConfig);
 if($fresh->installed())throw new RuntimeException('Una base vacía aparece instalada.');
 $fresh->install(['install_key'=>$config['install_key'],'username'=>'admin','name'=>'Instalación nueva','email'=>'fresh@example.test','password'=>'PruebaNueva-Segura-2026','password_confirmation'=>'PruebaNueva-Segura-2026']);
 if(!$fresh->installed())throw new RuntimeException('La instalación no creó el administrador.');
 $fresh->login(['username'=>'admin','password'=>'PruebaNueva-Segura-2026']);$state=$fresh->bootstrap()['data'];
 if(count($state['programas'])!==5||count($state['noticias'])!==7)throw new RuntimeException('No se cargó el contenido oficial.');
 if($state['certificados']||$state['academico']['estudiantes'])throw new RuntimeException('Hay certificados o alumnos ficticios.');
 foreach($state['programas'] as $program)if(preg_match_all('/./us',$program['resumen'])>300||!$program['campoItems'])throw new RuntimeException('Un programa no es editable.');
 $bad=$state;$bad['personas'][0]['dni']='ABC12345';
 try{$fresh->save(['data'=>$bad,'revision'=>$fresh->bootstrap()['revision']]);throw new RuntimeException('Se aceptó un DNI no válido.');}catch(PortalError $e){if($e->status!==422)throw $e;}
 $bad=$state;$bad['locales'][0]['lat']=-13.7;$bad['locales'][0]['lng']='';
 try{$fresh->save(['data'=>$bad,'revision'=>$fresh->bootstrap()['revision']]);throw new RuntimeException('Se aceptaron coordenadas incompletas.');}catch(PortalError $e){if($e->status!==422)throw $e;}
 echo "OK instalación en base vacía, acceso administrativo y carga de contenidos oficiales editables.\n";
}finally{$db->exec('DROP DATABASE `'.$name.'`');}
try{(new PortalApp([...$config,'db_name'=>$name]))->installed();throw new RuntimeException('Se aceptó una base inexistente.');}catch(PortalError $e){if($e->status!==503||!str_contains($e->getMessage(),'no existe'))throw $e;}
echo "OK diagnóstico de base inexistente sin exponer credenciales.\n";
