#!/bin/bash 
 
## script name; mysql_script_runner.sh 
## wrapper script to execute mysql script with variables 

serverUser="$1"
serverPassword="$2"
serverHost="$3"
sema="$4"
name="$5" 

mysql -vvv -h "$serverHost" -u "$serverUser" "-p$serverPassword" $sema -A -e "set @name='${name}'; source removeUser.sql;"
 
exit
 
# end of script.