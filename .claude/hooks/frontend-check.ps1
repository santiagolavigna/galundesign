# frontend-check: type-checks the Vue/TS frontend with vue-tsc when a commit
# includes .vue/.ts files. Runs at commit time (not per-edit) because vue-tsc
# checks the whole project and takes seconds.
# -Mode git    => .git/hooks/pre-commit (exit 1 blocks)
# -Mode claude => Claude Code PreToolUse on Bash, only acts on `git commit` (exit 2 blocks)
# -Mode cursor => Cursor beforeShellExecution: returns permission allow/deny JSON
param([string]$Mode = "git")

$ErrorActionPreference = "SilentlyContinue"

function Deny([string]$reason) {
    if ($Mode -eq "cursor") {
        @{ permission    = "deny"
           user_message  = "Commit bloqueado por errores de TypeScript. Corregilos y volve a commitear."
           agent_message = "Commit blocked: vue-tsc type errors. Fix them, then commit again.`n$reason" } | ConvertTo-Json -Compress
        exit 0
    }
    [Console]::Error.WriteLine("COMMIT BLOCKED - TypeScript errors:`n$reason")
    if ($Mode -eq "claude") { exit 2 } else { exit 1 }
}

function Allow() {
    if ($Mode -eq "cursor") { @{ permission = "allow" } | ConvertTo-Json -Compress }
    exit 0
}

# Agent modes: only act on git commit commands.
if ($Mode -eq "claude" -or $Mode -eq "cursor") {
    $raw = [Console]::In.ReadToEnd()
    $command = $null
    try {
        $json = $raw | ConvertFrom-Json
        if ($json.tool_input.command) { $command = $json.tool_input.command }
        elseif ($json.command)        { $command = $json.command }
    } catch {}
    if (-not $command -or ($command -notmatch 'git\s+(\S+\s+)*commit')) { Allow }
}

# Only run when the commit actually touches frontend source files.
$staged = git diff --cached --name-only 2>$null
$touchesFrontend = $staged | Where-Object { $_ -match '\.(vue|ts|tsx)$' } | Select-Object -First 1
if (-not $touchesFrontend) { Allow }

# Locate the frontend package (monorepo frontend/ or project root).
$feDir = $null
if (Test-Path "frontend\package.json") { $feDir = "frontend" }
elseif (Test-Path "package.json")      { $feDir = "." }
if (-not $feDir) { Allow }

$pkg = Get-Content (Join-Path $feDir "package.json") -Raw
if ($pkg -notmatch 'vue-tsc') { Allow }   # no type-checker configured in this project

Push-Location $feDir
if ($pkg -match '"type-check"') {
    $out = (& npm run type-check --silent 2>&1) | Out-String
} else {
    $out = (& npx vue-tsc -b --noEmit 2>&1) | Out-String
    if ($LASTEXITCODE -ne 0 -and $out -match 'may not be used with') {
        # tsconfig with project references: -b does not accept --noEmit
        $out = (& npx vue-tsc -b 2>&1) | Out-String
    }
}
$failed = ($LASTEXITCODE -ne 0)
Pop-Location

if ($failed) {
    # Keep the message short: first 30 lines are enough to locate the errors.
    $lines = ($out -split "`r?`n") | Select-Object -First 30
    Deny (($lines -join "`n").Trim())
}

Allow
