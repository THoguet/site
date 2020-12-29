<?php
try
{
$db = new PDO('mysql:host=localhost;dbname=forum', '***REMOVED***', '***REMOVED***');
}
catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}
$db->exec("SET CHARACTER SET utf8");
?>
