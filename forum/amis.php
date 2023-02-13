<?php
session_start();
$titre="Gestion des amis";
include("includes/identifiants.php");
include("includes/debut.php");

$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'';

echo '<p><i>Vous êtes ici</i> : <a href="./index.php">Index du forum</a> --> <a href="./amis.php">Gestion des amis</a>';
if ($id==0) erreur(ERR_IS_NOT_CO);
 
//Le titre 
echo '<h1>Gestion des amis</h1>';
switch($action)
{
    case "add": //On veut ajouter un ami
    if (!isset($_POST['pseudo']))
    {
    echo '<form action="amis.php?action=add" method="post">
    <p><label for="pseudo">Entrez le pseudo</label>
    <input type="text" name="pseudo" id="pseudo" />
    <input type="submit" value="Envoyer" />
    </p></form>';
    }
    else
    {
        $pseudo_d = $_POST['pseudo'];
        //On vérifie que le pseudo renvoit bien quelque chose :o

        $query=$db->prepare('SELECT membre_id, COUNT(*) AS nbr FROM forum_membres 
        WHERE LOWER(membre_pseudo) = :pseudo GROUP BY membre_pseudo');
        $query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
        $query->execute();
        $data = $query->fetch();
        $pseudo_exist = $data['nbr'];
        $i = 0;
        $id_to=$data['membre_id'];
        if(!$pseudo_exist)
        {
            echo '<p>Ce membre ne semble pas exister<br />
            Cliquez <a href="./amis.php?action=add">ici</a> pour réessayer</p>';
            $i++;
        }
        $query->CloseCursor();
        $query = $db->prepare('SELECT COUNT(*) AS nbr FROM forum_amis 
        WHERE ami_from = :id AND ami_to = :id_to
        OR ami_from = :id AND ami_to = :id_to');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':id_to', $id_to, PDO::PARAM_INT);
        $query->execute();
        $deja_ami=$query->fetchColumn();
        $query->CloseCursor();

        if ($deja_ami != 0)
        {
            echo '<p>Ce membre fait déjà parti de vos amis ou a déjà proposé son amitié :p<br />
            Cliquez <a href="./amis.php?action=add">ici</a> pour réessayer</p>';
            $i++;
        }
        if ($id_to == $id)
        {
            echo '<p>Vous ne pouvez pas vous ajouter vous même<br />
            Cliquez <a href="./amis.php?action=add">ici</a> pour réessayer</p>';
            $i++;
        }
        if ($i == 0)
        {
            $query=$db->prepare('INSERT INTO forum_amis (ami_from, ami_to, ami_confirm, ami_date)
            VALUES(:id, :id_to, :conf, :temps)');
            $query->bindValue(':id',$id,PDO::PARAM_INT);
            $query->bindValue(':id_to', $id_to, PDO::PARAM_INT);
            $query->bindValue(':conf','0',PDO::PARAM_STR);
            $query->bindValue(':temps', time(), PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
            echo '<p><a href="/voirprofil.php?m='.$data['membre_id'].'">'.stripslashes(htmlspecialchars($pseudo_d)).'</a> 
            a bien été ajouté à vos amis, il faut toutefois qu\'il donne son accord.<br />
            Cliquez <a href="./index.php">ici</a> pour retourner à l index du forum<br />
            Cliquez <a href="./amis.php">ici</a> pour retourner à la page de gestion des amis</p>';
        }
    }

case "check":
    $add = (isset($_GET['add']))?htmlspecialchars($_GET['add']):0;
    if (empty($add))
    {
        $query = $db->prepare('SELECT ami_from, ami_date, membre_pseudo FROM forum_amis
        LEFT JOIN forum_membres ON membre_id = ami_from
        WHERE ami_to = :id AND ami_confirm = :conf
        ORDER BY ami_date DESC');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':conf','0',PDO::PARAM_STR); 
        $query->execute();

        echo '<table><tr>
        <th class="pseudo"><strong>Pseudo</strong></th>
        <th class="inscrit"><strong>Date d\'ajout</strong></th>
        <th><strong>Action</strong></th></tr>';

        if ($query->rowCount() == 0)
        {
            echo '<td colspan="3" align="center">Vous n\'avez aucune proposition</td>';
        }
        while ($data = $query->fetch())
        {
            echo '<tr><td><a href="./voirprofil.php?m='.$data['ami_from'].'&amp;action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
            <td class="inscrit">'.date('d/m/Y',$data['ami_date']).'</td>
            <td><a href="./amis.php?action=check&amp;add=ok&amp;m='.$data['ami_from'].'">Accepter</a> - 
            <a href="./amis.php?action=delete&amp;m='.$data['ami_from'].'">Refuser</a> 
            </td></tr>';
        }
        $query->CloseCursor();
    }
    else
    {
        $membre = (int) $_GET['m'];
        $query = $db->prepare('UPDATE forum_amis SET ami_confirm = :conf 
        WHERE ami_from = :membre AND ami_to = :id');
        $query->bindValue(':conf','1',PDO::PARAM_STR);
        $query->bindValue(':membre',$membre,PDO::PARAM_INT);
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();
        echo '<p>Le membre a bien été ajouté à votre liste d\'ami<br />
        Cliquez <a href="./amis.php">ici</a> pour retourner à la liste des amis';
    }
break;
case "delete":
    $membre = (int) $_GET['m'];
    if (!isset($_GET['ok']))
    {
        echo '<p>Etes vous certain de vouloir supprimer ce membre ?<br />
        <a href="./amis.php?action=delete&amp;ok=ok&amp;m='.$membre.'">oui</a> - <a href="./amis.php">non</a></p>';
    }
    else
    {
        $query = $db->prepare('DELETE FROM forum_amis WHERE ami_from = :membre AND ami_to = :id');
        $query->bindValue(':membre',$membre,PDO::PARAM_INT);
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();

        $query = $db->prepare('DELETE FROM forum_amis WHERE ami_to = :membre AND ami_from = :id');
        $query->bindValue(':membre',$membre,PDO::PARAM_INT);
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $query->closeCursor();

        echo '<p>Membre correctement supprimé :D <br />
        Cliquez <a href="./amis.php">ici</a> pour retourner à la liste des amis</p>';
    }
break;
default:

    $query = $db->prepare('SELECT (ami_from + ami_to - :id) AS ami_id, ami_date, membre_pseudo, online_id 
    FROM forum_amis
    LEFT JOIN forum_membres ON membre_id = (ami_from + ami_to - :id)
    LEFT JOIN forum_whosonline ON online_id = membre_id
    WHERE (ami_from = :id OR ami_to = :id) AND ami_confirm = :conf ORDER BY membre_pseudo');
    $query->bindValue(':id',$id,PDO::PARAM_INT);       
    $query->bindValue(':conf','1',PDO::PARAM_STR);
    $query->execute();
    
    echo '<table><tr>
    <th class="pseudo"><strong>Pseudo</strong></th>
    <th class="inscrit"><strong>Date d\'ajout</strong></th>
    <th><strong>Action</strong></th>
    <th><strong>Connecté</strong></th></tr>';

    if ($query->rowCount() == 0)
    {
        echo '<td colspan="4" align="center">Vous n\'avez aucun ami pour l\'instant</td>';
    }
    while ($data = $query->fetch())
    {
        echo '<tr><td><a href="./voirprofil.php?m='.$data['ami_id'].'&amp;action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
        <td class="inscrit">'.date('d/m/Y',$data['ami_date']).'</td>
        <td><a href="./messagesprives.php?action=repondre&amp;dest='.$data['ami_id'].'">Envoyer un MP</a><br />
        <a href="./amis.php?action=delete&m='.$data['ami_id'].'">Supprimer</a></td>';
        if (!empty($data['online_id'])) echo '<td>Oui</td>'; else echo '<td>Non</td>';
        echo '</tr>';
    }
    echo '</table>';
    $query->CloseCursor();

    //On compte le nombre de demande en cours et on met quelques liens
    $query=$db->prepare('SELECT COUNT(*) FROM forum_amis 
    WHERE ami_to = :id AND ami_confirm = :conf');
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->bindValue(':conf','0', PDO::PARAM_STR);
    $query->execute();
    $demande_ami=$query->fetchColumn();

    //Cette ligne va permettre d'afficher 0 plutôt qu'un vide
    if (empty($demande_ami)) $demande_ami=0;
    echo '<br /><ul>
    <li><a href="./amis.php?action=add">Ajouter un ami</a></li>
    <li><a href="./amis.php?action=check">Voir les demandes d\'ajout ('.$demande_ami.')</a></li></ul>';
break;
}

?>
</div>
</body></html>