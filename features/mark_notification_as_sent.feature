Feature: Mark Notification As Sent API
  In order to update invoice status when notification is delivered
  As an API client
  I want to send a POST request to /api/notification/{id}/mark-as-sent

  Scenario: Successfully mark notification as sent
    Given there is an invoice for "John Doe" with email "john@example.com" and lines:
      | product   | quantity | unitPrice |
      | Test Item | 2        | 100       |
    And the invoice is marked as sending
    When I send a POST request to "/api/notification/{id}/mark-as-sent"
    Then the response status code should be 202
    And the created invoice status is "sent-to-client"
