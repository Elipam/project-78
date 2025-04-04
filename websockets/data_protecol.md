# data protecol for the json data in the websockets

Here we will go over how to use the the websocket server of the vrfb-test.nl Website.
It will be devided in 2 parts: how to user the websocket as a user and how to use the websocket as a device.
With users we mean the people that are using the website to connect to the weboscket server.
With devices we mean the battery systems that connect to the websocket server.

For both users and devices the URL to connect to the websocket server is the same: 

```
wss://vrfb-test.nl/wsapp/
```

# User

## initial connection

As a user you can only connect to the server by sending a correct cookie with the connection request.
This cookie is the same cookie generated by the server when you first log into the website.
This cookie is stored in your browser and is send automatically when you connect to the websocket server.
Therefor you do not have to do anything to connect to the websocket server as a user as long as the connection is made from within the site.

## JSON data

As a user you will receive data from the websocket server about the devices that you are allowed to listen to.
You are also able to send data to the websocket server to control the devices that you are allowed to control.
All this data will be send in JSON format.
Here a list of all the data that you can receive and send:

## incoming data

### **DB_update**
When a device sends new data to the database the websocket server will also send the new data to all the users that are allowed to listen to that device. the data will be in the following format:

```
{
    "type": "DB_update",
    "device_id": 1,
    "data": {
            "amperage": 0.0,
            "voltage": 0.0,
            "temp": 0.0
    }
}
```

### **online**
When a device connects or disconnects from the websocket server the websocket server will send a message to all the users that are allowed to listen to that device. the data will be in the following format:

```
{
    "type": "online",
    "device_id": 1,
    "online": true
}
```

### **response** 
When a user makes a request to update the speed of 1 of the motors the device will send a response to the websocket server. the websocket server will then send the response data to all devices that are allowed to listen to the device. the data will be in the following format:

```
{
    "type": "response",
    "data": {
            "device_id": 1,
            "response": 0.0,
            "motor_id": 1
    }
}
```

Response is the speed that the motor is currently running at.<br>

## outgoing data

### **request**
When a user wants to update the speed of 1 of the motors the user will send a request to the websocket server. the websocket server will then send the request to the device. the data will be in the following format:

```
{
    "type": "request",
    "data": {
            "device_id": 1,
            "request_data": 0.0,
            "motor_id": 1
    }
}
```

request_data is the speed that the user wants the motor to run at.

# Device

## initial connection

To establish a connection to the websocket server as a device you will need to send a correct cookie with the connection request.<br>
This cookie is the api key that is generated by the server when you first register the device. <br>
The cookie should be send in the following format:
    
    
    cookie: "API=your_api_key"
    

If the device is not succesfully connected the websocket will throw out the connection. <br>
If the connection is correct will not send any messages.<br>

## JSON data

As a device you are allowed to send data to the database and respond to requests from the users. All the data that you send to the websocket server will be in JSON format. Here a list of all the data that you can receive and send:<be>

## incoming data

### **request**
When a user wants to update the speed of 1 of the motors the user will send a request to the websocket server. the websocket server will then send the request to the device. the data will be in the following format:

```
{
    "type": "request",
    "data": {
            "device_id": 1,
            "request_data": 0.0,
            "motor_id": 1
    }
}
```

request_data is the speed that the user wants the motor to run at.<br>

## outgoing data

### **DB_update**
When a device sends new data to the database the websocket server will also send the new data to all the users that are allowed to listen to that device. the data will be in the following format:<br>

```
{
    "type": "DB_update",
    "device_id": 1,
    "data": {
            "amperage": 0.0,
            "voltage": 0.0,
            "temp": 0.0
    }
}
```

### **response** 
When a user makes a request to update the speed of 1 of the motors the device will send a response to the websocket server. the websocket server will then send the response data to all devices that are allowed to listen to the device. the data will be in the following format:<br>

```
{
    "type": "response",
    "data": {
            "device_id": 1,
            "response": 0.0,
            "motor_id": 1
    }
}
```





