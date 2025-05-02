/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
jQuery(document).ready(function() {
	/**
	 * Disable caching of AJAX responses
	 */
	$.ajaxSetup({
		cache: false
	});


	/**
	 * Add "target" attribute to links with rel="external"
	 */
	$("a[rel='external']").attr("target", "_blank");


	/**
	 * No autocompletion
	 */
	$("form, input").attr("autocomplete", "off");
	
	
	/**
	 * Add fancy checkboxes
	 */
	$(".restyle").checkBox({addVisualElement: false});
	
	
	/**
	 * Add date pickers
	 */
	$(".datePicker").datepicker({ dateFormat: gOptions.dateFormat });


	/**
	 * Add .has-js to popup messages
	 */
	$(".check-for-js, #success, #successImage, #toolate, #error, #deleted, #errorMsg").addClass("has-js");


	/**
	 * Automatically hide a popup message if clicked
	 */
	$(".has-js").click(function(evnt) {
		$(this).stop(true, true).hide().css("opacity", 1);
	});
	

	/**
	 * Observe the tooltips
	 */
	$("a.tooltip")
	.click(function(evnt) { evnt.preventDefault(); })
	.hover(
		function() {
			var $this, href, ttip, pos, top;
			$this = $(this);
			href  = $this.attr("href");
			ttip  = $(href);
			pos   = $this.position();
			top   = pos.top + $this.outerHeight() + 5;
			
			ttip.css({
				"top"  : top,
				"left" : pos.left
			})
			ttip.stop().fadeIn(250);
		},
		function() {
			var $this, href;
			$this = $(this);
			href  = $this.attr("href");

			$(href).stop().fadeOut(250);
		}
	);
	
	
	/**
	 * Change the content when selecting another date
	 */
	 $(".tni input").on('change', function() {

		$('input[name="date"]').each(function(){
			var dateId = $(this).val();
			var count_times_curDate = $('#times_'+dateId+' input[name="times[]"]:checked').length;
			$(this).parent().find('.datetimescount').text(
				( count_times_curDate > 0 ) ? '['+count_times_curDate+']' : ''
			);
		});

		var umnog = $('input[name="times[]"]:checked').length;

      	if (pp) {
			var all_price = pp * umnog;
			if ( all_price == 0 ) {
				all_price = pp;
			}
			all_price = Math.round(all_price * 100) / 100;
			all_price = all_price.toFixed(2);

			$('.price').text(all_price.replace('.',','));
			$('input[name="amount"]').val(all_price);
      	}

		var info_block_text = '';
		if ( umnog ){
			$('.timesCont').each(function(){
				var buff_dateId = $(this).data('dateId');
				var buff_timesText = []
				$('input[name="times[]"]:checked',this).each(function(){
					buff_timesText.push( $(this).parent('label').find('span').text() );
				});
				if (buff_timesText.length) {
					info_block_text += '<div><b>' + $('.dates label[for="date_'+buff_dateId+'"] span').text() + '</b>: (' + buff_timesText.join(', ') + ')</div>';
				}
			});
		}
		if ( info_block_text !== '' ){
			$('.times_info_block .text-warning').hide();
			$('.times_info_block .text-info').html(info_block_text);
		} else {
			$('.times_info_block .text-warning').show();
			$('.times_info_block .text-info').html(info_block_text);
		}
    });
	
	
	/**
	 * Change the content when selecting another date
	 */
	$("[name='date']").change(function(evnt) {
		// update: not make query and just switch blocks
		$(".timesCont").attr('hidden','hidden');
		$("#times_"+$(this).val()).removeAttr('hidden');
		return null;

		/*
		var $this, val, form, buffer, div, checkbox, label, input, data;
		$this  = $(this);
		val    = $this.val();
		buffer = $();

		// Add the checked class
		$this.parent("label").addClass("checked").siblings().removeClass("checked");
		
		
		//$(".timesCont").hide();
		
		// Get times for this date
		// AJAX request
		data = {
			"action" : "getTime",
			"id"     : val,
			"h"      : $("input[name='h']").val()
		};

		jQuery.post("ajax/index.php", data, function(response) {
			var json = jQuery.parseJSON(response);
			if (json.success == 1) {

				// Empty the content
				if ($("#times_"+val).length > 0) {
					div = $("#times_"+val);
				}

				// Create new div
				else {
					div = $("<div>").addClass("timesCont").attr("id", "times_"+val).hide();
				}
				
				// For each time value
				jQuery.each(json.data, function(k, ele) {
					disabled = (ele.taken && ele.taken > 0);
					label = $("<div class='form-check'>");
					input = $("<input>").attr({
						"type": "checkbox",
						"name": "times[]"
					}).val(ele.id);
					
					// Check if this appointment is already taken
					if (ele.taken && ele.taken > 0) {
						label.addClass("disabled");
						input.attr({"disabled": "disabled"});
					}
					
					checkbox = label.append(input).append(
						$("<span>").text(ele["time_start"]+" - "+ele["time_end"])
					);
					buffer = buffer.add(checkbox);
					//div.append(checkbox);
				});
				div.html(buffer);

				// Hide the old container and show the new one
				$(".times").append(div);
				$(".timesCont").hide();
				div.show();
			}
		});
		*/
	});
	
	

	/**
	 * Form methods
	 */
	
	/**
	 * Select content of input field on focus
	 */
	$("#cLink").live("focus mouseup", function(evnt) {
		this.select();
		if (evnt.type == "mouseup") {
			return false;
		}
	});
	

	/**
	 * Select and add a class
	 */
	$("#name, #email, #code").live("focus mouseup", function(evnt) {
		$(this).select().addClass("active");
		if (evnt.type == "mouseup") {
			return false;
		}
	});
	

	/**
	 * Remove the class on blur
	 */
	$("#name, #email, #code").blur(function() {
		$(this).removeClass("active");
	});
	
	
	/**
	 * Form validation
	 * Add a "not_regexp" rule to the form validator
	 */
	jQuery.validator.addMethod(
		"not_regex",
		function(value, element, regexp) {
			var re = new RegExp(regexp);
			return this.optional(element) || !re.test(value);
		}
	);
	
	// Add a "not" rule
	jQuery.validator.addMethod(
		"not",
		function(value, element, param) {
			return this.optional(element) || value != param;
		}
	);


	/**
	 * Validate the form
	 */
	$("#register").submit(function(evnt) {
		evnt.preventDefault();
	})
	.validate({
		onfocusout: false,
		onkeyup: false,
		onclick: false,
		
		// Rules for validation
		rules: {
			"name"  : {
				required  : true,
				minlength : 2,
				not_regex : "^Name$"
			},
			"email" : {
				required : true,
				email    : true
			},
			"times[]" : {
				required : true
			}
		},
		
		// Error messages
		messages: {
			"name"    : "Bitte geben Sie Ihren Namen an",
			"email"   : "Bitte geben Sie eine gültige E-Mail-Adresse an",
			"times[]" : "Bitte wählen Sie mindestens einen Termin aus"
		},
		
		// Show Errors in box
		showErrors: function(errorMap, errorList) {
			if (errorList.length > 0) {	// Only show if there actually was an error
				var cont, par;
				par  = $("#errorMsg").hide();
				cont = par.find("ul").empty();
				
				jQuery.each(errorList, function(k, err) {
					cont.append($("<li>").html(err.message));
				});
				par.stop().hide().css("opacity", 1).fadeIn().delay(3000).fadeOut();
			}
		},
		
		// Submit the form via AJAX
		submitHandler: function(form) {
			var success, toolate, error, data, json;
			success = $("#success");
			toolate = $("#toolate");
			error   = $("#error");
			data    = $(form).serializeObject();
			data.action = "ajaxSend";	// Default action
			
			jQuery.post("ajax/index.php", data, function(response) {
				json = jQuery.parseJSON(response);
				
				$(".toolate").removeClass("toolate");
				$(".success").removeClass("success");
				jQuery.each([success, toolate, error], function() {
					$(this).hide();
				});

				
				// No success
				if (json.success != 1) {
					
					// Some times were already taken
					if (json.taken) {
						// Set these to disabled and uncheck them
						jQuery.each(json.taken, function(k, ele) {
							$("input[name='times[]'][value='"+ele+"']").attr({"disabled": "disabled", "checked": false}).parent().addClass("disabled toolate");
						});
						
						// Display "too late" message
						toolate.stop().hide().css("opacity", 1).fadeIn().delay(5000).fadeOut();
					}
					
					// Some other error happened
					else {
						// console.log("json?.errors",json?.errors)
						error.html(json?.errors)
						error.stop().hide().css("opacity", 1).fadeIn().delay(5000).fadeOut();
					}
				}
				
				// Registration was successful
				else {
					if (json.data) {
						// Set times to disabled but set different class
						jQuery.each(json.data.times, function(k, ele) {
							$("input[name='times[]'][value='"+ele+"']").attr({"disabled": "disabled", "checked": false}).parent().addClass("disabled success");
						});
						// Display successful message
						success.stop().hide().css("opacity", 1).fadeIn().delay(5000).fadeOut();
					}
				}
			});
		}
	});
});



/**
 * Additional functions, methods, properties, plugins
 */
(function($) {
	/**
	 * Make console.log work in every browser
	 */

	// console not present at all
	if (!(window.console && console.log)) {
		(function() {
			var noop    = function() {};
			var methods = [
				'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 
				'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'markTimeline', 'table', 'time', 'timeEnd', 'timeStamp',
				'trace', 'warn'
			];
			var length  = methods.length;
			var console = window.console = {};
			while (length--) {
				console[methods[length]] = noop;
			}
		}());
	}


	// Replace any non existing method with an empty function
	if (window.console) {
		var noop    = function() {};
		var methods = [
				'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 
				'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'markTimeline', 'table', 'time', 'timeEnd', 'timeStamp',
				'trace', 'warn'
			];
		var length  = methods.length;

		// Check for .group()
		if (!console.group) {
			console.group = console.info;
			console.groupCollapsed = console.info;
		}
		
		// Replace any non-existing method with an empty function
		while (length--) {
			if (!console[methods[length]]) {
				//console["groupCollapsed"] = console.call(console["group"], console);
				console[methods[length]] = noop;
			}
		}

		// Create alias for console
		window.debug = window.console;
	}

	/**
	 * Add two functions to jQuery to test if variable is an Element
	 */
	$.isNode = function(o) {
		var o = $(o)[0];
		return(
			typeof Node === "object" ? o instanceof Node :
			typeof o === "object" && typeof o.nodeType === "number" && typeof o.nodeName === "string"
		);
	};
	$.isElement = function(o) {
		var o = $(o)[0];
		return(
			typeof HTMLElement === "object" ? o instanceof HTMLElement :
			typeof o === "object" && o.nodeType === 1 && typeof o.nodeName === "string"
		);
	};


	/**
	 * Extend jQuery to include a "serializeObject" method that returns a hash-like object ({name1: value1, name2: value2, etc})
	 */
	$.fn.serializeObject = function() {
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			}
			else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};


	/**
	 * Display an element and hide if after x seconds
	 * @param delay float The delay in seconds
	 */
	$.fn.showAndHide = function(delay) {
		delay = (typeof(delay) === "undefined" || isNaN(delay)) ? 5000 : (delay * 1000);
		this.stop(true, true).hide().css("opacity", 1).fadeIn().delay(delay).fadeOut();
	}
})(jQuery);