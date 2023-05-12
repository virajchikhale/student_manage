<?php
include('../../includes/connection.php');
//error_reporting('E_ALL');
$email = $_POST['email'];

        $sql="SELECT * from admin_reg where email='".$email."'";
        $result=mysql_query($sql);
        $cnt=mysql_num_rows($result);
        $otp = (rand(100000,999999));
        if($cnt > 0) {
                echo $otp;
                }
        else{
                echo "1";
        }
?>