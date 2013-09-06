<?php

class Database {
	private $HOST = 'localhost';
	private $USER = 'root';
	private $PASS = '';
	private $DB = 'tms';
	private $DEBUG = false;

	private $conn;

	public function __construct() {
		try {
			$this->conn = new PDO('mysql:host=' . $this->HOST . ';dbname=' . $this->DB, $this->USER, $this->PASS);
			if ($this->DEBUG) {
				$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
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

		// fetch rows for select queries
		if ($result && strpos(strtolower($query), 'select') !== FALSE) {
			return $stmt->fetchAll();
		}

		return $result;
	}
}

?>
