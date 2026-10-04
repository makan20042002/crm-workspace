param([ValidateSet('qwen2.5:3b','qwen2.5:7b','qwen2.5:14b')][string]$Model='qwen2.5:3b')
$ErrorActionPreference='Stop'
$ollama=Get-Command ollama.exe -ErrorAction SilentlyContinue
if(-not $ollama){
  $installer=Join-Path $env:TEMP 'CRMWorkspace-OllamaSetup.exe'
  Write-Host 'Downloading Ollama installer...'
  $job=Start-BitsTransfer -Source 'https://ollama.com/download/OllamaSetup.exe' -Destination $installer -Asynchronous
  while($job.JobState -in @('Connecting','Transferring')){$job=Get-BitsTransfer -Id $job.Id;$pct=if($job.BytesTotal -gt 0){[math]::Round(100*$job.BytesTransferred/$job.BytesTotal)}else{0};Write-Progress -Activity 'Downloading Ollama' -Status "$pct%" -PercentComplete $pct;Start-Sleep -Milliseconds 500}
  if($job.JobState -ne 'Transferred'){Remove-BitsTransfer $job;throw "Ollama download failed: $($job.JobState)"}
  Complete-BitsTransfer $job;Write-Progress -Activity 'Downloading Ollama' -Completed;Start-Process $installer -ArgumentList '/S' -Wait
}
$paths=@((Join-Path $env:LOCALAPPDATA 'Programs\Ollama\ollama.exe'),(Join-Path $env:ProgramFiles 'Ollama\ollama.exe'))
$exe=(Get-Command ollama.exe -ErrorAction SilentlyContinue).Source;if(-not $exe){$exe=($paths|Where-Object{Test-Path $_}|Select-Object -First 1)};if(-not $exe){throw 'Ollama was installed but ollama.exe was not found.'}
Write-Host "Downloading model $Model. Ollama will show layer-by-layer progress...";& $exe pull $Model;if($LASTEXITCODE -ne 0){throw "Model download failed with exit code $LASTEXITCODE"};Write-Host "Local AI model $Model is ready. You can close this window."
