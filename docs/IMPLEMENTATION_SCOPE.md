# JCI JDP release scope

The supplied SRS is a 22-volume enterprise specification compiled from 18 volumes. This PHP/MySQL release implements a usable operational starter across JCI's Head Office, five Regional Offices and DPC hierarchy. It does not claim to complete the 35+ modules described in the SRS.

## Implemented

- Organization and access: HO, RO and DPC office master; roles; user create/edit/disable; organization-scoped operational records; audit entries.
- Vendor master: GSTIN structure, PAN format, MSME/Udyam, bank and contact details, office assignment, active approval flag and blacklist flag.
- Customer master: customer type, GSTIN, PAN, address and contact, PIN/state, credit limit/days and risk category. New customer starts inactive until an authorized HO/Admin activates it.
- Product master: SKU, category, HSN, unit, GST rate, description and active flag.
- Procurement: product-linked purchase requisition, quantity, required-by date, justification, amount, maker-checker approval and status event history.
- Inventory and sales: office stock balances, sales register, customer/product, quantity, unit price, GST rate calculation and stock decrement when recording a sale. Sales open a printable invoice preview with a print action.
- Role access: role master entry/edit, configurable view/create/edit/approve rights, role-filtered menus and dashboard cards. Requisition rows identify the approving officer and decision time.
- Reporting: filtered sales, procurement and stock reports by date, office scope and CSV export.
- Account recovery: user-facing one-time email reset link; administrator can also issue a new password through User Management.

## SRS areas that remain for a later release

RFQ, supplier quotations/comparison, PO and delegated financial thresholds; GRN, batch/warehouse movement, approved stock adjustments and physical verification; customer/vendor multi-step verification and performance scoring; quotation/order/dispatch/returns, finance ledger and payment allocation; GST invoice/IRN/e-way bill integrations; email/SSO/MFA/Power BI/SIEM; escalation/delegation engines; and formal enterprise test, DR and compliance controls.

The printable sales invoice is an internal preview. Configure JCI seller identity and statutory details before official use; it is not an e-invoice or a validated statutory tax invoice. The inventory balance form is an opening-balance starter, not a controlled GRN process. Use the system for evaluation until JCI business owners approve the full workflows and security controls.

## Data and deployment assumptions

The source SRS extract does not identify the names of the five states or provide JCI's authoritative office/vendor/product masters. Replace State 1–5 and DPC placeholders with approved records. Configure PHP mail/SMTP to deliver password reset links; without it, an administrator can reset an account from User Management. Change the initial local XAMPP root database connection to a least-privilege account before shared deployment.
