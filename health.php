<?php
declare(strict_types=1);
require __DIR__.'/bootstrap.php';
$ok=true;$dbOk=false;
try{db()->query('SELECT 1');$dbOk=true;}catch(Throwable $e){$ok=false;}
json_response(['ok'=>$ok,'app'=>APP_PRODUCT_NAME,'version'=>APP_VERSION,'database'=>$dbOk?'up':'down','time'=>gmdate('c')],$ok?200:503);
