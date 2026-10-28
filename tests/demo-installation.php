<?php
declare(strict_types=1);
ob_start();require dirname(__DIR__).'/portal-private/bootstrap.php';
$path=getenv('PORTAL_CONFIG_FILE');if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Solo config.test.php.');
$config=require $path;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base _test.');
$app=portal_app();if($app->installed())throw new RuntimeException('Utiliza una base de pruebas nueva y vacía.');
$password='PruebaChincha-2026!';$app->install(['install_key'=>$config['install_key'],'username'=>'admin','name'=>'Administrador QA','email'=>'admin@example.test','password'=>$password,'password_confirmation'=>$password]);
$app->login(['username'=>'admin','password'=>$password]);$initial=$app->bootstrap()['data'];
if($initial['config']['inst']['nombreCorto']!=='IESTP “Chincha”'||count($initial['programas'])!==8)throw new RuntimeException('Identidad o catálogo incorrectos.');
foreach(['estudiantes','certs','notas','titulos','empresas','ofertas'] as $key)if($initial['academico'][$key]!==[])throw new RuntimeException('Datos operativos ficticios en '.$key);
if($initial['certificados']!==[])throw new RuntimeException('Certificados ficticios publicados.');echo "OK instalación de Chincha sin registros operativos ficticios\n";
$before=$app->bootstrap();$data=$before['data'];$original=$data['noticias'][0]['titulo'];$data['noticias'][0]['titulo']='Noticia de prueba de persistencia';$app->save(['revision'=>$before['revision'],'data'=>$data]);$app->logout();
$public=$app->bootstrap()['data'];if($public['noticias'][0]['titulo']!=='Noticia de prueba de persistencia')throw new RuntimeException('No se conserva la edición.');
foreach($public['personas'] as $person)if(isset($person['dni']))throw new RuntimeException('Se expuso un DNI.');
foreach($public['documentos'] as $document)if(!str_starts_with($document['archivo']['url']??'','/assets/chincha/'))throw new RuntimeException('Documento sin archivo original.');
echo "OK edición persistente, proyección pública sin DNI y documentos locales\n";
$app->login(['username'=>'admin','password'=>$password]);$before=$app->bootstrap();$data=$before['data'];$data['noticias'][0]['titulo']=$original;$app->save(['revision'=>$before['revision'],'data'=>$data]);$app->logout();ob_end_flush();
