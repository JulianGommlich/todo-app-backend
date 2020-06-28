  <?php

  /*
  Funcitons:
  -init():                                          creates database with three users
  -checkToken($token):                              checks the token for the user id 
  -createToDoList($listname, $token):               creates a new ToDoList item
  -deleteToDoList($id, $token):                     deletes a ToDoList item
  -deleteAllToDoList($token)                        deletes all ToDoList items of an user
  -createToDoItem($itemname, $listnummer, 
    $itemdescription, $itempriority, $dueDate, 
    $itemstate, $token):                            creates a new ToDoItem Item
  -deleteToDoItem($id, $listnummer, $token)         deletes a ToDoItem Item
  -deleteAllToDoItem($listnummer, $token)           deletes all ToDoItem Items of a list
  -changeToDoItem($id, $itemname, $listnummer, 
   $itemdescription, $itempriority, 
   $dueDate, $itemstate, $token):                   changes a ToDoItem Item
  -changeState($id, $itemstate, 
   $listnummer, $token):                            changes the state of a ToDoItem Item
  -getAllUsers()                                    gets all user information
  -getAllLists()                                    gets all lists existing
  -getAllItemsOfAList($listnummer, $token)          gets all ToDoItem Items of a list 
  -getOneItemsOfAList($listnummer, $id, $token)     gets one ToDoItem Item of a list
  -getAllListsOfAUser($token)                       gets all ToDoList Items of an user
  -getAllItemsOFAUser($token)                       gets all ToDoListItems Items of an user
  -Login($benutzername, $passwort)                  check the login data and returns the token

  -UserAnalysis($token)
  */

  function init()
  /* 
    input: none

    output: 
      creates database todoliste with this tables:
      - user (id, username, token, passwort, reg_date)
      - todolist(id, listname, creator(references user.id))
      - todoitem(id, itemname, listnummer(references todolist.id), itemdiscription, itempriority, dueDate, itemstate)
      user insert:
      - 1, Hierhammer, XXXX, absolut, [current date]
      - 2, Haase, XXXX, streng, [current date]
      - 3, Gommlich, XXXX, geheim, [current date]

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    // Create database
    $sql = "CREATE DATABASE todoliste";
    if ($conn->query($sql) === FALSE) {
      echo "Error creating database: " . $conn->error;
    }
    $conn->close();
    // sql to create table
    $sql = "CREATE TABLE user (
      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      username VARCHAR(30) NOT NULL,
      token VARCHAR(120) NOT NULL,
      passwort VARCHAR(50),
      reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
    $sql2 = "INSERT INTO user (username, token, passwort)
            VALUES ('Hierhammer', '4b3403665fea6', 'absolut'),
                    ('Haase', '4b3793665dxa8', 'streng'),
                    ('Gommlich', '4237d3665a5d8', 'geheim')";
    $sql3 = "CREATE TABLE todolist(
      id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
      listname VARCHAR(30) NOT NULL,
      creator INT(6) UNSIGNED NOT NULL,
      FOREIGN KEY (creator) REFERENCES user (id))";
    $sql4 = "CREATE TABLE todoitem(
          id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
          itemname VARCHAR(30) NOT NULL,
          listnummer INT(6) UNSIGNED NOT NULL,
          itemdiscription VARCHAR(150) NOT NULL,
          itempriority INT(2) NOT NULL,
          dueDate date NOT NULL,
          itemstate VARCHAR(1),
          FOREIGN KEY (listnummer) REFERENCES todolist (id) ON DELETE CASCADE)";

    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    if ($conn->query($sql) === FALSE) {
      echo "Error creating table: " . $conn->error;
    } else {
      $conn->query($sql2);
      $conn->query($sql3);
      $conn->query($sql4);
    }
  }

  function checkToken($token)
  /* 
    input: 
      -$token = VARCHAR(120) NOT NULL

    output: ID of the user

    return: ID of the user / NULL
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $ergebnis = NULL;

    $query5 = 'SELECT id from user
              WHERE token ="' . $token . '" ';
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    $rslt = $mysqli->query($query5);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }

    for ($x = 0; $row = mysqli_fetch_assoc($rslt); $x++) {
      $ergebnis =  $row["id"];
    }
    if (empty($ergebnis)) {
      return NULL;
    } else {
      return $ergebnis;
    }
  }

  function createToDoList($listname, $token)
  /* 
    input: 
      -$listname = VARCHAR(30) NOT NULL
      -$token = VARCHAR(120) NOT NULL

    output: new todolist item / False Token: NULL

    return:an Array with a dictionary per Index / NULL

    Example:
    echo(ergebnis[1]["listname"])
    -> "Beispielname" 
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $checked = checkToken($token);
    $query5 = "INSERT INTO todolist (listname, creator) 
                VALUES(?, ?)";
    $sql = "SELECT id, listname, creator FROM todolist WHERE creator like  '$checked'  and listname like '$listname'";
    if ($checked != NULL) {
      $mysqli = new mysqli($servername, $username, $password, $dbname);
      if ($mysqli->connect_errno) {
        die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
      }
      $stmt = $mysqli->prepare($query5);
      $stmt->bind_param("ss", $listname, $checked);
      $stmt->execute();
      $stmt = $mysqli->query($sql);
      for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
        $ergebnis[$x] =  array("id" => $row["id"], "listname" => $row["listname"], "creator" => $row["creator"]);
      }
      return $ergebnis;
    } else {
      return NULL;
    }
  }

  function deleteToDoList($id, $token)
  /* 
    input: 
      -$id = INT(6) -> Primary Key of todolist item
      -$token = VARCHAR(120) NOT NULL
    
    output: delete todolist item

    return: TRUE / NULL (in case of a wrong token)
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $checked = checkToken($token);
    if ($checked != NULL) {
      $query6 = "DELETE FROM todolist
                WHERE id = (?) AND creator like '%$checked%'";
      $mysqli = new mysqli($servername, $username, $password, $dbname);
      if ($mysqli->connect_errno) {
        die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
      }
      $stmt = $mysqli->prepare($query6);
      $stmt->bind_param("s", $id);
      $stmt->execute();
      return TRUE;
    } else {
      return NULL;
    }
  }

  function deleteAllToDoList($token)
  /* 
    input: 
      -$token = VARCHAR(120) NOT NULL

    output: delete all todolist items of an user

    return: TRUE / NULL (in case of a wrong token)
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $checked = checkToken($token);
    if ($checked != NULL) {
      $query6 = "DELETE FROM todolist
                WHERE creator = (?)";
      $mysqli = new mysqli($servername, $username, $password, $dbname);
      if ($mysqli->connect_errno) {
        die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
      }
      $stmt = $mysqli->prepare($query6);
      $stmt->bind_param("s", $checked);
      $stmt->execute();
      return TRUE;
    } else {
      return NULL;
    }
  }

  function createToDoItem($itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate, $token)
  /* 
    input: 
      -$itemname = VARCHAR(30) NOT NULL
      -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
      -$itemdescription = VARCHAR(150), NOT NULL
      -$itempriority = INT(2), NOT NULL
      -$dueDate = JJJJ-MM-DD, NOT NULL
      -$itemstate = VARCHAR(1), NOT NULL
      -$token = VARCHAR(120) NOT NULL

    output: new todoitem item / NULL
    return:an Array with a dictionary per Index / NULL

    Example:
    echo(ergebnis[1]["itemname"])
    -> "Beispielname" 
  */
  {
    $count = 0;
    $checked = getAllListsOfAUser($token);
    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        if ($checked[$x]['id'] == $listnummer) {
          $count = $count + 1;
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";
        $query7 = "INSERT INTO todoitem (itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate) 
                VALUES(?, ?, ?, ?, ?, ?)";
        $sql = "SELECT id, itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate FROM todoitem WHERE itemname like  '$itemname'  and listnummer like '$listnummer' and itemdiscription like '$itemdescription'";
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        if ($mysqli->connect_errno) {
          die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $stmt = $mysqli->prepare($query7);
        $stmt->bind_param("ssssss", $itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate);
        $stmt->execute();
        $stmt = $mysqli->query($sql);
        for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
          $ergebnis[$x] =  array("id" => $row["id"], "itemname" => $row["itemname"], "listnummer" => $row["listnummer"], "itemdiscription" => $row["itemdiscription"], "itempriority" => $row["itempriority"], "dueDate" => $row["dueDate"], "itemstate" => $row["itemstate"]);
        }
        return $ergebnis;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }

  function deleteToDoItem($id, $token)
  /* 
    input: 
      -$id = INT(6) -> Primary Key of todoitem item
      -$token = VARCHAR(120) NOT NULL

    output: delete todoitem item / False Token: NULL

    return: NONE (in case of a wrong token) / TRUE
  */
  {
    $count = 0;
    $checked = getAllItemsOfAUser($token);

    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        for ($y = 0; $y < count($checked[$x]); $y++) {
          if ($checked[$x][$y]["id"] == $id) {
            $count = $count + 1;
          }
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";
        $query7 = "DELETE FROM todoitem
                WHERE id = (?)";
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        if ($mysqli->connect_errno) {
          die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $stmt = $mysqli->prepare($query7);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return TRUE;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }


  function deleteAllToDoItem($listnummer, $token)
  /* 
    input: 
      -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
      -$token = VARCHAR(120) NOT NULL
    
    output: delete all todoitem items of a list / False Token: NULL

    return: NONE /NULL
  */
  {
    $count = 0;
    $checked = getAllListsOfAUser($token);
    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        if ($checked[$x]['id'] == $listnummer) {
          $count = $count + 1;
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";
        $query7 = "DELETE FROM todoitem
                WHERE listnummer = (?)";
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        if ($mysqli->connect_errno) {
          die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $stmt = $mysqli->prepare($query7);
        $stmt->bind_param("s", $listnummer);
        $stmt->execute();
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }

  function changeToDoItem($id, $itemname, $itemdescription, $itempriority, $dueDate, $itemstate, $listid, $token)
  /* 
    input: 
      -$id= INT(6) -> Primary Key of todoitem item
      -$itemname = VARCHAR(30) NOT NULL
      -$listid = Foreign Key has to be the todolist.id, NOT NULL
      -$itemdescription = VARCHAR(150), NOT NULL
      -$itempriority = INT(2), NOT NULL
      -$dueDate = JJJJ-MM-DD, NOT NULL
      -$itemstate = VARCHAR(1), NOT NULL
      -$token = VARCHAR(120) NOT NULL
    output: changed todoitem item

    return: TRUE / NULL (in case of a wrong token)
  */
  {
    $count = 0;
    $checked = getAllItemsOfAUser($token);

    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        for ($y = 0; $y < count($checked[$x]); $y++) {
          if ($checked[$x][$y]["id"] == $id) {
            $count = $count + 1;
          }
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";
        $query7 = "UPDATE todoitem 
              SET itemname =(?), listnummer =(?), itemdiscription=(?), itempriority=(?), dueDate=(?), itemstate=(?) 
              WHERE id = (?)";
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        if ($mysqli->connect_errno) {
          die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }
        $stmt = $mysqli->prepare($query7);
        $stmt->bind_param("sssssss", $itemname, $listid, $itemdescription, $itempriority, $dueDate, $itemstate, $id);
        $stmt->execute();
        return TRUE;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }
  
  function getAllUsers()
  /* 
    input: None

    output: all user information ("username", "id", "token", "passwort", "reg_date")

    return: an Array with a dictionary per Index

    Example:
    echo(ergebnis[1]["username"])
    -> "Beispielname"
  */

  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";

    // Create connection
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
    }
    $sql = "SELECT id, username, token, passwort, reg_date FROM user";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] = array("username" => $row["username"], "id" => $row["id"], "passwort" => $row["passwort"], "token" => $row["token"], "reg_date" => $row["reg_date"]);
    }
    return $ergebnis;
  }

  function getAllLists()
  /* 
    input: None

    output: all list information ("id", "listname", "creator")

    return: an Array with a dictionary per Index

    Example:
    echo(ergebnis[1]["listname"])
    -> "Beispielname"
  */

  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";

    // Create connection
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
    }
    $sql = "SELECT id, listname, creator FROM todolist";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] =  array("id" => $row["id"], "listname" => $row["listname"], "creator" => $row["creator"]);
    }
    return $ergebnis;
  }

  function getAllItemsOfAList($listnummer, $token)
  /* 
    input: 
    -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
    -$token = VARCHAR(120) NOT NULL


    output: all items of a list ("id", "itemname", "listnummer", "itemdiscription", "itempriority", "dueDate", "itemstate")

    return: an Array with a dictionary per Index /NULL

    Example:
    echo(ergebnis[1]["itemname"])
    -> "Beispielname"
  */

  {
    $count = 0;
    $checked = getAllListsOfAUser($token);
    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        if ($checked[$x]['id'] == $listnummer) {
          $count = $count + 1;
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";

        // Create connection
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($mysqli->connect_error) {
          die("Connection failed: " . $mysqli->connect_error);
        }
        $sql = 'SELECT id, itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate FROM todoitem WHERE listnummer = "' . $listnummer . '" ';
        $stmt = $mysqli->query($sql);

        for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
          $ergebnis[$x] =  array("id" => $row["id"], "itemname" => $row["itemname"], "listnummer" => $row["listnummer"], "itemdescription" => $row["itemdiscription"], "itempriority" => $row["itempriority"], "dueDate" => $row["dueDate"], "itemstate" => $row["itemstate"]);
        }
        return $ergebnis;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }

  function getOneItemOfAList($listnummer, $id, $token)
  /* 
    input: 
    -$id = INT(6) -> Primary Key of todoitem item
    -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
    -$token = VARCHAR(120) NOT NULL

    output: one item of a list ("id", "itemname", "listnummer", "itemdiscription", "itempriority", "dueDate", "itemstate")

    return: A dictionary / NULL

    Example:
    echo(ergebnis["itemname"])
    -> "Beispielname"
  */

  {
    $count = 0;
    $checked = getAllListsOfAUser($token);
    if ($checked != NULL) {
      for ($x = 0; $x < count($checked); $x++) {
        if ($checked[$x]['id'] == $listnummer) {
          $count = $count + 1;
        }
      }
      if ($count > 0) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "todoliste";

        // Create connection
        $mysqli = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($mysqli->connect_error) {
          die("Connection failed: " . $mysqli->connect_error);
        }
        $sql = "SELECT id, itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate FROM todoitem WHERE listnummer like '%$listnummer%' and id like '%$id%'";
        $stmt = $mysqli->query($sql);

        for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
          $ergebnis =  array("id" => $row["id"], "itemname" => $row["itemname"], "listnummer" => $row["listnummer"], "itemdescription" => $row["itemdiscription"], "itempriority" => $row["itempriority"], "dueDate" => $row["dueDate"], "itemstate" => $row["itemstate"]);
        }
        return $ergebnis;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  }

  function getAllListsOfAUser($token)
  /* 
    input: None

    output: all list information ("id", "listname", "creator")

    return: an Array with a dictionary per Index

    Example:
    echo(ergebnis[1]["listname"])
    -> "Beispielname"
  */

  {
    $checked = checkToken($token);
    if ($checked != NULL) {
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "todoliste";

      // Create connection
      $mysqli = new mysqli($servername, $username, $password, $dbname);
      // Check connection
      if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
      }
      $sql = 'SELECT id FROM todolist WHERE creator = "' . $checked . '"';
      $stmt = $mysqli->query($sql);

      for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
        $ergebnis[$x] =  array("id" => $row["id"], "name" => $row["name"], "creator" => $row["creator"]);
      }
      return $ergebnis;
    } else {
      return NULL;
    }
  }

  function getAllItemsOfAUser($token)
  /* 
    input: 
    -$token = VARCHAR(120) NOT NULL


    output: all items of a list ("id", "itemname", "listnummer", "itemdiscription", "itempriority", "dueDate", "itemstate")

    return: an Array with an Array per Index with a dictionary per Index /NULL

    Example:
    echo(result[0][0]["itemname"])
    -> "Beispielname"
  */
  {
    $storage = [];
    $result = [];
    $lists = getAllListsOfAUser($token);
    if ($lists != NULL) {
      for ($x = 0; $x < count($lists); $x++) {
        array_push($storage, $lists[$x]["id"]);
      }
      for ($x = 0; $x < count($storage) - 1; $x++) {
        array_push($result, getAllItemsOfAList($storage[$x], $token));
      }
      return $result;
    } else {
      return NULL;
    }
  }

  function Login($benutzername, $passwort)
  /* 
    input: 
    -$benutzername VARCHAR(120) NOT NULL
    -$passwort VARCHAR(120) NOT NULL

    output: token of the user / NULL

    return: STRING / NULL
  */

  {

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";

    // Create connection
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($mysqli->connect_error) {
      die("Connection failed: " . $mysqli->connect_error);
    }
    $sql = 'SELECT passwort, token FROM user WHERE username = "' . $benutzername . '"';
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] = array("passwort" => $row["passwort"], "token" => $row["token"]);
    }
    for ($x = 0; $x < count($ergebnis); $x++) {
      if ($passwort == $ergebnis[$x]["passwort"]) {
        return $ergebnis[$x]["token"];
      } else {
        return NULL;
      }
    }
  }

  function user_analysis($token)
    /*
    args:
    - $token = VARCHAR(120) NOT NULL

    return:
    - Array of Arrays
      1. Done Items
      2. Overdue Items
    */
  {
    $user           = checkToken($token);
    $items          = [];
    $done_items     = [];
    $overdue_items  = [];

    if ($user != null){
      $items = getAllListsOfAUser($token);

      for($i=0; $i < sizeof($items); $i++){
        if($items[$i][$i]["itemstate"] == "erledigt"){
          array_push($done_items, $items[$i]);
        }
      }

      for($i=0; $i < sizeof($items); $i++){
        if($items[$i][$i]["dueDate"] < date("Y-m-d")){
          array_push($overdue_items, $items[$i]);
        }
      }

      array_push($analysis_array, $done_items);
      array_push($analysis_array, $overdue_items);
      return $analysis_array;
    }
    return null;
  }

  ?>