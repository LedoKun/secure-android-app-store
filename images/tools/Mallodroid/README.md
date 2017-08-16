MalloDroid
---------------------------------
MalloDroid is a static analysis tool for Android applications. It detects broken SSL certificates verification in Android apps. This image was intended to be used with the Secure Android App Store Project. Please refer to the project's GitHub page (https://github.com/LedoKun/secure-android-app-store) for more information. It can be used as a stand-alone tool as follows:

Usage
==================

To perform static analysis:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/mallodroid --filepath /path/to/apk/sample.apk --timeout 3600
```

For more information on MalloDroid, please go to https://github.com/sfahl/mallodroid.
