# Webhook Integration Guide

## Overview
The Billing Module supports incoming webhooks to automate usage-based billing. This is primarily used to sync device counts from Remote Monitoring & Management (RMM) tools.

## Authentication
All webhook requests must include a signature header to verify authenticity.
*   **Header:** `X-Hub-Signature`
*   **Algorithm:** HMAC-SHA256
*   **Secret:** Your configured `RMM_WEBHOOK_SECRET`

## Endpoints

### 1. Update Device Count
Updates the quantity of a specific service (product) for a client based on active device counts.

*   **URL:** `POST /billing/webhooks/rmm/device-count`
*   **Content-Type:** `application/json`

#### Payload
```json
{
    "client_ref_id": "CUST-1001",
    "product_sku": "MSP-WKS-01",
    "quantity": 150,
    "timestamp": "2025-12-27T10:00:00Z"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `client_ref_id` | String | The external ID of the client (mapped in CRM settings). |
| `product_sku` | String | The SKU of the service to update (e.g., Workstation Agent). |
| `quantity` | Integer | The current active count. |
| `timestamp` | String | ISO 8601 timestamp of the event. |

#### Response
*   **200 OK:** Count updated successfully.
*   **404 Not Found:** Client or Product not found.
*   **401 Unauthorized:** Invalid signature.

## Example Implementation (PHP)

```php
$secret = 'your-webhook-secret';
$payload = json_encode([
    'client_ref_id' => 'CUST-1001',
    'product_sku' => 'MSP-WKS-01',
    'quantity' => 150,
    'timestamp' => date('c')
]);

$signature = hash_hmac('sha256', $payload, $secret);

$ch = curl_init('https://your-msp.com/billing/webhooks/rmm/device-count');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Hub-Signature: ' . $signature
]);
curl_exec($ch);
curl_close($ch);
```
