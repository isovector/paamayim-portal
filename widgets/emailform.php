<?php

$from = "no-reply@remlee.net";
$account = "sandy.g.maguire";
$password = "lsaK5122";

session_start();

foreach ($_POST as &$post)
    $post = strip_tags($post);

if (!isset($_SESSION["emailtoken"]) || @$_SESSION["emailtoken"] != @$_POST["emailtoken"])
    die("Potential email hacking attempt.");
unset($_SESSION["emailtoken"]);


// set the email up
include(top() ."/admin/components/phpmailer/class.phpmailer.php");

$mail             = new PHPMailer();

//$body             = $mail->getFile('contents.html');
//$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP();
$mail->SMTPAuth   = true;
$mail->SMTPSecure = "ssl";
$mail->Host       = "smtp.gmail.com";
$mail->Port       = 465;

$mail->Username   = $account . "@gmail.com";
$mail->Password   = $password;

$mail->From       = $from;
$mail->FromName   = $_POST['form_name'];
$mail->Subject    = "Response from Form `{$_POST['form_name']}`";
$mail->Body       = print_r($_POST, false);
$mail->WordWrap   = 80;

//$mail->MsgHTML($body);

$mail->AddReplyTo(, $_POST["name"]);
$mail->AddAddress($account . "@gmail.com", "Contact Form");

$mail->IsHTML(false);

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message has been sent";
}

echo "<pre>";
print_r($_POST);


?>