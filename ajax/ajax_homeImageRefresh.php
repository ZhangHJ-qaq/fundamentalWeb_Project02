<?php
include_once "../utilities/dbconfig.php";
include_once "../utilities/PDOAdapter.php";
try {
    $pdoAdapter=new PDOAdapter(HEADER,DBACCOUNT,DBPASSWORD,DBNAME);
    $imageArray=$pdoAdapter->selectRows("select imageID,title,description,path from travelimage order by rand()
limit 6");
    echo json_encode($imageArray);
}catch (PDOException $PDOException){
    header("location:../error.php?errorCode=0;");
}