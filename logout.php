<?php
/**
* logout.php
*
* A simple logout script to log users out.
*
* Shahid Qureshi
* shahid@nct.orguk
*/

// enable sessions
session_start();

// delete cookies, if any
setcookie("user","",time()-3600);
setcookie("pass","",time()-3600);

// log user out
setcookie(session_name(),"",time()-3600);
session_destroy();

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Log Out</title>
		<link href="style.css" rel="stylesheet">
	</head>
	<body>
		<h1 class="isa_info">You are logged out!</h1>
		<h3><a href="index.php">home</a></h3>
	</body>
</html>