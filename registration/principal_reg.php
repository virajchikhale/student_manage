<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Principal-Registration</title>
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<!-- Font-->
	<link rel="stylesheet" type="text/css" href="../includes/css/opensans-font.css">
	<link rel="stylesheet" type="text/css" href="../includes/fonts/material-design-iconic-font/css/material-design-iconic-font.min.css">
	<!-- Main Style Css -->
    <link rel="stylesheet" href="../includes/css/style.css"/>
	<style>
		.btn-primary{color:#fff;background-color:#28a745;border-color:#28a745}.btn-primary:hover{color:#fff;background-color:#0069d9;border-color:#0062cc}
		.btn{display:inline-block;font-weight:400;text-align:center;white-space:nowrap;vertical-align:middle;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;transition:color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out}
		.btn-block{display:block;width:100%}
		.text-right{margin-left:5%!important}
		</style>
</head>
<?php
		include("../includes/connection.php");
		if(isset($_POST['submit'])){
		$fname=$_POST['fname'];
		$lname=$_POST['lname'];
		$email=$_POST['email'];
		$phoneno=$_POST['phoneno'];
		$password=md5($_POST['password']);
		$cpassword=$_POST['cpassword'];

                          $sqlinsert="insert into principal_reg(first_name, last_name, email,phone,password) 
                          values('".$fname."' , '".$lname."', '".$email."', '".$phoneno."', '".$password."')";
                          mysql_query($sqlinsert);
                          //echo $sqlinsert;
                          echo "<script> alert('Signed Up Successfully....'); </script>";
                          echo "<script> window.location.href='index.php'; </script>";
                        
                        
                 
                
		}
	?>
<body onload="disable()">
	<div class="page-content">
		<div class="form-v1-content">
			<div class="wizard-form">
		        <form class="form-register" action="#" method="post">
		        	<div id="form-total">
		        		<!-- SECTION 1 -->
			            <h2>
			            	<p class="step-icon"><span>01</span></p>
			            	<span class="step-text">Pricipal's Infomation</span>
			            </h2>
			            <section>
			                <div class="inner">
			                	<div class="wizard-header">
									<h3 class="heading">Peronal Infomation of Farmer</h3>
									<p>Please enter your infomation and proceed to the next step so we can build your accounts.  </p>
								</div>
								<div class="form-row">
									<div class="form-holder">
										<fieldset>
											<legend>First Name</legend>
											<input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" required>
										</fieldset>
									</div>
									<div class="form-holder">
										<fieldset>
											<legend>Last Name</legend>
											<input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" required>
										</fieldset>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
										<fieldset>
											<legend>Your Email</legend>
											<input type="email" name="email" id="email" onchange=emailvalid(this.value) class="form-control" placeholder="example@email.com" required>
										</fieldset>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
										<fieldset>
											<legend>Phone Number</legend>
											<input type="text" class="form-control" onchange=checkmobno() id="phoneno" name="phoneno" placeholder="+1 888-999-7777" required>
										</fieldset>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
										<fieldset>
											<legend>Password</legend>
											<input type="password" class="form-control" onchange=passvalid() id="password" name="password" placeholder="Enter your Password" required>
										</fieldset>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-2">
										<fieldset>
											<legend>Password</legend>
											<input type="password" class="form-control" onchange=passcon() id="cpassword" name="cpassword" placeholder="Renter your Password" required>
										</fieldset>
									</div>
								</div>
							</div>
			            </section>
						<!-- SECTION 2 -->
			            <h2>
			            	<p class="step-icon"><span>02</span></p>
			            	<span class="step-text">Adderss Information</span>
			            </h2>
			            <section>
			                <div class="inner">
			                	<div class="wizard-header">
									<h3 class="heading">Adderss Information</h3>
									<p>Please enter your infomation and proceed to the next step so we can build your accounts.</p>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-1">
										<textarea  class="form-control" id="adderss" name="adderss" placeholder="Enter your Adderss" rows="4" cols="50" required></textarea>
									</div>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-1">
								<label for="state">State</label>
								<select class="form-control" id="state" name="state" required>
									<option disabled selected>Select your State</option>
									<option>Arunachal Pradesh</option>
									<option>Assam</option>
									<option>Bihar</option>
									<option>Chhattisgarh</option>
									<option>Goa</option>
									<option>Gujarat</option>
									<option>Haryana</option>
									<option>Himachal Pradesh</option>
									<option>Jharkhand</option>
									<option>Karnataka</option>
									<option>Kerala</option>
									<option>Madhya Pradesh</option>
									<option>Maharashtra</option>
									<option>Manipur</option>
									<option>Meghalaya</option>
									<option>Mizoram</option>
									<option>Nagaland</option>
									<option>Odisha</option>
									<option>Punjab</option>
									<option>Rajasthan</option>
									<option>Sikkim</option>
									<option>Tamil Nadu</option>
									<option>Telangana</option>
									<option>Tripura</option>
									<option>Uttar Pradesh</option>
									<option>Uttarakhand</option>
									<option>West Bengal</option>

									<option>Andaman and Nicobar Islands</option>
									<option>Chandigarh</option>
									<option>Dadra & Nagar Haveli and Daman & Diu</option>
									<option>Delhi</option>
									<option>Jammu and Kashmir</option>
									<option>Lakshadweep</option>
									<option>Puducherry</option>
									<option>Ladakh</option>

								</select>
								</div>
								</div>
							</div>
			            </section>
			            <!-- SECTION 3 -->
			            <h2>
			            	<p class="step-icon"><span>03</span></p>
			            	<span class="step-text">OTP Verifiction</span>
			            </h2>
			            <section>
			                <div class="inner">
			                	<div class="wizard-header">
									<h3 class="heading">OTP Verifiction</h3>
									<p>Please enter your infomation and proceed to the next step so we can build your accounts.</p>
								</div>
								<div class="form-row">
									<div class="form-holder form-holder-1">
										<fieldset>
												<legend>OTP</legend>
											<input type="text" class="form-control" onchange=otp() onkeyup=otp1()  id="otp" name="otp" placeholder="Enter your OTP" required>
										</fieldset>
									</div>
								</div>
								<div class="form-row">
									<div class="form-button form-button-2 text-right">
										<button type="Submit" class="btn btn-primary btn-user btn-block" onclick=response() id="submit" name="submit" >Register</button>
									</div>
								</div>
							</div>
			            </section>
		        	</div>
		        </form>
			</div>
		</div>
	</div>
	<!--mobile number validation -->
    <script> 
		function checkmobno() {
		  var mob = $('#phoneno').val();
		  //alert(mob);
		  $.ajax({
			type:'POST',
			url:'checkmob.php',
			data:{mob:mob},
			success:function(return_data) {
			  if(return_data == 1){
				alert('This Number already exist in system');
				$('#phoneno').val('');
				$('#phoneno').focus();
			  }   //	alert(return_data);      
			}
		  });
		}


  //<!--password length validation -->
	function passvalid() {
	  var pass = $('#password').val();
	  
	  //alert(pass.length);
	  var len = pass.length;
	  //alert(len);
		  if(len < 8){
			alert('Password must be atleast of 8 charechters');
			$('#password').val('');
			$('#password').focus();
		  }  
	}


//<!--password length validation -->
	function passcon() {
	  var pass = $('#password').val();
	  var cpass = $('#cpassword').val();
	 
		  if(pass != cpass){
			alert('Password Mismatched please try agian');
			$('#cpassword').val('');
			$('#cpassword').focus();
		  }  
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
            alert('This Email already exist in system');
            $('#email').val('');
            $('#email').focus();
          }  else{
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "../email/email_base.php?q="+str+"&otp="+return_data+"&type=otp&position=teacher", true);
                xmlhttp.send();
                //alert(return_data);
            alert('We have sent OTP to '+str);
            otp = return_data;
			
			//alert(otp);
          }   
        }
      });
    }

	function otp() {
      var raw = $('#otp').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 !== otp){
            alert('Plese enter valid OTP');
            $('#otp').val('');
            $('#otp').focus();
          }  
    }


	function otp1() {
      var raw = $('#otp').val();
      var otp1 = raw.trim();
      //alert(window.otp);
      //alert(otp1);
      //alert(len);
          if(otp1 == otp){

            var y = document.getElementById("submit");
            y.style.display = "block";

          }
    }


	function disable() {
    
    var x = document.getElementById("submit");
    x.style.display = "none";

}


function response() {
        
        var email = $('#email').val();
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                }
                xmlhttp.open("GET", "../email/email_base.php?q="+email+"&type=thanks&position=teacher", true);
                xmlhttp.send();
          } 

  </script>
	<script src="../includes/js/jquery-3.3.1.min.js"></script>
	<script src="../includes/js/jquery.steps.js"></script>
	<script src="../includes/js/main_steps.js"></script>
</body>
