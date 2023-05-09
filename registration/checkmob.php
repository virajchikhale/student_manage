<?php
include("../includes/connection.php");
//error_reporting('E_ALL');
$mob = $_POST['mob'];
$type = $_POST['type'];

        $sql="SELECT * from '".$type."' where phoneno='".$mob."'";
        $result=mysql_query($sql);
        $cnt=mysql_num_rows($result);
        if($cnt > 0) {
                echo "1";
                }
        else{
                echo"0";
        }
?>