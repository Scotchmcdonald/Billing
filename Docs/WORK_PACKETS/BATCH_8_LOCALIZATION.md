# Batch 8: Localization & Accessibility

**Execution Order:** Parallel (After Batch 3)
**Parallelization:** L10n and A11y can be developed in parallel
**Estimated Effort:** 3-4 days
**Priority:** P3 (Enhancement)

---

## Agent Prompt

```
You are a Senior Frontend Engineer specializing in internationalization (i18n), localization (l10n), and web accessibility (a11y).

Your task is to implement multi-language support and accessibility compliance for the FinOps billing module.

## Primary Objectives
1. Extract all strings to language files
2. Support multiple currencies and date formats
3. Achieve WCAG 2.1 AA compliance
4. Implement RTL support for applicable languages

## Technical Standards
- Language files in `Modules/Billing/Resources/lang/`
- Use Laravel's `__()` helper for all strings
- Currency formatting via `NumberFormatter`
- ARIA labels on all interactive elements
- Keyboard navigation support

## Accessibility Requirements
- All interactive elements keyboard accessible
- Color contrast ratio >= 4.5:1
- Screen reader compatible
- Focus indicators visible
- Error messages announced

## Files to Reference
- Existing lang files: `resources/lang/`
- Blade templates: `Modules/Billing/Resources/views/`
- Alpine components: Check for accessibility gaps

## Validation Criteria
- All strings extracted to lang files
- Currency formats correct per locale
- axe-core audit passes
- Keyboard-only navigation works
- Screen reader testing completed
```

---

## Context & Technical Details

### Laravel Localization
```php
// Usage in Blade
{{ __('billing::invoices.title') }}

// Usage in PHP
trans('billing::invoices.status.paid')

// With parameters
__('billing::invoices.due_message', ['date' => $dueDate])
```

### Language File Structure
```
Modules/Billing/Resources/lang/
├── en/
│   ├── invoices.php
│   ├── payments.php
│   ├── quotes.php
│   └── common.php
├── es/
│   ├── invoices.php
│   └── ...
└── fr/
    └── ...
```

### Currency Handling
```php
// Helper for currency formatting
function format_currency(float $amount, string $currency = 'USD', ?string $locale = null): string
{
    $locale = $locale ?? app()->getLocale();
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, $currency);
}
```

---

## Task Checklist

### 8.1 String Extraction (English Base)

#### Invoice Strings
- [ ] Create `Modules/Billing/Resources/lang/en/invoices.php`
  ```php
  return [
      'title' => 'Invoices',
      'create' => 'Create Invoice',
      'edit' => 'Edit Invoice',
      'send' => 'Send Invoice',
      'status' => [
          'draft' => 'Draft',
          'sent' => 'Sent',
          'paid' => 'Paid',
          'overdue' => 'Overdue',
          'void' => 'Void',
      ],
      'fields' => [
          'invoice_number' => 'Invoice Number',
          'company' => 'Client',
          'due_date' => 'Due Date',
          'total' => 'Total',
          'balance_due' => 'Balance Due',
      ],
      'messages' => [
          'created' => 'Invoice created successfully.',
          'sent' => 'Invoice sent to :email.',
          'paid' => 'Invoice marked as paid.',
          'overdue' => 'This invoice is :days days overdue.',
      ],
      'actions' => [
          'download_pdf' => 'Download PDF',
          'send_reminder' => 'Send Reminder',
          'record_payment' => 'Record Payment',
          'void_invoice' => 'Void Invoice',
      ],
  ];
  ```

#### Payment Strings
- [ ] Create `Modules/Billing/Resources/lang/en/payments.php`

#### Quote Strings
- [ ] Create `Modules/Billing/Resources/lang/en/quotes.php`

#### Common Strings
- [ ] Create `Modules/Billing/Resources/lang/en/common.php`
  ```php
  return [
      'save' => 'Save',
      'cancel' => 'Cancel',
      'delete' => 'Delete',
      'edit' => 'Edit',
      'view' => 'View',
      'search' => 'Search...',
      'filter' => 'Filter',
      'export' => 'Export',
      'loading' => 'Loading...',
      'confirm_delete' => 'Are you sure you want to delete this?',
      'no_results' => 'No results found.',
  ];
  ```

### 8.2 Spanish Translation

- [ ] Create `Modules/Billing/Resources/lang/es/invoices.php`
- [ ] Create `Modules/Billing/Resources/lang/es/payments.php`
- [ ] Create `Modules/Billing/Resources/lang/es/quotes.php`
- [ ] Create `Modules/Billing/Resources/lang/es/common.php`

### 8.3 French Translation

- [ ] Create `Modules/Billing/Resources/lang/fr/invoices.php`
- [ ] Create `Modules/Billing/Resources/lang/fr/payments.php`
- [ ] Create `Modules/Billing/Resources/lang/fr/quotes.php`
- [ ] Create `Modules/Billing/Resources/lang/fr/common.php`

### 8.4 Update Blade Templates

#### Systematic Replacement
- [ ] Update `invoices/index.blade.php` - Replace hardcoded strings
- [ ] Update `invoices/show.blade.php` - Replace hardcoded strings
- [ ] Update `invoices/create.blade.php` - Replace hardcoded strings
- [ ] Update `payments/*.blade.php` - Replace hardcoded strings
- [ ] Update `quotes/*.blade.php` - Replace hardcoded strings
- [ ] Update `portal/*.blade.php` - Replace hardcoded strings

#### Pattern to Follow
```blade
{{-- Before --}}
<h1>Invoices</h1>
<button>Create Invoice</button>

{{-- After --}}
<h1>{{ __('billing::invoices.title') }}</h1>
<button>{{ __('billing::invoices.create') }}</button>
```

### 8.5 Currency & Number Formatting

#### Currency Helper
- [ ] Create `Modules/Billing/Helpers/currency.php`
  ```php
  function format_currency(float $amount, ?string $currency = null, ?string $locale = null): string
  {
      $currency = $currency ?? config('billing.default_currency', 'USD');
      $locale = $locale ?? app()->getLocale();
      
      $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
      return $formatter->formatCurrency($amount, $currency);
  }
  
  function format_number(float $number, int $decimals = 2, ?string $locale = null): string
  {
      $locale = $locale ?? app()->getLocale();
      $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
      $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimals);
      return $formatter->format($number);
  }
  ```

#### Date Formatting
- [ ] Create date helper respecting locale
  ```php
  function format_date(Carbon $date, string $format = 'medium', ?string $locale = null): string
  {
      $locale = $locale ?? app()->getLocale();
      
      $formats = [
          'short' => IntlDateFormatter::SHORT,
          'medium' => IntlDateFormatter::MEDIUM,
          'long' => IntlDateFormatter::LONG,
      ];
      
      $formatter = new IntlDateFormatter(
          $locale,
          $formats[$format],
          IntlDateFormatter::NONE
      );
      
      return $formatter->format($date);
  }
  ```

#### Update Templates
- [ ] Replace `$invoice->total` with `format_currency($invoice->total)`
- [ ] Replace `$invoice->due_date->format('M d, Y')` with `format_date($invoice->due_date)`

### 8.6 Multi-Currency Support

#### Company Currency Setting
- [ ] Add `currency` column to companies table (if not exists)
- [ ] Add currency selector to company settings
- [ ] Default to tenant/system currency

#### Invoice Currency
- [ ] Store currency on invoice
- [ ] Display in correct format
- [ ] Convert for reports (optional)

### 8.7 WCAG Compliance - Forms

#### Form Labels
- [ ] Ensure all inputs have `<label>` with `for` attribute
- [ ] Add `aria-describedby` for help text
- [ ] Add `aria-invalid` for error states

#### Error Handling
- [ ] Add `role="alert"` to error messages
- [ ] Use `aria-live="polite"` for dynamic errors
- [ ] Focus first error field on submit

#### Example Pattern
```blade
<div class="form-group">
    <label for="due-date">{{ __('billing::invoices.fields.due_date') }}</label>
    <input 
        type="date" 
        id="due-date" 
        name="due_date"
        aria-describedby="due-date-help"
        @error('due_date') aria-invalid="true" aria-describedby="due-date-error" @enderror
    >
    <span id="due-date-help" class="help-text">
        {{ __('billing::invoices.help.due_date') }}
    </span>
    @error('due_date')
        <span id="due-date-error" role="alert" class="error-text">{{ $message }}</span>
    @enderror
</div>
```

### 8.8 WCAG Compliance - Tables

#### Data Tables
- [ ] Add `<caption>` to tables
- [ ] Use `<th scope="col">` for headers
- [ ] Add `aria-sort` for sortable columns
- [ ] Add `aria-label` to action buttons

#### Example Pattern
```blade
<table aria-label="{{ __('billing::invoices.title') }}">
    <caption class="sr-only">{{ __('billing::invoices.table_caption') }}</caption>
    <thead>
        <tr>
            <th scope="col" aria-sort="ascending">
                {{ __('billing::invoices.fields.invoice_number') }}
            </th>
            ...
        </tr>
    </thead>
</table>
```

### 8.9 WCAG Compliance - Modals

#### Modal Accessibility
- [ ] Add `role="dialog"` and `aria-modal="true"`
- [ ] Add `aria-labelledby` pointing to title
- [ ] Trap focus within modal
- [ ] Close on Escape key
- [ ] Return focus to trigger on close

#### Example Pattern
```blade
<div 
    x-show="open" 
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="modal-title"
    @keydown.escape.window="open = false"
    x-trap.noscroll="open"
>
    <h2 id="modal-title">{{ __('billing::payments.record') }}</h2>
    ...
</div>
```

### 8.10 Keyboard Navigation

#### Focus Management
- [ ] Visible focus indicators (2px solid outline)
- [ ] Logical tab order
- [ ] Skip links for main content

#### Custom Components
- [ ] Dropdown menus: Arrow keys, Enter, Escape
- [ ] Date pickers: Arrow keys for date selection
- [ ] Autocomplete: Arrow keys, Enter to select

#### CSS Focus Styles
```css
*:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(var(--color-primary-rgb), 0.3);
}
```

### 8.11 Color Contrast

#### Audit Current Colors
- [ ] Run contrast checker on all text/background combinations
- [ ] Ensure 4.5:1 ratio for normal text
- [ ] Ensure 3:1 ratio for large text and UI components

#### Status Badge Colors
- [ ] Update badge colors for contrast compliance
  ```css
  .badge-success { background: #047857; color: white; } /* 4.5:1 */
  .badge-warning { background: #d97706; color: black; } /* 4.5:1 */
  .badge-danger { background: #dc2626; color: white; }  /* 4.5:1 */
  ```

### 8.12 Screen Reader Testing

#### Test Scenarios
- [ ] Navigate invoice list with screen reader
- [ ] Complete payment flow with screen reader
- [ ] Fill out invoice form with screen reader
- [ ] Verify dynamic content announcements

#### Screen Reader Specific Additions
- [ ] Add `.sr-only` class for visual-only elements
- [ ] Add `aria-live` regions for updates
- [ ] Add `aria-busy` for loading states

### 8.13 RTL Support (Future)

#### CSS Preparation
- [ ] Use logical properties (start/end vs left/right)
  ```css
  /* Instead of: margin-left: 1rem */
  margin-inline-start: 1rem;
  
  /* Instead of: padding-right: 1rem */
  padding-inline-end: 1rem;
  ```

#### Document Direction
- [ ] Add `dir="rtl"` support to layout
- [ ] Test with Arabic placeholder text

---

## Testing

### Accessibility Testing
```bash
# Install axe-core CLI
npm install -g @axe-core/cli

# Run accessibility audit
axe http://localhost/billing/invoices --tags wcag2a,wcag2aa

# Run in browser (Chrome DevTools)
# Lighthouse > Accessibility audit
```

### Localization Testing
```bash
# Test Spanish locale
php artisan tinker --execute="
    app()->setLocale('es');
    echo __('billing::invoices.title');
"

# Verify all strings have translations
php artisan lang:missing --lang=es
```

---

## Completion Verification

```bash
# List all translation keys
php artisan lang:list billing

# Check for missing translations
find Modules/Billing/Resources/views -name "*.blade.php" -exec grep -l "hardcoded" {} \;

# Accessibility audit
axe http://localhost/billing/invoices --exit

# Contrast check
npx @contrast-ratio/cli "#047857" "#ffffff" # Should be >= 4.5
```

---

## Downstream Dependencies
- None (enhancement batch)
- Applies to UI from **Batch 3A, 3B, 3C**
