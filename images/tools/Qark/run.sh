#!/bin/bash

FULL_PATH_TO_APK=$1
APK_FILENAME=$2

export LANG=C.UTF-8

cp ${FULL_PATH_TO_APK} /tmp/
cd /opt/qark/qark
script --return -c "/usr/bin/python qarkMain.py --source 1 --pathtoapk /tmp/${APK_FILENAME} --exploit 0 --install 0" /dev/null

echo -e "---- HTML ----\n"
cat /opt/qark/qark/report/report.html
