<?php

    require "database.php";
    require_once "Table.php";

    $email = "";
    $pass = "";
    
    try {
        $personTable = new Table("person");
        $result = $personTable->select();
        
        $email = $result["email"];
        $pass = $result["password"];
        
    } catch (Exception $exception) {
        // wenn die verbindung oder abfrage fehlschlagen sollte, werden alle vorgänge rückgengig gemacht und die transration mit einer rückmeldung 500 internal server error geschlossen und verbindung unterbrochen
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    // wenn der skript fehlerfrei durch läuft wird eine rückmeldung an den client gesendet
    http_response_code(201);