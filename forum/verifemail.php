<?php
session_start();
$titre = 'Verification de l\'email';
include("includes/identifiants.php");
include("includes/debut.php");

//validation de l'email
if (isset($_GET['uid'])) {
	$uid = $_GET['uid'];
	$i = 0;
	$uid_erreur = NULL;

	//Vérification de l'uid
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_uid =:uid');
    $query->bindValue(':uid',$uid, PDO::PARAM_STR);
    $query->execute();
    $uid_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if($uid_free)
    {
        $uid_erreur = "L'uid n'existe pas !";
        $i++;
    }

	if ($i==0) {
		$query=$db->prepare('UPDATE forum_membres SET membre_emailverif = 1 WHERE membre_uid = :uid');
		$query->bindValue(':uid',$uid,PDO::PARAM_STR);
		$query->execute();
		$query->CloseCursor();
		echo'<h1>E-mail verifié</h1>';
		echo '<p>Merci de votre inscription !</p>';
		echo '<p>Votre compte est désormais finalisé !</p>';
		echo'<p>Cliquez <a href="./index.php">ici</a> pour revenir a l\'accueil ou <a href="./connexion.php">ici</a> pour allé vous connecter.</p>';
	}
	else {
   		echo'<h1>Envoi interrompu</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant l\'envoi</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$uid_erreur.'</p>';
        echo'<p>Cliquez <a href="./index.php">ici</a> pour revenir a l\'accueil</p>';
    }
}
//cas du traitement
elseif (isset($_POST['pseudo'])) {
	$captcha_erreur = NULL;
	$pseudo_erreur1 = NULL;
	$emailchecked_erreur = NULL;
	$secret = "6LfaOYwUAAAAAM3BTK95azA8GYogzsPS5-Kc7U9Q";
	$response = $_POST['g-recaptcha-response'];

	$i = 0;
	$pseudo=$_POST['pseudo'];

	//verif captcha
    $api_url = "https://www.google.com/recaptcha/api/siteverify?secret=" 
        . $secret
        . "&response=" . $response ;
        
        $decode = json_decode(file_get_contents($api_url), true);
    
    if ($decode['success'] == true) {
        // C'est un humain
    }
    
    else {
        // C'est un robot ou le code de vérification est incorrecte
        $i++;
        $captcha_erreur = "Vous n'avez pas coché le captcha.";
    }

    //Vérification du pseudo
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    $query->execute();
    $pseudo_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if($pseudo_free)
    {
        $pseudo_erreur1 = "Le pseudo n'est utilisé par personne.";
        $i++;
    }

    // verif emailcheck
    $query=$db->prepare('SELECT membre_uid, membre_email, membre_emailverif FROM forum_membres WHERE membre_pseudo = :pseudo');
   	$query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
   	$query->execute();
   	$data=$query->fetch();
   	$query->CloseCursor();
   	$uid = $data['membre_uid'];
   	$email = $data['membre_email'];
   	if ($data['membre_emailverif'] == 1) {
   		$emailchecked_erreur = 'Ce membre à déjà verifié son E-mail !';
   		$i++;
   	}
   	if ($i==0) {
   		verifemail($uid, $email, $pseudo);
   		echo'<h1>Envoi terminé</h1>';
   		echo '<p>Un email a bien été re envoyé, si vous ne recevez toujours pas d\'email, contactez nessar@mail.nessar.fr</p>';
   		echo '<p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p>';
   	}
   	else {
   		echo'<h1>Envoi interrompu</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant l\'envoi</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$captcha_erreur.'</p>';
        echo'<p>'.$pseudo_erreur1.'</p>';
        echo'<p>'.$emailchecked_erreur.'</p>';
        echo'<p>Cliquez <a href="./verifemail.php">ici</a> pour recommencer</p>';
   	}
}
// aucune info
else {
	echo '<form method="post" action="verifemail.php" enctype="multipart/form-data">';
	echo 'Vous avez reçu un mail pour verifier votre E-mail.</br>';
	echo 'Si vous n\'avez pas reçu cet E-mail veuillez entrer votre pseudo : ';
	echo '<label for="pseudo"></label>  <input required name="pseudo" type="text" id="pseudo" /><br />';
	echo '<div class="g-recaptcha" data-sitekey="6LfaOYwUAAAAALwLx4biwgKxMzKEyFB9BNJI-q8m"></div>';
	echo '<p><input type="submit" value="renvoyer" /></p></form>';
}
?>
</body>
</html>