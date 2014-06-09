<?php
/**
* login.php
*
* A simple login module that lets a user stay logged in
* by saving username and, ack, password in cookies.
*
* Shahid Qureshi
* shahid@nct.org
*/

// enable sessions
session_start();
$error = "";

// include the header file
include('templates/header.php');

$users = array(
	"shahid" => "test123",
	"mike" => "bidmead",
	"vaughan" => "molly"
);

define("USER","shahid");
define("PASS","test123");



// check if the username and password were saved in cookie
if (isset($_COOKIE["user"]) && (isset($_COOKIE["pass"]))) {
	// if username and password are valid, login the user
	if ($_COOKIE["user"] == USER && $_COOKIE["pass"] == PASS) {
		// set the authenticated to true
		$_SESSION["authenticated"] = true;
	}
	
	// set the cookie containing username and password for a week
	setcookie("user",$_COOKIE["user"],time() + 7 * 42 * 60 * 60);
	setcookie("pass",$_COOKIE["pass"],time() + 7 * 42 * 60 * 60);
	
	// redirect user to home page
	$host = $_SERVER["HTTP_HOST"];
	$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
	header("Location: http://$host$path/index.php");
	exit;
} else if (isset($_POST["user"]) && isset($_POST["pass"])) {
	if($_POST["user"] == USER && $_POST["pass"] == PASS) {
		$_SESSION["authenticated"] = true;
		$_SESSION["user"] = $_POST["user"];
		// save the username and password for a week in cookie
		setcookie("user",$_COOKIE["user"],time() + 7 * 42 * 60 * 60);
		setcookie("pass",$_COOKIE["pass"],time() + 7 * 42 * 60 * 60);
		// redirect the user as above
		// redirect user to home page
		$host = $_SERVER["HTTP_HOST"];
		$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
		header("Location: http://$host$path/pampers");
		exit;
	} else {
		// grab error if wrong username or password
		$error = "Wrong username or Password";	
	}
}

?>
<div id="container" style="width:600px; margin: 0 auto;min-height: 400px; text-align:center;">
	<?php
	if(isset($error) && !empty($error)) {
		echo "<div class=\"isa_error\">$error</div>";
	}
	?>
	<form action="<?=$_SERVER["PHP_SELF"]?>" method="post">
		<table style="margin: 0 auto;">
			<tr>
				<td></td>
				<td>
				<?php if(isset($_SESSION["authenticated"]) && $_SESSION["authenticated"] == true )  echo "<div class=\"isa_success\">Hello " . USER . "</div>" ; ?>
				</td>
			</tr>
			<tr>
				<td>Username:</td>
				<td><?if(isset($_POST["user"])):?><input name="user" type="text" value="<?=htmlspecialchars($_POST["user"])?>"><?elseif(isset($_COOKIE["user"])):?><input name="user" type="text" value="<?=htmlspecialchars($_COOKIE["user"])?>"><?else:?><input name="user" type="text" value=""><?endif?></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input name="pass" type="password"></td>
			</tr>
			<!--<tr>
				<td></td>
				<td><input name="keep" type="checkbox"> &nbsp; keep me logged in until I click <b>log out</b></td>

			</tr>-->
			<tr>
				<td></td>
				<td><input type="submit" value="Log In"></td>
			</tr>
		</table>
	</form>
</div>
<?php
// include the footer file
include('templates/footer.php');
?>
	