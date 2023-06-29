<?php
// QUEUE CONSUMER - PROCESS EMAIL
require 'vendor/autoload.php';
// rabbitmq
use PhpAmqpLib\Connection\AMQPStreamConnection;
// phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// load env
use Dotenv\Dotenv;

$dotenv = DotEnv::createImmutable(__DIR__);
$env = $dotenv->load();

$rmq_host = $_ENV['RABBITMQ_HOST'];
$rmq_port = $_ENV['RABBITMQ_PORT'];
$rmq_user = $_ENV['RABBITMQ_USER'];
$rmq_pass = $_ENV['RABBITMQ_PASS'];
$connection = new AMQPStreamConnection($rmq_host, $rmq_port, $rmq_user, $rmq_pass);
$channel = $connection->channel();

$channel->queue_declare($_ENV['RABBITMQ_QUEUE'], false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function ($msg) use ($env) {
    echo ' [x] Received ', $msg->body, "\n";
    send_email($msg->body, $env);
};

$channel->basic_consume($_ENV['RABBITMQ_QUEUE'], '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();

function send_email($msg, $env)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $_ENV['SMTP_HOST'];                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $_ENV['SMTP_USER'];                     //SMTP username
        $mail->Password   = $_ENV['SMTP_PASS'];                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('no-reply@example.com', 'No Reply Company');
        $mail->addAddress($_ENV['SMTP_SEND_TO'], 'Vendera');     //Add a recipient
        // $mail->addCC('cc@example.com');

        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $_ENV['SMTP_SUBJECT'];
        $mail->Body    = "This is the HTML message body <b>$msg</b>";

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
