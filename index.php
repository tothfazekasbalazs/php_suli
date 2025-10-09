<?php

// 

$servername = "localhost";
$username = "php_tesztelo";
$password = "_0ZOss-L!]YIP)dc";
$dbname = "php_suli";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if(isset($_POST['id'])) {
    $sql = "UPDATE cars SET brand = '".$_REQUEST['brand']."', 
            stock = ".$_REQUEST['stock'].", sold = ".$_REQUEST['sold']."
            WHERE id = ".$_POST['id'];
  }
  else {
    $sql = "INSERT INTO cars (brand, stock, sold) 
    VALUES ('".$_REQUEST['brand']."',".$_REQUEST['stock'].",".$_REQUEST['sold'].")";
  }
  $result = $conn->query($sql);

  if(!isset($_POST['id'])) {
    $_POST['id'] = $conn->insert_id;
  }

  if($_FILES["brandLogo"]["tmp_name"]) {
    /* file upload */
    $target_dir = "logos/";
    $target_file = $target_dir . $_POST['id'];
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $check = getimagesize($_FILES["brandLogo"]["tmp_name"]);
    if($check !== false) {
      echo "File is an image - " . $check["mime"] . ".";
      $fileExt = preg_split("/\//",$check["mime"]);
      $target_file = $target_file.".". $fileExt[1];
      $uploadOk = 1;
    } else {
      echo "File is not an image.";
      $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["brandLogo"]["size"] > 1024000) {
      echo "Sorry, your file is too large.";
      $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
      echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
      $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
      echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } 
    else {
      if (move_uploaded_file($_FILES["brandLogo"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars( basename( $_FILES["brandLogo"]["name"])). " has been uploaded.";
      } else {
        echo "Sorry, there was an error uploading your file.";
      }
    }
  }


}
elseif($_SERVER["REQUEST_METHOD"] == "GET" and isset($_GET['action'])) {
  switch($_GET['action']) {
    case "delete":
      $sql = "DELETE FROM cars WHERE id = ".$_REQUEST['id'];
      $result = $conn->query($sql);
    break;

    case "update":
      $sql = "SELECT id, brand, stock, sold FROM cars WHERE id = ".$_REQUEST['id'];
      $result = $conn->query($sql);
      if($row = $result->fetch_assoc()) {
        $update = $row;
      }
    break;
  }

}
?> 
<!DOCTYPE html>
<html>
<body>

<h1>My third PHP page</h1>

<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="multipart/form-data">
  <?php

  if(isset($update)) {
    echo "
        Car brand: <input type=\"text\" name=\"brand\" value=\"".$update['brand']."\"><br>
        stock: <input type=\"text\" name=\"stock\" value=\"".$update['stock']."\"><br>
        sold: <input type=\"text\" name=\"sold\" value=\"".$update['sold']."\"><br>
        <input type=\"hidden\" name=\"id\" value=\"".$update['id']."\"><br>";
  }
  else {
    ?>
    Car brand: <input type="text" name="brand"><br>
    stock: <input type="text" name="stock"><br>
    sold: <input type="text" name="sold"><br>
    <?php
  }
?>
  <input type="file" name="brandLogo" id="fileToUpload"><br>
  <input type="submit" value="Mentés">
</form>
<table>
<?php
$sql = "SELECT id, brand, stock, sold FROM cars";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
        echo '  <tr>';
        foreach($row as $data) {
            echo "      <td>$data</td>";
        }
        echo "      <td><a href=\"index.php?action=delete&id=".$row['id']."\">Törlés</a></td>";
        echo "      <td><a href=\"index.php?action=update&id=".$row['id']."\">Módosítás</a></td>";
    echo '  </tr>';
  }
} 

?>
</table>

</body>
</html>