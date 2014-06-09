<?php
/**
* functions.php
*
* file contains commonly used functions
*
* Shahid Qureshi
* shahid@nct.org
*/

function authenticate () {
	define("USER","shahid");
	define("USER","shahid");
	// check if the username and password were saved in cookie
	if (isset($_COOKIE["user"]) && (isset($_COOKIE["pass"]))) {
		// if username and password are valid, login the user
		if ($_COOKIE["user"] == USER && $_COOKIE["pass"] == PASS) {
			// set the authenticated to true
			$_SESSION["authenticated"] = true;
		} else {
			$_SESSION["authenticated"] = false;
		}
	}
}

?>