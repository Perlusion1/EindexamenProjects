<?php
    require_once('MySqlDatabaseClass.php');
    require_once("LoginClass.php");
    require_once("SessionClass.php");

    class VerkoopClass
    {
        //Fields
        private $idWinkelmand;
        private $idUserWm;
        private $idProductWm;
        private $aantalWm;

        //Properties
        public function getIdWinkelmand()       { return $this->idWinkelmand; }
        public function getIdUserWm()           { return $this->idUserWm; }
        public function getIdProductWm()        { return $this->idProductWm; }
        public function getAantalWm()           { return $this->aantalWm; }

        public function setIdWinkelmand($value) { $this->idWinkelmand = $value; }
        public function setIdUserWm($value)     { $this->idUserWm = $value; }
        public function setIdProductWm($value)  { $this->idProductWm = $value; }
        public function setAantalWm($value)     { $this->aantalWm = $value; }

        // Constructor
        public function __construct()
        {
        }

        //Methods
        public static function insert_winkelmanditem_database($post)
        {
            $idUserWm = $_POST['idUser'];
            $idProductWm = $_POST['idProduct'];
            $idAantalWm = $_POST['amount'];

            global $database;

            $query = "INSERT INTO `winkelmand` (`idWinkelmand`, `idUserWm`, `idProductWm`, `aantalWm`, `dagProductWm`) 
                                        VALUES (NULL, '" . $idUserWm . "', '" . $idProductWm . "', " . $idAantalWm . ", " . $_POST['dagProduct'] . ")";

            $database->fire_query($query);

            $last_id = mysqli_insert_id($database->getDb_connection());
        }
        public static function clear_winkelmand()
        {
            global $database;

            $query = "DELETE FROM `winkelmand` WHERE `idUserWm` = " . $_SESSION['idUser'] . " ";

            $database->fire_query($query);
        }
        public static function remove_item_winkelmand($post)
        {
            global $database;

            $query = "DELETE FROM `winkelmand` WHERE `idUserWm` = " . $_SESSION['idUser'] . "
                                                        AND `idWinkelmand` = " . $post["idWinkelmand"] . " ";

            $database->fire_query($query);
        }
        public static function insert_bestelling_database($post, $priceTotal)
        {
            global $database;

            $datetime = date('Y-m-d');

            $query = "INSERT INTO `order` (`idOrder`, 
                                                `idUser`, 
                                                `totaalPrijs`,
                                                `orderdatum`) 
                      VALUES                    (NULL, 
                                                '" . $_SESSION['idUser'] . "', 
                                                '" . $priceTotal . "',
                                                '" . $datetime . "')";

            $database->fire_query($query);

            self::send_email();
        }
        public static function insert_order_in_orderregel($row, $priceTotal)
        {
            global $database;

            $sql = "SELECT `idOrder` from `order` WHERE `idUser` = '" . $_SESSION['idUser']. "' AND `totaalPrijs` = '" . $priceTotal . "'";

            $idOrderVoorRegels = $database->fire_query($sql);

            $idOrderVoorRegel = $idOrderVoorRegels->fetch_assoc();

            $query = "INSERT INTO `orderregel` (`idOrderregel`, 
                                        `idProduct`,
                                         `idOrder`,
                                        `prijsOr`,
                                        `aantal`) 
              VALUES                    (NULL, 
                                        '" . $row['idProductWm'] . "',
                                        '" . $idOrderVoorRegel['idOrder'] . "', 
                                        '" . $row['totaalPrijs'] . "', 
                                        '" . $row['aantalWm'] . "')";

            $database->fire_query($query);

            self::lower_amount_Artikelen($row);
            self::increase_amount_hired($row);
        }
        public static function insert_most_sold_winkelmanditem_database($post)
        {
            global $database;

            $datetime = date('Y-m-d');

            $query = "INSERT INTO `order` (`idOrder`, 
                                                `idUser`, 
                                                `totaalPrijs`,
                                                `orderdatum`) 
                      VALUES                    (NULL, 
                                                '" . $_SESSION['idUser'] . "', 
                                                '" . $priceTotal . "',
                                                '" . $datetime . "')";

            $database->fire_query($query);

            self::insert_most_sold_winkelmand_orderregel();
    //        self::lower_amount_Artikelen($post);
    //        self::send_email($post, $last_id, $ophaaldatum);
    //        self::increase_amount_hired($post);
    //        self::update_beschikbaar();
        }
        public static function insert_most_sold_winkelmand_orderregel($row, $priceTotal)
        {
            global $database;

            $sql = "SELECT `idOrder` from `order` WHERE `idUser` = '" . $_SESSION['idUser']. "' AND `totaalPrijs` = '" . $priceTotal . "'";

            $idOrderVoorRegels = $database->fire_query($sql);

            $idOrderVoorRegel = $idOrderVoorRegels->fetch_assoc();

            $query = "INSERT INTO `orderregel` (`idOrderregel`, 
                                        `idProduct`,
                                         `idOrder`,
                                        `prijsOr`,
                                        `aantal`,
                                        `verkochtViaMeestVerkocht`) 
              VALUES                    (NULL, 
                                        '" . $row['idProductWm'] . "',
                                        '" . $idOrderVoorRegel['idOrder'] . "', 
                                        '" . $row['totaalPrijs'] . "', 
                                        '" . $row['aantalWm'] . "',
                                        '1')";

            $database->fire_query($query);

            self::lower_amount_Artikelen($row);
            self::increase_amount_sold($row);
        }
        public static function lower_amount_Artikelen($row)
        {
            global $database;

            $query = "UPDATE `producten`
                          SET `aantalBeschikbaar` = `aantalBeschikbaar` - '" . $row['aantalWm'] . "'
                          WHERE `idProduct` = '" . $row['idProductWm'] . "'";
            //echo $query;
            $database->fire_query($query);

        }
        private static function send_email()
        {
            $to = $_SESSION['emailAdres'];

            $subject = "Bevestigingsmail Bestelling Webshop AutoTrader";

            $message = "Geachte heer/mevrouw<br>";
            $message .= "Hartelijk dank voor het bestellen bij Webshop AutoTrader" . "<br>";
            $message .= "Wij wensen u veel plezier met uw aankoop.<br>";
            $message .= "Met vriendelijke groet," . "<br>";
            $message .= "Team AutoTrader" . "<br>";

            $headers = 'From: no-reply@WebshopAutoTrader.nl' . "\r\n";
            $headers .= 'Reply-To: webmaster@webshopAutoTrader.nl' . "\r\n";
            $headers .= 'Bcc: accountant@webshopAutoTrader.nl' . "\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
        }
        public static function increase_amount_sold($row)
        {
            global $database;

            $query = "UPDATE `producten` SET `aantalVerkocht` =  `aantalVerkocht` + '" .$row['aantalWm'] . "' WHERE `idProduct` = '" . $row['idProductWm'] ."'";

            $database->fire_query($query);
        }
        public static function get_all_orders()
        {
            global $database;

            $sql = "SELECT * FROM `order` WHERE `idUser` = " . $_SESSION['idUser'] . " ";

            $result = $database->fire_query($sql);

            return $result;
        }
        public static function get_regels_by_order($opgehaaldeOrders)
        {
            global $database;

            $sql = "SELECT * FROM `orderregel` INNER JOIN `producten` on orderregel.idProduct = producten.idProduct WHERE `idOrder` = " . $opgehaaldeOrders["idOrder"] . " ";

            $result2 = $database->fire_query($sql);

            foreach ($result2 as $opgehaaldeOrders2) {
                return $result2;
            }
        }
        public static function get_total_price_with_shipping()
        {
            global $database;
            $sql = "select `idWinkelmand`, `idProductWm`, `prijs`, `aantalWm`,`aantalWm` * `prijs` as totaalPrijs, `naam`  from `winkelmand`
                    INNER JOIN `producten` on winkelmand.idProductWm = producten.idProduct
                    where `idUserWm` = " . $_SESSION['idUser'] . " ";

            $priceWithShipping = $database->fire_query($sql);

            return $priceWithShipping;
        }
        public static function selecteer_totaal_prijs_winkelmand_items()
        {
            global $database;

            $sql =  "select sum(`aantalWm` * `prijs`) as totaalPrijs  from `winkelmand`
                    INNER JOIN `producten` on winkelmand.idProductWm = producten.idProduct
                    where `idUserWm` = " . $_SESSION['idUser'] . " ";

            $result = $database->fire_query($sql);

            $totalePrijs = $result->fetch_assoc();

            return $totalePrijs;
        }
        public static function get_most_popular_products()
        {
            global $database;

            $query = "SELECT * FROM producten 
                    ORDER BY aantalVerkocht DESC LIMIT 4";

            $result = $database->fire_query($query);

            return $result;
        }
        public static function get_most_popular_products_extra_page()
        {
            global $database;

            $query = "SELECT * FROM producten 
                    ORDER BY aantalVerkocht DESC LIMIT 10";

            $result = $database->fire_query($query);

            return $result;
        }
        public static function dagProductAanwezig()
        {

            global $database;

            $query = "SELECT * FROM `winkelmand` where `dagProductWm` = 1 AND `idUserWm` =  ".$_SESSION['idUser']." ";
            // echo $query;
            $result = $database->fire_query($query);

            return $result;
        }
        public static function selecteer_totaal_prijs_niet_dagproduct_winkelmand_items()
        {
            global $database;

            $sql =  "select `dagProduct`, sum(`aantalWm` * `prijs`) as totaalPrijs  from `winkelmand`
                    INNER JOIN `producten` on winkelmand.idProductWm = producten.idProduct
                    where `idUserWm` = " . $_SESSION['idUser'] . " AND `dagProduct` = 0 ";

            $result = $database->fire_query($sql);

            $totalePrijs = $result->fetch_assoc();

            return $totalePrijs;
        }
        public static function selecteer_totaal_prijs_dagproduct_winkelmand_items()
        {
            global $database;

            $sql =  "select sum(`aantalWm` * (`prijs` * 0.5)) as totaalPrijs, `dagProduct`  from `winkelmand`
                    INNER JOIN `producten` on winkelmand.idProductWm = producten.idProduct
                    where `idUserWm` = " . $_SESSION['idUser'] . " AND `dagProduct` = 1 ";

            $result = $database->fire_query($sql);

            $totalePrijs = $result->fetch_assoc();

            return $totalePrijs;
        }
    }
?>