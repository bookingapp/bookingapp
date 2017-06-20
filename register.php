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

require_once("tokens.php");
require_once("db.php");
require_once("secrets.php");

$tokens = tokens("1");
$admin_token = $tokens[0];
$salt = md5(rand(1,100000).time()."43nir%f");

//not a robot?
$ip=$_SERVER['REMOTE_ADDR'];
$recaptcha=$_GET['g-recaptcha-response'];
$param = "?secret=".$google_secret."&response=".$recaptcha."&remoteip=".$ip;
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify".$param);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);
curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
$res = curl_exec($curl);
curl_close($curl);
$res=json_decode($res, true);

//reCaptcha success check
if($res['success'])
{

	$fields = array(
		'name' => $_GET['username'],
		'password' => md5($_GET['password'].$salt),
		'email' => $_GET['email'],
		'salt' => $salt
	);
	$data = json_encode($fields);

	$link = mysqli_connect("localhost", $dbuser, $dbpassword, $dbname);
	mysqli_set_charset($link,"utf8");

	include("limit.php");

	$sql = "insert into users set data='$data'";
	$result = mysqli_query($link,$sql);
	$error = mysqli_error($link);
	$id=mysqli_insert_id($link);
	echo "{\"status\":\"success\"}";
}
else {
	echo "{\"status\":\"failure\"}";
}
?>
