<?php

/* local – comment this out on production / live env */
define('DB_SERV', "localhost:8889");
define('DB_USER', "root");
define('DB_PASS', "root");
define('DB_DB',   "meta");

/* production – use this for live
define('DB_SERV', "");      // Database server (normally localhost or 127.0.0.1, 0.0.0.0 etc, + port (8889, 3306, etc), depending on the system)
define('DB_USER', "");      // DB root username
define('DB_PASS', "");      // DB root password
define('DB_DB', "");  */    // DB name

ini_set('display_errors', '0');             // leave as zero's unless debugging
ini_set('display_startup_errors', '0');

?>