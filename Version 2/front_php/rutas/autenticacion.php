<?php
declare(strict_types=1);
/** AUTENTICACIÓN: mantiene el JWT solo en el servidor y protege las sesiones. */
function redirigir(string $url): never { header('Location: '.$url,true,303); exit; }
function escapar(mixed $valor): string { return htmlspecialchars(is_scalar($valor) ? (string)$valor : '',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function destino(string $vista, array $params=[]): string { return '/?'.http_build_query(['vista'=>$vista]+$params,'','&',PHP_QUERY_RFC3986); }
function sesion_valida(): bool { return is_string($_SESSION['jwt']??null) && $_SESSION['jwt']!=='' && (int)($_SESSION['expira']??0)>time(); }
function es_admin(): bool { return in_array('admin', (array)($_SESSION['usuario']['roles']??[]),true); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function verificar_csrf(): void {
    $valor=$_POST['csrf']??null;
    if (!is_string($valor) || !hash_equals(csrf(),$valor)) { http_response_code(403); exit('Formulario inválido. Actualiza la página.'); }
}
function cerrar_sesion(): void {
    $_SESSION=[];
    $p=session_get_cookie_params();
    setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$p['path'],'domain'=>$p['domain'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);
    session_destroy();
}
/** Contrato tomado del ejemplo: POST /api/login, datos.token y datos.usuario.roles.
 * Si tu API V2 usa otro contrato, ajusta esta función; no existe acceso de emergencia.
 */
function iniciar_sesion(string $username,string $password): array {
    $res=cliente_api_peticion('POST','/api/login',['username'=>$username,'password'=>$password]);
    if (!$res['ok']) return $res;
    $d=$res['respuesta']['datos']??[];
    $token=$d['token']??null; $usuario=$d['usuario']??null;
    if (!is_string($token) || $token==='' || preg_match('/[\r\n]/',$token) || !is_array($usuario) || !is_array($usuario['roles']??null)) {
        return ['ok'=>false,'mensaje'=>'El contrato de login de la API no coincide con el ejemplo.'];
    }
    $duracion=filter_var($d['expira_en']??3600,FILTER_VALIDATE_INT);
    if ($duracion===false || $duracion<=0) return ['ok'=>false,'mensaje'=>'La API devolvió una vigencia inválida.'];
    session_regenerate_id(true);
    $_SESSION=['jwt'=>$token,'expira'=>time()+min($duracion,86400), 'usuario'=>['username'=>is_string($usuario['username']??null)?$usuario['username']:$username, 'roles'=>$usuario['roles']]];
    return ['ok'=>true];
}
/** Un 401 de la API invalida la sesión local; 403 se conserva como error de permisos. */
function comprobar_autorizacion(array $res): array {
    if (($res['estado']??0)===401) { cerrar_sesion(); redirigir(destino('login')); }
    return $res;
}
