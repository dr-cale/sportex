<?php
if( isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["subject"]) && isset($_POST["textarea"]) ) {
    $name = $_POST["name"];
    $from = $_POST["email"];
    $subject = $_POST["subject"];
    $text = nl2br($_POST["textarea"]);
    $to = "sportexdragan@gmail.com";

    $htmlContent = '<p style="line-height: 20px; font-size: 16px;"><b>Ime:</b> '.$name.'<br><b>Mejl:</b> '.$from.'<br><b>Tekst poruke:<br></b>'.$text.'</p>';

    $headers = "From: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    if( mail($to, $subject, $htmlContent, $headers) ) {
        echo "success";
    } else {
        echo "The server failed to send the message. Please try again later";
    }
}
?>