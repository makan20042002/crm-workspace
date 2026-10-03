<?php
require __DIR__.'/bootstrap.php';
$method=$_SERVER['REQUEST_METHOD']??'GET';if($method!=='POST'){http_response_code(405);header('Allow: POST');exit('Method not allowed');}verify_csrf();
$u=current_user();
if($u){
    try{db()->prepare('INSERT INTO security_events(company_id,user_id,event_type,severity,email,ip_address,user_agent,metadata) VALUES(?,?,?,?,?,?,?,?)')->execute([$u['company_id'],$u['id'],'logout','info',$u['email'],client_ip(),substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255),json_encode([])]);}catch(Throwable $e){}
}
$_SESSION=[];
if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}
session_destroy();
header('Location:login.php');exit;
