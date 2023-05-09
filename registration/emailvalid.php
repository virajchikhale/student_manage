<?php
include('../includes/connection.php');
//error_reporting('E_ALL');
$email = $_POST['email'];
$type = $_POST['type'];

        $sql="SELECT * from $type where email='".$email."'";
        $result=mysql_query($sql);
        $cnt=mysql_num_rows($result);
        $otp = (rand(100000,999999));
        if($cnt > 0) {
                echo "1";
                }
        else{
                echo $otp;
        }
?>