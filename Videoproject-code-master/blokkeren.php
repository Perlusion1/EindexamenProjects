<?php
$userrole = array("admin", "eigenaar");
require_once("./security.php");
?>

<?php
if (isset($_POST['create'])) {
    require_once("./classes/VideoClass.php");
    VideoClass::insert_film_database($_POST);
}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script type="text/javascript" src="http://netdna.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet"
          type="text/css">
    <link href="style.css" rel="stylesheet" type="text/css">
    <style>
        .header {
            font-size: 24px;
            padding: 20px;
        }

        th {
            min-width: 250px;
        }
    </style>
</head>
<body>
<div class="section">
    <div class="container">
        <?php


        require_once("classes/LoginClass.php");
        if (isset($_POST['updateBlock'])) {
            include('connect_db.php');

            $sql = "UPDATE	`login` 
                     SET 		`geblokkeerd`		=	'" . $_POST['blockSelect'] . "'
                     WHERE	    `idKlant`			=	 " . $_POST['idKlant'] . " ";

            // echo $sql;
            $database->fire_query($sql);
            $result = mysqli_query($connection, $sql);

            echo "<h3 style='text-align: center;' >Uw wijzigingen zijn verwerkt.</h3><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>";
            header("refresh:4;url=index.php?content=blokkeren");

        } else {
        ?>
        <div class="row">
            <div class="section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12"><h2>Gebruiker Blokkeren</h2></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li><a href="index.php?content=adminHomepage">Homepage</a></li>
                                <li><a href="index.php?content=videoToevoegen">Video's Toevoegen</a></li>
                                <li><a href="index.php?content=videosBeheren">Video's beheren</a></li>
                                <li><a href="index.php?content=verwijderFilm">Video's verwijderen</a></li>
                                <li><a href="index.php?content=beschikbaarMaken">Video's beschikbaar maken</a></li>
                                <li><a href="index.php?content=rolWijzigen">Gebruikerrol veranderen</a></li>
                                <li><a href="index.php?content=gebruikerVerwijderen">Gebruiker verwijderen</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <?php
                    require_once("classes/LoginClass.php");
                    require_once("classes/HireClass.php");
                    require_once("classes/SessionClass.php");

                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    // <Wijzigingsopdracht>
                    $dbname = "videotheek";
                    // </Wijzigingsopdracht>

                    // Create connection
                    $conn = new mysqli($servername, $username, $password, $dbname);
                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }


                    $sql = "SELECT * FROM `login`";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                            echo "
                        <table class=\"table table - responsive\">
                            <thead>
                            <tr>
                                <th>
                                        Naam:
                                </th>
                                <th>
                                        E-mail:
                                </th>
                                <th>
                                        Rol:
                                </th>
                                <th>
                                        Geblokkeerd:
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                        " . $row["naam"] . "
                                </td>
                                <td>
                                        " . $row['email'] . "
                                </td>
                                <td>
                                        " . $row['userrole'] . "
                                </td>
                                <td>
                                        " . $row['geblokkeerd'] . "
                                </td>
                                <td>
                                        <form role=\"form\" action='' method='post'>
                                            <select name='blockSelect'>
                                                <option value='1'>Blokkeren ( 1 )</option>
                                                <option value='0'>Deblokkeren ( 0 )</option>
                                                </select>
                                            <input type='hidden' class=\"btn btn-info\" name='idKlant' value='" . $row['idKlant'] . "'/>
                                            <input type='submit' class=\"btn btn-info\" name='updateBlock' value='Update Blokkade'>
                                            
                                        </form>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                            ";
                        }
                    } else {
                        echo "Geen resultaten<br><br><br><br><br><br><br>";
                    }
                    $conn->close();
                    ?>

                    <br><br>
                </div>
            </div>
            <?php

            }
            ?>
            </row>
        </div>
    </div>

</body>
</html>