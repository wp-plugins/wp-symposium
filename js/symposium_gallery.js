jQuery(document).ready(function() { 	

    // Make sure query parameter is accurate
	if (symposium.q == '&amp;') {
		symposium.q = '&';
	}

	// Get translations
	var browseforfile = jQuery("#symposium_browseforfile").html();
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        GALLERY                                           |
	   +------------------------------------------------------------------------------------------+
	*/
	
   	// Act on "album_id" parameter, load album straight away (remember to set embed=on on hyperlink)
	if (symposium.album_id > 0 && symposium.embed == 'on') {
        
     	    jQuery.ajax({
      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
      		type: "POST",
      		data: ({
       			action:'menu_gallery',
     			album_id:symposium.album_id,
				uid1:symposium.current_user_page
      		}),
      		dataType: "html",
      		success: function(str){
     			jQuery('#profile_body').html(str);

				var user_login = jQuery("#symposium_user_login").html();
				var user_email = jQuery("#symposium_user_email").html();

          		jQuery('#menu_gallery_file_upload').uploadify({
				'uploader'  	: symposium.plugin_url+'uploadify/uploadify.swf',
				'buttonText'	: browseforfile,
				'script'    	: symposium.plugin_url+'upload_menu_gallery.php',
				'cancelImg' 	: symposium.plugin_url+'uploadify/cancel.png',
				'multi'      	: true,
				'auto'      	: true,
				'scriptData' 	: {'aid':symposium.album_id, 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
				'onError' 	: function(event, ID, fileObj, errorObj) {
							alert("Error: "+errorObj.type+" "+errorObj.info);
      						},
				'onComplete'	: function(event, queueID, fileObj, response, data) { 
							if (response.substring(0, 2) != 'OK') {
								alert(response); 
							}

						},
				'onAllComplete' : function(event,data) {
							jQuery("#dialog").html(data.filesUploaded + ' files uploaded successfully!');
							jQuery("#dialog").dialog({ title: 'File Upload', width: 600, height: 175, modal: true,
							buttons: {
									"OK": function() {
										jQuery("#dialog").dialog('close');
										window.location.href=symposium.plugin_url+'ajax/symposium_gallery_functions.php?href=redirect&num='+data.filesUploaded+'&aid='+symposium.album_id;
									}
								}
							});
    						}

			});

        	      	// Prepare ColorBox
        	      	jQuery("a[rel='symposium_gallery_photos']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
	      		}
            });	
        	
	} 
	
	// Manage album
	jQuery("#symposium_manage_album_button").live('click', function() {

        	symposium.album_id = jQuery(this).attr("title");
        
     		jQuery.ajax({
	      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	      		type: "POST",
	      		data: ({
	       			action:'menu_gallery_manage',
	     			album_id:symposium.album_id
	      		}),
	      		dataType: "html",
	      		success: function(str){
	     			jQuery('#profile_body').html(str);
	      		}
        	});	
        
   	 });

	// Manage album (select cover)
	jQuery(".symposium_photo_select_cover_button").live('click', function() {

		jQuery(".symposium_notice").inmiddle().show();

 		jQuery.ajax({
      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
      		type: "POST",
      		data: ({
       			action:'menu_gallery_select_cover',
     			item_id:jQuery(this).attr("title"),
				gallery_id:jQuery(this).attr("id")
      		}),
      		dataType: "html",
      		success: function(str){
				if (str != 'OK') { alert(str); }
				jQuery(".symposium_notice").fadeOut("slow");
      		}
    	});

	});

	// Change sharing status
	jQuery("#gallery_share").live('change', function() {

		jQuery('#symposium_album_sharing_save').show();

        	symposium.album_id = jQuery(this).attr("title");

     		jQuery.ajax({
	      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	      		type: "POST",
	      		data: ({
	       			action:'menu_gallery_change_share',
	     			album_id:symposium.album_id,
				new_share:jQuery("#gallery_share").val()
	      		}),
	      		dataType: "html",
	      		success: function(str){
				jQuery('#symposium_album_sharing_save').hide();
				if (str != 'OK') {
					alert(str);
				}
	      		}
        	});	

	});

	// Delete all
	jQuery(".symposium_photo_delete_all").live('click', function() {

		if ( confirm("Are you sure?") ) {

		        symposium.album_id = jQuery(this).attr("title");

	     		jQuery.ajax({
		      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	      			type: "POST",
		      		data: ({
		       			action:'menu_gallery_manage_delete_all',
	     				album_id:symposium.album_id		
		      		}),
	      			dataType: "html",
		      		success: function(str){
					jQuery('.symposium_photo_row').slideUp("slow");
	        	  		if (str != 'OK') {
	        	      			alert(str);
        	      			}
	      			}
		          });	

		}

	});

	// Delete
	jQuery(".symposium_photo_delete").live('click', function() {

		if ( confirm("Are you sure?") ) {

	          	var item_id = jQuery(this).attr("title");
			jQuery('#symposium_photo_row_'+item_id).slideUp("slow");

	     		jQuery.ajax({
		      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	      			type: "POST",
		      		data: ({
		       			action:'menu_gallery_manage_delete',
	     				item_id:item_id		
		      		}),
	      			dataType: "html",
		      		success: function(str){
					jQuery('#symposium_photo_saving_'+item_id).hide();
	        	  		if (str != 'OK') {
	        	      			alert(str);
        	      			}
	      			}
		          });	

		}

	});

	// Rename photo
	jQuery(".symposium_photo_update").live('click', function() {


          	var item_id = jQuery(this).attr("title");
          	var new_name = jQuery('#symposium_photo_'+item_id).val();
		jQuery('#symposium_photo_saving_'+item_id).show();
        
     		jQuery.ajax({
	      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	      		type: "POST",
	      		data: ({
	       			action:'menu_gallery_manage_rename',
	     			item_id:item_id,
	     			new_name:new_name		
	      		}),
	      		dataType: "html",
	      		success: function(str){
				jQuery('#symposium_photo_saving_'+item_id).hide();
	          		if (str != 'OK') {
        	      			alert(str);
              			}
	      		}
	          });	
        
    });    
         	
	// Click on album cover
	jQuery(".symposium_album_cover_action").live('click', function() {

        symposium.album_id = jQuery(this).attr("title");
		symposium_show_album();
        
    });

	// Back to top
	jQuery("#symposium_gallery_top").live('click', function() {
     	symposium.album_id = 0;
		symposium_show_album();
	});

	// Up a level
	jQuery("#symposium_gallery_up").live('click', function() {
     	symposium.album_id = jQuery(this).attr("title");
		symposium_show_album();
	});
	
	// Function to show album (for above)	
	function symposium_show_album() {
				
     	jQuery.ajax({
      		url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
      		type: "POST",
      		data: ({
       			action:'menu_gallery',
     			album_id:symposium.album_id,
				uid1:symposium.current_user_page
      		}),
      		dataType: "html",
      		success: function(str){
     			jQuery('#profile_body').html(str);

				var user_login = jQuery("#symposium_user_login").html();
				var user_email = jQuery("#symposium_user_email").html();

          		jQuery('#menu_gallery_file_upload').uploadify({
				'uploader'  	: symposium.plugin_url+'uploadify/uploadify.swf',
				'buttonText'	: browseforfile,
				'script'    	: symposium.plugin_url+'upload_menu_gallery.php',
				'cancelImg' 	: symposium.plugin_url+'uploadify/cancel.png',
				'multi'      	: true,
				'auto'      	: true,
				'scriptData' 	: {'aid':symposium.album_id, 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
				'onError' 	: function(event, ID, fileObj, errorObj) {
							alert("Error: "+errorObj.type+" "+errorObj.info);
      						},
				'onComplete'	: function(event, queueID, fileObj, response, data) { 
						
							if (response.substring(0, 2) != 'OK') {
								alert(response); 
							}

						},
				'onAllComplete' : function(event,data) {
							jQuery("#dialog").html(data.filesUploaded + ' files uploaded successfully!');
							jQuery("#dialog").dialog({ title: 'File Upload', width: 600, height: 175, modal: true,
							buttons: {
									"OK": function() {
										jQuery("#dialog").dialog('close');
										window.location.href=symposium.plugin_url+'ajax/symposium_gallery_functions.php?href=redirect&num='+data.filesUploaded+'&aid='+symposium.album_id;
									}
								}
							});
    						}
			});

              		// Prepare ColorBox
	              	jQuery("a[rel='symposium_gallery_photos']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});


      		}
        });		
	}
		
	// Toggle new album form
	jQuery(".symposium_new_album_button").live('click', function() {
		jQuery("#gallery_options").hide();
		jQuery("#symposium_album_covers").hide();
		jQuery("#symposium_album_content").hide();
		jQuery("#symposium_create_gallery").show();
		
		if (symposium.album_id > 0) {
			jQuery(".symposium_create_sub_gallery").show();
		} else {
			jQuery(".symposium_create_sub_gallery").hide();
		} 
	});
	jQuery("#symposium_cancel_album").live('click', function() {
		jQuery("#gallery_options").show();
		jQuery("#symposium_album_covers").show();
		jQuery("#symposium_album_content").show();
		jQuery("#symposium_create_gallery").hide();
	});
	
	// Create new album
	jQuery("#symposium_new_album").live('click', function() {

		jQuery(".symposium_pleasewait").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
			type: "POST",
			data: ({
				action:'create_album',
				name:jQuery("#symposium_new_album_title").val(),
				sub_album:jQuery("#symposium_new_album").is(":checked"),
				parent:jQuery("#symposium_create_sub_gallery_select").attr("title")
			}),
			dataType: "html",
			success: function(str){
				var reload_page = symposium.profile_url+symposium.q.substring(0, 1)+"uid="+symposium.current_user_page+"&embed=on&album_id="+str;
				window.location.href=reload_page;
			}
		});		
	});
	
	// Delete album
	jQuery("#symposium_delete_album_button").live('click', function() {

        symposium.album_id = jQuery(this).attr("title");

		if (confirm("Are you sure?")) {

			jQuery(".symposium_pleasewait").inmiddle().show();

	      		jQuery.ajax({
	     			url: symposium.plugin_url+"ajax/symposium_gallery_functions.php", 
	     			type: "POST",
	     			data: ({
	      				action:'delete_album',
	           			album_id:symposium.album_id
	     			}),
	     			dataType: "html",
	     			success: function(str){
           			if (str != 'OK') {
					jQuery(".symposium_pleasewait").hide();
          				alert(str);
          			} else {
					var reload_page = symposium.profile_url+symposium.q.substring(0, 1)+"uid="+symposium.current_user_page;
					window.location.href=reload_page;
          			}
     			}
     			
     		});
     		
		};		
	});
	
});