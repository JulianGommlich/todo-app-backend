<!DOCTYPE HTML>
<html>

<head>
  <title>Funktionen</title>
</head>

<body>

  <?php

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
      token VARCHAR(30) NOT NULL,
      passwort VARCHAR(50),
      reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)";
    $sql2 = "INSERT INTO user (username, token, passwort)
            VALUES ('Hierhammer', 'XXXX', 'absolut'),
                    ('Haase', 'XXXX', 'streng'),
                    ('Gommlich', 'XXXX', 'geheim')";
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

  function createToDoList($listname, $creator)
  /* 
    input: 
      -$listname = VARCHAR(30) NOT NULL
      -$creator = Foreign Key has to be the user.id, NOT NULL

    output: new todolist item

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $query5 = "INSERT INTO todolist (listname, creator) 
                VALUES(?, ?)";
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query5);
    $stmt->bind_param("ss", $listname, $creator);
    $stmt->execute();
  }

  function deleteToDoList($id)
  /* 
    input: 
      -$id = INT(6) -> Primary Key of todolist item
    
    output: delete todolist item

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $query6 = "DELETE FROM todolist
                WHERE id = (?)";
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query6);
    $stmt->bind_param("s", $id);
    $stmt->execute();
  }

  function deleteAllToDoList($creator)
  /* 
    input: 
      -$creator = Foreign Key has to be the user.id, NOT NULL

    output: delete all todolist items of an user

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $query6 = "DELETE FROM todolist
                WHERE creator = (?)";
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query6);
    $stmt->bind_param("s", $creator);
    $stmt->execute();
  }
  
  function createToDoItem($itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate)
  /* 
    input: 
      -$itemname = VARCHAR(30) NOT NULL
      -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
      -$itemdescription = VARCHAR(150), NOT NULL
      -$itempriority = INT(2), NOT NULL
      -$dueDate = JJJJ-MM-DD, NOT NULL
      -$itemstate = VARCHAR(1), NOT NULL

    output: new todoitem item

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $query7 = "INSERT INTO todoitem (itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate) 
                VALUES(?, ?, ?, ?, ?, ?)";
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query7);
    $stmt->bind_param("ssssss", $itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate);
    $stmt->execute();
  }
  

  function deleteToDoItem($id)
  /* 
    input: 
      -$id = INT(6) -> Primary Key of todoitem item
    
    output: delete todoitem item

    return: NONE
  */
  {
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
  }
  function deleteAllToDoItem($listnummer)
  /* 
    input: 
      -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
    
    output: delete all todoitem items of a list

    return: NONE
  */
  {
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
  }
  function changeToDoItem($id, $itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate)
  /* 
    input: 
      -$id= INT(6) -> Primary Key of todoitem item
      -$itemname = VARCHAR(30) NOT NULL
      -$listnummer = Foreign Key has to be the todolist.id, NOT NULL
      -$itemdescription = VARCHAR(150), NOT NULL
      -$itempriority = INT(2), NOT NULL
      -$dueDate = JJJJ-MM-DD, NOT NULL
      -$itemstate = VARCHAR(1), NOT NULL

    output: changed todoitem item

    return: NONE
  */
  {
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
    $stmt->bind_param("sssssss", $itemname, $listnummer, $itemdescription, $itempriority, $dueDate, $itemstate, $id);
    $stmt->execute();
  }

  function changeState($id, $itemstate)
    /* 
    input: 
      -$id= INT(6) -> Primary Key of todoitem item
      -$itemstate = VARCHAR(1), NOT NULL

    output: changed todoitem state

    return: NONE
  */
  {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todoliste";
    $query7 = "UPDATE todoitem 
              SET  itemstate=(?) 
              WHERE id = (?)";
    $mysqli = new mysqli($servername, $username, $password, $dbname);
    if ($mysqli->connect_errno) {
      die('Verbindungsfehler (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $stmt = $mysqli->prepare($query7);
    $stmt->bind_param("ss", $itemstate, $id);
    $stmt->execute();
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
      die("Connection failed: " . $mysqli->connect_error);}
    $sql = "SELECT id, username, token, passwort, reg_date FROM user";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] = array("username" => $row["username"], "id" => $row["id"],"passwort" => $row["passwort"], "token" => $row["token"], "reg_date" => $row["reg_date"]);
    
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
      die("Connection failed: " . $mysqli->connect_error);}
    $sql = "SELECT id, listname, creator FROM todolist";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] =  array( "id" => $row["id"],"listname" => $row["listname"], "creator" => $row["creator"]);
    
    }
    return $ergebnis;
  }
  function getAllItemsOfAList($listnummer)
  /* 
    input: 
    -$listnummer = Foreign Key has to be the todolist.id, NOT NULL

    output: all items of a list ("id", "itemname", "listnummer", "itemdiscription", "itempriority", "dueDate", "itemstate")

    return: an Array with a dictionary per Index

    Example:
    echo(ergebnis[1]["itemname"])
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
      die("Connection failed: " . $mysqli->connect_error);}
    $sql = "SELECT id, itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate FROM todoitem WHERE listnummer like '%$listnummer%' ";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis[$x] =  array( "id" => $row["id"],"itemname" => $row["itemname"], "listnummer" => $row["listnummer"], "itemdescription" =>$row["itemdiscription"], "itempriority" => $row["itempriority"], "dueDate" =>$row["dueDate"], "itemstate"=> $row["itemstate"]);
    
    }
    return $ergebnis;
  }

  function getOneItemOfAList($listnummer, $id)
  /* 
    input: 
    -$id = INT(6) -> Primary Key of todoitem item
    -$listnummer = Foreign Key has to be the todolist.id, NOT NULL

    output: one item of a list ("id", "itemname", "listnummer", "itemdiscription", "itempriority", "dueDate", "itemstate")

    return: A dictionary 

    Example:
    echo(ergebnis["itemname"])
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
      die("Connection failed: " . $mysqli->connect_error);}
    $sql = "SELECT id, itemname, listnummer, itemdiscription, itempriority, dueDate, itemstate FROM todoitem WHERE listnummer like '%$listnummer%' and id like '%$id%'";
    $stmt = $mysqli->query($sql);

    for ($x = 0; $row = mysqli_fetch_assoc($stmt); $x++) {
      $ergebnis =  array( "id" => $row["id"],"itemname" => $row["itemname"], "listnummer" => $row["listnummer"], "itemdescription" =>$row["itemdiscription"], "itempriority" => $row["itempriority"], "dueDate" =>$row["dueDate"], "itemstate"=> $row["itemstate"]);
    
    }
    return $ergebnis;
  }
  
  
 
  ?>
</body>
</html>