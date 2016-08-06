#!/bin/bash 

ARGS=4

serverUser="$1"
serverPassword="$2"
serverHost="$3"
sema="$4"

mysql -vvv -h "$serverHost" -u "$serverUser" "-p$serverPassword" $sema  < "ddl_v1.0.sql"  2>&1 >> install.log 
mysql -vvv -h "$serverHost" -u "$serverUser" "-p$serverPassword" $sema  < "dml_v1.0.sql"  2>&1 >> install.log
echo "kész" >> install.log

exit