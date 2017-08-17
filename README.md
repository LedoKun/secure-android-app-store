Secure Android App Store
========================

Secure Android App Store is an Android app store infrastructure that relies on fully automated app analysis using off-device application analysis tools. The purpose of this project is to integrate existing analysis tools that could be a part of an easy-to-deploy app store and to be a starting point for those who wish to build an Android app store that relies on automated app analysis.

Containerized Tools
-------------------

We selected several existing Android app analysis tools and incorporated them into our project as follows:

* Argus-SAF
* EviCheck
* FlowDroid
* QARK
* MalloDroid

Folder Structure
----------------

```
./secure_app_store
├── deploy.sh                   - An example of deployment script, used in the evaluation
├── docker-compose-build-image-locally.yml  - Docker compose file, build the image locally
├── docker-compose.yml          - Docker compose file, uses pre-built images from Docker Hub
├── images                      - Store the infrastructure Dockerfiles
│   ├── queue                       - Beanstalk Dockerfile
│   ├── tools                       - Containerized Tools Dockerfile
│   └── webserver                   - Web service stacks Dockerfile
├── LICENSE
├── README.md                   - This file!
├── storage                     - Storage area for MariaDB, Beanstalk, and EviCheck's policy
│   └── policy                      - Store EviCheck's policy
└── website                     - Laravel's root folder
    ├── app                         - Stores Controllers, Models, and Background worker
    ├── artisan
    ├── bootstrap
    ├── composer.json
    ├── composer.lock
    ├── config
    ├── database
    ├── node_modules
    ├── npm-debug.log
    ├── package.json
    ├── phpunit.xml
    ├── public
    ├── readme.md
    ├── resources                   - Stores website's views
    ├── routes                      - Stores website's routes
    ├── server.php
    ├── storage                     - Stores uploaded APK files, results and logs
    ├── tests                       - Stores Unit and Feature tests
    ├── vendor
    └── webpack.mix.js
```

Installation Guide
------------------

Requirement: Linux OS, Docker, Docker-compose

1. Clone the project repository to your computer.
```
$ git clone https://github.com/LedoKun/secure-android-app-store.git
```

2. Set Docker-compose environment variables and modify .env file
```
$ cd secure-android-app-store/
$ cp .env.example .env
$ nano .env
```


3. Download project's pre-built images from Docker Hub.
```
$ docker-compose pull
```

4. Configure App Store website.
```
$ docker-compose up -d webserver
$ docker-compose exec webserver sh
$ cp .env.example.appstore .env
$ nano .env
```

5. (Optional) Change admin's credentails.

```
$ nano database/migrations/2014_10_12_000000_create_users_table.php
```

Edit the following lines:

```
DB::table('users')->insert(
  array(
    'name'                          => 'admin',
    'username'                      => 'admin',
    'email'                         => 'admin@none.com',
    'password'                      => \Hash::make('secretpassword'),
    )
  );
}
```

6. Install Laravel PHP framework and populate the database.
```
$ composer update
$ php artisan key:generate
$ php artisan migrate
```

7. Restart the web server container after the configuration.
```
$ docker-compose restart
```

8. Navigate to http://localhost:8080 (The default username: admin, password: secretpassword).

9. (Optional) The default path for EviCheck's policy is at 'storage/policy', place your own policies there.

10. (Optional) To move the Secure Android App Store to production stage, edit 'website/.env' as follows:
```
APP_ENV=production
APP_DEBUG=false
```
