<?php
declare(strict_types=1);
ob_start();
require dirname(__DIR__).'/portal-private/bootstrap.php';
$path=getenv('PORTAL_CONFIG_FILE');
if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Solo config.test.php.');
$config=require $path;
if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base _test.');
$app=portal_app();$db=$app->db();$count=0;
function qaCheck(bool $value,string $label):void{global $count;if(!$value)throw new RuntimeException($label);$count++;echo "OK $label\n";}
function qaAdmin():void{global $db;$u=$db->query("SELECT id,auth_version FROM pisco_users WHERE role='admin' AND active=1 ORDER BY id LIMIT 1")->fetch();if(!$u)throw new RuntimeException('Primero instala la base de pruebas.');$_SESSION['user_id']=(int)$u['id'];$_SESSION['auth_version']=(int)$u['auth_version'];}
qaAdmin();$suffix=bin2hex(random_bytes(5));$password='Prueba-Regresion-2026!';
foreach([['estado'=>'Inactivo'],['active'=>false],['active'=>0],['estado'=>'Activo'],[]] as $i=>$state){
    qaAdmin();$name='qa-'.$suffix.'-'.$i;
    $result=$app->accountOperation(['action'=>'create','account'=>['username'=>$name,'name'=>'Cuenta QA','email'=>$name.'@example.test','role'=>'imagen','password'=>$password,...$state]]);
    $expected=$i<3?'Inactivo':'Activo';
    qaCheck($result['user']['estado']===$expected,'Alta respeta estado '.$i.': '.$expected);
    $app->logout();
    if($expected==='Inactivo'){
        try{$app->login(['username'=>$name,'password'=>$password]);throw new RuntimeException('Una cuenta inactiva inició sesión.');}
        catch(PortalError $e){qaCheck($e->status===422,'Cuenta inactiva no puede iniciar sesión '.$i);}
    }else{$app->login(['username'=>$name,'password'=>$password]);qaCheck($app->user()['usuario']===$name,'Cuenta activa inicia sesión '.$i);}
}
qaAdmin();
$bytes="%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF";
$file=PortalFiles::store($app,'archivo-original.pdf',$bytes);
$label='Resolución institucional personalizada '.$suffix;
$aliases=[];
$saved=PortalFiles::persist($app,[...$file,'nombre'=>$label,'original'=>'falso.pdf','sha256'=>'falso','size'=>1,'type'=>'text/html']);
qaCheck($saved['nombre']===$label,'Referencia de archivo conserva nombre editorial');
qaCheck($saved['original']==='archivo-original.pdf'&&$saved['sha256']===hash('sha256',$bytes)&&$saved['size']===strlen($bytes)&&$saved['type']==='application/pdf','Los metadatos técnicos se verifican contra el servidor');
$inline=PortalFiles::persist($app,['nombre'=>$label,'original'=>'inline-original.pdf','data'=>'data:application/pdf;base64,'.base64_encode($bytes)],$aliases);
qaCheck($inline['nombre']===$label&&$inline['original']==='inline-original.pdf'&&!isset($inline['data']),'Archivo codificado conserva título y nombre original separados');
$app->logout();
try{PortalFiles::metadata($app,$inline);throw new RuntimeException('Se expuso un archivo privado.');}
catch(PortalError $e){qaCheck($e->status===403,'Un título personalizado no concede acceso anónimo a un archivo privado');}
qaAdmin();
$before=$app->bootstrap();$data=$before['data'];
$data['licenciamiento']['documentos'][]=[...$file,'nombre'=>$label,'doc'=>$file];
$app->save(['revision'=>$before['revision'],'data'=>$data]);
$reloaded=$app->bootstrap()['data']['licenciamiento']['documentos'];
$stored=array_values(array_filter($reloaded,fn($d)=>($d['fileId']??null)===$file['fileId']))[0]??null;
qaCheck($stored!==null&&$stored['nombre']===$label,'Título de licenciamiento persiste tras guardar y leer');
qaCheck($stored['doc']['original']==='archivo-original.pdf'&&$stored['doc']['sha256']===hash('sha256',$bytes),'PDF de licenciamiento conserva identidad e integridad');
$next=$app->bootstrap();$data=$next['data'];
foreach($data['licenciamiento']['documentos'] as &$document)if(($document['fileId']??null)===$file['fileId'])$document['nombre']=$label.' editada';unset($document);
$app->save(['revision'=>$next['revision'],'data'=>$data]);
$rows=$app->bootstrap()['data']['licenciamiento']['documentos'];
qaCheck(count(array_filter($rows,fn($d)=>($d['fileId']??null)===$file['fileId']&&$d['nombre']===$label.' editada'))===1,'Edición posterior del título conserva el PDF');
echo "\n$count comprobaciones de regresión QA aprobadas.\n";ob_end_flush();
