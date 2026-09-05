# -----------------------------------------------------------------------------
# Initial Application Environment Variables Setup
# -----------------------------------------------------------------------------
#
# Before the development container is created, ensure the application .env file
# exists by copying it from the example file if required.
#
# This script is intended to be run from "initializeCommand" in
# devcontainer.json.
# -----------------------------------------------------------------------------

$workspace = $env:LOCAL_WORKSPACE_FOLDER

# Fallback if LOCAL_WORKSPACE_FOLDER is not set.
if ([string]::IsNullOrWhiteSpace($workspace)) {
    $workspace = Split-Path -Parent $PSScriptRoot
    $workspace = Split-Path -Parent $workspace
}

$envFile     = Join-Path $workspace ".env"
$exampleFile = Join-Path $workspace ".env.example"

if (-not (Test-Path $envFile)) {
    Copy-Item $exampleFile $envFile
    Write-Host "Created '$envFile' from '$exampleFile'"
}
else {
    Write-Host "'$envFile' already exists."
}
