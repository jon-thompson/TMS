<?php

class Database {
	private $HOST = 'localhost';
	private $USER = 'root';
	private $PASS = '';
	private $DB = 'tms';

	private $conn;

	public function __construct() {
		try {
			$this->conn = new PDO('mysql:host=' . $this->HOST . ';dbname=' . $this->DB, $this->USER, $this->PASS);
		}
		catch (PDOException $e) {
			die('Database error.');
		}
	}

	public function __destruct() {
		$this->conn = null;
	}

	public function execute($query, $params = array()) {
		$stmt = $this->conn->prepare($query);
		$result = $stmt->execute($params);

		if (!$result) {
			var_dump($this->conn->errorInfo());
		}
		return $result;
	}
}

?>
