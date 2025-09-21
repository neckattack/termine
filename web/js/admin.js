/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
jQuery(document).ready(function() { 
	
	/**
	 * Global error handler for AJAX calls
	 */
	$(document).ajaxError(function(evnt, jqxhr, settings, exception) {
		var msg = "Ein Fehler ist aufgetreten!";
		switch (jqxhr.status) {
			// Forbidden, session timed out
			case 403:
				msg += "\nIhre Session ist ausgelaufen. Bitte melden Sie sich neu an.";
				msg += "\nURL: "+settings.url;
			break;
			
			// File not found
			case 404: 
				msg += "\nDatei nicht gefunden.";
				msg += "\nURL: "+settings.url;
			break;
		}
		// Display the error message
		alert(msg);
	});
	


	/**
	 * Multiselect options
	 * Can be different from page to page
	 */
	var multiSelectOptions = {};



	/**
	 * Display options on the client overview page
	 */
	if ($("#display-options").length > 0) {

		/**
		 * Observe the the showGroup element
		 * Simply reload the page
		 */
		$("#showGroup").change(function(evnt) {
			var $this, val, href, nHref;
			$this = $(this);
			val   = $this.val();
			href  = window.location.href;
			nHref = href.split("?")[0];
			
			if (val.length > 0 && val != "-1") {
				nHref += "?group_id="+val;
			}
			window.location.href = nHref;
		});

		/**
		 * Display or hide disabled clients
		 */
		$("#showDisabled").change(function(evtn) {
			var show, eles;
			show = !!($(this).val() == 1);
			eles = $("#clientlist").find(".disabled");

			if (show === false) {
				eles.fadeOut();
			}
			else if (show === true) {
				eles.fadeIn();
			}
		});
	}



	/**
	 * Error message handler
	 * @param mixed errorMap
	 * @param array errorList Array with all errors
	 */
	var showErrorFunction = function(errorMap, errorList) {
		var cont, li;

		if (errorList.length > 0) {	// Only show if there actually was an error
			li   = $("<li />")
			cont = $("#errorMsg ul").empty();
			
			jQuery.each(errorList, function(k, err) {
				cont.append(li.clone().html(err.message));
			});

			cont.parent().hide().stop().css("opacity", 1).fadeIn(500, function() {
				$(this).delay(3000).fadeOut(500);
			});
		}
	};



	/**
	 * Catch the clicks on links with class .delete
	 */
	var clickLinkHandler = function(evnt) {
		evnt.preventDefault();
		var ele, params, id, item, href, nHref, msg, conf, responseHandler;
		ele    = $(this);
		href   = ele.attr("href");
		nHref  = "ajax/"+href.split(".php")[0]+".php";
		params = jQuery.parseQuery(href);
		id     = params.id;
		msg    = false;
		
		
		// Which action should be performed
		switch (params.action) {
			//copy a date
			case "copydate":
				msg  = "Do you want to copy this date?";
			break;
			// Delete a date
			case "deletedate":
				msg  = "Soll dieses Eintrag inklusive aller Reservierungen gelöscht werden?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;
			
			// Delete a reservation
			case "deletereservation":
				msg  = "Soll diese Reservierung gelöscht werden?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;

			// Delete a whole time entry
			case "deletetimeentry":
				msg  = "Soll dieser Termin inklusive der Reservierung gelöscht werden?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;
			
			// Delete a group
			case "deletegroup":
				msg  = "Soll diese Gruppe gelöscht werden?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;

			// Delete a user
			case "deleteuser":
				msg  = "Soll dieser Benutzer gelöscht werden?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;

			// Remove user from client / group
			case "removeuser":
				msg  = "Benutzer von hier entfernen?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;

			// Remove cleint from group
			case "removeclient":
				msg  = "Kunde von hier entfernen?";
				msg += "\n(Diese Aktion wird sofort durchgeführt.)";
			break;

			// Remove a date slot
			case "removeslot":

			break;
		}
		
		
		// Display a confirmation popup
		if (msg) {
			conf = confirm(msg);
			if (!conf) return;
		}

		// Response function
		responseHandler = function(json) {
			if (json.success == 1) {
				switch (params.action) {
					case "copydate":
					alert('copy date success');
					break;

					// It was a date, remove the line
					case "deletedate":
						$("#date_"+id).fadeOut(500, function() {
							ele.remove();
						});
					break;
					
					
					// Delete a reservation
					case "deletereservation":
						$("#res_"+id).removeAttr("id").find(".name").html("- frei -").next(".delete").fadeOut(500, function() {
							ele.remove();
						});
					break;


					// It was a client, only disable the line
					case "disableclient":
					case "enableclient":
						$("#client_"+json.id).toggleClass("disabled");
					break;


					// Delete group
					// Delete user
					// Remove user from a group / client
					// Delete a time entry
					case "deletetimeentry":
					case "deletegroup":
					case "deleteuser":
					case "removeuser":
						// id must be the value of uid for editgroups.php
						if ($("#editGroup").length > 0) {
							id = params.uid;
						}

						ele.closest("li").fadeOut(500, function() {
							$(this).remove();
						});
					break;


					// Remove a client from a group
					case "removeclient":
						ele.closest("li").fadeOut(500, function() {
							$(this).remove();
						});
					break;


					// Remove a slot
					case "deleteslot":
						ele.parents(".block").remove();
					break;
				}
			}

			else if (json.message) {
				alert(json.message);
			}
		};


		// Use AJAX or not?
		if (typeof(params.noajax) && params.noajax == 1) {
			responseHandler({success: 1});
		}

		// Send the AJAX request
		else {
			jQuery.get(nHref, params, responseHandler);
		}
		
		
		
	};
	
	// Set the click handler for those links
	$(".delete, .enable").live("click", clickLinkHandler);
	$(".copydate, .enable").live("click", clickLinkHandler);
	

	/**
	 * Edit/Add a client
	 * Validate the form
	 */
	var editClient = $("#editClient");
	if (editClient.length > 0) {
		editClient.validate({
			onfocusout: false,
			onkeyup: false,
			onclick: false,
			
			// Rules for validation
			rules: {
				"cName"  : {
					required  : true,
					minlength : 2
				}
			},
			
			// Error messages
			messages: {
				"cName" : "Bitte geben Sie einen Namen für den Kunden an"
			},
			
			// Show Errors in box
			showErrors: showErrorFunction,
			
			// Submit the form via AJAX
			submitHandler: function(form) {
				var form  = $(form);
				var data  = form.serializeObject();
				data.action = "ajaxSend";
				
				// Send the form
				jQuery.post("ajax/editclient.php", data, function(json) {
					$("#success, #error, #deleted").hide();
					
					// Success
					if (typeof(json) == "object" && json.success && json.success == 1) {
						$("#success").hide().stop().css("opacity", 1).fadeIn(500, function() {
							$(this).delay(3000).fadeOut(500);
						});

						
						// Set the id of the client to the form if a new user was added
						if (data.clientID == 0) {
							$("#cid").val(json.id);

							if (history.pushState) {
								history.pushState({id: json.id}, document.title, window.location.href+"?cid="+json.id);
							}
						}

						// If a contact was selected, also select the entry in the user list (if it is visible)
						var user_ids    = $("#user_ids");
						var contact_ids = [];
						if (user_ids.length > 0) {
							if (data.contact_masseur_id > 0) {
								contact_ids.push(data.contact_masseur_id);
							}
							if (data.contact_client_id > 0) {
								contact_ids.push(data.contact_client_id);
							}

							if (contact_ids.length > 0) {
								jQuery.each(contact_ids, function() {
									var that = this;
									user_ids.multiselect("widget").find("[value="+that+"]:not([checked])").each(function() {
									   this.click();
									});
								});
							}
						}
					}
					
					// Error
					else {
						$("#error").hide().stop().css("opacity", 1).fadeIn(500, function() {
							$(this).delay(3000).fadeOut(500);
						});
					}
				});
			}
		});

	
		// Define the multiselect options
		multiSelectOptions = {
			classes          : "is-multiselect",
			noneSelectedText : "Keiner zugeordnet",
			checkAllText     : "Alle",
			uncheckAllText   : "Keiner",
			selectedText     : "# von # Benutzern",
			selectedList     : 1,
			minWidth         : 300
		};
	}



	/**
	 * Show the image upload container
	 */
	$("#saveImageLink").click(function(evnt) {
		evnt.preventDefault();
		var cont, link, pos;
		cont = $("#saveImageCont");
		link = $("#imageLinks");
		pos  = link.position();
		
		cont.css({
			top : pos.top,
			left: pos.left
		});
		
		link.stop().fadeOut(250, function() {
			cont.show();
		});
		
	});



	/**
	 * Cancel the image upload
	 */
	$("#saveImageCont").find("[type='reset']").click(function(evnt) {
		var cont, link;
		cont = $("#saveImageCont");
		link = $("#imageLinks");
		
		cont.stop().fadeOut(250, function() {
			link.show();
		});
	});



	/**
	 * Image upload form submit
	 */
	$("#saveImageForm").submit(function(evnt) {
		var form, iframeId, iframe, response, json, img, src, id, type, now;
		evnt.preventDefault();
		iframeId = "imageUploadFrame";
		form     = $(this).attr("target", iframeId);

		if ($("#"+iframeId).length < 1) {
			iframe = $('<iframe name="'+iframeId+'" id="'+iframeId+'" style="display: none;" />');
			//iframe = $('<iframe name="'+iframeId+'" id="'+iframeId+'" />');
			$("#content").append(iframe);
			

			$("#"+iframeId).on("load", function(evnt) {
				response = $(this).contents().find("body").html();	// .text()?
				json = jQuery.parseJSON(response);

				if (json.success && json.success === true) {
					// Show message
					$("#successImage").hide()
					.html("Das Bild wurde erfolgreich hochgeladen.")
					.stop().css("opacity", 1).fadeIn(500, function() {
						$(this).delay(3000).fadeOut(500);
					});

					// Change image
					// Need to add another variable to prevent caching
					// Don't bother with base64 encoding at this point
					id   = $("#owner_id").val();
					type = $("#owner_type").val();
					now  = (new Date()).getTime();
					img  = $("#previewImage").find("img");
					src  = img.attr("src").split("?")[0];
					src += "?id="+id+"&type="+type+"&_="+now;

					img.remove();
					img = $("<img />").attr("alt", "").attr("src", src);
					$("#previewImage").find("a").append(img);

					// Show delete image button
					$("#deleteImage").show();

					// Hide upload form
					$("#imageLinks").show();
					$("#saveImageCont").stop().fadeOut(250, function() {
						$("#imageLinks").show();
					});
				}

				// Specific error
				else if (json.error && json.message) {
					showErrorFunction({}, [{message: json.message}]);
				}

				// Unspecific error
				else {
					showErrorFunction({}, [{message: "Ein unbekannter Fehler ist aufgetreten"}]);
				}

			});
		}

		// Trigger the regular submit event
		form.get(0).submit();
	});



	/**
	 * Delete an image
	 */
	$("#deleteImage").click(function(evnt) {
		evnt.preventDefault();
		var form, href, params, img, src, id, type, now;
		form = $("#saveImageForm");
		href = form.attr("action");
		params = {
			action     : "delete",
			owner_id   : $("#owner_id").val(),
			owner_type : $("#owner_type").val()
		};

		jQuery.get(href, params, function(json) {
			if (json.success && json.success === true) {
				// Show message
				$("#successImage").hide()
				.html("Das Bild wurde erfolgreich gelöscht.")
				.stop().css("opacity", 1).fadeIn(500, function() {
					$(this).delay(3000).fadeOut(500);
				});

				// Change image
				// Need to add another variable to prevent caching
				// Don't bother with base64 encoding at this point
				id   = $("#owner_id").val();
				type = $("#owner_type").val();
				now  = (new Date()).getTime();
				img  = $("#previewImage").find("img");
				src  = img.attr("src").split("?")[0];
				src += "?id="+id+"&type="+type+"&_="+now;
				
				img.remove();
				img = $("<img />").attr("alt", "").attr("src", src);
				$("#previewImage").find("a").append(img);

				$("#deleteImage").hide();
			}
		});
	});

	

	/**
	 * Edit/Add a group
	 * Validate the form
	 */
	$("#editGroup").validate({
		onfocusout: false,
		onkeyup: false,
		onclick: false,
		
		// Rules for validation
		rules: {
			"name"  : {
				required  : true,
				minlength : 2
			}
		},
		
		// Error messages
		messages: {
			"name" : "Bitte geben Sie einen Namen für die Gruppe an"
		},
		
		// Show Errors in box
		showErrors: showErrorFunction,
		
		// Submit the form via AJAX
		submitHandler: function(form) {
			var form  = $(form);
			var data  = form.serializeObject();
			data.action = "ajaxSend";
			
			// Send the form
			jQuery.post("ajax/editgroup.php", data, function(json) {
				$("#success, #error, #deleted").hide();
				
				// Success
				if (typeof(json) == "object" && json.success && json.success == 1) {
					$("#success").hide().stop().css("opacity", 1).fadeIn(500, function() {
						$(this).delay(3000).fadeOut(500);
					});
					
					// Set the id value to the newly added group id
					if (data.id == 0) {
						$("#id").val(json.id);

						if (history.pushState) {
							history.pushState({id: json.id}, document.title, window.location.href+"?id="+json.id);
						}
					}
				}
				
				// Error
				else {
					$("#error").hide().stop().css("opacity", 1).fadeIn(500, function() {
						$(this).delay(3000).fadeOut(500);
					});
				}
			});
		}
	});
	


	/**
	 * Edit/Add a user (admin)
	 * Validate the form
	 */
	var editUser = $("#editUser");
	if (editUser.length > 0) {
		var reqPass = false;

		// Check if a new user
		// If yes, the password fields are required
		if ($("#id").val() == 0) {
			reqPass = true;


			// Force clearing of the form fields
			// autocomplete=off doesn't really seem to work correctly
			editUser.find("input[type='text']").val("");
		}

		// Always clear the password field
		editUser.find("input[type='password']").val("");

		editUser.validate({
			onfocusout: false,
			onkeyup: false,
			onclick: false,
			
			// Rules for validation
			rules: {
				"user_username" : {
					required  : true,
					minlength : 2,
					remote    : {
						url      : "ajax/check_username.php",
						cache    : false,
						dataType : "json",
						data     : {
							action : "both",
							value  : $("#user_username").val(),
							id     : $("#id").val()
						}
					}
				},
				"user_email" : {
					required  : true,
					minlength : 6,
					email     : true
				},
				"user_password1" : {
					required  : reqPass
				},
				"user_password2" : {
					equalTo   : "#user_password1"
				}
			},
			
			// Error messages
			messages: {
				"user_username" : {
					required         : "Bitte geben Sie einen Benutzernamen ein.",
					minlength        : "Benutzername zu kurz (min. 2 Stellen).",
					alphanumeric     : 'Bitte geben sie nur A-Z, 0-9, Punkt, Binde- oder Unterstrich bei Benutzername ein.'
				},

				"user_email"    : "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
				"user_password1": {
					required : "Bitte geben Sie ein Passwort ein."
				},

				"user_password2": {
					equalTo  : "Die beiden Passwortfelder müssen übereinstimmen."
				}
			},
			
			// Show Errors in box
			showErrors: showErrorFunction,
			
			// Submit the form via AJAX
			submitHandler: function(form) {
				var form  = $(form);
				var data  = form.serializeObject();
				data.action = "ajaxSend";
				
				// Send the form
				jQuery.post("ajax/edituser.php", data, function(json) {
					$("#success, #error, #deleted").hide();
					
					// Success
					if (typeof(json) == "object" && json.success && json.success == 1) {
						$("#success").hide().stop().css("opacity", 1).fadeIn(500, function() {
							$(this).delay(3000).fadeOut(500);
						});
						
						// Set the id of the client to the form if a new user was added
						if (data.id == 0) {
							$("#id").val(json.id);
							
							if (history.pushState) {
								history.pushState({id: json.id}, document.title, window.location.href+"?id="+json.id);
							}
						}
					}
					
					// Error
					else {
						$("#error").hide().stop().css("opacity", 1).fadeIn(500, function() {
							$(this).delay(3000).fadeOut(500);
						});
					}
				});
			}
		});
	}

	

	
	/**
	 * Change date masseur
	 */
	$(".dates select.date-masseur").change(function(evnt) {
		var date_id = $(this).data('id');
		var masseur_id = parseInt($(this).val());

		params = {
			action 		: "datemasseur",
			date_id  	: date_id,
			masseur_id  : masseur_id,
		};
		
		// Send the form
		jQuery.post("ajax/editclient.php", params, function(json) {
			$("#success, #error").hide();

			// Display messages
			if (typeof(json) == "object" && json.success && json.success == 1) {
				$("#success").hide().stop().css("opacity", 1).fadeIn(500, function() {
					$(this).delay(3000).fadeOut(500);
				});
			}
			else {
				$("#error").hide().stop().css("opacity", 1).fadeIn(500, function() {
					$(this).delay(3000).fadeOut(500);
				});
			}
		});
	});
	

	
	/**
	 * Add a date picker row
	 */
	$("#addDate").click(function(evnt) {
		evnt.preventDefault();
		var div, cid;
		div = $("<div>");
		cid = $("#cid").val();
		
		div.append($("<input>").attr({
				type: "text",
				name: "cDays[]"
			}).addClass("datePicker")
			  .datepicker({ dateFormat: gOptions.dateFormat })
		);
		
		// Do not add the save button if the client hasn't been saved yet
		if (cid && cid > 0) {
			div.append($("<button>").attr({
					type: "button"
				}).addClass("saveDate").html("Speichern")
			)
			.append($("<a>").attr({
					href : "editslots.php?id="
				}).html("Termine").hide()
			)
			.append($("<a>").attr({
					href : "editclient.php?action=deletedate&id="
				}).addClass("delete").html("Löschen").hide()
			);
		}
		
		// Add the div to the container
		div.insertBefore("#addDate");
	});
	
	

	/**
	 * Observe all .saveDate buttons
	 * When a new date has been added
	 */
	$(".saveDate").live("click", function(evnt) {
		var target, ele, cid, params;
		evnt.preventDefault();
		
		target = $(this);
		ele    = target.prev();
		
		if (!ele.val()) return;
		
		params = {
			action: "savedate",
			date  : ele.val(),
			cid   : $("#cid").val()
		};
		
		// Send the form
		jQuery.post("ajax/editclient.php", params, function(json) {
			$("#error").hide();
			
			// Display messages
			if (typeof(json) == "object" && json.success && json.success == 1) {
				
				// Set the id of the date
				ele.parent().attr("id", "date_"+json.id);
				
				// Remove save button
				target.remove();
				
				// Set the ID to the hidden elements and show them
				jQuery.each(ele.nextAll(), function(k, elem) {
					elem = $(elem);
					elem.attr("href", elem.attr("href")+json.id).show();
				});
				
			}
			else {
				$("#error").hide().stop().css("opacity", 1).fadeIn(500, function() {
					$(this).delay(3000).fadeOut(500);
				});
			}
		});
	});
	

	/**
	 * Check if the time values entered in a block are valid
	 * @param  block element The .block element
	 * @param  emptyIsValid bool If an empty entry is valid or not (empty valids are valid on blur, but invalid on submit)
	 * @return valid bool
	 */
	var checkTimeFunction = function(block, emptyIsValid) {
		var start, end, startVal, endVal, pattern, validStart, validEnd, valid, matches, startHour, startMinute, endHour, endMinute;
		block        = $(block);
		emptyIsValid = !!(emptyIsValid);
		start        = block.find("[name='starttimes[]']");
		end          = block.find("[name='endtimes[]']");
		startVal     = start.val();
		endVal       = end.val();
		pattern      = /(^([0-9]|[0-1][0-9]|[2][0-3]):([0-5][0-9])$)|(^([0-9]|[1][0-9]|[2][0-3])$)/;		// 0-23, 00-23, 0:00-23:59, 00:00-23:59
		

		// Both entries are empty, ignore
		if (startVal.length < 1 && endVal.length < 1) {
			return true;
		}

		// Obey the emptyIsValid parameter
		if (emptyIsValid === true) {
			if (startVal.length < 1 || endVal.length < 1) {
				return true;
			}
		}

		// Check the format of the values
		validStart = pattern.test(startVal);
		validEnd   = pattern.test(endVal);
		valid      = !!(validStart && validEnd);
		
		
		// Show error
		// console.log("valid format? ", valid);
		if (!valid) {
			return false;
		}


		// Check if end time is larger than start time
		matches     = startVal.match(pattern);
		startHour   = Number(matches[2] || matches[4]);
		startMinute = Number(matches[3] || 0);
		
		matches     = endVal.match(pattern);
		endHour     = Number(matches[2] || matches[4]);
		endMinute   = Number(matches[3] || 0);
		
		// console.log("startHour:    ", startHour);
		// console.log("startMinute:  ", startMinute);
		// console.log("endHour:      ", endHour);
		// console.log("endMinute:    ", endMinute);

		// Start time must be earlier than end time
		valid = ( startHour < endHour || (startHour == endHour && startMinute < endMinute) );

		// console.log("valid time values? ", valid);
		if (!valid) {
			return false;
		}


		return true;
	};


	/**
	 * Check the time values automatically
	 * Those can be dynamically added, so observe the form instead
	 */
	$("#generateSlots").on("blur", "input[name='starttimes[]'], input[name='endtimes[]']", function(evnt) {
		var block, valid;
		block = $(this).closest(".block");
		valid = checkTimeFunction(block, true);	// Set emptyIsValid to true

		if (!valid) {
			block.stop(true, true).effect("highlight", {color: "#FF8080"}, 5000);
			$("#error-invalid").showAndHide(2);
		}
	});

	
	/**
	 * Edit slots
	 * Display confirmation message when generating new time values
	 */
	$("#generateSlots").submit(function(evnt) {
		evnt.preventDefault();

		var form, params, error, errors, duration;
		form          = $("#generateSlots");
		params        = form.serializeObject();
		params.action = "checkifavailable";
		errors        = $();
		proceed       = false;

		// For each entry there must be a start and an end time
		// as well as a duration
		form.find(".block").each(function(k, block) {
			block    = $(block);
			valid    = checkTimeFunction(block);
			duration = block.find("[name='durations[]']").val();

			// Duration needs to be set as well
			if (duration.length < 1 || isNaN(duration)) {
				valid = false;
			}

			// Insert the element into the errors array
			if (!valid) {
				errors = errors.add(block);
			}
			else {
				proceed = true;
			}
		});


		// Display error message and highlight rows
		if (errors.length > 0) {
			errors.stop(true, true).effect("highlight", {color: "#FF8080"}, 5000);
			$("#error-invalid").showAndHide(2);
			return false;
		}
		

		// Check if time values overlap
		// console.log(params);
		if (proceed === true) {
			jQuery.post("ajax/editslots.php", params, function(json) {
				// console.log(json);

				// Submit form
				if (json.success == 1) {
					form.get(0).submit();	// Native call to prevent an endless loop
				}

				// Show error
				else {
					if (typeof(json.errors) !== "undefined") {
						jQuery.each(json.errors, function(k, num) {
							// console.log(this);
							// console.log(k, num);
							form.find(".block").eq(num).stop(true, true).effect("highlight", {color: "#FF8080"}, 5000);
							$("#error-not-available").showAndHide();
						});
					}
				}
			});
		}


		return false;
	});
	
	// Add a new block
	$("#addBlock").find("a").click(function(evnt) {
		evnt.preventDefault();
		
		var div, a;
		
		// Copy the first line
		div = $(".block").first().clone().removeAttr("style");
		div.find("input").val("");

		// Add a delete link
		a = $("<a />").attr("href", "?action=deleteslot&noajax=1").text("Ausblenden").addClass("delete");
		div.append(a);

		// Insert
		div.insertBefore("#addBlock");
	});


	/**
	 * Add a client to a group
	 */
	$(".addToContClient").find(".add").click(function(evnt) {
		var $this, cont, mode, addTo, selfId, targetId, href, data,
			sName, mail, findVal, textChange, linkChange, textRemove, linkRemove,
			memberCont, li, span, a;
		
		$this = $(this);
		cont  = $this.closest(".addToCont");

		targetId = $("#id").val();
		selfId   = $("#addToClient").val();
		addTo    = cont.attr("class").match(/add-to-(\w+)/i)[1];
		href     = "ajax/editclient.php";
		data     = {
			action : "addclient",
			self   : selfId,
			target : targetId
		};

		// console.log(data);

		// Nothing selected
		if (selfId < 1 || targetId < 1 || typeof(selfId) === "undefined" || typeof(targetId) === "undefined" || typeof(selfId) == "" || typeof(targetId) == "") {
			return false;
		}

		// Selection already exists

		/*
		build = {
			textChange = {
				client : "Kunde Ändern",
				group  : "Gruppe Ändern",
				user   : "Benutzer Ändern"
			},

			linkChange = {
				client : "editclient.php?cid="+targetId,
				group  : "editgroup.php?id="+targetId,
				user   : "edituser.php?id="+selfId
			},

			textRemove = {
				client : "Von Kunden entfernen",
				group  : "Aus Gruppe entfernen",
				user   : "Aus Gruppe entfernen"
			},

			linkRemove = {
				client : "editclient.php?action=removeclient&tid="+targetId+"&id="+selfId,
				group  : "edituser.php?action=removeuser&type="+addTo+"&tid="+targetId+"&id="+selfId,
				user   : ""
			}
		};
		*/


		// Send data
		jQuery.get(href, data, function(response) {
			// Add group to group list
			if (response.success && response.success == 1) {
				findVal    = selfId;
				textChange = "Kunde Ändern";
				linkChange = "editclient.php?cid="+selfId;
				textRemove = "Aus Gruppe entfernen";
				linkRemove = "editclient.php?action=removeclient&tid="+targetId+"&id="+selfId;

				sName  = cont.find("option[value='"+findVal+"']").text();

				li     = $("<li />");
				span   = $("<span />").html(sName);
				change = $("<a />").addClass("change").attr("href", linkChange).text(textChange);
				remove = $("<a />").addClass("delete").attr("href", linkRemove).text(textRemove);

				memberCont = $(".memberships, .members").find("ul."+addTo+"-list");
				memberCont.append(
					li.attr("id", "entry_"+addTo+"_"+findVal).hide()
					.append(span).append(document.createTextNode(" "))
					.append(change).append(document.createTextNode(" "))
					.append(remove)
				);

				memberCont.find(".entry_0").hide();
				li.fadeIn(250).css("display", "list-item");
			}
		});
	});

	

	/**
	 * Add a user to a group / client
	 */
	$(".addToCont:not(.addToContClient)").find(".add").click(function(evnt) {
		var $this, cont, mode, addTo, selfId, targetId, href, data,
			sName, mail, findVal, textChange, linkChange, textRemove, linkRemove,
			memberCont, li, span, a;
		
		$this = $(this);
		cont  = $this.closest(".addToCont");


		// This exists in two pages, 1x in editgroup and 1x in edituser (which again is split up into "add to group" and "add to client")
		mode     = ($("#editGroup").length > 0) ? "group" : "user";
		addTo    = cont.attr("class").match(/add-to-(\w+)/i)[1];
		targetId = (mode == "group") ? $("#id").val()         : cont.find("select").val();
		selfId   = (mode == "group") ? $("#addToGroup").val() : $("#id").val();
		
		href    = "ajax/edituser.php";
		data    = {
			action : "adduser",
			type   : addTo,		// group, client, user
			self   : selfId,
			target : targetId
		};

		// Nothing selected
		if (selfId < 1 || targetId < 1 || typeof(selfId) === "undefined" || typeof(targetId) === "undefined" || typeof(selfId) == "" || typeof(targetId) == "") {
			return false;
		}

		// Selection already exists


		// Send data
		jQuery.get(href, data, function(response) {
			// Add group to group list
			if (response.success && response.success == 1) {
				findVal    = (mode  == "group")  ? selfId : targetId;
				textChange = (mode  == "group")  ? "Mitglied Ändern"         : ( (addTo == "client") ? "Kunde Ändern" : "Gruppe Ändern" );
				linkChange = (mode  == "group")  ? "edituser.php?id="+selfId : ( (addTo == "client") ? "editclient.php?cid="+targetId : "editgroup.php?id="+targetId );
				textRemove = (addTo == "client") ? "Von Kunden entfernen"    : "Aus Gruppe entfernen";
				linkRemove = "edituser.php?action=removeuser&type="+addTo+"&tid="+targetId+"&id="+selfId;

				sName  = cont.find("option[value='"+findVal+"']").text();

				// If mode = group, parse the user name and look for the email address
				if (mode == "group") {
					mail   = /\((.*)\)/ig.exec(sName)[1];
					sName  = sName.replace(new RegExp("("+mail+")", "ig"), '<a href="mailto:$1">$1</a>');
				}

				li     = $("<li />");
				span   = $("<span />").html(sName);
				change = $("<a />").addClass("change").attr("href", linkChange).text(textChange);
				remove = $("<a />").addClass("delete").attr("href", linkRemove).text(textRemove);

				memberCont = $(".memberships, .members").find("ul."+addTo+"-list");
				memberCont.append(
					li.attr("id", "entry_"+addTo+"_"+findVal).hide()
					.append(span).append(document.createTextNode(" "))
					.append(change).append(document.createTextNode(" "))
					.append(remove)
				);

				memberCont.find(".entry_0").hide();
				li.fadeIn(250).css("display", "list-item");
			}
		});
	});

	

	/**
	 * Click on cancel
	 */
	$(".addToCont").find(".cancel").click(function(evnt) {
		$(this).closest(".addToCont").stop().fadeOut(250, function() {
			//$("#addToGroupLink").show();
		});
	});



	/**
	 * Click on .addLink
	 */
	$(".addLink").click(function(evnt) {
		evnt.preventDefault();
		var cont, visible, type;
		type    = $(this).attr("class").match(/add-(\w+)/i)[1];
		cont    = $(".addToCont.add-to-"+type);
		visible = cont.is(":visible");

		if ($("#id").val() == 0 && !visible) {
			alert("Bitte speichern Sie erst den neuen Eintrag.")
			return false;
		}

		
		cont.toggle();
	});



	/**
	 * Multiselects
	 * Uses the multiSelectOptions variable
	 */
	$(".multiselect").multiselect(multiSelectOptions)
	.multiselectfilter({
		label       : "",
		placeholder : "Filter"
	});
});
 

/**
 * Additional Methods
 */
(function($) {
	/**
	 * Add a "alphanumeric" rule to the jQuery validator
	 */
	jQuery.validator.addMethod(
		"alphanumeric",
		function(value, element) {
			return this.optional(element) || /^[a-z0-9äÄöÖüÜßáÁàÀâÂéÉèÈêÊóÓòÒôÔúÚùÙûÛ._-]+$/i.test(value);
		},
		"Letters, numbers, spaces or underscores only please"
	);

	/**
	 * Specific function that checks both the availability and validity of a username
	 */
	$.validator.addMethod(
		"remote_is_valid",
		function(value, element) {
			var res = $.ajax({
				url      : "ajax/check_username.php",
				cache    : false,
				dataType : "json",
				data     : {
					action : "both",
					value  : value
				}
			});
			return res;
		}
	);



	/**
	 * A simple querystring parser.
	 * Example usage: var q = $.parseQuery(); q.fooreturns  "bar" if query contains "?foo=bar"; multiple values are added to an array. 
	 * Values are unescaped by default and plus signs replaced with spaces, or an alternate processing function can be passed in the params object .
	 * http://actingthemaggot.com/jquery
	 *
	 * Copyright (c) 2008 Michael Manning (http://actingthemaggot.com)
	 * Dual licensed under the MIT (MIT-LICENSE.txt)
	 * and GPL (GPL-LICENSE.txt) licenses.
	 **/
	jQuery.parseQuery = function(qs,options) {
		//var q = (typeof qs === 'string' ? qs : window.location.search);
		var q;
		if (typeof qs === 'string') {
			var arr = qs.split("?");
			arr.shift();
			q = "?"+arr.join("");
		}
		else {
			q = window.location.search;
		}
		
		var o = {'f':function(v){return unescape(v).replace(/\+/g,' ');}}, options = (typeof qs === 'object' && typeof options === 'undefined')?qs:options, o = jQuery.extend({}, o, options), params = {};
		jQuery.each(q.match(/^\??(.*)$/)[1].split('&'),function(i,p){
			p = p.split('=');
			p[1] = o.f(p[1]);
			params[p[0]] = params[p[0]]?((params[p[0]] instanceof Array)?(params[p[0]].push(p[1]),params[p[0]]):[params[p[0]],p[1]]):p[1];
		});
		return params;
	}
})(jQuery);