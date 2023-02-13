<?php
session_start();
$titre="Enregistrement";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Enregistrement';

if ($id!=0) erreur(ERR_IS_CO);

//if (empty($_POST['pseudo'])) { // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire

$pseudo=$_POST['pseudo'];
$signature = $_POST['signature'];
$email = $_POST['email'];
$twitch = $_POST['twitch'];
$website = $_POST['website'];
$steam = $_POST['steam'];
$lol = $_POST['lol'];
$r6 = $_POST['r6'];
$localisation = $_POST['localisation'];
$avatar = $_POST['avatar'];


echo '<h1>Inscription 1/2</h1>';
echo '<form method="post" action="registerverif.php" enctype="multipart/form-data">
<fieldset><legend>Identifiants</legend>
<label for="pseudo">* Pseudo :</label>  <input required name="pseudo" type="text" id="pseudo" value="'.$pseudo.'"/> (le pseudo doit contenir entre 3 et 15 caractères)<br />
<label for="password">* Mot de Passe :</label><input required type="password" name="password" id="password" /><br />
<label for="confirm">* Confirmer le mot de passe :</label><input required type="password" name="confirm" id="confirm" />
</fieldset>
<fieldset><legend>Contacts</legend>
<p style="margin-top: 0px; margin-bottom: 0px;"><label for="email">* Votre adresse Mail :</label><input required type="email" name="email" id="email" value="'.$email.'"/> Ne pas montrer l\'email ? <input type="checkbox" name="emailprive" value="on"></p>
<label for="twitch">Votre pseudo twitch :</label><input name="twitch" id="twitch" value="'.$twitch.'"/><br />
<label for="website">Votre site web :</label><input type="text" name="website" id="website" value="'.$website.'"/><br/>
<label for="steam">Votre profil steam: </label><input type="text" name="steam" id="steam" value="'.$steam.'"/><br/>
<label for="lol">Votre pseudo lol: </label><input type="text" name="lol" id="lol" value="'.$lol.'"/><br/>
<label for="lol">Votre pseudo R6: </label><input type="text" name="r6" id="r6" value="'.$r6.'"/>
</fieldset>
<fieldset><legend>Informations supplémentaires</legend>
<label for="localisation">Localisation :</label><input type="text" name="localisation" id="localisation" value="'.$localisation.'"/>
</fieldset>
<fieldset><legend>Profil sur le forum</legend>
<label for="avatar" style="height: 18px;">Choisissez votre avatar :</label><input type="file" name="avatar" id="avatar" value="'.$avatar.'"/>(Taille max : 100Ko)<br />
<label for="signature">Signature :</label><textarea cols="40" rows="4" name="signature" id="signature" value="'.$signature.'" maxlength="'.$config['sign_maxl'].'" placeholder="La signature est limitée à 200 caractères"></textarea>
<p>Profil privé ? <input type="checkbox" name="prive" value="on"></p>
</fieldset>
<p>Les champs précédés d\'un * sont obligatoires</p>
<div class="g-recaptcha" data-sitekey="6LdwmWoUAAAAANfbEyAjRfXn4ifbjMDRNZVuS557"></div>
<p><input type="submit" value="S\'inscrire" /></p></form>
</div>
</body>
</html>';
	
	
//} //Fin de la partie formulaire

/*else { //On est dans le cas traitement 
    $captcha_erreur = NULL;
	$pseudo_erreur1 = NULL;
    $pseudo_erreur2 = NULL;
    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $twitch_erreur = NULL;
    $steam_erreur = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;
	$ip_erreur = NULL;
	$secret = "**REMOVED**";
	$response = $_POST['g-recaptcha-response'];
	$remoteip = preg_replace('/[^0-9]/', '',$_SERVER['REMOTE_ADDR']);

    //On récupère les variables
    $i = 0;
    $temps = time(); 
	$prive = 0;
    $emailprive = 0;
    $pseudo=$_POST['pseudo'];
    $signature = $_POST['signature'];
    $email = $_POST['email'];
    $twitch = $_POST['twitch'];
    $website = $_POST['website'];
    $steam = $_POST['steam'];
    $lol = $_POST['lol'];
    $r6 = $_POST['r6'];
    $localisation = $_POST['localisation'];
    $pass = md5($_POST['password']);
    $confirm = md5($_POST['confirm']);

	//verif captcha
	$api_url = "https://www.google.com/recaptcha/api/siteverify?secret=" 
	    . $secret
	    . "&response=" . $response
	    . "&remoteip=" . $remoteip ;
		
		$decode = json_decode(file_get_contents($api_url), true);
	
	if ($decode['success'] == true) {
		// C'est un humain
	}
	
	else {
		// C'est un robot ou le code de vérification est incorrecte
		$i++;
		$captcha_erreur = "Vous n'avez pas coché le captcha.";
	}
	
    //Verif steam
    if (substr($steam, -1) == "/") {
        $steam = substr($steam, 0, -1);
    }

    //Vérification du pseudo
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    $query->execute();
    $pseudo_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if(!$pseudo_free)
    {
        $pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
        $i++;
    }

    if (strlen($pseudo) < $config['pseudo_minsize'] || strlen($pseudo) > $config['pseudo_maxsize'])
    {
        $pseudo_erreur2 = "Votre pseudo est soit trop grand, soit trop petit";
        $i++;
    }

    //Vérification du mdp
    if ($pass != $confirm || empty($confirm) || empty($pass))
    {
        $mdp_erreur = "Votre mot de passe et votre confirmation diffèrent, ou sont vides";
        $i++;
    }
	
    //Vérification de l'adresse email
    //Il faut que l'adresse email n'ait jamais été utilisée
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
    $query->bindValue(':mail',$email, PDO::PARAM_STR);
    $query->execute();
    $mail_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    
    if(!$mail_free)
    {
        $email_erreur1 = "Votre adresse email est déjà utilisée par un membre";
        $i++;
    }
    //On vérifie la forme maintenant
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
    {
        $email_erreur2 = "Votre adresse E-Mail n'a pas un format valide";
        $i++;
    }

    //Vérification de la signature
    if (strlen($signature) > 200)
    {
        $signature_erreur = "Votre signature est trop longue";
        $i++;
    }

    //Vérification de l'avatar :
    if (!empty($_FILES['avatar']['size']))
    {
        //On définit les variables :
        $maxsize = 100024; //Poid de l'image
        $maxwidth = 100; //Largeur de l'image
        $maxheight = 100; //Longueur de l'image
        $extensions_valides = array( 'jpg' , 'jpeg' , 'png' , 'png', 'bmp', 'gif' ); //Liste des extensions valides
        
        if ($_FILES['avatar']['error'] > 0)
        {
                $avatar_erreur = "Erreur lors du transfert de l'avatar : ";
        }
        if ($_FILES['avatar']['size'] > $maxsize)
        {
                $i++;
                $avatar_erreur1 = "Le fichier est trop gros : (<strong>".$_FILES['avatar']['size']." Octets</strong>    contre <strong>".$maxsize." Octets</strong>)";
        }

        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
                $i++;
                $avatar_erreur2 = "Image trop large ou trop longue : 
                (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre <strong>".$maxwidth."x".$maxheight."</strong>)";
        }
        
        $extension_upload = strtolower(substr(  strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte";
        }
    }
	
	//Privé ou pas
	if (isset($_POST['prive'])) {
		$prive = 1;
	}
	
	//Email privé ou pas
	if (isset($_POST['emailprive'])) {
		$emailprive = 1;
	}
	
    //uid
    $uid = getrandom(30);

   if ($i==0) {
        verifemail($uid, $email, $pseudo);
        echo'<h1>Inscription terminée</h1>';
        echo'<p>Bienvenue '.stripslashes(htmlspecialchars($_POST['pseudo'])).' vous êtes maintenant inscrit sur le forum, il ne vous reste plus qu\'a confirmer votre email.</p>
        <p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p>';
	
        //La ligne suivante sera commentée plus bas
	$nomavatar=(!empty($_FILES['avatar']['size']))?move_avatar($_FILES['avatar']):''; 
	

    $query=$db->prepare('INSERT INTO forum_membres (membre_pseudo, membre_mdp, membre_email,             
        membre_twitch, membre_siteweb, membre_avatar,
        membre_signature, membre_localisation, membre_inscrit,   
        membre_derniere_visite, membre_prive, membre_emailprive, membre_uid, membre_steam, membre_lol, membre_r6)
        VALUES (:pseudo, :pass, :email, :twitch, :website, :nomavatar, :signature, :localisation, :temps, :temps, :prive, :emailprive, :uid, :steam, :lol, :r6)');
	$query->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
	$query->bindValue(':pass', $pass, PDO::PARAM_INT);
	$query->bindValue(':email', $email, PDO::PARAM_STR);
	$query->bindValue(':twitch', $twitch, PDO::PARAM_STR);
	$query->bindValue(':website', $website, PDO::PARAM_STR);
	$query->bindValue(':nomavatar', $nomavatar, PDO::PARAM_STR);
	$query->bindValue(':signature', $signature, PDO::PARAM_STR);
	$query->bindValue(':localisation', $localisation, PDO::PARAM_STR);
	$query->bindValue(':temps', $temps, PDO::PARAM_INT);
	$query->bindValue(':prive', $prive, PDO::PARAM_INT);
    $query->bindValue(':emailprive', $emailprive, PDO::PARAM_INT);
	$query->bindValue(':uid', $uid, PDO::PARAM_STR);
    $query->bindValue(':steam', $steam, PDO::PARAM_STR);
    $query->bindValue(':lol', $lol, PDO::PARAM_STR);
    $query->bindValue(':r6', $r6, PDO::PARAM_STR);
    $query->execute();

	//Et on définit les variables de sessions
        // $_SESSION['pseudo'] = $pseudo;
        // $_SESSION['id'] = $db->lastInsertId(); ;
        // $_SESSION['level'] = 2;
        $query->CloseCursor();
    }
    else
    {
        echo'<h1>Inscription interrompue</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant l\'incription</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$captcha_erreur.'</p>';
        echo'<p>'.$ip_erreur.'</p>';
        echo'<p>'.$pseudo_erreur1.'</p>';
        echo'<p>'.$pseudo_erreur2.'</p>';
        echo'<p>'.$mdp_erreur.'</p>';
        echo'<p>'.$email_erreur1.'</p>';
        echo'<p>'.$email_erreur2.'</p>';
        echo'<p>'.$twitch_erreur.'</p>';
        echo'<p>'.$signature_erreur.'</p>';
        echo'<p>'.$avatar_erreur.'</p>';
        echo'<p>'.$avatar_erreur1.'</p>';
        echo'<p>'.$avatar_erreur2.'</p>';
        echo'<p>'.$avatar_erreur3.'</p>';
       
        echo'<form method="post" action="register.php" enctype="multipart/form-data> 
        <input type="hidden" name="pseudo" value="'.$pseudo.'">
        <input type="hidden" name="signature" value="'.$signature.'">
        <input type="hidden" name="email" value="'.$email.'">
        <input type="hidden" name="twitch" value="'.$twitch.'">
        <input type="hidden" name="website" value="'.$website.'">
        <input type="hidden" name="steam" value="'.$steam.'">
        <input type="hidden" name="lol" value="'.$lol.'">
        <input type="hidden" name="r6" value="'.$r6.'">
        <input type="hidden" name="localisation" value="'.$localisation.'">
        <p><input type="submit" value="Recommencer" /></p></form>';
    }
}
*/?>
</div>
</body>
</html>
