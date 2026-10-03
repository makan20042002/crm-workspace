<?php
declare(strict_types=1);require __DIR__.'/v10-lib.php';require_once __DIR__.'/migration-runner.php';if(PHP_SAPI!=='cli'){http_response_code(403);exit;}$a=migration_run(db(),false);echo $a?'Applied: '.implode(', ',$a).PHP_EOL:"No pending migrations.\n";
