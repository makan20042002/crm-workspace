<?php
require __DIR__.'/bootstrap.php';
if(current_user()){header('Location:index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $posted=(string)($_POST['csrf']??'');
    if($posted==='' || !hash_equals(csrf_token(),$posted)){
        $error='درخواست نامعتبر است. صفحه را تازه‌سازی کنید.';
    } else {
        $email=strtolower(trim((string)($_POST['email']??'')));
        $pass=(string)($_POST['password']??'');
        [$allowed,$rateKey,$wait]=auth_rate_check($email,client_ip());
        if(!$allowed){
            $error='تعداد تلاش‌های ورود بیش از حد مجاز است. حدود '.max(1,(int)ceil($wait/60)).' دقیقه دیگر تلاش کنید.';
        } else {
            $st=db()->prepare('SELECT * FROM users WHERE email=? AND active=1 ORDER BY id LIMIT 1');
            $st->execute([$email]); $usr=$st->fetch();
            if($usr && password_verify($pass,$usr['password_hash'])){
                auth_rate_success($rateKey);
                if(password_needs_rehash($usr['password_hash'],password_algo())){
                    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([hash_password($pass),$usr['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['user_id']=(int)$usr['id'];
                $_SESSION['csrf']=bin2hex(random_bytes(32));
                db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([$usr['id']]);
                db()->prepare('INSERT INTO security_events(company_id,user_id,event_type,severity,email,ip_address,user_agent,metadata) VALUES(?,?,?,?,?,?,?,?)')->execute([$usr['company_id'],$usr['id'],'login_success','info',$email,client_ip(),substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255),json_encode(['session_regenerated'=>true])]);
                header('Location:index.php');exit;
            }
            auth_rate_fail($rateKey,$email,client_ip());
            db()->prepare('INSERT INTO security_events(event_type,severity,email,ip_address,user_agent,metadata) VALUES(?,?,?,?,?,?)')->execute(['login_failed','warning',$email,client_ip(),substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,255),json_encode(['reason'=>'invalid_credentials'])]);
            usleep(250000);
            $error='ایمیل یا رمز عبور صحیح نیست.';
        }
    }
}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(APP_PRODUCT_NAME)?> — Secure Login</title><link rel="stylesheet" href="assets/app.css?v=<?=APP_VERSION?>"></head><body class="login-body"><div class="login-shell"><div class="brand-panel"><div class="brand-mark">CRM</div><p class="eyebrow">TRADE · SOURCE · DELIVER</p><h1><?=e(APP_PRODUCT_NAME)?></h1><p>محیط امن مدیریت فروش، پروژه، عملیات، تدارکات و تجارت بین‌الملل.</p><div class="brand-lines"><span>CRM</span><span>RFQ</span><span>PROJECTS</span><span>LOGISTICS</span></div></div><form class="login-card" method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div><small>ورود امن</small><h2>خوش آمدید</h2></div><?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?><label>ایمیل<input name="email" type="email" required autocomplete="username"></label><label>رمز عبور<input name="password" type="password" required autocomplete="current-password"></label><button class="btn primary" type="submit">ورود به سیستم</button><a href="forgot-password.php">رمز عبور را فراموش کرده‌اید؟ / Forgot password</a><p class="muted"><?=app_credit_html()?></p></form></div></body></html>
