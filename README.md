# Coordinatorr

## About Coordinatorr

Coordinatorr is a simple, free ham radio award event callsign booking software. It is based on the [Laravel](https://laravel.com) PHP Framework.

You can find the feature list, all information including system requirements and an installation guide, as well as the complete documentation [on this page](https://hamawardz.de).

Read about how this project came to be on my [Blog](https://www.db4scw.de/introducing-eventcoordinatorr/).

73, de Stefan, DB4SCW

## Running using Docker
A docker container can be created using the following:
```bash
docker run --rm -d \
  --name coordinatorr \
  -p 8073:80 \
  -v PATH_TO_ENV_FILE/.env:/var/www/coordinatorr/.env \ # Bind mount for .env
  -v coordinatorr_db:/var/www/coordinatorr/database \ # Volume for the database
  -e APACHE_RUN_USER=www-data \
  -e APACHE_RUN_GROUP=www-data \
  --restart unless-stopped \
  ghcr.io/DB4SCW/coordinatorr:master

```
Or using the compose file `docker-compose.sample.yml`:
```bash
cp docker-compose.sample.yml docker-compose.yml
docker compose up -d
```
> [!IMPORTANT]
> To manage the running instance, you need to bind mount the `.env` file to a known location (replace PATH_TO_ENV_FILE accordingly) and edit it according to your configuration, as explained in detail [here](https://hamawardz.de/docs/coordinatorr/installation/#step-4-configure-your-software-environment).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel itself, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). 

If you discover a security vulnerability within Coordinatorr, please send an e-mail to DB4SCW, Stefan Wolf via [db4scw@darc.de](mailto:db4scw@darc.de). 

All security vulnerabilities will be promptly addressed.

## Contributing

If you would like to contribute to Coordinatorr in any way, it is most welcome. This project has been developed in free time, so help is much appreciated.  

If you submit a PR, please only do so against the dev branch. PR against master will be rejected. As for your code, please check if it is properly commented and just contains ONE feature or bugfix per PR. Please also add a meaningful description what your new feature or bugfix does and why it is beneficial for coordinatorr.

## Contributors

Special thanks to our contributors, who have helped to improve this software:

[Bastien Cabay ON4BCY](https://github.com/Bastiti)

[Jules F4IEY](https://github.com/f4iey)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
