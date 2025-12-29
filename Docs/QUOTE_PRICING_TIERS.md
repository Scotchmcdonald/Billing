# Quote Pricing Tier Feature

## Overview
The quote creation system now supports automatic price pre-population based on pricing tiers (Standard, Non-Profit, Consumer) with variance tracking and approval workflows.

## Features

### 1. Pricing Tier Selection
- Users can select from three pricing tiers when creating a quote:
  - **Standard**: Regular pricing for standard customers
  - **Non-Profit**: Discounted pricing for non-profit organizations
  - **Consumer**: Consumer-specific pricing

### 2. Automatic Price Pre-Population
- When a product is selected, the price is automatically populated based on the selected pricing tier
- Prices are pulled from the `product_tier_prices` table
- If a company is selected, the pricing tier is automatically set based on the company's configured tier

### 3. Price Variance Tracking
- The system tracks the "standard" price for each line item
- When a user overrides the price, the variance is calculated and displayed:
  - **Variance Amount**: Dollar difference from standard price (e.g., +$5.00)
  - **Variance Percent**: Percentage difference from standard price (e.g., +10.5%)

### 4. Visual Variance Indicators
- **Green**: Price below standard (discount)
- **Orange**: Price above standard (premium) but within threshold
- **Red (Bold)**: Price variance exceeds approval threshold

### 5. Approval Threshold
- Configurable threshold percentage (default: 15%)
- When any line item's variance exceeds the threshold, the quote is flagged for approval
- Visual warning displayed when approval is required
- Quote cannot be sent until approved

### 6. Configuration

The approval threshold can be configured in:
- **Config file**: `/config/quotes.php`
- **Environment variable**: `QUOTE_APPROVAL_THRESHOLD` (e.g., 15.00)
- **Per-quote override**: Can be adjusted when creating each quote

## Database Schema

### Quotes Table
- `pricing_tier`: Selected pricing tier (standard, non_profit, consumer)
- `requires_approval`: Boolean flag indicating if approval is needed
- `approval_threshold_percent`: The threshold percentage used for this quote

### Quote Line Items Table
- `standard_price`: The standard tier price for comparison
- `variance_amount`: Dollar difference from standard price
- `variance_percent`: Percentage difference from standard price

## Usage Example

1. Navigate to `/billing/finance/quotes/create`
2. Select a company (pricing tier auto-populates based on company settings)
3. Or manually select a pricing tier for new prospects
4. Add products - prices automatically populate based on selected tier
5. Override prices if needed - variance is calculated and displayed
6. If variance exceeds threshold, quote requires approval before sending

## Configuration

Edit `/config/quotes.php`:

```php
'default_approval_threshold' => 15.00, // 15% variance threshold
'pricing_tiers' => [
    'standard' => [...],
    'non_profit' => [...],
    'consumer' => [...],
],
```

Or set in `.env`:

```
QUOTE_APPROVAL_THRESHOLD=15.00
```

## Future Enhancements

Potential future improvements:
- Approval workflow with notifications
- Role-based approval authority
- Price override reason tracking
- Historical price variance analytics
- Bulk approval interface
