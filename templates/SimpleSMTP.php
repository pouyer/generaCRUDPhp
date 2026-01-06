<?php

class SimpleSMTP {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $debug = false;
    private $socket;

    public function __construct($host, $user, $pass, $port = 587) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
    }

    public function send($to, $subject, $body, $fromName = 'Sistema') {
        try {
            if (empty($this->host)) {
                // Fallback to mail() if no SMTP host configured
                $headers = "From: $fromName <$this->user>\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                return mail($to, $subject, $body, $headers);
            }

            $this->connect();
            $this->auth();
            $this->sendMail($to, $subject, $body, $fromName);
            $this->quit();
            return true;
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    private function checkResponse($expectedCode) {
        $response = $this->read();
        $code = substr($response, 0, 3);
        if ($code != $expectedCode) {
             throw new Exception("SMTP Error: Expected $expectedCode but got $code. Response: $response");
        }
    }

    private function connect() {
        $protocol = 'tcp';
        if ($this->port == 465) {
            $protocol = 'ssl';
        }

        $socketContext = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $this->socket = stream_socket_client("$protocol://{$this->host}:{$this->port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $socketContext);

        if (!$this->socket) {
            throw new Exception("Connection failed: $errstr ($errno)");
        }
        $this->checkResponse('220');

        $this->write("EHLO " . gethostname());
        $this->checkResponse('250');

        if ($this->port == 587 || $this->port == 25) {
             $this->write("STARTTLS");
             $this->checkResponse('220');
             
             stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
             
             $this->write("EHLO " . gethostname());
             $this->checkResponse('250');
        }
    }

    private function auth() {
        $this->write("AUTH LOGIN");
        $this->checkResponse('334');
        
        $this->write(base64_encode($this->user));
        $this->checkResponse('334');
        
        $this->write(base64_encode($this->pass));
        $this->checkResponse('235');
    }

    private function sendMail($to, $subject, $body, $fromName) {
        $this->write("MAIL FROM: <{$this->user}>");
        $this->checkResponse('250');
        
        $this->write("RCPT TO: <$to>");
        $this->checkResponse('250');
        
        $this->write("DATA");
        $this->checkResponse('354');

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $fromName <{$this->user}>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";

        $this->write("$headers\r\n$body\r\n.");
        $this->checkResponse('250');
    }

    private function quit() {
        $this->write("QUIT");
        $this->read(); // Expect 221 but we are quitting anyway
        fclose($this->socket);
    }

    private function write($cmd) {
        if ($this->debug) echo "C: $cmd\n";
        fwrite($this->socket, $cmd . "\r\n");
    }

    private function read() {
        $response = "";
        while ($str = fgets($this->socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        if ($this->debug) echo "S: $response\n";
        return $response;
    }
}
?>
