<?php
session_start();
$titre="Administration";
$balises = true;
include("includes/debut.php");
// Numéro du port d'écoute de l'ordinateur cible. Habituellement tout port compris entre 1 et 50000 fonctionnera mais les ports 7 ou 9 sont à privilégier.
$socket_number = "9";
// Adresse MAC du périphérique réseau de l'ordinateur cible
$mac_addy = "**REMOVED**";
// Adresse IP de l'ordinateur cible (ip publique de votre routeur/modem). Entrez le nom de domaine si vous en utilisez-un (tel que Dynamic DNS/IP).
$ip_addy = "**REMOVED**";

## fonction ##
function WakeOnLan($addr, $mac,$socket_number) {
  $addr_byte = explode(':', $mac);
  $hw_addr = '';
  for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
  // send it to the broadcast address using UDP
  // SQL_BROADCAST option isn't help!!
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($s == false) {
	echo "Erreur lors de la création du socket!\n";
	echo "Code d'erreur: '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
	return FALSE;
	}
  else {
	// setting a broadcast option to socket:
	$opt_ret = socket_set_option($s, 1, 6, TRUE);
	if($opt_ret <0) {
	  echo "setsockopt() a échoué, erreur: " . strerror($opt_ret) . "\n";
	  return FALSE;
	  }
	if(socket_sendto($s, $msg, strlen($msg), 0, $addr, $socket_number)) {
	  echo "Paquet magique émis avec succès!";
	  socket_close($s);
	  return TRUE;
	  }
	else {
	  echo "L'envoie du paquet magique a échoué!";
	  return FALSE;
	  }
	}
  }

if (isset($_GET['mdp'])) {
	if ($_GET['mdp'] == '**REMOVED**') {
		flush();
		# envoi du paquet magique
		WakeOnLan($ip_addy, $mac_addy,$socket_number);
	}
}
else {
	echo "Erreur !";
}
?>
