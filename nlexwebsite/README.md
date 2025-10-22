 # NLEX Website (PHP/Tailwind)

 This project is a small PHP application that powers the NLEX Music site. It uses PDO (MySQL), simple OOP models, Tailwind (CDN), and session-based authentication.

 ## Quick setup (XAMPP on Windows)

 1. Copy the project to `C:\xampp\htdocs\nlexwebsite`.
 2. Start Apache and MySQL via the XAMPP Control Panel.
 3. Import the database (if not already imported):

 ```powershell
 & 'C:\xampp\mysql\bin\mysql.exe' -u root -p < 'C:\xampp\htdocs\nlexwebsite\db\nlexweb.sql'
 ```

 4. Adjust DB credentials in `config/config.php` if needed.
 5. Open `http://localhost/nlexwebsite/` in your browser.

 ## Admin account

 A static admin account is seeded by the SQL file:
 - email: `admin@gmail.com`
 - password: `admin11111111`

 If you deleted it, recreate it using phpMyAdmin or by re-running the seed in `db/nlexweb.sql`.

 ## Important files
 - `config/config.php` — database config
 - `vendor_autoload.php` — app autoloading helper
 - `classes/` — Database, Model, User, Message
 - `auth/auth.php` — authentication actions
 - `contact.php`, `reply_user.php`, `admin/reply.php` — message endpoints
 - `inc/header.php`, `inc/footer.php` — shared layout
 - `admin/messages.php` — admin dashboard
 - `profile.php` — user profile & inbox
 - `db/nlexweb.sql` — schema and seeds
 - `scripts/migrate_add_user_reply.php` — migration to add `user_reply` columns

 ## Finalization checklist (recommended)
 - Add CSRF tokens to forms and API endpoints.
 - Enforce stronger password rules and remove default seeded admin in production.
 - Enable HTTPS for remote access (or use ngrok for short-lived tunnels).
 - Add logging and error handling for admin actions.

 ## I can help with
 - Creating a one-shot migration runner or composer script for schema changes.
 - Adding test coverage (PHPUnit) for core models and endpoints.
 - Packaging the site for deployment (Docker/XAMPP-ready bundle).

 If you want a final cleanup (rename files, consistent naming, run a linter), say "please finalize" and I will run a small codebase reformat and create a migration script list.
