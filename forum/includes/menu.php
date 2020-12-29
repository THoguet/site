<div class="accueil">
	<?php if ($id==0) {
		echo'<a id="accueil" href="./connexion.php">Connexion</a>';
		echo'<br/><a id="accueil" href="./register.php">Inscription</a>';
	}
	else {
		//On compte le nombre de demande en cours et on met quelques liens
		$query=$db->prepare('SELECT COUNT(*) FROM forum_amis 
		WHERE ami_to = :id AND ami_confirm = :conf');
		$query->bindValue(':id',$id,PDO::PARAM_INT);
		$query->bindValue(':conf','0', PDO::PARAM_STR);
		$query->execute();
		$demande_ami=$query->fetchColumn();
		echo'<a id="accueil" href="./deconnexion.php">Deconnexion</a>';
		echo'<br/><a id="accueil" href="./amis.php">Amis('.$demande_ami.')</a>';
		$query->CloseCursor();
		$query=$db->prepare('SELECT COUNT(*) FROM forum_mp 
		WHERE mp_receveur = :id AND mp_lu = :conf');
		$query->bindValue(':id',$id,PDO::PARAM_INT);
		$query->bindValue(':conf','0', PDO::PARAM_STR);
		$query->execute();
		$mp_list=$query->fetchColumn();
		echo'<br/><a id="accueil" href="./messagesprives.php">MPs('.$mp_list.')</a>';
		$query->CloseCursor();
		echo'<br/><a id="accueil" href="./voirprofil.php?m='.$id.'&action=consulter">Profil</a>';
	}
	if (verif_auth(ADMIN)) {
		echo'<br/><a id="accueil" href="./admin.php">Administration</a>';
	}
	?>	
</div>