<?php
declare(strict_types=1);
final class OfficialContent
{
 public static function apply(PDO $db,bool $once=false): void
 {
  $seed=json_decode(file_get_contents(__DIR__.'/data/portal.json'),true,512,JSON_THROW_ON_ERROR);
$keys=['slides','enlaces','paginas','reconocimientos','programas','personas','jerarquica','egresados','categoriasNoticia','noticias','eventos','tiposDocumento','documentos','tupa','tupaDocs','estadisticas','licenciamiento','inversiones','convocatorias','admisiones','matriculas','tiposBeca','becasPeriodos','becarios','certificados','locales','servicios'];
$db->beginTransaction();
try {
 $row=$db->query('SELECT data,revision FROM pisco_state WHERE id=1 FOR UPDATE')->fetch();
 if(!$row)throw new RuntimeException('Primero instala el portal.');
 $current=json_decode($row['data'],true,512,JSON_THROW_ON_ERROR);
 if($once&&!empty($current['config']['actualizacionWebOficial']))throw new PortalError(410,'Esta actualización de contenido ya fue aplicada. Elimina actualizar-contenido.php desde hPanel.');
 $newPeople=array_column($seed['personas'],'id');
 $legacyPeople=[];
 foreach($current['academico']['docentes']??[] as $teacher){
  $id=$teacher['per']??null;
  if($id&&!in_array($id,$newPeople,true)){
   $person=PortalPolicy::find($current['personas']??[],'id',$id);
   if(!$person)throw new PortalError(422,'Existe un docente académico sin ficha personal. Revisa su vinculación antes de actualizar.');
   $person['estado']='Borrador';$legacyPeople[$id]=$person;
  }
 }
 $backupDir=__DIR__.'/backups';if(!is_dir($backupDir)&&!mkdir($backupDir,0700,true))throw new RuntimeException('No se pudo preparar el respaldo.');
 $backup=$backupDir.'/antes-contenido-oficial-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
 $handle=fopen($backup,'x');if(!$handle)throw new RuntimeException('No se pudo crear el respaldo.');chmod($backup,0600);
 $backupBody=json_encode(['data'=>$current,'revision'=>(int)$row['revision']],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
 if(fwrite($handle,$backupBody)!==strlen($backupBody)){fclose($handle);throw new RuntimeException('Respaldo incompleto.');}fclose($handle);
 foreach($keys as $key)$current[$key]=$seed[$key];
 $current['personas']=array_merge($current['personas'],array_values($legacyPeople));
 foreach(['inst','redes','whatsapp','seo','home','popup'] as $key)$current['config'][$key]=$seed['config'][$key];
 // Cambia únicamente la identidad de los documentos; conserva preferencias y datos de operación.
 foreach(['nombre','siglas','director','direccion','web','horarioAtencion','docs'] as $key)$current['academico']['cfg'][$key]=$seed['academico']['cfg'][$key];
 $current['config']['contenidoOficial']='2026-10-27';
 if($once)$current['config']['actualizacionWebOficial']=date(DATE_ATOM);
 $db->prepare('UPDATE pisco_state SET data=?,revision=revision+1 WHERE id=1')->execute([json_encode($current,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]);
 $db->commit();
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}

 }
}
