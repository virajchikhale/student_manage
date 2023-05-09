<?php
          use PHPMailer\PHPMailer\PHPMailer;
          use PHPMailer\PHPMailer\SMTP;
          use PHPMailer\PHPMailer\Exception;
          require 'vendor/autoload.php';
          $mail = new PHPMailer(true);
          
          $email=$_REQUEST["q"];;
          $subject = "Welcome to Smart Farming System";
          $message = "Thank you for registering with us as Farmer.\n You can now enjoy all the features Smart Farming System ...";
                  
                  $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      
                  $mail->isSMTP();                                            
                  $mail->Host       = 'smtp.gmail.com';                    
                  $mail->SMTPAuth   = true;                                 
                  $mail->Username   = 'codelikhoo@gmail.com';                     
                  $mail->Password   = 'jimapdcykodlxxpy';                             
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          
                  $mail->Port       = 587;                                     
              
                  
                  $mail->setFrom('codelikhoo@gmail.com', 'Student Managment');
                      
                  $mail->addAddress($email);               
                    
                  $mail->isHTML(true);                                  
                  $mail->Subject = $subject;
                  $mail->Body    = $message;
                  //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
              
                  $mail->send();  
          ?>