EviCheck
---------------------------------
EviCheck is a static analysis tool for the verification, certification and generation of lightweight fine-grained policies for Android applications. This image was intended to be used with the Secure Android App Store Project. Please refer to [![the project's GitHub page](https://github.com/LedoKun/secure-android-app-store) for more information. It can be used as a stand-alone tool as follows:

Usage
==================

1. For policy verification:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk ledokun/evicheck --filepath /path/to/apk/sample.apk --timeout 3600
```

2. To use generate a policy for application's certificate, please refer to EviCheck's documentation [![tdocumentation](http://groups.inf.ed.ac.uk/security/appguarden/tools/EviCheck/doc.html). To get a shell access to a container:
```
$ docker run -it --rm -v /path/to/apk:path/to/apk --entrypoint=bash ledokun/evicheck
```

For more information on EviCheck, please go to http://groups.inf.ed.ac.uk/security/appguarden/tools/EviCheck/.
