<?php
//Cette fonction doit être appelée avant tout code html
session_start();

//On donne ensuite un titre à la page, puis on appelle notre fichier debut.php
$titre = "Index du forum";
include("includes/identifiants.php");
include("includes/debut.php");

$topic = (isset($_GET['t']))?(int) $_GET['t']:'';
$all = (isset($_GET['all']))?(int) $_GET['all']:'';
$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'';

if ($id==0) erreur(ERR_IS_NOT_CO);

if (isset($action)) {
	if ($action == "add") {
		if (isset($_GET['t'])) {
			$query=$db->prepare('INSERT INTO forum_email
	        (topic_id, forum_all, membre_id)
	        VALUES(:topicid, NULL, :id)');
	        $query->bindValue(':topicid', $topic, PDO::PARAM_INT);
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
	    }
	    elseif (isset($_GET['all'])) {
			$query=$db->prepare('INSERT INTO forum_email
	        (topic_id, forum_all, membre_id)
	        VALUES(NULL, 1, :id)');
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
	    }
	}
	elseif ($action == "delete") {
		if (isset($_GET['t']) AND isset($_GET['all'])) {
			$query=$db->prepare('DELETE FROM `forum_email` WHERE `topic_id` = :topicid AND `membre_id` = :id');
	        $query->bindValue(':topicid', $topic, PDO::PARAM_INT);
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
			$query=$db->prepare('DELETE FROM `forum_email` WHERE `membre_id` = :id AND `forum_all` = 1');
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
		}
		elseif (isset($_GET['t'])) {
			$query=$db->prepare('DELETE FROM `forum_email` WHERE `topic_id` = :topicid AND `membre_id` = :id');
	        $query->bindValue(':topicid', $topic, PDO::PARAM_INT);
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
	    }
	    elseif (isset($_GET['all'])) {
			$query=$db->prepare('DELETE FROM `forum_email` WHERE `membre_id` = :id AND `forum_all` = 1');
	        $query->bindValue(':id', $id, PDO::PARAM_INT);
	        $query->execute();
		}
	}
}
if (isset($_SERVER['HTTP_REFERER'])) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
	exit;
}
else {
	echo'<p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p>';
}
?>