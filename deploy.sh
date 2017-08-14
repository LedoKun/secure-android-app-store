#!/bin/bash

docker pull ledokun/argus-saf
docker pull ledokun/evicheck
docker pull ledokun/flowdroid
docker pull ledokun/mallodroid
docker pull ledokun/qark
docker pull ledokun/secure-android-app-store
docker pull ledokun/beanstalkd
docker pull mariadb
docker pull tianon/true

docker tag ledokun/secure-android-app-store webserver
docker tag ledokun/beanstalkd queue

docker network create -d bridge --attachable secureappstore

docker run -d \
  --name applications \
  -v $PWD/website:/app \
  -v $PWD/storage/mysql:/var/lib/mysql  \
  -v $PWD/storage/queue:/data \
  --network=secureappstore \
  tianon/true

# Need to wait for applications container to be created
sleep 2

docker run -d \
  --name db \
  --rm \
  --hostname db \
  -e MYSQL_ROOT_PASSWORD=somepassword \
  -e MYSQL_DATABASE=default \
  -e MYSQL_USER=default \
  -e MYSQL_PASSWORD=defaultsecret \
  --volumes-from applications \
  --network=secureappstore \
  mariadb

docker run -d \
  --name queue \
  --rm \
  --hostname queue \
  --volumes-from applications \
  --network=secureappstore \
  queue

docker run -d \
  --name webserver \
  --rm \
  --hostname webserver \
  --link db \
  --link queue \
  -p 8080:80 \
  -p 4433:443 \
  --volumes-from applications \
  --network=secureappstore \
  webserver
