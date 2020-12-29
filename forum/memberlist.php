 <?php
session_start();
$titre="Liste des membres";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

//A partir d'ici, on va compter le nombre de members
//pour n'afficher que les 25 premiers
$query=$db->query('SELECT COUNT(*) AS nbr FROM forum_membres');
$data = $query->fetch();

$total = $data['nbr'] +1;
$query->CloseCursor();
$MembreParPage = 25;
$NombreDePages = ceil($total / $MembreParPage);
echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> 
<a href="./memberlist.php">Liste des membres</a></p>';

//Nombre de pages

$page = (isset($_GET['page']))?intval($_GET['page']):1;

//On affiche les pages 1-2-3, etc.
echo 'Page : ';
for ($i = 1 ; $i <= $NombreDePages ; $i++)
{
    if ($i == $page) //On ne met pas de lien sur la page actuelle
    {
        echo $i;
    }
    else
    {
        echo '<p><a href="memberlist.php?page='.$i.'">'.$i.'</a></p>';
    }
}
echo '</p>';

$premier = ($page - 1) * $MembreParPage;

//Le titre de la page
echo '<h1>Liste des membres</h1><br /><br />';
//Tri

$convert_order = array('membre_pseudo', 'membre_inscrit', 'membre_post', 'membre_derniere_visite'); 
$convert_tri = array('ASC', 'DESC');
//On récupère la valeur de s
if (isset ($_POST['s'])) $sort = $convert_order[$_POST['s']];
	else $sort = $convert_order[0];
		//On récupère la valeur de t
		if (isset ($_POST['t'])) $tri = $convert_tri[$_POST['t']];
			else $tri = $convert_tri[0];

?>
<form action="memberlist.php" method="post">
<p><label for="s">Trier par : </label>

<select name="s" id="s">
<option value="0" name="0">Pseudo</option>
<option value="1" name="1">Inscription</option>
<option value="2" name="2">Messages</option>
<option value="3" name="3">Dernière visite</option>
</select>

<select name="t" id="t">
<option value="0" name="0">Croissant</option>
<option value="1" name="1">Décroissant</option>
</select>
<input type="submit" value="Trier" /></p>
</form>
<?php
//Requête

$query = $db->prepare('SELECT membre_id, membre_rang, membre_pseudo, membre_inscrit, membre_post, membre_derniere_visite, online_id
FROM forum_membres
ORDER BY '.$sort.', online_id '.$tri.'
LIMIT :premier, :membreparpage');
$query->bindValue(':premier',$premier,PDO::PARAM_INT);
$query->bindValue(':membreparpage',$MembreParPage, PDO::PARAM_INT);
$query->execute();

if ($query->rowCount() >= 0)
{
?>
       <table>
       <tr>
       <th class="pseudo"><strong>Pseudo</strong></th>
       <th class="posts"><strong>Messages</strong></th>
       <th class="inscrit"><strong>Inscrit depuis le</strong></th>
       <th class="derniere_visite"><strong>Dernière visite</strong></th>
       <th class="rang"><strong>Rang</strong></th>
       <th><strong>Connecté</strong></th>             

       </tr>
       <?php
       //On lance la boucle
       while ($data = $query->fetch()) {
			if ($data['membre_rang'] == 0) {
				$rang = "<strong><style='color:#ff0000;'>Bannis</style></strong>";
			}
			elseif ($data['membre_rang'] == 1) {
				$rang = "<strong>Visiteur</strong>";
			}
			elseif ($data['membre_rang'] == 2) {
				$rang = "<strong>Inscrit</strong>";
			}
			elseif ($data['membre_rang'] == 3) {
				$rang = "<strong>KT</strong>";
			}
			elseif ($data['membre_rang'] == 4) {
				$rang = "<strong>Modérateur</strong>";
			}
			elseif ($data['membre_rang'] == 5) {
				$rang = "<strong>Administrateur</strong>";
			}
			echo '<tr><td>
			<a href="./voirprofil.php?m='.$data['membre_id'].'&amp;action=consulter">
			'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
			<td>'.$data['membre_post'].'</td>
			<td>'.date('d/m/Y',$data['membre_inscrit']).'</td>
			<td>'.date('d/m/Y',$data['membre_derniere_visite']).'</td>
			<td>'.$rang.'</td>';
			echo '</tr>';
		}
		$query->CloseCursor();
		?>
		</table>
		<?php
}
else //S'il n'y a pas de message
{
    echo'<p>Ce forum ne contient aucun membre actuellement</p>';
}
?>
</div>
</body></html>