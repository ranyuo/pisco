<?php
declare(strict_types=1);
// Importación explícita: nunca se ejecuta al abrir una página pública.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/bootstrap.php';
$seed=json_decode(file_get_contents(__DIR__.'/data/portal.json'),true,512,JSON_THROW_ON_ERROR);
$keys=['slides','enlaces','paginas','reconocimientos','programas','personas','jerarquica','egresados','categoriasNoticia','noticias','eventos','tiposDocumento','documentos','tupa','tupaDocs','estadisticas','licenciamiento','inversiones','convocatorias','admisiones','matriculas','tiposBeca','becasPeriodos','becarios','certificados','locales','servicios'];
// Solo reemplaza el contenido público inicial; las cuentas, archivos privados y trámites se conservan.
$app=portal_app();$db=$app->db();
if(!in_array('--apply',$argv,true)){echo "Simulación: se reemplazarán las colecciones públicas y la identidad institucional. Se conservan cuentas, contraseñas, sesiones, expedientes, reclamos, mensajes y registros académicos. Usa --apply para aplicar con respaldo local.\n";exit;}
$db->beginTransaction();
try {
 $row=$db->query('SELECT data,revision FROM pisco_state WHERE id=1 FOR UPDATE')->fetch();
 if(!$row)throw new RuntimeException('Primero instala el portal.');
 $current=json_decode($row['data'],true,512,JSON_THROW_ON_ERROR);
 $newPeople=array_column($seed['personas'],'id');
 foreach($current['academico']['docentes']??[] as $teacher){
  if(!empty($teacher['per'])&&!in_array($teacher['per'],$newPeople,true))throw new RuntimeException('La instalación contiene docentes académicos vinculados a personas anteriores. Concilia sus identidades antes de importar; no se modificó la base.');
 }
 $backupDir=__DIR__.'/backups';if(!is_dir($backupDir)&&!mkdir($backupDir,0700,true))throw new RuntimeException('No se pudo preparar el respaldo.');
 $backup=$backupDir.'/antes-contenido-oficial-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
 $handle=fopen($backup,'x');if(!$handle)throw new RuntimeException('No se pudo crear el respaldo.');chmod($backup,0600);
 $backupBody=json_encode(['data'=>$current,'revision'=>(int)$row['revision']],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
 if(fwrite($handle,$backupBody)!==strlen($backupBody)){fclose($handle);throw new RuntimeException('Respaldo incompleto.');}fclose($handle);
 foreach($keys as $key)$current[$key]=$seed[$key];
 foreach(['inst','redes','whatsapp','seo','home','popup'] as $key)$current['config'][$key]=$seed['config'][$key];
 // Cambia únicamente la identidad de los documentos; conserva preferencias y datos de operación.
 foreach(['nombre','siglas','director','direccion','web','horarioAtencion','docs'] as $key)$current['academico']['cfg'][$key]=$seed['academico']['cfg'][$key];
 $current['config']['contenidoOficial']='2026-10-27';
 $db->prepare('UPDATE pisco_state SET data=?,revision=revision+1 WHERE id=1')->execute([json_encode($current,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
 $db->commit();echo "Contenido oficial importado. Respaldo protegido en portal-private/backups. Cuentas y trámites conservados.\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
