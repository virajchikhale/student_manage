<?php
include('../includes/connection.php');

$email=$_POST['email'];
$password=md5($_POST['password']);
$table=$_POST['table'];
$sql="SELECT * FROM $table WHERE `email` = '".$email."' AND `password`='".$password."'";
$result=mysql_query($sql);
$cont=mysql_num_rows($result);
$_SESSION["user"] = "$user";

        //echo $sql;
if($cont>=1) {
        echo "0";
        }
else{
        echo "1";
}
?>