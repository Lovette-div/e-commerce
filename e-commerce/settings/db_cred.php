<?php
//Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'lovette.philips');
define('DB_PASS', 'Luny@19.com');
define('DB_NAME', 'ecommerce_2025A_lovette_philips');

if (!defined("SERVER")) {
    define("SERVER", DB_HOST);
}

if (!defined("USERNAME")) {
    define("USERNAME", DB_USER);
}

if (!defined("PASSWD")) {
    define("PASSWD", DB_PASS);
}

if (!defined("DATABASE")) {
    define("DATABASE", DB_NAME);
}
?>
