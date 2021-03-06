<?php
require_once('MySqlDatabaseClass.php');
require_once("LoginClass.php");
require_once("SessionClass.php");

    class KlachtClass
    {
        //Fields
        private $idKlacht;
        private $idUserKlacht;
        private $klacht;

        public function getIdKlacht()       { return $this->idKlacht; }
        public function getidUserKlacht()   { return $this->idUserKlacht; }
        public function getKlacht()         { return $this->klacht; }

        public function setIdKlacht($value) { $this->idKlacht = $value; }
        public function setidUserKlacht($value)   { $this->idUserKlacht = $value; }
        public function setKlacht($value)   { $this->klacht = $value; }

        // Constructor
        public function __construct()
        {
        }

        //Methods
        public static function insert_klacht_into_database($klacht)
        {
            global $database;

            $query = "INSERT INTO `klacht` (`idKlacht`, `idUserKlacht`, `klacht`) 
                      VALUES (NULL, '" . $_SESSION['idUser'] . "', '" . $klacht . "')";

            $database->fire_query($query);

            $last_id = mysqli_insert_id($database->getDb_connection());

            self::send_email($klacht);
        }
        public static function send_email($klacht)
        {
            $to = $_SESSION['emailAdres'];

            $subject = "Bevestigingsmail Klacht Webshop AutoTrader";

            $message = "Geachte heer/mevrouw<br>";
            $message .= "Bedankt voor het indienen van uw klacht." . "<br><br>";
            $message .= "Uw bericht: " . $klacht . "<br>";
            $message .= "Wij nemen spoedig contact met u op om dit probleem op te lossen.<br>";
            $message .= "Met vriendelijke groet," . "<br>";
            $message .= "Marielle van Dijk" . "<br>";

            $headers = 'From: no-reply@WebshopAutoTrader.nl' . "\r\n";
            $headers .= 'Reply-To: webmaster@webshopAutoTrader.nl' . "\r\n";
            $headers .= 'Bcc: accountant@webshopAutoTrader.nl' . "\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
        }
        public static function get_all_klachten()
        {
            global $database;

            $query = "SELECT * FROM `klacht`";

            $result = $database->fire_query($query);

            return $result;
        }
        public static function get_email_klant_with_klacht($row)
        {
            global $database;

            $query = "SELECT *, users.naam FROM `users` INNER JOIN `klacht` on `idUserKlacht` = `idUser` WHERE `idUser` = '" . $row['idUserKlacht'] . "'";

            $result = $database->fire_query($query);

            return $result;
        }
    }
?>