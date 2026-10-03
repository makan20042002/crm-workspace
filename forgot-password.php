<?php
declare(strict_types=1);
require __DIR__.'/automation-engine.php';
$msg='';
$companyCount=(int)db()->query('SELECT COUNT(*) FROM companies')->fetchColumn();
if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){
    verify_csrf();
    $slug=trim((string)($_POST['company_slug']??''));
    $email=trim((string)($_POST['email']??''));
    try{
        if($companyCount===1){
            $st=db()->prepare('SELECT u.id,u.company_id,u.name,u.email FROM users u WHERE u.email=? AND u.active=1 LIMIT 1');
            $st->execute([$email]);
        }else{
            $st=db()->prepare('SELECT u.id,u.company_id,u.name,u.email FROM users u JOIN companies c ON c.id=u.company_id WHERE c.slug=? AND u.email=? AND u.active=1 LIMIT 1');
            $st->execute([$slug,$email]);
        }
        $target=$st->fetch();
        if($target){
            $token=v10_generate_token(32);
            db()->prepare('INSERT INTO v10_password_resets(company_id,user_id,token_hash,expires_at) VALUES(?,?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))')->execute([$target['company_id'],$target['id'],hash('sha256',$token)]);
            $cfg=integration_settings((int)$target['company_id'],'email');
            if($cfg['enabled']){
                $url=rtrim((string)envv('APP_URL',''),'/').'/reset-password.php?token='.rawurlencode($token);
                [$ok,$detail]=smtp_send($cfg['settings'],$target['email'],'Password reset','<p><a href="'.e($url).'">Reset password</a></p>');
                if(!$ok)error_log('reset mail: '.$detail);
            }
        }
    }catch(Throwable $e){error_log($e->__toString());}
    $msg='اگر حساب معتبر باشد، لینک بازیابی ارسال می‌شود. / If the account is valid, a reset link will be sent.';
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>بازیابی رمز</title><link rel="stylesheet" href="assets/app.css?v=10"></head><body class="auth-page"><main class="auth-card"><h1>بازیابی رمز عبور</h1><?php if($msg):?><div class="state success"><?=e($msg)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><?php if($companyCount>1):?><label>شناسه شرکت / Company slug</label><input name="company_slug" required><?php endif;?><label>ایمیل / Email</label><input type="email" name="email" required><button class="btn primary">ارسال لینک / Send link</button></form><a href="login.php">بازگشت / Back</a></main></body></html>
