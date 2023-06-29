<?php
namespace Controllers;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EmailController
{
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->mq_host = $_ENV['RABBITMQ_HOST'];
        $this->mq_port = $_ENV['RABBITMQ_PORT'];
        $this->mq_user = $_ENV['RABBITMQ_USER'];
        $this->mq_pass = $_ENV['RABBITMQ_PASS'];
        $this->mq_queue = $_ENV['RABBITMQ_QUEUE'];
        $this->openQueueConnection();
    }

    // send email
    public function insert($uri)
    {
        $custom_message = !empty($_POST["message"]) ? $_POST["message"] : "NO MESSAGE";

        $this->channel->queue_declare($this->mq_queue, false, false, false, false);
        $msg = new AMQPMessage($custom_message);
        $this->channel->basic_publish($msg, '', $this->mq_queue);

        $this->insertData($custom_message);
        $this->closeQueueConnection();
        
        $this->successResponse(["meta" => ["message" => "Email Sent"], "data" => []]);
    }

    private function openQueueConnection()
    {
        $this->connection = new AMQPStreamConnection($this->mq_host, $this->mq_port, $this->mq_user, $this->mq_pass);
        $this->channel = $this->connection->channel();
    }

    private function closeQueueConnection()
    {
        $this->channel->close();
        $this->connection->close();
    }

    private function successResponse($data)
    {
        header('HTTP/1.1 200 OK');
        echo json_encode($data);
    }

    private function insertData($msg) {
        $receiver = $_ENV['SMTP_SEND_TO'];
        $subject = $_ENV['SMTP_SUBJECT'];
        $insert = "INSERT INTO emails(receiver, subject, message) VALUES ('$receiver', '$subject', '$msg')";
        $this->conn->exec($insert);
    }
}
