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

// 1. Nutzer
// Login
$app->post('/api/users', function (Request $request, Response $response, $args){
    // 1. Body auslesen
    $parsedBody = json_decode((string)$request->getBody(), true);
    $nick       = $parsedBody['username'];
    $pass       = $parsedBody['password'];
 
    // 2. DB Aufruf
    $token = Login($nick, $pass);

    // Falsches Token -> "null" 
    // Invalide Daten -> Header = 401, Valide Daten -> Rückgabe: Token des Nutzers
    $response->getBody()->write(json_encode($token));
    return checkToken($token) ? $response : $response->withStatus(401);
});


// 2. Aufgaben
// 2.1 Auslesen
// Alle Aufgaben holen
$app->get('/api/tasks', function (Request $request, Response $response, $args){
    // 1. Token aus Header auslesen
    $token           = $request->getHeader('token')[0];

    // 2. DB-Aufruf
    $tasks          = getAllItemsOfAUser($token);
    $response->getBody()->write(json_encode($tasks));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// Alle Aufgaben einer Liste holen
$app->get('/api/lists/{listId}/tasks', function (Request $request, Response $response, $args){
    // 1. Token aus Header auslesen
    $token           = $request->getHeader('token')[0];

    // 2. Argument(e) aus URI auslesen
    $listId         = $request->getAttribute('listId');

    // 3. DB-Aufruf
    $tasks = getAllItemsOfAList($listId, $token);
    $response->getBody()->write(json_encode($tasks));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// Eine Aufgabe einer Liste holen
$app->get('/api/lists/{listId}/tasks/{taskId}', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];

    $listId = $request->getAttribute('listId');
    $taskId = $request->getAttribute('taskId');

    $task = getOneItemOfAList($listId, $taskId, $token);
    $response->getBody()->write(json_encode($task));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// 2.2 Erstellen
// Erstellen einer Aufgabe
$app->post('/api/tasks', function(Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];

    // Körper der Anfrage auslesen 
    $parsedBody     = json_decode((string)$request->getBody(), true);
    $title          = $parsedBody['title'];
    $description    = $parsedBody['description'];
    $priority       = $parsedBody['priority'];
    $dueDate        = $parsedBody['dueDate'];
    $state          = $parsedBody['state'];
    $toDoList       = $parsedBody['toDoList'];

    // Objekt anlegen
    $task = createToDoItem($title, $toDoList, $description, $priority, $dueDate, $state, $token);
    $response->getBody()->write(json_encode($task));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// 2.3 Anpassen
// Anpassen einer Aufgabe
$app->put('/api/tasks/{taskId}', function(Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];

    $taskId         = $request->getAttribute('taskId');

    $parsedBody     = json_decode((string)$request->getBody(), true);
    $title          = $parsedBody['title'];
    $description    = $parsedBody['description'];
    $priority       = $parsedBody['priority'];
    $dueDate        = $parsedBody['dueDate'];
    $state          = $parsedBody['state'];
    $toDoList       = $parsedBody['toDoList'];

    $task = changeToDoItem($taskId, $title, $description, $priority, $dueDate, $state, $toDoList, $token);
    $response->getBody()->write(json_encode($task));
    
    return checkToken($token) ? $response : $response->withStatus(401);
});

// 2.4 Löschen
// Löschen einer Aufgabe
$app->delete('/api/lists/{listId}/tasks/{taskId}', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];
    $taskId         = $request->getAttribute('taskId');

    $del = deleteToDoItem($taskId, $token);
    $response->getBody()->write(json_encode($del));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// Löschen aller Aufgaben einer Liste
$app->delete('/api/lists/{listId}/tasks', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];
    $listId         = $request->getAttribute('listId');

    $del = deleteAllToDoItem($listId, $token);
    $response->getBody()->write(json_encode($del));

    return checkToken($token) ? $response : $response->withStatus(401);
});


// 3. Listen
// 3.1 Auslesen
// Alle Listen eines Nutzers holen
$app->get('/api/lists', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];

    $lists          = getAllListsOfAUser($token);
    $response->getBody()->write(json_encode($lists));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// 3.2 Erstellen
// Erstellen einer Liste
$app->post('/api/lists', function(Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];
    $parsedBody     = json_decode((string)$request->getBody(), true);
    $name           = $parsedBody['name'];

    $list = createToDoList($name, $token);
    $response->getBody()->write(json_encode($list));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// 3.3
// Ändern einer Liste
$app->put('/api/lists', function(Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];
    $parsedBody     = json_decode((string)$request->getBody(), true);
    $name           = $parsedBody['name'];

    $list = changeToDoList($name, $token);
    $response->getBody()->write(json_encode($list));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// 3.4
// Löschen einer Liste
$app->delete('/api/lists/{listId}', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];
    $listId         = $request->getAttribute('listId');

    $del = deleteToDoList($listId, $token);
    $response->getBody()->write(json_encode($del));

    return checkToken($token) ? $response : $response->withStatus(401);
});

// Löschen aller Listen
$app->delete('/api/lists', function (Request $request, Response $response, $args){
    $token           = $request->getHeader('token')[0];

    $del            = deleteAllToDoList($token);
    $response->getBody()->write(json_encode($del));

    return checkToken($token) ? $response : $response->withStatus(401);
});

$app->run();
?>
