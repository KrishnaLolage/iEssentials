<?php
header('Content-Type: application/json');
require $_SERVER["DOCUMENT_ROOT"] . '/hackathon/iEssentials/twilio-php/Services/Twilio.php';
require $_SERVER["DOCUMENT_ROOT"] . '/hackathon/iEssentials/pushnotifications/index.php';

class Section
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
            if ($_SERVER["REQUEST_METHOD"] == "PUT") {
                //$data = $this->createSection();
                $data = "NO PUT Method";
            } else if ($_SERVER["REQUEST_METHOD"] == "GET") {

            if(!isset($_GET["hardware_update"]))
            {
                if (isset($_GET["section_id"])) {
                    $data = $this->getSectionById($_GET["section_id"]);
                } else if (isset($_GET["tray_id"])) {
                    $data = $this->getAllSectionWithTaryId();
                } else {
                    $data = $this->getAllSection();
                }
            }
            else
            {
            	$data = $this->updateSectionFromHardware();
            }
            } else if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $data = $this->updateSection();
            }
        }
        
        echo json_encode($data);
    }
    
    
    public function getAllSection()
    {
        
        $sql = "SELECT id as section_id, Name, ItemName, Quantity, OriginalQty, Unit, UpdatedTime, Threshold, Status, TrayId as tray_id, GenericIdentifier, UserItemQty FROM Section";
        
        $data = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($data) > 0) {
            $dat = [];
            while ($row = mysqli_fetch_assoc($data)) {
                array_push($dat, $row);
            }
            $data = $dat;
        } else {
            $data = array(
                "Error" => "Section list could not be fetched. Please try later."
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
    
    public function getSectionByTrayIdANDIdentifier($trayId, $identifier)
    {
        
        if ($trayId) {
            $sql = "SELECT id as section_id, Name, ItemName, Quantity, OriginalQty, Unit, UpdatedTime, Threshold, Status, TrayId as tray_id, GenericIdentifier, UserItemQty FROM Section where TrayId = " . $trayId." AND GenericIdentifier = '".$identifier."'";
            
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
                $data = mysqli_fetch_assoc($data);
            } else {
                $data = array(
                    "Error" => "Please verify username and password."
                );
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
        if (isset($_POST["section_id"]) && isset($_POST["name"]) && isset($_POST["item_name"]) && isset($_POST["userItemQty"])) {
            
            $sql = "UPDATE Section SET Name = '" . $_POST["name"] . "', ItemName = '" . $_POST["item_name"] . "', UserItemQty = " . $_POST["userItemQty"] . ", Status = 'Low' WHERE id = " . $_POST["section_id"];

            if (mysqli_query($this->conn, $sql)) {
                $data = $this->getSectionById($_POST["section_id"]);
                
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
                                    
            } else {
                $data = array(
                    "Error" => "Update section failed. Please try later.",
                    "SQL Error" => mysqli_error($this->conn)
                );
            }
        }
        else
        {
        	$data = array(
                    "Error" => "Required params missing"
                );
        }
        
        return $data;
    }


	public function getTrayIdforUser($userId, $macId)
	{
		$sql = "SELECT * FROM Tray where userid = ".$userId." AND TrayMacAddress = '".$macId."'";
            
            $data = mysqli_query($this->conn, $sql);
            
            if (mysqli_num_rows($data) > 0) {
                $data = mysqli_fetch_assoc($data);
                return $data["id"];
            } else {
                $data = array(
                    "Error" => "Tray details not found."
                );
                
                return null;
            }
	}
	
	Public function updateSectionFromHardware()
    {
        if (isset($_GET["sectionIdentifier"]) && isset($_GET["quantity"]) && isset($_GET["macaddress"]) && isset($_GET["user_id"])) {
            
            $trayId = $this->getTrayIdforUser($_GET["user_id"], $_GET["macaddress"]);
            
            if(!$trayId)
            	return $data = array( "Error" => "Invalid Tray Id");
            	
            $sql = "UPDATE Section SET Quantity = " . $_GET["quantity"]. " WHERE genericidentifier = '" . $_GET["sectionIdentifier"]."' AND TrayId = " . $trayId;
            
            if (mysqli_query($this->conn, $sql)) {
            
            	$section = $this->getSectionByTrayIdANDIdentifier($trayId, $_GET["sectionIdentifier"]);
                
                    if ($section["Status"] == "Low" || $section["Status"] == "Empty") {
                        //trigger SMS, Notification
                        $account_sid = 'AC79bd8b9ef7076e78c1a087e6b1ca444d';
                        $auth_token  = '1b2d0de791aad80733cd872a37017258';
                        $client      = new Services_Twilio($account_sid, $auth_token);
                        
                        
                        $phones = array("+919860262264");
                        
                        $msg = "Your ".$section["ItemName"]." level is low.";                
                        
                        foreach ($phones as $value) {
						 
						 	$message = $client->account->messages->create(array(
                         	   'To' => $value,
                         	   'From' => "+19103632856",
                        	    'Body' => $msg
                     	   ));
                           
						}
                        
                        if($message->sid)
                        {
                        	$data = array(
                    					"sent_sms" => "true",
                    					"section" => $section
                					);
                        }
                        else
                        {
                        	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $section
                					);
                        }
                        
                        //Send Push notification
						//$pushData = $this->sendNotificationtoUserApp($_GET["user_id"], $section["section_id"]);
						
						//array_push($data, array("PushNotification"=>$pushData));
                    }
                    else
                    {
                    	$data = array(
                    					"sent_sms" => "false",
                    					"section" => $section
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


	Public function sendSilentNotificationtoUserApp($userId, $sectionId)
	{
        if ($userId) {
            $sql = "SELECT distinct(M.DeviceToken), M.Id, M.DeviceType, M.UserId  FROM MobileDevices M inner join Tray T on (M.UserId = T.UserId) inner join Section S on (T.id = S.TrayId) Where M.UserId = " . $userId." AND S.Id = ". $sectionId;
            
            $data = mysqli_query($this->conn, $sql);
        
        	if (mysqli_num_rows($data) > 0) {
        	    $dat = [];
            	while ($row = mysqli_fetch_assoc($data)) {
               	 array_push($dat, $row["DeviceToken"]);
               	 $push = new PushNotification();
				 $dat = $push->sendSilentNotificationtoDevice($row["DeviceToken"]);
            	}

        	} else {
            	$dat = array(
                	"Error" => "Section list could not be fetched. Please try later."
            	);
        }
        }
        else {
            $dat = array(
                "Error" => "user id param missing"
            );
        }
                
        return $dat;
    }
    
    
    Public function sendNotificationtoUserApp($userId, $sectionId)
	{
        if ($userId) {
            $sql = "SELECT distinct(M.DeviceToken), M.Id, M.DeviceType, M.UserId, S.ItemName FROM MobileDevices M inner join Tray T on (M.UserId = T.UserId) inner join Section S on (T.id = S.TrayId) Where M.UserId = " . $userId." AND S.Id = ". $sectionId;

            $data = mysqli_query($this->conn, $sql);
        
        	if (mysqli_num_rows($data) > 0) {
        	    $dat = [];
            	while ($row = mysqli_fetch_assoc($data)) {
               	 
               	 $msg = "Your ".$row["ItemName"]." level is low.";                
               	 $push = new PushNotification();
				 $pushDat = $push->sendNotificationtoDevice($row["DeviceToken"], $msg, $_GET["section_id"]); 
            	}

        	} else {
            	$dat = array(
                	"Error" => "Section list could not be fetched. Please try later."
            	);
        }
        }
        else {
            $dat = array(
                "Error" => "user id param missing"
            );
        }

        array_push($dat, $pushDat);
        return $dat;
    }
}

$section = new Section;
$section->processRequest();

?>
