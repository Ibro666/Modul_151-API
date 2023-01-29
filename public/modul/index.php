<?php

//require_once "Table.php";

$server = "mysql";
$user = "root";
$pass = "admin123";
$databasename = "test";

$id = "";
$personName = "";
$personAge = "";
$personPass = "";

// Datenbankanbindung nach PDO 
try {
    $dbconnect = new PDO("mysql:host=$server;dbname=$databasename", $user, $pass);
} catch (Exception $exception) {
    echo "Die Verbindung ist erfolgreich" . $exception->getMessage();
    die();
}

$dbconnect->beginTransaction();
// Datenbank befüllen
try {
	$name = "Uli";
	$age = 45;
    $pass = password_hash("2", PASSWORD_DEFAULT);

	// instanz der Tabelle Objekt erzeugen 

	$personTable = new Table("person");
	//$personTable->tableName = "person";

	$result = $personTable->insert();

	$statement = $dbconnect->prepare($result);

	$statement->bindParam(1, $name, PDO::PARAM_STR);
	$statement->bindParam(2, $age, PDO::PARAM_INT);
    $statement->bindParam(3, $pass, PDO::PARAM_STR);

	// $personTable->delete();

	$statement->execute();

    // $dbconnect->commit();
    
} catch (Exception $exception) {
    $dbconnect->rollBack();
    http_response_code(500);
    die();
}

// datensätze änderen und aktualisieren
try {
	$name = "Warlog";
	$age = 27;
    $pass = password_hash("33557", PASSWORD_DEFAULT);

	$personTable = new Table("person");
	//$personTable->tableName = "person";

	$result = $personTable->update(1);
	
	$statement = $dbconnect->prepare($result);

	$statement->bindParam(1, $name, PDO::PARAM_STR);
	$statement->bindParam(2, $age, PDO::PARAM_INT);
    $statement->bindParam(3, $pass, PDO::PARAM_STR);

	$statement->execute();

    // $dbconnect->commit();
    
} catch (Exception $exception) {
    $dbconnect->rollBack();
    http_response_code(500);
    die();
}

// datensätze löschen
try {
	$personTable = new Table();
	$personTable->tableName = "person";
	$result = $personTable->delete(27);
	// Abfrage mit prepared auffüren, prepare verindert injentions
	$selectdb = $dbconnect->prepare($result);
	
	$selectdb->execute();
	
} catch (Exception $exception) {
    $dbconnect->rollBack();
    http_response_code(500);
    die();
}
// Transaktion starten
// währen dem Transation werden alle weitere Anfragen an der Datenbak aufgehalten.
//  $dbconnect->beginTransaction();

// Datenbank Abfragen
try {
	// $query = "SELECT * FROM person";

	$personTable = new Table();
	$personTable->tableName = "person";
	$result = $personTable->select();
	// Abfrage mit prepared auffüren, prepare verindert injentions
	$selectdb = $dbconnect->prepare($result);
	
	$selectdb->execute();

	foreach ($selectdb as $person) {
		$id = $person["id"];
		$personName = $person["name"];
		$personAge = $person["age"];
		$personPass = $person["password"];
	}
	
	// $dbconnect->commit();
	
} catch (Exception $exception) {
    $dbconnect->rollBack();
    http_response_code(500);
    die();
}

http_response_code(201);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabellen Einträge</title>
</head>
<body>
    <div>
        <div>
			<form action="" method="GET">
				<table>
					<tr>
						<th>ID</th><th>Name</th><th>Alter</th><th>Passwort</th>
					</tr>
					<tr>
						<td><?php echo $id ?></td><td><?php echo $personName ?></td><td><?php echo $personAge ?></td><td><?php echo $personPass ?></td>
					</tr>
				</table>
			</form>
        </div>
    </div>
</body>
</html>