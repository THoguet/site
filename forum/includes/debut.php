<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
	<head>
		<!--Image dans l'onglet -->
		<link rel="shortcut icon" href="../image/n.png" type="image/x-icon">
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<!--Pour les éèàç -->
		<meta charset="utf-8" />
		<!-- Google analytics -->
		<?php include_once("analyticstracking.php") ?>
		<?php
			//Si le titre est indiqué, on l'affiche entre les balises <title>
			echo (!empty($titre))?'<title>'.$titre.'</title>':'<title> Forum </title>';
		?>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta name="description" content="Ici, vous êtes sur le site de Nessar, plus precisement sur le forum du site et du Teamspeak, vous pouvez vous creer un compte et y poster des messages." />
		<link rel="stylesheet" media="screen" type="text/css" title="Design" href="./design.css" />
		<?php
			$balises=(isset($balises))?$balises:0;
			if($balises) {
				//Inclure le script?>
				<script>
					function bbcode(bbdebut, bbfin) {
						var input = window.document.formulaire.message;
						input.focus();
						if(typeof document.selection != 'undefined') {
							var range = document.selection.createRange();
							var insText = range.text;
							range.text = bbdebut + insText + bbfin;
							range = document.selection.createRange();
							if (insText.length == 0) {
								range.move('character', -bbfin.length);
							}
							else {
								range.moveStart('character', bbdebut.length + insText.length + bbfin.length);
							}
							range.select();
						}
						else if(typeof input.selectionStart != 'undefined') {
							var start = input.selectionStart;
							var end = input.selectionEnd;
							var insText = input.value.substring(start, end);
							input.value = input.value.substr(0, start) + bbdebut + insText + bbfin + input.value.substr(end);
							var pos;
							if (insText.length == 0) {
								pos = start + bbdebut.length;
							}
							else {
								pos = start + bbdebut.length + insText.length + bbfin.length;
							}
							input.selectionStart = pos;
							input.selectionEnd = pos;
						}
						else {
							var pos;
							var re = new RegExp('^[0-9]{0,3}$');
							while(!re.test(pos)) {
								pos = prompt("insertion (0.." + input.value.length + "):", "0");
							}
							if(pos > input.value.length) {
								pos = input.value.length;
							}
							var insText = prompt("Veuillez taper le texte");
							input.value = input.value.substr(0, pos) + bbdebut + insText + bbfin + input.value.substr(pos);
						}
					}
					function smilies(img) {
						window.document.formulaire.message.value += '' + img + '';
					}
				</script><?php

			}
			
?>		<style>
			@import url('https://fonts.googleapis.com/css?family=Indie+Flower|Source+Sans+Pro|Yrsa');
		</style>
		<script src="https://cdn.ckeditor.com/ckeditor5/11.0.1/classic/ckeditor.js"></script>
	</head>
<?php
	//Attribution des variables de session
	$lvl=(isset($_SESSION['level']))?(int) $_SESSION['level']:1;
	$id=(isset($_SESSION['id']))?(int) $_SESSION['id']:0;
	$pseudo=(isset($_SESSION['pseudo']))?$_SESSION['pseudo']:'';
	//Création des variables
	$ip = ip2long($_SERVER['REMOTE_ADDR']);
	
	//Requête
	$query=$db->prepare('INSERT INTO forum_whosonline VALUES(:id, :time,:ip)
	ON DUPLICATE KEY UPDATE
	online_time = :time , online_id = :id');
	$query->bindValue(':id',$id,PDO::PARAM_INT);
	$query->bindValue(':time',time(), PDO::PARAM_INT);
	$query->bindValue(':ip', $ip, PDO::PARAM_INT);
	$query->execute();
	$query->CloseCursor();

	//Récupération des variables de configuration
	$query = $db->query('SELECT * FROM forum_config');
	$config = array();
	while($data=$query->fetch())
	{
		$config[$data['config_nom']] = $data['config_valeur']; 
	}
	$query->CloseCursor();
	//On inclue les 2 pages restantes
	include("./includes/functions.php");
	include("./includes/constants.php");
	echo'<body>
		<div class="corps">
			<header>
				<h1 class="titre1">Site de Nessar</h1>
			</header>
			<div class="menu">
				<ul class="menu_vertical">
					<li><a id="menu" href="../">Accueil</a></li>
					<li><a id="menu" href="../ts.php">Teamspeak</a></li>
					<li><a id="menu" href="../twitch.php">Twitch</a></li>
					<li><a id="menu" href="./">Forum</a></li>
				</ul>
			</div>
			<div class="contenu">
				<div id="contenu">';
	include("./includes/menu.php");
