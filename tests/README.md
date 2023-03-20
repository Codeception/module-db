# Codeception Internal Tests

In case you submit a pull request, you will be asked for writing a test.

## Dockerized testing

### Local testing and development with `docker compose`

Start

    docker compose up -d

Install composer dependencies

    docker exec -it codeception-module-db bash -c "composer install"

Run suite all tests

    docker exec -it codeception-module-db bash -c "php vendor/bin/codecept run"

Development bash

    docker exec -it codeception-module-db bash

Cleanup

    docker exec -it codeception-module-db bash -c "php vendor/bin/codecept clean"

Stop containers

    docker compose stop
