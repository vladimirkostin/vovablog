<?php
if(!defined("INDEX")){header('HTTP/1.1 403 Forbidden'); die('403 Forbidden');}
$error = '';
$ip_user = trim($_SERVER['REMOTE_ADDR']);
if(!empty($ip_allow) && md5($ip_user) != $ip_allow){
	header('HTTP/1.1 403 Forbidden');
    die('403 Forbidden');
	exit();
}
session_start();
if(isset($_GET['q']) && $_GET['q'] == 'logout'){
	if($auth == 1){
		unset($_SESSION['auth']);
		session_destroy();
	}
	else{
		setcookie("auth", false, time() - 4800, "/");
	}
	header("Location: $admin_page");
}
if(empty($_SESSION['auth'])){$_SESSION['auth'] = '';}
if(empty($_COOKIE['auth'])){$_COOKIE['auth'] = '';}
if($protect == 2){
	if(!file_exists('temp')){
		mkdir('temp', 0755);
	}
	if(!file_exists('temp/.htaccess')){
		file_put_contents('temp/.htaccess', "<Files *.dat>\nDeny from all\n</Files>", LOCK_EX);
	}
	require_once 'lib/GoogleAuthenticator.php';
	$ga = new PHPGangsta_GoogleAuthenticator();
	$qr = '';
	if(!file_exists('temp/'.$totp)){
		$secret = $ga->createSecret();
		file_put_contents('temp/'.$totp, $secret, LOCK_EX);
		$qrCodeUrl = $ga->getQRCodeGoogleUrl($_SERVER['HTTP_HOST'], $secret);
		$qr = '<img width="150" src="'.$qrCodeUrl.'"><br>';
	}
	else{
		$secret = trim(file_get_contents('temp/'.$totp));
	}
	$myCode = $ga->getCode($secret);
	$_SESSION['code'] = $myCode;
}
if(isset($_POST['submit'])){
	if($protect == 1){
		$post_code = trim(strtoupper($_POST['code']));
	}
	if($protect == 2){
		$post_code = trim($_POST['code']);
	}
	if($login != trim($_POST['login']) OR $pass != md5(trim($_POST['pass']))){
		$error = '
<br>
<b>Wrong login or password!</b>';
		sleep(1);
	}
	elseif($protect == 1 && (!isset($_SESSION['code']) || empty($_SESSION['code']) || $post_code != $_SESSION['code'])){
		$error = '
<br>
<b>Wrong captcha!</b>';
		sleep(1);
	}
	elseif($protect == 2 && (!isset($_SESSION['code']) || empty($_SESSION['code']) || $post_code != $_SESSION['code'])){
		$error = '
<br>
<b>Wrong code!</b>';
		sleep(1);
	}
	else{
		if($auth == 1){
			$_SESSION['auth'] = $login;
		}
		else{
			setcookie("auth", md5($ip_user.$pass), time()+60*60*24*365, "/");
		}
		header("Location: $admin_page");
	}
}
if(($auth == 1 && !$_SESSION['auth']) || ($auth == 0 && (!isset($_COOKIE['auth']) || (md5($ip_user.$pass) != $_COOKIE["auth"])))){
	echo '
<!DOCTYPE html>
<html>
<head>
<title>Authorization</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="template/style.css">
<link rel="shortcut icon" href="template/img/favicon.ico">
</head>
<body>
<center>
<br><br>
<div class="align_center bold title">zTDS '.$version.'</div>
<br><br>
<form method="post">
	Login<br>
	<input style="max-width:140px; width:100%;" type="text" name="login" autofocus><br><br>
	Password<br>
	<input style="max-width:140px; width:100%;" type="password" name="pass"><br>';
	if($protect == 1){
		echo'
	<img style="border: 0px solid gray;" src = "template/captcha/captcha.php" width="120" height="40"/><br>
	<input style="max-width:100px; width:100%;" type="text" name="code">
	<br>';
	}
	if($protect == 2){
		if(!empty($qr)){
			echo $qr;
		}
		else{
			echo '
	<br>';
		}
		echo'
	Code<br>
	<input style="max-width:100px; width:100%;" type="text" name="code">
	<br>';
	}
	echo'
	<br>
	<input class="button" type="submit" name="submit" value="Submit">
</form>'.$error.'
</center>
</body>
</html>
	';
	exit();
}
?>