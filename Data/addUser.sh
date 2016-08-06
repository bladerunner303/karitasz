#!/bin/bash 
 
## script name; mysql_script_runner.sh 
## wrapper script to execute mysql script with variables 
 
ARGS=5

serverUser="$1"
serverPassword="$2"
serverHost="$3"
sema="$4"
name="$5" 
email="$6" 
uuid=$(cat /proc/sys/kernel/random/uuid)
mysql -vvv -h "$serverHost" -u "$serverUser" "-p$serverPassword" $sema -A -e "set @UUID='${uuid}'; set @pname='${name}';  set @pemail='${email}'; source addUser.sql;"
 
exit
 
# end of script.