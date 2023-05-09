<?php
//session_start();
error_reporting('E_All');

$con=mysql_connect("localhost","root",'');
	if(!$con)
		{
			//echo "connection not succesfull";
		}
	else
		{
			//echo "connection succesfull";
		}
		
mysql_select_db("student_management", $con);
?>