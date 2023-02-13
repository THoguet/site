<?php
session_start();
$titre="Poster";
include("includes/identifiants.php");
include("includes/debut.php");

//On récupère la valeur de la variable action
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

// Si le membre n'est pas connecté, il est arrivé ici par erreur
if ($id==0) erreur(ERR_IS_NOT_CO);

switch($action) {

case "delete": //Si on veut supprimer le post
    //On récupère la valeur de p
    $post = (int) $_GET['p'];
    $query=$db->prepare('SELECT post_createur, post_texte, forum_id, topic_id, auth_modo
    FROM forum_post
    LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id
    WHERE post_id=:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    $topic = $data['topic_id'];
    $forum = $data['forum_id'];
	$poster = $data['post_createur'];

   
    //Ensuite on vérifie que le membre a le droit d'être ici 
    //(soit le créateur soit un modo/admin)
    if (!verif_auth($data['auth_modo']) && $poster != $id)
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_DELETE); 
    }
    else //Sinon ça roule et on continue
    {
        
        //Ici on vérifie plusieurs choses :
        //est-ce un premier post ? Dernier post ou post classique ?
 
        $query = $db->prepare('SELECT topic_first_post, topic_last_post FROM forum_topic
        WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data_post=$query->fetch();
               
               
               
        //On distingue maintenant les cas
        if ($data_post['topic_first_post']==$post) //Si le message est le premier
        {
 
            //Les autorisations ont changé !
            //Normal, seul un modo peut décider de supprimer tout un topic
            if (!verif_auth($data['auth_modo']))
            {
                erreur('ERR_AUTH_DELETE_TOPIC');
            }

            //Il faut s'assurer que ce n'est pas une erreur
 
            echo'<p>Vous avez choisi de supprimer un post.
            Cependant ce post est le premier du topic. Voulez vous supprimer le topic ? <br />
            <a href="./postok.php?action=delete_topic&amp;t='.$topic.'">oui</a> - <a href="./voirtopic.php?t='.$topic.'">non</a>
            </p>';
            $query->CloseCursor();                     
        }
        elseif ($data_post['topic_last_post']==$post)  //Si le message est le dernier
        {
 
            //On supprime le post
            $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
           
            //On modifie la valeur de topic_last_post pour cela on
            //récupère l'id du plus récent message de ce topic
            $query=$db->prepare('SELECT post_id FROM forum_post WHERE topic_id = :topic 
            ORDER BY post_id DESC LIMIT 0,1');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $data=$query->fetch();             
            $last_post_topic=$data['post_id'];
            $query->CloseCursor();

            //On fait de même pour forum_last_post_id
            $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :forum
            ORDER BY post_id DESC LIMIT 0,1');
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $data=$query->fetch();             
            $last_post_forum=$data['post_id'];
            $query->CloseCursor();   
                   
            //On met à jour la valeur de topic_last_post
			
            $query=$db->prepare('UPDATE forum_topic SET topic_last_post = :last
            WHERE topic_last_post = :post');
            $query->bindValue(':last',$last_post_topic,PDO::PARAM_INT);
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
 
            //On enlève 1 au nombre de messages du forum et on met à       
            //jour forum_last_post
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1, forum_last_post_id = :last
            WHERE forum_id = :forum');
            $query->bindValue(':last',$last_post_forum,PDO::PARAM_INT);
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor(); 
                        
            //On enlève 1 au nombre de messages du topic
            $query=$db->prepare('UPDATE forum_topic SET  topic_post = topic_post - 1
            WHERE topic_id = :topic');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor(); 
                       
            //On enlève 1 au nombre de messages du membre
            $query=$db->prepare('UPDATE forum_membres SET  membre_post = membre_post - 1
            WHERE membre_id = :id');
            $query->bindValue(':id',$poster,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();  
                        
            //Enfin le message
            echo'<p>Le message a bien été supprimé !<br />
            Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au topic<br />
            Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';
 
        }
        else // Si c'est un post classique
        {
 
            //On supprime le post
            $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
                       
            //On enlève 1 au nombre de messages du forum
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1  WHERE forum_id = :forum');
            $query->bindValue(':forum',$forum,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor(); 
                        
            //On enlève 1 au nombre de messages du topic
            $query=$db->prepare('UPDATE forum_topic SET  topic_post = topic_post - 1
            WHERE topic_id = :topic');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor(); 
                       
            //On enlève 1 au nombre de messages du membre
            $query=$db->prepare('UPDATE forum_membres SET  membre_post = membre_post - 1
            WHERE membre_id = :id');
            $query->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();  
                        
            //Enfin le message
            echo'<p>Le message a bien été supprimé !<br />
            Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au topic<br />
            Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';
        }
               
    } //Fin du else
break;

case "delete_topic":
    $topic = (int) $_GET['t'];
    $query=$db->prepare('SELECT forum_topic.forum_id, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id
    WHERE topic_id=:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    $forum = $data['forum_id'];
 
    //Ensuite on vérifie que le membre a le droit d'être ici 
    //c'est-à-dire si c'est un modo / admin
 
    if (!verif_auth($data['auth_modo']))
    {
        erreur('ERR_AUTH_DELETE_TOPIC');
    }
    else //Sinon ça roule et on continue
    {
        $query->CloseCursor();

        //On compte le nombre de post du topic
        $query=$db->prepare('SELECT topic_post FROM forum_topic WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        $nombrepost = $data['topic_post'] + 1;
        $query->CloseCursor();

        //On supprime le topic
        $query=$db->prepare('DELETE FROM forum_topic
        WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
       
        //On enlève le nombre de post posté par chaque membre dans le topic
        $query=$db->prepare('SELECT post_createur, COUNT(*) AS nombre_mess FROM forum_post
        WHERE topic_id = :topic GROUP BY post_createur');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();

        while($data = $query->fetch())
        {
            $query=$db->prepare('UPDATE forum_membres
            SET membre_post = membre_post - :mess
            WHERE membre_id = :id');
            $query->bindValue(':mess',$data['nombre_mess'],PDO::PARAM_INT);
            $query->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
            $query->execute();
        }

        $query->CloseCursor();       
        //Et on supprime les posts !
        $query=$db->prepare('DELETE FROM forum_post WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor(); 

        //Dernière chose, on récupère le dernier post du forum
        $query=$db->prepare('SELECT post_id FROM forum_post
        WHERE post_forum_id = :forum ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
		$last=(isset($data['post_id']))?$data['post_id']:0;
		
        //Ensuite on modifie certaines valeurs :
        $query=$db->prepare('UPDATE forum_forum
        SET forum_topic = forum_topic - 1, forum_post = forum_post - :nbr, forum_last_post_id = :id
        WHERE forum_id = :forum');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':id',$last,PDO::PARAM_INT);
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->execute(); 
        $query->CloseCursor();

        //Enfin le message
        echo'<p>Le topic a bien été supprimé !<br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';

    } //Fin du else
break;

case "lock": //Si on veut verrouiller le topic
    //On récupère la valeur de t
    $topic = (int) $_GET['t'];
    $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();

    //Ensuite on vérifie que le membre a le droit d'être ici
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_VERR);
    }  
    else //Sinon ça roule et on continue
    {
        //On met à jour la valeur de topic_locked
        $query->CloseCursor();
        $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
        $query->bindValue(':lock',1,PDO::PARAM_STR);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->CloseCursor();

        echo'<p>Le topic a bien été verrouillé ! <br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au topic<br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';
    }
break;
 
case "unlock": //Si on veut déverrouiller le topic
    //On récupère la valeur de t
        $topic = (int) $_GET['t'];
    $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
 
 //Ensuite on vérifie que le membre a le droit d'être ici
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_VERR);
    }  
    else //Sinon ça roule et on continue
    {
        //On met à jour la valeur de topic_locked
        $query->CloseCursor();
        $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
        $query->bindValue(':lock',0,PDO::PARAM_STR);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->CloseCursor();
 
        echo'<p>Le topic a bien été déverrouillé !<br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour retourner au topic<br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';
    }
break;

case "edit": //Si on veut éditer le post
    //On récupère la valeur de p
    $post = (int) $_GET['p'];
 
    //On récupère le message
    $message = $_POST['message'];

    //Ensuite on vérifie que le membre a le droit d'être ici (soit le créateur soit un modo/admin)
    $query=$db->prepare('SELECT post_createur, post_texte, post_time, topic_id, auth_modo
    FROM forum_post
    LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id
    WHERE post_id=:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data1 = $query->fetch();
    $topic = $data1['topic_id'];

    //On récupère la place du message dans le topic (pour le lien)
    $query = $db->prepare('SELECT COUNT(*) AS nbr FROM forum_post 
    WHERE topic_id = :topic AND post_time < '.$data1['post_time']);
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data2=$query->fetch();

    if (!verif_auth($data1['auth_modo'])&& $data1['post_createur'] != $id)
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_EDIT);    
    }
    else //Sinon ça roule et on continue
    {
        $query=$db->prepare('UPDATE forum_post SET post_texte =  :message WHERE post_id = :post');
        $query->bindValue(':message',$message,PDO::PARAM_STR);
        $query->bindValue(':post',$post,PDO::PARAM_INT);
        $query->execute();
        $nombreDeMessagesParPage = 15;
        $nbr_post = $data2['nbr']+1;
        $page = ceil($nbr_post / $nombreDeMessagesParPage);
        echo'<p>Votre message a bien été édité!<br /><br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum<br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'&amp;page='.$page.'#p_'.$post.'">ici</a> pour le voir</p>';
        $query->CloseCursor();
    }
break;
	
    //Premier cas : nouveau topic
    case "nouveautopic":
	$query=$db->prepare('SELECT auth_annonce, auth_topic FORM forum_forum WHERE forum_id = '.$_GET['f'].'');
	$query->execute();
	$data=$query->fetch();
	if (!verif_auth($data['auth_topic'])) erreur(ERR_AUTH_TOPIC);
	if (!verif_auth($data['auth_annonce']) && isset($_POST['mess'])) {
		exit('</div></body></html>');
	}
	$query->CloseCursor();
	
    //On passe le message dans une série de fonction
    $message = $_POST['message'];
    $mess = $_POST['mess'];
	
    //Pareil pour le titre
    $titre = $_POST['titre'];

    //ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable f
    $forum = (int) $_GET['f'];
    $temps = time();
	
    if (empty($message) || empty($titre))
    {
        echo'<p>Votre message ou votre titre est vide, 
        cliquez <a href="./poster.php?action=nouveautopic&amp;f='.$forum.'">ici</a> pour recommencer</p>';
    }
    else //Si jamais le message n'est pas vide
    {
        //On entre le topic dans la base de donnée en laissant
        //le champ topic_last_post à 0
        $query=$db->prepare('INSERT INTO forum_topic
        (forum_id, topic_titre, topic_createur, topic_vu, topic_time, topic_genre)
        VALUES(:forum, :titre, :id, 1, :temps, :mess)');
        $query->bindValue(':forum', $forum, PDO::PARAM_INT);
        $query->bindValue(':titre', $titre, PDO::PARAM_STR);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':temps', $temps, PDO::PARAM_INT);
        $query->bindValue(':mess', $mess, PDO::PARAM_STR);
        $query->execute();


        $nouveautopic = $db->lastInsertId(); //Notre fameuse fonction !
        $query->CloseCursor(); 

        //Puis on entre le message
        $query=$db->prepare('INSERT INTO forum_post
        (post_createur, post_texte, post_time, topic_id, post_forum_id)
        VALUES (:id, :mess, :temps, :nouveautopic, :forum)');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':mess', $message, PDO::PARAM_STR);
        $query->bindValue(':temps', $temps,PDO::PARAM_INT);
        $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
        $query->bindValue(':forum', $forum, PDO::PARAM_INT);
        $query->execute();


        $nouveaupost = $db->lastInsertId(); //Encore notre fameuse fonction !
        $query->CloseCursor(); 

        $query=$db->prepare('SELECT membre_id FROM forum_email WHERE forum_all = 1');
        $query->execute();
        $data=$query->fetchAll();
        $query->CloseCursor();
        foreach ($data as $i => $value) {
            $query=$db->prepare('SELECT membre_email, membre_pseudo FROM forum_membres WHERE membre_id = :id');
            $query->bindValue(':id', $data[$i]['membre_id'], PDO::PARAM_INT);
            $query->execute();
            $data2=$query->fetch();
            $query->CloseCursor();
            emailnotif($nouveautopic, $data2['membre_email'], $data2['membre_pseudo']);
            unset($data["membre_id"][$i]);
        }

        $query=$db->prepare('SELECT membre_id FROM forum_email WHERE topic_id = :id');
        $query->bindValue(':id', $nouveautopic, PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetchAll();
        $query->CloseCursor();
        foreach ($data as $i => $value) {
            $query=$db->prepare('SELECT membre_email, membre_pseudo FROM forum_membres WHERE membre_id = :id');
            $query->bindValue(':id', $data[$i]['membre_id'], PDO::PARAM_INT);
            $query->execute();
            $data2=$query->fetch();
            $query->CloseCursor();
            emailnotif($nouveautopic, $data2['membre_email'], $data2['membre_pseudo']);
            unset($data["membre_id"][$i]);
        }

        //Ici on update comme prévu la valeur de topic_last_post et de topic_first_post
        $query=$db->prepare('UPDATE forum_topic
        SET topic_last_post = :nouveaupost,
        topic_first_post = :nouveaupost
        WHERE topic_id = :nouveautopic');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
        $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        //Enfin on met à jour les tables forum_forum et forum_membres
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 ,forum_topic = forum_topic + 1, 
        forum_last_post_id = :nouveaupost
        WHERE forum_id = :forum');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);    
        $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
    
        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);    
        $query->execute();
        $query->CloseCursor();

        //Et un petit message
        echo'<p>Votre message a bien été ajouté!<br /><br />Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum<br />
        Cliquez <a href="./voirtopic.php?t='.$nouveautopic.'">ici</a> pour le voir</p>';
    }
    break; //Houra !

    //Deuxième cas : répondre
    case "repondre":
	$topic = (int) $_GET['t'];	
	$query=$db->prepare('SELECT forum_id, topic_post FROM forum_topic WHERE topic_id = :topic');
    $query->bindValue(':topic', $topic, PDO::PARAM_INT);    
    $query->execute();
    $data=$query->fetch();
	$topic_post = $data['topic_post'];
    $forum = $data['forum_id'];
	$query->CloseCursor();
    $query=$db->prepare('SELECT topic_locked, auth_post FROM forum_topic 
	LEFT JOIN forum_forum ON forum_forum.forum_id = :forum 
	WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    $query->execute(); 
    $data=$query->fetch();
    if ($data['topic_locked'] != 0)
    {
        erreur(ERR_TOPIC_VERR); //A vous d'afficher un message du genre : le topic est verrouillé qu'est ce que tu fous là !?
    }
    $query->CloseCursor();
    $message = $_POST['message'];
	
    //ici seulement, maintenant qu'on est sur qu'elle existe, on récupère la valeur de la variable t
    $topic = (int) $_GET['t'];
    $temps = time();

    if (empty($message))
    {
        echo'<p>Votre message est vide, cliquez <a href="./poster.php?action=repondre&amp;t='.$topic.'">ici</a> pour recommencer</p>';
    }
    else //Sinon, si le message n'est pas vide
    {
        //Puis on entre le message
        $query=$db->prepare('INSERT INTO forum_post
        (post_createur, post_texte, post_time, topic_id, post_forum_id)
        VALUES(:id,:mess,:temps,:topic,:forum)');
        $query->bindValue(':id', $id, PDO::PARAM_INT);   
        $query->bindValue(':mess', $message, PDO::PARAM_STR);  
        $query->bindValue(':temps', $temps, PDO::PARAM_INT);  
        $query->bindValue(':topic', $topic, PDO::PARAM_INT);   
        $query->bindValue(':forum', $forum, PDO::PARAM_INT); 
        $query->execute();

        $nouveaupost = $db->lastInsertId();
        $query->CloseCursor(); 

        //On change un peu la table forum_topic
        $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
        $query->bindValue(':topic', (int) $topic, PDO::PARAM_INT); 
        $query->execute();
        $query->CloseCursor(); 

        //Puis même combat sur les 2 autres tables
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 , forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
        $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT); 
        $query->execute();
        $query->CloseCursor(); 

        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT); 
        $query->execute();
        $query->CloseCursor(); 

        //Et un petit message
        $nombreDeMessagesParPage = 15;
        $nbr_post = $topic_post+1;
        $page = ceil($nbr_post / $nombreDeMessagesParPage);
        echo'<p>Votre message a bien été ajouté!<br /><br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum<br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'&amp;page='.$page.'#p_'.$nouveaupost.'">ici</a> pour le voir</p>';

        //EMAILS
        $topicemail = $topic.'&amp;page='.$page.'#p_'.$nouveaupost;
        $query=$db->prepare('SELECT membre_id FROM forum_email WHERE forum_all = 1');
        $query->execute();
        $data=$query->fetchAll();
        $query->CloseCursor();
        foreach ($data as $i => $value) {
            $query=$db->prepare('SELECT membre_email, membre_pseudo FROM forum_membres WHERE membre_id = :id');
            $query->bindValue(':id', $data[$i]['membre_id'], PDO::PARAM_INT);
            $query->execute();
            $data2=$query->fetch();
            $query->CloseCursor();
            emailnotif($topicemail, $data2['membre_email'], $data2['membre_pseudo']);
            unset($data["membre_id"][$i]);
        }

        $query=$db->prepare('SELECT membre_id FROM forum_email WHERE topic_id = :id');
        $query->bindValue(':id', $topic, PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetchAll();
        $query->CloseCursor();
        foreach ($data as $i => $value) {
            $query=$db->prepare('SELECT membre_email, membre_pseudo FROM forum_membres WHERE membre_id = :id');
            $query->bindValue(':id', $data[$i]['membre_id'], PDO::PARAM_INT);
            $query->execute();
            $data2=$query->fetch();
            $query->CloseCursor();
            emailnotif($topicemail, $data2['membre_email'], $data2['membre_pseudo']);
            unset($data["membre_id"][$i]);
        }
    }//Fin du else
    break;

case "deplacer":

    $topic = (int) $_GET['t'];
    $query= $db->prepare('SELECT forum_topic.forum_id, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum 
    ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id =:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    
    if (!verif_auth($data['auth_modo']))
    {
        // Si cette condition n'est pas remplie ça va barder :o
        erreur(ERR_AUTH_MOVE);
    }
    else //Sinon ça roule et on continue
    {
        $query->CloseCursor();
        $destination = (int) $_POST['dest'];
        $origine = (int) $_POST['from'];

        //On déplace le topic
        $query=$db->prepare('UPDATE forum_topic SET forum_id = :dest WHERE topic_id = :topic');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor(); 
 
        //On déplace les posts
        $query=$db->prepare('UPDATE forum_post SET post_forum_id = :dest
        WHERE topic_id = :topic');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute(); 
        $query->CloseCursor();     
        //On s'occupe d'ajouter / enlever les nombres de post / topic aux
        //forum d'origine et de destination
        //Pour cela on compte le nombre de post déplacé
               
        
        $query=$db->prepare('SELECT COUNT(*) AS nombre_post
        FROM forum_post WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();    
        $data = $query->fetch();
        $nombrepost = $data['nombre_post'];
        $query->CloseCursor();       
                
        //Il faut également vérifier qu'on a pas déplacé un post qui été
        //l'ancien premier post du forum (champ forum_last_post_id)
 
        $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :desti
        ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':desti',$destination,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();       
        $last=(isset($data['post_id']))?$data['post_id']:0;
        $query->CloseCursor();
        
        //Puis on met à jour le forum d'origine
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - :nbr,
        forum_topic = forum_topic - 1,
        forum_last_post_id = :last
        WHERE forum_id = :ori');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':ori',$origine,PDO::PARAM_INT);
        $query->bindValue(':last',$last,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        //Avant de mettre à jour le forum de destination il faut
        //vérifier la valeur de forum_last_post_id
        $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :dest
        ORDER BY post_id DESC LIMIT 0,1');
        $query->bindValue(':dest',$destination,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        $last_post=$data['post_id'];
        $query->CloseCursor();

        //Et on met à jour enfin !
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + :nbr,
        forum_topic = forum_topic + 1,
        forum_last_post_id = :last
        WHERE forum_id = :forum');
        $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
        $query->bindValue(':last',$last,PDO::PARAM_INT);
        $query->bindValue(':forum',$destination,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        //C'est gagné ! On affiche le message
        echo'<p>Le topic a bien été déplacé <br />
        Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a> pour revenir au topic<br />
        Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du forum</p>';
    }
break;
case "repondremp": //Si on veut répondre

    //On récupère le titre et le message
    $message = $_POST['message'];
    $titre = $_POST['titre'];
    $temps = time();

    //On récupère la valeur de l'id du destinataire
    $dest = (int) $_GET['dest'];

    //Enfin on peut envoyer le message

    $query=$db->prepare('INSERT INTO forum_mp
    (mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu)
    VALUES(:id, :dest, :titre, :txt, :tps, 0)'); 
    $query->bindValue(':id',$id,PDO::PARAM_INT);   
    $query->bindValue(':dest',$dest,PDO::PARAM_INT);   
    $query->bindValue(':titre',$titre,PDO::PARAM_STR);   
    $query->bindValue(':txt',$message,PDO::PARAM_STR);   
    $query->bindValue(':tps',$temps,PDO::PARAM_INT);   
    $query->execute();
    $query->CloseCursor(); 

    echo'<p>Votre message a bien été envoyé!<br />
    <br />Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du   
    forum<br />
    <br />Cliquez <a href="./messagesprives.php">ici</a> pour retourner
    à la messagerie</p>';

    break;

    case "nouveaump": //On envoie un nouveau mp

    //On récupère le titre et le message
    $message = $_POST['message'];
    $titre = $_POST['titre'];
    $temps = time();
    $dest = $_POST['to'];

    //On récupère la valeur de l'id du destinataire
    //Il faut déja vérifier le nom

    $query=$db->prepare('SELECT membre_id FROM forum_membres
    WHERE LOWER(membre_pseudo) = :dest');
    $query->bindValue(':dest',strtolower($dest),PDO::PARAM_STR);
    $query->execute();
    if($data = $query->fetch())
    {
        $query=$db->prepare('INSERT INTO forum_mp
        (mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu)
        VALUES(:id, :dest, :titre, :txt, :tps, :lu)'); 
        $query->bindValue(':id',$id,PDO::PARAM_INT);   
        $query->bindValue(':dest',(int) $data['membre_id'],PDO::PARAM_INT);   
        $query->bindValue(':titre',$titre,PDO::PARAM_STR);   
        $query->bindValue(':txt',$message,PDO::PARAM_STR);   
        $query->bindValue(':tps',$temps,PDO::PARAM_INT);   
        $query->bindValue(':lu','0',PDO::PARAM_STR);   
        $query->execute();
        $query->CloseCursor(); 

        

       echo'<p>Votre message a bien été envoyé!
       <br /><br />Cliquez <a href="./index.php">ici</a> pour revenir à l\'index du
       forum<br />
       <br />Cliquez <a href="./messagesprives.php">ici</a> pour retourner à
       la messagerie</p>';
    }
    //Sinon l'utilisateur n'existe pas !
    else
    {
        echo'<p>Désolé ce membre n existe pas, veuillez vérifier et
        réessayez à nouveau.</p>';
    }
    break;
    case "supprimer":
       
    //On récupère la valeur de l'id
    $id_mess = (int) $_GET['id'];
    //Il faut vérifier que le membre est bien celui qui a reçu le message
    $query=$db->prepare('SELECT mp_receveur
    FROM forum_mp WHERE mp_id = :id');
    $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
    $query->execute();
    $data = $query->fetch();
    //Sinon la sanction est terrible :p
    if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
    $query->CloseCursor(); 

    //2 cas pour cette partie : on est sûr de supprimer ou alors on ne l'est pas
    $sur = (int) $_GET['sur'];
    //Pas encore certain
    if ($sur == 0)
    {
    echo'<p>Etes-vous certain de vouloir supprimer ce message ?<br />
    <a href="./messagesprives.php?action=supprimer&amp;id='.$id_mess.'&amp;sur=1">
    Oui</a> - <a href="./messagesprives.php">Non</a></p>';
    }
    //Certain
    else
    {
        $query=$db->prepare('DELETE from forum_mp WHERE mp_id = :id');
        $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor(); 
        echo'<p>Le message a bien été supprimé.<br />
        Cliquez <a href="./messagesprives.php">ici</a> pour revenir à la boite
        de messagerie.</p>';
    }

    break;
	case "autorep":
	$topic = (int) $_GET['t'];
	$query=$db->prepare('SELECT forum_topic.forum_id, auth_modo
	FROM forum_topic
	LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
	WHERE topic_id = :topic');
	$query->bindValue(':topic',$topic,PDO::PARAM_INT);
	$query->execute();
	$data=$query->fetch();
	$forum=$data['forum_id'];
	if (!verif_auth($data['auth_modo']))
	{
		erreur(ERR_AUTH_MODO);
	}
	$query->CloseCursor();
	$rep = (int) $_POST['rep'];
	$query=$db->prepare('SELECT automess_mess FROM forum_automess 
	WHERE automess_id = :rep');
	$query->bindValue(':rep',$rep,PDO::PARAM_INT);
	$query->execute();
	$data = $query->fetch();
	$message = $data['automess_mess'];
	$query->CloseCursor();
	$query=$db->prepare('INSERT INTO forum_post
	(post_createur, post_texte, post_time, topic_id, post_forum_id)
	VALUES(:id,:mess,:temps,:topic,:forum)');
	$query->bindValue(':id', $id, PDO::PARAM_INT);   
	$query->bindValue(':mess', $message, PDO::PARAM_STR);  
	$query->bindValue(':temps', time(), PDO::PARAM_INT);  
	$query->bindValue(':topic', $topic, PDO::PARAM_INT);   
	$query->bindValue(':forum', $forum, PDO::PARAM_INT); 
	$query->execute();

	$nouveaupost = $db->lastInsertId();
	$query->CloseCursor(); 

	//On change un peu la table forum_topic
	$query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
	$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
	$query->bindValue(':topic', (int) $topic, PDO::PARAM_INT); 
	$query->execute();
	$query->CloseCursor(); 

	//Puis même combat sur les 2 autres tables
	$query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 , forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
	$query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);   
	$query->bindValue(':forum', (int) $forum, PDO::PARAM_INT); 
	$query->execute();
	$query->CloseCursor(); 

	$query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
	$query->bindValue(':id', $id, PDO::PARAM_INT); 
	$query->execute();
	$query->CloseCursor();
	$query=$db->prepare('UPDATE forum_topic
	SET topic_locked = :lock
	WHERE topic_id = :topic');
	$query->bindValue(':lock','1',PDO::PARAM_STR);
	$query->bindValue(':topic',$topic,PDO::PARAM_INT);
	$query->execute();
	$query->CloseCursor();

	echo '<p>La réponse automatique a bien été envoyée ! <br />
	Cliquez <a href="./voirtopic.php?t='.$topic.'">ici</a>
	pour revenir au topic<br />
	Cliquez <a href="./index.php">ici</a>
	pour revenir à l\'index du forum</p>';

	break;	

    default;
    echo'<p>Cette action est impossible</p>';
} //Fin du Switch
?>
</div>
</body>
</html>
