<?php
// Start the session
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>FramAdmin-Login</title>

    <!-- Custom fonts for this template-->
    <link href="../../includes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../../includes/css/sb-admin-2.css" rel="stylesheet">

</head>

<body onload="disable()" class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Get Started!</h1>
                                    </div>
                                    <form class="user" method="post">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="email" aria-describedby="emailHelp" name="email"
                                                onchange=emailvalid(this.value) placeholder="Enter Email Address...">
                                        </div>
                                        <div class="form-group box" id="box">
                                            <input type="text" class="form-control form-control-user" id="otp" onkeyup=otp1() onchange=aaaa() name="otp" required
                                                placeholder="Plese enter your OTP">
                                        </div>
                                        <div class="form-group box" id="pass">
                                            <input type="password" class="form-control form-control-user" id="pass1" onchange=passvalid() name="pass" required
                                                placeholder="Plese enter New password">
                                        </div>
                                        <div class="form-group box" id="cpass">
                                            <input type="password" class="form-control form-control-user" id="cpass1" onchange=passcon() name="cpass" required
                                                placeholder="Confirm your New password">
                                        </div>
                                        <button type='submit' onclick=response()  name="submit" class="btn btn-primary btn-user btn-block">
                                            Update
                                        </button>
                                    
                                    </form>
                                    <hr>
                                </div>
                            </div>
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
    
    var x = document.getElementById("box");
    x.style.display = "none";

    var y = document.getElementById("pass");
    y.style.display = "none";

    var z = document.getElementById("cpass");
    z.style.display = "none";

}


    function emailvalid(str) {
      var email = $('#email').val();
      //alert(email);
      $.ajax({
        type:'POST',
        url:'emailvalid.php',
        data:{email:email},
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
                //alert(return_data);
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


    <?php
    
		include('../../includes/connection.php');
		if(isset($_POST['submit'])){
			$user=$_POST['email'];
			$pass=$_POST['pass'];
            $sql="UPDATE `admin_reg` SET `pass`='".$pass."'  WHERE email='".$user."'" ;
			mysql_query($sql);
					   echo "<script> alert('Password updated Successfully....'); </script>";
					   echo "<script> window.location.href='../index.php'; </script>";
			}
            // Set session variables
        //echo "Session variables are set.";
	?>
    <!-- Bootstrap core JavaScript-->
    <script src="../../includes/vendor/jquery/jquery.min.js"></script>
    <script src="../../includes/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../../includes/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../includes/js/sb-admin-2.min.js"></script>

</body>

</html>