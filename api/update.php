<?php

require_once('lib/Database.class.php');
require_once('lib/Section.class.php');

define('ROOT', 'https://duapp2.drexel.edu');
define('APP_URL', 'https://duapp2.drexel.edu/webtms_du/app');
define('DEBUG', TRUE);
define('MAX_TIME_SECONDS', 300);

set_time_limit(MAX_TIME_SECONDS);
error_reporting(E_ALL ^ E_WARNING);

function output($msg)
{
	if (DEBUG) {
		echo $msg . '<br><br>';
	}
}

function processSubjects(&$db, $nodes)
{
	foreach ($nodes as $subject) {
		$href = $subject->getElementsByTagName('a')->item(0)->getAttribute('href');

		$sections = array();

		$dom = new DOMDocument();
		$dom->loadHTML(file_get_contents(ROOT . $href));
		$finder = new DomXPath($dom);

		processSections($db, $finder->query("//tr[contains(@class, 'odd')]"), $sections);
		processSections($db, $finder->query("//tr[contains(@class, 'even')]"), $sections);

		output(json_encode($sections));
	}
}

function processSections(&$db, $nodes, &$sections)
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

$dom = new DOMDocument();
$dom->loadHTML(file_get_contents(APP_URL));
$finder = new DomXPath($dom);

foreach ($finder->query("//div[contains(@class, 'term')]") as $elem) {
	$href = $elem->getElementsByTagName('a')->item(0)->getAttribute('href');

	$dom->loadHTML(file_get_contents(ROOT . $href));
	$finder = new DomXPath($dom);

	processSubjects($db, $finder->query("//div[contains(@class, 'odd')]"));
	processSubjects($db, $finder->query("//div[contains(@class, 'even')]"));
	
	break;
}

?>
