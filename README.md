# Creator Studio — Initial Version

A Persian idea and content management system built using plain PHP, MySQL, HTML, CSS, and JavaScript. No Laravel, no AI APIs.

## Current Features
- Statistical dashboard
- Add and delete ideas
- Log content for multiple platforms
- Specify content type and production status
- Update content status
- Responsive, RTL (Right-to-Left) Persian interface
- Uses PDO and prepared statements
- CSRF protection for data-modifying forms

## Installation on XAMPP
1. Place the `creator-studio` folder in `C:\xampp\htdocs\`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Go to `http://localhost/phpmyadmin/`.
4. Import the `database/schema.sql` file (this file also creates the database).
5. If your MySQL credentials differ, edit `app/config.php`. The default setting for a standard XAMPP installation is `root` with no password.
6. Go to `http://localhost/creator-studio/`.

## Prerequisites
- PHP 8.0 or newer recommended.
- PDO MySQL extension enabled.
- MySQL/MariaDB running.

## Structure
- `index.php`: Pages and operations for the initial version
- `app/config.php`: Connection settings
- `app/bootstrap.php`: Database connection
- `database/schema.sql`: Table schema
- `assets/css/style.css`: Styling and responsiveness
- `assets/js/app.js`: Basic interface interactions

## Limitations of the Initial Version
This version is intended for local development and testing; User login, advanced calendar, media upload, analytics, automated backups, and direct social media publishing have not yet been implemented. Prior to public deployment, authentication, request rate limiting, data retention and deletion policies, error handling, and server security configurations must be finalized.

## License
MIT — Open to modification and development.
