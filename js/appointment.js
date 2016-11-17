$(document).ready(function() {
	//patient name code
	jQuery("#RadioGroup1_1").click(function() {
		jQuery('#pname').fadeIn(500);
	});
	jQuery("#RadioGroup1_0").click(function() {
		jQuery('#pname').fadeOut(500);
	});


	/* step2 - procceed to 2nd step */
	$("#nxtStp1").click(function (){
		//check if all fields are ok
		if(validateStep1()) {
			$.post('/appointment/check-login/', function(bool){ 
				var str = jQuery("#patient_slot").val();
				var timedata = str.split("_"); 
				if(bool==1){ //if already logged in					
					$('#status').val('e');	//status - logged in as an old user
					//store the appointment
					step1to(3, timedata[0], timedata[1]);
					return true;
				} else { // not logged in, go to step 2					
					step1to(2, timedata[0], timedata[1]);
					return false;
				}
			});
		} else {
			console.log("Validation problem");
		}
	});


	/*---------------------------- step2 - login ---------------------------------*/	
	//step2 - validate login form, proceed to step3
	$("#loginBtn").click(function (){		
		var error_flag = 0;
		var username = $("#username").val();
		var password = $("#password").val();
		
		var error_msg = "<div id='errorbox'><h4>Please fill in the following fields:</h4><ul id='errorList'>";
		if(username=="" ) { 
			error_msg += "<li>Email</li>";	
			error_flag = 1; 
		}
		if(!isEmail(username) && username != '') { 
			error_msg += "<li>A valid Email</li>";	
			error_flag = 1; 
		}
		if(password=="") { 
			error_msg += "<li>Password</li>";	
			error_flag = 1; 
		}
		error_msg +="</ul></div>";
		
		if(error_flag) { 
			//alert(error_msg); 
			$("#errorMessage").html(""); //clean any previous messages
			$("#errorMessage").html(error_msg);
			$("#errorLauncher").fancybox({
				'width' : 200,
				'height' : 300,
				'autoScale' : true,
				'transitionIn' : 'none',
				'transitionOut' : 'none',
				'autoDimensionst' : false,
				'hideOnContentClick': true,
				'content': error_msg
			});
			$("#errorLauncher").click();
			return false; 
		} else {
			$.post('/appointment/do-login/', {
				username:username,
				password:password,
				rememberMe:0
			}, function(html){
				var decoded = $.json.decode(html);
				if(decoded['err']=='1'){
					var error_msg = "<div id='errorbox'><h4>There was an error</h4><ul id='errorList'>";
					error_msg += decoded['msg'];
					error_msg +="</ul><div>";
					$("#errorMessage").html(""); //clean any previous messages
					$("#errorMessage").html(error_msg);
					$("#errorLauncher").fancybox({
						'width' : 200,
						'height' : 300,
						'autoScale' : true,
						'transitionIn' : 'none',
						'transitionOut' : 'none',
						'autoDimensionst' : false,
						'hideOnContentClick': true,
						'content': error_msg
					});
					$("#errorLauncher").click();
					//alert(error_msg);
				} else {
					$('#status_val').val('e');	//status - logged in as an old user
					//book the appointment
					appdate = jQuery("#appointment_date").val();
					apptime = jQuery("#appointment_time").val();
					if($("#onbehalf").val() == 1) {
						patname = decoded["name"];
					} else {
						patname = pname;
					}					
					reason = jQuery("#reason_for_visit").val();
					step2to3();
				}
			});			
		}
	});

	/*---------------------------- step2 - register ---------------------------------*/	
	//step2 - validate registration form, proceed to step3
	$("#registerBtn").click(function (){
		$('#status').val('n');
		var error_flag = 0;
		var firstname = $("#firstname").val();
		var lastname = $("#lastname").val();
		var username = $("#newemail").val();
		var password = $("#newpassword").val();
		var password2 = $("#newpass2").val();
        var terms = $('#terms').is(':checked');
		
		var error_msg = "<div id='errorbox'><h4>Please fill in the following fields:</h4><ul id='errorList'>";

        if(terms==false ) {
            error_msg += "<li>Terms &amp; Conditions</li>";
            error_flag = 1;
        }
		if(username=="" ) { 
			error_msg += "<li>Email</li>";	
			error_flag = 1; 
		}
		if(!isEmail(username) && username != '') { 
			error_msg += "<li>A valid Email</li>";	
			error_flag = 1; 
		}
		if(password=="") { 
			error_msg += "<li>Password</li>";	
			error_flag = 1; 
		} else if(password != password2) {
			error_msg += "<li>Passwords don't match</li>";	
			error_flag = 1; 
		}
		
		error_msg +="</ul></div>";
		
		if(error_flag) { 
			//alert(error_msg); 
			$("#errorMessage").html(""); //clean any previous messages
			$("#errorMessage").html(error_msg);
			$("#errorLauncher").fancybox({
				'width' : 200,
				'height' : 300,
				'autoScale' : true,
				'transitionIn' : 'none',
				'transitionOut' : 'none',
				'autoDimensionst' : false,
				'hideOnContentClick': true,
				'content': error_msg
			});
			$("#errorLauncher").click();
			return false;
		} else {

			var newemail = jQuery.trim($("#newemail").val());
			$.post('/appointment/checknewmail/', {
				newemail:newemail
			},
			function(html){
				var decoded = $.json.decode(html);
				if(decoded['err']=='1'){
					var error_msg = "<div id='errorbox'><h4>There was an error</h4><ul id='errorList'>";
					error_msg += decoded['msg'];
					error_msg +="</ul><div>";
					$("#errorMessage").html(""); //clean any previous messages
					$("#errorMessage").html(error_msg);
					$("#errorLauncher").fancybox({
						'width' : 200,
						'height' : 300,
						'autoScale' : true,
						'transitionIn' : 'none',
						'transitionOut' : 'none',
						'autoDimensionst' : false,
						'hideOnContentClick': true,
						'content': error_msg
					});
					$("#errorLauncher").click();
				} else {
					//can register
					$.post('/appointment/register-patient/', {
						newemail:username,
						newpassword:password,
						newname:firstname,
						newlastname:lastname
					},
					function(html){
						var decoded = $.json.decode(html);
						if(decoded['err']=='1'){
							var error_msg = "<div id='errorbox'><h4>There was an error</h4><ul id='errorList'>";
							error_msg += decoded['msg'];
							error_msg +="</ul><div>";
							$("#errorMessage").html(""); //clean any previous messages
							$("#errorMessage").html(error_msg);
							$("#errorLauncher").fancybox({
								'width' : 200,
								'height' : 300,
								'autoScale' : true,
								'transitionIn' : 'none',
								'transitionOut' : 'none',
								'autoDimensionst' : false,
								'hideOnContentClick': true,
								'content': error_msg
							});
							$("#errorLauncher").click();
							return false;
						} else {
							//successful registration
							//get patient info for step 3
							appdate = jQuery("#appointment_date").val();
							apptime = jQuery("#appointment_time").val();
							reason = jQuery("#reason_for_visit").val();
							//store appointment
							step2to3();
						} 
					});
				}
			});	
		}

	});
	$('.allprofile').on('click', '.data-column a', function(e){
		e.preventDefault();
		changeslot(jQuery(this));
	});

});

//timeslot selection
function changeslot(elem) {
	var currentId = jQuery(elem).attr('id');
	//console.log("selecting "+currentId);
	jQuery('.slots-n').removeClass("slots-n").addClass("slots");
	jQuery(".selectedSlot").removeClass("selectedSlot");

	jQuery(elem).removeClass("slots").addClass("slots-n");	
	jQuery(elem).closest(".bluebg").addClass("selectedSlot");

	jQuery("#patient_slot").val(currentId);
	var str = currentId.split("_");
	jQuery("#appointment_time").val(str[1]);
	jQuery("#appointment_date").val(str[0]);

}

/* step1 - validate */
function validateStep1() {
	error_flag = 0;
	var drid_val = $("#drid").val();
	var patient_slot_val = $("#patient_slot").val();
	var reason_val = $("#reason_to_visit").val();
	var insurance = $("#insurance_company").val();
	var needs_val = $("#needs").val();
	var error_msg = "<div id='errorbox'><h4>Please fill in the following fields:</h4><ul id='errorList'>";
	if(patient_slot_val == "" ) { 
		error_msg += "<li>Choose a time slot</li>";	
		error_flag = 1; 
	}
	if(reason_val=="" || (reason_val=="0" && needs_val=="") || (reason_val=="0" && needs_val=="other") ){ 
		error_msg += "<li>Reason of visit</li>";	
		error_flag = 1; 
	}
	error_msg +="</ul></div>";
	if(error_flag) { 
		//alert(error_msg); 
		$("#errorMessage").html(""); //clean any previous messages
		$("#errorMessage").html(error_msg);
		$("#errorLauncher").fancybox({
			'width' : 200,
			'height' : 300,
			'autoScale' : true,
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'autoDimensionst' : false,
			'hideOnContentClick': true,
			'content': error_msg
		});
		$("#errorLauncher").click();
		return false; 
	} else {
		return true;
	}
}

function step1to(step, appdate, apptime) {
	if(step==2) {
		jQuery('#appstep1').fadeOut(500,function(){});
		jQuery('#appstep2').fadeIn(500);		
		jQuery("#appdate").html(formatDate(appdate));
		var aptime = apptime.split(':');
		var hour = aptime[0];
		var ampm = "am";
		if(aptime[0]>12) {
			hour = aptime[0];
			ampm = "pm";
		} 
		jQuery('#apptime').html(hour+":"+aptime[1]+" "+ampm);
		jQuery("#appointmentData").fadeIn(500);
		jQuery(".appointmentdetails-section01-left a").removeClass("active");
		jQuery(".appointmentdetails-section01-middle a").addClass("active");

	} else {
		jQuery('#initialScreen').fadeOut(500,function(){});
		jQuery(".appointmentdetails-section01-left a").removeClass("active");
		jQuery(".appointmentdetails-section01-right a").addClass("active");
		submitAppointment();
		jQuery('#appstep3').fadeIn(500);
	}
	window.scrollTo(0, 0);
}

function step2to3() {
	jQuery('#initialScreen').fadeOut(500,function(){});
	jQuery(".appointmentdetails-section01-middle a").removeClass("active");
	jQuery(".appointmentdetails-section01-right a").addClass("active");
	//book appointment
	submitAppointment();
	jQuery('#appstep3').fadeIn(500);
	window.scrollTo(0, 0);
}

function isEmail(emailStr) {
    var reEmail=/^[0-9a-zA-Z_\.-]+\@[0-9a-zA-Z_\.-]+\.[0-9a-zA-Z_\.-]+$/;
    if(!reEmail.test(emailStr))
    {
        return false;
    }
    return true;
}

//final submit of the entire form.
function submitAppointment() {
	var newemail = $("#newemail").val();
	var newpassword = $("#newpassword").val();
	var name_val = $("#firstname").val();
	var lastname_val = $("#lastname").val();
	
	var status_val = $("#status").val();
	var first_visit = get_radio_value('first_visit');
	var pname_val = $("#pname").val();
	var drid_val = $("#drid").val();
	var appointment_time_val = $("#appointment_time").val();
	var appointment_date_val = $("#appointment_date").val();
	var onbehalf_val = get_radio_value('onbehalf');

	var needs_val = $("#needs").val();	
	var reason_val = $("#reason_for_visit").val();
	var insurance_company_val = $("#insurance_company").val();
	var insurance_plan_val = $("#insurance_plan").val();

	var error_flag = 0;

	$.post('/appointment/create-appointment/', {
		newemail:newemail, newpassword:newpassword, drid:drid_val, name:name_val, lastname:lastname_val, status:status_val,
		appointment_time:appointment_time_val,appointment_date:appointment_date_val,needs:needs_val,
		reason:reason_val,insurance_company:insurance_company_val,insurance_plan:insurance_plan_val,first_visit:first_visit,onbehalf:onbehalf_val,pname:pname_val
	},
	function(html){
		var decoded = $.json.decode(html);
		afterAppointment(decoded);
	});
}

function afterAppointment(decoded){		
	if(decoded['err']==1){
		var error_msg = "<div id='errorbox'><h4>Please fill in the following fields:</h4><ul id='errorList'>";
		error_msg += decoded['msg'];
		error_msg +="</ul></div>";
		//alert(error_msg);
		$("#errorMessage").html(""); //clean any previous messages
		$("#errorMessage").html(error_msg);
		$("#errorLauncher").fancybox({
			'width' : 200,
			'height' : 300,
			'autoScale' : true,
			'transitionIn' : 'none',
			'transitionOut' : 'none',
			'autoDimensionst' : false,
			'hideOnContentClick': true,
			'content': error_msg
		});
		$("#errorLauncher").click();
	} else {
		appdate = jQuery("#appointment_date").val();
		apptime = jQuery("#appointment_time").val();
		patname = decoded["name"];
		reason = jQuery("#reason_for_visit").val();
		if(reason == "") {
			reason = "Not specified";
		}

		jQuery('#appdateFinal').html(formatDate(appdate));
		var aptime = apptime.split(':');
		var hour = aptime[0];
		var ampm = "am";
		if(aptime[0]>12) {
			hour = aptime[0];
			ampm = "pm";
		} 
		jQuery('#apptimeFinal').html(hour+":"+aptime[1]+" "+ampm);
		jQuery('#patnameFinal').html(patname);
		jQuery('#reasonFinal').html(reason);
		var icalLink = "/appointment/show-ical/id/"+decoded['app_id'];
		ical = jQuery("#ical").attr("href", icalLink);
	}
	window.scrollTo(0, 0);
}

function get_radio_value(element){
    var ret = false;
    jQuery("input[name='"+element+"']").each(function() {
        if(this.checked==true)ret = this.value;
    });
    return ret;
}

function formatDate(appdate) {
	var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
	var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	var date = new Date(appdate);
	day = days[date.getDay()];
	month = months[date.getMonth()];
	//console.log(date.toISOString());
	var dateToShow = day+" "+month+" "+date.getDate()+", "+date.getFullYear();
	return dateToShow;
}
