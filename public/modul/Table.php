<?php
	class Table {
		
		public $tableName;

		function __construct($tableName) {
			$this->tableName = $tableName;
		}

        public function insert($email, $age, $password) {
			global $dbconnect;
			$dbconnect->beginTransaction();

			$query = "INSERT INTO ". $this->tableName ."(email, bday, password) VALUES(?, ?, ?)";
			$statment = $dbconnect->prepare($query);
			
			// mit dem bindparam werden datensätze in die datenbank geladen
			$statment->bindParam(1, $email, PDO::PARAM_STR);
			$statment->bindParam(2, $age, PDO::PARAM_STR);
			$statment->bindParam(3, $password, PDO::PARAM_STR);
			$statment->execute();

			$dbconnect->commit();
		}

		public function select() {
			global $dbconnect;
			$dbconnect->beginTransaction();

			$query = "SELECT * FROM " . $this->tableName . " WHERE valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
			$person = $dbconnect->prepare($query);
    		$person->execute();

			// datenbank abfrage in eine assoziatives array umgewandel und speichern
			$sort = $person->setFetchMode(PDO::FETCH_ASSOC);
			foreach ($person as $personData) {
				$result = $personData;
			}

			$dbconnect->commit();
			return $result;
		}

		public function update($id, $email, $age, $password) {
			global $dbconnect;
			$dbconnect->beginTransaction();

			$query = "SELECT * FROM " . $this->tableName . " WHERE id=?" . $id . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
			$statment = $dbconnect->prepare($query);
			$statment->execute();

			$query = "UPDATE " . $this->tableName . " SET valid_to = CURRENT_TIMESTAMP WHERE id = " . $id . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
			$statment = $dbconnect->prepare($query);
			$statment->execute();

			$query = "INSERT INTO " . $this->tableName . "(email, bday, password) VALUES (?, ?, ?)";
			$statment = $dbconnect->prepare($query);
			
			// mit dem bindparam werden datensätze in die datenbank geladen
			$statment->bindParam(1, $email, PDO::PARAM_STR);
			$statment->bindParam(2, $age, PDO::PARAM_STR);
			$statment->bindParam(3, $password, PDO::PARAM_STR);
			$statment->execute();

			$dbconnect->commit();
		}

		public function delete($id) {
			global $dbconnect;
			$dbconnect->beginTransaction();

			$query = "UPDATE " . $this->tableName . " SET email=?, bday=?, password=? WHERE id=" . $id . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
			$statment = $dbconnect->prepare($query);
			$statment->execute();

			$dbconnect->commit();
		}
}