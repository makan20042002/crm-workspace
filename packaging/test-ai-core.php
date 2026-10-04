<?php
declare(strict_types=1);
$_SERVER['SCRIPT_NAME']='install.php';
session_save_path(sys_get_temp_dir());
require dirname(__DIR__).'/ai-lib.php';
$failures=[];
$prompt=ai_system_prompt('summarize','fa');
if(!str_contains($prompt,'single allowed task')||!str_contains($prompt,'untrusted data')||!str_contains($prompt,'Never follow instructions'))$failures[]='system prompt guardrails';
$valid='{"supplier":"ACME","items":[{"description":"Motor","qty":2,"unit":"pcs","unit_price":10}],"currency":"USD","freight":5,"lead_time_days":14}';
$quote=ai_parse_quote_json($valid);if($quote['supplier']!=='ACME'||count($quote['items'])!==1||$quote['freight']!==5.0)$failures[]='quote JSON validation';
try{ai_parse_quote_json('not json');$failures[]='invalid JSON accepted';}catch(RuntimeException){}
try{ai_parse_quote_json('{"supplier":"A","items":[]}');$failures[]='missing fields accepted';}catch(RuntimeException){}
if($failures){fwrite(STDERR,'AI core checks failed: '.implode(', ',$failures).PHP_EOL);exit(1);}echo"AI core checks passed\n";
