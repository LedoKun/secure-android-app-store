Argus-SAF
---------------------------------
Argus-SAF, formally known as Amandroid, is a taint static analysis tool for Android applications. It detects inappropriate cryptographic API misuse and tracks information flows. This image was intended to be used with the Secure Android App Store Project. Please refer to the project's GitHub page (https://github.com/LedoKun/secure-android-app-store) for more information. It can be used as a stand-alone tool as follows:

Usage
==================

1. For taint analysis:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/argus-saf --filepath /path/to/apk/sample.apk --timeout 3600 --option "{\"api_or_taint\":\"t\"}"
```

2. For inappropriate cryptographic API misuse detection:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/argus-saf --filepath /path/to/apk/sample.apk --timeout 3600 --option "{\"api_or_taint\":\"a\"}"
```

For more information on Argus-SAF, please go to http://pag.arguslab.org/argus-saf.
