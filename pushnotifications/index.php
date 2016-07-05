<?php
header('Content-Type: application/json');

class PushNotification
{
    
    public $deviceToken;
    public $message;
    public $isTestEnvironment;
    public $passphrase;
    public $certPath;
    
    public function __construct()
    {
        
//         $this->deviceToken = $dt;
//         $this->message   = $msg;
        $this->isTestEnvironment  =  true;//TBD from Const File;
        $this->certPath = "apns-dev-cert.pem";        //Certificate Path for Prod or Dev env
        $this->passphrase = "Smart2016";
    }
    
    public function sendSilentNotificationtoDevice($deviceToken)
    {
        ////////////////////////////////////////////////////////////////////////////////
        
        $deviceToken =  substr($deviceToken, 1, -1);
        $deviceToken = str_replace(' ', '', $deviceToken);

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', "apns-dev-cert.pem");
        stream_context_set_option($ctx, 'ssl', 'passphrase', "Smart2016");
        
        // Open a connection to the APNS server
       	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
         
        if (!$fp)
        {
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        }
        
//         echo 'Connected to APNS' . PHP_EOL;
        
        // Create the payload body
        $body['aps'] = array(
            'content-available' => '1',
            'sound' => ''
        );
        
        // Encode the payload as JSON
        $payload = json_encode($body);
        
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
//         echo $result;
        
        if (!$result)
            $data = 'Message not delivered' . PHP_EOL;
        else
            $data = 'Message successfully delivered' . PHP_EOL;
        
        // Close the connection to the server
        fclose($fp);
        
        $dat = array("Push data" => $result);
        return $dat;
    }
    
    public function sendNotificationtoDevice($deviceToken, $message)
    {
        ////////////////////////////////////////////////////////////////////////////////
        
        $deviceToken =  substr($deviceToken, 1, -1);
        $deviceToken = str_replace(' ', '', $deviceToken);

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', "apns-dev-cert.pem");
        stream_context_set_option($ctx, 'ssl', 'passphrase', "Smart2016");
        
        // Open a connection to the APNS server
       	$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
         
        if (!$fp)
        {
            exit("Failed to connect: $err $errstr" . PHP_EOL);
        }
        
//         echo 'Connected to APNS' . PHP_EOL;
        
        // Create the payload body
        $body['aps'] = array(
            'alert' => $message,
			'sound' => 'default'
        );
        
        // Encode the payload as JSON
        $payload = json_encode($body);
        
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
//         echo $result;
        
        if (!$result)
            $data = 'Message not delivered' . PHP_EOL;
        else
            $data = 'Message successfully delivered' . PHP_EOL;
        
        // Close the connection to the server
        fclose($fp);
        
        $dat = array("Push data" => $data);
        return $dat;
    }

}
// $push = new PushNotification();
// $push->sendNotificationtoDevice("<eb8708ba 26e30765 479a4620 2c08efa7 b065a7ad c7473c94 b8b060e7 1168e68a>", "Hello Krishna");
			
?>
