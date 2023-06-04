<?php

namespace MyApp;

use database;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require 'database.php';
require 'vendor/autoload.php';

class socket implements MessageComponentInterface {
    protected database $db;

    //Array containing all the devices and a list of all users that are allowed to listen to the updates
    //devices = array("device_id" => array("conn" => $conn, "listerners" => array("user_id", "user_id")));
    protected $devices = array(); 

    //Array containing all the users that are connected to the websocket
    //users = array("user_id" => array("conn" => $conn));
    protected $users = array();
    protected $count = 1;

    
    /**
     * Function that sends a json object to all the listeners of a device that are currently online
     * 
     * @param $deviceID ID of the device that is sending the update
     * @param $jsonData json object that is send to the listeners
     * @return void
     */
    public function send_json_to_listeners($deviceID, $jsonData){
        foreach ($this->devices[$deviceID]["listerners"] as $listener){
            if(array_key_exists($listener, $this->users)){
                $this->users[$listener]["conn"]->send($jsonData);
            }
            
        }
    }

    /**
     * Function that registers a device to the websocket
     * The device needs to send its API key that will be checked in the database
     * If correct the device will be registered in the devices array
     * Also sends a message to all the listeners of the device and the database that the device is online
     * 
     * @param $apikey API key of the device that is registering
     * @param $conn Connection of the device that is registering
     * @return void
     */
    public function register_apikey($apikey, $conn){
        $deviceID = $this->db->check_API($apikey);
        if ($deviceID == false){
            $conn->close();
            return;
        }
        $this->db->toggle_online($deviceID,1);
        $listeners = $this->db->get_Listerners($deviceID);
        
        $this->devices[$deviceID] = array("conn" => $conn, "listerners" => $listeners);
        $this->send_json_to_listeners($deviceID, json_encode(array("type" => "online", "device_id" => $deviceID, "online" => 1)));
        
    }

    /**
     * Function that handles incoming messages with the type "request"
     * The request type "request" is used for user to adjust the speed of the device (see data_protecol.md for more info).
     * 
     * @param $from Connection of the device that is sending the message
     * @param $msg Message that is send by the device
     * @return void
     */
    public function handle_request($from, $msg, $json){
        $targetDevice = $json->data->device_id;
            if (array_key_exists($targetDevice, $this->devices)){
                    echo "device connected";
                    $this->devices[$targetDevice]["conn"]->send($msg);
            }
            else{
                $from->send("device not connected");
            }

    }

    /**
     * Function that handles incoming messages with the type "DB_update"
     * The request type "DB_update" is used for devices to send updates to the database (see data_protecol.md for more info).
     * Also sends the update to all the listeners of the device that send the update
     * 
     * @param $from Connection of the device that is sending the message
     * @param $msg Message that is send by the device
     * @return void
     */
    public function handle_DB_update($from, $msg, $json){
        $id = false;
            foreach($this->devices as $key => $value){
                if($value["conn"] == $from){
                    $id = $key;
                } 
            }
            if ($id == false){
                echo "DB_updates from unregistered device";
                $from->close();
                return;
            }
            $json->device_id =  $id;
            $this->send_json_to_listeners($id, json_encode($json));
            $this->db->update_device($json->data, $id);

    }

    /**
     * Function that handles incoming messages with the type "response"
     * The request type "response" is used for devices to be able to respond to requests (see data_protecol.md for more info).
     * The response is send to all the listeners of the device that send the response
     * 
     * @param $from Connection of the device that is sending the message
     * @param $msg Message that is send by the device
     * @return void
     */
    public function handle_response($from, $msg, $json){
        $id = false;
            foreach($this->devices as $key => $value){
                if($value["conn"] == $from){
                    $id = $key;
                } 
            }
            if ($id == false){
                echo "response from unregistered device";
                $from->close();
                return;
            }
            $json->device_id =  $id;
            $msg = json_encode($json);
            $this->send_json_to_listeners($id, $msg);
            
    }

   
    /**
     * Function that validates new connections from clients
     * It checks or the client send a valid cookie by comparing it to the session file
     * If the cookie is valid the user is registered in the users array
     * 
     * @param $conn Connection of the client that is trying to connect
     * @param $session_file Path to the session file of the client
     * @return void
     */
    public function register_user($conn, $session_file){
        if(!file_exists($session_file)){
            echo $session_file;
            echo " session file does not exist";
            $conn->close();
            return;
        }
        $session_data = $this->get_session_data($session_file);
        if ($session_data["login"] != 1){
            echo "user not logged in";
            $conn->close();
            return;
        }
        else{
            $user_id = $session_data["user_id"];
            $this->users[$user_id] = array("conn" => $conn);
            echo "user logged in";
            return;

        }
    }


    public function __construct() {
        session_save_path("/var/lib/php/sessions/");
        $this->db = new database();
    }

    /**
     * Function that gets the session data from the session file
     * 
     * @param $fileString Path to the session file
     * @return $session_data Array with the session data
     */
    public function get_session_data($fileString){
        $contents = file_get_contents($fileString);
        $explodeString = explode(";", $contents);
        $session_data = array();
        foreach ($explodeString as $segment) {
            if ($segment == ""){
                break;
            }
            $segment = explode("|", $segment);
            $key = $segment[0];
            $value = explode(":", $segment[1]);
            $value = $value[array_key_last($value)];
            $session_data[$key] = $value;
        }
        return $session_data;


    }

    /**
     * Function that is called when a new connection is made
     * Its responsible for checking the cookie and registering the user or device if the cookie is valid
     * 
     * @param $conn Connection of the client that is trying to connect
     * @return void
     */
    public function onOpen(ConnectionInterface $conn) {
        $cookie = $conn->httpRequest->getHeader('cookie');
        $cookie = explode("=", $cookie[0]);
        
        if ($cookie[0] == "API"){
            $this->register_apikey($cookie[1] , $conn);
            return;
        }
        else if ($cookie[0] == "PHPSESSID"){
            $session_file = "/var/lib/php/sessions"."/sess_".$cookie[1];
            $this->register_user($conn, $session_file);
        }
        else{
            echo "no cookie";
            $conn->close();
        }
    }

    /**
     * Function that is called when a new message is received
     * Its responsible for handling the message and calling the correct function to handle the message
     * 
     * @param $from Connection of the client that send the message
     * @param $msg Message that is send by the client
     * @return void
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        
        $json = json_decode($msg);
       
        if($json->type == "request"){
            $this->handle_request($from, $msg, $json);
        }
        else if($json->type == "DB_update"){
            $this->handle_DB_update($from, $msg, $json);
        }

        if($json->type == "response"){
            $this->handle_response($from, $msg, $json);
        } 
    }

    /**
     * Function that is called when a connection is closed
     * Its checks the arrays for the cocnnection that is closed and removes it from the array
     * if a device is disconnected it will be set to offline in the database
     * 
     * @param $conn Connection of the client that is trying to connect
     * @return void
     */
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        
        foreach ($this->devices as $key => $value)
        {
           
            if($value["conn"] == $conn)
            {   
                $this->db->toggle_online($key,0);
                $this->send_json_to_listeners($key, json_encode(array("type" => "online", "device_id" => $key, "online" => 0)));
                unset($this->devices[$key]);
            }
        }
        foreach ($this->users as $key => $value)
        {
            if($value["conn"] == $conn)
            {
                unset($this->users[$key]);
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * Function that is called when an error occurs
     * If a error occurs the connection is closed
     * 
     * @param $conn Connection of the client that is trying to connect
     * @param $e Exception that is thrown
     * @return void
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
