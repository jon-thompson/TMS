<?php

require_once('lib/Database.class.php');
require_once('lib/Section.class.php');

define('ROOT', 'https://duapp2.drexel.edu');
define('APP_URL', 'https://duapp2.drexel.edu/webtms_du/app');
define('DEBUG', TRUE);
define('MAX_TIME_SECONDS', 1200);

set_time_limit(MAX_TIME_SECONDS);
error_reporting(E_ALL ^ E_WARNING);

function output($msg)
{
	if (DEBUG) {
		echo date('Y-m-d H:i:s') . "\t" . $msg . PHP_EOL;
	}
}

function saveSections($db, $sections)
{
	$n = count($sections);

	if ($n <= 0) {
		return;
	}

	$query = 'INSERT INTO section (Term, CRN, Object) VALUES';
	$params = array();

	for ($i = 0; $i < $n; $i++) {
		$section = $sections[$i];

		if ($i != 0) {
			$query .= ',';
		}

		$query .= ' (?, ?, ?)';

		$params[] = $section->Term;
		$params[] = $section->CRN;
		$params[] = json_encode($section);
	}

	$db->execute($query, $params) or die("Failed to insert.\n" . $db-getError());
}

function processTerms(&$db, $nodes)
{
	foreach ($nodes as $elem) {
		$a = $elem->getElementsByTagName('a')->item(0);
		$href = $a->getAttribute('href');
		$term = $a->nodeValue;

		$dom = new DOMDocument();
		$dom->loadHTML(file_get_contents(ROOT . $href));
		$finder = new DomXPath($dom);

		// process first college
		processSubjects($db, $term, $finder->query("//div[contains(@class, 'odd')]"));
		processSubjects($db, $term, $finder->query("//div[contains(@class, 'even')]"));

		processColleges($db, $term, $dom->getElementById('sideLeft')->getElementsByTagName('a'));
	}
}

function processColleges(&$db, $term, $nodes)
{
	// skip first college because that should be already parsed by processTerms
	for ($i = 1; $i < $nodes->length; $i++) {
		$node = $nodes->item($i);

		$href = $node->getAttribute('href');

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
		$link = $subject->getElementsByTagName('a')->item(0);
		$href = $link->getAttribute('href');

		$sections = array();

		$dom = new DOMDocument();
		$dom->loadHTML(file_get_contents(ROOT . $href));
		$finder = new DomXPath($dom);

		processSections($db, $term, $finder->query("//tr[contains(@class, 'odd')]"), $sections);
		processSections($db, $term, $finder->query("//tr[contains(@class, 'even')]"), $sections);

		saveSections($db, $sections);

		output($term . ' - ' . $link->nodeValue . ': ' . count($sections));
	}
}

function processSections(&$db, $term, $nodes, &$sections)
{
	$i = 0;
	foreach ($nodes as $node) {
		$i++;
		if ($i % 2 === 0) continue;
		try {
			$sections[] = new Section($term, $node);
		}
		catch (Exception $e) {
		}
	}
}

$db = new Database();

$db->execute('DELETE FROM section') or die('Failed to delete.');

$dom = new DOMDocument();
$dom->loadHTML(file_get_contents(APP_URL));
$finder = new DomXPath($dom);

processTerms($db, $finder->query("//div[contains(@class, 'term')]"));

?>
