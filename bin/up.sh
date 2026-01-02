#!/bin/bash

CU_DIR="$( cd "$( dirname "$0" )" && cd ../ && pwd )";

export $(egrep -v '^#' $CU_DIR/.env | xargs);

docker kill $(docker ps -q)

docker-compose -p $APP_NAME --file $CU_DIR/docker-compose.yml up -d

docker ps
