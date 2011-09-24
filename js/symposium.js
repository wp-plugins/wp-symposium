jQuery(document).ready(function() { 	

	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          SHARED                                          |
	   +------------------------------------------------------------------------------------------+
	*/
	
	// Get translated strings
	var pleasewait = jQuery("#symposium_pleasewait").html();
	var saving = jQuery("#symposium_saving").html();
	var more = jQuery("#symposium_more").html();
	var browseforfile = jQuery("#symposium_browseforfile").html();
	var attachfile = jQuery("#symposium_attachfile").html();
	var whatsup = jQuery("#symposium_whatsup").html();
	if (jQuery("#symposium_areyousure").length) 					{ var areyousure = jQuery("#symposium_areyousure").html(); } else { var areyousure = 'Are you sure?'; };
	
	// Centre in screen
	jQuery.fn.inmiddle = function () {
		this.css("position","absolute");
		this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
		this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
		return this;
	}

    // Check if really want to delete	    
	jQuery(".delete").click(function(){
	  var answer = confirm(areyousure);
	  return answer // answer is a boolean
	});
	jQuery(".deletebutton").live('click', function() {
	  var answer = confirm(areyousure);
	  return answer // answer is a boolean
	});
	   

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        WIDGET: LOGIN                                     |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("#symposium-widget-login").length) {
		jQuery("#symposium_widget_login_button").click(function(){

			jQuery(".symposium_pleasewait").inmiddle().show();

			var show_form = '';
			if (jQuery("#symposium-widget-login").length) {
				show_form = 'on';
			}

			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
				type: "POST",
				data: ({
					action:"doLogin",
					username:jQuery("#symposium_widget_login").val(),
					password:jQuery("#symposium_widget_password").val(),
					show_form:show_form,
					login_url:jQuery("#symposium_widget_login_button").attr("title")
				}),
			    	dataType: "html",
				async: false,
				success: function(str){
					if (str.substring(0, 4) == 'FAIL') {
						jQuery(".symposium_pleasewait").hide();
						alert('Login Failed');
					} else {
						window.location.href=str;
					}
				},
				error: function(err){
					alert("V:"+err);
				}		
	   		});
		});
	}

	jQuery('#symposium_widget_password').keypress(function(event) {
		if (event.which == 13) {

			var show_form = '';
			if (jQuery("#symposium-widget-login").length) {
				show_form = 'on';
			}

			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
				type: "POST",
				data: ({
					action:"doLogin",
					username:jQuery("#symposium_widget_login").val(),
					password:jQuery("#symposium_widget_password").val(),
					show_form:show_form,
					login_url:jQuery("#symposium_widget_login_button").attr("title")
				}),
			    	dataType: "html",
				async: false,
				success: function(str){
					if (str.substring(0, 4) == 'FAIL') {
						jQuery(".symposium_pleasewait").hide();
						alert('Login Failed');
					} else {
						window.location.href=str;
					}
				},
				error: function(err){
					alert("V:"+err);
				}		
	   		});
		}
	})


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                     MEMBER DIRECTORY                                     |
	   +------------------------------------------------------------------------------------------+
	*/
	
	jQuery('#symposium_member').live('keydown', function(e) { 
	  var keyCode = e.keyCode || e.which; 
	
	  if (keyCode == 9 || keyCode == 27) { 
		jQuery('#symposium_member_list').hide();
	  } 

	});

	jQuery('#members_go_button').live('click', function () {
		symposium_do_member_search();
	});
	jQuery('#symposium_member').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			symposium_do_member_search();
		}
	});
	function symposium_do_member_search() {
		jQuery('#symposium_members').html("<br /><img src='"+symposium.plugin_url+"/images/busy.gif' />");

		var friends = '';
		if (jQuery("#symposium_member_friends").is(":checked")) {
			var friends = 'on';
		};

	 	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_members_functions.php", 
			type: "POST",
			data: ({
				action:"getMembers",
				friends:friends,
				term:jQuery('#symposium_member').val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery('#symposium_members').html(str);
			},
			error: function(err){
				//alert("getBox:"+err);
			}		
	  	});
	}
	
	if (jQuery("input#symposium_member_small").length) {
		
		jQuery("input#symposium_member_small").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_members_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#symposium_member_small" ).val( ui.item.label );
					jQuery( "input#uid" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#symposium_member_small" ).val( ui.item.label );
					jQuery( "input#uid" ).val( ui.item.value );
					jQuery(".symposium_pleasewait").inmiddle().show().delay(3000).fadeOut("slow");
					window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'uid='+jQuery("#uid").val();
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				var member = "<a>";
					member += "<div style='height:40px; overflow:hidden'>";
						member += "<div style=\'float:left; background-color:#fff; margin-right: 8px; width:40px; height:40px; \'>";	
						member += item.avatar;
						member += "</div>";			
						member += "<div>" + item.label + "<br />";
						member += item.city + item.country + "</div>";
						member += "<br style='clear:both' />";
					member += "</div>";
				member += "</a>";
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( member )
					.appendTo( ul );
			};
			
		   	jQuery("#symposium_small_members_button").click(function() {
				window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'uid='+jQuery("#uid").val();
				return false;
		   	});

	}		
   		
	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           MAIL                                           |
	   +------------------------------------------------------------------------------------------+
	*/

	// Go straight to compose form
	if (symposium.view == 'compose') {

		jQuery(".symposium_pleasewait").inmiddle().show();

		var mail_to = symposium.to;
		jQuery('#mail_recipient_list').val(mail_to);
		
		jQuery("#compose_form").show();
	  	jQuery("#mail_office").hide();

		jQuery('#compose_subject').focus();
		jQuery(".symposium_pleasewait").fadeOut("slow");
		
		symposium.view = 'in';

	};

	// Default load	
	if (jQuery("#compose_form").length && symposium.view != 'compose') {
		
	   	// Load box on first page load
		jQuery('#mailbox_list').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery('#messagebox').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

	 	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getBox",
				tray:"in",
				term:"",
				start: jQuery('#next_message_id').html(),
				length:5
			}),
		    dataType: "html",
			async: true,
			success: function(str){

				if (strpos(str, 'mail_mid')) {
					
					var html = "";
					
					var msg_count = 0;
			
					var template = symposium.template_mail_tray;
					template = template.replace(/&lt;/g, '<');
					template = template.replace(/&gt;/g, '>');
					template = template.replace(/\[\]/g, '');
			
					var rows = jQuery.parseJSON(str);
		            jQuery.each(rows, function(i,row){
			
						msg_count++;
						if (msg_count == 1) {
							jQuery('#next_message_id').html(row.next_message_id);
						} else {
	
							if (html == "") {

								// Check for default mail ID
								var mail_id = row.mail_mid;
								if (symposium.mail_id != '') {
									mail_id = symposium.mail_id;
								}
								
								// Show first message as default message
								jQuery.ajax({
									url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
									type: "POST",
									data: ({
										action:"getMailMessage",
										tray:"in",
										mid:mail_id
									}),
								    dataType: "html",
									async: true,
									success: function(str){
										var details = str.split("[split]");
										if (details[2] == "in") {
											jQuery("#"+details[0]).removeClass("row");
											jQuery("#"+details[0]).addClass("row_odd");
										}
										jQuery("#messagebox").html(details[3]);
										if (details[1] > 0) {
											jQuery("#in_unread").html('('+details[1]+')');
										} else {
											jQuery("#in_unread").html('');
										}
										jQuery(".symposium_pleasewait").fadeOut("slow");
									},
									error: function(err){
										//alert("getMailMessage:"+err);
									}		
						   		});
							}
				
							var new_item = template;
							new_item = new_item.replace(/mail_mid/, row.mail_mid);
							new_item = new_item.replace(/mail_read/, row.mail_read);
							new_item = new_item.replace(/\[mail_sent\]/, row.mail_sent);
							new_item = new_item.replace(/\[mail_from\]/, row.mail_from);
							new_item = new_item.replace(/\[mail_subject\]/, row.mail_subject);
							new_item = new_item.replace(/\[mail_message\]/, row.message);
							html += new_item;
							
						}

					});
					
					html += '<div id="show_more_mail" style="text-align:center; padding:6px; cursor:pointer;">'+more+'</div>';
					jQuery('#mailbox_list').html(html);
					
				} else {
					
					jQuery('#mailbox_list').html('');
					jQuery('#messagebox').html('');
					
				}

			},
			error: function(err){
				//alert("getBox:"+err);
			}		
	  	});	
		
	}

	// Clicked on More...
	jQuery("#show_more_mail").live('click', function() {
		
		var tray = 'in';
		if (jQuery("#sent").is(":checked")) {
			var tray = 'sent';
		};
		
		jQuery('#show_more_mail').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		var template = symposium.template_mail_tray;
		template = template.replace(/&lt;/g, '<');
		template = template.replace(/&gt;/g, '>');
		template = template.replace(/\[\]/g, '');
							
	 	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getBox",
				tray:tray,
				term:"",
				start:jQuery('#next_message_id').html()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				
				if (strpos(str, 'mail_mid')) {

					var msg_count = 0;
					var html = "";
					var rows = jQuery.parseJSON(str);
		            jQuery.each(rows, function(i,row){

						msg_count++;
						if (msg_count == 1) {
							jQuery('#next_message_id').html(row.next_message_id);
						} else {

							var new_item = template;
							new_item = new_item.replace(/mail_mid/, row.mail_mid);
							new_item = new_item.replace(/mail_read/, row.mail_read);
							new_item = new_item.replace(/\[mail_sent\]/, row.mail_sent);
							new_item = new_item.replace(/\[mail_from\]/, row.mail_from);
							new_item = new_item.replace(/\[mail_subject\]/, row.mail_subject);
							new_item = new_item.replace(/\[mail_message\]/, row.message);
							html += new_item;
							
						}
					
					});

					html += '<div id="show_more_mail" style="text-align:center; padding:6px; cursor:pointer;">'+more+'</div>';

					jQuery('#show_more_mail').remove();
					jQuery(html).appendTo('#mailbox_list').hide().slideDown("slow");
					
				} else {
					// No more...
					jQuery('#show_more_mail').remove();
				}
			},
			error: function(err){
				//alert("getBox:"+err);
			}		
	  	});

	});
		
	// Send
	jQuery("#mail_send_button").live('click', function() {
	
		var recipient_id = jQuery("#mail_recipient_list").val();
		
		jQuery("#compose_form").hide();
		jQuery('#mail_sent_message').show().html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
	  	jQuery("#mail_office").show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"sendMail",
				compose_recipient_id:recipient_id,
				compose_subject:jQuery('#compose_subject').val().replace(/(<([^>]+)>)/ig, ''),
				compose_text:jQuery('#compose_text').val().replace(/(<([^>]+)>)/ig, ''),
				compose_previous:jQuery('#compose_previous').val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#mail_sent_message").html(str);
				jQuery("#mail_sent_message").delay(1000).slideUp("slow");
			},
			error: function(err){
				//alert("sendMail:"+err);
			}		
   		});	
   		
   	});


	// Delete message
	jQuery(".message_delete").live('click', function() {
	
		if (confirm(areyousure)) {

			var tray = 'in';
			if (jQuery("#sent").is(":checked")) {
				var tray = 'sent';
			};
			
			var mail_id = jQuery(this).attr("id");

			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
				type: "POST",
				data: ({
					action:"deleteMail",
					mid:mail_id,
					tray:tray
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					jQuery("#messagebox").html(str);
					jQuery("#"+mail_id).slideUp("slow");
				},
				error: function(err){
					//alert("deleteMail:"+err);
				}		
	   		});
		
		}
			
	});
	
	// Reply
	jQuery(".message_reply").live('click', function() {

		var mail_id = jQuery(this).attr("title");
		var mail_from = jQuery(this).attr("id");
		
		jQuery('#mail_recipient_list').val(mail_from);
		jQuery('#compose_recipient_name').val('');
		jQuery('#compose_text').val('');
		jQuery('#compose_previous').val('');
		jQuery("#mail_sent_message").hide();
		
		jQuery("#compose_form").show();
	  	jQuery("#mail_office").hide();

		jQuery(".symposium_pleasewait").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getReply",
				mail_id:mail_id,
				recipient_id:mail_from
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				var detail = jQuery.parseJSON(str);
				jQuery("#compose_subject").val(detail[0].subject);
				jQuery("#compose_text").val(detail[0].message);

				jQuery(".symposium_pleasewait").fadeOut("slow");

			},
			error: function(err){
				//alert("getReply:"+err);
			}		
   		});

	});
	
   	// Search
	jQuery("#search_inbox_go").live('click', function() {
		symposium_do_mail_search();
   	});
	jQuery('#search_inbox').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			symposium_do_mail_search();
		}
	});

    function symposium_do_mail_search() {
  		var term = jQuery("#search_inbox").val();

		var tray = 'in';
		if (jQuery("#sent").is(":checked")) {
			var tray = 'sent';
		};

  		if(term != '') {
			jQuery('#mailbox_list').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
				type: "POST",
				data: ({
					action:"getBox",
					tray:tray,
					term:term
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					var html = "";
					var rows = jQuery.parseJSON(str);
					var first_mail_mid = '';
		            jQuery.each(rows, function(i,row){
						if (row.mail_mid != undefined) {
							if (first_mail_mid == '') { first_mail_mid = row.mail_mid; }
			            	html += "<div id='"+row.mail_mid+"' class='mail_item "+row.mail_read+"'>";
			            	html += "<div class='mail_item_age'>"+row.mail_sent+"</div>";
			            	html += "<strong>"+row.mail_from+"</strong><br />";
							html += "<span class='mailbox_message_subject'>"+row.mail_subject+"</span><br />";
							html += "<span class='mailbox_message'>"+row.message+"</span>";
							html += "</div>";
						}
					});
					jQuery('#mailbox_list').html(html);

					// Load first retrieved message
					if (first_mail_mid != '') {

						jQuery('#messagebox').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

						// Show first message as default message
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
							type: "POST",
							data: ({
								action:"getMailMessage",
								tray:"in",
								mid:first_mail_mid
							}),
						    dataType: "html",
							async: true,
							success: function(str){
								var details = str.split("[split]");
								jQuery("#messagebox").html(details[3]);
							},
							error: function(err){
								//alert("getMailMessage:"+err);
							}		
				   		});

					} else {

						jQuery("#messagebox").html('');
						
					}
					
				},
				error: function(err){
					//alert("getBox:"+err);
				}		
	   		});	  
  		}		
	}

	// Change tray
	jQuery(".mail_tray").live('click', function() {
		
		jQuery("#search_inbox").val('');

		var tray = 'in';
		if (jQuery("#sent").is(":checked")) {
			var tray = 'sent';
		};
		
		jQuery('#mailbox_list').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery('#messagebox').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		var template = symposium.template_mail_tray;
		template = template.replace(/&lt;/g, '<');
		template = template.replace(/&gt;/g, '>');
		template = template.replace(/\[\]/g, '');
							
	 	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getBox",
				tray:jQuery(this).attr("id"),
				term:""
			}),
		    dataType: "html",
			async: true,
			success: function(str){

				if (strpos(str, 'mail_mid')) {

					var msg_count = 0;
					var html = "";
					var rows = jQuery.parseJSON(str);
		            jQuery.each(rows, function(i,row){


						msg_count++;
						if (msg_count == 1) {
							jQuery('#next_message_id').html(row.next_message_id);
						} else {

							if (html == "") {
								// Show first message as default message
								jQuery.ajax({
									url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
									type: "POST",
									data: ({
										action:"getMailMessage",
										tray:tray,
										mid:row.mail_mid
									}),
								    dataType: "html",
									async: true,
									success: function(str){
										var details = str.split("[split]");
										if (details[2] == "in") {
											jQuery("#"+details[0]).removeClass("row");
											jQuery("#"+details[0]).addClass("row_odd");
											if (details[1] > 0) {
												jQuery("#in_unread").html('('+details[1]+')');
											} else {
												jQuery("#in_unread").html('');
												}
										}
										jQuery("#messagebox").html(details[3]);
										jQuery(".symposium_pleasewait").fadeOut("slow");
									},
									error: function(err){
										//alert("1:"+err);
									}		
						   		});
							}

							var new_item = template;
							new_item = new_item.replace(/mail_mid/, row.mail_mid);
							new_item = new_item.replace(/mail_read/, row.mail_read);
							new_item = new_item.replace(/\[mail_sent\]/, row.mail_sent);
							new_item = new_item.replace(/\[mail_from\]/, row.mail_from);
							new_item = new_item.replace(/\[mail_subject\]/, row.mail_subject);
							new_item = new_item.replace(/\[mail_message\]/, row.message);
							html += new_item;
							
						}
					
					});
					html += '<div id="show_more_mail" style="text-align:center; padding:6px; cursor:pointer;">'+more+'</div>';
					jQuery('#mailbox_list').html(html);
					
				} else {
					
					jQuery('#mailbox_list').html('');
					jQuery('#messagebox').html('');

				}
			},
			error: function(err){
				//alert("getBox:"+err);
			}		
	  	});
	
	
	});

	// React to click on message list
	jQuery(".mail_item").live('click', function() {
   		
		jQuery('#messagebox').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		var mail_mid = jQuery(this).attr("id");

		var tray = 'in';
		if (jQuery("#sent").is(":checked")) {
			var tray = 'sent';
		};
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getMailMessage",
				tray:tray,
				'mid':mail_mid
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				var details = str.split("[split]");
				if (details[2] == "in") {
					jQuery("#"+details[0]).removeClass("row");
					jQuery("#"+details[0]).addClass("row_odd");
				}
				jQuery("#messagebox").html(details[3]);
				if (details[1] > 0 && tray == 'in') {
					jQuery("#in_unread").html('('+details[1]+')');
				} else {
					jQuery("#in_unread").html('');
				}
				jQuery(".symposium_pleasewait").fadeOut("slow");
			},
			error: function(err){
				//alert("1:"+err);
			}		
   		});	   		
   		
   	});	


	// Compose
	jQuery("#compose_button").live('click', function() {
		jQuery('#mail_recipient_id').html('');
		jQuery('#compose_recipient_name').val('');
		jQuery('#compose_subject').val('');
		jQuery('#compose_text').val('');
		jQuery('#compose_previous').val('');
		jQuery("#mail_sent_message").hide();
		jQuery("#compose_form").show();
	  	jQuery("#mail_office").hide();
	});
	jQuery("#mail_cancel_button").live('click', function() {
		jQuery("#compose_form").hide();
	  	jQuery("#mail_office").show();
	});

		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                         PROFILE                                          |
	   +------------------------------------------------------------------------------------------+
	*/
		
	// Act on "view" parameter on first page load
	if ( (jQuery("#profile_body").length) && (symposium.embed != 'on') ) {
		
			var menu_id = 'menu_'+symposium.view;
				
			if (menu_id == 'menu_in') { menu_id = 'menu_friends'; }
			if (jQuery('#force_profile_page').length) {
				menu_id = 'menu_'+jQuery('#force_profile_page').html();
			}

			if ( (menu_id == 'menu_extended') || 
	      		(menu_id == 'menu_wall') || 
	      		(menu_id == 'menu_activity') || 
	      		(menu_id == 'menu_all') || 
	      		(menu_id == 'menu_groups') || 
	      		(menu_id == 'menu_friends') || 
	      		(menu_id == 'menu_avatar') || 
	      		(menu_id == 'menu_personal') || 
	      		(menu_id == 'menu_settings') ) {
	      	
	            var ajax_path = symposium.plugin_url+"ajax/symposium_profile_functions.php";
	             	
	      	} else {
	      	
	            var ajax_part = menu_id.replace(/menu_/g, "");
	            var ajax_path = symposium.plugin_pro_url+ajax_part+"/ajax/symposium_"+ajax_part+"_functions.php";
	      	
	      	}

			jQuery.ajax({
				url: ajax_path, 
				type: "POST",
				data: ({
					action:menu_id,
					post:symposium.post,
					limit_from:0,
					uid1:symposium.current_user_page
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					jQuery('#profile_body').html(str);
					
					var user_id = jQuery("#symposium_user_id").html();
					var user_login = jQuery("#symposium_user_login").html();
					var user_email = jQuery("#symposium_user_email").html();

					jQuery('#profile_file_upload').uploadify({
					    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
						'buttonText': browseforfile,
					    'script'    : symposium.plugin_url+'uploadify/upload_profile_avatar.php', 
						'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
					    'cancelImg' : symposium.plugin_url+'uploadify/uploadify-cancel.png',
						'auto'      : true,
						'scriptData' : {'user_login':user_login, 'user_email':user_email, 'user_id':user_id, 'page':symposium.current_user_page}, 
						'onError' 	: function(event, ID, fileObj, errorObj) {
										 alert("Error: "+errorObj.type+" "+errorObj.info);
	      							  },
	      				'onComplete': function(event, queueID, fileObj, response, data) { 

											if (response.substring(0, 5) == 'Error') {
												alert(response); 
											} else {
												jQuery('#profile_image_to_crop').html(response);
					
												jQuery('#profile_jcrop_target').Jcrop({
													onChange: showProfilePreview,
													onSelect: showProfilePreview
												});
											}				
									  }
					
				   	});

				}
	   		});
   		
	}

	// Remove avatar
	jQuery("#symposium_remove_avatar").live('click', function() {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:'remove_avatar',
				uid:symposium.current_user_page
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				window.location.href=window.location.href;
			}
   		});
	});
		
	// Poke
	jQuery(".poke-button").live('click', function() {

		jQuery("#dialog").html('Message sent!');
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 175, modal: true,
		buttons: {
				"OK": function() {
					jQuery("#dialog").dialog('close');
				}
			}
		});

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:'send_poke',
				recipient:symposium.current_user_page
			}),
		    dataType: "html",
			async: false,
			success: function(str){
			}
   		});
	});
	
	// Setup for Facebook
	jQuery("#setup_facebook").live('click', function() {
		var str = '<br /><input type="text" id="facebook_id" style="width:200px; height:19px; float:left;" />';
		str += '<input type="submit" id="facebook_id_submit" value="OK" class="symposium-button" style="width:50px; height:25px; margin-left: 3px;" />';
		str += '<p>To find out your ID <a target="_blank" href="http://apps.facebook.com/whatismyid/">click here</a>.</p>';
		
		jQuery("#dialog").html(str);
		jQuery("#dialog").dialog({ title: 'Please enter your Facebook ID', width: 420, height: 200, modal: true, buttons: {} });
	});
	jQuery("#facebook_id_submit").live('click', function() {		

		jQuery(".symposium_pleasewait").inmiddle().show();
		if (jQuery('#facebook_id').val() != '') {
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:'facebook_id',
					facebook_id:jQuery('#facebook_id').val()
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					if (str != "") {
						if (str == "OK") {
							window.location.href=symposium.profile_url;
						} else {
							if (str.substring(0, 24) == 'https://www.facebook.com') {
								window.location.href=str;
							} else {
								jQuery("#dialog").dialog('close');
								jQuery(".symposium_pleasewait").hide();
								jQuery("#dialog").html(str);
								jQuery("#dialog").dialog({ title: 'Facebook Connect Error', width: 600, height: 400, modal: true,
								buttons: {
										"OK": function() {
											jQuery(".symposium_pleasewait").inmiddle().show();
											window.location.href=symposium.profile_url;
										}
									}
								});
							}
						}
					}
				}
	   		});
		}
	});
	jQuery("#cancel_facebook").live('click', function() {		
		jQuery("#facebook_div").hide();
		jQuery(".symposium_pleasewait").inmiddle().show();
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:'facebook_id',
				facebook_id:''
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				jQuery(".symposium_pleasewait").hide();

				jQuery("#dialog").html("You may want to <a href='http://www.facebook.com/settings/?tab=applications' target='_blank'>remove access</a> on your Facebook account");

				jQuery("#dialog").dialog({ title: 'Facebook Connect Removed', width: 400, height: 220, modal: true,
				buttons: {
						"OK": function() {
							jQuery(this).dialog("close");
						}
					}
				});
			}
   		});
	});
		
	// Clicked on show more...
	jQuery(".showmore_wall").live('click', function() {
		
		var limit_from = jQuery(this).attr("title");
		jQuery(this).html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		var menu_id = 'menu_'+jQuery(this).attr("id");
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:menu_id,
				post:'',
				limit_from:limit_from,
				uid1:symposium.current_user_page
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery('.showmore_wall').remove();
				jQuery(str).appendTo('#profile_body').hide().slideDown("slow");
			}
   		});		

	});
	
	// Menu choices
	jQuery(".symposium_profile_menu").click(function(){
				
		var menu_id = jQuery(this).attr("id");
		jQuery('#profile_body').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		if (!(jQuery("#profile_body").length)) {
			var view = menu_id.replace(/menu_/g, "");
			window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'view='+view;
		}
		
		if ( (menu_id == 'menu_extended') || 
      		(menu_id == 'menu_wall') || 
      		(menu_id == 'menu_activity') || 
      		(menu_id == 'menu_all') || 
      		(menu_id == 'menu_groups') || 
      		(menu_id == 'menu_friends') || 
      		(menu_id == 'menu_avatar') || 
      		(menu_id == 'menu_personal') || 
      		(menu_id == 'menu_settings') ) {
      	
            var ajax_path = symposium.plugin_url+"ajax/symposium_profile_functions.php";
             	
      	} else {
      	
            var ajax_part = menu_id.replace(/menu_/g, "");
            var ajax_path = symposium.plugin_pro_url+ajax_part+"/ajax/symposium_"+ajax_part+"_functions.php";
      	
      	}
      	      	
		jQuery.ajax({
			url: ajax_path, 
			type: "POST",
			data: ({
				action:menu_id,
				post:'',
				limit_from:0,
				uid1:symposium.current_user_page
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				
				jQuery('#profile_body').hide().html(str).fadeIn("slow");

				var user_id = jQuery("#symposium_user_id").html();
				var user_login = jQuery("#symposium_user_login").html();
				var user_email = jQuery("#symposium_user_email").html();
				
				jQuery('#profile_file_upload').uploadify({
				    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
					'buttonText': browseforfile,
				    'script'    : symposium.plugin_url+'uploadify/upload_profile_avatar.php',
					'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
				    'cancelImg' : symposium.plugin_url+'uploadify/cancel.png',
				    'auto'      : true,
					'scriptData' : {'user_login':user_login, 'user_email':user_email, 'user_id':user_id, 'page':symposium.current_user_page}, 
					'onError' 	: function(event, ID, fileObj, errorObj) {
									 alert("Error: "+errorObj.type+" "+errorObj.info);
      							  },
					'onComplete': function(event, queueID, fileObj, response, data) { 
						
						if (response.substring(0, 5) == 'Error') {
							alert(response); 
						} else {
							
							if (trim(response) == 'no-crop') {
								location.reload();
							} else {

								jQuery('#profile_image_to_crop').html(response);
	
								jQuery('#profile_jcrop_target').Jcrop({
									onChange: showProfilePreview,
									onSelect: showProfilePreview
								});
							}
						}

					}
			   	});
			}
   		});	

	});

	if (jQuery("#profile_jcrop_target").length) {
		jQuery('#profile_jcrop_target').Jcrop({
			onChange: showPreview,
			onSelect: showPreview
		});
	}

	function showProfilePreview(coords)
	{
		var rx = 100 / coords.w;
		var ry = 100 / coords.h;

		jQuery('#x').val(coords.x);
		jQuery('#y').val(coords.y);
		jQuery('#x2').val(coords.x2);
		jQuery('#y2').val(coords.y2);
		jQuery('#w').val(coords.w);
		jQuery('#h').val(coords.h);
			
		jQuery('#profile_preview').css({
			width: Math.round(rx * jQuery('#profile_jcrop_target').width()) + 'px',
			height: Math.round(ry * jQuery('#profile_jcrop_target').height()) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
	};

	// Save profile avatar
	jQuery("#saveProfileAvatar").live('click', function() {
		
		if (jQuery("#w").val() > 0) {
			
			jQuery(".symposium_notice").inmiddle().show();
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"saveProfileAvatar",
					uid:symposium.current_user_page,
					x:jQuery("#x").val(),
					y:jQuery("#y").val(),
					w:jQuery("#w").val(),
					h:jQuery("#h").val()
					}),
			    dataType: "html",
				async: true,
				success: function(str){
					if (trim(str) == 'reload') {
						location.reload();
					} else {
						jQuery(".symposium_notice").fadeOut("slow");
						alert('Oops: '+str);
					}
				},
				error: function(err){
					//alert("saveProfileAvatar:"+err);
				}		
	   		});
	   		
		} else {
			alert('Please select an area in your uploaded image');
		}
   			
   	});		

	// Show delete link on wall post hover
	jQuery('.wall_post_div').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
			jQuery(this).find(".delete_post_top").show();
	  } else {
        	jQuery(this).find(".delete_post_top").hide();
	  }
	});
    
	// Show delete link on reply hover
	jQuery('.wall_reply').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
	        jQuery(this).find(".delete_reply").show();
	  } else {
	        jQuery(this).find(".delete_reply").hide();
	  }
	});

	// View all comments
	jQuery(".view_all_comments").live('click', function() {
		var parent_comment_id = jQuery(this).attr("title");
        jQuery('#'+parent_comment_id).find(".reply_div").slideDown("slow");
	});
		
	// Delete a reply
	jQuery(".delete_post").live('click', function() {
		
		if (confirm(areyousure)) {

			jQuery(".symposium_pleasewait").inmiddle().show();
	
			var comment_id = jQuery(this).attr("title");
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"deletePost",
					cid:comment_id
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					if (str.substring(0, 4) != 'FAIL') { 
						jQuery(str).slideUp("slow");
					} else {
						//alert("P2:"+str);
					}
				},
				error: function(err){
					//alert("P1:"+err);
				}		
	   		});		
			
			jQuery(".symposium_pleasewait").fadeOut("slow");
			
		}
	});

	// new status (ie. post to your own wall)
	jQuery("#symposium_add_update").live('click', function() {
		symposium_add_update();
	});
	jQuery('#symposium_status').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			symposium_add_update();
		}
	});

	function symposium_add_update() {

		var comment_text = jQuery("#symposium_status").val().replace(/(<([^>]+)>)/ig, '');
		
		if (comment_text != '') {
		
			var comment = "<div class='add_wall_post_div'>";
			comment = comment + "<div class='add_wall_post'>";
			comment = comment + "<div class='add_wall_post_text'>";
			comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
			comment = comment + symposium.current_user_display_name+'</a><br />';
			comment = comment + comment_text;
			comment = comment + "</div>";
			comment = comment + "</div>";			
			comment = comment + "<div class='add_wall_post_avatar'>";
			comment = comment + "<img src='"+jQuery('#symposium_current_user_avatar img:first').attr('src')+"' style='width:64px; height:64px' />";
			comment = comment + "</div>";	
			comment = comment + "</div>";
			
			var facebook_post = 0;
			if (jQuery("#post_to_facebook").length) {
				if (jQuery("#post_to_facebook").is(":checked")) {
					facebook_post = 1;
				}
			}
	
			jQuery("#symposium_status").val('');
			jQuery(comment).prependTo('#symposium_wall');
						
			// Update status
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"addStatus",
					text:comment_text,
					facebook:facebook_post
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					if (str != '' && str != 'Array') {
						jQuery("#dialog").html(str);
						jQuery("#dialog").dialog({ title: 'Alert', width: 400, height: 400, modal: true, buttons: {}  });
					}
					jQuery(".symposium_notice").fadeOut("slow");
				}
	   		});
	
			// If not on the profile page
			if (!(jQuery("#symposium_wall").length)) {
				jQuery(".symposium_pleasewait").inmiddle().show();
				window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'view=wall';
			}
		}
   	}		
	
	// new post on another members wall
	jQuery("#symposium_add_comment").live('click', function() {
		symposium_add_comment_to_profile();
	});
	jQuery('#symposium_comment').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			symposium_add_comment_to_profile();
		}
	});

	function symposium_add_comment_to_profile() {

		var comment_text = jQuery("#symposium_comment").val().replace(/(<([^>]+)>)/ig, '');

		if (comment_text != '') {
		
			var comment = "<div class='add_wall_post_div' style='"
			if (symposium.row_border_size != '') { 
				comment = comment + " border-top:"+symposium.row_border_size+"px "+symposium.row_border_style+" "+symposium.text_color_2+";"; 
			}
			comment = comment + "'>";
			comment = comment + "<div class='add_wall_post'>";
			comment = comment + "<div class='add_wall_post_text'>";
			comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
			comment = comment + symposium.current_user_display_name+'</a><br />';
			comment = comment + comment_text;
			comment = comment + "</div>";
			comment = comment + "</div>";			
			comment = comment + "<div class='add_wall_post_avatar'>";
			comment = comment + "<img src='"+jQuery('#symposium_current_user_avatar img:first').attr('src')+"' style='width:64px; height:64px' />";
			comment = comment + "</div>";	
	
			jQuery("#symposium_comment").val('');
			jQuery(comment).prependTo('#symposium_wall');
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"addStatus",
					subject_uid:symposium.current_user_page,
					parent:0,
					text:comment_text,
					facebook:0
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					if (str != '') {
						alert(str);
					}
					jQuery(".symposium_notice").fadeOut("slow");
				}
	   		});
		}
   	}

	// new reply/comment
	jQuery(".symposium_add_reply").live('click', function() {
		symposium_add_comment(this);
	});
	jQuery('.symposium_reply').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			symposium_add_comment(this);
		}
	});

	function symposium_add_comment(comment_trigger) {

		var comment_id = jQuery(comment_trigger).attr("title");
		var author_id = jQuery('#symposium_author_'+comment_id).val();
		var comment_text = jQuery("#symposium_reply_"+comment_id).val().replace(/(<([^>]+)>)/ig, '');

		if (comment_text != '') {
		
			var comment = "<div class='reply_div'>";
			comment = comment + "<div class='wall_reply_div'";
			if (symposium.bg_color_2 != '') { comment = comment + " style='background-color:"+symposium.bg_color_2+"'"; }
			comment = comment + ">";
			comment = comment + "<div class='wall_reply'>";
			comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
			comment = comment + symposium.current_user_display_name+'</a><br />';
			comment = comment + comment_text;
			comment = comment + "</div>";
			comment = comment + "</div>";			
			comment = comment + "<div class='wall_reply_avatar'>";
			comment = comment + "<img src='"+jQuery('#symposium_current_user_avatar img:first').attr('src')+"' style='width:40px; height:40px' />";
			comment = comment + "</div>";	
			comment = comment + "</div>";
	
			jQuery(comment).appendTo('#symposium_comment_'+comment_id);
			jQuery("#symposium_reply_"+comment_id).val('');
	
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"addComment",
					uid:author_id,
					parent:comment_id,
					text:comment_text
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					if (str != '') {
						alert(str);
					}
				}
	   		});
		}   			
   	}
	
	// update settings
	jQuery("#updateSettingsButton").live('click', function() {
		
		var display_name = jQuery("#display_name").val().replace(/(<([^>]+)>)/ig, '');
		var user_email = jQuery("#user_email").val().replace(/(<([^>]+)>)/ig, '');
		var signature = jQuery("#signature").val().replace(/(<([^>]+)>)/ig, '');
		
		if (signature.length > 128) {
			jQuery("#dialog").html('Maximum length for signatures is 128 characters.');
			jQuery("#dialog").dialog({ title: 'Signature', width: 600, height: 225, modal: true,
			buttons: { "OK": function() { jQuery("#dialog").dialog('close'); } }
			});							
		}
		
		if (display_name == '') {
			jQuery("#display_name").effect("highlight", {}, 4000);
		} else {

			if (user_email == '') {
				jQuery("#user_email").effect("highlight", {}, 4000);
			} else {

				jQuery(".symposium_notice").inmiddle().show();
		
				if (jQuery("#notify_new_messages").is(":checked")) {
					var notify_new_messages = 'on';
				} else {
					var notify_new_messages = '';
				}
		
				if (jQuery("#notify_new_wall").is(":checked")) {
					var notify_new_wall = 'on';
				} else {
					var notify_new_wall = '';
				}

				if (jQuery("#forum_all").is(":checked")) {
					var forum_all = 'on';
				} else {
					var forum_all = '';
				}

				if (jQuery("#trusted").length) {
					if (jQuery("#trusted").is(":checked")) {
						var trusted = 'on';
					} else {
						var trusted = '';
					}
				} else {
					var trusted = jQuery("#trusted_hidden").val();			
				}
		
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
					type: "POST",
					data: ({
						action:"updateSettings",
						uid:symposium.current_user_page,
						trusted:trusted,
						bar_position:jQuery("#bar_position").val(),
						display_name:display_name,
						user_email:user_email,
						signature:signature,
						notify_new_messages:notify_new_messages,
						notify_new_wall:notify_new_wall,
						forum_all:forum_all,
						xyz1:jQuery("#xyz1").val(),
						xyz2:jQuery("#xyz2").val()
					}),
				    dataType: "html",
					async: true,
					success: function(str){
						if (str == 'PASSWORD CHANGED') {
							/* when password changes, have to log in again, can't work out why */
							window.location.href=window.location.href;
						}
						if (str != "OK") {
							alert(str);
						}
						jQuery(".symposium_notice").fadeOut("slow");
					},
					error: function(err){
						//alert("updateSettings:"+err);
					}		
		   		});
			}
		}
   			
   	});		

	// update personal
	jQuery("#updatePersonalButton").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		var extended = '';

		jQuery('.eid_value').each(function(index) {
			var title = jQuery(this).attr("title");
			var value = jQuery(this).val().replace(/(<([^>]+)>)/ig, '');
			if (value == 'on') { value = jQuery(this).is(":checked"); }
			if (value == true) { value = 'on'; }
			if (value == false) { value = ''; }
		    extended += title + '[]';
		    extended += value + '[|]';
		});

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"updatePersonal",
				uid:symposium.current_user_page,
				dob_day:jQuery("#dob_day").val(),
				dob_month:jQuery("#dob_month").val(),
				dob_year:jQuery("#dob_year").val(),
				city:jQuery("#city").val().replace(/(<([^>]+)>)/ig, ''),
				country:jQuery("#country").val().replace(/(<([^>]+)>)/ig, ''),
				share:jQuery("#share").val(),
				wall_share:jQuery("#wall_share").val(),
				rss_share:jQuery("#rss_share").val(),
				extended:extended
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("updatePersonal:"+err);
			}		
   		});
   			
   	});					
	
	// delete a friend
	jQuery(".frienddelete").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"deleteFriend",
				friend:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'NOT LOGGED IN') {
					jQuery("#friend_"+str).slideUp("slow");			
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("P6:"+err);
			}		
   		});
   			
   	});		
	
	// send mail (via send mail button)
	jQuery("#profile_send_mail_button").live('mousedown', function() {
		document.location = symposium.mail_url+symposium.q+'view=compose&to='+symposium.current_user_page;
	});

	// add a friend request
	jQuery("#addasfriend").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"addFriend",
				friend_to:jQuery(this).attr("title"),
				friend_message:jQuery('#addfriend').val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'NOT LOGGED IN') {
					jQuery("#addasfriend_done1").hide();
					jQuery("#addasfriend_done2").delay(1000).slideDown("fast");
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("P6:"+err);
			}		
   		});
   			
   	});			

	// cancel a friend request
	jQuery("#cancelfriendrequest").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"cancelFriend",
				friend_to:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'NOT LOGGED IN') {
					jQuery("#cancelfriendrequest").hide();
					jQuery("#cancelfriendrequest_done").delay(1000).slideDown("fast");
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("P7:"+err);
			}		
   		});
   			
   	});			

	// reject a friend request
	jQuery("#rejectfriendrequest").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"rejectFriend",
				friend_to:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'NOT LOGGED IN') {
					jQuery("#request_"+str).slideUp("slow");
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("P8:"+err);
			}		
   		});
   			
   	});			

	// accept a friend request
	jQuery("#acceptfriendrequest").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"acceptFriend",
				friend_to:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'NOT LOGGED IN') {
					jQuery("#request_"+str).slideUp("slow");
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("acceptfriendrequest:"+err);
			}		
   		});
   			
   	});			

	// clear all current subscriptions
	jQuery("#symposium_clear_all_subs").live('click', function() {

		if (confirm(areyousure)) {

			jQuery("#dialog").html('All subscriptions cleared.');
			jQuery("#dialog").dialog({ title: 'Preferences', width: 600, height: 225, modal: true,
			buttons: {
					"OK": function() {
						jQuery("#dialog").dialog('close');
					}
				}
			});							
	
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"clearSubs"
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					if (str != 'OK') { alert(str); }
				},
				error: function(err){
				}		
	   		});		
		}
		
	});

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          FORUM                                           |
	   +------------------------------------------------------------------------------------------+
	*/


	// If using AJAX, set up Forum deep linking
	if (symposium.forum_ajax == 'on') {

		jQuery(window).bind( 'hashchange', function(e) { 
			
			var tmp_loc = window.location.href.replace(/\//g, '').toLowerCase();
			var tmp_url = symposium.forum_url.replace(/\//g, '').toLowerCase();
			if (strpos(tmp_loc, tmp_url) !== false) {
				var hash = window.location.hash.replace(/#/g, '');		
				if (hash == '') {
					if(tmp_loc == tmp_url) {
						getForum(0);
					} else {
						var params = tmp_loc.split('?');
						var pieces = params[1].split('&');
						var goto_cid = false;
						var cid = 0;
						var goto_tid = false;
						for (var num=0;   num<pieces.length;   num++)
						{	
							var piece=pieces[num].split('=');
							if (piece[0] == 'cid') {
								goto_cid = true;
								cid = piece[1];
							}
							if (piece[0] == 'tid' || piece[0] == 'show') {
								goto_tid = true;
								var tid = piece[1];
							}
				
						}
						if (goto_tid == true) {
							getTopic(tid);
						} else {
							if (jQuery("#new-topic").length == 0 && goto_cid == false && goto_tid == false) {
								getForum(symposium.cat_id);
							}
							if (goto_cid == true && goto_tid == false) {
								getForum(cid);
							}
						}
					}
				} else {
					var pieces=hash.split(',');
					var goto_cid = false;
					var cid = 0;
					var goto_tid = false;
					for (var num=0;   num<pieces.length;   num++)
					{	
						var piece=pieces[num].split('=');
						if (piece[0] == 'cid') {
							if (symposium.cat_id != piece[1]) {
								goto_cid = true;
								cid = piece[1];
							}
						}
						if (piece[0] == 'tid') {
							goto_tid = true;
							var tid = piece[1];
						}
			
					}
					if (goto_tid == true) {
						getTopic(tid);
					} else {
						if (jQuery("#new-topic").length == 0 && goto_cid == false && goto_tid == false) {
							getForum(symposium.cat_id);
						}
						if (goto_cid == true && goto_tid == false) {
							getForum(cid);
						}
					}
				}
			}
		});	
		
	}

	// On page load, get forum top level, but first check for deep linking	
	if (jQuery("#symposium-forum-div").length) {

		var sub = "getForum";
		if (symposium.show_tid > 0) {
			var sub = "getTopic";
		}

		var hash = window.location.hash.replace(/#/g, '');		
		if (hash != '') {
			
			var pieces=hash.split(',');
			var goto_cid = false;
			var goto_tid = false;
			for (var num=0;   num<pieces.length;   num++)
			{	
				var piece=pieces[num].split('=');
				if (piece[0] == 'cid') {
					symposium.cat_id = piece[1];
					goto_cid = true;
				}
				if (piece[0] == 'tid') {
					goto_tid = true;
					symposium.show_tid = piece[1];
				}
	
			}
			if (goto_cid == true && goto_tid == false) {
				sub = "getForum";
			}			
			if (goto_tid == true) {
				sub = "getTopic";
			}			
		}
	
		jQuery(".symposium_pleasewait").inmiddle().show();


		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:sub,
				limit_from:0,
				cat_id:symposium.cat_id,
				topic_id:symposium.show_tid,
				group_id:symposium.current_group 
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'DONTSHOW') {
					str = trim(str);
					jQuery("#symposium-forum-div").html(str);
	
					var user_login = jQuery("#symposium_user_login").html();
					var user_email = jQuery("#symposium_user_email").html();
										
					// Enable file uploading
					jQuery('#forum_file_upload').uploadify({
					    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
						'buttonText': attachfile,
					    'script'    : symposium.plugin_url+'uploadify/upload_forum_file.php', 
						'fileExt'   : symposium.permitted_ext,
					    'cancelImg' : symposium.plugin_url+'uploadify/cancel.png',
					    'auto'      : true,
						'scriptData' : {'tid':symposium.show_tid, 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
						'onError' 	: function(event, ID, fileObj, errorObj) {
										 	alert("Error: "+errorObj.type+" "+errorObj.info);
	      							  },
	      				'onComplete': function(event, queueID, fileObj, response, data) { 
											jQuery('#forum_file_list').html(response);
									  }
					
				   	});
	
					if (typeof colorbox == 'object') {
	    	      		jQuery("a[rel='symposium_forum_images-"+symposium.show_tid+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});						
						jQuery('.jwplayer').each(function(i, obj){
							var title = jQuery(this).attr("rel");
							jQuery(this).colorbox({width:"75%", height:"75%", scalePhotos:false, scrolling:false, inline:true, href:'#'+title});
						});
					}
	
					jQuery(".symposium_pleasewait").fadeOut("slow");
					
					// Set up auto-expanding textboxes
					if (jQuery(".elastic").length) {	
						jQuery('.elastic').elastic();
					}
				}
									
			},
			error: function(err){
				//alert("getForum:"+err);
			}		
   		});
		
	}

	
	// Answer accepted
	jQuery(".forum_post_answer").live('click', function() {
		var tid = jQuery(this).attr("id");

		jQuery(this).text("");
		jQuery("<img id='symposium_tmp' src='"+symposium.plugin_url+"/images/busy.gif' />").prependTo(this);

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"acceptAnswer",
				tid:tid
			}),
		    dataType: "html",
			async: true,
			success: function(str){	
				var row = jQuery.parseJSON(str)[0];
				jQuery("#dialog").html(row.message);
				jQuery("#dialog").dialog({ title: row.title, width: 600, height: 225, modal: true,
				buttons: {
						"OK": function() {
							jQuery("#dialog").dialog('close');
						}
					}
				});							
			}
   		});

		jQuery('.forum_post_answer').hide();
		jQuery('#symposium_accepted_answer').hide();
		jQuery("#symposium_tmp").hide();
		jQuery(this).show();
		jQuery("<img id='symposium_tmp' src='"+symposium.plugin_url+"/images/tick.png' />").prependTo(this);
   		
	});	

	// Remove uploaded image
	jQuery(".remove_forum_image").live('click', function() {
		
		var folder = jQuery(this).attr("id");
		var file = jQuery(this).attr("title");
		var me = this;
		jQuery(this).attr('src', symposium.plugin_url+"/images/busy.gif");
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"removeUploadedImage",
				folder:folder,
				file:file 
			}),
		    dataType: "html",
			async: true,
			success: function(str){	
				if (str == 'OK') {
					jQuery(me).parent().hide();
				} else {
					alert(str);
				}
			}
   		});
	});
	
	// Share permalink
	jQuery("#share_permalink").live('click', function() {
		var str = 'Copy and Paste the following:';
		str += '<br /><input type="text" style="width:550px;" value="'+jQuery(this).attr("title")+'" />';
		jQuery("#dialog").html(str);
		jQuery("#dialog").dialog({ title: 'Forum Permalink', width: 650, height: 150, modal: true, buttons: {}  });
	});
	
	// Clicked on show more...
	jQuery("#showmore_forum").live('click', function() {
		
		var details = jQuery(this).attr("title").split(",");
		limit_from = details[0];
		cat_id = details[1];
		
		jQuery('#showmore_forum').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getForum",
				limit_from:limit_from,
				cat_id:cat_id,
				group_id:symposium.current_group 
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery('#showmore_forum').remove();
				jQuery(str).appendTo('#symposium_table').hide().slideDown("slow");
			}
   		});		

	});
		
	// Click on category title to drill down
	jQuery(".category_title").live('click', function() { getForum(jQuery(this).attr("title")); });
	function getForum(id) {
		symposium.cat_id = id;
		
		jQuery(".symposium_pleasewait").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getForum",
				cat_id:id,
				group_id:symposium.current_group 
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				str = trim(str);
				if (jQuery("#symposium-forum-div").length) {
					jQuery("#symposium-forum-div").html(str);
				} else {
					jQuery("#group_body").html(str);
				}

				var user_login = jQuery("#symposium_user_login").html();
				var user_email = jQuery("#symposium_user_email").html();
				
				// Enable file uploading
				jQuery('#forum_file_upload').uploadify({
				    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
					'buttonText': attachfile,
				    'script'    : symposium.plugin_url+'uploadify/upload_forum_file.php', 
					'fileExt'   : symposium.permitted_ext,
				    'cancelImg' : symposium.plugin_url+'uploadify/cancel.png',
				    'auto'      : true,
					'scriptData' : {'tid':'0', 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
					'onError' 	: function(event, ID, fileObj, errorObj) {
									 	alert("Error: "+errorObj.type+" "+errorObj.info);
      							  },
      				'onComplete': function(event, queueID, fileObj, response, data) { 
										jQuery('#forum_file_list').html(response);
								  }
				
			   	});
				window.scrollTo(0,0);
				jQuery(".symposium_pleasewait").fadeOut("slow");
			
				// Set up auto-expanding textboxes
				if (jQuery(".elastic").length) {	
					jQuery('.elastic').elastic();
				}
									
			},
			error: function(err){
				//alert("getForum:"+err);
			}		
   		});
		
	}
	
	// Click on topic subject title
	jQuery(".topic_subject").live('click', function() { getTopic(jQuery(this).attr("title")); });
	function getTopic(id) {
		jQuery(".symposium_pleasewait").inmiddle().show();
		
		var topic_id = id;
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getTopic",
				topic_id:topic_id,
				group_id:symposium.current_group 
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				str = trim(str);
				if (jQuery("#symposium-forum-div").length) {
					jQuery("#symposium-forum-div").html(str);
				} else {
					jQuery("#group_body").html(str);
				}

				var user_login = jQuery("#symposium_user_login").html();
				var user_email = jQuery("#symposium_user_email").html();

				// Enable file uploading
				jQuery('#forum_file_upload').uploadify({
				    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
					'buttonText': attachfile,
				    'script'    : symposium.plugin_url+'uploadify/upload_forum_file.php', 
					'fileExt'   : symposium.permitted_ext,
				    'cancelImg' : symposium.plugin_url+'uploadify/cancel.png',
				    'auto'      : true,
					'scriptData' : {'tid':topic_id, 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
					'onError' 	: function(event, ID, fileObj, errorObj) {
									 	alert("Error: "+errorObj.type+" "+errorObj.info);
      							  },
      				'onComplete': function(event, queueID, fileObj, response, data) { 
										jQuery('#forum_file_list').html(response);
								  }
				
			   	});

				if (typeof colorbox == 'object') {
    	      		jQuery("a[rel='symposium_forum_images-"+topic_id+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});						
				}
				window.scrollTo(0,0);
				jQuery(".symposium_pleasewait").fadeOut("slow");

				// Set up auto-expanding textboxes
				if (jQuery(".elastic").length) {	
					jQuery('.elastic').elastic();
				}
									
			},
			error: function(err){
				//alert("getTopic:"+err);
			}		
   		});
	}
	
	// Fav Icon
	jQuery("#fav_link").live('click', function() {
   		
		if (jQuery('#fav_link').attr('src') == symposium.plugin_url+'images/fav-on.gif' ) {
			jQuery('#fav_link').attr({ src: symposium.plugin_url+'images/fav-off.png' });
		} else {
			jQuery('#fav_link').attr({ src: symposium.plugin_url+'images/fav-on.gif' });
		}
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"toggleFav",
				tid:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true
   		});

   	});

	// Show favourites list
	jQuery("#show_favs").live('click', function() {

		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'Favorites', width: 850, height: 500, modal: true, buttons: {}  });
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getFavs",
				tid:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#dialog").html(str);
			},
			error: function(err){
				//alert("13:"+err);
			}		
   		});
   	});
   	// Delete a favourite
	jQuery(".symposium-delete-fav").live('click', function() {

		jQuery(".symposium_notice").inmiddle().fadeIn();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"toggleFav",
				tid:jQuery(this).attr("title")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#fav_"+str).slideUp("slow");
			},
			error: function(err){
				//alert("12:"+err);
			}		
   		});

		jQuery(".symposium_notice").delay(100).fadeOut("slow");

   	});
	// Delete fav link
	jQuery('.fav_row').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find(".symposium-delete-fav").show();
	  } else {
        jQuery(this).find(".symposium-delete-fav").hide();
	  }
	});
   	
	// Show activity list
	jQuery("#show_activity").live('click', function() {

		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'My Forum Activity', width: 850, height: 500, modal: true, buttons: {}  });

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getActivity"
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#dialog").html(str);
			},
			error: function(err){
				//alert("13:"+err);
			}		
   		});
   	});
	// Show all activity list
	jQuery("#show_all_activity").live('click', function() {

		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'All Forum Activity', width: 850, height: 500, modal: true, buttons: {}  });

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getAllActivity",
				gid:symposium.current_group
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#dialog").html(str);
			},
			error: function(err){
				//alert("13:"+err);
			}		
   		});
   	});
	// Show all activity threads
	jQuery("#show_threads_activity").live('click', function() {
		
		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'Latest Topics', width: 850, height: 500, modal: true, buttons: {}  });
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getThreadsActivity",
				gid:symposium.current_group
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#dialog").html(str);
			},
			error: function(err){
				//alert("13:"+err);
			}		
   		});
   	});

	// Show search
	jQuery("#show_search").live('click', function() {

		var search_form = "<div id='search-box' style='clear:both;margin-top:12px;'>";
		search_form += "<input type='text' id='search-box-input' class='new-topic-subject-input' style='width:75%; float: left; ' />";
		search_form += "<input type='submit' id='search-box-go' class='symposium-button' style='height: 46px; float: left; margin-left: 10px;' value='Go' />";
		search_form += "</div>";
		search_form += "<div id='search-internal' style='clear:both;margin-top:12px;padding:6px;'></div>";
		
		jQuery("#dialog").html(search_form);
		jQuery("#dialog").dialog({ title: 'Search', width: 850, height: 500, modal: true, buttons: {}  });

	});
   	// Do search
	jQuery("#search-box-go").live('click', function() {
	
		jQuery("#search-internal").html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getSearch",
				term:jQuery("#search-box-input").val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#search-internal").hide().html(str).fadeIn("slow");
			},
			error: function(err){
				//alert("13:"+err);
			}		
   		});
	});
   	
	// Edit topic (AJAX)
	jQuery('#starting-post').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find("#edit-this-topic").show();
	  } else {
        jQuery(this).find("#edit-this-topic").hide();
	  }
	});

	// Edit the topic
	jQuery("#edit-this-topic").live('click', function() {
	
    	var tid = jQuery(this).attr("title");	
		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'Edit Topic', width: 600, height: 400, modal: true,
		buttons: {
				"Update": function() {
					jQuery(".symposium_notice").inmiddle().show();
					var tid = jQuery(".edit-topic-tid").attr("id");	
					var parent = jQuery(".edit-topic-parent").attr("id");
					var topic_subject = jQuery(".new-topic-subject-input").val();	
					var topic_post = jQuery(".new-topic-subject-text").val();	
					var topic_category = jQuery(".new-category").val();	
				
					if (parent == 0) {
						jQuery(".topic-post-header").html(topic_subject.replace(/\</g, "&lt;").replace(/\>/g, "&gt;"));
						jQuery(".topic-post-post").html(topic_post.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br />"));
					} else {
						jQuery("#child_"+tid).html("<p>"+topic_post.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br />")+"</p>");
					}

					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
						type: "POST",
						data: ({
							action:"updateEditDetails",
							'tid':tid,
							'topic_subject':topic_subject,
							'topic_post':topic_post,
							'topic_category':topic_category
						}),
						dataType: "html",
						async: true,
						success: function(str){
							jQuery(".symposium_notice").fadeOut("fast");
						},
						error: function(err){
							//alert("updateEditDetails:"+err);
						}		
					});
					jQuery("#edit-topic-div").html(window.html_tmp);
					jQuery(this).dialog("close");
				},
				"Cancel": function() {
					jQuery("#edit-topic-div").html(window.html_tmp);
					jQuery(this).dialog("close");
				}
			}
		});
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getEditDetails",
				tid:tid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				jQuery("#dialog").html(jQuery("#edit-topic-div").html());
				window.html_tmp = jQuery("#edit-topic-div").html();
				jQuery("#edit-topic-div").html('');
				jQuery(".new-category-div").show();
				var details = str.split("[split]");
				jQuery(".new-topic-subject-input").val(details[0]);
				jQuery(".new-topic-subject-input").removeAttr("disabled");
				jQuery(".new-topic-subject-text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery(".new-category").val(details[4]);
			},
			error: function(err){
				//alert("getEditDetails:"+err);
			}		
   		});
   	});	    	

   	// Edit a reply
	jQuery(".edit_forum_reply").live('click', function() {
		
		var tid = jQuery(this).attr("id");	
		jQuery("#dialog").html("<img src='"+symposium.plugin_url+"images/busy.gif' />");
		jQuery("#dialog").dialog({ title: 'Edit Reply', width: 600, height: 400, modal: true,
		buttons: {
				"Update": function() {
					jQuery(".symposium_notice").inmiddle().show();
					var tid = jQuery(".edit-topic-tid").attr("id");	
					var parent = jQuery(".edit-topic-parent").attr("id");
					var topic_subject = jQuery(".new-topic-subject-input").val();	
					var topic_post = jQuery(".new-topic-subject-text").val();	
					var topic_category = jQuery(".new-category").val();	
				
					if (parent == 0) {
						jQuery(".topic-post-header").html(topic_subject);
						jQuery(".topic-post-post").html(topic_post.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br />"));
					} else {
						jQuery("#child_"+tid).html("<p>"+topic_post.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br />")+"</p>");
					}

					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
						type: "POST",
						data: ({
							action:"updateEditDetails",
							'tid':tid,
							'topic_subject':topic_subject,
							'topic_post':topic_post,
							'topic_category':topic_category
						}),
						dataType: "html",
						async: true,
						success: function(str){
							jQuery(".symposium_notice").fadeOut("fast");
						},
						error: function(err){
							//alert("updateEditDetails:"+err);
						}		
					});
					jQuery("#edit-topic-div").html(window.html_tmp);
					jQuery(this).dialog("close");
				},
				"Cancel": function() {
					jQuery("#edit-topic-div").html(window.html_tmp);
					jQuery(this).dialog("close");
				}
			}
		});
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getEditDetails",
				tid:tid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				jQuery("#dialog").html(jQuery("#edit-topic-div").html());
				window.html_tmp = jQuery("#edit-topic-div").html();
				jQuery("#edit-topic-div").html('');
				jQuery(".new-category-div").hide();
				var details = str.split("[split]");
				jQuery(".new-topic-subject-input").val(details[0]);
				jQuery(".new-topic-subject-input").attr("disabled", "enabled");
				jQuery(".new-topic-subject-text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery(".edit-topic-tid").attr("id", details[3]);
			},
			error: function(err){
				//alert("3:"+err);
			}		
   		});
   		
   	});	 
   	
	// Add new reply to a topic
	jQuery("#quick-reply-warning").live('click', function() {

		var reply_text = jQuery('#symposium_reply_text').val().replace(/[\n\r]$/,"");
		if (reply_text == '') {
			jQuery("#symposium_reply_text").css('border', '1px solid red').effect("highlight", {}, 4000);
		} else {
		
			var html = "<div class='child-reply' style='overflow:hidden'>";
			html += "<div class='avatar'>";
			html += jQuery('#symposium_current_user_avatar').html().replace(/200/g, '64');		
			html += "</div>";
			html += "<div style='padding-left: 85px;'>";
			html += "<div class='child-reply-post'>";
			html += reply_text.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/(<([^>]+)>)/ig, '').replace(/\n/g, "<br />");
			html += "</div>";
			html += "<br class='clear' />";						
			html += "</div>";
			if (jQuery('#forum_file_list').length) {
				html += jQuery('#forum_file_list').html().replace(/<.*?>/g,'');
			}
			html += "</div>";
			html += "<div class='sep'></div>";						
			jQuery(html).appendTo('#child-posts'); 
			jQuery('#symposium_reply_text').val('');
			jQuery('#forum_file_list').html('');
				
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
				type: "POST",
				data: ({
					action:"reply",
					'tid':jQuery('#symposium_reply_tid').val(),
					'cid':jQuery('#symposium_reply_cid').val(),
					'reply_text':reply_text,
					'group_id':symposium.current_group
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					//alert(str);
				},
				error: function(err){
					//alert("quick-reply-warning:"+err);
				}
	   		});
	
		}
   				
	});


	// Show delete links on hover
	jQuery('.row').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find(".delete_topic").show()
	  } else {
        jQuery(this).find(".delete_topic").hide();
	  }
	});
	jQuery('.row_odd').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find(".delete_topic").show()
	  } else {
        jQuery(this).find(".delete_topic").hide();
	  }
	});
	jQuery('.child-reply').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find(".delete_forum_reply").show();
        jQuery(this).find(".edit_forum_reply").show();
	  } else {
        jQuery(this).find(".delete_forum_reply").hide();
        jQuery(this).find(".edit_forum_reply").hide();
	  }
	});
	
	// Delete reply
	jQuery('.delete_forum_reply').live('click', function(event) {

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"deleteReply",
				topic_id:jQuery(this).attr("id")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery('#reply'+str).slideUp("slow");
			}	
   		});	

	});

    // Delete topic
	jQuery(".delete_topic").live('click', function() {
		
		if ( confirm(areyousure) ) {
	  	
	    	var topic_id = jQuery(this).attr("id");	

			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
				type: "POST",
				data: ({
					action:"deleteTopic",
					topic_id:topic_id
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					jQuery('#row'+str).slideUp("slow");
				}	
	   		});
	
		}
		
	});

	// Show new topic and reply topic forms
	jQuery("#new-topic-button").live('click', function() {
	  	jQuery("#new-topic").show();
	  	jQuery("#new-topic-button").hide();
	});
	jQuery("#cancel_post").live('click', function() {
	  	jQuery("#new-topic").hide();
	  	jQuery("#new-topic-button").show();
	});

	// Post a new topic
	jQuery("#symposium_new_post").live('click', function() {
		
		var subject = jQuery('#new_topic_subject').val();
		var text = jQuery('#new_topic_text').val();
		var category = jQuery('#new_topic_category').val();

		if (subject == '') {
			jQuery("#new_topic_subject").css('border', '1px solid red').effect("highlight", {}, 4000);
		} else {
		
			if (text == '') {
				jQuery("#new_topic_text").css('border', '1px solid red').effect("highlight", {}, 4000);
			} else {

				jQuery(".symposium_pleasewait").inmiddle().show();
		
				var subscribed = '';
		        if(jQuery('#new_topic_subscribe').is(":checked")) {
					var subscribed = 'on'
				}
				var info_only = '';
		        if(jQuery('#info_only').is(":checked")) {
					var info_only = 'on'
				}

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"forumNewPost",
						'subject':subject,
						'text':text,
						'category':category,
						'subscribed':subscribed,
						'info_only':info_only,
						'group_id':symposium.current_group
					}),
					success: function(str){
						window.location.href=str;
						},
				    error: function(err){
						jQuery(".symposium_pleasewait").fadeOut("slow");
						alert("updateForumSubscribe:"+err);
					}		
		   		});
			}
		}
	});
	
	
	// Has a checkbox been clicked? If so, check if one for symposium (AJAX)
	jQuery("input[type='checkbox']").live('click', function() {

    	var checkbox = jQuery(this).attr("id");		

		// Toggle for info only
    	if (checkbox == "symposium_for_info") {
    	    var value = '';
	        if(jQuery(this).is(":checked")) {
	            value = 'on';
		   		jQuery(".forum_post_answer").hide();
	        } else {
		   		jQuery(".forum_post_answer").show();
	        }
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
				type: "POST",
				data: ({
					action:"toggleForInfo",
					tid:jQuery(this).attr("title"),
					value:value
				}),
			    dataType: "html",
				async: true
	   		});
	   	};
	   	
    	// Subscribe to New Forum Topics in a category
    	if (checkbox == "symposium_subscribe") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSubscribe",
						'cid':jQuery(this).attr("title"),
						"value":1
					}),
				    error: function(err){
						//alert("5:"+err);
					}		
		   		});

	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSubscribe",
						'cid':jQuery(this).attr("title"),
						"value":0
					}),
				    error: function(err){
						//alert("6:"+err);
					}		
		   		});
		   		
	        }
			jQuery(".symposium_notice").delay(100).fadeOut("slow");
    	}

    	// Subscribe to Topic Posts
    	if (checkbox == "subscribe") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForum",
						'tid':jQuery(this).attr("title"), 
						'value':1
					}),
				    error: function(err){
						//alert("7:"+err);
					}		
		   		});
		   		
	        } else {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForum",
						'tid':jQuery(this).attr("title"), 
						'value':0
					}),
				    error: function(err){
						//alert("8:"+err);
					}		
		   		});

	        }
			jQuery(".symposium_notice").delay(100).fadeOut("slow");
    	}
    	
    	// Sticky Topics
    	if (checkbox == "sticky") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSticky",
						'tid':jQuery(this).attr("title"), 
						'value':1
					}),
				    error: function(err){
						//alert("9:"+err);
					}		
		   		});
		   							
	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSticky",
						'tid':jQuery(this).attr("title"), 
						'value':0
					}),
				    error: function(err){
						//alert("10:"+err);
					}		
		   		});

	        }
			jQuery(".symposium_notice").delay(100).fadeOut("slow");
    	}
    			    	
    	// Digest
    	if (checkbox == "symposium_digest") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateDigest",
						'value':'on'
					}),
				    error: function(err){
						//alert("11:"+err);
					}		
		   		});
				
	        } else {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateDigest",
						'value':''
					}),
				    error: function(err){
						//alert("12:"+err);
					}		
		   		});
		   				        }
			jQuery(".symposium_notice").delay(100).fadeOut("slow");
    	}
    		
    	// Replies
    	if (checkbox == "replies") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicReplies", 
						'tid':jQuery(this).attr("title"), 
						'value':'on'
					}),
				    error: function(err){
						//alert("13:"+err);
					}		
		   		});
	        	
	        } else {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicReplies", 
						'tid':jQuery(this).attr("title"), 
						'value':''
					}),
				    error: function(err){
						//alert("14:"+err);
					}		
		   		});

	        }
			jQuery(".symposium_notice").delay(100).fadeOut("slow");
    	}

	});
	
    // Score
	jQuery(".forum_post_score_change").live('click', function() {
		
		if (symposium.current_user_id == 0) {
			alert('Please log in to register your vote.');
		} else {
		
			var change = jQuery(this).attr("title");
			var tid = jQuery(this).attr("id");
			var score = parseFloat(jQuery('#forum_score_'+tid).html());
					
			if (change == 'novote') {

				jQuery("#dialog").html(jQuery("#symposium_novote").html());
				jQuery("#dialog").dialog({ title: jQuery('#symposium_novote_dialog').html(), width: 600, height: 175, modal: true,
				buttons: {
						"OK": function() {
							jQuery("#dialog").dialog('close');
						}
					}
				});
				
			} else {

				if (change == 'plus') {
					var change = 1;
				} else {
					var change = -1;
				}
	
				var vote_off = parseFloat(jQuery('#symposium_forum_vote_remove').html());
				if (vote_off != 0 && vote_off == new_score) {
						jQuery('#reply'+tid).html(jQuery('#symposium_forum_vote_remove_msg').html())
				}
			
				jQuery('#forum_score_'+tid).html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicScore", 
						'tid':tid, 
						'change':change
					}),
					success: function(str){
						var rows = jQuery.parseJSON(str);
				        jQuery.each(rows, function(i,row){

							var score = row.score;
							if (score > 0) { score = '+'+score; }
							jQuery('#forum_score_'+tid).html(score);							
							if (row.str != 'OK') {
								jQuery("#dialog").html(row.str);
								jQuery("#dialog").dialog({ title: jQuery('#symposium_novote_dialog').html(), width: 600, height: 225, modal: true,
								buttons: {
										"OK": function() {
											jQuery("#dialog").dialog('close');
										}
									}
								});							
							}
				        })
					},
				    error: function(err){
						//alert("forum_post_score_change:"+err);
					}		
		   		});
		   		
			}
	
		}

	});
	
	

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          PANEL                                           |
	   +------------------------------------------------------------------------------------------+
	*/


	if (jQuery("#symposium-notification-bar").length) {

		// Quick check on polling frequency
		if ( (symposium.bar_polling > 0) && (symposium.chat_polling > 0) ) {
		
			// Sound Manager
			// soundManager.url = symposium.plugin_url+'/js/soundmanager/soundmanager2.swf'; // override default SWF url
			// soundManager.debugMode = false;
			// soundManager.consoleOnly = false;
					
		  	// Set up icon actions ******************************************************

			// Hover/click on logout?
	    	jQuery("#symposium-logout").mouseenter(function() {
	    		jQuery("#symposium-logout-div").show();
	    	});
	    	jQuery("#symposium-logout-div").mouseleave(function() {
	    		jQuery("#symposium-logout-div").fadeOut('slow');
	    	});
	    	
			// Click on change online status?
	    	jQuery("#symposium-online-status").click(function() {
				var status = jQuery("#symposium-online-status").is(":checked");
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
					type: "POST",
					data: ({
						action:'symposium_status',
						status:status
					}),
				    dataType: "html",
					async: false
			  	});
	    	});
			
			// Click on logout link
	    	jQuery("#symposium-logout-link").click(function() {
			  	if ( confirm(areyousure) ) {
					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
						type: "POST",
						data: ({
							action:'symposium_logout'
						}),
					    dataType: "html",
						async: false,
						success: function(str){
							window.location.href=symposium.site_url;
						}		
				  	});
			  	} else {
		    		jQuery("#symposium-logout-div").hide();
			  	}
	    	});
			
			// Email icon
			if (jQuery("#symposium-email-box").css("display") != "none") {
		    	jQuery("#symposium-email-box").click(function() {
					window.location.href=symposium.mail_url;
		    	});
		
			}
			
			// Icon Actions
			if (jQuery("#symposium-friends-box").css("display") != "none") {
				
		    	jQuery("#symposium-friends-box").click(function() {
					window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'view=friends';
		    	});
		    	jQuery("#symposium-online-box").click(function() {
					jQuery('#symposium-who-online').toggle("fast");
		    	});
		    	jQuery("#symposium-who-online_close").click(function() {
					jQuery('#symposium-who-online').hide("fast");
		    	});
		    	jQuery("#symposium-chatroom-box").click(function() {
					jQuery('#symposium-chatroom').show("fast");
					jQuery('#symposium-chatroom-box').removeClass('symposium-chatroom-new').addClass('symposium-chatroom-none');
					var objDiv = document.getElementById('chatroom_messages');
					objDiv.scrollTop = objDiv.scrollHeight;
					createCookie('wps_chatroom','show',7);
		    	});
		    	jQuery("#symposium-chatroom_close").click(function() {
					jQuery('#symposium-chatroom').hide("fast");
					eraseCookie('wps_chatroom');
		    	});				
			}
	
			// Make DIVS draggable (there is a slight problem, but uncomment if you want this feature)
			if (jQuery("#symposium-chatroom").length) {
				//jQuery('#symposium-chatroom').draggable();
			}
			if (jQuery("#symposium-who-online").length) {
				//jQuery('#symposium-who-online').draggable();
			}
			if (jQuery(".chat_window").length) {
				//jQuery('.chat_window').draggable();
			}
			if (jQuery("#symposium-fav-list").length) {
				//jQuery('#symposium-fav-list').draggable();
			}
			
			// Scheduled checks for chat/unread mail/etc ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			if (symposium.current_user_id > 0 ) {
			
				// Clear locking cookies
				eraseCookie('wps_bar_check');
				eraseCookie('wps_chat_check');
				eraseCookie('wps_chatroom_check');			
			   	
				// Check for notifications, unread mail, friend requests, etc
				bar_polling();
				chat_polling();
			}

			// Chatroom Clear ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery("#symposium-chatroom_clear").live('click', function() {
		
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
					type: "POST",
					data: ({
						action:'symposium_clear_chatroom'
					}),
				    dataType: "html",
					async: false,
					success: function(str){
						jQuery("#chatroom_messages").html('');
					},
					error: function(err){
						//alert("25:"+err);
					}		
			  	});
			  	
		   	});	   	
		   		
			// Chatroom Change Size ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery("#symposium-chatroom_big").live('click', function() {
				var hc = jQuery('#symposium-chatroom').height();
				if (hc < 50) {
					jQuery('#chatroom_messages').show();
					jQuery('#chatroom_typing_area').show();
					jQuery('#symposium-chatroom').css('margin-top', '0px' );
					jQuery('#symposium-chatroom').height(hc+214);
					jQuery('#symposium-chatroom_header').removeClass('symposium_unreadChat').addClass('symposium_readChat');
					jQuery('#symposium-chatroom_big').hide();
					jQuery('#symposium-chatroom_small').show();
					jQuery('#symposium-chatroom_max').show();
					jQuery('#symposium-chatroom_min').hide();	
					jQuery('#symposium-chatroom-box').removeClass('symposium-chatroom-new').addClass('symposium-chatroom-none');
					jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
					var objDiv = document.getElementById('chatroom_messages');
					objDiv.scrollTop = objDiv.scrollHeight;
					createCookie('wps_chatroom','show',7);
				}
			});
			jQuery("#symposium-chatroom_small").live('click', function() {
				var hc = jQuery('#symposium-chatroom').height();
				if (hc > 50) {
					jQuery('#chatroom_messages').hide();
					jQuery('#chatroom_typing_area').hide();
					jQuery('#symposium-chatroom').css('margin-top', '214px' );
					jQuery('#symposium-chatroom').height(hc-214);
					jQuery('#symposium-chatroom_big').show();
					jQuery('#symposium-chatroom_small').hide();
					jQuery('#symposium-chatroom_max').hide();
					jQuery('#symposium-chatroom_min').hide();	
					createCookie('wps_chatroom','min',7);
				}
			});

			// Chat window Change Size ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery(".symposium-chat_small").live('click', function() {
				var hc = jQuery(this).parent().parent().height();
				if (hc > 50) {
					jQuery(this).parent().parent().css('margin-top', '214px');
					jQuery(this).parent().find(".chat_messages").hide();
					jQuery(this).parent().find(".chat_message").hide();
					jQuery(this).parent().parent().height(hc-214);
					jQuery(this).parent().find(".symposium-chat_big").show();
					jQuery(this).hide();
					// Cookie to store minimised state
					createCookie('wps_'+jQuery(this).parent().parent().attr("id"),'min',7);
				}
			});
			jQuery(".symposium-chat_big").live('click', function() {
				var hc = jQuery(this).parent().parent().height();
				if (hc < 50) {
					jQuery(this).parent().parent().css('margin-top', '0px');
					jQuery(this).parent().find(".chat_messages").show();
					jQuery(this).parent().find(".chat_message").show();
					jQuery(this).parent().parent().height(hc+214);
					jQuery(this).parent().find(".symposium-chat_small").show();
					jQuery(this).hide();
					jQuery(this).parent().removeClass('symposium_unreadChat').addClass('symposium_readChat');
					// Remove cookies
					eraseCookie('wps_'+jQuery(this).parent().parent().attr("id"));
					eraseCookie('lastchat'+jQuery(this).parent().parent().attr("id"));
				}
			});
								   		
			// Chat Window Close ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery(".chat_close").live('click', function() {
		
		   		var chat_win = jQuery(this).parent().parent().attr('id');
		   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';
		   		var display_name = jQuery(this).parent().parent().attr('id')+'_display_name';
				jQuery('#'+chat_win).hide();
				createCookie('chatwin'+jQuery('#'+chat_to).html(),'hidden',7);
				
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
					type: "POST",
					data: ({
						action:'symposium_closechat', 
						chat_to:jQuery('#'+chat_to).html()
					}),
				    dataType: "html",
					async: true,
					success: function(str){
						jQuery('#'+chat_to).html('');
					},
					error: function(err){
						//alert("15:"+err);
					}		
			  	});
			  	
		   	});
	
			// Type in Chat Window ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery('.chat_message').keypress(function(event) {
				if (event.which == 13) {
					var msg = jQuery(this).val();
					msg = msg.replace(/</g, '&lt');
					msg = msg.replace(/>/g, '&gt');

					jQuery(this).val('');
					jQuery.trim(msg);
					event.preventDefault();

					if (msg != '') {
		
				   		var chat_message = jQuery(this).parent().parent().attr('id')+'_message';
				   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';

						jQuery('#'+chat_message).append('<div style="color: #006">'+msg+'<br /></div>');
						var objDiv = document.getElementById(chat_message);
						objDiv.scrollTop = objDiv.scrollHeight;
	
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
							type: "POST",
							data: ({
								action:'symposium_addchat',
								chat_to:jQuery('#'+chat_to).html(),
								chat_message:msg
							}),
						    dataType: "html",
							async: true
					  	});
					  	
					}
					
				}
			});
			
			// Type in ChatRoom Window ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			jQuery('#chatroom_textarea').keypress(function(event) {
				if (event.which == 13) {
					var msg = jQuery(this).val();
					msg = msg.replace(/</g, '&lt');
					msg = msg.replace(/>/g, '&gt');

					jQuery(this).val('');
					jQuery.trim(msg);
					event.preventDefault();
	
					if (msg != '') {

						jQuery('#chatroom_messages').append('<div style=""><div style="clear:both;color:#006; font-style:normal;float: left;">'+msg+'</div><div style="clear:both; float:right; color:#aaa; font-style:italic;">'+symposium.current_user_display_name+'</div><br style="clear:both;" /></div>');
						var objDiv = document.getElementById('chatroom_messages');
						objDiv.scrollTop = objDiv.scrollHeight;

						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
							type: "POST",
							data: ({
								action:'symposium_addchatroom',
								chat_message:msg
							}),
						    dataType: "html",
							async: true
					  	});
					}
				}
			});
			
			
			// CHAT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		
			if (symposium.use_chat == 'on') {
			
		   		var numChatWindows = 3;
		
		    	// ************** When clicking on a name to chat...
		    	
				jQuery(".symposium_online_name").live('click', function() {
					// clear hidden flag if it's there
					eraseCookie('chatwin'+jQuery(this).attr("title"));
		    		// choose a chat box
		    		var chatbox = 0;
		    		var already_chatting = 0;
		    		// first check to see if already chatting to them/Volumes/simon.goodchild
					for (w=1;w<=numChatWindows;w++) {	
			    		if ( (already_chatting == 0) && (jQuery('#chat'+w+'_to').html() == jQuery(this).attr("title")) ) { already_chatting = w; }
					}
		    		if (already_chatting == 0) {
			    		// not already chatting, so find a free chat window
			    		chatbox = 0;
						for (w=1;w<=numChatWindows;w++) {	
				    		if (jQuery('#chat'+w).css("display") == "none") { chatbox = w; }
						}
			    		if (chatbox > 0) {
			    			// found a free chat window
							jQuery('#chat'+chatbox+'_to').html(jQuery(this).attr("title"));
							jQuery('#chat'+chatbox+'_display_name').html(pleasewait);
							jQuery("#chat"+chatbox+"_message").html('');
							jQuery("#chat"+chatbox).show('fast');
							
							jQuery.ajax({
								url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
								type: "POST",
								data: ({
									action:"symposium_openchat", 
									chat_to:jQuery(this).attr("title")
								}),
							    dataType: "html",
								async: true,
								success: function(str){
									if (str.substring(0, 2) == 'OK') { 
										var details = str.split("[split]");
										jQuery('#chat'+chatbox+'_to').html(details[1]);
										jQuery('#chat'+chatbox+'_display_name').html(details[2]);
										jQuery('#chat'+chatbox+'_message').html('');
									} else {
										if (jQuery('#chat'+chatbox+'_to').html() == str) { 
											jQuery('#chat'+chatbox).show("fast"); 
										}
									}
								},
								error: function(err){
									//alert("17:"+err);
								}		
						  	});
												
			    		} else {
			    			// no free chat windows
			    			alert("Sorry - you can't open any more chat windows.");
			    		}
			    		
		    		} else {
		    			
		    			// already chatting, so clear closed tag and re-open it
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
							type: "POST",
							data: ({
								action:"symposium_reopenchat", 
								chat_to:jQuery(this).attr("title")
							}),
						    dataType: "html",
							async: false,
							success: function(str){
								if (str != '') {
									for (w=1;w<=numChatWindows;w++) {	
										if (jQuery('#chat'+w+'_to').html() == str) { 
											jQuery('#chat'+w).show(); 
										}
									}
								}
							},
							error: function(err){
								//alert("18:"+err);
							}		
					  	});
					  	
		    		}
		    	});
		    	
			}
			
		} else {
			
			alert('Polling frequencies needs to be changed to a longer period.');
			
		}
	}


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          ADMIN                                           |
	   +------------------------------------------------------------------------------------------+
	*/
	
	// Styles (clear save as name if loading stored style)
	jQuery("#style_save_as_button").click(function() {
		jQuery("#style_save_as").val('');
	});

	// Installation Page (Add to new)
	jQuery(".symposium_addnewpage").click(function() {
		var shortcode = jQuery(this).attr("title");
		var name = jQuery(this).attr("id");
		
		jQuery('#symposium_wait_'+shortcode).html("<img src='"+symposium.plugin_url+"/images/busy.gif' />&nbsp;");
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
			type: "POST",
			data: ({
				action:"add_new_page",
				shortcode:shortcode,
				name:name
				}),
		    dataType: "html",
			async: false,
			success: function(str){
				if (trim(str) != 'OK') {
					alert('Problem adding page, please add manually ('+str+')');
				} else {
					location.reload();
				}
			}
   		});				
   	});

	// Installation Page (Add to existing)
	jQuery(".symposium_addtopage").click(function() {
		var shortcode = jQuery(this).attr("title");
		var value = jQuery('#symposium_pagechoice_'+shortcode).val();  

		jQuery('#symposium_wait_'+shortcode).html("<img src='"+symposium.plugin_url+"/images/busy.gif' />&nbsp;");
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
			type: "POST",
			data: ({
				action:"add_to_page",
				shortcode:shortcode,
				id:value				
				}),
		    dataType: "html",
			async: false,
			success: function(str){
				if (trim(str) != 'OK') {
					alert('Problem adding to page, please add manually ('+str+')');
				} else {
					location.reload();
				}
			}
   		});				
   	});
   	
   	// Show moderation post in full
	if (jQuery(".show_full_post").length) {
		jQuery(".show_full_post").click(function() {
			alert(jQuery(this).attr("title"));
		});
	}
   		
	// Hide DIVs after showing for 1.5 seconds
	jQuery(".slideaway").delay(1500).slideUp("slow");

	if (jQuery("#jstest").length) {
		jQuery("#jstest").hide();
	}

	// Hidden column on installation page
	jQuery(".symposium_url").hide();
 	jQuery("#symposium_url").click(function() {
		jQuery(".symposium_url").toggle();
 	});
	
	// Import/Export Templates
	jQuery("#symposium_import_templates").click(function() {
		jQuery("#symposium_import_templates_form").show();
		jQuery("#symposium_templates_values").hide();
	});
	
	jQuery("#symposium_export_templates").click(function() {
		jQuery("#symposium_export_templates_form").show();
		jQuery("#symposium_templates_values").hide();
	});
	
	jQuery(".symposium_templates_cancel").click(function() {
		jQuery("#symposium_import_templates_form").hide();
		jQuery("#symposium_export_templates_form").hide();
		jQuery("#symposium_templates_values").show();
	});
	jQuery("#symposium_import_file_button").click(function() {
		if (confirm(areyousure)) {
			var import_file = jQuery("#symposium_import_file").val();
			jQuery('#symposium_import_file_pleasewait').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
				type: "POST",
				data: ({
					action:"import_template_file",
					import_file:import_file
					}),
			    dataType: "html",
				async: false,
				success: function(str){
					if (trim(str) != 'OK') {
						alert('Problem importing, please check format of import file');
					}
					location.reload();
				}
	   		});
		}
	});
	
	
	// Templates
 	jQuery("#reset_profile_header").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='profile_header_div'>[]<div id='profile_header_panel'>[]<div id='profile_details'>[]<div style='float:right'>[poke]</div>[]<div id='profile_name'>[display_name]</div>[]<p>[location]<br />[born]</p>[]<div style='padding: 0px;'>[actions]</div>[]</div>[]</div>[]<div id='profile_photo' class='corners'>[avatar,200]</div>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#profile_header_textarea").val(reset);
		}
	});
 	jQuery("#reset_profile_body").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='profile_wrapper'>[]<div id='force_profile_page' style='display:none'>[default]</div>[]<div id='profile_body_wrapper'>[]<div id='profile_body'>[page]</div>[]</div>[]<div id='profile_menu'>[menu]</div>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#profile_body_textarea").val(reset);
		}
	});
 	jQuery("#reset_page_footer").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='powered_by_wps'>[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#page_footer_textarea").val(reset);
		}
	});
 	jQuery("#reset_email").click(function() {
		if (confirm(areyousure)) {
			var reset = "<style> body { background-color: #eee; } </style>[]<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>[][message][]<br /><hr />[][footer]<br />[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#email_textarea").val(reset);
		}
	});
 	jQuery("#reset_forum_header").click(function() {
		if (confirm(areyousure)) {
			var reset = "[breadcrumbs][new_topic_button][new_topic_form][][digest][subscribe][][forum_options][][sharing]";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_forum_header_textarea").val(reset);
		}
	});
 	jQuery("#reset_mail").click(function() {
		if (confirm(areyousure)) {
			var reset = "[compose_form][]<div id='mail_sent_message'></div>[]<div id='mail_office'>[]<div id='mail_toolbar'>[]<input id='compose_button' class='symposium-button' type='submit' value='[compose]'>[]<div id='trays'>[]<input type='radio' id='in' class='mail_tray' name='tray' checked> [inbox] <span id='in_unread'></span>&nbsp;&nbsp;[]<input type='radio' id='sent' class='mail_tray' name='tray'> [sent][]</div>[]<div id='search'>[]<input id='search_inbox' type='text' style='width: 160px'>[]<input id='search_inbox_go' class='symposium-button message_search' type='submit' style='margin-left:10px;' value='Search'>[]</div>[]</div>[]<div id='mailbox'>[]<div id='mailbox_list'></div>[]</div>[]<div id='messagebox'></div>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_mail_textarea").val(reset);
		}
	});
 	jQuery("#reset_mail_tray").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='mail_mid' class='mail_item mail_read'>[]<div class='mailbox_message_from'>[mail_from]</div>[]<div class='mail_item_age'>[mail_sent]</div>[]<div class='mailbox_message_subject'>[mail_subject]</div>[]<div class='mailbox_message'>[mail_message]</div>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_mail_tray_textarea").val(reset);
		}
	});
 	jQuery("#reset_mail_message").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='message_header'>[]<div id='message_header_avatar'>[avatar,44]</div>[mail_subject]<br />[mail_recipient] [mail_sent]</div>[]<div id='message_header_delete'>[delete_button]</div><div id='message_header_reply'>[reply_button]</div>[]<div id='message_mail_message'>[message]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_mail_message_textarea").val(reset);
		}
	});
 	jQuery("#reset_group").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div id='group_header_div'><div id='group_header_panel'>[]<div id='group_details'>[]<div id='group_name'>[group_name]</div>[]<div id='group_description'>[group_description]</div>[]<div style='padding: 15px;'>[actions]</div>[]</div></div>[]<div id='group_photo' class='corners'>[avatar,200]</div>[]</div>[]<div id='group_wrapper'>[]<div id='force_group_page' style='display:none'>[default]</div>[]<div id='group_body_wrapper'>[]<div id='group_body'>[page]</div>[]</div>[]<div id='group_menu'>[menu]</div>[]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_group_textarea").val(reset);
		}
	});
 	jQuery("#reset_template_forum_category").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][subject][ago]</div>[]</div>[]<div class='row_views'>[post_count]</div>[]<div class='row_topic row_replies'>[topic_count]</div>[]<div class='row_topic'>[category_title]<br />[category_desc]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_forum_category_textarea").val(reset);
		}
	});
 	jQuery("#reset_template_forum_topic").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][topic][ago]</div>[]</div>[]<div class='row_views'>[views]</div>[]<div class='row_replies'>[replies]</div>[]<div class='row_topic'>[topic_title]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_forum_topic_textarea").val(reset);
		}
	});	
 	jQuery("#reset_template_group_forum_topic").click(function() {
		if (confirm(areyousure)) {
			var reset = "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][topic][ago]</div>[]<div class='row_topic'>[topic_title]</div>";
			reset = reset.replace(/\[\]/g, String.fromCharCode(13));
			jQuery("#template_group_forum_topic_textarea").val(reset);
		}
	});	
	
	var adm_user_login = jQuery("#symposium_user_login").html();
	var adm_user_email = jQuery("#symposium_user_email").html();
		
	// Uploadify
	jQuery('#admin_file_upload').uploadify({
	    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
		'buttonText': 'Browse for file',
	    'script'    : symposium.plugin_url+'uploadify/upload_admin_avatar.php',
		'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
	    'cancelImg' : symposium.plugin_url+'uploadify/cancel.png',
	    'auto'      : true,
		'scriptData' : {'user_login':adm_user_login, 'user_email':adm_user_email, 'uid':symposium.current_user_id}, 
		'onError' 	: function(event, ID, fileObj, errorObj) {
						 alert("Error: "+errorObj.type+" "+errorObj.info);
					  },
		'onComplete': function(event, queueID, fileObj, response, data) { 

							if (response.substring(0, 5) == 'Error') {
								alert(response); 
							} else {

								jQuery('#admin_jcrop_target').Jcrop({
									onChange: showAdminPreview,
									onSelect: showAdminPreview
								});

								jQuery('#admin_image_to_crop').html(response);
		
								jQuery('#admin_jcrop_target').Jcrop({
									onChange: showAdminPreview,
									onSelect: showAdminPreview
								});
							}				
					  }
   	});
			
	function showAdminPreview(coords)
	{
		var rx = 100 / coords.w;
		var ry = 100 / coords.h;

		jQuery('#x').val(coords.x);
		jQuery('#y').val(coords.y);
		jQuery('#x2').val(coords.x2);
		jQuery('#y2').val(coords.y2);
		jQuery('#w').val(coords.w);
		jQuery('#h').val(coords.h);
			
		jQuery('#admin_preview').css({
			width: Math.round(rx * jQuery('#admin_jcrop_target').width()) + 'px',
			height: Math.round(ry * jQuery('#admin_jcrop_target').height()) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
	};

	// Save admin avatar
	jQuery("#saveAdminAvatar").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
			type: "POST",
			data: ({
				action:"saveAdminAvatar",
				x:jQuery("#x").val(),
				y:jQuery("#y").val(),
				w:jQuery("#w").val(),
				h:jQuery("#h").val()
				}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (trim(str) != '') {
					alert(str);
				}
				location.reload();
			},
			error: function(err){
				jQuery(".symposium_notice").fadeOut("slow");
				//alert("saveAdminAvatar:"+err);
			}		
   		});
   			
   	});

 	// Test AJAX
 	jQuery("#testAJAX").click(function() {
 		random = Math.floor(Math.random()*10)+1;
 		alert("The random number being sent is "+random);

	  	jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_menu_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_test",
				postID:random
			}),
		    dataType: "html",
			async: false,
			success: function(str_test){
				jQuery("#testAJAX_results").val('Value of '+str_test+' returned!');
			},
			error: function(err){
				alert("Test:"+err);
			}		
 		});
 		
	});

	if (jQuery("#hide_motd").length) {	
		jQuery('#hide_motd').click(function(){
			jQuery("#motd").slideUp("slow");
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
				type: "POST",
				data: ({
					action:"symposium_motd"
					}),
			    dataType: "html",
				async: true,
				success: function(str){
					window.location.href="admin.php?page=symposium_debug";
				}				
				
	   		});	
		});
	}	
			
});

// For Notification Bar (chat windows)
function do_chat_check() {

	var chat_check_cookie = readCookie('wps_chat_check');

	if (chat_check_cookie == 'lock') {
		// Still processing previous
		//alert('CHAT LOCKED');
	} else {
		// Set cookie (to avoid over-lapping checks)
		//alert('CREATE CHAT LOCK');
		createCookie('wps_chat_check','lock',1);

		var numChatWindows = 3; // Should equal number of chat windows set up in symposium_bar.php

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getchat", 
				inactive:symposium.inactive,
				offline:symposium.offline
			}),
			dataType: "html",
			async: true,
			success: function(str){
				if (str != '') {

					var rows=str.split("[split]");
					var num_rows = rows.length-1;
					var play_sound = false;
					var last_chid = new Array();
					var new_last_chid = new Array();
					var new_last_from = new Array();
					
					// clear chat windows, and get last chid from each
					for (w=1;w<=numChatWindows;w++) {	
						last_chid[w] = readCookie('lastchat'+w);
						jQuery('#chat'+w+'_to').html('');
						jQuery('#chat'+w+'_message').html('');
						jQuery('#chat'+w+'_display_name').html('');
					}
					
					var allocated_windows = 0;
					// loop through messages, setting up all the chat windows for each person
					for (i=0;i<num_rows;i++) {	
						var details=rows[i].split("[|]");
						var chid = details[0];
						var from = details[1];
						var to = details[2];
						var name = details[3];
						var status = details[5];

						var other = 0;
						
						if (from == symposium.current_user_id) {
							other = to; 
						} else {
							other = from;
						}

						// see if a window has been allocated
						var chat_win = 0;
						for (w=1;w<=numChatWindows;w++) {	
							if (jQuery('#chat'+w+'_to').html() == other) { chat_win = w; }
							new_last_chid[w] = chid; 							
							new_last_from[w] = from; 
						}
						
						// if not, then find next free window
						if (chat_win == 0) {
							var allocated = false;
							for (w=1;w<=numChatWindows;w++) {	
								if ( (jQuery('#chat'+w+'_to').html() == '') && (allocated == false) ) { 
									jQuery('#chat'+w+'_to').html(other); 
									jQuery('#chat'+w+'_display_name').html('<img src="'+symposium.plugin_url+'images/'+status+'_header.gif" /> '+name); 
									allocated_windows++; 
									allocated = true;
									new_last_chid[w] = chid;
									new_last_from[w] = from; 							
								}
							}
						}
					}
					
					// Loop through the messages, adding the message to the correct chat window
					for (i=0;i<num_rows;i++) {	
						var details=rows[i].split("[|]");
						var chid = details[0];
						var from = details[1];
						var to = details[2];
						var name = details[3];
						var msg = details[4];
		
						if (from == symposium.current_user_id) {
							other = to; 
						} else {
							other = from;
						}
						
						// Find the window to add the message to
						var chat_win = 0;
						for (w=1;w<=numChatWindows;w++) {	
							if (jQuery('#chat'+w+'_to').html() == other) { chat_win = w; }
						}
						if (chat_win > 0) {											
							for (w=1;w<=numChatWindows;w++) {	
								if (chat_win == w) { 
									if (msg.indexOf('[start]') < 0) { 
										if (!(msg.indexOf('[closed-'+other+']') >= 0)) {
											jQuery('#chat'+w+'_message').append(msg);
										}
									} else {
										// New chat session
										jQuery('#chat'+w+'_message').append('');
									}
								}
							}
						}
																
					}
					
					// Show/hide all the chat windows
					for (w=1;w<=numChatWindows;w++) {	
						if (jQuery('#chat'+w+'_to').html() != '') {

							var message = jQuery("#chat"+w+"_message").html()+' ';
							
							if (message.indexOf('[closed-'+symposium.current_user_id+']') >= 0) { 						
								jQuery('#chat'+w+'_to').html(''); 
								jQuery('#chat'+w).hide(); 
								
							} else {

								var chat_to = jQuery('#chat'+w+'_to').html();
								if (readCookie('chatwin'+chat_to) == 'hidden') {
									eraseCookie('chatwin'+chat_to);
								} else {
									if (readCookie('wps_chat'+w) == 'min') {
										var hc = jQuery('#chat'+w).height();
										if (hc > 50) {
											jQuery('#chat'+w).css('margin-top', '214px');
											jQuery('#chat'+w).find(".message").hide();
											jQuery('#chat'+w).find(".messages").hide();
											jQuery('#chat'+w).height(hc-214);
											jQuery('#chat'+w).find(".symposium-chat_big").show();
											jQuery('#chat'+w).find(".symposium-chat_small").hide();
										}
									}

									jQuery('#chat'+w).show();

								}
								
							}

							// Highlight title if new message and minimised
							if (last_chid[w] != new_last_chid[w]) {
								if (jQuery('#chat'+w).height() < 50) {
									jQuery('#chat'+w+'_header').removeClass('symposium_readChat').addClass('symposium_unreadChat');
								} else {
									createCookie('lastchat'+w,new_last_chid[w],7);
								}	
							}
							// Scroll to bottom
							var objDiv = document.getElementById('chat'+w+'_message');
							objDiv.scrollTop = objDiv.scrollHeight;

						} else {
							jQuery('#chat'+w).hide();
						}
					}
					
					// Finished all messages, play sound? There is no check for new mail yet, so won't work yet
					if (play_sound == true) {
						// soundManager.play('ChatAlert',symposium.plugin+'/js/soundmanager/'+symposium.soundchat);
					}
					
				} else {								
					// No chat occuring, close all windows
					for (w=1;w<=numChatWindows;w++) {	
						jQuery('#chat'+w).hide();
					}
				}
				
				//alert('CLEAR CHAT LOCK');
				eraseCookie('wps_chat_check');

			},
			error: function(err){
				//alert("19:"+err);
			}		
		});
	}
		
}	
function do_chatroom_check() {

	var chatroom_check_cookie = readCookie('wps_chatroom_check');

	if (chatroom_check_cookie == 'lock') {
		// Still processing previous
		//alert('CHATROOM LOCKED');
	} else {
		// Set cookie (to avoid over-lapping checks)
		createCookie('wps_chatroom_check','lock',1);
		//alert('CREATE CHATROOM LOCK');

		var show_chatroom = readCookie('wps_chatroom');
		
		if(!(jQuery('#symposium-chatroom').is(':visible'))) {	
			if (show_chatroom == "show") {	
				jQuery('#symposium-chatroom').show("fast");
			}
			if (show_chatroom == "min") {	
				var hc = jQuery('#symposium-chatroom').height();
				if (hc > 50) {
					jQuery('#chatroom_messages').show();
					jQuery('#chatroom_typing_area').show();
					jQuery('#symposium-chatroom').css('margin-top', '214px' )
					jQuery('#symposium-chatroom').height(hc-214);
				}
				jQuery('#symposium-chatroom_big').show();
				jQuery('#symposium-chatroom_small').hide();
				jQuery('#symposium-chatroom_max').hide();
				jQuery('#symposium-chatroom_min').hide();	
				
				jQuery('#symposium-chatroom').show("fast");
			}
		}
			
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getchatroom",
				use_chat:symposium.use_chat,
				inactive:symposium.inactive,
				offline:symposium.offline
			}),
			dataType: "html",
			async: true,
			success: function(str){

				var split=str.split("[split]");

				if (split[1] != readCookie('wps_chatroom_chid') || jQuery('#chatroom_messages').html() == '') {
					jQuery('#chatroom_messages').html(split[2]);
					var objDiv = document.getElementById('chatroom_messages');
					objDiv.scrollTop = objDiv.scrollHeight;
				}
				
				if (split[0] != '' && split[0] != symposium.current_user_id && split[1] != readCookie('wps_chatroom_chid')) {
					if (jQuery('#symposium-chatroom').height() < 50) {
						jQuery('#symposium-chatroom_header').removeClass('symposium_readChat').addClass('symposium_unreadChat');
					}
					jQuery('#symposium-chatroom-box').removeClass('symposium-chatroom-none').addClass('symposium-chatroom-new');
				} else {
					if (show_chatroom == "show") {
						jQuery('#symposium-chatroom-box').removeClass('symposium-chatroom-new').addClass('symposium-chatroom-none');
					}
				}
				
				createCookie('wps_chatroom_chid',split[1],7);

				//alert('CLEAR CHATROOM LOCK');
				eraseCookie('wps_chatroom_check');			
			
			},
			error: function(err){
				//alert("24:"+err);
			}		
		});
	}
		
}	
function do_online_friends_check() {
	
  	// Friends Online ******************************************************
	if (jQuery("#symposium-friends-box").css("display") != "none") {
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_friendrequests"
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str > 0) {
					jQuery("#symposium-friends-box").html(str);
					jQuery("#symposium-friends-box").removeClass("symposium-friends-box-none");
					jQuery("#symposium-friends-box").addClass("symposium-friends-box-new");
				}
			},
			error: function(err){
				//alert("22:"+err);
			}		
   		});
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getfriendsonline", 
				inactive:symposium.inactive,
				offline:symposium.offline,
				use_chat:symposium.use_chat
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != '') {
					var split=str.split("[split]");
					jQuery("#symposium-online-box").html(split[0]);
					jQuery("#symposium-friends-online-list").html(split[1]);
					if (split[0] > 0) {
						jQuery("#symposium-online-box").removeClass("symposium-online-box-none");
						jQuery("#symposium-online-box").addClass("symposium-online-box");
					} else {
						jQuery("#symposium-online-box").removeClass("symposium-online-box");
						jQuery("#symposium-online-box").addClass("symposium-online-box-none");
					}
				}
			},
			error: function(err){
				//alert("23:"+err);
			}		
   		});
   				
	}
}
function do_bar_check() {

	var bar_check_cookie = readCookie('wps_bar_check');

	if (bar_check_cookie == 'lock') {
		// Still processing previous
		//alert('BAR LOCKED');
	} else {
		// Set cookie (to avoid over-lapping checks)
		createCookie('wps_bar_check','lock',1);
		//alert('CREATE BAR LOCK');
		
		// Email ******************************************************
		if (jQuery("#symposium-email-box").css("display") != "none") {
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
				type: "POST",
				data: ({
					action:"symposium_getunreadmail"
				}),
				dataType: "html",
				async: true,
				success: function(str){
					if (str > 0) {
						jQuery("#symposium-email-box").html(str);
						jQuery("#symposium-email-box").removeClass("symposium-email-box-read");
						jQuery("#symposium-email-box").addClass("symposium-email-box-unread");
					}
					eraseCookie('wps_bar_check');
					//alert('CLEAR BAR LOCK');
				},
				error: function(err){
					//alert("21:"+err);
				}		
			});

		}
	}
}		

function removeHTMLTags(strInputCode){
 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
	 	return (p1 == "lt")? "<" : ">";
	});
	var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
	return strTagStrippedText;	
}

// Cookies
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}


// Form validations
function validate_form(thisform)
{
	form_id = thisform.id;

	// Login
	if ( (form_id) == "symposium_login") {
		var r = true;
		with (thisform)
		{
			if (forgotten_email.value == '') {
				if (username.value == '' || username.value == null) {
					jQuery("#username-warning").show("slow");
					username.focus(); 
				} else {
					jQuery("#username-warning").hide("slow");
				}
				if (pwd.value == '' || pwd.value == null) {
					jQuery("#pwd-warning").show("slow");
					username.focus(); 
				} else {
					jQuery("#pwd-warning").hide("slow");
				}
			}
		}
		// return false to avoid submit, redirect handled in jQuery
		return false;
	}
	
	// Registration
	if ( (form_id) == "symposium_registration") {
		var r = true;
		with (thisform)
		{
			if ( (pwd.value != '' || pwd2.value != null) && (pwd.value != pwd2.value) ) {
				jQuery("#password2-warning").show("slow");
				pwd.focus(); 
				r = false;
			} else {
				jQuery("#password2-warning").hide("slow");
			}
			if (pwd.value == '' || pwd.value == null) {
				jQuery("#password-warning").show("slow");
				pwd.focus(); 
				r = false;
			} else {
				jQuery("#password-warning").hide("slow");
			}
			if (youremail.value == '' || youremail.value == null) {
				jQuery("#youremail-warning").show("slow");
				youremail.focus(); 
				r = false;
			} else {
				jQuery("#youremail-warning").hide("slow");
			}
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
   			if (reg.test(youremail.value) == false) {
				jQuery("#youremail-warning").show("slow");
				youremail.focus(); 
				r = false;
			} else {
				jQuery("#youremail-warning").hide("slow");
			}
			if (display_name.value == '' || display_name.value == null) {
				jQuery("#display_name-warning").show("slow");
				display_name.focus(); 
				r = false;
			} else {
				jQuery("#display_name-warning").hide("slow");
			}
			if (username.value == '' || username.value == null) {
				jQuery("#username-warning").show("slow");
				username.focus(); 
				r = false;
			} else {
				jQuery("#username-warning").hide("slow");
			}
		}
		return r;
	}
		
	// Forum	
	if ( (form_id) == "start-new-topic") {
		with (thisform)
		{
			if (new_topic_subject.value == '' || new_topic_subject.value == null) {
				jQuery(".new-topic-subject-warning").show("slow");
				new_topic_subject.focus(); 
				return false;
			}
			if (new_topic_text.value == '' || new_topic_text.value == null) {
				jQuery(".new_topic_text-warning").show("slow");
				new_topic_text.focus(); 
				return false;
			}
		}
	}
	if ( (form_id) == "start-reply-topic") {
		with (thisform)
		{
			if (reply_text.value == '' || reply_text.value == null) {
				jQuery(".reply_text-warning").show("slow");
				reply_text.focus(); 
				return false;
			}
		}
	}


}

function strpos (haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
}

function trim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}

function bar_polling() {
	do_bar_check();				
	setTimeout(bar_polling, symposium.bar_polling*1000);
}
function chat_polling() {
	do_chat_check();
	do_chatroom_check();
	do_online_friends_check();
	setTimeout(chat_polling, symposium.chat_polling*1000);
}

// Password strength
(function(A){A.extend(A.fn,{pstrength:function(B){var B=A.extend({verdects:["Very weak","Weak","Medium","Strong","Very strong"],colors:["#f00","#c06","#f60","#3c0","#3f0"],scores:[10,15,30,40],common:["password","sex","god","123456","123","welcome","test","qwerty","admin"],minchar:6},B);return this.each(function(){var C=A(this).attr("id");A(this).after("<div class=\"pstrength-info\" id=\""+C+"_text\"></div>");A(this).after("<div class=\"pstrength-bar\" id=\""+C+"_bar\" style=\"border: 1px solid white; font-size: 1px; height: 5px; width: 0px;\"></div>");A(this).keyup(function(){A.fn.runPassword(A(this).val(),C,B)})})},runPassword:function(D,F,C){nPerc=A.fn.checkPassword(D,C);var B="#"+F+"_bar";var E="#"+F+"_text";if(nPerc==-200){strColor="#f00";strText="Unsafe password word!";A(B).css({width:"0%"})}else{if(nPerc<0&&nPerc>-199){strColor="#ccc";strText="Too short";A(B).css({width:"5%"})}else{if(nPerc<=C.scores[0]){strColor=C.colors[0];strText=C.verdects[0];A(B).css({width:"10%"})}else{if(nPerc>C.scores[0]&&nPerc<=C.scores[1]){strColor=C.colors[1];strText=C.verdects[1];A(B).css({width:"25%"})}else{if(nPerc>C.scores[1]&&nPerc<=C.scores[2]){strColor=C.colors[2];strText=C.verdects[2];A(B).css({width:"50%"})}else{if(nPerc>C.scores[2]&&nPerc<=C.scores[3]){strColor=C.colors[3];strText=C.verdects[3];A(B).css({width:"75%"})}else{strColor=C.colors[4];strText=C.verdects[4];A(B).css({width:"92%"})}}}}}}A(B).css({backgroundColor:strColor});A(E).html("<span style='color: "+strColor+";'>"+strText+"</span>")},checkPassword:function(C,B){var F=0;var E=B.verdects[0];if(C.length<B.minchar){F=(F-100)}else{if(C.length>=B.minchar&&C.length<=(B.minchar+2)){F=(F+6)}else{if(C.length>=(B.minchar+3)&&C.length<=(B.minchar+4)){F=(F+12)}else{if(C.length>=(B.minchar+5)){F=(F+18)}}}}if(C.match(/[a-z]/)){F=(F+1)}if(C.match(/[A-Z]/)){F=(F+5)}if(C.match(/\d+/)){F=(F+5)}if(C.match(/(.*[0-9].*[0-9].*[0-9])/)){F=(F+7)}if(C.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)){F=(F+5)}if(C.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)){F=(F+7)}if(C.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)){F=(F+2)}if(C.match(/([a-zA-Z])/)&&C.match(/([0-9])/)){F=(F+3)}if(C.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)){F=(F+3)}for(var D=0;D<B.common.length;D++){if(C.toLowerCase()==B.common[D]){F=-200}}return F}})})(jQuery)
