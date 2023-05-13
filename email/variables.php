<?php
    
    $system_name = "Student Management";
    $mail_id="codelikhoo@gmail.com";
    $password="jimapdcykodlxxpy";

    $email=$_REQUEST["q"];
    $otp = $_REQUEST["otp"];
    $type = $_REQUEST["type"];
    $position = $_REQUEST["position"];

    if($type=="reg_otp"){
        if($position=="principal"){
            $subject = "OTP for Principal Confirmation";
            $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
        }else if($position=="hod"){
            $subject = "OTP for HOD Confirmation";
            $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
        }else if($position=="teacher"){
            $subject = "OTP for Teacher Confirmation";
            $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
        }else if($position=="admin"){
            $subject = "OTP for Admin Confirmation";
            $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
        }
    }else if($type=="forgot_otp"){
        if($position=="principal"){
            $subject = "OTP for Principal Confirmation";
            $message = "Dear User, your OTP for password change is <b><u> $otp </u></b>";
        }else if($position=="hod"){
            $subject = "OTP for HOD Confirmation";
            $message = "Dear User, your OTP for password change is <b><u> $otp </u></b>";
        }else if($position=="teacher"){
            $subject = "OTP for Teacher Confirmation";
            $message = "Dear User, your OTP for password change is <b><u> $otp </u></b>";
        }else if($position=="admin"){
            $subject = "OTP for Admin Confirmation";
            $message = "Dear User, your OTP for password change is <b><u> $otp </u></b>";
        }
    }else if($type=="thanks"){
        if($position=="principal"){
              $subject = "Welcome to ".$system_name." System";
              $message = "Thank you for registering with us as ".$position.".\n You can now enjoy all the features ".$system_name." ...";
        }else if($position=="hod"){
            $subject = "Welcome to ".$system_name." System";
            $message = "Thank you for registering with us as ".$position.".\n You can now enjoy all the features ".$system_name." ...";
        } else if($position=="teacher"){
            $subject = "Welcome to ".$system_name." System";
            $message = "Thank you for registering with us as ".$position.".\n You can now enjoy all the features ".$system_name." ...";
        }  else if($position=="admin"){
            $subject = "Welcome to ".$system_name." System";
            $message = "Thank you for registering with us as ".$position.".\n You can now enjoy all the features ".$system_name." ...";
        } 
    }else if($type=="pass_change_alert"){
        if($position=="principal"){
            $subject = "Alert from ".$system_name." System";
            $message = "Password for ".$email." has been changed as ".$position."";
        }else if($position=="hod"){
            $subject = "Alert from ".$system_name." System";
            $message = "Password for ".$email." has been changed as ".$position."";
        } else if($position=="teacher"){
            $subject = "Alert from ".$system_name." System";
            $message = "Password for ".$email." has been changed as ".$position."";
        }  else if($position=="admin"){
            $subject = "Alert from ".$system_name." System";
            $message = "Password for ".$email." has been changed as ".$position."";
        } 
    }
?>