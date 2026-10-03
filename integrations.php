<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

function integration_key(): string { $raw=(string)envv('APP_KEY',''); if(strlen($raw)<32) throw new RuntimeException('APP_KEY is missing or invalid. Generate it in .env before saving integration credentials.'); return hash('sha256',$raw,true); }
function integration_encode(array $settings): string { $plain=json_encode($settings,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); if(!function_exists('openssl_encrypt')) return $plain; $iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($plain,'aes-256-gcm',integration_key(),OPENSSL_RAW_DATA,$iv,$tag); return 'enc:'.base64_encode($iv.$tag.$cipher); }
function integration_decode(string $stored): array { if(!str_starts_with($stored,'enc:')) return json_decode($stored,true)?:[]; $raw=base64_decode(substr($stored,4),true);if($raw===false||strlen($raw)<29)return []; $iv=substr($raw,0,12);$tag=substr($raw,12,16);$cipher=substr($raw,28);$plain=openssl_decrypt($cipher,'aes-256-gcm',integration_key(),OPENSSL_RAW_DATA,$iv,$tag);return $plain===false?[]:(json_decode($plain,true)?:[]); }
function integration_settings(int $companyId,string $type): array {
    $st=db()->prepare('SELECT provider_name,settings_json,enabled FROM integration_settings WHERE company_id=? AND provider_type=? ORDER BY enabled DESC,id DESC LIMIT 1');
    $st->execute([$companyId,$type]);$r=$st->fetch();
    if(!$r)return ['provider'=>'','enabled'=>false,'settings'=>[]];
    return ['provider'=>$r['provider_name'],'enabled'=>(bool)$r['enabled'],'settings'=>integration_decode((string)$r['settings_json'])];
}
function smtp_send(array $cfg,string $to,string $subject,string $body): array {
    $host=(string)($cfg['host']??'');$port=(int)($cfg['port']??587);$user=(string)($cfg['username']??'');$pass=(string)($cfg['password']??'');
    $from=(string)($cfg['from_email']??$user);$fromName=(string)($cfg['from_name']??APP_PRODUCT_NAME);$secure=(string)($cfg['secure']??'tls');
    if(!$host||!filter_var($to,FILTER_VALIDATE_EMAIL)||!filter_var($from,FILTER_VALIDATE_EMAIL))return [false,'invalid SMTP settings'];
    $target=($secure==='ssl'?'ssl://':'').$host.':'.$port;$fp=@stream_socket_client($target,$errno,$errstr,15,STREAM_CLIENT_CONNECT);
    if(!$fp)return [false,"connect: $errstr"];
    stream_set_timeout($fp,15);
    $read=function()use($fp){$s='';while(($l=fgets($fp,515))!==false){$s.=$l;if(strlen($l)<4||$l[3]!=='-')break;}return $s;};
    $cmd=function(string $c,array $ok=[2,3])use($fp,$read){fwrite($fp,$c."\r\n");$r=$read();$code=(int)substr($r,0,3);if(!in_array((int)floor($code/100),$ok,true))throw new RuntimeException(trim($r));return $r;};
    try{$read();$cmd('EHLO crm-workspace');if($secure==='tls'){$cmd('STARTTLS');if(!stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT))throw new RuntimeException('TLS failed');$cmd('EHLO crm-workspace');}
      if($user!==''){$cmd('AUTH LOGIN');$cmd(base64_encode($user));$cmd(base64_encode($pass));}
      $cmd('MAIL FROM:<'.$from.'>');$cmd('RCPT TO:<'.$to.'>');$cmd('DATA',[3]);
      $headers=['From: '.$fromName.' <'.$from.'>','To: <'.$to.'>','Subject: =?UTF-8?B?'.base64_encode($subject).'?=','MIME-Version: 1.0','Content-Type: text/html; charset=UTF-8'];
      fwrite($fp,implode("\r\n",$headers)."\r\n\r\n".$body."\r\n.\r\n");$resp=$read();if((int)substr($resp,0,1)!==2)throw new RuntimeException(trim($resp));$cmd('QUIT');fclose($fp);return [true,'sent'];
    }catch(Throwable $e){fclose($fp);return [false,$e->getMessage()];}
}
function sms_send(array $cfg,string $to,string $message): array {
    $url=(string)($cfg['url']??''); if(!$url||!$to)return [false,'SMS gateway not configured'];
    $payload=['to'=>$to,'message'=>$message,'sender'=>$cfg['sender']??null];
    $headers=['Content-Type: application/json'];if(!empty($cfg['token']))$headers[]='Authorization: Bearer '.$cfg['token'];
    if(!function_exists('curl_init'))return [false,'PHP cURL extension is required'];
    $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);
    $resp=(string)curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);return [v10_provider_response_ok($code,$resp,$err),$err?:($resp?:'HTTP '.$code)];
}
function log_communication(int $cid,?int $uid,string $channel,string $recipient,string $subject,string $body,string $status,string $provider='',string $error=''): void {
    db()->prepare('INSERT INTO communication_logs(company_id,user_id,channel,recipient,subject,body,status,provider,error_message) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$cid,$uid,$channel,$recipient,$subject,$body,$status,$provider,$error?:null]);
}

function imap_decode_part_v10($mb,int $uid,$part,string $number=''): string {
    if(!empty($part->parts)){
        $plain='';$html='';foreach($part->parts as $i=>$child){$value=imap_decode_part_v10($mb,$uid,$child,$number===''?(string)($i+1):$number.'.'.($i+1));if((int)($child->type??0)===0&&strtoupper((string)($child->subtype??''))==='PLAIN')$plain.=$value;elseif((int)($child->type??0)===0&&strtoupper((string)($child->subtype??''))==='HTML')$html.=$value;elseif($plain==='')$plain.=$value;}return trim($plain!==''?$plain:strip_tags($html));
    }
    if((int)($part->type??0)!==0)return '';$raw=$number===''?imap_body($mb,(string)$uid,FT_UID|FT_PEEK):imap_fetchbody($mb,(string)$uid,$number,FT_UID|FT_PEEK);$encoding=(int)($part->encoding??0);if($encoding===3)$raw=(string)base64_decode($raw,true);elseif($encoding===4)$raw=quoted_printable_decode($raw);$charset='UTF-8';foreach(array_merge((array)($part->parameters??[]),(array)($part->dparameters??[])) as $p)if(strtolower((string)($p->attribute??''))==='charset')$charset=(string)$p->value;if(strtoupper($charset)!=='UTF-8'&&function_exists('mb_convert_encoding'))$raw=mb_convert_encoding($raw,'UTF-8',$charset);return $raw;
}
function imap_sync_v10(int $cid,int $limit=200): array {
    if(!function_exists('imap_open'))return[false,'imap_extension_missing',0];$pdo=db();$cfg=integration_settings($cid,'email');$s=$cfg['settings'];$host=(string)($s['imap_host']??'');$user=(string)($s['imap_username']??$s['username']??'');$pass=(string)($s['imap_password']??$s['password']??'');$port=(int)($s['imap_port']??993);$flags=(string)($s['imap_flags']??'/imap/ssl');if(!$cfg['enabled']||!$host||!$user)return[false,'imap_not_configured',0];$mb=@imap_open('{'.$host.':'.$port.$flags.'}INBOX',$user,$pass);if(!$mb)return[false,imap_last_error()?:'imap_connect_failed',0];$uids=imap_search($mb,'SINCE "'.date('d-M-Y',strtotime('-14 days')).'"',SE_UID)?:[];$added=0;foreach(array_slice($uids,-$limit) as $uid){$ov=imap_fetch_overview($mb,(string)$uid,FT_UID)[0]??null;if(!$ov)continue;$msgId=(string)($ov->message_id??('uid-'.$uid));$from=isset($ov->from)?imap_utf8($ov->from):'';$to=isset($ov->to)?imap_utf8($ov->to):'';$subject=isset($ov->subject)?imap_utf8($ov->subject):'';$date=!empty($ov->date)?date('Y-m-d H:i:s',strtotime($ov->date)):null;$body=imap_decode_part_v10($mb,(int)$uid,imap_fetchstructure($mb,(string)$uid,FT_UID));$email='';if(preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',$from,$mm))$email=$mm[0];$customerId=null;$contactId=null;if($email){$q=$pdo->prepare('SELECT id,customer_id FROM contacts WHERE company_id=? AND email=? LIMIT 1');$q->execute([$cid,$email]);if($r=$q->fetch()){$contactId=(int)$r['id'];$customerId=(int)$r['customer_id'];}else{$q=$pdo->prepare('SELECT id FROM customers WHERE company_id=? AND email=? LIMIT 1');$q->execute([$cid,$email]);$customerId=(int)($q->fetchColumn()?:0)?:null;}}$st=$pdo->prepare('INSERT IGNORE INTO v10_imap_messages(company_id,message_uid,customer_id,contact_id,sender,recipient,subject,body_text,message_date) VALUES(?,?,?,?,?,?,?,?,?)');$st->execute([$cid,$msgId,$customerId,$contactId,$from,$to,$subject,$body,$date]);$added+=$st->rowCount();}imap_close($mb);return[true,'ok',$added];
}
