# Restospect

**CIS 2102 — Front-end Development (Framework Implementation)**

A responsive restaurant and dish review website based on the supplied Restospect proposal and mockups. Built with PHP, MySQL, HTML, CSS, and vanilla JavaScript.

## Features

- Home page and searchable dish-review directory with cuisine and sort filters.
- Community-weighted restaurant ratings: each review's influence changes with helpful and not-helpful votes, while scores remain between 1 and 5.
- Account registration and sign-in with PHP password hashing and session handling.
- Create, read, update, and delete reviews; upload optional JPG/PNG dish photos (up to 5 MB).
- Helpful/not-helpful voting, with one vote per account per review and no self-voting.
- Personal dashboard showing review, restaurant, rating, and helpful-vote statistics.
- Admin-only restaurant directory creation and deletion, as in the proposal.
- Responsive layouts for mobile and desktop.

## Requirements

- PHP 8.1+ with PDO, `pdo_mysql`, Fileinfo, and file uploads enabled.
- MySQL 8.0+ (or a recent MariaDB release).
- Apache/Nginx with PHP configured, or PHP's development server for local testing.

## Database setup

1. Start MySQL and import `database.sql` using a MySQL client such as phpMyAdmin or MySQL Workbench. It creates the `restospect` database and sample restaurants/reviews.
2. Set the database environment variables before starting PHP. Defaults are `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=restospect`, `DB_USER=root`, and an empty `DB_PASSWORD`. For a real deployment, use a dedicated MySQL account and set its password in the environment; do not commit credentials.
3. Make the project directory writable by PHP for photo uploads. The app creates `uploads/` automatically when the first photo is submitted.

New accounts are regular members. To grant restaurant-manager access, first register the account, then use an administrator database client to run `UPDATE users SET role = 'admin' WHERE username = 'your_username';`. Sign out and sign back in to refresh the role in the session. Admins can add and remove directory listings from the dashboard.

## Run locally

From the project directory, start PHP's local server with `php -S 127.0.0.1:8000`, then open `http://127.0.0.1:8000`. Configure MySQL first; this project needs the MySQL PDO driver.

On Windows, XAMPP or Laragon can provide PHP, MySQL, and a web server. Put the project in that stack's web root, import `database.sql`, and open the local site URL.

## Project map

- `index.php` — request routing, form actions, SQL queries, and validation.
- `includes/` — database connection, escaping, session, and CSRF helpers.
- `views/` — PHP page templates.
- `assets/` — responsive styles and small UI interactions.
- `database.sql` — schema and sample content.
