<?php

if (!isset($_GET['url'])) {
	exit('Please specify a URL to open.');
}

require_once('lib/Database.class.php');
require_once('lib/Section.class.php');

function processNodes($db, $nodes)
{
	$i = 0;
	foreach ($nodes as $node) {
		$i++;
		if ($i % 2 === 0) continue;
		$s = new Section($node);
		$sections[] = $s;
		$s->save($db);
	}
}

$db = new Database();

error_reporting(E_ALL ^ E_WARNING);

$url = urldecode($_GET['url']);
$sections = array();

$dom = new DOMDocument();
$dom->loadHTML(file_get_contents($url));
$finder = new DomXPath($dom);

processNodes($db, $finder->query("//tr[contains(@class, 'odd')]"));
processNodes($db, $finder->query("//tr[contains(@class, 'even')]"));

echo json_encode($sections);

?>
