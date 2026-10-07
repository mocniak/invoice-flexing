Feature:
    In order to create a valid invoice
    As a user
    I want create some valid and invalid invoices

    Scenario: Creating a new invoice
        Given there is an Invoice created
        When I retrieve created invoice
        Then retrieved Invoice status is "draft"
