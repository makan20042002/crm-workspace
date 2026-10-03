param([Parameter(Mandatory=$true)][string]$Root)
$ErrorActionPreference='SilentlyContinue'
$data=Join-Path $Root 'data';$runtime=Join-Path $Root 'runtime';$app=Join-Path $Root 'app'
$mysql=Join-Path $runtime 'mariadb\bin\mysqld.exe';$php=Join-Path $runtime 'php\php-cgi.exe';$caddy=Join-Path $runtime 'caddy\caddy.exe'
if(-not(Get-Process mysqld -ErrorAction SilentlyContinue)){Start-Process $mysql -ArgumentList "--defaults-file=`"$data\mariadb\my.ini`"" -WindowStyle Hidden}
if(-not(Get-Process php-cgi -ErrorAction SilentlyContinue)){Start-Process $php -ArgumentList '-b','127.0.0.1:9000','-c',(Join-Path $runtime 'php\php.ini') -WorkingDirectory $app -WindowStyle Hidden}
if(-not(Get-Process caddy -ErrorAction SilentlyContinue)){Start-Process $caddy -ArgumentList 'run','--config',(Join-Path $data 'Caddyfile'),'--adapter','caddyfile' -WorkingDirectory $app -WindowStyle Hidden}
