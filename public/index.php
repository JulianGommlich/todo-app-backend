<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require 'verarbeiten.php';

$app = AppFactory::create();
$app->setBasePath((function () {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
        return $_SERVER['SCRIPT_NAME'];
    }
    if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
        return $scriptDir;
    }
    return '';
})());

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/test/{arg}', function (Request $request, Response $response, $args) {
    $aargh = $request->getAttribute('arg');
    $response->getBody()->write("Test erfolgreich! " . $aargh);
    return (3 < 2) ? $response : $response->withStatus(401);
    //$response->getBody()->write("Test erfolgreich! " . $aargh);
});

// 1. Nutzer
// Login
$app->post('/api/users', function (Request $request, Response $response, $args){
    // 1. Body auslesen
    $parsedBody = json_decode((string)$request->getBody(), true);
    $nick       = $parsedBody['username'];
    $pass       = $parsedBody['password'];
 
    // 2. DB Aufruf
    $token = Login($nick, $pass);
    // Falsches Token -> "null"  (Tabelle leer -> Ausgabe leer ([]))
    // Invalide Daten -> Header = 401, Valide Daten -> Rückgabe: Token des Nutzers
    $response->getBody()->write(json_encode($token));
    return ($token != null) ? $response : $response->withStatus(401);
});


// 2. Aufgaben
// 2.1 Auslesen
// Alle Aufgaben holen
$app->get('/api/tasks', function (Request $request, Response $response, $args){
    // 1. Token aus Header auslesen
    $user           = $request->getHeader('token')[0];

    // 2. DB-Aufruf
    $tasks          = getAllItemsOfAUser($user);
    $response->getBody()->write(json_encode($tasks));

    return ($tasks != null) ? $response : $response->withStatus(401);
});

// Alle Aufgaben einer Liste holen
$app->get('/api/lists/{listId}/tasks', function (Request $request, Response $response, $args){
    // 1. Token aus Header auslesen
    $user           = $request->getHeader('token')[0];

    // 2. Argument(e) aus URI auslesen
    $listId         = $request->getAttribute('listId');

    // 3. DB-Aufruf
    $tasks = getAllItemsOfAList($listId, $user);
    $response->getBody()->write(json_encode($tasks));

    return ($tasks != null) ? $response : $response->withStatus(401);
});

// Eine Aufgabe einer Liste holen
$app->get('/api/lists/{listId}/tasks/{taskId}', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];

    $listId = $request->getAttribute('listId');
    $taskId = $request->getAttribute('taskId');

    $task = getOneItemOfAList($listId, $taskId, $user);
    $response->getBody()->write(json_encode($task));

    return ($task != null) ? $response : $response->withStatus(401);
});

// 2.2 Erstellen
// Erstellen einer Aufgabe
$app->post('/api/tasks', function(Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];

    // Körper der Anfrage auslesen 
    $parsedBody     = json_decode((string)$request->getBody(), true);
    $title          = $parsedBody['title'];
    $description    = $parsedBody['description'];
    $priority       = $parsedBody['priority'];
    $dueDate        = $parsedBody['dueDate'];
    $state          = $parsedBody['state'];
    $toDoList       = $parsedBody['toDoList'];

    // Objekt anlegen
    $task = createToDoItem($title, $toDoList, $description, $priority, $dueDate, $state, $user);
    $response->getBody()->write(json_encode($task));

    return ($task != null) ? $response : $response->withStatus(401);
});

// 2.3 Anpassen
// Anpassen einer Aufgabe
$app->put('/api/tasks/{taskId}', function(Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];

    $taskId         = $request->getAttribute('taskId');

    $parsedBody     = json_decode((string)$request->getBody(), true);
    $title          = $parsedBody['title'];
    $description    = $parsedBody['description'];
    $priority       = $parsedBody['priority'];
    $dueDate        = $parsedBody['dueDate'];
    $state          = $parsedBody['state'];
    $toDoList       = $parsedBody['toDoList'];

    $task = changeToDoItem($taskId, $title, $description, $priority, $dueDate, $state, $toDoList, $user);
    $response->getBody()->write(json_encode($task));
    
    return ($task != null) ? $response : $response->withStatus(401);
});

// 2.4 Löschen
// Löschen einer Aufgabe
$app->delete('/api/tasks/{taskId}', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];
    $taskId         = $request->getAttribute('taskId');

    $del = deleteToDoItem($taskId, $user);

    // del = true -> 200 OK / del = null -> 401 Unauthorized
    return ($del != null) ? $response : $response->withStatus(401);
});

// Löschen aller Aufgaben einer Liste
$app->delete('/api//lists/{listId}/tasks/', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];
    $listId         = $request->getAttribute('listId');

    $del = deleteAllToDoItem($listId, $user);

    // del = true -> 200 OK / del = null -> 401 Unauthorized
    return ($del != null) ? $response : $response->withStatus(401);
});


// 3. Listen
// 3.1 Auslesen
// Alle Listen eines Nutzers holen
$app->get('/api/lists', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];

    $lists          = getAllListsOfAUser($user);
    $response->getBody()->write(json_encode($lists));

    return ($lists != null) ? $response : $response->withStatus(401);
});

// 3.2 Erstellen
// Erstellen einer Liste
$app->post('/api/lists', function(Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];
    $parsedBody     = json_decode((string)$request->getBody(), true);
    $name           = $parsedBody['name'];

    $list = createToDoList($name, $user);
    $response->getBody()->write(json_encode($list));

    return ($list != null) ? $response : $response->withStatus(401);
});

// 3.3
// Löschen einer Liste
$app->delete('/api/lists/{listId}', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];
    $listId         = $request->getAttribute('listId');

    $del = deleteToDoList($listId, $user);
    return ($del != null) ? $response : $response->withStatus(401);
});

// Löschen aller Listen
$app->delete('/api/lists', function (Request $request, Response $response, $args){
    $user           = $request->getHeader('token')[0];

    $del            = deleteAllToDoList($user);
    
    return ($del != null) ? $response : $response->withStatus(401);
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();
?>
