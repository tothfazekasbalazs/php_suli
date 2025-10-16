<?php

//http://localhost/github_teszt/php_suli/index.php

$servername = "localhost";
$username = "php_tesztelo";
$password = "/dweo7]hHEycLMCE";
$dbname = "suli_teszt";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['brand']) && !empty($_POST['brand']) && isset($_POST['stock']) && is_numeric($_POST['stock']) && isset($_POST['sold']) && is_numeric($_POST['sold'])) {
    if (isset($_POST['id'])) {
      $sql = "UPDATE cars SET brand = '" . $conn->real_escape_string($_POST['brand']) . "', 
                stock = " . (int)$_POST['stock'] . ", sold = " . (int)$_POST['sold'] . "
                WHERE id = " . (int)$_POST['id'];
      $conn->query($sql);
      $id = (int)$_POST['id'];
    } else {
      $sql = "INSERT INTO cars (brand, stock, sold) VALUES ('" . $conn->real_escape_string($_POST['brand']) . "', " . (int)$_POST['stock'] . ", " . (int)$_POST['sold'] . ")";
      if ($conn->query($sql) === TRUE) {
        $id = $conn->insert_id;
      } else {
        echo "Hiba történt az adatbázis művelet során: " . $conn->error;
        $id = null;
      }
    }

    if ($id && isset($_FILES["brandLogo"]) && $_FILES["brandLogo"]["tmp_name"]) {
      $target_dir = "logos/";
      $extensions = ['jpg', 'jpeg', 'png', 'gif'];
      foreach ($extensions as $ext) {
        $oldFile = $target_dir . $id . "." . $ext;
        if (file_exists($oldFile)) {
          unlink($oldFile);
        }
      }

      $check = getimagesize($_FILES["brandLogo"]["tmp_name"]);

      if ($check !== false) {
        $fileExt = strtolower(pathinfo($_FILES["brandLogo"]["name"], PATHINFO_EXTENSION));
        if (in_array($fileExt, $extensions)) {
          if ($_FILES["brandLogo"]["size"] <= 1024000) {
            $target_file = $target_dir . $id . "." . $fileExt;
            if (move_uploaded_file($_FILES["brandLogo"]["tmp_name"], $target_file)) {
              echo "A fájl " . htmlspecialchars(basename($_FILES["brandLogo"]["name"])) . " sikeresen feltöltésre került.<br>";
            } else {
              echo "Hiba történt a fájl feltöltése során.<br>";
            }
          } else {
            echo "A fájl túl nagy.<br>";
          }
        } else {
          echo "Csak JPG, JPEG, PNG és GIF fájlok engedélyezettek.<br>";
        }
      } else {
        echo "A fájl nem egy kép.<br>";
      }
    }

    echo "Művelet sikeres!<br>";
  } else {
    echo "Kérjük, töltse ki az összes mezőt érvényes értékekkel!<br>";
  }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
  switch ($_GET['action']) {
    case "delete":
      $id = (int)$_GET['id'];
      $extensions = ['jpg', 'jpeg', 'png', 'gif'];
      foreach ($extensions as $ext) {
        $file = "logos/" . $id . "." . $ext;
        if (file_exists($file)) {
          unlink($file);
        }
      }

      $sql = "DELETE FROM cars WHERE id = " . $id;
      $conn->query($sql);
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
      break;

    case "update":
      $id = (int)$_GET['id'];
      $sql = "SELECT id, brand, stock, sold FROM cars WHERE id = " . $id;
      $result = $conn->query($sql);
      if ($row = $result->fetch_assoc()) {
        $update = $row;
      }
      break;
  }
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Autók kezelése</title>
  <style>
    table {
      border-collapse: collapse;
      width: 80%;
    }
    table, th, td {
      border: 1px solid #ccc;
    }
    th, td {
      padding: 8px;
      text-align: left;
    }
    img {
      max-height: 50px;
    }
  </style>
</head>
<body>

<h1>Autók kezelése</h1>

<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
  <?php if (isset($update)): ?>
    <label>Car brand: <input type="text" name="brand" value="<?php echo htmlspecialchars($update['brand']); ?>"></label><br>
    <label>Stock: <input type="text" name="stock" value="<?php echo htmlspecialchars($update['stock']); ?>"></label><br>
    <label>Sold: <input type="text" name="sold" value="<?php echo htmlspecialchars($update['sold']); ?>"></label><br>
    <input type="hidden" name="id" value="<?php echo (int)$update['id']; ?>"><br>
  <?php else: ?>
    <label>Car brand: <input type="text" name="brand"></label><br>
    <label>Stock: <input type="text" name="stock"></label><br>
    <label>Sold: <input type="text" name="sold"></label><br>
  <?php endif; ?>
  <label>Brand Logo: <input type="file" name="brandLogo" id="fileToUpload"></label><br>
  <input type="submit" value="Mentés">
</form>

<br>

<table>
  <tr>
    <th>ID</th>
    <th>Brand</th>
    <th>Stock</th>
    <th>Sold</th>
    <th>Logo</th>
    <th>Törlés</th>
    <th>Módosítás</th>
  </tr>

<?php
$sql = "SELECT id, brand, stock, sold FROM cars";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . (int)$row['id'] . '</td>';
    echo '<td>' . htmlspecialchars($row['brand']) . '</td>';
    echo '<td>' . (int)$row['stock'] . '</td>';
    echo '<td>' . (int)$row['sold'] . '</td>';

    $logoPath = null;
    $possibleExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($possibleExtensions as $ext) {
      $file = "logos/" . $row['id'] . "." . $ext;
      if (file_exists($file)) {
        $logoPath = $file;
        break;
      }
    }

    if ($logoPath) {
      echo '<td><img src="' . $logoPath . '" alt="Logo"></td>';
    } else {
      echo '<td>Nincs kép</td>';
    }

    echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . (int)$row['id'] . '" onclick="return confirm(\'Biztos törölni akarod?\');">Törlés</a></td>';
    echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?action=update&id=' . (int)$row['id'] . '">Módosítás</a></td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="7">Nincs rögzített autó.</td></tr>';
}
?>

</table>

</body>
</html>