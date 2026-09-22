# Sanghavi Vyapari - Hostinger Version

Fresh PHP 8.2+ rebuild of the Vyapari ledger app for Hostinger shared/cloud hosting.

## Features

- Secure login
- Vyapari/trader master
- Product master
- Transaction entry for maal liya, maal return, cash diya, bank payment diya, fine diya, cash rate cut, bill rate cut, and opening balance
- Ledger with opening, period, and closing balances
- Round-off carry-forward
- Settlement
- Hostinger-ready `.htaccess` routing
- No Composer or Node build required

## Hostinger Setup

1. Create a MySQL database in Hostinger.
2. Import `database/schema.sql` using phpMyAdmin.
3. Copy `.env.example` to `.env` and fill your Hostinger database credentials.
4. Set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`.
5. Upload the full folder contents to Hostinger.
6. Point your domain/subdomain document root to this folder. The included `.htaccess` forwards traffic to `public/`.

## Local XAMPP Setup

1. Copy `.env.example` to `.env`.
2. Create a MySQL database and import `database/schema.sql`.
3. Visit `http://localhost/sanghavi_vyapari_hostinger`.

## Notes

- Existing old Yii code is not required.
- Existing database data can be migrated later by exporting old tables and mapping columns to the new schema.
