@echo off & setlocal ENABLEEXTENSIONS
rem
rem *** Knotwerk Build Standard Packages *******************
rem *                                                      *
rem * This script builds the standard release packages for *
rem * the Knotwerk library. It builds and then runs the    *
rem * full tests on each release.                          *
rem *                                                      *
rem ********************************************************

rem CHANGE TO ROOT DIR
cd /d %~dp0/..
echo Building standard knotwerk packages...

rem BUILD BASIC MVC PACKAGE
set file=core-db-controllers-views.php
php _tools/export.php -t %file% -a bootstrap.php -a core -a db -a controllers -a views
if errorlevel = 1 goto Error
echo Basic MVC development: %file%
php test.php -b %file% -p core -p db -p controllers -p views
if errorlevel = 1 goto Error

rem APPLICATION PACKAGE
set file=core-db-controllers-views-i18n-acl-forms-wiki.php
php _tools/export.php -t %file% -a bootstrap.php -a core -a db -a controllers -a views -a i18n -a acl -a forms -a wiki
if errorlevel = 1 goto Error
echo Application development: %file%
php test.php -b %file% -p core -p db -p controllers -p views -p i18n -p acl -p forms -p wiki
if errorlevel = 1 goto Error

rem COMPLETE, AND ERROR HANDLING
goto Ok

:Error
del %file%
echo !!! An error has occurred, please investigate. !!!
goto End

:Ok
echo Completed OK

:End
