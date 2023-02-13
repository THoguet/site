<?php
//Cette fonction doit être appelée avant tout code html
session_start();

//On donne ensuite un titre à la page, puis on appelle notre fichier debut.php
$titre = "Index du forum";
include("includes/identifiants.php");
include("includes/debut.php");

echo'<i>Vous êtes ici : </i><a href ="./index.php">Index du forum</a>';

if ($id != 0) {
    $query=$db->prepare('SELECT COUNT(*) FROM `forum_email` WHERE `membre_id` = :id AND `forum_all` = 1');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $forumall = $query->fetchColumn();
    $query->CloseCursor();
    if ($forumall > 0) {?>
        <a href="./notifemail.php?action=delete&all=1"><img src="./images/alarm.png" alt="go" style="float:right;"/></a><?php
    }
    else {?>
        <a href="./notifemail.php?action=add&all=1"><img src="./images/no-alarm.png" alt="go" style="float:right;"/></a><?php
    }
}?>
<h1>Forum</h1>
<?php
//Initialisation de deux variables
$totaldesmessages = 0;
$categorie = NULL;

//Cette requête permet d'obtenir tout sur le forum
$query=$db->prepare('SELECT cat_id, cat_nom, 
forum_forum.forum_id, forum_name, forum_desc, forum_post, forum_topic, auth_view, forum_topic.topic_id,  forum_topic.topic_post, post_id, post_time, post_createur, membre_pseudo, 
membre_id 
FROM forum_categorie
LEFT JOIN forum_forum ON forum_categorie.cat_id = forum_forum.forum_cat_id
LEFT JOIN forum_post ON forum_post.post_id = forum_forum.forum_last_post_id
LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur
WHERE auth_view <= :lvl 
ORDER BY cat_ordre, forum_ordre DESC');
$query->bindValue(':lvl',$lvl,PDO::PARAM_INT);
$query->execute();
?>
<table>
<?php
//Début de la boucle
while($data = $query->fetch())
{
    //On affiche chaque catégorie
    if( $categorie != $data['cat_id'] )
    {
        //Si c'est une nouvelle catégorie on l'affiche
       
        $categorie = $data['cat_id'];
        ?>
        <tr>
        <th class="ico"></th>
        <th class="titre"><strong><?php echo stripslashes(htmlspecialchars($data['cat_nom'])); ?>
        </strong></th>             
        <th class="nombremessages"><strong>Sujets</strong></th>       
        <th class="nombresujets"><strong>Messages</strong></th>       
        <th class="derniermessage"><strong>Dernier message</strong></th>   
        </tr>
        <?php
               
    }

	if (verif_auth($data['auth_view'])) {
	//Affichage des forums	
    //Ici, on met le contenu de chaque catégorie
    // Ce super echo de la mort affiche tous
    // les forums en détail : description, nombre de réponses etc...

    echo'<tr><td><center><img src="./images/message.png" alt="message" /></center></td>
		<td class="titre"><strong>
		<a href="./voirforum.php?f='.$data['forum_id'].'">
		'.stripslashes(htmlspecialchars($data['forum_name'])).'</a></strong>
		<br />'.nl2br(stripslashes(htmlspecialchars($data['forum_desc']))).'</td>
		<td class="nombresujets">'.$data['forum_topic'].'</td>
		<td class="nombremessages">'.$data['forum_post'].'</td>';

    // Deux cas possibles :
    // Soit il y a un nouveau message, soit le forum est vide
    if (!empty($data['forum_post'])) {
         //Selection dernier message
		$nombreDeMessagesParPage = 15;
		$nbr_post = $data['topic_post'] +1;
		$page = ceil($nbr_post / $nombreDeMessagesParPage);
		 
		echo'<td class="derniermessage">
			'.date('H\hi \l\e d/m/Y',$data['post_time']).'<br />
			<a href="./voirprofil.php?m='.stripslashes(htmlspecialchars($data['membre_id'])).'&amp;action=consulter">'.$data['membre_pseudo'].'  </a>
			<a href="./voirtopic.php?t='.$data['topic_id'].'&amp;page='.$page.'#p_'.$data['post_id'].'">
			<img src="./images/ok.png" alt="go" style="width: 16px" /></a></td></tr>';

    }
    else {
         echo'<td class="nombremessages">Pas de message</td></tr>';
    }
	}
     //Cette variable stock le nombre de messages, on la met à jour
     $totaldesmessages += $data['forum_post'];

     //On ferme notre boucle et nos balises
} //fin de la boucle
$query->CloseCursor();
echo '</table></div>';

//Le pied de page ici :
echo'<div id="footer"><h2 class="footer" style="margin-top: 0px">Qui est en ligne ?</h2>';

//On compte les membres
$TotalDesMembres = $db->query('SELECT COUNT(*) FROM forum_membres')->fetchColumn();
$query->CloseCursor();	
$query = $db->query('SELECT membre_pseudo, membre_id FROM forum_membres ORDER BY membre_id DESC LIMIT 0, 1');
$data = $query->fetch();
$derniermembre = stripslashes(htmlspecialchars($data['membre_pseudo']));


echo'<p class="footer">Le total des messages du forum est <strong>'.$totaldesmessages.'</strong>.<br />';
echo'Le site et le forum comptent <strong>'.$TotalDesMembres.'</strong> membres.<br />';
echo'Le dernier membre est <a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">'.$derniermembre.'</a>.</p>';
$query->CloseCursor();
//Initialisation de la variable
$count_online = 0;

//Décompte des visiteurs
$count_visiteurs=$db->query('SELECT COUNT(*) AS nbr_visiteurs FROM forum_whosonline WHERE online_id = 0')->fetchColumn();
$query->CloseCursor();

//Décompte des membres
$texte_a_afficher = "<br />Liste des personnes en ligne : ";
$time_max = time() - (60 * 5);
$query=$db->prepare('SELECT membre_id, membre_pseudo 
FROM forum_whosonline
LEFT JOIN forum_membres ON online_id = membre_id
WHERE online_time > :timemax AND online_id <> 0');
$query->bindValue(':timemax',$time_max, PDO::PARAM_INT);
$query->execute();
$count_membres=0;
while ($data = $query->fetch())
{
	$count_membres ++;
	$texte_a_afficher .= '<a class="footer" href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
	'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a> ,';
}

$texte_a_afficher = substr($texte_a_afficher, 0, -1);
$count_online = $count_visiteurs + $count_membres;
echo'<p class="footer">Vous pouvez voir la liste complète des membres: <a href="./memberlist.php">ICI</a><br/>';
echo 'Il y a '.$count_online.' connectés ('.$count_membres.' membres et '.$count_visiteurs.' invités)';
echo $texte_a_afficher.'</p>';
$query->CloseCursor();
?>
</div>
</body>
</html>
