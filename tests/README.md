# Local Test Environment

## Prerequisites

- Docker
- Docker Compose
- Make

## Setup

1. Clone the repository to your local machine.
2. Navigate to the project directory.

## Running the Docker Environment

To start the Docker environment, use the following command:

```bash
make start
```

This command will start all the necessary containers for the application. It will also build the Docker images if they are not already built.

## Running Tests

To run the tests, use the following command:

```bash
make test
```

This command will execute the tests inside the PHP container.

## Running the Tests Pipeline

To run the tests pipeline, use the following command:
    
```bash
make pipeline
```

This command will start the containers, install dependencies, run the tests, and then stop the containers.

## Running the Tests for Continuous Integration

```bash
make ci
```

This command will run the tests pipeline for all supported PHP versions.

## Other Commands

- To stop and remove the Docker containers, use the following command:

```bash
make stop
```

- To open a bash shell inside the PHP container, use the following command:

```bash
make php-cli
```

- To install the dependencies, use the following command:

```bash
make vendor
```

Please note that all these commands should be run from the root directory of the project where the `Makefile` is located.
