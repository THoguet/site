<?php
session_start();
$titre="Enregistrement";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Enregistrement';

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
    $extensions_valides = array( 'jpg' , 'jpeg' , 'png', 'bmp', 'gif' ); //Liste des extensions valides
    
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
else {
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
   
    echo'<form method="post" action="register.php" enctype="multipart/form-data"> 
    <input type="hidden" name="pseudo" value="'.$pseudo.'">
    <input type="hidden" name="signature" value="'.$signature.'">
    <input type="hidden" name="email" value="'.$email.'">
    <input type="hidden" name="twitch" value="'.$twitch.'">
    <input type="hidden" name="website" value="'.$website.'">
    <input type="hidden" name="steam" value="'.$steam.'">
    <input type="hidden" name="lol" value="'.$lol.'">
    <input type="hidden" name="r6" value="'.$r6.'">
    <input type="hidden" name="localisation" value="'.$localisation.'">
    <input type="file" name="avatar" value="'.$_FILES['avatar'].'">
    <p><input type="submit" value="Recommencer" /></p></form>';
}
?>
</div>
</body>
</html>