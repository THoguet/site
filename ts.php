 <!DOCTYPE html>
<html>
	<head>
		<meta name="description" content="Ici, vous êtes sur le site de nessar, plus precisement dans la partie consacré au Teamspeak, elle affiche l'ip de celui-ci ainsi que quelques infos." />
		<?php include_once("head.php") ?>
	</head>
	<body>
		<!-- Google analytics -->
		<?php include_once("analyticstracking.php") ?>

		<div class="corps">
			<header>
				<h1 class="titre">Site de Nessar</h1>
			</header>
			<div class="menu">
				<ul style="padding-left: 0px;margin-bottom:0px;">
				<li><a href="./">Accueil</a></li>
				<li><a href="./ts.php">Teamspeak</a></li>
				<li><a href="./twitch.php">Twitch</a></li>
				<li><a href="./forum/">Forum</a></li>
				</ul>
			</div>
			<div class="contenu">
				<div class="paddingdiv" >
					<aside class="imagets">
						<img class="imageO" src="https://ts3index.com/banner/s500_263770.png">
						<img class="imageM" src="https://ts3index.com/banner/s410_263770.png">
					</aside>
					<div class="divts" style="width: 50%; text-align: center;">
						<img class="imagegifts" src=./image/ts.gif />
						<a class="bouton" href="ts3server://ts.ktfaction.fr" target="_blank">
							<div id="bouton">
								<p style="padding-top:1px;">Rejoindre le TS</p>
							</div>
						</a>
						<p>Si vous n'arrivez pas à vous connecter voici l'ip: <u>ts.ktfaction.fr</u></p>
					</div>
					<center><h2>Nouveau système de ranks et de statistiques disponible a cette adresse :</h2><a href="http://ktfaction.fr/ranksystem/"><div id="bouton" style="padding:0px">Cliquez ici</div></a></center>
				</div>
			</div>
		</div>
	</body>
</html>
