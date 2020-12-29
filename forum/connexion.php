<?php
session_start();
$titre="Connexion";
include("includes/identifiants.php");
include("includes/debut.php");

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Connexion';
echo '<h1>Connexion</h1>';
if ($id!=0) erreur(ERR_IS_CO);
if (!isset($_POST['pseudo'])) //On est dans la page de formulaire
{
	echo '<form method="post" action="connexion.php">
	<fieldset>
	<legend>Connexion</legend>
	<p>
	<label for="pseudo">Pseudo :</label><input name="pseudo" type="text" id="pseudo" /><br />
	<label for="password">Mot de Passe :</label><input type="password" name="password" id="password" />
	</p>
	</fieldset>
	<p><input type="submit" value="Connexion" /></p></form>
	<a href="./register.php">Pas encore inscrit ?</a>
	 
	</div>
	</body>
	</html>';
}

else {
    $message='';
    if (empty($_POST['pseudo']) || empty($_POST['password']) ) { //Oublie d'un champ
        $message = '<p>une erreur s\'est produite pendant votre identification.
		Vous devez remplir tous les champs</p>
		<p>Cliquez <a href="./connexion.php">ici</a> pour revenir</p>';
    }
    else { //On check le mot de passe
        $query=$db->prepare('SELECT membre_mdp, membre_id, membre_rang, membre_pseudo, membre_emailverif
        FROM forum_membres WHERE membre_pseudo = :pseudo');
        $query->bindValue(':pseudo',$_POST['pseudo'], PDO::PARAM_STR);
        $query->execute();
        $data=$query->fetch();
		if ($data['membre_mdp'] == md5($_POST['password'])) { // Acces OK !
			if ($data['membre_rang'] == 0) {//Le membre est banni 
				$message="<p>Vous avez été banni ! Votre connexion est interdite !</p>";
			}
			elseif ($data['membre_emailverif'] == 0) {
				$message = '<p>Votre E-mail n\'a pas été verifié, vous ne pouvez pas vous connecter</p><p>Allez sur cette page pour verifier votre E-mail: <a href="https://nessar.fr/forum/verifemail.php">Verification email</a>.</p>';
			}

			else { //Sinon c'est ok, on se connecte
				$_SESSION['pseudo'] = $data['membre_pseudo'];
				$_SESSION['level'] = $data['membre_rang'];
				$_SESSION['id'] = $data['membre_id'];
				$message = '<p>Bienvenue '.$data['membre_pseudo'].', vous êtes maintenant connecté!</p><p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p>';
			}
		}
		else { // Acces pas OK !
			$message = '<p>Une erreur s\'est produite 
			pendant votre identification.<br /> Le mot de passe ou le pseudo 
			entré n\'est pas correcte.</p><p>Cliquez <a href="./connexion.php">ici</a> 
			pour revenir à la page précédente
			<br /><br />Cliquez <a href="./index.php">ici</a> 
			pour revenir à la page d\'accueil</p>';
		}
		$query->CloseCursor();
    }
    echo $message.'</div></body></html>';
}
?>
