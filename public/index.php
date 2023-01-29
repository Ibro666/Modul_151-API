<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
error_reporting(E_ALL);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use ReallySimpleJWT\Token;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="IN Webshop API", version="0.1")
 */

require __DIR__ . '/../vendor/autoload.php';

require_once "modul/database.php";
require_once "modul/Product.php";
require_once "modul/Category.php";


// dem Server definieren dass er mit JSON-Datei arbeiten soll
header("Content-Type: application/json");

$app = AppFactory::create();


/**
    * @OA\Get(
    *   path="/",
    *   summary="Endpoint zum Testen gib nur Welcompage zurück",
    *   tags={"test"},
    *   @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
*/
$app->get("/", function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Welcome to root Page!");
    return $response;
});

/**
    * @OA\Post(
    *   path="/API/V1/Authenticate",
    *   summary="Endpoint führt Authentifikation durch, hierbei werden die Benutzername und Passwort geprüft und ein Token generiert",
    *   tags={"users"},
    *   requestBody=@OA\RequestBody(
    *       request="/API/V1/Authenticate",
    *       required=true,
    *       description="Request Body muss einen JSON-Datei mit dem Inhalt Benutzername und Passwort enthalten",
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               @OA\Property(property="username", type="string", example="admin"),
    *               @OA\Property(property="password", type="string", example="sec!ReT423*&")
    *           )
    *       )
    *   ),
    *   @OA\Response(response="201", description="Response liefert Status 201 wenn die Authentifizierung erfolgreich war. Benutzername und Passwort sind korrekt"))
    * )
*/
$app->post("/API/V1/Authenticate", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    $expiration = time() + 3600;
    $host = "localhost";

    // inhalt der requestbody als string in die variable speichern
    $requestBody = file_get_contents("php://input");
    // json_decode wandelt den input und speichert es als string in die variable
    $requestData = json_decode($requestBody, true);

    // wenn die eingabe leer ist wird 400 zurück gegeben.
    if (empty($requestData)) {
        // echo "Benutzername oder Password darf nicht leer sein!";
        http_response_code(400);
        die();
    }

    // überprüfen der username und pass wenn valide ist dann Token erzeugen
    if ($requestData["email"] == $email && $requestData["password"] == $pass) {
        $token = Token::create($email, $pass, $expiration, $host);
        // token wir in den Client zurück gesendet
        setcookie("token", $token, $expiration);
    }
  
    http_response_code(201);
    return $response;
});

/**
    * @OA\Post(
    *   path="/API/V1/Product/{sku}",
    *   summary="Beim aufrufen der Endpoint wird ein neu Produkt hinzugefügt",
    *   tags={"product"},
    *   requestBody=@OA\RequestBody(
    *       request="/API/V1/Product/{sku}",
    *       required=true,
    *       description="Request Body muss einen JSON-Datei mit dem Inhalt sku, active, category, name, image, description, price, stock enthalten",
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               @OA\Property(property="sku", type="string", example="25321"),
 *               @OA\Property(property="active", type="integer", example="1"),
 *               @OA\Property(property="categoryId", type="integer", example="1"),
 *               @OA\Property(property="name", type="string", example="Löfel"),
 *               @OA\Property(property="image", type="string", example="image.jpg"),
 *               @OA\Property(property="description", type="string", example="edler Silberlöfel"),
 *               @OA\Property(property="price", type="integer", example="17.25"),
 *               @OA\Property(property="stock", type="integer", example="1")
    *           )
    *       )
    *   ),
    *   @OA\Response(response="201", description="Response liefert Status 201 wenn die Aktion erfolgreich war, und einen JSON-Datei mit dem Inhalt 'Produkt erfolgreich erzeugt: sku'"))
    * )
*/
$app->post("/API/V1/Product/{sku}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        http_response_code(401);
        die();
    }
    
    $requestBody = file_get_contents("php://input");
    $requestData = json_decode($requestBody, true);
    
    // daten in die datebank hinzufügen
    try {
        $produktTab = new Product();
        $statment = $produktTab->insert($requestData["sku"], $requestData["active"], $requestData["category"], $requestData["name"], $requestData["image"], $requestData["description"], $requestData["price"], $requestData["stock"]);
    } catch (Exception $exception) {
        // wenn die verbindung oder abfrage fehlschlagen sollte, werden alle vorgänge rückgengig gemacht und die transration mit einer rückmeldung 500 internal server error geschlossen und verbindung unterbrochen
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }

    echo json_encode(
        array(
            "massage" => "Product created: " . $args["sku"] . " has bean created!"
        )
    );

    http_response_code(201);
    return $response;
});

/**
    * @OA\Delete(
    *     path="/API/V1/Product/{produktId}",
    *     summary="Beim aufrufen der Endpoint wird Produkt mit dem angegebenen productId aus dem datenbank löschen",
    *     tags={"product"},
    *     @OA\Parameter(
    *         name="produktId",
    *         in="path",
    *         required=true,
    *         description="Primärschlüssel einer Produkt",
    *         @OA\Schema(
    *             type="integer",
    *             example="1"
    *         )
    *     ),
    *     @OA\Response(response="201", description="Wenn das zulöschende Produkt erfolgreich gelöschtworden ist wird der Status 201 zurückgegeben"))
    * )
*/
// 
$app->delete("/API/V1/Product/{produktId}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    try {
        $product = new Product();
        $delete = $product->delete($args["produktId"]);
        
        echo json_encode(
            array(
                "massage" => "Product " . $args["produktId"] . " has bean deleted!"
            )
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(201);
    return $response;
});

/**
    * @OA\Put(
    *     path="/API/V1/Product/{produktId}",
    *     summary="Beim aufrufen der Endpoint wird produkt mit bestimmten id bearbeitet und aktualisiert",
    *     tags={"product"},
    *     @OA\Parameter(
    *         name="produktId",
    *         in="path",
    *         required=true,
    *         description="Primärschlüssel einer Produkt",
    *         @OA\Schema(
    *             type="integer",
    *             example="1"
    *         )
    *     ),
    *     requestBody=@OA\RequestBody(
    *         request="/API/V1/Product/{produktId}",
    *         required=true,
    *         description="Request-Body muss einen JSON-Datei mit dem Inhalt produktId, sku, active, category, name, image, description, price, stock enthalten",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(property="produktId", type="integer", example="2"),
 *                 @OA\Property(property="sku", type="string", example="266321"),
 *                 @OA\Property(property="active", type="integer", example="1"),
 *                 @OA\Property(property="categoryId", type="integer", example="1"),
 *                 @OA\Property(property="name", type="string", example="Löfel"),
 *                 @OA\Property(property="image", type="string", example="image.jpg"),
 *                 @OA\Property(property="description", type="string", example="edler Silberlöfel"),
 *                 @OA\Property(property="price", type="integer", example="12.25"),
 *                 @OA\Property(property="stock", type="integer", example="1")
    *             )
    *         )
    *     ),
    *     @OA\Response(response="201", description="Wenn das zuaktualisierende Produkt erfolgreich aktualisiert worden ist, wird der Status 201 zurückgegeben"))
    * )
*/
$app->put("/API/V1/Product/{produktId}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    $requestBody = file_get_contents("php://input");
    $requestData = json_decode($requestBody, true);

    try {
        $product = new Product();
        $update = $product->update($args["produktId"], $requestData["sku"], $requestData["active"], $requestData["category"], $requestData["name"], $requestData["image"], $requestData["description"], $requestData["price"], $requestData["stock"]);
        echo json_encode(
            array(
                "massage" => "Product " . $args["produktId"] . " has bean updated!"
            )
        );
    } catch (Exception $exception) {
        var_dump($exception);
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(201);
    return $response;
});

/**
    * @OA\Get(
    *     path="/API/V1/Product/{sku}",
    *     summary="Beim aufrufen der Endpoint wird ein bestimmte Produkt ausgegeben",
    *     tags={"product"},
    *     @OA\Parameter(
    *         name="sku",
    *         in="path",
    *         required=true,
    *         description="sku kann einen Artikel-Nummer smybolisieren",
    *         @OA\Schema(
    *             type="string",
    *             example="1e57i"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
 */
$app->get("/API/V1/Product/{sku}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    try {
        $product = new Product();
        $result = $product->select($args["sku"]);
        echo json_encode(
            $result
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(201);
    return $response;
});

/**
    * @OA\Get(
    *     path="/API/V1/Products",
    *     summary="Beim aufrufen der Endpoint werden alle Produkte aufgelistet.",
    *     tags={"product"},
    *     @OA\Response(response="200", description="Wenn die Abfrage erfolgreich war, dann wird responsecodestatus 200 zurückgegeben."))
 */
$app->get("/API/V1/Products", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    try {
        $productList = new Product();
        $result = $productList->selectAll();
        echo json_encode(
            $result
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }

    http_response_code(201);
    return $response;
});

/**
    * @OA\Post(
    *   path="/API/V1/Category/{name}",
    *   summary="Beim aufrufen der Endpoint wird ein neu Kategorie hinnzugefügt",
    *   tags={"category"},
    *   requestBody=@OA\RequestBody(
    *       request="/API/V1/Category/{name}",
    *       required=true,
    *       description="Request Body muss einen JSON-Datei mit dem Inhalt active, name enthalten",
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               @OA\Property(property="active", type="integer", example="1"),
    *               @OA\Property(property="name", type="string", example="Smartphon")
    *           )
    *       )
    *   ),
    *   @OA\Response(response="201", description="Response liefert Status 201 wenn die Aktion erfolgreich war, und einen JSON-Datei mit dem Inhalt 'Kategorie erfolgreich erzeugt: name'"))
    * )
*/
$app->post("/API/V1/Category/{name}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        http_response_code(401);
        die();
    }
    
    $requestBody = file_get_contents("php://input");
    $requestData = json_decode($requestBody, true);

    // daten in die datebank hinzufügen
    try {
        $catTab = new Category();
        $catTab->insert($requestData["active"], $requestData["name"]);
    } catch (Exception $exception) {
        // wenn die verbindung oder abfrage fehlschlagen sollte, werden alle vorgänge rückgengig gemacht und die transration mit einer rückmeldung 500 internal server error geschlossen und verbindung unterbrochen
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }

    echo json_encode(
        array(
            "massage" => "Category created: " . $args["name"] . " has bean created!"
        )
    );

    http_response_code(201);
    return $response;
});

/**
    * @OA\Delete(
    *     path="/API/V1/Category/{categoryId}",
    *     summary="Beim aufrufen der Endpoint wird der Kategorie mit dem angegeben categoryId aus der Datenbank gelöscht",
    *     tags={"category"},
    *     @OA\Parameter(
    *         name="categoryId",
    *         in="path",
    *         required=true,
    *         description="Primarschlüssel eines Kategories",
    *         @OA\Schema(
    *             type="integer",
    *             example="1"
    *         )
    *     ),
    *     @OA\Response(response="201", description="Wenn das zulöschende Kategorie erfolgreich gelöschtworden ist wird der Status 201 zurückgegeben")
    * )
*/
$app->delete("/API/V1/Category/{categoryId}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    try {
        $cat = new Category();
        $cat->delete($args["categoryId"]);

        echo json_encode(
            array(
                "massage" => "Category " . $args["categoryId"] . " has bean deleted!"
            )
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(201);
    return $response;
});

/**
    * @OA\Put(
    *     path="/API/V1/Category/{categoryId}",
    *     summary="Beim aufrufen der Endpoint wird Kategorie mit bestimmten id bearbeitet und aktualisiert",
    *     tags={"category"},
    *     @OA\Parameter(
    *         name="categoryId",
    *         in="path",
    *         required=true,
    *         description="Primärschlüssel einer Kategorie",
    *         @OA\Schema(
    *             type="integer",
    *             example="1"
    *         )
    *     ),
    *     requestBody=@OA\RequestBody(
    *         request="/API/V1/Category/{categoryId}",
    *         required=true,
    *         description="Request-Body muss einen JSON-Datei mit dem Inhalt categoryId, active, name enthalten",
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(property="categoryId", type="integer", example="1"),
    *                 @OA\Property(property="active", type="integer", example="1"),
    *                 @OA\Property(property="name", type="string", example="Zahnbürste")
    *             )
    *         )
    *     ),
    *     @OA\Response(response="201", description="Wenn das zuaktualisierende Kategorie erfolgreich aktualisiert worden ist, wird der Status 201 zurückgegeben"))
    * )
*/
$app->put("/API/V1/Category/{categoryId}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    $requestBody = file_get_contents("php://input");
    $requestData = json_decode($requestBody, true);

    try {
        $cat = new Category();
        $cat->update($args["categoryId"], $requestData["active"], $requestData["name"]);

        echo json_encode(
            array(
                "massage" => "Category " . $args["categoryId"] . " has bean updated!"
            )
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(201);
    return $response;
});

/**
    * @OA\Get(
    *     path="/API/V1/Category/{categoryId}",
    *     summary="Beim aufrufen der Endpoint wird ein bestimmte Kategorie ausgegeben",
    *     tags={"category"},
    *     @OA\Parameter(
    *         name="categoryId",
    *         in="path",
    *         required=true,
    *         description="",
    *         @OA\Schema(
    *             type="integer",
    *             example="1"
    *         )
    *     ),
    *     @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
 */
$app->get("/API/V1/Category/{categoryId}", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    try {
        $cat = new Category();
        $result = $cat->select($args["categoryId"]);      
        echo json_encode(
            $result
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }
    
    http_response_code(200);
    return $response;
});

/**
    * @OA\Get(
    *     path="/API/V1/Categorys",
    *     summary="Beim aufrufen der Endpoint werden alle Kategorie aufgelistet",
    *     tags={"category"},
    *     @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
 */
$app->get("/API/V1/Categorys", function (Request $request, Response $response, array $args) {
    require "modul/authentication.php";

    if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $pass)) {
        echo "Authentifikation hat felgeschlagen!!!";
        http_response_code(401);
        die();
    }

    // es wird durch den Aufruf der Klasse Kategorie ein JSON-Datei mit den Kategorien erzueugt und im Response-Body mit gegeben
    try {
        $catList = new Category();
        $result = $catList->selectAll();
        echo json_encode(
            $result
        );
    } catch (Exception $exception) {
        $dbconnect->rollBack();
        http_response_code(500);
        die();
    }

    http_response_code(200);
    return $response;
});

// Applikation Starten (Slim)
$app->run();
