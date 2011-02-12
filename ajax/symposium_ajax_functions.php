<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');
	

$action = $_POST['action'].$_GET['action'];

if ($action == "symposium_test_ajax") {

	$value = $_POST['postID'];	
	echo $value*100;
	exit;
}

?>
