<?php
/**
 * @name db.php
 *
 * @usage connects to databass
 * 
 * @author S S Qureshi
 *
 * @copyright NCT 2014
 * 
 * 
 */

$dsn = 'mysql:dbname=datadept;host=127.0.0.1';
$user = 'shahid';
$password = 'FrIdaY555';

try {
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

?>