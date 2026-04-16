# ProLink — Forum module

Quick setup and usage (development):

- Import the SQL schema: open `data/database.sql` in your MySQL client (phpMyAdmin, MySQL CLI) and run it to create the `prolink` database and sample user/post.
- Configure DB credentials in [config/database.php](config/database.php#L1) if needed (default uses `root` with empty password).
- Serve the `public` folder with PHP built-in server for quick testing:

  php -S localhost:8000 -t public

- Open http://localhost:8000 in your browser and click "Connect" to enter the forum.

Notes:
- There is no user module integrated yet — a demo user (id=1) is used for actions.
- Reactions and reposts are implemented and toggle on repeated clicks.
- All data access and business logic are in controllers; models only contain getters/setters.
- Client-side validations live in `public/js/*` and should be extended for real user flows.
