FlowDroid
---------------------------------
FlowDroid is a taint static analysis tool for Android applications. This image was intended to be used with the Secure Android App Store Project. Please refer to ![the project's GitHub page](https://github.com/LedoKun/secure-android-app-store) for more information. It can be used as a stand-alone tool as follows:

Usage
==================

To perform taint analysis:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/flowdroid --filepath /path/to/apk/sample.apk --timeout 3600 --option "[\"APLENGTH 5\"]"
```

Note that *--option* is FlowDroid's command-line arguments in JSON array.

For more information on FlowDroid, please go to https://blogs.uni-paderborn.de/sse/tools/flowdroid/.
