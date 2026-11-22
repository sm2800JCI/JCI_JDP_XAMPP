# JCI Enterprise JDP Platform — XAMPP release

This PHP 8.1+/MySQL application is installed through the browser. See `README_XAMPP.txt` for new-install and update steps. Existing installs should keep `config.php`; application startup applies additive database migrations.

## Implemented modules

- HO, RO, DPC office master and role-assigned user management.
- Vendor register with GSTIN, PAN, MSME/Udyam, bank details, contacts, office scope, active approval state and blacklist flag.
- Customer register with GSTIN/PAN, classification, address, credit terms and risk category.
- Product master with SKU, category, HSN, unit and GST rate.
- Product-linked procurement requests with quantity, required date and justification; maker-checker approve/return.
- Office product stock balance maintenance and sales register with GST-rate calculation, stock deduction, and printable invoice preview.
- Date/office filtered sales, procurement and stock reports with CSV export.
- Admin account creation/edit/disable/password reset, forgot-password link, role master entry, role permissions, and audit trail.

See `docs/IMPLEMENTATION_SCOPE.md` for SRS traceability and deferred enterprise capabilities. In particular, current GST calculation does not create a statutory invoice/e-invoice, and stock balance adjustment is a starter capability rather than a controlled GRN workflow.

## XAMPP setup

1. Start Apache and MySQL.
2. Extract `JCI_JDP_XAMPP` under `C:\xampp\htdocs`.
3. Open `http://localhost/JCI_JDP_XAMPP/setup.php`, enter first administrator details and MySQL credentials. For standard XAMPP, MySQL user is `root` with a blank password.
4. Sign in at `http://localhost/JCI_JDP_XAMPP/`.

PHP mail/SMTP must be configured in XAMPP for emailed password-reset links. If mail is not configured, an administrator can set a new password from User Management.
