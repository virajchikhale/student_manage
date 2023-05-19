<?php
include('../includes/connection.php');

$email=$_POST['email'];
$password=md5($_POST['password']);
$table=$_POST['table'];
$sql="UPDATE `admin_reg` SET `password`='".$password."'  WHERE email='".$email."'" ;
        

// if($table=="teacher_reg"){
//         $i="select * from hod_reg where department_id='".$department."'";
//         $x=mysql_fetch_array(mysql_query($i));
//         $sqlinsert="insert into teacher_reg(first_name, last_name, email,phone,password,report_to,department_id) 
//         values('".$fname."' , '".$lname."', '".$email."', '".$phoneno."', '".$password."', '".$x['id']."', '".$department."')";
// }else if($table=="principal_reg"){
//         $sqlinsert="insert into principal_reg(first_name, last_name, email,phone,password) 
//         values('".$fname."' , '".$lname."', '".$email."', '".$phoneno."', '".$password."')";
// }else if($table=="hod_reg"){
//         $i="update department set status='1' where id='".$department."'";
//         $x=mysql_fetch_array(mysql_query($i));
//         $sqlinsert="insert into hod_reg(first_name, last_name, email,phone,password,report_to,department_id) 
//         values('".$fname."' , '".$lname."', '".$email."', '".$phoneno."', '".$password."', '".$report_to."', '".$department."')";;
// }else if($table=="admin_reg"){
//         $sqlinsert="insert into admin_reg(first_name, last_name, email,phone,password) 
//         values('".$fname."' , '".$lname."', '".$email."', '".$phoneno."', '".$password."')";;
// }

$res=mysql_query($sql);

        // echo $sql;
if($res) {
        echo "0";
        }
else{
        echo "1";
}
?>