<?php
echo "testMail<br>";
require '../module/PHPMailer-master/PHPMailerAutoload.php';
echo "get the file<br>";
$results_messages = array();

$mail = new PHPMailer(true);
echo "get mailer instance<br>";
$mail->CharSet = 'utf-8';

class phpmailerAppException extends phpmailerException
{
}

try {
    echo "i am in the try <br>";
    $to = 'l.yuhai@gmail.com';
    if (! PHPMailer::validateAddress($to)) {
        throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!");
    }
    $mail->isSMTP();
    $mail->SMTPDebug = 2;
    $mail->Host = "smtp.gmail.com";
    $mail->Port = "25";
    $mail->SMTPSecure = "tls";
    $mail->SMTPAuth = true;
    $mail->Username = "l.yuhai@gmail.com";
    $mail->Password = "lyh0313lyc";
    $mail->addReplyTo("l.yuhai@ttruus.com", "Yuhai");
    $mail->From = "l.yuhai@ttruus.com"; // in smtp module it doesn't work
    $mail->FromName = "Yuhai";
    $mail->addAddress("l.yuhai@gmail.com", "yuhai liu");
    $mail->addBCC("yuhailiuca@hotmail.com");
    $mail->addCC("l.yuhai@foxmail.com");
    $mail->Subject = "gmail smtp ok(PHPMailer test using SMTP)";
    echo "before the body<br>";
    echo "after the body<br>";
    $mail->WordWrap = 80;
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
    //$mail->msgHTML($body, dirname(__FILE__), true); // Create message bodies and embed images
    //$mail->addAttachment('images/phpmailer_mini.gif', 'phpmailer_mini.gif'); // optional name
    //$mail->addAttachment('images/phpmailer.png', 'phpmailer.png'); // optional name
    
    echo "before the send<br>";
    
    try {
        $mail->send();
        echo "<br>after the send<br>";
        
        $results_messages[] = "Message has been sent using SMTP";
    } catch (phpmailerException $e) {
        throw new phpmailerAppException('Unable to send to: ' . $to . ': ' . $e->getMessage());
    }
} catch (phpmailerAppException $e) {
    $results_messages[] = $e->errorMessage();
}

if (count($results_messages) > 0) {
    echo "<h2>Run results</h2>\n";
    echo "<ul>\n";
    foreach ($results_messages as $result) {
        echo "<li>$result</li>\n";
    }
    echo "</ul>\n";
}