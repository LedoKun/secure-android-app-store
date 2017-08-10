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

# Installs OpenJDK-8
RUN \
apk --no-cache add openjdk8-jre

ENV JAVA_HOME /usr/lib/jvm/java-1.8-openjdk

# Installs Argus-SAF
ENV ARGUS_URL https://bintray.com/arguslab/maven/download_file?file_path=com%2Fgithub%2Farguslab%2Fargus-saf_2.12%2F3.1.1%2Fargus-saf_2.12-3.1.1-assembly.jar
ENV ARGUS_ROOT /opt/argus
ENV PATH ${ARGUS_ROOT}:$PATH

COPY dummy.apk /tmp

RUN \
mkdir -p ${ARGUS_ROOT} && \
wget ${ARGUS_URL} -O ${ARGUS_ROOT}/argus-saf-assembly.jar && \
java -jar ${ARGUS_ROOT}/argus-saf-assembly.jar a /tmp/dummy.apk && \
rm -f /tmp/dummy.apk

# Cleans up
RUN \
apk del .build-dependencies && \
rm -rf /tmp/* /var/tmp/*

# Create application folder
RUN \
mkdir -p /app

VOLUME ["/app"]
WORKDIR /tmp

ENTRYPOINT ["/usr/bin/java", "-Xmx8g", "-jar", "/opt/argus/argus-saf-assembly.jar"]
# RUN: docker run -v /home/rom/Documents/secure_app_store/images/tools/Qark/:/app:ro -it --rm AG t -mo OAUTH_TOKEN_TRACKING -o /tmp /app/kodi-17.3-Krypton-armeabi-v7a.apk
