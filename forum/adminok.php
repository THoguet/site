<?php
session_start();
$titre="Administration";
$balises = true;
include("includes/identifiants.php");
include("includes/debut.php");

echo'<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> -->  <a href="./admin.php">Administration du forum</a>';
if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);
$cat = htmlspecialchars($_GET['cat']); //on récupère dans l'url la variable cat
switch($cat) {//1er switch
	case "config":
		echo'<h1>Configuration du forum</h1>';
		//On récupère les valeurs et le nom de chaque entrée de la table
		$query=$db->query('SELECT config_nom, config_valeur FROM forum_config');
		//Avec cette boucle, on va pouvoir contrôler le résultat pour voir s'il a changé
		while($data = $query->fetch()) {
			if ($data['config_valeur'] != $_POST[$data['config_nom']]) {
				//On met ensuite à jour
				$valeur = htmlspecialchars($_POST[$data['config_nom']]);
				$query=$db->prepare('UPDATE forum_config SET config_valeur = :valeur
				WHERE config_nom = :nom');
				$query->bindValue(':valeur', $valeur, PDO::PARAM_STR);
				$query->bindValue(':nom', $data['config_nom'],PDO::PARAM_STR);
				$query->execute();
			}
		}
    $query->CloseCursor();
    //Et le message !
    echo'<br /><br />Les nouvelles configurations ont été mises à jour !<br />  
    Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration';
break;
case "forum":
    //Ici forum
    $action = htmlspecialchars($_GET['action']); //On récupère la valeur de action
    switch($action) {//2ème switch
    case "creer":
        //On commence par les forums
		if ($_GET['c'] == "f") {
			$titre = $_POST['nom'];
			$desc = $_POST['desc'];
			$cat = (int) $_POST['cat'];
			$query=$db->prepare('INSERT INTO forum_forum (forum_cat_id, forum_name, forum_desc) 
			VALUES (:cat, :titre, :desc)');
            $query->bindValue(':cat',$cat,PDO::PARAM_INT);
            $query->bindValue(':titre',$titre, PDO::PARAM_STR);
            $query->bindValue(':desc',$desc,PDO::PARAM_STR);
            $query->execute();
			echo'<br /><br />Le forum a été créé !<br />
			Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration';
			$query->CloseCursor();
        }
        //Puis par les catégories
        elseif ($_GET['c'] == "c") {
            $titre = $_POST['nom'];
            $query=$db->prepare('INSERT INTO forum_categorie (cat_nom) VALUES (:titre)');
            $query->bindValue(':titre',$titre, PDO::PARAM_STR); 
            $query->execute();          
            echo'<p>La catégorie a été créée !<br /> Cliquez <a href="./admin.php">ici</a> 
            pour revenir à l\'administration</p>';
			$query->CloseCursor();
        }	
    break;
	
	case "edit":
		echo'<h1>Edition d\'un forum</h1>';
			
		if($_GET['e'] == "editf") {
			//Récupération d'informations
			$titre = $_POST['nom'];
			$desc = $_POST['desc'];
			$cat = (int) $_POST['depl'];       

			//Vérification
			$query=$db->prepare('SELECT COUNT(*) 
			FROM forum_forum WHERE forum_id = :id');
			$query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
			$query->execute();
			$forum_existe=$query->fetchColumn();
			$query->CloseCursor();
			if ($forum_existe == 0) erreur(ERR_FOR_EXIST);
	
            
			//Mise à jour
			$query=$db->prepare('UPDATE forum_forum 
			SET forum_cat_id = :cat, forum_name = :name, forum_desc = :desc 
			WHERE forum_id = :id');
			$query->bindValue(':cat',$cat,PDO::PARAM_INT);  
			$query->bindValue(':name',$titre,PDO::PARAM_STR);
			$query->bindValue(':desc',$desc,PDO::PARAM_STR);
			$query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
			//Message
			echo'<p>Le forum a été modifié !<br />Cliquez <a href="./admin.php">ici</a> 
			pour revenir à l\'administration</p>';
		}
        elseif($_GET['e'] == "editc") {
            //Récupération d'informations
            $titre = $_POST['nom'];

            //Vérification
            $query=$db->prepare('SELECT COUNT(*) 
            FROM forum_categorie WHERE cat_id = :cat');
            $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
            $query->execute();
            $cat_existe=$query->fetchColumn();
            $query->CloseCursor();
            if ($cat_existe == 0) erreur(ERR_CAT_EXIST);
            
            //Mise à jour
            $query=$db->prepare('UPDATE forum_categorie
            SET cat_nom = :name WHERE cat_id = :cat');
            $query->bindValue(':name',$titre,PDO::PARAM_STR);
            $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            //Message
            echo'<p>La catégorie a été modifiée !<br />
            Cliquez <a href="./admin.php">ici</a> 
            pour revenir à l\'administration</p>';
        
        }
       elseif($_GET['e'] == "ordref") {
            //On récupère les id et l'ordre de tous les forums
            $query=$db->query('SELECT forum_id, forum_ordre FROM forum_forum');
            
            //On boucle les résultats
            while($data= $query->fetch()) {
                $ordre = (int) $_POST[$data['forum_id']]; 
        
                //Si et seulement si l'ordre est différent de l'ancien, on le met à jour
                if ($data['forum_ordre'] != $ordre) {
                    $query=$db->prepare('UPDATE forum_forum SET forum_ordre = :ordre
                    WHERE forum_id = :id');
                    $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                    $query->bindValue(':id',$data['forum_id'],PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
                }
            } 
        $query->CloseCursor();
        //Message
        echo'<p>L\'ordre a été modifié !<br /> 
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
        }
       elseif($_GET['e'] == "ordrec") {
    
            //On récupère les id et les ordres de toutes les catégories
            $query=$db->query('SELECT cat_id, cat_ordre FROM forum_categorie');
        
            //On boucle le tout
            while($data = $query->fetch()) {
                $ordre = (int) $_POST[$data['cat_id']]; 
        
                //On met à jour si l'ordre a changé
                if($data['cat_ordre'] != $ordre) {
                    $query=$db->prepare('UPDATE forum_categorie SET cat_ordre = :ordre
                    WHERE cat_id = :id');
                    $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                    $query->bindValue(':id',$data['cat_id'],PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
                }
            }
        echo'<p>L\'ordre a été modifié !<br />
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
        }
    break;
	case "messageauto":
		$c = htmlspecialchars($_GET['c']);
		switch($c) {
			case "new":
				$mess = $_POST['message'];
				$titre = $_POST['titre'];
				$query=$db->prepare('INSERT INTO forum_automess (automess_id, automess_mess, automess_titre) VALUES (NULL, :mess, :titre)');
				$query-> bindValue(':mess', $mess, PDO::PARAM_STR);
				$query-> bindValue(':titre', $titre, PDO::PARAM_STR);
				$query->execute();
				$query->CloseCursor();
				echo'<p>Le message automatique a été ajouté.</p>';
			break;
			case "edit":
				$mess = isset($_POST['message']);
				$titre = isset($_POST['titre']);
				$query=$db->prepare('UPDATE forum_automess SET automess_id=:id,automess_mess=:mess,automess_titre=:titre WHERE automess_id = :id');
				$query->bindValue(':id',$_POST['automess_id'],PDO::PARAM_INT);
				$query->bindValue(':mess',$_POST['message'],PDO::PARAM_STR);
				$query->bindValue(':titre',$_POST['titre'],PDO::PARAM_STR);
				$query->execute();
				echo'<h1>Modification</h1>';
				echo'Le message automatique: '.$_POST['titre'].' a été modifié.';
				echo'<br/><br/>Cliquez <a href="admin.php">ici</a> pour revenir à l\'administration';
				$query->CloseCursor();
			break;
			case "del":
				$query=$db->prepare('DELETE FROM forum_automess WHERE automess_id = :id');
				$query->bindValue(':id',$_GET['m'],PDO::PARAM_INT);
				$query->execute();
				echo'<p>Le message a bien été supprimé.</p>';
				echo'Cliquez <a href="admin.php">ici</a> pour revenir à l\'administration';
			break;
		}
	break;
    case "droits":    
        //Récupération d'informations
        $auth_view = (int) $_POST['auth_view'];
        $auth_post = (int) $_POST['auth_post'];
        $auth_topic = (int) $_POST['auth_topic'];
        $auth_annonce = (int) $_POST['auth_annonce'];
        $auth_modo = (int) $_POST['auth_modo'];
        
        //Mise à jour
        $query=$db->prepare('UPDATE forum_forum
        SET auth_view = :view, auth_post = :post, auth_topic = :topic,
        auth_annonce = :annonce, auth_modo = :modo WHERE forum_id = :id');
        $query->bindValue(':view',$auth_view,PDO::PARAM_INT);
        $query->bindValue(':post',$auth_post,PDO::PARAM_INT);
        $query->bindValue(':topic',$auth_topic,PDO::PARAM_INT);
        $query->bindValue(':annonce',$auth_annonce,PDO::PARAM_INT);
        $query->bindValue(':modo',$auth_modo,PDO::PARAM_INT);
        $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
      
        //Message
        echo'<p>Les droits ont été modifiés !<br />
        Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
    break;
    } //Fin du switch
break;

case"membres":
	$action = htmlspecialchars($_GET['action']); //On récupère la valeur de action
    switch($action) {//2ème switch
	case"edit":
		//On déclare les variables 
		$pseudo_erreur1 = NULL;
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
		$pseudo = $_POST['pseudo'];
		$temps = time();
		$signature = $_POST['signature'];
		$email = $_POST['email'];
		$twitch = $_POST['twitch'];
		$website = $_POST['website'];
		$localisation = $_POST['localisation'];

		//Vérification de l'adresse email
		//Il faut que l'adresse email n'ait jamais été utilisée (sauf si elle n'a pas été modifiée)

		//On commence donc par récupérer le mail
		$query=$db->prepare('SELECT membre_email FROM forum_membres WHERE membre_id =:id'); 
		$query->bindValue(':id',$_POST['idori'],PDO::PARAM_INT);
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
			
			//Le pseudo doit être unique !
			//Il faut donc vérifier s'il a été modifié, si c'est le cas, on vérifie bien 
			//l'unicité
			$query=$db->prepare('SELECT membre_pseudo FROM forum_membres WHERE membre_id =:id');
			$query->bindValue(':id',$_POST['idori'],PDO::PARAM_INT);
			$query->execute();
			$data1=$query->	fetch();
			if (strtolower($data1['membre_pseudo']) != $pseudo) {
				$query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
				$query->execute(array('pseudo'=>$pseudo));
				$pseudo_free=($query->fetchColumn()==0)?1:0;
				if(!$pseudo_free)
				{
					$pseudo_erreur1 = "Votre pseudo est déjà utilisé par un membre";
					$i++;
				}
			}
			$query->CloseCursor();
	
		//Vérification de la signature
		if (strlen($signature) > 200) {
			$signature_erreur = "Votre nouvelle signature est trop longue";
			$i++;
		}
 
 
			echo'<h1>Modification terminée</h1>';
			echo'<p>Votre profil a été modifié avec succès !</p>';
			echo'<p>Cliquez <a href="./index.php">ici</a> 
			pour revenir à la page d\'accueil</p>';
 
			//On modifie la table
	
			$query=$db->prepare('UPDATE forum_membres
			SET membre_email=:mail, membre_twitch=:twitch, membre_siteweb=:website,
			membre_signature=:sign, membre_localisation=:loc, membre_pseudo=:pseudo
			WHERE membre_id=:id');
			$query->bindValue(':mail',$email,PDO::PARAM_STR);
			$query->bindValue(':pseudo',$pseudo,PDO::PARAM_STR);
			$query->bindValue(':twitch',$twitch,PDO::PARAM_STR);
			$query->bindValue(':website',$website,PDO::PARAM_STR);
			$query->bindValue(':sign',$signature,PDO::PARAM_STR);
			$query->bindValue(':loc',$localisation,PDO::PARAM_STR);
			$query->bindValue(':id',$_POST['idori'],PDO::PARAM_INT);
			$query->execute();
			$query->CloseCursor();
		}
		else
		{
			echo'<h1>Modification interrompue</h1>';
			echo'<p>Une ou plusieurs erreurs se sont produites pendant la modification du profil</p>';
			echo'<p>'.$i.' erreur(s)</p>';
			echo'<p>'.$pseudo_erreur1.'</p>';
			echo'<p>oui'.$_POST['idori'].'</p>';
			echo'<p>'.$email_erreur1.'</p>';
			echo'<p>'.$email_erreur2.'</p>';
			echo'<p>'.$twitch_erreur.'</p>';
			echo'<p>'.$signature_erreur.'</p>';
			echo'<p>'.$avatar_erreur.'</p>';
			echo'<p>'.$avatar_erreur1.'</p>';
			echo'<p>'.$avatar_erreur2.'</p>';
			echo'<p>'.$avatar_erreur3.'</p>';
			echo'<p> Cliquez <a href="./admin.php?cat=membres&action=edit">ici</a> pour recommencer</p>';
		}
		break;
		case "droits":
			$membre =$_POST['pseudo'];
			$rang = (int) $_POST['droits'];
			$query=$db->prepare('UPDATE forum_membres SET membre_rang = :rang
			WHERE LOWER(membre_pseudo) = :pseudo');
			$query->bindValue(':rang',$rang,PDO::PARAM_INT);
			$query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
			$query->execute();
			$query->CloseCursor();
			echo'<p>Le niveau du membre a été modifié !<br />
			Cliquez <a href="./admin.php">ici</a> pour revenir à l\'administration</p>';
		break;
		case "ban":
			//Bannissement dans un premier temps
			//Si jamais on n'a pas laissé vide le champ pour le pseudo
			if (isset($_POST['membre']) AND !empty($_POST['membre']))
			{
				$membre = $_POST['membre'];
				$query=$db->prepare('SELECT membre_id 
				FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo');    
				$query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
				$query->execute();
				//Si le membre existe
				if ($data = $query->fetch())
				{
					//On le bannit
					$query=$db->prepare('UPDATE forum_membres SET membre_rang = 0 
					WHERE membre_id = :id');
					$query->bindValue(':id',$data['membre_id'], PDO::PARAM_INT);
					$query->execute();
					$query->CloseCursor();
					echo'<br /><br />
					Le membre '.stripslashes(htmlspecialchars($membre)).' a bien été banni !<br />';
				}
				else 
				{
					echo'<p>Désolé, le membre '.stripslashes(htmlspecialchars($membre)).' n existe pas !
					<br />
					Cliquez <a href="./admin.php?cat=membres&action=ban">ici</a> 
					pour réessayer</p>';
				}
			}
			//Debannissement ici
			$query = $db->query('SELECT membre_id FROM forum_membres 
			WHERE membre_rang = 0');
			//Si on veut débannir au moins un membre
			if ($query->rowCount() > 0)
			{
				$i=0;
				while($data= $query->fetch())
				{
					if(isset($_POST[$data['membre_id']]))
					{
					$i++;
						//On remet son rang à 2
						$query=$db->prepare('UPDATE forum_membres SET membre_rang = 2 
						WHERE membre_id = :id');
						$query->bindValue(':id',$data['membre_id'],PDO::PARAM_INT);
						$query->execute();
						$query->CloseCursor();
					}
				}
				if ($i!=0)
				echo'<p>Les membres ont été débannis<br />
				Cliquez <a href="./admin.php">ici</a> pour retourner à l\'administration</p>';
			}
		break;
		}
break;
}
