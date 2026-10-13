Feature: Send Invoice API
  In order to dispatch invoices to customers
  As an API client
  I want to send a POST request to /api/invoices/{id}/send

  Scenario: Successfully send an invoice
    Given there is an invoice for "John Doe" with email "john@example.com" and lines:
      | product   | quantity | unitPrice |
      | Test Item | 2        | 100       |
    When I send a POST request to "/api/invoices/{id}/send"
    Then the response status code should be 202
    And the message is sent with created invoice
    And the created invoice status is "sending"

  Scenario: Attempting to send a non-existent invoice
    When I send a POST request to "/api/invoices/018f0a00-0000-7000-8000-000000000000/send"
    Then the response status code should be 404

  Scenario: Attempting to send an invoice with an invalid UUID
    When I send a POST request to "/api/invoices/not-a-valid-uuid/send"
    Then the response status code should be 404

  Scenario: Attempting to send an invoice that is not in draft status
    Given there is an invoice for "Jane Doe" with email "jane@example.com" and lines:
      | product   | quantity | unitPrice |
      | Test Item | 1        | 500       |
    And the invoice is marked as sending
    When I send a POST request to "/api/invoices/{id}/send"
    Then the response status code should be 409
    And the response content type should be "application/json"
    And the response JSON should contain error "Only invoices in draft status can be marked as in sending"

  Scenario: Attempting to send an invoice without lines
    Given there is an invoice for "Bob Smith" with email "bob@example.com"
    When I send a POST request to "/api/invoices/{id}/send"
    Then the response status code should be 409
    And the response content type should be "application/json"
    And the response JSON should contain error "Invoice has to contain at least one line"
