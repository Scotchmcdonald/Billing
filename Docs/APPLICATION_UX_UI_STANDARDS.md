# Application UX/UI Standards & Implementation Guide

## 1. Core Philosophy: "The Pilot's Cockpit"
Our application is a mission-critical tool for our users. Whether they are managing emails, configuring modules, or analyzing data, they are the "Pilot" and the interface is their "Cockpit".

The interface must be:
*   **Clinical & Precise**: No ambiguity. Every pixel serves a purpose.
*   **Resilient**: Errors are expected and handled with clear, actionable advice.
*   **Dense yet Scannable**: High information density is required, but hierarchy must prevent cognitive overload.
*   **State-Aware**: The UI must always reflect the *current reality* (loading, syncing, verifying) without requiring a page refresh.

---

## 2. UX Patterns & Behaviors

### A. The Guided Journey (Wizards)
**Use Case**: Complex, multi-step processes (e.g., Setup, Import, Creation).
*   **Concept**: Never dump a complex task on a single long form. Break it into linear, logical steps.
*   **Behavior**:
    *   **State Preservation**: Users can move back and forth without losing data.
    *   **Validation at the Gate**: Users cannot proceed to the next step until the current one is valid.
    *   **Visual Progress**: A clear stepper component always indicates "Where am I?" and "How much is left?".
*   **Quality Standard**: Transitions between steps should be animated (slide/fade) to maintain spatial context.

### B. The Control Tower (Dashboards)
**Use Case**: Landing pages, Module indexes, Overview screens.
*   **Concept**: Provide a 30,000ft view of operations with immediate access to critical controls.
*   **Hierarchy**:
    1.  **Emergency/Critical Console**: Dangerous or urgent actions are separated and visually distinct (Red/Amber zones).
    2.  **Key Metrics**: Big numbers with clear labels.
    3.  **Active Operations**: A detailed table/list of what is happening *right now*.
*   **Interaction**:
    *   **Real-Time Updates**: Progress bars and status badges should reflect live data.
    *   **Circuit Breakers**: Destructive actions require explicit confirmation (e.g., typing a hostname or "DELETE") to prevent accidental clicks.

### C. Resilient Design (Error Handling)
**Use Case**: API failures, Validation errors, System issues.
*   **Concept**: Errors are not dead ends; they are forks in the road.
*   **Pattern**: The `Troubleshooting Card`.
    *   **Visuals**: Distinct from standard content (e.g., distinct border color, distinct background).
    *   **Content Structure**:
        1.  **What happened?** (The Error).
        2.  **Why?** (The Context).
        3.  **What now?** (Actionable advice).
*   **Quality Standard**: Never show a raw stack trace to a user. Always wrap it in a "Troubleshooting" context.

---

## 3. Visual System (Abstracted)

### Semantic Color Usage
**Rule**: Do not use hardcoded Tailwind colors (e.g., `bg-indigo-600`). Use semantic classes that map to the active theme.

*   **Primary Action** (`primary`): The "Brand" color. Used for main buttons, active states, and focus rings.
    *   Class: `bg-primary-600`, `text-primary-600`, `ring-primary-500`
*   **Success** (`success`): Completed operations, verified states.
    *   Class: `text-success-700`, `bg-success-50`, `border-success-200`
*   **Warning** (`warning`): Pending actions, non-critical alerts.
    *   Class: `text-warning-700`, `bg-warning-50`, `border-warning-200`
*   **Danger** (`danger`): Failed operations, destructive actions, critical errors.
    *   Class: `text-danger-700`, `bg-danger-50`, `border-danger-200`
*   **Info/Neutral** (`info` / `gray`): Structure, meta-data, neutral updates.

### Spatial System
*   **Density**:
    *   **Dashboards**: High density. Small text (`text-xs`/`text-sm`), compact tables.
    *   **Wizards/Forms**: Low density. Focus on one input at a time. Generous padding (`py-3` on inputs).
*   **Elevation**:
    *   **Base**: Page background (Subtle/Flat).
    *   **Layer 1**: Content Cards (`shadow-sm`, `bg-white`/`bg-card`).
    *   **Layer 2**: Modals/Dropdowns (`shadow-xl`).

### Motion & Feedback
*   **Transitions**: Elements entering the DOM (wizard steps, alerts) must fade/slide in.
*   **Loading**: Buttons enter a "Processing" state (spinner, disabled) immediately upon click.
*   **Pulse**: Use a "Pulse" animation for active waiting states (e.g., "Verifying...", "Connecting...").

---

## 4. Implementation Standards

### Component Architecture
*   **Encapsulation**: Complex UI elements (Status Badges, Progress Bars, Troubleshooting Cards) must be extracted into Blade Components (`x-component-name`).
*   **Flexibility**: Components must accept semantic props (`status="error"`) rather than style classes.
*   **Responsiveness**: All layouts must adapt to mobile. Tables should have a mobile-friendly list view or horizontal scroll.

### Accessibility (A11y)
*   **Semantics**: Use `<nav>`, `<main>`, `<section>` tags.
*   **Labels**: All inputs must have visible labels. Icon-only buttons must have `aria-label` or `sr-only` text.
*   **Focus**: Interactive elements must have visible focus rings (`focus:ring`).

---

## 5. Integrated LLM Prompts

Use these prompts to ensure AI-generated code meets our standards.

### Prompt 1: The UX Auditor
> "Act as a Senior UX Architect. Review the following Blade view code against the 'Application UX/UI Standards'.
> 1. **Resilience Check**: Are errors handled gracefully? Is there actionable advice?
> 2. **State Awareness**: Does the user know if the system is working, waiting, or finished?
> 3. **Hierarchy**: Is the most important information (Big Numbers/Status) visually dominant?
> 4. **Safety**: Are destructive actions protected (confirmations, distinct styling)?
> 5. **Theming**: Are hardcoded colors used instead of semantic theme classes?
> Output a critique focusing on *interaction quality* and *user confidence*."

### Prompt 2: The Semantic Refactor
> "Refactor this Blade view to be **Theme-Agnostic** and **Standard-Compliant**.
> - Remove hardcoded colors (e.g., `bg-indigo-600`, `text-red-500`).
> - Replace them with semantic theme classes (e.g., `bg-primary-600`, `text-danger-600`) or Blade components (`x-status-badge`).
> - Ensure the layout follows the 'Card' pattern: `bg-white shadow-sm rounded-lg`.
> - Ensure inputs use the standard 'Comfort' padding (`py-3`).
> - Ensure interactive elements have proper focus states."

### Prompt 3: The Feature Architect
> "Design a new feature: `[Feature Name]`.
> - **User Story**: As a [User Role], I want to [Action] so that [Benefit].
> - **Interaction Flow**: Describe the step-by-step user journey (Entry -> Action -> Feedback).
> - **Component Strategy**: Which existing components can be reused? What new components are needed?
> - **Draft Code**: Provide a high-level Blade template using the 'Wizard' or 'Dashboard' pattern defined in the Style Guide."

---

## 6. Phase-Specific Implementation Guidelines

### Phase 11: Executive Dashboard & KPI Enhancements
**Pattern**: Control Tower Dashboard
*   Use large, prominent KPI cards with sparkline trends
*   Implement threshold alerts with visual indicators
*   Historical comparison views (MoM, YoY) using timeline components
*   Alert configuration UI using modal wizards

### Phase 12: Bulk Operations & Finance Admin Tools
**Pattern**: Guided Journey + Control Tower
*   Bulk operations use wizard pattern with preview/confirmation steps
*   Enhanced audit logs with filterable timeline view
*   Export functionality with clear progress indicators
*   Effective Hourly Rate displayed as prominent metric

### Phase 13: Accountant Role & Reconciliation Tools
**Pattern**: Control Tower + Resilient Design
*   Read-only views with clear role indicators
*   Export buttons with format selection (Excel, CSV, PDF)
*   Payments register as high-density table
*   Revenue recognition with timeline visualization

### Phase 14: Technician Efficiency & Context Awareness
**Pattern**: State-Aware + Contextual Indicators
*   AR status badges with color coding (green/yellow/red)
*   Contract coverage indicators using tooltip overlays
*   Barcode scanning with camera preview
*   Real-time inventory levels with visual stock indicators

### Phase 15: Client Portal Self-Service Enhancements
**Pattern**: Guided Journey + Resilient Design
*   Self-service profile editing with validation feedback
*   PDF downloads with clear file size/format indicators
*   Procurement tracking with progress stepper
*   Dispute submission wizard with multi-file upload

### Phase 16: Sales Pipeline & Quote-to-Cash
**Pattern**: Control Tower + Guided Journey
*   Pipeline kanban board with drag-and-drop
*   Quote builder with live margin calculation
*   Margin floor warnings with visual alerts
*   Product bundles selector using multi-step wizard
*   One-click conversion with confirmation modal

---

## 7. Component Library Reference

### Core Components
*   `x-status-badge`: Status indicators with semantic colors
*   `x-troubleshooting-card`: Error handling with actionable steps
*   `x-kpi-card`: Large metric displays with trend indicators
*   `x-progress-stepper`: Multi-step wizard navigation
*   `x-timeline`: Activity and audit log visualization
*   `x-confirmation-modal`: Destructive action safeguards
*   `x-loading-state`: Button and content loading indicators

### Module-Specific Components
*   `x-billing::invoice-card`: Invoice summary display
*   `x-billing::payment-wizard`: Multi-step payment flow
*   `x-billing::ar-aging-widget`: Accounts receivable aging display
*   `x-billing::profitability-card`: Client profitability metrics
*   `x-billing::subscription-card`: Subscription status and details

---

## 8. Testing & Quality Assurance

### UX Testing Checklist
- [ ] All wizards maintain state when navigating back/forward
- [ ] All destructive actions require explicit confirmation
- [ ] All error states provide actionable recovery steps
- [ ] All loading states show progress indicators
- [ ] All forms validate before allowing submission
- [ ] All dashboards update in real-time without refresh
- [ ] All color usage is semantic, not hardcoded
- [ ] All interactive elements have visible focus states
- [ ] All mobile views are usable without horizontal scroll

### Accessibility Testing
- [ ] All forms have visible labels
- [ ] All icon-only buttons have aria-labels
- [ ] All semantic HTML tags are used correctly
- [ ] All color contrasts meet WCAG AA standards
- [ ] All keyboard navigation works without mouse
- [ ] All screen reader announcements are clear

---

## 9. Performance Standards

### Loading & Response Times
*   **Initial Page Load**: < 2 seconds
*   **Dashboard Refresh**: < 500ms
*   **Form Submission**: Immediate feedback (spinner), < 3 seconds for completion
*   **Export Generation**: Progress indicator for operations > 5 seconds

### Data Refresh Rates
*   **Real-Time Indicators**: Update every 30 seconds (polling or WebSocket)
*   **Historical Charts**: Update on page load, refresh on user action
*   **Audit Logs**: Load most recent 100 items, paginate on scroll

---

## 10. Documentation Standards

### Feature Documentation Template
```markdown
## [Feature Name]

### User Story
As a [Role], I want to [Action] so that [Benefit].

### UX Pattern
[Control Tower | Guided Journey | Resilient Design]

### Components Used
- Component 1 (purpose)
- Component 2 (purpose)

### Interaction Flow
1. Entry point
2. User action
3. System response
4. Feedback/Confirmation

### Implementation Notes
- Special considerations
- Edge cases handled
- Performance optimizations

### Testing Checklist
- [ ] Test item 1
- [ ] Test item 2
```

---

## Conclusion

This style guide ensures consistency, quality, and user confidence across all features. Every new feature should be designed and reviewed against these standards before implementation.

**Remember**: We are building a mission-critical tool. Every interaction should reinforce the user's trust in the system's reliability and precision.
