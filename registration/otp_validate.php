        <?php
          use PHPMailer\PHPMailer\PHPMailer;
          use PHPMailer\PHPMailer\SMTP;
          use PHPMailer\PHPMailer\Exception;
          require '../includes/vendor/autoload.php';
          require '../includes/vendor/phpmailer/src/Exception.php';
          require '../includes/vendor/phpmailer/src/PHPMailer.php';
          require '../includes/vendor/phpmailer/src/SMTP.php';
          $mail = new PHPMailer(true);
          
          $email=$_REQUEST["q"];;
          $otp = $_REQUEST["otp"];
          $subject = "OTP for Famer Confirmation";
          $message = "Dear User, your OTP for signin Confirmation is <b><u> $otp </u></b>";
                  
                  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
                  $mail->isSMTP();                                            
                  $mail->Host       = 'smtp.gmail.com';                    
                  $mail->SMTPAuth   = true;                                 
                  $mail->Username   = 'codelikhoo@gmail.com';                     
                  $mail->Password   = 'jimapdcykodlxxpy';                             
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          
                  $mail->Port       = 587;                                     
              
                  
                  $mail->setFrom('codelikhoo@gmail.com', 'Student Management');
                      
                  $mail->addAddress($email);               
                    
                  $mail->isHTML(true);                                  
                  $mail->Subject = $subject;
                  $mail->Body    = $message;
                  //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
              
                  $mail->send();  
          ?>


