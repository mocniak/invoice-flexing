Feature: Get Invoice API
    In order to view invoice details in JSON format
    As an API client
    I want to retrieve an invoice by its ID from the /api/invoices endpoint

    Scenario: Successfully retrieve an invoice with line items
        Given there is an invoice for "John Doe" with email "john@example.com" and lines:
            | product   | quantity | unitPrice |
            | Test Item | 2        | 100       |
        When I send a GET request to "/api/invoices/{id}"
        Then the response status code should be 200
        And the response content type should be "application/json"
        And the response JSON should match the created invoice

    Scenario: Successfully retrieve an invoice with multiple line items
        Given there is an invoice for "Jane Smith" with email "jane@example.com" and lines:
            | product  | quantity | unitPrice |
            | Widget A | 1        | 50        |
            | Widget B | 3        | 25        |
        When I send a GET request to "/api/invoices/{id}"
        Then the response status code should be 200
        And the response content type should be "application/json"
        And the response JSON should match the created invoice

    Scenario: Request a non-existent invoice
        When I send a GET request to "/api/invoices/018f0a00-0000-7000-8000-000000000000"
        Then the response status code should be 404

    Scenario: Request an invoice with an invalid UUID
        When I send a GET request to "/api/invoices/not-a-valid-uuid"
        Then the response status code should be 404
