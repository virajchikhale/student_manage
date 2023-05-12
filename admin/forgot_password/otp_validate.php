        <?php
          use PHPMailer\PHPMailer\PHPMailer;
          use PHPMailer\PHPMailer\SMTP;
          use PHPMailer\PHPMailer\Exception;
          require 'vendor/autoload.php';
          $mail = new PHPMailer(true);
          
          $email=$_REQUEST["q"];;
          $otp = $_REQUEST["otp"];
          $subject = "OTP for Admin Confirmation";
          $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
                  
                  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
                  $mail->isSMTP();                                            
                  $mail->Host       = 'smtp.gmail.com';                    
                  $mail->SMTPAuth   = true;                                 
                  $mail->Username   = 'mail.sender.black@gmail.com';                     
                  $mail->Password   = 'mailBlack@12';                             
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          
                  $mail->Port       = 587;                                     
              
                  
                  $mail->setFrom('mail.sender.black@gmail.com', 'Smart Farming');
                      
                  $mail->addAddress($email);               
                    
                  $mail->isHTML(true);                                  
                  $mail->Subject = $subject;
                  $mail->Body    = $message;
                  //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
              
                  $mail->send();  
          ?>


