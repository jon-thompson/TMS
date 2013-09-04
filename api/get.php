<?php

require_once('lib/Database.class.php');

if(!isset($_GET['term'])) {
	exit(json_encode(array()));
}

$term = $_GET['term'];

$db = new Database();

$sections = array();
$results = $db->execute("SELECT * FROM section WHERE Term LIKE ?", array($term));

foreach ($results as $result) {
	$sections[] = json_decode($result['Object']);
}

exit(json_encode($sections));

?>
