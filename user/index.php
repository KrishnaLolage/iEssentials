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
    if(isset($_FILES['uploadedfile']['name']))
        {
        		$uploaddir = 'pics/';
    			$file = basename($_FILES['uploadedfile']['name']);
			    $uploadfile = $uploaddir . $file;
			    move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile);
			    
			    $imgData =addslashes (file_get_contents($uploadfile));
// 			    $sql = "UPDATE User SET Image = {$imgData} WHERE username = ".$uname;
// 			    $data2 = mysqli_query($this->conn, $sql);
        }
        
     if (isset($_POST["username"]) && isset($_POST["pwd"]) && isset($_POST["phone"])) {
            $uname = $_POST["username"];
            $pwd   = $_POST["pwd"];
            $phone = $_POST["phone"];

            $sql = "INSERT INTO User (Username, Password, Phone, Image) VALUES ('" . $uname . "', '" . $pwd . "', '" . $phone . "', '{$imgData}')";

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
    		
    		if(isset($_FILES['uploadedfile']['name']) && isset($_POST["user_id"]))
    		{
   				$uploaddir = 'pics/';
    			$file = basename($_FILES['uploadedfile']['name']);
			    $uploadfile = $uploaddir . $file;

			    if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
			    
			    	$imgData =addslashes (file_get_contents($uploadfile));

			    	$sql = "UPDATE User SET Image = {$imgData} WHERE id = ".$_POST["user_id"];
			    	$data2 = mysqli_query($this->conn, $sql);
    			}
			    else {
			    
			    $data = array(
                    "Error" => "Failed to update user. Please try agian."
                );
    			}
			}
    
     if (isset($_POST["username"]) && isset($_POST["pwd"]) && isset($_POST["user_id"])) {
            $uname = $_POST["username"];
            $pwd   = $_POST["pwd"];
            $phone = $_POST["phone"];
            
            if(isset($_POST["phone"]))
            {
    			$sql = "UPDATE User SET Username = '".$uname."', Password = '".$pwd."', Phone = ".$phone." WHERE id = ".$_POST["user_id"];
    		}
    		else
    		{
    			$sql = "UPDATE User SET Username = '".$uname."', Password = '".$pwd."' WHERE id = ".$_POST["user_id"];
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
    
    public function getUser($uname, $pwd)
    {
        $sql = "SELECT id, Username, Password, Phone, Image FROM User where Username = '" . $uname . "' AND Password = '" . $pwd . "'";

        $data = mysqli_query($this->conn, $sql);
        
        $result = $this->conn->query($sql);
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
