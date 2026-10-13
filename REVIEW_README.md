# Reviewer Readme

A simple Symfony-based application for creating and sending invoices

## Getting Started

1. To leverage a makefile, have `make` installed on the local machine (for debian/ubuntu - `sudo apt install make`)
2. Run `make build` to build fresh images
3. Run `make up` to start the application
4. Rub `make composer-install` to install dependencies
5. Run `make create-db` to create the database schema

## Endpoint documentation

Application endpoints are documented with OpenAPI attributes. SwaggerUI is available at http://localhost:8000/api/doc

## Testing

### Unit tests
Run `make test` to execute tests created with PHPUnit.

### Functional tests

Run `make behat` to execute tests created with Behat (the human-readable tests are in `features/` directory).

### Manual testing

For apps like Postman a JSON schema of endpoints is available at http://localhost:8000/api/doc.json

## Known shortcuts

1. Doctrine attributes and some persistence-related logic are present in the Invoice entity. This is a common practice in Symfony applications, and creating separate proxy entities is usually needed much later in the development process.
2. Invoice Query maps entities instead of fetching them directly from the database. I'd optimize this when the performance becomes an issue.
3. Notification API is used directly in the Invoice application layer, but since they're both in the same codebase and have the same development team (me :v) no ACL is used.
4. Notification module is assumed to response synchronously to API calls and to handle asynchronous communication and errors with mailing services on its own, so I decided that outbox/sagas are not required for now.
