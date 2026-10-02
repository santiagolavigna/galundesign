# commit-check: blocks commits that include secrets or client-data dumps.
# -Mode git    => used as .git/hooks/pre-commit (exit 1 blocks the commit)
# -Mode claude => Claude Code PreToolUse on Bash: only acts on `git commit` commands (exit 2 blocks)
# -Mode cursor => Cursor beforeShellExecution: returns permission allow/deny JSON
param([string]$Mode = "git")

$ErrorActionPreference = "SilentlyContinue"

function Deny([string]$reason) {
    if ($Mode -eq "cursor") {
        @{ permission    = "deny"
           user_message  = "Commit bloqueado: $reason"
           agent_message = "Commit blocked by commit-check hook: $reason. Unstage the offending files and commit again." } | ConvertTo-Json -Compress
        exit 0
    }
    [Console]::Error.WriteLine("COMMIT BLOCKED: $reason")
    if ($Mode -eq "claude") { exit 2 } else { exit 1 }
}

function Allow() {
    if ($Mode -eq "cursor") { @{ permission = "allow" } | ConvertTo-Json -Compress }
    exit 0
}

# In agent modes, inspect the shell command from stdin; ignore anything that isn't a git commit.
if ($Mode -eq "claude" -or $Mode -eq "cursor") {
    $raw = [Console]::In.ReadToEnd()
    $command = $null
    try {
        $json = $raw | ConvertFrom-Json
        if ($json.tool_input.command) { $command = $json.tool_input.command }
        elseif ($json.command)        { $command = $json.command }
    } catch {}

    if (-not $command -or ($command -notmatch 'git\s+(\S+\s+)*commit')) { Allow }
    if ($command -match '--no-verify') {
        Deny "no uses --no-verify; los hooks de commit existen para proteger secretos"
    }
}

# Check staged files.
$staged = git diff --cached --name-only 2>$null
if (-not $staged) { Allow }

$violations = New-Object System.Collections.Generic.List[string]
foreach ($f in $staged) {
    $name = Split-Path $f -Leaf

    if ($name -match '^\.env' -and $name -notmatch '\.example$') {
        $violations.Add("$f (archivo .env con credenciales)")
        continue
    }
    if ($name -ieq 'config.local.php') {
        $violations.Add("$f (config local con credenciales)")
        continue
    }
    if ($name -match '\.sql$' -and $name -notmatch '^(schema|structure)\.sql$' -and $f -notmatch '(sql/updates/|database/)') {
        $violations.Add("$f (posible dump SQL con datos de cliente)")
        continue
    }
}

if ($violations.Count -gt 0) {
    Deny ("archivos prohibidos en el commit -> " + ($violations -join '; ') + ". Sacalos del stage (git reset HEAD <archivo>) y agregalos a .gitignore.")
}

Allow
