<?php
header('Content-Type: application/json');

class Tray
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
    function __destruct()
    {
        $this->conn->close();
    }
    
    public function processRequest()
    {
        if (isset($_SERVER["REQUEST_METHOD"])) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $data = $this->createTray();
                //$data = "NO POST Method";
            } else if ($_SERVER["REQUEST_METHOD"] == "GET") {

				if(isset($_GET["user_id"]))
				{
					$memberId = $_GET["user_id"];
					$data = $this->getAllTrayDetailsForMember($memberId);
				}
            }
            else
            {
            	$data = $this->updateSectionFromHardware();
            }
        } else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $data = $this->updateSection();
        }
        
        echo json_encode($data);
    }
    
    
    public function getAllTrayDetailsForMember($memberId)
    {
        
        $sql = "SELECT * from Tray where userid = ".$memberId;
        $data = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($data) > 0) {
            $dat = [];
            while ($row = mysqli_fetch_assoc($data)) {
            	
            	$trayid = $row["id"];

            	$sectionData = $this->getAllSectionWithTaryId($trayid);
            	
            	$row["sections"] = $sectionData;
                array_push($dat, $row);
            }
            $data = $dat;
        } else {
            $data = array(
                "Error" => "Tray list could not be fetched. Please try later."
            );
        }
        
        return $data;
    }
    
    public function getSectionById($sectionId)
    {
        
        if ($sectionId) {
            $sql = "SELECT id as section_id, Name, ItemName, Quantity, OriginalQty, Unit, UpdatedTime, Threshold, Status, TrayId as tray_id, GenericIdentifier, UserItemQty FROM Section where id = " . $sectionId;
            
            $data = mysqli_query($this->conn, $sql);
            
            if (mysqli_num_rows($data) > 0) {
                $data = mysqli_fetch_assoc($data);
            } else {
                $data = array(
                    "Error" => "Section details not found."
                );
            }
        } else {
            $data = array(
                "Error" => "Section id not valid."
            );
        }
        
        return $data;
    }
    
    public function getAllSectionWithTaryId($trayid)
    {
        
        if ($trayid) {
            $sql = "SELECT id as section_id, Name, ItemName, Quantity, OriginalQty, Unit, UpdatedTime, Threshold, Status, TrayId as tray_id, GenericIdentifier, UserItemQty FROM iEssentials.Section where TrayId = " . $trayid;
            
            $data = mysqli_query($this->conn, $sql);
            
            if (mysqli_num_rows($data) > 0) {
            
            	$dat = [];
            	while ($row = mysqli_fetch_assoc($data)) {
              		  	array_push($dat, $row);
           		 }
            	$data = $dat;
//                 $data = mysqli_fetch_assoc($data);
            } else {
                $data = null;
            }
        } else {
            $data = array(
                "Error" => "Tray id not found."
            );
        }
        
        return $data;
    }
    
    
    Public function updateSection()
    {
        $post_vars = file_get_contents("php://input");
        $post_vars = (array) json_decode($post_vars);
        
        if (isset($post_vars["section_id"]) && isset($post_vars["name"]) && isset($post_vars["item_name"]) && isset($post_vars["quantity"])) {
            
            $sql = "UPDATE Section SET Name = '" . $post_vars["name"] . "', ItemName = '" . $post_vars["item_name"] . "', Quantity = " . $post_vars["quantity"] . ", Status = 'Low' WHERE id = " . $post_vars["section_id"];
            
            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getSectionById($post_vars["section_id"]);
                
//                 if (!isset($data["Error"])) {
                    if ($data["Status"] == "Low" || $data["Status"] == "Empty") {
                        //trigger SMS, Notification
                        $account_sid = 'AC79bd8b9ef7076e78c1a087e6b1ca444d';
                        $auth_token  = '1b2d0de791aad80733cd872a37017258';
                        $client      = new Services_Twilio($account_sid, $auth_token);
                        
                        $message = $client->account->messages->create(array(
                            'To' => "+919860262264",
                            'From' => "+19103632856",
                            'Body' => "Running Low"
                        ));
                        
                        if($message->sid)
                        {
                        	$data = array(
                    					"sent_sms" => "true",
                    					"section" => $data
                					);
                        }
                        else
                        {
                        	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $data
                					);
                        }
                    }
                    else
                    {
                    	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $data
                					);
                    }
//                 }
                
            } else {
                $data = array(
                    "Error" => "Update section failed. Please try later.",
                    "SQL Error" => mysqli_error($this->conn)
                );
            }
        }
        
        return $data;
    }

	Public function updateSectionFromHardware()
    {
        if (isset($_GET["section_id"]) && isset($_GET["quantity"])) {
            
            $sql = "UPDATE Section SET Quantity = " . $_GET["quantity"]. " WHERE id = " . $_GET["section_id"];
            
            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getSectionById($_GET["section_id"]);
                
//                 if (!isset($data["Error"])) {
                    if ($data["Status"] == "Low" || $data["Status"] == "Empty") {
                        //trigger SMS, Notification
                        $account_sid = 'AC79bd8b9ef7076e78c1a087e6b1ca444d';
                        $auth_token  = '1b2d0de791aad80733cd872a37017258';
                        $client      = new Services_Twilio($account_sid, $auth_token);
                        
                        $message = $client->account->messages->create(array(
                            'To' => "+918867721983",
                            'From' => "+19103632856",
                            'Body' => "Running Low"
                        ));
                        
                        if($message->sid)
                        {
                        	$data = array(
                    					"sent_sms" => "true",
                    					"section" => $data
                					);
                        }
                        else
                        {
                        	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $data
                					);
                        }
                    }
                    else
                    {
                    	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $data
                					);
                    }
//                 }
                
            } else {
                $data = array(
                    "Error" => "Update section failed. Please try later.",
                    "SQL Error" => mysqli_error($this->conn)
                );
            }
        }
        
        return $data;
    }
    
    
    public function getTrayById($trayId)
    {
        
        if ($trayId) {
            $sql = "SELECT * from Tray where id = " . $trayId;
            
            $data = mysqli_query($this->conn, $sql);
            
            if (mysqli_num_rows($data) > 0) {
                $data = mysqli_fetch_assoc($data);
            } else {
                $data = array(
                    "Error" => "Section details not found."
                );
            }
        } else {
            $data = array(
                "Error" => "Tray id not valid."
            );
        }
        
        return $data;
    }
    
    public function createTray()
    {
    
    try
    {

     if (isset($_POST["tray_name"]) && isset($_POST["user_id"])) {
            $trayNmae = $_POST["tray_name"];
            $userId   = $_POST["user_id"];

			$sql = "INSERT INTO Tray (Name, UserId) VALUES(\"" . $trayNmae . "\", " . $userId . ")";
//            $sql = "INSERT INTO User (Username, Password, Phone, Image) VALUES ('" . $uname . "', '" . $pwd . "', '" . $phone . "', '{$imgData}')";

            if (mysqli_query($this->conn, $sql)) {

				$trayId = mysqli_insert_id($this->conn);            
            	$sectionList = $_POST["sections"];

            	foreach ($sectionList as $section) {
                	$sql = "INSERT INTO Section (Name, ItemName, TrayId, GenericIdentifier) VALUES ('" . $section["name"] . "', '" . $section["name"] . "', ".$trayId.", '".$section["identfier"]."')";                
                	mysqli_query($this->conn, $sql);
                }
                
                $data = $this->getTrayById($trayId);
                $data["sections"] = $this->getAllSectionWithTaryId($trayId);
                
            } else {
                $data = array(
                    "Error" => "Failed to create tray. Please try agian."
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
    
    
    public function createTrayGETMethod()
    {
    
    try
    {
    	echo json_encode($_SERVER);
    		$sectionList = $_GET["sections"];
    }
		catch(Exception $e)
		{
			$data = array(
                    "Exception" => "Exception ".$e
                );
		}
		
		return $data;
    
    }
}

$tray = new Tray;
$tray->processRequest();

?>
