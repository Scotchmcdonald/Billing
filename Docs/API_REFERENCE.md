# API Reference

## Public Quote Builder API

These endpoints allow external applications or the public-facing website to interact with the quoting engine.

### Base URL
`/billing/quote-builder`

### 1. Calculate Quote
Calculates the total cost based on selected items.

*   **Endpoint:** `POST /calculate`
*   **Auth:** None (Public)

#### Request Body
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 5
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ]
}
```

#### Response
```json
{
    "total": 700.00,
    "breakdown": [
        {
            "product": "Basic Support",
            "quantity": 5,
            "unit_price": 100.00,
            "total": 500.00
        },
        {
            "product": "Premium Support",
            "quantity": 1,
            "unit_price": 200.00,
            "total": 200.00
        }
    ]
}
```

### 2. Submit Quote
Generates a draft quote and prospect record.

*   **Endpoint:** `POST /submit`
*   **Auth:** None (Public)

#### Request Body
```json
{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "company_name": "Acme Corp",
    "phone": "555-0123",
    "items": [
        {
            "product_id": 1,
            "quantity": 5
        }
    ]
}
```

#### Response
```json
{
    "message": "Quote generated successfully!",
    "quote_token": "abc123xyz...",
    "redirect_url": "https://your-msp.com/billing/quote-builder/view/abc123xyz..."
}
```

### 3. View Quote
Retrieves details of a submitted quote.

*   **Endpoint:** `GET /view/{token}`
*   **Auth:** None (Public - Token based)

#### Response
Returns HTML view of the quote.
