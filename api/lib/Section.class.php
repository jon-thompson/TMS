<?php

class SectionTime {
	public $Day;
	public $Time;
}

class Section {
	public $Subject;
	public $Number;
	public $Type;
	public $Section;
	public $CRN;
	public $Title;
	public $Instructor;
	public $Term;
	public $Times = array();

	public function __construct($term, $domElement) {
		$children = $domElement->childNodes;

		if ($children->length < 15) {
			throw new Exception("Invalid element length.");
		}

		$this->Subject = $children->item(0)->nodeValue; 
		$this->Number = $children->item(2)->nodeValue;
		$this->Type = $children->item(4)->nodeValue;
		$this->Section = $children->item(6)->nodeValue;
		$this->CRN = $children->item(8)->nodeValue;
		$this->Title = $children->item(10)->nodeValue;
		$this->Instructor = $children->item(14)->nodeValue;
		$this->Term = $term;

		$rows = $children->item(12)->getElementsByTagName('tr');
		foreach ($rows as $row) {
			// skip over TBD rows
			if (strpos($row->nodeValue, 'TBD') !== FALSE) {
				continue;
			}

			$time = new SectionTime();
			$time->Day = $row->firstChild->nodeValue;
			$time->Time = $row->childNodes->item(2)->nodeValue;
			
			$this->Times[] = $time;
		}
	}
}

?>
