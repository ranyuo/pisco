<?php
declare(strict_types=1);
ob_start();require dirname(__DIR__).'/portal-private/bootstrap.php';require dirname(__DIR__).'/portal-private/ChinchaContent.php';
$path=getenv('PORTAL_CONFIG_FILE');if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Solo config.test.php.');
$config=require $path;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base _test. Ejecuta parity.php primero.');
$app=portal_app();$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
// Simular IDs académicos de una instalación anterior: Contabilidad=2, Civil=3.
// Importar nunca debe reinterpretar los expedientes según el orden del catálogo nuevo.
$legacy=$app->bootstrap();$data=$legacy['data'];$data['academico']['progMap']['cont']=2;$data['academico']['progMap']['cc']=3;
$app->save(['revision'=>$legacy['revision'],'data'=>$data]);
$before=$app->bootstrap()['data'];$users=$app->db()->query('SELECT id,username,password,role,profile FROM pisco_users ORDER BY id')->fetchAll();
$checks=0;
function checkChincha(bool $ok,string $label):void{global $checks;if(!$ok)throw new RuntimeException($label);$checks++;echo "OK $label\n";}
// Importación por un usuario sin permiso: ni respaldo ni escritura.
$app->logout();try{ChinchaContent::apply($app);throw new RuntimeException('Se permitió importar sin sesión.');}catch(PortalError $e){checkChincha(in_array($e->status,[401,403],true),'Importación rechazada sin administrador');}
$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
$result=ChinchaContent::apply($app);$after=$app->bootstrap()['data'];
checkChincha(count(array_filter($after['programas'],fn($r)=>$r['estado']==='Publicado'))===8,'Ocho programas publicados');
checkChincha($after['config']['inst']['nombreCorto']==='IESTP “Chincha”','Identidad institucional importada');
checkChincha(is_file(PortalBackups::path($result['backup'])),'Respaldo privado creado antes de importar');
checkChincha($users===$app->db()->query('SELECT id,username,password,role,profile FROM pisco_users ORDER BY id')->fetchAll(),'Cuentas, roles y contraseñas conservados');
foreach(['mensajes','reclamos','expedientes','certificados','suscriptores'] as $key)checkChincha(PortalPolicy::equal($before[$key],$after[$key]),'Se conserva '.$key);
foreach(['estudiantes','notas','certs','titulos','sesiones','publicados','docentes','empresas','ofertas','postulaciones','perfilesLab','docs'] as $key)checkChincha(PortalPolicy::equal($before['academico'][$key],$after['academico'][$key]),'Se conserva academico.'.$key);
foreach($before['academico']['progMap'] as $pid=>$id)checkChincha(($after['academico']['progMap'][$pid]??null)===$id,'Identificador académico conservado: '.$pid);
$count=count($after['academico']['programas']);ChinchaContent::apply($app);$twice=$app->bootstrap()['data'];checkChincha(count($twice['academico']['programas'])===$count,'Repetir importación no duplica programas');
// El padrón público no necesita DNI inventados; si se conoce uno debe ser válido.
$seed=json_decode(file_get_contents(dirname(__DIR__).'/portal-private/data/portal.json'),true,flags:JSON_THROW_ON_ERROR);
$bad=$seed;$bad['becarios'][0]['dni']='123';try{PortalDomain::validate($seed,$bad,['rol'=>'admin']);throw new RuntimeException('Se permitió DNI incorrecto.');}catch(PortalError $e){checkChincha($e->status===422,'DNI incompleto rechazado');}
$duplicate=$seed;$row=$duplicate['becarios'][0];$row['id']='duplicado';$duplicate['becarios'][]=$row;try{PortalDomain::validate($seed,$duplicate,['rol'=>'admin']);throw new RuntimeException('Se permitió becario duplicado.');}catch(PortalError $e){checkChincha($e->status===422,'Beneficiario duplicado sin DNI rechazado por nombre');}
// La ficha pública no equivale a un CV autorizado; una revocación expresa se respeta.
$editable=$app->bootstrap();$personId=$editable['data']['personas'][0]['id'];
$editable['data']['personas'][0]['cv']['autoriza']=['ok'=>false,'revocado'=>true];
$app->save(['revision'=>$editable['revision'],'data'=>$editable['data']]);$app->logout();
$person=PortalPolicy::find($app->bootstrap()['data']['personas'],'id',$personId);
checkChincha(!isset($person['dni'])&&!isset($person['cv']),'Proyección pública sin DNI ni CV sin autorización');
checkChincha(!isset($person['fuente']),'La revocación retira la ficha del directorio público');
$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
echo "$checks comprobaciones de importación aprobadas.\n";ob_end_flush();
