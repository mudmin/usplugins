@echo off
SETLOCAL ENABLEDELAYEDEXPANSION

REM Define the list of folders
SET "FOLDERS=alerts api bio badges charts cleanurls cms codesnippets comments cronpro demo downloads forms gdpr links membership messages points profile_pic pushover recaptcha refer rememberme reports saas sendinblue spicebin stripe twilio uptime usertags v4api v5api watchdog webhooks"

REM Loop through each folder and create a zip file
FOR %%F IN (%FOLDERS%) DO (
    IF EXIST "%%F\" (
        ECHO Zipping folder: %%F
        REM Creating zip file for the folder
        powershell Compress-Archive -Path "%%F\*" -DestinationPath "%%F.zip"
    ) ELSE (
        ECHO Folder %%F does not exist
    )
)

ECHO Done zipping folders.
ENDLOCAL
