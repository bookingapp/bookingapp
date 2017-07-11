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

$method = $_SERVER["REQUEST_METHOD"];
$token = $_GET["TOKEN"];
$user = $_GET["USER"];
$request = explode("/", trim($_SERVER["PATH_INFO"],"/"));
$raw=file_get_contents("php://input");
$input = json_decode($raw,true);
$link = mysqli_connect("localhost", $dbuser, $dbpassword, $dbname);
mysqli_set_charset($link,"utf8");
$data = mysqli_real_escape_string($link,$raw);
$table=preg_replace("/[^a-z0-9_]+/i","",array_shift($request));
$key = array_shift($request);
//echo "table: $table\nkey: $key\ndata: $data\n";
$tokens = tokens($user);
if (!in_array($token,$tokens)) {
	if (!($table=="events" && $method=='GET')) {
		header("HTTP/1.1 403 Forbidden");
		die();
	}
}	
if (in_array($table,array("users","events","bookings")))
{
switch ($method) {
case 'GET':
$sql = "select id,data from $table".($key?" WHERE id=$key":''); break;
break;
case 'PUT':
if ($table=="users" && $user!="1") break;
if ($table=="bookings") break;
$sql = "update $table set data='$data' where id=$key"; break;
break;
case 'POST':
if ($table=="users" && $user!="1") break;
if ($table!="bookings") {
	$sql = "insert into $table set data='$data'";
	break;
	}
else {
	$booking = json_decode($raw);
	mysqli_autocommit($link,FALSE);
	mysqli_begin_transaction($link);
	$result=mysqli_query($link,"SELECT id,data FROM events where id='$booking->event_id'");
	if ($result) {
		$result_object=mysqli_fetch_object($result);
		$event=json_decode($result_object->data);
		if ($event->bookings+$booking->bookings <= $event->places) {
			$event->bookings += $booking->bookings;
			$data = mysqli_real_escape_string($link,json_encode($event));
			$result=mysqli_query($link,"UPDATE events set data='$data' where id='$booking->event_id'");
			if ($result) {
				$data = mysqli_real_escape_string($link,$raw);
				$result = mysqli_query($link,"insert into bookings set data='$data'");
			}
			else {
				header("HTTP/1.1 423 Locked");
				mysqli_rollback($link);
				die("failed to update number of bookings in event");
			}
		}
		else {
				header("HTTP/1.1 423 Locked");
				mysqli_rollback($link);
				die("not enough places free in event");
		}
	}
	else {
				header("HTTP/1.1 423 Locked");
				mysqli_rollback($link);
				die("could not load events");
	}
	mysqli_commit($link);	
	header("HTTP/1.1 200 OK");
	echo mysqli_insert_id($link);
	exit();
}
break;
case 'DELETE':
if ($table=="users" && $user!="1") break;
if ($table=="bookings") {
		mysqli_autocommit($link,FALSE);
		mysqli_begin_transaction($link);
		$result=mysqli_query($link,"select id,data from bookings where id='$key'");
		if ($result) {
			$result_object=mysqli_fetch_object($result);
			$booking = json_decode($result_object->data);
			$event_id = $booking->event_id;
			$result = mysqli_query($link,"select id,data from events where id='$event_id'");
			if ($result) {
				$result_object=mysqli_fetch_object($result);
				$event = json_decode($result_object->data);
				if ($event->bookings >= $booking->bookings) {
					$event->bookings -= $booking->bookings;
					$data = mysqli_real_escape_string($link,json_encode($event));
					$result=mysqli_query($link,"UPDATE events set data='$data' where id='$booking->event_id'");
					if (!$result) {
					header("HTTP/1.1 423 Locked");
					mysqli_rollback($link);
					die("update number of bookings in event failed");
					}
				}
				else {
					header("HTTP/1.1 423 Locked");
					mysqli_rollback($link);
					die("places already unbooked");
				}
			}
			else {
					header("HTTP/1.1 423 Locked");
					mysqli_rollback($link);
					die("failed to load event");
			}
		}
		else {
					header("HTTP/1.1 423 Locked");
					mysqli_rollback($link);
					die("failed to load bookings");
			}
			
		$sql = "delete from bookings where id='$key'";
		$result = mysqli_query($link,$sql);			
		
		mysqli_commit($link);
		die("booking deleted successfully");
	}
	
	
}
//echo "sql: $sql\n";
$result = mysqli_query($link,$sql);
$error = mysqli_error($link);
//echo "error: $error\n";
if (!$result) {
header("HTTP/1.1 404 Not found");
die($error);
}
if ($method == 'GET') {

if (!$key) echo '[';
$j=0;
for ($i=0;$i<mysqli_num_rows($result);$i++) {
$result_object=mysqli_fetch_object($result);
$result_data = json_decode($result_object->data);
$result_data->id = $result_object->id;
if ($table=="users" && $user!="1" && $result_data->id!=$user) continue; //only admin can get all users
if ($table=="bookings" && $user!="1" && $result_data->user_id!=$user) continue; //users can only see own bookings
echo ($j++>0?',':'').json_encode($result_data);
}
if (!$key) echo ']';
}
elseif ($method == 'POST') {
echo mysqli_insert_id($link);
}
else
{
//echo mysqli_affected_rows($link);
}
mysqli_close($link);
}
else
{
	header("HTTP/1.1 400 Unknown Table");
	die();
}
header("HTTP/1.1 200 OK");
?>