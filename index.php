<?php
ob_start();
session_start();
try {
$db =  new PDO('mysql:host=localhost;dbname=buckutt', 'buckutt', '');
}
catch (Exception $e) {
	die('Erreur : ' . $e->getMessage());
}

$page = $_GET['p'];
if(empty($page))
{
	$page = 'index';
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>BuckUTT Manager</title>

		<meta charset="utf-8" />
		<link rel="stylesheet" type="text/css" href="./main.css" />
		<link rel="stylesheet" type="text/css" href="./anytime.5.0.5.min.css" />

		<script src="http://code.jquery.com/jquery-1.9.1.min.js" type="text/javascript"></script>
		<script src="./js/anytime.5.0.5.min.js" type="text/javascript"></script>
	</head>
	<body>
		<header>
			<h3>BuckUTT Manager</h3>
			<nav><a href="./?p=objects">Objets</a> - <a href="./?p=periods">Périodes</a></nav>
		</header>
		<div class="content">
<?php
if(isset($_SESSION['logged'])) if(is_file('./php/'.$page.'.php')) require_once('./php/'.$page.'.php'); else echo '404';
else require_once('./php/login.php');
?>
		</div>
	</body>
</html>
<?php
ob_end_flush();
