# Reviewer Readme

A simple Symfony-based application for creating and sending invoices

## Getting Started

1. To leverage a makefile, have `make` installed on the local machine (for debian/ubuntu - `sudo apt install make`)
2. Run `make build` to build fresh images
3. Run `make up` to start the application
4. Run `make create-db` to install the database schema

## Endpoint documentation

Application endpoints are documented with OpenAPI attributes. SwaggerUI is available at http://localhost:8000/api/doc

## Testing

### Unit tests
Run `make test` to execute tests created with PHPUnit.

### Functional tests

Run `make behat` to execute tests created with Behat (the human-readable tests are in `features/` directory).

### Manual testing

For apps like Postman a JSON schema of endpoints is available at http://localhost:8000/api/doc.json
