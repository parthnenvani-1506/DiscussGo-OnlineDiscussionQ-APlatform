<?php
    $host="localhost:3307";
    //  $host="localhost";
    $username="root";
    $password=NULL;
    $database="discussgo";

    $conn=new mysqli($host,$username,$password,$database);
    if($conn->connect_error)
    {
        die("not connected".$conn->connect_error);
    }
?>