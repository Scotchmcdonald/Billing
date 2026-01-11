# Tabbed Views Implementation Guide

**Status: COMPLETED ✅**  
**Implementation Date: December 28, 2025**

This document provides comprehensive guidance on implementing tabbed interfaces throughout the billing module, following the patterns established in the UX Style Guide.

---

## 🎯 Philosophy

Tabbed interfaces consolidate related views under a single page, reducing:
- **Navigation Overhead**: No need to click back and jump between pages
- **Cognitive Load**: Related information stays together contextually
- **URL Complexity**: Single URL with hash fragments for state
- **Menu Clutter**: Fewer top-level menu items

---

## 🏗️ Architecture

### Components Created

1. **`<x-billing::tabs>`** - Tab container with navigation
2. **`<x-billing::tab-panel>`** - Individual tab content with transitions

### Key Features

✅ **URL Persistence**: Tab state saved in URL hash (#tab-name)  
✅ **Session Storage**: Last active tab persists across page reloads  
✅ **Keyboard Navigation**: Arrow keys to switch tabs  
✅ **Accessibility**: ARIA attributes, tab roles, focus management  
✅ **Animations**: Smooth fade/slide transitions between tabs  
✅ **Lazy Loading**: Option to defer content rendering until tab activation  
✅ **Count Badges**: Display counts on tab labels (e.g., "Disputes (3)")  
✅ **Icons**: Font Awesome icons on each tab  

---

## 📚 Component API

### `<x-billing::tabs>`

**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `active` | string | (required) | ID of the initially active tab |
| `tabs` | array | (required) | Array of tab configurations |

**Tab Configuration:**

```php
[
    'id' => 'details',              // Unique tab identifier
    'label' => 'Invoice Details',   // Display label
    'icon' => 'file-invoice-dollar', // Font Awesome icon (without 'fa-' prefix)
    'count' => 5,                    // Optional: Show badge with count
    'badge' => [                     // Optional: Show colored badge
        'text' => 'New',
        'color' => 'primary'         // success, warning, danger, info
    ]
]
```

**Example:**

```blade
<x-billing::tabs :active="request()->query('tab', 'details')" :tabs="[
    ['id' => 'details', 'label' => 'Details', 'icon' => 'info-circle'],
    ['id' => 'timeline', 'label' => 'Activity', 'icon' => 'history', 'count' => 12],
    ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'flag', 'badge' => ['text' => 'New', 'color' => 'danger']]
]">
    <!-- Tab panels go here -->
</x-billing::tabs>
```

---

### `<x-billing::tab-panel>`

**Props:**

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `id` | string | (required) | Tab identifier (matches tab config) |
| `lazy` | boolean | false | Defer rendering until tab is activated |

**Example:**

```blade
<x-billing::tab-panel id="details">
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <!-- Content here -->
    </div>
</x-billing::tab-panel>

<!-- Lazy-loaded tab (good for heavy content) -->
<x-billing::tab-panel id="reports" lazy="true">
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <!-- This content won't render until user clicks "Reports" tab -->
    </div>
</x-billing::tab-panel>
```

---

## 🎨 Implementation Examples

### 1. Invoice Detail Page

**Use Case**: Consolidate invoice information, line items, timeline, disputes, and payments

```blade
<x-billing::tabs :active="'details'" :tabs="[
    ['id' => 'details', 'label' => 'Invoice Details', 'icon' => 'file-invoice-dollar'],
    ['id' => 'line-items', 'label' => 'Line Items', 'icon' => 'list'],
    ['id' => 'timeline', 'label' => 'Activity Timeline', 'icon' => 'history', 'count' => $timelineCount],
    ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'flag', 'count' => $disputeCount],
    ['id' => 'payments', 'label' => 'Payments', 'icon' => 'credit-card'],
]">
    <x-billing::tab-panel id="details">
        <!-- Invoice header, amounts, etc -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="line-items">
        <!-- Line items table -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="timeline" lazy="true">
        <x-billing::invoice-timeline :invoice="$invoice" />
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="disputes" lazy="true">
        <!-- Disputes list -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="payments" lazy="true">
        <!-- Payments table -->
    </x-billing::tab-panel>
</x-billing::tabs>
```

**Benefits:**
- All invoice information accessible without page navigation
- Timeline/disputes lazy-loaded to improve initial page load
- Count badges show active disputes at a glance

---

### 2. Reports Hub

**Use Case**: Consolidate financial reports and dashboards

```blade
<x-billing::tabs :active="'executive'" :tabs="[
    ['id' => 'executive', 'label' => 'Executive Dashboard', 'icon' => 'chart-line'],
    ['id' => 'reports', 'label' => 'Detailed Reports', 'icon' => 'file-alt'],
    ['id' => 'ar-aging', 'label' => 'AR Aging', 'icon' => 'clock', 'count' => $overdueCount],
    ['id' => 'profitability', 'label' => 'Profitability', 'icon' => 'dollar-sign'],
]">
    <x-billing::tab-panel id="executive">
        @include('billing::finance._partials.executive-dashboard-content')
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="reports" lazy="true">
        @include('billing::finance._partials.reports-content')
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="ar-aging" lazy="true">
        @include('billing::finance._partials.ar-aging-content')
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="profitability" lazy="true">
        @include('billing::finance._partials.profitability-content')
    </x-billing::tab-panel>
</x-billing::tabs>
```

**Benefits:**
- Single "Reports" menu item instead of 4 separate items
- Executive dashboard loads immediately, others on-demand
- Easy comparison between different report views

---

### 3. Settings Hub

**Use Case**: Consolidate all billing configuration screens

```blade
<x-billing::tabs :active="'general'" :tabs="[
    ['id' => 'general', 'label' => 'General Settings', 'icon' => 'cog'],
    ['id' => 'integrations', 'label' => 'Integrations', 'icon' => 'plug'],
    ['id' => 'templates', 'label' => 'Invoice Templates', 'icon' => 'file-invoice'],
    ['id' => 'numbering', 'label' => 'Numbering', 'icon' => 'hashtag'],
    ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
]">
    <x-billing::tab-panel id="general">
        <!-- General billing settings -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="integrations">
        <!-- Helcim, QuickBooks, etc -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="templates" lazy="true">
        <!-- Template customizer -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="numbering" lazy="true">
        <!-- Invoice numbering config -->
    </x-billing::tab-panel>
    
    <x-billing::tab-panel id="notifications" lazy="true">
        <!-- Email notification settings -->
    </x-billing::tab-panel>
</x-billing::tabs>
```

**Benefits:**
- All settings in one place
- No need to remember which settings page has what
- Faster navigation between related settings

---

## 🎯 When to Use Tabs

### ✅ **Good Use Cases**

1. **Entity Detail Pages**
   - Invoice: Details, Line Items, Timeline, Disputes, Payments
   - Client: Info, Contacts, Invoices, Payments, Activity
   - Project: Overview, Time Entries, Expenses, Billing

2. **Multi-View Reports**
   - Dashboard: Executive, Detailed, AR Aging, Profitability
   - Analytics: Revenue, Expenses, Profit, Forecasts

3. **Configuration Screens**
   - Settings: General, Integrations, Templates, Notifications
   - Preferences: Display, Behavior, Notifications, Security

4. **Workflow Stages**
   - Pre-flight: All Invoices, Clean, Review Needed, Approved
   - Time Review: Pending, Approved, Rejected, Billed

### ❌ **Avoid Tabs For**

1. **Linear Workflows** (use wizards instead)
   - Onboarding steps that must be completed in order
   - Multi-step forms with validation gates

2. **Unrelated Content**
   - Random collection of features
   - Content that doesn't share context

3. **Single Items**
   - Don't create tabs if there's only 1-2 sections

4. **Mobile-First Views**
   - Consider accordion/expansion panels for mobile
   - Tabs work but require careful responsive design

---

## 🚀 Advanced Features

### 1. Deep Linking

Tabs automatically update the URL hash, making them bookmarkable:

```
https://app.example.com/billing/invoices/123?tab=disputes#disputes
```

Users can share direct links to specific tabs.

### 2. Session Persistence

Last active tab is saved to sessionStorage:

```javascript
activeTab: $persist('details').using(sessionStorage).as('billing-invoices-123-tab')
```

If user refreshes page, they return to the last tab they were viewing.

### 3. Keyboard Navigation

- **Arrow Right**: Next tab
- **Arrow Left**: Previous tab
- **Tab Key**: Move through interactive elements
- **Enter/Space**: Activate focused tab button

### 4. Count Badges

Display dynamic counts on tabs:

```php
['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'flag', 'count' => $disputeCount]
```

Automatically styled with matching colors when tab is active.

### 5. Lazy Loading

Defer rendering of heavy content until tab is activated:

```blade
<x-billing::tab-panel id="reports" lazy="true">
    <!-- This won't render until user clicks "Reports" tab -->
    @include('billing::finance._partials.complex-report')
</x-billing::tab-panel>
```

Improves initial page load performance.

---

## 📐 Design Guidelines

### Visual Hierarchy

1. **Page Header** (always visible)
   - Entity name or page title
   - Primary actions (Print, Export, etc)
   - Back navigation

2. **Tab Bar** (sticky on scroll recommended)
   - Horizontal navigation
   - Active tab underlined in primary color
   - Hover states on inactive tabs

3. **Tab Content** (full width)
   - Smooth fade/slide transitions
   - Consistent padding and spacing
   - Cards for grouped content

### Color System

- **Active Tab**: Primary-500 border, Primary-600 text
- **Inactive Tab**: Transparent border, Gray-500 text
- **Hover State**: Gray-700 text, Gray-300 border
- **Count Badges**: Match active/inactive state colors

### Spacing

- **Tab Bar**: `-mb-px` to overlap with border
- **Tab Buttons**: `py-4 px-1` with `space-x-8` between
- **Tab Content**: `mt-6` below tab bar
- **Cards**: `p-6` padding inside cards

### Typography

- **Tab Labels**: `text-sm font-medium`
- **Count Badges**: `text-xs font-medium`
- **Section Headings**: `text-lg font-medium`

---

## 🔧 Customization

### Custom Transitions

Override transition timing:

```blade
<x-billing::tab-panel id="custom">
    <div x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <!-- Custom animation -->
    </div>
</x-billing::tab-panel>
```

### Conditional Tabs

Show/hide tabs based on conditions:

```blade
@php
    $tabs = [
        ['id' => 'details', 'label' => 'Details', 'icon' => 'info-circle'],
        ['id' => 'timeline', 'label' => 'Timeline', 'icon' => 'history'],
    ];
    
    if (auth()->user()->can('view-disputes')) {
        $tabs[] = ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'flag'];
    }
@endphp

<x-billing::tabs :active="'details'" :tabs="$tabs">
    <!-- Tab panels -->
</x-billing::tabs>
```

### Custom Styling

Add classes to tab panels:

```blade
<x-billing::tab-panel id="custom" class="min-h-screen bg-gray-50">
    <!-- Full-height tab with custom background -->
</x-billing::tab-panel>
```

---

## 📊 Performance Optimization

### Lazy Loading Strategy

**Heavy Content**: Always lazy-load
- Complex reports with charts
- Large data tables
- Media galleries
- External API calls

**Light Content**: Load immediately
- Basic forms
- Summary cards
- Static content

**Example:**

```blade
<!-- Load immediately (light) -->
<x-billing::tab-panel id="summary">
    <div class="grid grid-cols-3 gap-4">
        <x-stat-card title="Revenue" value="$45,200" />
        <x-stat-card title="Expenses" value="$12,800" />
        <x-stat-card title="Profit" value="$32,400" />
    </div>
</x-billing::tab-panel>

<!-- Lazy load (heavy) -->
<x-billing::tab-panel id="detailed-report" lazy="true">
    @include('billing::reports.complex-report-with-charts')
</x-billing::tab-panel>
```

### Alpine.js Best Practices

1. **Use `x-cloak`** to prevent flash of unstyled content
2. **Debounce** tab switching if triggering API calls
3. **Memoize** computed values in x-data
4. **Batch** DOM updates

---

## 🧪 Testing Checklist

### Functionality
- [ ] Tabs switch correctly when clicked
- [ ] URL hash updates when tab changes
- [ ] Page loads with correct tab from URL hash
- [ ] Session storage persists last active tab
- [ ] Lazy-loaded tabs render on first activation
- [ ] Count badges display correct numbers

### Keyboard Navigation
- [ ] Tab key focuses tab buttons
- [ ] Arrow keys navigate between tabs
- [ ] Enter/Space activates focused tab
- [ ] Focus visible indicator shows current tab

### Accessibility
- [ ] Screen reader announces tab labels
- [ ] ARIA attributes present (role, aria-selected)
- [ ] Tab panels have role="tabpanel"
- [ ] Focus management correct

### Visual
- [ ] Active tab shows primary color underline
- [ ] Hover states work on inactive tabs
- [ ] Transitions smooth (no jank)
- [ ] Count badges styled correctly
- [ ] Icons display properly

### Responsive
- [ ] Tabs stack/scroll on mobile
- [ ] Touch targets minimum 44px
- [ ] Tab bar doesn't overflow
- [ ] Content areas adapt to viewport

---

## 📦 Files Created

### Components
- `/Modules/Billing/Resources/views/components/tabs.blade.php`
- `/Modules/Billing/Resources/views/components/tab-panel.blade.php`

### Implementation Examples
- `/Modules/Billing/Resources/views/finance/reports-hub.blade.php`
- `/Modules/Billing/Resources/views/finance/settings-hub.blade.php`
- `/Modules/Billing/Resources/views/finance/invoices/show-tabbed.blade.php`

### Documentation
- `/var/www/html/UX_STYLE_GUIDE.md` (Section D added)
- `/Modules/Billing/Docs/TABBED_VIEWS_IMPLEMENTATION.md` (this file)

---

## 🎓 Learning Resources

### Alpine.js Patterns
- [Alpine.js Transitions](https://alpinejs.dev/directives/transition)
- [Alpine.js Persist Plugin](https://alpinejs.dev/plugins/persist)
- [Alpine.js Best Practices](https://alpinejs.dev/advanced/reactivity)

### Accessibility
- [ARIA Tabs Pattern](https://www.w3.org/WAI/ARIA/apg/patterns/tabs/)
- [Keyboard Navigation](https://www.w3.org/WAI/ARIA/apg/practices/keyboard-interface/)

### UX Research
- [Tab Design Best Practices](https://www.nngroup.com/articles/tabs-used-right/)
- [Information Architecture](https://www.nngroup.com/articles/ia-vs-navigation/)

---

## 🔮 Future Enhancements

### Phase 2
1. **Vertical Tabs**: For sidebar navigation
2. **Nested Tabs**: Sub-tabs within tabs (use sparingly)
3. **Closeable Tabs**: Dynamic tabs that can be closed
4. **Sortable Tabs**: Drag-and-drop reordering
5. **Tab Preloading**: Preload next likely tab

### Phase 3
1. **Smart Tab Suggestions**: AI-powered "You might also want to see..."
2. **Tab History**: Recently viewed tabs
3. **Custom Tab Layouts**: User-configurable tab order
4. **Tab Shortcuts**: Quick keyboard shortcuts (Cmd+1, Cmd+2)

---

**Status:** Ready for production use ✅  
**Last Updated:** December 28, 2025
