<?php
include_once "../utilities/dbconfig.php";
include_once "../utilities/PDOAdapter.php";
if (isset($_GET['ISO'])) {
    try {
        $pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
        $sql = "select distinct AsciiName,GeoNameID from geocities where CountryCodeISO=? order by AsciiName ";
        $cityList = $pdoAdapter->selectRows($sql, array($_GET['ISO']));
        $output = json_encode($cityList);
        echo $output;

    } catch (PDOException $exception) {

    }
}
