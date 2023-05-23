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

    <title>Principal-Login</title>

    <!-- Custom fonts for this template-->
    <link href="../includes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../includes/css/sb-admin-2.css" rel="stylesheet">
    <!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="../includes/images/icons/favicon.ico"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../includes/vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../includes/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../includes/vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="../includes/vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../includes/vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="../includes/css/util.css">
	<link rel="stylesheet" type="text/css" href="../includes/css/main.css">
<!--===============================================================================================-->
</head>

<body onload=disable()>

<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="../includes/images/noun-principal-4585288.png" alt="IMG">
				</div>
                <form method="post" class="login100-form validate-form">
					<span class="login100-form-title">
						Farmer Login
					</span>

					<div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
						<input class="input100" type="text" name="email" id="email" placeholder="Email">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="password"  id="password" placeholder="Password">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>
					
					<div id="alert" class="alert alert-danger" role="alert">
					</div>
					
					<div class="container-login100-form-btn">
						<button type='Button' onclick=response() id="submit" name="submit"  class="login100-form-btn">
							Login
						</button>
					</div>

					<div class="text-center p-t-12">
						<span class="txt1">
							Forgot
						</span>
						<a class="txt2" href="forgot_password">
							Username / Password?
						</a>
					</div>

					<div class="text-center p-t-136">
						<a class="txt2" href="reg">
							Create your Account
							<i class="fa fa-long-arrow-right m-l-5" aria-hidden="true"></i>
						</a>
					</div>
				</form>
				
			</div>
		</div>
	</div>


    <!--===============================================================================================-->	
	<script src="../includes/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="../includes/vendor/bootstrap/js/popper.js"></script>
	<script src="../includes/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="../includes/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="../includes/vendor/tilt/tilt.jquery.min.js"></script>
	<script >
		$('.js-tilt').tilt({
			scale: 1.1
		})

		function response() {
        var email = $('#email').val();
        var password = $('#password').val();
		var	table='principal_reg';
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
			var x = document.getElementById("alert");
			x.style.display = "block";
          if(return_data == "1"){
			x.innerHTML = "Incorrect Username or Password!!!";
			$('#password').val('');
			$('#password').focus();
          }  else{ 
				window.location.href='index.php';
          } 
        }
      });
	}
	</script>
<!--===============================================================================================-->
	<script src="../includes/js/main.js"></script>

</body>

</html>