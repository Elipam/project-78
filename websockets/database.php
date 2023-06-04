<?php


class database{
    // set name of database 
    protected $servername = "localhost";
    // set name of database
    protected $dbname = "Battery_system";
    // set database username
    protected $username = "API_user";
    // set database password
    protected $password = "3Pp8bx1!3";


    /**
     * Clears the input from any special characters
     * 
     * @param $input string
     * @return string
     */
    protected function clearQuery($input){
        $input = trim($input);
        $input = htmlspecialchars($input);

        return $input;
    }

    /**
     * Connects to the database
     * 
     * @return mysqli
     */
    protected function connect(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    /**
     * Checks the given api key against the database
     * 
     * @param $apiKey string
     * @return $deviceid if found else false
     */
    public function check_API($apiKey){
        $this->clearQuery($apiKey);
        $apiKeyHash = hash("sha256", $apiKey);
        $conn = $this->connect();
        $query = $conn->prepare("SELECT device_id FROM devices WHERE api_key = ? LIMIT 1;");
        $query->bind_param('s', $apiKeyHash);
        $query->execute();
        $query->bind_result($deviceId);
        $query->fetch();
        $query->close();
        $conn->close();

        if($deviceId != null){
            return $deviceId;
        }else{
            return false;
        }
    }

    /*
     * Function that updates the online status of a device in the database
     * 
     * @param $device_id int
     * @param $online int
     * @return void
     */
    public function toggle_online($device_id, $online){
        $conn = $this->connect();
        $query = $conn->prepare("UPDATE devices SET online = ? WHERE device_id = ?;");
        $query->bind_param('ii', $online, $device_id);
        $query->execute();
        $query->close();
        $conn->close();
    }

    /*
     * Function that gets all the listeners of a device, A listerner is either a user with permissions or an admin
     * 
     * @param $device_id int
     * @return array of user_id's
     */
    public function get_Listerners($device_id){
        $conn = $this->connect();
        $query = $conn->prepare("SELECT user_id FROM user_permissions WHERE device_id = ?;");
        $query->bind_param('i', $device_id);
        $query->execute();
        $query->bind_result($user_id);
        $listeners = array();
        while($query->fetch()){
            array_push($listeners, $user_id);
        }
        $query->close();
        $query = $conn->prepare("SELECT user_id FROM users WHERE admin = '1';");
        $query->execute();
        $query->bind_result($user_id);
        while($query->fetch()){
            array_push($listeners, $user_id);
        }
        $query->close();
        $conn->close();
        return $listeners;
    }

    /*
     * Function that puts the data gained from a device and puts it into the database
     * 
     * @param $user_id int
     * @return array of device_id's
     */
    public function update_device($data, $device_id){
        
        $Voltage = $this->clearQuery($data->voltage);
        $Current = $this->clearQuery($data->amperage);
        $Temp = $this->clearQuery($data->temp);
        $motor1 = $this->clearQuery($data->motor1);
        $motor2 = $this->clearQuery($data->motor2);
        $dateTime = date("Y-m-d H:i:s");
        
        $conn = $this->connect();
        $query = $conn->prepare("INSERT INTO measurements(device_id, temperature, voltage, time_, ampere, motor1, motor2) VALUES (?, ?, ?, ?, ?,?,?)");
        $query->bind_param("iddsdii", $device_id, $Temp, $Voltage, $dateTime, $Current, $motor1, $motor2);
        $query->execute();
        $query->close();
        $conn->close();
    }
}