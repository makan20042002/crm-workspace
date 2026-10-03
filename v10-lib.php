<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

function v10_caps(): array {
    return [
        'super_admin'=>['*'],
        'company_admin'=>['*'],
        'manager'=>['crm.read','crm.write','sales.read','sales.write','trade.read','trade.write','projects.read','projects.write','service.read','service.write','reports.read','reports.write','communications.read','communications.write','automation.read','automation.write','admin.users.read','admin.settings.read','data.import','data.export','trash.manage'],
        'sales'=>['crm.read','crm.write','sales.read','sales.write','trade.read','reports.read','communications.read','communications.write','projects.read','service.read','data.export'],
        'project_manager'=>['crm.read','trade.read','trade.write','projects.read','projects.write','reports.read','communications.read','service.read','service.write','data.export'],
        'technical'=>['crm.read','trade.read','projects.read','projects.write','communications.read','service.read','service.write'],
        'finance'=>['crm.read','sales.read','sales.write','trade.read','trade.write','projects.read','reports.read','data.export'],
        'employee'=>['crm.read','projects.read','projects.write','communications.read','service.read'],
        'viewer'=>['crm.read','sales.read','trade.read','projects.read','service.read','reports.read'],
    ];
}

function can_v10(string $cap, ?array $u=null): bool {
    $u ??= current_user();
    if(!$u) return false;
    $caps=v10_caps()[$u['role']]??[];
    return in_array('*',$caps,true)||in_array($cap,$caps,true);
}
function require_cap_v10(string $cap): void { if(!can_v10($cap)) json_response(['ok'=>false,'error'=>'forbidden','cap'=>$cap],403); }

function v10_entity_meta(): array {
    return [
      'customers'=>['table'=>'customers','label'=>'customer','read'=>'crm.read','write'=>'crm.write','title'=>'name','owner'=>'owner_id','status'=>'status','fields'=>['name','type','phone','email','city','address','status','owner_id','notes']],
      'contacts'=>['table'=>'contacts','label'=>'contact','read'=>'crm.read','write'=>'crm.write','title'=>'name','owner'=>null,'status'=>null,'fields'=>['customer_id','name','job_title','phone','email','notes']],
      'leads'=>['table'=>'leads','label'=>'lead','read'=>'sales.read','write'=>'sales.write','title'=>'title','owner'=>'owner_id','status'=>'stage','fields'=>['title','customer_name','phone','email','source','vertical','stage','value','currency','owner_id','next_followup_at','notes']],
      'opportunities'=>['table'=>'opportunities','label'=>'opportunity','read'=>'sales.read','write'=>'sales.write','title'=>'title','owner'=>'owner_id','status'=>'stage','fields'=>['customer_id','title','stage','probability','amount','currency','owner_id','expected_close_date','notes']],
      'projects'=>['table'=>'projects','label'=>'project','read'=>'projects.read','write'=>'projects.write','title'=>'name','owner'=>'manager_id','status'=>'status','fields'=>['customer_id','name','code','vertical','category','manager_id','status','progress','start_date','due_date','budget','currency','description']],
      'suppliers'=>['table'=>'suppliers','label'=>'supplier','read'=>'trade.read','write'=>'trade.write','title'=>'name','owner'=>null,'status'=>'status','fields'=>['name','category','contact_name','phone','email','status','notes']],
      'rfqs'=>['table'=>'rfqs','label'=>'rfq','read'=>'trade.read','write'=>'trade.write','title'=>'title','owner'=>null,'status'=>'status','fields'=>['customer_id','opportunity_id','project_id','rfq_no','title','vertical','status','currency','due_date','incoterm','destination','notes']],
      'tasks'=>['table'=>'tasks','label'=>'task','read'=>'projects.read','write'=>'projects.write','title'=>'title','owner'=>'assignee_id','status'=>'status','fields'=>['project_id','title','description','assignee_id','status','priority','due_date','estimated_hours']],
      'followups'=>['table'=>'followups','label'=>'followup','read'=>'crm.read','write'=>'crm.write','title'=>'subject','owner'=>'assigned_to','status'=>'status','fields'=>['customer_id','lead_id','opportunity_id','type','subject','due_at','assigned_to','status','notes']],
      'quotations'=>['table'=>'quotations','label'=>'quotation','read'=>'sales.read','write'=>'sales.write','title'=>'title','owner'=>'created_by','status'=>'status','fields'=>['customer_id','opportunity_id','quote_no','title','currency','valid_until','status','terms','notes']],
      'payments'=>['table'=>'payment_milestones','label'=>'payment','read'=>'trade.read','write'=>'trade.write','title'=>'title','owner'=>null,'status'=>'status','fields'=>['customer_id','project_id','opportunity_id','title','method','currency','amount','exchange_rate','due_date','status','reference_no','notes']],
      'lcs'=>['table'=>'lc_records','label'=>'lc','read'=>'trade.read','write'=>'trade.write','title'=>'lc_no','owner'=>null,'status'=>'status','fields'=>['customer_id','project_id','lc_no','issuing_bank','advising_bank','currency','amount','issue_date','expiry_date','status','latest_shipment_date','terms','notes']],
      'shipments'=>['table'=>'shipments','label'=>'shipment','read'=>'trade.read','write'=>'trade.write','title'=>'shipment_no','owner'=>null,'status'=>'status','fields'=>['customer_id','project_id','rfq_id','shipment_no','status','mode','incoterm','container_no','bl_no','vessel','forwarder','port_loading','port_destination','etd','eta','actual_delivery_date','tracking_url','notes']],
    ];
}


function v10_entity_singular(string $entity): string {
    return [
      'customers'=>'customer','contacts'=>'contact','leads'=>'lead','opportunities'=>'opportunity',
      'projects'=>'project','suppliers'=>'supplier','rfqs'=>'rfq','tasks'=>'task','followups'=>'followup',
      'quotations'=>'quotation','payments'=>'payment','lcs'=>'lc','shipments'=>'shipment'
    ][$entity] ?? rtrim($entity,'s');
}

function v10_meta(string $entity): array {
    $all=v10_entity_meta(); if(!isset($all[$entity])) json_response(['ok'=>false,'error'=>'unknown_entity'],404); return $all[$entity];
}
function v10_is_deleted_sql(string $alias='t'): string {
    return "NOT EXISTS (SELECT 1 FROM v10_record_deletions vd WHERE vd.company_id={$alias}.company_id AND vd.entity_type=? AND vd.entity_id={$alias}.id)";
}
function v10_record_exists(string $entity,int $id,int $cid): bool {
    $m=v10_meta($entity); $st=db()->prepare("SELECT 1 FROM `{$m['table']}` t WHERE t.id=? AND t.company_id=? AND ".v10_is_deleted_sql('t')." LIMIT 1");$st->execute([$id,$cid,$entity]);return(bool)$st->fetchColumn();
}
function v10_require_record(string $entity,int $id,int $cid): array {
    $m=v10_meta($entity); require_cap_v10($m['read']); $st=db()->prepare("SELECT t.* FROM `{$m['table']}` t WHERE t.id=? AND t.company_id=? AND ".v10_is_deleted_sql('t')." LIMIT 1");$st->execute([$id,$cid,$entity]);$row=$st->fetch();if(!$row)json_response(['ok'=>false,'error'=>'not_found'],404);return$row;
}
function v10_soft_delete(string $entity,int $id,array $u): void {
    $m=v10_meta($entity);require_cap_v10($m['write']); if(!v10_record_exists($entity,$id,(int)$u['company_id']))json_response(['ok'=>false,'error'=>'not_found'],404);
    db()->prepare('INSERT INTO v10_record_deletions(company_id,entity_type,entity_id,deleted_by) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE deleted_by=VALUES(deleted_by),deleted_at=NOW()')->execute([$u['company_id'],$entity,$id,$u['id']]);
    audit('soft_delete',$entity,$id);v10_timeline($entity,$id,'audit','Deleted','',null,null,(int)$u['id']);
}
function v10_restore(string $entity,int $id,array $u): void {require_cap_v10('trash.manage');db()->prepare('DELETE FROM v10_record_deletions WHERE company_id=? AND entity_type=? AND entity_id=?')->execute([$u['company_id'],$entity,$id]);audit('restore',$entity,$id);}
function v10_timeline(string $entity,int $id,string $type,string $title,string $body='',?string $relType=null,?int $relId=null,?int $uid=null,array $meta=[]): void {
    $u=current_user();$cid=(int)($u['company_id']??0);if(!$cid)return;$uid??=(int)($u['id']??0)?:null;
    db()->prepare('INSERT INTO v10_timeline_events(company_id,entity_type,entity_id,event_type,title,body,related_type,related_id,user_id,metadata_json) VALUES(?,?,?,?,?,?,?,?,?,?)')->execute([$cid,$entity,$id,$type,$title,$body?:null,$relType,$relId,$uid,json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)]);
}
function v10_json_decode(?string $s,array $fallback=[]): array {if(!$s)return$fallback;$v=json_decode($s,true);return is_array($v)?$v:$fallback;}
function v10_clean_sort(string $sort,array $allowed,string $fallback): string {return in_array($sort,$allowed,true)?$sort:$fallback;}
function v10_clean_dir(string $dir): string {return strtolower($dir)==='asc'?'ASC':'DESC';}
function v10_identifier(string $s): string {if(!preg_match('/^[A-Za-z0-9_]+$/',$s))throw new InvalidArgumentException('invalid identifier');return'`'.$s.'`';}
function v10_generate_token(int $bytes=18): string {return rtrim(strtr(base64_encode(random_bytes($bytes)),'+/','-_'),'=');}
function v10_table_exists(string $table): bool {$st=db()->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');$st->execute([$table]);return(int)$st->fetchColumn()>0;}
function v10_storage_dir(string $name): string {
    if(!in_array($name,['private_uploads','backups','imports','logs','rate-limits'],true))throw new InvalidArgumentException('Invalid storage directory');
    $base=APP_ROOT.'/storage';$dir=$name==='backups'?(string)envv('BACKUP_DIR',$base.'/backups'):$base.'/'.$name;
    if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Storage folder could not be created: '.$name);
    $deny=$base.'/.htaccess';if(!is_file($deny)&&file_put_contents($deny,"Require all denied\nDeny from all\n",LOCK_EX)===false)throw new RuntimeException('Storage access-protection file could not be created');
    if(!is_writable($dir))throw new RuntimeException('Storage folder is not writable: '.$name);
    return $dir;
}
function v10_company_setting(int $cid,string $key,?string $default=null): ?string {$st=db()->prepare('SELECT setting_value FROM app_settings WHERE company_id=? AND setting_key=? LIMIT 1');$st->execute([$cid,$key]);$v=$st->fetchColumn();return$v===false?$default:(string)$v;}
function v10_set_company_setting(int $cid,string $key,string $value): void {db()->prepare('INSERT INTO app_settings(company_id,setting_key,setting_value) VALUES(?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$cid,$key,$value]);}
