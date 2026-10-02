# php-lint: runs `php -l` on the edited PHP file.
# -Mode claude => stderr + exit 2 (feeds error back to Claude Code)
# -Mode cursor => JSON additional_context on stdout (feeds error back to Cursor agent)
param([string]$Mode = "claude")

$ErrorActionPreference = "SilentlyContinue"

$raw = [Console]::In.ReadToEnd()
$filePath = $null
try {
    $json = $raw | ConvertFrom-Json
    if ($json.tool_input.file_path) { $filePath = $json.tool_input.file_path }
    elseif ($json.file_path)        { $filePath = $json.file_path }
    elseif ($json.tool_input.path)  { $filePath = $json.tool_input.path }
} catch {}

if (-not $filePath -or ($filePath -notmatch '\.php$') -or -not (Test-Path -LiteralPath $filePath)) {
    exit 0
}

$phpExe = "C:\xampp\php\php.exe"
if (-not (Test-Path $phpExe)) { $phpExe = "php" }

$output = (& $phpExe -l $filePath 2>&1) | Out-String
if ($LASTEXITCODE -eq 0) { exit 0 }

$msg = "PHP SYNTAX ERROR in $filePath - fix it now:`n$($output.Trim())"

if ($Mode -eq "cursor") {
    @{ additional_context = $msg } | ConvertTo-Json -Compress
    exit 0
}

[Console]::Error.WriteLine($msg)
exit 2
