#!/bin/bash
set -e

REDY=`docker-compose ps doc-management-app`

if [[ ! ${REDY} =~ doc-management-app.*Up ]]; then
    echo "start royal-console"
    docker-compose up -d
else
    echo "starting royal-console"
fi
