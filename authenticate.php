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

include ("db.php");
include("tokens.php");

$link = mysqli_connect("localhost", $dbuser, $dbpassword, $dbname);
mysqli_set_charset($link,"utf8");

include("limit.php");

//query user list
$sql = "select id,data from users";
$result = mysqli_query($link,$sql);
$error = mysqli_error($link);

if ($result) {
    for ($i=0;$i<mysqli_num_rows($result);$i++) {
        $object=mysqli_fetch_object($result);
        $users[$object->id] = json_decode($object->data);
    }
    
    //for admin check against plaintext password, for other users check hashed password
    foreach ($users as $id => $user) {
        if (($_GET['username']==$user->name && md5($_GET['password'].$user->salt)==$user->password)
        || ($id=='1' && $_GET['username']==$user->name && $_GET['password']==$user->password)) {
		    $token = tokens($id);
		    echo "{\"TOKEN\":\"$token[0]\",\"ID\":\"$id\"}";		
	        break;
        }
    }
    
}

mysqli_close($link);

?>