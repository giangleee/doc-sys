#!/bin/bash
set -e

REDY=`docker-compose ps doc-management-db`

if [[ ! ${REDY} =~ doc-management-db.*Up ]]; then
    docker-compose up -d doc-management-db
fi

docker-compose exec doc-management-db bash -c 'mysql -uroot -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}'
