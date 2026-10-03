<?php
declare(strict_types=1);
require dirname(__DIR__).'/v10-lib.php';
$u=require_login();$cid=(int)$u['company_id'];require_cap_v10('crm.read');$pdo=db();$method=$_SERVER['REQUEST_METHOD']??'GET';if($method!=='GET')verify_csrf();$action=(string)($_GET['action']??'list');
try{switch($action){
 case 'list':$st=$pdo->prepare('SELECT id,title,body,read_at,created_at FROM notifications WHERE company_id=? AND user_id=? ORDER BY created_at DESC LIMIT 100');$st->execute([$cid,$u['id']]);$items=$st->fetchAll();$unread=0;foreach($items as $x)if(!$x['read_at'])$unread++;json_response(['ok'=>true,'items'=>$items,'unread'=>$unread]);
 case 'read':$b=body_json();$id=(int)req($b,'id',0);if($id)$pdo->prepare('UPDATE notifications SET read_at=NOW() WHERE id=? AND company_id=? AND user_id=?')->execute([$id,$cid,$u['id']]);else$pdo->prepare('UPDATE notifications SET read_at=NOW() WHERE company_id=? AND user_id=?')->execute([$cid,$u['id']]);json_response(['ok'=>true]);
 case 'delete':$b=body_json();$id=(int)req($b,'id',0);$pdo->prepare('DELETE FROM notifications WHERE id=? AND company_id=? AND user_id=?')->execute([$id,$cid,$u['id']]);json_response(['ok'=>true]);
 default:json_response(['ok'=>false,'error'=>'unknown_action'],404);
}}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'server_error','message'=>envv('APP_ENV','local')==='local'?$e->getMessage():'Internal server error'],500);}
