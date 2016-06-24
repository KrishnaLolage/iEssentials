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
				$data = $this->addUser();
			}
			else if ($_SERVER["REQUEST_METHOD"] == "GET")
			{
				$uname = $_GET["username"];
			    $pwd   = $_GET["pwd"];
				$data = $this->getUser($uname, $pwd);
			}
			else if($_SERVER["REQUEST_METHOD"] == "PUT")
			{
				$data = $this->updateSection();
			}
    	}
    	
    	echo json_encode($data);
    }
    
    //INSERT INTO `iEssentials`.`User` (`Username`, `Password`, `Phone`) VALUES ('Krishna', 'Qwerty123', '2692058769')
    public function addUser(){
        if (isset($_POST["username"]) && isset($_POST["pwd"]) && isset($_POST["phone"])) {
            $uname = $_POST["username"];
            $pwd   = $_POST["pwd"];
            $phone = $_POST["phone"];
            
            $sql = "INSERT INTO User (Username, Password, Phone) VALUES ('" . $uname . "', '" . $pwd . "', '" . $phone . "')";

            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getUser($uname, $pwd);
            } else {
                $data = array(
                    "Error" => "Failed to create user. Please try agian."
                );
            }
        } else {
            $data = array(
                "Error" => "Params mismatch"
            );
        }
        
    	return $data;
    }
    
    
    public function getUser($uname, $pwd)
    {
        $sql = "Select * from User where Username = '" . $uname . "' AND Password = '" . $pwd . "'";
        
        $data = mysqli_query($this->conn, $sql);
        
        $result = $this->conn->query($sql);
        
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
        } else {
            $data = array(
                "Error" => "Please verify username and password."
            );
        }

        return $data;
    }
    
    public function getUserById($userid)
    {
        $sql = "Select * from User where id = '" . $userid;
        
        $data = mysqli_query($this->conn, $sql);
        
        $result = $this->conn->query($sql);
        
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);
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

