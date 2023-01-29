<?php
    class Category {

        public function insert($active, $name) {
            global $dbconnect;
            $dbconnect->beginTransaction();
            
            $query = "INSERT INTO category(active, name) VALUES(?, ?)";
            $statment = $dbconnect->prepare($query);
            
            // mit dem bindparam werden datensätze in die datenbank geladen
            $statment->bindParam(1, $active, PDO::PARAM_INT);
            $statment->bindParam(2, $name, PDO::PARAM_STR);
            $statment->execute();

            $dbconnect->commit();
        }

        public function select($categoryId) {
            global $dbconnect;
            $dbconnect->beginTransaction();

            $query = "SELECT * FROM category WHERE category_id = " . $categoryId . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();
            
            // datenbank abfrage in eine assoziatives array umgewandel und speichern
            $result = $statment->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($statment as $key => $value) {
                $out[] = $value;
            }

            $dbconnect->commit();
            return $out;
        }

        public function selectAll() {
            global $dbconnect;
            $dbconnect->beginTransaction();

            $query = "SELECT * FROM category WHERE valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();
            
            // datenbank abfrage in eine assoziatives array umgewandel und speichern
            $result = $statment->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($statment->fetchAll() as $key => $value) {
                $out[] = $value;
            }

            $dbconnect->commit();
            return $out;
        }

        public function update($categoryId, $active, $name) {
            global $dbconnect;
            $dbconnect->beginTransaction();

            $query = "SELECT * FROM category WHERE category_id = " . $categoryId ." AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();

            $query = "UPDATE category SET valid_to = CURRENT_TIMESTAMP WHERE category_id = " . $categoryId . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();

            $query = "INSERT INTO category(category_id, active, name) VALUES (?, ?, ?)";
            $statment = $dbconnect->prepare($query);
            
            // mit dem bindparam werden datensätze in die datenbank geladen
            $statment->bindParam(1, $categoryId, PDO::PARAM_INT);
            $statment->bindParam(2, $active, PDO::PARAM_INT);
            $statment->bindParam(3, $name, PDO::PARAM_STR);
            $statment->execute();
            
            $dbconnect->commit();
        }

        public function delete($categoryId) {
            global $dbconnect;
            $dbconnect->beginTransaction();

            $query = "UPDATE category SET valid_to = CURRENT_TIMESTAMP WHERE category_id = " . $categoryId . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();

            $dbconnect->commit();
        }
    }