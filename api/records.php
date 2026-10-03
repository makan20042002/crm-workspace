<?php
declare(strict_types=1);
require dirname(__DIR__).'/automation-engine.php';
$u=require_login();$cid=(int)$u['company_id'];$pdo=db();$method=$_SERVER['REQUEST_METHOD']??'GET';if($method!=='GET')verify_csrf();$action=(string)($_GET['action']??'list');

function records_scope_sql(string $entity,array $m,array $u,array &$args): string {
    if(can_v10('crm.write',$u)||can_v10('sales.write',$u)||can_v10('trade.write',$u)||can_v10('projects.write',$u)) return '';
    $uid=(int)$u['id'];
    if($entity==='tasks'){ $args[]=$uid; return ' AND t.assignee_id=?'; }
    if($entity==='followups'){ $args[]=$uid; return ' AND t.assigned_to=?'; }
    if($m['owner']){ $args[]=$uid; return ' AND (t.`'.$m['owner'].'`=? OR t.`'.$m['owner'].'` IS NULL)'; }
    return '';
}
function relation_labels(string $entity,array $rows,int $cid): array {
    if(!$rows)return $rows;$pdo=db();
    $maps=['customer_id'=>['customers','name'],'project_id'=>['projects','name'],'opportunity_id'=>['opportunities','title'],'rfq_id'=>['rfqs','title'],'lead_id'=>['leads','title']];
    foreach($maps as $field=>[$table,$label]){
        $ids=[];foreach($rows as $r)if(!empty($r[$field]))$ids[]=(int)$r[$field];$ids=array_values(array_unique($ids));if(!$ids)continue;
        $in=implode(',',array_fill(0,count($ids),'?'));$args=array_merge([$cid],$ids);$st=$pdo->prepare("SELECT id,`$label` label FROM `$table` WHERE company_id=? AND id IN($in)");$st->execute($args);$lookup=[];foreach($st as $x)$lookup[(int)$x['id']]=$x['label'];foreach($rows as &$r)if(!empty($r[$field]))$r[$field.'_name']=$lookup[(int)$r[$field]]??null;
    }
    return $rows;
}

try{
 switch($action){
  case 'user_options':
    $st=$pdo->prepare('SELECT id,name FROM users WHERE company_id=? AND active=1 ORDER BY name');$st->execute([$cid]);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
  case 'options':
    $entity=(string)($_GET['entity']??'');$q=trim((string)($_GET['q']??''));
    if($entity==='users'){$m=['table'=>'users','title'=>'name','read'=>null,'soft'=>false];}elseif($entity==='supplier_quotes'){$m=['table'=>'supplier_quotes','title'=>'quote_ref','read'=>'trade.read','soft'=>false];}else{$m=v10_meta($entity);$m['soft']=true;}
    if($m['read'])require_cap_v10($m['read']);$table=(string)$m['table'];v10_identifier($table);$title=(string)$m['title'];v10_identifier($title);$where='company_id=?';$args=[$cid];
    if($entity==='users'){$where.=' AND active=1';}elseif($m['soft']){$where.=' AND NOT EXISTS(SELECT 1 FROM v10_record_deletions d WHERE d.company_id=`'.$table.'`.company_id AND d.entity_type=? AND d.entity_id=`'.$table.'`.id)';$args[]=$entity;}
    if($q!==''){$where.=' AND (CAST(id AS CHAR)=? OR `'.$title.'` LIKE ?)';$args[]=$q;$args[]='%'.$q.'%';}
    $st=$pdo->prepare('SELECT id,COALESCE(NULLIF(`'.$title.'`,\'\'),CONCAT(\'#\',id)) name FROM `'.$table.'` WHERE '.$where.' ORDER BY `'.$title.'` LIMIT 25');$st->execute($args);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
  case 'list':
    $entity=(string)($_GET['entity']??'');$m=v10_meta($entity);require_cap_v10($m['read']);
    $page=max(1,(int)($_GET['page']??1));$size=min(100,max(10,(int)($_GET['page_size']??25)));$offset=($page-1)*$size;
    $allowed=array_values(array_unique(array_merge(['id','created_at','updated_at'],$m['fields'])));$sort=v10_clean_sort((string)($_GET['sort']??($m['title']??'id')),$allowed,$m['title']??'id');$dir=v10_clean_dir((string)($_GET['dir']??'desc'));
    $where=['t.company_id=?',v10_is_deleted_sql('t')];$args=[$cid,$entity];
    $q=trim((string)($_GET['q']??''));if($q!==''){$searchFields=array_values(array_intersect([$m['title'],'name','title','email','phone','code','rfq_no','shipment_no','lc_no'],$allowed));if($searchFields){$or=[];foreach($searchFields as $f){$or[]='t.'.v10_identifier($f).' LIKE ?';$args[]='%'.$q.'%';}$where[]='('.implode(' OR ',$or).')';}}
    $dateFilterFields=['created_at','updated_at','due_date','due_at','expected_close_date','start_date','valid_until','expiry_date','issue_date','latest_shipment_date','etd','eta','actual_delivery_date','next_followup_at'];$exactFields=['id','status','stage','priority','owner_id','manager_id','assignee_id','assigned_to','customer_id','project_id','opportunity_id','rfq_id'];foreach($allowed as $f){$key='f_'.$f;if(in_array($f,$dateFilterFields,true)){if(!empty($_GET[$key.'_from'])){$where[]='t.'.v10_identifier($f).'>=?';$args[]=(string)$_GET[$key.'_from'];}if(!empty($_GET[$key.'_to'])){$where[]='t.'.v10_identifier($f).'<=?';$args[]=(string)$_GET[$key.'_to'].' 23:59:59';}continue;}if(!isset($_GET[$key])||$_GET[$key]==='')continue;if(in_array($f,$exactFields,true)){$where[]='t.'.v10_identifier($f).'=?';$args[]=(string)$_GET[$key];}else{$where[]='t.'.v10_identifier($f).' LIKE ?';$args[]='%'.(string)$_GET[$key].'%';}}
    $scopeArgs=[];$scope=records_scope_sql($entity,$m,$u,$scopeArgs);$args=array_merge($args,$scopeArgs);
    $base=" FROM `{$m['table']}` t WHERE ".implode(' AND ',$where).$scope;
    $st=$pdo->prepare('SELECT COUNT(*)'.$base);$st->execute($args);$total=(int)$st->fetchColumn();
    $sql='SELECT t.*'.$base.' ORDER BY t.'.v10_identifier($sort).' '.$dir.' LIMIT '.$size.' OFFSET '.$offset;$st=$pdo->prepare($sql);$st->execute($args);$items=$st->fetchAll();
    $users=[];$us=$pdo->prepare('SELECT id,name FROM users WHERE company_id=? AND active=1 ORDER BY name');$us->execute([$cid]);foreach($us as $r)$users[(int)$r['id']]=$r['name'];
    foreach($items as &$r){foreach(['owner_id','manager_id','assignee_id','assigned_to','created_by'] as $f)if(isset($r[$f])&&$r[$f])$r[$f.'_name']=$users[(int)$r[$f]]??null;}
    $items=relation_labels($entity,$items,$cid);
    json_response(['ok'=>true,'items'=>$items,'total'=>$total,'page'=>$page,'page_size'=>$size,'pages'=>(int)ceil($total/$size),'meta'=>$m]);

  case 'record':
    $entity=(string)($_GET['entity']??'');$id=(int)($_GET['id']??0);$m=v10_meta($entity);$row=v10_require_record($entity,$id,$cid);
    $custom=[];$st=$pdo->prepare('SELECT cf.id,cf.field_key,cf.label_fa,cf.label_en,cf.field_type,cf.required,cf.options_json,cv.value_text FROM custom_fields cf LEFT JOIN custom_field_values cv ON cv.custom_field_id=cf.id AND cv.entity_type=? AND cv.entity_id=? WHERE cf.company_id=? AND cf.entity_type IN (?,?) AND cf.active=1 ORDER BY cf.position,cf.id');$sing=v10_entity_singular($entity);$st->execute([$sing,$id,$cid,$sing,$entity]);$custom=$st->fetchAll();
    json_response(['ok'=>true,'record'=>$row,'custom_fields'=>$custom,'meta'=>$m]);

  case 'custom_fields':
    $entity=(string)($_GET['entity']??'');$m=v10_meta($entity);require_cap_v10($m['read']);$sing=v10_entity_singular($entity);$st=$pdo->prepare('SELECT id,field_key,label_fa,label_en,field_type,required,options_json FROM custom_fields WHERE company_id=? AND entity_type IN (?,?) AND active=1 ORDER BY position,id');$st->execute([$cid,$sing,$entity]);json_response(['ok'=>true,'items'=>$st->fetchAll()]);

  case 'timeline':
    $entity=(string)($_GET['entity']??'');$id=(int)($_GET['id']??0);v10_require_record($entity,$id,$cid);$events=[];
    $st=$pdo->prepare('SELECT te.*,u.name user_name FROM v10_timeline_events te LEFT JOIN users u ON u.id=te.user_id WHERE te.company_id=? AND te.entity_type=? AND te.entity_id=? ORDER BY te.occurred_at DESC LIMIT 300');$st->execute([$cid,$entity,$id]);foreach($st as $r)$events[]=$r;
    $st=$pdo->prepare('SELECT a.id,a.action event_type,a.action title,NULL body,a.user_id,u.name user_name,a.created_at occurred_at FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id WHERE a.company_id=? AND a.entity_type IN (?,?) AND a.entity_id=? ORDER BY a.created_at DESC LIMIT 150');$st->execute([$cid,$entity,v10_entity_singular($entity),$id]);foreach($st as $r){$duplicate=false;foreach($events as $existing){if(($existing['user_id']??null)===$r['user_id']&&abs(strtotime((string)$existing['occurred_at'])-strtotime((string)$r['occurred_at']))<=2&&in_array((string)$r['event_type'],['create','update','inline_update','soft_delete'],true)){$duplicate=true;break;}}if(!$duplicate)$events[]=$r;}
    usort($events,fn($a,$b)=>strcmp((string)$b['occurred_at'],(string)$a['occurred_at']));json_response(['ok'=>true,'items'=>array_slice($events,0,300)]);

  case 'related':
    $entity=(string)($_GET['entity']??'');$id=(int)($_GET['id']??0);v10_require_record($entity,$id,$cid);$out=[];
    if($entity==='customers'){
      $queries=['contacts'=>'SELECT * FROM contacts WHERE company_id=? AND customer_id=? ORDER BY id DESC','opportunities'=>'SELECT * FROM opportunities WHERE company_id=? AND customer_id=? ORDER BY id DESC','projects'=>'SELECT * FROM projects WHERE company_id=? AND customer_id=? ORDER BY id DESC','quotations'=>'SELECT * FROM quotations WHERE company_id=? AND customer_id=? ORDER BY id DESC','followups'=>'SELECT * FROM followups WHERE company_id=? AND customer_id=? ORDER BY id DESC','rfqs'=>'SELECT * FROM rfqs WHERE company_id=? AND customer_id=? ORDER BY id DESC'];
      foreach($queries as $k=>$sql){$st=$pdo->prepare($sql);$st->execute([$cid,$id]);$out[$k]=$st->fetchAll();}
    }elseif($entity==='projects'){
      foreach(['tasks'=>'SELECT * FROM tasks WHERE company_id=? AND project_id=? ORDER BY id DESC','documents'=>'SELECT * FROM documents WHERE company_id=? AND project_id=? ORDER BY id DESC','rfqs'=>'SELECT * FROM rfqs WHERE company_id=? AND project_id=? ORDER BY id DESC','payments'=>'SELECT * FROM payment_milestones WHERE company_id=? AND project_id=? ORDER BY id DESC','shipments'=>'SELECT * FROM shipments WHERE company_id=? AND project_id=? ORDER BY id DESC'] as $k=>$sql){$st=$pdo->prepare($sql);$st->execute([$cid,$id]);$out[$k]=$st->fetchAll();}
    }elseif($entity==='rfqs'){
      foreach(['items'=>'SELECT * FROM rfq_items WHERE company_id=? AND rfq_id=? ORDER BY id','supplier_quotes'=>'SELECT sq.*,s.name supplier_name FROM supplier_quotes sq JOIN suppliers s ON s.id=sq.supplier_id WHERE sq.company_id=? AND sq.rfq_id=? ORDER BY sq.total','shipments'=>'SELECT * FROM shipments WHERE company_id=? AND rfq_id=? ORDER BY id DESC'] as $k=>$sql){$st=$pdo->prepare($sql);$st->execute([$cid,$id]);$out[$k]=$st->fetchAll();}
    }elseif($entity==='quotations'){
      $st=$pdo->prepare('SELECT qi.*,p.name_fa product_name_fa,p.name_en product_name_en FROM quotation_items qi LEFT JOIN v10_quote_item_products qp ON qp.company_id=qi.company_id AND qp.quotation_item_id=qi.id LEFT JOIN v10_products p ON p.id=qp.product_id AND p.company_id=qi.company_id WHERE qi.company_id=? AND qi.quotation_id=? ORDER BY qi.position,qi.id');$st->execute([$cid,$id]);$out['quotation_items']=$st->fetchAll();
    }elseif($entity==='opportunities'){
      foreach(['quotations'=>'SELECT * FROM quotations WHERE company_id=? AND opportunity_id=? ORDER BY id DESC','followups'=>'SELECT * FROM followups WHERE company_id=? AND opportunity_id=? ORDER BY id DESC','rfqs'=>'SELECT * FROM rfqs WHERE company_id=? AND opportunity_id=? ORDER BY id DESC'] as $k=>$sql){$st=$pdo->prepare($sql);$st->execute([$cid,$id]);$out[$k]=$st->fetchAll();}
    }elseif($entity==='contacts'){
      $st=$pdo->prepare('SELECT c.* FROM customers c JOIN contacts x ON x.customer_id=c.id WHERE x.company_id=? AND x.id=? LIMIT 1');$st->execute([$cid,$id]);$r=$st->fetch();$out['customer']=$r?[$r]:[];
    }elseif($entity==='suppliers'){
      $st=$pdo->prepare('SELECT sq.*,r.title rfq_title FROM supplier_quotes sq JOIN rfqs r ON r.id=sq.rfq_id WHERE sq.company_id=? AND sq.supplier_id=? ORDER BY sq.id DESC');$st->execute([$cid,$id]);$out['supplier_quotes']=$st->fetchAll();
    }elseif($entity==='leads'){
      $st=$pdo->prepare('SELECT * FROM followups WHERE company_id=? AND lead_id=? ORDER BY id DESC');$st->execute([$cid,$id]);$out['followups']=$st->fetchAll();
    }
    foreach($out as $relatedEntity=>&$relatedRows){if(!isset(v10_entity_meta()[$relatedEntity]))continue;$relatedRows=array_values(array_filter($relatedRows,fn($row)=>v10_record_exists($relatedEntity,(int)$row['id'],$cid)));}unset($relatedRows);json_response(['ok'=>true,'groups'=>$out]);

  case 'save':
    $b=body_json();$entity=(string)req($b,'entity','');$id=(int)req($b,'id',0);$m=v10_meta($entity);require_cap_v10($m['write']);$data=(array)req($b,'data',[]);$cols=[];$vals=[];
    foreach($m['fields'] as $f){if(!array_key_exists($f,$data))continue;$v=$data[$f];if(is_string($v))$v=trim($v);if($v==='')$v=null;$cols[$f]=$v;}
    if(!$cols)json_response(['ok'=>false,'error'=>'no_fields'],422);
    $required=['customers'=>['name'],'contacts'=>['customer_id','name'],'leads'=>['title'],'opportunities'=>['customer_id','title'],'projects'=>['name'],'suppliers'=>['name'],'rfqs'=>['title'],'tasks'=>['title'],'followups'=>['subject','due_at'],'quotations'=>['customer_id','title'],'payments'=>['title'],'lcs'=>['customer_id'],'shipments'=>['customer_id']][$entity]??[];$missing=[];foreach($required as $field)if((!$id||array_key_exists($field,$cols))&&($cols[$field]??null)===null)$missing[]=$field;if($missing)json_response(['ok'=>false,'error'=>'validation_failed','fields'=>$missing,'message'=>'Required fields: '.implode(', ',$missing)],422);
    $numberFields=['quotations'=>['quote_no','Q'],'rfqs'=>['rfq_no','RFQ'],'shipments'=>['shipment_no','SHP'],'lcs'=>['lc_no','LC']];if(isset($numberFields[$entity])){[$numberField,$prefix]=$numberFields[$entity];if((!$id&&!isset($cols[$numberField]))||(array_key_exists($numberField,$cols)&&empty($cols[$numberField])))$cols[$numberField]=$prefix.'-'.date('Ymd-His').'-'.strtoupper(bin2hex(random_bytes(2)));}
    if(in_array('owner_id',array_keys($cols),true)&&$cols['owner_id'])tenant_ref('users',$cols['owner_id'],$cid,true);if(isset($cols['manager_id'])&&$cols['manager_id'])tenant_ref('users',$cols['manager_id'],$cid,true);if(isset($cols['assignee_id'])&&$cols['assignee_id'])tenant_ref('users',$cols['assignee_id'],$cid,true);
    foreach(['customer_id'=>'customers','project_id'=>'projects','opportunity_id'=>'opportunities','rfq_id'=>'rfqs'] as $f=>$tbl)if(isset($cols[$f])&&$cols[$f])tenant_ref($tbl,$cols[$f],$cid,true);
    if($entity==='opportunities'&&array_key_exists('probability',$cols))$cols['probability']=max(0,min(100,(int)$cols['probability']));
    if($entity==='payments'&&$id){$st=$pdo->prepare('SELECT status,paid_at FROM payment_milestones WHERE id=? AND company_id=?');$st->execute([$id,$cid]);$prev=$st->fetch();if($prev){if(($cols['status']??$prev['status'])==='paid'&&$prev['status']!=='paid')$cols['paid_at']=date('Y-m-d H:i:s');elseif(($cols['status']??$prev['status'])!=='paid')$cols['paid_at']=null;else$cols['paid_at']=$prev['paid_at'];}}
    if($entity==='payments'&&!$id&&($cols['status']??'')==='paid')$cols['paid_at']=date('Y-m-d H:i:s');
    if($id){$before=v10_require_record($entity,$id,$cid);$sets=[];$args=[];foreach($cols as $f=>$v){$sets[]=v10_identifier($f).'=?';$args[]=$v;}$args[]=$id;$args[]=$cid;$pdo->prepare('UPDATE `'.$m['table'].'` SET '.implode(',',$sets).' WHERE id=? AND company_id=?')->execute($args);$verb='update';}
    else{if(in_array($entity,['tasks','quotations','rfqs'],true)&&!array_key_exists('created_by',$cols))$cols['created_by']=(int)$u['id'];$names=['company_id'];$qs=['?'];$args=[$cid];foreach($cols as $f=>$v){$names[]=v10_identifier($f);$qs[]='?';$args[]=$v;}if($entity==='quotations'&&!isset($cols['quote_no'])){}$pdo->prepare('INSERT INTO `'.$m['table'].'` ('.implode(',',$names).') VALUES('.implode(',',$qs).')')->execute($args);$id=(int)$pdo->lastInsertId();$verb='create';}
    if(isset($b['custom_values'])&&is_array($b['custom_values'])){foreach($b['custom_values'] as $fid=>$val){$st=$pdo->prepare('SELECT id FROM custom_fields WHERE id=? AND company_id=? AND entity_type IN (?,?)');$sing=v10_entity_singular($entity);$st->execute([(int)$fid,$cid,$sing,$entity]);if($st->fetchColumn())$pdo->prepare('INSERT INTO custom_field_values(company_id,custom_field_id,entity_type,entity_id,value_text) VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE value_text=VALUES(value_text)')->execute([$cid,(int)$fid,$sing,$id,(string)$val]);}}
    audit($verb,$entity,$id,['fields'=>array_keys($cols)]);v10_timeline($entity,$id,'audit',$verb==='create'?'Created':'Updated','',null,null,(int)$u['id'],['fields'=>array_keys($cols)]);$fresh=v10_require_record($entity,$id,$cid);v10_run_event_rules($cid,$entity,$verb==='create'?'record_created':'record_updated',$fresh,$id);if($verb==='update'&&$m['status']==='stage'&&array_key_exists('stage',$cols)&&($before['stage']??null)!==$fresh['stage'])v10_run_event_rules($cid,$entity,'stage_changed',$fresh,$id,'stage');json_response(['ok'=>true,'id'=>$id]);

  case 'delete':$b=body_json();$entity=(string)req($b,'entity','');$id=(int)req($b,'id',0);v10_soft_delete($entity,$id,$u);json_response(['ok'=>true]);
  case 'restore':$b=body_json();$entity=(string)req($b,'entity','');$id=(int)req($b,'id',0);v10_restore($entity,$id,$u);json_response(['ok'=>true]);
  case 'trash':require_cap_v10('trash.manage');$st=$pdo->prepare('SELECT d.*,u.name deleted_by_name FROM v10_record_deletions d LEFT JOIN users u ON u.id=d.deleted_by WHERE d.company_id=? ORDER BY d.deleted_at DESC');$st->execute([$cid]);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
  case 'bulk':
    $b=body_json();$entity=(string)req($b,'entity','');$ids=array_values(array_filter(array_map('intval',(array)req($b,'ids',[]))));$op=(string)req($b,'operation','');$m=v10_meta($entity);require_cap_v10($m['write']);if(!$ids)json_response(['ok'=>false,'error'=>'no_selection'],422);
    if($op==='delete'){foreach($ids as $id)v10_soft_delete($entity,$id,$u);}
    elseif($op==='assign'){if(!$m['owner'])json_response(['ok'=>false,'error'=>'owner_not_supported'],422);$owner=tenant_ref('users',req($b,'owner_id'),$cid,true);$in=implode(',',array_fill(0,count($ids),'?'));$args=array_merge([$owner,$cid],$ids);$pdo->prepare('UPDATE `'.$m['table'].'` SET '.v10_identifier($m['owner']).'=? WHERE company_id=? AND id IN('.$in.')')->execute($args);}
    else json_response(['ok'=>false,'error'=>'bad_operation'],422);json_response(['ok'=>true]);
  case 'inline':
    $b=body_json();$entity=(string)req($b,'entity','');$id=(int)req($b,'id',0);$field=(string)req($b,'field','');$m=v10_meta($entity);require_cap_v10($m['write']);if(!in_array($field,[$m['status'],$m['owner']],true)||!in_array($field,$m['fields'],true))json_response(['ok'=>false,'error'=>'field_not_inline'],422);$value=req($b,'value');if($field===$m['owner']&&$value)$value=tenant_ref('users',$value,$cid,true);v10_require_record($entity,$id,$cid);$pdo->prepare('UPDATE `'.$m['table'].'` SET '.v10_identifier($field).'=? WHERE id=? AND company_id=?')->execute([$value,$id,$cid]);audit('inline_update',$entity,$id,['field'=>$field]);v10_timeline($entity,$id,'stage','Updated '.$field,(string)$value,null,null,(int)$u['id']);$fresh=v10_require_record($entity,$id,$cid);v10_run_event_rules($cid,$entity,$field==='stage'?'stage_changed':'field_changed',$fresh,$id,$field);json_response(['ok'=>true]);
  case 'note':
    $b=body_json();$entity=(string)req($b,'entity','');$id=(int)req($b,'id',0);$m=v10_meta($entity);require_cap_v10($m['write']);v10_require_record($entity,$id,$cid);$text=trim((string)req($b,'body',''));if(!$text)json_response(['ok'=>false,'error'=>'body_required'],422);v10_timeline($entity,$id,'note','Note',$text,null,null,(int)$u['id']);json_response(['ok'=>true]);

  case 'documents':
    $entity=(string)($_GET['entity']??'');$id=(int)($_GET['id']??0);v10_require_record($entity,$id,$cid);$sql='SELECT DISTINCT d.* FROM documents d LEFT JOIN v10_document_links l ON l.company_id=d.company_id AND l.document_id=d.id WHERE d.company_id=? AND ((l.entity_type=? AND l.entity_id=?)';$args=[$cid,$entity,$id];if(in_array($entity,['projects','customers','tasks'],true)){$field=['projects'=>'project_id','customers'=>'customer_id','tasks'=>'task_id'][$entity];$sql.=' OR d.'.$field.'=?';$args[]=$id;}$sql.=') ORDER BY d.created_at DESC';$st=$pdo->prepare($sql);$st->execute($args);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
  case 'create_linked_task':
    require_cap_v10('projects.write');$b=body_json();$entity=(string)req($b,'entity','');$eid=(int)req($b,'entity_id',0);v10_require_record($entity,$eid,$cid);$title=trim((string)req($b,'title',''));if(!$title)json_response(['ok'=>false,'error'=>'title_required'],422);$assignee=(int)req($b,'assignee_id',$u['id']);tenant_ref('users',$assignee,$cid,true);$project=$entity==='projects'?$eid:((int)req($b,'project_id',0)?:null);if($project)tenant_ref('projects',$project,$cid,true);$pdo->prepare("INSERT INTO tasks(company_id,project_id,title,description,assignee_id,status,priority,due_date,created_by) VALUES(?,?,?,?,?,'todo',?,?,?)")->execute([$cid,$project,$title,req($b,'description'),$assignee,req($b,'priority','medium'),req($b,'due_date')?:null,$u['id']]);$task=(int)$pdo->lastInsertId();$pdo->prepare('INSERT INTO v10_task_links(company_id,task_id,entity_type,entity_id) VALUES(?,?,?,?)')->execute([$cid,$task,$entity,$eid]);v10_timeline($entity,$eid,'task','Task created',$title,'tasks',$task,(int)$u['id']);json_response(['ok'=>true,'id'=>$task]);
  case 'task_work':
    $task=(int)($_GET['id']??0);v10_require_record('tasks',$task,$cid);$st=$pdo->prepare('SELECT c.*,u.name user_name FROM task_comments c LEFT JOIN users u ON u.id=c.user_id WHERE c.company_id=? AND c.task_id=? ORDER BY c.created_at');$st->execute([$cid,$task]);$comments=$st->fetchAll();$st=$pdo->prepare('SELECT t.*,u.name user_name FROM time_entries t JOIN users u ON u.id=t.user_id WHERE t.company_id=? AND t.task_id=? ORDER BY t.work_date DESC,t.id DESC');$st->execute([$cid,$task]);json_response(['ok'=>true,'comments'=>$comments,'time_entries'=>$st->fetchAll()]);
  case 'task_comment':
    require_cap_v10('projects.write');$b=body_json();$task=(int)req($b,'task_id',0);v10_require_record('tasks',$task,$cid);$text=trim((string)req($b,'body',''));if(!$text)json_response(['ok'=>false,'error'=>'body_required'],422);$pdo->prepare('INSERT INTO task_comments(company_id,task_id,user_id,body) VALUES(?,?,?,?)')->execute([$cid,$task,$u['id'],$text]);json_response(['ok'=>true]);
  case 'task_time':
    require_cap_v10('projects.write');$b=body_json();$task=(int)req($b,'task_id',0);$row=v10_require_record('tasks',$task,$cid);$minutes=max(1,(int)req($b,'minutes',0));$pdo->prepare('INSERT INTO time_entries(company_id,user_id,task_id,project_id,minutes,note,work_date) VALUES(?,?,?,?,?,?,?)')->execute([$cid,$u['id'],$task,$row['project_id']?:null,$minutes,req($b,'note'),req($b,'work_date',date('Y-m-d'))]);json_response(['ok'=>true]);
  case 'views':
    $entity=(string)($_GET['entity']??'');v10_meta($entity);$st=$pdo->prepare('SELECT * FROM v10_list_views WHERE company_id=? AND user_id=? AND entity_type=? ORDER BY is_default DESC,name');$st->execute([$cid,$u['id'],$entity]);json_response(['ok'=>true,'items'=>$st->fetchAll()]);
  case 'save_view':
    $b=body_json();$entity=(string)req($b,'entity','');v10_meta($entity);$name=trim((string)req($b,'name',''));if(!$name)json_response(['ok'=>false,'error'=>'name_required'],422);$config=(array)req($b,'config',[]);$pdo->prepare('INSERT INTO v10_list_views(company_id,user_id,entity_type,name,config_json,is_default) VALUES(?,?,?,?,?,?)')->execute([$cid,$u['id'],$entity,$name,json_encode($config,JSON_UNESCAPED_UNICODE),!empty($b['is_default'])?1:0]);json_response(['ok'=>true,'id'=>(int)$pdo->lastInsertId()]);
  default:json_response(['ok'=>false,'error'=>'unknown_action'],404);
 }
}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'server_error','message'=>envv('APP_ENV','local')==='local'?$e->getMessage():'Internal server error'],500);}
