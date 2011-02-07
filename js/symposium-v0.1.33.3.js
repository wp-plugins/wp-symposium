jQuery(document).ready(function() { 	

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          SHARED                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	// Centre in screen
	jQuery.fn.inmiddle = function () {
		this.css("position","absolute");
		this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
		this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
		return this;
	}

	// Are you sure?
	jQuery('.areyousure').click(function(){
		var answer = confirm('Are you sure?\n\nAll topics in the category will become un-categorised.');
		return answer // answer is a boolean
	});

    // Check if really want to delete	    
	jQuery(".delete").click(function(){
	  var answer = confirm("Are you sure?");
	  return answer // answer is a boolean
	});
	   		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                      PROFILE PHOTO                                       |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("#profile_jcrop_target").length) {	
		jQuery(".symposium_pleasewait").inmiddle().show().delay(3000).fadeOut("slow");
	}
	
	if (jQuery("#file_upload").length) {
		
		jQuery(".symposium_pleasewait").inmiddle().show().delay(3000).fadeOut("slow");
		
		jQuery('#file_upload').uploadify({
		    'uploader'  : '/wp-content/plugins/wp-symposium/uploadify/uploadify.swf',
			'buttonText': 'Browse for file',
		    'script'    : '/wp-content/plugins/wp-symposium/uploadify/uploadify.php',
		    'cancelImg' : '/wp-content/plugins/wp-symposium/uploadify/cancel.png',
		    'folder' 	: '/wp-content/plugins/wp-symposium/uploads',
		    'auto'      : true,
			'onComplete': function(event, queueID, fileObj, response, data) { 
				if (symposium.avatar_url.indexOf('?') > 0) {
					window.location.href=symposium.avatar_url+"&crop=y&img="+fileObj['name'];
				} else {
					window.location.href=symposium.avatar_url+"?crop=y&img="+fileObj['name'];
				}
			}
	   	});
		  
	}

	if (jQuery("#profile_jcrop_target").length) {
		jQuery('#profile_jcrop_target').Jcrop({
			onChange: showPreview,
			onSelect: showPreview,
			aspectRatio: 1
		});
	}

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
		


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                        WIDGET: VOTE                                      |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery(".symposium_answer").length) {
		jQuery(".symposium_answer").click(function(){
			
			var vote_answer = jQuery(this).attr("title");
			
			jQuery(".symposium_pleasewait").inmiddle().show();
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
					jQuery("#symposium_vote_forum").hide();
					jQuery("#symposium_vote_thankyou").slideDown("fast").effect("highlight", {}, 3000);
				},
				error: function(err){
					alert("V:"+err);
				}		
	   		});	
			jQuery(".symposium_pleasewait").fadeOut("slow");
		});
	}

	if (jQuery("#symposium_chartcontainer").length) {

		var myData = new Array(['Yes', parseFloat(symposium.widget_vote_yes)], ['No', parseFloat(symposium.widget_vote_no)]);
		var myChart = new JSChart('symposium_chartcontainer', 'bar');
		myChart.setDataArray(myData);
		myChart.setSize(200, 200);
		myChart.setTitleFontSize(14);
		myChart.setTitle("");
		myChart.setAxisNameX("");
		myChart.setAxisNameY("");
		myChart.setAxisPaddingTop(15);
		myChart.setAxisPaddingBottom(15);
		myChart.setAxisPaddingLeft(0);
		myChart.setBarValuesSuffix('%');
		myChart.draw();
		
	}


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          REGISTER                                        |
	   +------------------------------------------------------------------------------------------+
	*/

	// Password strength field	
	if (jQuery("input#pwd2").length) {
		jQuery(function() {
			jQuery('#pwd2').pstrength();
		});
	}

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           LOGIN                                          |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("input#symposium_login").length) {

		jQuery('#symposium_login').submit(function() {		

	   		var username = jQuery("#symposium_login_username").val();
	   		var pwd = jQuery("#symposium_login_pwd").val();
	   		var forgot = jQuery("#forgotten_email").val();
	   		var previous_page = jQuery('#previous-page').val();
	   		
			if (forgot == '') {
				if (username != '' && pwd != '') {
					jQuery(".symposium_pleasewait").inmiddle().show();
					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_login_functions.php", 
						type: "POST",
						data: ({
							action:"doLogin",
							username:username,
							pwd:pwd,
							redirect_to:previous_page
						}),
					    dataType: "html",
						async: false,
						success: function(str){
							if (str == "FAIL") {
								jQuery(".symposium_pleasewait").fadeOut("slow");
								alert("Login failed, please try again");
							} else {
								if (str != "Important: Please update!" && str != "none") {
									window.location.href=str;
								} else {
									alert("Trying to redirect, but target plugin URL (Options->Settings) not set up. ("+str+")");
								}
							}
						},
						error: function(err){
							alert("L:"+err);
						}		
			   		});	
				}
				
			} else {

			   		var sum1 = parseFloat(jQuery("#sum1").val());
			   		var sum2 = parseFloat(jQuery("#sum2").val());
			   		var actual = sum1+sum2;
			   		var result = parseFloat(jQuery("#result").val());
			   		
			   		if (actual == result) {
				
						jQuery(".symposium_pleasewait").inmiddle().show();
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_login_functions.php", 
							type: "POST",
							data: ({
								action:"doForgot",
								email:forgot
							}),
						    dataType: "html",
							async: false,
							success: function(str){
								jQuery(".symposium_pleasewait").fadeOut("slow");
								if (str.substring(0, 2) == 'OK') { 
									jQuery('#symposium_forgotten_password_msg').show("slow");
									jQuery("#symposium_login_username").val('');
									jQuery("#symposium_login_pwd").val('');
									jQuery("#forgotten_email").val('');
								} else {
									alert(str);
								}
							},
							error: function(err){
								alert("L2: "+err);
							}		
				   		});
			   		} else {
			   			alert('Answer to the sum is incorrect.');
			   		}					
			}
	   	});	
	   	
		jQuery('#symposium_forgotten').click(function() {		
			jQuery('#symposium_forgotten_password').toggle("slow");
	   	});	

	}


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                         DIRECTORY                                        |
	   +------------------------------------------------------------------------------------------+
	*/

	if (jQuery("input#member").length) {
		
		jQuery("input#member").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_members_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#member" ).val( ui.item.label );
					jQuery( "input#member_id" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#member" ).val( ui.item.label );
					jQuery( "input#member_id" ).val( ui.item.value );
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "<div style=\'float:right\'>" + item.city + item.country + "</div></a>" )
					.appendTo( ul );
			};

		jQuery(".symposium_pleasewait").inmiddle().show();

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_members_functions.php", 
			type: "POST",
			data: ({
				action:"getMembers",
				page:1,
				me:symposium.current_user_id
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				jQuery("#symposium_members").html(str);
				jQuery(".symposium_pleasewait").fadeOut("slow");
			},
			error: function(err){
				//alert("D1:"+err);
			}		
   		});	
	}	

	if (jQuery("input#member_small").length) {
		
		jQuery("input#member_small").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_members_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#member_small" ).val( ui.item.label );
					jQuery( "input#uid" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#member_small" ).val( ui.item.label );
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
			
		   	jQuery("#small_members_button").click(function() {
				window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'uid='+jQuery("#uid").val();
				return false;
		   	});

	}		
   		
	
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           MAIL                                           |
	   +------------------------------------------------------------------------------------------+
	*/
	
	if (jQuery("#mail_sent").length) {
		jQuery("#mail_sent").effect("highlight", {}, 4000).slideUp("slow");
	}
		
	// Change between boxes
   	jQuery(".mail_tab").click(function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
   	});		

	// React to click on message list
   	jQuery(".mail_item").click(function() {
   		
		jQuery(".symposium_pleasewait").inmiddle().show();
		var mail_mid = jQuery(this).attr("id");

		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_mail_functions.php", 
			type: "POST",
			data: ({
				action:"getMailMessage",
				tray:symposium.view,
				'mid':mail_mid
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				var details = str.split("[split]");
				if (symposium.view == "in") {
					if (details[1] > 0) {
						jQuery("#incount").html(' ('+details[1]+')');
					}
					if (details[1] == 0) {
						jQuery("#incount").html('');
					}
					if (details[2] == "in") {
						jQuery("#"+details[0]).removeClass("row");
						jQuery("#"+details[0]).addClass("row_odd");
					}
				}
				if (symposium.view == "in") {
					jQuery("#in_message").html(details[3]);
				} else {
					jQuery("#sent_message").html(details[3]);
				}
				jQuery(".symposium_pleasewait").fadeOut("slow");
			},
			error: function(err){
				//alert("1:"+err);
			}		
   		});	   		
   		
   	});	

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                       MAIL COMPOSE                                       |
	   +------------------------------------------------------------------------------------------+
	*/
	
	if (jQuery("input#compose_recipient").length) {
		jQuery("input#compose_recipient").autocomplete({
				source: symposium.plugin_url+"ajax/symposium_mail_functions.php",
				minLength: 1,
				focus: function( event, ui ) {
					jQuery( "input#compose_recipient" ).val( ui.item.value );
					return false;
				},
				select: function( event, ui ) {
					jQuery( "input#compose_recipient" ).val( ui.item.value );
					return false;
				}
			})
			.data( "autocomplete" )._renderItem = function( ul, item ) {
				return jQuery( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "<div style=\'float:right\'>" + item.city + item.country + "</div></a>" )
					.appendTo( ul );
			};
	}
		
	/*
	   +------------------------------------------------------------------------------------------+
	   |                                         PROFILE                                          |
	   +------------------------------------------------------------------------------------------+
	*/
	
	// Act on "view" parameter on first page load
	if (jQuery("#profile_body").length) {
		
		var menu_id = 'menu_'+symposium.view;
		if (menu_id == 'menu_in') { menu_id = 'menu_wall'; }
		if (jQuery('#force_profile_page').length) {
			menu_id = 'menu_'+jQuery('#force_profile_page').html();
		}
				
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:menu_id,
				post:symposium.post,
				uid1:symposium.current_user_page,
				uid2:symposium.current_user_id				
			}),
		    dataType: "html",
			success: function(str){
				jQuery('#profile_body').html(str);
			}
   		});	
   		
	}
	
	// Menu choices
	jQuery(".symposium_profile_menu").click(function(){
		
		var menu_id = jQuery(this).attr("id");
		jQuery('#profile_body').html("<img src='"+symposium.plugin_url+"/images/busy.gif' />");
		
		if (menu_id == 'menu_photo') {
			window.location.href=symposium.avatar_url;
			exit;
		}

		if (!(jQuery("#profile_body").length)) {
			var view = menu_id.replace(/menu_/g, "");
			window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'view='+view;
			exit;			
		}
				
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:menu_id,
				post:'',
				uid1:symposium.current_user_page,
				uid2:symposium.current_user_id				
			}),
		    dataType: "html",
			success: function(str){
				jQuery('#profile_body').hide().html(str).fadeIn("slow");
			}
   		});	

	});

	// Show delete link on wall post hover
	jQuery('.wall_post').live('mouseover mouseout', function(event) {
	  if (event.type == 'mouseover') {
			jQuery(this).find(".delete_post_top").show();
	  } else {
        	jQuery(this).find(".delete_post_top").hide();
	  }
	});
    
	// Show delete link on reply hover
	jQuery('.wall_reply').live('mouseover mouseout', function(event) {
	  if (event.type == 'mouseover') {
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
		
		if (confirm("Are you sure?")) {

			jQuery(".symposium_pleasewait").inmiddle().show();
	
			var comment_id = jQuery(this).attr("title");
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
				type: "POST",
				data: ({
					action:"deletePost",
					uid:symposium.current_user_id,
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

	// new status
	jQuery("#symposium_add_update").live('click', function() {

		var comment_text = jQuery("#symposium_status").val();
		
		var comment = "<div class='add_wall_post_div'>";
		comment = comment + "<div class='add_wall_post'>";
		comment = comment + "<div class='add_wall_post_text'>";
		comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
		comment = comment + symposium.current_user_display_name+'</a><br />';
		comment = comment + comment_text;
		comment = comment + "</div>";
		comment = comment + "</div>";			
		comment = comment + "<div class='add_wall_post_avatar'>";
		comment = comment + jQuery('#profile_photo').html().replace(/200/g, '64');		
		comment = comment + "</div>";	
		comment = comment + "</div>";

		jQuery("#symposium_status").val('');
		jQuery(comment).prependTo('#symposium_wall').hide().slideDown("slow");
					
		// Update status
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"addStatus",
				subject_uid:symposium.current_user_id,
				author_uid:symposium.current_user_id,
				text:comment_text
			}),
		    dataType: "html",
			async: true
   		});

		// If not on the profile page
		if (!(jQuery("#symposium_wall").length)) {
			jQuery(".symposium_pleasewait").inmiddle().show();
			window.location.href=symposium.profile_url+symposium.q.substring(0, 1)+'view=wall';
		}
		   		
   	});		
	
	// new post
	jQuery("#symposium_add_comment").live('click', function() {

		var comment_text = jQuery("#symposium_comment").val();
		
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
		comment = comment + jQuery('#symposium_current_user_avatar').html().replace(/200/g, '64');		
		comment = comment + "</div>";	

		jQuery("#symposium_comment").val('');
		jQuery(comment).prependTo('#symposium_wall').hide().slideDown();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"addStatus",
				subject_uid:symposium.current_user_page,
				author_uid:symposium.current_user_id,
				parent:0,
				text:comment_text
			}),
		    dataType: "html",
			async: true
   		});

   	});		

	// new reply
	jQuery(".symposium_add_reply").live('click', function() {
		
		var comment_id = jQuery(this).attr("title");
		var author_id = jQuery('#symposium_author_'+comment_id).val();
		var comment_text = jQuery("#symposium_reply_"+comment_id).val();
		
		var comment = "<div class='reply_div'>";
		comment = comment + "<div class='wall_reply_div'";
		if (symposium.bg_color_2 != '') { comment = comment + " style='background-color:"+symposium.bg_color_2+"'"; }
		comment = comment + ">";
		comment = comment + "<div class='wall_reply'>";
		comment = comment + '<a href="'+symposium.profile_url+symposium.q.substring(0, 1)+'uid='+symposium.current_user_id+'">';
		comment = comment + symposium.current_user_display_name+'</a><br />';
		comment = comment + comment_text;
		comment = comment + "</div>";
		comment = comment + "<br class='clear' /></div>";			
		comment = comment + "<div class='wall_reply_avatar'>";
		comment = comment + jQuery('#symposium_current_user_avatar').html().replace(/200/g, '40');		
		comment = comment + "</div>";	
		comment = comment + "</div>";

		jQuery(comment).appendTo('#symposium_comment_'+comment_id).hide().slideDown("slow");
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
			async: true
   		});
   			
   	});		
	
	// update settings
	jQuery("#updateSettingsButton").live('click', function() {
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
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"updateSettings",
				timezone:jQuery("#timezone").val(),
				sound:jQuery("#sound").val(),
				soundchat:jQuery("#soundchat").val(),
				bar_position:jQuery("#bar_position").val(),
				display_name:jQuery("#display_name").val(),
				user_email:jQuery("#user_email").val(),
				notify_new_messages:notify_new_messages,
				notify_new_wall:notify_new_wall,
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
				if (str != 'OK') {
					alert(str);
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				alert("P4:"+err);
			}		
   		});
   			
   	});		

	// update personal
	jQuery("#updatePersonalButton").live('click', function() {
		jQuery(".symposium_notice").inmiddle().show();

		var extended = '';

		jQuery('.eid_value').each(function(index) {
		    extended += jQuery(this).attr("title") + '[]';
		    extended += jQuery(this).val() + '[|]';
		});
		  		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_profile_functions.php", 
			type: "POST",
			data: ({
				action:"updatePersonal",
				dob_day:jQuery("#dob_day").val(),
				dob_month:jQuery("#dob_month").val(),
				dob_year:jQuery("#dob_year").val(),
				city:jQuery("#city").val(),
				country:jQuery("#country").val(),
				share:jQuery("#share").val(),
				wall_share:jQuery("#wall_share").val(),
				extended:extended
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str != 'OK') {
					alert(str);
				}
				jQuery(".symposium_notice").fadeOut("slow");
			},
			error: function(err){
				//alert("P5:"+err);
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
					jQuery("#addasfriend_done2").slideDown("fast").effect("highlight", {}, 3000);
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
					jQuery("#cancelfriendrequest_done").slideDown("fast").effect("highlight", {}, 3000);
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

	// reject a friend request
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
				//alert("P9:"+err);
			}		
   		});
   			
   	});			


	/*
	   +------------------------------------------------------------------------------------------+
	   |                                          FORUM                                           |
	   +------------------------------------------------------------------------------------------+
	*/

   	jQuery(".backto").click(function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
   	});		
	jQuery(".new-topic-subject-warning").hide();
	jQuery(".new_topic_text-warning").hide();
	jQuery(".reply_text-warning").hide();
	jQuery(".quick-reply-warning").hide();

	jQuery("#share_link").hover(function() {
		jQuery("#share_label").show("slide", {direction: 'right'}, 300);
	}, function () {
		jQuery("#share_label").hide("slide", {direction: 'right'}, 300);
	});
	
	// Fav Icon
   	jQuery("#fav_link").click(function() {
   		
		jQuery(".symposium_notice").inmiddle().fadeIn();
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"toggleFav",
				tid:symposium.show_tid
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str == "added") {
					jQuery('#fav_link').attr({ src: symposium.plugin_url+'images/star-on.gif' });
				} else {
					jQuery('#fav_link').attr({ src: symposium.plugin_url+'images/star-off.gif' });
				}
				jQuery(".symposium_notice").delay(100).fadeOut("slow");
			},
			error: function(err){
				//alert("12:"+err);
			}		
   		});

   	});

	// Show favourites list
   	jQuery("#show_favs").click(function() {
        
		jQuery("#fav-list-internal").html("Retrieving content...");
        jQuery("#symposium-fav-list").inmiddle().fadeIn();
		jQuery(".symposium_pleasewait").inmiddle().fadeIn();
		
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
				jQuery("#fav-list-internal").hide().html(str).fadeIn("slow");
				jQuery(".symposium_pleasewait").delay(100).fadeOut("slow");
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
	jQuery('.fav_row').live('mouseover mouseout', function(event) {
	  if (event.type == 'mouseover') {
        jQuery(this).find(".symposium-delete-fav").show();
	  } else {
        jQuery(this).find(".symposium-delete-fav").hide();
	  }
	});
	// Close favourites
   	jQuery("#favs_close").click(function() {
		jQuery('#symposium-fav-list').fadeOut("slow");
   	});
   	
	// Edit topic (AJAX)
   	jQuery("#starting-post").hover(function() {
        jQuery(this).find("#edit-this-topic").show();
   	}, function() {
        jQuery(this).find("#edit-this-topic").hide();
   	});
	// Edit the topic
   	jQuery("#edit-this-topic").click(function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		jQuery("#new-category-div").show();
    	var tid = jQuery(".edit-topic-tid").attr("id");	
		jQuery("#edit_topic_subject").val("Please wait...");
		jQuery("#edit_topic_text").html("Retrieving content...");
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
			type: "POST",
			data: ({
				action:"getEditDetails",
				tid:symposium.show_tid
			}),
		    dataType: "html",
			async: false,
			success: function(str){
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").removeAttr("disabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery("#new-category").val(details[4]);
			},
			error: function(err){
				//alert("2:"+err);
			}		
   		});
   					
		jQuery("#edit-topic-div").inmiddle().fadeIn();
		jQuery(".symposium_pleasewait").fadeOut("slow");
   	});	    	

   	// Edit a reply
   	jQuery(".edit-child-topic").click(function() {
		jQuery(".symposium_pleasewait").inmiddle().show();
		jQuery("#new-category-div").hide();
    	var tid = jQuery(this).attr("id");	
		jQuery("#edit_topic_subject").val("Please wait...");
		jQuery("#edit_topic_text").html("Retrieving content...");

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
				var details = str.split("[split]");
				jQuery("#edit_topic_subject").val(details[0]);
				jQuery("#edit_topic_subject").attr("disabled", "enabled");
				jQuery("#edit_topic_text").html(details[1]);
				jQuery(".edit-topic-parent").attr("id", details[2]);
				jQuery(".edit-topic-tid").attr("id", details[3]);
			},
			error: function(err){
				//alert("3:"+err);
			}		
   		});
   		
		jQuery(".symposium_pleasewait").fadeOut("slow");
		jQuery("#edit-topic-div").inmiddle().fadeIn();
   	});	 
   	
   	// Update contents of edit form
	jQuery(".edit_topic_submit").click(function(){
		jQuery(".symposium_notice").inmiddle().show();
   		var tid = jQuery(".edit-topic-tid").attr("id");	
   		var parent = jQuery(".edit-topic-parent").attr("id");
		var topic_subject = jQuery("#edit_topic_subject").val();	
		var topic_post = jQuery("#edit_topic_text").val();	
		var topic_category = jQuery("#new-category").val();	
			
		if (parent == 0) {
			jQuery(".topic-post-header").html(topic_subject);
			jQuery(".topic-post-post").html(topic_post.replace(/\n/g, "<br />"));
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
			async: false,
			success: function(str){
				jQuery("#edit-topic-div").fadeOut("fast");
				window.location.href=window.location.href;
			},
			error: function(err){
				//alert("4:"+err);
			}		
   		});
   		
	});
	// Cancel form
	jQuery(".edit_topic_cancel").click(function(){
		jQuery("#edit-topic-div").fadeOut("fast");
		jQuery(".symposium_notice").fadeOut("fast");
   	});

	// Show delete link on row hover
    jQuery(".row").hover(function() {
        jQuery(this).find(".delete_post").show()
    }, function() {
        jQuery(this).find(".delete_post").hide();
    });
    jQuery(".row_odd").hover(function() {
        jQuery(this).find(".delete_post").show()
    }, function() {
        jQuery(this).find(".delete_post").hide();
    });	    
    jQuery(".child-reply").hover(function() {
        jQuery(this).find(".delete_post").show();
        jQuery(this).find(".edit").show();
    }, function() {
        jQuery(this).find(".delete_post").hide();
        jQuery(this).find(".edit").hide();
    });
    
	// Show new topic and reply topic forms
	jQuery("#new-topic-button").click(function() {
	  	jQuery("#new-topic").toggle("slow");
	});
	jQuery("#cancel_post").click(function() {
	  	jQuery("#new-topic").hide("slow");
	});
	jQuery("#cancel_reply").click(function() {
	  	jQuery("#reply-topic").hide("slow");
	});
	
	// Has a checkbox been clicked? If so, check if one for symposium (AJAX)
    jQuery("input[type='checkbox']").bind("click",function() {
    	
    	var checkbox = jQuery(this).attr("id");		    		

    	// Subscribe to New Forum Topics in a category
    	if (checkbox == "symposium_subscribe") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {
	        	
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateForumSubscribe",
						'cid':symposium.cat_id,
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
						'cid':symposium.cat_id,
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
						'tid':symposium.show_tid, 
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
						'tid':symposium.show_tid, 
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
						'tid':symposium.show_tid, 
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
						'tid':symposium.show_tid, 
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
    		
    	// Replied
    	if (checkbox == "replies") {
			jQuery(".symposium_notice").inmiddle().fadeIn();
	        if(jQuery(this).is(":checked")) {

				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_forum_functions.php", 
					type: "POST",
					data: ({
						action:"updateTopicReplies", 
						'tid':symposium.show_tid, 
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
						'tid':symposium.show_tid, 
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

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                           MENU                                           |
	   +------------------------------------------------------------------------------------------+
	*/

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

	/*
	   +------------------------------------------------------------------------------------------+
	   |                                     NOTIFICATION BAR                                     |
	   +------------------------------------------------------------------------------------------+
	*/


	// Quick check on polling frequency
	if ( (symposium.bar_polling > 1) && (symposium.chat_polling > 1) ) {
	
		// Sound Manager
		// soundManager.url = symposium.plugin_url+'/js/soundmanager/soundmanager2.swf'; // override default SWF url
		// soundManager.debugMode = false;
		// soundManager.consoleOnly = false;
				
	  	// Set up icon actions ******************************************************
		if (jQuery("#symposium-email-box").css("display") != "none") {
	    	jQuery("#symposium-email-box").click(function() {
				window.location.href=symposium.mail_url;
	    	});
	
		}
		
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
				jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
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
		
		   	// Check for notifications, unread mail, friend requests, etc
			do_bar_check();
		   	var refreshId = setInterval(function()
		   	{
				do_bar_check();
		   	}, symposium.bar_polling*1000); // Delay to check for new mail, etc
		   	
			do_chat_check();
	   		do_chatroom_check();
			var refreshChatId = setInterval(function()
		   	{
		   		do_chat_check();
		   		do_chatroom_check();
		   	}, symposium.chat_polling*1000); // Delay to check for new messages
	
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
		jQuery("#symposium-chatroom_max").live('click', function() {
			var hm = jQuery('#chatroom_messages').height();
			var hc = jQuery('#symposium-chatroom').height();
			if (hm < 400) {
				jQuery('#chatroom_messages').height(hm+400);
				jQuery('#symposium-chatroom').height(hc+400);
				jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
				jQuery('#symposium-chatroom_max').hide();
				jQuery('#symposium-chatroom_min').show();	
			}
		});
		jQuery("#symposium-chatroom_min").live('click', function() {
			var hm = jQuery('#chatroom_messages').height();
			var hc = jQuery('#symposium-chatroom').height();
			if (hm > 400) {
				jQuery('#chatroom_messages').height(hm-400).attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
				jQuery('#symposium-chatroom').height(hc-400);
				jQuery('#symposium-chatroom_max').show();
				jQuery('#symposium-chatroom_min').hide();
			}
		});
	   		
		// Chat Window Close ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		jQuery(".chat_close").live('click', function() {
	
	   		var chat_win = jQuery(this).parent().parent().attr('id');
	   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';
	   		var display_name = jQuery(this).parent().parent().attr('id')+'_display_name';
	   		jQuery('#'+display_name).html('Closing...');
			jQuery('#'+chat_win).hide();
			
			jQuery.ajax({
				url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
				type: "POST",
				data: ({
					action:'symposium_closechat', 
					chat_from:symposium.current_user_id,
					chat_to:jQuery('#'+chat_to).html()
				}),
			    dataType: "html",
				async: false,
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
				jQuery.trim(msg);
				jQuery(this).val('');
				event.preventDefault();
	
		   		var chat_message = jQuery(this).parent().parent().attr('id')+'_message';
		   		var chat_to = jQuery(this).parent().parent().attr('id')+'_to';
				
				jQuery.ajax({
					url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
					type: "POST",
					data: ({
						action:'symposium_addchat',
						chat_from:symposium.current_user_id,
						chat_to:jQuery('#'+chat_to).html(),
						chat_message:msg
					}),
				    dataType: "html",
					async: false,
					success: function(str) {
						jQuery('#'+chat_message).append('<span style="font-weight:bold">'+str+'</span><br />');
						jQuery('#'+chat_message).attr({ scrollTop: jQuery('#'+chat_message).attr('scrollHeight') });
					},
					error: function(err){
						//alert("16:"+err);
					}		
			  	});
				
			}
		});
		
		// Type in ChatRoom Window ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
		jQuery('#chatroom_textarea').keypress(function(event) {
			if (event.which == 13) {
				var msg = jQuery(this).val();
				jQuery.trim(msg);
				jQuery(this).val('');
				event.preventDefault();

				if (msg != '') {
					jQuery.ajax({
						url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
						type: "POST",
						data: ({
							action:'symposium_addchatroom',
							chat_from:symposium.current_user_id,
							chat_message:msg
						}),
					    dataType: "html",
						async: false,
						success: function(str) {
							jQuery('#chatroom_messages').append('<div style="clear:both;font-weight:bold">'+str+'</div>');
							jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
						},
						error: function(err){
							//alert("17:"+err);
						}		
				  	});
				}
			}
		});
		
		
		// CHAT ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	
		if (symposium.use_chat == 'on') {
		
	   		var numChatWindows = 3;
	
	    	// ************** When clicking on a name to chat...
	    	
			jQuery(".symposium_online_name").live('click', function() {
	    		// choose a chat box
	    		var chatbox = 0;
	    		var already_chatting = 0;
	    		// first check to see if already chatting to them
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
						jQuery('#chat'+chatbox+'_display_name').html('Please wait...');
						jQuery("#chat"+chatbox+"_message").html('');
						jQuery("#chat"+chatbox).show('fast');
						
						jQuery.ajax({
							url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
							type: "POST",
							data: ({
								action:"symposium_openchat", 
								chat_from:symposium.current_user_id,
								chat_to:jQuery(this).attr("title")
							}),
						    dataType: "html",
							async: false,
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
							chat_from:symposium.current_user_id,
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
		
		alert('Polling frequencies needs to be changed');
		
	}

		
});

// For Notification Bar (chat windows)
function do_chat_check() {

  	var numChatWindows = 3; // Should equal number of chat windows set up in symposium_bar.php

	jQuery.ajax({
		url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
		type: "POST",
		data: ({
			action:"symposium_getchat", 
			me:symposium.current_user_id,
			inactive:symposium.inactive,
			offline:symposium.offline
		}),
	    dataType: "html",
		async: true,
		success: function(str){
			if (str != '[topsplit]') {
				var topsplit=str.split("[topsplit]");
				if (topsplit.length == 2) {
					var last_post=topsplit[0];
					var rows=topsplit[1].split("[split]");
					var num_rows = rows.length-1;
					var play_sound = false;
					
					// clear chat windows	
					for (w=1;w<=numChatWindows;w++) {	
						jQuery('#chat'+w+'_to').html('');
						jQuery('#chat'+w+'_display_name').html('');
						jQuery('#chat'+w+'_message').html('');
					}
					
					var allocated_windows = 0;
					// loop through messages, setting up all the chat windows for each person
					for (i=0;i<num_rows;i++) {	
						var details=rows[i].split("[|]");
						var from = details[0];
						var to = details[1];
						var msg = details[2];
						var name = details[3];
						var status = details[4];
	
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
						}
						
						if (chat_win == 0) {
							var allocated = false;
							for (w=1;w<=numChatWindows;w++) {	
								if ( (jQuery('#chat'+w+'_to').html() == '') && (allocated == false) ) { 
									jQuery('#chat'+w+'_to').html(other); 
									jQuery('#chat'+w+'_display_name').html('<img src="'+symposium.plugin_url+'/images/'+status+'_header.gif"> '+name); 
										allocated_windows++; 
										allocated = true;
									}
							}
						}
					}
				}		
				
				// Loop through the messages, adding the message to the correct chat window
				for (i=0;i<num_rows;i++) {	
					var details=rows[i].split("[|]");
					var from = details[0];
					var to = details[1];
					var msg = details[2];
	
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
										if (from != other) {
											jQuery('#chat'+w+'_message').append('<span style="color:#006">'+msg+'</span><br />');
										} else {
											jQuery('#chat'+w+'_message').append('<span style="color:#600">'+msg+'</span><br />');
										}
									}
								} else {
									// New chat session
									//jQuery('#chat'+w+'_message').append('Powered by <a href="http://www.wpsymposium.com" target="_blank">WP Symposium</a><hr />');
									jQuery('#chat'+w+'_message').append('Chat is still being developed...<hr />');
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
							jQuery("#chat"+w+"_message").html(message);
							jQuery('#chat'+w).show();
							jQuery("#chat"+w+"_message").attr({ scrollTop: jQuery("#chat"+w+"_message").attr("scrollHeight") });
						}
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
		},
		error: function(err){
			//alert("19:"+err);
		}		
  	});
		   	
}	
function do_chatroom_check() {

	if(!(jQuery('#symposium-chatroom').is(':visible'))) {	
		var show_chatroom = readCookie('wps_chatroom');
		if (show_chatroom == "show") {	
			jQuery('#symposium-chatroom').show("fast");
			jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
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
			jQuery('#chatroom_messages').html(str);
			jQuery('#chatroom_messages').attr({ scrollTop: jQuery('#chatroom_messages').attr('scrollHeight') });
		},
		error: function(err){
			//alert("24:"+err);
		}		
  	});
		   	
}	
function do_bar_check() {

  	// Notifications ************************************************
	jQuery.ajax({
		url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
		type: "POST",
		data: ({
			action:"checkForNotifications"
		}),
	    dataType: "html",
		async: true,
		success: function(str){
			if (str != '' && str != '-1') {
				jQuery('#info').hide().delay((symposium.bar_polling*1000) * 0.75).fadeIn('slow'); // 11 seconds
	    		jQuery('#alerts').html(str);
	    		if (symposium.sound != 'None') {
					// soundManager.play('Alert',symposium.plugin_url+'/js/soundmanager/'+symposium.sound);
	    		}
				jQuery('#alerts').fadeIn().delay((symposium.bar_polling*1000)*0.5).fadeOut('slow');
			}
		},
		error: function(err){
			//alert("20:"+err);
		}		
  	});
	
  	// Email ******************************************************
	if (jQuery("#symposium-email-box").css("display") != "none") {
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_getunreadmail", 
				me:symposium.current_user_id
			}),
		    dataType: "html",
			async: true,
			success: function(str){
				if (str > 0) {
					jQuery("#symposium-email-box").html(str);
					jQuery("#symposium-email-box").removeClass("symposium-email-box-read");
					jQuery("#symposium-email-box").addClass("symposium-email-box-unread");
				}
			},
			error: function(err){
				//alert("21:"+err);
			}		
   		});

	}
	
  	// Friends ******************************************************
	if (jQuery("#symposium-friends-box").css("display") != "none") {
		
		jQuery.ajax({
			url: symposium.plugin_url+"ajax/symposium_bar_functions.php", 
			type: "POST",
			data: ({
				action:"symposium_friendrequests", 
				me:symposium.current_user_id
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
				me:symposium.current_user_id,
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

// Cookies
function removeHTMLTags(strInputCode){
 	strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
	 	return (p1 == "lt")? "<" : ">";
	});
	var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
	return strTagStrippedText;	
}

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
	if ( (form_id) == "quick-reply") {
		with (thisform)
		{
			if (reply_text.value == '' || reply_text.value == null) {
				jQuery(".quick-reply-warning").show("slow");
				reply_text.focus(); 
				return false;
			}
		}
	}			
}

// Password strength
(function(A){A.extend(A.fn,{pstrength:function(B){var B=A.extend({verdects:["Very weak","Weak","Medium","Strong","Very strong"],colors:["#f00","#c06","#f60","#3c0","#3f0"],scores:[10,15,30,40],common:["password","sex","god","123456","123","welcome","test","qwerty","admin"],minchar:6},B);return this.each(function(){var C=A(this).attr("id");A(this).after("<div class=\"pstrength-info\" id=\""+C+"_text\"></div>");A(this).after("<div class=\"pstrength-bar\" id=\""+C+"_bar\" style=\"border: 1px solid white; font-size: 1px; height: 5px; width: 0px;\"></div>");A(this).keyup(function(){A.fn.runPassword(A(this).val(),C,B)})})},runPassword:function(D,F,C){nPerc=A.fn.checkPassword(D,C);var B="#"+F+"_bar";var E="#"+F+"_text";if(nPerc==-200){strColor="#f00";strText="Unsafe password word!";A(B).css({width:"0%"})}else{if(nPerc<0&&nPerc>-199){strColor="#ccc";strText="Too short";A(B).css({width:"5%"})}else{if(nPerc<=C.scores[0]){strColor=C.colors[0];strText=C.verdects[0];A(B).css({width:"10%"})}else{if(nPerc>C.scores[0]&&nPerc<=C.scores[1]){strColor=C.colors[1];strText=C.verdects[1];A(B).css({width:"25%"})}else{if(nPerc>C.scores[1]&&nPerc<=C.scores[2]){strColor=C.colors[2];strText=C.verdects[2];A(B).css({width:"50%"})}else{if(nPerc>C.scores[2]&&nPerc<=C.scores[3]){strColor=C.colors[3];strText=C.verdects[3];A(B).css({width:"75%"})}else{strColor=C.colors[4];strText=C.verdects[4];A(B).css({width:"92%"})}}}}}}A(B).css({backgroundColor:strColor});A(E).html("<span style='color: "+strColor+";'>"+strText+"</span>")},checkPassword:function(C,B){var F=0;var E=B.verdects[0];if(C.length<B.minchar){F=(F-100)}else{if(C.length>=B.minchar&&C.length<=(B.minchar+2)){F=(F+6)}else{if(C.length>=(B.minchar+3)&&C.length<=(B.minchar+4)){F=(F+12)}else{if(C.length>=(B.minchar+5)){F=(F+18)}}}}if(C.match(/[a-z]/)){F=(F+1)}if(C.match(/[A-Z]/)){F=(F+5)}if(C.match(/\d+/)){F=(F+5)}if(C.match(/(.*[0-9].*[0-9].*[0-9])/)){F=(F+7)}if(C.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)){F=(F+5)}if(C.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)){F=(F+7)}if(C.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)){F=(F+2)}if(C.match(/([a-zA-Z])/)&&C.match(/([0-9])/)){F=(F+3)}if(C.match(/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/)){F=(F+3)}for(var D=0;D<B.common.length;D++){if(C.toLowerCase()==B.common[D]){F=-200}}return F}})})(jQuery)
