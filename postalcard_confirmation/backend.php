<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Benutzer Filtern</title>
  </head>
  <body>
    <?php
    require 'functions.php';
    $error = init();
    if ($error) die(var_dump($error));
    ?>

    <div>
      <table>
        <tr>
          <th>Vorname</th>
          <th>Nachname</th>
          <th>Email</th>
          <th>Abonierte Newsletter</th>
        </tr>

        <?php
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $query = mysqli_query($conn, "SELECT * FROM postalcard_confirmations ORDER BY firstname");
        $result = mysqli_fetch_assoc($query);

        print_r($result);
         ?>
        <tr>
          <td></td>
          <td></td>
          <td>
            <ul>
              <li></li>
            </ul>
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>
