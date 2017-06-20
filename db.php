<?php

/*
    This file is part of Booking App.

    Booking App is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Booking App is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Booking App.  If not, see <http://www.gnu.org/licenses/>.
*/

    require_once("secrets.php");

    $dbuser = "user";
    $dbpassword = "password";
    $dbname = "database";

    //detect if database tables exist and if not create them

    $test_link = mysqli_connect("localhost", $dbuser, $dbpassword, $dbname);
    mysqli_set_charset($test_link,"utf8");
    
    if ($result = mysqli_query($test_link,"SHOW TABLES LIKE 'users'")) {
        if(mysqli_num_rows($result) == 1) {
            // "Table exists";
            
        }
        else {
            // "Table does not exist";
            mysqli_query($test_link,"CREATE TABLE users (id int(11) NOT NULL AUTO_INCREMENT, data varchar(2048) NOT NULL, PRIMARY KEY (id))") or die(mysqli_error($test_link));
            mysqli_query($test_link,"INSERT INTO users VALUES (1,'{\"name\":\"$default_admin_user\",\"password\":\"$default_admin_password\"}')")  or die(mysqli_error($test_link));
        }
    }

    if ($result = mysqli_query($test_link,"SHOW TABLES LIKE 'bookings'")) {
        if(mysqli_num_rows($result) == 1) {
            // "Table exists";
        }
        else {
            // "Table does not exist";
            mysqli_query($test_link,"CREATE TABLE bookings (id int(11) NOT NULL AUTO_INCREMENT,data varchar(2048) NOT NULL, PRIMARY KEY (id))")  or die(mysqli_error($test_link));
        }
    }
    
    if ($result = mysqli_query($test_link,"SHOW TABLES LIKE 'events'")) {
        if(mysqli_num_rows($result) == 1) {
            // "Table exists";
        }
        else {
            //"Table does not exist";
            mysqli_query($test_link,"CREATE TABLE events (id int(11) NOT NULL AUTO_INCREMENT,data varchar(2048) NOT NULL, PRIMARY KEY (id))")  or die(mysqli_error($test_link));
        }
    }
    
    if ($result = mysqli_query($test_link,"SHOW TABLES LIKE 'login'")) {
        if(mysqli_num_rows($result) == 1) {
            // "Table exists";
        }
        else {
            //"Table does not exist";
            mysqli_query($test_link,"CREATE TABLE login (latest bigint(20) DEFAULT NULL)")  or die(mysqli_error($test_link));
            mysqli_query($test_link,"INSERT INTO login VALUES ('1497000000')")  or die(mysqli_error($test_link));
        }
    }

    mysqli_close($test_link);

?>