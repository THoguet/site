<?php
session_start();
$titre="Voir un sujet";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/bbcode.php"); //On verra plus tard ce qu'est ce fichier	
 
//On récupère la valeur de t
$topic = (int) $_GET['t'];
 
if ($id != 0) {
    $query=$db->prepare('SELECT COUNT(*) FROM `forum_email` WHERE `membre_id` = :id AND `topic_id` = :topic');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $topic_sub = $query->fetchColumn();
    $query->CloseCursor();

    $query=$db->prepare('SELECT COUNT(*) FROM `forum_email` WHERE `membre_id` = :id AND `forum_all` = 1');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $forumall = $query->fetchColumn();
    $query->CloseCursor();
}

//A partir d'ici, on va compter le nombre de messages pour n'afficher que les 15 premiers
$query=$db->prepare('SELECT topic_titre, topic_post, forum_topic.forum_id, topic_last_post,
forum_name, auth_view, auth_topic, auth_post, auth_modo, topic_locked
FROM forum_topic 
LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id 
WHERE topic_id = :topic');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();

if (!verif_auth($data['auth_view']))
{
    erreur(ERR_AUTH_VIEW);
}

$forum=$data['forum_id'];
$totalDesMessages = $data['topic_post'] + 1;
$nombreDeMessagesParPage = $config['post_par_page'];
$nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
<a href="./voirforum.php?f='.$forum.'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a>
 --> <a href="./voirtopic.php?t='.$topic.'">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a>';
if ($id != 0) {
    if ($forumall > 0 && $topic_sub > 0) {
    	echo'<a href="./notifemail.php?action=delete&all=1&t='.$topic.'"><img src="./images/alarm.png" alt="go" style="float:right;"/></a>';
    }

    elseif ($forumall > 0) {
    	echo'<a href="./notifemail.php?action=delete&all=1"><img src="./images/alarm.png" alt="go" style="float:right;"/></a>';
    }

    elseif ($topic_sub > 0) {
        echo'<a href="./notifemail.php?action=delete&t='.$topic.'"><img src="./images/alarm.png" alt="go" style="float:right;"/></a>';
    }
    else {
        echo'<a href="./notifemail.php?action=add&t='.$topic.'"><img src="./images/no-alarm.png" alt="go" style="float:right;"/></a>';
    }
}
echo '<h1>'.stripslashes(htmlspecialchars($data['topic_titre'])).'</h1>';

//Nombre de pages
$page = (isset($_GET['page']))?intval($_GET['page']):1;
 
$premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;

 
if (!verif_auth($data['auth_post']) OR $data['topic_locked'] == 1) {
//On affiche l'image répondre
echo'<img src="./images/pasanswer.png" alt="Pas Répondre" title="Vous ne pouvez pas répondre à ce topic"></a>';
}

else {
	echo'<a href="./poster.php?action=repondre&amp;t='.$topic.'">
	<img src="./images/answer.png" alt="Répondre" title="Répondre à ce topic"></a>';
}

if ($data['topic_locked'] == 1) {
	if (verif_auth($data['auth_modo'])) {
		echo'<a href="./postok.php?action=unlock&t='.$topic.'">
		<img src="./images/lock.png" alt="deverrouiller" title="Déverrouiller ce sujet ?" /></a>';
	}
	else {
		echo'<img src="./images/lock.png" alt="verrouille" title="Ce sujet est verrouillé" />';
	}
}

else {
	if (verif_auth($data['auth_modo'])) {
		echo'<a href="./postok.php?action=lock&t='.$topic.'">
		<img src="./images/unlock.png" alt="verrouiller" title="Verrouiller ce sujet ?" /></a>';
	}
	else {
		echo'<img src="./images/unlock.png" alt="pasverouille" title="Le topic n\'est pas verrouillé.">';
	}
}

$query=$db->prepare('SELECT auth_modo FROM forum_forum WHERE forum_id = :forum');
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();
if (verif_auth($data['auth_modo'])) {
	$query->CloseCursor();
	$query=$db->prepare('SELECT forum_id, forum_name FROM forum_forum WHERE forum_id <> :forum');
	$query->bindValue(':forum',$forum,PDO::PARAM_INT);
	$query->execute();
	
	//$forum a été définie tout en haut de la page !
	echo'<p style="margin-bottom: 0px;">Déplacer vers :</p>
	<form method="post" action=postok.php?action=deplacer&amp;t='.$topic.'>
	<select name="dest">';               
	while($data=$query->fetch()) {
		echo'<option value='.$data['forum_id'].' id='.$data['forum_id'].'>'.$data['forum_name'].'</option>';
	}	
	echo'
	</select>
	<input type="hidden" name="from" value='.$forum.'>
	<input type="submit" name="submit" value="Envoyer" />
	</form>';
	$query->CloseCursor();
	$query=$db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id =:topic');
	$query->bindValue(':topic',$topic,PDO::PARAM_INT);
	$query->execute();
	$data=$query->fetch();
	if ($data['topic_locked']==1) {
		echo '<p style="margin-bottom: 0px;">Réponse automatique :</p>
		<form method="post" action=postok.php?action=autorep&amp;t='.$topic.'>
		<select name="rep">';
		$query=$db->query('SELECT automess_id, automess_titre
		FROM forum_automess');
		while ($data = $query->fetch())
		{
			 echo '<option value="'.$data['automess_id'].'">
			 '.$data['automess_titre'].'</option>';
		}
		echo '</select>  
		<input type="submit" name="submit" value="Envoyer" /></form>';
		$query->CloseCursor();
	}
}
//Enfin on commence la boucle !
$query=$db->prepare('SELECT post_id , auth_modo , post_createur , post_texte , post_time ,
membre_id, membre_pseudo, membre_inscrit, membre_avatar, membre_localisation, membre_post, membre_signature, membre_rang, topic_locked
FROM forum_post
LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur
LEFT JOIN forum_topic ON forum_topic.topic_id = :topic
LEFT JOIN forum_forum ON forum_forum.forum_id = :forum
WHERE forum_post.topic_id =:topic
ORDER BY post_id
LIMIT :premier, :nombre');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
$query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
$query->execute();
 
//On vérifie que la requête a bien retourné des messages
if ($query->rowCount()<1)
{
        echo'<p>Il n y a aucun post sur ce topic, vérifiez l\'url et reessayez</p>';
}
else
{
        //Si tout roule on affiche notre tableau puis on remplit avec une boucle
        ?><table>
        <tr>
        <th class="vt_auteur"><strong>Auteurs</strong></th>             
        <th class="vt_mess"><strong>Messages</strong></th>       
        </tr>
        <?php
        while ($data = $query->fetch())
        {
//On commence à afficher le pseudo du créateur du message :
         //On vérifie les droits du membre
         //(partie du code commentée plus tard)
         echo'<tr><td><strong>
         <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
         '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></strong></td>';
           
         /* Si on est l'auteur du message, on affiche des liens pour
         Modérer celui-ci.
         Les modérateurs pourront aussi le faire, il faudra donc revenir sur
         ce code un peu plus tard ! */     
   
		if (verif_auth($data['auth_modo']) OR $data['post_createur'] == $id) {
			echo'<td id=p_'.$data['post_id'].'>Posté à '.date('H\hi \l\e d m y',$data['post_time']).'
			<a href="./poster.php?p='.$data['post_id'].'&amp;action=delete">
			<img  src="./images/delete.png" alt="Supprimer"
			title="Supprimer ce message" /></a>   
			<a href="./poster.php?p='.$data['post_id'].'&amp;action=edit">
			<img  src="./images/edit.png" alt="Editer"
			title="Editer ce message" /></a></td></tr>';
        }
        else
        {
        echo'<td>
        Posté à '.date('H\hi \l\e d m y',$data['post_time']).'
        </td></tr>';
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
		
         //Détails sur le membre qui a posté
         echo'<tr><td>
         <img style="width:32px;" src="./images/avatars/'.$data['membre_avatar'].'" alt="" />
         <br />Membre inscrit le '.date('d/m/Y',$data['membre_inscrit']).'
         <br />Messages : '.$data['membre_post'].'
		 <br />Localisation : '.stripslashes(htmlspecialchars($data['membre_localisation'])).'
		 <br/>Grade : '.$rang.'</td>';
               
         //Message
         echo'<td><p>'.code(nl2br(stripslashes(htmlspecialchars($data['post_texte'])))).'</p>
         <br /><hr />'.code(nl2br(stripslashes(htmlspecialchars($data['membre_signature'])))).'</td></tr>';
         } //Fin de la boucle ! \o/
         $query->CloseCursor();

         ?>
</table>
<?php
	echo '<p>Page : ';
	echo get_list_page($page, $nombreDePages, './voirtopic.php?t='.$topic);
	echo'</p>';      
	
        //On ajoute 1 au nombre de visites de ce topic
        $query=$db->prepare('UPDATE forum_topic
        SET topic_vu = topic_vu + 1 WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

} //Fin du if qui vérifiait si le topic contenait au moins un message

?>           
</div>
</body>
</html>
