<?php
session_start();
include('../../includes/connection.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="au theme template">
    <meta name="author" content="Hau Nguyen">
    <meta name="keywords" content="au theme template">

    <!-- Title Page-->
    <title>Admin Forgot</title>

    <!-- Fontfaces CSS-->
    <link href="../css/font-face.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="../vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="../vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="../vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="../vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="../vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="../vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="../vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="../vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="../vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="../css/theme.css" rel="stylesheet" media="all">

</head>

<body onload="disable()" class="animsition">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#">
                                <img src="../images/icon/logo.png" alt="CoolAdmin">
                            </a>
                        </div>
                        <div class="login-form">
                            <form action="" method="post">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" type="email" onchange=emailvalid() name="email" placeholder="Email">
                                </div>
                                <div class="form-group"  id="otp">
                                    <input type="text" class="form-control form-control-user" id="otp" onkeyup=otp1() onchange=aaaa() name="otp" required
                                    placeholder="Plese enter your OTP">
                                </div>
                                <div class="form-group"  id="pass">
                                    <input type="password" class="form-control form-control-user" id="pass1" onchange=passvalid() name="pass" required
                                    placeholder="Plese enter New password">
                                </div>
                                <div class="form-group"  id="cpass">
                                    <input type="password" class="form-control form-control-user" id="cpass1" onchange=passcon() name="cpass" required
                                    placeholder="Confirm your New password">
                                </div>
                                <div class="login-checkbox">
                                    <label>
                                        <a href="../forgot_password">Forgotten Password?</a>
                                    </label>
                                </div>
                                <button name="submit"  onclick=response() class="au-btn au-btn--block au-btn--green m-b-20" type="submit">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    
<script>

function passvalid() {
      var pass = $('#pass1').val();
      
      //alert(pass.length);
      var len = pass.length;
      //alert(len);
          if(len < 8){
            alert('Password must be atleast of 8 charechters');
            $('#pass1').val('');
            $('#pass1').focus();
          }  
    }

    function passcon() {
      var pass = $('#pass1').val();
      var cpass = $('#cpass1').val();
     
          if(pass != cpass){
            alert('Password Mismatched please try agian');
            $('#cpass1').val('');
            $('#cpass1').focus();
          }  
    }

function disable() {
    
    var x = document.getElementById("otp");
    x.style.display = "none";

    var y = document.getElementById("pass");
    y.style.display = "none";

    var z = document.getElementById("cpass");
    z.style.display = "none";

}


    function emailvalid() {
      var email = $('#email').val();
      //alert(email);
      $.ajax({
        type:'POST',
        url:'../../validation/emailvalid.php',
		data:{email:email,
            table:'admin_reg',
            type:'forgot'
        },
        success:function(return_data) {
          if(return_data == "1"){
            alert('This Email dose not exist in system.Plese register in system');
            $('#email').val('');
            $('#email').focus();
           
          }  else{
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "otp_validate.php?q="+str+"&otp="+return_data, true);
                xmlhttp.send();
                alert(return_data);
            alert('We have sent OTP to '+str);
             var x = document.getElementById("box");
            x.style.display = "block";
            otp = return_data;
          }   
        }
      });
    }

    function otp1() {
      var raw = $('#otp').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 == otp){

            var x = document.getElementById("box");
            x.style.display = "none";

            var y = document.getElementById("pass");
            y.style.display = "block";

            var z = document.getElementById("cpass");
            z.style.display = "block";
          }
    }

    function aaaa() {
      var raw = $('#otp').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 !== otp){
            alert('Plese enter valid OTP');
            $('#otp').val('');
            $('#otp').focus();
          }  else{
            var x = document.getElementById("box");
            x.style.display = "none";

            var y = document.getElementById("pass");
            y.style.display = "block";

            var z = document.getElementById("cpass");
            z.style.display = "block";
          }
    }

    function response() {
        
        var email = $('#email').val();
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "pass_change.php?q="+email, true);
                xmlhttp.send();
          } 
</script>


    <!-- Jquery JS-->
    <script src="../    vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="../    vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="../    vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="../    vendor/slick/slick.min.js">
    </script>
    <script src="../    vendor/wow/wow.min.js"></script>
    <script src="../    vendor/animsition/animsition.min.js"></script>
    <script src="../    vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="../    vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="../    vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="../    vendor/circle-progress/circle-progress.min.js"></script>
    <script src="../    vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../    vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="../    vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="../js/main.js"></script>

</body>

</html>
<!-- end document-->