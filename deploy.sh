#!/bin/bash

docker build -t secureappstore_argus ./images/tools/Argus_SAF/
docker build -t secureappstore_evicheck ./images/tools/EviCheck/
docker build -t secureappstore_flowdroid ./images/tools/Flowdroid/
docker build -t secureappstore_mallodroid ./images/tools/Mallodroid/
docker build -t secureappstore_qark ./images/tools/Qark/
docker build -t webserver ./images/webserver
docker build -t queue ./images/queue/
docker pull mariadb
docker pull tianon/true

docker network create -d bridge --attachable secureappstore

docker run -d \
  --name applications \
  -v /home/rom/secure_app_store/website:/app \
  -v /home/rom/secure_app_store/storage/mysql:/var/lib/mysql  \
  -v /home/rom/secure_app_store/storage/queue:/data \
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
