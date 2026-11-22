JCI JDP — XAMPP QUICK START AND UPDATE

NEW INSTALL
1. Start Apache and MySQL from the XAMPP Control Panel.
2. Extract this folder into C:\xampp\htdocs\JCI_JDP_XAMPP.
3. Open http://localhost/JCI_JDP_XAMPP/setup.php
4. Use the standard XAMPP MySQL defaults (user root, blank password) unless you changed them. Create the first administrator with a unique password of 14+ characters.
5. Sign in at http://localhost/JCI_JDP_XAMPP/

UPDATE AN EXISTING INSTALLATION
1. Back up your JCI_JDP database in phpMyAdmin and keep a copy of C:\xampp\htdocs\JCI_JDP_XAMPP\config.php.
2. Extract this update over the existing JCI_JDP_XAMPP folder. Keep your config.php; it is not included in this ZIP.
3. Refresh the application. Additive database migrations run automatically at the next request.
4. Use User Management, Vendor/Customer/Product masters, Inventory, Sales and Reports in the left menu.
5. For UAT, sign in as the administrator and select “UAT demo data” in the left menu. Load the 25 linked sample business records once; they are marked JDP-UAT/JCI-UAT and the loader prevents duplicate loads. The sample transactions are for testing only.

The application requires PHP 8.1+ with pdo_mysql and Apache mod_rewrite. The app directory contains Apache deny rules for private code and setup.php refuses a second install once config.php exists.

VALIDATION
Master fields validate required values, lengths, email, Indian PAN/GSTIN/IFSC/PIN/phone formats, and numeric ranges on the server. Sales, requisitions, and report date filters also validate on submit; browser checks provide immediate feedback.

PASSWORD RECOVERY
Forgot Password sends a one-time link using PHP mail(). Configure SMTP/sendmail in XAMPP's php.ini/sendmail.ini to deliver it. If local email is not configured, an administrator can reset the user's password from User Management.

Before shared or production use, replace the five state and DPC sample names with JCI's approved office master, configure a least-privilege database account and SMTP, and complete JCI's security and business approvals. See docs/IMPLEMENTATION_SCOPE.md for SRS coverage and deferred areas.
