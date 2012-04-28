<?php
/*  Copyright 2011  Web Technology Solutions Ltd  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR WPS Media Gallery

IMPORTANT PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT CAREFULLY BEFORE CONTINUING WITH THIS PROGRAM 

INSTALL: Web Technology Solutions Ltd End-User License Agreement ("EULA") is a legal agreement between you (either an individual or a single entity) and Web Technology Solutions Ltd, for the software product(s) identified above which may include associated software components, media, printed materials, and "online" or electronic documentation ("SOFTWARE PRODUCT"). 

By installing, copying, or otherwise using the SOFTWARE PRODUCT, you agree to be bound by the terms of this EULA. This license agreement represents the entire agreement concerning the program between you and Web Technology Solutions Ltd, (referred to as "licenser"), and it supersedes any prior proposal, representation, or understanding between the parties. If you do not agree to the terms of this EULA, do not install or use the SOFTWARE PRODUCT.

The SOFTWARE PRODUCT is protected by copyright laws and international copyright treaties, as well as other intellectual property laws and treaties. 

The SOFTWARE PRODUCT is licensed, not sold.

1. GRANT OF LICENSE. 
The SOFTWARE PRODUCT is licensed as follows: 
(a) Installation and Use.
Web Technology Solutions Ltd grants you the right to install and use copies of the SOFTWARE PRODUCT on your computer running a validly licensed copy of the operating system for which the SOFTWARE PRODUCT was designed.
(b) Backup Copies.
You may also make copies of the SOFTWARE PRODUCT as may be necessary for backup and archival purposes.

2. DESCRIPTION OF OTHER RIGHTS AND LIMITATIONS.
(a) Maintenance of Copyright Notices.
You must not remove or alter any copyright notices on any and all copies of the SOFTWARE PRODUCT.
(b) Distribution.
You may not distribute registered copies of the SOFTWARE PRODUCT to third parties. Evaluation versions available for download from Web Technology Solutions Ltd's websites may be freely distributed.
(c) Prohibition on Reverse Engineering, Decompilation, and Disassembly.
You may not reverse engineer, decompile, or disassemble the SOFTWARE PRODUCT, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation. 
(d) Rental.
You may not rent, lease, or lend the SOFTWARE PRODUCT.
(e) Support Services.
Web Technology Solutions Ltd may provide you with support services related to the SOFTWARE PRODUCT ("Support Services"). Any supplemental software code provided to you as part of the Support Services shall be considered part of the SOFTWARE PRODUCT and subject to the terms and conditions of this EULA. 
(f) Compliance with Applicable Laws.
You must comply with all applicable laws regarding use of the SOFTWARE PRODUCT.

3. TERMINATION 
Without prejudice to any other rights, Web Technology Solutions Ltd may terminate this EULA if you fail to comply with the terms and conditions of this EULA. In such event, you must destroy all copies of the SOFTWARE PRODUCT in your possession.

4. COPYRIGHT
All title, including but not limited to copyrights, in and to the SOFTWARE PRODUCT and any copies thereof are owned by Web Technology Solutions Ltd or its suppliers. All title and intellectual property rights in and to the content which may be accessed through use of the SOFTWARE PRODUCT is the property of the respective content owner and may be protected by applicable copyright or other intellectual property laws and treaties. This EULA grants you no rights to use such content. All rights not expressly granted are reserved by Web Technology Solutions Ltd.

5. NO WARRANTIES
Web Technology Solutions Ltd expressly disclaims any warranty for the SOFTWARE PRODUCT. The SOFTWARE PRODUCT is provided 'As Is' without any express or implied warranty of any kind, including but not limited to any warranties of merchantability, noninfringement, or fitness of a particular purpose. Web Technology Solutions Ltd does not warrant or assume responsibility for the accuracy or completeness of any information, text, graphics, links or other items contained within the SOFTWARE PRODUCT. Web Technology Solutions Ltd makes no warranties respecting any harm that may be caused by the transmission of a computer virus, worm, time bomb, logic bomb, or other such computer program. Web Technology Solutions Ltd further expressly disclaims any warranty or representation to Authorized Users or to any third party.

6. LIMITATION OF LIABILITY
In no event shall Web Technology Solutions Ltd be liable for any damages (including, without limitation, lost profits, business interruption, or lost information) rising out of 'Authorized Users' use of or inability to use the SOFTWARE PRODUCT, even if Web Technology Solutions Ltd has been advised of the possibility of such damages. In no event will Web Technology Solutions Ltd be liable for loss of data or for indirect, special, incidental, consequential (including lost profit), or other damages based in contract, tort or otherwise. Web Technology Solutions Ltd shall have no liability with respect to the content of the SOFTWARE PRODUCT or any part thereof, including but not limited to errors or omissions contained therein, libel, infringements of rights of publicity, privacy, trademark rights, business interruption, personal injury, loss of privacy, moral rights or the disclosure of confidential information.

*/

include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');

global $wpdb;

$aid = $_POST['aid'];
$uid = $_POST['uid'];
$user_login = $_POST['user_login'];
$user_email = $_POST['user_email'];

if (upload_gallery_is_logged_in($uid, $user_login, $user_email)) {

	if (!empty($_FILES)) {
     	
		$html = '';
	
	    if ($aid != '') {

			if (get_option('symposium_img_db') == "on") {
			
				// Save to database

				// Work out decent version of original filename (as uploaded)
				$filename = $_FILES['Filedata']['name'];
				$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);

				// Check that upload folder exists
				if (!file_exists(WP_CONTENT_DIR."/uploads")) {
					if (!mkdir(WP_CONTENT_DIR."/uploads", 0777, true)) {
						echo '>Failed to create temporary upload folder: '.WP_CONTENT_DIR."/uploads, please create manually with permissions to allow uploads.";
						exit;
					}
				}

				// Move to original filename
				if (move_uploaded_file($_FILES['Filedata']['tmp_name'], WP_CONTENT_DIR."/uploads/".$filename)) {

					// Get rescaled image
					// NB. we don't store the large original in the database to keep size down
					// Produce 'show' version to test if format is supported
			        $show_image = scaleImageFileToBlob(WP_CONTENT_DIR."/uploads/".$filename, 'show');

			        if ($show_image == '') {
			            echo 'Image type not supported';
						exit;
			        } else {

						// Is supported, so produce thumbnail version
				        $thumb_image = scaleImageFileToBlob(WP_CONTENT_DIR."/uploads/".$filename, 'thumb');

						// Deal with quotes if present
			        	$show_image = addslashes($show_image);
			        	$thumb_image = addslashes($thumb_image);

						// Add uploaded image into database
   		      			$wpdb->query( $wpdb->prepare( "
 						INSERT INTO ".$wpdb->prefix."symposium_gallery_items
	     				( 	gid,
 							name,
 							owner,
	     					created,
 							cover,
 							original,
	     					photo,
            		        thumbnail,
							groupid,
							title
		                )
 						VALUES ( %d, %s, %d, %s, %s, %s, %s, %s, %d, %s )", 
		     		        array(
       				        	$aid, 
	           		        	'', 
       				        	$uid,
 		        			   	date("Y-m-d H:i:s"),
	     		        	   	'',
 				        	   	'',
 		        			   	$show_image,
	     		        	   	$thumb_image,
								0,
								''
 								) 
 		        		) );
			        }


					// remove temporary file
					$myFile = WP_CONTENT_DIR."/uploads/".$filename;
					unlink($myFile);	

					// Set album cover if not yet set
					$cover = $wpdb->get_var($wpdb->prepare("SELECT cover FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d", $aid));
					if (!$cover) {
						$first_item = $wpdb->get_var($wpdb->prepare("SELECT iid FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid LIMIT 0,1", $aid));
		      			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery_items SET cover = 'on' WHERE iid = %d", $first_item  ) );			
					}

					echo 'OK';


				} else {
					echo 'Failed to move uploaded file - check the permissions of '.WP_CONTENT_DIR.'/uploads.';
					exit;
				}


			} else {

				// Save to filesystem

				$tempFile = $_FILES['Filedata']['tmp_name'];

				// Where the files are going
				$targetPath = get_option('symposium_img_path')."/members/".$uid."/media/".$aid."/";
				$targetPath = str_replace('//','/',$targetPath);

				//New filename without odd characters
				$filename = $_FILES['Filedata']['name'];
				$filename = preg_replace('/[^A-Za-z0-9.]/','_',$filename);

				$uniqid = uniqid();

				// Work out paths to new images
				// $targetFile = path to copy of original;
				// $fullsize_targetFile = path to image shown
				// $thumbnail_targetFile = path to thumbnail
				$filename = $uniqid.'_'.$filename;
				$targetFile = $targetPath.$filename;

				$fullsize_targetFile =  $targetPath.'show_'.$filename;
				$thumbnail_targetFile = $targetPath.'thumb_'.$filename;

				if (!file_exists($targetPath)) {
					if (!mkdir($targetPath, 0777, true)) {
						$html = 'Failed to create temporary upload folder: '.$targetPath;
					}
				}

				if ($html == '') {			
					if (!move_uploaded_file($tempFile,$targetFile)) {
						$html .= "FAILED: Could not move ".$tempFile." to ".$targetFile;
					} else {

						// resize to a various sizes
						$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';
						include_once('../wp-symposium/SimpleImage.php');
					   	$image = new symposium_SimpleImage();
					   	$image->load($targetFile);
					   	$image->resizeToWidth(800);
					   	$image->save($fullsize_targetFile);
					   	$image->resizeToWidth($thumbnail_size);
					   	$image->save($thumbnail_targetFile);

					   	// Record filename of uploaded image to database
						// NB. show and thumbnail are prefixes to filename
	        		      		$wpdb->query( $wpdb->prepare( "
	     						INSERT INTO ".$wpdb->prefix."symposium_gallery_items
			     				( 	gid,
	     							name,
	     							owner,
			     					created,
	     							cover,
	     							original,
			     					photo,
	                		        thumbnail,
									groupid,
									title
				                        )
	     						VALUES ( %d, %s, %d, %s, %s, %s, %s, %s, %d, %s )", 
				     		        array(
	           				        	$aid, 
			           		        	$filename, 
	           				        	$uid,
	     		        			   	date("Y-m-d H:i:s"),
			     		        	   	'',
	     				        	   	'',
	     		        			   	'',
			     		        	   	'',
										0,
										''
	     						) 
	     		        		) );

			     		        // Updated gallery table
	                      			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery SET updated = %s WHERE gid = %d", date("Y-m-d H:i:s"), $aid  ) );

									// Set album cover if not yet set
									$cover = $wpdb->get_var($wpdb->prepare("SELECT cover FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d", $aid));
									if (!$cover) {
										$first_item = $wpdb->get_var($wpdb->prepare("SELECT iid FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid LIMIT 0,1", $aid));
						      			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery_items SET cover = 'on' WHERE iid = %d", $first_item  ) );			
									}

						echo 'OK';

					}

				} else {

	     				echo $html;
		     			exit;

	     		}
	
			}
								
		} else {

			echo "NO ALBUM ID PASSED: ".$aid;
			exit;
  
	  	}
	
	} else {
	
		echo __("Failed to upload the file.", "wp-symposium");
		exit;
	}
} else {
	echo "NOT LOGGED IN";
	exit;
}

function upload_gallery_is_logged_in($uid, $user_login, $user_email) {

	global $wpdb;
	$user = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM ".$wpdb->base_prefix."users WHERE ID = %d AND lcase(user_login) = %s AND lcase(user_email) = %s", $uid, $user_login, $user_email ) );
	if ($user) {
		return true;
	} else {
		return false;
	}
	
}

function scaleImageFileToBlob($file, $size) {

    $source_pic = $file;
	$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';
    if ($size == 'show') {
    	$max_width = 800;
    	$max_height = 600;
    } else {
    	$max_width = $thumbnail_size;
    	$max_height = $thumbnail_size;
    }

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
