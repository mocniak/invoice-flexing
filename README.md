# Sewik Readme

A web application allowing fast search and report generation for the Polish SEWIK (System Ewidencji Wypadków i Kolizji) database.

![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. Get Unix-based system. Dockerized Clickhouse does not work on Windows, we will fix it in the future. Maybe.
2. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
3. Run `docker compose build --no-cache` to build fresh images
4. Run `docker compose up -d --wait` to set up and start application
5. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
6. Run to create the database:
```shell
docker compose exec php bin/console doctrine:database:create
``` 

7. Run to create the database schema (we do not use migrations for now)
```shell
docker compose exec php bin/console doctrine:schema:update --force
``` 

## Importing Data from XML files
1. Download the XML files from [here (480 MB tar.gz)](http://mocniak.pl/sewik/fixed-sewik-xml-(2018-25).tar.xz) or ask KGP (Komenda Główna Policji) for their database dump.
2. Create a directory in main directory of the application, e.g. `xml`. In the directory create subdirectories for every year, e.g. `xml/2020/`, `xml/2021/`, etc. and put the (uncompressed) XML files in the corresponding directories.
3. Run `docker compose exec php bin/console app:mysql:import-accidents-from-xml` to import the data (by default from the `xml` directory, see `--directory` flag). The command will automatically find all XML files in the subdirectories and import them into the database. By default, command skips records already imported (see `--truncate` flag).
4. Run `docker compose exec php bin/console app:clickhouse:import-from-mysql` to import the data from MySQL to ClickHouse (if it fails create a new user in clickhouse and grant the same permissions as in the default user, update credentials in `.env.local` file and try again).
5. Run `docker compose exec php bin/console app:clickhouse:validate` to compare official number of accidents (by year) with those imported from xml files (it might be off by some number depending on the time of date of dump).
6. Run `docker compose exec php bin/console app:mysql:import-code-descriptions` to import code descriptions from SEWIK dictionary to the database (dictionary is already in the repository).
7. Run `docker compose exec php bin/console app:mysql:generate-location-dictionary` to generate location (voivodeships, counties and municipalities) list.
8. Open `https://localhost/reports` and play with the reports.

## Shutting Down 
1. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Frontend
There is a simple frontend application for displaying accidents on a map. It is built using Leaflet.js and its files are located in the assets directory. The main file is `assets/app.ts`. The dependencies are listed in `package.json` and after running `npm install` (or `yarn install`) they will be installed in the `node_modules` directory. To build the frontend run `npm run build` (or `yarn build`). Running the build script will create the `public/build` directory with the built files that are being loaded in the browser.

## Troubleshooting
In case of problems with connection to mysql/clickhouse check credentials in `.env` file. If you change them, make sure to rebuild the containers with `docker compose build --no-cache`. You can use credentials from the `.env` file in standalone database client om your local machine with host set to `localhost` and with a port number exposed by docker to a given container (run `docker compose ps` to check the ports).

**Enjoy!**
