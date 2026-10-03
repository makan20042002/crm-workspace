<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/v10-lib.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$u=api_bearer_user();if(!$u)json_response(['ok'=>false,'error'=>'invalid_or_missing_token'],401);$cid=(int)$u['company_id'];$resource=(string)($_GET['resource']??'me');
$pdo=db();
switch($resource){
 case 'me':require_cap_v10_for_api('crm.read',$u);json_response(['ok'=>true,'user'=>['id'=>$u['user_id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role'],'company_id'=>$cid,'company_name'=>$u['company_name']]]);
 case 'projects':require_cap_v10_for_api('projects.read',$u);$args=[$cid];$scope='';if(!can_v10('projects.write',$u)&&!can_v10('admin.users.read',$u)){$scope=' AND (p.manager_id=? OR EXISTS(SELECT 1 FROM tasks t WHERE t.project_id=p.id AND t.company_id=p.company_id AND t.assignee_id=?))';$args[]=$u['user_id'];$args[]=$u['user_id'];}$st=$pdo->prepare("SELECT p.id,p.name,p.code,p.vertical,p.category,p.status,p.progress,p.start_date,p.due_date,p.updated_at FROM projects p WHERE p.company_id=? AND NOT EXISTS(SELECT 1 FROM v10_record_deletions d WHERE d.company_id=p.company_id AND d.entity_type='projects' AND d.entity_id=p.id)$scope ORDER BY p.updated_at DESC LIMIT 200");$st->execute($args);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
 case 'tasks':require_cap_v10_for_api('projects.read',$u);$args=[$cid];$scope='';if(!can_v10('projects.write',$u)&&!can_v10('admin.users.read',$u)){$scope=' AND t.assignee_id=?';$args[]=$u['user_id'];}$st=$pdo->prepare("SELECT t.id,t.project_id,t.title,t.assignee_id,t.status,t.priority,t.due_date,t.updated_at FROM tasks t WHERE t.company_id=? AND NOT EXISTS(SELECT 1 FROM v10_record_deletions d WHERE d.company_id=t.company_id AND d.entity_type='tasks' AND d.entity_id=t.id)$scope ORDER BY t.updated_at DESC LIMIT 500");$st->execute($args);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
 case 'customers':require_cap_v10_for_api('crm.read',$u);if(!can_v10('crm.write',$u)&&!can_v10('sales.read',$u)&&!can_v10('admin.users.read',$u))json_response(['ok'=>false,'error'=>'forbidden'],403);$st=$pdo->prepare('SELECT id,name,type,phone,email,city,status,updated_at FROM customers c WHERE c.company_id=? AND NOT EXISTS(SELECT 1 FROM v10_record_deletions d WHERE d.company_id=c.company_id AND d.entity_type=\'customers\' AND d.entity_id=c.id) ORDER BY updated_at DESC LIMIT 500');$st->execute([$cid]);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
 default:json_response(['ok'=>false,'error'=>'resource_not_found'],404);
}
function require_cap_v10_for_api(string $cap,array $u): void{if(!can_v10($cap,$u))json_response(['ok'=>false,'error'=>'forbidden','cap'=>$cap],403);}
