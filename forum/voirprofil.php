<?php
session_start();
$titre="Profil";
include("includes/identifiants.php");
include("includes/debut.php");

//On récupère la valeur de nos variables passées par URL
$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'consulter';
$membre = isset($_GET['m'])?(int) $_GET['m']:'';
//On regarde la valeur de la variable $action
switch($action) {
    //Si c'est "consulter"
    case "consulter":
		//On récupère les infos du membre
		$query=$db->prepare('SELECT membre_pseudo, membre_avatar,
		membre_email, membre_twitch, membre_signature, membre_siteweb, membre_post,
		membre_inscrit, membre_localisation, membre_prive, membre_rang, membre_emailprive, membre_steam, membre_tsuid, membre_lol, membre_r6
		FROM forum_membres WHERE membre_id=:membre');
		$query->bindValue(':membre',$membre, PDO::PARAM_INT);
		$query->execute();
		$data=$query->fetch();
		
		if ($data['membre_prive'] and $membre != $id) erreur(ERR_PRIVE);
	   
		//On affiche les infos sur le membre
		echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
		profil de '.stripslashes(htmlspecialchars($data['membre_pseudo']));
		echo'<h1>Profil de '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</h1>';
		
		echo'<img src="./images/avatars/'.$data['membre_avatar'].'"
		alt="Ce membre n\'a pas d\'avatar" />';
		
		if ($id == $membre) {
			echo'<p> </p><a href="./voirprofil.php?action=modifier"><img src="./images/edit.png" alt="Editer" title="Editer ce message" /></a>';
		}
		
		if ($data['membre_rang'] == 5) {
			$rang = "Administrateur";
		} 
		elseif ($data['membre_rang'] == 4) {
			$rang = "Modérateur";
		} 		
		elseif ($data['membre_rang'] == 3) {
			$rang = "KT";
		} 
		elseif ($data['membre_rang'] == 2) {
			$rang = "Membre";
		} 
		elseif ($data['membre_rang'] == 1) {
			$rang = "Visiteur";
		}
		elseif ($data['membre_rang'] == 0) {
			$rang = "Banni";
		} 
		else {
			$rang = "Inconnu";
		}
			
		echo'<p><strong>Grade:</strong> '.$rang.'<br />';
		
		echo'<strong>Adresse E-Mail : </strong>';
		if ($data['membre_emailprive'] == 0) {
			echo'<a href="mailto:'.stripslashes($data['membre_email']).'">
			'.stripslashes(htmlspecialchars($data['membre_email'])).'</a><br />';
		}
		else {
			echo'Privé</br>';
		}
		
		if ($data['membre_twitch'] != '') {
			echo'<strong>Chaine Twitch: </strong><a href="https://twitch.tv/'.stripslashes(htmlspecialchars($data['membre_twitch'])).'">twitch.tv/'.stripslashes(htmlspecialchars($data['membre_twitch'])).'</a><br />';
		}
        if ($data['membre_steam'] != NULL) {
            echo'<strong>Profil steam: </strong><a href="'.stripslashes(htmlspecialchars($data['membre_steam'])).'">'.stripslashes(htmlspecialchars($data['membre_steam'])).'</a><br />';
        }
		if ($data['membre_siteweb'] != '') {
			echo'<strong>Site Web : </strong><a href="http://'.stripslashes($data['membre_siteweb']).'" target=_blank>'.stripslashes(htmlspecialchars($data['membre_siteweb'])).'</a><br />';
		}

        if ($data['membre_r6'] != NULL) {
            echo'<strong>Pseudo R6: </strong>'.stripslashes(htmlspecialchars($data['membre_r6'])).'</a><br />';
        }

        if ($data['membre_lol'] != NULL) {
            echo'<strong>Pseudo Riot: </strong>'.stripslashes(htmlspecialchars($data['membre_lol'])).'</a><br />';
        }
		echo'<br /><br />';
	
		if ($data['membre_post'] != 0 and $data['membre_post'] != 1) {
			echo'Ce membre est inscrit depuis le <strong>'.date('d/m/Y',$data['membre_inscrit']).'</strong> et a posté <strong>'.$data['membre_post'].'</strong> messages<br /><br />';
		}
		elseif ($data['membre_post'] == 1) {
			echo'Ce membre est inscrit depuis le <strong>'.date('d/m/Y',$data['membre_inscrit']).'</strong> et n\'a posté qu\'<strong>'.$data['membre_post'].'</strong> message<br /><br />';
		}
		else {
			echo'Ce membre est inscrit depuis le <strong>'.date('d/m/Y',$data['membre_inscrit']).'</strong> et n\'a posté aucun message<br /><br />';
		}
		echo'<strong>Localisation : </strong>'.stripslashes(htmlspecialchars($data['membre_localisation'])).'</p>';
		$query->CloseCursor();
		break;
	   
    //Si on choisit de modifier son profil
    case "modifier":
    if (empty($_POST['sent'])) // Si on la variable est vide, on peut considérer qu'on est sur la page de formulaire
    {
        //On commence par s'assurer que le membre est connecté
        if ($id==0) erreur(ERR_IS_NOT_CO);

        //On prend les infos du membre
        $query=$db->prepare('SELECT membre_pseudo, membre_email,
        membre_siteweb, membre_signature, membre_twitch, membre_localisation,
        membre_avatar, membre_prive, membre_steam, membre_lol, membre_r6, membre_tsuid
        FROM forum_membres WHERE membre_id=:id');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Modification du profil';
        echo '<h1>Modifier son profil</h1>';
        
        echo '<form method="post" action="voirprofil.php?action=modifier" enctype="multipart/form-data">
       
 
        <fieldset><legend>Identifiants</legend>
        Pseudo : <strong>'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</strong><br />       
        <label for="password" style="width: 200px;">Nouveau mot de Passe :</label>
        <input placeholder="Nouveau mot de passe" type="password" name="password" id="password" /><br />
        <label for="confirm" style="width: 200px;">Confirmer le mot de passe :</label>
        <input placeholder="Confirmer le mot de passe" type="password" name="confirm" id="confirm"  />
        </fieldset>
 
        <fieldset><legend>Contacts</legend>
        <label for="email">Votre adresse E_Mail :</label>
        <input type="text" name="email" id="email"
        value="'.stripslashes($data['membre_email']).'" /><br />
 
        <label for="twitch">Votre pseudo Twitch :</label>
        <input type="text" name="twitch" id="twitch"
        value="'.stripslashes($data['membre_twitch']).'" /><br />
 
        <label for="website">Votre site web :</label>
        <input type="text" name="website" id="website"
        value="'.stripslashes($data['membre_siteweb']).'" /><br />

        <label for="website">Votre profil steam :</label>
        <input type="text" name="steam" id="steam"
        value="'.stripslashes($data['membre_steam']).'" /><br />

        <label for="website">Votre pseudo Riot: </label>
        <input type="text" name="lol" id="lol"
        value="'.stripslashes($data['membre_lol']).'" /><br />

        <label for="website">Votre pseudo R6: </label>
        <input type="text" name="r6" id="r6"
        value="'.stripslashes($data['membre_r6']).'" /><br />';
        
        if ($data['membre_tsuid'] == NULL) {
            echo '<label for="website">Liez votre Teamspeak avec le forum:</label>
            <a href="./verifts.php" style="text-decoration: none; color: white;"><div id="bouton" style="padding:0px">Verif TS</div></a><br />';
        }
        else {
            echo '<label for="website">Pour re-lier votre TS avec le site : </label>
            <a href="./verifts.php" style="text-decoration: none; color: white;"><div id="bouton" style="padding:0px">Verif TS</div></a><br />';
        }

        echo '</fieldset>

        <fieldset><legend>Informations supplémentaires</legend>
        <label for="localisation">Localisation :</label>
        <input type="text" name="localisation" id="localisation"
        value="'.stripslashes($data['membre_localisation']).'" /><br />
        </fieldset>

        <fieldset><legend>Profil sur le forum</legend>
        <label for="avatar">Changer votre avatar :</label>
        <input type="file" name="avatar" id="avatar" />
        (Taille max : 10 ko)<br /><br />
        <label><input type="checkbox" name="delete" value="Delete" />
        Supprimer l\'avatar</label>
        Avatar actuel :
        <img src="./images/avatars/'.$data['membre_avatar'].'"
        alt="pas d avatar" />
     
        <br /><br />
        <label for="signature">Signature :</label>
        <textarea maxlength="200" placeholder="La signature est limitée à 200 caractères" cols="40" rows="4" name="signature" id="signature">
        '.stripslashes($data['membre_signature']).'</textarea>';
		if ($data['membre_prive'] == 1) {
			echo '<p>Profil privé ? <input type="checkbox" checked="checked" name="prive" value="on"></p>';
		}
		else {
			echo '<p>Profil privé ? <input type="checkbox" name="prive" value="on"></p>';
		}
		echo '</fieldset>
        <p>
        <input type="submit" value="Modifier son profil" />
        <input type="hidden" id="sent" name="sent" value="1" />
        </p></form>';
        $query->CloseCursor();   
    }   

    else //Cas du traitement
    {
     //On déclare les variables 

    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $twitch_erreur = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;

    //Encore et toujours notre belle variable $i :p
    $i = 0;
    $temps = time(); 
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
	
    //Vérification du mdp
		if ($pass != $confirm)
		{
			 $mdp_erreur = "Votre mot de passe et votre confirmation sont différents";
			 $i++;
		}
    //Vérification de l'adresse email
    //Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)

    //On commence donc par récupérer le mail
    $query=$db->prepare('SELECT membre_email FROM forum_membres WHERE membre_id =:id'); 
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    if (strtolower($data['membre_email']) != strtolower($email))
    {
        //Il faut que l'adresse email n'ait jamais été utilisée
        $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
        $query->bindValue(':mail',$email,PDO::PARAM_STR);
        $query->execute();
        $mail_free=($query->fetchColumn()==0)?1:0;
        $query->CloseCursor();
        if(!$mail_free)
        {
            $email_erreur1 = "Votre adresse email est déjà utilisé par un membre";
            $i++;
        }

        //On vérifie la forme maintenant
        if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
        {
            $email_erreur2 = "Votre nouvelle adresse E-Mail n'a pas un format valide";
            $i++;
        }
    }

    //Verif steam
    if (substr($steam, -1) == "/") {
        $steam = substr($steam, 0, -1);
    }

    //Vérification de la signature
    if (strlen($signature) > 200)
    {
        $signature_erreur = "Votre nouvelle signature est trop longue";
        $i++;
    }
 
 
    //Vérification de l'avatar
 
    if (!empty($_FILES['avatar']['size']))
    {
        //On définit les variables :
        $maxsize = 30072; //Poid de l'image
        $maxwidth = 100; //Largeur de l'image
        $maxheight = 100; //Longueur de l'image
        //Liste des extensions valides
        $extensions_valides = array( 'jpg' , 'jpeg' , 'png' , 'png', 'bmp' );
 
        if ($_FILES['avatar']['error'] > 0)
        {
        $avatar_erreur = "Erreur lors du tranfsert de l'avatar : ";
        }
        if ($_FILES['avatar']['size'] > $maxsize)
        {
        $i++;
        $avatar_erreur1 = "Le fichier est trop gros :
        (<strong>".$_FILES['avatar']['size']." Octets</strong>
        contre <strong>".$maxsize." Octets</strong>)";
        }
 
        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
        $i++;
        $avatar_erreur2 = "Image trop large ou trop longue :
        (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> contre
        <strong>".$maxwidth."x".$maxheight."</strong>)";
        }
 
        $extension_upload = strtolower(substr(  strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
                $i++;
                $avatar_erreur3 = "Extension de l'avatar incorrecte";
        }
    }
	
	//Prive ou pas
	if (isset($_POST['prive'])) {
		$prive = 1;
	}
	else {
		$prive = 0;
	}

    echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> Modification du profil';

 
    if ($i == 0) // Si $i est vide, il n'y a pas d'erreur
    {
        if (!empty($_FILES['avatar']['size']))
        {
                $nomavatar=move_avatar($_FILES['avatar']);
                $query=$db->prepare('UPDATE forum_membres
                SET membre_avatar = :avatar 
                WHERE membre_id = :id');
                $query->bindValue(':avatar',$nomavatar,PDO::PARAM_STR);
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
        }
 
        //Une nouveauté ici : on peut choisis de supprimer l'avatar
        if (isset($_POST['delete']))
        {
                $query=$db->prepare('UPDATE forum_membres
		SET membre_avatar=0 WHERE membre_id = :id');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
        }
        echo'<h1>Modification terminée</h1>';
        echo'<p>Votre profil a été modifié avec succès !</p>';
        echo'<p>Cliquez <a href="./index.php">ici</a> 
        pour revenir à la page d accueil</p>';
 
        //On modifie la table
		if ($pass != 'd41d8cd98f00b204e9800998ecf8427e') {
			$query=$db->prepare('UPDATE forum_membres
			SET  membre_mdp = :mdp, membre_email=:mail, membre_twitch=:twitch, membre_siteweb=:website, membre_lol=:lol, membre_r6=:r6 ,membre_signature=:sign, membre_localisation=:loc, membre_prive=:prive
			WHERE membre_id=:id');
			$query->bindValue(':mdp',$pass,PDO::PARAM_INT);
            $query->bindValue(':mail',$email,PDO::PARAM_STR);
            $query->bindValue(':twitch',$twitch,PDO::PARAM_STR);
            $query->bindValue(':website',$website,PDO::PARAM_STR);
            $query->bindValue(':steam',$steam,PDO::PARAM_STR);
            $query->bindValue(':lol',$lol,PDO::PARAM_STR);
            $query->bindValue(':r6',$r6,PDO::PARAM_STR);
            $query->bindValue(':sign',$signature,PDO::PARAM_STR);
            $query->bindValue(':loc',$localisation,PDO::PARAM_STR);
            $query->bindValue(':id',$id,PDO::PARAM_INT);
            $query->bindValue(':prive',$prive,PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
		}
		else {
			$query=$db->prepare('UPDATE forum_membres
			SET membre_email=:mail, membre_twitch=:twitch, membre_siteweb=:website, membre_signature=:sign, membre_localisation=:loc, membre_prive=:prive, membre_steam=:steam, membre_lol=:lol, membre_r6=:r6
			WHERE membre_id=:id');
			$query->bindValue(':mail',$email,PDO::PARAM_STR);
			$query->bindValue(':twitch',$twitch,PDO::PARAM_STR);
			$query->bindValue(':website',$website,PDO::PARAM_STR);
            $query->bindValue(':steam',$steam,PDO::PARAM_STR);
            $query->bindValue(':lol',$lol,PDO::PARAM_STR);
            $query->bindValue(':r6',$r6,PDO::PARAM_STR);
			$query->bindValue(':sign',$signature,PDO::PARAM_STR);
			$query->bindValue(':loc',$localisation,PDO::PARAM_STR);
			$query->bindValue(':id',$id,PDO::PARAM_INT);
			$query->bindValue(':prive',$prive,PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
		}
    }
    else
    {
        echo'<h1>Modification interrompue</h1>';
        echo'<p>Une ou plusieurs erreurs se sont produites pendant la modification du profil</p>';
        echo'<p>'.$i.' erreur(s)</p>';
        echo'<p>'.$mdp_erreur.'</p>';
        echo'<p>'.$email_erreur1.'</p>';
        echo'<p>'.$email_erreur2.'</p>';
        echo'<p>'.$twitch_erreur.'</p>';
        echo'<p>'.$signature_erreur.'</p>';
        echo'<p>'.$avatar_erreur.'</p>';
        echo'<p>'.$avatar_erreur1.'</p>';
        echo'<p>'.$avatar_erreur2.'</p>';
        echo'<p>'.$avatar_erreur3.'</p>';
        echo'<p> Cliquez <a href="./voirprofil.php?action=modifier">ici</a> pour recommencer</p>';
    }
} //Fin du else
    break;
 
default; //Si jamais c'est aucun de ceux là c'est qu'il y a eu un problème :o
echo'<p>Cette action est impossible</p>';
 
} //Fin du switch
?>
</div>
</body>
</html>