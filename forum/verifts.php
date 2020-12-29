<?php
session_start();
$titre="Verificaion Teamspeak";
include("includes/identifiants.php");
include("includes/debut.php");

if ($id==0) erreur(ERR_IS_NOT_CO);

//Si uid + tsuid
if (isset($_POST['codesecret']) && isset($_POST['tsuid'])) {
	$uid = $_POST['codesecret'];
	$tsuid = $_POST['tsuid'];
	$i = 0;
	$uid_erreur = NULL;
	$tsuid_erreur = NULL;

	//Vérification de l'uid
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_uid =:uid');
    $query->bindValue(':uid',$uid, PDO::PARAM_STR);
    $query->execute();
    $uid_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if($uid_free) {
        $uid_erreur = "Mauvais code secret !";
        $i++;
    }

	if ($i==0) {
		$query=$db->prepare('UPDATE forum_membres SET membre_tsuid = :tsuid WHERE membre_uid = :uid');
		$query->bindValue(':uid',$uid,PDO::PARAM_STR);
		$query->bindValue(':tsuid',$tsuid,PDO::PARAM_STR);
		$query->execute();
		$query->CloseCursor();
		echo'<h1>Le Teamspeak et le forum sont relié</h1>';
		echo '<p>Merci de votre inscription !</p>';
		echo '<p>Vous avez désormé accès a toute les foncionalités</p>';
		echo'<p>Cliquez <a href="./index.php">ici</a> pour revenir a l\'accueil.</p>';
	}
	else {
   		echo'<h1>Erreur</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant la liaison</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$uid_erreur.'</p>';
        echo'<p>'.$tsuid_erreur.'</p>';
        echo'<p>Cliquez <a href="./index.php">ici</a> pour revenir a l\'accueil</p>';
    }
}

// aucune info
else {
	echo '<form method="post" action="verifts.php" enctype="multipart/form-data">';
	echo 'Veuillez entrer dans le channel <a href=ts3server://ts.ktfaction.fr?channel=%5Bcspacer%5D%3C%3C%20%E2%98%85%20%3E%3E%20Verif%20Site%20%3C%3C%20%E2%98%85%20%3E%3E>Verif site</a>, vous receverez les informations a rentrer ci-dessous:</br>';
	echo '<label for="Uid">Uid : </label>  <input required name="tsuid" type="text" id="tsuid" /><br />';
	echo '<label for="Code secret">Code Secret : </label>  <input required name="codesecret" type="text" id="codesecret" /><br />';
	echo '<p><input type="submit" value="envoyer" /></p></form>';
}
?>
</body>
</html>