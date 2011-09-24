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

global $wpdb, $blog_id;

$tid = $_POST['tid'];
$uid = $_POST['uid'];
$user_login = $_POST['user_login'];
$user_email = $_POST['user_email'];

if (upload_file_is_logged_in($uid, $user_login, $user_email)) {

	if (!empty($_FILES)) {
	
		$html = '';
	

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

		if ($config->img_db == "on") {
		
			// Save to database

			// Work out decent version of original filename
			$filename = $_FILES['Filedata']['name'];
			$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);

			// Check that extension is allowed
			$ext = explode('.', strtolower($filename));
			if (strpos($config->image_ext.','.$config->video_ext.','.$config->doc_ext, $ext[sizeof($ext)-1]) > 0) {

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
			            echo 'Image type not supported';
			        } else {
		        	
			        	$image = addslashes($image);
		            
						// Add uploaded image into database
		 				$wpdb->query( $wpdb->prepare( "
		 				INSERT INTO ".$wpdb->prefix."symposium_topics_images
		     				( 	uid,
								filename,
								tid,
		 						upload
		    	                )
		 					VALUES ( %d, %s, %d, %s )", 
		 		        		array(
		       		        	$uid, 
								$filename,
								0,
		 		        		$image
		 						) 
		 	    		    ) );
	
						// get list of uploaded files
						$html = '';
						$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = 0 AND uid = ".$uid." ORDER BY tmpid";
						$images = $wpdb->get_results($sql);
						foreach ($images as $file) {
							$html .= '<div>';
							$html .= '<a href=""';
							$ext = explode('.', $file->filename);
							if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
								$html .= ' rel="symposium_forum_images-'.$tid.'"';
							} else {
								$html .= ' target="_blank"';
							}
							$html .= ' title="'.$file->filename.'">'.$file->filename.'</a> ';
							$html .= '<img id="'.$tid.'_'.$uid.'_tmp" title="'.$file->filename.'" class="remove_forum_image link_cursor" src="'.WP_CONTENT_URL.'/plugins/wp-symposium/images/delete.png" /> ';
							$html .= '</div>';	
						}
					
			        }
	
					// remove temporary file
					$myFile = WP_CONTENT_DIR."/uploads/".$filename;
					unlink($myFile);	
				
				} else {
					$html .= '<p><span style="color:red;font-weight:bold">Failed to move uploaded file - check the permissions of '.WP_CONTENT_DIR.'/uploads.</span></p>';
				}

			} else {
				// Invalid extension, just remove uploaded file
				$html .= __('Invalid file extension, the following are permitted:', 'wp-symposium').' '.$config->image_ext.','.$config->video_ext.','.$config->doc_ext;
			}	        
	
		} else {
		
			// Save to filesystem

			$tempFile = $_FILES['Filedata']['tmp_name'];

			global $blog_id;
			if ($blog_id > 1) {
				$targetPath = $config->img_path."/".$blog_id."/forum/".$tid.'_'.$uid.'_tmp/';
			} else {
				$targetPath = $config->img_path."/forum/".$tid.'_'.$uid.'_tmp/';
			}
			$filename = $_FILES['Filedata']['name'];
			$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);
			$targetFile =  str_replace('//','/',$targetPath) . $filename;
		
			// Check that extension is allowed
			$ext = explode('.', strtolower($filename));
			if (strpos($config->image_ext.','.$config->video_ext.','.$config->doc_ext, $ext[sizeof($ext)-1]) > 0) {
			
				if (!file_exists($targetPath)) {
					if (!mkdir($targetPath, 0777, true)) {
						$html = 'Failed to create upload folder: '.$targetPath;
					}
				}

				if (!move_uploaded_file($tempFile,$targetFile)) {
					$html .= "FAILED: Could not move ".$tempFile." to ".$targetFile;
				} else {

					$html = '';
					$handler = opendir($targetPath);
					while ($file = readdir($handler)) {
						if ($file != "." && $file != ".." && $file != ".DS_Store") {
							$html .= '<div>';
								if ($blog_id > 1) {
									$url = WP_CONTENT_URL.'/wps-content/'.$blog_id.'/forum/'.$tid.'_'.$uid.'_tmp/'.$file;
								} else {
									$url = WP_CONTENT_URL.'/wps-content/forum/'.$tid.'_'.$uid.'_tmp/'.$file;
								}
								$html .= '<a href="'.$url.'"';
								$ext = explode('.', $file);
								if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
									$html .= ' rel="symposium_forum_images-'.$tid.'"';
								} else {
									$html .= ' target="_blank"';
								}
								$html .= ' title="'.$file.'">'.$file.'</a> ';
								$html .= '<img id="'.$tid.'_'.$uid.'_tmp" title="'.$file.'" class="remove_forum_image link_cursor" src="'.WP_CONTENT_URL.'/plugins/wp-symposium/images/delete.png" /> ';

							$html .= '</div>';
						}
					}			
					closedir($handler);

					echo $html;
					exit;
				
				}
			} else {
				// Invalid extension, just remove uploaded file
				$html .= __('Invalid file extension, the following are permitted:', 'wp-symposium').' '.$config->image_ext.','.$config->video_ext.','.$config->doc_ext;
			}

		}
	
		echo $html;
		exit;
	
	} else {
	
		echo __("Failed to upload the file.", "wp-symposium");
		exit;
	}
} else {
	echo "NOT LOGGED IN";
	exit;
}

function upload_file_is_logged_in($uid, $user_login, $user_email) {

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
    $max_width = 20000;
    $max_height = 20000;

    list($width, $height, $image_type) = getimagesize($file);

    switch ($image_type)
    {
        case 1: $src = imagecreatefromgif($file); break;
        case 2: $src = imagecreatefromjpeg($file);  break;
        case 3: $src = imagecreatefrompng($file); break;
        default: return $file;  break;
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
        default: return $file; break;
    }

    $final_image = ob_get_contents();

    ob_end_clean();

    return $final_image;
}

?>