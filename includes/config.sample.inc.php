<?php
define("ENVIRONMENT", "dev"); // dev or prod

define("MYSQL_DSN", "mysql:host=localhost;dbname=mvo"); // MySQL DSN string
define("MYSQL_USERNAME", "root"); // MySQL username
define("MYSQL_PASSWORD", ""); // MySQL password

define("SMTP_SERVER", "smtp.example.com"); // SMTP server used to send mails
define("SMTP_PORT", 25); // Port of the SMTP server
define("SMTP_ENCRYPTION", ""); // Encryption to use ('', 'ssl' or 'tls')
define("SMTP_USERNAME", "mailuser"); // Username used to authenticate at the SMTP server
define("SMTP_PASSWORD", "mailpassword"); // Password to authenticate at the SMTP server
define("SMTP_FROM_ADDRESS", "webmaster@example.com"); // Sender address
define("SMTP_FROM_NAME", "Example Webmaster"); // Sender name

define("WEBMASTER_EMAIL", SMTP_FROM_ADDRESS); // Email-Address of the webmaster

define("PAGE_TITLE_SEPARATOR", " - "); // String which should be used to separate title parts in page title

define("TIMEOUT_CONFIRMLINK", 60 * 60 * 24); // Validity in seconds for confirmation links (e.g. reset password or change email)

define("PASSWORDS_MINLENGTH", 6); // Minimum length of passwords
?>