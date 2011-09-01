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

include_once('../../../../wp-load.php');
include_once('../../../../wp-includes/wp-db.php');

global $wpdb;

$uid = $_POST['uid'];
$user_login = $_POST['user_login'];
$user_email = $_POST['user_email'];

if (upload_admin_is_logged_in($uid, $user_login, $user_email)) {

	if (!empty($_FILES)) {

		$html = '';
	
		if ( $uid > 0 ) {
		
			$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
	
			if ($config->img_db == "on") {
			
				// Save to database

				// Work out decent version of original filename
				$filename = $_FILES['Filedata']['name'];
				$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);

				// Check that upload folder exists
				if (!file_exists(WP_CONTENT_DIR."/uploads")) {
					if (!mkdir(WP_CONTENT_DIR."/uploads", 0777, true)) {
						$html .= '<p>Failed to create temporary upload folder: '.WP_CONTENT_DIR."/uploads, please create manually with permissions to allow uploads.</p>";
					}
				}

				// Move to original filename
				if (move_uploaded_file($_FILES['Filedata']['tmp_name'], WP_CONTENT_DIR."/uploads/".$filename)) {
	
			        $image = scaleImageFileToBlob(WP_CONTENT_DIR."/uploads/".$filename);
		        
			        if ($image == '') {		        	
			            $html .= 'Image type not supported<br />';		            
			        } else {
	
			            $image = addslashes($image);
		            
						// update database with resized blob
						$wpdb->update( $wpdb->base_prefix.'symposium_config', 
							array( 'img_upload' => $image ), 
							array( 'img_db' => 'on' ), 
							array( '%s' ), 
							array( '%s' )
							);
					
			        }

					// remove temporary file
					$myFile = WP_CONTENT_DIR."/uploads/".$filename;
					unlink($myFile);	
				
					$img_src = WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_admin_avatar.php?r=".time();
				
				} else {
					$html .= '<p><span style="color:red;font-weight:bold">Failed to move uploaded file - check the permissions of '.WP_CONTENT_DIR.'/uploads.</span></p>';
				}
			
			} else {
			
				// Save to filesystem
			
				$tempFile = $_FILES['Filedata']['tmp_name'];
		
				$targetPath = $config->img_path."/members/".$uid."/profile/";
				$filename = $_FILES['Filedata']['name'];
				$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);
				$targetFile =  str_replace('//','/',$targetPath) . $filename;
			
				if (!file_exists($targetPath)) {
					if (!mkdir($targetPath, 0777, true)) {
						$html = 'Failed to create temporary upload folder: '.$targetPath;
					}
				}

				if ($html == '') {			
					if (!move_uploaded_file($tempFile,$targetFile)) {
						$html .= "FAILED: Could not move ".$tempFile." to ".$targetFile;
					} else {
					
						// resize to a decent size
						include_once('../SimpleImage.php');
					   	$image = new symposium_SimpleImage();
					   	$image->load($targetFile);
					   	$image->resizeToWidth(400);
					   	$image->save($targetFile);
				   	
						// update database with filename
						$wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
							array( 'profile_photo' => $filename ), 
							array( 'uid' => $uid ), 
							array( '%s' ), 
							array( '%d' )
							);

					}
				}

				$img_url = $config->img_url."/members/".$uid."/profile/";	
				$img_src =  str_replace('//','/',$img_url) . $filename;
			
			}
	
			if ($html == '') {	
				
				$html .= "<style>";
			
				$html .= "	#admin_image_div {";
				$html .= "		overflow:visible; ";
				$html .= "		margin-top:15px; ";
				$html .= "	}";

				$html .= "	#admin_image_to_crop {";
				$html .= "		float:left; ";
				$html .= "		margin-bottom:25px";
				$html .= "	}";

				$html .= "	#image_preview {";
				$html .= "		clear: both; ";
				$html .= "		float:left;";
				$html .= "		width:100px;";
				$html .= "		height:100px;";
				$html .= "		overflow:hidden;";
				$html .= "	}";

				$html .= "  #image_instructions {";
				$html .= "		margin-left: 20px; ";
				$html .= "		float: left;";
				$html .= "		width: 250px;";
				$html .= "	}";
			
				$html .= "</style>";
			
				$html .= '<div id="admin_image_div">';
			
					$html .= '<div id="admin_image_to_crop">';
					$html .= "<img src='".$img_src."' id='admin_jcrop_target' />";
					$html .= '</div>';
			
					$html .= '<div id="image_preview"> ';
					$html .= "<img src='".$img_src."' id='admin_preview' />";
					$html .= '</div>';
		
					$html .= '<div id="image_instructions"> ';
						$html .= '<input type="text" id="x" name="x" />';
						$html .= '<input type="text" id="y" name="y" />';
						$html .= '<input type="text" id="x2" name="x2" />';
						$html .= '<input type="text" id="y2" name="y2" />';
						$html .= '<input type="text" id="w" name="w" />';
						$html .= '<input type="text" id="h" name="h" />';
						$html .= '<input type="submit" id="saveAdminAvatar" class="button-primary" value="OK" />';
					$html .= '</div>';

					$html .= '<br style="clear:both" />';

				$html .= '</div>';
			
			}
			
			echo $html;
			exit;

		} else {
		
			echo "NOT LOGGED IN";
			exit;
		        
	    }
	
	} else {
	
		echo __("Failed to upload the file.", "wp-symposium");
		exit;
	}
} else {
	echo 'NOT LOGGED IN';
	exit;
}

function upload_admin_is_logged_in($uid, $user_login, $user_email) {

	global $wpdb;
	$user = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM ".$wpdb->base_prefix."users WHERE ID = %d AND lcase(user_login) = %s AND lcase(user_email) = %s", $uid, $user_login, $user_email ) );
	if ($user) {
		return true;
	} else {
		return false;
	}
	
}

function scaleImageFileToBlob($file) {

    $source_pic = $file;
    $max_width = 400;
    $max_height = 20000;

    list($width, $height, $image_type) = getimagesize($file);
    
    switch ($image_type)
    {
        case 1: $src = imagecreatefromgif($file); break;
        case 2: $src = imagecreatefromjpeg($file);  break;
        case 3: $src = imagecreatefrompng($file); break;
        default: return '';  break;
    }

    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;

    if( ($width <= $max_width) && ($height <= $max_height) ){
        $tn_width = $width;
        $tn_height = $height;
        }elseif (($x_ratio * $height) < $max_height){
            $tn_height = ceil($x_ratio * $height);
            $tn_width = $max_width;
        }else{
            $tn_width = ceil($y_ratio * $width);
            $tn_height = $max_height;
    }

    $tmp = imagecreatetruecolor($tn_width,$tn_height);

    /* Check if this image is PNG or GIF, then set if Transparent*/
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($tmp, false);
        imagesavealpha($tmp,true);
        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
    }
    imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

    /*
     * imageXXX() only has two options, save as a file, or send to the browser.
     * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
     * So I start the output buffering, use imageXXX() to output the data stream to the browser, 
     * get the contents of the stream, and use clean to silently discard the buffered contents.
     */
    ob_start();

    switch ($image_type)
    {
        case 1: imagegif($tmp); break;
        case 2: imagejpeg($tmp, NULL, 100);  break; // best quality
        case 3: imagepng($tmp, NULL, 0); break; // no compression
        default: echo ''; break;
    }

    $final_image = ob_get_contents();

    ob_end_clean();

    return $final_image;
}

?>