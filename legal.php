<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
$license=is_file(__DIR__.'/LICENSE')?(string)file_get_contents(__DIR__.'/LICENSE'):'';
$notice=is_file(__DIR__.'/NOTICE')?(string)file_get_contents(__DIR__.'/NOTICE'):'';
$source=trim((string)envv('SOURCE_URL',APP_SOURCE_URL));
?><!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(APP_PRODUCT_NAME)?> · مجوز</title><link rel="stylesheet" href="assets/app.css?v=<?=APP_VERSION?>"></head><body>
<main class="panel" style="max-width:960px;margin:32px auto">
<h1><?=e(APP_PRODUCT_NAME)?> <?=e(APP_VERSION)?></h1>
<p><b>مجوز / License:</b> <?=e(APP_LICENSE)?></p>
<p>این نرم‌افزار آزاد و رایگان است و بدون هرگونه ضمانت ارائه می‌شود.</p>
<p>This is free software provided without warranty.</p>
<?php if($source!==''):?><p><a class="btn primary" href="<?=e($source)?>" target="_blank" rel="noopener">دریافت کد منبع / Get source code</a></p><?php else:?><p>کد منبع همراه انتشار رسمی از <a href="<?=e(APP_CREDIT['site'])?>" target="_blank" rel="noopener"><?=e(APP_CREDIT['site'])?></a> ارائه می‌شود.</p><?php endif?>
<h2>NOTICE</h2><pre style="white-space:pre-wrap;direction:ltr;text-align:left"><?=e($notice)?></pre>
<details><summary>GNU Affero General Public License</summary><pre style="white-space:pre-wrap;direction:ltr;text-align:left"><?=e($license)?></pre></details>
<footer class="app-credit"><?=app_credit_html()?></footer>
</main></body></html>
