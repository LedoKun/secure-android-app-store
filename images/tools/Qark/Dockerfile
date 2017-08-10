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

# Installs OpenJDK-7
RUN apk --no-cache add openjdk7-jre
RUN apk --no-cache add openjdk7

ENV JAVA_HOME /usr/lib/jvm/java-1.7-openjdk

# Installs Android SDKs
# Modified from https://hub.docker.com/r/bwits/android-sdk-alpine/
ENV ANDROID_SDK_FILENAME tools_r25.2.3-linux.zip
ENV ANDROID_SDK_URL https://dl.google.com/android/repository/${ANDROID_SDK_FILENAME}
ENV ANDROID_API_LEVELS android-1,android-2,android-3,android-4,android-5,android-6,android-7,android-8,android-9,android-10,android-11,android-12,android-13,android-14,android-15,android-16,android-17,android-18,android-19,android-20,android-21,android-22,android-23,android-24,android-25
ENV ANDROID_BUILD_TOOLS_VERSION 26.0.0
ENV ANDROID_HOME /opt/android-sdk-linux
ENV PATH ${PATH}:${ANDROID_HOME}/tools:${ANDROID_HOME}/platform-tools

RUN \
apk --no-cache add libc6-compat ncurses-libs libstdc++ expect && \
mkdir -p /opt && \
cd /opt && \
wget ${ANDROID_SDK_URL} && \
unzip ${ANDROID_SDK_FILENAME} -d "${ANDROID_HOME}" && \
rm ${ANDROID_SDK_FILENAME} && \
echo y | android update sdk --no-ui --all --filter tools,platform-tools,${ANDROID_API_LEVELS},build-tools-${ANDROID_BUILD_TOOLS_VERSION} && \

chown -R 1000:1000 $ANDROID_HOME

# Installs QARK
ENV GIT_COMMIT 9c8ccb5c70c5c699328116f558370e0d09a0d0cd
ENV QARK_ROOT /opt/qark
ENV PATH ${QARK_ROOT}/qark:$PATH
RUN \
export LANG=C.UTF-8 && \
apk add --no-cache python py2-pip util-linux && \
git clone https://github.com/linkedin/qark.git ${QARK_ROOT} && \
cd ${QARK_ROOT} && \
git checkout ${GIT_COMMIT} && \
pip install -r requirements.txt && \
pip install .

# Allow QARK to download its dependencies
RUN \
cp /opt/qark/qark/sampleApps/goatdroid/goatdroid.apk /tmp && \
cd /opt/qark/qark && \
script --return -c "/usr/bin/python2 qarkMain.py \
-b /opt/android-sdk-linux/ \
--source 1 --pathtoapk /tmp/goatdroid.apk \
--exploit 0 --install 0" /dev/null > /dev/null 2>&1  && \
rm -rf /tmp/goatdroid* /tmp/hsperfdata_root /tmp/*.tmp && \
rm -rf /opt/qark/qark/report/report.html

# Cleanup
RUN \
rm -rf /var/tmp/*

# Create application folder
RUN \
mkdir -p /app

VOLUME ["/app"]

COPY run.sh /tmp/
RUN \
chmod +x /tmp/run.sh

WORKDIR /tmp

ENTRYPOINT ["/tmp/run.sh"]

# RUN: docker run -v /home/rom/Documents/secure_app_store/images/tools/Qark/:/app:ro -it --rm qt --source 1 --pathtoapk /app/kodi-17.3-Krypton-armeabi-v7a.apk --exploit 0 --install 0 -b /opt/android-sdk-linux/
