<?php
declare(strict_types=1);
require dirname(__DIR__).'/v10-lib.php';
$u=require_login();$cid=(int)$u['company_id'];require_cap_v10('crm.read');$pdo=db();$q=trim((string)($_GET['q']??''));if(mb_strlen($q)<2)json_response(['ok'=>true,'items'=>[]]);$like='%'.$q.'%';$items=[];
$defs=[
 ['customers','customers','name',['name','email','phone'],'crm.read'],['contacts','contacts','name',['name','email','phone'],'crm.read'],['leads','leads','title',['title','customer_name','email','phone'],'sales.read'],['opportunities','opportunities','title',['title'],'sales.read'],['projects','projects','name',['name','code'],'projects.read'],['suppliers','suppliers','name',['name','email','phone'],'trade.read'],['rfqs','rfqs','title',['title','rfq_no'],'trade.read']
];foreach($defs as [$entity,$table,$title,$fields,$cap]){if(!can_v10($cap,$u))continue;$or=[];$args=[$cid,$entity];foreach($fields as $f){$or[]='t.'.v10_identifier($f).' LIKE ?';$args[]=$like;}$sql='SELECT t.id,t.'.v10_identifier($title).' title FROM `'.$table.'` t WHERE t.company_id=? AND '.v10_is_deleted_sql('t').' AND ('.implode(' OR ',$or).') LIMIT 8';$st=$pdo->prepare($sql);$st->execute($args);foreach($st as $r)$items[]=['entity'=>$entity,'id'=>(int)$r['id'],'title'=>$r['title']];}
json_response(['ok'=>true,'items'=>array_slice($items,0,40)]);
