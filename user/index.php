<?php
header('Content-Type: application/json');
class User
{
    public $servername;
    public $username;
    public $password;
    public $dbname;
    public $conn;
    
    public function __construct()
    {
        
        $this->servername = "127.0.0.1";
        $this->username   = "root";
        $this->password   = "root";
        $this->dbname     = "iEssentials";
        
        $this->conn = mysqli_connect($this->servername, $this->username, $this->password, $this->dbname);
        //Check connection
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
    }
    
      // Destructor - close conn connection
    function __destruct() {
        $this->conn->close();
    }
    
    public function processRequest()
    {
		if (isset($_SERVER["REQUEST_METHOD"]))
		{
			if($_SERVER["REQUEST_METHOD"] == "POST")
			{
				if($_POST["action"] ==  "create")
				{
					$data = $this->addUser();
				}
				else if($_POST["action"] ==  "SaveDeviceToken")
				{
					$data = $this->saveUserMobileDeviceToken();
				}
				else
				{
					$data = $this->updateUserNew();
				}
			}
			else if ($_SERVER["REQUEST_METHOD"] == "GET")
			{
				$uname = $_GET["username"];
			    $pwd   = $_GET["pwd"];
				$data = $this->getUser($uname, $pwd);
			}
			else if($_SERVER["REQUEST_METHOD"] == "PUT")
			{
				//$data = $this->addUser();
			}
    	}
       
    	echo json_encode($data);
    }
    
    //INSERT INTO `iEssentials`.`User` (`Username`, `Password`, `Phone`) VALUES ('Krishna', 'Qwerty123', '2692058769')
    public function addUser(){
    
    try
    {      
     if (isset($_POST["username"]) && isset($_POST["pwd"]) && isset($_POST["phone"])) {
            $uname = $_POST["username"];
            $pwd   = $_POST["pwd"];
            $phone = $_POST["phone"];
            $imageData = $_POST["imagedata"];

            $sql = "INSERT INTO User (Username, Password, Phone, Image) VALUES ('" . $uname . "', '" . $pwd . "', '" . $phone . "', '".$imageData."')";

            if (mysqli_query($this->conn, $sql)) {
            
                $data = $this->getUser($uname, $pwd);
                
            } else {
                $data = array(
                    "Error" => "Failed to create user. Please try agian."
                );
            }
        }
        
		return $data;
		}
		catch(Exception $e)
		{
			$data = array(
                    "Exception" => "Exception ".$e
                );
		}
		
		return $data;
    }
    
    public function updateUserNew(){
    		
     if (isset($_POST["username"]) && isset($_POST["pwd"]) && isset($_POST["user_id"])) {
            $uname = $_POST["username"];
            $pwd   = $_POST["pwd"];
            $phone = $_POST["phone"];
            $imgData = $_POST["imagedata"];
            
            if(isset($_POST["phone"]))
            {
    			$sql = "UPDATE User SET Username = '".$uname."', Password = '".$pwd."', Phone = ".$phone.", Image = '".$imgData."' WHERE id = ".$_POST["user_id"];
    		}
    		else
    		{
    			$sql = "UPDATE User SET Username = '".$uname."', Password = '".$pwd."',  Image = '".$imgData."' WHERE id = ".$_POST["user_id"];
    		}
    		
            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getUser($uname, $pwd);
            } else {
                $data = array(
                    "Error" => "Failed to update user. Please try agian."
                );
            }
        }

		if($data2 != null)
			array_push($data, $data2);
		return $data;
    }
    
    public function saveUserMobileDeviceToken(){
    		
     if (isset($_POST["device_type"]) && isset($_POST["device_token"]) && isset($_POST["user_id"])) {
            $deviceType = $_POST["device_type"];
            $deviceToken   = $_POST["device_token"];
            $userID = $_POST["user_id"];
            
            //INSERT INTO MobileDevices (DeviceType, DeviceToken, UserId) VALUES ('iOS', '<eb8708ba 26e30765 479a4620 2c08efa7 b065a7ad c7473c94 b8b060e7 1168e68a>', 1)"
            $sql = "INSERT INTO MobileDevices (DeviceType, DeviceToken, UserId) VALUES ('" . $deviceType . "', '" . $deviceToken . "', " . $userID. ")";
                
            if (mysqli_query($this->conn, $sql)) {
            
                $data = array(
                    "Result" => "Success",
                    "Message" => "Device token saved successfully"
                );
                
            } else {
                 $data = array(
                 	"Error" => "Error while saving device token",
                    "Result" => "Failure",
                    "SQL Error" => mysqli_error($this->conn)
                );
            }
            
        }

		return $data;
    }
    
    public function getUser($uname, $pwd)
    {
        $sql = "SELECT id, Username, Password, Phone, Image FROM User where Username = '" . $uname . "' AND Password = '" . $pwd . "'";

        $data = mysqli_query($this->conn, $sql);
        
        $result = $this->conn->query($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
		    $data = $row;
        } else {
            $data = array(
                "Error" => "Please verify username and password."
            );
        }

        return $data;
    }
    
    public function getUserById($userid)
    {
        $sql = "SELECT id, Username, Password, Phone, Image FROM User where id = " . $userid;
        
        $data = mysqli_query($this->conn, $sql);
        
        $result = $this->conn->query($sql);
        
        	// Read from $fp file pointer using the size of an upload temp name (stored as $tmpName)
		$content = fread($fp, filesize($tmpName));

        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
            
            $img = $data["Image"];
    		$b64img = base64_encode ($img);
		    $data["Image"] = $b64img;
		    
        } else {
            $data = array(
                "Error" => "Please verify username and password."
            );
        }

        return $data;
    }
    
    Public function updateUser()
	{
	$post_vars = file_get_contents("php://input");
	$post_vars  = (array)json_decode($post_vars);
	
	if (isset($post_vars["user_id"]) && isset($post_vars["username"]) && isset($post_vars["pwd"]) && isset($post_vars["phone"])) 
	{
	
             $sql = "UPDATE User SET Username = '".$post_vars["username"]."', Password = '".$post_vars["pwd"]."', Phone = ".$post_vars["phone"]." WHERE id = ".$post_vars["user_id"];

            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getUserById($post_vars["user_id"]);
            } else {
                $data = array(
                    "Error" => "Failed to create user. Please try agian."
                );
            }
    } 
        
    	return $data;
	}
}

$user = new User();
$user->processRequest();

?>

