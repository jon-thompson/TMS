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

function processTerms(&$db, $nodes)
{
	foreach ($nodes as $elem) {
		$a = $elem->getElementsByTagName('a')->item(0);
		$href = $a->getAttribute('href');
		$term = $a->nodeValue;

		output("Processing $term ...");

		$dom = new DOMDocument();
		$dom->loadHTML(file_get_contents(ROOT . $href));
		$finder = new DomXPath($dom);

		processSubjects($db, $term, $finder->query("//div[contains(@class, 'odd')]"));
		processSubjects($db, $term, $finder->query("//div[contains(@class, 'even')]"));
	}
}

function processSubjects(&$db, $term, $nodes)
{
	foreach ($nodes as $subject) {
		$href = $subject->getElementsByTagName('a')->item(0)->getAttribute('href');

		$sections = array();

		$dom = new DOMDocument();
		$dom->loadHTML(file_get_contents(ROOT . $href));
		$finder = new DomXPath($dom);

		processSections($db, $term, $finder->query("//tr[contains(@class, 'odd')]"), $sections);
		processSections($db, $term, $finder->query("//tr[contains(@class, 'even')]"), $sections);

		output(json_encode($sections));
	}
}

function processSections(&$db, $term, $nodes, &$sections)
{
	$i = 0;
	foreach ($nodes as $node) {
		$i++;
		if ($i % 2 === 0) continue;
		try {
			$s = new Section($term, $node);
			$sections[] = $s;
			$s->save($db);
		}
		catch (Exception $e) {
			output('********');
			output($e->getMessage());
			var_dump($node);
			output('');
			output('********');
		}
	}
}

$db = new Database();

$dom = new DOMDocument();
$dom->loadHTML(file_get_contents(APP_URL));
$finder = new DomXPath($dom);

processTerms($db, $finder->query("//div[contains(@class, 'term')]"));

?>
