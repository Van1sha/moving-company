<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Safely require PHPMailer only if it exists so the page doesn\'t crash
if (file_exists('PHPMailer/src/Exception.php')) {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
}

function sendOTPEmail($email, $name, $otp) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'easymovers.trust@gmail.com'; // Updated email
        $mail->Password   = 'kgoi knri xaqr qrea'; // Updated app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('easymovers.trust@gmail.com', 'EasyMovers'); // Updated email
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code - EasyMovers';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #6C63FF;">EasyMovers</h1>
                </div>
                <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                    <h2 style="margin-top: 0;">Verification Code</h2>
                    <p>Hello ' . $name . ',</p>
                    <p>Your verification code for EasyMovers is:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <div style="font-size: 32px; font-weight: bold; letter-spacing: 5px; padding: 15px; background-color: #6C63FF; color: white; border-radius: 5px;">' . $otp . '</div>
                    </div>
                    <p>This code will expire in 15 minutes.</p>
                    <p>If you did not request this code, please ignore this email.</p>
                </div>
                <div style="margin-top: 20px; text-align: center; color: #666;">
                    <p>&copy; 2023 EasyMovers. All rights reserved.</p>
                </div>
            </div>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function sendWelcomeEmail($email, $name) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'easymovers.trust@gmail.com'; // Updated email
        $mail->Password   = 'kgoi knri xaqr qrea'; // Updated app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('easymovers.trust@gmail.com', 'EasyMovers'); // Updated email
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to EasyMovers!';
        $mail->Body    = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h1 style="color: #6C63FF;">EasyMovers</h1>
                </div>
                <div style="padding: 20px; background-color: #f9f9f9; border-radius: 5px;">
                    <h2 style="margin-top: 0;">Welcome to EasyMovers!</h2>
                    <p>Hello ' . $name . ',</p>
                    <p>Thank you for joining EasyMovers! We\'re excited to help you with your moving needs.</p>
                    <p>With your new account, you can:</p>
                    <ul>
                        <li>Request moving services</li>
                        <li>Track your moving status</li>
                        <li>Get support from our team</li>
                    </ul>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="http://localhost/EasyMovers/dashboard.php" style="background-color: #6C63FF; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Go to Dashboard</a>
                    </div>
                    <p>If you have any questions, feel free to contact our support team.</p>
                </div>
                <div style="margin-top: 20px; text-align: center; color: #666;">
                    <p>&copy; 2023 EasyMovers. All rights reserved.</p>
                </div>
            </div>
        ';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>