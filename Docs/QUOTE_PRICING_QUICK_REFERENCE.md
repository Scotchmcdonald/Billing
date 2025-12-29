# Quote Pricing Tier - Quick Reference

## 🎯 What's New

When creating quotes, you can now:
1. Select a pricing tier (Standard, Non-Profit, Consumer)
2. Prices auto-populate based on the selected tier
3. See +/- variance when you override prices
4. Get warned when price variance exceeds approval threshold

---

## 📍 Where to Access

Navigate to: **Billing → Finance → Reports Hub → Quotes Tab → Create Quote**

Or directly: `https://freescout-modern.local/billing/finance/quotes/create`

---

## 🔧 How to Use

### Step 1: Select Client
- Choose existing company → Pricing tier auto-selects
- Or enter new prospect → Manually select pricing tier

### Step 2: Add Products
- Select product from dropdown
- Price auto-populates based on pricing tier
- Quantity defaults to 1

### Step 3: Override Prices (Optional)
- Change price if needed
- Variance shows immediately:
  - 🟢 **Green**: Below standard (discount)
  - 🟠 **Orange**: Above standard (within threshold)
  - 🔴 **Red**: Above threshold (requires approval)

### Step 4: Review & Create
- Check total
- Review approval warning if present
- Click "Create Quote"

---

## 📊 Pricing Tiers

| Tier | Typical Use | Example |
|------|------------|---------|
| **Standard** | Regular customers | $100.00 |
| **Non-Profit** | Discounted (typically -15-20%) | $80.00 |
| **Consumer** | Individual pricing (typically +10%) | $110.00 |

---

## ⚠️ Approval Threshold

**Default: 15%**

When price variance exceeds this:
- Quote is flagged "Requires Approval"
- Cannot be sent until approved
- Visual warning appears

**Example:**
- Standard price: $100
- Threshold: 15%
- Safe range: $85 - $115
- Over/under: Requires approval

---

## 💡 Tips

1. **Company Selection**: If you select a company, the pricing tier automatically matches their configured tier

2. **Variance Calculation**: Based on "Standard" tier price, not the selected tier price

3. **Custom Items**: For products not in the system, enter description and price manually (no variance tracking)

4. **Threshold Adjustment**: You can adjust the approval threshold per-quote if needed

5. **Visual Feedback**: Watch the variance column - it updates in real-time as you change prices

---

## 🐛 Troubleshooting

**Q: Prices not auto-populating?**
A: Ensure the product has tier prices configured in Inventory

**Q: Variance always shows 0%?**
A: Variance is calculated against standard tier price. If product has no standard tier price, variance won't calculate.

**Q: Can't send quote?**
A: Check if "Requires Approval" flag is set. Contact manager for approval if variance is too high.

---

## 📞 Support

For questions or issues, contact the finance team or create a ticket in DevFeedback module.
