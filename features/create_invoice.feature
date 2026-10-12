Feature: Create Invoice API
    In order to create new invoices
    As an API client
    I want to send invoice details to the /api/invoices endpoint and receive the created invoice

    Scenario: Successfully create an invoice with product lines
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "Customer Name",
                "customerEmail": "customer@example.com",
                "lines": [
                    {
                        "productName": "Domain Registration",
                        "quantity": 1,
                        "unitPrice": 1200
                    },
                    {
                        "productName": "Web Hosting",
                        "quantity": 12,
                        "unitPrice": 500
                    }
                ]
            }
            """
        Then the response status code should be 201
        And the response content type should be "application/json"
        And the response JSON should contain a valid UUID id

    Scenario: Successfully create an invoice with empty product lines
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "John Doe",
                "customerEmail": "john@example.com",
                "lines": []
            }
            """
        Then the response status code should be 201
        And the response content type should be "application/json"
        And the response JSON should contain a valid UUID id

    Scenario: Validation fails when request payload contains additional fields
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "Jane Doe",
                "customerEmail": "jane@example.com",
                "unnecessaryField": "sent-to-client",
                "lines": []
            }
            """
        Then the response status code should be 422
        And the response content type should be "application/json"
        And the response JSON should contain a violation on "unnecessaryField"

    Scenario: Validation fails when customer name is blank and email is invalid
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "",
                "customerEmail": "not-an-email"
            }
            """
        Then the response status code should be 422
        And the response content type should be "application/json"
        And the response JSON should contain a violation on "customerName"
        And the response JSON should contain a violation on "customerEmail"

    Scenario: Validation fails when product line has invalid quantity and unit price
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "Test Customer",
                "customerEmail": "test@example.com",
                "lines": [
                    {
                        "productName": "",
                        "quantity": 0,
                        "unitPrice": -50
                    }
                ]
            }
            """
        Then the response status code should be 422
        And the response content type should be "application/json"
        And the response JSON should contain a violation on "lines[0].productName"
        And the response JSON should contain a violation on "lines[0].quantity"
        And the response JSON should contain a violation on "lines[0].unitPrice"

    Scenario: Request with malformed JSON syntax fails
        When I send a POST request to "/api/invoices" with JSON:
            """
            {
                "customerName": "Test Customer",
                "customerEmail":
            }
            """
        Then the response status code should be 400
