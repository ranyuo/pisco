<?php
declare(strict_types=1);

/** Importa contenido público sin borrar cuentas, solicitudes ni expedientes académicos. */
final class ChinchaContent
{
    public static function apply(PortalApp $app): array
    {
        $app->requireAdmin();
        $seed=json_decode(file_get_contents(__DIR__.'/data/portal.json'),true,flags:JSON_THROW_ON_ERROR);
        if(($seed['config']['inst']['nombreCorto']??'')!=='IESTP PISCO')throw new PortalError(422,'El paquete no contiene la información institucional de Pisco.');
        $before=$app->bootstrap();$next=$before['data'];
        foreach(['slides','enlaces','paginas','programas','personas','jerarquica','categoriasNoticia','noticias','eventos','admisiones','matriculas','licenciamiento','locales','servicios'] as $key)$next[$key]=$seed[$key];
        // Las personas y los programas antiguos pueden estar vinculados a cuentas o expedientes.
        // Se conservan fuera de la publicación, con sus identificadores originales.
        foreach(['personas','programas'] as $key){
            $ids=array_column($next[$key],'id');
            foreach($before['data'][$key] as $old)if(!in_array($old['id'],$ids,true)){$old['estado']=$key==='programas'?'Borrador':'Despublicado';$next[$key][]=$old;}
        }
        // Conservar unidades ya asignadas: una actualización de textos no rompe docencia.
        foreach($before['data']['personas'] as $person)foreach($person['uds']??[] as $assignment){
            $pid=$assignment['programaId'];$uid=$assignment['udId'];$oldUnit=null;
            foreach($before['data']['programas'] as $program)if($program['id']===$pid)foreach($program['modulos']??[] as $module)foreach($module['uds']??[] as $unit)if($unit['id']===$uid)$oldUnit=$unit;
            if(!$oldUnit)continue;
            foreach($next['programas'] as &$program)if($program['id']===$pid){
                $found=false;foreach($program['modulos']??[] as $module)foreach($module['uds']??[] as $unit)if($unit['id']===$uid)$found=true;
                if($found)continue;
                $matched=false;foreach($program['modulos'] as &$module)foreach($module['uds'] as &$unit)if(!$matched&&PortalRules::normal($unit['nombre'])===PortalRules::normal($oldUnit['nombre'])&&(string)$unit['semestre']===(string)$oldUnit['semestre']){
                    $newId=$unit['id'];$unit['id']=$uid;$matched=true;
                    foreach($next['personas'] as &$updated)foreach($updated['uds']??[] as $key=>$ref)if($ref['programaId']===$pid&&$ref['udId']===$newId)$updated['uds'][$key]['udId']=$uid;
                    unset($updated);
                }unset($module,$unit);
                if(!$matched&&isset($program['modulos'][0]))$program['modulos'][0]['uds'][]=$oldUnit;
            }unset($program);
        }
        foreach(['inst','redes','whatsapp','seo','correoContacto','correoReclamos','popup','home','tupaReferencial','contenidoPiscoPrototipo'] as $key)$next['config'][$key]=$seed['config'][$key];
        // El prototipo no reemplaza documentos, tarifas ni otros registros ausentes.
        foreach($next['documentos'] as &$document){
            if(str_starts_with($document['archivo']['url']??'','/assets/chincha/')&&!str_starts_with($document['nombre'],'Referencia pendiente de validar')){
                $document['nombre']='Referencia pendiente de validar — '.$document['nombre'];
                $document['descripcion']=trim(($document['descripcion']??'').' Documento conservado, pendiente de validación para Pisco.');
            }
        }unset($document);
        foreach(['mpv','lr','bolsa'] as $section)foreach(['correo','telefono','prefijo','responsable','cargo','jefe'] as $field)if(isset($seed['config'][$section][$field]))$next['config'][$section][$field]=$seed['config'][$section][$field];
        $next['config']['temas']['pisco']['label']=$seed['config']['temas']['pisco']['label'];
        foreach($next['academico']['areas'] as &$area)if(str_contains($area['correo']??'','iestpchincha.edu.pe'))$area['correo']=$seed['config']['inst']['correo'];unset($area);
        $academic=&$next['academico'];
        foreach(['nombre','siglas','director','direccion','web','horarioAtencion'] as $key)$academic['cfg'][$key]=$seed['academico']['cfg'][$key];
        $academic['cfg']['docs']['logoIzq']=$seed['academico']['cfg']['docs']['logoIzq'];
        // No reasignar el ID numérico de un programa existente: identifica a alumnos y documentos.
        $last=max([0,...array_column($academic['programas'],'id')]);
        foreach($seed['programas'] as $program){
            $id=$academic['progMap'][$program['id']]??null;
            $exists=$id!==null&&PortalPolicy::find($academic['programas'],'id',$id);
            if(!$exists){$id=++$last;$academic['programas'][]=['id'=>$id,'cod'=>$program['codigo'],'nombre'=>$program['nombre'],'dur'=>'3 años','sem'=>6,'activo'=>true,'pid'=>$program['id']];}
            foreach($academic['programas'] as &$record)if($record['id']===$id){$record['cod']=$program['codigo'];$record['nombre']=$program['nombre'];$record['pid']=$program['id'];$record['activo']=($program['estado']??'')==='Publicado';$record['estado']=$record['activo']?'Activo':'Inactivo';}
            unset($record);
            $academic['progMap'][$program['id']]=$id;
        }
        unset($academic);
        $backup=PortalBackups::create($app,'antes-de-importar-pisco');
        $result=$app->save(['revision'=>$before['revision'],'data'=>$next]);
        return ['revision'=>$result['revision'],'backup'=>$backup['id']];
    }
}
