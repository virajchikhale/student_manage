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
    <title>Admin Login</title>

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

<body  onload=disable() class="animsition">
    <P hidden id="php"></p>
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
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" type="email" id="email" name="email" placeholder="Email">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="au-input au-input--full" type="password"  id="password" name="password" placeholder="Password">
                                </div>
                                <div id="alert" class="alert alert-danger" role="alert">
                                </div>
                                <div class="login-checkbox">
                                    <label>
                                        <a href="forgot_password">Forgotten Password?</a>
                                    </label>
                                </div>
                                <button type='Button' onclick=response() id="submit" name="submit"  class="au-btn au-btn--block au-btn--green m-b-20">sign in</button>
                            </form>
                            <div class="register-link">
                                <p>
                                    Don't you have account?
                                    <a href="register.php">Sign Up Here</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <?php
		// if(isset($_POST['submit'])){
		// 	$user=$_POST['email'];
		// 	$pass=md5($_POST['password']);
		// 	$sql="SELECT * FROM `admin_reg` WHERE `email`='".$user."' AND `password`='".$pass."'";
		// 	$result=mysql_query($sql);
        //     $cont=mysql_num_rows($result);
		// 	if($cont>=1){
		// 			   echo "<script> alert('Logged in Successfully....'); </script>";
		// 			   echo "<script> window.location.href='dashboard.php'; </script>";
		// 			}
		// 			else{
		// 				echo "<script> alert('Plese check password and username....'); </script>";
		// 				echo "<script> window.location.href='index.php'; </script>";
		// 			}
		// 	}
        //     // Set session variables
        // $_SESSION["user"] = "$user";
        //echo "Session variables are set.";
	?>

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

    <script>
        
		function disable() {
			var x = document.getElementById("alert");
			x.style.display = "none";
		}

		function response() {
        var email = $('#email').val();
        var password = $('#password').val();
		var	table='admin_reg';
		//alert(password);
		$.ajax({
        type:'POST',
        url:'../sqloperations/login.php',
        data:{email:email,
			password:password,
			table:table
		},
        success:function(return_data) {
			//alert(return_data);
          if(return_data == "1"){
			var x = document.getElementById("alert");
			x.style.display = "block";
			x.innerHTML = "Incorrect Username or Password!!!";
			$('#password').val('');
			$('#password').focus();
          }  else{ 
			var a = document.getElementById("php");
			a.innerHTML = "<?php echo 'hii';?>";
                
				//window.location.href='dashboard.php';
          } 
        }
      });
	}</script>
    <!-- Main JS-->
    <script src="js/main.js"></script>

</body>

</html>
<!-- end document-->