# security-check: scans the edited PHP file for dangerous patterns
# (SQL concatenation, unescaped superglobal output, weak hashing, hardcoded credentials).
# Warnings are fed back to the agent so it fixes them immediately.
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
# Local-only credential files are allowed to contain secrets.
if ($filePath -match '(config\.local\.php|config\.example\.php|\.env)') { exit 0 }

$patterns = @(
    @{ Re = '["''](SELECT|INSERT\s+INTO|UPDATE|DELETE\s+FROM)\b[^"'']*\$';
       Msg = 'Variable interpolated inside SQL string - use a prepared statement with placeholders' },
    @{ Re = '["'']\s*\.\s*\$_(GET|POST|REQUEST|COOKIE)';
       Msg = 'Superglobal concatenated into a string (possible SQL/HTML injection) - validate and bind/escape' },
    @{ Re = '(echo|print)\s+\$_(GET|POST|REQUEST|COOKIE)';
       Msg = 'Superglobal echoed without escaping (XSS) - wrap with e()/htmlspecialchars' },
    @{ Re = '<\?=\s*\$_(GET|POST|REQUEST|COOKIE)';
       Msg = 'Superglobal in short echo without escaping (XSS) - wrap with e()/htmlspecialchars' },
    @{ Re = '(md5|sha1)\s*\([^)]*(pass|clave|pwd)';
       Msg = 'Weak hash used for a password - use password_hash()/password_verify()' },
    @{ Re = '(?i)(db_pass|db_password|api_key|api_secret|secret_key)["'']?\s*(=|=>)\s*["''][^"'']{6,}["'']';
       Msg = 'Possible hardcoded credential - move it to config.local.php / .env' }
)

$findings = New-Object System.Collections.Generic.List[string]
$lineNum = 0
foreach ($line in (Get-Content -LiteralPath $filePath)) {
    $lineNum++
    foreach ($p in $patterns) {
        if ($line -cmatch $p.Re -or $line -match $p.Re) {
            $findings.Add("  line ${lineNum}: $($p.Msg)")
            $findings.Add("    > $($line.Trim())")
            break
        }
    }
}

if ($findings.Count -eq 0) { exit 0 }

$msg = "SECURITY WARNINGS in $filePath - review and fix before continuing:`n" + ($findings -join "`n")

if ($Mode -eq "cursor") {
    @{ additional_context = $msg } | ConvertTo-Json -Compress
    exit 0
}

[Console]::Error.WriteLine($msg)
exit 2
