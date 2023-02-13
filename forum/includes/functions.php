<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/var/www/phpmailer/src/Exception.php';
require '/var/www/phpmailer/src/PHPMailer.php';
require '/var/www/phpmailer/src/SMTP.php';

function erreur($err='') {
   $mess=($err!='')? $err:'Une erreur inconnue s\'est produite';
   exit('<p>'.$mess.'</p>
   <p>Cliquez <a href="./index.php">ici</a> pour revenir à la page d\'accueil</p></div></body></html>');
}

function move_avatar($avatar) {
    $extension_upload = strtolower(substr(  strrchr($avatar['name'], '.')  ,1));
    $name = time();
    $nomavatar = str_replace(' ','',$name).".".$extension_upload;
    $name = "./images/avatars/".str_replace(' ','',$name).".".$extension_upload;
    move_uploaded_file($avatar['tmp_name'],$name);
    return $nomavatar;
}

function verif_auth($auth_necessaire) {
	$level=(isset($_SESSION['level']))?$_SESSION['level']:1;
	return ($auth_necessaire <= intval($level));
}

//Fonction listant les pages
function get_list_page($page, $nb_page, $link, $nb = 2){
	$list_page = array();
	for ($i=1; $i <= $nb_page; $i++){
		if (($i < $nb) OR ($i > $nb_page - $nb) OR (($i < $page + $nb) AND ($i > $page -$nb)))
			$list_page[] = ($i==$page)?'<strong>'.$i.'</strong>':'<a href="'.$link.'&amp;page='.$i.'">'.$i.'</a>'; 
		else{
		if ($i >= $nb AND $i <= $page - $nb)
			$i = $page - $nb;
		elseif ($i >= $page + $nb AND $i <= $nb_page - $nb)
			$i = $nb_page - $nb;
			$list_page[] = '...';
		}
	}
	$print= implode('-', $list_page);
	return $print;
}

function getrandom($taille) {
	$str = "123456789azertyuiopqsdfghjklmwxcvbn";
	$randstr = substr(str_shuffle($str), 0, $taille);
	return $randstr;
}

function verifemail($uid, $email, $pseudo) {
	$mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.ionos.fr';
    $mail->SMTPAuth = true;
    $mail->Username = 'botmail@mail.nessar.fr';
    $mail->Password = '**REMOVED**';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('botmail@mail.nessar.fr', 'nessar.fr');
    $mail->addAddress($email, $pseudo);
    $mail->addReplyTo('nessar@mail.nessar.fr', 'Information');
    $mail->Subject = 'Verification de l\'E-mail';
    $mail->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Verifiez votre E-mail</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		</head>
		<body style="margin: 0; padding: 0;">
		    <table border="0" cellpadding="0" cellspacing="0" width="100%"> 
		        <tr>
		            <td style="padding: 10px 0 30px 0;">
		                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
		                    <tr>
		                        <td align="center" bgcolor="#70bbd9" style="padding: 40px 0 30px 0; color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">
		                            <img src="http://ktfaction.fr/image/h1.gif" alt="Verifiez votre E-mail" width="300" height="230" style="display: block;" />
		                        </td>
		                    </tr>
		                    <tr>
		                        <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
		                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
		                                <tr>
		                                    <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
		                                        <b>Verifiez votre E-mail</b>
		                                    </td>
		                                </tr>
		                                <tr>
		                                    <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
		                                        Cet E-mail vous a été envoyé car votre adresse E-mail a été renseigné sur ce site: <b><a style ="color: black;" href="http://ktfaction.fr">ktfaction.fr</a></b>, merci de l\'interet porté a mon site ! Pour verifer votre E-mail, cliquez sur ce <b><a style ="color: black;"href="https://ktfaction.fr/forum/verifemail.php?uid='.$uid.'">lien</a></b>
		                                    </td>
		                                </tr>
		                            </table>
		                        </td>
		                    </tr>
		                    <tr>
		                        <td bgcolor="#ee4c50" style="padding: 30px 30px 30px 30px;">
		                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
		                                <tr>
		                                    <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
		                                        &reg; Nessar, France 2019<br/>
		                                    </td>
		                                    <td align="right" width="25%">
		                                        <table border="0" cellpadding="0" cellspacing="0">
		                                            <tr>
		                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
		                                                    <a href="http://www.twitter.com/nessarhd" style="color: #ffffff;">
		                                                        <img src="http://ktfaction.fr/image/tw.gif" alt="Twitter" width="38" height="38" style="display: block;" border="0" />
		                                                    </a>
		                                                </td>
		                                                <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
		                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
		                                                    <a href="http://twitch.tv/nessarhd" style="color: #ffffff;">
		                                                        <img src="http://ktfaction.fr/image/fb.gif" alt="Twitch" width="38" height="38" style="display: block;" border="0" />
		                                                    </a>
		                                                </td>
		                                            </tr>
		                                        </table>
		                                    </td>
		                                </tr>
		                            </table>
		                        </td>
		                    </tr>
		                </table>
		            </td>
		        </tr>
		    </table>
		</body>
		</html>';
    $mail->AltBody = 'Cliquez sur ce lien pour verifier votre E-mail: http://ktfaction.fr/forum/verifemail.php?uid='.$uid.'';
    $mail->send();
}


function emailnotif($topic, $email, $pseudo) {
	$mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'smtp.ionos.fr';
    $mail->SMTPAuth = true;
    $mail->Username = 'botmail@mail.nessar.fr';
    $mail->Password = '**REMOVED**';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('botmail@mail.nessar.fr', 'nessar.fr');
    $mail->addAddress($email, $pseudo);
    $mail->addReplyTo('nessar@mail.nessar.fr', 'Information');
    $mail->Subject = 'Il y a du nouveau sur le forum !';
    $mail->Body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Il y a du nouveau sur le forum !</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
		</head>
		<body style="margin: 0; padding: 0;">
		    <table border="0" cellpadding="0" cellspacing="0" width="100%"> 
		        <tr>
		            <td style="padding: 10px 0 30px 0;">
		                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
		                    <tr>
		                        <td align="center" bgcolor="#70bbd9" style="padding: 40px 0 30px 0; color: #153643; font-size: 28px; font-weight: bold; font-family: Arial, sans-serif;">
		                            <img src="http://ktfaction.fr/image/h2.gif" alt="Nouveau message !" width="300" height="230" style="display: block;" />
		                        </td>
		                    </tr>
		                    <tr>
		                        <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
		                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
		                                <tr>
		                                    <td style="color: #153643; font-family: Arial, sans-serif; font-size: 24px;">
		                                        <b>Il y a du nouveau sur le forum !</b>
		                                    </td>
		                                </tr>
		                                <tr>
		                                    <td style="padding: 20px 0 30px 0; color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 20px;">
												Bonjour '.$pseudo.', cet E-mail vous a été envoyé car vous êtes abonné a <b><a style ="color: black;" href="http://ktfaction.fr">ktfaction.fr</a></b>, merci de l\'interet porté a mon site ! Pour voir de quoi il s\'agit, cliquez sur ce <b><a style ="color: black;"href="http://ktfaction.fr/forum/voirtopic.php?t='.$topic.'">lien</a></b>. Merci et a bientot !
		                                    </td>
		                                </tr>
		                            </table>
		                        </td>
		                    </tr>
		                    <tr>
		                        <td bgcolor="#ee4c50" style="padding: 30px 30px 30px 30px;">
		                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
		                                <tr>
		                                    <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;" width="75%">
		                                        &reg; Nessar, France 2019<br/>
		                                    </td>
		                                    <td align="right" width="25%">
		                                        <table border="0" cellpadding="0" cellspacing="0">
		                                            <tr>
		                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
		                                                    <a href="http://www.twitter.com/nessarhd" style="color: #ffffff;">
		                                                        <img src="http://ktfaction.fr/image/tw.gif" alt="Twitter" width="38" height="38" style="display: block;" border="0" />
		                                                    </a>
		                                                </td>
		                                                <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
		                                                <td style="font-family: Arial, sans-serif; font-size: 12px; font-weight: bold;">
		                                                    <a href="http://twitch.tv/nessarhd" style="color: #ffffff;">
		                                                        <img src="http://ktfaction.fr/image/fb.gif" alt="Twitch" width="38" height="38" style="display: block;" border="0" />
		                                                    </a>
		                                                </td>
		                                            </tr>
		                                        </table>
                                                <tr>
                                                	<td style="color: #AF2F33; font-family: Arial, sans-serif; font-size: 10px;" width="75%">
                                                    	Pour vous désinscrire cliquez <a style="color:#AF2F33" href="http://ktfaction.fr/forum/notifemail.php?action=delete&all=1&t='.$topic.'">ici</a><br/>
                                                	</td>
                                                </tr>
		                                    </td>
		                                </tr>
		                            </table>
		                        </td>
		                    </tr>
		                </table>
		            </td>
		        </tr>
		    </table>
		</body>
		</html>';
    $mail->AltBody = 'Il y a du nouveau sur le forum ! Cliquez sur ce lien pour aller voir: http://ktfaction.fr/forum/voirtopic.php?t='.$topic.' . Pour vous désinscrire cliquez sur ce lien http://ktfaction.fr/forum/notifemail.php?action=delete&all=1&t='.$topic;
    $mail->send();
}
?>
