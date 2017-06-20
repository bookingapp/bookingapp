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

//limit rate
retry:
$sql = "select latest from login limit 1";
$result = mysqli_query($link,$sql);
$object=mysqli_fetch_object($result);
$latest=$object->result;
if ($latest==time()) {
    sleep(2);
    $sql = "update login set latest=".time();
    $result = mysqli_query($link,$sql);
    goto retry;
}
$sql = "update login set latest=".time();
$result = mysqli_query($link,$sql);

?>