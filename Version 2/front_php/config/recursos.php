<?php
declare(strict_types=1);
/**
 * CONFIGURACIÓN: catálogo de recursos autorizados. Cada campo indica su tipo.
 * fk define la tabla y las columnas con las que se construye el desplegable.
 * pk es la lista ordenada de llaves primarias (una o varias).
 * Los nombres provienen de tu index.php y tus formularios; deben cotejarse con la API.
 */
function campo(string $etiqueta, string $tipo='text', bool $requerido=true, ?array $fk=null): array {
    return compact('etiqueta','tipo','requerido','fk');
}
function fk(string $etiqueta,string $recurso,string $id='id',array $texto=['nombre'],string $tipo='int'): array {
    return campo($etiqueta,$tipo,true,compact('recurso','id','texto'));
}
function recurso(string $titulo,string $grupo,array $pk,array $campos,array $operaciones,array $extra=[]): array {
    return compact('titulo','grupo','pk','campos','operaciones') + $extra;
}
$crud=['crear','editar','retirar'];
$puente=['crear','retirar'];
$recursos=[
 'area_conocimiento'=>recurso('Áreas de conocimiento','Catálogos',['id'],[
    'id'=>campo('ID','int'),'gran_area'=>campo('Gran área'),'area'=>campo('Área'),'disciplina'=>campo('Disciplina')],$crud,['pk_en_edicion'=>true,'nota'=>'Tu JavaScript V1 usa ID numérico; confirma el tipo real en el SQL V2.']),
 'objetivo_desarrollo_sostenible'=>recurso('Objetivos de desarrollo sostenible','Catálogos',['id'],[
    'id'=>campo('ID','int'),'nombre'=>campo('Nombre'),'categoria'=>campo('Categoría')],$crud,['pk_en_edicion'=>true]),
 'area_aplicacion'=>recurso('Áreas de aplicación','Catálogos',['id'],['id'=>campo('ID','int'),'nombre'=>campo('Nombre')],$crud,['pk_en_edicion'=>true]),
 'termino_clave'=>recurso('Términos clave','Catálogos',['termino'],['termino'=>campo('Término'),'termino_ingles'=>campo('Término en inglés','text',false)],$crud),
 'universidad'=>recurso('Universidades','Catálogos',['id'],['id'=>campo('ID','int'),'nombre'=>campo('Nombre'),'tipo'=>campo('Tipo'),'ciudad'=>campo('Ciudad')],$crud,['pk_en_edicion'=>true]),
 'linea_investigacion'=>recurso('Líneas de investigación','Catálogos',['id'],['nombre'=>campo('Nombre'),'descripcion'=>campo('Descripción','textarea')],$crud,['auto_id'=>true]),
 'docente'=>recurso('Docentes','Investigación',['cedula'],['cedula'=>campo('Cédula','int'),'nombres'=>campo('Nombres'),'apellidos'=>campo('Apellidos')],$crud,['metodo_edicion'=>'PATCH','nota'=>'Se usan los tres campos de tu formulario actual; comprueba si tu API exige otros.']),
 'grupo_investigacion'=>recurso('Grupos de investigación','Investigación',['id'],[
    'gruplac'=>campo('GrupLAC'),'categoria'=>campo('Categoría'),'convocatoria'=>campo('Convocatoria'),'ambito'=>campo('Ámbito'),
    'universidad_id'=>fk('Universidad','universidad')],$crud,['auto_id'=>true,'detalle'=>true]),
 'semillero'=>recurso('Semilleros','Investigación',['id'],['nombre'=>campo('Nombre')],[],['nota'=>'Tu cliente V2 expone semilleros en solo lectura; se gestionan desde el maestro-detalle de grupos.']),
 'participa_grupo'=>recurso('Docentes por grupo','Relaciones',['docente_cedula','grupo_id'],[
    'docente_cedula'=>fk('Docente','docente','cedula',['nombres','apellidos']), 'grupo_id'=>fk('Grupo','grupo_investigacion','id',['gruplac'])],$puente),
 'participa_semillero'=>recurso('Docentes por semillero','Relaciones',['docente_cedula','semillero_id'],[
    'docente_cedula'=>fk('Docente','docente','cedula',['nombres','apellidos']), 'semillero_id'=>fk('Semillero','semillero')],$puente),
 'grupo_linea'=>recurso('Grupos por línea','Relaciones',['grupo_id','linea_id'],[
    'grupo_id'=>fk('Grupo','grupo_investigacion','id',['gruplac']), 'linea_id'=>fk('Línea de investigación','linea_investigacion')],$puente),
 'semillero_linea'=>recurso('Semilleros por línea','Relaciones',['semillero_id','linea_id'],[
    'semillero_id'=>fk('Semillero','semillero'),'linea_id'=>fk('Línea de investigación','linea_investigacion')],$puente),
 'ac_linea'=>recurso('Áreas de conocimiento por línea','Relaciones',['area_conocimiento_id','linea_id'],[
    'area_conocimiento_id'=>fk('Área de conocimiento','area_conocimiento','id',['disciplina']), 'linea_id'=>fk('Línea de investigación','linea_investigacion')],$puente),
 'ods_linea'=>recurso('ODS por línea','Relaciones',['ods_id','linea_id'],[
    'ods_id'=>fk('ODS','objetivo_desarrollo_sostenible'),'linea_id'=>fk('Línea de investigación','linea_investigacion')],$puente),
 'aa_linea'=>recurso('Áreas de aplicación por línea','Relaciones',['area_aplicacion_id','linea_id'],[
    'area_aplicacion_id'=>fk('Área de aplicación','area_aplicacion'),'linea_id'=>fk('Línea de investigación','linea_investigacion')],$puente),
];
// Referencias adicionales detectadas. No se inventan rutas CRUD ni se declaran tablas verificadas.
$pendientes = [
 'usuario'=>'Usuarios (ejemplo de autenticación)', 'rol'=>'Roles (ejemplo)', 'rol_usuario'=>'Roles por usuario (ejemplo)',
 'convocatoria'=>'Convocatorias (formulario recibido)', 'proyecto'=>'Proyectos (formulario recibido)',
 'convocatoria_proyecto'=>'Convocatorias por proyecto (formulario recibido)'
];
