<?php
declare(strict_types=1);
/** RUTAS CRUD: preparan formularios y envían las peticiones a la API. */
function claves_de(array $config,array $origen): ?array {
    $claves=[];
    foreach ($config['pk'] as $k) {
        $v=$origen[$k]??null;
        if ((!is_string($v)&&!is_int($v)) || trim((string)$v)==='') return null;
        $claves[$k]=(string)$v;
    }
    return $claves;
}
function etiqueta_fk(array $fila,array $fk): string {
    $partes=[];
    foreach ($fk['texto'] as $c) if (isset($fila[$c])&&is_scalar($fila[$c])) $partes[]=(string)$fila[$c];
    return (implode(' ',$partes) ?: 'Registro').' (ID '.(string)$fila[$fk['id']].')';
}
/** Consulta cada tabla relacionada una sola vez por petición. */
function catalogos(array $config): array {
    $cache=[]; $opciones=[]; $avisos=[];
    foreach ($config['campos'] as $k=>$campo) {
        $fk=$campo['fk']; if ($fk===null) continue;
        $r=$fk['recurso']; $cache[$r]??=comprobar_autorizacion(consultar_registros($r));
        $opciones[$k]=[];
        if (!$cache[$r]['ok']) { $avisos[$k]='No se pudo cargar '.$campo['etiqueta'].'.'; continue; }
        foreach ($cache[$r]['filas'] as $fila) {
            $id=$fila[$fk['id']]??null;
            if ((!is_int($id)&&!is_string($id))||$id==='') continue;
            if (array_key_exists('activo',$fila)&&in_array($fila['activo'],[false,0,'0','false'],true)) continue;
            $opciones[$k][(string)$id]=etiqueta_fk($fila,$fk);
        }
        if ($opciones[$k]===[] && $campo['requerido']) $avisos[$k]='No hay registros disponibles para '.$campo['etiqueta'].'.';
    }
    return [$opciones,$avisos];
}
function preparar_datos(array $config,array $post,bool $edicion,array $opciones): array {
    $datos=[]; $errores=[];
    foreach ($config['campos'] as $k=>$campo) {
        if ($edicion && in_array($k,$config['pk'],true) && !($config['pk_en_edicion']??false)) continue;
        $valor=$post[$k]??'';
        if (!is_string($valor)) { $errores[]='Campo inválido: '.$campo['etiqueta']; continue; }
        $valor=trim($valor);
        if ($valor==='') {
            if ($campo['requerido']) $errores[]='Completa '.$campo['etiqueta'].'.';
            else $datos[$k]=null;
            continue;
        }
        if ($campo['fk']!==null && !array_key_exists($valor,$opciones[$k]??[])) { $errores[]='Selecciona una opción válida para '.$campo['etiqueta'].'.'; continue; }
        if ($campo['tipo']==='int') {
            $numero=filter_var($valor,FILTER_VALIDATE_INT);
            if ($numero===false) { $errores[]='Ingresa un entero válido para '.$campo['etiqueta'].'.'; continue; }
            $valor=$numero;
        }
        $datos[$k]=$valor;
    }
    // Maestro-detalle: se envía la colección de semilleros junto con el grupo.
    if ($config['detalle']??false) {
        $datos['detalle']=[]; $nombres=$post['detalle_nombre']??[];
        if (!is_array($nombres)) $errores[]='Detalle inválido.';
        else foreach ($nombres as $nombre) {
            if (!is_string($nombre)) { $errores[]='Nombre de semillero inválido.'; continue; }
            if (trim($nombre)!=='') $datos['detalle'][]=['nombre'=>trim($nombre)];
        }
    }
    return [$datos,$errores];
}
function procesar_recurso(string $vista,array $config,string $metodo): void {
    $accion=$_GET['accion']??'listar';
    if (!is_string($accion)||!in_array($accion,['listar','ver','crear','editar','retirar'],true)) { http_response_code(404); exit('Acción no encontrada.'); }
    $escritura=in_array($accion,['crear','editar','retirar'],true);
    if ($escritura && (!es_admin()||!in_array($accion,$config['operaciones'],true))) { http_response_code(403); plantilla('Acceso denegado',__DIR__.'/../vistas/error.php',['mensaje'=>'Operación no habilitada para tu usuario.']); return; }
    if ($metodo==='POST') { if (!$escritura) { http_response_code(405); exit; } verificar_csrf(); }
    if ($accion==='retirar'&&$metodo!=='POST') { header('Allow: POST'); http_response_code(405); exit; }
    $claves=in_array($accion,['ver','editar','retirar'],true)?claves_de($config,$_GET):[];
    if ($claves===null) { http_response_code(400); exit('Faltan identificadores.'); }
    $errores=[]; $valores=[];
    if ($accion==='retirar') {
        $res=comprobar_autorizacion(cliente_api_peticion('DELETE',ruta_api($vista,$claves)));
        $_SESSION['flash']=['ok'=>$res['ok'],'mensaje'=>$res['ok']?'Registro retirado.':$res['mensaje']]; redirigir(destino($vista));
    }
    if ($accion==='listar') {
        $res=comprobar_autorizacion(consultar_registros($vista));
        [$opciones,$avisos]=$res['ok']&&$res['filas']!==[]?catalogos($config):[[],[]];
        $flash=$_SESSION['flash']??null; unset($_SESSION['flash']);
        plantilla($config['titulo'],__DIR__.'/../vistas/lista.php',compact('vista','config','res','opciones','avisos','flash')); return;
    }
    if ($accion==='editar'||$accion==='ver') {
        $res=comprobar_autorizacion(consultar_registro($vista,$claves));
        if (!$res['ok']) { plantilla('Error',__DIR__.'/../vistas/error.php',['mensaje'=>$res['mensaje']]); return; }
        $valores=$res['fila'];
        // Evita reemplazar el detalle con una lista vacía si el GET no lo entregó.
        if ($accion==='editar' && ($config['detalle']??false) && (!isset($valores['detalle']) || !is_array($valores['detalle']) || !array_is_list($valores['detalle']))) {
            plantilla('Detalle no disponible',__DIR__.'/../vistas/error.php',['mensaje'=>'La API no entregó los semilleros del grupo. No se puede editar sin verificar el detalle.']); return;
        }
        if ($accion==='ver') { plantilla('Detalle',__DIR__.'/../vistas/detalle.php',compact('vista','config','valores')); return; }
    }
    $edicion=$accion==='editar'; [$opciones,$avisos]=catalogos($config);
    if ($metodo==='POST') {
        $post=$edicion?$claves+$_POST:$_POST;
        [$datos,$errores]=preparar_datos($config,$post,$edicion,$opciones);
        $valores=array_replace($valores,$datos);
        if ($avisos!==[]) $errores[]='Resuelve los problemas de las listas desplegables antes de guardar.';
        if ($errores===[]) {
            $res=comprobar_autorizacion(cliente_api_peticion($edicion?($config['metodo_edicion']??'PUT'):'POST',ruta_api($vista,$claves),$datos));
            if ($res['ok']) { $_SESSION['flash']=['ok'=>true,'mensaje'=>'Registro guardado correctamente.']; redirigir(destino($vista)); }
            $errores[]=$res['mensaje'];
        }
        // Mantiene los datos ingresados para que el usuario pueda corregirlos.
        foreach ($config['campos'] as $k=>$c) if (isset($post[$k])&&is_string($post[$k])) $valores[$k]=$post[$k];
    }
    plantilla($edicion?'Editar':'Registrar',__DIR__.'/../vistas/formulario.php',compact('vista','config','edicion','claves','valores','errores','opciones','avisos'));
}
