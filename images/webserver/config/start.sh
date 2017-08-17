#!/bin/bash

# Fix docker socket permission
# This only works if the docker group does not already exist
# Taken from http://vitorbaptista.com/how-to-access-hosts-docker-socket-without-root
DOCKER_SOCKET=/var/run/docker.sock
DOCKER_GROUP=hostdocker
REGULAR_USER=www

if [ -S ${DOCKER_SOCKET} ]; then
    DOCKER_GID=$(stat -c '%g' ${DOCKER_SOCKET})
    groupadd -for -g ${DOCKER_GID} ${DOCKER_GROUP}
    usermod -aG ${DOCKER_GROUP} ${REGULAR_USER}
fi

mkdir -p /root/.docker
# touch /root/.docker/config.json
echo "{}" > /root/.docker/config.json
chown -R www /root

# Tweak nginx to match the workers to cpu's
procs=$(cat /proc/cpuinfo | grep processor | wc -l)
sed -i -e "s/worker_processes 5/worker_processes $procs/" /etc/nginx/nginx.conf

# Create symlink for Laravel
if [ -L /app/public/storage ]; then
    rm /app/public/storage
fi

ln -s /app/storage/app/public /app/public/storage

# Set the permission
chown www:root -R /app
chmod 775 -R /app

chown -Rf www:www /var/lib/nginx

# Start supervisord and services
exec supervisord -c /etc/supervisord.conf
