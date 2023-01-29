<?php
    class Product {
        
        public function insert($sku, $active, $idCategory, $name, $image, $description, $price, $stock) {
            global $dbconnect;
            $dbconnect->beginTransaction();
            
            $query = "INSERT INTO product(sku, active, id_category, name, image, description, price, stock) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
            $statment = $dbconnect->prepare($query);
            
            // mit dem bindparam werden datensätze in die datenbank geladen
            $statment->bindParam(1, $sku, PDO::PARAM_STR);
            $statment->bindParam(2, $active, PDO::PARAM_INT);
            $statment->bindParam(3, $idCategory, PDO::PARAM_INT);
            $statment->bindParam(4, $name, PDO::PARAM_STR);
            $statment->bindParam(5, $image, PDO::PARAM_STR);
            $statment->bindParam(6, $description, PDO::PARAM_STR);
            $statment->bindParam(7, $price, PDO::PARAM_STR);
            $statment->bindParam(8, $stock, PDO::PARAM_INT);
            $statment->execute();
            
            $dbconnect->commit();
        }

        public function select($sku) {
            global $dbconnect;
            $dbconnect->beginTransaction();
			
            $query = "SELECT * FROM product WHERE sku = " . $sku . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
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
            
            $query = "SELECT * FROM product WHERE valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();
            
            // datenbank abfrage in eine assoziatives array umgewandel und speichern
            $result = $statment->setFetchMode(PDO::FETCH_ASSOC);
            foreach ($statment->fetchAll() as $result => $value) {
                $out[] = $value;
            }

            $dbconnect->commit();
            return $out;
        }

        public function update($productId, $sku, $active, $category, $name, $image, $description, $price, $stock) {
            global $dbconnect;
            $dbconnect->beginTransaction();

            $query = "SELECT * FROM product WHERE product_id = " . $productId ." AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();

            $query = "UPDATE product SET valid_to = CURRENT_TIMESTAMP WHERE product_id = " . $productId . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();

            $query = "INSERT INTO product(product_id, sku, active, id_category, name, image, description, price, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $statment = $dbconnect->prepare($query);
            
            // mit dem bindparam werden datensätze in die datenbank geladen
            $statment->bindParam(1, $productId, PDO::PARAM_INT);
            $statment->bindParam(2, $sku, PDO::PARAM_STR);
            $statment->bindParam(3, $active, PDO::PARAM_INT);
            $statment->bindParam(4, $category, PDO::PARAM_INT);
            $statment->bindParam(5, $name, PDO::PARAM_STR);
            $statment->bindParam(6, $image, PDO::PARAM_STR);
            $statment->bindParam(7, $description, PDO::PARAM_STR);
            $statment->bindParam(8, $price, PDO::PARAM_STR);
            $statment->bindParam(9, $stock, PDO::PARAM_INT);
            $statment->execute();
            
            $dbconnect->commit();
        }

        public function delete($productId) {
            global $dbconnect;
            $dbconnect->beginTransaction();
            
            $query = "UPDATE product SET valid_to = CURRENT_TIMESTAMP WHERE product_id = " . $productId . " AND valid_from <= CURRENT_TIMESTAMP AND valid_to > CURRENT_TIMESTAMP";
            $statment = $dbconnect->prepare($query);
            $statment->execute();
            
            $dbconnect->commit();
        }
    }