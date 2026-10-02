# laravel-check: runs Pint (style) and PHPStan (static analysis) on the edited file,
# only in Laravel projects and only if those tools are installed in vendor/bin.
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
# Hooks run from the project root. Support monorepos where Laravel lives in backend/.
$laravelRoot = $null
if (Test-Path "artisan") { $laravelRoot = "." }
elseif (Test-Path "backend\artisan") { $laravelRoot = "backend" }
if (-not $laravelRoot) { exit 0 }
# Never analyze vendor or generated files.
if ($filePath -match '\\vendor\\|/vendor/|\\storage\\|/storage/') { exit 0 }

$problems = New-Object System.Collections.Generic.List[string]

$pint = Join-Path $laravelRoot "vendor\bin\pint.bat"
if (Test-Path $pint) {
    $out = (& $pint $filePath --test 2>&1) | Out-String
    if ($LASTEXITCODE -ne 0) {
        $problems.Add("Pint style issues (auto-fix with: $pint `"$filePath`"):`n$($out.Trim())")
    }
}

$phpstan = Join-Path $laravelRoot "vendor\bin\phpstan.bat"
if (Test-Path $phpstan) {
    $out = (& $phpstan analyse $filePath --no-progress --memory-limit=512M 2>&1) | Out-String
    if ($LASTEXITCODE -ne 0) {
        $problems.Add("PHPStan findings:`n$($out.Trim())")
    }
}

if ($problems.Count -eq 0) { exit 0 }

$msg = "LARAVEL QUALITY CHECKS failed for $filePath :`n" + ($problems -join "`n`n")

if ($Mode -eq "cursor") {
    @{ additional_context = $msg } | ConvertTo-Json -Compress
    exit 0
}

[Console]::Error.WriteLine($msg)
exit 2
