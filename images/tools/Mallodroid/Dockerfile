FROM alpine:3.6

MAINTAINER Rom Luengwattanapong <s1567783@ed.ac.uk>

USER root

# Installs bash and python for run script
RUN \
apk --no-cache upgrade && \
apk --no-cache add bash coreutils

# Installs Dependencies
RUN \
apk --no-cache add --virtual .build-dependencies openssl wget \
ca-certificates unzip tar curl git

RUN \
apk --no-cache add --virtual .compile-dependencies alpine-sdk g++ patch && \
apk --no-cache add python py2-pip python2-dev musl-dev libxml2-dev libxml2 \
libxslt libxslt-dev

# Install Androguard and all its requirements
RUN \
pip install --upgrade pip && \
pip install setuptools requests yara future && \
pip install git+git://github.com/dweinstein/pyfuzzy@master && \
pip install git+git://github.com/androguard/androguard.git@v2.0

# Install Mallodroid
RUN \
git clone https://github.com/adithyabenny/mallodroid.git /opt/mallodroid/

COPY mallodroid.patch /opt/mallodroid

RUN \
cd /opt/mallodroid/ && \
patch < mallodroid.patch

# Cleans up
RUN \
apk del .build-dependencies .compile-dependencies && \
rm -rf /tmp/* /var/tmp/*

# Create application folder
RUN \
mkdir -p /app

VOLUME ["/app"]
ENTRYPOINT ["/usr/bin/python", "/opt/mallodroid/mallodroid.py"]

# RUN: docker run -v /home/rom/Documents/secure_app_store/images/tools/Mallodroid/:/app:ro -it --rm ML -f /app/kodi-17.3-Krypton-armeabi-v7a.apk
