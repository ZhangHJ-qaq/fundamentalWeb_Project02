<?php
include_once "utilities/PDOAdapter.php";
include_once "utilities/utilityFunction.php";
include_once "utilities/dbconfig.php";

$pdoAdapter = new PDOAdapter(HEADER, DBACCOUNT, DBPASSWORD, DBNAME);
$table = $pdoAdapter->selectRows("select ImageID,HashedID from travelimage");


for ($i = 4; $i <= 14; $i++) {
    $sql="select * from travelimagefavor where FavorID=?";
    $imageID=$pdoAdapter->selectRows($sql,array($i))[0]['ImageID'];
    $sql = "update travelimagefavor set ImageID=? where FavorID=?";
    $pdoAdapter->exec($sql, array(ImageIDToHashedID($imageID), $i));
}


function ImageIDToHashedID($imageID)
{
    global $table;
    for ($i = 0; $i <= count($table) - 1; $i++) {
        if ($table[$i]['ImageID'] == $imageID) {
            return $table[$i]['HashedID'];
        }
    }
    return null;
}
