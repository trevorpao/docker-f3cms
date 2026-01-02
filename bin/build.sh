#!/bin/bash

CU_DIR="$( cd "$( dirname "$0" )" && cd ../ && pwd )";

export $(egrep -v '^#' $CU_DIR/.env | xargs);

docker-compose -p $APP_NAME --file $CU_DIR/docker-compose.yml build --no-cache

docker ps
