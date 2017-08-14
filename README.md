Secure Android App Store
======

Secure Android App Store is an Android app store infrastructure that relies on fully automated app analysis using off-device application analysis tools. The purpose of this project is to integrate existing analysis tools that could be a part of an easy-to-deploy app store and to be a starting point for those who wish to build an Android app store that relies on automated app analysis.

Containerized Tools
------

We selected several existing Android app analysis tools and incorporated them into our project as follows:

* Argus-SAF
* EviCheck
* FlowDroid
* QARK
* MalloDroid

Installation Guide
------

Requirement: Docker, Docker-compose

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

5. (Optional) Change admin credentails. Edit the following lines:

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


```
$ nano database/migrations/2014_10_12_000000_create_users_table.php
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

8. Navigate to http://localhost:8080.
