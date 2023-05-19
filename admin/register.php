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
    <title>Admin Register</title>

    <!-- Fontfaces CSS-->
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="css/theme.css" rel="stylesheet" media="all">

</head>

<body class="animsition" onload="disable()">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#">
                                <img src="images/icon/logo.png" alt="CoolAdmin">
                            </a>
                        </div>
                        <div class="login-form">
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input class="au-input au-input--full" type="text" id="first_name" name="first_name" placeholder="First Name">
                                        </div>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input class="au-input au-input--full" type="text" id="last_name" name="last_name" placeholder="Last Name">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <input class="au-input au-input--full" type="text" id="phoneno" name="phoneno" placeholder="Phone Number">
                                        </div>
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="email" class="au-input au-input--full" id="email" onchange=emailvalid() name="email" required
                                                placeholder="Email Address">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="otp_box">
                                    <label>OTP</label>
                                    <input class="au-input au-input--full" type="text" id="otp" name="otp" onchange=otpp() placeholder="Confirm OTP">
                                </div>
                                <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input class="au-input au-input--full" type="password" id="password" name="password" onchange=passvalid() placeholder="Password">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input class="au-input au-input--full" type="password" id="cpassword" name="cpassword" onchange=passcon() placeholder="Confirm Password">
                                    </div>
                                </div>
                                </div>
                                <button type="Button" id="test" onclick=response() name="submit" class="au-btn au-btn--block au-btn--green m-b-20">
                                    Register
                                </button >
                            </form>
                            <div class="register-link">
                                <p>
                                    Already have account?
                                    <a href="index.php">Sign In</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script> 



function emailvalid() {
      var email = $('#email').val();
    //   alert(email);
      $.ajax({
        type:'POST',
        url:'../validation/emailvalid.php',
		data:{email:email,
            type:'reg',
            table:'admin_reg'
        },
        success:function(return_data) {
        // alert(return_data);
          if(return_data == "1"){
            alert('This Email already exist in system');
            $('#email').val('');
            $('#email').focus();
          }  else{
                // alert(return_data);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "../email/email_base.php?q="+email+"&otp="+return_data+"&type=reg_otp&position=admin", true);
                xmlhttp.send();
                // alert(return_data);
            alert('We have sent OTP to '+email);
            otp = return_data;
            
            var x = document.getElementById("otp_box");
            x.style.display = "block";
			
			//alert(otp);
          } 
        }
      });
    }
    
       
        function response() {
        
            var email = $('#email').val();
            //alert(email);
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open('GET', '../email/email_base.php?q='+email+'&type=thanks&position=admin', true);
                xmlhttp.send(); 
          } 

          function response() {
        var fname = $('#first_name').val();
        var lname = $('#last_name').val();
        var email = $('#email').val();
        var phoneno = $('#phoneno').val();
        var password = $('#password').val();
		var	table='admin_reg';
		
		$.ajax({
        type:'POST',
        url:'../sqloperations/insert_reg.php',
        data:{fname:fname,
			lname:lname,
			email:email,
			phoneno:phoneno,
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
				alert('Signed Up Successfully....');
				window.location.href='index.php';
          } 
        }
      });
	}
      </script>


    <!-- Jquery JS-->
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="vendor/slick/slick.min.js">
    </script>
    <script src="vendor/wow/wow.min.js"></script>
    <script src="vendor/animsition/animsition.min.js"></script>
    <script src="vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="vendor/circle-progress/circle-progress.min.js"></script>
    <script src="vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="js/main.js"></script>
	<script src="js/validation.js"></script>

</body>

</html>
<!-- end document-->