@echo off
setlocal enabledelayedexpansion

set merged=0
set skipped=0

for /f %%P in ('gh pr list --repo pratikid/LEG --state open --limit 1000 --json number --jq ".[].number"') do (
    echo Processing PR #%%P

    gh pr merge %%P --repo pratikid/LEG --squash --delete-branch >nul 2>&1

    if errorlevel 1 (
        set /a skipped+=1
        echo SKIPPED %%P
    ) else (
        set /a merged+=1
        echo MERGED %%P
    )
)

echo.
echo ========================
echo Merged : !merged!
echo Skipped: !skipped!
echo ========================

pause