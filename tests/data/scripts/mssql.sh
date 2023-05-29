#!/bin/bash

/opt/mssql/bin/sqlservr &
/scripts/wait-for-it.sh 127.0.0.1:1433

for i in {1..50};
do
    /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P $SA_PASSWORD -d master -Q "CREATE DATABASE $MSSQL_DATABASE;"
    if [ $? -eq 0 ]
    then
        echo "database created"
        break
    else
        echo "not ready yet..."
        sleep 1
    fi
done

sleep infinity # Keep the container running forever
