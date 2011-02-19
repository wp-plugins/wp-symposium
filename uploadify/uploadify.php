<?php
/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2010 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

if (!empty($_FILES)) {

	include_once('../../../../wp-config.php');

	$tempFile = $_FILES['Filedata']['tmp_name'];

	if ( WPS_TMP_DIR != "WPS_TMP_DIR" ) {
		$targetPath = WPS_TMP_DIR.'/';
	} else {
		$targetPath = WP_PLUGIN_DIR.'/wp-symposium/uploads/';
	}

	$filename = $_FILES['Filedata']['name'];
	$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);
	$targetFile =  str_replace('//','/',$targetPath) . $filename;
	
	if (!file_exists($targetPath)) {
		mkdir(str_replace('//','/',$targetPath), 0755, true);
	}

	if (file_exists($targetPath)) {

		if (file_exists($tempFile)) {
		
			if (move_uploaded_file($tempFile,$targetFile)) {
				echo $filename;
				exit;
			} else {
				echo "FAILED: Could not move ".$tempFile." ".$targetFile;
				exit;
			};

		} else {
			echo "FAILED: Could not find temporary file ".$tempFile;
			exit;
		}
		
	} else {
		echo "FAILED: Could not create ".$targetPath." (".WPS_TMP_DIR.")";
		exit;
	}

} else {
	echo "FAILED: file did not upload at all";
	exit;
}
?>