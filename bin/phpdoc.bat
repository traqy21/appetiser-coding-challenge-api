@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET PHPDOC=%~dp0/phpdoc.phar
SET PHPDOCMD=%~dp0/../vendor/evert/phpdoc-md/bin/phpdocmd
SET SOURCE=%~dp0/../app/
SET HTML=%~dp0/../docs/html/
SET STRUCTURE=%~dp0/../docs/
SET STRUCTURE_FILE=%~dp0/../docs/structure.xml
SET DESTINATION=%~dp0/../docs/md

php "%PHPDOC%" -d "%SOURCE%" -t "%HTML%"
php "%PHPDOC%" -d "%SOURCE%" -t "%STRUCTURE%" --template="xml"
php "%PHPDOCMD%" "%STRUCTURE_FILE%" "%DESTINATION%"