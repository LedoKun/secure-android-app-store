QARK
---------------------------------
QARK is a vulnerability scanner for Android applications. This image was intended to be used with the Secure Android App Store Project. Please refer to ![the project's GitHub page](https://github.com/LedoKun/secure-android-app-store) for more information. It can be used as a stand-alone tool as follows:

Usage
==================

To scan for vulnerability:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/qark --filepath /path/to/apk/sample.apk --timeout 3600
```

For more information on QARK, please go to https://github.com/linkedin/qark.
