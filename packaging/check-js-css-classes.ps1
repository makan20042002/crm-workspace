param([Parameter(Mandatory=$true)][string]$Root)
$ErrorActionPreference='Stop'
$assetRoot=Join-Path $Root 'assets'
$jsFiles=@(Join-Path $assetRoot 'app.js')+@(Get-ChildItem -LiteralPath (Join-Path $assetRoot 'js') -Filter '*.js' -File|ForEach-Object FullName)
$js=($jsFiles|ForEach-Object{Get-Content -LiteralPath $_ -Raw})-join"`n"
$used=[Collections.Generic.HashSet[string]]::new([StringComparer]::Ordinal)
foreach($match in [regex]::Matches($js,'class(?:Name)?\s*=\s*["''`]([^"''`]+)["''`]')){foreach($class in ($match.Groups[1].Value-split'\s+')){if($class-match'^[A-Za-z_][A-Za-z0-9_-]*$'){[void]$used.Add($class)}}}
foreach($match in [regex]::Matches($js,'classList\.(?:add|remove|toggle)\(\s*["'']([A-Za-z_][A-Za-z0-9_-]*)["'']')){[void]$used.Add($match.Groups[1].Value)}
$css=Get-Content -LiteralPath (Join-Path $assetRoot 'app.css') -Raw
$defined=[Collections.Generic.HashSet[string]]::new([StringComparer]::Ordinal)
foreach($match in [regex]::Matches($css,'\.([A-Za-z_][A-Za-z0-9_-]*)')){[void]$defined.Add($match.Groups[1].Value)}
$missing=@($used|Where-Object{-not $defined.Contains($_)}|Sort-Object)
if($missing.Count){throw "CSS classes used by JavaScript but not defined: $($missing-join', ')"}
Write-Host "CSS coverage OK: $($used.Count) JavaScript classes are defined."
