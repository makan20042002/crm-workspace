param([string]$Root=(Resolve-Path (Join-Path $PSScriptRoot '..')).Path)
$ErrorActionPreference='Stop'
$workspace=(Resolve-Path $Root).Path
$testRoot=Join-Path $workspace '_test_web_lead_rate_limit'
$mariaZip=Join-Path $workspace 'build-cache\mariadb-11.4.8-winx64.zip'
$phpExe='G:\xampp\php\php.exe'
if(!(Test-Path -LiteralPath $mariaZip)){throw 'MariaDB test runtime is missing'}
if(!(Test-Path -LiteralPath $phpExe)){throw 'PHP test runtime is missing'}
if(Test-Path -LiteralPath $testRoot){$resolved=(Resolve-Path -LiteralPath $testRoot).Path;if(!$resolved.StartsWith($workspace,[StringComparison]::OrdinalIgnoreCase)){throw 'Unsafe test cleanup path'};Remove-Item -LiteralPath $resolved -Recurse -Force}
New-Item -ItemType Directory -Force -Path $testRoot | Out-Null
$dbProcess=$null;$webProcess=$null;$database='crm_web_lead_test';$dbPassword='';$port=3311;$webPort=18081
$ErrorActionPreference='Continue' # MariaDB clients write benign connection warnings to stderr on Windows.
try{
  $extract=Join-Path $testRoot 'mariadb';Expand-Archive -LiteralPath $mariaZip -DestinationPath $extract -Force
  $mariaHome=(Get-ChildItem -LiteralPath $extract -Directory|Select-Object -First 1).FullName
  $data=Join-Path $testRoot 'data';New-Item -ItemType Directory -Force -Path $data|Out-Null
  $server=Join-Path $mariaHome 'bin\mariadbd.exe';$installer=Join-Path $mariaHome 'bin\mariadb-install-db.exe';$mysql=Join-Path $mariaHome 'bin\mysql.exe';$admin=Join-Path $mariaHome 'bin\mysqladmin.exe'
  &$installer "--datadir=$data" "--password=$dbPassword" "--port=$port"
  if($LASTEXITCODE-ne0){throw 'MariaDB initialization failed'}
  $dbProcess=Start-Process -FilePath $server -ArgumentList @('--no-defaults',"--basedir=$mariaHome","--datadir=$data","--port=$port",'--bind-address=127.0.0.1','--skip-networking=0','--skip-grant-tables') -PassThru -WindowStyle Hidden
  $ready=$false;for($i=0;$i-lt30;$i++){Start-Sleep -Milliseconds 500;$client=New-Object Net.Sockets.TcpClient;try{if($client.ConnectAsync('127.0.0.1',$port).Wait(500)-and$client.Connected){$ready=$true;break}}catch{}finally{$client.Dispose()}}
  if(!$ready){throw 'MariaDB test instance did not start'}
  $key='rate-limit-test-'+[guid]::NewGuid().ToString('N')
  $sql=('CREATE DATABASE {0} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE {0}; CREATE TABLE v10_web_forms(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,company_id BIGINT UNSIGNED NOT NULL,public_key VARCHAR(100) NOT NULL,active TINYINT NOT NULL,fields_json JSON NOT NULL,title_fa VARCHAR(255) NOT NULL); CREATE TABLE leads(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,company_id BIGINT UNSIGNED NOT NULL,title VARCHAR(255) NOT NULL,customer_name VARCHAR(255),phone VARCHAR(64),email VARCHAR(255),source VARCHAR(64),stage VARCHAR(64),notes TEXT); INSERT INTO v10_web_forms(company_id,public_key,active,fields_json,title_fa) VALUES(1,''{1}'',1,JSON_ARRAY(''name'',''phone'',''email'',''message''),''آزمون'');' -f $database,$key)
  &$mysql --skip-ssl --connect-timeout=3 --protocol=tcp --host=127.0.0.1 "--port=$port" --user=root -e $sql
  if($LASTEXITCODE-ne0){throw 'Test schema creation failed'}
  $web=Join-Path $testRoot 'web';New-Item -ItemType Directory -Force -Path $web|Out-Null
  foreach($file in @('web-lead.php','v10-lib.php','bootstrap.php','product.php')){Copy-Item -LiteralPath (Join-Path $workspace $file) -Destination $web}
  $config="<?php return ['timezone'=>'Asia/Tehran','db'=>['host'=>'127.0.0.1','port'=>$port,'name'=>'$database','user'=>'root','pass'=>'$dbPassword','charset'=>'utf8mb4']];"
  [IO.File]::WriteAllText((Join-Path $web 'config.php'),$config,(New-Object Text.UTF8Encoding($false)))
  $webProcess=Start-Process -FilePath $phpExe -ArgumentList @('-S',"127.0.0.1:$webPort",'-t',$web) -WorkingDirectory $web -PassThru -WindowStyle Hidden
  $url="http://127.0.0.1:$webPort/web-lead.php?k=$key";$available=$false;for($i=0;$i-lt20;$i++){Start-Sleep -Milliseconds 250;$status=&curl.exe -sS -o (Join-Path $testRoot 'get.html') -w '%{http_code}' $url;if($status-eq'200'){$available=$true;break}}
  if(!$available){throw 'PHP test server did not start'}
  $codes=@();for($i=1;$i-le6;$i++){$codes+=(&curl.exe -sS -o (Join-Path $testRoot "post-$i.html") -w '%{http_code}' -X POST --data-urlencode "name=Rate Test $i" --data-urlencode 'phone=0210000000' --data-urlencode 'email=rate@example.test' --data-urlencode 'message=burst test' $url)}
  $count=(& $mysql --skip-ssl --connect-timeout=3 --protocol=tcp -N -B --host=127.0.0.1 "--port=$port" --user=root -e "SELECT COUNT(*) FROM $database.leads").Trim()
  if(($codes[0..4]|Where-Object{$_-ne'200'}).Count-ne0-or$codes[5]-ne'429'-or$count-ne'5'){throw "Rate-limit test failed: codes=$($codes-join',') leads=$count"}
  Write-Host "Web-lead integration OK: HTTP $($codes-join', '); accepted leads=$count; sixth submission limited."
}finally{
  if($webProcess-and!$webProcess.HasExited){Stop-Process -Id $webProcess.Id -Force -ErrorAction SilentlyContinue}
  if($dbProcess-and!$dbProcess.HasExited){try{&$admin --skip-ssl --connect-timeout=2 --protocol=tcp --host=127.0.0.1 "--port=$port" --user=root shutdown 2>$null|Out-Null}catch{};if(!$dbProcess.WaitForExit(5000)){Stop-Process -Id $dbProcess.Id -Force -ErrorAction SilentlyContinue}}
  if(Test-Path -LiteralPath $testRoot){$resolved=(Resolve-Path -LiteralPath $testRoot).Path;if(!$resolved.StartsWith($workspace,[StringComparison]::OrdinalIgnoreCase)){throw 'Unsafe test cleanup path'};Remove-Item -LiteralPath $resolved -Recurse -Force}
}
