jQuery(document).ready(function() { 	
	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          SHARED                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	// Sort out ampersand in .q
	if (symposium.q == '&amp;') {
		symposium.q = '&';
	}
	
	// Get translated strings
	var pleasewait = jQuery("#symposium_pleasewait").html();
	var saving = jQuery("#symposium_saving").html();
	var more = jQuery("#symposium_more").html();
	var browseforfile = jQuery("#symposium_browseforfile").html();
	var attachfile = jQuery("#symposium_attachfile").html();
	var whatsup = jQuery("#symposium_whatsup").html();
	if (jQuery("#symposium_areyousure").length) {
		var areyousure = jQuery("#symposium_areyousure").html(); 
	} else { 
		var areyousure = 'Are you sure?'; 
	}
	
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
	   |                                       AJAX WIDGETS                                       |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("#symposium_summary_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"symposium_summary_Widget",
				show_loggedout:jQuery("#symposium_summary_Widget_show_loggedout").html(),
				form:jQuery("#symposium_summary_Widget_form").html(),
				login_url:jQuery("#symposium_summary_Widget_login_url").html()
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_summary_Widget").html(str);
				}				
			}
   		});
	}
	
	if (jQuery("#symposium_friends_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"symposium_friends_Widget",
				count:jQuery("#symposium_friends_count").html(),
				desc:jQuery("#symposium_friends_desc").html(),
				mode:jQuery("#symposium_friends_mode").html(),
				show_light:jQuery("#symposium_friends_show_light").html(),
				show_mail:jQuery("#symposium_friends_show_mail").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_friends_Widget").html(str);
				}				
			}
   		});
	}

	if (jQuery("#symposium_Forumexperts_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"Forumexperts_Widget",
				cat_id:jQuery("#symposium_Forumexperts_Widget_cat_id").html(),
				cat_id_exclude:jQuery("#symposium_Forumexperts_Widget_cat_id_exclude").html(),
				timescale:jQuery("#symposium_Forumexperts_Widget_timescale").html(),
				postcount:jQuery("#symposium_Forumexperts_Widget_postcount").html(),
				groups:jQuery("#symposium_Forumexperts_Widget_groups").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_Forumexperts_Widget").html(str);
				}				
			}
   		});
	}
	
	if (jQuery("#symposium_Forumnoanswer_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"Forumnoanswer_Widget",
				preview:jQuery("#symposium_Forumnoanswer_Widget_preview").html(),
				cat_id:jQuery("#symposium_Forumnoanswer_Widget_cat_id").html(),
				cat_id_exclude:jQuery("#symposium_Forumnoanswer_Widget_cat_id_exclude").html(),
				timescale:jQuery("#symposium_Forumnoanswer_Widget_timescale").html(),
				postcount:jQuery("#symposium_Forumnoanswer_Widget_postcount").html(),
				groups:jQuery("#symposium_Forumnoanswer_Widget_groups").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_Forumnoanswer_Widget").html(str);
				}				
			}
   		});
	}

	if (jQuery("#symposium_recent_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"recent_Widget",
				count:jQuery("#symposium_recent_Widget_count").html(),
				desc:jQuery("#symposium_recent_Widget_desc").html(),
				show_light:jQuery("#symposium_recent_Widget_show_light").html(),
				show_mail:jQuery("#symposium_recent_Widget_show_mail").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_recent_Widget").html(str);
				}				
			}
   		});
	}
		
	if (jQuery("#symposium_members_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"members_Widget",
				count:jQuery("#symposium_members_Widget_count").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_members_Widget").html(str);
				}				
			}
   		});
	}	
	
	if (jQuery("#symposium_Recentactivity_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"Recentactivity_Widget",
				postcount:jQuery("#symposium_Recentactivity_Widget_postcount").html(),
				preview:jQuery("#symposium_Recentactivity_Widget_preview").html(),
				forum:jQuery("#symposium_Recentactivity_Widget_forum").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_Recentactivity_Widget").html(str);
				}				
			}
   		});
	}

	if (jQuery("#symposium_Forumrecentposts_Widget").length) {
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
			data: ({
				action:"Forumrecentposts_Widget",
				postcount:jQuery("#symposium_Forumrecentposts_Widget_postcount").html(),
				preview:jQuery("#symposium_Forumrecentposts_Widget_preview").html(),
				cat_id:jQuery("#symposium_Forumrecentposts_Widget_cat_id").html(),
				show_replies:jQuery("#symposium_Forumrecentposts_Widget_show_replies").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_Forumrecentposts_Widget").html(str);
				}				
			}
   		});
	}	   

	if (jQuery("#symposium_Gallery_Widget").length) {
		jQuery.ajax({
      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
			data: ({
				action:"Gallery_Widget",
				albumcount:jQuery("#symposium_Gallery_Widget_albumcount").html(),
			}),
			type: "POST", dataType: "html", async: false,
			success: function(str){
				if (str.substring(0, 4) == 'FAIL') { alert(str); } else {
					jQuery("#symposium_Gallery_Widget").html(str);
				}				
			}
   		});
	}
	
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

	// Show mail link on friend hover
	jQuery('.members_row').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
			jQuery(this).find(".mail_icon").show();
	  } else {
        	jQuery(this).find(".mail_icon").hide();
	  }
	});

	jQuery('#symposium_member').live('keydown', function(e) { 
	  var keyCode = e.keyCode || e.which; 
	
	  if (keyCode == 9 || keyCode == 27) { 
		jQuery('#symposium_member_list').hide();
	  } 

	});

	// Order by
	jQuery('#symposium_members_orderby').change(function() {
		jQuery("#symposium_directory_start").html('0');
		symposium_do_member_search();
	});

	jQuery('#members_go_button').live('click', function () {
		jQuery("#symposium_directory_start").html('0');
		symposium_do_member_search();
	});
	jQuery('#symposium_member').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			jQuery("#symposium_directory_start").html('0');
			symposium_do_member_search();
		}
	});
	
	// Search
	jQuery('#showmore_directory').live('click', function () {
		symposium_do_member_search();
	});	
	
	function symposium_do_member_search() {

		var page_length = jQuery('#symposium_directory_page_length').html();
		var start = jQuery("#symposium_directory_start").html();
		
		if (start == 0) {
			jQuery('#symposium_members').html("<br /><img src='"+symposium.images_url+"/busy.gif' />");
		}

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
				start:start,
				orderby:jQuery('#symposium_members_orderby').val(),
				term:jQuery('#symposium_member').val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){				
				var new_start = parseFloat(start)+parseFloat(page_length);
				jQuery("#symposium_directory_start").html(new_start);

				if (start == 0) {
					jQuery('#symposium_members').html(str);
				} else {
					jQuery('#showmore_directory').remove();
					jQuery(str).appendTo('#symposium_members').hide().slideDown("slow");
				}

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
		jQuery('#mailbox_list').html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery('#messagebox').html("<img src='"+symposium.images_url+"/busy.gif' />");

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
		
		jQuery('#show_more_mail').html("<img src='"+symposium.images_url+"/busy.gif' />");

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
		jQuery('#mail_sent_message').show().html("<img src='"+symposium.images_url+"/busy.gif' />");
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
			jQuery('#mailbox_list').html("<img src='"+symposium.images_url+"/busy.gif' />");

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

						jQuery('#messagebox').html("<img src='"+symposium.images_url+"/busy.gif' />");

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
		
		jQuery('#mailbox_list').html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery('#messagebox').html("<img src='"+symposium.images_url+"/busy.gif' />");

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
   		
		jQuery('#messagebox').html("<img src='"+symposium.images_url+"/busy.gif' />");

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

				  	// Prepare ColorBox
				  	if (jQuery(".wps_profile_album").length) {
					  	var list = new Array();
						jQuery(".wps_profile_album").each(function(index) {
							var rel = jQuery(this).attr("rel");
							if (jQuery.inArray(rel, list) == -1) {
								list.push(rel);
						      	jQuery("a[rel='"+rel+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
							}
						});  	
				  	}
					
				}
	   		});
   		
	}
	
	jQuery('.symposium_wall_replies').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
			jQuery("#symposium_reply_div_"+jQuery(this).attr("id")).hide().slideDown("fast");
	  } else {
			jQuery("#symposium_reply_div_"+jQuery(this).attr("id")).slideUp("fast");
	  }
	});

	// Show mail/delete link on friend hover
	jQuery('.friend_div').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
			jQuery(this).find(".friend_icons").show();
	  } else {
        	jQuery(this).find(".friend_icons").hide();
	  }
	});
	 
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
		jQuery(this).html("<img src='"+symposium.images_url+"/busy.gif' />");

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

				// Prepare ColorBox
			  	if (jQuery(".wps_profile_album").length) {
				  	var list = new Array();
					jQuery(".wps_profile_album").each(function(index) {
						var rel = jQuery(this).attr("rel");
						if (jQuery.inArray(rel, list) == -1) {
							list.push(rel);
					      	jQuery("a[rel='"+rel+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
						}
					});  	
			  	}
			}
   		});		

	});
	
	// Menu choices
	jQuery(".symposium_profile_menu").click(function(){
				
		var menu_id = jQuery(this).attr("id");
		jQuery('#profile_body').html("<img src='"+symposium.images_url+"/busy.gif' />");

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
				
				jQuery('#profile_body').html(str);

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
			   	

				// Prepare ColorBox
			  	if (jQuery(".wps_profile_album").length) {
				  	var list = new Array();
					jQuery(".wps_profile_album").each(function(index) {
						var rel = jQuery(this).attr("rel");
						if (jQuery.inArray(rel, list) == -1) {
							list.push(rel);
					      	jQuery("a[rel='"+rel+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
						}
					});  	
			  	}

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
			jQuery(this).find(".report_post_top").show();
			jQuery(this).find(".delete_post_top").show();
	  } else {
        	jQuery(this).find(".report_post_top").hide();
        	jQuery(this).find(".delete_post_top").hide();
	  }
	});
    
	// Show delete link on reply hover
	jQuery('.wall_reply').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
	        jQuery(this).find(".report_post").show();
	        jQuery(this).find(".delete_reply").show();
	  } else {
	        jQuery(this).find(".report_post").hide();
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

			var comment_id = jQuery(this).attr("title");
			if (jQuery(this).attr("rel") == 'post') {
				jQuery('#post_'+comment_id).slideUp("slow");
			} else {
				jQuery('#'+comment_id).slideUp("slow");
			}
			
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
					if (str.substring(0, 4) == 'FAIL') { 
						alert("delete_post:"+str);
					}
				},
				error: function(err){
					//alert("P1:"+err);
				}		
	   		});		
			
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
		
		if (comment_text != '' && comment_text != jQuery('#symposium_whatsup').html()) {
		
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
				async: true,
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
	jQuery("#addasfriend").live('click', function() { addasfriend(this); });
	jQuery(".addasfriend").live('click', function() { addasfriend(this); });
	jQuery('#addfriend').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			addasfriend(this);
		}
	});
	jQuery('.addfriend_text').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			addasfriend(this);
		}
	});
	
	function addasfriend(id) {
		var uid = jQuery(id).attr("title");
		
		jQuery("#addasfriend_done1_"+uid).hide();
		jQuery("#addasfriend_done2_"+uid).slideDown("fast");
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"addFriend",
				friend_to:uid,
				friend_message:jQuery('#addtext_'+uid).val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#dialog").html('Your friend request has been sent.');
				jQuery("#dialog").dialog({ title: 'Friend request', width: 600, height: 225, modal: true,
				buttons: {
						"OK": function() {
							jQuery("#dialog").dialog('close');
						}
					}
				});							
			},
			error: function(err){
				//alert("P6:"+err);
			}		
   		});
   			
   	};			

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
	   |                                       PROFILE PLUS                                       |
	   +------------------------------------------------------------------------------------------+
	*/
	
	jQuery('.symposium-follow').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter' && symposium.current_user_id > 0) {
	    var display_name = jQuery(this).attr("title");
	    var id = jQuery(this).attr("id");
	    var rel = jQuery(this).attr("rel");
	    var rev = jQuery(this).attr("rev");
	    var src = jQuery(this).attr("src");

		var xPos = event.pageX - (jQuery('#symposium-follow-box').width() / 2);
		var yPos = event.pageY - (jQuery('#symposium-follow-box').height() / 2);
		var html = '<img id="symposium_plus_box_avatar" src="'+src+'" style="width:20px;height:20px;float:right;" />';
		html += '<a style="font-size:14px;" href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+id+'">'+display_name+'</a><br />';
		if (rel == 'friend' || id == symposium.current_user_id) {
			if (strpos(symposium.mail_url, 'INVALID PLUGIN URL REQUESTED') === false && id != symposium.current_user_id) {
				html += '<input id="symposium_plus_sendmail" ref="'+id+'" type="text" style="padding-left:18px; background: transparent url(\''+symposium.images_url+'/mail_small.png\') 2px 2px no-repeat;margin-top:4px;margin-bottom:8px;height:14px;font-size:12px;line-height:12px;width:90%;"  onblur = "this.value=(this.value==\'\') ? \''+jQuery('#symposium_sendmail').html()+'\' : this.value;"  onfocus= "this.value=(this.value==\''+jQuery('#symposium_sendmail').html()+'\') ? \'\' : this.value;"  value  = "'+jQuery('#symposium_sendmail').html()+'" />';
			} else {
				html += '<input id="symposium_plus_post" type="text" style="padding-left:18px; background: transparent url(\''+symposium.images_url+'/bubble.png\') 2px 2px no-repeat;margin-top:4px;margin-bottom:8px;height:14px;font-size:12px;line-height:12px;width:90%;"  onfocus= "this.value= \'\'"  value  = "'+jQuery('#symposium_whatsup').html()+'" />';
			}
		} else {
			if (rel == 'pending') {
				html += '<div style="margin-top:4px;margin-bottom:8px;"><a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'view=friends">'+jQuery('#symposium_friendpending').html()+'</a></div>';
			} else {
				html += '<input id="symposium_plus_addasafriend" title="'+id+'" type="text" style="padding-left:18px; background: transparent url(\''+symposium.images_url+'/add_small.png\') 2px 3px no-repeat;margin-top:4px;margin-bottom:8px;height:14px;font-size:12px;line-height:12px;width:90%;"  onblur = "this.value=(this.value==\'\') ? \''+jQuery('#symposium_addasafriend').html()+'...\' : this.value;"  onfocus= "this.value=(this.value==\''+jQuery('#symposium_addasafriend').html()+'...\') ? \'\' : this.value;"  value  = "'+jQuery('#symposium_addasafriend').html()+'..." />';
			}
		}
		html += "<img id='symposium_plus_profile' ref='"+id+"' title='"+jQuery('#symposium_profile_info').html()+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/profile.png' />";
		html += "<img id='symposium_plus_friends' ref='"+id+"' title='"+jQuery('#symposium_plus_friends').html()+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/friends.png' />";
		if (id == symposium.current_user_id) {
			html += "<img id='symposium_following_who' title='"+jQuery('#symposium_plus_follow_who').html()+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/fav-who.png' />";
			html += "<img id='symposium_plus_mail' title='"+jQuery('#symposium_plus_mail').html()+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/mail.png' />";
		} else {
			if (rev == 'following') {
				html += "<img id='symposium_following' title='"+jQuery('#symposium_unfollow').html()+"' ref='"+id+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/fav-on.png' />";
			} else {
				html += "<img id='symposium_following' title='"+jQuery('#symposium_follow').html()+"' ref='"+id+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/fav-off.png' />";
			}
			if (symposium.wps_use_poke) {
				html += "<img id='symposium_attention' title='"+jQuery('#symposium_attention').html()+"' ref='"+id+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/attention.png' />";
			}
		}
		if (strpos(symposium.forum_url, 'INVALID PLUGIN URL REQUESTED') === false) {
			html += "<img id='symposium_forum_search' title='"+jQuery('#symposium_forumsearch').html()+"' rel='"+display_name+"' ref='"+id+"' style='float:left; margin-right:5px; cursor:pointer' src='"+symposium.images_url+"/search2.png' />";
		}
		if (symposium.gallery_url != '') {
			html += '<div style="float:left; margin-right:5px;"><img style="cursor:pointer" rel="'+display_name+'" id="symposium_gallery_search" src="'+symposium.images_url+'/gallery.png" title="'+jQuery('#symposium_gallerysearch').html()+'" /></a></div>';
		}
		
		jQuery('#symposium-follow-box').html(html);
		jQuery('#symposium-follow-box').css({'z-index':99999, 'position':'absolute', 'top':yPos,'left':xPos}).show();

	  }

	});
	jQuery('#symposium-follow-box').live('mouseenter mouseleave', function(event) {
		if (event.type == 'mouseleave') {
	    	jQuery(this).hide();
		}
	});
	
	// Go to friends page
	jQuery("#symposium_plus_friends").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		window.location = symposium.profile_url+symposium.q.substring(0, 1)+'uid='+jQuery(this).attr("ref")+'&view=friends';
	});
	
	// Go to profile information page
	jQuery("#symposium_plus_profile").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		window.location = symposium.profile_url+symposium.q.substring(0, 1)+'uid='+jQuery(this).attr("ref")+'&view=extended';
	});

	// Post a status on pressing return
	jQuery('#symposium_plus_post').live('keypress', function (e) {
		if ( e.keyCode == 13 ){			
			jQuery(".symposium_pleasewait").inmiddle().show();
			var message = jQuery(this).val().replace(/(<([^>]+)>)/ig, '');
			jQuery(this).val('');

			if (message != '' && message != jQuery('#symposium_whatsup').html()) {
			
				// Add to wall if on page
				if (jQuery("#symposium_wall").css("display") != 'none' && symposium.current_user_page == symposium.current_user_id) {
					var comment = "<div class='add_wall_post_div'>";
					comment = comment + "<div class='add_wall_post'>";
					comment = comment + "<div class='add_wall_post_text'>";
					comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
					comment = comment + symposium.current_user_display_name+'</a><br />';
					comment = comment + message;
					comment = comment + "</div>";
					comment = comment + "</div>";			
					comment = comment + "<div class='add_wall_post_avatar'>";
					comment = comment + "<img src='"+jQuery('#symposium_current_user_avatar img:first').attr('src')+"' style='width:64px; height:64px' />";
					comment = comment + "</div>";	
					comment = comment + "</div>";
					jQuery(comment).prependTo('#symposium_wall');
				}

				// Update status
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
					type: "POST",
					data: ({
						action:"addStatus",
						text:message,
						facebook:0
					}),
				    dataType: "html",
					async: true,
					success: function(str){
						jQuery(".symposium_pleasewait").hide();
						jQuery('.symposium-follow-box').hide();

						if (symposium.current_user_page != symposium.current_user_id || jQuery("#symposium_wall").length == 0 ) {
							jQuery("#dialog").html(jQuery('#symposium_whatsup_done').html()+'<br /><br />'+message);
							jQuery("#dialog").dialog({ title: symposium.site_title, width: 300, height: 250, modal: true,
							buttons: {
									"OK": function() {
										jQuery("#dialog").dialog('close');
									},
									"View": function() {
										jQuery(".symposium_pleasewait").inmiddle().show();
										window.location = symposium.profile_url;
									}
								}
							});
						}
					},
					error: function(err){
						alert("symposium_plus_post:"+err);
					}
		   		});
		

			}
	   		
		}
	});	  
		
	// Following who?
	jQuery("#symposium_following_who").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		window.location = symposium.profile_url+symposium.q.substring(0, 1)+'view=plus';
	});
		
	// Go to mail
	jQuery("#symposium_plus_mail").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		window.location = symposium.mail_url;
	});
	
	// Gallery search
	jQuery("#symposium_gallery_search").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		window.location = symposium.gallery_url+symposium.q.substring(0, 1)+'term='+jQuery(this).attr("rel");
	});
	
	// Forum search
	jQuery("#symposium_forum_search").live('click', function() {
		do_show_search();
		jQuery("#search-box-input").val(jQuery(this).attr("rel"));
		do_forum_search();
	});

	// Grab attention
	jQuery("#symposium_attention").live('click', function() {
		var avatar = "<img style='float:left; width:48px; height:48px;margin-right:5px;' src='"+jQuery('#symposium_plus_box_avatar').attr("src")+"' />";
		jQuery("#dialog").html(avatar+jQuery('#symposium_sent').html());
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 200, height: 175, modal: true,
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
				recipient:jQuery(this).attr("ref")
			}),
		    dataType: "html",
			async: true,
			success: function(str){
			}
   		});
	});
		
	// Toggle following
	jQuery("#symposium_following").live('click', function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		if (jQuery(this).attr("src") == symposium.images_url+'/fav-on.png') {
			// Remove from following
			jQuery(this).attr("src", symposium.images_url+'/fav-off.png');
 			jQuery.ajax({
	      		url: symposium.plugin_pro_url+"plus/ajax/symposium_plus_functions.php", 
	      		type: "POST",
	      		data: ({
	       			action:'toggle_following',
					following:jQuery(this).attr("ref")
	      		}),
	      		dataType: "html",
	      		async: false
	    	});
		} else {
			// Add to following
			jQuery(this).attr("src", symposium.images_url+'/fav-on.png');
 			jQuery.ajax({
	      		url: symposium.plugin_pro_url+"plus/ajax/symposium_plus_functions.php", 
	      		type: "POST",
	      		data: ({
	       			action:'toggle_following',
					following:jQuery(this).attr("ref")
	      		}),
	      		dataType: "html",
	      		async: false
	    	});
		}
		// var loc = window.location.href;
		// if view can be updated from profile menu items, then page would return to current view
		// if (symposium.view != '') { loc += symposium.q+'view='+symposium.view; }
		location.reload();
	});	
	
   	// Send mail on pressing return
	jQuery('#symposium_plus_sendmail').live('keypress', function (e) {
		if ( e.keyCode == 13 ){			
			jQuery(".symposium_sending").inmiddle().show();
			var recipient_id = jQuery(this).attr("ref");
			var message = jQuery(this).val().replace(/(<([^>]+)>)/ig, '');
			jQuery(this).val('');
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
				type: "POST",
				data: ({
					action:"sendMail",
					compose_recipient_id:recipient_id,
					compose_subject:jQuery('#symposium_privatemail').html(),
					compose_text:message,
					compose_previous:''
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					jQuery(".symposium_sending").hide();
					jQuery('.symposium-follow-box').hide();
					var avatar = "<img style='float:left; width:48px; height:48px;margin-right:10px;' src='"+jQuery('#symposium_plus_box_avatar').attr("src")+"' />";
					jQuery("#dialog").html(avatar+message);
					jQuery("#dialog").dialog({ title: jQuery('#symposium_privatemailsent').html(), width: 300, height: 250, modal: true,
					buttons: {
							"OK": function() {
								jQuery("#dialog").dialog('close');
							}
						}
					});
				},
				error: function(err){
					//alert("symposium_plus_sendmail:"+err);
				}		
	   		});	
	   		
		}
	});	  

   	// Add as a friend on pressing return
	jQuery('#symposium_plus_addasafriend').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			jQuery(".symposium_pleasewait").inmiddle().show();
			addasfriend(this);
			jQuery(".symposium_pleasewait").hide();
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

					if (strpos(str, "[|]", 0) ) {
						var details=str.split("[|]");
						jQuery(document).attr('title', details[0]);
						str = details[1];
					}	
									
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
	
					if (jQuery().colorbox) {
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
		jQuery("<img id='symposium_tmp' src='"+symposium.images_url+"/busy.gif' />").prependTo(this);

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
		jQuery("<img id='symposium_tmp' src='"+symposium.images_url+"/tick.png' />").prependTo(this);
   		
	});	

	// Remove uploaded image
	jQuery(".remove_forum_image").live('click', function() {
		
		var folder = jQuery(this).attr("id");
		var file = jQuery(this).attr("title");
		var me = this;
		jQuery(this).attr('src', symposium.images_url+"/busy.gif");
		
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
	
	// Warning
	jQuery(".symposium_report").live('click', function() {

		var code = jQuery(this).attr('title');
		var str = '<p>Please provide as much information about your report to the site administrator as possible.<br />';
		str += '<em>Ref: '+code+'</em></p>';
		str += '<textarea id="report_text" style="width:100%; height:200px"></textarea>';
		jQuery("#dialog").html(str);
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 400, modal: true,
		buttons: {
				"Report": function() {

					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_ajax_functions.php", 
						type: "POST",
						data: ({
							action:"sendReport",
							report_text:jQuery('#report_text').val(),
							code:code
						}),
						dataType: "html",
						async: true,
						success: function(str){
							jQuery("#dialog").html('Your report has been sent to the site administrator.');
							jQuery("#dialog").dialog({ title: symposium.site_title, width: 650, height: 150, modal: true, buttons: {}  });
						},
						error: function(err){
							//alert("symposium_report:"+err);
						}		
					});
					jQuery(this).dialog("close");
				},
				"Cancel": function() {
					jQuery(this).dialog("close");
				}
			}
		});

	});
		
	// Share permalink
	jQuery("#share_permalink").live('click', function() {
		var str = 'Copy and Paste the following:';
		str += '<br /><input type="text" style="width:550px;" value="'+jQuery(this).attr("title")+'" />';
		jQuery("#dialog").html(str);
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 650, height: 150, modal: true, buttons: {}  });
	});
	
	// Clicked on show more...
	jQuery("#showmore_forum").live('click', function() {
		
		var details = jQuery(this).attr("title").split(",");
		limit_from = details[0];
		cat_id = details[1];
		
		jQuery('#showmore_forum').html("<img src='"+symposium.images_url+"/busy.gif' />");

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

				if (strpos(str, "[|]", 0) ) {
					var details=str.split("[|]");
					jQuery(document).attr('title', details[0]);
					str = details[1];
				}	

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
				//window.scrollTo(0,0);
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
				if (strpos(str, "[|]", 0) ) {
					var details=str.split("[|]");
					jQuery(document).attr('title', details[0]);
					str = details[1];
				}
			
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

				if (jQuery().colorbox) {
    	      		jQuery("a[rel='symposium_forum_images-"+topic_id+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});						
				}
				//window.scrollTo(0,0);
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
   		
		if (jQuery('#fav_link').attr('src') == symposium.images_url+'/fav-on.png' ) {
			jQuery('#fav_link').attr({ src: symposium.images_url+'/fav-off.png' });
		} else {
			jQuery('#fav_link').attr({ src: symposium.images_url+'/fav-on.png' });
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

		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 500, modal: true, buttons: {}  });
		
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

		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 500, modal: true, buttons: {}  });

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

		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 500, modal: true, buttons: {}  });

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
		
		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 500, modal: true, buttons: {}  });
		
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
		do_show_search();
	});
	function do_show_search() {
		var search_form = "<div id='search-box' style='clear:both;margin-top:6px;'>";
		search_form += "<input type='text' id='search-box-input' style='width:50%; float: left; ' />";
		search_form += "<input type='submit' class='symposium-button' style='margin-top:2px; margin-left:10px;' id='search-box-button' value='Go' />";
		search_form += "</div>";
		search_form += "<div id='search-internal' style='clear:both;padding-left:6px;'></div>";
		
		jQuery("#dialog").html(search_form);
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 500, modal: true, buttons: {}  });
	}
			
   	// Do search on pressing return
	jQuery('#search-box-input').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			do_forum_search();
		}
	});
   	// Do search on pressing button
	jQuery("#search-box-button").live('click', function() {
			do_forum_search();
	});
	
	function do_forum_search() {
		jQuery("#search-internal").html("<img src='"+symposium.images_url+"/busy.gif' />");
		
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
	}
   	
	// Edit topic (AJAX)
	jQuery('#starting-post').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
        jQuery(this).find(".symposium_report").show();
        jQuery(this).find("#edit-this-topic").show();
	  } else {
        jQuery(this).find(".symposium_report").hide();
        jQuery(this).find("#edit-this-topic").hide();
	  }
	});

	// Edit the topic
	jQuery("#edit-this-topic").live('click', function() {
	
    	var tid = jQuery(this).attr("title");	
		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 400, modal: true,
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
		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 400, modal: true,
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
			
			// Default to answered?
			var answered = '';
	        if(jQuery('#quick-reply-answer').is(":checked")) {
				var answered = 'on';
			}	
						
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
				type: "POST",
				data: ({
					action:"reply",
					'tid':jQuery('#symposium_reply_tid').val(),
					'cid':jQuery('#symposium_reply_cid').val(),
					'reply_text':reply_text,
					'group_id':symposium.current_group,
					'answered':answered
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
        jQuery(this).find(".symposium_report").show();
        jQuery(this).find(".delete_forum_reply").show();
        jQuery(this).find(".edit_forum_reply").show();
	  } else {
        jQuery(this).find(".symposium_report").hide();
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
			
				jQuery('#forum_score_'+tid).html("<img src='"+symposium.images_url+"/busy.gif' />");
	
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
			}
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
			if (symposium.current_user_id > 0) {
				
				// Clear locking cookies
				eraseCookie('wps_bar_check');
				eraseCookie('wps_chat_check');
				eraseCookie('wps_chatroom_check');			
			   	
				// Check for notifications, unread mail, friend requests, etc
				bar_polling();
				if (symposium.wps_lite != 'on') {
					chat_polling();
				}
			}

			if (symposium.wps_lite != 'on') {
				
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
		jQuery(this).attr('value', 'Working...').attr("disabled", true);
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
				location.reload();
			}
   		});				
   	});

	// Installation Page (Add to existing)
	jQuery(".symposium_addtopage").click(function() {
		var shortcode = jQuery(this).attr("title");
		var value = jQuery('#symposium_pagechoice_'+shortcode).val();  

		jQuery(this).attr('value', 'Working...').attr("disabled", true);
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
			jQuery('#symposium_import_file_pleasewait').html("<img src='"+symposium.images_url+"/busy.gif' />");
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
									jQuery('#chat'+w+'_display_name').html('<img src="'+symposium.images_url+'/'+status+'_header.gif" /> '+name); 
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
	do_online_friends_check();
	if (symposium.wps_lite != 'on') {
		setTimeout(bar_polling, symposium.bar_polling*1000);
	}
}
function chat_polling() {
	do_chat_check();
	do_chatroom_check();
	setTimeout(chat_polling, symposium.chat_polling*1000);
}

// Password strength
(function(A){A.extend(A.fn,{pstrength:function(B){var B=A.extend({verdects:["Very weak","Weak","Medium","Strong","Very strong"],colors:["#f00","#c06","#f60","#3c0","#3f0"],scores:[10,15,30,40],common:["password","sex","god","123456","123","welcome","test","qwerty","admin"],minchar:6},B);return this.each(function(){var C=A(this).attr("id");A(this).after("<div class=\"pstrength-info\" id=\""+C+"_text\"></div>");A(this).after("<div class=\"pstrength-bar\" id=\""+C+"_bar\" style=\"border: 1px solid white; font-size: 1px; height: 5px; width: 0px;\"></div>");A(this).keyup(function(){A.fn.runPassword(A(this).val(),C,B)})})},runPassword:function(D,F,C){nPerc=A.fn.checkPassword(D,C);var B="#"+F+"_bar";var E="#"+F+"_text";if(nPerc==-200){strColor="#f00";strText="Unsafe password word!";A(B).css({width:"0%"})}else{if(nPerc<0&&nPerc>-199){strColor="#ccc";strText="Too short";A(B).css({width:"5%"})}else{if(nPerc<=C.scores[0]){strColor=C.colors[0];strText=C.verdects[0];A(B).css({width:"10%"})}else{if(nPerc>C.scores[0]&&nPerc<=C.scores[1]){strColor=C.colors[1];strText=C.verdects[1];A(B).css({width:"25%"})}else{if(nPerc>C.scores[1]&&nPerc<=C.scores[2]){strColor=C.colors[2];strText=C.verdects[2];A(B).css({width:"50%"})}else{if(nPerc>C.scores[2]&&nPerc<=C.scores[3]){strColor=C.colors[3];strText=C.verdects[3];A(B).css({width:"75%"})}else{strColor=C.colors[4];strText=C.verdects[4];A(B).css({width:"92%"})}}}}}}A(B).css({backgroundColor:strColor});A(E).html("<span style='color: "+strColor+";'>"+strText+"</span>")},checkPassword:function(C,B){var F=0;var E=B.verdects[0];if(C.length<B.minchar){F=(F-100)}else{if(C.length>=B.minchar&&C.length<=(B.minchar+2)){F=(F+6)}else{if(C.length>=(B.minchar+3)&&C.length<=(B.minchar+4)){F=(F+12)}else{if(C.length>=(B.minchar+5)){F=(F+18)}}}}if(C.match(/[a-z]/)){F=(F+1)}if(C.match(/[A-Z]/)){F=(F+5)}if(C.match(/\d+/)){F=(F+5)}if(C.match(/(.*[0-9].*[0-9].*[0-9])/)){F=(F+7)}if(C.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)){F=(F+5)}if(C.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)){F=(F+7)}if(C.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)){F=(F+2)}if(C.match(/([a-zA-Z])/)&&C.match(/([0-9])/)){F=(F+3)}if(C.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)){F=(F+3)}for(var D=0;D<B.common.length;D++){if(C.toLowerCase()==B.common[D]){F=-200}}return F}})})(jQuery)



	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        ALERTS                                            |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("#symposium_alerts").length) {
		
		// Start regular checks for lounge contents
	   	if (symposium.is_admin == 0) {
			news_polling();
	   	}
	
		// Show/hide news events as drop down below menu item
		jQuery('#symposium_alerts').live('mouseenter', function(event) {
			// Only show if list is present
			if(!(jQuery('#symposium_news_items').is(':visible'))) {	
				if (event.type == 'mouseenter') {
					jQuery("#symposium_news_items").show();
	
					jQuery.ajax({
						url: symposium.plugin_pro_url+"alerts/ajax/symposium_news_functions.php", 
						type: "POST",
			      			data: ({
			    				action:'clear_read_news'
				      		}),
			     			dataType: "html",
			     			success: function(str){
				      		},
						error: function(err){
							// Uncomment to receive any AJAX errors, sometimes these are rogue due to network issues
							// alert(err);
						}
					});
				}
			}
		});
		jQuery('#symposium_alerts').live('mouseleave', function(event) {
			if (event.type == 'mouseleave') {
				jQuery("#symposium_news_items").hide();
				jQuery("#symposium_news_highlight").remove();
			}
		});
	
	}
		
function news_polling() {

   	// Don't do anything if is_admin
   	if (symposium.is_admin == 0) {

		// Don't poll if showing drop-down list of items
		if(!(jQuery('#symposium_news_items').is(':visible'))) {	

			var news_items = '';

			jQuery.ajax({
				url: symposium.plugin_pro_url+"alerts/ajax/symposium_news_functions.php", 
				type: "POST",
	      			data: ({
    				action:'get_news'
	      		}),
     			dataType: "html",
     			success: function(str){
	
					jQuery("#symposium_news_items").remove();
					if (str != '[]') {

						var rows = jQuery.parseJSON(str);

						var items = "<div id='symposium_news_items' style='display:none; left:inherit; top:inherit;'>";
						
						var row_count = 0;
						var new_count = 0;
						var url = '';
				
			      		jQuery.each(rows, function(i,row) {
								if (row.nid > 0) {
									if (row.new_item == 'on') { new_count++; }
									if (row_count < 10 ) {
										row_count++;
										items += "<div class='symposium_news_item'>";
										if (row.new_item == 'on') {
											items += "<span class='symposium_news_item_newitem'>";
										}
										items += stripslashes(row.news);
										if (row.new_item == 'on') {
											items += "</span>";
										}
										items += "</div>";
									}
								} else {
									url = row.news;
								}
						});
						
						if (url != '') {
							items += "<div class='symposium_news_item'>";
							items += "<a style='float;right' href='"+url+"'>&#8658</a>";
							items += "</div>";
						}
						
						items += '</div>';

						if (new_count > 0) {
							if(jQuery("#symposium_news_highlight").length > 0) {
								jQuery("#symposium_news_highlight").html(new_count);	
							} else {
								jQuery('<span id="symposium_news_highlight">'+new_count+'</span>').appendTo('#symposium_alerts');
							}
						} else {
							jQuery("#symposium_news_highlight").hide();
						}
						jQuery(items).appendTo('#symposium_alerts');
						var symposium_news_x_offset = jQuery("#symposium_news_x_offset").html();
						var symposium_news_y_offset = jQuery("#symposium_news_y_offset").html();
						jQuery("#symposium_news_items").css("margin-left", (symposium_news_x_offset-17)+"px");
						jQuery("#symposium_news_items").css("margin-top", (symposium_news_y_offset-44)+"px");
					}
	      		},
				error: function(err){
					// Uncomment to receive any AJAX errors, sometimes these are rogue due to network issues
					//alert(err);
				}
			});

		}

	}
	
   	// Repeat check every 5 seconds
   	if (symposium.wps_lite != 'on') {
	   	var polling = jQuery('#symposium_news_polling').html();
   		if (polling < 1) { polling = 5; }
	   	setTimeout(news_polling, polling*1000);
   	}

}

function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
return str;
}
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        EVENTS                                            |
	   +------------------------------------------------------------------------------------------+
	*/
	
	// Act on default view on profile page being for Events
	if (symposium.view == 'wps_events' && symposium.embed == 'on') {

		jQuery('#profile_body').html("<img src='"+symposium.images_url+"/busy.gif' />"); 

		var menu_id = 'menu_events';
		var ajax_path = symposium.plugin_pro_url+"events/ajax/symposium_events_functions.php";

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
			}
   		});
	}
	
	/* Show/hide edit and delete icons */
	jQuery('.symposium_event_list_item').live('mouseenter mouseleave', function(event) {
	  if (event.type == 'mouseenter') {
	        jQuery(this).find(".link_cursor").show();
	  } else {
	        jQuery(this).find(".link_cursor").hide();
	  }
	});

	/* Create button */
	jQuery("#symposium_create_event_button").live('click', function() {
		jQuery("#symposium_create_event_button").hide();
		jQuery("#symposium_events_list").hide();
		jQuery("#symposium_create_event_form").show();
		jQuery(".datepicker").datepicker({showButtonPanel: true});		
	});

	/* Cancel button */
	jQuery("#symposium_cancel_event_button").live('click', function() {
		jQuery("#symposium_create_event_button").show();
		jQuery("#symposium_events_list").show();
		jQuery("#symposium_create_event_form").hide();
	});

	/* Create (save) button */
	jQuery("#symposium_add_event_button").live('click', function() {
		var name = jQuery("#symposium_create_event_name").val().replace(/(<([^>]+)>)/ig, '');
		if (name == '') {
			jQuery("#symposium_create_event_name").css('border', '1px solid red').effect("highlight", {}, 4000);
		} else {
			// submit to database
			var desc = jQuery("#symposium_create_event_desc").val().replace(/(<([^>]+)>)/ig, '');
			var location = jQuery("#symposium_create_event_location").val().replace(/(<([^>]+)>)/ig, '');
			var start_date = jQuery("#event_start").val();
			var end_date = jQuery("#event_end").val();
			var start_hours = jQuery("#event_start_time_hours").val();
			var start_minutes = jQuery("#event_start_time_minutes").val();
			var end_hours = jQuery("#event_end_time_hours").val();
			var end_minutes = jQuery("#event_end_time_minutes").val();

			jQuery.ajax({
				url: symposium.plugin_pro_url+"events/ajax/symposium_events_functions.php", 
				type: "POST",
				data: ({
					action:"addEvent",
					'name':name,
					'desc':desc,
					'location':location,
					'start_date':start_date,
					'start_hours':start_hours,
					'start_minutes':start_minutes,
					'end_date':end_date,
					'end_hours':end_hours,
					'end_minutes':end_minutes
				}),
				dataType: "html",
				async: true,
				success: function(str){
					if (str == 'OK') {
						jQuery(".symposium_pleasewait").inmiddle().show();
						var reload_page = symposium.profile_url+symposium.q.substring(0, 1)+"uid="+symposium.current_user_page+"&embed=on&view=wps_events";
						window.location.href=reload_page;
					} else {
						alert(str);
					}
				},
				error: function(err){
					//alert("addEvent:"+err);
				}		
			});		
		}
	});
	
	/* Delete event */
	jQuery(".symposium_delete_event").live('click', function() {

	  	var answer = confirm(areyousure);
	  	if (answer) {
			var event_id = jQuery(this).attr("id");
			jQuery(this).parent().parent().slideUp("slow");

			jQuery.ajax({
				url: symposium.plugin_pro_url+"events/ajax/symposium_events_functions.php", 
				type: "POST",
				data: ({
					action:"deleteEvent",
					'eid':event_id
				}),
				dataType: "html",
				async: true,
				success: function(str){
					if (str != 'OK') {
						alert(str);
					}
				},
				error: function(err){
					//alert("deleteEvent:"+err);
				}		
			});		
		}
	});	

	// Edit event
	jQuery(".symposium_edit_event").live('click', function() {

		jQuery("#dialog").html("<img src='"+symposium.images_url+"/busy.gif' />");
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 850, height: 550, modal: true, buttons: {}  });

		var event_id = jQuery(this).attr("id");

		jQuery.ajax({
			url: symposium.plugin_pro_url+"events/ajax/symposium_events_functions.php", 
			type: "POST",
			data: ({
				action:"editEvent",
				eid:event_id
			}),
			dataType: "html",
			async: false,
			success: function(str){
				var rows = jQuery.parseJSON(str);
				var html = '<div id="symposium_edit_event_eid" style="display:none">'+event_id+'</div>';
				html += '<div id="symposium_edit_event">';
		        jQuery.each(rows, function(i,row){

					html += "<input type='text' id='symposium_edit_event_name' class='symposium_edit_event_input' value='"+row.event_name.replace(/'/g, '&apos;')+"'><br />";
					html += "<input type='text' id='symposium_edit_event_location' class='symposium_edit_event_input' value='"+row.event_location.replace(/'/g, '&apos;')+"'><br />";
					html += "<textarea type='text' id='symposium_edit_event_desc' class='symposium_edit_event_textarea'>"+row.event_description.replace(/'/g, '&apos;')+"</textarea><br /><br />";
					html += '<div style="float:left;">';
						html += '<input type="text" id="symposium_edit_event_start" style="margin-left:5px; width:120px" class="datepicker" value="';
							if (row.start_date != '01/01/1970' && row.start_date != '11/30/-0001') { html += row.start_date };
							html += '" /> ';
						html += '<select id="symposium_edit_event_start_time_hours">';
						html += '<option value=99>-</option>';
					 	for(i=0;i<=23;i++){
							html += '<option value='+i;
							if (i==row.start_hours) { html += ' SELECTED'; }
							html += '>'+i+'</option>';
						}
						html += '</select> : ';
						html += '<select id="symposium_edit_event_start_time_minutes">';
						html += '<option value=99>-</option>';
					 	for(i=0;i<=3;i++){
							html += '<option value='+(i*15);
							if (i*15==row.start_minutes) { html += ' SELECTED'; }
							html += '>'+(i*15)+'</option>';
						}
						html += '</select>';
						html += ' &rarr; ';
						html += '<input type="text" id="symposium_edit_event_end" style="width:120px" class="datepicker" value="';
							if (row.end_date != '01/01/1970' && row.end_date != '11/30/-0001') { html += row.end_date };
							html += '" /> ';
						html += '<select id="symposium_edit_event_end_time_hours">';
						html += '<option value=99>-</option>';
					 	for(i=0;i<=23;i++){
							html += '<option value='+i;
							if (i==row.end_hours) { html += ' SELECTED'; }
							html += '>'+i+'</option>';
						}
						html += '</select> : ';
						html += '<select id="symposium_edit_event_end_time_minutes">';
						html += '<option value=99>-</option>';
					 	for(i=0;i<=3;i++){
							html += '<option value='+(i*15);
							if (i*15==row.end_minutes) { html += ' SELECTED'; }
							html += '>'+(i*15)+'</option>';
						}
						html += '</select>';
					html += '</div>';
					html += '<div style="clear: both; float: left; margin-top: 10px; margin-left:5px;">';
						html += '<input id="symposium_event_update_status" type="checkbox" ';
						if (row.event_live == 'on') { html += 'CHECKED'; }
						html += ' /> Published?';
						html += '<input style="margin-left:25px" id="event_google_map_status" type="checkbox" ';
						if (row.event_google_map == 'on') { html += 'CHECKED'; }
						html += ' /> Google Map?';
					html += '</div>';
					html += '<div style="clear: both; float: left; margin-top: 10px; margin-left:5px;">';
						html += '<input id="symposium_event_update_button" type="submit" class="symposium-button" value="Update" />';
						html += '<input id="symposium_event_cancel_button" type="submit" class="symposium-button" value="Cancel" />';
						html += '<div style="float: right;" id="symposium_edit_wait"></div>';
					html += '</div>';
				});
				html += '</div>';
				jQuery('#dialog').html(html);
				jQuery(".datepicker").datepicker({showButtonPanel: true});		
			},
			error: function(err){
				alert("deleteEvent:"+err);
			}		
		});		
   	});

	// Cancel event
	jQuery("#symposium_event_cancel_button").live('click', function() {
		jQuery("#dialog").dialog('close');
	});

	// Update event
	jQuery("#symposium_event_update_button").live('click', function() {

		jQuery(".symposium_pleasewait").inmiddle().show();

		var eid = jQuery('#symposium_edit_event_eid').html();
		var name = jQuery('#symposium_edit_event_name').val();
		var location = jQuery('#symposium_edit_event_location').val();
		var google_map = jQuery('#symposium_edit_event_location').val();
		var desc = jQuery('#symposium_edit_event_desc').val();
		var start = jQuery('#symposium_edit_event_start').val();
		var start_hours = jQuery('#symposium_edit_event_start_time_hours').find(':selected').val();
		var start_minutes = jQuery('#symposium_edit_event_start_time_minutes').find(':selected').val();
		var end = jQuery('#symposium_edit_event_end').val();
		var end_hours = jQuery('#symposium_edit_event_end_time_hours').find(':selected').val();
		var end_minutes = jQuery('#symposium_edit_event_end_time_minutes').find(':selected').val();
		var event_live = '';
		if (jQuery('#symposium_event_update_status:checked').val() == 'on') { event_live = 'on'; }
		var google_map = '';
		if (jQuery('#event_google_map_status:checked').val() == 'on') { google_map = 'on'; }

		jQuery.ajax({
			url: symposium.plugin_pro_url+"events/ajax/symposium_events_functions.php", 
			type: "POST",
			data: ({
				action:"updateEvent",
				'eid':eid,
				'name':name,
				'location':location,
				'google_map':google_map,
				'desc':desc,
				'start_date':start,
				'start_hours':start_hours,
				'start_minutes':start_minutes,
				'end_date':end,
				'end_hours':end_hours,
				'end_minutes':end_minutes,
				'event_live':event_live
			}),
			dataType: "html",
			async: true,
			success: function(str){
				if (str == 'OK') {
					var reload_page = symposium.profile_url+symposium.q.substring(0, 1)+"uid="+symposium.current_user_page+"&embed=on&view=wps_events";
					window.location.href=reload_page;
				} else {
					alert(str);
				}
			},
			error: function(err){
				//alert("updateEvent:"+err);
			}		
		});
		
	});

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        GALLERY                                           |
	   +------------------------------------------------------------------------------------------+
	*/

  	// Prepare ColorBox
  	if (jQuery(".wps_gallery_album").length) {
	  	var list = new Array();
		jQuery(".wps_gallery_album").each(function(index) {
			var rel = jQuery(this).attr("rel");
			if (jQuery.inArray(rel, list) == -1) {
				list.push(rel);
		      	jQuery("a[rel='"+rel+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
			}
		});  	
  	}
	
   	// Act on "album_id" parameter, load album straight away (remember to set embed=on on hyperlink)
	if (symposium.album_id > 0 && symposium.embed == 'on') {
        
     	    jQuery.ajax({
      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
				'script'    	: symposium.plugin_pro_url+'gallery/upload_menu_gallery.php',
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
							jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 175, modal: true,
							buttons: {
									"OK": function() {
										jQuery("#dialog").dialog('close');
										window.location.href=symposium.plugin_pro_url+'gallery/ajax/symposium_gallery_functions.php?href=redirect&num='+data.filesUploaded+'&aid='+symposium.album_id;
									}
								}
							});
    						}

			});

        	      	// Prepare ColorBox
					if (jQuery().colorbox) {
	        	      	jQuery("a[rel='symposium_gallery_photos']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
					}
	      		}
            });	
        	
	} 

	jQuery('#gallery_go_button').live('click', function () {
		jQuery("#symposium_gallery_start").html('0');
		symposium_do_gallery_search();
	});
	jQuery('#gallery_member').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			jQuery("#symposium_gallery_start").html('0');
			symposium_do_gallery_search();
		}
	});
	
	// Search
	jQuery('#showmore_gallery').live('click', function () {
		jQuery(this).html("<br /><img src='"+symposium.images_url+"/busy.gif' />");
		symposium_do_gallery_search();
	});	
	
	function symposium_do_gallery_search() {

		var page_length = jQuery('#symposium_gallery_page_length').html();
		var start = jQuery("#symposium_gallery_start").html();
		
		if (start == 0) {
			jQuery('#symposium_gallery_albums').html("<br /><img src='"+symposium.images_url+"/busy.gif' />");
		}

	 	jQuery.ajax({
	      	url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
			type: "POST",
			data: ({
				action:"getGallery",
				start:start,
				term:jQuery('#gallery_member').val()
			}),
		    dataType: "html",
			async: true,
			success: function(str){		
				var details = str.split("[split]");
				str = details[1];
				var new_start = parseFloat(start)+parseFloat(details[0]);
				jQuery("#symposium_gallery_start").html(new_start);
				if (start == 0) {
					jQuery('#symposium_gallery_albums').html(str);
				} else {
					jQuery('#symposium_gallery_albums').html(jQuery('#symposium_gallery_albums').html() + str);
					jQuery('#showmore_gallery').remove();
				}

			  	// Prepare ColorBox
			  	if (jQuery(".wps_gallery_album").length) {
				  	var list = new Array();
					jQuery(".wps_gallery_album").each(function(index) {
						var rel = jQuery(this).attr("rel");
						if (jQuery.inArray(rel, list) == -1) {
							list.push(rel);
					      	jQuery("a[rel='"+rel+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
						}
					});  	
			  	}
			  	
			},
			error: function(err){
				alert("symposium_do_gallery_search:"+err);
			}		
	  	});
	}
		
	// Stretch div on activity stream
	jQuery("#wps_gallery_comment_more").live('click', function() {
		jQuery(this).hide();
		jQuery('#wps_comment_plus').css("overflow", "visible");
	});
	
	// Manage album
	jQuery("#symposium_manage_album_button").live('click', function() {

        	symposium.album_id = jQuery(this).attr("title");
        
     		jQuery.ajax({
	      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
	      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
		      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
		      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
	      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
      		url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
				'script'    	: symposium.plugin_pro_url+'gallery/upload_menu_gallery.php',
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
							jQuery("#dialog").dialog({ title: symposium.site_title, width: 600, height: 175, modal: true,
							buttons: {
									"OK": function() {
										jQuery("#dialog").dialog('close');
										window.location.href=symposium.plugin_pro_url+'gallery/ajax/symposium_gallery_functions.php?href=redirect&num='+data.filesUploaded+'&aid='+symposium.album_id;
									}
								}
							});
    						}
			});

              		// Prepare ColorBox
					if (jQuery().colorbox) {
		              	jQuery("a[rel='symposium_gallery_photos']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});
					}


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
			url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
	     			url: symposium.plugin_pro_url+"gallery/ajax/symposium_gallery_functions.php", 
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
	
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        LOUNGE                                            |
	   +------------------------------------------------------------------------------------------+
	*/

	// Start regular checks for lounge contents
	lounge_polling();

	// Add comment via button
	jQuery("#symposium_lounge_add_comment_button").live('click', function() {
		add_comment_to_lounge();
	});

	// Add comment via Return on keyboard
	jQuery('#symposium_lounge_add_comment').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
			add_comment_to_lounge();
		}
	});

	// Delete comment via trash icon
	jQuery(".symposium_lounge_del_icon").live('click', function() {
		var comment_id = jQuery(this).attr("id");
		jQuery("#comment_"+comment_id).slideUp("slow");
		jQuery.ajax({
			url: symposium.plugin_pro_url+"lounge/ajax/symposium_lounge_functions.php", 
      			type: "POST",
      			data: ({
    				action:'delete_comment',
				comment_id:comment_id
      			}),
     			dataType: "html",
     			success: function(str){
				if (str != "OK") {
					alert(str);
				}
      			},
			error: function(err){
				// Uncomment to receive any AJAX errors, sometimes these are rogue due to network issues
				//alert(err);
			}
		});
	});

	
	

function add_comment_to_lounge() {

	var new_comment = jQuery('#symposium_lounge_add_comment').val();

	if (jQuery('#symposium_lounge_add_comment').val() != 'Add a comment..') {
		var items = '';
		items += '<div id="symposium_lounge_comment">';
		items += '<div class="symposium_lounge_new_comment symposium_lounge_new_comment_you">'+new_comment+'</div>';
		items += '<div class="symposium_lounge_new_status">';
			items += '<img style="float: left" src="'+symposium.images_url+'/online.gif">';
		items += '</div>';
		items += '<div class="symposium_lounge_new_author">';
			items += 'You';
		items += '</div>';
		jQuery(items).prependTo('#symposium_lounge_div');
		jQuery('#symposium_lounge_add_comment').val('');	
	}
    		
	jQuery.ajax({
		url: symposium.plugin_pro_url+"lounge/ajax/symposium_lounge_functions.php", 
      		type: "POST",
      		data: ({
    			action:'add_comment',
			comment:new_comment
      		}),
     		dataType: "html",
     		success: function(str){
      		},
		error: function(err){
			// Uncomment to receive any AJAX errors, sometimes these are rogue due to network issues
			//alert(err);
		}
	});
}

function lounge_polling() {

	if (jQuery("#symposium_lounge_div").length) {

     		jQuery.ajax({
      			url: symposium.plugin_pro_url+"lounge/ajax/symposium_lounge_functions.php", 
	      		type: "POST",
	      		data: ({
     				action:'get_comments',
				inactive:symposium.inactive,
				offline:symposium.offline
	      		}),
      			dataType: "html",
      			success: function(str){

				// AJAX function return JSON array of comments to create HTML
				var items = "";
				var rows = jQuery.parseJSON(str);

			        jQuery.each(rows, function(i,row){
					items += '<div id="comment_'+row.lid+'" class="symposium_lounge_comment">';
						if (symposium.current_user_level == 5) {
							var del_icon = "<a title='Delete' id='"+row.lid+"' href='javascript:void(0);' class='symposium_lounge_del_icon'><img src='"+symposium.images_url+"/delete.png' style='width:16px;height:16px' /></a>";
							items += del_icon;
						}

						items += '<div class="symposium_lounge_new_comment';
						if (row.author_id == symposium.current_user_id) {
							items += ' symposium_lounge_new_comment_you';
						}
						items += '">'+row.comment+'</div>';
						items += '<div class="symposium_lounge_new_status">';
							items += '<img style="float: left" src="'+symposium.images_url+'/'+row.status+'.gif">';
						items += '</div>';
						items += '<div class="symposium_lounge_new_author">';
							items += row.author+' '+row.added;
						items += '</div>';
					items += '</div>';
				});
				jQuery('#symposium_lounge_div').html(items);

	      		},
			error: function(err){
				// Uncomment to receive any AJAX errors, sometimes these are rogue due to network issues
				//alert(err);
			}
		});
	}

	// Repeat check every 5 seconds
	setTimeout(lounge_polling, 5000);
}


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                       RSS FEED                                           |
	   +------------------------------------------------------------------------------------------+
	*/
	
	
	jQuery("#symposium_rss_icon").live('click', function() {
		var str = "Use the following to receive an RSS feed of this member's activity:";
		str += '<br /><input type="text" style="width:650px;" value="'+symposium.plugin_pro_url+'rss/activity.php?uid='+symposium.current_user_page+'" />';
		str += '<br /><a href='+symposium.plugin_pro_url+'rss/activity.php?uid='+symposium.current_user_page+' target="_blank">View</a>';
		jQuery("#dialog").html(str);
		jQuery("#dialog").dialog({ title: symposium.site_title, width: 700, height: 175, modal: true, buttons: {}  });
		
	});
	

/*
	   +------------------------------------------------------------------------------------------+
	   |                                        WIDGET: VOTE                                      |
	   +------------------------------------------------------------------------------------------+
*/

	if (jQuery(".symposium_answer").length) {
		jQuery(".symposium_answer").click(function(){
		
			var vote_answer = jQuery(this).attr("title");
			jQuery("#symposium_vote_thankyou").slideDown("fast").effect("highlight", {}, 3000);
		
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_widget_functions.php", 
				type: "POST",
				data: ({
					action:"doVote",
					vote:vote_answer
				}),
			    dataType: "html",
				async: false,
				success: function(str){
				},
				error: function(err){
					//alert("V:"+err);
				}		
	   		});	
			jQuery(".symposium_pleasewait").fadeOut("slow");
		});
	}

	if (jQuery("#symposium_chartcontainer").length) {
	
		var yes = parseFloat(jQuery('#symposium_chart_yes').html());
		var no = parseFloat(jQuery('#symposium_chart_no').html());
	
		if (jQuery("#symposium_chart_counts").html() != 'on') {
			if (yes > 0) {
				if (no > 0) {
					yes = Math.floor(yes/(yes+no)*100);
					no = 100 - yes;
				} else {
					yes = 100;
					no = 0;
				}
			} else {
				yes = 0;
				if (no > 0) {
					no = 100;
				} else {
					no = 0;
				}
			}			
		}
	
		var myData = new Array(['Yes', yes], ['No', no]);
		var bar_type = 'bar';
		if (jQuery("#symposium_chart_type").html() != '') { bar_type = jQuery("#symposium_chart_type").html(); }
		var myChart = new JSChart('symposium_chartcontainer', bar_type, jQuery('#symposium_chart_key').html());
		myChart.setDataArray(myData);
		var myColors = new Array('#09f', '#06a')
		if (bar_type == 'bar') {
			myChart.colorizeBars(myColors);
		} else {
			myChart.colorizePie(myColors);
		}
		myChart.setSize(200, 200);
		myChart.setTitleFontSize(14);
		myChart.setTitle("");
		myChart.setAxisNameX("");
		myChart.setAxisNameY("");
		myChart.setAxisPaddingTop(15);
		myChart.setAxisPaddingBottom(15);
		myChart.setAxisPaddingLeft(0);
		if (jQuery("#symposium_chart_counts").html() != 'on') {
			if (bar_type == 'bar') {
				myChart.setBarValuesSuffix('%');
			} else {
				myChart.setPieValuesSuffix('%');
			}
		}
		myChart.draw();
	
	}
	
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          GROUP                                           |
	   +------------------------------------------------------------------------------------------+
	*/

	// Menu choices
	jQuery(".symposium_group_menu").live('click', function() {
		
		var menu_id = jQuery(this).attr("id");

		if (menu_id == 'group_menu_all') {
			window.location.href=symposium.groups_url;
		} else {

			jQuery('#group_body').html("<img src='"+symposium.images_url+"/busy.gif' />");
                
			if (menu_id != 'group_menu_forum') {
				
				jQuery.ajax({
					url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
					type: "POST",
					data: ({
						action:menu_id,
						post:'',
						limit_from:0,
						uid1:symposium.current_group
					}),
				    dataType: "html",
					success: function(str){
						jQuery('#group_body').html(str);
						
						var user_login = jQuery("#symposium_user_login").html();
						var user_email = jQuery("#symposium_user_email").html();
				
						jQuery('#file_upload').uploadify({
						    'uploader'  : symposium.plugin_pro_url+'groups/uploadify/uploadify.swf',
							'buttonText': browseforfile,
						    'script'    : symposium.plugin_pro_url+'groups/uploadify/upload_group_avatar.php',
						    'cancelImg' : symposium.plugin_pro_url+'groups/uploadify/cancel.png',
						    'auto'      : true,
							'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
							'scriptData' : {'gid':symposium.current_group, 'user_login':user_login, 'user_email':user_email, 'uid':symposium.current_user_id}, 
							'onError' 	: function(event, ID, fileObj, errorObj) {
											 alert("Error: "+errorObj.type+" "+errorObj.info);
		      							  },
							'onComplete': function(event, queueID, fileObj, response, data) { 
								jQuery('#group_image_to_crop').html(response);

								jQuery('#profile_jcrop_target').Jcrop({
									onChange: showPreview,
									onSelect: showPreview
								});

							}
					   	});
	   		   					
					}
		   		});
		
			} else {
				
					
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:'getForum',
						limit_from:0,
						cat_id:symposium.cat_id,
						topic_id:symposium.show_tid,
						group_id:symposium.current_group
					}),
				    dataType: "html",
					async: true,
					success: function(str){

						str = trim(str);

						if (strpos(str, "[|]", 0) ) {
							var details=str.split("[|]");
							jQuery(document).attr('title', details[0]);
							str = details[1];
						}	

						jQuery("#group_body").html(str);
						
						var user_login = jQuery("#symposium_user_login").html();
						var user_email = jQuery("#symposium_user_email").html();
						
						// Enable file uploading
						jQuery('#forum_file_upload').uploadify({
						    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
							'buttonText': browseforfile,
						    'script'    : symposium.plugin_url+'uploadify/upload_forum_file.php', 
							'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
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

						if (jQuery().colorbox) {
		    	      		jQuery("a[rel='symposium_forum_images-"+symposium.show_tid+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});						
						}

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
		}
		
	});

	function showPreview(coords)
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

	// Act on "view" parameter on first page load
	if ((symposium.cat_id != '') && (symposium.cat_id) && (symposium.current_group > 0)) {
		// Forum parameter passed, show forum
		var sub = "getForum";
		if (symposium.show_tid > 0) {
			var sub = "getTopic";
		}
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

					if (strpos(str, "[|]", 0) ) {
						var details=str.split("[|]");
						jQuery(document).attr('title', details[0]);
						str = details[1];
					}	

					jQuery("#group_body").html(str);
	
					var user_login = jQuery("#symposium_user_login").html();
					var user_email = jQuery("#symposium_user_email").html();
					
					// Enable file uploading
					jQuery('#forum_file_upload').uploadify({
					    'uploader'  : symposium.plugin_url+'uploadify/uploadify.swf',
						'buttonText': browseforfile,
					    'script'    : symposium.plugin_url+'uploadify/upload_forum_file.php', 
						'fileExt'   : '*.jpg;*.gif;*.png;*.jpeg;',
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
				
					if (jQuery().colorbox) {
	    	      		jQuery("a[rel='symposium_forum_images-"+symposium.show_tid+"']").colorbox({transition:"none", width:"75%", height:"75%", photo:true});						
					}
	
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
	} else {
		// Load defaut page
		if (jQuery("#force_group_page").length) {
			
			if(jQuery("#force_group_page").html() != '') {
				
				var menu_id = 'group_menu_'+jQuery('#force_group_page').html();

				jQuery.ajax({
					url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
					type: "POST",
					data: ({
						action:menu_id,
						post:symposium.post,
						limit_from:0,
						uid1:symposium.current_group,
						uid2:symposium.current_user_id				
					}),
				    dataType: "html",
					success: function(str){
						jQuery('#group_body').html(str);
					}
		   		});	
	
	   		}
		}
	}
	
	// Delete group member
	jQuery(".delete_group_member").live('click', function() {

		if (confirm("Are you sure?")) {

			var id = jQuery(this).attr("title");
			jQuery(this).parent().parent().slideUp("slow");

			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
				type: "POST",
				data: ({
					action:"member_delete",
					group_id:symposium.current_group,
					id:id
				}),
			    dataType: "html",
				success: function(str){
				}
	   		});	
	   		
		}
	});
	
	
	// Clicked on show more...
	jQuery("#showmore_group_wall").live('click', function() {
		
		var limit_from = jQuery(this).attr("title");
		jQuery('#showmore_group_wall').html("<img src='"+symposium.images_url+"/busy.gif' />");

		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:menu_id,
				post:'',
				limit_from:limit_from,
				uid1:symposium.current_group,
				uid2:symposium.current_user_id				
			}),
		    dataType: "html",
			success: function(str){
				jQuery('#showmore_group_wall').remove();
				jQuery(str).appendTo('#group_body').hide().slideDown("slow");
			}
   		});	

	});
	
	// new post to group wall
	jQuery('#symposium_group_comment').live('keypress', function (e) {
		if ( e.keyCode == 13 ){

			var comment_text = jQuery("#symposium_group_comment").val();
			jQuery("#symposium_group_comment").val('');
		
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
				url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
				type: "POST",
				data: ({
					action:"group_addStatus",
					subject_uid:symposium.current_group,
					author_uid:symposium.current_user_id,
					parent:0,
					text:comment_text
				}),
			    dataType: "html",
				async: true
	   		});
		}
   	});	

	// new reply
	jQuery('.reply_field').live('keypress', function (e) {
		if ( e.keyCode == 13 ){
		
			var comment_id = jQuery(this).attr("title");
			var author_id = jQuery('#symposium_author_'+comment_id).val();
			var comment_text = jQuery("#symposium_reply_"+comment_id).val();

			jQuery("#symposium_reply_"+comment_id).val('');
		
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
				url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
				type: "POST",
				data: ({
					action:"group_addComment",
					uid:symposium.current_group,
					parent:comment_id,
					text:comment_text
				}),
			    dataType: "html",
				async: true
	   		});
   		}
   	});	   	


	// update group settings
	jQuery("#updateGroupSettingsButton").live('click', function() {
		
		jQuery(".symposium_notice").inmiddle().show();
		
		var is_private = '';
		if (jQuery("#private").is(":checked")) {
			is_private = 'on';
		}
		
		var content_private = '';
		if (jQuery("#content_private").is(":checked")) {
			var content_private = 'on';
		}

		var group_forum = '';
		if (jQuery("#group_forum").is(":checked")) {
			group_forum = 'on';
		}

		var show_forum_default = '';
		if (jQuery("#show_forum_default").is(":checked")) {
			show_forum_default = 'on';
		}

		var allow_new_topics = '';
		if (jQuery("#allow_new_topics").is(":checked")) {
			allow_new_topics = 'on';
		}

		var new_member_emails = '';
		if (jQuery("#new_member_emails").is(":checked")) {
			new_member_emails = 'on';
		}

		var add_alerts = '';
		if (jQuery("#add_alerts").is(":checked")) {
			add_alerts = 'on';
		}

		var group_admin = jQuery("#transfer_admin").val();

		jQuery("#group_name").html(jQuery("#groupname").val());
		jQuery("#group_description").html(jQuery("#groupdescription").val());


		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"updateGroupSettings",
				gid:symposium.current_group,
				groupname:jQuery("#groupname").val(),
				groupdescription:jQuery("#groupdescription").val(),
				is_private:is_private,
				content_private:content_private,	
				group_forum:group_forum,	
				show_forum_default:show_forum_default,	
				allow_new_topics:allow_new_topics,
				group_admin:group_admin,
				new_member_emails:new_member_emails,
				add_alerts:add_alerts,
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
					}
				},
			error: function(err){
				alert("updateGroupSettings:"+err);
			}		
   		});
   										   			
   	});		
   	
   	// Join group
	jQuery("#groups_join_button").live('click', function() {

		jQuery(".symposium_pleasewait").inmiddle().show();
		
		jQuery("#groups_join_button").hide();
		
		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"joinGroup",
				gid:symposium.current_group
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				if (str != '') {
					alert(str);
				}
				jQuery("#groups_join_button_done").effect("highlight", {}, 3000);
				location.reload();
			}			
   		});

	});
	
   	// Delete group
	jQuery("#groups_delete_button").live('click', function() {
		
	  	var answer = confirm("This cannot be un-done - are you really sure?");

	  	if (answer) {

			jQuery("#groups_delete_button").hide();
			jQuery("#groups_delete_button_done").effect("highlight", {}, 3000);
			
			jQuery(".symposium_pleasewait").inmiddle().show();
		
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
				type: "POST",
				data: ({
					action:"deleteGroup",
					gid:symposium.current_group
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					window.location.href=symposium.groups_url;
				}
	   		});

	   		
	  	}

	});
	
   	// Leave group
	jQuery("#groups_leave_button").live('click', function() {
		
		if (confirm(areyousure)) {
		
			jQuery(".symposium_pleasewait").inmiddle().show();
			jQuery("#groups_leave_button").hide();
			
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
				type: "POST",
				data: ({
					action:"leaveGroup",
					gid:symposium.current_group
				}),
			    dataType: "html",
				async: false,
				success: function(str){
					jQuery("#groups_leave_button_done").effect("highlight", {}, 3000);
					location.reload();
				}			
				
	   		});
		}

	});

	// Subscribe/unsubscribe
	jQuery("#group_notify").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		if (jQuery("#group_notify").is(":checked")) {
			var group_notify = 'on';
		} else {
			var group_notify = '';
		}
		
		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"group_subscribe",
				notify:group_notify,
				gid:symposium.current_group
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery(".symposium_notice").fadeOut("slow");
			}
   		});  		
	});

	// reject a group request
	jQuery("#rejectgrouprequest").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"rejectGroup",
				uid:jQuery(this).attr("title"),
				gid:symposium.current_group
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
				//alert("rejectGroup:"+err);
			}		
   		});
   			
   	});	
   		
	// accept a group request
	jQuery("#acceptgrouprequest").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"acceptGroup",
				uid:jQuery(this).attr("title"),
				gid:symposium.current_group
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
				//alert("rejectGroup:"+err);
			}		
   		});
   			
   	});	
   		
   	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          GROUPS                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("input#group").length) {
		
		jQuery("input#group").autocomplete({
				source: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#group" ).val( ui.item.name );
					jQuery( "input#group_id" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#group" ).val( ui.item.name );
					jQuery( "input#group_id" ).val( ui.item.value );
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				var group = "<a>";
					group += "<div style='height:40px; overflow:hidden'>";
						group += "<div style=\'float:left; background-color:#fff; margin-right: 8px; width:40px; height:40px; \'>";	
						group += item.avatar;
						group += "</div>";			
						group += "<div>" + item.name + "</div>";
						group += "<br style='clear:both' />";
					group += "</div>";
				group += "</a>";
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( group )
					.appendTo( ul );
			};

		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php", 
			type: "POST",
			data: ({
				action:"getGroups",
				page:1,
				me:symposium.current_user_id
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#symposium_groups").html(str);
			},
			error: function(err){
				//alert("D1:"+err);
			}		
   		});	
	};	

	jQuery("#show_create_group_button").click(function(){
		jQuery("#show_create_group_button").hide();
		jQuery("#groups_results").hide();
		jQuery("#create_group_form").fadeIn("slow");
	});
	
	jQuery("#cancel_create_group_button").click(function(){
		jQuery("#show_create_group_button").show();
		jQuery("#create_group_form").hide();		
		jQuery("#groups_results").fadeIn("slow");
	});
	
	jQuery("#create_group_button").click(function(){

		var name_of_group = jQuery('#name_of_group').val();
		var description_of_group = jQuery('#description_of_group').val();
		
		if (name_of_group != '') {
			
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php", 
				type: "POST",
				data: ({
					action:"createGroup",
					me:symposium.current_user_id,
					name_of_group:name_of_group,
					description_of_group:description_of_group
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					window.location.href=symposium.group_url+symposium.q+'gid='+trim(str);
				},
				error: function(err){
					alert("createGroup:"+err);
				}		
	   		});				
			
		}
		
	});

	
	jQuery("#symposium_group_invites_button").live('click', function() {

		jQuery(".symposium_pleasewait").inmiddle().show();

		var emails = jQuery('#symposium_group_invites').val();

		jQuery.ajax({
			url: symposium.plugin_pro_url+"groups/ajax/symposium_group_functions.php", 
			type: "POST",
			data: ({
				action:"sendInvites",
				emails:emails,
				group_id:symposium.current_group
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery(".symposium_pleasewait").hide();
				jQuery('#symposium_group_invites_button').hide();
				jQuery('#symposium_group_invites').hide();
				jQuery('#symposium_group_invites_sent').html(str).show();
			},
			error: function(err){
				alert("symposium_group_invites_button:"+err);
			}		
   		});				
	});

   	/*
	   +------------------------------------------------------------------------------------------+
	   |                                       GROUP ADMIN                                        |
	   +------------------------------------------------------------------------------------------+
	*/

	// Search for members
	jQuery("#user_list_search_button").live('click', function() {
		
		var gid = jQuery('#group_list').val();
		
		if (gid == 0) {

			jQuery("#dialog").html('Please select a group');
			jQuery("#dialog").dialog({ title: symposium.site_title, width: 400, height: 220, modal: true,
			buttons: {
					"OK": function() {
						jQuery(this).dialog("close");
					}
				}
			});

		} else {
			
			jQuery('#user_list').html("<img src='"+symposium.images_url+"/busy.gif' />");
			
			var term = jQuery("#user_list_search").val();
	
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php", 
				type: "POST",
				data: ({
					action:"get_user_list",
					term:term,
					gid:gid
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					jQuery('#user_list').html(str);	               					
				},
				error: function(err){
					alert("user_list_search_button:"+err);
				}		
	   		});		
		
		}
		
	});

	// Select new group	
	jQuery('#group_list').live('change',function(){

		jQuery('#user_list').html('');
		jQuery('#selected_users').html('');
		
		var gid = jQuery(this).val();
	    if (gid > 0) {

			jQuery('#selected_users').html("<img src='"+symposium.images_url+"/busy.gif' />");
			
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php", 
				type: "POST",
				data: ({
					action:"get_group_members",
					gid:gid
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					jQuery('#selected_users').html(str);	               					
				},
				error: function(err){
					alert("group_list:"+err);
				}		
	   		});		
	   		
	    }

	});	
	
	// Add or remove a user to/from a group selection
	jQuery(".user_list_item").live('click', function() {

		var id = jQuery(this).attr("id");		
		var parent_id = jQuery(this).parent().attr("id");
		if (parent_id == 'user_list') {

			// Add a user to the selected list
			jQuery(this).clone().appendTo('#selected_users');
			jQuery(this).remove();
			var html = jQuery('#selected_users #'+id).html();
			html = html.replace('add', 'cross');
			jQuery('#selected_users #'+id).html(html);
			
		} else {
			// Remove a user to the selected list
			jQuery(this).clone().appendTo('#user_list');
			jQuery(this).remove();
			var html = jQuery('#user_list #'+id).html();
			html = html.replace('cross', 'add');
			jQuery('#user_list #'+id).html(html);

		}

	});
	
	// Add button
	jQuery("#users_add_button").live('click', function() {
		
		if (jQuery('#group_list').val() > 0) {
			
			var id = '';
			jQuery('#selected_users').children('div').each(function () {
			    id += jQuery(this).attr('id')+',';
			});
	
			jQuery('#selected_users').html("<img src='"+symposium.images_url+"/busy.gif' /> "+pleasewait+'...');
	
			jQuery.ajax({
				url: symposium.plugin_pro_url+"groups/ajax/symposium_groups_functions.php", 
				type: "POST",
				data: ({
					action:"add_group_members",
					gid:jQuery('#group_list').val(),
					ids:id
				}),
			    dataType: "html",
				async: true,
				success: function(str){
					if (str != '') { 
						alert (str); 
					} else {
						location.reload();
					}
				},
				error: function(err){
					alert("users_add_button:"+err);
				}		
	   		});	
	   		
		}
		
		return void(0);
	});


	
});

   	/*
	   +------------------------------------------------------------------------------------------+
	   |                                     SHARED FUNCTIONS                                     |
	   +------------------------------------------------------------------------------------------+
	*/

function trim(s)
{
	var l=0; var r=s.length -1;
	while(l < s.length && s[l] == ' ')
	{	l++; }
	while(r > l && s[r] == ' ')
	{	r-=1;	}
	return s.substring(l, r+1);
}

   	/*
	   +------------------------------------------------------------------------------------------+
	   |                                     EXTERNAL SCRIPTS                                     |
	   +------------------------------------------------------------------------------------------+
	*/

/**
*	@name							Elastic
*	@descripton						Elastic is Jquery plugin that grow and shrink your textareas automaticliy
*	@version						1.6.4
*	@requires						Jquery 1.2.6+
*
*	@author							Jan Jarfalk
*	@author-email					jan.jarfalk@unwrongest.com
*	@author-website					http://www.unwrongest.com
*
*	@licens							MIT License - http://www.opensource.org/licenses/mit-license.php
*/

(function(jQuery){ 
	
	jQuery.fn.extend({  
		elastic: function() {
		
			//	We will create a div clone of the textarea
			//	by copying these attributes from the textarea to the div.
			var mimics = [
				'paddingTop',
				'paddingRight',
				'paddingBottom',
				'paddingLeft',
				'fontSize',
				'lineHeight',
				'fontFamily',
				'width',
				'fontWeight'];
			
			return this.each( function() {
				
				// Elastic only works on textareas
				if ( this.type != 'textarea' ) {
					return false;
				}
				
				var $textarea	=	jQuery(this),
					$twin		=	jQuery('<div />').css({'position': 'absolute','display':'none','word-wrap':'break-word'}),
					lineHeight	=	parseInt($textarea.css('line-height'),10) || parseInt($textarea.css('font-size'),'10'),
					minheight	=	parseInt($textarea.css('height'),10) || lineHeight*3,
					maxheight	=	parseInt($textarea.css('max-height'),10) || Number.MAX_VALUE,
					goalheight	=	0,
					i 			=	0;
				
				// Opera returns max-height of -1 if not set
				if (maxheight < 0) { maxheight = Number.MAX_VALUE; }
					
				// Append the twin to the DOM
				// We are going to meassure the height of this, not the textarea.
				$twin.appendTo($textarea.parent());
				
				// Copy the essential styles (mimics) from the textarea to the twin
				var i = mimics.length;
				while(i--){
					$twin.css(mimics[i].toString(),$textarea.css(mimics[i].toString()));
				}
				
				
				// Sets a given height and overflow state on the textarea
				function setHeightAndOverflow(height, overflow){
					curratedHeight = Math.floor(parseInt(height,10));
					if($textarea.height() != curratedHeight){
						$textarea.css({'height': curratedHeight + 'px','overflow':overflow});
						
					}
				}
				
				
				// This function will update the height of the textarea if necessary 
				function update() {
					
					// Get curated content from the textarea.
					var textareaContent = $textarea.val().replace(/&/g,'&amp;').replace(/  /g, '&nbsp;').replace(/<|>/g, '&gt;').replace(/\n/g, '<br />');

					var twinContent = $twin.html();
					
					if(textareaContent+'&nbsp;' != twinContent){
					
						// Add an extra white space so new rows are added when you are at the end of a row.
						$twin.html(textareaContent+'&nbsp;');
						
						// Change textarea height if twin plus the height of one line differs more than 3 pixel from textarea height
						if(Math.abs($twin.height()+lineHeight - $textarea.height()) > 3){
							
							var goalheight = $twin.height()+lineHeight;
							if(goalheight >= maxheight) {
								setHeightAndOverflow(maxheight,'auto');
							} else if(goalheight <= minheight) {
								setHeightAndOverflow(minheight,'hidden');
							} else {
								setHeightAndOverflow(goalheight,'hidden');
							}
							
						}
						
					}
					
				}
				
				// Hide scrollbars
				$textarea.css({'overflow':'hidden'});
				
				// Update textarea size on keyup
				$textarea.keyup(function(){ update(); });
				
				// And this line is to catch the browser paste event
				$textarea.live('input paste',function(e){ setTimeout( update, 250); });				
				
				// Run update once when elastic is initialized
				update();
				
			});
			
        } 
    }); 
})(jQuery);



// JW Player
if(typeof jwplayer=="undefined"){var jwplayer=function(a){if(jwplayer.api){return jwplayer.api.selectPlayer(a)}};var $jw=jwplayer;jwplayer.version="5.7.1896";jwplayer.vid=document.createElement("video");jwplayer.audio=document.createElement("audio");jwplayer.source=document.createElement("source");(function(b){b.utils=function(){};b.utils.typeOf=function(d){var c=typeof d;if(c==="object"){if(d){if(d instanceof Array){c="array"}}else{c="null"}}return c};b.utils.extend=function(){var c=b.utils.extend["arguments"];if(c.length>1){for(var e=1;e<c.length;e++){for(var d in c[e]){c[0][d]=c[e][d]}}return c[0]}return null};b.utils.clone=function(f){var c;var d=b.utils.clone["arguments"];if(d.length==1){switch(b.utils.typeOf(d[0])){case"object":c={};for(var e in d[0]){c[e]=b.utils.clone(d[0][e])}break;case"array":c=[];for(var e in d[0]){c[e]=b.utils.clone(d[0][e])}break;default:return d[0];break}}return c};b.utils.extension=function(c){if(!c){return""}c=c.substring(c.lastIndexOf("/")+1,c.length);c=c.split("?")[0];if(c.lastIndexOf(".")>-1){return c.substr(c.lastIndexOf(".")+1,c.length).toLowerCase()}return};b.utils.html=function(c,d){c.innerHTML=d};b.utils.wrap=function(c,d){if(c.parentNode){c.parentNode.replaceChild(d,c)}d.appendChild(c)};b.utils.ajax=function(g,f,c){var e;if(window.XMLHttpRequest){e=new XMLHttpRequest()}else{e=new ActiveXObject("Microsoft.XMLHTTP")}e.onreadystatechange=function(){if(e.readyState===4){if(e.status===200){if(f){f(e)}}else{if(c){c(g)}}}};try{e.open("GET",g,true);e.send(null)}catch(d){if(c){c(g)}}return e};b.utils.load=function(d,e,c){d.onreadystatechange=function(){if(d.readyState===4){if(d.status===200){if(e){e()}}else{if(c){c()}}}}};b.utils.find=function(d,c){return d.getElementsByTagName(c)};b.utils.append=function(c,d){c.appendChild(d)};b.utils.isIE=function(){return((!+"\v1")||(typeof window.ActiveXObject!="undefined"))};b.utils.isLegacyAndroid=function(){var c=navigator.userAgent.toLowerCase();return(c.match(/android 2.[012]/i)!==null)};b.utils.isIOS=function(d){if(typeof d=="undefined"){d=/iP(hone|ad|od)/i}var c=navigator.userAgent.toLowerCase();return(c.match(d)!==null)};b.utils.isIPad=function(){return b.utils.isIOS(/iPad/i)};b.utils.isIPod=function(){return b.utils.isIOS(/iP(hone|od)/i)};b.utils.getFirstPlaylistItemFromConfig=function(c){var d={};var e;if(c.playlist&&c.playlist.length){e=c.playlist[0]}else{e=c}d.file=e.file;d.levels=e.levels;d.streamer=e.streamer;d.playlistfile=e.playlistfile;d.provider=e.provider;if(!d.provider){if(d.file&&(d.file.toLowerCase().indexOf("youtube.com")>-1||d.file.toLowerCase().indexOf("youtu.be")>-1)){d.provider="youtube"}if(d.streamer&&d.streamer.toLowerCase().indexOf("rtmp://")==0){d.provider="rtmp"}if(e.type){d.provider=e.type.toLowerCase()}}if(d.provider=="audio"){d.provider="sound"}return d};b.utils.getOuterHTML=function(c){if(c.outerHTML){return c.outerHTML}else{try{return new XMLSerializer().serializeToString(c)}catch(d){return""}}};b.utils.setOuterHTML=function(f,e){if(f.outerHTML){f.outerHTML=e}else{var g=document.createElement("div");g.innerHTML=e;var c=document.createRange();c.selectNodeContents(g);var d=c.extractContents();f.parentNode.insertBefore(d,f);f.parentNode.removeChild(f)}};b.utils.hasFlash=function(){if(typeof navigator.plugins!="undefined"&&typeof navigator.plugins["Shockwave Flash"]!="undefined"){return true}if(typeof window.ActiveXObject!="undefined"){try{new ActiveXObject("ShockwaveFlash.ShockwaveFlash");return true}catch(c){}}return false};b.utils.getPluginName=function(c){if(c.lastIndexOf("/")>=0){c=c.substring(c.lastIndexOf("/")+1,c.length)}if(c.lastIndexOf("-")>=0){c=c.substring(0,c.lastIndexOf("-"))}if(c.lastIndexOf(".swf")>=0){c=c.substring(0,c.lastIndexOf(".swf"))}if(c.lastIndexOf(".js")>=0){c=c.substring(0,c.lastIndexOf(".js"))}return c};b.utils.getPluginVersion=function(c){if(c.lastIndexOf("-")>=0){if(c.lastIndexOf(".js")>=0){return c.substring(c.lastIndexOf("-")+1,c.lastIndexOf(".js"))}else{if(c.lastIndexOf(".swf")>=0){return c.substring(c.lastIndexOf("-")+1,c.lastIndexOf(".swf"))}else{return c.substring(c.lastIndexOf("-")+1)}}}return""};b.utils.getAbsolutePath=function(j,h){if(!b.utils.exists(h)){h=document.location.href}if(!b.utils.exists(j)){return undefined}if(a(j)){return j}var k=h.substring(0,h.indexOf("://")+3);var g=h.substring(k.length,h.indexOf("/",k.length+1));var d;if(j.indexOf("/")===0){d=j.split("/")}else{var e=h.split("?")[0];e=e.substring(k.length+g.length+1,e.lastIndexOf("/"));d=e.split("/").concat(j.split("/"))}var c=[];for(var f=0;f<d.length;f++){if(!d[f]||!b.utils.exists(d[f])||d[f]=="."){continue}else{if(d[f]==".."){c.pop()}else{c.push(d[f])}}}return k+g+"/"+c.join("/")};function a(d){if(!b.utils.exists(d)){return}var e=d.indexOf("://");var c=d.indexOf("?");return(e>0&&(c<0||(c>e)))}b.utils.pluginPathType={ABSOLUTE:"ABSOLUTE",RELATIVE:"RELATIVE",CDN:"CDN"};b.utils.getPluginPathType=function(d){if(typeof d!="string"){return}d=d.split("?")[0];var e=d.indexOf("://");if(e>0){return b.utils.pluginPathType.ABSOLUTE}var c=d.indexOf("/");var f=b.utils.extension(d);if(e<0&&c<0&&(!f||!isNaN(f))){return b.utils.pluginPathType.CDN}return b.utils.pluginPathType.RELATIVE};b.utils.mapEmpty=function(c){for(var d in c){return false}return true};b.utils.mapLength=function(d){var c=0;for(var e in d){c++}return c};b.utils.log=function(d,c){if(typeof console!="undefined"&&typeof console.log!="undefined"){if(c){console.log(d,c)}else{console.log(d)}}};b.utils.css=function(d,g,c){if(b.utils.exists(d)){for(var e in g){try{if(typeof g[e]==="undefined"){continue}else{if(typeof g[e]=="number"&&!(e=="zIndex"||e=="opacity")){if(isNaN(g[e])){continue}if(e.match(/color/i)){g[e]="#"+b.utils.strings.pad(g[e].toString(16),6)}else{g[e]=Math.ceil(g[e])+"px"}}}d.style[e]=g[e]}catch(f){}}}};b.utils.isYouTube=function(c){return(c.indexOf("youtube.com")>-1||c.indexOf("youtu.be")>-1)};b.utils.transform=function(c,d){c.style.webkitTransform=d;c.style.MozTransform=d;c.style.OTransform=d};b.utils.stretch=function(h,n,m,f,l,g){if(typeof m=="undefined"||typeof f=="undefined"||typeof l=="undefined"||typeof g=="undefined"){return}var d=m/l;var e=f/g;var k=0;var j=0;n.style.overflow="hidden";b.utils.transform(n,"");var c={};switch(h.toUpperCase()){case b.utils.stretching.NONE:c.width=l;c.height=g;break;case b.utils.stretching.UNIFORM:if(d>e){c.width=l*e;c.height=g*e}else{c.width=l*d;c.height=g*d}break;case b.utils.stretching.FILL:if(d>e){c.width=l*d;c.height=g*d}else{c.width=l*e;c.height=g*e}break;case b.utils.stretching.EXACTFIT:b.utils.transform(n,["scale(",d,",",e,")"," translate(0px,0px)"].join(""));c.width=l;c.height=g;break;default:break}c.top=(f-c.height)/2;c.left=(m-c.width)/2;b.utils.css(n,c)};b.utils.stretching={NONE:"NONE",FILL:"FILL",UNIFORM:"UNIFORM",EXACTFIT:"EXACTFIT"};b.utils.deepReplaceKeyName=function(h,e,c){switch(b.utils.typeOf(h)){case"array":for(var g=0;g<h.length;g++){h[g]=b.utils.deepReplaceKeyName(h[g],e,c)}break;case"object":for(var f in h){var d=f.replace(new RegExp(e,"g"),c);h[d]=b.utils.deepReplaceKeyName(h[f],e,c);if(f!=d){delete h[f]}}break}return h};b.utils.isInArray=function(e,d){if(!(e)||!(e instanceof Array)){return false}for(var c=0;c<e.length;c++){if(d===e[c]){return true}}return false};b.utils.exists=function(c){switch(typeof(c)){case"string":return(c.length>0);break;case"object":return(c!==null);case"undefined":return false}return true};b.utils.empty=function(c){if(typeof c.hasChildNodes=="function"){while(c.hasChildNodes()){c.removeChild(c.firstChild)}}};b.utils.parseDimension=function(c){if(typeof c=="string"){if(c===""){return 0}else{if(c.lastIndexOf("%")>-1){return c}else{return parseInt(c.replace("px",""),10)}}}return c};b.utils.getDimensions=function(c){if(c&&c.style){return{x:b.utils.parseDimension(c.style.left),y:b.utils.parseDimension(c.style.top),width:b.utils.parseDimension(c.style.width),height:b.utils.parseDimension(c.style.height)}}else{return{}}};b.utils.timeFormat=function(c){str="00:00";if(c>0){str=Math.floor(c/60)<10?"0"+Math.floor(c/60)+":":Math.floor(c/60)+":";str+=Math.floor(c%60)<10?"0"+Math.floor(c%60):Math.floor(c%60)}return str}})(jwplayer);(function(a){a.events=function(){};a.events.COMPLETE="COMPLETE";a.events.ERROR="ERROR"})(jwplayer);(function(jwplayer){jwplayer.events.eventdispatcher=function(debug){var _debug=debug;var _listeners;var _globallisteners;this.resetEventListeners=function(){_listeners={};_globallisteners=[]};this.resetEventListeners();this.addEventListener=function(type,listener,count){try{if(!jwplayer.utils.exists(_listeners[type])){_listeners[type]=[]}if(typeof(listener)=="string"){eval("listener = "+listener)}_listeners[type].push({listener:listener,count:count})}catch(err){jwplayer.utils.log("error",err)}return false};this.removeEventListener=function(type,listener){if(!_listeners[type]){return}try{for(var listenerIndex=0;listenerIndex<_listeners[type].length;listenerIndex++){if(_listeners[type][listenerIndex].listener.toString()==listener.toString()){_listeners[type].splice(listenerIndex,1);break}}}catch(err){jwplayer.utils.log("error",err)}return false};this.addGlobalListener=function(listener,count){try{if(typeof(listener)=="string"){eval("listener = "+listener)}_globallisteners.push({listener:listener,count:count})}catch(err){jwplayer.utils.log("error",err)}return false};this.removeGlobalListener=function(listener){if(!_globallisteners[type]){return}try{for(var globalListenerIndex=0;globalListenerIndex<_globallisteners.length;globalListenerIndex++){if(_globallisteners[globalListenerIndex].listener.toString()==listener.toString()){_globallisteners.splice(globalListenerIndex,1);break}}}catch(err){jwplayer.utils.log("error",err)}return false};this.sendEvent=function(type,data){if(!jwplayer.utils.exists(data)){data={}}if(_debug){jwplayer.utils.log(type,data)}if(typeof _listeners[type]!="undefined"){for(var listenerIndex=0;listenerIndex<_listeners[type].length;listenerIndex++){try{_listeners[type][listenerIndex].listener(data)}catch(err){jwplayer.utils.log("There was an error while handling a listener: "+err.toString(),_listeners[type][listenerIndex].listener)}if(_listeners[type][listenerIndex]){if(_listeners[type][listenerIndex].count===1){delete _listeners[type][listenerIndex]}else{if(_listeners[type][listenerIndex].count>0){_listeners[type][listenerIndex].count=_listeners[type][listenerIndex].count-1}}}}}for(var globalListenerIndex=0;globalListenerIndex<_globallisteners.length;globalListenerIndex++){try{_globallisteners[globalListenerIndex].listener(data)}catch(err){jwplayer.utils.log("There was an error while handling a listener: "+err.toString(),_globallisteners[globalListenerIndex].listener)}if(_globallisteners[globalListenerIndex]){if(_globallisteners[globalListenerIndex].count===1){delete _globallisteners[globalListenerIndex]}else{if(_globallisteners[globalListenerIndex].count>0){_globallisteners[globalListenerIndex].count=_globallisteners[globalListenerIndex].count-1}}}}}}})(jwplayer);(function(a){var b={};a.utils.animations=function(){};a.utils.animations.transform=function(c,d){c.style.webkitTransform=d;c.style.MozTransform=d;c.style.OTransform=d;c.style.msTransform=d};a.utils.animations.transformOrigin=function(c,d){c.style.webkitTransformOrigin=d;c.style.MozTransformOrigin=d;c.style.OTransformOrigin=d;c.style.msTransformOrigin=d};a.utils.animations.rotate=function(c,d){a.utils.animations.transform(c,["rotate(",d,"deg)"].join(""))};a.utils.cancelAnimation=function(c){delete b[c.id]};a.utils.fadeTo=function(m,f,e,j,h,d){if(b[m.id]!=d&&a.utils.exists(d)){return}if(m.style.opacity==f){return}var c=new Date().getTime();if(d>c){setTimeout(function(){a.utils.fadeTo(m,f,e,j,0,d)},d-c)}if(m.style.display=="none"){m.style.display="block"}if(!a.utils.exists(j)){j=m.style.opacity===""?1:m.style.opacity}if(m.style.opacity==f&&m.style.opacity!==""&&a.utils.exists(d)){if(f===0){m.style.display="none"}return}if(!a.utils.exists(d)){d=c;b[m.id]=d}if(!a.utils.exists(h)){h=0}var k=(e>0)?((c-d)/(e*1000)):0;k=k>1?1:k;var l=f-j;var g=j+(k*l);if(g>1){g=1}else{if(g<0){g=0}}m.style.opacity=g;if(h>0){b[m.id]=d+h*1000;a.utils.fadeTo(m,f,e,j,0,b[m.id]);return}setTimeout(function(){a.utils.fadeTo(m,f,e,j,0,d)},10)}})(jwplayer);(function(a){a.utils.arrays=function(){};a.utils.arrays.indexOf=function(c,d){for(var b=0;b<c.length;b++){if(c[b]==d){return b}}return -1};a.utils.arrays.remove=function(c,d){var b=a.utils.arrays.indexOf(c,d);if(b>-1){c.splice(b,1)}}})(jwplayer);(function(a){a.utils.extensionmap={"3gp":{html5:"video/3gpp",flash:"video"},"3gpp":{html5:"video/3gpp"},"3g2":{html5:"video/3gpp2",flash:"video"},"3gpp2":{html5:"video/3gpp2"},flv:{flash:"video"},f4a:{html5:"audio/mp4"},f4b:{html5:"audio/mp4",flash:"video"},f4v:{html5:"video/mp4",flash:"video"},mov:{html5:"video/quicktime",flash:"video"},m4a:{html5:"audio/mp4",flash:"video"},m4b:{html5:"audio/mp4"},m4p:{html5:"audio/mp4"},m4v:{html5:"video/mp4",flash:"video"},mp4:{html5:"video/mp4",flash:"video"},rbs:{flash:"sound"},aac:{html5:"audio/aac",flash:"video"},mp3:{html5:"audio/mp3",flash:"sound"},ogg:{html5:"audio/ogg"},oga:{html5:"audio/ogg"},ogv:{html5:"video/ogg"},webm:{html5:"video/webm"},m3u8:{html5:"audio/x-mpegurl"},gif:{flash:"image"},jpeg:{flash:"image"},jpg:{flash:"image"},swf:{flash:"image"},png:{flash:"image"},wav:{html5:"audio/x-wav"}}})(jwplayer);(function(e){e.utils.mediaparser=function(){};var g={element:{width:"width",height:"height",id:"id","class":"className",name:"name"},media:{src:"file",preload:"preload",autoplay:"autostart",loop:"repeat",controls:"controls"},source:{src:"file",type:"type",media:"media","data-jw-width":"width","data-jw-bitrate":"bitrate"},video:{poster:"image"}};var f={};e.utils.mediaparser.parseMedia=function(j){return d(j)};function c(k,j){if(!e.utils.exists(j)){j=g[k]}else{e.utils.extend(j,g[k])}return j}function d(n,j){if(f[n.tagName.toLowerCase()]&&!e.utils.exists(j)){return f[n.tagName.toLowerCase()](n)}else{j=c("element",j);var o={};for(var k in j){if(k!="length"){var m=n.getAttribute(k);if(e.utils.exists(m)){o[j[k]]=m}}}var l=n.style["#background-color"];if(l&&!(l=="transparent"||l=="rgba(0, 0, 0, 0)")){o.screencolor=l}return o}}function h(n,k){k=c("media",k);var l=[];var j=e.utils.selectors("source",n);for(var m in j){if(!isNaN(m)){l.push(a(j[m]))}}var o=d(n,k);if(e.utils.exists(o.file)){l[0]={file:o.file}}o.levels=l;return o}function a(l,k){k=c("source",k);var j=d(l,k);j.width=j.width?j.width:0;j.bitrate=j.bitrate?j.bitrate:0;return j}function b(l,k){k=c("video",k);var j=h(l,k);return j}f.media=h;f.audio=h;f.source=a;f.video=b})(jwplayer);(function(a){a.utils.loaderstatus={NEW:"NEW",LOADING:"LOADING",ERROR:"ERROR",COMPLETE:"COMPLETE"};a.utils.scriptloader=function(c){var d=a.utils.loaderstatus.NEW;var b=new a.events.eventdispatcher();a.utils.extend(this,b);this.load=function(){if(d==a.utils.loaderstatus.NEW){d=a.utils.loaderstatus.LOADING;var e=document.createElement("script");e.onload=function(f){d=a.utils.loaderstatus.COMPLETE;b.sendEvent(a.events.COMPLETE)};e.onerror=function(f){d=a.utils.loaderstatus.ERROR;b.sendEvent(a.events.ERROR)};e.onreadystatechange=function(){if(e.readyState=="loaded"||e.readyState=="complete"){d=a.utils.loaderstatus.COMPLETE;b.sendEvent(a.events.COMPLETE)}};document.getElementsByTagName("head")[0].appendChild(e);e.src=c}};this.getStatus=function(){return d}}})(jwplayer);(function(a){a.utils.selectors=function(b,e){if(!a.utils.exists(e)){e=document}b=a.utils.strings.trim(b);var c=b.charAt(0);if(c=="#"){return e.getElementById(b.substr(1))}else{if(c=="."){if(e.getElementsByClassName){return e.getElementsByClassName(b.substr(1))}else{return a.utils.selectors.getElementsByTagAndClass("*",b.substr(1))}}else{if(b.indexOf(".")>0){var d=b.split(".");return a.utils.selectors.getElementsByTagAndClass(d[0],d[1])}else{return e.getElementsByTagName(b)}}}return null};a.utils.selectors.getElementsByTagAndClass=function(e,h,g){var j=[];if(!a.utils.exists(g)){g=document}var f=g.getElementsByTagName(e);for(var d=0;d<f.length;d++){if(a.utils.exists(f[d].className)){var c=f[d].className.split(" ");for(var b=0;b<c.length;b++){if(c[b]==h){j.push(f[d])}}}}return j}})(jwplayer);(function(a){a.utils.strings=function(){};a.utils.strings.trim=function(b){return b.replace(/^\s*/,"").replace(/\s*$/,"")};a.utils.strings.pad=function(c,d,b){if(!b){b="0"}while(c.length<d){c=b+c}return c};a.utils.strings.serialize=function(b){if(b==null){return null}else{if(b=="true"){return true}else{if(b=="false"){return false}else{if(isNaN(Number(b))||b.length>5||b.length==0){return b}else{return Number(b)}}}}};a.utils.strings.seconds=function(d){d=d.replace(",",".");var b=d.split(":");var c=0;if(d.substr(-1)=="s"){c=Number(d.substr(0,d.length-1))}else{if(d.substr(-1)=="m"){c=Number(d.substr(0,d.length-1))*60}else{if(d.substr(-1)=="h"){c=Number(d.substr(0,d.length-1))*3600}else{if(b.length>1){c=Number(b[b.length-1]);c+=Number(b[b.length-2])*60;if(b.length==3){c+=Number(b[b.length-3])*3600}}else{c=Number(d)}}}}return c};a.utils.strings.xmlAttribute=function(b,c){for(var d=0;d<b.attributes.length;d++){if(b.attributes[d].name&&b.attributes[d].name.toLowerCase()==c.toLowerCase()){return b.attributes[d].value.toString()}}return""};a.utils.strings.jsonToString=function(f){var h=h||{};if(h&&h.stringify){return h.stringify(f)}var c=typeof(f);if(c!="object"||f===null){if(c=="string"){f='"'+f+'"'}else{return String(f)}}else{var g=[],b=(f&&f.constructor==Array);for(var d in f){var e=f[d];switch(typeof(e)){case"string":e='"'+e+'"';break;case"object":if(a.utils.exists(e)){e=a.utils.strings.jsonToString(e)}break}if(b){if(typeof(e)!="function"){g.push(String(e))}}else{if(typeof(e)!="function"){g.push('"'+d+'":'+String(e))}}}if(b){return"["+String(g)+"]"}else{return"{"+String(g)+"}"}}}})(jwplayer);(function(c){var d=new RegExp(/^(#|0x)[0-9a-fA-F]{3,6}/);c.utils.typechecker=function(g,f){f=!c.utils.exists(f)?b(g):f;return e(g,f)};function b(f){var g=["true","false","t","f"];if(g.toString().indexOf(f.toLowerCase().replace(" ",""))>=0){return"boolean"}else{if(d.test(f)){return"color"}else{if(!isNaN(parseInt(f,10))&&parseInt(f,10).toString().length==f.length){return"integer"}else{if(!isNaN(parseFloat(f))&&parseFloat(f).toString().length==f.length){return"float"}}}}return"string"}function e(g,f){if(!c.utils.exists(f)){return g}switch(f){case"color":if(g.length>0){return a(g)}return null;case"integer":return parseInt(g,10);case"float":return parseFloat(g);case"boolean":if(g.toLowerCase()=="true"){return true}else{if(g=="1"){return true}}return false}return g}function a(f){switch(f.toLowerCase()){case"blue":return parseInt("0000FF",16);case"green":return parseInt("00FF00",16);case"red":return parseInt("FF0000",16);case"cyan":return parseInt("00FFFF",16);case"magenta":return parseInt("FF00FF",16);case"yellow":return parseInt("FFFF00",16);case"black":return parseInt("000000",16);case"white":return parseInt("FFFFFF",16);default:f=f.replace(/(#|0x)?([0-9A-F]{3,6})$/gi,"$2");if(f.length==3){f=f.charAt(0)+f.charAt(0)+f.charAt(1)+f.charAt(1)+f.charAt(2)+f.charAt(2)}return parseInt(f,16)}return parseInt("000000",16)}})(jwplayer);(function(a){a.utils.parsers=function(){};a.utils.parsers.localName=function(b){if(!b){return""}else{if(b.localName){return b.localName}else{if(b.baseName){return b.baseName}else{return""}}}};a.utils.parsers.textContent=function(b){if(!b){return""}else{if(b.textContent){return b.textContent}else{if(b.text){return b.text}else{return""}}}}})(jwplayer);(function(a){a.utils.parsers.jwparser=function(){};a.utils.parsers.jwparser.PREFIX="jwplayer";a.utils.parsers.jwparser.parseEntry=function(c,d){for(var b=0;b<c.childNodes.length;b++){if(c.childNodes[b].prefix==a.utils.parsers.jwparser.PREFIX){d[a.utils.parsers.localName(c.childNodes[b])]=a.utils.strings.serialize(a.utils.parsers.textContent(c.childNodes[b]))}if(!d.file&&String(d.link).toLowerCase().indexOf("youtube")>-1){d.file=d.link}}return d};a.utils.parsers.jwparser.getProvider=function(c){if(c.type){return c.type}else{if(c.file.indexOf("youtube.com/w")>-1||c.file.indexOf("youtube.com/v")>-1||c.file.indexOf("youtu.be/")>-1){return"youtube"}else{if(c.streamer&&c.streamer.indexOf("rtmp")==0){return"rtmp"}else{if(c.streamer&&c.streamer.indexOf("http")==0){return"http"}else{var b=a.utils.strings.extension(c.file);if(extensions.hasOwnProperty(b)){return extensions[b]}}}}}return""}})(jwplayer);(function(a){a.utils.parsers.mediaparser=function(){};a.utils.parsers.mediaparser.PREFIX="media";a.utils.parsers.mediaparser.parseGroup=function(d,f){var e=false;for(var c=0;c<d.childNodes.length;c++){if(d.childNodes[c].prefix==a.utils.parsers.mediaparser.PREFIX){if(!a.utils.parsers.localName(d.childNodes[c])){continue}switch(a.utils.parsers.localName(d.childNodes[c]).toLowerCase()){case"content":if(!e){f.file=a.utils.strings.xmlAttribute(d.childNodes[c],"url")}if(a.utils.strings.xmlAttribute(d.childNodes[c],"duration")){f.duration=a.utils.strings.seconds(a.utils.strings.xmlAttribute(d.childNodes[c],"duration"))}if(a.utils.strings.xmlAttribute(d.childNodes[c],"start")){f.start=a.utils.strings.seconds(a.utils.strings.xmlAttribute(d.childNodes[c],"start"))}if(d.childNodes[c].childNodes&&d.childNodes[c].childNodes.length>0){f=a.utils.parsers.mediaparser.parseGroup(d.childNodes[c],f)}if(a.utils.strings.xmlAttribute(d.childNodes[c],"width")||a.utils.strings.xmlAttribute(d.childNodes[c],"bitrate")||a.utils.strings.xmlAttribute(d.childNodes[c],"url")){if(!f.levels){f.levels=[]}f.levels.push({width:a.utils.strings.xmlAttribute(d.childNodes[c],"width"),bitrate:a.utils.strings.xmlAttribute(d.childNodes[c],"bitrate"),file:a.utils.strings.xmlAttribute(d.childNodes[c],"url")})}break;case"title":f.title=a.utils.parsers.textContent(d.childNodes[c]);break;case"description":f.description=a.utils.parsers.textContent(d.childNodes[c]);break;case"keywords":f.tags=a.utils.parsers.textContent(d.childNodes[c]);break;case"thumbnail":f.image=a.utils.strings.xmlAttribute(d.childNodes[c],"url");break;case"credit":f.author=a.utils.parsers.textContent(d.childNodes[c]);break;case"player":var b=d.childNodes[c].url;if(b.indexOf("youtube.com")>=0||b.indexOf("youtu.be")>=0){e=true;f.file=a.utils.strings.xmlAttribute(d.childNodes[c],"url")}break;case"group":a.utils.parsers.mediaparser.parseGroup(d.childNodes[c],f);break}}}return f}})(jwplayer);(function(b){b.utils.parsers.rssparser=function(){};b.utils.parsers.rssparser.parse=function(f){var c=[];for(var e=0;e<f.childNodes.length;e++){if(b.utils.parsers.localName(f.childNodes[e]).toLowerCase()=="channel"){for(var d=0;d<f.childNodes[e].childNodes.length;d++){if(b.utils.parsers.localName(f.childNodes[e].childNodes[d]).toLowerCase()=="item"){c.push(a(f.childNodes[e].childNodes[d]))}}}}return c};function a(d){var e={};for(var c=0;c<d.childNodes.length;c++){if(!b.utils.parsers.localName(d.childNodes[c])){continue}switch(b.utils.parsers.localName(d.childNodes[c]).toLowerCase()){case"enclosure":e.file=b.utils.strings.xmlAttribute(d.childNodes[c],"url");break;case"title":e.title=b.utils.parsers.textContent(d.childNodes[c]);break;case"pubdate":e.date=b.utils.parsers.textContent(d.childNodes[c]);break;case"description":e.description=b.utils.parsers.textContent(d.childNodes[c]);break;case"link":e.link=b.utils.parsers.textContent(d.childNodes[c]);break;case"category":if(e.tags){e.tags+=b.utils.parsers.textContent(d.childNodes[c])}else{e.tags=b.utils.parsers.textContent(d.childNodes[c])}break}}e=b.utils.parsers.mediaparser.parseGroup(d,e);e=b.utils.parsers.jwparser.parseEntry(d,e);return new b.html5.playlistitem(e)}})(jwplayer);(function(a){var c={};var b={};a.plugins=function(){};a.plugins.loadPlugins=function(e,d){b[e]=new a.plugins.pluginloader(new a.plugins.model(c),d);return b[e]};a.plugins.registerPlugin=function(h,f,e){var d=a.utils.getPluginName(h);if(c[d]){c[d].registerPlugin(h,f,e)}else{a.utils.log("A plugin ("+h+") was registered with the player that was not loaded. Please check your configuration.");for(var g in b){b[g].pluginFailed()}}}})(jwplayer);(function(a){a.plugins.model=function(b){this.addPlugin=function(c){var d=a.utils.getPluginName(c);if(!b[d]){b[d]=new a.plugins.plugin(c)}return b[d]}}})(jwplayer);(function(a){a.plugins.pluginmodes={FLASH:"FLASH",JAVASCRIPT:"JAVASCRIPT",HYBRID:"HYBRID"};a.plugins.plugin=function(b){var d="http://plugins.longtailvideo.com";var j=a.utils.loaderstatus.NEW;var k;var h;var l;var c=new a.events.eventdispatcher();a.utils.extend(this,c);function e(){switch(a.utils.getPluginPathType(b)){case a.utils.pluginPathType.ABSOLUTE:return b;case a.utils.pluginPathType.RELATIVE:return a.utils.getAbsolutePath(b,window.location.href);case a.utils.pluginPathType.CDN:var n=a.utils.getPluginName(b);var m=a.utils.getPluginVersion(b);return d+"/"+a.version.split(".")[0]+"/"+n+"/"+n+(m!==""?("-"+m):"")+".js"}}function g(m){l=setTimeout(function(){j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)},1000)}function f(m){j=a.utils.loaderstatus.ERROR;c.sendEvent(a.events.ERROR)}this.load=function(){if(j==a.utils.loaderstatus.NEW){if(b.lastIndexOf(".swf")>0){k=b;j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE);return}j=a.utils.loaderstatus.LOADING;var m=new a.utils.scriptloader(e());m.addEventListener(a.events.COMPLETE,g);m.addEventListener(a.events.ERROR,f);m.load()}};this.registerPlugin=function(o,n,m){if(l){clearTimeout(l);l=undefined}if(n&&m){k=m;h=n}else{if(typeof n=="string"){k=n}else{if(typeof n=="function"){h=n}else{if(!n&&!m){k=o}}}}j=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)};this.getStatus=function(){return j};this.getPluginName=function(){return a.utils.getPluginName(b)};this.getFlashPath=function(){if(k){switch(a.utils.getPluginPathType(k)){case a.utils.pluginPathType.ABSOLUTE:return k;case a.utils.pluginPathType.RELATIVE:if(b.lastIndexOf(".swf")>0){return a.utils.getAbsolutePath(k,window.location.href)}return a.utils.getAbsolutePath(k,e());case a.utils.pluginPathType.CDN:if(k.indexOf("-")>-1){return k+"h"}return k+"-h"}}return null};this.getJS=function(){return h};this.getPluginmode=function(){if(typeof k!="undefined"&&typeof h!="undefined"){return a.plugins.pluginmodes.HYBRID}else{if(typeof k!="undefined"){return a.plugins.pluginmodes.FLASH}else{if(typeof h!="undefined"){return a.plugins.pluginmodes.JAVASCRIPT}}}};this.getNewInstance=function(n,m,o){return new h(n,m,o)};this.getURL=function(){return b}}})(jwplayer);(function(a){a.plugins.pluginloader=function(h,e){var g={};var k=a.utils.loaderstatus.NEW;var d=false;var b=false;var c=new a.events.eventdispatcher();a.utils.extend(this,c);function f(){if(!b){b=true;k=a.utils.loaderstatus.COMPLETE;c.sendEvent(a.events.COMPLETE)}}function j(){if(!b){var m=0;for(plugin in g){var l=g[plugin].getStatus();if(l==a.utils.loaderstatus.LOADING||l==a.utils.loaderstatus.NEW){m++}}if(m==0){f()}}}this.setupPlugins=function(n,l,s){var m={length:0,plugins:{}};var p={length:0,plugins:{}};for(var o in g){var q=g[o].getPluginName();if(g[o].getFlashPath()){m.plugins[g[o].getFlashPath()]=l.plugins[o];m.plugins[g[o].getFlashPath()].pluginmode=g[o].getPluginmode();m.length++}if(g[o].getJS()){var r=document.createElement("div");r.id=n.id+"_"+q;r.style.position="absolute";r.style.zIndex=p.length+10;p.plugins[q]=g[o].getNewInstance(n,l.plugins[o],r);p.length++;if(typeof p.plugins[q].resize!="undefined"){n.onReady(s(p.plugins[q],r,true));n.onResize(s(p.plugins[q],r))}}}n.plugins=p.plugins;return m};this.load=function(){k=a.utils.loaderstatus.LOADING;d=true;for(var l in e){if(a.utils.exists(l)){g[l]=h.addPlugin(l);g[l].addEventListener(a.events.COMPLETE,j);g[l].addEventListener(a.events.ERROR,j)}}for(l in g){g[l].load()}d=false;j()};this.pluginFailed=function(){f()};this.getStatus=function(){return k}}})(jwplayer);(function(b){var a=[];b.api=function(d){this.container=d;this.id=d.id;var n={};var s={};var q={};var c=[];var h=undefined;var l=false;var j=[];var p=b.utils.getOuterHTML(d);var r={};var k={};this.getBuffer=function(){return this.callInternal("jwGetBuffer")};this.getContainer=function(){return this.container};function e(u,t){return function(z,v,w,x){if(u.renderingMode=="flash"||u.renderingMode=="html5"){var y;if(v){k[z]=v;y="jwplayer('"+u.id+"').callback('"+z+"')"}else{if(!v&&k[z]){delete k[z]}}h.jwDockSetButton(z,y,w,x)}return t}}this.getPlugin=function(t){var v=this;var u={};if(t=="dock"){return b.utils.extend(u,{setButton:e(v,u),show:function(){v.callInternal("jwDockShow");return u},hide:function(){v.callInternal("jwDockHide");return u},onShow:function(w){v.componentListener("dock",b.api.events.JWPLAYER_COMPONENT_SHOW,w);return u},onHide:function(w){v.componentListener("dock",b.api.events.JWPLAYER_COMPONENT_HIDE,w);return u}})}else{if(t=="controlbar"){return b.utils.extend(u,{show:function(){v.callInternal("jwControlbarShow");return u},hide:function(){v.callInternal("jwControlbarHide");return u},onShow:function(w){v.componentListener("controlbar",b.api.events.JWPLAYER_COMPONENT_SHOW,w);return u},onHide:function(w){v.componentListener("controlbar",b.api.events.JWPLAYER_COMPONENT_HIDE,w);return u}})}else{if(t=="display"){return b.utils.extend(u,{show:function(){v.callInternal("jwDisplayShow");return u},hide:function(){v.callInternal("jwDisplayHide");return u},onShow:function(w){v.componentListener("display",b.api.events.JWPLAYER_COMPONENT_SHOW,w);return u},onHide:function(w){v.componentListener("display",b.api.events.JWPLAYER_COMPONENT_HIDE,w);return u}})}else{return this.plugins[t]}}}};this.callback=function(t){if(k[t]){return k[t]()}};this.getDuration=function(){return this.callInternal("jwGetDuration")};this.getFullscreen=function(){return this.callInternal("jwGetFullscreen")};this.getHeight=function(){return this.callInternal("jwGetHeight")};this.getLockState=function(){return this.callInternal("jwGetLockState")};this.getMeta=function(){return this.getItemMeta()};this.getMute=function(){return this.callInternal("jwGetMute")};this.getPlaylist=function(){var u=this.callInternal("jwGetPlaylist");if(this.renderingMode=="flash"){b.utils.deepReplaceKeyName(u,"__dot__",".")}for(var t=0;t<u.length;t++){if(!b.utils.exists(u[t].index)){u[t].index=t}}return u};this.getPlaylistItem=function(t){if(!b.utils.exists(t)){t=this.getCurrentItem()}return this.getPlaylist()[t]};this.getPosition=function(){return this.callInternal("jwGetPosition")};this.getRenderingMode=function(){return this.renderingMode};this.getState=function(){return this.callInternal("jwGetState")};this.getVolume=function(){return this.callInternal("jwGetVolume")};this.getWidth=function(){return this.callInternal("jwGetWidth")};this.setFullscreen=function(t){if(!b.utils.exists(t)){this.callInternal("jwSetFullscreen",!this.callInternal("jwGetFullscreen"))}else{this.callInternal("jwSetFullscreen",t)}return this};this.setMute=function(t){if(!b.utils.exists(t)){this.callInternal("jwSetMute",!this.callInternal("jwGetMute"))}else{this.callInternal("jwSetMute",t)}return this};this.lock=function(){return this};this.unlock=function(){return this};this.load=function(t){this.callInternal("jwLoad",t);return this};this.playlistItem=function(t){this.callInternal("jwPlaylistItem",t);return this};this.playlistPrev=function(){this.callInternal("jwPlaylistPrev");return this};this.playlistNext=function(){this.callInternal("jwPlaylistNext");return this};this.resize=function(u,t){if(this.renderingMode=="html5"){h.jwResize(u,t)}else{this.container.width=u;this.container.height=t}return this};this.play=function(t){if(typeof t=="undefined"){t=this.getState();if(t==b.api.events.state.PLAYING||t==b.api.events.state.BUFFERING){this.callInternal("jwPause")}else{this.callInternal("jwPlay")}}else{this.callInternal("jwPlay",t)}return this};this.pause=function(t){if(typeof t=="undefined"){t=this.getState();if(t==b.api.events.state.PLAYING||t==b.api.events.state.BUFFERING){this.callInternal("jwPause")}else{this.callInternal("jwPlay")}}else{this.callInternal("jwPause",t)}return this};this.stop=function(){this.callInternal("jwStop");return this};this.seek=function(t){this.callInternal("jwSeek",t);return this};this.setVolume=function(t){this.callInternal("jwSetVolume",t);return this};this.onBufferChange=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BUFFER,t)};this.onBufferFull=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_BUFFER_FULL,t)};this.onError=function(t){return this.eventListener(b.api.events.JWPLAYER_ERROR,t)};this.onFullscreen=function(t){return this.eventListener(b.api.events.JWPLAYER_FULLSCREEN,t)};this.onMeta=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_META,t)};this.onMute=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_MUTE,t)};this.onPlaylist=function(t){return this.eventListener(b.api.events.JWPLAYER_PLAYLIST_LOADED,t)};this.onPlaylistItem=function(t){return this.eventListener(b.api.events.JWPLAYER_PLAYLIST_ITEM,t)};this.onReady=function(t){return this.eventListener(b.api.events.API_READY,t)};this.onResize=function(t){return this.eventListener(b.api.events.JWPLAYER_RESIZE,t)};this.onComplete=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_COMPLETE,t)};this.onSeek=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_SEEK,t)};this.onTime=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_TIME,t)};this.onVolume=function(t){return this.eventListener(b.api.events.JWPLAYER_MEDIA_VOLUME,t)};this.onBuffer=function(t){return this.stateListener(b.api.events.state.BUFFERING,t)};this.onPause=function(t){return this.stateListener(b.api.events.state.PAUSED,t)};this.onPlay=function(t){return this.stateListener(b.api.events.state.PLAYING,t)};this.onIdle=function(t){return this.stateListener(b.api.events.state.IDLE,t)};this.remove=function(){n={};j=[];if(b.utils.getOuterHTML(this.container)!=p){b.api.destroyPlayer(this.id,p)}};this.setup=function(u){if(b.embed){var t=this.id;this.remove();var v=b(t);v.config=u;return new b.embed(v)}return this};this.registerPlugin=function(v,u,t){b.plugins.registerPlugin(v,u,t)};this.setPlayer=function(t,u){h=t;this.renderingMode=u};this.stateListener=function(t,u){if(!s[t]){s[t]=[];this.eventListener(b.api.events.JWPLAYER_PLAYER_STATE,g(t))}s[t].push(u);return this};function g(t){return function(v){var u=v.newstate,x=v.oldstate;if(u==t){var w=s[u];if(w){for(var y=0;y<w.length;y++){if(typeof w[y]=="function"){w[y].call(this,{oldstate:x,newstate:u})}}}}}}this.componentListener=function(t,u,v){if(!q[t]){q[t]={}}if(!q[t][u]){q[t][u]=[];this.eventListener(u,m(t,u))}q[t][u].push(v);return this};function m(t,u){return function(w){if(t==w.component){var v=q[t][u];if(v){for(var x=0;x<v.length;x++){if(typeof v[x]=="function"){v[x].call(this,w)}}}}}}this.addInternalListener=function(t,u){t.jwAddEventListener(u,'function(dat) { jwplayer("'+this.id+'").dispatchEvent("'+u+'", dat); }')};this.eventListener=function(t,u){if(!n[t]){n[t]=[];if(h&&l){this.addInternalListener(h,t)}}n[t].push(u);return this};this.dispatchEvent=function(v){if(n[v]){var u=f(v,arguments[1]);for(var t=0;t<n[v].length;t++){if(typeof n[v][t]=="function"){n[v][t].call(this,u)}}}};function f(v,t){var x=b.utils.extend({},t);if(v==b.api.events.JWPLAYER_FULLSCREEN&&!x.fullscreen){x.fullscreen=x.message=="true"?true:false;delete x.message}else{if(typeof x.data=="object"){x=b.utils.extend(x,x.data);delete x.data}}var u=["position","duration","offset"];for(var w in u){if(x[u[w]]){x[u[w]]=Math.round(x[u[w]]*1000)/1000}}return x}this.callInternal=function(u,t){if(l){if(typeof h!="undefined"&&typeof h[u]=="function"){if(b.utils.exists(t)){return(h[u])(t)}else{return(h[u])()}}return null}else{j.push({method:u,parameters:t})}};this.playerReady=function(v){l=true;if(!h){this.setPlayer(document.getElementById(v.id))}this.container=document.getElementById(this.id);for(var t in n){this.addInternalListener(h,t)}this.eventListener(b.api.events.JWPLAYER_PLAYLIST_ITEM,function(w){r={}});this.eventListener(b.api.events.JWPLAYER_MEDIA_META,function(w){b.utils.extend(r,w.metadata)});this.dispatchEvent(b.api.events.API_READY);while(j.length>0){var u=j.shift();this.callInternal(u.method,u.parameters)}};this.getItemMeta=function(){return r};this.getCurrentItem=function(){return this.callInternal("jwGetPlaylistIndex")};function o(v,x,w){var t=[];if(!x){x=0}if(!w){w=v.length-1}for(var u=x;u<=w;u++){t.push(v[u])}return t}return this};b.api.selectPlayer=function(d){var c;if(!b.utils.exists(d)){d=0}if(d.nodeType){c=d}else{if(typeof d=="string"){c=document.getElementById(d)}}if(c){var e=b.api.playerById(c.id);if(e){return e}else{return b.api.addPlayer(new b.api(c))}}else{if(typeof d=="number"){return b.getPlayers()[d]}}return null};b.api.events={API_READY:"jwplayerAPIReady",JWPLAYER_READY:"jwplayerReady",JWPLAYER_FULLSCREEN:"jwplayerFullscreen",JWPLAYER_RESIZE:"jwplayerResize",JWPLAYER_ERROR:"jwplayerError",JWPLAYER_COMPONENT_SHOW:"jwplayerComponentShow",JWPLAYER_COMPONENT_HIDE:"jwplayerComponentHide",JWPLAYER_MEDIA_BUFFER:"jwplayerMediaBuffer",JWPLAYER_MEDIA_BUFFER_FULL:"jwplayerMediaBufferFull",JWPLAYER_MEDIA_ERROR:"jwplayerMediaError",JWPLAYER_MEDIA_LOADED:"jwplayerMediaLoaded",JWPLAYER_MEDIA_COMPLETE:"jwplayerMediaComplete",JWPLAYER_MEDIA_SEEK:"jwplayerMediaSeek",JWPLAYER_MEDIA_TIME:"jwplayerMediaTime",JWPLAYER_MEDIA_VOLUME:"jwplayerMediaVolume",JWPLAYER_MEDIA_META:"jwplayerMediaMeta",JWPLAYER_MEDIA_MUTE:"jwplayerMediaMute",JWPLAYER_PLAYER_STATE:"jwplayerPlayerState",JWPLAYER_PLAYLIST_LOADED:"jwplayerPlaylistLoaded",JWPLAYER_PLAYLIST_ITEM:"jwplayerPlaylistItem"};b.api.events.state={BUFFERING:"BUFFERING",IDLE:"IDLE",PAUSED:"PAUSED",PLAYING:"PLAYING"};b.api.playerById=function(d){for(var c=0;c<a.length;c++){if(a[c].id==d){return a[c]}}return null};b.api.addPlayer=function(c){for(var d=0;d<a.length;d++){if(a[d]==c){return c}}a.push(c);return c};b.api.destroyPlayer=function(g,d){var f=-1;for(var j=0;j<a.length;j++){if(a[j].id==g){f=j;continue}}if(f>=0){var c=document.getElementById(a[f].id);if(document.getElementById(a[f].id+"_wrapper")){c=document.getElementById(a[f].id+"_wrapper")}if(c){if(d){b.utils.setOuterHTML(c,d)}else{var h=document.createElement("div");var e=c.id;if(c.id.indexOf("_wrapper")==c.id.length-8){newID=c.id.substring(0,c.id.length-8)}h.setAttribute("id",e);c.parentNode.replaceChild(h,c)}}a.splice(f,1)}return null};b.getPlayers=function(){return a.slice(0)}})(jwplayer);var _userPlayerReady=(typeof playerReady=="function")?playerReady:undefined;playerReady=function(b){var a=jwplayer.api.playerById(b.id);if(a){a.playerReady(b)}else{jwplayer.api.selectPlayer(b.id).playerReady(b)}if(_userPlayerReady){_userPlayerReady.call(this,b)}};(function(a){a.embed=function(g){var j={width:400,height:300,components:{controlbar:{position:"over"}}};var f=a.utils.mediaparser.parseMedia(g.container);var e=new a.embed.config(a.utils.extend(j,f,g.config),this);var h=a.plugins.loadPlugins(g.id,e.plugins);function c(m,l){for(var k in l){if(typeof m[k]=="function"){(m[k]).call(m,l[k])}}}function d(){if(h.getStatus()==a.utils.loaderstatus.COMPLETE){for(var m=0;m<e.modes.length;m++){if(e.modes[m].type&&a.embed[e.modes[m].type]){var k=e;if(e.modes[m].config){k=a.utils.extend(a.utils.clone(e),e.modes[m].config)}var l=new a.embed[e.modes[m].type](document.getElementById(g.id),e.modes[m],k,h,g);if(l.supportsConfig()){l.embed();c(g,e.events);return g}}}a.utils.log("No suitable players found");new a.embed.logo(a.utils.extend({hide:true},e.components.logo),"none",g.id)}}h.addEventListener(a.events.COMPLETE,d);h.addEventListener(a.events.ERROR,d);h.load();return g};function b(){if(!document.body){return setTimeout(b,15)}var c=a.utils.selectors.getElementsByTagAndClass("video","jwplayer");for(var d=0;d<c.length;d++){var e=c[d];a(e.id).setup({})}}b()})(jwplayer);(function(e){function h(){return[{type:"flash",src:"/jwplayer/player.swf"},{type:"html5"},{type:"download"}]}var a={players:"modes",autoplay:"autostart"};function b(n){var m=n.toLowerCase();var l=["left","right","top","bottom"];for(var k=0;k<l.length;k++){if(m==l[k]){return true}}return false}function c(l){var k=false;k=(l instanceof Array)||(typeof l=="object"&&!l.position&&!l.size);return k}function j(k){if(typeof k=="string"){if(parseInt(k).toString()==k||k.toLowerCase().indexOf("px")>-1){return parseInt(k)}}return k}var g=["playlist","dock","controlbar","logo","display"];function f(k){var n={};switch(e.utils.typeOf(k.plugins)){case"object":for(var m in k.plugins){n[e.utils.getPluginName(m)]=m}break;case"string":var o=k.plugins.split(",");for(var l=0;l<o.length;l++){n[e.utils.getPluginName(o[l])]=o[l]}break}return n}function d(o,n,m,k){if(e.utils.typeOf(o[n])!="object"){o[n]={}}var l=o[n][m];if(e.utils.typeOf(l)!="object"){o[n][m]=l={}}if(k){if(n=="plugins"){var p=e.utils.getPluginName(m);l[k]=o[p+"."+k];delete o[p+"."+k]}else{l[k]=o[m+"."+k];delete o[m+"."+k]}}}e.embed.deserialize=function(l){var m=f(l);for(var k in m){d(l,"plugins",m[k])}for(var p in l){if(p.indexOf(".")>-1){var o=p.split(".");var n=o[0];var p=o[1];if(e.utils.isInArray(g,n)){d(l,"components",n,p)}else{if(m[n]){d(l,"plugins",m[n],p)}}}}return l};e.embed.config=function(k,u){var t=e.utils.extend({},k);var r;if(c(t.playlist)){r=t.playlist;delete t.playlist}t=e.embed.deserialize(t);t.height=j(t.height);t.width=j(t.width);if(typeof t.plugins=="string"){var l=t.plugins.split(",");if(typeof t.plugins!="object"){t.plugins={}}for(var p=0;p<l.length;p++){var q=e.utils.getPluginName(l[p]);if(typeof t[q]=="object"){t.plugins[l[p]]=t[q];delete t[q]}else{t.plugins[l[p]]={}}}}for(var s=0;s<g.length;s++){var o=g[s];if(e.utils.exists(t[o])){if(typeof t[o]!="object"){if(!t.components[o]){t.components[o]={}}if(o=="logo"){t.components[o].file=t[o]}else{t.components[o].position=t[o]}delete t[o]}else{if(!t.components[o]){t.components[o]={}}e.utils.extend(t.components[o],t[o]);delete t[o]}}if(typeof t[o+"size"]!="undefined"){if(!t.components[o]){t.components[o]={}}t.components[o].size=t[o+"size"];delete t[o+"size"]}}if(typeof t.icons!="undefined"){if(!t.components.display){t.components.display={}}t.components.display.icons=t.icons;delete t.icons}for(var n in a){if(t[n]){if(!t[a[n]]){t[a[n]]=t[n]}delete t[n]}}var m;if(t.flashplayer&&!t.modes){m=h();m[0].src=t.flashplayer;delete t.flashplayer}else{if(t.modes){if(typeof t.modes=="string"){m=h();m[0].src=t.modes}else{if(t.modes instanceof Array){m=t.modes}else{if(typeof t.modes=="object"&&t.modes.type){m=[t.modes]}}}delete t.modes}else{m=h()}}t.modes=m;if(r){t.playlist=r}return t}})(jwplayer);(function(a){a.embed.download=function(c,g,b,d,f){this.embed=function(){var k=a.utils.extend({},b);var q={};var j=b.width?b.width:480;if(typeof j!="number"){j=parseInt(j,10)}var m=b.height?b.height:320;if(typeof m!="number"){m=parseInt(m,10)}var u,o,n;var s={};if(b.playlist&&b.playlist.length){s.file=b.playlist[0].file;o=b.playlist[0].image;s.levels=b.playlist[0].levels}else{s.file=b.file;o=b.image;s.levels=b.levels}if(s.file){u=s.file}else{if(s.levels&&s.levels.length){u=s.levels[0].file}}n=u?"pointer":"auto";var l={display:{style:{cursor:n,width:j,height:m,backgroundColor:"#000",position:"relative",textDecoration:"none",border:"none",display:"block"}},display_icon:{style:{cursor:n,position:"absolute",display:u?"block":"none",top:0,left:0,border:0,margin:0,padding:0,zIndex:3,width:50,height:50,backgroundImage:"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAALdJREFUeNrs18ENgjAYhmFouDOCcQJGcARHgE10BDcgTOIosAGwQOuPwaQeuFRi2p/3Sb6EC5L3QCxZBgAAAOCorLW1zMn65TrlkH4NcV7QNcUQt7Gn7KIhxA+qNIR81spOGkL8oFJDyLJRdosqKDDkK+iX5+d7huzwM40xptMQMkjIOeRGo+VkEVvIPfTGIpKASfYIfT9iCHkHrBEzf4gcUQ56aEzuGK/mw0rHpy4AAACAf3kJMACBxjAQNRckhwAAAABJRU5ErkJggg==)"}},display_iconBackground:{style:{cursor:n,position:"absolute",display:u?"block":"none",top:((m-50)/2),left:((j-50)/2),border:0,width:50,height:50,margin:0,padding:0,zIndex:2,backgroundImage:"url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEpJREFUeNrszwENADAIA7DhX8ENoBMZ5KR10EryckCJiIiIiIiIiIiIiIiIiIiIiIh8GmkRERERERERERERERERERERERGRHSPAAPlXH1phYpYaAAAAAElFTkSuQmCC)"}},display_image:{style:{width:j,height:m,display:o?"block":"none",position:"absolute",cursor:n,left:0,top:0,margin:0,padding:0,textDecoration:"none",zIndex:1,border:"none"}}};var h=function(v,x,y){var w=document.createElement(v);if(y){w.id=y}else{w.id=c.id+"_jwplayer_"+x}a.utils.css(w,l[x].style);return w};q.display=h("a","display",c.id);if(u){q.display.setAttribute("href",a.utils.getAbsolutePath(u))}q.display_image=h("img","display_image");q.display_image.setAttribute("alt","Click to download...");if(o){q.display_image.setAttribute("src",a.utils.getAbsolutePath(o))}if(true){q.display_icon=h("div","display_icon");q.display_iconBackground=h("div","display_iconBackground");q.display.appendChild(q.display_image);q.display_iconBackground.appendChild(q.display_icon);q.display.appendChild(q.display_iconBackground)}_css=a.utils.css;_hide=function(v){_css(v,{display:"none"})};function r(v){_imageWidth=q.display_image.naturalWidth;_imageHeight=q.display_image.naturalHeight;t()}function t(){a.utils.stretch(a.utils.stretching.UNIFORM,q.display_image,j,m,_imageWidth,_imageHeight)}q.display_image.onerror=function(v){_hide(q.display_image)};q.display_image.onload=r;c.parentNode.replaceChild(q.display,c);var p=(b.plugins&&b.plugins.logo)?b.plugins.logo:{};q.display.appendChild(new a.embed.logo(b.components.logo,"download",c.id));f.container=document.getElementById(f.id);f.setPlayer(q.display,"download")};this.supportsConfig=function(){if(b){var j=a.utils.getFirstPlaylistItemFromConfig(b);if(typeof j.file=="undefined"&&typeof j.levels=="undefined"){return true}else{if(j.file){return e(j.file,j.provider,j.playlistfile)}else{if(j.levels&&j.levels.length){for(var h=0;h<j.levels.length;h++){if(j.levels[h].file&&e(j.levels[h].file,j.provider,j.playlistfile)){return true}}}}}}else{return true}};function e(j,l,h){if(h){return false}var k=["image","sound","youtube","http"];if(l&&(k.toString().indexOf(l)>-1)){return true}if(!l||(l&&l=="video")){var m=a.utils.extension(j);if(m&&a.utils.extensionmap[m]){return true}}return false}}})(jwplayer);(function(a){a.embed.flash=function(f,g,l,e,j){function m(o,n,p){var q=document.createElement("param");q.setAttribute("name",n);q.setAttribute("value",p);o.appendChild(q)}function k(o,p,n){return function(q){if(n){document.getElementById(j.id+"_wrapper").appendChild(p)}var s=document.getElementById(j.id).getPluginConfig("display");o.resize(s.width,s.height);var r={left:s.x,top:s.y};a.utils.css(p,r)}}function d(p){if(!p){return{}}var r={};for(var o in p){var n=p[o];for(var q in n){r[o+"."+q]=n[q]}}return r}function h(q,p){if(q[p]){var s=q[p];for(var o in s){var n=s[o];if(typeof n=="string"){if(!q[o]){q[o]=n}}else{for(var r in n){if(!q[o+"."+r]){q[o+"."+r]=n[r]}}}}delete q[p]}}function b(q){if(!q){return{}}var t={},s=[];for(var n in q){var p=a.utils.getPluginName(n);var o=q[n];s.push(n);for(var r in o){t[p+"."+r]=o[r]}}t.plugins=s.join(",");return t}function c(p){var n=p.netstreambasepath?"":"netstreambasepath="+encodeURIComponent(window.location.href.split("#")[0])+"&";for(var o in p){if(typeof(p[o])=="object"){n+=o+"="+encodeURIComponent("[[JSON]]"+a.utils.strings.jsonToString(p[o]))+"&"}else{n+=o+"="+encodeURIComponent(p[o])+"&"}}return n.substring(0,n.length-1)}this.embed=function(){l.id=j.id;var y;var q=a.utils.extend({},l);var n=q.width;var w=q.height;if(f.id+"_wrapper"==f.parentNode.id){y=document.getElementById(f.id+"_wrapper")}else{y=document.createElement("div");y.id=f.id+"_wrapper";a.utils.wrap(f,y);a.utils.css(y,{position:"relative",width:n,height:w})}var o=e.setupPlugins(j,q,k);if(o.length>0){a.utils.extend(q,b(o.plugins))}else{delete q.plugins}var r=["height","width","modes","events"];for(var u=0;u<r.length;u++){delete q[r[u]]}var p="opaque";if(q.wmode){p=q.wmode}h(q,"components");h(q,"providers");if(typeof q["dock.position"]!="undefined"){if(q["dock.position"].toString().toLowerCase()=="false"){q.dock=q["dock.position"];delete q["dock.position"]}}var x="#000000";var t;if(a.utils.isIE()){var v='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" bgcolor="'+x+'" width="100%" height="100%" id="'+f.id+'" name="'+f.id+'" tabindex=0"">';v+='<param name="movie" value="'+g.src+'">';v+='<param name="allowfullscreen" value="true">';v+='<param name="allowscriptaccess" value="always">';v+='<param name="seamlesstabbing" value="true">';v+='<param name="wmode" value="'+p+'">';v+='<param name="flashvars" value="'+c(q)+'">';v+="</object>";a.utils.setOuterHTML(f,v);t=document.getElementById(f.id)}else{var s=document.createElement("object");s.setAttribute("type","application/x-shockwave-flash");s.setAttribute("data",g.src);s.setAttribute("width","100%");s.setAttribute("height","100%");s.setAttribute("bgcolor","#000000");s.setAttribute("id",f.id);s.setAttribute("name",f.id);s.setAttribute("tabindex",0);m(s,"allowfullscreen","true");m(s,"allowscriptaccess","always");m(s,"seamlesstabbing","true");m(s,"wmode",p);m(s,"flashvars",c(q));f.parentNode.replaceChild(s,f);t=s}j.container=t;j.setPlayer(t,"flash")};this.supportsConfig=function(){if(a.utils.hasFlash()){if(l){var o=a.utils.getFirstPlaylistItemFromConfig(l);if(typeof o.file=="undefined"&&typeof o.levels=="undefined"){return true}else{if(o.file){return flashCanPlay(o.file,o.provider)}else{if(o.levels&&o.levels.length){for(var n=0;n<o.levels.length;n++){if(o.levels[n].file&&flashCanPlay(o.levels[n].file,o.provider)){return true}}}}}}else{return true}}return false};flashCanPlay=function(n,p){var o=["video","http","sound","image"];if(p&&(o.toString().indexOf(p<0))){return true}var q=a.utils.extension(n);if(!q){return true}if(a.utils.exists(a.utils.extensionmap[q])&&!a.utils.exists(a.utils.extensionmap[q].flash)){return false}return true}}})(jwplayer);(function(a){a.embed.html5=function(c,g,b,d,f){function e(j,k,h){return function(l){var m=document.getElementById(c.id+"_displayarea");if(h){m.appendChild(k)}var n=m.style;j.resize(parseInt(n.width.replace("px","")),parseInt(n.height.replace("px","")));k.left=n.left;k.top=n.top}}this.embed=function(){if(a.html5){d.setupPlugins(f,b,e);c.innerHTML="";var j=a.utils.extend({screencolor:"0x000000"},b);var h=["plugins","modes","events"];for(var k=0;k<h.length;k++){delete j[h[k]]}if(j.levels&&!j.sources){j.sources=b.levels}if(j.skin&&j.skin.toLowerCase().indexOf(".zip")>0){j.skin=j.skin.replace(/\.zip/i,".xml")}var l=new (a.html5(c)).setup(j);f.container=document.getElementById(f.id);f.setPlayer(l,"html5")}else{return null}};this.supportsConfig=function(){if(!!a.vid.canPlayType){if(b){var j=a.utils.getFirstPlaylistItemFromConfig(b);if(typeof j.file=="undefined"&&typeof j.levels=="undefined"){return true}else{if(j.file){return html5CanPlay(a.vid,j.file,j.provider,j.playlistfile)}else{if(j.levels&&j.levels.length){for(var h=0;h<j.levels.length;h++){if(j.levels[h].file&&html5CanPlay(a.vid,j.levels[h].file,j.provider,j.playlistfile)){return true}}}}}}else{return true}}return false};html5CanPlay=function(k,j,l,h){if(h){return false}if(l&&l=="youtube"){return true}if(l&&l!="video"&&l!="http"&&l!="sound"){return false}var m=a.utils.extension(j);if(!a.utils.exists(m)||!a.utils.exists(a.utils.extensionmap[m])){return true}if(!a.utils.exists(a.utils.extensionmap[m].html5)){return false}if(a.utils.isLegacyAndroid()&&m.match(/m4v|mp4/)){return true}return browserCanPlay(k,a.utils.extensionmap[m].html5)};browserCanPlay=function(j,h){if(!h){return true}if(j.canPlayType(h)){return true}else{if(h=="audio/mp3"&&navigator.userAgent.match(/safari/i)){return j.canPlayType("audio/mpeg")}else{return false}}}}})(jwplayer);(function(a){a.embed.logo=function(m,l,d){var j={prefix:"http://l.longtailvideo.com/"+l+"/",file:"logo.png",link:"http://www.longtailvideo.com/players/jw-flv-player/",margin:8,out:0.5,over:1,timeout:5,hide:false,position:"bottom-left"};_css=a.utils.css;var b;var h;k();function k(){o();c();f()}function o(){if(j.prefix){var q=a.version.split(/\W/).splice(0,2).join("/");if(j.prefix.indexOf(q)<0){j.prefix+=q+"/"}}h=a.utils.extend({},j)}function p(){var s={border:"none",textDecoration:"none",position:"absolute",cursor:"pointer",zIndex:10};s.display=h.hide?"none":"block";var r=h.position.toLowerCase().split("-");for(var q in r){s[r[q]]=h.margin}return s}function c(){b=document.createElement("img");b.id=d+"_jwplayer_logo";b.style.display="none";b.onload=function(q){_css(b,p());e()};if(!h.file){return}if(h.file.indexOf("http://")===0){b.src=h.file}else{b.src=h.prefix+h.file}}if(!h.file){return}function f(){if(h.link){b.onmouseover=g;b.onmouseout=e;b.onclick=n}else{this.mouseEnabled=false}}function n(q){if(typeof q!="undefined"){q.preventDefault();q.stopPropagation()}if(h.link){window.open(h.link,"_blank")}return}function e(q){if(h.link){b.style.opacity=h.out}return}function g(q){if(h.hide){b.style.opacity=h.over}return}return b}})(jwplayer);(function(a){a.html5=function(b){var c=b;this.setup=function(d){a.utils.extend(this,new a.html5.api(c,d));return this};return this}})(jwplayer);(function(b){var d=b.utils;var c=d.css;b.html5.view=function(r,q,f){var u=r;var n=q;var x=f;var w;var g;var C;var s;var D;var p;var A;function z(){w=document.createElement("div");w.id=n.id;w.className=n.className;_videowrapper=document.createElement("div");_videowrapper.id=w.id+"_video_wrapper";n.id=w.id+"_video";c(w,{position:"relative",height:x.height,width:x.width,padding:0,backgroundColor:E(),zIndex:0});function E(){if(u.skin.getComponentSettings("display")&&u.skin.getComponentSettings("display").backgroundcolor){return u.skin.getComponentSettings("display").backgroundcolor}return parseInt("000000",16)}c(n,{width:x.width,height:x.height,top:0,left:0,zIndex:1,margin:"auto",display:"block"});c(_videowrapper,{overflow:"hidden",position:"absolute",top:0,left:0,bottom:0,right:0});d.wrap(n,w);d.wrap(n,_videowrapper);s=document.createElement("div");s.id=w.id+"_displayarea";w.appendChild(s)}function k(){for(var E=0;E<x.plugins.order.length;E++){var F=x.plugins.order[E];if(d.exists(x.plugins.object[F].getDisplayElement)){x.plugins.object[F].height=d.parseDimension(x.plugins.object[F].getDisplayElement().style.height);x.plugins.object[F].width=d.parseDimension(x.plugins.object[F].getDisplayElement().style.width);x.plugins.config[F].currentPosition=x.plugins.config[F].position}}v()}function m(E){c(s,{display:x.getMedia().hasChrome()?"none":"block"})}function v(F){var H=x.getMedia()?x.getMedia().getDisplayElement():null;if(d.exists(H)){if(A!=H){if(A&&A.parentNode){A.parentNode.replaceChild(H,A)}A=H}for(var E=0;E<x.plugins.order.length;E++){var G=x.plugins.order[E];if(d.exists(x.plugins.object[G].getDisplayElement)){x.plugins.config[G].currentPosition=x.plugins.config[G].position}}}j(x.width,x.height)}this.setup=function(){if(x&&x.getMedia()){n=x.getMedia().getDisplayElement()}z();k();u.jwAddEventListener(b.api.events.JWPLAYER_PLAYER_STATE,m);u.jwAddEventListener(b.api.events.JWPLAYER_MEDIA_LOADED,v);u.jwAddEventListener(b.api.events.JWPLAYER_MEDIA_META,function(){y()});var E;if(d.exists(window.onresize)){E=window.onresize}window.onresize=function(F){if(d.exists(E)){try{E(F)}catch(H){}}if(u.jwGetFullscreen()){var G=document.body.getBoundingClientRect();x.width=Math.abs(G.left)+Math.abs(G.right);x.height=window.innerHeight}j(x.width,x.height)}};function h(E){switch(E.keyCode){case 27:if(u.jwGetFullscreen()){u.jwSetFullscreen(false)}break;case 32:if(u.jwGetState()!=b.api.events.state.IDLE&&u.jwGetState()!=b.api.events.state.PAUSED){u.jwPause()}else{u.jwPlay()}break}}function j(H,E){if(w.style.display=="none"){return}var G=[].concat(x.plugins.order);G.reverse();D=G.length+2;if(!x.fullscreen){x.width=H;x.height=E;g=H;C=E;c(s,{top:0,bottom:0,left:0,right:0,width:H,height:E,position:"relative"});c(w,{height:C,width:g});var F=o(t,G);if(F.length>0){D+=F.length;var J=F.indexOf("playlist"),I=F.indexOf("controlbar");if(J>=0&&I>=0){F[J]=F.splice(I,1,F[J])[0]}o(l,F,true)}}else{if(!(navigator&&navigator.vendor&&navigator.vendor.indexOf("Apple")==0)){o(B,G,true)}}y()}function o(J,G,H){var F=[];for(var E=0;E<G.length;E++){var K=G[E];if(d.exists(x.plugins.object[K].getDisplayElement)){if(x.plugins.config[K].currentPosition!=b.html5.view.positions.NONE){var I=J(K,D--);if(!I){F.push(K)}else{x.plugins.object[K].resize(I.width,I.height);if(H){delete I.width;delete I.height}c(x.plugins.object[K].getDisplayElement(),I)}}else{c(x.plugins.object[K].getDisplayElement(),{display:"none"})}}}return F}function t(F,G){if(d.exists(x.plugins.object[F].getDisplayElement)){if(x.plugins.config[F].position&&a(x.plugins.config[F].position)){if(!d.exists(x.plugins.object[F].getDisplayElement().parentNode)){w.appendChild(x.plugins.object[F].getDisplayElement())}var E=e(F);E.zIndex=G;return E}}return false}function l(G,H){if(!d.exists(x.plugins.object[G].getDisplayElement().parentNode)){s.appendChild(x.plugins.object[G].getDisplayElement())}var E=x.width,F=x.height;if(typeof x.width=="string"&&x.width.lastIndexOf("%")>-1){percentage=parseFloat(x.width.substring(0,x.width.lastIndexOf("%")))/100;E=Math.round(window.innerWidth*percentage)}if(typeof x.height=="string"&&x.height.lastIndexOf("%")>-1){percentage=parseFloat(x.height.substring(0,x.height.lastIndexOf("%")))/100;F=Math.round(window.innerHeight*percentage)}return{position:"absolute",width:(E-d.parseDimension(s.style.left)-d.parseDimension(s.style.right)),height:(F-d.parseDimension(s.style.top)-d.parseDimension(s.style.bottom)),zIndex:H}}function B(E,F){return{position:"fixed",width:x.width,height:x.height,zIndex:F}}function y(){if(!d.exists(x.getMedia())){return}s.style.position="absolute";var H=x.getMedia().getDisplayElement();if(H&&H.tagName.toLowerCase()=="video"){H.style.position="absolute";var E,I;if(s.style.width.toString().lastIndexOf("%")>-1||s.style.width.toString().lastIndexOf("%")>-1){var F=s.getBoundingClientRect();E=Math.abs(F.left)+Math.abs(F.right);I=Math.abs(F.top)+Math.abs(F.bottom)}else{E=d.parseDimension(s.style.width);I=d.parseDimension(s.style.height)}if(H.parentNode){H.parentNode.style.left=s.style.left;H.parentNode.style.top=s.style.top}d.stretch(u.jwGetStretching(),H,E,I,H.videoWidth?H.videoWidth:400,H.videoHeight?H.videoHeight:300)}else{var G=x.plugins.object.display.getDisplayElement();if(G){x.getMedia().resize(d.parseDimension(G.style.width),d.parseDimension(G.style.height))}else{x.getMedia().resize(d.parseDimension(s.style.width),d.parseDimension(s.style.height))}}}function e(F){var G={position:"absolute",margin:0,padding:0,top:null};var E=x.plugins.config[F].currentPosition.toLowerCase();switch(E.toUpperCase()){case b.html5.view.positions.TOP:G.top=d.parseDimension(s.style.top);G.left=d.parseDimension(s.style.left);G.width=g-d.parseDimension(s.style.left)-d.parseDimension(s.style.right);G.height=x.plugins.object[F].height;s.style[E]=d.parseDimension(s.style[E])+x.plugins.object[F].height+"px";s.style.height=d.parseDimension(s.style.height)-G.height+"px";break;case b.html5.view.positions.RIGHT:G.top=d.parseDimension(s.style.top);G.right=d.parseDimension(s.style.right);G.width=x.plugins.object[F].width;G.height=C-d.parseDimension(s.style.top)-d.parseDimension(s.style.bottom);s.style[E]=d.parseDimension(s.style[E])+x.plugins.object[F].width+"px";s.style.width=d.parseDimension(s.style.width)-G.width+"px";break;case b.html5.view.positions.BOTTOM:G.bottom=d.parseDimension(s.style.bottom);G.left=d.parseDimension(s.style.left);G.width=g-d.parseDimension(s.style.left)-d.parseDimension(s.style.right);G.height=x.plugins.object[F].height;s.style[E]=d.parseDimension(s.style[E])+x.plugins.object[F].height+"px";s.style.height=d.parseDimension(s.style.height)-G.height+"px";break;case b.html5.view.positions.LEFT:G.top=d.parseDimension(s.style.top);G.left=d.parseDimension(s.style.left);G.width=x.plugins.object[F].width;G.height=C-d.parseDimension(s.style.top)-d.parseDimension(s.style.bottom);s.style[E]=d.parseDimension(s.style[E])+x.plugins.object[F].width+"px";s.style.width=d.parseDimension(s.style.width)-G.width+"px";break;default:break}return G}this.resize=j;this.fullscreen=function(H){if(navigator&&navigator.vendor&&navigator.vendor.indexOf("Apple")===0){if(x.getMedia().getDisplayElement().webkitSupportsFullscreen){if(H){try{x.getMedia().getDisplayElement().webkitEnterFullscreen()}catch(G){}}else{try{x.getMedia().getDisplayElement().webkitExitFullscreen()}catch(G){}}}}else{if(H){document.onkeydown=h;clearInterval(p);var F=document.body.getBoundingClientRect();x.width=Math.abs(F.left)+Math.abs(F.right);x.height=window.innerHeight;var E={position:"fixed",width:"100%",height:"100%",top:0,left:0,zIndex:2147483000};c(w,E);E.zIndex=1;if(x.getMedia()&&x.getMedia().getDisplayElement()){c(x.getMedia().getDisplayElement(),E)}E.zIndex=2;c(s,E)}else{document.onkeydown="";x.width=g;x.height=C;c(w,{position:"relative",height:x.height,width:x.width,zIndex:0})}j(x.width,x.height)}}};function a(e){return([b.html5.view.positions.TOP,b.html5.view.positions.RIGHT,b.html5.view.positions.BOTTOM,b.html5.view.positions.LEFT].toString().indexOf(e.toUpperCase())>-1)}b.html5.view.positions={TOP:"TOP",RIGHT:"RIGHT",BOTTOM:"BOTTOM",LEFT:"LEFT",OVER:"OVER",NONE:"NONE"}})(jwplayer);(function(a){var b={backgroundcolor:"",margin:10,font:"Arial,sans-serif",fontsize:10,fontcolor:parseInt("000000",16),fontstyle:"normal",fontweight:"bold",buttoncolor:parseInt("ffffff",16),position:a.html5.view.positions.BOTTOM,idlehide:false,layout:{left:{position:"left",elements:[{name:"play",type:"button"},{name:"divider",type:"divider"},{name:"prev",type:"button"},{name:"divider",type:"divider"},{name:"next",type:"button"},{name:"divider",type:"divider"},{name:"elapsed",type:"text"}]},center:{position:"center",elements:[{name:"time",type:"slider"}]},right:{position:"right",elements:[{name:"duration",type:"text"},{name:"blank",type:"button"},{name:"divider",type:"divider"},{name:"mute",type:"button"},{name:"volume",type:"slider"},{name:"divider",type:"divider"},{name:"fullscreen",type:"button"}]}}};_utils=a.utils;_css=_utils.css;_hide=function(c){_css(c,{display:"none"})};_show=function(c){_css(c,{display:"block"})};a.html5.controlbar=function(l,V){var k=l;var D=_utils.extend({},b,k.skin.getComponentSettings("controlbar"),V);if(D.position==a.html5.view.positions.NONE||typeof a.html5.view.positions[D.position]=="undefined"){return}if(_utils.mapLength(k.skin.getComponentLayout("controlbar"))>0){D.layout=k.skin.getComponentLayout("controlbar")}var ac;var P;var ab;var E;var v="none";var g;var j;var ad;var f;var e;var y;var Q={};var p=false;var c={};var Y;var h=false;var o;var d;var S=false;var G=false;var W=new a.html5.eventdispatcher();_utils.extend(this,W);function J(){if(!Y){Y=k.skin.getSkinElement("controlbar","background");if(!Y){Y={width:0,height:0,src:null}}}return Y}function N(){ab=0;E=0;P=0;if(!p){var ak={height:J().height,backgroundColor:D.backgroundcolor};ac=document.createElement("div");ac.id=k.id+"_jwplayer_controlbar";_css(ac,ak)}var aj=(k.skin.getSkinElement("controlbar","capLeft"));var ai=(k.skin.getSkinElement("controlbar","capRight"));if(aj){x("capLeft","left",false,ac)}var al={position:"absolute",height:J().height,left:(aj?aj.width:0),zIndex:0};Z("background",ac,al,"img");if(J().src){Q.background.src=J().src}al.zIndex=1;Z("elements",ac,al);if(ai){x("capRight","right",false,ac)}}this.getDisplayElement=function(){return ac};this.resize=function(ak,ai){_utils.cancelAnimation(ac);document.getElementById(k.id).onmousemove=A;e=ak;y=ai;if(G!=k.jwGetFullscreen()){G=k.jwGetFullscreen();d=undefined}var aj=w();A();I({id:k.id,duration:ad,position:j});u({id:k.id,bufferPercent:f});return aj};this.show=function(){if(h){h=false;_show(ac);T()}};this.hide=function(){if(!h){h=true;_hide(ac);aa()}};function q(){var aj=["timeSlider","volumeSlider","timeSliderRail","volumeSliderRail"];for(var ak in aj){var ai=aj[ak];if(typeof Q[ai]!="undefined"){c[ai]=Q[ai].getBoundingClientRect()}}}function A(ai){if(h){return}if(D.position==a.html5.view.positions.OVER||k.jwGetFullscreen()){clearTimeout(o);switch(k.jwGetState()){case a.api.events.state.PAUSED:case a.api.events.state.IDLE:if(!D.idlehide||_utils.exists(ai)){U()}if(D.idlehide){o=setTimeout(function(){z()},2000)}break;default:if(ai){U()}o=setTimeout(function(){z()},2000);break}}}function z(ai){aa();_utils.cancelAnimation(ac);_utils.fadeTo(ac,0,0.1,1,0)}function U(){T();_utils.cancelAnimation(ac);_utils.fadeTo(ac,1,0,1,0)}function H(ai){return function(){if(S&&d!=ai){d=ai;W.sendEvent(ai,{component:"controlbar",boundingRect:O()})}}}var T=H(a.api.events.JWPLAYER_COMPONENT_SHOW);var aa=H(a.api.events.JWPLAYER_COMPONENT_HIDE);function O(){if(D.position==a.html5.view.positions.OVER||k.jwGetFullscreen()){return _utils.getDimensions(ac)}else{return{x:0,y:0,width:0,height:0}}}function Z(am,al,ak,ai){var aj;if(!p){if(!ai){ai="div"}aj=document.createElement(ai);Q[am]=aj;aj.id=ac.id+"_"+am;al.appendChild(aj)}else{aj=document.getElementById(ac.id+"_"+am)}if(_utils.exists(ak)){_css(aj,ak)}return aj}function M(){ah(D.layout.left);ah(D.layout.right,-1);ah(D.layout.center)}function ah(al,ai){var am=al.position=="right"?"right":"left";var ak=_utils.extend([],al.elements);if(_utils.exists(ai)){ak.reverse()}for(var aj=0;aj<ak.length;aj++){C(ak[aj],am)}}function K(){return P++}function C(am,ao){var al,aj,ak,ai,aq;if(am.type=="divider"){x("divider"+K(),ao,true,undefined,undefined,am.width,am.element);return}switch(am.name){case"play":x("playButton",ao,false);x("pauseButton",ao,true);R("playButton","jwPlay");R("pauseButton","jwPause");break;case"prev":x("prevButton",ao,true);R("prevButton","jwPlaylistPrev");break;case"stop":x("stopButton",ao,true);R("stopButton","jwStop");break;case"next":x("nextButton",ao,true);R("nextButton","jwPlaylistNext");break;case"elapsed":x("elapsedText",ao,true);break;case"time":aj=!_utils.exists(k.skin.getSkinElement("controlbar","timeSliderCapLeft"))?0:k.skin.getSkinElement("controlbar","timeSliderCapLeft").width;ak=!_utils.exists(k.skin.getSkinElement("controlbar","timeSliderCapRight"))?0:k.skin.getSkinElement("controlbar","timeSliderCapRight").width;al=ao=="left"?aj:ak;ai=k.skin.getSkinElement("controlbar","timeSliderRail").width+aj+ak;aq={height:J().height,position:"absolute",top:0,width:ai};aq[ao]=ao=="left"?ab:E;var an=Z("timeSlider",Q.elements,aq);x("timeSliderCapLeft",ao,true,an,ao=="left"?0:al);x("timeSliderRail",ao,false,an,al);x("timeSliderBuffer",ao,false,an,al);x("timeSliderProgress",ao,false,an,al);x("timeSliderThumb",ao,false,an,al);x("timeSliderCapRight",ao,true,an,ao=="right"?0:al);X("time");break;case"fullscreen":x("fullscreenButton",ao,false);x("normalscreenButton",ao,true);R("fullscreenButton","jwSetFullscreen",true);R("normalscreenButton","jwSetFullscreen",false);break;case"volume":aj=!_utils.exists(k.skin.getSkinElement("controlbar","volumeSliderCapLeft"))?0:k.skin.getSkinElement("controlbar","volumeSliderCapLeft").width;ak=!_utils.exists(k.skin.getSkinElement("controlbar","volumeSliderCapRight"))?0:k.skin.getSkinElement("controlbar","volumeSliderCapRight").width;al=ao=="left"?aj:ak;ai=k.skin.getSkinElement("controlbar","volumeSliderRail").width+aj+ak;aq={height:J().height,position:"absolute",top:0,width:ai};aq[ao]=ao=="left"?ab:E;var ap=Z("volumeSlider",Q.elements,aq);x("volumeSliderCapLeft",ao,true,ap,ao=="left"?0:al);x("volumeSliderRail",ao,true,ap,al);x("volumeSliderProgress",ao,false,ap,al);x("volumeSliderCapRight",ao,true,ap,ao=="right"?0:al);X("volume");break;case"mute":x("muteButton",ao,false);x("unmuteButton",ao,true);R("muteButton","jwSetMute",true);R("unmuteButton","jwSetMute",false);break;case"duration":x("durationText",ao,true);break}}function x(al,ao,aj,ar,am,ai,ak){if(_utils.exists(k.skin.getSkinElement("controlbar",al))||al.indexOf("Text")>0||al.indexOf("divider")===0){var an={height:J().height,position:"absolute",display:"block",top:0};if((al.indexOf("next")===0||al.indexOf("prev")===0)&&k.jwGetPlaylist().length<2){aj=false;an.display="none"}var at;if(al.indexOf("Text")>0){al.innerhtml="00:00";an.font=D.fontsize+"px/"+(J().height+1)+"px "+D.font;an.color=D.fontcolor;an.textAlign="center";an.fontWeight=D.fontweight;an.fontStyle=D.fontstyle;an.cursor="default";at=14+3*D.fontsize}else{if(al.indexOf("divider")===0){if(ai){if(!isNaN(parseInt(ai))){at=parseInt(ai)}}else{if(ak){var ap=k.skin.getSkinElement("controlbar",ak);if(ap){an.background="url("+ap.src+") repeat-x center left";at=ap.width}}else{an.background="url("+k.skin.getSkinElement("controlbar","divider").src+") repeat-x center left";at=k.skin.getSkinElement("controlbar","divider").width}}}else{an.background="url("+k.skin.getSkinElement("controlbar",al).src+") repeat-x center left";at=k.skin.getSkinElement("controlbar",al).width}}if(ao=="left"){an.left=isNaN(am)?ab:am;if(aj){ab+=at}}else{if(ao=="right"){an.right=isNaN(am)?E:am;if(aj){E+=at}}}if(_utils.typeOf(ar)=="undefined"){ar=Q.elements}an.width=at;if(p){_css(Q[al],an)}else{var aq=Z(al,ar,an);if(_utils.exists(k.skin.getSkinElement("controlbar",al+"Over"))){aq.onmouseover=function(au){aq.style.backgroundImage=["url(",k.skin.getSkinElement("controlbar",al+"Over").src,")"].join("")};aq.onmouseout=function(au){aq.style.backgroundImage=["url(",k.skin.getSkinElement("controlbar",al).src,")"].join("")}}}}}function F(){k.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,B);k.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,s);k.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_BUFFER,u);k.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,r);k.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_TIME,I);k.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_MUTE,ag);k.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_VOLUME,m);k.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_COMPLETE,L)}function B(){N();M();w();ae()}function s(ai){ad=k.jwGetPlaylist()[ai.index].duration;I({id:k.id,duration:ad,position:0});u({id:k.id,bufferProgress:0})}function ae(){I({id:k.id,duration:k.jwGetDuration(),position:0});u({id:k.id,bufferProgress:0});ag({id:k.id,mute:k.jwGetMute()});r({id:k.id,newstate:a.api.events.state.IDLE});m({id:k.id,volume:k.jwGetVolume()})}function R(ak,al,aj){if(p){return}if(_utils.exists(k.skin.getSkinElement("controlbar",ak))){var ai=Q[ak];if(_utils.exists(ai)){_css(ai,{cursor:"pointer"});if(al=="fullscreen"){ai.onmouseup=function(am){am.stopPropagation();k.jwSetFullscreen(!k.jwGetFullscreen())}}else{ai.onmouseup=function(am){am.stopPropagation();if(_utils.exists(aj)){k[al](aj)}else{k[al]()}}}}}}function X(ai){if(p){return}var aj=Q[ai+"Slider"];_css(Q.elements,{cursor:"pointer"});_css(aj,{cursor:"pointer"});aj.onmousedown=function(ak){v=ai};aj.onmouseup=function(ak){ak.stopPropagation();af(ak.pageX)};aj.onmousemove=function(ak){if(v=="time"){g=true;var al=ak.pageX-c[ai+"Slider"].left-window.pageXOffset;_css(Q.timeSliderThumb,{left:al})}}}function af(aj){g=false;var ai;if(v=="time"){ai=aj-c.timeSliderRail.left+window.pageXOffset;var al=ai/c.timeSliderRail.width*ad;if(al<0){al=0}else{if(al>ad){al=ad-3}}if(k.jwGetState()==a.api.events.state.PAUSED||k.jwGetState()==a.api.events.state.IDLE){k.jwPlay()}k.jwSeek(al)}else{if(v=="volume"){ai=aj-c.volumeSliderRail.left-window.pageXOffset;var ak=Math.round(ai/c.volumeSliderRail.width*100);if(ak<0){ak=0}else{if(ak>100){ak=100}}if(k.jwGetMute()){k.jwSetMute(false)}k.jwSetVolume(ak)}}v="none"}function u(aj){if(_utils.exists(aj.bufferPercent)){f=aj.bufferPercent}if(c.timeSliderRail){var ak=c.timeSliderRail.width;var ai=isNaN(Math.round(ak*f/100))?0:Math.round(ak*f/100);_css(Q.timeSliderBuffer,{width:ai})}}function ag(ai){if(ai.mute){_hide(Q.muteButton);_show(Q.unmuteButton);_hide(Q.volumeSliderProgress)}else{_show(Q.muteButton);_hide(Q.unmuteButton);_show(Q.volumeSliderProgress)}}function r(ai){if(ai.newstate==a.api.events.state.BUFFERING||ai.newstate==a.api.events.state.PLAYING){_show(Q.pauseButton);_hide(Q.playButton)}else{_hide(Q.pauseButton);_show(Q.playButton)}A();if(ai.newstate==a.api.events.state.IDLE){_hide(Q.timeSliderBuffer);_hide(Q.timeSliderProgress);_hide(Q.timeSliderThumb);I({id:k.id,duration:k.jwGetDuration(),position:0})}else{_show(Q.timeSliderBuffer);if(ai.newstate!=a.api.events.state.BUFFERING){_show(Q.timeSliderProgress);_show(Q.timeSliderThumb)}}}function L(ai){u({bufferPercent:0});I(_utils.extend(ai,{position:0,duration:ad}))}function I(al){if(_utils.exists(al.position)){j=al.position}if(_utils.exists(al.duration)){ad=al.duration}var aj=(j===ad===0)?0:j/ad;var am=c.timeSliderRail;if(am){var ai=isNaN(Math.round(am.width*aj))?0:Math.round(am.width*aj);var ak=ai;if(Q.timeSliderProgress){Q.timeSliderProgress.style.width=ai+"px";if(!g){if(Q.timeSliderThumb){Q.timeSliderThumb.style.left=ak+"px"}}}}if(Q.durationText){Q.durationText.innerHTML=_utils.timeFormat(ad)}if(Q.elapsedText){Q.elapsedText.innerHTML=_utils.timeFormat(j)}}function n(){var am,aj;var ak=document.getElementById(ac.id+"_elements");if(!ak){return}var al=ak.childNodes;for(var ai in ak.childNodes){if(isNaN(parseInt(ai,10))){continue}if(al[ai].id.indexOf(ac.id+"_divider")===0&&aj&&aj.id.indexOf(ac.id+"_divider")===0&&al[ai].style.backgroundImage==aj.style.backgroundImage){al[ai].style.display="none"}else{if(al[ai].id.indexOf(ac.id+"_divider")===0&&am&&am.style.display!="none"){al[ai].style.display="block"}}if(al[ai].style.display!="none"){aj=al[ai]}am=al[ai]}}function w(){n();if(k.jwGetFullscreen()){_show(Q.normalscreenButton);_hide(Q.fullscreenButton)}else{_hide(Q.normalscreenButton);_show(Q.fullscreenButton)}var aj={width:e};var ai={};if(D.position==a.html5.view.positions.OVER||k.jwGetFullscreen()){aj.left=D.margin;aj.width-=2*D.margin;aj.top=y-J().height-D.margin;aj.height=J().height}var al=k.skin.getSkinElement("controlbar","capLeft");var ak=k.skin.getSkinElement("controlbar","capRight");ai.left=al?al.width:0;ai.width=aj.width-ai.left-(ak?ak.width:0);var am=!_utils.exists(k.skin.getSkinElement("controlbar","timeSliderCapLeft"))?0:k.skin.getSkinElement("controlbar","timeSliderCapLeft").width;_css(Q.timeSliderRail,{width:(ai.width-ab-E),left:am});if(_utils.exists(Q.timeSliderCapRight)){_css(Q.timeSliderCapRight,{left:am+(ai.width-ab-E)})}_css(ac,aj);_css(Q.elements,ai);_css(Q.background,ai);q();return aj}function m(am){if(_utils.exists(Q.volumeSliderRail)){var ak=isNaN(am.volume/100)?1:am.volume/100;var al=_utils.parseDimension(Q.volumeSliderRail.style.width);var ai=isNaN(Math.round(al*ak))?0:Math.round(al*ak);var an=_utils.parseDimension(Q.volumeSliderRail.style.right);var aj=(!_utils.exists(k.skin.getSkinElement("controlbar","volumeSliderCapLeft")))?0:k.skin.getSkinElement("controlbar","volumeSliderCapLeft").width;_css(Q.volumeSliderProgress,{width:ai,left:aj});if(_utils.exists(Q.volumeSliderCapLeft)){_css(Q.volumeSliderCapLeft,{left:0})}}}function t(){N();M();q();p=true;F();D.idlehide=(D.idlehide.toString().toLowerCase()=="true");if(D.position==a.html5.view.positions.OVER&&D.idlehide){ac.style.opacity=0;S=true}else{setTimeout((function(){S=true;T()}),1)}ae()}t();return this}})(jwplayer);(function(b){var a=["width","height","state","playlist","item","position","buffer","duration","volume","mute","fullscreen"];var c=b.utils;b.html5.controller=function(z,w,h,v){var C=z;var G=h;var g=v;var o=w;var J=true;var e=-1;var A=c.exists(G.config.debug)&&(G.config.debug.toString().toLowerCase()=="console");var m=new b.html5.eventdispatcher(o.id,A);c.extend(this,m);var E=[];var d=false;function r(M){if(d){m.sendEvent(M.type,M)}else{E.push(M)}}function K(M){if(!d){m.sendEvent(b.api.events.JWPLAYER_READY,M);if(b.utils.exists(window.playerReady)){playerReady(M)}if(b.utils.exists(window[h.config.playerReady])){window[h.config.playerReady](M)}while(E.length>0){var O=E.shift();m.sendEvent(O.type,O)}if(h.config.autostart&&!b.utils.isIOS()){t(G.item)}while(p.length>0){var N=p.shift();x(N.method,N.arguments)}d=true}}G.addGlobalListener(r);G.addEventListener(b.api.events.JWPLAYER_MEDIA_BUFFER_FULL,function(){G.getMedia().play()});G.addEventListener(b.api.events.JWPLAYER_MEDIA_TIME,function(M){if(M.position>=G.playlist[G.item].start&&e>=0){G.playlist[G.item].start=e;e=-1}});G.addEventListener(b.api.events.JWPLAYER_MEDIA_COMPLETE,function(M){setTimeout(s,25)});function u(){try{f(G.item);if(G.playlist[G.item].levels[0].file.length>0){if(J||G.state==b.api.events.state.IDLE){G.getMedia().load(G.playlist[G.item]);J=false}else{if(G.state==b.api.events.state.PAUSED){G.getMedia().play()}}}return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function I(){try{if(G.playlist[G.item].levels[0].file.length>0){switch(G.state){case b.api.events.state.PLAYING:case b.api.events.state.BUFFERING:G.getMedia().pause();break}}return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function D(M){try{if(G.playlist[G.item].levels[0].file.length>0){if(typeof M!="number"){M=parseFloat(M)}switch(G.state){case b.api.events.state.IDLE:if(e<0){e=G.playlist[G.item].start;G.playlist[G.item].start=M}u();break;case b.api.events.state.PLAYING:case b.api.events.state.PAUSED:case b.api.events.state.BUFFERING:G.seek(M);break}}return true}catch(N){m.sendEvent(b.api.events.JWPLAYER_ERROR,N)}return false}function n(M){if(!c.exists(M)){M=true}try{G.getMedia().stop(M);return true}catch(N){m.sendEvent(b.api.events.JWPLAYER_ERROR,N)}return false}function k(){try{if(G.playlist[G.item].levels[0].file.length>0){if(G.config.shuffle){f(y())}else{if(G.item+1==G.playlist.length){f(0)}else{f(G.item+1)}}}if(G.state!=b.api.events.state.IDLE){var N=G.state;G.state=b.api.events.state.IDLE;m.sendEvent(b.api.events.JWPLAYER_PLAYER_STATE,{oldstate:N,newstate:b.api.events.state.IDLE})}u();return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function j(){try{if(G.playlist[G.item].levels[0].file.length>0){if(G.config.shuffle){f(y())}else{if(G.item===0){f(G.playlist.length-1)}else{f(G.item-1)}}}if(G.state!=b.api.events.state.IDLE){var N=G.state;G.state=b.api.events.state.IDLE;m.sendEvent(b.api.events.JWPLAYER_PLAYER_STATE,{oldstate:N,newstate:b.api.events.state.IDLE})}u();return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function y(){var M=null;if(G.playlist.length>1){while(!c.exists(M)){M=Math.floor(Math.random()*G.playlist.length);if(M==G.item){M=null}}}else{M=0}return M}function t(N){if(!G.playlist||!G.playlist[N]){return false}try{if(G.playlist[N].levels[0].file.length>0){var O=G.state;if(O!==b.api.events.state.IDLE){if(G.playlist[G.item].provider==G.playlist[N].provider){n(false)}else{n()}}f(N);u()}return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function f(M){if(!G.playlist[M]){return}G.setActiveMediaProvider(G.playlist[M]);if(G.item!=M){G.item=M;J=true;m.sendEvent(b.api.events.JWPLAYER_PLAYLIST_ITEM,{index:M})}}function H(N){try{f(G.item);var O=G.getMedia();switch(typeof(N)){case"number":O.volume(N);break;case"string":O.volume(parseInt(N,10));break}return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function q(N){try{f(G.item);var O=G.getMedia();if(typeof N=="undefined"){O.mute(!G.mute)}else{if(N.toString().toLowerCase()=="true"){O.mute(true)}else{O.mute(false)}}return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function l(N,M){try{G.width=N;G.height=M;g.resize(N,M);m.sendEvent(b.api.events.JWPLAYER_RESIZE,{width:G.width,height:G.height});return true}catch(O){m.sendEvent(b.api.events.JWPLAYER_ERROR,O)}return false}function B(N){try{if(typeof N=="undefined"){G.fullscreen=!G.fullscreen;g.fullscreen(!G.fullscreen)}else{if(N.toString().toLowerCase()=="true"){G.fullscreen=true;g.fullscreen(true)}else{G.fullscreen=false;g.fullscreen(false)}}m.sendEvent(b.api.events.JWPLAYER_RESIZE,{width:G.width,height:G.height});m.sendEvent(b.api.events.JWPLAYER_FULLSCREEN,{fullscreen:N});return true}catch(M){m.sendEvent(b.api.events.JWPLAYER_ERROR,M)}return false}function L(M){try{n();G.loadPlaylist(M);f(G.item);return true}catch(N){m.sendEvent(b.api.events.JWPLAYER_ERROR,N)}return false}b.html5.controller.repeatoptions={LIST:"LIST",ALWAYS:"ALWAYS",SINGLE:"SINGLE",NONE:"NONE"};function s(){switch(G.config.repeat.toUpperCase()){case b.html5.controller.repeatoptions.SINGLE:u();break;case b.html5.controller.repeatoptions.ALWAYS:if(G.item==G.playlist.length-1&&!G.config.shuffle){t(0)}else{k()}break;case b.html5.controller.repeatoptions.LIST:if(G.item==G.playlist.length-1&&!G.config.shuffle){n();f(0)}else{k()}break;default:n();break}}var p=[];function F(M){return function(){if(d){x(M,arguments)}else{p.push({method:M,arguments:arguments})}}}function x(O,N){var M=[];for(i=0;i<N.length;i++){M.push(N[i])}O.apply(this,M)}this.play=F(u);this.pause=F(I);this.seek=F(D);this.stop=F(n);this.next=F(k);this.prev=F(j);this.item=F(t);this.setVolume=F(H);this.setMute=F(q);this.resize=F(l);this.setFullscreen=F(B);this.load=F(L);this.playerReady=K}})(jwplayer);(function(a){a.html5.defaultSkin=function(){this.text='<?xml version="1.0" ?><skin author="LongTail Video" name="Five" version="1.0"><settings><setting name="backcolor" value="0xFFFFFF"/><setting name="frontcolor" value="0x000000"/><setting name="lightcolor" value="0x000000"/><setting name="screencolor" value="0x000000"/></settings><components><component name="controlbar"><settings><setting name="margin" value="20"/><setting name="fontsize" value="11"/></settings><elements><element name="background" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAIAAABvFaqvAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAFJJREFUeNrslLENwAAIwxLU/09j5AiOgD5hVQzNAVY8JK4qEfHMIKBnd2+BQlBINaiRtL/aV2rdzYBsM6CIONbI1NZENTr3RwdB2PlnJgJ6BRgA4hwu5Qg5iswAAAAASUVORK5CYII="/><element name="capLeft" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAIAAAC0rgCNAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAD5JREFUeNosi8ENACAMAgnuv14H0Z8asI19XEjhOiKCMmibVgJTUt7V6fe9KXOtSQCfctJHu2q3/ot79hNgANc2OTz9uTCCAAAAAElFTkSuQmCC"/><element name="capRight" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAIAAAC0rgCNAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAD5JREFUeNosi8ENACAMAgnuv14H0Z8asI19XEjhOiKCMmibVgJTUt7V6fe9KXOtSQCfctJHu2q3/ot79hNgANc2OTz9uTCCAAAAAElFTkSuQmCC"/><element name="divider" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAYCAIAAAC0rgCNAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAD5JREFUeNosi8ENACAMAgnuv14H0Z8asI19XEjhOiKCMmibVgJTUt7V6fe9KXOtSQCfctJHu2q3/ot79hNgANc2OTz9uTCCAAAAAElFTkSuQmCC"/><element name="playButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEhJREFUeNpiYqABYBo1dNRQ+hr6H4jvA3E8NS39j4SpZvh/LJig4YxEGEqy3kET+w+AOGFQRhTJhrEQkGcczfujhg4CQwECDADpTRWU/B3wHQAAAABJRU5ErkJggg=="/><element name="pauseButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAChJREFUeNpiYBgFo2DwA0YC8v/R1P4nRu+ooaOGUtnQUTAKhgIACDAAFCwQCfAJ4gwAAAAASUVORK5CYII="/><element name="prevButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEtJREFUeNpiYBgFo2Dog/9QDAPyQHweTYwiQ/2B+D0Wi8g2tB+JTdBQRiIMJVkvEy0iglhDF9Aq9uOpHVEwoE+NJDUKRsFgAAABBgDe2hqZcNNL0AAAAABJRU5ErkJggg=="/><element name="nextButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAYCAYAAAAVibZIAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAElJREFUeNpiYBgFo2Dog/9AfB6I5dHE/lNqKAi/B2J/ahsKw/3EGMpIhKEk66WJoaR6fz61IyqemhEFSlL61ExSo2AUDAYAEGAAiG4hj+5t7M8AAAAASUVORK5CYII="/><element name="timeSliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADxJREFUeNpiYBgFo2AU0Bwwzluw+D8tLWARFhKiqQ9YuLg4aWsBGxs7bS1gZ6e5BWyjSX0UjIKhDgACDABlYQOGh5pYywAAAABJRU5ErkJggg=="/><element name="timeSliderBuffer" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAD1JREFUeNpiYBgFo2AU0Bww1jc0/aelBSz8/Pw09QELOzs7bS1gY2OjrQWsrKy09gHraFIfBaNgqAOAAAMAvy0DChXHsZMAAAAASUVORK5CYII="/><element name="timeSliderProgress" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAClJREFUeNpiYBgFo2AU0BwwAvF/WlrARGsfjFow8BaMglEwCugAAAIMAOHfAQunR+XzAAAAAElFTkSuQmCC"/><element name="timeSliderThumb" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAAICAYAAAA870V8AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAABZJREFUeNpiZICA/yCCiQEJUJcDEGAAY0gBD1/m7Q0AAAAASUVORK5CYII="/><element name="muteButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAYCAYAAADKx8xXAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADFJREFUeNpiYBgFIw3MB+L/5Gj8j6yRiRTFyICJXHfTXyMLAXlGati4YDRFDj8AEGAABk8GSqqS4CoAAAAASUVORK5CYII="/><element name="unmuteButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAYCAYAAADKx8xXAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAD1JREFUeNpiYBgFgxz8p7bm+cQa+h8LHy7GhEcjIz4bmAjYykiun/8j0fakGPIfTfPgiSr6aB4FVAcAAQYAWdwR1G1Wd2gAAAAASUVORK5CYII="/><element name="volumeSliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAYCAYAAADkgu3FAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAGpJREFUeNpi/P//PwM9ABMDncCoRYPfIqqDZcuW1UPp/6AUDcNM1DQYKtRAlaAj1mCSLSLXYIIWUctgDItoZfDA5aOoqKhGEANIM9LVR7SymGDQUctikuOIXkFNdhHEOFrDjlpEd4sAAgwAriRMub95fu8AAAAASUVORK5CYII="/><element name="volumeSliderProgress" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAYCAYAAADkgu3FAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAFtJREFUeNpi/P//PwM9ABMDncCoRYPfIlqAeij9H5SiYZiqBqPTlFqE02BKLSLaYFItIttgQhZRzWB8FjENiuRJ7aAbsMQwYMl7wDIsWUUQ42gNO2oR3S0CCDAAKhKq6MLLn8oAAAAASUVORK5CYII="/><element name="fullscreenButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAE5JREFUeNpiYBgFo2DQA0YC8v/xqP1PjDlMRDrEgUgxkgHIlfZoriVGjmzLsLFHAW2D6D8eA/9Tw7L/BAwgJE90PvhPpNgoGAVDEQAEGAAMdhTyXcPKcAAAAABJRU5ErkJggg=="/><element name="normalscreenButton" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEZJREFUeNpiYBgFo2DIg/9UUkOUAf8JiFFsyX88fJyAkcQgYMQjNkzBoAgiezyRbE+tFGSPxQJ7auYBmma0UTAKBhgABBgAJAEY6zON61sAAAAASUVORK5CYII="/></elements></component><component name="display"><elements><element name="background" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEpJREFUeNrszwENADAIA7DhX8ENoBMZ5KR10EryckCJiIiIiIiIiIiIiIiIiIiIiIh8GmkRERERERERERERERERERERERGRHSPAAPlXH1phYpYaAAAAAElFTkSuQmCC"/><element name="playIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAALdJREFUeNrs18ENgjAYhmFouDOCcQJGcARHgE10BDcgTOIosAGwQOuPwaQeuFRi2p/3Sb6EC5L3QCxZBgAAAOCorLW1zMn65TrlkH4NcV7QNcUQt7Gn7KIhxA+qNIR81spOGkL8oFJDyLJRdosqKDDkK+iX5+d7huzwM40xptMQMkjIOeRGo+VkEVvIPfTGIpKASfYIfT9iCHkHrBEzf4gcUQ56aEzuGK/mw0rHpy4AAACAf3kJMACBxjAQNRckhwAAAABJRU5ErkJggg=="/><element name="muteIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHJJREFUeNrs1jEOgCAMBVAg7t5/8qaoIy4uoobyXsLCxA+0NCUAAADGUWvdQoQ41x4ixNBB2hBvBskdD3w5ZCkl3+33VqI0kjBBlh9rp+uTcyOP33TnolfsU85XX3yIRpQph8ZQY3wTZtU5AACASA4BBgDHoVuY1/fvOQAAAABJRU5ErkJggg=="/><element name="errorIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAWlJREFUeNrsl+1twjAQhsHq/7BBYQLYIBmBDcoGMAIjtBPQTcII2SDtBDBBwrU6pGsUO7YbO470PtKJkz9iH++d4ywWAAAAAABgljRNsyWr2bZzDuJG1rLdZhcMbTjrBCGDyUKsqQLFciJb9bSvuG/WagRVRUVUI6gqy5HVeKWfSgRyJruKIU//TrZTSn2nmlaXThrloi/v9F2STC1W4+Aw5cBzkquRc09bofFNc6YLxEON0VUZS5FPTftO49vMjRsIF3RhOGr7/D/pJw+FKU+q0vDyq8W42jCunDqI3LC5XxNj2wHLU1XjaRnb0Lhykhqhhd8MtSF5J9tbjCv4mXGvKJz/65FF/qJryyaaIvzP2QRxZTX2nTuXjvV/VPFSwyLnW7mpH99yTh1FEVro6JBSd40/pMrRdV8vPtcKl28T2pT8TnFZ4yNosct3Q0io6JfBiz1FlGdqVQH3VHnepAEAAAAAADDzEGAAcTwB10jWgxcAAAAASUVORK5CYII="/><element name="bufferIcon" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAuhJREFUeNrsWr9rU1EUznuNGqvFQh1ULOhiBx0KDtIuioO4pJuik3FxFfUPaAV1FTdx0Q5d2g4FFxehTnEpZHFoBy20tCIWtGq0TZP4HfkeHB5N8m6Sl/sa74XDybvv3vvOd8/Pe4lXrVZT3dD8VJc0B8QBcUAcEAfESktHGeR5XtMfqFQq/f92zPe/NbtGlKTdCY30kuxrpMGO94BlQCXs+rbh3ONgA6BlzP1p20d80gEI5hmA2A92Qua1Q2PtAFISM+bvjMG8U+Q7oA3rQGASwrYCU6WpNdLGYbA+Pq5jjXIiwi8EEa2UDbQSaKOIuV+SlkcCrfjY8XTI9EpKGwP0C2kru2hLtHqa4zoXtZRWyvi4CLwv9Opr6Hkn6A9HKgEANsQ1iqC3Ub/vRUk2JgmRkatK36kVrnt0qObunwUdUUMXMWYpakJsO5Am8tAw2GBIgwWA+G2S2dMpiw0gDioQRQJoKhRb1QiDwlHZUABYbaXWsm5ae6loTE4ZDxN4CZar8foVzOJ2iyZ2kWF3t7YIevffaMT5yJ70kQb2fQ1sE5SHr2wazs2wgMxgbsEKEAgxAvZUJbQLBGTSBMgNrncJbA6AljtS/eKDJ0Ez+DmrQEzXS2h1Ck25kAg0IZcUOaydCy4sYnN2fOA+2AP16gNoHALlQ+fwH7XO4CxLenUpgj4xr6ugY2roPMbMx+Xs18m/E8CVEIhxsNeg83XWOAN6grG3lGbk8uE5fr4B/WH3cJw+co/l9nTYsSGYCJ/lY5/qv0thn6nrIWmjeJcPSnWOeY++AkF8tpJHIMAUs/MaBBpj3znZfQo5psY+ZrG4gv5HickjEOymKjEeRpgyST6IuZcTcWbnjcgdPi5ghxciRKsl1lDSsgwA1i8fssonJgzmTSqfGUkCENndNdAL7PS6QQ7ZYISTo+1qq0LEWjTWcvY4isa4z+yfQB+7ooyHVg5RI7/i1Ijn/vnggDggDogD4oC00P4KMACd/juEHOrS4AAAAABJRU5ErkJggg=="/></elements></component><component name="dock"><elements><element name="button" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAFBJREFUeNrs0cEJACAQA8Eofu0fu/W6EM5ZSAFDRpKTBs00CQQEBAQEBAQEBAQEBAQEBATkK8iqbY+AgICAgICAgICAgICAgICAgIC86QowAG5PAQzEJ0lKAAAAAElFTkSuQmCC"/></elements></component><component name="playlist"><elements><element name="item" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA8CAIAAAC1nk4lAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHhJREFUeNrs2NEJwCAMBcBYuv/CFuIE9VN47WWCR7iocXR3pdWdGPqqwIoMjYfQeAiNh9B4JHc6MHQVHnjggQceeOCBBx77TifyeOY0iHi8DqIdEY8dD5cL094eePzINB5CO/LwcOTptNB4CP25L4TIbZzpU7UEGAA5wz1uF5rF9AAAAABJRU5ErkJggg=="/><element name="sliderRail" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAA8CAIAAADpFA0BAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADhJREFUeNrsy6ENACAMAMHClp2wYxZLAg5Fcu9e3OjuOKqqfTMzbs14CIZhGIZhGIZhGP4VLwEGAK/BBnVFpB0oAAAAAElFTkSuQmCC"/><element name="sliderThumb" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAA8CAIAAADpFA0BAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAADRJREFUeNrsy7ENACAMBLE8++8caFFKKiRffU53112SGs3ttOohGIZhGIZhGIZh+Fe8BRgAiaUGde6NOSEAAAAASUVORK5CYII="/></elements></component></components></skin>';this.xml=null;if(window.DOMParser){parser=new DOMParser();this.xml=parser.parseFromString(this.text,"text/xml")}else{this.xml=new ActiveXObject("Microsoft.XMLDOM");this.xml.async="false";this.xml.loadXML(this.text)}return this}})(jwplayer);(function(a){_utils=a.utils;_css=_utils.css;_hide=function(b){_css(b,{display:"none"})};_show=function(b){_css(b,{display:"block"})};a.html5.display=function(k,G){var j={icons:true,showmute:false};var Q=_utils.extend({},j,G);var h=k;var P={};var e;var u;var w;var N;var s;var I;var A;var J=!_utils.exists(h.skin.getComponentSettings("display").bufferrotation)?15:parseInt(h.skin.getComponentSettings("display").bufferrotation,10);var q=!_utils.exists(h.skin.getComponentSettings("display").bufferinterval)?100:parseInt(h.skin.getComponentSettings("display").bufferinterval,10);var z=-1;var t="";var K=true;var d;var g=false;var n=false;var H=new a.html5.eventdispatcher();_utils.extend(this,H);var D={display:{style:{cursor:"pointer",top:0,left:0,overflow:"hidden"},click:m},display_icon:{style:{cursor:"pointer",position:"absolute",top:((h.skin.getSkinElement("display","background").height-h.skin.getSkinElement("display","playIcon").height)/2),left:((h.skin.getSkinElement("display","background").width-h.skin.getSkinElement("display","playIcon").width)/2),border:0,margin:0,padding:0,zIndex:3,display:"none"}},display_iconBackground:{style:{cursor:"pointer",position:"absolute",top:((u-h.skin.getSkinElement("display","background").height)/2),left:((e-h.skin.getSkinElement("display","background").width)/2),border:0,backgroundImage:(["url(",h.skin.getSkinElement("display","background").src,")"]).join(""),width:h.skin.getSkinElement("display","background").width,height:h.skin.getSkinElement("display","background").height,margin:0,padding:0,zIndex:2,display:"none"}},display_image:{style:{display:"none",width:e,height:u,position:"absolute",cursor:"pointer",left:0,top:0,margin:0,padding:0,textDecoration:"none",zIndex:1}},display_text:{style:{zIndex:4,position:"relative",opacity:0.8,backgroundColor:parseInt("000000",16),color:parseInt("ffffff",16),textAlign:"center",fontFamily:"Arial,sans-serif",padding:"0 5px",fontSize:14}}};h.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,p);h.jwAddEventListener(a.api.events.JWPLAYER_MEDIA_MUTE,p);h.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,p);h.jwAddEventListener(a.api.events.JWPLAYER_ERROR,o);L();function L(){P.display=C("div","display");P.display_text=C("div","display_text");P.display.appendChild(P.display_text);P.display_image=C("img","display_image");P.display_image.onerror=function(R){_hide(P.display_image)};P.display_image.onload=y;P.display_icon=C("div","display_icon");P.display_iconBackground=C("div","display_iconBackground");P.display.appendChild(P.display_image);P.display_iconBackground.appendChild(P.display_icon);P.display.appendChild(P.display_iconBackground);f();setTimeout((function(){n=true;if(Q.icons.toString()=="true"){F()}}),1)}this.getDisplayElement=function(){return P.display};this.resize=function(S,R){_css(P.display,{width:S,height:R});_css(P.display_text,{width:(S-10),top:((R-P.display_text.getBoundingClientRect().height)/2)});_css(P.display_iconBackground,{top:((R-h.skin.getSkinElement("display","background").height)/2),left:((S-h.skin.getSkinElement("display","background").width)/2)});if(e!=S||u!=R){e=S;u=R;d=undefined;F()}c();p({})};this.show=function(){if(g){g=false;r(h.jwGetState())}};this.hide=function(){if(!g){B();g=true}};function y(R){w=P.display_image.naturalWidth;N=P.display_image.naturalHeight;c()}function c(){_utils.stretch(h.jwGetStretching(),P.display_image,e,u,w,N)}function C(R,T){var S=document.createElement(R);S.id=h.id+"_jwplayer_"+T;_css(S,D[T].style);return S}function f(){for(var R in P){if(_utils.exists(D[R].click)){P[R].onclick=D[R].click}}}function m(R){if(typeof R.preventDefault!="undefined"){R.preventDefault()}else{R.returnValue=false}if(h.jwGetState()!=a.api.events.state.PLAYING){h.jwPlay()}else{h.jwPause()}}function O(R){if(A){B();return}P.display_icon.style.backgroundImage=(["url(",h.skin.getSkinElement("display",R).src,")"]).join("");_css(P.display_icon,{width:h.skin.getSkinElement("display",R).width,height:h.skin.getSkinElement("display",R).height,top:(h.skin.getSkinElement("display","background").height-h.skin.getSkinElement("display",R).height)/2,left:(h.skin.getSkinElement("display","background").width-h.skin.getSkinElement("display",R).width)/2});b();if(_utils.exists(h.skin.getSkinElement("display",R+"Over"))){P.display_icon.onmouseover=function(S){P.display_icon.style.backgroundImage=["url(",h.skin.getSkinElement("display",R+"Over").src,")"].join("")};P.display_icon.onmouseout=function(S){P.display_icon.style.backgroundImage=["url(",h.skin.getSkinElement("display",R).src,")"].join("")}}else{P.display_icon.onmouseover=null;P.display_icon.onmouseout=null}}function B(){if(Q.icons.toString()=="true"){_hide(P.display_icon);_hide(P.display_iconBackground);M()}}function b(){if(!g&&Q.icons.toString()=="true"){_show(P.display_icon);_show(P.display_iconBackground);F()}}function o(R){A=true;B();P.display_text.innerHTML=R.error;_show(P.display_text);P.display_text.style.top=((u-P.display_text.getBoundingClientRect().height)/2)+"px"}function E(){P.display_image.style.display="none"}function p(R){if((R.type==a.api.events.JWPLAYER_PLAYER_STATE||R.type==a.api.events.JWPLAYER_PLAYLIST_ITEM)&&A){A=false;_hide(P.display_text)}var S=h.jwGetState();if(S==t){return}t=S;if(z>=0){clearTimeout(z)}if(K||h.jwGetState()==a.api.events.state.PLAYING||h.jwGetState()==a.api.events.state.PAUSED){r(h.jwGetState())}else{z=setTimeout(l(h.jwGetState()),500)}}function l(R){return(function(){r(R)})}function r(R){if(_utils.exists(I)){clearInterval(I);I=null;_utils.animations.rotate(P.display_icon,0)}switch(R){case a.api.events.state.BUFFERING:if(_utils.isIOS()){E();B()}else{if(h.jwGetPlaylist()[h.jwGetItem()].provider=="sound"){v()}s=0;I=setInterval(function(){s+=J;_utils.animations.rotate(P.display_icon,s%360)},q);O("bufferIcon");K=true}break;case a.api.events.state.PAUSED:if(!_utils.isIOS()){if(h.jwGetPlaylist()[h.jwGetItem()].provider!="sound"){_css(P.display_image,{background:"transparent no-repeat center center"})}O("playIcon");K=true}break;case a.api.events.state.IDLE:if(h.jwGetPlaylist()[h.jwGetItem()]&&h.jwGetPlaylist()[h.jwGetItem()].image){v()}else{E()}O("playIcon");K=true;break;default:if(h.jwGetPlaylist()[h.jwGetItem()]&&h.jwGetPlaylist()[h.jwGetItem()].provider=="sound"){if(_utils.isIOS()){E();K=false}else{v()}}else{E();K=false}if(h.jwGetMute()&&Q.showmute){O("muteIcon")}else{B()}break}z=-1}function v(){if(h.jwGetPlaylist()[h.jwGetItem()]&&h.jwGetPlaylist()[h.jwGetItem()].image){_css(P.display_image,{display:"block"});P.display_image.src=_utils.getAbsolutePath(h.jwGetPlaylist()[h.jwGetItem()].image)}}function x(R){return function(){if(!n){return}if(!g&&d!=R){d=R;H.sendEvent(R,{component:"display",boundingRect:_utils.getDimensions(P.display_iconBackground)})}}}var F=x(a.api.events.JWPLAYER_COMPONENT_SHOW);var M=x(a.api.events.JWPLAYER_COMPONENT_HIDE);return this}})(jwplayer);(function(a){_css=a.utils.css;a.html5.dock=function(p,u){function q(){return{align:a.html5.view.positions.RIGHT}}var k=a.utils.extend({},q(),u);if(k.align=="FALSE"){return}var f={};var s=[];var g;var v;var d=false;var t=false;var e={x:0,y:0,width:0,height:0};var r;var j=new a.html5.eventdispatcher();_utils.extend(this,j);var m=document.createElement("div");m.id=p.id+"_jwplayer_dock";p.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,l);this.getDisplayElement=function(){return m};this.setButton=function(A,x,y,z){if(!x&&f[A]){a.utils.arrays.remove(s,A);m.removeChild(f[A].div);delete f[A]}else{if(x){if(!f[A]){f[A]={}}f[A].handler=x;f[A].outGraphic=y;f[A].overGraphic=z;if(!f[A].div){s.push(A);f[A].div=document.createElement("div");f[A].div.style.position="relative";m.appendChild(f[A].div);f[A].div.appendChild(document.createElement("img"));f[A].div.childNodes[0].style.position="absolute";f[A].div.childNodes[0].style.left=0;f[A].div.childNodes[0].style.top=0;f[A].div.childNodes[0].style.zIndex=10;f[A].div.childNodes[0].style.cursor="pointer";f[A].div.appendChild(document.createElement("img"));f[A].div.childNodes[1].style.position="absolute";f[A].div.childNodes[1].style.left=0;f[A].div.childNodes[1].style.top=0;if(p.skin.getSkinElement("dock","button")){f[A].div.childNodes[1].src=p.skin.getSkinElement("dock","button").src}f[A].div.childNodes[1].style.zIndex=9;f[A].div.childNodes[1].style.cursor="pointer";f[A].div.onmouseover=function(){if(f[A].overGraphic){f[A].div.childNodes[0].src=f[A].overGraphic}if(p.skin.getSkinElement("dock","buttonOver")){f[A].div.childNodes[1].src=p.skin.getSkinElement("dock","buttonOver").src}};f[A].div.onmouseout=function(){if(f[A].outGraphic){f[A].div.childNodes[0].src=f[A].outGraphic}if(p.skin.getSkinElement("dock","button")){f[A].div.childNodes[1].src=p.skin.getSkinElement("dock","button").src}};if(f[A].overGraphic){f[A].div.childNodes[0].src=f[A].overGraphic}if(f[A].outGraphic){f[A].div.childNodes[0].src=f[A].outGraphic}if(p.skin.getSkinElement("dock","button")){f[A].div.childNodes[1].src=p.skin.getSkinElement("dock","button").src}}if(x){f[A].div.onclick=function(B){B.preventDefault();a(p.id).callback(A);if(f[A].overGraphic){f[A].div.childNodes[0].src=f[A].overGraphic}if(p.skin.getSkinElement("dock","button")){f[A].div.childNodes[1].src=p.skin.getSkinElement("dock","button").src}}}}}h(g,v)};function h(x,J){if(s.length>0){var y=10;var I=y;var F=-1;var G=p.skin.getSkinElement("dock","button").height;var E=p.skin.getSkinElement("dock","button").width;var C=x-E-y;var H,B;if(k.align==a.html5.view.positions.LEFT){F=1;C=y}for(var z=0;z<s.length;z++){var K=Math.floor(I/J);if((I+G+y)>((K+1)*J)){I=((K+1)*J)+y;K=Math.floor(I/J)}var A=f[s[z]].div;A.style.top=(I%J)+"px";A.style.left=(C+(p.skin.getSkinElement("dock","button").width+y)*K*F)+"px";var D={x:a.utils.parseDimension(A.style.left),y:a.utils.parseDimension(A.style.top),width:E,height:G};if(!H||(D.x<=H.x&&D.y<=H.y)){H=D}if(!B||(D.x>=B.x&&D.y>=B.y)){B=D}I+=p.skin.getSkinElement("dock","button").height+y}e={x:H.x,y:H.y,width:B.x-H.x+B.width,height:H.y-B.y+B.height}}if(t!=p.jwGetFullscreen()||g!=x||v!=J){g=x;v=J;t=p.jwGetFullscreen();r=undefined;setTimeout(n,1)}}function b(x){return function(){if(!d&&r!=x&&s.length>0){r=x;j.sendEvent(x,{component:"dock",boundingRect:e})}}}function l(x){if(a.utils.isIOS()){switch(x.newstate){case a.api.events.state.IDLE:o();break;default:c();break}}}var n=b(a.api.events.JWPLAYER_COMPONENT_SHOW);var w=b(a.api.events.JWPLAYER_COMPONENT_HIDE);this.resize=h;var o=function(){_css(m,{display:"block"});if(d){d=false;n()}};var c=function(){_css(m,{display:"none"});if(!d){w();d=true}};this.hide=c;this.show=o;return this}})(jwplayer);(function(a){a.html5.eventdispatcher=function(d,b){var c=new a.events.eventdispatcher(b);a.utils.extend(this,c);this.sendEvent=function(e,f){if(!a.utils.exists(f)){f={}}a.utils.extend(f,{id:d,version:a.version,type:e});c.sendEvent(e,f)}}})(jwplayer);(function(a){var b={prefix:"http://l.longtailvideo.com/html5/",file:"logo.png",link:"http://www.longtailvideo.com/players/jw-flv-player/",margin:8,out:0.5,over:1,timeout:5,hide:true,position:"bottom-left"};_css=a.utils.css;a.html5.logo=function(n,r){var q=n;var u;var d;var t;var h=false;g();function g(){o();c();l()}function o(){if(b.prefix){var v=n.version.split(/\W/).splice(0,2).join("/");if(b.prefix.indexOf(v)<0){b.prefix+=v+"/"}}if(r.position==a.html5.view.positions.OVER){r.position=b.position}d=a.utils.extend({},b)}function c(){t=document.createElement("img");t.id=q.id+"_jwplayer_logo";t.style.display="none";t.onload=function(v){_css(t,k());q.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,j);p()};if(!d.file){return}if(d.file.indexOf("http://")===0){t.src=d.file}else{t.src=d.prefix+d.file}}if(!d.file){return}this.resize=function(w,v){};this.getDisplayElement=function(){return t};function l(){if(d.link){t.onmouseover=f;t.onmouseout=p;t.onclick=s}else{this.mouseEnabled=false}}function s(v){if(typeof v!="undefined"){v.stopPropagation()}if(!h){return}q.jwPause();q.jwSetFullscreen(false);if(d.link){window.open(d.link,"_top")}return}function p(v){if(d.link&&h){t.style.opacity=d.out}return}function f(v){if(d.hide.toString()=="true"&&h){t.style.opacity=d.over}return}function k(){var x={textDecoration:"none",position:"absolute",cursor:"pointer"};x.display=(d.hide.toString()=="true")?"none":"block";var w=d.position.toLowerCase().split("-");for(var v in w){x[w[v]]=d.margin}return x}function m(){if(d.hide.toString()=="true"){t.style.display="block";t.style.opacity=0;a.utils.fadeTo(t,d.out,0.1,parseFloat(t.style.opacity));u=setTimeout(function(){e()},d.timeout*1000)}h=true}function e(){h=false;if(d.hide.toString()=="true"){a.utils.fadeTo(t,0,0.1,parseFloat(t.style.opacity))}}function j(v){if(v.newstate==a.api.events.state.BUFFERING){clearTimeout(u);m()}}return this}})(jwplayer);(function(a){var c={ended:a.api.events.state.IDLE,playing:a.api.events.state.PLAYING,pause:a.api.events.state.PAUSED,buffering:a.api.events.state.BUFFERING};var e=a.utils;var b=e.css;var d=e.isIOS();a.html5.mediavideo=function(h,s){var r={abort:n,canplay:k,canplaythrough:k,durationchange:G,emptied:n,ended:k,error:u,loadeddata:G,loadedmetadata:G,loadstart:k,pause:k,play:n,playing:k,progress:v,ratechange:n,seeked:k,seeking:k,stalled:k,suspend:k,timeupdate:D,volumechange:n,waiting:k,canshowcurrentframe:n,dataunavailable:n,empty:n,load:z,loadedfirstframe:n};var j=new a.html5.eventdispatcher();e.extend(this,j);var y=h,l=s,m,B,A,x,f,H=false,C,p,q;o();this.load=function(J,K){if(typeof K=="undefined"){K=true}x=J;e.empty(m);q=0;if(J.levels&&J.levels.length>0){if(J.levels.length==1){m.src=J.levels[0].file}else{if(m.src){m.removeAttribute("src")}for(var I=0;I<J.levels.length;I++){var L=m.ownerDocument.createElement("source");L.src=J.levels[I].file;m.appendChild(L);q++}}}else{m.src=J.file}if(d){if(J.image){m.poster=J.image}m.controls="controls";m.style.display="block"}C=p=A=false;y.buffer=0;if(!e.exists(J.start)){J.start=0}y.duration=J.duration;j.sendEvent(a.api.events.JWPLAYER_MEDIA_LOADED);if((!d&&J.levels.length==1)||!H){m.load()}H=false;if(K){E(a.api.events.state.BUFFERING);j.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:0});this.play()}};this.play=function(){if(B!=a.api.events.state.PLAYING){t();if(p){E(a.api.events.state.PLAYING)}else{E(a.api.events.state.BUFFERING)}m.play()}};this.pause=function(){m.pause();E(a.api.events.state.PAUSED)};this.seek=function(I){if(!(y.duration<=0||isNaN(y.duration))&&!(y.position<=0||isNaN(y.position))){m.currentTime=I;m.play()}};_stop=this.stop=function(I){if(!e.exists(I)){I=true}g();if(I){m.style.display="none";p=false;var J=navigator.userAgent;if(J.match(/chrome/i)){m.src=undefined}else{if(J.match(/safari/i)){m.removeAttribute("src")}else{m.src=""}}m.removeAttribute("controls");m.removeAttribute("poster");e.empty(m);m.load();H=true;if(m.webkitSupportsFullscreen){try{m.webkitExitFullscreen()}catch(K){}}}E(a.api.events.state.IDLE)};this.fullscreen=function(I){if(I===true){this.resize("100%","100%")}else{this.resize(y.config.width,y.config.height)}};this.resize=function(J,I){if(false){b(l,{width:J,height:I})}j.sendEvent(a.api.events.JWPLAYER_MEDIA_RESIZE,{fullscreen:y.fullscreen,width:J,hieght:I})};this.volume=function(I){if(!d){m.volume=I/100;y.volume=I;j.sendEvent(a.api.events.JWPLAYER_MEDIA_VOLUME,{volume:Math.round(I)})}};this.mute=function(I){if(!d){m.muted=I;y.mute=I;j.sendEvent(a.api.events.JWPLAYER_MEDIA_MUTE,{mute:I})}};this.getDisplayElement=function(){return m};this.hasChrome=function(){return false};function o(){m=document.createElement("video");B=a.api.events.state.IDLE;for(var I in r){m.addEventListener(I,function(J){if(e.exists(J.target.parentNode)){r[J.type](J)}},true)}m.setAttribute("x-webkit-airplay","allow");if(l.parentNode){l.parentNode.replaceChild(m,l)}if(!m.id){m.id=l.id}}function E(I){if(I==a.api.events.state.PAUSED&&B==a.api.events.state.IDLE){return}if(B!=I){var J=B;y.state=B=I;j.sendEvent(a.api.events.JWPLAYER_PLAYER_STATE,{oldstate:J,newstate:I})}}function n(I){}function v(K){var J;if(e.exists(K)&&K.lengthComputable&&K.total){J=K.loaded/K.total*100}else{if(e.exists(m.buffered)&&(m.buffered.length>0)){var I=m.buffered.length-1;if(I>=0){J=m.buffered.end(I)/m.duration*100}}}if(p===false&&B==a.api.events.state.BUFFERING){j.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER_FULL);p=true}if(!C){if(J==100){C=true}if(e.exists(J)&&(J>y.buffer)){y.buffer=Math.round(J);j.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:Math.round(J)})}}}function D(J){if(e.exists(J)&&e.exists(J.target)){if(!isNaN(J.target.duration)&&(isNaN(y.duration)||y.duration<1)){if(J.target.duration==Infinity){y.duration=0}else{y.duration=Math.round(J.target.duration*10)/10}}if(!A&&m.readyState>0){m.style.display="block";E(a.api.events.state.PLAYING)}if(B==a.api.events.state.PLAYING){if(!A&&m.readyState>0){A=true;try{if(m.currentTime<x.start){m.currentTime=x.start}}catch(I){}m.volume=y.volume/100;m.muted=y.mute}y.position=y.duration>0?(Math.round(J.target.currentTime*10)/10):0;j.sendEvent(a.api.events.JWPLAYER_MEDIA_TIME,{position:y.position,duration:y.duration});if(y.position>=y.duration&&(y.position>0||y.duration>0)){w()}}}v(J)}function z(I){}function k(I){if(c[I.type]){if(I.type=="ended"){w()}else{E(c[I.type])}}}function G(I){var J={height:I.target.videoHeight,width:I.target.videoWidth,duration:Math.round(I.target.duration*10)/10};if((y.duration===0||isNaN(y.duration))&&I.target.duration!=Infinity){y.duration=Math.round(I.target.duration*10)/10}j.sendEvent(a.api.events.JWPLAYER_MEDIA_META,{metadata:J})}function u(K){if(B==a.api.events.state.IDLE){return}var J="There was an error: ";if((K.target.error&&K.target.tagName.toLowerCase()=="video")||K.target.parentNode.error&&K.target.parentNode.tagName.toLowerCase()=="video"){var I=!e.exists(K.target.error)?K.target.parentNode.error:K.target.error;switch(I.code){case I.MEDIA_ERR_ABORTED:J="You aborted the video playback: ";break;case I.MEDIA_ERR_NETWORK:J="A network error caused the video download to fail part-way: ";break;case I.MEDIA_ERR_DECODE:J="The video playback was aborted due to a corruption problem or because the video used features your browser did not support: ";break;case I.MEDIA_ERR_SRC_NOT_SUPPORTED:J="The video could not be loaded, either because the server or network failed or because the format is not supported: ";break;default:J="An unknown error occurred: ";break}}else{if(K.target.tagName.toLowerCase()=="source"){q--;if(q>0){return}J="The video could not be loaded, either because the server or network failed or because the format is not supported: "}else{e.log("An unknown error occurred.  Continuing...");return}}_stop(false);J+=F();_error=true;j.sendEvent(a.api.events.JWPLAYER_ERROR,{error:J});return}function F(){var K="";for(var J in x.levels){var I=x.levels[J];var L=l.ownerDocument.createElement("source");K+=a.utils.getAbsolutePath(I.file);if(J<(x.levels.length-1)){K+=", "}}return K}function t(){if(!e.exists(f)){f=setInterval(function(){v()},100)}}function g(){clearInterval(f);f=null}function w(){if(B!=a.api.events.state.IDLE){_stop(false);j.sendEvent(a.api.events.JWPLAYER_MEDIA_COMPLETE)}}}})(jwplayer);(function(a){var c={ended:a.api.events.state.IDLE,playing:a.api.events.state.PLAYING,pause:a.api.events.state.PAUSED,buffering:a.api.events.state.BUFFERING};var b=a.utils.css;a.html5.mediayoutube=function(j,e){var f=new a.html5.eventdispatcher();a.utils.extend(this,f);var l=j;var h=document.getElementById(e.id);var g=a.api.events.state.IDLE;var n,m;function k(p){if(g!=p){var q=g;l.state=p;g=p;f.sendEvent(a.api.events.JWPLAYER_PLAYER_STATE,{oldstate:q,newstate:p})}}this.getDisplayElement=function(){return h};this.play=function(){if(g==a.api.events.state.IDLE){f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:100});f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER_FULL);k(a.api.events.state.PLAYING)}else{if(g==a.api.events.state.PAUSED){k(a.api.events.state.PLAYING)}}};this.pause=function(){k(a.api.events.state.PAUSED)};this.seek=function(p){};this.stop=function(p){if(!_utils.exists(p)){p=true}l.position=0;k(a.api.events.state.IDLE);if(p){b(h,{display:"none"})}};this.volume=function(p){l.volume=p;f.sendEvent(a.api.events.JWPLAYER_MEDIA_VOLUME,{volume:Math.round(p)})};this.mute=function(p){h.muted=p;l.mute=p;f.sendEvent(a.api.events.JWPLAYER_MEDIA_MUTE,{mute:p})};this.resize=function(q,p){if(q*p>0&&n){n.width=m.width=q;n.height=m.height=p}f.sendEvent(a.api.events.JWPLAYER_MEDIA_RESIZE,{fullscreen:l.fullscreen,width:q,height:p})};this.fullscreen=function(p){if(p===true){this.resize("100%","100%")}else{this.resize(l.config.width,l.config.height)}};this.load=function(p){o(p);b(n,{display:"block"});k(a.api.events.state.BUFFERING);f.sendEvent(a.api.events.JWPLAYER_MEDIA_BUFFER,{bufferPercent:0});f.sendEvent(a.api.events.JWPLAYER_MEDIA_LOADED);this.play()};this.hasChrome=function(){return(g!=a.api.events.state.IDLE)};function o(v){var s=v.levels[0].file;s=["http://www.youtube.com/v/",d(s),"&amp;hl=en_US&amp;fs=1&autoplay=1"].join("");n=document.createElement("object");n.id=h.id;n.style.position="absolute";var u={movie:s,allowfullscreen:"true",allowscriptaccess:"always"};for(var p in u){var t=document.createElement("param");t.name=p;t.value=u[p];n.appendChild(t)}m=document.createElement("embed");n.appendChild(m);var q={src:s,type:"application/x-shockwave-flash",allowfullscreen:"true",allowscriptaccess:"always",width:n.width,height:n.height};for(var r in q){m.setAttribute(r,q[r])}n.appendChild(m);n.style.zIndex=2147483000;if(h!=n&&h.parentNode){h.parentNode.replaceChild(n,h)}h=n}function d(q){var p=q.split(/\?|\#\!/);var s="";for(var r=0;r<p.length;r++){if(p[r].substr(0,2)=="v="){s=p[r].substr(2)}}if(s==""){if(q.indexOf("/v/")>=0){s=q.substr(q.indexOf("/v/")+3)}else{if(q.indexOf("youtu.be")>=0){s=q.substr(q.indexOf("youtu.be/")+9)}else{s=q}}}if(s.indexOf("?")>-1){s=s.substr(0,s.indexOf("?"))}if(s.indexOf("&")>-1){s=s.substr(0,s.indexOf("&"))}return s}this.embed=m;return this}})(jwplayer);(function(jwplayer){var _configurableStateVariables=["width","height","start","duration","volume","mute","fullscreen","item","plugins","stretching"];jwplayer.html5.model=function(api,container,options){var _api=api;var _container=container;var _model={id:_container.id,playlist:[],state:jwplayer.api.events.state.IDLE,position:0,buffer:0,config:{width:480,height:320,item:-1,skin:undefined,file:undefined,image:undefined,start:0,duration:0,bufferlength:5,volume:90,mute:false,fullscreen:false,repeat:"",stretching:jwplayer.utils.stretching.UNIFORM,autostart:false,debug:undefined,screencolor:undefined}};var _media;var _eventDispatcher=new jwplayer.html5.eventdispatcher();var _components=["display","logo","controlbar","playlist","dock"];jwplayer.utils.extend(_model,_eventDispatcher);for(var option in options){if(typeof options[option]=="string"){var type=/color$/.test(option)?"color":null;options[option]=jwplayer.utils.typechecker(options[option],type)}var config=_model.config;var path=option.split(".");for(var edge in path){if(edge==path.length-1){config[path[edge]]=options[option]}else{if(!jwplayer.utils.exists(config[path[edge]])){config[path[edge]]={}}config=config[path[edge]]}}}for(var index in _configurableStateVariables){var configurableStateVariable=_configurableStateVariables[index];_model[configurableStateVariable]=_model.config[configurableStateVariable]}var pluginorder=_components.concat([]);if(jwplayer.utils.exists(_model.plugins)){if(typeof _model.plugins=="string"){var userplugins=_model.plugins.split(",");for(var userplugin in userplugins){if(typeof userplugins[userplugin]=="string"){pluginorder.push(userplugins[userplugin].replace(/^\s+|\s+$/g,""))}}}}if(jwplayer.utils.isIOS()){pluginorder=["display","logo","dock","playlist"];if(!jwplayer.utils.exists(_model.config.repeat)){_model.config.repeat="list"}}else{if(_model.config.chromeless){pluginorder=["logo","dock","playlist"];if(!jwplayer.utils.exists(_model.config.repeat)){_model.config.repeat="list"}}}_model.plugins={order:pluginorder,config:{},object:{}};if(typeof _model.config.components!="undefined"){for(var component in _model.config.components){_model.plugins.config[component]=_model.config.components[component]}}for(var pluginIndex in _model.plugins.order){var pluginName=_model.plugins.order[pluginIndex];var pluginConfig=!jwplayer.utils.exists(_model.plugins.config[pluginName])?{}:_model.plugins.config[pluginName];_model.plugins.config[pluginName]=!jwplayer.utils.exists(_model.plugins.config[pluginName])?pluginConfig:jwplayer.utils.extend(_model.plugins.config[pluginName],pluginConfig);if(!jwplayer.utils.exists(_model.plugins.config[pluginName].position)){if(pluginName=="playlist"){_model.plugins.config[pluginName].position=jwplayer.html5.view.positions.NONE}else{_model.plugins.config[pluginName].position=jwplayer.html5.view.positions.OVER}}else{_model.plugins.config[pluginName].position=_model.plugins.config[pluginName].position.toString().toUpperCase()}}if(typeof _model.plugins.config.dock!="undefined"){if(typeof _model.plugins.config.dock!="object"){var position=_model.plugins.config.dock.toString().toUpperCase();_model.plugins.config.dock={position:position}}if(typeof _model.plugins.config.dock.position!="undefined"){_model.plugins.config.dock.align=_model.plugins.config.dock.position;_model.plugins.config.dock.position=jwplayer.html5.view.positions.OVER}}function _loadExternal(playlistfile){var loader=new jwplayer.html5.playlistloader();loader.addEventListener(jwplayer.api.events.JWPLAYER_PLAYLIST_LOADED,function(evt){_model.playlist=new jwplayer.html5.playlist(evt);_loadComplete(true)});loader.addEventListener(jwplayer.api.events.JWPLAYER_ERROR,function(evt){_model.playlist=new jwplayer.html5.playlist({playlist:[]});_loadComplete(false)});loader.load(playlistfile)}function _loadComplete(){if(_model.config.shuffle){_model.item=_getShuffleItem()}else{if(_model.config.item>=_model.playlist.length){_model.config.item=_model.playlist.length-1}else{if(_model.config.item<0){_model.config.item=0}}_model.item=_model.config.item}_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_PLAYLIST_LOADED,{playlist:_model.playlist});_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_PLAYLIST_ITEM,{index:_model.item})}_model.loadPlaylist=function(arg){var input;if(typeof arg=="string"){if(arg.indexOf("[")==0||arg.indexOf("{")=="0"){try{input=eval(arg)}catch(err){input=arg}}else{input=arg}}else{input=arg}var config;switch(jwplayer.utils.typeOf(input)){case"object":config=input;break;case"array":config={playlist:input};break;default:_loadExternal(input);return;break}_model.playlist=new jwplayer.html5.playlist(config);if(jwplayer.utils.extension(_model.playlist[0].file)=="xml"){_loadExternal(_model.playlist[0].file)}else{_loadComplete()}};function _getShuffleItem(){var result=null;if(_model.playlist.length>1){while(!jwplayer.utils.exists(result)){result=Math.floor(Math.random()*_model.playlist.length);if(result==_model.item){result=null}}}else{result=0}return result}function forward(evt){if(evt.type==jwplayer.api.events.JWPLAYER_MEDIA_LOADED){_container=_media.getDisplayElement()}_eventDispatcher.sendEvent(evt.type,evt)}var _mediaProviders={};_model.setActiveMediaProvider=function(playlistItem){if(playlistItem.provider=="audio"){playlistItem.provider="sound"}var provider=playlistItem.provider;var current=_media?_media.getDisplayElement():null;if(provider=="sound"||provider=="http"||provider==""){provider="video"}if(!jwplayer.utils.exists(_mediaProviders[provider])){switch(provider){case"video":_media=new jwplayer.html5.mediavideo(_model,current?current:_container);break;case"youtube":_media=new jwplayer.html5.mediayoutube(_model,current?current:_container);break}if(!jwplayer.utils.exists(_media)){return false}_media.addGlobalListener(forward);_mediaProviders[provider]=_media}else{if(_media!=_mediaProviders[provider]){if(_media){_media.stop()}_media=_mediaProviders[provider]}}return true};_model.getMedia=function(){return _media};_model.seek=function(pos){_eventDispatcher.sendEvent(jwplayer.api.events.JWPLAYER_MEDIA_SEEK,{position:_model.position,offset:pos});return _media.seek(pos)};_model.setupPlugins=function(){if(!jwplayer.utils.exists(_model.plugins)||!jwplayer.utils.exists(_model.plugins.order)||_model.plugins.order.length==0){jwplayer.utils.log("No plugins to set up");return _model}for(var i=0;i<_model.plugins.order.length;i++){try{var pluginName=_model.plugins.order[i];if(jwplayer.utils.exists(jwplayer.html5[pluginName])){if(pluginName=="playlist"){_model.plugins.object[pluginName]=new jwplayer.html5.playlistcomponent(_api,_model.plugins.config[pluginName])}else{_model.plugins.object[pluginName]=new jwplayer.html5[pluginName](_api,_model.plugins.config[pluginName])}}else{_model.plugins.order.splice(plugin,plugin+1)}if(typeof _model.plugins.object[pluginName].addGlobalListener=="function"){_model.plugins.object[pluginName].addGlobalListener(forward)}}catch(err){jwplayer.utils.log("Could not setup "+pluginName)}}};return _model}})(jwplayer);(function(a){a.html5.playlist=function(b){var d=[];if(b.playlist&&b.playlist instanceof Array&&b.playlist.length>0){for(var c in b.playlist){if(!isNaN(parseInt(c))){d.push(new a.html5.playlistitem(b.playlist[c]))}}}else{d.push(new a.html5.playlistitem(b))}return d}})(jwplayer);(function(a){var c={size:180,position:a.html5.view.positions.NONE,itemheight:60,thumbs:true,fontcolor:"#000000",overcolor:"",activecolor:"",backgroundcolor:"#f8f8f8",font:"_sans",fontsize:"",fontstyle:"",fontweight:""};var b={_sans:"Arial, Helvetica, sans-serif",_serif:"Times, Times New Roman, serif",_typewriter:"Courier New, Courier, monospace"};_utils=a.utils;_css=_utils.css;_hide=function(d){_css(d,{display:"none"})};_show=function(d){_css(d,{display:"block"})};a.html5.playlistcomponent=function(r,B){var w=r;var e=a.utils.extend({},c,w.skin.getComponentSettings("playlist"),B);if(e.position==a.html5.view.positions.NONE||typeof a.html5.view.positions[e.position]=="undefined"){return}var x;var l;var C;var d;var g;var f;var k=-1;var h={background:undefined,item:undefined,itemOver:undefined,itemImage:undefined,itemActive:undefined};this.getDisplayElement=function(){return x};this.resize=function(F,D){l=F;C=D;if(w.jwGetFullscreen()){_hide(x)}else{var E={display:"block",width:l,height:C};_css(x,E)}};this.show=function(){_show(x)};this.hide=function(){_hide(x)};function j(){x=document.createElement("div");x.id=w.id+"_jwplayer_playlistcomponent";switch(e.position){case a.html5.view.positions.RIGHT:case a.html5.view.positions.LEFT:x.style.width=e.size+"px";break;case a.html5.view.positions.TOP:case a.html5.view.positions.BOTTOM:x.style.height=e.size+"px";break}A();if(h.item){e.itemheight=h.item.height}x.style.backgroundColor="#C6C6C6";w.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,s);w.jwAddEventListener(a.api.events.JWPLAYER_PLAYLIST_ITEM,u);w.jwAddEventListener(a.api.events.JWPLAYER_PLAYER_STATE,m)}function p(){var D=document.createElement("ul");_css(D,{width:x.style.width,minWidth:x.style.width,height:x.style.height,backgroundColor:e.backgroundcolor,backgroundImage:h.background?"url("+h.background.src+")":"",color:e.fontcolor,listStyle:"none",margin:0,padding:0,fontFamily:b[e.font]?b[e.font]:b._sans,fontSize:(e.fontsize?e.fontsize:11)+"px",fontStyle:e.fontstyle,fontWeight:e.fontweight,overflowY:"auto"});return D}function y(D){return function(){var E=f.getElementsByClassName("item")[D];var F=e.fontcolor;var G=h.item?"url("+h.item.src+")":"";if(D==w.jwGetPlaylistIndex()){if(e.activecolor!==""){F=e.activecolor}if(h.itemActive){G="url("+h.itemActive.src+")"}}_css(E,{color:e.overcolor!==""?e.overcolor:F,backgroundImage:h.itemOver?"url("+h.itemOver.src+")":G})}}function o(D){return function(){var E=f.getElementsByClassName("item")[D];var F=e.fontcolor;var G=h.item?"url("+h.item.src+")":"";if(D==w.jwGetPlaylistIndex()){if(e.activecolor!==""){F=e.activecolor}if(h.itemActive){G="url("+h.itemActive.src+")"}}_css(E,{color:F,backgroundImage:G})}}function q(I){var P=d[I];var O=document.createElement("li");O.className="item";_css(O,{height:e.itemheight,display:"block",cursor:"pointer",backgroundImage:h.item?"url("+h.item.src+")":"",backgroundSize:"100% "+e.itemheight+"px"});O.onmouseover=y(I);O.onmouseout=o(I);var J=document.createElement("div");var F=new Image();var K=0;var L=0;var M=0;if(v()&&(P.image||P["playlist.image"]||h.itemImage)){F.className="image";if(h.itemImage){K=(e.itemheight-h.itemImage.height)/2;L=h.itemImage.width;M=h.itemImage.height}else{L=e.itemheight*4/3;M=e.itemheight}_css(J,{height:M,width:L,"float":"left",styleFloat:"left",cssFloat:"left",margin:"0 5px 0 0",background:"black",overflow:"hidden",margin:K+"px",position:"relative"});_css(F,{position:"relative"});J.appendChild(F);F.onload=function(){a.utils.stretch(a.utils.stretching.FILL,F,L,M,this.naturalWidth,this.naturalHeight)};if(P["playlist.image"]){F.src=P["playlist.image"]}else{if(P.image){F.src=P.image}else{if(h.itemImage){F.src=h.itemImage.src}}}O.appendChild(J)}var E=l-L-K*2;if(C<e.itemheight*d.length){E-=15}var D=document.createElement("div");_css(D,{position:"relative",height:"100%",overflow:"hidden"});var G=document.createElement("span");if(P.duration>0){G.className="duration";_css(G,{fontSize:(e.fontsize?e.fontsize:11)+"px",fontWeight:(e.fontweight?e.fontweight:"bold"),width:"40px",height:e.fontsize?e.fontsize+10:20,lineHeight:24,"float":"right",styleFloat:"right",cssFloat:"right"});G.innerHTML=_utils.timeFormat(P.duration);D.appendChild(G)}var N=document.createElement("span");N.className="title";_css(N,{padding:"5px 5px 0 "+(K?0:"5px"),height:e.fontsize?e.fontsize+10:20,lineHeight:e.fontsize?e.fontsize+10:20,overflow:"hidden","float":"left",styleFloat:"left",cssFloat:"left",width:((P.duration>0)?E-50:E)-10+"px",fontSize:(e.fontsize?e.fontsize:13)+"px",fontWeight:(e.fontweight?e.fontweight:"bold")});N.innerHTML=P?P.title:"";D.appendChild(N);if(P.description){var H=document.createElement("span");H.className="description";_css(H,{display:"block","float":"left",styleFloat:"left",cssFloat:"left",margin:0,paddingLeft:N.style.paddingLeft,paddingRight:N.style.paddingRight,lineHeight:(e.fontsize?e.fontsize+4:16)+"px",overflow:"hidden",position:"relative"});H.innerHTML=P.description;D.appendChild(H)}O.appendChild(D);return O}function s(E){x.innerHTML="";d=w.jwGetPlaylist();if(!d){return}items=[];f=p();for(var F=0;F<d.length;F++){var D=q(F);D.onclick=z(F);f.appendChild(D);items.push(D)}k=w.jwGetPlaylistIndex();o(k)();x.appendChild(f);if(_utils.isIOS()&&window.iScroll){f.style.height=e.itemheight*d.length+"px";var G=new iScroll(x.id)}}function z(D){return function(){w.jwPlaylistItem(D);w.jwPlay(true)}}function n(){f.scrollTop=w.jwGetPlaylistIndex()*e.itemheight}function v(){return e.thumbs.toString().toLowerCase()=="true"}function u(D){if(k>=0){o(k)();k=D.index}o(D.index)();n()}function m(){if(e.position==a.html5.view.positions.OVER){switch(w.jwGetState()){case a.api.events.state.IDLE:_show(x);break;default:_hide(x);break}}}function A(){for(var D in h){h[D]=t(D)}}function t(D){return w.skin.getSkinElement("playlist",D)}j();return this}})(jwplayer);(function(b){b.html5.playlistitem=function(d){var e={author:"",date:"",description:"",image:"",link:"",mediaid:"",tags:"",title:"",provider:"",file:"",streamer:"",duration:-1,start:0,currentLevel:-1,levels:[]};var c=b.utils.extend({},e,d);if(c.type){c.provider=c.type;delete c.type}if(c.levels.length===0){c.levels[0]=new b.html5.playlistitemlevel(c)}if(!c.provider){c.provider=a(c.levels[0])}else{c.provider=c.provider.toLowerCase()}return c};function a(e){if(b.utils.isYouTube(e.file)){return"youtube"}else{var f=b.utils.extension(e.file);var c;if(f&&b.utils.extensionmap[f]){if(f=="m3u8"){return"video"}c=b.utils.extensionmap[f].html5}else{if(e.type){c=e.type}}if(c){var d=c.split("/")[0];if(d=="audio"){return"sound"}else{if(d=="video"){return d}}}}return""}})(jwplayer);(function(a){a.html5.playlistitemlevel=function(b){var d={file:"",streamer:"",bitrate:0,width:0};for(var c in d){if(a.utils.exists(b[c])){d[c]=b[c]}}return d}})(jwplayer);(function(a){a.html5.playlistloader=function(){var c=new a.html5.eventdispatcher();a.utils.extend(this,c);this.load=function(e){a.utils.ajax(e,d,b)};function d(g){var f=[];try{var f=a.utils.parsers.rssparser.parse(g.responseXML.firstChild);c.sendEvent(a.api.events.JWPLAYER_PLAYLIST_LOADED,{playlist:new a.html5.playlist({playlist:f})})}catch(h){b("Could not parse the playlist")}}function b(e){c.sendEvent(a.api.events.JWPLAYER_ERROR,{error:e?e:"could not load playlist for whatever reason.  too bad"})}}})(jwplayer);(function(a){a.html5.skin=function(){var b={};var c=false;this.load=function(d,e){new a.html5.skinloader(d,function(f){c=true;b=f;e()},function(){new a.html5.skinloader("",function(f){c=true;b=f;e()})})};this.getSkinElement=function(d,e){if(c){try{return b[d].elements[e]}catch(f){a.utils.log("No such skin component / element: ",[d,e])}}return null};this.getComponentSettings=function(d){if(c){return b[d].settings}return null};this.getComponentLayout=function(d){if(c){return b[d].layout}return null}}})(jwplayer);(function(a){a.html5.skinloader=function(f,p,k){var o={};var c=p;var l=k;var e=true;var j;var n=f;var s=false;function m(){if(typeof n!="string"||n===""){d(a.html5.defaultSkin().xml)}else{a.utils.ajax(a.utils.getAbsolutePath(n),function(t){try{if(a.utils.exists(t.responseXML)){d(t.responseXML);return}}catch(u){h()}d(a.html5.defaultSkin().xml)},function(t){d(a.html5.defaultSkin().xml)})}}function d(y){var E=y.getElementsByTagName("component");if(E.length===0){return}for(var H=0;H<E.length;H++){var C=E[H].getAttribute("name");var B={settings:{},elements:{},layout:{}};o[C]=B;var G=E[H].getElementsByTagName("elements")[0].getElementsByTagName("element");for(var F=0;F<G.length;F++){b(G[F],C)}var z=E[H].getElementsByTagName("settings")[0];if(z&&z.childNodes.length>0){var K=z.getElementsByTagName("setting");for(var P=0;P<K.length;P++){var Q=K[P].getAttribute("name");var I=K[P].getAttribute("value");var x=/color$/.test(Q)?"color":null;o[C].settings[Q]=a.utils.typechecker(I,x)}}var L=E[H].getElementsByTagName("layout")[0];if(L&&L.childNodes.length>0){var M=L.getElementsByTagName("group");for(var w=0;w<M.length;w++){var A=M[w];o[C].layout[A.getAttribute("position")]={elements:[]};for(var O=0;O<A.attributes.length;O++){var D=A.attributes[O];o[C].layout[A.getAttribute("position")][D.name]=D.value}var N=A.getElementsByTagName("*");for(var v=0;v<N.length;v++){var t=N[v];o[C].layout[A.getAttribute("position")].elements.push({type:t.tagName});for(var u=0;u<t.attributes.length;u++){var J=t.attributes[u];o[C].layout[A.getAttribute("position")].elements[v][J.name]=J.value}if(!a.utils.exists(o[C].layout[A.getAttribute("position")].elements[v].name)){o[C].layout[A.getAttribute("position")].elements[v].name=t.tagName}}}}e=false;r()}}function r(){clearInterval(j);if(!s){j=setInterval(function(){q()},100)}}function b(y,x){var w=new Image();var t=y.getAttribute("name");var v=y.getAttribute("src");var A;if(v.indexOf("data:image/png;base64,")===0){A=v}else{var u=a.utils.getAbsolutePath(n);var z=u.substr(0,u.lastIndexOf("/"));A=[z,x,v].join("/")}o[x].elements[t]={height:0,width:0,src:"",ready:false,image:w};w.onload=function(B){g(w,t,x)};w.onerror=function(B){s=true;r();l()};w.src=A}function h(){for(var u in o){var w=o[u];for(var t in w.elements){var x=w.elements[t];var v=x.image;v.onload=null;v.onerror=null;delete x.image;delete w.elements[t]}delete o[u]}}function q(){for(var t in o){if(t!="properties"){for(var u in o[t].elements){if(!o[t].elements[u].ready){return}}}}if(e===false){clearInterval(j);c(o)}}function g(t,v,u){if(o[u]&&o[u].elements[v]){o[u].elements[v].height=t.height;o[u].elements[v].width=t.width;o[u].elements[v].src=t.src;o[u].elements[v].ready=true;r()}else{a.utils.log("Loaded an image for a missing element: "+u+"."+v)}}m()}})(jwplayer);(function(a){a.html5.api=function(c,n){var m={};var f=document.createElement("div");c.parentNode.replaceChild(f,c);f.id=c.id;m.version=a.version;m.id=f.id;var l=new a.html5.model(m,f,n);var j=new a.html5.view(m,f,l);var k=new a.html5.controller(m,f,l,j);m.skin=new a.html5.skin();m.jwPlay=function(o){if(typeof o=="undefined"){e()}else{if(o.toString().toLowerCase()=="true"){k.play()}else{k.pause()}}};m.jwPause=function(o){if(typeof o=="undefined"){e()}else{if(o.toString().toLowerCase()=="true"){k.pause()}else{k.play()}}};function e(){if(l.state==a.api.events.state.PLAYING||l.state==a.api.events.state.BUFFERING){k.pause()}else{k.play()}}m.jwStop=k.stop;m.jwSeek=k.seek;m.jwPlaylistItem=k.item;m.jwPlaylistNext=k.next;m.jwPlaylistPrev=k.prev;m.jwResize=k.resize;m.jwLoad=k.load;function h(o){return function(){return l[o]}}function d(o,q,p){return function(){var r=l.plugins.object[o];if(r&&r[q]&&typeof r[q]=="function"){r[q].apply(r,p)}}}m.jwGetItem=h("item");m.jwGetPosition=h("position");m.jwGetDuration=h("duration");m.jwGetBuffer=h("buffer");m.jwGetWidth=h("width");m.jwGetHeight=h("height");m.jwGetFullscreen=h("fullscreen");m.jwSetFullscreen=k.setFullscreen;m.jwGetVolume=h("volume");m.jwSetVolume=k.setVolume;m.jwGetMute=h("mute");m.jwSetMute=k.setMute;m.jwGetStretching=h("stretching");m.jwGetState=h("state");m.jwGetVersion=function(){return m.version};m.jwGetPlaylist=function(){return l.playlist};m.jwGetPlaylistIndex=m.jwGetItem;m.jwAddEventListener=k.addEventListener;m.jwRemoveEventListener=k.removeEventListener;m.jwSendEvent=k.sendEvent;m.jwDockSetButton=function(r,o,p,q){if(l.plugins.object.dock&&l.plugins.object.dock.setButton){l.plugins.object.dock.setButton(r,o,p,q)}};m.jwControlbarShow=d("controlbar","show");m.jwControlbarHide=d("controlbar","hide");m.jwDockShow=d("dock","show");m.jwDockHide=d("dock","hide");m.jwDisplayShow=d("display","show");m.jwDisplayHide=d("display","hide");m.jwGetLevel=function(){};m.jwGetBandwidth=function(){};m.jwGetLockState=function(){};m.jwLock=function(){};m.jwUnlock=function(){};function b(){if(l.config.playlistfile){l.addEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,g);l.loadPlaylist(l.config.playlistfile)}else{if(typeof l.config.playlist=="string"){l.addEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,g);l.loadPlaylist(l.config.playlist)}else{l.loadPlaylist(l.config);setTimeout(g,25)}}}function g(o){l.removeEventListener(a.api.events.JWPLAYER_PLAYLIST_LOADED,g);l.setupPlugins();j.setup();var o={id:m.id,version:m.version};k.playerReady(o)}if(l.config.chromeless&&!a.utils.isIOS()){b()}else{m.skin.load(l.config.skin,b)}return m}})(jwplayer)};


// ColorBox v1.3.17.1 - a full featured, light-weight, customizable lightbox based on jQuery 1.3+
// Copyright (c) 2011 Jack Moore - jack@colorpowered.com
// Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php

(function ($, document, window) {
	var
	// ColorBox Default Settings.	
	// See http://colorpowered.com/colorbox for details.
	defaults = {
		transition: "elastic",
		speed: 300,
		width: false,
		initialWidth: "600",
		innerWidth: false,
		maxWidth: false,
		height: false,
		initialHeight: "450",
		innerHeight: false,
		maxHeight: false,
		scalePhotos: true,
		scrolling: true,
		inline: false,
		html: false,
		iframe: false,
		fastIframe: true,
		photo: false,
		href: false,
		title: false,
		rel: false,
		opacity: 0.9,
		preloading: true,
		current: "image {current} of {total}",
		previous: "previous",
		next: "next",
		close: "close",
		open: false,
		returnFocus: true,
		loop: true,
		slideshow: false,
		slideshowAuto: true,
		slideshowSpeed: 2500,
		slideshowStart: "start slideshow",
		slideshowStop: "stop slideshow",
		onOpen: false,
		onLoad: false,
		onComplete: false,
		onCleanup: false,
		onClosed: false,
		overlayClose: true,		
		escKey: true,
		arrowKey: true,
        top: false,
        bottom: false,
        left: false,
        right: false,
        fixed: false,
        data: false
	},
	
	// Abstracting the HTML and event identifiers for easy rebranding
	colorbox = 'colorbox',
	prefix = 'cbox',
	
	// Events	
	event_open = prefix + '_open',
	event_load = prefix + '_load',
	event_complete = prefix + '_complete',
	event_cleanup = prefix + '_cleanup',
	event_closed = prefix + '_closed',
	event_purge = prefix + '_purge',
	
	// Special Handling for IE
	isIE = $.browser.msie && !$.support.opacity, // Detects IE6,7,8.  IE9 supports opacity.  Feature detection alone gave a false positive on at least one phone browser and on some development versions of Chrome, hence the user-agent test.
	isIE6 = isIE && $.browser.version < 7,
	event_ie6 = prefix + '_IE6',

	// Cached jQuery Object Variables
	$overlay,
	$box,
	$wrap,
	$content,
	$topBorder,
	$leftBorder,
	$rightBorder,
	$bottomBorder,
	$related,
	$window,
	$loaded,
	$loadingBay,
	$loadingOverlay,
	$title,
	$current,
	$slideshow,
	$next,
	$prev,
	$close,
	$groupControls,

	// Variables for cached values or use across multiple functions
	settings = {},
	interfaceHeight,
	interfaceWidth,
	loadedHeight,
	loadedWidth,
	element,
	index,
	photo,
	open,
	active,
	closing,
    handler,
    loadingTimer,
	
	publicMethod,
	boxElement = prefix + 'Element';
	
	// ****************
	// HELPER FUNCTIONS
	// ****************

	// jQuery object generator to reduce code size
	function $div(id, cssText) { 
		var div = document.createElement('div');
		if (id) {
            div.id = prefix + id;
        }
		div.style.cssText = cssText || '';
		return $(div);
	}

	// Convert % values to pixels
	function setSize(size, dimension) {
		dimension = dimension === 'x' ? $window.width() : $window.height();
		return (typeof size === 'string') ? Math.round((/%/.test(size) ? (dimension / 100) * parseInt(size, 10) : parseInt(size, 10))) : size;
	}
	
	// Checks an href to see if it is a photo.
	// There is a force photo option (photo: true) for hrefs that cannot be matched by this regex.
	function isImage(url) {
		return settings.photo || /\.(gif|png|jpg|jpeg|bmp)(?:\?([^#]*))?(?:#(\.*))?$/i.test(url);
	}
	
	// Assigns function results to their respective settings.  This allows functions to be used as values.
	function process(settings) {
		for (var i in settings) {
			if ($.isFunction(settings[i]) && i.substring(0, 2) !== 'on') { // checks to make sure the function isn't one of the callbacks, they will be handled at the appropriate time.
			    settings[i] = settings[i].call(element);
			}
		}
        
		settings.rel = settings.rel || element.rel || 'nofollow';
		settings.href = settings.href || $(element).attr('href');
		settings.title = settings.title || element.title;
        
        if (typeof settings.href === "string") {
            settings.href = $.trim(settings.href);
        }
	}

	function trigger(event, callback) {
		if (callback) {
			callback.call(element);
		}
		$.event.trigger(event);
	}

	// Slideshow functionality
	function slideshow() {
		var
		timeOut,
		className = prefix + "Slideshow_",
		click = "click." + prefix,
		start,
		stop,
		clear;
		
		if (settings.slideshow && $related[1]) {
			start = function () {
				$slideshow
					.text(settings.slideshowStop)
					.unbind(click)
					.bind(event_complete, function () {
						if (index < $related.length - 1 || settings.loop) {
							timeOut = setTimeout(publicMethod.next, settings.slideshowSpeed);
						}
					})
					.bind(event_load, function () {
						clearTimeout(timeOut);
					})
					.one(click + ' ' + event_cleanup, stop);
				$box.removeClass(className + "off").addClass(className + "on");
				timeOut = setTimeout(publicMethod.next, settings.slideshowSpeed);
			};
			
			stop = function () {
				clearTimeout(timeOut);
				$slideshow
					.text(settings.slideshowStart)
					.unbind([event_complete, event_load, event_cleanup, click].join(' '))
					.one(click, start);
				$box.removeClass(className + "on").addClass(className + "off");
			};
			
			if (settings.slideshowAuto) {
				start();
			} else {
				stop();
			}
		} else {
            $box.removeClass(className + "off " + className + "on");
        }
	}

	function launch(elem) {
		if (!closing) {
			
			element = elem;
			
			process($.extend(settings, $.data(element, colorbox)));
			
			$related = $(element);
			
			index = 0;
			
			if (settings.rel !== 'nofollow') {
				$related = $('.' + boxElement).filter(function () {
					var relRelated = $.data(this, colorbox).rel || this.rel;
					return (relRelated === settings.rel);
				});
				index = $related.index(element);
				
				// Check direct calls to ColorBox.
				if (index === -1) {
					$related = $related.add(element);
					index = $related.length - 1;
				}
			}
			
			if (!open) {
				open = active = true; // Prevents the page-change action from queuing up if the visitor holds down the left or right keys.
				
				$box.show();
				
				if (settings.returnFocus) {
					try {
						element.blur();
						$(element).one(event_closed, function () {
							try {
								this.focus();
							} catch (e) {
								// do nothing
							}
						});
					} catch (e) {
						// do nothing
					}
				}
				
				// +settings.opacity avoids a problem in IE when using non-zero-prefixed-string-values, like '.5'
				$overlay.css({"opacity": +settings.opacity, "cursor": settings.overlayClose ? "pointer" : "auto"}).show();
				
				// Opens inital empty ColorBox prior to content being loaded.
				settings.w = setSize(settings.initialWidth, 'x');
				settings.h = setSize(settings.initialHeight, 'y');
				publicMethod.position(0);
				
				if (isIE6) {
					$window.bind('resize.' + event_ie6 + ' scroll.' + event_ie6, function () {
						$overlay.css({width: $window.width(), height: $window.height(), top: $window.scrollTop(), left: $window.scrollLeft()});
					}).trigger('resize.' + event_ie6);
				}
				
				trigger(event_open, settings.onOpen);
				
				$groupControls.add($title).hide();
				
				$close.html(settings.close).show();
			}
			
			publicMethod.load(true);
		}
	}

	// ****************
	// PUBLIC FUNCTIONS
	// Usage format: $.fn.colorbox.close();
	// Usage from within an iframe: parent.$.fn.colorbox.close();
	// ****************
	
	publicMethod = $.fn[colorbox] = $[colorbox] = function (options, callback) {
		var $this = this, autoOpen;
		
		if (!$this[0] && $this.selector) { // if a selector was given and it didn't match any elements, go ahead and exit.
			return $this;
		}
		
		options = options || {};
		
		if (callback) {
			options.onComplete = callback;
		}
		
		if (!$this[0] || $this.selector === undefined) { // detects $.colorbox() and $.fn.colorbox()
			$this = $('<a/>');
			options.open = true; // assume an immediate open
		}
		
		$this.each(function () {
			$.data(this, colorbox, $.extend({}, $.data(this, colorbox) || defaults, options));
			$(this).addClass(boxElement);
		});
		
		autoOpen = options.open;
		
		if ($.isFunction(autoOpen)) {
			autoOpen = autoOpen.call($this);
		}
		
		if (autoOpen) {
			launch($this[0]);
		}
		
		return $this;
	};

	// Initialize ColorBox: store common calculations, preload the interface graphics, append the html.
	// This preps colorbox for a speedy open when clicked, and lightens the burdon on the browser by only
	// having to run once, instead of each time colorbox is opened.
	publicMethod.init = function () {
		// Create & Append jQuery Objects
		$window = $(window);
		$box = $div().attr({id: colorbox, 'class': isIE ? prefix + (isIE6 ? 'IE6' : 'IE') : ''});
		$overlay = $div("Overlay", isIE6 ? 'position:absolute' : '').hide();
		
		$wrap = $div("Wrapper");
		$content = $div("Content").append(
			$loaded = $div("LoadedContent", 'width:0; height:0; overflow:hidden'),
			$loadingOverlay = $div("LoadingOverlay").add($div("LoadingGraphic")),
			$title = $div("Title"),
			$current = $div("Current"),
			$next = $div("Next"),
			$prev = $div("Previous"),
			$slideshow = $div("Slideshow").bind(event_open, slideshow),
			$close = $div("Close")
		);
		$wrap.append( // The 3x3 Grid that makes up ColorBox
			$div().append(
				$div("TopLeft"),
				$topBorder = $div("TopCenter"),
				$div("TopRight")
			),
			$div(false, 'clear:left').append(
				$leftBorder = $div("MiddleLeft"),
				$content,
				$rightBorder = $div("MiddleRight")
			),
			$div(false, 'clear:left').append(
				$div("BottomLeft"),
				$bottomBorder = $div("BottomCenter"),
				$div("BottomRight")
			)
		).children().children().css({'float': 'left'});
		
		$loadingBay = $div(false, 'position:absolute; width:9999px; visibility:hidden; display:none');
		
		$('body').prepend($overlay, $box.append($wrap, $loadingBay));
		
		$content.children()
		.hover(function () {
			$(this).addClass('hover');
		}, function () {
			$(this).removeClass('hover');
		}).addClass('hover');
		
		// Cache values needed for size calculations
		interfaceHeight = $topBorder.height() + $bottomBorder.height() + $content.outerHeight(true) - $content.height();//Subtraction needed for IE6
		interfaceWidth = $leftBorder.width() + $rightBorder.width() + $content.outerWidth(true) - $content.width();
		loadedHeight = $loaded.outerHeight(true);
		loadedWidth = $loaded.outerWidth(true);
		
		// Setting padding to remove the need to do size conversions during the animation step.
		$box.css({"padding-bottom": interfaceHeight, "padding-right": interfaceWidth}).hide();
		
        // Setup button events.
        $next.click(function () {
            publicMethod.next();
        });
        $prev.click(function () {
            publicMethod.prev();
        });
        $close.click(function () {
            publicMethod.close();
        });
		
		$groupControls = $next.add($prev).add($current).add($slideshow);
		
		// Adding the 'hover' class allowed the browser to load the hover-state
		// background graphics.  The class can now can be removed.
		$content.children().removeClass('hover');
		


        
		$overlay.click(function () {
			if (settings.overlayClose) {
				publicMethod.close();
			}
		});
		
		// Set Navigation Key Bindings
		$(document).bind('keydown.' + prefix, function (e) {
            var key = e.keyCode;
			if (open && settings.escKey && key === 27) {
				e.preventDefault();
				publicMethod.close();
			}
			if (open && settings.arrowKey && $related[1]) {
				if (key === 37) {
					e.preventDefault();
					$prev.click();
				} else if (key === 39) {
					e.preventDefault();
					$next.click();
				}
			}
		});
	};
	
	publicMethod.remove = function () {
		$box.add($overlay).remove();
		$('.' + boxElement).removeData(colorbox).removeClass(boxElement);
	};

	publicMethod.position = function (speed, loadedCallback) {
        var animate_speed, top = 0, left = 0;
        
        // remove the modal so that it doesn't influence the document width/height        
        $box.hide();
        
        if (settings.fixed && !isIE6) {
            $box.css({position: 'fixed'});
        } else {
            top = $window.scrollTop();
            left = $window.scrollLeft();
            $box.css({position: 'absolute'});
        }
        
		// keeps the top and left positions within the browser's viewport.
        if (settings.right !== false) {
            left += Math.max($window.width() - settings.w - loadedWidth - interfaceWidth - setSize(settings.right, 'x'), 0);
        } else if (settings.left !== false) {
            left += setSize(settings.left, 'x');
        } else {
            left += Math.max($window.width() - settings.w - loadedWidth - interfaceWidth, 0) / 2;
        }
        
        if (settings.bottom !== false) {
            top += Math.max(document.documentElement.clientHeight - settings.h - loadedHeight - interfaceHeight - setSize(settings.bottom, 'y'), 0);
        } else if (settings.top !== false) {
            top += setSize(settings.top, 'y');
        } else {
            top += Math.max(document.documentElement.clientHeight - settings.h - loadedHeight - interfaceHeight, 0) / 2;
        }
        
        $box.show();
        
		// setting the speed to 0 to reduce the delay between same-sized content.
		animate_speed = ($box.width() === settings.w + loadedWidth && $box.height() === settings.h + loadedHeight) ? 0 : speed;
        
		// this gives the wrapper plenty of breathing room so it's floated contents can move around smoothly,
		// but it has to be shrank down around the size of div#colorbox when it's done.  If not,
		// it can invoke an obscure IE bug when using iframes.
		$wrap[0].style.width = $wrap[0].style.height = "9999px";
		
		function modalDimensions(that) {
			// loading overlay height has to be explicitly set for IE6.
			$topBorder[0].style.width = $bottomBorder[0].style.width = $content[0].style.width = that.style.width;
			$loadingOverlay[0].style.height = $loadingOverlay[1].style.height = $content[0].style.height = $leftBorder[0].style.height = $rightBorder[0].style.height = that.style.height;
		}
		
		$box.dequeue().animate({width: settings.w + loadedWidth, height: settings.h + loadedHeight, top: top, left: left}, {
			duration: animate_speed,
			complete: function () {
				modalDimensions(this);
				
				active = false;
				
				// shrink the wrapper down to exactly the size of colorbox to avoid a bug in IE's iframe implementation.
				$wrap[0].style.width = (settings.w + loadedWidth + interfaceWidth) + "px";
				$wrap[0].style.height = (settings.h + loadedHeight + interfaceHeight) + "px";
				
				if (loadedCallback) {
					loadedCallback();
				}
			},
			step: function () {
				modalDimensions(this);
			}
		});
	};

	publicMethod.resize = function (options) {
		if (open) {
			options = options || {};
			
			if (options.width) {
				settings.w = setSize(options.width, 'x') - loadedWidth - interfaceWidth;
			}
			if (options.innerWidth) {
				settings.w = setSize(options.innerWidth, 'x');
			}
			$loaded.css({width: settings.w});
			
			if (options.height) {
				settings.h = setSize(options.height, 'y') - loadedHeight - interfaceHeight;
			}
			if (options.innerHeight) {
				settings.h = setSize(options.innerHeight, 'y');
			}
			if (!options.innerHeight && !options.height) {				
				var $child = $loaded.wrapInner("<div style='overflow:auto'></div>").children(); // temporary wrapper to get an accurate estimate of just how high the total content should be.
				settings.h = $child.height();
				$child.replaceWith($child.children()); // ditch the temporary wrapper div used in height calculation
			}
			$loaded.css({height: settings.h});
			
			publicMethod.position(settings.transition === "none" ? 0 : settings.speed);
		}
	};

	publicMethod.prep = function (object) {
		if (!open) {
			return;
		}
		
		var speed = settings.transition === "none" ? 0 : settings.speed;
		
		$window.unbind('resize.' + prefix);
		$loaded.remove();
		$loaded = $div('LoadedContent').html(object);
		
		function getWidth() {
			settings.w = settings.w || $loaded.width();
			settings.w = settings.mw && settings.mw < settings.w ? settings.mw : settings.w;
			return settings.w;
		}
		function getHeight() {
			settings.h = settings.h || $loaded.height();
			settings.h = settings.mh && settings.mh < settings.h ? settings.mh : settings.h;
			return settings.h;
		}
		
		$loaded.hide()
		.appendTo($loadingBay.show())// content has to be appended to the DOM for accurate size calculations.
		.css({width: getWidth(), overflow: settings.scrolling ? 'auto' : 'hidden'})
		.css({height: getHeight()})// sets the height independently from the width in case the new width influences the value of height.
		.prependTo($content);
		
		$loadingBay.hide();
		
		// floating the IMG removes the bottom line-height and fixed a problem where IE miscalculates the width of the parent element as 100% of the document width.
		//$(photo).css({'float': 'none', marginLeft: 'auto', marginRight: 'auto'});
		
        $(photo).css({'float': 'none'});
        
		// Hides SELECT elements in IE6 because they would otherwise sit on top of the overlay.
		if (isIE6) {
			$('select').not($box.find('select')).filter(function () {
				return this.style.visibility !== 'hidden';
			}).css({'visibility': 'hidden'}).one(event_cleanup, function () {
				this.style.visibility = 'inherit';
			});
		}
		
		function setPosition(s) {
			publicMethod.position(s, function () {
				var prev, prevSrc, next, nextSrc, total = $related.length, iframe, complete;
				
				if (!open) {
					return;
				}
				
                function removeFilter() {
                    if (isIE) {
                        $box[0].style.removeAttribute('filter');
                    }
                }
                
				complete = function () {
                    clearTimeout(loadingTimer);
					$loadingOverlay.hide();
					trigger(event_complete, settings.onComplete);
				};
				
				if (isIE) {
					//This fadeIn helps the bicubic resampling to kick-in.
					if (photo) {
						$loaded.fadeIn(100);
					}
				}
				
				$title.html(settings.title).add($loaded).show();
				
				if (total > 1) { // handle grouping
					if (typeof settings.current === "string") {
						$current.html(settings.current.replace(/\{current\}/, index + 1).replace(/\{total\}/, total)).show();
					}
					
					$next[(settings.loop || index < total - 1) ? "show" : "hide"]().html(settings.next);
					$prev[(settings.loop || index) ? "show" : "hide"]().html(settings.previous);
					
					prev = index ? $related[index - 1] : $related[total - 1];
					next = index < total - 1 ? $related[index + 1] : $related[0];
					
					if (settings.slideshow) {
						$slideshow.show();
					}
					
					// Preloads images within a rel group
					if (settings.preloading) {
						nextSrc = $.data(next, colorbox).href || next.href;
						prevSrc = $.data(prev, colorbox).href || prev.href;
						
						nextSrc = $.isFunction(nextSrc) ? nextSrc.call(next) : nextSrc;
						prevSrc = $.isFunction(prevSrc) ? prevSrc.call(prev) : prevSrc;
						
						if (isImage(nextSrc)) {
							$('<img/>')[0].src = nextSrc;
						}
						
						if (isImage(prevSrc)) {
							$('<img/>')[0].src = prevSrc;
						}
					}
				} else {
					$groupControls.hide();
				}
				
				if (settings.iframe) {
					iframe = $('<iframe/>').addClass(prefix + 'Iframe')[0];
					
					if (settings.fastIframe) {
						complete();
					} else {
						$(iframe).one('load', complete);
					}
					iframe.name = prefix + (+new Date());
					iframe.src = settings.href;
					
					if (!settings.scrolling) {
						iframe.scrolling = "no";
					}
					
					if (isIE) {
                        iframe.frameBorder = 0;
						iframe.allowTransparency = "true";
					}
					
					$(iframe).appendTo($loaded).one(event_purge, function () {
						iframe.src = "//about:blank";
					});
				} else {
					complete();
				}
				
				if (settings.transition === 'fade') {
					$box.fadeTo(speed, 1, removeFilter);
				} else {
                    removeFilter();
				}
				
				$window.bind('resize.' + prefix, function () {
					publicMethod.position(0);
				});
			});
		}
		
		if (settings.transition === 'fade') {
			$box.fadeTo(speed, 0, function () {
				setPosition(0);
			});
		} else {
			setPosition(speed);
		}
	};

	publicMethod.load = function (launched) {
		var href, setResize, prep = publicMethod.prep;
		
		active = true;
		
		photo = false;
		
		element = $related[index];
		
		if (!launched) {
			process($.extend(settings, $.data(element, colorbox)));
		}
		
		trigger(event_purge);
		
		trigger(event_load, settings.onLoad);
		
		settings.h = settings.height ?
				setSize(settings.height, 'y') - loadedHeight - interfaceHeight :
				settings.innerHeight && setSize(settings.innerHeight, 'y');
		
		settings.w = settings.width ?
				setSize(settings.width, 'x') - loadedWidth - interfaceWidth :
				settings.innerWidth && setSize(settings.innerWidth, 'x');
		
		// Sets the minimum dimensions for use in image scaling
		settings.mw = settings.w;
		settings.mh = settings.h;
		
		// Re-evaluate the minimum width and height based on maxWidth and maxHeight values.
		// If the width or height exceed the maxWidth or maxHeight, use the maximum values instead.
		if (settings.maxWidth) {
			settings.mw = setSize(settings.maxWidth, 'x') - loadedWidth - interfaceWidth;
			settings.mw = settings.w && settings.w < settings.mw ? settings.w : settings.mw;
		}
		if (settings.maxHeight) {
			settings.mh = setSize(settings.maxHeight, 'y') - loadedHeight - interfaceHeight;
			settings.mh = settings.h && settings.h < settings.mh ? settings.h : settings.mh;
		}
		
		href = settings.href;
		
        loadingTimer = setTimeout(function () {
            $loadingOverlay.show();
        }, 100);
        
		if (settings.inline) {
			// Inserts an empty placeholder where inline content is being pulled from.
			// An event is bound to put inline content back when ColorBox closes or loads new content.
			$div().hide().insertBefore($(href)[0]).one(event_purge, function () {
				$(this).replaceWith($loaded.children());
			});
			prep($(href));
		} else if (settings.iframe) {
			// IFrame element won't be added to the DOM until it is ready to be displayed,
			// to avoid problems with DOM-ready JS that might be trying to run in that iframe.
			prep(" ");
		} else if (settings.html) {
			prep(settings.html);
		} else if (isImage(href)) {
			$(photo = new Image())
			.addClass(prefix + 'Photo')
			.error(function () {
				settings.title = false;
				prep($div('Error').text('This image could not be loaded'));
			})
			.load(function () {
				var percent;
				photo.onload = null; //stops animated gifs from firing the onload repeatedly.
				
				if (settings.scalePhotos) {
					setResize = function () {
						photo.height -= photo.height * percent;
						photo.width -= photo.width * percent;	
					};
					if (settings.mw && photo.width > settings.mw) {
						percent = (photo.width - settings.mw) / photo.width;
						setResize();
					}
					if (settings.mh && photo.height > settings.mh) {
						percent = (photo.height - settings.mh) / photo.height;
						setResize();
					}
				}
				
				if (settings.h) {
					photo.style.marginTop = Math.max(settings.h - photo.height, 0) / 2 + 'px';
				}
				
				if ($related[1] && (index < $related.length - 1 || settings.loop)) {
					photo.style.cursor = 'pointer';
					photo.onclick = function () {
                        publicMethod.next();
                    };
				}
				
				if (isIE) {
					photo.style.msInterpolationMode = 'bicubic';
				}
				
				setTimeout(function () { // A pause because Chrome will sometimes report a 0 by 0 size otherwise.
					prep(photo);
				}, 1);
			});
			
			setTimeout(function () { // A pause because Opera 10.6+ will sometimes not run the onload function otherwise.
				photo.src = href;
			}, 1);
		} else if (href) {
			$loadingBay.load(href, settings.data, function (data, status, xhr) {
				prep(status === 'error' ? $div('Error').text('Request unsuccessful: ' + xhr.statusText) : $(this).contents());
			});
		}
	};
        
	// Navigates to the next page/image in a set.
	publicMethod.next = function () {
		if (!active && $related[1] && (index < $related.length - 1 || settings.loop)) {
			index = index < $related.length - 1 ? index + 1 : 0;
			publicMethod.load();
		}
	};
	
	publicMethod.prev = function () {
		if (!active && $related[1] && (index || settings.loop)) {
			index = index ? index - 1 : $related.length - 1;
			publicMethod.load();
		}
	};

	// Note: to use this within an iframe use the following format: parent.$.fn.colorbox.close();
	publicMethod.close = function () {
		if (open && !closing) {
			
			closing = true;
			
			open = false;
			
			trigger(event_cleanup, settings.onCleanup);
			
			$window.unbind('.' + prefix + ' .' + event_ie6);
			
			$overlay.fadeTo(200, 0);
			
			$box.stop().fadeTo(300, 0, function () {
                 
				$box.add($overlay).css({'opacity': 1, cursor: 'auto'}).hide();
				
				trigger(event_purge);
				
				$loaded.remove();
				
				setTimeout(function () {
					closing = false;
					trigger(event_closed, settings.onClosed);
				}, 1);
			});
		}
	};

	// A method for fetching the current element ColorBox is referencing.
	// returns a jQuery object.
	publicMethod.element = function () {
		return $(element);
	};

	publicMethod.settings = defaults;
    
	// Bind the live event before DOM-ready for maximum performance in IE6 & 7.
    handler = function (e) {
        // checks to see if it was a non-left mouse-click and for clicks modified with ctrl, shift, or alt.
        if (!((e.button !== 0 && typeof e.button !== 'undefined') || e.ctrlKey || e.shiftKey || e.altKey)) {
            e.preventDefault();
            launch(this);
        }
    };
    
    if ($.fn.delegate) {
        $(document).delegate('.' + boxElement, 'click', handler);
    } else {
        $('.' + boxElement).live('click', handler);
    }
    
	// Initializes ColorBox when the DOM has loaded
	$(publicMethod.init);

}(jQuery, document, this));


/***************************************************************************************

JSCharts v3.00 Ð Javascript charts component
Copyright © 2011 SmartketerLLC | jscharts.com | jumpeyecomponents.com

Shall not be used by any customer to create third party applications/components that may compete with Smartketer by providing the third party consumer with the possibility to have the embedded component within an editor application.
To get the source codes, special customizations licenses please contact our sales department at sales [at] jumpeyecomponents.com.

JSCharts by JumpeyeComponents, Smartketer LLC is licensed under a Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 Unported License.
Based on a work at www.jscharts.com. 

For details, see the JSCharts website: www.jscharts.com

***************************************************************************************/

eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)d[e(c)]=k[c]||e(c);k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1;};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p;}('1c me(){1a.mf=1g;1a.mg={1L:[\'1h\',\'1h\'],2T:[\'1n\',\'1h\'],2i:[\'1n\',\'1h\']};1a.mh=[\'1L\',\'2T\',\'2i\'];1a.mi={};1a.f=\'#uV\';1a.mj=\'#8S\';1a.mk=\'#8S\';1a.ml=1g;1a.mm=1g;1a.k=11;1a.mn=11;1a.m=\'X\';1a.mo=\'Y\';1a.mp=1g;1a.mq=30;1a.mr=1g;1a.ms=40;1a.mt=1g;1a.mu=30;1a.u=1g;1a.v=50;1a.w=0;1a.mv=1g;1a.mw=1g;1a.z=1g;1a.A=1g;1a.B=1g;1a.C=0;1a.D=\'#5x\';1a.E=\'#5x\';1a.F=\'2J\';1a.G=\'2J\';1a.H=5;1a.I=2;1a.J=1g;1a.K=1g;1a.L=8;1a.M=8;1a.N=1g;1a.O=1g;1a.P=1g;1a.Q=1g;1a.R=1B;1a.S=1B;1a.T=1g;1a.U=1g;1a.V=0;1a.W=0;1a.X=2;1a.Y=1g;1a.Z={};1a.ba=\'\';1a.bb=\'\';1a.bc=\'mP\';1a.bd=1g;1a.be=[\'#mS\',\'#nj\',\'#nq\',\'#nE\',\'#ny\',\'#nx\',\'#nI\',\'#nd\',\'#ng\',\'#aF\',\'#n8\',\'#nV\',\'#nU\',\'#nY\',\'#nX\',\'#nP\',\'#nT\',\'#aF\',\'#o9\',\'#nZ\',\'#o4\',\'#m9\',\'#mH\',\'#m8\',\'#m6\',\'#mY\',\'#mU\',\'#n3\',\'#mL\',\'#n2\'];1a.bf={};1a.bg=1g;1a.bh=[];1a.bi=[];1a.bj=[];1a.bk=3;1a.bl=1;1a.bm=\'#n0\';1a.bn=1g;1a.bo=-50;1a.bp=1;1a.bq=3;1a.br=\'8G\';1a.bs=1;1a.bt=0;1a.bu=0;1a.bv=\'#9l\';1a.bw=\'#9l\';1a.bx=1;1a.by=\'#8Y\';1a.bz=\'#8Y\';1a.bA=0.9;1a.bB=10;1a.bC=0.9;1a.bD=1;1a.bE=10;1a.bF=10;1a.bG=1B;1a.bH=\'#mV\';1a.bI=\'2J\';1a.bJ=1g;1a.bK=8;1a.bL=1g;1a.bM=1g;1a.bN=1g;1a.bO=1g;1a.bP=[1a.bQ];1a.bQ=\'#8Y\';1a.bR=0.9;1a.bS=2;1a.bT=[1a.bR];1a.bU=90;1a.bV=1g;1a.bW=[1a.bS];1a.bX=45;1a.bY=15;1a.bZ=1;1a.ca=0;1a.cb=0;1a.cc=0;1a.cd=\'#5x\';1a.ce=1g;1a.cf=8;1a.cg=10;1a.mx=\'#8s\';1a.ci=\'2J\';1a.cj=1g;1a.ck=8;1a.cl=-20;1a.cm=1g;1a.cn=1g;1a.co=1B;1a.cp=\'#9E\';1a.cq=\'#9E\';1a.cr=0.5;1a.cs=0.5;1a.ct=[];1a.cu=\'\';1a.cv=1g;1a.cw=1g;1a.cx=1g;1a.cy=1g;1a.cz=1g;1a.cA=1g;1a.cB=1g;1a.cC=1g;1a.cD=1g;1a.cE=\'#5x\';1a.cF=\'#5x\';1a.cG=1g;1a.cH=1g;1a.cI=8;1a.cJ=8;1a.cK=1g;1a.cL=1g;1a.cM=[];1a.cN=[];1a.cO=\'#8S\';1a.cP=1B;1a.cQ=1g;1a.cR=8;1a.cS=1g;1a.cT=\'2w 5n\';1a.cU=[];1a.cV=[];1a.cW=1g;1a.cX=[];1a.cY=0;1a.cZ=0;1a.da=0;1a.db=0;1a.dc=0;1a.dd=0;1a.de=0;1a.df=0;1a.dg=1g;1a.dh=\'\';1a.di=0;1a.dj=0;1a.dk=0;1a.dl=0;1a.dm=1B;1a.dn=0;1a.my=0;1a.dp=0;1a.dq=0;1a.dr=4l;1a.ds=qG;1a.dt=1T;1a.du=\'#7u\';1a.dv=\'8z\';1a.dw=12;1a.dx=1T;1a.dy=1T;1a.dz=\'\';1a.dA=1;1a.dB=0;1a.dC=1g;1a.dD=1;1a.dE=1g;1a.dF=8;1a.dG=15;1a.dH=1B;1a.dI=\'qB 5B\';1a.dJ=\'#qw\';1a.dK=1g;1a.dL=11;1a.dM=\'5n\';1a.dN=\'#qz\';1a.dO=\'5A qQ #qS\';1a.dP=[];1a.dQ=\'#qO\';1a.dR=\'8z\';1a.dS=12;1a.dT=7;1a.dU=0.7;1a.dV=\'qJ qg\';1a.dW=\'se\';1a.dX=[\'nw\',\'sw\',\'se\',\'ne\'];1a.dY={};1a.dZ={};1a.ea=[];1a.eb=\'1L\';1a.ec=1;1a.ed=1;1a.ee=\'\';1a.ef=\'#qd\';1a.eg=1g;1a.eh=9;1a.ei=0.8;1a.ej=\'ne\';1a.ek=\'#8s\';1a.el=1g;1a.em=1B;1a.en=\'#qc\';1a.eo=0;1a.ep=1g;1a.eq=8;1a.er=\'\';1a.es=19;1a.et=77;1a.eu=0.8;1a.ev=\'\';1a.ew=0.5;1a.ex=\'\';1a.ey=\'#8s\';1a.ez=1c(38){if(1a.dm){34(1a.eA[38])}};1a.eB=1c(eC,eD,eE){if(eC.5L){eC.5L(\'on\'+eD,eE);1d 1B}1i if(eC.aw){eC.aw(eD,eE,1g);1d 1B}1d 1g};1a.eF=1c(){1a.ev+=\'qv\';1a.eG+=\'qu\';1a.ex+=\'ql\';5f{1a.Z.3k(\'2d\')}5l(38){1d 1g}1d 1B};1a.eH=1c(eI,eJ,1m){if(1f 1m===\'1r\'){1m=1g}if((eI<1a.bt||eI>1a.bt+1a.dp||eJ<1a.bu||eJ>1a.bu+1a.dq)&&1m===1B){1d 1g}if((eI<1a.de||eI>1a.da||eJ<1a.df||eJ>1a.db)&&1m===1g){1d 1g}1d 1B};1a.eK=1c(2A){if(1f 2A!==\'1n\'){1d 1g}if(!1W.3r(2A)){1d 1g}1d 1B};1a.eL=1c(2e){1b eM=1a.mh.1k;1q(1b eN=0;eN<eM;eN++){if(1a.mh[eN]===2e){1d 1B}}1d 1g};1a.eO=1c(eM,eP){if(eP){1b eQ=1a.db;1b eR=1a.df;1b eS=1a.G;1b eT=1a.Q;1b eU=1a.U;1b eV=1a.K;1b eW=1a.M;1b eX=1a.I}1i{1b eQ=1a.da;1b eR=1a.de;1b eS=1a.F;1b eT=1a.P;1b eU=1a.T;1b eV=1a.J;1b eW=1a.L;1b eX=1a.H}1b eY=(eV===1g)?1a.dv:eV;1b eZ=0;1b fa=1a.dp/eM;1b fb=1a.fc((eQ-eR)/eM,eS);1b fd=eR;1b fe=1a.bt;1b ff;2s(fe<1a.dp+20){ff=1K(1a.fc(fd,1a.eS));if(1f eT===\'1n\'){ff=eT+ff}if(1f eU===\'1n\'){ff=ff+eU}1b fg=1a.fh(ff,eW,1l,1l,eY);if(fd===eR){fg=fg/2}fd+=fb;fe+=fa;eZ+=fg}ff=1K(eQ);if(1f eT===\'1n\'){ff=eT+ff}if(1f eU===\'1n\'){ff=ff+eU}eZ+=1a.fh(ff,eW,1l,1l,eY)/2;if(1a.dp-eZ-eM*eX>0){1d 1B}1d 1g};1a.fi=1c(eM,fj){1b eZ=0;1b fa=1a.dq/eM;1b fb=(1a.db-1a.df)/eM;1b fd=1a.df;1b fk=1a.bu+1a.dq;2s(fk>1a.bu){1b fl=1a.fm(1a.M);if(fd===1a.df){fl=fl/2}fd+=fb;fk-=fa;eZ+=fl}eZ+=1a.fm(1a.M)/2;1b fn=1a.dq-eZ-eM*1a.I;if(1f fj===\'1r\'){if(fn>0){1d 1B}1d 1g}1i{if(fj&&fn>1a.fm(1a.M)*(eM-1)*2){1d 1g}1d 1B}};1a.fo=1c(fp){1b fq=(fp 74 41)?[]:{};1q(1b eN in fp){if(fp[eN]&&1f fp[eN]==="4h"){fq[eN]=1a.fo(fp[eN])}1i{fq[eN]=fp[eN]}}1d fq};1a.fr=1c(){1a.ev+=\'rr\';1a.eG+=\'rw\';1a.ex+=\'rk\';if(1a.fs()){1d 1g}1b 1z=1W.2n(\'8M\');1z.2I(\'id\',1a.bc+1a.dh);1z.2I(\'1o\',1a.dr);1z.2I(\'1C\',1a.ds);1z.1v.1G=\'rH\';1z.1v.aO=1a.ba;1a.bf.2C(1z);1a.Z=1z;1a.bb=1a.bc+1a.dh};1a.ft=1c(){1b 2N=1W.2n(\'rA\');2N.2I(\'2A\',\'8k\'+1a.bb);2N.2I(\'id\',\'8k\'+1a.bb);1a.bf.2C(2N);1d 2N};1a.fu=1c(2N,1m){1b fv=1W.2n(\'r2\');fv.2I(\'3F\',\'r3\');fv.2I(\'1m\',1m);2N.2C(fv);1d fv};1a.fw=1c(2N){1b fx=\'<42 3v="1D:2k/8h;8g,8n///8u==" \'+\'1o="\'+1a.dr+\'" \'+\'1C="\'+1a.ds+\'" \'+\'r4="#8k\'+1a.bb+\'" \'+\'1v="4n:0;1G:2W;2a:\'+1a.Z.3L+\'px;2w:\'+1a.Z.3K+\'px;\'+\'8Q:\\\'8m(0, \'+1a.dr+\'px, \'+1a.ds+\'px, 0)\\\';\'+\'53:3j(2l=0);\'+\'z-1V:\'+(1a.Z.1v.3f+10)+\';">\';2N.3m+=fx;1d fx};1a.fy=1c(eI,eJ,fz,fA,fB){1b fC=1W.2n(\'8t\');fC.1v.1G=\'2W\';fC.1v.2a=(1a.Z.3L+fz)+\'px\';fC.1v.2w=(1a.Z.3K+fA)+\'px\';fC.1v.4n=1a.dO;fC.1v.6f=1a.dV;fC.1v.aO=1a.dN;fC.1v.2z=1a.dS+\'px\';fC.1v.9Q=1a.dR;fC.1v.1j=1a.dQ;fC.1v.fD=1a.dU;fC.1v.53=\'3j(2l=\'+(1a.dU*1T)+\')\';fC.1v.4S=\'5R\';fC.1v.3f=1E(1a.Z.1v.3f)+1T;fC.2I(\'id\',\'96\'+1a.bb+\'4D\'+eI+\'4D\'+eJ);if(fB===1l||fB===\'\'||fB===\' \'){fC.3m=1a.m+\': \'+eI+\'<br>\'+1a.mo+\': \'+eJ}1i{fC.3m=fB}fC.1G=1a.dW;fC.fE=1a.dT;fC.fz=1a.Z.3L+fz;fC.fA=1a.Z.3K+fA;1d fC};1a.fF=1c(eI,eJ,fz,fA,fB,fG){if(1a.Z.1v.3f===\'\'){1a.Z.1v.3f=1}if(1B){1b fC=1a.fy(eI,eJ,fz,fA,fB);1b 2g=5;1b 1o=1a.bq*2+1a.bs*2+2g*2+5;1b 1C=1a.bq*2+1a.bs*2+2g*2+5;1b fH=1g;1b 1z=1W.2n(\'8M\');1z.2I(\'id\',fC.2P(\'id\')+\'8L\');1z.1v.1G=\'2W\';1z.1v.8O=\'6W\';1z.1v.2a=(1a.Z.3L+fz-1o/2)+\'px\';1z.1v.2w=(1a.Z.3K+fA-1C/2)+\'px\';1z.1v.1o=1o+\'px\';1z.1o=1o;1z.1v.1C=1C+\'px\';1z.1C=1C;1z.1v.3f=1E(1a.Z.1v.3f)+90;1a.bf.2C(1z);if(!1a.fI(1z)&&1a.fJ()){1z=65.5M(1z);fH=1B}1b fK=1z.3k(\'2d\');fK.2o();fK.1R=1a.bs;fK.1O=1a.fL(1a.bm,(1a.bs===0)?0:1a.bp);2r(1a.br){1A\'6Q\':fK.1H(1z.1o/2-1a.bq-2g,1z.1C/2+1a.bq+2g);fK.1u(1z.1o/2-1a.bq-2g,1z.1C/2-1a.bq-2g);fK.1u(1z.1o/2+1a.bq+2g,1z.1C/2-1a.bq-2g);fK.1u(1z.1o/2+1a.bq+2g,1z.1C/2+1a.bq+2g);fK.31();1F;1A\'9d\':fK.1H(1o/2-(1a.bq+2g)*1p.2H(1p.1X/6),1C/2+(1a.bq+2g)*1p.2H(1p.1X/3));fK.1u(1o/2,1C/2-1a.bq-2g);fK.1u(1o/2+(1a.bq+2g)*1p.2H(1p.1X/6),1C/2+(1a.bq+2g)*1p.2H(1p.1X/3));fK.31();1F;1A\'9e\':fK.1H(1z.1o/2-1a.bq-2g,1z.1C/2);fK.1u(1z.1o/2,1z.1C/2-1a.bq-2g);fK.1u(1z.1o/2+1a.bq+2g,1z.1C/2);fK.1u(1z.1o/2,1z.1C/2+1a.bq+2g);fK.31();1F;1A\'8G\':2u:fK.4q(1o/2,1C/2,1a.bq+2g,0,1p.1X*2,1g)}if(1a.bn!==1g){fK.1N=1a.fL(1a.bn,1a.bp);fK.2S()}fK.2c();if(fH){1a.fM(1z)}1b fN=1c(){1b eC=1W.3r(fC.2P(\'id\')+\'8L\');eC.1v.8O=\'\';1b eC=1W.3r(fC.2P(\'id\')+\'8N\');eC.1v.4S=\'5R\';if(fB!==1g&&fB!==\'1g\'){eC=1W.3r(fC.2P(\'id\'));eC.1v.4S=\'\';2r(eC.1G){1A\'nw\':eC.1v.2a=(1U(eC.fz,10)-eC.fE-eC.9o)+\'px\';eC.1v.2w=(1U(eC.fA,10)-eC.fE-eC.9f)+\'px\';1F;1A\'ne\':eC.1v.2a=(1U(eC.fz,10)+eC.fE)+\'px\';eC.1v.2w=(1U(eC.fA,10)-eC.fE-eC.9f)+\'px\';1F;1A\'sw\':eC.1v.2a=(1U(eC.fz,10)-eC.fE-eC.9o)+\'px\';eC.1v.2w=(1U(eC.fA,10)+eC.fE)+\'px\';1F;2u:eC.1v.2a=(1U(eC.fz,10)+eC.fE)+\'px\';eC.1v.2w=(1U(eC.fA,10)+eC.fE)+\'px\'}}};1b fO=1c(){1W.3r(fC.2P(\'id\')).1v.4S=\'5R\';1W.3r(fC.2P(\'id\')+\'8L\').1v.8O=\'6W\';1W.3r(fC.2P(\'id\')+\'8N\').1v.4S=\'\'};1a.bf.2C(fC);1b fP=1a.fQ(eI,eJ,fz,fA,fG);1a.eB(fP,\'re\',fN);1a.eB(fP,\'rg\',fO);1a.bf.2C(fP)}};1a.fQ=1c(eI,eJ,fz,fA,fG){1b fP=\'<47 \'+\'1v="1G:2W;\'+\'2a:\'+(1a.Z.3L+fz-1a.bq)+\'px;\'+\'2w:\'+(1a.Z.3K+fA-1a.bq)+\'px;\'+\'1o:\'+(1a.bq*2)+\'px;\'+\'1C:\'+(1a.bq*2)+\'px;\'+\'1Y-1t: 5A;\'+\'z-1V: \'+(1E(1a.Z.1v.3f)+4L)+\';\'+\'" id="ri\'+eI+\'4D\'+eJ+\'">\'+\'<42 3v="1D:2k/8h;8g,8n///8u==" \'+\'1o="\'+(1a.bq*2)+\'" \'+\'1C="\'+(1a.bq*2)+\'" \'+"><\\/47>";1b fR=1W.2n(\'8t\');fR.3m+=fP;1b fP=fR.3x;1b fS=fP.c5(1B);fP.c1.8w(fP);if(fG){1a.eB(fS,\'6t\',fG)}1d fS};1a.fT=1c(){if(!1a.fs()&&1a.bb){1d 1g}1b fU=1W.3r(1a.dh);if(fU.rd()){2s(fU.3M.1k>=1){fU.8w(fU.3x)}}};1a.fM=1c(1z){1b fV;if(1f 1z===\'1r\'){fV=1a.Z.3x.1v}1i{fV=1z.3x.1v}if(fV.1o==="8B"){1b fW=1a.Z.1v;fV.1o=fW.1o;fV.1C=fW.1C}};1a.fX=1c(){1b fH=1g;1a.ev=\'r7\';1a.eG=\'r9\';1a.ex=\'ra\';if(1a.fs()){1a.fT()}1a.fr();if(!1a.fI(1a.Z)&&1a.fJ()){1a.Z=65.5M(1a.Z);fH=1B}if(!1a.eF()){1a.ez(\'ay\')}1a.bg=1a.Z.3k(\'2d\');if(!1a.fJ()){1a.dH=1a.fY()}if(1a.cW&&!1a.fZ(1a.cT)){1a.ga()}if(1a.u===1g){1a.u=1a.v}if(1a.mp===1g){1a.mp=1a.mq}if(1a.mr===1g){1a.mr=1a.ms}if(1a.mt===1g){1a.mt=1a.mu}if(1a.dC===1g){1a.dC=1a.dD}if(1a.dE===1g){1a.dE=1a.dF}1a.gb();1a.gc();1a.gd();1a.cu=3A(ge.gf(1a.gg));if(/^q8\\./i.3a(1a.cu)){1a.cu=1a.cu.2G(4)}if(1a.Y!==1g){1a.gh()}if(1a.eb===\'1L\'){1a.gi();if(1a.V===0){1a.gj()}if(1a.W===0){1a.gk()}1a.gl();if(1a.co){1a.gm();1a.gn()}if(1a.R){1a.mz()}if(1a.S){1a.gp()}1a.gq();1a.gr();1a.gs()}if(1a.eb===\'2i\'){1a.gi();if(1a.W===0){1a.gk()}if(1a.co){1a.gm()}1a.gt();if(1a.S){1a.gp(1a.mv)}1a.gq();1a.gs()}if(1a.eb===\'2T\'){1a.gu()}1a.gv();if(fH){1a.fM()}if(1a.cW&&1a.fZ(1a.cT)){1a.ga()}1b 6V=1a.gw(1a.cu);1b gx;1b eM;1b gy;1b eN;if(1a.uC===\'\'){gx=ge.gf(\'4I\'+1a.ev);gy=1a.gz(\'\',1g,1B);2L((1c(fp){1d 1c(){1b fR=1W.2n(\'47\');1b fx=gx+\'1v="4n:0;1G:2W;2a:\'+(fp.Z.3L+gy[0])+\'px;2w:\'+(fp.Z.3K+gy[1])+\'px;\'+\'z-1V:\'+(fp.Z.1v.3f+3J)+\';"/>\';fR.3m=fx;fp.bf.2C(fR)}}(1a)),1a.eo);1d}gx=1a.gA.gB(6V+\'-\'+1a.ex);1b rs=[gx];1b ix=0;6V=1a.uC;if(1a.uC.43(\',\')==\'-1\'){if(3A(ge.gf(\'4I\'+1a.eG))){2L(1a.gC(),1a.eo);1d}}1i{rs=1a.uC.5s(\',\');6V=gx;1q(ix=0;ix<rs.1k;ix++){if(3A(ge.gf(\'4I\'+1a.eG))){2L(1a.gC(),1a.eo);1d}}}gx=ge.gf(\'4I\'+1a.ev);gy=1a.gz(\'\',1g,1B);2L((1c(fp){1d 1c(){1b fR=1W.2n(\'47\');1b fx=gx+\'1v="4n:0;1G:2W;2a:\'+(fp.Z.3L+gy[0])+\'px;2w:\'+(fp.Z.3K+gy[1])+\'px;\'+\'z-1V:\'+(fp.Z.1v.3f+3J)+\';"/>\';fR.3m=fx;fp.bf.2C(fR)}}(1a)),1a.eo);1d};1a.gi=1c(){1b gD=1a.bN?1a.dp/15:0;1b gE=1a.bO?1a.dq/15:0;1a.bg.2o();1a.bg.1R=1a.X;1a.bg.1O=1a.f;1a.bg.1H(1a.mr,1a.u-gE);1a.bg.1u(1a.mr,1a.ds-1a.mp);1a.bg.1u(1a.dr-1a.mt+gD,1a.ds-1a.mp);1a.bg.2c();if(1a.mf&&1a.co&&1a.eb!==\'1L\'){1a.bg.1R=1;1a.bg.1O=1a.f;1a.bg.1H(1a.mr+1a.bB,1a.u-gE-1a.bB);1a.bg.1u(1a.mr+1a.bB,1a.ds-1a.mp-1a.bB);1a.bg.1u(1a.dr-1a.mt+gD+1a.bB,1a.ds-1a.mp-1a.bB);1a.bg.1H(1a.mr+1a.bB,1a.ds-1a.mp-1a.bB);1a.bg.1u(1a.mr,1a.ds-1a.mp)}1a.bg.2c()};1a.gh=1c(){1a.Z.1v.g4=\'h4(\'+1a.Y+\')\'};1a.gt=1c(){1b eM=1a.bh.1k;1b gF;1b gG;1b fg;1b fl;1b 1o=1a.gH();1b fe=1a.bD;1b gI;1b gJ;1b gK;1b gL;1b fk;1b gM;1b gN;1b gO;1b gP;1b 1m;1b fB;1b gQ;1b gR;1b eI;1b eJ;1b gS;1b gT;1b ff;1b 1N;1b 1O;1b fC;1b gU;1b gV;1b gW;1b gX;1b gY;1b eY;1b fa=(1a.bF!==1);1b gZ=[];1b ha;1b hb;1b hc;1b hd=0;1b he=0;1b hf=0;1b hg=-1a.C*1p.1X/4j;1b 2N;1b hh;1b fv;1a.dZ=1a.fo(1a.dY);if(1a.mv){1o/=1a.w;fk=1a.dq-1a.bD/1a.w}2N=1a.ft();1a.fw(2N);1q(1b eN=(1a.mv)?eM-1:0;(1a.mv&&eN>=0)||(!1a.mv&&eN<eM);eN=(1a.mv)?eN-1:eN+1){gG=1a.bh[eN].1k-1;gX=1g;gY=1g;if(1a.mv&&eN===eM-1){fk-=1o/gG}1q(gR=0;gR<gG;gR++){gV=(1f 1a.bv===\'1n\'||1f 1a.bv[gR]===\'1r\')?1a.bw:1a.bv[gR];gU=(1f 1a.by===\'1n\'||1f 1a.by[gR]===\'1r\')?1a.bz:1a.by[gR];gW=(1f 1a.bC===\'1n\'||1f 1a.bC[gR]===\'1r\')?1a.bA:1a.bC[gR];1m=1a.hi(1l,1a.bh[eN][gR+1]);if(1a.mv){1m=[1m[0],(1a.dq+1a.bu-1m[1])*1a.w+1a.bt]}1N=1a.fL((1a.bd!==1g)?1a.bd[eN]:gU,gW);if(1m[1]>1a.bt+1a.dp&&1a.mv){1m[1]=1a.bt+1a.dp+1;fe=1m[1]}if(1m[1]<1a.bu&&!1a.mv){1m[1]=1a.bu-1;fk=1m[1]}if(!1a.ct[(1a.mv)?1p.2h(gR-gG)-1:gR]){1a.bg.2o();1a.bg.1N=1N;if(fa===1g){if(1a.mv){1a.bg.5H(1a.bt,1a.bu+fk,1m[1]-1a.bt,1o/gG);if(1a.mf){1a.bg.1N=1N;1a.bg.1H(1a.bt,1a.bu+fk);1a.bg.1u(1m[1],1a.bu+fk);1a.bg.1u(1m[1],1a.bu+fk+1o/gG);1a.bg.1u(1m[1]+1a.bB,1a.bu+fk+1o/gG-1a.bB);1a.bg.1u(1m[1]+1a.bB,1a.bu+fk-1a.bB);1a.bg.1u(1a.bt+1a.bB,1a.bu+fk-1a.bB);1a.bg.2S()}}1i{1a.bg.5H(1a.bt+fe,1m[1],1o/gG,1a.bu+1a.dq-1m[1]);if(1a.mf){1a.bg.1N=1N;1a.bg.1H(1a.bt+fe,1m[1]);1a.bg.1u(1a.bt+fe+1a.bB,1m[1]-1a.bB);1a.bg.1u(1a.bt+fe+1o/gG+1a.bB,1m[1]-1a.bB);1a.bg.1u(1a.bt+fe+1o/gG+1a.bB,1a.bu+1a.dq-1a.bB);1a.bg.1u(1a.bt+fe+1o/gG,1a.bu+1a.dq);1a.bg.1u(1a.bt+fe+1o/gG,1m[1]);1a.bg.2S()}}}1i{fa=1p.2p((1a.bu+1a.dq)/1a.bF);if(fa<3){fa=3}if(1a.mv){1q(ha=1a.bt;ha<1m[1];ha+=fa,hd++){hc=[];hb=fa;if(ha+fa>1m[1]){hb=1m[1]-ha}hc.1s({1H:[ha,1a.bu+fk]});hc.1s({1u:[ha+hb,1a.bu+fk]});hc.1s({1u:[ha+hb,1a.bu+fk+1o/gG]});hc.1s({1u:[ha,1a.bu+fk+1o/gG]});if(1a.mf){hc.1s({1H:[ha,1a.bu+fk]});hc.1s({1u:[ha+1a.bB,1a.bu+fk-1a.bB]});hc.1s({1u:[ha+hb+gW*0.5+1a.bB,1a.bu+fk-1a.bB]});hc.1s({1u:[ha+hb+gW*0.5,1a.bu+fk]})}if(ha+fa>=1m[1]&&1a.mf){hc.1s({1H:[ha+hb,1a.bu+fk]});hc.1s({1u:[ha+hb+1a.bB,1a.bu+fk-1a.bB]});hc.1s({1u:[ha+hb+1a.bB,1a.bu+fk+1o/gG-1a.bB]});hc.1s({1u:[ha+hb,1a.bu+fk+1o/gG]})}hc.1s({2S:[]});gZ.1s({hc:hc,hd:hd})}}1i{1q(ha=1a.bu+1a.dq;ha>1m[1];ha-=fa,hd++){hc=[];hb=fa;if(ha-fa<1m[1]){hb=ha-1m[1]}hc.1s({1H:[1a.bt+fe,ha-hb]});hc.1s({1u:[1a.bt+fe,ha]});hc.1s({1u:[1a.bt+fe+1o/gG,ha]});hc.1s({1u:[1a.bt+fe+1o/gG,ha-hb]});if(1a.mf){hc.1s({1H:[1a.bt+fe+1o/gG,ha-hb-gW*0.6]});hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-hb-gW*0.6-1a.bB]});hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-1a.bB]});hc.1s({1u:[1a.bt+fe+1o/gG,ha]})}if(ha-fa<1m[1]&&1a.mf){hc.1s({1H:[1a.bt+fe,ha-hb]});hc.1s({1u:[1a.bt+fe+1a.bB,ha-hb-1a.bB]});hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-hb-1a.bB]});hc.1s({1u:[1a.bt+fe+1o/gG,ha-hb]})}hc.1s({2S:[]});gZ.1s({hc:hc,hd:hd})}}1q(ha=0,gF=gZ.1k;ha<gF;ha++){hf=gZ[ha].hd*10+4L;2L((1c(fK,gZ,1N){1d 1c(){fK.2o();fK.1N=1N;1b hj,eI;1q(1b eN=0,eM=gZ.1k;eN<eM;eN++){1q(eI in gZ[eN]){hj=gZ[eN][eI].3n(\',\');3A(\'fK.\'+eI+\'(\'+hj+\');\')}}fK.31()}})(1a.bg,gZ[ha].hc,1N),hf)}gZ=[]}1a.bg.1O=1a.fL(gV,gW);if(1a.bx>0){1a.bg.1R=1a.bx;if(fa===1g){if(1a.mv){1a.bg.1H(1a.bt,1a.bu+fk);1a.bg.1u(1m[1],1a.bu+fk);if(1m[1]>1a.bt+1a.dp){1a.bg.1H(1m[1],1a.bu+fk+1o/gG)}1i{1a.bg.1u(1m[1],1a.bu+fk+1o/gG)}1a.bg.1u(1a.bt,1a.bu+fk+1o/gG)}1i{1a.bg.1H(1a.bt+fe,1a.bu+1a.dq);1a.bg.1u(1a.bt+fe,1m[1]);if(1m[1]<1a.bu){1a.bg.1H(1a.bt+fe+1o/gG,1m[1])}1i{1a.bg.1u(1a.bt+fe+1o/gG,1m[1])}1a.bg.1u(1a.bt+fe+1o/gG,1a.bu+1a.dq)}}1i{if(1a.mv){1q(ha=1a.bt;ha<1m[1];ha+=fa,he++){hb=fa;hc=[];if(ha+fa>1m[1]){hb=1m[1]-ha}hc.1s({1H:[ha,1a.bu+fk]});hc.1s({1u:[ha+hb,1a.bu+fk]});hc.1s({1H:[ha,1a.bu+fk+1o/gG]});hc.1s({1u:[ha+hb,1a.bu+fk+1o/gG]});if(1a.mf){if(ha===1a.bt){hc.1s({1H:[ha,1a.bu+fk]});hc.1s({1u:[ha+1a.bB,1a.bu+fk-1a.bB]})}1i{hc.1s({1H:[ha+1a.bB,1a.bu+fk-1a.bB]})}hc.1s({1u:[ha+hb+1a.bB,1a.bu+fk-1a.bB]})}if(ha+hb===1m[1]&&ha+hb<=1a.bt+1a.dp){hc.1s({1H:[ha+hb,1a.bu+fk]});hc.1s({1u:[ha+hb,1a.bu+fk+1o/gG]});if(1a.mf){hc.1s({1u:[ha+hb+1a.bB,1a.bu+fk+1o/gG-1a.bB]});hc.1s({1u:[ha+hb+1a.bB,1a.bu+fk-1a.bB]});hc.1s({1u:[ha+hb,1a.bu+fk]})}}gZ.1s({hc:hc,hd:he})}}1i{1q(ha=1a.bu+1a.dq;ha>1m[1];ha-=fa,he++){hb=fa;hc=[];if(ha-fa<1m[1]){hb=ha-1m[1]}hc.1s({1H:[1a.bt+fe,ha]});hc.1s({1u:[1a.bt+fe,ha-hb]});hc.1s({1H:[1a.bt+fe+1o/gG,ha]});hc.1s({1u:[1a.bt+fe+1o/gG,ha-hb]});if(1a.mf){if(ha===1a.bu+1a.dq){hc.1s({1H:[1a.bt+fe+1o/gG,ha]});hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-1a.bB]})}1i{hc.1s({1H:[1a.bt+fe+1o/gG+1a.bB,ha-1a.bB]})}hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-hb-1a.bB]})}if(ha-hb===1m[1]&&ha-hb>=1a.bu){hc.1s({1H:[1a.bt+fe,ha-hb]});hc.1s({1u:[1a.bt+fe+1o/gG,ha-hb]});if(1a.mf){hc.1s({1u:[1a.bt+fe+1o/gG+1a.bB,ha-hb-1a.bB]});hc.1s({1u:[1a.bt+fe+1a.bB,ha-hb-1a.bB]});hc.1s({1u:[1a.bt+fe,ha-hb]})}}gZ.1s({hc:hc,hd:he})}}1q(ha=0,gF=gZ.1k;ha<gF;ha++){hf=gZ[ha].hd*10+4L;2L((1c(fK,gZ,1O,1R){1d 1c(){fK.2o();fK.1O=1O;fK.1R=1R;1b hj,eI;1q(1b eN=0,eM=gZ.1k;eN<eM;eN++){1q(eI in gZ[eN]){hj=gZ[eN][eI].3n(\',\');3A(\'fK.\'+eI+\'(\'+hj+\');\')}}fK.2c()}})(1a.bg,gZ[ha].hc,1a.bg.1O,1a.bg.1R),hf)}gZ=[]}1a.bg.2c();1a.bg.31();if(1m[1]>1a.bt+1a.dp&&1a.mv){1m[1]=1a.bt+1a.dp+1;fe=1m[1];1q(gQ=gW;gQ>0;gQ-=0.1){1O=1a.fL((1a.bd!==1g)?1a.bd[eN]:gU,(gQ>gW/2)?gQ:gQ/2);2L((1c(fK,1O,mf,hk,fe,gM,gN){1d 1c(){fK.2o();fK.1R=1;fK.1O=1O;if(mf){fK.1H(fe+hk,gM-hk);fK.1u(fe+hk,gN-hk);fK.1u(fe,gN)}1i{fK.1H(fe,gM);fK.1u(fe,gN)}fK.2c()}})(1a.bg,1O,1a.mf,1a.bB,fe,1a.bu+fk,1a.bu+fk+1o/gG),hf);fe++}}if(1m[1]<1a.bu&&!1a.mv){1m[1]=1a.bu-1;fk=1m[1];1q(gQ=gW;gQ>0;gQ-=0.1){1O=1a.fL((1a.bd!==1g)?1a.bd[eN]:gU,(gQ>gW/2)?gQ:gQ/2);2L((1c(fK,1O,mf,hk,gI,gJ,fk){1d 1c(){fK.2o();fK.1R=1;fK.1O=1O;if(mf){fK.1H(gI,fk);fK.1u(gI+hk,fk-hk);fK.1u(gJ+hk,fk-hk)}1i{fK.1H(gI,fk);fK.1u(gJ,fk)}fK.2c()}})(1a.bg,1O,1a.mf,1a.bB,1a.bt+fe,1a.bt+fe+1o/gG,fk),hf);fk--}}if(1m[1]>1a.bt+1a.dp&&1a.mv){fe=1m[1]+0.5;1q(gQ=gW;gQ>0;gQ-=0.1){1O=1a.fL(gV,(gQ>gW/2)?gQ:gQ/2);2L((1c(fK,1O,mf,hk,fe,gM,gN,gO,gP){1d 1c(){fK.2o();fK.1R=1;fK.1O=1O;fK.1H(fe,gM);fK.1u(fe,gN);fK.1H(fe,gO);fK.1u(fe,gP);if(mf){fK.1H(fe+hk,gM-hk);fK.1u(fe+hk,gN-hk)}fK.2c()}})(1a.bg,1O,1a.mf,1a.bB,fe,1a.bu+fk-1a.bx/2,1a.bu+fk+1a.bx/2,1a.bu+fk-1a.bx/2+1o/gG,1a.bu+fk+1a.bx/2+1o/gG),hf);fe++}}if(1m[1]<1a.bu&&!1a.mv){fk=1m[1]-0.5;1q(gQ=gW;gQ>0;gQ-=0.1){1O=1a.fL(gV,(gQ>gW/2)?gQ:gQ/2);2L((1c(fK,1O,mf,hk,gI,gJ,gK,gL,fk){1d 1c(){fK.2o();fK.1R=1;fK.1O=1O;fK.1H(gI,fk);fK.1u(gJ,fk);fK.1H(gK,fk);fK.1u(gL,fk);if(mf){fK.1H(gK+hk,fk-hk);fK.1u(gL+hk,fk-hk)}fK.2c()}})(1a.bg,1O,1a.mf,1a.bB,1a.bt+fe-1a.bx/2,1a.bt+fe+1a.bx/2,1a.bt+fe+1o/gG-1a.bx/2,1a.bt+fe+1o/gG+1a.bx/2,fk),hf);fk--}}}if(1f 1a.mi[1a.bh[eN][0]]!==\'1r\'){if(1a.mv){hh=[1a.bt,1a.bu+fk,1m[1],1a.bu+fk,1m[1],1a.bu+fk+1o/gG,1a.bt,1a.bu+fk+1o/gG]}1i{hh=[1a.bt+fe,1m[1],1a.bt+fe+1o/gG,1m[1],1a.bt+fe+1o/gG,1a.bu+1a.dq,1a.bt+fe,1a.bu+1a.dq]}fv=1a.fu(2N,hh);1a.eB(fv,\'6t\',1a.mi[1a.bh[eN][0]])}gQ=1a.bh[eN][0];fC=1a.dY[gR+1];if(1f fC!==\'1r\'&&1f fC[gQ]!==\'1r\'){fB=(1f fC[gQ][1]===\'1r\')?1l:fC[gQ][1];ff=1a.bh[eN][gR+1];if(1f 1a.bL===\'1n\'){ff=1a.bL+ff}if(1f 1a.bM===\'1n\'){ff=ff+1a.bM}if(1a.mv){eI=1m[1];eJ=1a.bu+fk+1o/gG/2}1i{eI=1a.bt+fe+1o/gG/2;eJ=1m[1]}1a.fF(1a.bh[eN][0],ff,eI,eJ,fB,(1f fC[gQ][\'3q\']===\'1r\')?1g:fC[gQ][\'3q\']);1a.ea.1s(2L(1a.hl(1a,[eI,eJ],1a.bh[eN][0],ff),hf))}fC=1a.dY[\'46\'];if(1f fC!==\'1r\'&&1f fC[gQ]!==\'1r\'){fB=(1f fC[gQ][1]===\'1r\')?1l:fC[gQ][1];ff=1a.bh[eN][gR+1];if(1f 1a.bL===\'1n\'){ff=1a.bL+ff}if(1f 1a.bM===\'1n\'){ff=ff+1a.bM}if(1a.mv){eI=1m[1];eJ=1a.bu+fk+1o/gG/2}1i{eI=1a.bt+fe+1o/gG/2;eJ=1m[1]}1a.fF(1a.bh[eN][0],ff,eI,eJ,fB,(1f fC[gQ][\'3q\']===\'1r\')?1g:fC[gQ][\'3q\']);1a.ea.1s(2L(1a.hl(1a,[eI,eJ],1a.bh[eN][0],ff),hf))}}eY=(1a.J===1g)?1a.dv:1a.J;fg=1a.fh(1a.bh[eN][0],1a.L,1l,1l,eY);if(1a.bh[eN][gR+1]<1a.df){if(1a.mv){1m[1]=1a.mr-4-fg}1i{1m[1]=1a.bu+1a.dq}}if(1m[1]<1a.bu-1a.hm){1m[1]=1a.bu-1a.hm-5}if(1a.R&&!gX){gX=1B;if(1a.mv){fl=1a.fm(1a.L);1a.hn(1a.bh[eN][0],1a.mr-4-fg,1a.bu+fk+1o/gG-1o/2-fl/2,1a.L,1l,1l,1l,eY,1a.D)}1i{if(1a.C>0){1a.bg.3R();1a.bg.4c(1a.bt+fe+1o/2,1a.ds-((1a.N===1g)?1a.mp-4:1a.N)+1a.L/2+2);1a.bg.4f(hg);1a.hn(1a.bh[eN][0],0-fg,0-1a.L/2-2,1a.L,1l,1l,1l,eY,1a.D);1a.bg.3T()}1i{1a.hn(1a.bh[eN][0],1a.bt+fe+1o/2-fg/2,1a.ds-((1a.N===1g)?1a.mp-4:1a.N),1a.L,1l,1l,1l,eY,1a.D)}}}if(1a.bG&&!1a.ct[(1a.mv)?1p.2h(gR-gG)-1:gR]){ff=(1a.bI===\'2J\')?1a.bh[eN][gR+1]:1a.bh[eN][gR+1].2V(1a.bI);gS=1K(1a.bh[eN][gR+1]).3Q(\'.\');gT=1K(1a.bh[eN][gR+1]).2G(gS+1).1k;if(gT>3&&1a.bI===\'2J\'){ff=1a.fc(1a.bh[eN][gR+1],3)}if(1f 1a.bL===\'1n\'){ff=1a.bL+ff}if(1f 1a.bM===\'1n\'){ff=ff+1a.bM}if(1a.el!==1g){ff=1a.ho(ff)}eY=(1a.bJ===1g)?1a.dv:1a.bJ;fg=1a.fh(1K(ff),1a.bK,1l,1l,eY);if(1a.mv){1a.hn(ff,1m[1]+3,1a.bu+fk+1o/gG/2-fl/2,1a.bK,1l,1l,1l,eY,1a.bH,1l,1l,1l,hf)}1i{1a.hn(ff,1a.bt+fe+1o/gG/2-fg/2,1m[1]-1a.fm(1a.bK)-3,1a.bK,1l,1l,1l,eY,1a.bH,1l,1l,1l,hf)}}if(!gY){gY=1B;gF=1a.cM.1k;eY=(1a.cG===1g)?1a.dv:1a.cG;1q(gQ=0;gQ<gF;gQ++){if(1a.cM[gQ][0]===1a.bh[eN][0]){fg=1a.fh(1K(1a.cM[gQ][1]),1a.cI,1l,1l,eY);if(1a.mv){1a.hn(1a.cM[gQ][1],(1a.cL===1g)?1a.mr-4-fg:1a.cL-fg,1a.bu+fk+1o/2-fl/2,1a.cI,1l,1l,1l,eY,1a.cE)}1i{if(1a.C>0){1a.bg.3R();1a.bg.4c(1a.bt+fe+1o/2,1a.ds-((1a.cK===1g)?1a.mp-10:1a.cK-4));1a.bg.4f(hg);1a.hn(1a.cM[gQ][1],0-fg,0-1a.cI/2-2,1a.cI,1l,1l,1l,eY,1a.cE);1a.bg.3T()}1i{1a.hn(1a.cM[gQ][1],1a.bt+fe+1o/2-fg/2,1a.ds-((1a.cK===1g)?1a.mp-4:1a.cK),1a.cI,1l,1l,1l,eY,1a.cE)}}}}}if(1a.mv){if(gR===gG-1&&1f 1a.bh[eN-1]!==\'1r\'){fk-=1o/(1a.bh[eN-1].1k-1);fk-=1a.bD*2/1a.w}1i{fk-=1o/gG}}1i{fe=(gR===gG-1)?fe+1o/gG+2*1a.bD:fe+1o/gG}}}1a.eo=hf};1a.hp=1c(eI,eJ,1R){1a.bg.5H(eI-1R/4,eJ-1R/4,1R/2,1R/2)};1a.gm=1c(){if(1a.mv){1b hq=1a.bO?1a.dq/15:0}1i{1b hq=1a.bN?1a.dp/15:0}1b fa;1b fe;1b fk;if(1a.W===0){1b fd=1a.dd;fk=1a.bu+1a.dq-1a.ed;fe=(1a.mv&&1a.eb!==\'1L\')?1a.bt+1a.ed*1a.w:1a.bt;2s(fd<=1a.cZ-1a.my){1a.bg.2o();1a.bg.1O=1a.fL(1a.cp,1a.cr);1a.bg.1R=1;if(1a.mv&&1a.eb!==\'1L\'){if(1a.mf){1a.bg.1H(fe+1a.bB,1a.bu-hq-1a.bB);1a.bg.1u(fe+1a.bB,1a.bu+1a.dq-1a.bB);1a.bg.1u(fe,1a.bu+1a.dq)}1i{1a.bg.1H(fe,1a.bu-hq);1a.bg.1u(fe,1a.bu+1a.dq)}fe+=1a.ed*1a.w}1i{if(1a.mf&&1a.eb!==\'1L\'){1a.bg.1H(1a.bt,fk);1a.bg.1u(1a.bt+1a.bB,fk-1a.bB);1a.bg.1u(1a.bt+1a.dp+hq+1a.bB,fk-1a.bB)}1i{1a.bg.1H(1a.bt,fk);1a.bg.1u(1a.bt+1a.dp+hq,fk)}fk-=1a.ed}fd+=1a.my;fd=1E(fd.2V(10));1a.bg.2c()}}1i{1b eM=(1a.W>1)?1a.W-1:((1a.dg)?1a.bh[0].1k:1a.bh.1k)-1;2s(!1a.fi(eM)){eM=1p.2m(eM/2)}if(1a.mv){fa=1a.dp/eM;fe=1a.bt}1i{fa=1a.dq/eM;fk=1a.bu+1a.dq-fa}1q(1b eN=1;eN<=eM;eN++){1a.bg.2o();1a.bg.1O=1a.fL(1a.cp,1a.cr);1a.bg.1R=1;if(1a.mv&&1a.eb!==\'1L\'){if(1a.mf){1a.bg.1H(fe,1a.bu-hq);1a.bg.1u(fe,1a.bu+1a.dq)}1i{1a.bg.1H(fe,1a.bu-hq);1a.bg.1u(fe,1a.bu+1a.dq)}fe+=fa}1i{if(1a.mf&&1a.eb!==\'1L\'){1a.bg.1H(1a.bt,fk);1a.bg.1u(1a.bt+1a.bB,fk-1a.bB);1a.bg.1u(1a.bt+1a.dp+hq+1a.bB,fk-1a.bB)}1i{1a.bg.1H(1a.bt,fk);1a.bg.1u(1a.bt+1a.dp+hq,fk)}fk-=fa}1a.bg.2c()}}};1a.gn=1c(){1b hq=1a.bO?1a.dq/15:0;1b fb;1b fd;1b fa;1b fe;if(1a.V===0){fd=1a.dc;fe=1a.bt+1a.ec;2s(fd<=1a.cY-1a.dn){1a.bg.2o();1a.bg.1O=1a.fL(1a.cq,1a.cs);1a.bg.1R=1;1a.bg.1H(fe,1a.bu+1a.dq);1a.bg.1u(fe,1a.bu-hq);1a.bg.2c();fd+=1a.dn;fe+=1a.ec}}1i{1b eM=(1a.V>1)?1a.V-1:((1a.dg)?1a.bh[0].1k:1a.bh.1k)-1;2s(!1a.eO(eM)){eM=1p.2m(eM/2)}fa=1a.dp/eM;fb=(1a.da-1a.de)/eM;fd=1a.de;fe=1a.bt+fa;1q(1b eN=0;eN<=eM-1;eN++){1a.bg.2o();1a.bg.1O=1a.fL(1a.cq,1a.cs);1a.bg.1R=1;1a.bg.1H(fe,1a.bu+1a.dq);1a.bg.1u(fe,1a.bu-hq);1a.bg.2c();fd+=fb;fe+=fa}}};1a.gl=1c(){1b gG=1a.bh.1k;1b eM;1b gF;1b eI;1b eJ;1b eN;1b gQ;1b 1L;1b 1m;1b hr;1b hs;1b ht;1b hu;1b 1R;1b hv;1b hw=[];1b hx=[];1b hy=[];1b fa=(1a.bU===1)?1g:1a.dp/1a.bU;1b hz=0;1b hA;if(1a.dY[\'46\']==={}){b7 1a.dY[\'46\']}1a.dZ=1a.fo(1a.dY);1q(1b hB=0;hB<gG;hB++){if(1a.ct[hB]){3d}eM=1a.bh[hB].1k;if(1a.bP.1k===1){hu=1a.bP[0]}1i{hu=(1f 1a.bP[hB]===\'1r\')?1a.bP[0]:1a.bP[hB]}if(1a.bT.1k===1){hv=1a.bT[0]}1i{hv=(1f 1a.bT[hB]===\'1r\')?1a.bT[0]:1a.bT[hB]}if(1a.bW.1k===1){1R=1a.bW[0]}1i{1R=(1f 1a.bW[hB]===\'1r\')?1a.bW[0]:1a.bW[hB]}hC=1a.fL(hu,hv);1q(eN=0;eN<eM;eN++){1m=1a.hi(1a.bh[hB][eN][0],1a.bh[hB][eN][1]);if(eN<eM-1){ht=1a.hi(1a.bh[hB][eN+1][0],1a.bh[hB][eN+1][1]);if(!1a.eH(1a.bh[hB][eN][0],1a.bh[hB][eN][1])){hr=1g;1q(gQ=1m[0];gQ<ht[0];gQ+=0.9t){if(ht[1]>=1m[1]){eJ=(ht[1]-1m[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1]}1i{eJ=(1m[1]-ht[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1];eJ=1m[1]*2-eJ}if(1a.eH(gQ,eJ,1B)){hr=1B;1F}}if(!hr){3d}1m[0]=gQ;1m[1]=eJ}if(!1a.eH(1a.bh[hB][eN+1][0],1a.bh[hB][eN+1][1])){hr=1g;hs=1g;1q(gQ=1m[0];gQ<ht[0];gQ+=0.9t){if(ht[1]>=1m[1]){eJ=(ht[1]-1m[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1]}1i{eJ=(1m[1]-ht[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1];eJ=1m[1]*2-eJ}if(1a.eH(gQ,eJ,1B)){hs=1B}1i if(hs){hr=1B;1F}}if(hr){ht[0]=gQ;ht[1]=eJ}}if(fa===1g){1a.bg.2o();1a.bg.1O=hC;1a.bg.1R=1R;1a.bg.1H(1m[0],1m[1]);1a.bg.1u(ht[0],ht[1]);1a.bg.2c()}1i{1q(gQ=1m[0];gQ<ht[0];gQ+=fa){if(ht[1]>=1m[1]){eJ=(ht[1]-1m[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1]}1i{eJ=(1m[1]-ht[1])*(gQ-1m[0])/(ht[0]-1m[0])+1m[1];eJ=1m[1]*2-eJ}hx.1s([gQ,eJ])}hx.1s([ht[0],ht[1]]);hw.1s(hx);hx=[]}}1m=1a.hi(1a.bh[hB][eN][0],1a.bh[hB][eN][1]);1L=1a.bi[hB];eI=1a.bh[hB][eN][0];if(1f 1a.dY[1L]!==\'1r\'&&1f 1a.dY[1L][eI]!==\'1r\'){if(fa!==1g){hy[1m[0]]=[1L,eI,hB,eN,1m]}1i{1a.hD(1L,eI,hB,eN,1m)}}if(1f 1a.dY[\'46\']!==\'1r\'&&1f 1a.dY[\'46\'][eI]!==\'1r\'){if(fa!==1g){hy[1m[0]]=[\'46\',eI,hB,eN,1m]}1i{1a.hD(\'46\',eI,hB,eN,1m)}}}if(fa!==1g){1q(eN=0,eM=hw.1k;eN<eM;eN++){hx=hw[eN];1q(gQ=0,gF=hx.1k;gQ<gF;gQ++){hz+=gQ+eN;if(1f hy[hx[gQ][0]]!==\'1r\'){gR=hy[hx[gQ][0]];1a.hD(gR[0],gR[1],gR[2],gR[3],gR[4],hz+50)}if(gQ>=gF-1){3d}hA=hx[gQ+1];2L((1c(fK,hx,hA,hC,1R){1d 1c(){fK.2o();fK.1O=hC;fK.1R=1R;fK.1H(hx[0],hx[1]);fK.1u(hA[0],hA[1]);fK.2c()}})(1a.bg,hx[gQ],hA,hC,1R),hz+50)}}}hz=0;hx=[];hw=[]}};1a.gu=1c(){1b 1m;1b 1k;1b eM=1a.bh.1k;1b gF;1b 1M=0;1b eZ=0;1b fB;1b 4e;1b 4g;1b 1j;1b hE=[];1b gS;1b gT;1b ff;1b fg;1b gQ;1b hb;1b hF=1;1b hG=1;1b eY;1b fx;1b 2N;1b hH;1b hI;1b hh;1b 2g=2;1b 1o;1b 1C;1b 1z;1b hJ=1a.cb;if(1a.ca===0){1a.ca=1a.dr/2}if(1a.cb===0){1a.cb=1a.ds/2}if(1a.cc===0){1a.cc=(1a.ds>1a.dr)?1a.dr/3.75:1a.ds/3.75}1q(1b eN=0;eN<eM;eN++){eZ+=1a.bh[eN][1]}1a.bg.3R();if(1a.mf){gQ=1a.cc*1p.2H(1a.bX*1p.1X/4j);hG=gQ/1a.cc;hF=1a.bY*1p.3W(1a.bX*1p.1X/4j)/hG;1a.bg.4H(1,hG);1a.cb+=(1a.cb-1a.cb*hG)/hG}1q(eN=0;eN<eM;eN++){1k=1a.bh[eN][1]*1p.1X*2/eZ;1a.bg.2o();if(1a.fJ()){1a.bg.2S();if(1k===0){1k=0.p5}}1j=1a.hK();if(eN>0){2s(hE[hE.1k-1]===1j||(eN===eM-1&&hE[0]===1j)){1j=1a.hK()}}hE[hE.1k]=1j;1N=1a.fL(1j,1a.bZ);if(1a.bd!==1g){1N=1a.fL(1a.bd[eN],1a.bZ)}1a.bg.1N=1N;1a.bg.4q(1a.ca,1a.cb,1a.cc,1M,1M+1k,1g);1a.bg.1u(1a.ca,1a.cb);1a.bg.2S();1a.bg.31();if(1a.mf&&1M<=1p.1X){hb=1k;hL=1a.cc*1p.2H(1M);hM=1a.cc*1p.3W(1M);if(1M+1k>1p.1X){hN=-1a.cc;hO=0;hb=1p.1X-1M}1i{hN=1a.cc*1p.2H(1M+hb);hO=1a.cc*1p.3W(1M+hb)}1a.bg.2o();1a.bg.1N=1N;1a.bg.4q(1a.ca,1a.cb+hF,1a.cc,1M,1M+hb,1g);1a.bg.1u(1a.ca+hN,1a.cb+hO+hF);1a.bg.4q(1a.ca,1a.cb,1a.cc,1M+hb,1M,1B);1a.bg.2S();1a.bg.31()}1M+=1k}1a.bg.3T();2N=1a.ft();1a.fw(2N);hH=1p.2m(1a.cc/20);1M=0;1q(eN=0;eN<eM;eN++){1k=1a.bh[eN][1]*1p.1X*2/eZ;if(1f 1a.mi[1a.bh[eN][0]]!==\'1r\'){hh=[1a.ca,1a.cb*hG];hb=1M;hI=1k/hH;1q(gQ=0;gQ<=hH;gQ++,hb+=hI){hh.1s(1a.ca+1a.cc*1p.2H(hb));hh.1s((1a.cb+1a.cc*1p.3W(hb))*hG)}fv=1a.fu(2N,hh);1a.eB(fv,\'6t\',1a.mi[1a.bh[eN][0]])}gF=1a.dY.1k;gQ=1a.bh[eN][0];if(1f 1a.dY[gQ]!==\'1r\'){1m=1a.hP(1M,1k,1a.bo);fB=(1f 1a.dY[gQ][1]===\'1r\')?1l:1a.dY[gQ][1];ff=1a.bh[eN][1];if(1f 1a.cm===\'1n\'){ff=1a.cm+ff}if(1f 1a.cn===\'1n\'){ff=ff+1a.cn}1a.fF(1a.bh[eN][0],ff,1m[0],1m[1]*hG,fB,(1f 1a.dY[gQ][\'3q\']===\'1r\')?1g:1a.dY[gQ][\'3q\']);1a.ea.1s(2L(1a.hl(1a,1m,1a.bh[eN][0],ff,hG),0))}if(1a.R){1m=1a.hP(1M,1k,1a.cg);eY=(1a.ce===1g)?1a.dv:1a.ce;if(1M+1k/2<1p.1X/3||1M+1k/2>=1p.1X/3*5){4e=0;4g=-1a.fm(1a.cf)/2}1i if(1M+1k/2<1p.1X/3*2){4e=-1a.fh(1a.bh[eN][0],1a.cf,1l,1l,eY)/2;4g=0}1i if(1M+1k/2<1p.1X/3*4){4e=-1a.fh(1a.bh[eN][0],1a.cf,1l,1l,eY);4g=-1a.fm(1a.cf)/2}1i if(1M+1k/2<1p.1X/3*5){4e=-1a.fh(1a.bh[eN][0],1a.cf,1l,1l,eY)/2;4g=-1a.fm(1a.cf)}1a.hn(1a.bh[eN][0],1m[0]+4e,1m[1]*hG+4g,1a.cf,1l,1l,1l,eY,1a.cd)}if(1a.S){ff=(1a.ci===\'2J\')?1a.bh[eN][1]:1a.bh[eN][1].2V(1a.ci);gS=1K(1a.bh[eN][1]).3Q(\'.\');gT=1K(1a.bh[eN][1]).2G(gS+1).1k;if(gT>3&&1a.ci===\'2J\'){ff=1a.fc(1a.bh[eN][1],3)}if(1f 1a.cm===\'1n\'){ff=1a.cm+ff}if(1f 1a.cn===\'1n\'){ff=ff+1a.cn}if(1a.el!==1g){ff=1a.ho(ff)}eY=(1a.cj===1g)?1a.dv:1a.cj;fg=1a.fh(1K(ff),1a.ck,1l,1l,eY);1k=1a.bh[eN][1]*1p.1X*2/eZ;1m=1a.hP(1M,1k,1a.cl);4g=-1a.fm(1a.ck)/2;4e=-fg/2;1a.hn(ff,1m[0]+4e,1m[1]*hG+4g,1a.ck,1l,1l,1l,eY,1a.mx)}1M+=1k}1a.cb=hJ};1a.hl=1c(hQ,1m,hR,hS,hG){1d 1c(){if(1f hG===\'1r\'){hG=1}1b 2g=2;1b 1o=hQ.bq*2+hQ.bs*2+2g*2;1b 1C=hQ.bq*2+hQ.bs*2+2g*2;1b fH=1g;1b 1z=1W.2n(\'8M\');1z.2I(\'id\',\'96\'+hQ.bb+\'4D\'+hR+\'4D\'+hS+\'8N\');1z.1v.1G=\'2W\';1z.1v.1o=1o+\'px\';1z.1o=1o;1z.1v.1C=1C+\'px\';1z.1C=1C;1z.1v.2a=(hQ.Z.3L+1m[0]-1o/2)+\'px\';1z.1v.2w=(hQ.Z.3K+1m[1]-1C/2)*hG+\'px\';1z.1v.3f=1E(hQ.Z.1v.3f)+80;hQ.bf.2C(1z);if(!hQ.fI(1z)&&hQ.fJ()){1z=65.5M(1z);fH=1g}1b fK=1z.3k(\'2d\');fK.2o();fK.1R=hQ.bs;fK.1O=hQ.fL(hQ.bm,(hQ.bs===0)?0:hQ.bp);2r(hQ.br){1A\'6Q\':fK.1H(1z.1o/2-hQ.bq,1z.1C/2+hQ.bq);fK.1u(1z.1o/2-hQ.bq,1z.1C/2-hQ.bq);fK.1u(1z.1o/2+hQ.bq,1z.1C/2-hQ.bq);fK.1u(1z.1o/2+hQ.bq,1z.1C/2+hQ.bq);fK.31();1F;1A\'9d\':fK.1H(1z.1o/2-hQ.bq*1p.2H(1p.1X/6),1z.1C/2+hQ.bq*1p.2H(1p.1X/3));fK.1u(1z.1o/2,1z.1C/2-hQ.bq);fK.1u(1z.1o/2+hQ.bq*1p.2H(1p.1X/6),1z.1C/2+hQ.bq*1p.2H(1p.1X/3));fK.31();1F;1A\'9e\':fK.1H(1z.1o/2-hQ.bq,1z.1C/2);fK.1u(1z.1o/2,1z.1C/2-hQ.bq);fK.1u(1z.1o/2+hQ.bq,1z.1C/2);fK.1u(1z.1o/2,1z.1C/2+hQ.bq);fK.31();1F;1A\'8G\':2u:fK.4q(1z.1o/2,1z.1C/2,hQ.bq,0,1p.1X*2,1g)}if(hQ.bn!==1g){fK.1N=hQ.fL(hQ.bn,hQ.bp);fK.2S()}fK.2c();if(fH){hQ.fM(1z)}}};1a.hT=1c(hU){if(hU===""||hU==="0"||hU===0||hU===1l||hU===1g||hU===[]){1d 1g}1d 1B};1a.fs=1c(){if(1a.bb!==\'\'&&1W.3r(1a.bb)){1d 1B}1d 1g};1a.hV=1c(2t,hW,hX,hY,eY,1j,fD,hZ,id){1b ia=[];ia.2t=(1f 2t===\'1r\'||2t===1l)?1a.dw:2t;ia.hW=(1f hW===\'1r\'||hW===1l)?1a.dx:hW;ia.hX=(1f hX===\'1r\'||hX===1l)?1a.dy:hX;ia.hY=(1f hY===\'1r\')||hY===1l?1a.dt:hY;ia.eY=(1f eY===\'1r\'||eY===1l)?1a.dv:eY;ia.1j=(1f 1j===\'1r\'||1j===1l)?1a.du:1j;ia.fD=(1f fD===\'1r\'||fD===1l)?1a.dA:fD;ia.hZ=(1f hZ===\'1r\'||hZ===1l)?1a.dB:hZ;ia.id=(1f id===\'1r\'||id===1l)?1a.dz:id;1d ia};1a.ho=1c(eN){eN=1K(eN);1b ib=1g;1b he=1g;1b ic=\'\';1b hB;if(1a.el===\'.\'){1b mA=\'.\';1b ie=\',\'}1i{1b mA=\',\';1b ie=\'.\'}1b mB=eN.3Q(\'.\');if(mB===-1){ib=eN}1i{ib=eN.2G(0,mB);he=eN.2G(mB+1)}1q(1b gQ=ib.1k-1,fa=0;gQ>=0;gQ--,fa++){hB=ib.2G(gQ,1);if(/[0-9]/.3a(hB)){if(fa===3){fa=-1;gQ++;ic=ie+ic}1i{ic=hB+ic}}}1d(he===1g)?ic:ic+mA+he};1a.gH=1c(){1b ig=1a.dp/1a.bh.1k;1a.bD=1p.2p(ig*1a.bE/1T);1d ig-2*1a.bD};1a.hi=1c(eI,eJ){1b ih=[1l,1l];if(1f eI===\'1h\'){ih[0]=(1a.V===0)?(eI-1a.dc)*1a.ec/1a.dn+1a.bt:(eI-1a.de)*1a.ec+1a.bt}if(1f eJ===\'1h\'){ih[1]=(1a.W===0)?(1a.cZ-eJ)*1a.ed/1a.my+1a.bu:(1a.db-eJ)*1a.ed+1a.bu}1d ih};1a.gc=1c(){1a.ev+=\'p7\';1a.gg+=\'p3\';1a.de=1a.ii();1a.df=1a.ij();1a.da=1a.mC();1a.db=1a.il();if(1a.eb===\'2i\'){1b im=0;2s(1a.db-1a.df<(1a.df-im)*20/1T){im=(1a.df-im)*90/1T+im}1a.df=im}if(1a.eb===\'1L\'){if(1a.cy!==1g&&1a.cw!==1g&&1a.cy>1a.cw){1a.ez(\'a4\');1a.cy=1g;1a.cw=1g}1i{if(1a.cy!==1g){1a.de=1a.cy}if(1a.cw!==1g){1a.da=1a.cw}}}if(1a.eb===\'1L\'||1a.eb===\'2i\'){if(1a.cz!==1g&&1a.cx!==1g&&1a.cz>1a.cx){1a.ez(\'a2\');1a.cz=1g;1a.cx=1g}1i{if(1a.cz!==1g){1a.df=1a.cz}1i if(1a.eb===\'2i\'){1a.df=0}if(1a.cx!==1g){1a.db=1a.cx}}}};1a.mC=1c(){1b mD;1b eM;1b eN;if(1a.dg){1b gG=1a.bh.1k;1q(1b hB=0;hB<gG;hB++){eM=1a.bh[hB].1k;1q(eN=0;eN<eM;eN++){if(1f mD===\'1r\'){mD=1a.bh[hB][eN][0]}1i{if(mD<1a.bh[hB][eN][0]){mD=1a.bh[hB][eN][0]}}}}}1i{eM=1a.bh.1k;1q(eN=0;eN<eM;eN++){if(eN===0){mD=1a.bh[eN][0]}1i{if(mD<1a.bh[eN][0]){mD=1a.bh[eN][0]}}}}1d mD};1a.il=1c(){1b mD;1b gG;1b eM;1b eN;1b hB;if(1a.dg){gG=1a.bh.1k;1q(hB=0;hB<gG;hB++){eM=1a.bh[hB].1k;1q(eN=0;eN<eM;eN++){if(1f mD===\'1r\'){mD=1a.bh[hB][eN][1]}1i{if(mD<1a.bh[hB][eN][1]){mD=1a.bh[hB][eN][1]}}}}}1i{eM=1a.bh.1k;1q(eN=0;eN<eM;eN++){gG=1a.bh[eN].1k-1;1q(hB=1;hB<=gG;hB++){if(eN===0&&hB===1){mD=1a.bh[eN][hB]}1i{if(mD<1a.bh[eN][hB]){mD=1a.bh[eN][hB]}}}}}1d mD};1a.ii=1c(){1b mD;1b io;1b eN;if(1a.dg){1b gG=1a.bh.1k;1b eN;1q(1b hB=0;hB<gG;hB++){eM=1a.bh[hB].1k;1q(eN=0;eN<eM;eN++){if(1f mD===\'1r\'){mD=1a.bh[hB][eN][0]}1i{if(mD>1a.bh[hB][eN][0]){mD=1a.bh[hB][eN][0]}}}}}1i{eM=1a.bh.1k;1q(eN=0;eN<eM;eN++){if(eN===0){mD=1a.bh[eN][0]}1i{if(mD>1a.bh[eN][0]){mD=1a.bh[eN][0]}}}}1d mD};1a.ij=1c(){1b mD;1b eM;1b eN;if(1a.dg){1b gG=1a.bh.1k;1q(1b hB=0;hB<gG;hB++){eM=1a.bh[hB].1k;1q(eN=0;eN<eM;eN++){if(1f mD===\'1r\'){mD=1a.bh[hB][eN][1]}1i{if(mD>1a.bh[hB][eN][1]){mD=1a.bh[hB][eN][1]}}}}}1i{eM=1a.bh.1k;1q(eN=0;eN<eM;eN++){if(eN===0){mD=1a.bh[eN][1]}1i{if(mD>1a.bh[eN][1]){mD=1a.bh[eN][1]}}}}1d mD};1a.hP=1c(1M,1k,fE){1b 1m;if(1M+1k/2<1p.1X/2){1m=1a.ip(1M,1M+1k,1a.ca,1a.cb,1a.cc+fE)}1i if(1M+1k/2<1p.1X){1m=1a.iq(1M,1M+1k,1a.ca,1a.cb,1a.cc+fE)}1i if(1M+1k/2<1p.1X+1p.1X/2){1m=1a.ir(1M,1M+1k,1a.ca,1a.cb,1a.cc+fE)}1i{1m=1a.is(1M,1M+1k,1a.ca,1a.cb,1a.cc+fE)}1d 1m};1a.ip=1c(it,iu,eI,eJ,iv){1b hg=(iu-it)/2+it;1d[eI+iv*1p.2H(hg),eJ+iv*1p.3W(hg)]};1a.iq=1c(it,iu,eI,eJ,iv){1b hg=(iu-it)/2+1p.1X-iu;1d[eI-iv*1p.2H(hg),eJ+iv*1p.3W(hg)]};1a.ir=1c(it,iu,eI,eJ,iv){1b hg=(iu-it)/2+it-1p.1X;1d[eI-iv*1p.2H(hg),eJ-iv*1p.3W(hg)]};1a.is=1c(it,iu,eI,eJ,iv){1b hg=2*1p.1X-it-(iu-it)/2;1d[eI+iv*1p.2H(hg),eJ-iv*1p.3W(hg)]};1a.hK=1c(){1b 1V=1p.2h(1p.2p(1p.9b()*1a.be.1k-1));1d 1a.be[1V]};1a.fm=1c(1t){if(!1a.dH){1d 8C(1t)}1b 1t=(1f 1t===\'1r\')?12:1t;1d 32*(1t/25)};1a.fh=1c(1Q,2t,hX,hY,eY){1a.bg.1Y=(1a.fJ())?2t+1a.bl+1a.bk+\'px "\'+eY+\'"\':2t+1a.bk+\'px "\'+eY+\'"\';if(!1a.dH){1d 8F(1Q,2t,hX,hY,\'4a-49\')}1i{1d 1a.bg.8R(1Q).1o}};1a.gz=1c(iw,mE,iy){1b iz;1b 2z;1b hG=1;1b gQ;1b eY=(1a.eg===1g)?1a.dv:1a.eg;if(mE){2r(1a.ej){1A\'ne\':iz=0;1F;1A\'se\':iz=1;1F;1A\'sw\':iz=2;1F;2u:iz=3}2z=1a.eh}1i{iz=1U(1p.9b()*4,10);iz=3;2z=1a.eq;eY=\'8z\'}1b fe;1b fk;if(1a.eb===\'2T\'){if(1a.cb===0){1a.cb=1a.ds/2}if(1a.mf){gQ=1a.cc*1p.2H(1a.bX*1p.1X/4j);hG=gQ/1a.cc}}if(iy){2r(iz){1A 0:fe=1a.dr-1a.et;fk=0;1F;1A 1:fe=1a.dr-1a.et;fk=1a.ds-1a.es;1F;1A 2:fe=0;fk=1a.ds-1a.es;1F;2u:fe=0;fk=0}}1i{2r(iz){1A 0:if(1a.eb===\'2T\'){fk=1a.cb-1a.cc-1a.cg-1a.cf-1a.fm(2z)-5;fe=1a.ca+1a.cb-fk-1a.fh(iw,2z,1l,1l,eY)}1i{fe=1a.dr-1a.mt-1a.fh(iw,2z,1l,1l,eY);fk=1a.bu}1F;1A 1:if(1a.eb===\'2T\'){fk=1a.cb+1a.cc+1a.cg+1a.cf+5;fe=1a.ca+fk-1a.cb-1a.fh(iw,2z,1l,1l,eY)}1i{fe=1a.dr-1a.mt-1a.fh(iw,2z,1l,1l,eY);fk=1a.ds-1a.mp-1a.fm(2z)-5}1F;1A 2:if(1a.eb===\'2T\'){fk=1a.cb+1a.cc+1a.cg+1a.cf+5;fe=1a.ca-(fk-1a.cb)-5}1i{fe=1a.mr+5;fk=1a.ds-1a.mp-1a.fm(2z)-5}1F;2u:if(1a.eb===\'2T\'){fk=1a.cb-1a.cc-1a.cg-1a.cf-1a.fm(2z)-5;fe=1a.ca-(1a.cb-fk)+5}1i{fe=1a.mr+5;fk=1a.bu}}}1d[fe,fk]};1a.gw=1c(9R){1b iA=9R.5s(\'.\');1b eM=iA.1k;1b iB=\'\';1q(1b eN=0;eN<eM;eN++){iB+=1a.gA.gB(iA[eN])}1d 1a.gA.gB(iB)};1a.fL=1c(1j,iC){if(1f 1j===\'1r\'||(1j.1k!==4&&1j.1k!==7)){1a.ez(\'29\');1d 1g}if(1j.1k===4){1j=(\'#\'+1j.2U(1,2))+1j.2U(1,2)+1j.2U(2,3)+1j.2U(2,3)+1j.2U(3,4)+1j.2U(3,4)}1b iD=1U(1j.2U(1,7).2U(0,2),16);1b iE=1U(1j.2U(1,7).2U(2,4),16);1b he=1U(1j.2U(1,7).2U(4,6),16);1d\'p2(\'+iD+\', \'+iE+\', \'+he+\', \'+iC+\')\'};1a.iF=1c(iA,iG){1q(1b eN=0,eM=iA.1k;eN<eM;eN++){if(iA[eN]===iG){1d 1B}}1d 1g};1a.iH=1c(2A,2e,3Y,ik,4N){if(1f 3Y===\'1r\'){3Y=\'\';ik=\'\'}if(!1a.eK(2A)){1a.iI=1B;1a.ez(\'am\');1d}1a.dh=2A;1a.bf=1W.3r(2A);1a.bf.3m=\'\';if(!1a.eL(2e)){1a.cv=1B;1a.ez(\'aC\');1d}3A(ge.gf(\'p9\'));3A(ge.gf(\'pg\'));if(4N){1b 1o=1a.bf.1v.1o;1b 1C=1a.bf.1v.1C;1a.iJ(1o,1C)}};1a.iK=1c(1D){1b iL=1a.mg[1a.eb];if(!1a.fZ(1D)){1d 1g}1b eM=1D.1k;1q(1b eN=0;eN<eM;eN++){if(!1a.fZ(1D[eN])){1d 1g}if(1D[eN].1k<iL.1k){1d 1g}gF=2;1q(1b gQ=0;gQ<gF;gQ++){if(1f 1D[eN][gQ]!==iL[gQ]){1d 1g}if(5G(1D[eN][gQ])&&iL[gQ]===\'1h\'){1d 1g}}}1d 1B};1a.fZ=1c(1D){if(1D 74 41){1d 1B}1d 1g};1a.fI=1c(iM){1d(iM.3k)};1a.iN=1c(iM){1d 6n.3i.4J.44(iM)==="[4h pi]"};1a.fJ=1c(){1b iO=5F.5E.3e();1d(!/^7b/.3a(iO)&&/pj/.3a(iO))};1a.iP=1c(){1b iO=5F.5E.3e();1d(/pf/.3a(iO)&&!/(pa|pc)/.3a(iO))};1a.iQ=1c(){1b iO=5F.5E.3e();1b a1=1E(iO.3Q(\'/\'));1d(/^7b/.3a(iO)&&a1<10.50)};1a.fY=1c(){1d(1f 1a.bg.8R!==\'1r\')};1a.iR=1c(iS){iS=2F.63(iS);if(1f iS.1x===\'1r\'){1a.ez(\'6v\');1d}1i{1b iT=1W.2n(\'1x\');1b iU;1b iV;1b 1D;1b iW;1b iX;1b eN;1b gQ;1b eM;if(1f iS.1x[\'a0\']===\'1r\'){1a.ez(\'6v\');1d}1i{iU=iS.1x[\'a0\'];eM=iU.1k;1q(eN=0;eN<eM;eN++){iV=iU[eN];iY=1W.2n(\'d8\');if(1f iV.5k===\'1n\'&&iV.5k===\'1B\'){iY.2I(\'5k\',1B)}if(1f iV.2e===\'1n\'){iY.2I(\'2e\',iV[\'2e\'])}if(1f iV.id===\'1n\'){iY.2I(\'id\',iV[\'id\'])}if(1f iV[\'1D\']!==\'1r\'&&1f iV[\'1D\']!==\'1r\'){gF=iV[\'1D\'].1k;1q(gQ=0;gQ<gF;gQ++){iZ=1W.2n(\'1D\');iZ.2I(\'8l\',iV[\'1D\'][gQ][\'8l\']);iZ.2I(\'1J\',iV[\'1D\'][gQ][\'1J\']);iY.2C(iZ)}}iT.2C(iY)}if(1f iS.1x[\'73\']!==\'1r\'){ja=iS.1x[\'73\'];eM=ja.1k;jb=1W.2n(\'73\');1q(eN=0;eN<eM;eN++){jc=1W.2n(\'1j\');jc.2I(\'1J\',ja[eN]);jb.2C(jc)}iT.2C(jb)}if(1f iS.1x[\'71\']!==\'1r\'){iW=iS.1x[\'71\'];eM=iW.1k;jd=1W.2n(\'71\');1q(eN=0;eN<eM;eN++){je=1W.2n(\'6x\');je.2I(\'3U\',iW[eN][\'3U\']);je.2I(\'1J\',iW[eN][\'1J\']);jd.2C(je)}iT.2C(jd)}}}jf=1W.2n(\'9X\');jf.2C(iT);1d jf};1a.hD=1c(1L,eI,hB,eN,1m,hf){if(1f 1a.dY[1L]===\'1r\'||1f 1a.dY[1L][eI]===\'1r\'){1d 1g}if(1f 1a.dY[1L][eI][2]!==\'1r\'&&1a.dY[1L][eI][2]!==1a.bi[hB]){1d 1g}if(1a.iF(1a.dP,1m)){1d 1g}1a.dP.1s(1m);1b fB=(1f 1a.dY[1L][eI][1]===\'1r\')?1l:1a.dY[1L][eI][1];1b hR=1a.bh[hB][eN][0];if(1f 1a.P===\'1n\'){hR=1a.P+hR}if(1f 1a.P===\'1n\'){hR=hR+1a.P}1b hS=1a.bh[hB][eN][1];if(1f 1a.Q===\'1n\'){hS=1a.Q+hS}if(1f 1a.U===\'1n\'){hS=hS+1a.U}1a.fF(hR,hS,1m[0],1m[1],fB,(1f 1a.dY[1L][eI][\'3q\']===\'1r\')?1g:1a.dY[1L][eI][\'3q\']);if(1f hf===\'1r\'){hf=0}1a.ea.1s(2L(1a.hl(1a,1m,hR,hS),hf))};1a.jg=1c(jh){1b jf;if(!1a.fJ()){84.9x=(1c(iM,38){if(iM){1d 1c(){34(38)}}1d 1c(){}})(1a.dm,1a.eA.5X)}5f{jf=2D 9K(\'7U.pd\')}5l(ji){5f{1b jf=2D 9D()}5l(38){34(38.8v);1d}}jf.9G("ld",jh,1g);jf.m0("k0-j4","oG/9X, 1Q/oF");jf.k2(1l);jf=jf.oJ;jf.g2=1g;1d jf};1a.jj=1c(jh,ff){1b jf;ff=(1f ff!==\'1r\'&&ff===1B);if(!1a.fJ()){84.9x=(1c(iM,38){if(iM){1d 1c(){34(38)}}1d 1c(){}})(1a.dm,1a.eA.5X)}5f{jf=2D 9K(\'7U.oA\')}5l(ji){5f{if(!ff){1b jk=2D 9D();jk.9G("ld",jh,1g);jk.m0("k0-j4","1Q/f4");jk.k2(1l);jf=jk.oR}}5l(38){34(38.8v);1d}}1b jl;if(ff){if(1a.fJ()){jl=jf.pV(jh)}1i{1b jm=2D pQ();jf=jm.q1(jh,"1Q/f4");jl=1B}}1i{jf.g2=1g;jl=1a.fJ()?jf.pr(jh):1B}if(!jl){1a.ez(\'5X\');1d}1d jf};1a.jn=1c(eI,im){1d 1p.f9(eI)/1p.f9(im)};1a.jo=1c(jf){if(1f jf===\'1r\'){1d 1g}1b jp=[];1b jq=[];1b jr=[];if(jf.7X(\'1x\').1k!==1){1a.ez(\'6v\');1d}1b js=jf.7X(\'1x\')[0];1b eM=js.3M.1k;1b gF;1b gG;1b jt;1b 1D;1b 1j;1b eb;1b ju;1b jv;1b jw;1b jx;1b jy;1b gQ;1b gR;1b id;1b jz;1b 7a;1b jA;1q(1b eN=0;eN<eM;eN++){jt=js.3M[eN];if(1f jt===\'1r\'){3d}if(1K(jt.4p).3e()===\'d8\'){eb=jt.2P(\'2e\');if(eb===1l||eb===\'\'){1a.ez(\'au\');1d}1a.eb=eb;gF=jt.3M.1k;if(gF<1){1a.ez(\'ak\');1d}gG=0;jy=(jt.2P(\'5k\')===\'1B\');1q(gQ=0;gQ<gF;gQ++){1D=jt.3M[gQ];if(1K(1D.4p).3e()===\'1D\'){jv=1D.2P(\'8l\');jw=1D.2P(\'1J\');if(jv===1l||jv===\'\'||jw===1l||jw===\'\'){1a.ez(\'6o\');1d}2r(eb){1A\'2i\':if(jw.43(\',\')>-1){7a=[1K(jv)];jA=jw.5s(\',\');1q(gR=0;gR<jA.1k;gR++){7a.1s(1E(jA[gR]))}jp.1s(7a);if(gG<jA.1k){gG=jA.1k}}1i{jp.1s([1K(jv),1E(jw)])}1F;1A\'2T\':if(5G(1E(jw))){1a.ez(\'6o\');1d}jp.1s([1K(jv),1E(jw)]);1F;2u:if(5G(1E(jw))){1a.ez(\'6o\');1d}if(/^[0-9.]*$/.3a(jv)&&!jy){jp.1s([1E(jv),1E(jw)])}1i{jp.1s([1K(jv),1E(jw)]);1a.bV=1B}}}}if(1a.eb===\'2i\'){1q(gQ=0;gQ<gG;gQ++){1a.cV.1s([1a.bz,1K(gQ+1),gQ+1]);1a.ct.1s(1g)}}if(1f jp[0][0]===\'1n\'&&1a.eb===\'1L\'){if(1a.bh.1k===0){1q(gR=0,gF=jp.1k;gR<gF;gR++){1a.cM.1s([gR,1K(jp[gR][0]),\'x-1J\']);1a.bj[jp[gR][0]]=gR;jp[gR][0]=gR}}1i{1b 1w=1a.ii();1b 1y=1a.mC();1b fa=1p.2p((1y-1w)/(jp.1k-1));1q(1b jB=0,gR=1w;gR<1y,jB<jp.1k;gR+=fa,jB++){1a.cM.1s([gR,1K(jp[jB][0]),\'x-1J\']);1a.bj[jp[jB][0]]=gR;jp[jB][0]=gR}}1a.R=1g}id=1g;jz=jt.2P(\'id\');if(jz!==1l&&jz!==\'\'){id=jz}if(1a.eb===\'1L\'){1a.dg=1B;if(1a.bh===[]){1a.bh=2D 41(jp)}1i{1a.bh.1s(jp)}1b 1V=1K(1a.bh.1k-1);1a.bi[1V]=(id===1g)?\'h9\'+1V:id;if(1f 1a.bP[1V]===\'1r\'){1a.bP[1V]=1a.bQ}if(1f 1a.bT[1V]===\'1r\'){1a.bT[1V]=1a.bR}if(1f 1a.bW[1V]===\'1r\'){1a.bW[1V]=1a.bS}1a.cX.1s([1a.bQ,1a.bi[1V],1a.bi[1V]])}1i{1a.bh=jp}jp=[]}if(1K(jt.4p).3e()===\'73\'){gF=jt.3M.1k;1q(gQ=0;gQ<gF;gQ++){1j=jt.3M[gQ];if(1K(1j.4p).3e()===\'1j\'){ju=1j.2P(\'1J\');if(ju===1l||ju===\'\'){1a.ez(\'ad\');1d}jq.1s(ju)}}1a.bd=jq}if(1K(jt.4p).3e()===\'71\'){gF=jt.3M.1k;1q(gQ=0;gQ<gF;gQ++){1j=jt.3M[gQ];if(1K(1j.4p).3e()===\'6x\'){jx=1j.2P(\'3U\');jw=1j.2P(\'1J\');jz=1j.2P(\'id\');if(jx===1l||jx===\'\'||jw===1l||jw===\'\'){1a.ez(\'aZ\');1d}if(jz===1l||jz===\'\'){jr.1s([jx,jw])}1i{jr.1s([jx,jw,jz])}}}}}1d jr};1a.fc=1c(ib,jA){1b he=1E(ib);1d 1E(he.2V(jA))};1a.gj=1c(eP){1b jC;1b fd;1b jD;1b gS;1b gT;1b ff;1b fg;1b jE=1;1b eZ=0;if(eP){1b eR=1a.df;1b eQ=1a.db}1i{1b eR=1a.de;1b eQ=1a.da}2s(eZ<1a.dp){jC=1a.jF(eR,eQ,jE);1a.dc=jC[0];1a.cY=jC[1];1a.dn=jC[2];fd=1a.dc;jD=0;2s(fd<=1a.cY){ff=(1a.F===\'2J\')?fd:fd.2V(1a.F);gS=1K(fd).3Q(\'.\');gT=1K(fd).2G(gS+1).1k;if(gT>3&&1a.F===\'2J\'){ff=1a.fc(fd,3)}fg=1a.fh(1K(ff),1a.L,1l,1l,1a.L);eZ+=fg;fd+=1a.dn;jD++;if(jD<1){eZ-=fg/2}}eZ-=fg/2;jE++}1a.dk=(eR-1a.dc)*1a.dp/(1a.cY-1a.dc);1a.di=(1a.cY-eQ)*1a.dp/(1a.cY-1a.dc);1a.ec=1a.dn*1a.dp/(1a.cY-1a.dc)};1a.gk=1c(){1b fl=1a.fm(1a.M);1b jE=1p.2m(1a.dq/(fl+6));1b jC=1a.jF(1a.df,1a.db,jE);1a.dd=jC[0];1a.cZ=jC[1];1a.my=jC[2];1a.dl=(1a.df-1a.dd)*1a.dq/(1a.cZ-1a.dd);1a.dj=(1a.cZ-1a.db)*1a.dq/(1a.cZ-1a.dd);1a.ed=1a.my*1a.dq/(1a.cZ-1a.dd)};1a.gb=1c(){1a.ev+=\'po\';1a.gg=\'pq\';1a.bt=1a.mr+1;1a.bu=1a.u+1;1a.dp=1a.dr-1a.mr-1a.mt-2;1a.dq=1a.ds-1a.u-1a.mp-2;1a.w=1a.dp/1a.dq};1a.iJ=1c(eI,eJ){if(eI){1b 1o=1U(eI,10);if(!5G(1o)){1a.dr=1o}}if(eJ){1b 1C=1U(eJ,10);if(!5G(1C)){1a.ds=1C}}};1a.gd=1c(){if(1a.dp===0){1a.gc()}if(1a.da===1a.de){1a.da++}if(1a.db===1a.df){1a.db++}1a.ec=1a.dp/(1a.da-1a.de);1a.ed=1a.dq/(1a.db-1a.df)};1a.ga=1c(){1b 2O=(1a.u===1g)?1a.v:1a.u;1b 3C=(1a.mp===1g)?1a.mq:1a.mp;1b 3c=(1a.mr===1g)?1a.ms:1a.mr;1b 3z=(1a.mt===1g)?1a.mu:1a.mt;1b jG=(1a.dC===1g)?1a.dD:1a.dC;1b jH=(1a.dE===1g)?1a.dF:1a.dE;1b jI=1a.dr-3c-3z+8;1b eN;1b eM;if(1a.cP===1B){if(1a.eb===\'1L\'){eM=1a.cX.1k;1q(eN=0;eN<eM;eN++){1a.cU.1s([1a.bP[eN],1a.cX[eN][1],1a.cX[eN][2]])}}if(1a.eb===\'2i\'){eM=1a.cV.1k;1q(eN=0;eN<eM;eN++){1a.cU.1s([(1f 1a.by===\'1n\'||1f 1a.by[eN]===\'1r\')?1a.bz:1a.by[eN],1a.cV[eN][1],eN])}}}eM=1a.cU.1k;if(eM===0){1d 1g}1b 1G;if(1a.fZ(1a.cT)){1G=1a.cT}1i{1G=1a.cT.5s(\' \');1G[0]=1G[0].2G(0,1);1b jJ=(1G[0]===\'l\'||1G[0]===\'r\');if(1f 1G[1]===\'1r\'){if(jJ){1G[1]=\'m\'}1i{1G[1]=\'c\'}}1G[1]=1G[1].2G(0,1);1G=1G[0]+1G[1]}1b 4o=[[]];1b jK=0;1b jL=0;1b jM=0;1b jN=0;1b jO;1b jP=(jJ||1a.fZ(1G))?0:1a.fm(1a.cR)+10;1q(eN=0;eN<eM;eN++){jO=1a.fh(1a.cU[eN][1],1a.cR,1l,1l,1a.cQ)+25;if(jM<jO){jM=jO}}1q(eN=0;eN<eM;eN++){jK+=jM;if(jK>jI||jJ||1a.fZ(1G)){jK=jM;jN++;if(jJ||1a.fZ(1G)){4o[jN-1]=[1a.cU[eN]]}1i{4o[jN]=[1a.cU[eN]]}jP+=1a.fm(1a.cR)+4}1i{if(jL<jK){jL=jK}4o[jN].1s(1a.cU[eN]);if(eN===eM-1){}}}1b jQ;1b jR;2r(1G){1A\'tc\':2u:if(1f 1G===\'1n\'){if(1a.cS!==1g){2O=1a.cS}jQ=3c+jI/2-jL/2;jR=2O}1F;1A\'tl\':if(1a.cS!==1g){2O=1a.cS}jQ=3c;jR=2O;1F;1A\'tr\':if(1a.cS!==1g){2O=1a.cS}jQ=3c+jI-jL;jR=2O;1F;1A\'bl\':jQ=3c;if(1a.cS!==1g){jR=1a.ds-1a.cS-2*jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1i{jR=1a.ds-3C-jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1F;1A\'bc\':jQ=3c+jI/2-jL/2;if(1a.cS!==1g){jR=1a.ds-1a.cS-2*jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1i{jR=1a.ds-3C-jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1F;1A\'br\':jQ=3c+jI-jL;if(1a.cS!==1g){jR=1a.ds-1a.cS-2*jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1i{jR=1a.ds-3C-jP+1a.fm(1a.mn)+1a.fm((1a.mv)?1a.M:1a.L)+10}1F;1A\'lt\':jQ=10;if(1a.cS!==1g){jQ=1a.cS;3c=1a.cS}jR=2O;1F;1A\'lm\':jQ=10;if(1a.cS!==1g){jQ=1a.cS;3c=1a.cS}jR=2O+(1a.ds-2O-3C-jP)/2;1F;1A\'lb\':jQ=10;if(1a.cS!==1g){jQ=1a.cS;3c=1a.cS}jR=1a.ds-3C-jP;1F;1A\'rt\':if(1a.cS!==1g){3z=1a.cS}jQ=1a.dr-3z-jM;jR=2O;1F;1A\'rm\':if(1a.cS!==1g){3z=1a.cS}jQ=1a.dr-3z-jM;jR=2O+(1a.ds-2O-3C-jP)/2;1F;1A\'rb\':if(1a.cS!==1g){3z=1a.cS}jQ=1a.dr-3z-jM;jR=1a.ds-3C-jP;1F}1b jS=4o.1k;1b fe=(1a.fZ(1G))?1G[0]:jQ;1b fk=(1a.fZ(1G))?1G[1]:jR;1b jT;1b jU=0;1b eY=(1a.cQ===1g)?1a.dv:1a.cQ;1b fP;1b fS;1b fR;1q(1b iD=0;iD<jS;iD++){if(jJ||1a.fZ(1G)){eM=1}1i{eM=4o[iD].1k}1q(eN=0;eN<eM;eN++){jT=4o[iD][eN];1a.bg.1N=1a.fL(jT[0],1);1a.bg.5H(fe,fk,10,10);1a.hn(jT[1],fe+15,fk,1a.cR,1l,1l,1l,eY,1a.cO);fP=\'<47 \'+\'1v="1G:2W;\'+\'2a:\'+(1a.Z.3L+fe)+\'px;\'+\'2w:\'+(1a.Z.3K+fk)+\'px;\'+\'1o: 7t;\'+\'1C: 7t;\'+\'1Y-1t: 5A;\'+\'z-1V: \'+(1E(1a.Z.1v.3f)+4L)+\';\'+\'" id="pz\'+1U(fe,10)+\'4D\'+1U(fk,10)+\'">\'+\'<42 3v="1D:2k/8h;8g,8n///8u==" \'+\'1o="\'+(1a.bq*2)+\'" \'+\'1C="\'+(1a.bq*2)+\'" \'+"><\\/47>";fR=1W.2n(\'8t\');fR.3m+=fP;fP=fR.3x;fS=fP.c5(1B);fP.c1.8w(fP);1a.eB(fS,\'6t\',(1c(hQ,jT,2O,3C,3c,3z,jG){1d 1c(){1b eN;1b eM;hQ.u=2O;hQ.mp=3C;hQ.mr=3c;hQ.mt=3z;hQ.dC=jG;if(hQ.cP===1B){1b jV=[];eM=hQ.cU.1k;1q(eN=0;eN<eM;eN++){if(hQ.cU[eN][2]===\'83\'){jV.1s(hQ.cU[eN])}}hQ.cU=jV}hQ.dY=hQ.dZ;if(hQ.eb===\'1L\'){eM=hQ.bi.1k;1q(eN=0;eN<eM;eN++){if(jT[2]!==\'83\'&&jT[2]===hQ.bi[eN]){hQ.ct[eN]=!(hQ.ct[eN])}}}if(hQ.eb===\'2i\'){eM=hQ.ct.1k;1q(eN=0;eN<eM;eN++){if(jT[2]===eN){hQ.ct[eN]=!(hQ.ct[eN])}}}eM=hQ.ea.1k;1q(eN=0;eN<eM;eN++){pH(hQ.ea[eN])}hQ.ea=[];hQ.fX()}})(1a,jT,1a.u,1a.mp,1a.mr,1a.mt,1a.dC));1a.bf.2C(fS);fe+=jM;jU++}fe=(1a.fZ(1G))?1G[0]:jQ;fk+=1a.fm(1a.cR)+4}if(jJ){jM+=10}2r(1G){1A\'tc\':1A\'tl\':1A\'tr\':2u:if(1a.u===1g&&!1a.fZ(1G)){1b jW=1a.u;1a.u=2O+jP}1F;1A\'bl\':1A\'bc\':1A\'br\':if(1a.mp===1g){1b jW=1a.mp;1a.mp=1a.mq+jP}if(1a.dC===1g){1a.dC=1a.dD+jP}1F;1A\'lt\':1A\'lm\':1A\'lb\':if(1a.mr===1g){1b jW=1a.mr;1a.mr=1a.ms+jM}if(1a.dE===1g){1a.dE=1a.dF+jM}1F;1A\'rt\':1A\'rm\':1A\'rb\':if(1a.mt===1g){1b jW=1a.mt;1a.mt=1a.mu+jM}1F}1a.hm=jP};1a.hn=1c(1Q,eI,eJ,2t,hW,hX,hY,eY,1j,fD,hZ,id,hf){if(1f 1a.bg===1g||1f 1Q===\'1r\'||1f eI===\'1r\'||1f eJ===\'1r\'){1d 1g}1Q=1K(1Q);1b jX=1a.hV(2t,hW,hX,hY,eY,1j,fD,hZ,id);2t=(1a.fJ())?jX.2t+1a.bl+1a.bk+\'px "\'+eY+\'"\':jX.2t+1a.bk+\'px "\'+eY+\'"\';if(1a.fJ()){eI-=1}if(!1a.dH){if(1f hf===\'1r\'){1a.bg.1O=1a.fL(jX.1j,jX.fD);1a.bg.5h(1Q,eI,eJ,jX.2t,jX.hW,jX.hX,jX.hY,jX.eY,jX.1j,jX.fD,jX.hZ,jX.id)}1i{2L((1c(fK,1O,1Q,eI,eJ,2t,hW,hX,hY,eY,1j,fD,hZ,id){1d 1c(){fK.1O=1O;fK.5h(1Q,eI,eJ,2t,hW,hX,hY,eY,1j,fD,hZ,id)}})(1a.bg,1a.fL(jX.1j,jX.fD),1Q,eI,eJ,jX.2t,jX.hW,jX.hX,jX.hY,jX.eY,jX.1j,jX.fD,jX.hZ,jX.id),hf)}}1i{if(1f hf===\'1r\'){1a.bg.1N=1a.fL(jX.1j,jX.fD);1a.bg.1Y=2t;1a.bg.5c=\'2w\';1a.bg.8X(1Q,eI,eJ)}1i{2L((1c(fK,1N,1Y,1Q,eI,eJ){1d 1c(){fK.1N=1N;fK.1Y=1Y;fK.5c=\'2w\';fK.8X(1Q,eI,eJ)}})(1a.bg,1a.fL(jX.1j,jX.fD),2t,1Q,eI,eJ),hf)}}};1a.gq=1c(){1b jY=(1a.ml===1g)?1a.dv:1a.ml;1b jZ=(1a.mm===1g)?1a.dv:1a.mm;1b ka=1a.fh(1a.m,1a.k,1l,1l,jY);1b fe=(1a.dp-ka)/2+1a.mr;1b fk=1a.ds-1a.dC-1a.fm((1a.mv)?1a.mn:1a.k);if(1a.mv){1a.hn(1a.mo,fe,fk,1a.mn,1l,1l,1l,jZ,1a.mk)}1i{1a.hn(1a.m,fe,fk,1a.k,1l,1l,1l,jY,1a.mj)}1b eM=(1a.mv)?1a.m.1k:1a.mo.1k;1b fl=eM*1a.fm((1a.mv)?1a.k:1a.mn);fk=1p.2p((1a.ds-fl)/2);1q(1b eN=0;eN<eM;eN++){fe=1a.dE;if(1a.mv){1a.hn(1a.m.2G(eN,1),fe,fk,1a.k,1l,1l,1l,jY,1a.mj)}1i{1a.hn(1a.mo.2G(eN,1),fe,fk,1a.mn,1l,1l,1l,jZ,1a.mk)}fk+=1a.fm((1a.mv)?1a.k:1a.mn)}};1a.gr=1c(){if(1a.eb!==\'1L\'){1d 1g}1b eM;1b 1m;1b fg;1b eY;1b eN;1b eM=1a.cM.1k;1b hg=-1a.C*1p.1X/4j;1b fk;1b eI;1b kb;1b kc;1b kd=[];1b ke=[];1q(eN=0;eN<eM;eN++){1m=1a.hi(1a.cM[eN][0],0);if(1m[0]<1a.mr||1m[0]>1a.dr-1a.mt){3d}if(1f 1a.cM[eN][2]!==\'1r\'&&1a.cM[eN][2]===\'x-1J\'){kd.1s(1a.cM[eN])}1i{ke.1s(1a.cM[eN])}}eM=kd.1k;1q(eN=0;eN<eM;eN++){1m=1a.hi(kd[eN][0],0);kb=(1a.mw&&eN===0);kc=(1a.A&&eN+1===eM);fk=1a.ds-((1a.N===1g)?1a.mp-4:1a.N);eY=(1a.J===1g)?1a.dv:1a.J;fg=1a.fh(1K(kd[eN][1]),1a.L,1l,1l,eY);if(1a.C>0){1a.bg.3R();if(kb){eI=1m[0]+2+1a.L/2}1i if(kc){eI=1m[0]+2-1a.L/2}1i{eI=1m[0]+2}1a.bg.4c(eI,fk+4);1a.bg.4f(hg);1a.hn(kd[eN][1],0-fg,0-1a.L/2-2,1a.L,1l,1l,1l,eY,1a.D);1a.bg.3T()}1i{if(kb){eI=1m[0]}1i if(kc){eI=1m[0]-fg}1i{eI=1m[0]-fg/2}1a.hn(kd[eN][1],eI,fk,1a.L,1l,1l,1l,eY,1a.D)}}eM=ke.1k;1q(eN=0;eN<eM;eN++){1m=1a.hi(ke[eN][0],0);1m[0]=1E(1m[0].2V(12));kb=(1a.cA&&1m[0]===1a.bt);kc=(1a.cC&&1m[0]===1a.bt+1a.dp);fk=1a.ds-((1a.cK===1g)?1a.mp-4:1a.cK);eY=(1a.cG===1g)?1a.dv:1a.cG;fg=1a.fh(1K(ke[eN][1]),1a.cI,1l,1l,eY);if(1a.C>0){1a.bg.3R();if(kb){eI=1m[0]+2+1a.L/2}1i if(kc){eI=1m[0]+2-1a.L/2}1i{eI=1m[0]+2}1a.bg.4c(eI,fk+4);1a.bg.4f(hg);1a.hn(ke[eN][1],0-fg,0-1a.cI/2-2,1a.cI,1l,1l,1l,eY,1a.cE);1a.bg.3T()}1i{if(kb){eI=1m[0]}1i if(kc){eI=1m[0]-fg}1i{eI=1m[0]-fg/2}1a.hn(ke[eN][1],eI,fk,1a.cI,1l,1l,1l,eY,1a.cE)}}};1a.gs=1c(){1b eM;1b 1m;1b fg;1b eM=1a.cN.1k;1b eN;1b eY=(1a.cH===1g)?1a.dv:1a.cH;1b hg=-1a.C*1p.1X/4j;1b kb;1b kc;1b eI;1b eJ;if(1a.mv){1q(eN=0;eN<eM;eN++){1m=1a.hi(1a.cN[eN][0],0);if(1m[0]<1a.mr||1m[0]>1a.dp+1a.bt){3d}kb=(1a.cB&&1m[0]===1a.bt);kc=(1a.cD&&1m[0]===1a.bt+1a.dp);fg=1a.fh(1K(1a.cN[eN][1]),1a.cJ,1l,1l,eY);if(1a.C>0){1a.bg.3R();if(kb){eI=1m[0]+2+1a.cJ}1i if(kc){eI=1m[0]+2-1a.cJ/4}1i{eI=1m[0]+2}1a.bg.4c(eI,(1a.cK===1g)?1a.ds-1a.mp+8:1a.ds-1a.cK);1a.bg.4f(hg);1a.hn(1a.cN[eN][1],0-fg,0-1a.cJ/2-2,1a.cJ,1l,1l,1l,eY,1a.cF);1a.bg.3T()}1i{if(kb){eI=1m[0]}1i if(kc){eI=1m[0]-fg}1i{eI=1m[0]-fg/2}1a.hn(1a.cN[eN][1],eI,(1a.cK===1g)?1a.ds-1a.mp+4:1a.ds-1a.cK,1a.cJ,1l,1l,1l,eY,1a.cF)}}}1i{1q(eN=0;eN<eM;eN++){1m=1a.hi(0,1a.cN[eN][0]);if(1m[1]<1a.u||1m[1]>1a.ds-1a.mp){3d}kb=(1a.cB&&1m[1]===1a.bu+1a.dq);kc=(1a.cD&&1m[1]===1a.bu);fg=1a.fh(1K(1a.cN[eN][1]),1a.cJ,1l,1l,eY);if(kb){eJ=1m[1]-1a.fm(1a.cJ)}1i if(kc){eJ=1m[1]}1i{eJ=1m[1]-1a.fm(1a.cJ/2)}1a.hn(1a.cN[eN][1],(1a.cL===1g)?1a.mr-fg-4:1a.cL-fg,eJ,1a.cJ,1l,1l,1l,eY,1a.cF)}}};1a.gv=1c(){1b eY=(1a.dK===1g)?1a.dv:1a.dK;1b kf=1a.fh(1a.dI,1a.dL,1l,1l,eY);1b fe;2r(1a.dM){1A\'2a\':fe=1a.mr;1F;1A\'3p\':fe=1a.dr-1a.mt-kf;1F;2u:fe=1p.2p((1a.dr-kf)/2)}1b fk=1a.dG;1a.hn(1a.dI,fe,fk,1a.dL,1l,1l,1l,eY,1a.dJ)};1a.gC=1c(){1b fp=1a;1d 1c(){1b gy=fp.gz(fp.ee,1B);1b eY=(fp.eg===1g)?fp.dv:fp.eg;fp.hn(fp.ee,gy[0]+1,gy[1]+1,fp.eh,1l,1l,1l,eY,fp.ek,fp.ei);fp.hn(fp.ee,gy[0],gy[1],fp.eh,1l,1l,1l,eY,fp.ef,fp.ei)}};1a.mz=1c(eP){1b fg;1b ff;1b gT;1b gS;1b fa;1b fb;1b fd;1b fe;1b fk=1a.ds-((1a.N===1g)?1a.mp-4:1a.N);1b hg=-1a.C*1p.1X/4j;1b eI;1b kb;1b kc;if(eP){1a.ec=1a.dp/(1a.db-1a.df);1a.gj(eP);1b kg=1a.W;1b kh=1a.dd;1b ki=1a.cZ;1b eS=1a.G;1b eT=1a.Q;1b eU=1a.U;1b eV=1a.K;1b eW=1a.M;1b kj=1a.E;1b eQ=1a.db;1b eR=1a.df}1i{1b kg=1a.V;1b kh=1a.dc;1b ki=1a.cY;1b eS=1a.F;1b eT=1a.P;1b eU=1a.T;1b eV=1a.J;1b eW=1a.L;1b kj=1a.D;1b eQ=1a.da;1b eR=1a.de}1b eY=(eV===1g)?1a.dv:eV;if(kg===0){fd=kh;fe=1a.bt;2s(fd<=ki){ff=(eS===\'2J\')?fd:fd.2V(eS);kb=(((1a.mw&&!eP)||(1a.z&&eP))&&fd===kh);kc=(((1a.A&&!eP)||(1a.B&&eP))&&fd+1a.dn>ki);gS=1K(fd).3Q(\'.\');gT=1K(fd).2G(gS+1).1k;if(gT>3&&eS===\'2J\'){ff=1a.fc(fd,3)}if(1a.el!==1g){ff=1a.ho(ff)}if(1f eT===\'1n\'){ff=eT+ff}if(1f eU===\'1n\'){ff=ff+eU}fg=1a.fh(1K(ff),eW,1l,1l,eY);if(1a.C>0){if(kb){eI=fe+eW}1i if(kc){eI=fe-eW/4}1i{eI=fe+eW/4}1a.bg.3R();1a.bg.4c(eI,fk+4);1a.bg.4f(hg);1a.hn(ff,0-fg,0-eW/2-2,eW,1l,1l,1l,eY,kj);1a.bg.3T()}1i{if(kb){eI=fe}1i if(kc){eI=fe-fg}1i{eI=fe-fg/2}1a.hn(ff,eI,fk,eW,1l,1l,1l,eY,kj)}fd+=1a.dn;fe+=1a.ec}}1i{1b hr=1g;1q(1b eN=2;eN<4L;eN++){if((eQ-eR)%eN===0){hr=eN;if(!1a.eO(eN,1B)){3d}1F}}1b eM=(hr)?hr:eN;if(kg>1){eM=kg-1}1b kk=0;1b 1D=(1a.dg)?1a.bh[0]:1a.bh;1q(eN=0;eN<1D.1k;eN++){gS=1K(1D[eN][0]).3Q(\'.\');if(gS>=0){gT=1K(1D[eN][0]).2G(gS+1).1k;if(kk<gT){kk=gT}}}kk++;2s(!1a.eO(eM,1B)){eM=1p.2m(eM/2)}fa=1a.dp/eM;fb=(eQ-eR)/eM;fd=eR;fe=1a.mr;fk=(1a.N===1g)?1a.bu+1a.dq+4:1a.ds-1a.N;1q(eN=0;eN<=eM;eN++){kb=(1a.mw&&eN===0);kc=(1a.A&&eN+1>eM);ff=(eS===\'2J\'&&1a.fc(fd,eS)!==fd)?1a.fc(fd,kk):fd.2V(eS);if(1a.el!==1g){ff=1a.ho(ff)}if(1f eT===\'1n\'){ff=eT+ff}if(1f eU===\'1n\'){ff=ff+eU}fg=1a.fh(ff,eW,1l,1l,eY);if(1a.C>0){1a.bg.3R();if(kb){eI=fe+fg/4+eW/2}1i if(kc){eI=fe+fg/4-eW/2}1i{eI=fe+fg/4}1a.bg.4c(eI,fk+4);1a.bg.4f(hg);1a.hn(ff,0-fg,0-eW/2-2,eW,1l,1l,1l,eY,kj);1a.bg.3T()}1i{if(kb){eI=fe}1i if(kc){eI=fe-fg}1i{eI=fe-fg/2}1a.hn(ff,eI,fk,eW,1l,1l,1l,eY,kj)}fd+=fb;fe+=fa}}};1a.gp=1c(eP){if(eP){1a.mz(1B);1d}1b fg;1b ff;1b gT;1b gS;1b fa;1b fb;1b fd;1b fk;1b fe=(1a.O===1g)?1a.mr-4:1a.O;1b fl=1a.fm(1a.M);1b eY=(1a.K===1g)?1a.dv:1a.K;1b kb;1b kc;if(1a.W===0){fd=1a.dd;fk=1a.bu+1a.dq;2s(fd<=1a.cZ){kb=(1a.z&&fd===1a.dd);kc=(1a.B&&fd+1a.my>1a.cZ);ff=(1a.G===\'2J\')?fd:fd.2V(1a.G);gS=1K(fd).3Q(\'.\');gT=1K(fd).2G(gS+1).1k;if(gT>3&&1a.G===\'2J\'){ff=1a.fc(fd,3)}if(1a.el!==1g){ff=1a.ho(ff)}if(1f 1a.Q===\'1n\'){ff=1a.Q+ff}if(1f 1a.U===\'1n\'){ff=ff+1a.U}fg=1a.fh(1K(ff),1a.M,1l,1l,eY);if(kb){eJ=fk-fl}1i if(kc){eJ=fk}1i{eJ=fk-fl/2}1a.hn(ff,fe-fg,eJ,1a.M,1l,1l,1l,eY,1a.E);fd+=1a.my;fd=1E(fd.2V(10));fk-=1a.ed}}1i{1b hr=1g;1q(1b eN=2;eN<4L;eN++){if((1a.db-1a.df)%eN===0){hr=eN;if(!1a.fi(eN,1B)){3d}1F}}1b eM=(hr)?hr:eN;if(1a.W>1){eM=1a.W-1}1b kk=0;1b 1D=(1a.dg)?1a.bh[0]:1a.bh;1q(eN=0;eN<1D.1k;eN++){gS=1K(1D[eN][1]).3Q(\'.\');if(gS>=0){gT=1K(1D[eN][1]).2G(gS+1).1k;if(kk<gT){kk=gT}}}kk++;2s(!1a.fi(eM)){eM=1p.2m(eM/2)}fa=1a.dq/eM;fb=(1a.db-1a.df)/eM;fd=1a.df;fe=(1a.O===1g)?1a.mr-4:1a.O;fk=1a.bu+1a.dq;1q(eN=0;eN<=eM;eN++){kb=(1a.z&&eN===0);kc=(1a.B&&eN+1>eM);fl=1a.fm(1a.M);ff=(1a.G===\'2J\'&&1a.fc(fd,1a.G)!==fd)?1a.fc(fd,kk):fd.2V(1a.G);if(1a.el!==1g){ff=1a.ho(ff)}if(1f 1a.Q===\'1n\'){ff=1a.Q+ff}if(1f 1a.U===\'1n\'){ff=ff+1a.U}fg=1a.fh(ff,1a.M,1l,1l,eY);if(kb){eJ=fk-fl}1i if(kc){eJ=fk}1i{eJ=fk-fl/2}1a.hn(ff,fe-fg,eJ,1a.M,1l,1l,1l,eY,1a.E);fd+=fb;fk-=fa}}};1a.kl=1g;1a.km=1g;1a.kn=0;1a.ko=0;1a.kp=1g;1a.kq=1c(1w,1y,kr,ks){if(1f ks===\'1r\'){ks=1B}1w=1p.2m(1w);1y=1p.3G(1y);if(1p.2h(1w-1y)===0){--1w;++1y}kr=1p.2m(kr);1b kt=1p.2p((1a.kn/1T.0)*1p.2h(1y-1w));1b ku=1p.2p((1a.ko/1T.0)*1p.2h(1y-1w));if(1f 1a.kl===\'1h\'){1w=1p.3G(1a.kl);if(1w>=1y){34(\'6j 6b 5m a 1w 1J 5z k6() 6e is k7 5S 3O l6 1J 61 1q 3O 4H. 66 is 2Y 4Z.\');1d}}if(1f 1a.km===\'1h\'){1y=1p.3G(1a.km);if(1w>=1y){34(\'6j 6b 5m a 1y 1J 5z l3() 6e is i8 5S 3O i0 1J 61 1q 3O 4H. 66 is 2Y 4Z.\');1d}}if(1p.2h(1w-1y)===0){++1y;--1w}1w-=ku;1y+=kt;1b mD;1b kv;1b kw;1b kx;1b ky;1b kz;1b kA;1b kB;1b kC;1b kD;1b kE;1b kF;1b kG;if(ks){mD=1a.kH(kr,1w,1y,1);kv=mD[0];kB=mD[1];kC=mD[2];ky=mD[3]}1i{kB=1w;kC=1y;mD=1a.kI(kr,1w,1y,1);kv=mD[0];ky=mD[1]}if(1p.2h(1w-1y)>2){if(ks){mD=1a.kH(kr,1w,1y,5);kw=mD[0];kD=mD[1];kE=mD[2];kz=mD[3]}1i{kD=1w;kE=1y;mD=1a.kI(kr,1w,1y,5);kw=mD[0];kz=mD[1]}}1i{kw=8o}if(1p.2h(1w-1y)>5){if(ks){mD=1a.kH(kr,1w,1y,2);kx=mD[0];kF=mD[1];kG=mD[2];kA=mD[3]}1i{kF=1w;kG=1y;mD=1a.kI(kr,1w,1y,2);kx=mD[0];kA=mD[1]}}1i{kx=8o}1b kJ=1p.2h(kv-kr);1b kK=1p.2h(kw-kr);1b kL=(!1a.hT(kA)&&kA>1)?1p.2h(kx-kr):kL=8o;1b iD;if(kJ<kK){iD=(kJ<kL)?1:3}1i{iD=(kK<kL)?2:3}2r(iD){1A 1:1d[kB,kC,ky];1A 2:1d[kD,kE,kz];1A 3:1d[kF,kG,kA];2u:34(\'j0 r (pK) \');1d}};1a.jF=1c(1w,1y,kr,ks){if(1f ks===\'1r\'){ks=1B}if(1a.kp){1a.kq(1w,1y,kr,ks);1d}if(1p.2h(1w-1y)<0.92){if(1w===0&&1y===0){1w=-1;1y=1}1i{1b kM=(1p.2h(1y)+1p.2h(1w))*0.pA;1w-=kM;1y+=kM}}1b kt=(1a.kn/1T.0)*1p.2h(1y-1w);1b ku=(1a.ko/1T.0)*1p.2h(1y-1w);if(1f 1a.kl===\'1h\'){1w=1a.kl;if(1w>=1y){34(\'6j 6b 5m a 1w 1J 5z k6() 6e is k7 5S 3O l6 1J 61 1q 3O 4H. 66 is 2Y 4Z.\');1d}if(1p.2h(1w-1y)<0.92){1y*=1.2}}if(1f 1a.km===\'1h\'){1y=1a.km;if(1w>=1y){34(\'6j 6b 5m a 1y 1J 5z l3() 6e is i8 5S 3O i0 1J 61 1q 3O 4H. 66 is 2Y 4Z.\');1d}if(1p.2h(1w-1y)<0.92){1w*=0.8}}1w-=ku;1y+=kt;1b mD;1b kv;1b kw;1b kx;1b kB;1b kC;1b kD;1b kE;1b kF;1b kG;1b kN;1b ky;1b kO;1b kz;1b kP;1b kA;if(ks){mD=1a.kQ(kr,1w,1y,1,2);kv=mD[0];kB=mD[1];kC=mD[2];kN=mD[3];ky=mD[4]}1i{kB=1w;kC=1y;mD=1a.kR(kr,1w,1y,1,2,1g);kv=mD[0];kN=mD[1];ky=mD[2]}if(ks){mD=1a.kQ(kr,1w,1y,5,2);kw=mD[0];kD=mD[1];kE=mD[2];kO=mD[3];kz=mD[4]}1i{kD=1w;kE=1y;mD=1a.kR(kr,1w,1y,5,2,1g);kw=mD[0];kO=mD[1];kz=mD[2]}if(ks){mD=1a.kQ(kr,1w,1y,2,5);kx=mD[0];kF=mD[1];kG=mD[2];kP=mD[3];kA=mD[4]}1i{kF=1w;kG=1y;mD=1a.kR(kr,1w,1y,2,5,1g);kx=mD[0];kP=mD[1];kA=mD[2]}1b kJ=1p.2h(kv-kr);1b kK=1p.2h(kw-kr);1b kL=1p.2h(kx-kr);1b iD=1a.kS(kJ,kK,kL,0.8);2r(iD){1A 1:1d[kB,kC,ky];1A 2:1d[kD,kE,kz];1A 3:1d[kF,kG,kA];2u:34(\'j0 r (pD) \');1d}};1a.kQ=1c(kr,1w,1y,ib,he,ks){if(1f ks===\'1r\'){ks=1B}1b kT=1y-1w;1b kU=(kT===0)?0:1p.2m(1a.jn(kT,10));if(1w>0&&1w<1p.3y(10,kU)){1w=0}1b kV=1p.3y(10,kU)/ib;1b kW=kV/he;1b kX=1p.3G(1y/kW)*kW;1b kY=1p.2m(1w/kW)*kW;1b kZ=kX-kY;1b la=kZ/kV;2s(la>kr){kV=1p.3y(10,kU)/ib;la=kZ/kV;++kU}kW=kV/he;kY=1p.2m(1w/kW)*kW;kZ=kX-kY;if(ks){kY=1p.2m(1w/kV)*kV;kZ=kX-kY;kX=1p.3G(kZ/kV)*kV+kY}1i{kX=1p.3G(1y/kW)*kW}1d[la,kY,kX,kW,kV]};1a.kR=1c(kr,1w,1y,ib,he){1b kT=1y-1w;1b kU=(kT===0)?0:1p.2m(1a.jn(kT,10));1b kV=1p.3y(10,kU)/ib;1b kW=1p.kV/he;1b la=1p.2m(kT/kV);2s(la>kr){kV=1p.3y(10,kU)/ib;la=1p.2m(kT/kV);++kU}kW=kV/he;1d[la,kW,kV]};1a.kH=1c(kr,1w,1y,ib,ks){if(1f ks===\'1r\'){ks=1B}1b kT=1y-1w;if(kT===0){34(\'j9\\\'t i4 i6 i7 l7 1w == 1y.\');1d}1i{1b kU=1p.2m(1a.jn(kT,10))}if(1w>0&&1w<1p.3y(10,kU)){1w=0}if(kU===0){kU=1}1b kV=(ib===1)?1:1p.3y(10,kU)/ib;1b kX=1p.3G(1y/kV)*kV;1b kY=1p.2m(1w/kV)*kV;1b kZ=kX-kY;1b la=kZ/kV;2s(la>kr){kV=1p.3y(10,kU)/ib;la=kZ/kV;++kU}kY=1p.2m(1w/kV)*kV;kZ=kX-kY;if(ks){kY=1p.2m(1w/kV)*kV;kZ=kX-kY;kX=1p.3G(kZ/kV)*kV+kY}1i{kX=1p.3G(1y/kV)*kV}1d[la,kY,kX,kV]};1a.kI=1c(kr,1w,1y,ib){1b kT=1y-1w;if(kT===0){34(\'j9\\\'t i4 i6 i7 l7 1w == 1y.\');1d}1i{1b kU=1p.2m(1a.jn(kT,10))}if(kU===0){kU=1}1b kV=(ib===1)?1:1p.3y(10,kU)/ib;1b la=1p.2m(kT/kV);2s(la>kr){kV=1p.3y(10,kU)/ib;la=1p.2m(kT/kV);++kU}1d[la,kV]};1a.kS=1c(ib,he,hd,lb){if(ib<he){if(ib<hd*lb){1d 1}1d 3}1i if(he<hd*lb){1d 2}1d 3};1a.gA={lc:0,mF:"",le:8,gB:1c(hB){1d 1a.lf(1a.lg(1a.lh(hB),hB.1k*1a.le))},li:1c(hB){1d 1a.lj(1a.lg(1a.lh(hB),hB.1k*1a.le))},lk:1c(hB){1d 1a.ll(1a.lg(1a.lh(hB),hB.1k*1a.le))},lm:1c(ln,1D){1d 1a.lf(1a.lo(ln,1D))},lp:1c(ln,1D){1d 1a.lj(1a.lo(ln,1D))},lq:1c(ln,1D){1d 1a.ll(1a.lo(ln,1D))},lr:1c(){1d 1a.gB("pE")==="pC"},lg:1c(eI,eM){eI[eM>>5]|=pB<<((eM)%32);eI[(((eM+64)>>>9)<<4)+14]=eM;1b ib=pF;1b he=-pJ;1b hd=-pI;1b hc=pG;1q(1b eN=0;eN<eI.1k;eN+=16){1b ls=ib;1b lt=he;1b lu=hd;1b lv=hc;ib=1a.lw(ib,he,hd,hc,eI[eN+0],7,-pp);hc=1a.lw(hc,ib,he,hd,eI[eN+1],12,-pm);hd=1a.lw(hd,hc,ib,he,eI[eN+2],17,pn);he=1a.lw(he,hd,hc,ib,eI[eN+3],22,-pw);ib=1a.lw(ib,he,hd,hc,eI[eN+4],7,-py);hc=1a.lw(hc,ib,he,hd,eI[eN+5],12,pv);hd=1a.lw(hd,hc,ib,he,eI[eN+6],17,-ps);he=1a.lw(he,hd,hc,ib,eI[eN+7],22,-pu);ib=1a.lw(ib,he,hd,hc,eI[eN+8],7,q0);hc=1a.lw(hc,ib,he,hd,eI[eN+9],12,-pZ);hd=1a.lw(hd,hc,ib,he,eI[eN+10],17,-pX);he=1a.lw(he,hd,hc,ib,eI[eN+11],22,-pY);ib=1a.lw(ib,he,hd,hc,eI[eN+12],7,q2);hc=1a.lw(hc,ib,he,hd,eI[eN+13],12,-q6);hd=1a.lw(hd,hc,ib,he,eI[eN+14],17,-q7);he=1a.lw(he,hd,hc,ib,eI[eN+15],22,q5);ib=1a.lx(ib,he,hd,hc,eI[eN+1],5,-q3);hc=1a.lx(hc,ib,he,hd,eI[eN+6],9,-q4);hd=1a.lx(hd,hc,ib,he,eI[eN+11],14,pW);he=1a.lx(he,hd,hc,ib,eI[eN+0],20,-pO);ib=1a.lx(ib,he,hd,hc,eI[eN+5],5,-pP);hc=1a.lx(hc,ib,he,hd,eI[eN+10],9,pN);hd=1a.lx(hd,hc,ib,he,eI[eN+15],14,-pL);he=1a.lx(he,hd,hc,ib,eI[eN+4],20,-pM);ib=1a.lx(ib,he,hd,hc,eI[eN+9],5,pU);hc=1a.lx(hc,ib,he,hd,eI[eN+14],9,-pT);hd=1a.lx(hd,hc,ib,he,eI[eN+3],14,-pR);he=1a.lx(he,hd,hc,ib,eI[eN+8],20,pS);ib=1a.lx(ib,he,hd,hc,eI[eN+13],5,-pl);hc=1a.lx(hc,ib,he,hd,eI[eN+2],9,-oO);hd=1a.lx(hd,hc,ib,he,eI[eN+7],14,oP);he=1a.lx(he,hd,hc,ib,eI[eN+12],20,-oN);ib=1a.ly(ib,he,hd,hc,eI[eN+5],4,-oL);hc=1a.ly(hc,ib,he,hd,eI[eN+8],11,-oM);hd=1a.ly(hd,hc,ib,he,eI[eN+11],16,oQ);he=1a.ly(he,hd,hc,ib,eI[eN+14],23,-oU);ib=1a.ly(ib,he,hd,hc,eI[eN+1],4,-oV);hc=1a.ly(hc,ib,he,hd,eI[eN+4],11,oT);hd=1a.ly(hd,hc,ib,he,eI[eN+7],16,-oS);he=1a.ly(he,hd,hc,ib,eI[eN+10],23,-oK);ib=1a.ly(ib,he,hd,hc,eI[eN+13],4,oC);hc=1a.ly(hc,ib,he,hd,eI[eN+0],11,-oD);hd=1a.ly(hd,hc,ib,he,eI[eN+3],16,-oB);he=1a.ly(he,hd,hc,ib,eI[eN+6],23,oz);ib=1a.ly(ib,he,hd,hc,eI[eN+9],4,-oE);hc=1a.ly(hc,ib,he,hd,eI[eN+12],11,-oI);hd=1a.ly(hd,hc,ib,he,eI[eN+15],16,oH);he=1a.ly(he,hd,hc,ib,eI[eN+2],23,-pe);ib=1a.lz(ib,he,hd,hc,eI[eN+0],6,-pb);hc=1a.lz(hc,ib,he,hd,eI[eN+7],10,pk);hd=1a.lz(hd,hc,ib,he,eI[eN+14],15,-ph);he=1a.lz(he,hd,hc,ib,eI[eN+5],21,-oZ);ib=1a.lz(ib,he,hd,hc,eI[eN+12],6,oY);hc=1a.lz(hc,ib,he,hd,eI[eN+3],10,-oW);hd=1a.lz(hd,hc,ib,he,eI[eN+10],15,-oX);he=1a.lz(he,hd,hc,ib,eI[eN+1],21,-p8);ib=1a.lz(ib,he,hd,hc,eI[eN+8],6,p6);hc=1a.lz(hc,ib,he,hd,eI[eN+15],10,-p4);hd=1a.lz(hd,hc,ib,he,eI[eN+6],15,-rc);he=1a.lz(he,hd,hc,ib,eI[eN+13],21,r8);ib=1a.lz(ib,he,hd,hc,eI[eN+4],6,-rh);hc=1a.lz(hc,ib,he,hd,eI[eN+11],10,-rf);hd=1a.lz(hd,hc,ib,he,eI[eN+2],15,r6);he=1a.lz(he,hd,hc,ib,eI[eN+9],21,-qY);ib=1a.lA(ib,ls);he=1a.lA(he,lt);hd=1a.lA(hd,lu);hc=1a.lA(hc,lv)}1d[ib,he,hd,hc]},lB:1c(lC,ib,he,eI,hB,gR){1d 1a.lA(1a.lD(1a.lA(1a.lA(ib,lC),1a.lA(eI,gR)),hB),he)},lw:1c(ib,he,hd,hc,eI,hB,gR){1d 1a.lB((he&hd)|((~he)&hc),ib,he,eI,hB,gR)},lx:1c(ib,he,hd,hc,eI,hB,gR){1d 1a.lB((he&hc)|(hd&(~hc)),ib,he,eI,hB,gR)},ly:1c(ib,he,hd,hc,eI,hB,gR){1d 1a.lB(he^hd^hc,ib,he,eI,hB,gR)},lz:1c(ib,he,hd,hc,eI,hB,gR){1d 1a.lB(hd^(he|(~hc)),ib,he,eI,hB,gR)},lo:1c(ln,1D){1b lE=1a.lh(ln);if(lE.1k>16){lE=1a.lg(lE,ln.1k*1a.le)}1b lF=[16],lG=[16];1q(1b eN=0;eN<16;eN++){lF[eN]=lE[eN]^qZ;lG[eN]=lE[eN]^qX}1b 9q=1a.lg(lF.8a(1a.lh(1D)),9p+1D.1k*1a.le);1d 1a.lg(lG.8a(9q),9p+qV)},lA:1c(eI,eJ){1b lH=(eI&8b)+(eJ&8b);1b lI=(eI>>16)+(eJ>>16)+(lH>>16);1d(lI<<16)|(lH&8b)},lD:1c(lJ,lK){1d(lJ<<lK)|(lJ>>>(32-lK))},lh:1c(lL){1b lM=[];1b lN=(1<<1a.le)-1;1q(1b eN=0;eN<lL.1k*1a.le;eN+=1a.le){lM[eN>>5]|=(lL.5Q(eN/1a.le)&lN)<<(eN%32)}1d lM},ll:1c(lM){1b lL="";1b lN=(1<<1a.le)-1;1q(1b eN=0;eN<lM.1k*32;eN+=1a.le){lL+=1K.aI((lM[eN>>5]>>>(eN%32))&lN)}1d lL},lf:1c(lO){1b lP=1a.lc?"qW":"r0";1b lL="";1q(1b eN=0;eN<lO.1k*4;eN++){lL+=lP.4r((lO[eN>>2]>>((eN%4)*8+4))&aN)+lP.4r((lO[eN>>2]>>((eN%4)*8))&aN)}1d lL},lj:1c(lO){1b lQ="r5+/";1b lL="";1q(1b eN=0;eN<lO.1k*4;eN+=3){1b lR=(((lO[eN>>2]>>8*(eN%4))&8d)<<16)|(((lO[eN+1>>2]>>8*((eN+1)%4))&8d)<<8)|((lO[eN+2>>2]>>8*((eN+2)%4))&8d);1q(1b jB=0;jB<4;jB++){if(eN*8+jB*6>lO.1k*32){lL+=1a.mF}1i{lL+=lQ.4r((lR>>6*(3-jB))&r1)}}}1d lL}};1a.eA={9n:\'1x: 3D 3N 1I be 1Z (1B/1g)\',3b:\'1x: rB 3N 1I be 1Z (1B/1g)\',7m:\'1x: 51 2A 1I be 1n\',7B:\'1x: 51 2Z 4y 1I be 1n\',7A:\'1x: 51 2Z 7o 1I be 1n\',9A:\'1x: 51 3E 1I be a 1h 7T 0 4A 90\',7y:\'1x: 9B 1h of 7F 2Z 1I be a 1h rC 5S 1\',e1:\'1x: 51 1o 1I be a 1h\',d7:\'1x: ry 2k 2A 1I be 1n\',e8:\'1x: b4 4n 1o 1I be a 1h\',d1:\'1x: b4 rz rD 1I be a 1h 7T 0 4A 1T\',h5:\'1x: 87 2Z 3N 1I be 1Z (1B/1g)\',f1:\'1x: 87 2Z 4y 1I be 1n\',h1:\'1x: 87 2Z 7o 1I be 1n\',aR:\'1x: rI 72 1k 1I rG 1D 1k in 1A of 2T 4A 2i rE\',b5:\'1x: rF 1D 2Y 72\',d9:\'1x: aV 1D 2Y 72\',e2:\'1x: aV 1D in rx 4K 1q rn 68 2e\',7x:\'1x: ro 3N 1I be a 1h\',k5:\'1x: 6u rl 3N 1I be 1Z (1B/1g)\',6y:\'1x: 67 rj 3N 1I be 1Z (1B/1g)\',f7:\'1x: 7L 33 1I be a 1h\',f6:\'1x: 7L 3F 1I be a 1n\',h0:\'1x: 7L 1L 1o 1I be a 1h\',2M:\'1x: 5p 1Y 5g rp 1I be rv\',2K:\'1x: 5p 1Y ru 1I be 4V\',i2:\'1x: rq 3N 1I be 1Z (1B/1g)\',5W:\'1x: qU 2Y 1n\',5N:\'1x: 5p 7K qo 1I be 4V\',9j:\'1x: 3S qp 44\',7J:\'1x: 3S 2i 1h\',29:\'1x: 5p 1j 2Z 1I be in qn 4K (#98 or #qm)\',ax:\'1x: qq 2Y a 1c\',a4:\'1x: 3S 7K on 7F X\',a2:\'1x: 3S 7K on 7F Y\',7j:\'1x: 3S 2v 4K\',ao:\'1x: 3S 2T 3E\',aj:\'1x: 3S 7v 4K\',b3:\'1x: 3S 2Z 4K\',7M:\'1x: 7E 2r 1I be 1Z (1B/1g)\',ac:\'1x: 7E 1G 1I be 1n (qt qr 1q 2Z) or qs 1h 9L\',6w:\'1x: 7E 1Q 2Y 1n\',as:\'1x: qk 1o 1I be a 1h\',ay:\'1x: 7R 1z qb\',7i:\'1x: 7R 1D ag\',aK:\'1x: q9 qa 1D to qe 68\',qi:\'1x: qj 6a or qh\',am:\'1x: qf 1I be 1n 4A qM to an qN qL\',qK:\'1x: 7R 7v 5z 1a id\',aC:\'1x: 5B 2e 2Y qT\',aG:\'1x: 5B is 2Y qR 2e\',qP:\'1x: 5B is 2Y 1L 2e\',aH:\'1x: 5B is 2Y 2T 2e\',3B:\'1x: qI 2l 1I be a 1h 7T 0 (qA) 4A 1 (qy)\',qx:\'1x: qH 3U 2Y 72\',37:\'1x: qF 2Z 1I be 4V\',5K:\'1x: 9B 6Y of qD qE 3O 1z 1t\',a9:\'1x: 4U 1G 9L 1I be 4V\',m7:\'1x: 4U 9J 1Y 1t 1I be a 1h\',88:\'1x: 4U 9J 33 1I be a 1h\',mX:\'1x: 4U 2Z 33 1I be a 1h\',9v:\'1x: 4U 2Z 4y 1I be 1n\',9N:\'1x: 4U 2Z 7o 1I be 1n\',c8:\'1x: n5 id 4y 1I be 1n\',7q:\'1x: 5p 58 n1 1I be 4V\',9O:\'1x: 51 5Y 3N 1I be 1Z (1B/1g)\',7N:\'1x: 67 1t 2q 1I be 4V\',7f:\'1x: mM 1I be a 1h\',af:\'1x: aJ 1I be 1n\',9F:\'1x: aJ 1G 1I be 1n (5n, 2a or 3p)\',a5:\'1x: 4W 4n 1I be 1n\',ah:\'1x: 4W 1Y 5g 1I be 1n\',aT:\'1x: 4W 33 1I be a 1h\',mN:\'1x: 4W id 1I be a 1h\',b0:\'1x: 4W 6f 1I be 1n\',aU:\'1x: 4W 1G 1I be 1n\',b6:\'1x: a3 7v 1G, 4Z 2Z az nw, ne, sw 4A se\',do:\'1x: 67 2v 1I be 1n\',l8:\'1x: 67 2v 1G 1I be 1n\',l5:\'1x: a3 mR 2v 1G, 4Z 2Z az nw, ne, sw 4A se\',4X:\'1x: mQ 1I be a 1h\',7O:\'1x: mO 4B 3N 1I be 1Z (1B/1g)\',ak:\'1x 3I/2F: 62 1D 3U\',mJ:\'1x 3I/2F: 62 or 6a 3u\',mI:\'1x 3I/2F: 62 or 6a 68 2A\',au:\'1x 3I/2F: 62 or 6a 68 2e\',5X:\'1x 3I/2F: mW 2Y ag or m5 1j/1D/6x 3U\',ad:\'1x 3I/2F: 7Q 1j 3U\',6o:\'1x 3I/2F: 7Q 1D 3U\',aZ:\'1x 3I/2F: 7Q 6x 3U\',6v:\'1x 3I/2F: o5 4K\'}}1c 1x(2A,2e,3Y,ln,4N){1a.lS=2D me();1b lS=1a.lS;1b lT;if(1f ln===\'1Z\'){4N=ln}ln=\'\';if(1f 3Y===\'1r\'){3Y=\'\'}lS.iH(2A,2e.3e(),3Y,ln,4N);lS.ez=1c(38){if(lS.dm){34(lS.eA[38])}};1a.85=1c(lU){if(lS.bh.1k===0){lS.ez(\'7i\');1d}if(!lS.fZ(lU)){lS.ez(\'b5\');1d}if(lS.bh.1k!==lU.1k&&lS.eb!==\'1L\'){lS.ez(\'aR\');1d}lS.bd=lU};1a.o8=1c(lU){if(lS.eb===\'2i\'){1a.85(lU)}1i{lS.ez(\'aG\')}};1a.o6=1c(lU){if(lS.eb===\'2T\'){1a.85(lU)}1i{lS.ez(\'aH\')}};1a.nR=1c(){if(lS.bh.1k===0){lS.ez(\'7i\');1d}if(((lS.bh.1k===1&&!lS.dg)||(lS.bh[0].1k===1&&lS.dg))&&lS.eb===\'1L\'){lS.ez(\'aK\');1d}lS.fX()};1a.nW=1c(){1d lS.bi};1a.4N=1c(eI,eJ){if(1f eI!==\'1h\'||1f eJ!==\'1h\'){lS.ez(\'7N\');1d}lS.iJ(eI,eJ);lS.fX()};1a.ob=1c(lV){if(1f lV!==\'1Z\'){lS.ez(\'9n\');1d}lS.mf=lV};1a.ot=1c(fd,fG){if(1f fd!==\'1n\'||!lS.iN(fG)){lS.ez(\'9j\');1d}lS.mi[fd]=fG};1a.oo=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.mw=1S;lS.A=1S};1a.op=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.z=1S;lS.B=1S};1a.ox=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.mw=1S};1a.oy=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.z=1S};1a.ow=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.A=1S};1a.ou=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.B=1S};1a.ov=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.f=1j};1a.og=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.mj=1j;lS.mk=1j};1a.oh=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.mj=1j};1a.od=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.mk=1j};1a.om=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.ml=ff;lS.mm=ff};1a.nO=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.ml=ff};1a.nk=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.mm=ff};1a.nr=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.k=1E(1t);lS.mn=1E(1t)};1a.np=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.k=1E(1t)};1a.nn=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.mn=1E(1t)};1a.n6=1c(2A){if(1f 2A!==\'1n\'){lS.ez(\'7m\');1d}lS.m=2A};1a.n7=1c(2A){if(1f 2A!==\'1n\'){lS.ez(\'7m\');1d}lS.mo=2A};1a.nb=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}if(lS.u+1h>=lS.ds){lS.ez(\'5K\');1d}lS.mp=1E(1h)};1a.nc=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}if(1h+lS.mt>=lS.dr){lS.ez(\'5K\');1d}lS.mr=1E(1h)};1a.ns=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}if(lS.mr+1h>=lS.dr){lS.ez(\'5K\');1d}lS.mt=1E(1h)};1a.nH=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}if(1h+lS.mp>=lS.ds){lS.ez(\'5K\');1d}lS.u=1E(1h)};1a.nG=1c(5Y){if(1f 5Y!==\'1Z\'){lS.ez(\'9O\');1d}if(lS.eb===\'2i\'){lS.mv=5Y}};1a.m4=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'9A\');1d}if(1h<0){1h=0}if(1h>89.9){1h=89.9}lS.C=1h};1a.nt=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.D=1j;lS.E=1j};1a.nB=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.D=1j};1a.nz=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.E=1j};1a.nA=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'4X\');1d}lS.F=1h;lS.G=1h};1a.nD=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'4X\');1d}lS.F=1h};1a.nC=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'4X\');1d}lS.G=1h};1a.nu=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.J=ff;lS.K=ff};1a.nv=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.J=ff};1a.nK=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.K=ff};1a.nJ=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.L=1E(1t);lS.M=1E(1t)};1a.nL=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.L=1E(1t)};1a.nN=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.M=1E(1t)};1a.nM=1c(1h){if(1f 1h!==\'1h\'&&1h>1){lS.ez(\'7y\');1d}lS.V=1h};1a.nF=1c(1h){if(1f 1h!==\'1h\'&&1h>1){lS.ez(\'7y\');1d}lS.W=1h};1a.nf=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.N=1E(1h)};1a.na=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.O=1E(1h)};1a.n9=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'7B\');1d}lS.P=ff};1a.nm=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'7B\');1d}lS.Q=ff};1a.ni=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'7A\');1d}lS.T=ff};1a.nh=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'7A\');1d}lS.U=ff};1a.nl=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'e1\');1d}lS.X=1h};1a.oj=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.ba=1j};1a.oi=1c(3v){if(1f 3v!==\'1n\'){lS.ez(\'d7\');1d}lS.Y=3v};1a.ok=1c(1j,hB){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}if(1f hB===\'1r\'){lS.bw=1j}1i if(1f lS.bv===\'1n\'){lS.bv=[];lS.bv[hB-1]=1j}1i{lS.bv[hB-1]=1j}};1a.ol=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'e8\');1d}lS.bx=1E(1h)};1a.oc=1c(1j,hB){if(lS.eb!==\'2i\'){1d 1g}if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}if(1f hB===\'1r\'){lS.bz=1j}1i if(1f lS.by===\'1n\'){lS.by=[];lS.by[hB-1]=1j}1i{lS.by[hB-1]=1j;if(1f lS.cV[hB-1]!==\'1r\'){lS.cV[hB-1][0]=1j}}};1a.oe=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'7x\');1d}lS.bB=1h};1a.oq=1c(fD,hB){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}if(1f hB===\'1r\'){lS.bA=fD}1i if(1f lS.bC===\'1h\'){lS.bC=[];lS.bC[hB-1]=fD}1i{lS.bC[hB-1]=fD}};1a.os=1c(1h){if(1f 1h!==\'1h\'||(1h<0||1h>1T)){lS.ez(\'d1\');1d}lS.bE=1E(1h)/2};1a.aL=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'7f\');1d}1h=1T-1h;if(1h<1){1h=1}if(1h>1T){1h=1T}lS.bF=1E(1h)};1a.nQ=1c(jA){if(1f jA!==\'1Z\'){lS.ez(\'h5\');1d}lS.bG=jA};1a.nS=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.bH=1j};1a.o7=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'4X\');1d}lS.bI=1h};1a.oa=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.bJ=ff};1a.o0=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.bK=1E(1t)};1a.o3=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'f1\');1d}lS.bL=ff};1a.mG=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'h1\');1d}lS.bM=ff};1a.n4=1c(4y){if(1f 4y!==\'1n\'){lS.ez(\'c8\');1d}lS.bc=4y};1a.mK=1c(1D,id,5k){if(lS.cv){1d}if(!lS.fZ(1D)){lS.ez(\'d9\');1d}lS.eb=2e.3e();1b eN;1b eM;if((1f 1D[0][0]===\'1n\'||5k===1B)&&lS.eb===\'1L\'){if(lS.bh.1k===0){1q(eN=0,eM=1D.1k;eN<eM;eN++){1a.7h([eN,1K(1D[eN][0]),\'x-1J\']);lS.bj[1D[eN][0]]=eN;1D[eN][0]=eN}}1i{1b 1w=lS.ii();1b 1y=lS.mC();1b fa=1p.2p((1y-1w)/(1D.1k-1));1q(1b jB=0,eN=1w,eM=1D.1k;eN<1y,jB<eM;eN+=fa,jB++){1a.7h([eN,1K(1D[jB][0])],\'x-1J\');lS.bj[1D[jB][0]]=eN;1D[jB][0]=eN}}1a.99(1g);1a.bV=1B}if(!lS.iK(1D)){lS.ez(\'e2\');1d}if(1f id!==\'1r\'&&id!==1l&&1f id!==\'1n\'){lS.ez(\'5W\');1d}if(lS.eb===\'1L\'){lS.dg=1B;1b lW=1g;if(lS.bh===[]){lS.bh=2D 41(1D)}1i{1q(1b eI in lS.bi){if(lS.bi[eI]===id){lS.bh[eI]=1D;lW=1B}}if(!lW){lS.bh[lS.bh.1k]=1D}}if(!lW){1b 1V=lS.bh.1k-1;lS.bi[1V]=(1f id===\'1r\'||id===1l)?\'h9\'+1V:id;lS.ct[1V]=1g;if(1f lS.bP[1V]===\'1r\'){lS.bP[1V]=lS.bQ}if(1f lS.bT[1V]===\'1r\'){lS.bT[1V]=lS.bR}if(1f lS.bW[1V]===\'1r\'){lS.bW[1V]=lS.bS}lS.cX.1s([lS.bQ,lS.bi[1V],lS.bi[1V]])}}1i if(lS.eb===\'2i\'){eM=1D.1k;1b hB=0;1q(eN=0;eN<eM;eN++){if(hB<1D[eN].1k){hB=1D[eN].1k}}1q(eN=1;eN<hB;eN++){lS.cV.1s([lS.bz,1K(eN),eN]);lS.ct.1s(1g)}lS.bh=1D}1i{lS.bh=1D}};1a.mT=1c(jh,ff){if(lS.cv){1d}1b iS=lS.jg(jh,ff);1b iT=lS.iR(iS);1b iW=lS.jo(iT);if(lS.fZ(iW)&&iW.1k>0){1b eM=iW.1k;1b hj;1b gQ;1b lX;1q(1b eN=0;eN<eM;eN++){if(iW[eN].1k<3){lX=iW[eN][0]+\'(\'+iW[eN][1]+\')\'}1i{lX=iW[eN][0]+\'(\'+iW[eN][1]+\', "\'+iW[eN][2]+\'")\'}3A("1a."+lX)}}};1a.mZ=1c(jh,ff){if(lS.cv){1d}1b iW=lS.jo(lS.jj(jh,ff));if(lS.fZ(iW)&&iW.1k>0){1b eM=iW.1k;1b hj;1b gQ;1b lX;1q(1b eN=0;eN<eM;eN++){if(iW[eN].1k<3){lX=iW[eN][0]+\'(\'+iW[eN][1]+\')\'}1i{lX=iW[eN][0]+\'(\'+iW[eN][1]+\', "\'+iW[eN][2]+\'")\'}3A("1a."+lX)}}};1a.vo=1c(lY){if(1f lY!==\'1Z\'){lS.ez(\'k5\');1d}lS.dm=lY};1a.vs=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.bm=1j};1a.uA=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.bn=1j};1a.ul=1c(fE){if(1f fE!==\'1h\'){lS.ez(\'f7\');1d}lS.bo=1E(fE)};1a.uY=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.bp=fD};1a.v3=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'f6\');1d}lS.br=ff};1a.uJ=1c(iv){if(1f iv!==\'1h\'){lS.ez(\'7q\');1d}lS.bq=1E(iv)};1a.uQ=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'h0\');1d}lS.bs=1E(1h)};1a.uL=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.dv=ff};1a.uG=1c(hq){if(1f hq!==\'1Z\'){lS.ez(\'6y\');1d}lS.bN=hq;lS.bO=hq};1a.uE=1c(hq){if(1f hq!==\'1Z\'){lS.ez(\'6y\');1d}lS.bN=hq};1a.uK=1c(hq){if(1f hq!==\'1Z\'){lS.ez(\'6y\');1d}lS.bO=hq};1a.v6=1c(2v){if(1f 2v!==\'1n\'){lS.ez(\'do\');1d}lS.ee=2v};1a.uT=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.ef=1j};1a.uW=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.eg=ff};1a.uX=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.eh=1E(1t)};1a.ug=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.ei=fD};1a.um=1c(1G){if(1f 1G!==\'1n\'){lS.ez(\'l8\');1d}1b eM=lS.dX.1k;1q(1b eN=0;eN<eM;eN++){if(lS.dX[eN]===1G){lS.ej=1G;1d 1B}}lS.ez(\'l5\');1d};1a.uk=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.ek=1j};1a.ua=1c(lZ){if(1f lZ!==\'1Z\'){lS.ez(\'i2\');1d}lS.co=lZ};1a.uf=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cp=1j;lS.cq=1j};1a.ue=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cp=1j};1a.ud=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cq=1j};1a.ux=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.cr=fD;lS.cs=fD};1a.uw=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.cr=fD};1a.uy=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.cs=fD};1a.9Z=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'5N\');1d}lS.cw=1h};1a.9M=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'5N\');1d}lS.cx=1h};1a.9V=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'5N\');1d}lS.cy=1h};1a.9P=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'5N\');1d}lS.cz=1h};1a.uo=1c(1M,ma){1a.9V(1M);1a.9Z(ma)};1a.uu=1c(1M,ma){1a.9P(1M);1a.9M(ma)};1a.us=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cA=1S;lS.cC=1S};1a.vr=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cB=1S;lS.cD=1S};1a.vz=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cA=1S};1a.vv=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cB=1S};1a.vb=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cC=1S};1a.vf=1c(1S){if(1f 1S!==\'1Z\'){lS.ez(\'3b\');1d}lS.cD=1S};1a.vh=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cE=1j;lS.cF=1j};1a.vi=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cE=1j};1a.vd=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cF=1j};1a.vq=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.cG=ff;lS.cH=ff};1a.v9=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.cG=ff};1a.vj=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.cH=ff};1a.vg=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.cI=1E(1t);lS.cJ=1E(1t)};1a.vm=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.cI=1E(1t)};1a.vc=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.cJ=1E(1t)};1a.vn=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.cK=1E(1h)};1a.sA=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.cL=1E(1h)};1a.7h=1c(2v){if(!lS.fZ(2v)||2v.1k<2||2v.1k>3){lS.ez(\'7j\');1d}if(lS.eb===\'1L\'&&1f 2v[0]===\'1n\'){if(1f lS.bj[2v[0]]!==\'1r\'){2v[0]=lS.bj[2v[0]]}}lS.cM.1s(2v)};1a.sz=1c(2v){if(!lS.fZ(2v)||2v.1k!==2){lS.ez(\'7j\');1d}lS.cN.1s(2v)};1a.sF=1c(1j,1Q){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}if(1f 1Q!==\'1n\'){lS.ez(\'6w\');1d}lS.cU.1s([1j,1Q,\'83\'])};1a.sC=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cO=1j};1a.su=1c(jT){if(1f jT!==\'1Z\'){lS.ez(\'7M\');1d}lS.cP=jT};1a.sn=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.cQ=ff};1a.sl=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.cR=1E(1t)};1a.so=1c(2i,1Q){if(1f 2i!==\'1h\'){lS.ez(\'7J\');1d}2i--;if(2i<0||2i>=lS.cV.1k){lS.ez(\'7J\');1d}if(1f 1Q!==\'1n\'){lS.ez(\'6w\');1d}lS.cV[2i]=[(1f lS.by[2i]===\'1r\')?lS.bz:lS.by[2i],1Q]};1a.sS=1c(id,1Q){if(1f id!==\'1r\'&&1f id!==\'1n\'){lS.ez(\'5W\');1d}if(1f 1Q!==\'1n\'){lS.ez(\'6w\');1d}1b eM=lS.bi.1k;1q(1b eN=0;eN<eM;eN++){if(1f lS.bi[eN]!==\'1r\'&&lS.bi[eN]===id){lS.cX[eN][1]=1Q;1F}}};1a.sP=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.cS=1h};1a.sV=1c(fe,fk){if(1f fe===\'1n\'){lS.cT=fe}1i if(1f fe===\'1h\'&&1f fk===\'1h\'){lS.cT=[fe,fk]}1i{lS.ez(\'ac\');1d}};1a.sT=1c(jT){if(1f jT!==\'1Z\'){lS.ez(\'7M\');1d}lS.cW=jT};1a.sO=1c(1j,id){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}if(1f id!==\'1r\'&&1f id!==\'1n\'){lS.ez(\'5W\');1d}if(1f id===\'1r\'){if(lS.bP.1k===1){lS.bP[0]=1j}1i{1b eM=lS.bi.1k;1q(1b eN=0;eN<eM;eN++){if(1f lS.bP[eN]!==\'1r\'){lS.bP[eN]=1j}}}}1i{if(lS.bi.1k<2){lS.bP[0]=1j}1i{1q(1b ln in lS.bi){if(lS.bi[ln]===id){lS.bP[ln]=1j;lS.cX[ln][0]=1j}}}}};1a.sI=1c(fD,id){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}if(1f id===\'1r\'){if(lS.bT.1k===1){lS.bT[0]=fD}1i{1b eM=lS.bi.1k;1q(1b eN=0;eN<eM;eN++){if(1f lS.bP[eN]!==\'1r\'){lS.bT[eN]=fD}}}}1i{if(lS.bi.1k<2){lS.bT[0]=fD}1i{1b 1V=1g;1q(1b ln in lS.bi){if(lS.bi[ln]===id){1V=ln;1F}}if(1V!==1g){lS.bT[1V]=fD}}}};1a.aM=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'7f\');1d}1h=(1T-1h)*10;if(1h<1){1h=1}if(1h>3J){1h=3J}lS.bU=1E(1h)};1a.sG=1c(1h,id){if(1f 1h!==\'1h\'){lS.ez(\'as\');1d}1h=1E(1h);if(1f id===\'1r\'){if(lS.bW.1k===1){lS.bW[0]=1h}1i{1b eM=lS.bi.1k;1q(1b eN=0;eN<eM;eN++){if(1f lS.bW[eN]!==\'1r\'){lS.bW[eN]=1h}}}}1i{if(lS.bi.1k<2){lS.bW[0]=1h}1i{1b 1V=1g;1q(1b ln in lS.bi){if(lS.bi[ln]===id){1V=ln;1F}}if(1V!==1g){lS.bW[1V]=1h}}}};1a.sM=1c(1h){if(1f 1h!==\'1h\'||1h<0||1h>89){lS.ez(\'ao\');1d}lS.bX=1h};1a.sL=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'7x\');1d}if(1h<1){1h=1}lS.bY=1h};1a.sj=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.bZ=1E(fD)};1a.rS=1c(eI,eJ){if(1f eI!==\'1h\'||1f eJ!==\'1h\'){lS.ez(\'a9\');1d}lS.ca=1E(eI);lS.cb=1E(eJ)};1a.rZ=1c(iv){if(1f iv!==\'1h\'){lS.ez(\'7q\');1d}lS.cc=1E(iv)};1a.rX=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.cd=1j};1a.rL=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.ce=ff};1a.rM=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.cf=1E(1t)};1a.rJ=1c(fE){if(1f fE!==\'1h\'){lS.ez(\'88\');1d}lS.cg=1E(fE)};1a.rK=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.mx=1j};1a.rP=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'4X\');1d}lS.ci=1h};1a.rN=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.cj=ff};1a.sb=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.ck=1E(1t)};1a.s9=1c(fE){if(1f fE!==\'1h\'){lS.ez(\'88\');1d}lS.cl=1E(fE)};1a.sa=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'9v\');1d}lS.cm=ff};1a.si=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'9N\');1d}lS.cn=ff};1a.99=1c(4B){if(1f 4B!==\'1Z\'){lS.ez(\'7O\');1d}lS.R=4B};1a.sd=1c(4B){if(1f 4B!==\'1Z\'){lS.ez(\'7O\');1d}lS.S=4B};1a.sf=1c(eI,eJ){if(1f eI!==\'1h\'||1f eJ!==\'1h\'){lS.ez(\'7N\');1d}lS.iJ(eI,eJ)};1a.s3=1c(1h){lS.aL(1h);if(1f 1h===\'1h\'){lS.aM(1h)}};1a.s0=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.dC=1E(1h)};1a.s1=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.dE=1E(1h)};1a.s6=1c(1h){if(1f 1h!==\'1h\'){lS.ez(\'37\');1d}lS.dG=1E(1h)};1a.s4=1c(7S){if(1f 7S!==\'1n\'){lS.ez(\'af\');1d}lS.dI=7S};1a.s5=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.dJ=1j};1a.tM=1c(ff){if(1f ff!==\'1n\'){lS.ez(\'2M\');1d}lS.dK=ff};1a.tN=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.dL=1E(1t)};1a.tK=1c(gy){if(1f gy!==\'1n\'){lS.ez(\'9F\');1d}lS.dM=gy};1a.tQ=1c(fC,fG){if(!lS.fZ(fC)||fC.1k<1||fC.1k>3){lS.ez(\'aj\');1d}if(lS.eb!==\'2T\'){1b 1L=(1f fC[2]===\'1r\')?\'46\':fC[2];if(1f lS.dY[1L]===\'1r\'){lS.dY[1L]={}}if(lS.eb===\'1L\'&&1f fC[0]===\'1n\'){if(1f lS.bj[fC[0]]!==\'1r\'){lS.dY[1L][lS.bj[fC[0]]]=fC}}1i{lS.dY[1L][fC[0]]=fC}}1i{lS.dY[fC[0]]=fC}if(1f fG!==\'1r\'){if(!lS.iN(fG)){lS.ez(\'ax\');1d}if(lS.eb!==\'2T\'){if(lS.eb===\'1L\'&&1f fC[0]===\'1n\'){if(1f lS.bj[fC[0]]!==\'1r\'){lS.dY[1L][lS.bj[fC[0]]][\'3q\']=fG}}1i{lS.dY[1L][fC[0]][\'3q\']=fG}}1i{lS.dY[fC[0]][\'3q\']=fG}}};1a.tO=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.dN=1j};1a.tJ=1c(mb){if(1f mb!==\'1n\'){lS.ez(\'a5\');1d}lS.dO=mb};1a.tD=1c(1j){if(1f 1j!==\'1n\'||lS.fL(1j)===1g){lS.ez(\'29\');1d}lS.dQ=1j};1a.tB=1c(1Y){if(1f 1Y!==\'1n\'){lS.ez(\'ah\');1d}lS.dR=1Y};1a.tH=1c(1t){if(1f 1t!==\'1h\'){lS.ez(\'2K\');1d}lS.dS=1E(1t)};1a.tF=1c(fD){if(1f fD!==\'1h\'||(fD<0||fD>1)){lS.ez(\'3B\');1d}lS.dU=1E(fD)};1a.u3=1c(mb){if(1f mb!==\'1n\'){lS.ez(\'b0\');1d}lS.dV=mb};1a.u1=1c(fE){if(1f fE!==\'1h\'){lS.ez(\'aT\');1d}lS.dT=1E(fE)};1a.u7=1c(1G){if(1f 1G!==\'1n\'){lS.ez(\'aU\');1d}1b eM=lS.dX.1k;1q(1b eN=0;eN<eM;eN++){if(lS.dX[eN]===1G){lS.dW=1G;1d 1B}}lS.ez(\'b6\');1d};1a.u6=1c(iL){if(iL!==\'.\'&&iL!==\',\'&&iL!==1g){lS.ez(\'b3\');1d}lS.el=iL}}1c ge(){}ge.mc=1c(hB){1b iD="4I";1b md=2D 41("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");1q(1b eN=0;eN<hB.1k;eN++){iD+=md[hB.5Q(eN)>>4]+md[hB.5Q(eN)&tV]}1d iD};ge.gf=1c(ha){1b iD="";1q(1b eN=(ha.2G(0,2)=="4I")?2:0;eN<ha.1k;eN+=2){iD+=1K.aI(1U(ha.2G(eN,2),16))}1d iD};if(!1W.2n(\'1z\').3k){(1c(){1b m=1p;1b mr=m.2p;1b ms=m.3W;1b mc=m.2H;1b 2h=m.2h;1b 7P=m.7P;1b Z=10;1b 4m=Z/2;1c 3k(){1d 1a.aE||(1a.aE=2D 69(1a))}1b 4Q=41.3i.4Q;1c 9k(f,aD,j6){1b a=4Q.44(2q,2);1d 1c(){1d f.b1(aD,a.8a(4Q.44(2q)))}}1c 8T(s){1d 1K(s).4E(/&/g,\'&t9;\').4E(/"/g,\'&ta;\')}1c 7g(2X){if(!2X.4v[\'2Q\']){2X.4v.8j(\'2Q\',\'8J:8K-8H-8I:g8\',\'#2u#6I\')}if(!2X.4v[\'7e\']){2X.4v.8j(\'7e\',\'8J:8K-8H-8I:aQ:aQ\',\'#2u#6I\')}if(!2X.t7[\'9g\']){1b ss=2X.g3();ss.t1.id=\'9g\';ss.t2=\'1z{4S:t3-ts;9W:6W;\'+\'1Q-1S:2a;1o:tt;1C:tk}\'}}7g(1W);1b 8e={9r:1c(9m){if(/tm/.3a(5F.5E)&&!84.7b){1b 2X=9m||1W;2X.2n(\'1z\');2X.5L(\'tp\',9k(1a.9h,1a,2X))}},9h:1c(2X){1b 7W=2X.7X(\'1z\');1q(1b i=0;i<7W.1k;i++){1a.5M(7W[i])}},5M:1c(el){if(!el.3k){el.3k=3k;7g(el.8V);el.3m=\'\';el.5L(\'tn\',9s);el.5L(\'th\',9u);1b 4C=el.7d;if(4C.1o&&4C.1o.5m){el.1v.1o=4C.1o.6Z+\'px\'}1i{el.1o=el.6S}if(4C.1C&&4C.1C.5m){el.1v.1C=4C.1C.6Z+\'px\'}1i{el.1C=el.6U}}1d el}};1c 9s(e){1b el=e.9c;2r(e.tg){1A\'1o\':el.3k().7k();el.1v.1o=el.7d.1o.6Z+\'px\';el.3x.1v.1o=el.6S+\'px\';1F;1A\'1C\':el.3k().7k();el.1v.1C=el.7d.1C.6Z+\'px\';el.3x.1v.1C=el.6U+\'px\';1F}}1c 9u(e){1b el=e.9c;if(el.3x){el.3x.1v.1o=el.6S+\'px\';el.3x.1v.1C=el.6U+\'px\'}}8e.9r();1b 7z=[];1q(1b i=0;i<16;i++){1q(1b j=0;j<16;j++){7z[i*16+j]=i.4J(16)+j.4J(16)}}1c 5Z(){1d[[1,0,0],[0,1,0],[0,0,1]]}1c 4R(m1,m2){1b 7w=5Z();1q(1b x=0;x<3;x++){1q(1b y=0;y<3;y++){1b 6Y=0;1q(1b z=0;z<3;z++){6Y+=m1[x][z]*m2[z][y]}7w[x][y]=6Y}}1d 7w}1c 86(o1,o2){o2.1N=o1.1N;o2.3H=o1.3H;o2.4G=o1.4G;o2.1R=o1.1R;o2.4F=o1.4F;o2.95=o1.95;o2.93=o1.93;o2.97=o1.97;o2.94=o1.94;o2.1O=o1.1O;o2.4t=o1.4t;o2.1Y=o1.1Y;o2.3l=o1.3l;o2.5c=o1.5c;o2.4s=o1.4s;o2.4u=o1.4u;o2.5v=o1.5v}1c 6g(35){1b 3w,3j=1;35=1K(35);if(35.2U(0,3)==\'98\'){1b 1M=35.43(\'(\',3);1b 7D=35.43(\')\',1M+1);1b 6X=35.2U(1M+1,7D).5s(\',\');3w=\'#\';1q(1b i=0;i<3;i++){3w+=7z[1E(6X[i])]}if(6X.1k==4&&35.2G(3,1)==\'a\'){3j=6X[3]}}1i{3w=35}1d{1j:3w,3j:3j}}1b 5i={1v:\'7r\',6N:\'7r\',2B:\'7r\',1t:10,5g:\'4a-49\'};1b 6O={};1c k8(35){if(6O[35]){1d 6O[35]}1b el=1W.2n(\'47\');1b 1v=el.1v;5f{1v.1Y=35}5l(ex){}1d 6O[35]={1v:1v.5b||5i.1v,6N:1v.tq||5i.6N,2B:1v.tx||5i.2B,1t:1v.2z||5i.1t,5g:1v.9Q||5i.5g}}1c k9(1v,9T){1b 3s={};1q(1b p in 1v){3s[p]=1v[p]}1b 5w=9S(9T.l1.2z),2z=9S(1v.1t);if(1f 1v.1t==\'1h\'){3s.1t=1v.1t}1i if(1v.1t.43(\'px\')!=-1){3s.1t=2z}1i if(1v.1t.43(\'em\')!=-1){3s.1t=5w*2z}1i if(1v.1t.43(\'%\')!=-1){3s.1t=(5w/1T)*2z}1i if(1v.1t.43(\'pt\')!=-1){3s.1t=5w*(4/3)*2z}1i{3s.1t=5w}3s.1t*=0.tw;1d 3s}1c l2(1v){1d 1v.1v+\' \'+1v.6N+\' \'+1v.2B+\' \'+1v.1t+\'px \'+1v.5g}1c g5(3H){2r(3H){1A\'9Y\':1d\'tz\';1A\'2p\':1d\'2p\';1A\'6Q\':2u:1d\'6Q\'}}1c 69(5d){1a.2x=5Z();1a.7H=[];1a.6s=[];1a.39=[];1a.1O=\'#7u\';1a.1N=\'#7u\';1a.1R=1;1a.4G=\'ty\';1a.3H=\'9Y\';1a.4F=Z*1;1a.4t=1;1a.1Y=\'7t 4a-49\';1a.3l=\'2a\';1a.5c=\'m3\';1a.1z=5d;1b el=5d.8V.2n(\'47\');el.1v.1o=5d.6S+\'px\';el.1v.1C=5d.6U+\'px\';el.1v.9W=\'6W\';el.1v.1G=\'2W\';5d.2C(el);1a.3t=el;1a.4s=1;1a.4u=1;1a.5v=1}1b 28=69.3i;28.7k=1c(){if(1a.3P){1a.3P.tv(1B);1a.3P=1l}1a.3t.3m=\'\'};28.2o=1c(){1a.39=[]};28.1H=1c(aX,aY){1b p=1a.2y(aX,aY);1a.39.1s({2e:\'1H\',x:p.x,y:p.y});1a.4P=p.x;1a.4O=p.y};28.1u=1c(aX,aY){1b p=1a.2y(aX,aY);1a.39.1s({2e:\'1u\',x:p.x,y:p.y});1a.4P=p.x;1a.4O=p.y};28.4x=1c(9z,9C,9w,9y,aX,aY){1b p=1a.2y(aX,aY);1b 3Z=1a.2y(9z,9C);1b 4k=1a.2y(9w,9y);4x(1a,3Z,4k,p)};1c 4x(6F,3Z,4k,p){6F.39.1s({2e:\'4x\',d5:3Z.x,d6:3Z.y,d3:4k.x,d0:4k.y,x:p.x,y:p.y});6F.4P=p.x;6F.4O=p.y}28.9U=1c(9I,9H,aX,aY){1b cp=1a.2y(9I,9H);1b p=1a.2y(aX,aY);1b 3Z={x:1a.4P+2.0/3.0*(cp.x-1a.4P),y:1a.4O+2.0/3.0*(cp.y-1a.4O)};1b 4k={x:3Z.x+(p.x-1a.4P)/3.0,y:3Z.y+(p.y-1a.4O)/3.0};4x(1a,3Z,4k,p)};28.4q=1c(aX,aY,4w,81,7Z,82){4w*=Z;1b c0=82?\'at\':\'f0\';1b 57=aX+mc(81)*4w-4m;1b 6H=aY+ms(81)*4w-4m;1b 5t=aX+mc(7Z)*4w-4m;1b 6J=aY+ms(7Z)*4w-4m;if(57==5t&&!82){57+=0.tu}1b p=1a.2y(aX,aY);1b 7V=1a.2y(57,6H);1b 7Y=1a.2y(5t,6J);1a.39.1s({2e:c0,x:p.x,y:p.y,58:4w,57:7V.x,6H:7V.y,5t:7Y.x,6J:7Y.y})};28.8m=1c(aX,aY,3X,3V){1a.1H(aX,aY);1a.1u(aX+3X,aY);1a.1u(aX+3X,aY+3V);1a.1u(aX,aY+3V);1a.31()};28.8x=1c(aX,aY,3X,3V){1b 6A=1a.39;1a.2o();1a.1H(aX,aY);1a.1u(aX+3X,aY);1a.1u(aX+3X,aY+3V);1a.1u(aX,aY+3V);1a.31();1a.2c();1a.39=6A};28.5H=1c(aX,aY,3X,3V){1b 6A=1a.39;1a.2o();1a.1H(aX,aY);1a.1u(aX+3X,aY);1a.1u(aX+3X,aY+3V);1a.1u(aX,aY+3V);1a.31();1a.2S();1a.39=6A};28.t5=1c(6B,6C,6G,6K){1b 2R=2D 56(\'2R\');2R.5U=6B;2R.5I=6C;2R.6d=6G;2R.6c=6K;1d 2R};28.t4=1c(6B,6C,j1,6G,6K,k1){1b 2R=2D 56(\'sZ\');2R.5U=6B;2R.5I=6C;2R.8Z=j1;2R.6d=6G;2R.6c=6K;2R.8q=k1;1d 2R};28.sY=1c(2k,j6){1b dx,dy,dw,dh,sx,sy,sw,sh;1b j2=2k.55.1o;1b j7=2k.55.1C;2k.55.1o=\'2J\';2k.55.1C=\'2J\';1b w=2k.1o;1b h=2k.1C;2k.55.1o=j2;2k.55.1C=j7;if(2q.1k==3){dx=2q[1];dy=2q[2];sx=sy=0;sw=dw=w;sh=dh=h}1i if(2q.1k==5){dx=2q[1];dy=2q[2];dw=2q[3];dh=2q[4];sx=sy=0;sw=w;sh=h}1i if(2q.1k==9){sx=2q[1];sy=2q[2];sw=2q[3];sh=2q[4];dx=2q[5];dy=2q[6];dw=2q[7];dh=2q[8]}1i{60 6u(\'3S 1h of 2q\')}1b d=1a.2y(dx,dy);1b t0=sw/2;1b h2=sh/2;1b 59=[];1b W=10;1b H=10;59.1s(\' <2Q:8f\',\' 5q="\',Z*W,\',\',Z*H,\'"\',\' 5r="0,0"\',\' 1v="1o:\',W,\'px;1C:\',H,\'px;1G:2W;\');if(1a.2x[0][0]!=1||1a.2x[0][1]||1a.2x[1][1]!=1||1a.2x[1][0]){1b 53=[];53.1s(\'t6=\',1a.2x[0][0],\',\',\'td=\',1a.2x[1][0],\',\',\'tb=\',1a.2x[0][1],\',\',\'tf=\',1a.2x[1][1],\',\',\'te=\',mr(d.x/Z),\',\',\'t8=\',mr(d.y/Z),\'\');1b 1y=d;1b c2=1a.2y(dx+dw,dy);1b c3=1a.2y(dx,dy+dh);1b c4=1a.2y(dx+dw,dy+dh);1y.x=m.1y(1y.x,c2.x,c3.x,c4.x);1y.y=m.1y(1y.y,c2.y,c3.y,c4.y);59.1s(\'6f:0 \',mr(1y.x/Z),\'px \',mr(1y.y/Z),\'px 0;53:tA:tX.7U.tW(\',53.3n(\'\'),", tZ=\'8Q\');")}1i{59.1s(\'2w:\',mr(d.y/Z),\'px;2a:\',mr(d.x/Z),\'px;\')}59.1s(\' ">\',\'<2Q:2k 3v="\',2k.3v,\'"\',\' 1v="1o:\',Z*dw,\'px;\',\' 1C:\',Z*dh,\'px"\',\' tY="\',sx/w,\'"\',\' tT="\',sy/h,\'"\',\' tS="\',(w-sx-sw)/w,\'"\',\' tU="\',(h-sy-sh)/h,\'"\',\' />\',\'</2Q:8f>\');1a.3t.6i(\'u0\',59.3n(\'\'))};28.2c=1c(70){1b 2j=[];1b u5=1g;1b W=10;1b H=10;2j.1s(\'<2Q:3F\',\' 6M="\',!!70,\'"\',\' 1v="1G:2W;1o:\',W,\'px;1C:\',H,\'px;"\',\' 5r="0,0"\',\' 5q="\',Z*W,\',\',Z*H,\'"\',\' 6L="\',!70,\'"\',\' 3g="\');1b u8=1g;1b 1w={x:1l,y:1l};1b 1y={x:1l,y:1l};1q(1b i=0;i<1a.39.1k;i++){1b p=1a.39[i];1b c;2r(p.2e){1A\'1H\':c=p;2j.1s(\' m \',mr(p.x),\',\',mr(p.y));1F;1A\'1u\':2j.1s(\' l \',mr(p.x),\',\',mr(p.y));1F;1A\'c6\':2j.1s(\' x \');p=1l;1F;1A\'4x\':2j.1s(\' c \',mr(p.d5),\',\',mr(p.d6),\',\',mr(p.d3),\',\',mr(p.d0),\',\',mr(p.x),\',\',mr(p.y));1F;1A\'at\':1A\'f0\':2j.1s(\' \',p.2e,\' \',mr(p.x-1a.4s*p.58),\',\',mr(p.y-1a.4u*p.58),\' \',mr(p.x+1a.4s*p.58),\',\',mr(p.y+1a.4u*p.58),\' \',mr(p.57),\',\',mr(p.6H),\' \',mr(p.5t),\',\',mr(p.6J));1F}if(p){if(1w.x==1l||p.x<1w.x){1w.x=p.x}if(1y.x==1l||p.x>1y.x){1y.x=p.x}if(1w.y==1l||p.y<1w.y){1w.y=p.y}if(1y.y==1l||p.y>1y.y){1y.y=p.y}}}2j.1s(\' ">\');if(!70){7p(1a,2j)}1i{7s(1a,2j,1w,1y)}2j.1s(\'</2Q:3F>\');1a.3t.6i(\'8U\',2j.3n(\'\'))};1c 7p(2b,2j){1b a=6g(2b.1O);1b 1j=a.1j;1b 2l=a.3j*2b.4t;1b 1R=2b.5v*2b.1R;if(1R<1){2l*=1R}2j.1s(\'<2Q:2c\',\' 2l="\',2l,\'"\',\' k3="\',2b.4G,\'"\',\' l0="\',2b.4F,\'"\',\' k4="\',g5(2b.3H),\'"\',\' 2B="\',1R,\'px"\',\' 1j="\',1j,\'" />\')}1c 7s(2b,2j,1w,1y){1b 1N=2b.1N;1b 52=2b.4s;1b 4Y=2b.4u;1b 1o=1y.x-1w.x;1b 1C=1y.y-1w.y;if(1N 74 56){1b 3E=0;1b 5O={x:0,y:0};1b 79=0;1b 8c=1;if(1N.91==\'2R\'){1b g6=1N.5U/52;1b g7=1N.5I/4Y;1b h6=1N.6d/52;1b h7=1N.6c/4Y;1b p0=2b.2y(g6,g7);1b p1=2b.2y(h6,h7);1b dx=p1.x-p0.x;1b dy=p1.y-p0.y;3E=1p.u2(dx,dy)*4j/1p.1X;if(3E<0){3E+=u4}if(3E<1e-6){3E=0}}1i{1b p0=2b.2y(1N.5U,1N.5I);5O={x:(p0.x-1w.x)/1o,y:(p0.y-1w.y)/1C};1o/=52*Z;1C/=4Y*Z;1b 7G=m.1y(1o,1C);79=2*1N.8Z/7G;8c=2*1N.8q/7G-79}1b 4d=1N.8r;4d.tG(1c(f2,f3){1d f2.33-f3.33});1b 1k=4d.1k;1b f8=4d[0].1j;1b 7l=4d[1k-1].1j;1b g1=4d[0].3j*2b.4t;1b 7C=4d[1k-1].3j*2b.4t;1b 76=[];1q(1b i=0;i<1k;i++){1b 7n=4d[i];76.1s(7n.33*8c+79+\' \'+7n.1j)}2j.1s(\'<2Q:2S 2e="\',1N.91,\'"\',\' tI="5R" 5O="1T%"\',\' 1j="\',f8,\'"\',\' 7l="\',7l,\'"\',\' 76="\',76.3n(\',\'),\'"\',\' 2l="\',7C,\'"\',\' 7e:7C="\',g1,\'"\',\' 3E="\',3E,\'"\',\' tC="\',5O.x,\',\',5O.y,\'" />\')}1i if(1N 74 5V){if(1o&&1C){1b f5=-1w.x;1b h3=-1w.y;2j.1s(\'<2Q:2S\',\' 1G="\',f5/1o*52*52,\',\',h3/1C*4Y*4Y,\'"\',\' 2e="tE"\',\' 3v="\',1N.e6,\'" />\')}}1i{1b a=6g(2b.1N);1b 1j=a.1j;1b 2l=a.3j*2b.4t;2j.1s(\'<2Q:2S 1j="\',1j,\'" 2l="\',2l,\'" />\')}}28.2S=1c(){1a.2c(1B)};28.31=1c(){1a.39.1s({2e:\'c6\'})};28.2y=1c(aX,aY){1b m=1a.2x;1d{x:Z*(aX*m[0][0]+aY*m[1][0]+m[2][0])-4m,y:Z*(aX*m[0][1]+aY*m[1][1]+m[2][1])-4m}};28.3R=1c(){1b o={};86(1a,o);1a.6s.1s(o);1a.7H.1s(1a.2x);1a.2x=4R(5Z(),1a.2x)};28.3T=1c(){if(1a.6s.1k){86(1a.6s.c7(),1a);1a.2x=1a.7H.c7()}};1c ch(m){1d 48(m[0][0])&&48(m[0][1])&&48(m[1][0])&&48(m[1][1])&&48(m[2][0])&&48(m[2][1])}1c 4T(2b,m,c9){if(!ch(m)){1d}2b.2x=m;if(c9){1b e3=m[0][0]*m[1][1]-m[0][1]*m[1][0];2b.5v=7P(2h(e3))}}28.4c=1c(aX,aY){1b m1=[[1,0,0],[0,1,0],[aX,aY,1]];4T(1a,4R(m1,1a.2x),1g)};28.4f=1c(7I){1b c=mc(7I);1b s=ms(7I);1b m1=[[c,s,0],[-s,c,0],[0,0,1]];4T(1a,4R(m1,1a.2x),1g)};28.4H=1c(aX,aY){1a.4s*=aX;1a.4u*=aY;1b m1=[[aX,0,0],[0,aY,0],[0,0,1]];4T(1a,4R(m1,1a.2x),1B)};28.tP=1c(6k,6l,6m,6h,dx,dy){1b m1=[[6k,6l,0],[6m,6h,0],[dx,dy,1]];4T(1a,4R(m1,1a.2x),1B)};28.tR=1c(6k,6l,6m,6h,dx,dy){1b m=[[6k,6l,0],[6m,6h,0],[dx,dy,1]];4T(1a,m,1B)};28.8W=1c(1Q,x,y,5y,2c){1b m=1a.2x,6z=3J,2a=0,3p=6z,33={x:0,y:0},2j=[];1b 5b=k9(k8(1a.1Y),1a.3t);1b j8=l2(5b);1b 7c=1a.3t.l1;1b 3l=1a.3l.3e();2r(3l){1A\'2a\':1A\'5n\':1A\'3p\':1F;1A\'7D\':3l=7c.l9==\'s2\'?\'3p\':\'2a\';1F;1A\'1M\':3l=7c.l9==\'s8\'?\'3p\':\'2a\';1F;2u:3l=\'2a\'}2r(1a.5c){1A\'sg\':1A\'2w\':33.y=5b.1t/1.75;1F;1A\'sc\':1F;2u:1A 1l:1A\'m3\':1A\'rO\':1A\'rQ\':33.y=-5b.1t/2.25;1F}2r(3l){1A\'3p\':2a=6z;3p=0.j5;1F;1A\'5n\':2a=3p=6z/2;1F}1b d=1a.2y(x+33.x,y+33.y);2j.1s(\'<2Q:1L rR="\',-2a,\' 0" to="\',3p,\' 0.j5" \',\' 5q="1T 1T" 5r="0 0"\',\' 6M="\',!2c,\'" 6L="\',!!2c,\'" 1v="1G:2W;1o:5A;1C:5A;">\');if(2c){7p(1a,2j)}1i{7s(1a,2j,{x:-2a,y:0},{x:3p,y:5b.1t})}1b i5=m[0][0].2V(3)+\',\'+m[1][0].2V(3)+\',\'+m[0][1].2V(3)+\',\'+m[1][1].2V(3)+\',0,0\';1b i1=mr(d.x/Z)+\',\'+mr(d.y/Z);2j.1s(\'<2Q:sR on="t" sq="\',i5,\'" \',\' 33="\',i1,\'" st="\',2a,\' 0" />\',\'<2Q:3g sr="1B" />\',\'<2Q:g0 on="1B" 1n="\',8T(1Q),\'" 1v="v-1Q-1S:\',3l,\';1Y:\',8T(j8),\'" /></2Q:1L>\');1a.3t.6i(\'8U\',2j.3n(\'\'))};28.8X=1c(1Q,x,y,5y){1a.8W(1Q,x,y,5y,1g)};28.5h=1c(1Q,x,y,5y){1a.8W(1Q,x,y,5y,1B)};28.8R=1c(1Q){if(!1a.3P){1b s=\'<l4 1v="1G:2W;\'+\'2w:-sk;2a:0;6f:0;8y:0;4n:5R;\'+\'sm-2E:sD;"></l4>\';1a.3t.6i(\'8U\',s);1a.3P=1a.3t.vu}1b 2X=1a.3t.8V;1a.3P.3m=\'\';1a.3P.1v.1Y=1a.1Y;1a.3P.2C(2X.sE(1Q));1d{1o:1a.3P.sv}};28.8Q=1c(){};28.sB=1c(){};28.vA=1c(2k,5o){1d 2D 5V(2k,5o)};1c 56(e0){1a.91=e0;1a.5U=0;1a.5I=0;1a.8Z=0;1a.6d=0;1a.6c=0;1a.8q=0;1a.8r=[]}56.3i.va=1c(e7,5P){5P=6g(5P);1a.8r.1s({33:e7,1j:5P.1j,3j:5P.3j})};1c 5V(2k,5o){e5(2k);2r(5o){1A\'5D\':1A 1l:1A\'\':1a.e9=\'5D\';1F;1A\'5D-x\':1A\'5D-y\':1A\'no-5D\':1a.e9=5o;1F;2u:6p(\'d2\')}1a.e6=2k.3v;1a.uz=2k.1o;1a.uv=2k.1C}1c 6p(s){60 2D 78(s)}1c e5(42){if(!42||42.ve!=1||42.4p!=\'vk\'){6p(\'h8\')}if(42.vy!=\'vp\'){6p(\'d4\')}}1c 78(s){1a.3Y=1a[s];1a.8v=s+\': uP v2 \'+1a.3Y}1b p=78.3i=2D 6u;p.v1=1;p.v0=2;p.v4=3;p.uZ=4;p.ui=5;p.uj=6;p.ub=7;p.u9=8;p.uc=9;p.un=10;p.d4=11;p.d2=12;p.uB=13;p.uq=14;p.up=15;p.ur=16;p.h8=17;65=8e;6R=69;ut=56;v7=5V;vw=78})()}if(/^7b/.3a(5F.5E.3e())){1c vt(){if(1W.4v[\'v\']==1l){1b e=["3F","vl","8f","g4","3g","sQ","sW","2S","2c","sU","sJ","g0","sH","1L","sN","sK","rU","rV","8m","4q","2k"],s=1W.g3();1q(1b i=0;i<e.1k;i++){s.rT("v\\\\:"+e[i],"rY: h4(#2u#6I);")}1W.4v.8j("v","8J:8K-8H-8I:g8","#2u#6I");}if(1f go==\'1c\'&&1W.4v[\'v\']!=1l){1d 1B}1i{1d 1g}}1c rW(x,y,5e,6D,5T,2B,1j,2l,3h){3h=1f(3h)!=\'1r\'?3h:0;1j=1f(1j)!=\'1r\'?1j:\'#j3\';2l=1f(2l)!=\'1r\'?2l:1;id=1f(id)!=\'1r\'?\'id="\'+id+\'"\':\'\';1b w=1U(5T),b=1U(5e),h=1U(6D);1d\'<v:3F \'+id+\' 6M="f" 6L="t" 5r="0,0" 5q="\'+w+\',\'+h+\'" 3g="m 0,\'+b+\' l 0,0,\'+w+\',0,\'+w+\',\'+b+\',0,\'+b+\',0,\'+h+\',\'+w+\',\'+h+\',\'+w+\',\'+b+\' e" 1v="3h:\'+3h+\';1G:2W;8y:8B;2w:\'+1p.2p(y)+\'px;2a:\'+1p.2p(x)+\'px;1o:\'+w+\'px;1C:\'+h+\'px;"><v:2c 1j="\'+1j+\'" 2l="\'+2l+\'" 2B="\'+2B+\'" /></v:3F>\'}1c go(1n,x,y,1t,2B,1o,2E,1Y,1j,2l,3h,id){1c qC(cX,cY,g9,e4,aX,aY){1b t=2D 41(6);t[0]=cX+2.0/3.0*(g9-cX);t[1]=cY+2.0/3.0*(e4-cY);t[2]=t[0]+(aX-cX)/3.0;t[3]=t[1]+(aY-cY)/3.0;t[4]=aX;t[5]=aY;1d t}1t=1f(1t)!=\'1r\'?1t:12;2B=1f(2B)!=\'1r\'?2B:1T;1o=1T;2E=1T;1Y="4a-49";1n=1f(1n)!=\'1r\'?1n:\' \';1b i9=1f(x)!=\'1r\'?x:0;1b i3=1f(y)!=\'1r\'?y:0;3h=1f(3h)!=\'1r\'?3h:0;1j=1f(1j)!=\'1r\'?1j:\'#j3\';2l=1f(2l)!=\'1r\'?2l:1;id=1f(id)!=\'1r\'?\'id="\'+id+\'"\':\'\';1b i=0,j=0,f=10,3g="",a,b,z,k,c,p,o,4i=1n.1k,1P=1t/25.0,6P=1p.1y(1p.1w(2B,4l),1)/40,2f=1p.1y(1p.1w(1o,4l),10)/1T;1b 6T=1p.1y(1p.1w(2E,3J),10)/1T,mx=((1P*16*2f)*6T)-(1P*16*2f),lw=(6P*1P);x=0;y=1t;1b 8E=1p.2p(8F(1n,1t,1o,2E,1Y)),hh=1p.2p(8C(1t));1b 8D=\'<v:3F \'+id+\' 6M="f" 6L="t" 5r="0,0" 5q="\'+1U(8E*f)+\',\'+1U(hh*f)+\'"\';1q(i=0;i<4i;i++){c=5j[1Y][1n.4r(i)];if(!c){3d}o=0;1q(j=0;j<c.n;j++){if(1f(c.d[o])!="1n"){o++;3d}p=c.d[o];o++;a=c.d[o];if(p=="m"){3g+=\' m \'+1U((x+a[0]*1P*2f)*f)+\',\'+1U((y-a[1]*1P)*f);o++}1i if(p=="q"){z=c.d[o-2];o++;b=c.d[o];k=qC(z[0],z[1],a[0],a[1],b[0],b[1]);3g+=\' c \'+1U((x+k[0]*1P*2f)*f)+\',\'+1U((y-k[1]*1P)*f)+\',\'+1U((x+k[2]*1P*2f)*f)+\',\'+1U((y-k[3]*1P)*f)+\',\'+1U((x+k[4]*1P*2f)*f)+\',\'+1U((y-k[5]*1P)*f);o++}1i if(p=="b"){o++;b=c.d[o];o++;z=c.d[o];3g+=\' c \'+1U((x+a[0]*1P*2f)*f)+\',\'+1U((y-a[1]*1P)*f)+\',\'+1U((x+a[0]*1P*2f)*f)+\',\'+1U((y-a[1]*1P)*f)+\',\'+1U((x+z[0]*1P*2f)*f)+\',\'+1U((y-z[1]*1P)*f);o++}1i if(p=="l"){3g+=\' l \'+1U((x+a[0]*1P*2f)*f)+\',\'+1U((y-a[1]*1P)*f);o++;2s(1f(c.d[o])!="1n"&&o<c.d.1k){a=c.d[o];3g+=\' l \'+1U((x+a[0]*1P*2f)*f)+\',\'+1U((y-a[1]*1P)*f);o++}}}x+=((c.w*2f)*1P)+mx}8D+=\' 3g="\'+3g+\' e" 1v="3h:\'+3h+\';1G:2W;8y:8B;2w:\'+1p.2p(i3)+\'px;2a:\'+1p.2p(i9)+\'px;1o:\'+8E+\'px;1C:\'+hh+\'px;"><v:2c 1j="\'+1j+\'" 2l="\'+2l+\'" 2B="\'+lw+\'" l0="0" k4="2p" k3="2p" /></v:3F>\';1d 8D}1c s7(1t){1d 1t}1c 8C(1t){1t=1f(1t)!=\'1r\'?1t:12;1d 32*(1t/25)}1c 8F(1n,1t,1o,2E,1Y){1t=1f(1t)!=\'1r\'?1t:12;1o=1T;2E=1T;1n=1f(1n)!=\'1r\'?1n:\' \';1Y="4a-49";1b 5a=0,4i=1n.1k,mg=1t/25.0,fw=1p.1y(1p.1w(1o,4l),10)/1T,sp=1p.1y(1p.1w(2E,3J),10)/1T,m=((mg*16*fw)*sp)-(mg*16*fw);1q(1b i=0;i<4i;i++){1b c=5j[1Y][1n.4r(i)];if(c)5a+=((c.w*fw)*mg)+m}1d 5a-(m)}1c sX(1n,1o,1t,5J,2E,1Y){1t=1f(1t)!=\'1r\'?1t:12;5J=1f(5J)!=\'1r\'?5J:1T;2E=1T;1n=1f(1n)!=\'1r\'?1n:\' \';1o=1T;1Y="4a-49";1b 6E=0,5a=0,4i=1n.1k,mg=1t/25.0,fw=1p.1y(1p.1w(5J,4l),10)/1T,sp=1p.1y(1p.1w(2E,3J),10)/1T,m=((mg*16*fw)*sp)-(mg*16*fw);1q(1b i=0;i<4i;i++){1b c=5j[1Y][1n.4r(i)];if(c){6E=((c.w*fw)*mg)+m;if((5a+6E-(m))<=1o){5a+=6E}1i{1F}}1i{1F}}1d 1n.2U(0,i)}1c tL(2b,x,y,5e,6D,5T){2b.8x(x,y+5e,5T,6D-5e);2b.8x(x,y,5T,5e)}1c 8A(1n,x,y,1t,2B,1o,2E,1Y){1t=1f(1t)!=\'1r\'?1t:12;2B=1f(2B)!=\'1r\'?2B:1T;1o=1T;2E=1T;1Y="4a-49";x=1f(x)!=\'1r\'?x:0;y=1f(y)!=\'1r\'?y+1t:0+1t;1n=1f(1n)!=\'1r\'?1n:\' \';1b i=0,j=0,a,b,z,c,p,o,4i=1n.1k,1P=1t/25.0,6P=1p.1y(1p.1w(2B,4l),1)/40,2f=1p.1y(1p.1w(1o,4l),10)/1T;1b 6T=1p.1y(1p.1w(2E,3J),10)/1T,mx=((1P*16*2f)*6T)-(1P*16*2f),lw=1a.1R,ml=1a.4F,lj=1a.4G,lc=1a.3H;1a.1R=(6P*1P);1a.4F=0;1a.4G="2p";1a.3H="2p";1q(i=0;i<4i;i++){c=5j[1Y][1n.4r(i)];if(!c){3d}o=0;1a.2o();1q(j=0;j<c.n;j++){if(1f(c.d[o])!="1n"){o++;3d}p=c.d[o];o++;a=c.d[o];if(p=="m"){1a.1H(x+a[0]*1P*2f,y-a[1]*1P);o++}1i if(p=="q"){o++;b=c.d[o];1a.9U(x+a[0]*1P*2f,y-a[1]*1P,x+b[0]*1P*2f,y-b[1]*1P);o++}1i if(p=="b"){o++;b=c.d[o];o++;z=c.d[o];1a.4x(x+a[0]*1P*2f,y-a[1]*1P,x+b[0]*1P*2f,y-b[1]*1P,x+z[0]*1P*2f,y-z[1]*1P);o++}1i if(p=="l"){1a.1u(x+a[0]*1P*2f,y-a[1]*1P);o++;2s(1f(c.d[o])!="1n"&&o<c.d.1k){a=c.d[o];1a.1u(x+a[0]*1P*2f,y-a[1]*1P);o++}}}1a.2c();x+=((c.w*2f)*1P)+mx}1a.1R=lw;1a.4F=ml;1a.4G=lj;1a.3H=lc}1c ti(2b){if(1f 6R==\'1r\'){2b.5h=8A}}1c tj(2b){if(1f 2b.5h==\'1c\'){1d 1B}1i{1d 1g}}if(1f 6R!=\'1r\'){6R.3i.5h=8A}5j=2D 41();5j["4a-49"]={\' \':{w:16,n:1,d:[]},\'!\':{w:10,n:4,d:[\'m\',[5,21],\'l\',[5,7],\'m\',[5,2],\'l\',[4,1],[5,0],[6,1],[5,2]]},\'"\':{w:14,n:4,d:[\'m\',[4,21],\'l\',[4,14],\'m\',[10,21],\'l\',[10,14]]},\'#\':{w:21,n:8,d:[\'m\',[11,25],\'l\',[4,-7],\'m\',[17,25],\'l\',[10,-7],\'m\',[4,12],\'l\',[18,12],\'m\',[3,6],\'l\',[17,6]]},\'$\':{w:20,n:12,d:[\'m\',[16,18],\'q\',[15,21],[10,21],\'q\',[5,21],[4,17],\'q\',[3,12],[7,11],\'l\',[13,10],\'q\',[18,9],[17,4],\'q\',[16,0],[10,0],\'q\',[4,0],[3,4],\'m\',[8,25],\'l\',[6,-4],\'m\',[14,25],\'l\',[12,-4]]},\'%\':{w:24,n:12,d:[\'m\',[21,21],\'l\',[3,0],\'m\',[7,21],\'q\',[3,21],[3,17],\'q\',[3,13],[7,13],\'q\',[11,13],[11,17],\'q\',[11,21],[7,21],\'m\',[17,8],\'q\',[13,8],[13,4],\'q\',[13,0],[17,0],\'q\',[21,0],[21,4],\'q\',[21,8],[17,8]]},\'&\':{w:26,n:14,d:[\'m\',[23,12],\'q\',[23,14],[22,14],\'q\',[20,14],[19,11],\'l\',[17,6],\'q\',[15,0],[9,0],\'q\',[3,0],[3,5],\'q\',[3,8],[7,10],\'l\',[12,13],\'q\',[14,15],[14,17],\'q\',[14,21],[11,21],\'q\',[8,21],[8,17],\'q\',[8,14],[12,8],\'q\',[17,0],[21,0],\'q\',[23,0],[23,2]]},\'\\\'\':{w:10,n:2,d:[\'m\',[5,19],\'l\',[4,20],[5,21],[6,20],[6,18],[5,16],[4,15]]},\'(\':{w:14,n:3,d:[\'m\',[11,25],\'q\',[4,19],[4,9],\'q\',[4,-1],[11,-7]]},\')\':{w:14,n:3,d:[\'m\',[3,25],\'q\',[10,19],[10,9],\'q\',[10,-1],[3,-7]]},\'*\':{w:16,n:6,d:[\'m\',[8,21],\'l\',[8,9],\'m\',[3,18],\'l\',[13,12],\'m\',[13,18],\'l\',[3,12]]},\'+\':{w:26,n:4,d:[\'m\',[13,18],\'l\',[13,0],\'m\',[4,9],\'l\',[22,9]]},\',\':{w:10,n:2,d:[\'m\',[6,1],\'l\',[5,0],[4,1],[5,2],[6,1],[6,-1],[5,-3],[4,-4]]},\'-\':{w:26,n:2,d:[\'m\',[4,9],\'l\',[22,9]]},\'.\':{w:10,n:2,d:[\'m\',[5,2],\'l\',[4,1],[5,0],[6,1],[5,2]]},\'/\':{w:22,n:2,d:[\'m\',[20,25],\'l\',[2,-7]]},\'0\':{w:20,n:7,d:[\'m\',[10,21],\'q\',[3,21],[3,12],\'l\',[3,9],\'q\',[3,0],[10,0],\'q\',[17,0],[17,9],\'l\',[17,12],\'q\',[17,21],[10,21]]},\'1\':{w:20,n:3,d:[\'m\',[6,17],\'q\',[8,18],[11,21],\'l\',[11,0]]},\'2\':{w:20,n:5,d:[\'m\',[17,0],\'l\',[3,0],[13,10],\'q\',[16,13],[16,16],\'q\',[16,21],[10,21],\'q\',[4,21],[4,16]]},\'3\':{w:20,n:5,d:[\'m\',[5,21],\'l\',[16,21],[10,14],\'q\',[17,14],[17,7],\'q\',[17,0],[10,0],\'q\',[5,0],[3,4]]},\'4\':{w:20,n:2,d:[\'m\',[13,0],\'l\',[13,21],[3,7],[18,7]]},\'5\':{w:20,n:6,d:[\'m\',[15,21],\'l\',[5,21],[4,12],\'q\',[5,14],[10,14],\'q\',[17,14],[17,7],\'q\',[17,0],[10,0],\'q\',[5,0],[3,4]]},\'6\':{w:20,n:8,d:[\'m\',[16,18],\'q\',[15,21],[10,21],\'q\',[3,21],[3,12],\'l\',[3,7],\'q\',[3,0],[10,0],\'q\',[17,0],[17,7],\'q\',[17,13],[10,13],\'q\',[3,13],[3,7]]},\'7\':{w:20,n:2,d:[\'m\',[3,21],\'l\',[17,21],[7,0]]},\'8\':{w:20,n:9,d:[\'m\',[10,13],\'q\',[15,13],[15,17],\'q\',[15,21],[10,21],\'q\',[5,21],[5,17],\'q\',[5,13],[10,13],\'q\',[3,13],[3,7],\'q\',[3,0],[10,0],\'q\',[17,0],[17,7],\'q\',[17,13],[10,13]]},\'9\':{w:20,n:8,d:[\'m\',[17,14],\'q\',[17,8],[10,8],\'q\',[3,8],[3,14],\'q\',[3,21],[10,21],\'q\',[17,21],[17,14],\'l\',[17,9],\'q\',[17,0],[10,0],\'q\',[5,0],[4,3]]},\':\':{w:10,n:4,d:[\'m\',[5,14],\'l\',[4,13],[5,12],[6,13],[5,14],\'m\',[5,2],\'l\',[4,1],[5,0],[6,1],[5,2]]},\';\':{w:10,n:4,d:[\'m\',[5,14],\'l\',[4,13],[5,12],[6,13],[5,14],\'m\',[6,1],\'l\',[5,0],[4,1],[5,2],[6,1],[6,-1],[5,-3],[4,-4]]},\'<\':{w:24,n:2,d:[\'m\',[20,18],\'l\',[4,9],[20,0]]},\'=\':{w:26,n:4,d:[\'m\',[4,12],\'l\',[22,12],\'m\',[4,6],\'l\',[22,6]]},\'>\':{w:24,n:2,d:[\'m\',[4,18],\'l\',[20,9],[4,0]]},\'?\':{w:18,n:8,d:[\'m\',[3,16],\'q\',[3,21],[9,21],\'q\',[15,21],[15,16],\'q\',[15,11],[10,11],\'q\',[9,11],[9,10],\'l\',[9,7],\'m\',[9,2],\'l\',[8,1],[9,0],[10,1],[9,2]]},\'@\':{w:27,n:17,d:[\'m\',[21,3],\'q\',[20,1],[14,0],\'l\',[13,0],\'q\',[4,1],[3,10],\'l\',[3,11],\'q\',[4,20],[13,21],\'l\',[14,21],\'q\',[23,20],[24,11],\'l\',[24,10],\'q\',[24,6],[20,6],\'q\',[17,6],[18,10],\'q\',[18,6],[13,6],\'q\',[8,6],[9,11],\'q\',[10,15],[14,15],\'q\',[19,15],[18,10],\'m\',[18,10],\'l\',[19,14]]},\'A\':{w:18,n:6,d:[\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'B\':{w:21,n:9,d:[\'m\',[4,11],\'l\',[12,11],\'m\',[13,0],\'l\',[4,0],[4,21],[12,21],\'q\',[17,21],[17,16],\'q\',[17,11],[12,11],\'q\',[18,11],[18,6],\'l\',[18,5],\'q\',[18,0],[13,0]]},\'C\':{w:21,n:7,d:[\'m\',[11,21],\'q\',[17,21],[18,16],\'m\',[18,5],\'q\',[17,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'D\':{w:21,n:5,d:[\'m\',[11,0],\'l\',[4,0],[4,21],[11,21],\'q\',[18,21],[18,12],\'l\',[18,9],\'q\',[18,0],[11,0]]},\'E\':{w:19,n:4,d:[\'m\',[17,21],\'l\',[4,21],[4,0],[17,0],\'m\',[4,11],\'l\',[12,11]]},\'F\':{w:18,n:4,d:[\'m\',[17,21],\'l\',[4,21],[4,0],\'m\',[4,11],\'l\',[12,11]]},\'G\':{w:21,n:8,d:[\'m\',[11,21],\'q\',[17,21],[18,16],\'m\',[13,8],\'l\',[18,8],[18,5],\'q\',[17,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'H\':{w:22,n:6,d:[\'m\',[4,21],\'l\',[4,0],\'m\',[18,21],\'l\',[18,0],\'m\',[4,11],\'l\',[18,11]]},\'I\':{w:8,n:2,d:[\'m\',[4,21],\'l\',[4,0]]},\'J\':{w:16,n:5,d:[\'m\',[12,21],\'l\',[12,5],\'q\',[12,0],[7,0],\'q\',[2,0],[2,5],\'l\',[2,7]]},\'K\':{w:21,n:6,d:[\'m\',[4,21],\'l\',[4,0],\'m\',[18,21],\'l\',[4,7],\'m\',[9,12],\'l\',[18,0]]},\'L\':{w:17,n:2,d:[\'m\',[4,21],\'l\',[4,0],[16,0]]},\'M\':{w:24,n:2,d:[\'m\',[4,0],\'l\',[4,21],[12,0],[20,21],[20,0]]},\'N\':{w:22,n:2,d:[\'m\',[4,0],\'l\',[4,21],[18,0],[18,21]]},\'O\':{w:22,n:7,d:[\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'P\':{w:21,n:6,d:[\'m\',[4,10],\'l\',[13,10],\'q\',[18,10],[18,15],\'l\',[18,16],\'q\',[18,21],[13,21],\'l\',[4,21],[4,0]]},\'Q\':{w:22,n:9,d:[\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21],\'m\',[12,4],\'l\',[18,-2]]},\'R\':{w:21,n:8,d:[\'m\',[4,10],\'l\',[13,10],\'q\',[18,10],[18,15],\'l\',[18,16],\'q\',[18,21],[13,21],\'l\',[4,21],[4,0],\'m\',[13,10],\'l\',[18,0]]},\'S\':{w:20,n:8,d:[\'m\',[16,18],\'q\',[15,21],[10,21],\'q\',[5,21],[4,17],\'q\',[3,12],[7,11],\'l\',[13,10],\'q\',[18,9],[17,4],\'q\',[16,0],[10,0],\'q\',[4,0],[3,4]]},\'T\':{w:16,n:4,d:[\'m\',[8,21],\'l\',[8,0],\'m\',[1,21],\'l\',[15,21]]},\'U\':{w:22,n:5,d:[\'m\',[4,21],\'l\',[4,6],\'q\',[4,0],[11,0],\'q\',[18,0],[18,6],\'l\',[18,21]]},\'V\':{w:18,n:2,d:[\'m\',[1,21],\'l\',[9,0],[17,21]]},\'W\':{w:24,n:2,d:[\'m\',[2,21],\'l\',[7,0],[12,21],[17,0],[22,21]]},\'X\':{w:20,n:4,d:[\'m\',[3,21],\'l\',[17,0],\'m\',[17,21],\'l\',[3,0]]},\'Y\':{w:18,n:4,d:[\'m\',[1,21],\'l\',[9,11],[17,21],\'m\',[9,11],\'l\',[9,0]]},\'Z\':{w:20,n:2,d:[\'m\',[3,21],\'l\',[17,21],[3,0],[17,0]]},\'[\':{w:14,n:2,d:[\'m\',[11,25],\'l\',[4,25],[4,-7],[11,-7]]},\'\\\\\':{w:14,n:2,d:[\'m\',[0,21],\'l\',[14,-3]]},\']\':{w:14,n:2,d:[\'m\',[3,25],\'l\',[10,25],[10,-7],[3,-7]]},\'^\':{w:16,n:2,d:[\'m\',[3,16],\'l\',[8,21],[13,16]]},\'4D\':{w:16,n:2,d:[\'m\',[0,-2],\'l\',[16,-2]]},\'`\':{w:10,n:2,d:[\'m\',[6,21],\'l\',[5,20],[4,18],[4,16],[5,15],[6,16],[5,17]]},\'a\':{w:19,n:10,d:[\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'b\':{w:19,n:10,d:[\'m\',[4,21],\'l\',[4,0],\'m\',[10,14],\'l\',[9,14],\'q\',[6,14],[4,12],\'m\',[4,2],\'q\',[6,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'c\':{w:18,n:10,d:[\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[15,11],\'q\',[14,14],[10,14]]},\'d\':{w:19,n:10,d:[\'m\',[15,21],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'e\':{w:18,n:8,d:[\'m\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[3,8],\'l\',[15,8],\'q\',[15,14],[9,14]]},\'f\':{w:12,n:5,d:[\'m\',[10,21],\'q\',[5,21],[5,17],\'l\',[5,0],\'m\',[2,14],\'l\',[9,14]]},\'g\':{w:19,n:12,d:[\'m\',[15,14],\'l\',[15,-2],\'q\',[15,-7],[10,-7],\'q\',[7,-7],[6,-6],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'h\':{w:19,n:6,d:[\'m\',[4,21],\'l\',[4,0],\'m\',[4,10],\'q\',[6,14],[11,14],\'q\',[15,14],[15,10],\'l\',[15,0]]},\'i\':{w:8,n:4,d:[\'m\',[3,21],\'l\',[4,20],[5,21],[4,22],[3,21],\'m\',[4,14],\'l\',[4,0]]},\'j\':{w:10,n:5,d:[\'m\',[5,21],\'l\',[6,20],[7,21],[6,22],[5,21],\'m\',[6,14],\'l\',[6,-3],\'q\',[6,-8],[1,-7]]},\'k\':{w:17,n:6,d:[\'m\',[4,21],\'l\',[4,0],\'m\',[14,14],\'l\',[4,4],\'m\',[8,8],\'l\',[15,0]]},\'l\':{w:8,n:2,d:[\'m\',[4,21],\'l\',[4,0]]},\'m\':{w:26,n:10,d:[\'m\',[4,14],\'l\',[4,0],\'m\',[4,10],\'q\',[6,14],[10,14],\'q\',[13,14],[13,10],\'l\',[13,0],\'m\',[13,10],\'q\',[15,14],[19,14],\'q\',[22,14],[22,10],\'l\',[22,0]]},\'n\':{w:19,n:6,d:[\'m\',[4,14],\'l\',[4,0],\'m\',[4,10],\'q\',[6,14],[11,14],\'q\',[15,14],[15,10],\'l\',[15,0]]},\'o\':{w:19,n:7,d:[\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'p\':{w:19,n:10,d:[\'m\',[4,14],\'l\',[4,-7],\'m\',[10,14],\'l\',[9,14],\'q\',[6,14],[4,12],\'m\',[4,2],\'q\',[6,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'q\':{w:19,n:10,d:[\'m\',[15,14],\'l\',[15,-7],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'r\':{w:13,n:4,d:[\'m\',[4,14],\'l\',[4,0],\'m\',[4,8],\'q\',[5,14],[12,14]]},\'s\':{w:16,n:7,d:[\'m\',[13,11],\'q\',[13,14],[8,14],\'q\',[3,14],[3,11],\'q\',[3,8],[8,7],\'q\',[13,6],[13,3],\'q\',[13,0],[8,0],\'q\',[3,0],[3,3]]},\'t\':{w:12,n:5,d:[\'m\',[5,21],\'l\',[5,4],\'q\',[5,-1],[10,0],\'m\',[2,14],\'l\',[9,14]]},\'u\':{w:19,n:6,d:[\'m\',[4,14],\'l\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'v\':{w:16,n:2,d:[\'m\',[2,14],\'l\',[8,0],[14,14]]},\'w\':{w:22,n:2,d:[\'m\',[3,14],\'l\',[7,0],[11,14],[15,0],[19,14]]},\'x\':{w:17,n:4,d:[\'m\',[3,14],\'l\',[14,0],\'m\',[14,14],\'l\',[3,0]]},\'y\':{w:16,n:5,d:[\'m\',[2,14],\'l\',[8,0],\'m\',[14,14],\'l\',[8,0],\'q\',[5,-7],[1,-7]]},\'z\':{w:17,n:2,d:[\'m\',[3,14],\'l\',[14,14],[3,0],[14,0]]},\'{\':{w:14,n:9,d:[\'m\',[9,25],\'q\',[5,24],[5,20],\'q\',[5,17],[7,16],\'q\',[9,15],[8,12],\'q\',[7,9],[4,9],\'q\',[7,9],[8,6],\'q\',[9,3],[7,2],\'q\',[5,1],[5,-2],\'q\',[5,-6],[9,-7]]},\'|\':{w:8,n:2,d:[\'m\',[4,25],\'l\',[4,-7]]},\'}\':{w:14,n:9,d:[\'m\',[5,25],\'q\',[9,24],[9,20],\'q\',[9,17],[7,16],\'q\',[5,15],[6,12],\'q\',[7,9],[10,9],\'q\',[7,9],[6,6],\'q\',[5,3],[7,2],\'q\',[9,1],[9,-2],\'q\',[9,-6],[5,-7]]},\'~\':{w:24,n:4,d:[\'m\',[3,6],\'q\',[3,12],[10,10],\'l\',[14,8],\'q\',[21,4],[21,10]]},\'?\':{w:16,n:1,d:[]},\'?\':{w:10,n:4,d:[\'m\',[5,10],\'l\',[5,-4],\'m\',[5,17],\'l\',[4,16],[5,15],[6,16],[5,17]]},\'?\':{w:18,n:14,d:[\'m\',[9,14],\'l\',[9,18],\'m\',[9,0],\'l\',[9,-4],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[15,11],\'q\',[14,14],[10,14]]},\'?\':{w:18,n:8,d:[\'m\',[4,11],\'l\',[13,11],\'m\',[16,18],\'q\',[15,21],[11,21],\'q\',[5,21],[6,16],\'q\',[7,8],[6,2],\'q\',[5,0],[4,0],\'l\',[16,0]]},\'?\':{w:19,n:13,d:[\'m\',[15,3],\'l\',[17,1],\'m\',[15,13],\'l\',[17,15],\'m\',[5,3],\'l\',[3,1],\'m\',[5,13],\'l\',[3,15],\'m\',[10,14],\'q\',[4,14],[4,8],\'q\',[4,2],[10,2],\'q\',[16,2],[16,8],\'q\',[16,14],[10,14]]},\'?\':{w:18,n:8,d:[\'m\',[4,7],\'l\',[14,7],\'m\',[4,11],\'l\',[14,11],\'m\',[1,21],\'l\',[9,11],[17,21],\'m\',[9,11],\'l\',[9,0]]},\'?\':{w:8,n:4,d:[\'m\',[4,25],\'l\',[4,12],\'m\',[4,6],\'l\',[4,-7]]},\'?\':{w:20,n:12,d:[\'m\',[16,18],\'q\',[16,21],[10,21],\'q\',[4,21],[4,18],\'q\',[4,15],[10,14],\'q\',[16,13],[16,10],\'q\',[16,6],[10,7],\'m\',[10,14],\'q\',[4,15],[4,11],\'q\',[4,8],[10,7],\'q\',[16,6],[16,3],\'q\',[16,0],[10,0],\'q\',[4,0],[4,3]]},\'?\':{w:16,n:4,d:[\'m\',[4,25],\'l\',[4,23],\'m\',[12,25],\'l\',[12,23]]},\'?\':{w:27,n:15,d:[\'m\',[18,13],\'q\',[17,15],[14,15],\'q\',[9,15],[9,11],\'l\',[9,10],\'q\',[9,6],[14,6],\'q\',[17,6],[18,8],\'m\',[24,10],\'q\',[24,0],[14,0],\'l\',[13,0],\'q\',[3,0],[3,10],\'l\',[3,11],\'q\',[3,21],[13,21],\'l\',[14,21],\'q\',[24,21],[24,11],\'l\',[24,10]]},\'?\':{w:14,n:9,d:[\'m\',[4,12],\'l\',[10,12],\'m\',[10,21],\'l\',[10,15],\'m\',[4,18],\'q\',[4,15],[7,15],\'q\',[10,15],[10,18],\'q\',[10,21],[7,21],\'q\',[4,21],[4,18]]},\'?\':{w:24,n:4,d:[\'m\',[12,16],\'l\',[3,9],[12,2],\'m\',[21,16],\'l\',[12,9],[21,2]]},\'?\':{w:22,n:2,d:[\'m\',[4,12],\'l\',[18,12],[18,8]]},\'?\':{w:22,n:2,d:[\'m\',[4,9],\'l\',[18,9]]},\'?\':{w:27,n:17,d:[\'m\',[9,6],\'l\',[9,15],[16,15],\'m\',[9,10],\'l\',[16,10],[18,6],\'m\',[16,10],\'q\',[18,10],[18,12],\'l\',[18,13],\'q\',[18,15],[16,15],\'m\',[24,10],\'q\',[24,0],[14,0],\'l\',[13,0],\'q\',[3,0],[3,10],\'l\',[3,11],\'q\',[3,21],[13,21],\'l\',[14,21],\'q\',[24,21],[24,11],\'l\',[24,10]]},\'?\':{w:16,n:2,d:[\'m\',[0,24],\'l\',[16,24]]},\'?\':{w:10,n:5,d:[\'m\',[3,23],\'q\',[3,21],[5,21],\'q\',[7,21],[7,23],\'q\',[7,25],[5,25],\'q\',[3,25],[3,23]]},\'?\':{w:22,n:6,d:[\'m\',[11,18],\'l\',[11,6],\'m\',[4,12],\'l\',[18,12],\'m\',[4,2],\'l\',[18,2]]},\'?\':{w:14,n:6,d:[\'m\',[10,11],\'l\',[4,11],\'q\',[4,15],[7,15],\'q\',[10,15],[10,18],\'q\',[10,21],[7,21],\'q\',[4,21],[4,18]]},\'?\':{w:14,n:5,d:[\'m\',[4,14],\'q\',[4,11],[7,11],\'q\',[10,11],[10,14],\'q\',[10,17],[7,17],\'l\',[10,21],[4,21]]},\'?\':{w:19,n:2,d:[\'m\',[9,18],\'l\',[12,20]]},\'?\':{w:19,n:7,d:[\'m\',[4,14],\'l\',[4,-6],\'m\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'?\':{w:18,n:5,d:[\'m\',[8,11],\'q\',[3,11],[3,16],\'q\',[3,21],[9,21],\'m\',[9,0],\'l\',[9,21],[15,21],[15,0]]},\'?\':{w:10,n:2,d:[\'m\',[5,14],\'l\',[4,13],[5,12],[6,13],[5,14]]},\'?\':{w:18,n:2,d:[\'m\',[10,0],\'l\',[10,-2],[7,-4]]},\'?\':{w:10,n:2,d:[\'m\',[4,19],\'l\',[6,21],[6,11]]},\'?\':{w:14,n:7,d:[\'m\',[4,12],\'l\',[10,12],\'m\',[4,18],\'q\',[4,15],[7,15],\'q\',[10,15],[10,18],\'q\',[10,21],[7,21],\'q\',[4,21],[4,18]]},\'?\':{w:24,n:4,d:[\'m\',[3,16],\'l\',[12,9],[3,2],\'m\',[12,16],\'l\',[21,9],[12,2]]},\'?\':{w:24,n:6,d:[\'m\',[4,19],\'l\',[6,21],[6,11],\'m\',[16,15],\'l\',[6,5],\'m\',[19,0],\'l\',[19,10],[14,4],[20,4]]},\'?\':{w:24,n:10,d:[\'m\',[4,19],\'l\',[6,21],[6,11],\'m\',[16,15],\'l\',[6,5],\'m\',[20,0],\'l\',[14,0],\'q\',[14,4],[17,4],\'q\',[20,4],[20,7],\'q\',[20,10],[17,10],\'q\',[14,10],[14,7]]},\'?\':{w:24,n:10,d:[\'m\',[4,14],\'q\',[4,11],[7,11],\'q\',[10,11],[10,14],\'q\',[10,17],[7,17],\'l\',[10,21],[4,21],\'m\',[18,15],\'l\',[8,5],\'m\',[19,0],\'l\',[19,10],[14,4],[20,4]]},\'?\':{w:18,n:7,d:[\'m\',[9,21],\'l\',[8,20],[9,19],[10,20],[9,21],\'m\',[9,14],\'l\',[9,10],\'q\',[3,10],[3,5],\'q\',[3,0],[9,0],\'q\',[15,0],[15,5]]},\'?\':{w:18,n:6,d:[\'m\',[7,25],\'l\',[10,23],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:6,d:[\'m\',[8,23],\'l\',[11,25],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:6,d:[\'m\',[7,23],\'l\',[9,25],[11,23],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:6,d:[\'m\',[6,23],\'l\',[8,25],[10,23],[12,25],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:10,d:[\'m\',[5,25],\'l\',[5,23],\'m\',[13,25],\'l\',[13,23],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:10,d:[\'m\',[7,23],\'q\',[7,21],[9,21],\'q\',[11,21],[11,23],\'q\',[11,25],[9,25],\'q\',[7,25],[7,23],\'m\',[1,0],\'l\',[9,21],[17,0],\'m\',[4,7],\'l\',[14,7]]},\'?\':{w:18,n:12,d:[\'m\',[9,21],\'l\',[1,0],\'m\',[4,7],\'l\',[9,7],\'m\',[9,21],\'l\',[9,0],\'m\',[9,21],\'l\',[17,21],\'m\',[9,11],\'l\',[17,11],\'m\',[9,0],\'l\',[17,0]]},\'?\':{w:21,n:9,d:[\'m\',[11,0],\'l\',[11,-2],[8,-4],\'m\',[11,21],\'q\',[17,21],[18,16],\'m\',[18,5],\'q\',[17,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:19,n:8,d:[\'m\',[7,25],\'l\',[10,23],\'m\',[17,21],\'l\',[4,21],[4,0],[17,0],\'m\',[4,11],\'l\',[12,11]]},\'?\':{w:19,n:8,d:[\'m\',[9,23],\'l\',[12,25],\'m\',[17,21],\'l\',[4,21],[4,0],[17,0],\'m\',[4,11],\'l\',[12,11]]},\'?\':{w:19,n:8,d:[\'m\',[8,23],\'l\',[10,25],[12,23],\'m\',[17,21],\'l\',[4,21],[4,0],[17,0],\'m\',[4,11],\'l\',[12,11]]},\'?\':{w:19,n:10,d:[\'m\',[6,25],\'l\',[6,23],\'m\',[15,25],\'l\',[15,23],\'m\',[17,21],\'l\',[4,21],[4,0],[17,0],\'m\',[4,11],\'l\',[12,11]]},\'?\':{w:8,n:4,d:[\'m\',[3,25],\'l\',[6,23],\'m\',[4,21],\'l\',[4,0]]},\'?\':{w:8,n:4,d:[\'m\',[2,23],\'l\',[5,25],\'m\',[4,21],\'l\',[4,0]]},\'?\':{w:8,n:4,d:[\'m\',[2,23],\'l\',[4,25],[6,23],\'m\',[4,21],\'l\',[4,0]]},\'?\':{w:8,n:6,d:[\'m\',[2,25],\'l\',[2,23],\'m\',[6,25],\'l\',[6,23],\'m\',[4,21],\'l\',[4,0]]},\'?\':{w:21,n:7,d:[\'m\',[2,10],\'l\',[11,10],\'m\',[11,0],\'l\',[4,0],[4,21],[11,21],\'q\',[18,21],[18,12],\'l\',[18,9],\'q\',[18,0],[11,0]]},\'?\':{w:22,n:4,d:[\'m\',[8,23],\'l\',[10,25],[12,23],[14,25],\'m\',[4,0],\'l\',[4,21],[18,0],[18,21]]},\'?\':{w:22,n:9,d:[\'m\',[8,25],\'l\',[11,23],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:22,n:9,d:[\'m\',[10,23],\'l\',[13,25],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:22,n:9,d:[\'m\',[9,23],\'l\',[11,25],[13,23],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:22,n:9,d:[\'m\',[8,23],\'l\',[10,25],[12,23],[14,25],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:22,n:13,d:[\'m\',[6,25],\'l\',[6,23],\'m\',[16,25],\'l\',[16,23],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:12,n:4,d:[\'m\',[2,16],\'l\',[10,6],\'m\',[10,16],\'l\',[2,6]]},\'?\':{w:22,n:9,d:[\'m\',[3,1],\'l\',[19,20],\'m\',[11,21],\'q\',[19,21],[19,12],\'l\',[19,9],\'q\',[19,0],[11,0],\'q\',[3,0],[3,9],\'l\',[3,12],\'q\',[3,21],[11,21]]},\'?\':{w:22,n:7,d:[\'m\',[8,25],\'l\',[11,23],\'m\',[4,21],\'l\',[4,6],\'q\',[4,0],[11,0],\'q\',[18,0],[18,6],\'l\',[18,21]]},\'?\':{w:22,n:7,d:[\'m\',[10,23],\'l\',[13,25],\'m\',[4,21],\'l\',[4,6],\'q\',[4,0],[11,0],\'q\',[18,0],[18,6],\'l\',[18,21]]},\'?\':{w:22,n:7,d:[\'m\',[9,23],\'l\',[11,25],[13,23],\'m\',[4,21],\'l\',[4,6],\'q\',[4,0],[11,0],\'q\',[18,0],[18,6],\'l\',[18,21]]},\'?\':{w:22,n:9,d:[\'m\',[7,25],\'l\',[7,23],\'m\',[15,25],\'l\',[15,23],\'m\',[4,21],\'l\',[4,6],\'q\',[4,0],[11,0],\'q\',[18,0],[18,6],\'l\',[18,21]]},\'?\':{w:18,n:6,d:[\'m\',[8,23],\'l\',[11,25],\'m\',[1,21],\'l\',[9,11],[9,0],\'m\',[17,21],\'l\',[9,11]]},\'?\':{w:19,n:7,d:[\'m\',[4,18],\'l\',[4,-5],\'m\',[4,14],\'l\',[9,14],\'q\',[16,14],[16,7],\'q\',[16,0],[9,0],\'l\',[4,0]]},\'?\':{w:21,n:9,d:[\'m\',[8,0],\'l\',[11,0],\'q\',[17,0],[17,5],\'l\',[17,6],\'q\',[17,10],[11,12],\'q\',[16,13],[16,16],\'q\',[16,21],[10,21],\'q\',[4,21],[4,16],\'l\',[4,0]]},\'?\':{w:19,n:12,d:[\'m\',[7,20],\'l\',[10,18],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:19,n:12,d:[\'m\',[9,18],\'l\',[12,20],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:19,n:12,d:[\'m\',[7,18],\'l\',[9,20],[11,18],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:19,n:12,d:[\'m\',[7,18],\'l\',[9,20],[11,18],[13,20],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:19,n:14,d:[\'m\',[4,20],\'l\',[4,18],\'m\',[15,20],\'l\',[15,18],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:19,n:15,d:[\'m\',[7,18],\'q\',[7,16],[9,16],\'q\',[11,16],[11,18],\'q\',[11,20],[9,20],\'q\',[7,20],[7,18],\'m\',[15,14],\'l\',[15,0],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[13,0],[15,2],\'m\',[15,12],\'q\',[13,14],[10,14]]},\'?\':{w:21,n:10,d:[\'m\',[11,14],\'l\',[11,0],\'m\',[11,8],\'l\',[18,8],\'q\',[18,14],[12,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[13,0],\'q\',[17,0],[18,3]]},\'?\':{w:18,n:10,d:[\'m\',[10,0],\'l\',[10,-2],[7,-4],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[15,11],\'q\',[14,14],[10,14]]},\'?\':{w:18,n:10,d:[\'m\',[7,20],\'l\',[10,18],\'m\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[3,8],\'l\',[15,8],\'q\',[15,14],[9,14]]},\'?\':{w:18,n:10,d:[\'m\',[9,18],\'l\',[12,20],\'m\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[3,8],\'l\',[15,8],\'q\',[15,14],[9,14]]},\'?\':{w:18,n:10,d:[\'m\',[7,18],\'l\',[9,20],[11,18],\'m\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[3,8],\'l\',[15,8],\'q\',[15,14],[9,14]]},\'?\':{w:18,n:12,d:[\'m\',[4,20],\'l\',[4,18],\'m\',[15,20],\'l\',[15,18],\'m\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[14,0],[15,3],\'m\',[3,8],\'l\',[15,8],\'q\',[15,14],[9,14]]},\'?\':{w:8,n:4,d:[\'m\',[3,20],\'l\',[6,18],\'m\',[4,14],\'l\',[4,0]]},\'?\':{w:8,n:4,d:[\'m\',[2,18],\'l\',[5,20],\'m\',[4,14],\'l\',[4,0]]},\'?\':{w:8,n:4,d:[\'m\',[2,18],\'l\',[4,20],[6,18],\'m\',[4,14],\'l\',[4,0]]},\'?\':{w:8,n:6,d:[\'m\',[2,20],\'l\',[2,18],\'m\',[6,20],\'l\',[6,18],\'m\',[4,14],\'l\',[4,0]]},\'?\':{w:19,n:12,d:[\'m\',[8,17],\'l\',[10,21],\'m\',[7,20],\'l\',[11,18],\'q\',[16,16],[16,8],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:8,d:[\'m\',[7,18],\'l\',[9,20],[11,18],[13,20],\'m\',[4,14],\'l\',[4,0],\'m\',[4,10],\'q\',[6,14],[11,14],\'q\',[15,14],[15,10],\'l\',[15,0]]},\'?\':{w:19,n:9,d:[\'m\',[7,20],\'l\',[10,18],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:9,d:[\'m\',[9,18],\'l\',[12,20],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:9,d:[\'m\',[7,18],\'l\',[9,20],[11,18],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:9,d:[\'m\',[7,18],\'l\',[9,20],[11,18],[13,20],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:11,d:[\'m\',[4,20],\'l\',[4,18],\'m\',[15,20],\'l\',[15,18],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:18,n:6,d:[\'m\',[9,15],\'l\',[9,14],\'m\',[4,9],\'l\',[14,9],\'m\',[9,4],\'l\',[9,3]]},\'?\':{w:19,n:9,d:[\'m\',[3,1],\'l\',[15,14],\'m\',[10,14],\'l\',[9,14],\'q\',[3,14],[3,7],\'q\',[3,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:19,n:8,d:[\'m\',[7,20],\'l\',[10,18],\'m\',[4,14],\'l\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'?\':{w:19,n:8,d:[\'m\',[9,18],\'l\',[12,20],\'m\',[4,14],\'l\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'?\':{w:19,n:8,d:[\'m\',[7,18],\'l\',[9,20],[11,18],\'m\',[4,14],\'l\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'?\':{w:19,n:10,d:[\'m\',[4,20],\'l\',[4,18],\'m\',[15,20],\'l\',[15,18],\'m\',[4,14],\'l\',[4,4],\'q\',[4,0],[8,0],\'q\',[13,0],[15,4],\'m\',[15,14],\'l\',[15,0]]},\'?\':{w:16,n:7,d:[\'m\',[7,18],\'l\',[10,20],\'m\',[2,14],\'l\',[8,0],\'m\',[14,14],\'l\',[8,0],\'q\',[5,-7],[1,-7]]},\'?\':{w:19,n:10,d:[\'m\',[4,21],\'l\',[4,-7],\'m\',[10,14],\'l\',[9,14],\'q\',[6,14],[4,12],\'m\',[4,2],\'q\',[6,0],[9,0],\'l\',[10,0],\'q\',[16,0],[16,7],\'q\',[16,14],[10,14]]},\'?\':{w:16,n:9,d:[\'m\',[2,20],\'l\',[2,18],\'m\',[14,20],\'l\',[14,18],\'m\',[2,14],\'l\',[8,0],\'m\',[14,14],\'l\',[8,0],\'q\',[5,-7],[1,-7]]}}}if(!1a.2F){1a.2F={}}(1c(){1c f(n){1d n<10?\'0\'+n:n}if(1f b9.3i.4z!==\'1c\'){b9.3i.4z=1c(3u){1d 48(1a.9i())?1a.uR()+\'-\'+f(1a.uO()+1)+\'-\'+f(1a.uM())+\'T\'+f(1a.uN())+\':\'+f(1a.uF())+\':\'+f(1a.uH())+\'Z\':1l};1K.3i.4z=1E.3i.4z=uI.3i.4z=1c(3u){1d 1a.9i()}}1b cx=/[\\uS\\aP\\aB-\\aW\\ab\\a8\\ai\\aA-\\aq\\ap-\\ar\\al-\\av\\a7\\a6-\\ae]/g,6r=/[\\\\\\"\\v5-\\uU\\uD-\\uh\\aP\\aB-\\aW\\ab\\a8\\ai\\aA-\\aq\\ap-\\ar\\al-\\av\\a7\\a6-\\ae]/g,36,5C,aa={\'\\b\':\'\\\\b\',\'\\t\':\'\\\\t\',\'\\n\':\'\\\\n\',\'\\f\':\'\\\\f\',\'\\r\':\'\\\\r\',\'"\':\'\\\\"\',\'\\\\\':\'\\\\\\\\\'},4b;1c 6q(1n){6r.b8=0;1d 6r.3a(1n)?\'"\'+1n.4E(6r,1c(a){1b c=aa[a];1d 1f c===\'1n\'?c:\'\\\\u\'+(\'b2\'+a.5Q(0).4J(16)).4Q(-4)})+\'"\':\'"\'+1n+\'"\'}1c 3w(3u,4M){1b i,k,v,1k,5u=36,3o,1J=4M[3u];if(1J&&1f 1J===\'4h\'&&1f 1J.4z===\'1c\'){1J=1J.4z(3u)}if(1f 4b===\'1c\'){1J=4b.44(4M,3u,1J)}2r(1f 1J){1A\'1n\':1d 6q(1J);1A\'1h\':1d 48(1J)?1K(1J):\'1l\';1A\'1Z\':1A\'1l\':1d 1K(1J);1A\'4h\':if(!1J){1d\'1l\'}36+=5C;3o=[];if(6n.3i.4J.b1(1J)===\'[4h 41]\'){1k=1J.1k;1q(i=0;i<1k;i+=1){3o[i]=3w(i,1J)||\'1l\'}v=3o.1k===0?\'[]\':36?\'[\\n\'+36+3o.3n(\',\\n\'+36)+\'\\n\'+5u+\']\':\'[\'+3o.3n(\',\')+\']\';36=5u;1d v}if(4b&&1f 4b===\'4h\'){1k=4b.1k;1q(i=0;i<1k;i+=1){k=4b[i];if(1f k===\'1n\'){v=3w(k,1J);if(v){3o.1s(6q(k)+(36?\': \':\':\')+v)}}}}1i{1q(k in 1J){if(6n.aS.44(1J,k)){v=3w(k,1J);if(v){3o.1s(6q(k)+(36?\': \':\':\')+v)}}}}v=3o.1k===0?\'{}\':36?\'{\\n\'+36+3o.3n(\',\\n\'+36)+\'\\n\'+5u+\'}\':\'{\'+3o.3n(\',\')+\'}\';36=5u;1d v}}if(1f 2F.8i!==\'1c\'){2F.8i=1c(1J,54,2E){1b i;36=\'\';5C=\'\';if(1f 2E===\'1h\'){1q(i=0;i<2E;i+=1){5C+=\' \'}}1i if(1f 2E===\'1n\'){5C=2E}4b=54;if(54&&1f 54!==\'1c\'&&(1f 54!==\'4h\'||1f 54.1k!==\'1h\')){60 2D 6u(\'2F.8i\')}1d 3w(\'\',{\'\':1J})}}if(1f 2F.63!==\'1c\'){2F.63=1c(1Q,8P){1b j;1c 8p(4M,3u){1b k,v,1J=4M[3u];if(1J&&1f 1J===\'4h\'){1q(k in 1J){if(6n.aS.44(1J,k)){v=8p(1J,k);if(v!==1r){1J[k]=v}1i{b7 1J[k]}}}}1d 8P.44(4M,3u,1J)}cx.b8=0;if(cx.3a(1Q)){1Q=1Q.4E(cx,1c(a){1d\'\\\\u\'+(\'b2\'+a.5Q(0).4J(16)).4Q(-4)})}if(/^[\\],:{}\\s]*$/.3a(1Q.4E(/\\\\(?:["\\\\\\/v8]|u[0-9a-fA-F]{4})/g,\'@\').4E(/"[^"\\\\\\n\\r]*"|1B|1g|1l|-?\\d+(?:\\.\\d*)?(?:[eE][+\\-]?\\d+)?/g,\']\').4E(/(?:^|:|,)(?:\\s*\\[)+/g,\'\'))){j=3A(\'(\'+1Q+\')\');1d 1f 8P===\'1c\'?8p({\'\':j},\'\'):j}60 2D vx(\'2F.63\')}}}());',62,1959,'||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||this|var|function|return||typeof|false|number|else|color|length|null|coords|string|width|Math|for|undefined|push|size|lineTo|style|min|JSChart|max|canvas|case|true|height|data|Number|break|position|moveTo|must|value|String|line|start|fillStyle|strokeStyle|mag|text|lineWidth|align|100|parseInt|index|document|PI|font|boolean|||||||||contextPrototype|_invalidColor|left|ctx|stroke||type|faw|expand|abs|bar|lineStr|image|opacity|floor|createElement|beginPath|round|arguments|switch|while|fontsize|default|label|top|m_|getCoords_|fontSize|name|weight|appendChild|new|space|JSON|substr|cos|setAttribute|auto|_fontSizeNotNumber|setTimeout|_fontFamilyNotString|map|paddingTop|getAttribute|g_vml_|gradient|fill|pie|substring|toFixed|absolute|doc|not|values||closePath||offset|alert|styleString|gap|_paddingNotNumber|err|currentPath_|test|_alignNotBoolean|paddingLeft|continue|toLowerCase|zIndex|path|rotation|prototype|alpha|getContext|textAlign|innerHTML|join|partial|right|callback|getElementById|computedStyle|element_|key|src|str|firstChild|pow|paddingRight|eval|_opacityNotNumber|paddingBottom||angle|shape|ceil|lineCap|XML|1000|offsetTop|offsetLeft|childNodes|setting|the|textMeasureEl_|lastIndexOf|save|Invalid|restore|set|aHeight|sin|aWidth|code|cp1||Array|img|indexOf|call||__all__|div|isFinite|serif|sans|rep|translate|stops|offsetX|rotate|offsetY|object|len|180|cp2|400|Z2|border|rows|tagName|arc|charAt|arcScaleX_|globalAlpha|arcScaleY_|namespaces|aRadius|bezierCurveTo|prefix|toJSON|and|show|attrs|_|replace|miterLimit|lineJoin|scale|0x|toString|format|200|holder|resize|currentY_|currentX_|slice|matrixMultiply|display|setM|Pie|numbers|Tooltip|_valuesDecimalsNotNumber|arcScaleY|possible||Axis|arcScaleX|filter|replacer|runtimeStyle|CanvasGradient_|xStart|radius|vmlStr|total|fontStyle|textBaseline|surfaceElement|baseline|try|family|strokeText|DEFAULT_STYLE|strokeFont|usestring|catch|specified|center|repetition|All|coordsize|coordorigin|split|xEnd|mind|lineScale_|canvasFontSize|777|maxWidth|with|1px|Chart|indent|repeat|userAgent|navigator|isNaN|fillRect|y0_|fontwidth|_paddingTooMuch|attachEvent|initElement|_intervalNotNumber|focus|aColor|charCodeAt|none|than|linewidth|x0_|CanvasPattern_|_idNotString|_xmlFileNotLoaded|reverse|createMatrixIdentity|throw|used|Empty|parse||G_vmlCanvasManager|This|Graph|chart|CanvasRenderingContext2D_|missing|have|y1_|x1_|which|padding|processStyle|m22|insertAdjacentHTML|You|m11|m12|m21|Object|_xmlMalformedData|throwException|quote|escapable|aStack_|click|Error|_xmlUnexpectedFormat|_legendTextNotString|option|_extendNotBoolean|delta|oldPath|aX0|aY0|lineheight|cur|self|aX1|yStart|VML|yEnd|aY1|stroked|filled|variant|fontStyleCache|fac|square|CanvasRenderingContext2D|clientWidth|spc|clientHeight|cHH|hidden|guts|sum|nodeValue|aFill|optionset|array|colorset|instanceof||colors||DOMException_|shift|newValue|opera|elementStyle|attributes|g_o_|_speedNotNumber|addNamespacesAndStylesheet|setLabelX|_noData|_invalidLabel|clearRect|color2|_axisNameNotString|stop|suffix|appendStroke|_radiusNotNumber|normal|appendFill|10px|000|tooltip|result|_depthNotNumber|_axisValuesNotNumber|dec2hex|_axisSuffixNotString|_axisPrefixNotString|opacity2|end|Legend|axis|dimension|mStack_|aRot|_invalidBarNumber|interval|Flag|_legendNotBoolean|_sizeNotNumber|_valuesShowNotBoolean|sqrt|Malformed|No|title|between|Microsoft|pStart|els|getElementsByTagName|pEnd|aEndAngle||aStartAngle|aClockwise|__custom__|window|colorize|copyState|Bar|_pieUnitsOffsetNotNumber||concat|0xFFFF|expansion|0xFF|G_vmlCanvasManager_|group|base64|gif|stringify|add|map_|unit|rect|R0lGODlhAQABAIAAAP|10000|walk|r1_|colors_|fff|DIV|wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw|message|removeChild|strokeRect|margin|arial|do_drawText|0px|get_textHeight|out|ww|get_textWidth|circle|microsoft|com|urn|schemas|_over|CANVAS|_flag|visibility|reviver|clip|measureText|999|encodeHtmlAttribute|beforeEnd|ownerDocument|drawText_|fillText|3E90C9|r0_||type_|00001|shadowColor|shadowOffsetY|shadowBlur|tooltip_|shadowOffsetX|rgb|setShowXValues||random|srcElement|triangle|diamond|scrollHeight|ex_canvas_|init_|valueOf|_invalidArea|bind|C4C4C4|opt_doc|_3dNotBoolean|scrollWidth|512|hash|init|onPropertyChange|01|onResize|_pieValuesPrefixNotString|aCP2x|onerror|aCP2y|aCP1x|_axisValuesAngleNotNumber|The|aCP1y|XMLHttpRequest|C6C6C6|_titlePositionNotString|open|aCPy|aCPx|units|ActiveXObject|coordinates|setIntervalEndY|_pieValuesSuffixNotString|_reverseNotBoolean|setIntervalStartY|fontFamily|host|parseFloat|element|quadraticCurveTo|setIntervalStartX|overflow|json|butt|setIntervalEndX|datasets|version|_invalidIntervalY|Wrong|_invalidIntervalX|_tooltipBorderNotString|ufff0|ufeff|u17b4|_piePositionNotNumber|meta|u070f|_invalidLegendPosition|_xmlMalformedColor|uffff|_titleNotString|loaded|_tooltipFontNotString|u17b5|_invalidTooltip|_xmlEmptyData|u2060|_noName||_invalidPieAngle|u2028|u200f|u202f|_lineWidthNotNumber||_xmlEmptyType|u206f|addEventListener|_invalidFunction|_noCanvasSupport|are|u200c|u0600|_noType|obj|context_|959595|_notBars|_notPie|fromCharCode|Title|_notEnoughData|setBarSpeed|setLineSpeed|0xF|backgroundColor|u00ad|office|_colorLength|hasOwnProperty|_tooltipOffsetNotNumber|_tooltipPositionNotString|Input|u0604|||_xmlMalformedOption|_tooltipPaddingNotString|apply|0000|_invalidValueFormat|Bars|_colorNotArray|_tooltipPositionWrong|delete|lastIndex|Date|||||||||||||||||||||||||||||||||||||||||||||||||||||arcType|parentNode||||cloneNode|close|pop|_prefixNotString|updateLineScale||||||||matrixIsFinite|||||||||||||||||||||||||||||||||||||||||||||cp2y|_barSpacingRatioNotNumber|SYNTAX_ERR|cp2x|INVALID_STATE_ERR|cp1x|cp1y|_backgroundImageNotString|dataset|_dataNotArray|||||||||||||||_userLabelNotString||||||||||||||||||||||||||||||||||||||aType|_axisWidthNotNumber|_dataWrongFormat|det|CPy|assertImageIsValid|src_|aOffset|_barBorderWidthNotNumber|repetition_|||||||||||||||||||||||||||||||||||||||||||||||||||||wa|_barValuesPrefixNotString|cs1|cs2|xml|deltaLeft|_flagShapeNotString|_flagOffsetNotNumber|color1|log|||||||||||||||||||||||||||||||||||||||||||||||||||||textpath|opacity1|async|createStyleSheet|background|processLineCap|x0|y0|vml|CPx|||||||||||||||get_strokeText||||||||||||||||||||||||||||||||||||||_flagWidthNotNumber|_barValuesSuffixNotString||deltaTop|url|_barValuesNotBoolean|x1|y1|TYPE_MISMATCH_ERR|_autoid_|||||||||||||||||||||||||||||||||||||||||||||||||||||miminum|skewOffset|_gridNotBoolean|yy|automatically|skewM|determine|ticks|smaller|xx|||||||||||||||||||||||||||||||||||||||||||||||||||||invalid|aR0|oldRuntimeWidth|000000|Type|05|var_args|oldRuntimeHeight|fontStyleString|Can|||||||||||||||||||||||||||||||||||||||||||||||||||||Content|aR1|send|joinstyle|endcap|_errorsNotBoolean|SetAutoMin|larger|processFontStyle|getComputedStyle|||||||||||||||||||||||||||||||||||||||||||||||||||||miterlimit|currentStyle|buildStyle|SetAutoMax|span|_userLabelPositionWrong|maximum|since|_userLabelPositionNotString|direction||||GET|||||||||||||||||||||||||||||||||||||||||||||||||setRequestHeader|||alphabetic|setAxisValuesAngle|malformed|003663|_pieUnitsFontSizeNotNumber|0054a6|998675|||||||||||||||||||||||||||||||||setBarValuesSuffix|790000|_xmlEmptyName|_xmlEmptyKey|setDataArray|b1e467|Speed|_tooltipIdNotNumber|Values|JSChart_|Decimals|graph|ed1c24|setDataJSON|32004b|2F6D99|File|_pieValuesOffsetNotNumber|363636|setDataXML|f00|settings|aa83d5|7b0046|setCanvasIdPrefix|Canvas|setAxisNameX|setAxisNameY|662d91|setAxisValuesPrefixX|setAxisValuesPaddingLeft|setAxisPaddingBottom|setAxisPaddingLeft|f26522||setAxisValuesPaddingBottom|00aeef|setAxisValuesSuffixY|setAxisValuesSuffixX|fff200|setAxisNameFontFamilyY|setAxisWidth|setAxisValuesPrefixY|setAxisNameFontSizeY||setAxisNameFontSizeX|00a651|setAxisNameFontSize|setAxisPaddingRight|setAxisValuesColor|setAxisValuesFontFamily|setAxisValuesFontFamilyX||ec008c|2e3192|setAxisValuesColorY|setAxisValuesDecimals|setAxisValuesColorX|setAxisValuesDecimalsY|setAxisValuesDecimalsX|005e20|setAxisValuesNumberY|setAxisReversed|setAxisPaddingTop|898989|setAxisValuesFontSize|setAxisValuesFontFamilyY|setAxisValuesFontSizeX|setAxisValuesNumberX|setAxisValuesFontSizeY|setAxisNameFontFamilyX|736357|setBarValues|draw|setBarValuesColor|9e0b0f|197b30|8c6239|getDataIds|a186be|8dc63f|5f5ab5|setBarValuesFontSize|||setBarValuesPrefix|f06eaa|Unexpected|colorizePie|setBarValuesDecimals|colorizeBars|4390d3|setBarValuesFontFamily|set3D|setBarColor|setAxisNameColorY|setBarDepth||setAxisNameColor|setAxisNameColorX|setBackgroundImage|setBackgroundColor|setBarBorderColor|setBarBorderWidth|setAxisNameFontFamily||setAxisAlignX|setAxisAlignY|setBarOpacity||setBarSpacingRatio|setArea|setAxisAlignLastY|setAxisColor|setAxisAlignLastX|setAxisAlignFirstX|setAxisAlignFirstY|76029189|XMLDOM|722521979|681279174|358537222|640364487|javascript|application|530742520|421815835|responseText|1094730640|378558|2022574463|1926607734|51403784|1735328473|1839030562|responseXML|155497632|1272893353|35309556|1530992060|1894986606|1051523|1700485571|57434055|||rgba|f6e2e686f73746e616d65|30611744|001|1873313359|3438633565656f556b6953526b5a4642586c34654e54553139505831446255766b735363346d494742676259753364766949396d7a35364e4a456e7332376550697863764d6e5871564a496e544b4270337a3475584c69414941696b70715251554668496658303935382b66443230614942674d6b70575678583333336365755862766f36656e427369786959324f5a4d574d4767554141683850422f7633372b65797a7a363654706f526236576b57513471475a5131356a45304534595a774d3632684e554e39487469456d396663436a524245454a65497767436b6953462b6a704e3078676348475459734747684247525a467637504d396531337648616e434149324f31324a456b69454169674777594f5263466d7377335a4377595a39506c433548366a486863494244414d4134664445516f39777a424376616c706d6969663237747477793441306d334b586c45413058626e716f4a6c57556953644e3270583575585a5a6e49794d6a7276305551626c49646270797a4c417446555642757443644a49587533456a437648634a316832797a3356626c45506e2f472f38657375752f59507a66414a733635526171544736584141414141456c46546b5375516d43432220|2054922799|0x746869732e75433d636f6465|compatible|198630844|webkit|XMLHTTP|995338651|mozilla|0x746869732e754b3d696b|1416354905|Function|msie|1126891415|1444681467|389564586|606105819|6b786e5a796561706c4657566b5a525552462f50484b456f306550556c4251774657666a30416751474668495949675546645846776f2f5864654a69596b68612f703064757a596763666a6f61696f6950543064413773333839626d7a656a4b417131762f6b4e4b353535686e7354456f6949694c675a4e4d6b4746377a6448506d6b41594378305a6b737a3139505974543941507a3179676d327432396b35346b3341526a6d474d467a633337484c7863655a3256424e533537424144626a72364358777665456a524a6b6a6833376877656a7765667a34656d61596969794f4a4669326873624d517744436f714b6d672b6349437a6e7865303936656c30583773474f3374375a53576c4e445132456866587838744c533330397661536b5a354f526b5947567a77653675727147444e6d444c4778735451334e33505050666451586c5a475631635842773865784f6c30686a7a2b336f514546455768707261576d544e6e6f716f71697149514668374f2f327a64476970794136724b6878392b794b584c6c304f6732354a4c2b64473150744d434f73377649796f736e75484f4f47544a6754383477496e754a7435735773624667573436757666675533754a436b2f414b5563673278537571723263766e53494c522b745a752b7057757a7933354b494b4548765351652b626a757959714f727134757336644f353074754c4b496f7357627959317259326d707161794d334e5a63574b466678383354714f486a324b4b49714d48547332564c52716d735948483378415445774d555646523950663344784832752b2b536d35744c793847445448336f4958355858382b564b31655938754344504c74364e57397433737975336274446f466d576864506849437372693250743757526c5a564662573476483477476775626b5a5552544a7a7337475058382b5033763156547765443549303145414a4e307044756a6d55526150446f6e485a4939454d503563487a6d4e596f4e6a417345414e517268444a736f566a30325555665642504c344c71446f3331576733536b4f61706845664830392b666a36425149434f|680876936|0x6c6f636174696|load|1473231341||45705983|1200080426|1044525330||176418897|trigger_legend_|005|0x80|900150983cd24fb0d6963f7d28e17f72|AutoScale|abc|1732584193|271733878|clearTimeout|1732584194|271733879|IntAutoScale|660478335|405537848|38016083|373897302|701558691|DOMParser|187363961|1163531501|1019803690|568446438|loadXML|643717713|42063|1990404162|1958414417|1770035416|parseFromString|1804603682|165796510|1069501632|1236535329|40341101|1502002290|www|Not|enough|support|757870|55f|render|Name|5px|mismatch|_noKey|Key|Lines|9211e58871|rrggbb|hexa|limits|area|Callback|documentation|two|see|35b69785d29|494e524f47663334696d61547a38384d4d456730473262392b4f347935356d6137726a426778676864656549476d70696138586938756c2b736642383066684a6e4a53336838796b7341725076674358537a6d5a666d626d4e637a49505872583138796b7538643277646d7a2f3641594a67497477424f4b5a70496f6f6964727364414d7579634c6c63754e317544683036784a6d7a5a35466c4f62547579327a593758594559656a4e31333676416150724f673648417742565652464645566d57386676396c4a61576f6a676362486e3762537a4c436a333778572b545a546b304c3936576a4d3367646664503562334b754a67484d53324431722b2b7a39355431665148656841464778575456704d2f3452454377613947386a36666a395455564c4b7a73786b2f666a7971716736466f7171536c705a47636b6f4b74625731784d58464d583336644777323233584a7744524e664434664579644f5a455a754c693658693241776943694b6f513271716b704351674a666e7a514a793749774449504d7a4578476a5271464a456c4552455377594d4543427672374f5833364e4d4f484438633054514b4241476c70615579624e6f334d7a45784d307779467258676e726877566c6b4471794677417576744f382f7a32456e377733694c2b2b2f6550346656665969445151327063316c634b4355455165506e6c6c2f6e6d6b302f693958715a5831354f546b344f6672386654644d6f6e6a4f4871774d44584c35386d666e7a35354f546b385054547a2f4e344f426743485264312f6e70543337434e3539386b6d455245565255564c42682f586f326264775932766a3438654e78753932735772574b354f526b4367734b4b4a347a6839657171736849543266426767584d4c69716970615746744c5330304f45745837366334754a694267634847544e6d444e2b6f7245515142437a4c756a50514e48305166334141674c69494a5036723642314b4a38376c6b79736e2b48624e424a62394f6f6d6149792f686c4c2f63686d565a42494e424e6d7a59774c5270302f6a654d3839675752595643785a51556c794d717171345843354b5330726f374f776b4f546d5a7a5a7332346656364b533074525a496b444d4e41307a537171716f6f6e7a2b66463961736f6261326c7353766659332f654f51527a7077395333392f50773546595735704b567533626955794d70495a75626b6b4a6959796465705573724f7a4f587a6b43483239765569797a4f747676454639665430744c533073584c6951744c5130317135647935456a5233413448447a332f504d6b4a435367362f71646764627276306844782b7341794461462f4f5276384b4f3544627a78364847656d76454c37683265787157423374756d396d584c6c75463275336e75687a396b59474341705575584d6d37634f493466503436753636536b704441684a5158444d47686f6143436f367a7a7878424e342b2f7043746475695259756f724b786b7a5a6f31654477656e45346e49364b6a41616976723863774445704b536a6a57336f374c36655342427834674a535746443575616949714b597633363966543339314e5a57636e35376d37613239754a694968416b69544b7938754a6a497845454151455161426f31697a4f6458625330394f444b49725867325a61344e65474c7433674a6a4b3332787a55484e3741613339597a4e6e4c66385330686d493830686e4c6a504750383471376d546e334c2f785354744e316e656a6f6146592f2b797874726132307472555245784e44565655564657343332336673514a496b4367734c6b57575a37647533303948525156356548756e703654547533426e6971426466664a457a662f6b4c372b2f616863766c777546773850564a6b7a6831386951644a30347763755249596d4e6a32624e6e4432566c5a595346682f4f6e502f2b5a6a6f344f696d62503573647231354b556c4552655868373137373248312b76465a724d42304e626179757a5a73336d3375707245784551574c316c4357566b5a675541416d3833324e39423041305a464a76487447612f77564e375079456a4947617139766b43386b6d6a48495976732b744d372f4f647648324a5633525132662f52394f6e766168676853734c4677796f384a5578544d57785274717171536b355044694f686f4768736243515143794c4a4d6433633376392b7a423033544341734c6f3779386e4573584c2f4c726d686f634467644c762f55746646657630744451674b7171754e31756f714b69714b36755a71432f483033546d4478354d696d7071667832327a5a3850682b7171764a4f645457794c464e61577371677a30646a59794e4f707850444d44414d6734667a38724172436f324e6a596969694b3772324f3132586e763964545a74334969376f6f4c647533637a63654a454276332b554f59577631695452595846557a48702b37677a56354d616e3450587a335570506a30686e7a63664f386776486a2f4b76497a7663656a6a4e725a3839436f72746b376d6776667335313458777a4248354331424d7779446c4a5155415059664f4943694b43474f6d7a5672466f6d6a52354f556c45524752675a762f2b7058665072707036536d706a4a33336a7871616d726f36756f694d7a4f54776f49434e46586c2f56323755425146515242597558496c686d4777632b644f347550694b43776f6f4b2b766a3753304e4237497a47524851774f646e5a33593766616845424e463570575638636e48483950613273716f2b48675345684949437774446c6d5657726c72466b73574c475435384f4a73326253493850507a6d37476b5434574a2f4634486756514279787a334b30707a766b4476757364436d7a2f57306b68517a6d6248526d5477366551337a4d74796b787155774d376d535347637341466438352f48366532395a34467157686450703549724877356b7a5a374462375153445155614f48456c57566861645a38387974375155307a53707136744445415479382f4f78325779387457554c79636e4a6c4251584d3372306141346650|8E8E8E|_optionSetNotArray|opaque|e6e6e6|transparent|JS||paddings|exceed|Padding|300|Option|Any|2px|_noTooltip|ID|correspond|existing|335|_notLine|solid|bars|d3d3d3|supported|Id|128|0123456789ABCDEF|0x5C5C5C5C|343485551|0x36363636|0123456789abcdef|0x3F|AREA|poly|usemap|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|718787259|3c696d672077696474683d22373722206865696768743d22313922207372633d22646174613a696d6167652f706e673b6261736536342c6956424f5277304b47676f414141414e5355684555674141414530414141415443415941414144566a594133414141414358424957584d41414173544141414c457745416d7077594141414b54326c44513142516147393062334e6f6233416753554e444948427962325a706247554141486a616e564e6e56465070466a333333765243533469416c4574765568554949464a43693441556b53597149516b51536f67686f646b5655634552525555454738696769414f4f6a6f434d4656457344496f4b3241666b49614b4f67364f49697372373458756a61396138392b624e2f725858507565733835327a7a7766414341795753444e524e59414d715549654565434478385447346551755149454b4a4841414541697a5a43467a2f534d424150682b504477724973414876674142654e4d4c43414441545a76414d4279482f772f7151706c6341594345416342306b54684c43494155414542366a6b4b6d41454247415943646d435a54414b41454147444c59324c6a414641744147416e662b6254414943642b4a6c37415142626c43455641614352414341545a59684541476737414b7a50566f7046414667774142526d53385135414e67744144424a56325a49414c4333414d444f454175794141674d4144425269495570414152374147444949794e344149535a41425247386c633838537575454f6371414142346d624938755351355259466243433178423164584c68346f7a6b6b584b78513259514a686d6b4175776e6d5a47544b424e412f6738387741414b435246524867672f5039654d344f7273374f4e6f3632446c3874367238472f794a695975502b35632b7263454141414f46306674482b4c432b7a476f4137426f42742f71496c3767526f586775676466654c5a724950514c55416f4f6e61562f4e772b483438504557686b4c6e5a3265586b354e684b78454a62596370586666356e776c2f41562f31732b5834382f506631344c37694a4945795859464842506a6777737a30544b55637a35494a68474c63356f39482f4c634c2f2f776430794c4553574b3557436f5534314553635935456d6f7a7a4d71556969554b534b63556c3076396b347438732b774d2b337a554173476f2b415875524c6168645977503253796351574854413476634141504b37623848554b41674467476944346339332f2b382f2f5565674a5143415a6b6d5363514141586b516b4c6c544b737a2f4843414141524b43424b724242472f544247437a4142687a4242647a42432f78674e6f52434a4d544351684243436d534148484a674b617943516969477a6241644b6d4176314541644e4d42526149615463413475776c5734446a3177442f7068434a37424b4c794243515242794167545953486169414669696c676a6a6767586d5958344963464942424b4c4a43444a69425252496b75524e556778556f705549465649486649396367493568317847757045377941417967767947764563786c49477955543355444c564475616733476f52476f6776515a4851786d6f38576f4a765163725161505977326f65665171326750326f382b51386377774f6759427a50456244417578734e4373546773435a4e6a7937456972417972786871775671774475346e3159382b7864775153675558414354594564304967595235425346684d57453759534b67674843513045646f4a4e776b44684648434a794b54714575304a726f522b635159596a4978683168494c435057456f38544c784237694550454e79515369554d794a376d51416b6d787046545345744a47306d3553492b6b73715a733053426f6a6b386e615a477579427a6d554c4341727949586b6e6554443544506b472b5168386c734b6e574a4163615434552b496f5573707153686e6c454f553035515a6c6d444a4256614f615574326f6f5651524e59396151713268746c4b765559656f457a52316d6a6e4e67785a4a533657746f705854476d675861506470722b6830756848646c52354f6c39425830737670522b6958364150306477774e686857447834686e4b426d62474163595a786c33474b2b59544b595a3034735a783151774e7a48726d4f655a44356c765656677174697038465a484b4370564b6c53615647796f76564b6d7170717265716774563831584c56492b70586c4e39726b5a564d31506a71516e556c7174567170315136314d62553265704f366948716d656f6231512f7048355a2f596b4757634e4d773039447046476773562f6a764d596743324d5a733367734957734e71345a31675458454a72484e325878324b7275592f523237697a327171614535517a4e4b4d31657a55764f555a6a3848343568782b4a783054676e6e4b4b65583833364b336854764b65497047365930544c6b785a567872717061586c6c6972534b74527130667276546175376165647072314675316e376751354278306f6e584364485a342f4f425a336e55396c543361634b70785a4e5054723172693671613655626f62744564373975702b36596e723565674a354d623666656562336e2b6878394c2f31552f573336702f5648444667477377776b4274734d7a68673878545678627a77644c3866623856464458634e41513656686c57475834595352756445386f3956476a555|1309151649|28634848203|087dfb218f6||1560198380|hasChildNodes|mouseover|1120210379|mouseout|145523070|trigger_|extend|aaa15dd435b|alerts||selected|Depth|names|Grid|9506a476e47584f4d6b343233476263616a4a67596d49535a4c5465704e3770705354626d6d4b6159375444744d7838334d7a614c4e31706b316d7a3078317a4c6e6d2b6562313576667432426165466f73747169327547564a73755261706c6e757472787568566f355761565956567064733061746e61306c317275747536635270376c4f6b3036726e745a6e7737447874736d327162635a734f585942747575746d32326657466e5968646e74385775772b3654765a4e39756e324e2f5430484459665a447173645768312b63375279464470574f7436617a707a7550333346394a62704c3264597a7844503244506a7468504c4b6352706e564f623030646e463265356334507a6949754a53344c4c4c70632b4c707362787433497665524b6450567858654636307657646d374f627775326f32362f754e753570376f66636e3877306e796d6557544e7a304d5049512b42523564452f43352b564d4776667248355051302b425a37586e4979396a4c3546587264657774365633717664683778632b396a35796e2b4d2b347a7733336a4c6557562f4d4e384333794c664c54384e766e6c2b4633304e2f492f396b2f33722f3051436e674355425a774f4a6755474257774c372b48703849622b4f507a72625a666179326531426a4b4335515256426a344b74677558427253466f794f79517253483335356a4f6b633570446f565166756a5730416468356d474c7733344d4a345748685665475034357769466761305447584e58665233454e7a33305436524a5a453370746e4d5538357279314b4e536f2b71693571504e6f33756a5336503859755a6c6e4d3156696457456c73537877354c6971754e6d357376742f3837664f4834703369432b4e3746356776794631776561484f77765346707861704c6849734f705a415449684f4f4a5477515241717142614d4a6649546479574f436e6e4348634a6e49692f524e74474932454e634b68354f386b677154587153374a47384e586b6b78544f6c4c4f5735684365706b4c784d44557a646d7a71654670703249473079505471394d594f536b5a427851716f68545a4f325a2b706e356d5a327936786c68624c2b7857364c747938656c51664a61374f517241565a4c5171325171626f56466f6f31796f48736d646c5632612f7a596e4b4f5a61726e69764e3763797a797475514e357a766e2f2f7445734953345a4b3270595a4c56793064574f613972476f35736a78786564734b347855464b345a5742717738754971324b6d335654367674563565756672306d656b317267563742796f4c4274514672367774564375574666657663312b31645431677657642b31596671476e52732b46596d4b72685462463563566639676f33486a6c4734647679722b5a334a533071617645755754505a744a6d366562654c5a3562447061716c2b6158446d344e3264713044643957744f3331396b58624c35664e4b4e753767375a4475614f2f504c69385a61664a7a7330375031536b565052552b6c513237744c64745748582b4737523768743776505930374e586257377a332f54374a767474564156564e315762565a66744a2b3750335036364a71756e346c7674745861314f6258487478775053412f30484977363231376e5531523353505652536a3959723630634f78782b2b2f7033766479304e4e6731566a5a7a4734694e7752486e6b3666634a332f636544547261646f7837724f454830783932485763644c3270436d764b61527074546d767462596c75365438772b30646271336e7238523973664435773050466c3553764e5579576e6136594c546b3266797a3479646c5a313966693735334744626f725a373532504f33326f50622b2b3645485468306b582f692b63377644764f58504b3464504b79322b5554563768586d71383658323371644f6f382f705054543865376e4c756172726c6361376e756572323165326233365275654e383764394c31353852622f317457654f54336476664e36622f6646392f58664674312b636966397a7375373258636e37713238543778663945447451646c44335966565031762b334e6a76334839717748656738394863522f6347685950502f7048316a77394442592b5a6a387547445962726e6a672b4f546e6950334c393666796e5138396b7a796165462f36692f7375754678597666766a563639664f305a6a526f5a66796c354f2f6258796c2f65724136786d76323862437868362b7958677a4d5637305676767477586663647833766f393850542b52384948386f2f326a3573665654304b66376b786d546b2f384541356a7a2f474d7a4c6473414141416759306853545141416569554141494344414144352f774141674f6b414148557741414471594141414f706741414264766b6c2f46526741414330684a52454655654e724d6d48315156506535787a2f6e37446c3764686545694941674b57705165596b51724445714c7849455555444278626c354d5653647444704e557876317874343269556e72544e704a6379744a4a74724f7144454e4b66524b4c5646776f6a553271457a7770614349725670466b75616955566c68775758336e44307639772f694e72366b7872626536572f6d7a4d373537653838352f792b762b663550732f7a46636f3259484558683032427a6d3333635046514f446246764b4e6e44634e6763484151575a5a784f703159316c3339314b38384a50344e68326d61544a38326a58486a78354f546b304e6261797476726c2b506f696833375a302b6e772b41734c4377323634562f393666417143624541674f585959354e47646134412b4354774f66436f4d617144722f4d706356525a46392b2f647a764c3264737249794245464131625337426c6767454744356437394c31627031534a4b45615a722f754b66704a6f794b48454e63784830412f472f7653627139463468774f4d6c4b6d6b504b79437863396d4830584f336d324b65374f58482b4d4a|||sizes|strings|d3d3d20727|wrong|Background|spacing|MAP|Alignment|greater|ratio|graphs|Color|equal|relative|Colors|setPieUnitsOffset|setPieValuesColor|setPieUnitsFontFamily|setPieUnitsFontSize|setPieValuesFontFamily|ideographic|setPieValuesDecimals|bottom|from|setPiePosition|addRule|roundrect|oval|get_boundingBox|setPieUnitsColor|behavior|setPieRadius|setTextPaddingBottom|setTextPaddingLeft|ltr|setSpeed|setTitle|setTitleColor|setTextPaddingTop|get_baseLine|rtl|setPieValuesOffset|setPieValuesPrefix|setPieValuesFontSize|middle|setShowYValues||setSize|hanging||setPieValuesSuffix|setPieOpacity|20000px|setLegendFontSize|white|setLegendFontFamily|setLegendForBar||matrix|textpathok||origin|setLegendDetect|offsetWidth||||setLabelY|setLabelPaddingLeft|arcTo|setLegendColor|pre|createTextNode|setLegend|setLineWidth|imagedata|setLineOpacity|textbox|curve|setPieDepth|setPieAngle|polyline|setLineColor|setLegendPadding|formulas|skew|setLegendForLine|setLegendShow|shadow|setLegendPosition|handles|get_widthText|drawImage|gradientradial|w2|owningElement|cssText|inline|createRadialGradient|createLinearGradient|M11|styleSheets|Dy|amp|quot|M21||M12|Dx|M22|propertyName|onresize|set_textRenderContext|check_textRenderContext|150px||MSIE|onpropertychange||onreadystatechange|fontVariant||block|300px|125|removeNode|981|fontWeight|miter|flat|progid|setTooltipFontFamily|focusposition|setTooltipFontColor|tile|setTooltipOpacity|sort|setTooltipFontSize|method|setTooltipBorder|setTitlePosition|draw_boundingBox|setTitleFontFamily|setTitleFontSize|setTooltipBackground|transform|setTooltip|setTransform|cropright|croptop|cropbottom|0xf|Matrix|DXImageTransform|cropleft|sizingmethod|BeforeEnd|setTooltipOffset|atan2|setTooltipPadding|360|lineOpen|setValuesFormat|setTooltipPosition|newSeq|NOT_FOUND_ERR|setGrid|NO_MODIFICATION_ALLOWED_ERR|NOT_SUPPORTED_ERR|setGridColorY|setGridColorX|setGridColor|setGraphLabelOpacity|x9f|INVALID_CHARACTER_ERR|NO_DATA_ALLOWED_ERR|setGraphLabelShadowColor|setFlagOffset|setGraphLabelPosition|INUSE_ATTRIBUTE_ERR|setIntervalX|INVALID_ACCESS_ERR|NAMESPACE_ERR|VALIDATION_ERR|setLabelAlignX|CanvasGradient|setIntervalY|height_|setGridOpacityX|setGridOpacity|setGridOpacityY|width_|setFlagFillColor|INVALID_MODIFICATION_ERR||x7f|setGraphExtendX|getUTCMinutes|setGraphExtend|getUTCSeconds|Boolean|setFlagRadius|setGraphExtendY|setFontFamily|getUTCDate|getUTCHours|getUTCMonth|DOM|setFlagWidth|getUTCFullYear|u0000|setGraphLabelColor|x1f|B5B5B5|setGraphLabelFontFamily|setGraphLabelFontSize|setFlagOpacity|WRONG_DOCUMENT_ERR|DOMSTRING_SIZE_ERR|INDEX_SIZE_ERR|Exception|setFlagShape|HIERARCHY_REQUEST_ERR|x00|setGraphLabel|CanvasPattern|bfnrt|setLabelFontFamilyX|addColorStop|setLabelAlignLastX|setLabelFontSizeY|setLabelColorY|nodeType|setLabelAlignLastY|setLabelFontSize|setLabelColor|setLabelColorX|setLabelFontFamilyY|IMG|shapetype|setLabelFontSizeX|setLabelPaddingBottom|setErrors|complete|setLabelFontFamily|setLabelAlignY|setFlagColor|check_strokeTextCapability|lastChild|setLabelAlignFirstY|DOMException|SyntaxError|readyState|setLabelAlignFirstX|createPattern'.split('|'),0,{}));

/**
 * jquery.Jcrop.js v0.9.9
 * jQuery Image Cropping Plugin
 * @author Kelly Hallman <khallman@gmail.com>
 * Copyright (c) 2008-2011 Kelly Hallman - released under MIT License {{{
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * }}}
 */

(function (jQuery) {

  jQuery.Jcrop = function (obj, opt) {
    var options = jQuery.extend({}, jQuery.Jcrop.defaults),
        docOffset, lastcurs, ie6mode = false;

    // Internal Methods {{{
    function px(n) {
      return parseInt(n, 10) + 'px';
    }
    function pct(n) {
      return parseInt(n, 10) + '%';
    }
    function cssClass(cl) {
      return options.baseClass + '-' + cl;
    }
    function supportsColorFade() {
      return jQuery.fx.step.hasOwnProperty('backgroundColor');
    }
    function getPos(obj) //{{{
    {
      // Updated in v0.9.4 to use built-in dimensions plugin
      var pos = jQuery(obj).offset();
      return [pos.left, pos.top];
    }
    //}}}
    function mouseAbs(e) //{{{
    {
      return [(e.pageX - docOffset[0]), (e.pageY - docOffset[1])];
    }
    //}}}
    function setOptions(opt) //{{{
    {
      if (typeof(opt) !== 'object') {
        opt = {};
      }
      options = jQuery.extend(options, opt);

      if (typeof(options.onChange) !== 'function') {
        options.onChange = function () {};
      }
      if (typeof(options.onSelect) !== 'function') {
        options.onSelect = function () {};
      }
      if (typeof(options.onRelease) !== 'function') {
        options.onRelease = function () {};
      }
    }
    //}}}
    function myCursor(type) //{{{
    {
      if (type !== lastcurs) {
        Tracker.setCursor(type);
        lastcurs = type;
      }
    }
    //}}}
    function startDragMode(mode, pos) //{{{
    {
      docOffset = getPos(jQueryimg);
      Tracker.setCursor(mode === 'move' ? mode : mode + '-resize');

      if (mode === 'move') {
        return Tracker.activateHandlers(createMover(pos), doneSelect);
      }

      var fc = Coords.getFixed();
      var opp = oppLockCorner(mode);
      var opc = Coords.getCorner(oppLockCorner(opp));

      Coords.setPressed(Coords.getCorner(opp));
      Coords.setCurrent(opc);

      Tracker.activateHandlers(dragmodeHandler(mode, fc), doneSelect);
    }
    //}}}
    function dragmodeHandler(mode, f) //{{{
    {
      return function (pos) {
        if (!options.aspectRatio) {
          switch (mode) {
          case 'e':
            pos[1] = f.y2;
            break;
          case 'w':
            pos[1] = f.y2;
            break;
          case 'n':
            pos[0] = f.x2;
            break;
          case 's':
            pos[0] = f.x2;
            break;
          }
        } else {
          switch (mode) {
          case 'e':
            pos[1] = f.y + 1;
            break;
          case 'w':
            pos[1] = f.y + 1;
            break;
          case 'n':
            pos[0] = f.x + 1;
            break;
          case 's':
            pos[0] = f.x + 1;
            break;
          }
        }
        Coords.setCurrent(pos);
        Selection.update();
      };
    }
    //}}}
    function createMover(pos) //{{{
    {
      var lloc = pos;
      KeyManager.watchKeys();

      return function (pos) {
        Coords.moveOffset([pos[0] - lloc[0], pos[1] - lloc[1]]);
        lloc = pos;

        Selection.update();
      };
    }
    //}}}
    function oppLockCorner(ord) //{{{
    {
      switch (ord) {
      case 'n':
        return 'sw';
      case 's':
        return 'nw';
      case 'e':
        return 'nw';
      case 'w':
        return 'ne';
      case 'ne':
        return 'sw';
      case 'nw':
        return 'se';
      case 'se':
        return 'nw';
      case 'sw':
        return 'ne';
      }
    }
    //}}}
    function createDragger(ord) //{{{
    {
      return function (e) {
        if (options.disabled) {
          return false;
        }
        if ((ord === 'move') && !options.allowMove) {
          return false;
        }
        btndown = true;
        startDragMode(ord, mouseAbs(e));
        e.stopPropagation();
        e.preventDefault();
        return false;
      };
    }
    //}}}
    function presize(jQueryobj, w, h) //{{{
    {
      var nw = jQueryobj.width(),
          nh = jQueryobj.height();
      if ((nw > w) && w > 0) {
        nw = w;
        nh = (w / jQueryobj.width()) * jQueryobj.height();
      }
      if ((nh > h) && h > 0) {
        nh = h;
        nw = (h / jQueryobj.height()) * jQueryobj.width();
      }
      xscale = jQueryobj.width() / nw;
      yscale = jQueryobj.height() / nh;
      jQueryobj.width(nw).height(nh);
    }
    //}}}
    function unscale(c) //{{{
    {
      return {
        x: parseInt(c.x * xscale, 10),
        y: parseInt(c.y * yscale, 10),
        x2: parseInt(c.x2 * xscale, 10),
        y2: parseInt(c.y2 * yscale, 10),
        w: parseInt(c.w * xscale, 10),
        h: parseInt(c.h * yscale, 10)
      };
    }
    //}}}
    function doneSelect(pos) //{{{
    {
      var c = Coords.getFixed();
      if ((c.w > options.minSelect[0]) && (c.h > options.minSelect[1])) {
        Selection.enableHandles();
        Selection.done();
      } else {
        Selection.release();
      }
      Tracker.setCursor(options.allowSelect ? 'crosshair' : 'default');
    }
    //}}}
    function newSelection(e) //{{{
    {
      if (options.disabled) {
        return false;
      }
      if (!options.allowSelect) {
        return false;
      }
      btndown = true;
      docOffset = getPos(jQueryimg);
      Selection.disableHandles();
      myCursor('crosshair');
      var pos = mouseAbs(e);
      Coords.setPressed(pos);
      Selection.update();
      Tracker.activateHandlers(selectDrag, doneSelect);
      KeyManager.watchKeys();

      e.stopPropagation();
      e.preventDefault();
      return false;
    }
    //}}}
    function selectDrag(pos) //{{{
    {
      Coords.setCurrent(pos);
      Selection.update();
    }
    //}}}
    function newTracker() //{{{
    {
      var trk = jQuery('<div></div>').addClass(cssClass('tracker'));
      if (jQuery.browser.msie) {
        trk.css({
          opacity: 0,
          backgroundColor: 'white'
        });
      }
      return trk;
    }
    //}}}

    // }}}
    // Initialization {{{
    // Sanitize some options {{{
    if (jQuery.browser.msie && (jQuery.browser.version.split('.')[0] === '6')) {
      ie6mode = true;
    }
    if (typeof(obj) !== 'object') {
      obj = jQuery(obj)[0];
    }
    if (typeof(opt) !== 'object') {
      opt = {};
    }
    // }}}
    setOptions(opt);
    // Initialize some jQuery objects {{{
    // The values are SET on the image(s) for the interface
    // If the original image has any of these set, they will be reset
    // However, if you destroy() the Jcrop instance the original image's
    // character in the DOM will be as you left it.
    var img_css = {
      border: 'none',
      margin: 0,
      padding: 0,
      position: 'absolute'
    };

    var jQueryorigimg = jQuery(obj);
    var jQueryimg = jQueryorigimg.clone().removeAttr('id').css(img_css);

    jQueryimg.width(jQueryorigimg.width());
    jQueryimg.height(jQueryorigimg.height());
    jQueryorigimg.after(jQueryimg).hide();

    presize(jQueryimg, options.boxWidth, options.boxHeight);

    var boundx = jQueryimg.width(),
        boundy = jQueryimg.height(),
        
        
        jQuerydiv = jQuery('<div />').width(boundx).height(boundy).addClass(cssClass('holder')).css({
        position: 'relative',
        backgroundColor: options.bgColor
      }).insertAfter(jQueryorigimg).append(jQueryimg);

    delete(options.bgColor);
    if (options.addClass) {
      jQuerydiv.addClass(options.addClass);
    }

    var jQueryimg2 = jQuery('<img />')
        .attr('src', jQueryimg.attr('src')).css(img_css).width(boundx).height(boundy),

        jQueryimg_holder = jQuery('<div />') 
        .width(pct(100)).height(pct(100)).css({
          zIndex: 310,
          position: 'absolute',
          overflow: 'hidden'
        }).append(jQueryimg2),

        jQueryhdl_holder = jQuery('<div />') 
        .width(pct(100)).height(pct(100)).css('zIndex', 320), 

        jQuerysel = jQuery('<div />') 
        .css({
          position: 'absolute',
          zIndex: 300
        }).insertBefore(jQueryimg).append(jQueryimg_holder, jQueryhdl_holder); 

    if (ie6mode) {
      jQuerysel.css({
        overflowY: 'hidden'
      });
    }

    var bound = options.boundary;
    var jQuerytrk = newTracker().width(boundx + (bound * 2)).height(boundy + (bound * 2)).css({
      position: 'absolute',
      top: px(-bound),
      left: px(-bound),
      zIndex: 290
    }).mousedown(newSelection);

    /* }}} */
    // Set more variables {{{
    var bgopacity = options.bgOpacity,
        xlimit, ylimit, xmin, ymin, xscale, yscale, enabled = true,
        btndown, animating, shift_down;

    docOffset = getPos(jQueryimg);
    // }}}
    // }}}
    // Internal Modules {{{
    // Touch Module {{{ 
    var Touch = (function () {
      // Touch support detection function adapted (under MIT License)
      // from code by Jeffrey Sambells - http://github.com/iamamused/
      function hasTouchSupport() {
        var support = {},
            events = ['touchstart', 'touchmove', 'touchend'],
            el = document.createElement('div'), i;

        try {
          for(i=0; i<events.length; i++) {
            var eventName = events[i];
            eventName = 'on' + eventName;
            var isSupported = (eventName in el);
            if (!isSupported) {
              el.setAttribute(eventName, 'return;');
              isSupported = typeof el[eventName] == 'function';
            }
            support[events[i]] = isSupported;
          }
          return support.touchstart && support.touchend && support.touchmove;
        }
        catch(err) {
          return false;
        }
      }

      function detectSupport() {
        if ((options.touchSupport === true) || (options.touchSupport === false)) return options.touchSupport;
          else return hasTouchSupport();
      }
      return {
        createDragger: function (ord) {
          return function (e) {
            e.pageX = e.originalEvent.changedTouches[0].pageX;
            e.pageY = e.originalEvent.changedTouches[0].pageY;
            if (options.disabled) {
              return false;
            }
            if ((ord === 'move') && !options.allowMove) {
              return false;
            }
            btndown = true;
            startDragMode(ord, mouseAbs(e));
            e.stopPropagation();
            e.preventDefault();
            return false;
          };
        },
        newSelection: function (e) {
          e.pageX = e.originalEvent.changedTouches[0].pageX;
          e.pageY = e.originalEvent.changedTouches[0].pageY;
          return newSelection(e);
        },
        isSupported: hasTouchSupport,
        support: detectSupport()
      };
    }());
    // }}}
    // Coords Module {{{
    var Coords = (function () {
      var x1 = 0,
          y1 = 0,
          x2 = 0,
          y2 = 0,
          ox, oy;

      function setPressed(pos) //{{{
      {
        pos = rebound(pos);
        x2 = x1 = pos[0];
        y2 = y1 = pos[1];
      }
      //}}}
      function setCurrent(pos) //{{{
      {
        pos = rebound(pos);
        ox = pos[0] - x2;
        oy = pos[1] - y2;
        x2 = pos[0];
        y2 = pos[1];
      }
      //}}}
      function getOffset() //{{{
      {
        return [ox, oy];
      }
      //}}}
      function moveOffset(offset) //{{{
      {
        var ox = offset[0],
            oy = offset[1];

        if (0 > x1 + ox) {
          ox -= ox + x1;
        }
        if (0 > y1 + oy) {
          oy -= oy + y1;
        }

        if (boundy < y2 + oy) {
          oy += boundy - (y2 + oy);
        }
        if (boundx < x2 + ox) {
          ox += boundx - (x2 + ox);
        }

        x1 += ox;
        x2 += ox;
        y1 += oy;
        y2 += oy;
      }
      //}}}
      function getCorner(ord) //{{{
      {
        var c = getFixed();
        switch (ord) {
        case 'ne':
          return [c.x2, c.y];
        case 'nw':
          return [c.x, c.y];
        case 'se':
          return [c.x2, c.y2];
        case 'sw':
          return [c.x, c.y2];
        }
      }
      //}}}
      function getFixed() //{{{
      {
        if (!options.aspectRatio) {
          return getRect();
        }
        // This function could use some optimization I think...
        var aspect = options.aspectRatio,
            min_x = options.minSize[0] / xscale,
            
            
            //min_y = options.minSize[1]/yscale,
            max_x = options.maxSize[0] / xscale,
            max_y = options.maxSize[1] / yscale,
            rw = x2 - x1,
            rh = y2 - y1,
            rwa = Math.abs(rw),
            rha = Math.abs(rh),
            real_ratio = rwa / rha,
            xx, yy;

        if (max_x === 0) {
          max_x = boundx * 10;
        }
        if (max_y === 0) {
          max_y = boundy * 10;
        }
        if (real_ratio < aspect) {
          yy = y2;
          w = rha * aspect;
          xx = rw < 0 ? x1 - w : w + x1;

          if (xx < 0) {
            xx = 0;
            h = Math.abs((xx - x1) / aspect);
            yy = rh < 0 ? y1 - h : h + y1;
          } else if (xx > boundx) {
            xx = boundx;
            h = Math.abs((xx - x1) / aspect);
            yy = rh < 0 ? y1 - h : h + y1;
          }
        } else {
          xx = x2;
          h = rwa / aspect;
          yy = rh < 0 ? y1 - h : y1 + h;
          if (yy < 0) {
            yy = 0;
            w = Math.abs((yy - y1) * aspect);
            xx = rw < 0 ? x1 - w : w + x1;
          } else if (yy > boundy) {
            yy = boundy;
            w = Math.abs(yy - y1) * aspect;
            xx = rw < 0 ? x1 - w : w + x1;
          }
        }

        // Magic %-)
        if (xx > x1) { // right side
          if (xx - x1 < min_x) {
            xx = x1 + min_x;
          } else if (xx - x1 > max_x) {
            xx = x1 + max_x;
          }
          if (yy > y1) {
            yy = y1 + (xx - x1) / aspect;
          } else {
            yy = y1 - (xx - x1) / aspect;
          }
        } else if (xx < x1) { // left side
          if (x1 - xx < min_x) {
            xx = x1 - min_x;
          } else if (x1 - xx > max_x) {
            xx = x1 - max_x;
          }
          if (yy > y1) {
            yy = y1 + (x1 - xx) / aspect;
          } else {
            yy = y1 - (x1 - xx) / aspect;
          }
        }

        if (xx < 0) {
          x1 -= xx;
          xx = 0;
        } else if (xx > boundx) {
          x1 -= xx - boundx;
          xx = boundx;
        }

        if (yy < 0) {
          y1 -= yy;
          yy = 0;
        } else if (yy > boundy) {
          y1 -= yy - boundy;
          yy = boundy;
        }

        return makeObj(flipCoords(x1, y1, xx, yy));
      }
      //}}}
      function rebound(p) //{{{
      {
        if (p[0] < 0) {
          p[0] = 0;
        }
        if (p[1] < 0) {
          p[1] = 0;
        }

        if (p[0] > boundx) {
          p[0] = boundx;
        }
        if (p[1] > boundy) {
          p[1] = boundy;
        }

        return [p[0], p[1]];
      }
      //}}}
      function flipCoords(x1, y1, x2, y2) //{{{
      {
        var xa = x1,
            xb = x2,
            ya = y1,
            yb = y2;
        if (x2 < x1) {
          xa = x2;
          xb = x1;
        }
        if (y2 < y1) {
          ya = y2;
          yb = y1;
        }
        return [Math.round(xa), Math.round(ya), Math.round(xb), Math.round(yb)];
      }
      //}}}
      function getRect() //{{{
      {
        var xsize = x2 - x1,
            ysize = y2 - y1,
            delta;

        if (xlimit && (Math.abs(xsize) > xlimit)) {
          x2 = (xsize > 0) ? (x1 + xlimit) : (x1 - xlimit);
        }
        if (ylimit && (Math.abs(ysize) > ylimit)) {
          y2 = (ysize > 0) ? (y1 + ylimit) : (y1 - ylimit);
        }

        if (ymin / yscale && (Math.abs(ysize) < ymin / yscale)) {
          y2 = (ysize > 0) ? (y1 + ymin / yscale) : (y1 - ymin / yscale);
        }
        if (xmin / xscale && (Math.abs(xsize) < xmin / xscale)) {
          x2 = (xsize > 0) ? (x1 + xmin / xscale) : (x1 - xmin / xscale);
        }

        if (x1 < 0) {
          x2 -= x1;
          x1 -= x1;
        }
        if (y1 < 0) {
          y2 -= y1;
          y1 -= y1;
        }
        if (x2 < 0) {
          x1 -= x2;
          x2 -= x2;
        }
        if (y2 < 0) {
          y1 -= y2;
          y2 -= y2;
        }
        if (x2 > boundx) {
          delta = x2 - boundx;
          x1 -= delta;
          x2 -= delta;
        }
        if (y2 > boundy) {
          delta = y2 - boundy;
          y1 -= delta;
          y2 -= delta;
        }
        if (x1 > boundx) {
          delta = x1 - boundy;
          y2 -= delta;
          y1 -= delta;
        }
        if (y1 > boundy) {
          delta = y1 - boundy;
          y2 -= delta;
          y1 -= delta;
        }

        return makeObj(flipCoords(x1, y1, x2, y2));
      }
      //}}}
      function makeObj(a) //{{{
      {
        return {
          x: a[0],
          y: a[1],
          x2: a[2],
          y2: a[3],
          w: a[2] - a[0],
          h: a[3] - a[1]
        };
      }
      //}}}

      return {
        flipCoords: flipCoords,
        setPressed: setPressed,
        setCurrent: setCurrent,
        getOffset: getOffset,
        moveOffset: moveOffset,
        getCorner: getCorner,
        getFixed: getFixed
      };
    }());

    //}}}
    // Selection Module {{{
    var Selection = (function () {
      var awake, hdep = 370;
      var borders = {};
      var handle = {};
      var seehandles = false;
      var hhs = options.handleOffset;

      // Private Methods
      function insertBorder(type) //{{{
      {
        var jq = jQuery('<div />').css({
          position: 'absolute',
          opacity: options.borderOpacity
        }).addClass(cssClass(type));
        jQueryimg_holder.append(jq);
        return jq;
      }
      //}}}
      function dragDiv(ord, zi) //{{{
      {
        var jq = jQuery('<div />').mousedown(createDragger(ord)).css({
          cursor: ord + '-resize',
          position: 'absolute',
          zIndex: zi
        });

        if (Touch.support) {
          jq.bind('touchstart', Touch.createDragger(ord));
        }

        jQueryhdl_holder.append(jq);
        return jq;
      }
      //}}}
      function insertHandle(ord) //{{{
      {
        return dragDiv(ord, hdep++).css({
          top: px(-hhs + 1),
          left: px(-hhs + 1),
          opacity: options.handleOpacity
        }).addClass(cssClass('handle'));
      }
      //}}}
      function insertDragbar(ord) //{{{
      {
        var s = options.handleSize,
            h = s,
            w = s,
            t = hhs,
            l = hhs;

        switch (ord) {
        case 'n':
        case 's':
          w = pct(100);
          break;
        case 'e':
        case 'w':
          h = pct(100);
          break;
        }

        return dragDiv(ord, hdep++).width(w).height(h).css({
          top: px(-t + 1),
          left: px(-l + 1)
        });
      }
      //}}}
      function createHandles(li) //{{{
      {
        var i;
        for (i = 0; i < li.length; i++) {
          handle[li[i]] = insertHandle(li[i]);
        }
      }
      //}}}
      function moveHandles(c) //{{{
      {
        var midvert = Math.round((c.h / 2) - hhs),
            midhoriz = Math.round((c.w / 2) - hhs),
            north = -hhs + 1,
            west = -hhs + 1,
            east = c.w - hhs,
            south = c.h - hhs,
            x, y;

        if (handle.e) {
          handle.e.css({
            top: px(midvert),
            left: px(east)
          });
          handle.w.css({
            top: px(midvert)
          });
          handle.s.css({
            top: px(south),
            left: px(midhoriz)
          });
          handle.n.css({
            left: px(midhoriz)
          });
        }
        if (handle.ne) {
          handle.ne.css({
            left: px(east)
          });
          handle.se.css({
            top: px(south),
            left: px(east)
          });
          handle.sw.css({
            top: px(south)
          });
        }
        if (handle.b) {
          handle.b.css({
            top: px(south)
          });
          handle.r.css({
            left: px(east)
          });
        }
      }
      //}}}
      function moveto(x, y) //{{{
      {
        jQueryimg2.css({
          top: px(-y),
          left: px(-x)
        });
        jQuerysel.css({
          top: px(y),
          left: px(x)
        });
      }
      //}}}
      function resize(w, h) //{{{
      {
        jQuerysel.width(w).height(h);
      }
      //}}}
      function refresh() //{{{
      {
        var c = Coords.getFixed();

        Coords.setPressed([c.x, c.y]);
        Coords.setCurrent([c.x2, c.y2]);

        updateVisible();
      }
      //}}}

      // Internal Methods
      function updateVisible() //{{{
      {
        if (awake) {
          return update();
        }
      }
      //}}}
      function update() //{{{
      {
        var c = Coords.getFixed();

        resize(c.w, c.h);
        moveto(c.x, c.y);

/*
			options.drawBorders &&
				borders.right.css({ left: px(c.w-1) }) &&
					borders.bottom.css({ top: px(c.h-1) });
      */

        if (seehandles) {
          moveHandles(c);
        }
        if (!awake) {
          show();
        }

        options.onChange.call(api, unscale(c));
      }
      //}}}
      function show() //{{{
      {
        jQuerysel.show();

        if (options.bgFade) {
          jQueryimg.fadeTo(options.fadeTime, bgopacity);
        } else {
          jQueryimg.css('opacity', bgopacity);
        }

        awake = true;
      }
      //}}}
      function release() //{{{
      {
        disableHandles();
        jQuerysel.hide();

        if (options.bgFade) {
          jQueryimg.fadeTo(options.fadeTime, 1);
        } else {
          jQueryimg.css('opacity', 1);
        }

        awake = false;
        options.onRelease.call(api);
      }
      //}}}
      function showHandles() //{{{
      {
        if (seehandles) {
          moveHandles(Coords.getFixed());
          jQueryhdl_holder.show();
        }
      }
      //}}}
      function enableHandles() //{{{
      {
        seehandles = true;
        if (options.allowResize) {
          moveHandles(Coords.getFixed());
          jQueryhdl_holder.show();
          return true;
        }
      }
      //}}}
      function disableHandles() //{{{
      {
        seehandles = false;
        jQueryhdl_holder.hide();
      } 
      //}}}
      function animMode(v) //{{{
      {
        if (animating === v) {
          disableHandles();
        } else {
          enableHandles();
        }
      } 
      //}}}
      function done() //{{{
      {
        animMode(false);
        refresh();
      } 
      //}}}
      /* Insert draggable elements {{{*/

      // Insert border divs for outline
      if (options.drawBorders) {
        borders = {
          top: insertBorder('hline'),
          bottom: insertBorder('hline bottom'),
          left: insertBorder('vline'),
          right: insertBorder('vline right')
        };
      }

      // Insert handles on edges
      if (options.dragEdges) {
        handle.t = insertDragbar('n');
        handle.b = insertDragbar('s');
        handle.r = insertDragbar('e');
        handle.l = insertDragbar('w');
      }

      // Insert side and corner handles
      if (options.sideHandles) {
        createHandles(['n', 's', 'e', 'w']);
      }
      if (options.cornerHandles) {
        createHandles(['sw', 'nw', 'ne', 'se']);
      }

      
      //}}}

      var jQuerytrack = newTracker().mousedown(createDragger('move')).css({
        cursor: 'move',
        position: 'absolute',
        zIndex: 360
      });

      if (Touch.support) {
        jQuerytrack.bind('touchstart.jcrop', Touch.createDragger('move'));
      }

      jQueryimg_holder.append(jQuerytrack);
      disableHandles();

      return {
        updateVisible: updateVisible,
        update: update,
        release: release,
        refresh: refresh,
        isAwake: function () {
          return awake;
        },
        setCursor: function (cursor) {
          jQuerytrack.css('cursor', cursor);
        },
        enableHandles: enableHandles,
        enableOnly: function () {
          seehandles = true;
        },
        showHandles: showHandles,
        disableHandles: disableHandles,
        animMode: animMode,
        done: done
      };
    }());
    
    //}}}
    // Tracker Module {{{
    var Tracker = (function () {
      var onMove = function () {},
          onDone = function () {},
          trackDoc = options.trackDocument;

      function toFront() //{{{
      {
        jQuerytrk.css({
          zIndex: 450
        });
        if (trackDoc) {
          jQuery(document)
            .bind('mousemove',trackMove)
            .bind('mouseup',trackUp);
        }
      } 
      //}}}
      function toBack() //{{{
      {
        jQuerytrk.css({
          zIndex: 290
        });
        if (trackDoc) {
          jQuery(document)
            .unbind('mousemove', trackMove)
            .unbind('mouseup', trackUp);
        }
      } 
      //}}}
      function trackMove(e) //{{{
      {
        onMove(mouseAbs(e));
        return false;
      } 
      //}}}
      function trackUp(e) //{{{
      {
        e.preventDefault();
        e.stopPropagation();

        if (btndown) {
          btndown = false;

          onDone(mouseAbs(e));

          if (Selection.isAwake()) {
            options.onSelect.call(api, unscale(Coords.getFixed()));
          }

          toBack();
          onMove = function () {};
          onDone = function () {};
        }

        return false;
      }
      //}}}
      function activateHandlers(move, done) //{{{
      {
        btndown = true;
        onMove = move;
        onDone = done;
        toFront();
        return false;
      }
      //}}}
      function trackTouchMove(e) //{{{
      {
        e.pageX = e.originalEvent.changedTouches[0].pageX;
        e.pageY = e.originalEvent.changedTouches[0].pageY;
        return trackMove(e);
      }
      //}}}
      function trackTouchEnd(e) //{{{
      {
        e.pageX = e.originalEvent.changedTouches[0].pageX;
        e.pageY = e.originalEvent.changedTouches[0].pageY;
        return trackUp(e);
      }
      //}}}
      function setCursor(t) //{{{
      {
        jQuerytrk.css('cursor', t);
      }
      //}}}

      if (Touch.support) {
        jQuery(document)
          .bind('touchmove', trackTouchMove)
          .bind('touchend', trackTouchEnd);
      }

      if (!trackDoc) {
        jQuerytrk.mousemove(trackMove).mouseup(trackUp).mouseout(trackUp);
      }

      jQueryimg.before(jQuerytrk);
      return {
        activateHandlers: activateHandlers,
        setCursor: setCursor
      };
    }());
    //}}}
    // KeyManager Module {{{
    var KeyManager = (function () {
      var jQuerykeymgr = jQuery('<input type="radio" />').css({
        position: 'fixed',
        left: '-120px',
        width: '12px'
      }),
          jQuerykeywrap = jQuery('<div />').css({
          position: 'absolute',
          overflow: 'hidden'
        }).append(jQuerykeymgr);

      function watchKeys() //{{{
      {
        if (options.keySupport) {
          jQuerykeymgr.show();
          jQuerykeymgr.focus();
        }
      }
      //}}}
      function onBlur(e) //{{{
      {
        jQuerykeymgr.hide();
      }
      //}}}
      function doNudge(e, x, y) //{{{
      {
        if (options.allowMove) {
          Coords.moveOffset([x, y]);
          Selection.updateVisible();
        }
        e.preventDefault();
        e.stopPropagation();
      }
      //}}}
      function parseKey(e) //{{{
      {
        if (e.ctrlKey) {
          return true;
        }
        shift_down = e.shiftKey ? true : false;
        var nudge = shift_down ? 10 : 1;

        switch (e.keyCode) {
        case 37:
          doNudge(e, -nudge, 0);
          break;
        case 39:
          doNudge(e, nudge, 0);
          break;
        case 38:
          doNudge(e, 0, -nudge);
          break;
        case 40:
          doNudge(e, 0, nudge);
          break;
        case 27:
          Selection.release();
          break;
        case 9:
          return true;
        }

        return false;
      }
      //}}}

      if (options.keySupport) {
        jQuerykeymgr.keydown(parseKey).blur(onBlur);
        if (ie6mode || !options.fixedSupport) {
          jQuerykeymgr.css({
            position: 'absolute',
            left: '-20px'
          });
          jQuerykeywrap.append(jQuerykeymgr).insertBefore(jQueryimg);
        } else {
          jQuerykeymgr.insertBefore(jQueryimg);
        }
      }


      return {
        watchKeys: watchKeys
      };
    }());
    //}}}
    // }}}
    // API methods {{{
    function setClass(cname) //{{{
    {
      jQuerydiv.removeClass().addClass(cssClass('holder')).addClass(cname);
    }
    //}}}
    function animateTo(a, callback) //{{{
    {
      var x1 = parseInt(a[0], 10) / xscale,
          y1 = parseInt(a[1], 10) / yscale,
          x2 = parseInt(a[2], 10) / xscale,
          y2 = parseInt(a[3], 10) / yscale;

      if (animating) {
        return;
      }

      var animto = Coords.flipCoords(x1, y1, x2, y2),
          c = Coords.getFixed(),
          initcr = [c.x, c.y, c.x2, c.y2],
          animat = initcr,
          interv = options.animationDelay,
          ix1 = animto[0] - initcr[0],
          iy1 = animto[1] - initcr[1],
          ix2 = animto[2] - initcr[2],
          iy2 = animto[3] - initcr[3],
          pcent = 0,
          velocity = options.swingSpeed;

      x = animat[0];
      y = animat[1];
      x2 = animat[2];
      y2 = animat[3];

      Selection.animMode(true);
      var anim_timer;

      function queueAnimator() {
        window.setTimeout(animator, interv);
      }
      var animator = (function () {
        return function () {
          pcent += (100 - pcent) / velocity;

          animat[0] = x + ((pcent / 100) * ix1);
          animat[1] = y + ((pcent / 100) * iy1);
          animat[2] = x2 + ((pcent / 100) * ix2);
          animat[3] = y2 + ((pcent / 100) * iy2);

          if (pcent >= 99.8) {
            pcent = 100;
          }
          if (pcent < 100) {
            setSelectRaw(animat);
            queueAnimator();
          } else {
            Selection.done();
            if (typeof(callback) === 'function') {
              callback.call(api);
            }
          }
        };
      }());
      queueAnimator();
    }
    //}}}
    function setSelect(rect) //{{{
    {
      setSelectRaw([
      parseInt(rect[0], 10) / xscale, parseInt(rect[1], 10) / yscale, parseInt(rect[2], 10) / xscale, parseInt(rect[3], 10) / yscale]);
    }
    //}}}
    function setSelectRaw(l) //{{{
    {
      Coords.setPressed([l[0], l[1]]);
      Coords.setCurrent([l[2], l[3]]);
      Selection.update();
    }
    //}}}
    function tellSelect() //{{{
    {
      return unscale(Coords.getFixed());
    }
    //}}}
    function tellScaled() //{{{
    {
      return Coords.getFixed();
    }
    //}}}
    function setOptionsNew(opt) //{{{
    {
      setOptions(opt);
      interfaceUpdate();
    }
    //}}}
    function disableCrop() //{{{
    {
      options.disabled = true;
      Selection.disableHandles();
      Selection.setCursor('default');
      Tracker.setCursor('default');
    }
    //}}}
    function enableCrop() //{{{
    {
      options.disabled = false;
      interfaceUpdate();
    }
    //}}}
    function cancelCrop() //{{{
    {
      Selection.done();
      Tracker.activateHandlers(null, null);
    }
    //}}}
    function destroy() //{{{
    {
      jQuerydiv.remove();
      jQueryorigimg.show();
      jQuery(obj).removeData('Jcrop');
    }
    //}}}
    function setImage(src, callback) //{{{
    {
      Selection.release();
      disableCrop();
      var img = new Image();
      img.onload = function () {
        var iw = img.width;
        var ih = img.height;
        var bw = options.boxWidth;
        var bh = options.boxHeight;
        jQueryimg.width(iw).height(ih);
        jQueryimg.attr('src', src);
        jQueryimg2.attr('src', src);
        presize(jQueryimg, bw, bh);
        boundx = jQueryimg.width();
        boundy = jQueryimg.height();
        jQueryimg2.width(boundx).height(boundy);
        jQuerytrk.width(boundx + (bound * 2)).height(boundy + (bound * 2));
        jQuerydiv.width(boundx).height(boundy);
        enableCrop();

        if (typeof(callback) === 'function') {
          callback.call(api);
        }
      };
      img.src = src;
    }
    //}}}
    function interfaceUpdate(alt) //{{{
    // This method tweaks the interface based on options object.
    // Called when options are changed and at end of initialization.
    {
      if (options.allowResize) {
        if (alt) {
          Selection.enableOnly();
        } else {
          Selection.enableHandles();
        }
      } else {
        Selection.disableHandles();
      }

      Tracker.setCursor(options.allowSelect ? 'crosshair' : 'default');
      Selection.setCursor(options.allowMove ? 'move' : 'default');


      if (options.hasOwnProperty('setSelect')) {
        setSelect(options.setSelect);
        Selection.done();
        delete(options.setSelect);
      }

      if (options.hasOwnProperty('trueSize')) {
        xscale = options.trueSize[0] / boundx;
        yscale = options.trueSize[1] / boundy;
      }
      if (options.hasOwnProperty('bgColor')) {

        if (supportsColorFade() && options.fadeTime) {
          jQuerydiv.animate({
            backgroundColor: options.bgColor
          }, {
            queue: false,
            duration: options.fadeTime
          });
        } else {
          jQuerydiv.css('backgroundColor', options.bgColor);
        }

        delete(options.bgColor);
      }
      if (options.hasOwnProperty('bgOpacity')) {
        bgopacity = options.bgOpacity;

        if (Selection.isAwake()) {
          if (options.fadeTime) {
            jQueryimg.fadeTo(options.fadeTime, bgopacity);
          } else {
            jQuerydiv.css('opacity', options.opacity);
          }
        }
        delete(options.bgOpacity);
      }

      xlimit = options.maxSize[0] || 0;
      ylimit = options.maxSize[1] || 0;
      xmin = options.minSize[0] || 0;
      ymin = options.minSize[1] || 0;

      if (options.hasOwnProperty('outerImage')) {
        jQueryimg.attr('src', options.outerImage);
        delete(options.outerImage);
      }

      Selection.refresh();
    }
    //}}}
    //}}}

    if (Touch.support) {
      jQuerytrk.bind('touchstart', Touch.newSelection);
    }

    jQueryhdl_holder.hide();
    interfaceUpdate(true);

    var api = {
      setImage: setImage,
      animateTo: animateTo,
      setSelect: setSelect,
      setOptions: setOptionsNew,
      tellSelect: tellSelect,
      tellScaled: tellScaled,
      setClass: setClass,

      disable: disableCrop,
      enable: enableCrop,
      cancel: cancelCrop,
      release: Selection.release,
      destroy: destroy,

      focus: KeyManager.watchKeys,

      getBounds: function () {
        return [boundx * xscale, boundy * yscale];
      },
      getWidgetSize: function () {
        return [boundx, boundy];
      },
      getScaleFactor: function () {
        return [xscale, yscale];
      },

      ui: {
        holder: jQuerydiv,
        selection: jQuerysel
      }
    };

    if (jQuery.browser.msie) {
      jQuerydiv.bind('selectstart', function () {
        return false;
      });
    }

    jQueryorigimg.data('Jcrop', api);
    return api;
  };
  jQuery.fn.Jcrop = function (options, callback) //{{{
  {

    function attachWhenDone(from) //{{{
    {
      var opt = (typeof(options) === 'object') ? options : {};
      var loadsrc = opt.useImg || from.src;
      var img = new Image();
      img.onload = function () {
        function attachJcrop() {
          var api = jQuery.Jcrop(from, opt);
          if (typeof(callback) === 'function') {
            callback.call(api);
          }
        }

        function attachAttempt() {
          if (!img.width || !img.height) {
            window.setTimeout(attachAttempt, 50);
          } else {
            attachJcrop();
          }
        }
        window.setTimeout(attachAttempt, 50);
      };
      img.src = loadsrc;
    }
    //}}}

    // Iterate over each object, attach Jcrop
    this.each(function () {
      // If we've already attached to this object
      if (jQuery(this).data('Jcrop')) {
        // The API can be requested this way (undocumented)
        if (options === 'api') {
          return jQuery(this).data('Jcrop');
        }
        // Otherwise, we just reset the options...
        else {
          jQuery(this).data('Jcrop').setOptions(options);
        }
      }
      // If we haven't been attached, preload and attach
      else {
        attachWhenDone(this);
      }
    });

    // Return "this" so the object is chainable (jQuery-style)
    return this;
  };
  //}}}
  // Global Defaults {{{
  jQuery.Jcrop.defaults = {

    // Basic Settings
    allowSelect: true,
    allowMove: true,
    allowResize: true,

    trackDocument: true,

    // Styling Options
    baseClass: 'jcrop',
    addClass: null,
    bgColor: 'black',
    bgOpacity: 0.6,
    bgFade: false,
    borderOpacity: 0.4,
    handleOpacity: 0.5,
    handleSize: 9,
    handleOffset: 5,

    aspectRatio: 0,
    keySupport: true,
    cornerHandles: true,
    sideHandles: true,
    drawBorders: true,
    dragEdges: true,
    fixedSupport: true,
    touchSupport: null,

    boxWidth: 0,
    boxHeight: 0,
    boundary: 2,
    fadeTime: 400,
    animationDelay: 20,
    swingSpeed: 3,

    minSelect: [0, 0],
    maxSize: [0, 0],
    minSize: [0, 0],

    // Callbacks / Event Handlers
    onChange: function () {},
    onSelect: function () {},
    onRelease: function () {}
  };

  // }}}
}(jQuery));

