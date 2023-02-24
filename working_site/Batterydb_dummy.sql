drop user if exists 'u122296p140383_u122296p140383'@'localhost';

CREATE USER 'u122296p140383_u122296p140383'@'localhost' IDENTIFIED BY '2r.07cddoa|O>Cdq[ju<J."x';
GRANT insert, select on * to 'u122296p140383_u122296p140383'@'localhost';

drop DATABASE if exists  u122296p140383_battery;
create DATABASE u122296p140383_battery;
use u122296p140383_battery;

create table users (
username varchar(255),
user_id int, 
admin varchar(255), 
password varchar(255), 
name varchar(255), 
email varchar(255));

insert into users VALUES ("admin", 1, "1", "admin", "admin", "1039779@hr.nl");
insert into users VALUES ("user", 1, "0", "user", "user", "1039779@hr.nl");

create table logs(
time DATETIME ,
user_id Int,
message varchar(255),
ip varchar(255));
 
create table measurements(
time_ DATETIME ,
measurement_id int,
 device_id int , 
 voltage float,
 temperature float, 
 ampere float);

insert into measurements values (NOW(), 1, 1, 1, 2, 3);

create table devices(
device_id int, 
display_name varchar(255), 
api_key varchar(255), 
description varchar(255), 
image varchar(255));

insert into devices values (1, "testdevice", "api1" , "da test", "hmm");
 
 