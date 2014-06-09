<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link href="../../../uploadfile.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="jquery.uploadfile.min.js"></script>
</head>
<body>
<div class="header">
	<div class ="hcontainer">
		<div class="logo">
			<h1>
				<a rel="home" title="Home" href="/"></a>
			</h1>
		</div>
		<div id="user" style="float: right; padding-top: 50px;font-size: 28px; font-weight: bold;color:#007F66;">
		<?php
		if(isset($_SESSION["user"])) {
			echo "Hello ". ucwords($_SESSION["user"]);
		}
		?>
		</div>
	</div>
</div>