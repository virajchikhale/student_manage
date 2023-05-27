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
    <link href="../vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
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
                                <div class="form-group" id="email_box">
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" id="email" type="email" onchange=emailvalid(this.value) name="email" placeholder="Email">
                                </div>
                                <div class="form-group"  id="otp">
                                    <input type="text" class="form-control form-control-user" id="otpin" onkeyup=otp1() onchange=aaaa() name="otp" required
                                    placeholder="Plese enter your OTP">
                                </div>
                                <div class="form-group"  id="pass">
                                    <input type="password" class="form-control form-control-user" id="password" onchange=passvalid() name="pass" required
                                    placeholder="Plese enter New password">
                                </div>
                                <div class="form-group"  id="cpass">
                                    <input type="password" class="form-control form-control-user" id="cpassword" onchange=passcon() name="cpass" required
                                    placeholder="Confirm your New password">
                                </div>
                                <div class="login-checkbox">
                                    <label>
                                        <a href="../">Login</a>
                                    </label>
                                </div>
                                <button name="submit"  onclick=response() class="au-btn au-btn--block au-btn--green m-b-20" type="Button">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


    
<script>


function disable() {
    
    var x = document.getElementById("otp");
    x.style.display = "none";

    var y = document.getElementById("pass");
    y.style.display = "none";

    var z = document.getElementById("cpass");
    z.style.display = "none";

}


    function emailvalid(str) {
      var email = str;
    //  alert(email)
    $.ajax({
        type:'POST',
        url:'../../validation/emailvalid.php',
		data:{email:email,
            type:'forgot',
            table:'admin_reg'
        },
        success:function(return_data) {
        //alert(return_data);
          if(return_data == "1"){
            alert('This Email does not exist in system');
            $('#email').val('');
            $('#email').focus();
          }  else{
                // alert(return_data);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "../../email/email_base.php?q="+email+"&otp="+return_data+"&type=forgot_otp&position=admin", true);
                xmlhttp.send();
                // alert(return_data);
            alert('We have sent OTP to '+email);
            otp = return_data;
            
            var x = document.getElementById("otp");
            x.style.display = "block";
			
			//alert(otp);
          } 
        }
      });
    }

    function otp1() {
      var raw = $('#otpin').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 == otp){

            var x = document.getElementById("email_box");
            x.style.display = "none";

            var y = document.getElementById("pass");
            y.style.display = "block";

            var z = document.getElementById("cpass");
            z.style.display = "block";
          }
    }

    function aaaa() {
      var raw = $('#otpin').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 !== otp){
            alert('Plese enter valid OTP');
            $('#otp').val('');
            $('#otp').focus();
          }  else{
            var x = document.getElementById("email_box");
            x.style.display = "none";

            var y = document.getElementById("pass");
            y.style.display = "block";

            var z = document.getElementById("cpass");
            z.style.display = "block";
          }
    }

      function response() {
    var email = $('#email').val();
    var password = $('#password').val();
    var	table='admin_reg';
    // alert(email);
    // alert(password);
    // alert(table);
    $.ajax({
    type:'POST',
    url:'../../sqloperations/update_reg.php',
    data:{
        email:email,
        password:password,
        table:table
    },
    success:function(return_data) {
        //alert(return_data);
      if(return_data == "1"){
        alert('Someting went wrong!!!');
      }  else{
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            }
            xmlhttp.open('GET', '../email/email_base.php?q='+email+'&type=thanks&position=admin', true);
            xmlhttp.send(); 
            alert('Password Updated Successfully....');
            window.location.href='../index.php';
      } 
    }
  });
}
</script>


    <!-- Jquery JS-->
    <script src="../vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="../vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="../vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="../vendor/slick/slick.min.js">
    </script>
    <script src="../vendor/wow/wow.min.js"></script>
    <script src="../vendor/animsition/animsition.min.js"></script>
    <script src="../vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="../vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="../vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="../vendor/circle-progress/circle-progress.min.js"></script>
    <script src="../vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="../vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="../js/main.js"></script>

</body>

</html>
<!-- end document-->