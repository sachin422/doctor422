<?php

class User_TimeslotController extends Base_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();
        $this->_helper->layout->setLayout('doctorpanel');
		$usersNs = new Zend_Session_Namespace("members");
        $userid = $usersNs->userId;
		$Doctor = new Application_Model_Doctor();
		$doctor = $Doctor->fetchRow("user_id=".$userid);
		$DoctorPatient = new Application_Model_DoctorPatient();
		$patients = $DoctorPatient->fetchAll("doctor_id=".$doctor->getId());
		$this->view->patientCount = count($patients);
			
		 /*  code for  referrals patient count   start     */
	   
		$ReferralsPatient = new Application_Model_ReferralsPatient();
		$referralspatient = $ReferralsPatient->fetchAll("doctor_id=".$doctor->getId());
		$this->view->referralspatientCount = count($referralspatient);
		/* code for  referrals patient count   end      */

		$Appointment = new Application_Model_Appointment();
		$date  = date('Y-m-d'); 
		$where = "doctor_id='{$doctor->getId()}' AND appointment_date >= '$date' AND approve!=2";
		$appointments = $Appointment->fetchAll($where );
		$this->view->appointmentsCount = count($appointments);

    }
 
    public function indexAction() {
        $request = $this->getRequest();
        $post = $request->getPost();
        $post['month'] = $this->_getParam("month");
        $post['year'] = $this->_getParam("year");
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;

        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->find($drid);

        $Calendar = new Zend_Session_Namespace("calendar");
        if(isset($Calendar->CALDAY)){
            $month = date('m', $Calendar->CALDAY);
            $year = date('Y', $Calendar->CALDAY);
        }else{
            $month = date('m');
            $year = date('Y');
        }

        if (isset($post['month']) && $post['month'] > 0)
            $month = $post['month'];
        if (isset($post['year']) && $post['year'] > 0)
            $year = $post['year'];

         if ((isset($post['month']) && $post['month'] > 0) && (isset($post['year']) && $post['year'] > 0)){
             $today = mktime(0, 0, 0, $month, 1, $year);
             $Calendar->CALDAY =$today;
         }
       
        $monthArr = array(1 => "Jan", "Feb", "Mar", "Apr", "May", "June", "July", "Aug", "Sep", "Oct", "Nov", "Dec");

        $this->view->docObject = $docObject;
        $this->view->month = $month;
        $this->view->year = $year;
        $this->view->monthArr = $monthArr;
        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }

    function editAction() {
		
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;

        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->find($drid);
        $this->view->docObject = $docObject;
		
		$DoctorAppointment = new Application_Model_DoctorAppointmentCal();
		$allAppointments = $DoctorAppointment->fetchAll("doctor_id='{$drid}'", "slot_date ASC");
		$this->view->allAppointments = $allAppointments;

		$RemovedDates = new Application_Model_AppointmentRemovedDate();
		$removedDates = $RemovedDates->fetchAll("doctor_id = ".$drid, 'slot_date ASC');
		$this->view->removedDates = $removedDates;
		
    }

    
	function updateAction() {
        $request = $this->getRequest();
        $post = $request->getPost();
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;
        $date = $post['date'];
        $DoctorAppointment = new Application_Model_DoctorAppointmentCal();
        $DoctorAppointment->delete("slot_date='" . $date . "' AND doctor_id = " . $drid);
        $AppointmentRemovedDate = new Application_Model_AppointmentRemovedDate();
        $AppointmentRemovedDate->delete("doctor_id='{$drid}'  AND slot_date='$date'");// remove the entry from deleted date
        //error_log("saving...".print_r($post['displayCheck'], true));
        if($post['displayCheck']){
        	$displayCheck = explode(",", $post['displayCheck']);
            foreach ($displayCheck  as $slot) {
                $timeSlot = date("H:i", strtotime($date . " " . trim($slot)));
				
				//error_log(date("H:i", strtotime("2011-10-14 01:00 PM")));
                if (trim($slot) != "") {
                    $DoctorAppointment->setDoctorId($drid);
                    $DoctorAppointment->setSlotTime($timeSlot);
                    $DoctorAppointment->setSlotDate($date);
                    $DoctorAppointment->save();
                }
            }
        }else{
        	//error_log("saved");
            $AppointmentRemovedDate->setDoctorId($drid);
            $AppointmentRemovedDate->setSlotDate($date);
            $AppointmentRemovedDate->save();
        }
        /*$m = date("m", strtotime($date));
        $y = date("Y", strtotime($date));*/
        //$this->_helper->redirector('index', 'timeslot', "user", Array('drid' => $drid, 'month' => $m, 'year' => $y));
        exit();
    }

    function cancelTimeOffAction() {
    	$request = $this->getRequest();
        $post = $request->getPost();
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;
        $date = $post['date'];
        $DoctorAppointment = new Application_Model_DoctorAppointmentCal();
        $DoctorAppointment->delete("slot_date='" . $date . "' AND doctor_id = " . $drid);

        $AppointmentRemovedDate = new Application_Model_AppointmentRemovedDate();
        $AppointmentRemovedDate->delete("doctor_id='{$drid}' AND slot_date='$date'");
        exit();
    }

    function showTimeOffSlots() {
    	$request = $this->getRequest();
        $post = $request->getPost();
        $date = $post['date'];
        
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;
		
		$TimeSlot  = new Base_Timeslot();
		$slotsAvailable = $TimeSlot->getDoctorTimeSlots($drid, $date);
		if($slotsAvailable){
		   // echo '<input type="checkbox" id="checkall" name="checkall"> <strong>'.$this->lang[527].'</strong><br />';
		}
		$returnString = "";
		$isDeletedSlot = $TimeSlot->isDeletedSlot($drid, $date);
		foreach($slotsAvailable as $slot){
		    $checked = "";
			
		    if($isDeletedSlot===false){ // first check if all the slots had deleted for this date.
		        if(!empty($slotsForDay)){
		            if(in_array($slot, $slotsForDay))$checked = "checked=\"checked\"";
		        }else{
		            $checked = "checked=\"checked\"";
		        }
		    }
		    $timetoshow = date("h:i a", strtotime($slot));
		    $returnString .= '<input type="checkbox" class="displayCheck" name="displayCheck[]" value="'.$slot.'" '.$checked.'> '.$timetoshow.'<br/>';
		}


        return $returnString;
        exit;
	}


	function showSlots($drid, $date) {
		$returnString = "";
		$TimeSlot  = new Base_Timeslot();
		$slotsAvailable = $TimeSlot->getDoctorTimeSlots($drid, $date);
		if($slotsAvailable){
		   // echo '<input type="checkbox" id="checkall" name="checkall"> <strong>'.$this->lang[527].'</strong><br />';
		}

		$Checked = new Application_Model_DoctorAppointmentCal();
		$checked = $Checked->fetchAll("slot_date='" . $date . "' AND doctor_id = " . $drid);
		$slotsForDay = array();
		if($checked) {
			foreach($checked as $checkslot) {
				$slotsForDay[] = $checkslot->getSlotTime();
			}
		} else { //no timeslots are set, use the default ones
			$MasterSlot = new Application_Model_MasterTimeslot();
			$day = strtoupper(date("D", strtotime($date)));
			$object = $MasterSlot->fetchRow("slot_day='" . $day . "' AND doctor_id = -1 AND week_number=1");
			if($object) {
				$TimeSlot = new Base_Timeslot();
				$slotsForDay = $TimeSlot->breakTimeslots($object->getStartTime(), $object->getEndTime(), $object->getSlotInterval());
			}
		}

		$isDeletedSlot = $TimeSlot->isDeletedSlot($drid, $date);
		foreach($slotsAvailable as $slot){
		    $checked = "";
			
		    if($isDeletedSlot===false){ // first check if all the slots had deleted for this date.
		        if(!empty($slotsForDay) && in_array($slot, $slotsForDay)) { //uncheck removed slots
	            	$checked = "checked=\"checked\"";
		        }else{
		        	//check each slot
		            $checked = "";
		        }
		    } else {
		    	$checked = "";
		    }
		    $timetoshow = date("h:i a", strtotime($slot));
		    
		    $returnString .= '<input type="checkbox" class="displayCheck" name="displayCheck[]" value="'.$slot.'" '.$checked.'> '.$timetoshow.'<br/>';
		    
		}
		return $returnString;
	}

	function drawTimeOffLineAction() {
		$request = $this->getRequest();
        $post = $request->getPost();
        $date = $post['date'];
        
        $usersNs = new Zend_Session_Namespace("members");
        $drid = $usersNs->doctorId;

        $slots = $this->showSlots($drid, $date); 
        //error_log($slots);

		$returnString = '
<div class="row">
	<div class="col-sm-12">
		<div class="whiterow clearfix">
    		<div class="leftblue-grad-big">
        		<div class="wmiddle text-center">
            		<img src="/images/user/datecount.png" alt="Date"> '.strftime("%a, %d-%B %Y", strtotime($date)).'
        		</div>
        	</div>

        	<div class="rightwhite small">
        		<div class="container-fluid daycontainer" id="dayContainer1">
            		<div class="row">
            			<div class="col-sm-1">
            			</div>
            			<div class="col-sm-11">
            				<div class="timeoff">
            					<form name="form1" method="post" action="">
            						<label>
            							<input type="radio" name="RadioGroup" value="All Day" class="alldayRadio" checked="checked" >
            							<span class="ms">All Day</span>
            						</label>
            						<label>
            							<input type="radio" name="RadioGroup" value="radio" class="timeslotsRadio" >
            							<span class="ms">Select time</span>
        							</label>
        							<input type="submit" name="submit" class="saveTimeslots" value="Save" style="text-transform: none; width: 325px;"/>
        							<input name="Cancel" type="submit" value="Cancel" class="cancel">
        						</form>
        					</div>
        				</div>
        			</div>
        			<div class="row">
        				<div class="col-sm-12">
        					<div class="timeslots" style="display: none;">
                      			<table>
                  					<tr>
               	 	                  <td align="right" valign="top">choose the timeslots you want to appear in your callendar</td>
               	 	                </tr>
               	 	                <tr>
               	 	                    <td>
               	 	                    	'.$slots.'
               	 	                    </td>
               	 	                </tr>
               	 	                <tr>
               	 	                    <td align="center">
               	 	                        <input type="hidden" name="date" class="date" value="'.$date.'" />
               	 	                    </td>
               	 	                </tr>
               	 	            </table>
                   	 	    </div>
        				</div>
        			</div>
        		</div>
        	</div>
        </div>
	 	</div>
	</div>';
	echo $returnString;
	exit();
	}
	
	  function masterSlotAction() {

        $drid = $this->_getParam('drid');
        $request = $this->getRequest();
        $options = $request->getPost();
       
        $form = new User_Form_MasterSlot();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
            $element->removeDecorator('tag');
        }

        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->find($drid);
        $MasterSlot = new Application_Model_MasterTimeslot();
        if($drid > 0){
            $slotObject = $MasterSlot->fetchAll("doctor_id='{$drid}' AND week_number=1", "id ASC");
            $i = 1;
            $setOptions = array();
            foreach($slotObject as $slots){

                $setOptions['id'.$i] = $slots->getId();
                $setOptions['ischecked'.$i] = $slots->getIsChecked();
                $setOptions['stime'.$i] = $slots->getStartTime();
                $setOptions['etime'.$i] = $slots->getEndTime();
                $setOptions['time'.$i] = $slots->getSlotInterval();
                $i++;
            }
            $setOptions['notificationby'] = $docObject->getNotificationby();
            /*$setOptions['minutesbefore'] = $docObject->getMinutesbefore();
            $setOptions['hoursbefore'] = $docObject->getHoursbefore();
            $setOptions['daybefore'] = $docObject->getDaybefore();*/
            $form->populate($setOptions);
            
        }

        if ($request->isPost()) {
        	if ($form->isValid($options)) {
                $MasterSlot->delete("doctor_id='{$drid}'");
                $weekDays = array('1'=>'MON','2'=>'TUE','3'=>'WED','4'=>'THU','5'=>'FRI','6'=>'SAT','7'=>'SUN');

                $j = 1;
                $weekNum = 1;
                for($i=1; $i<=7; $i++) {
                	if($options['displayCheck'.$i]){
					 $slots = implode(',', $options['displayCheck'.$i]);
					}else{
					 $slots = '';
					}
					// for time slot 1
					$MasterSlot->setId(null);
					$MasterSlot->setDoctorId($drid);
					$MasterSlot->setSlotDay($weekDays[$j]);
					$MasterSlot->setIsChecked($options['ischecked'.$i]);
					$MasterSlot->setStartTime($options['stime'.$i]);
					$MasterSlot->setEndTime($options['etime'.$i]);
					$MasterSlot->setSlotInterval($options['time'.$i]);
					$MasterSlot->setWeekNumber($weekNum);
					$MasterSlot->setDisplaySlots($slots);
					$MasterSlot->save();
					$j++;
                }

                $MasterSlot2= new Application_Model_MasterTimeslot();
			  	$j = 1;
                $weekNum = 2;
				for($i=1; $i<=7; $i++) {
					if($options['displayCheck'.$i]){
					$slots = implode(',', $options['displayCheck'.$i]);
					}else{
					$slots = '';
					}

					// for time slot 2
					$MasterSlot2->setId(null);
					$MasterSlot2->setDoctorId($drid);
					$MasterSlot2->setSlotDay($weekDays[$j]);
					$MasterSlot2->setIsChecked($options['ischecked'.$i]);
					$MasterSlot2->setStartTime($options['stime'.$i]);
					$MasterSlot2->setEndTime($options['etime'.$i]);
					$MasterSlot2->setSlotInterval($options['time'.$i]);
					$MasterSlot2->setWeekNumber($weekNum);
					$MasterSlot2->setDisplaySlots($slots);
					$MasterSlot2->save();

					$j++;
                }

                //reminder options
               // $docObject->setNotificationby($options['notificationby']);
                /*$docObject->setMinutesbefore($options['minutesbefore']);
                $docObject->setHoursbefore($options['hoursbefore']);
                $docObject->setDaybefore($options['daybefore']);*/
                $docObject->save();

                $Reminder = new Application_Model_DoctorReminder();
                $Reminder->delete('drid='.$drid);
                if($options['daybefore']) {
	                foreach($options['daybefore'] as $key=>$value) {
	                	$reminder = new Application_Model_DoctorReminder();
	                	$reminder->setDrid($drid);
	                	$reminder->setDaybefore($value);
	                	$reminder->setHoursbefore($options['hoursbefore'][$key]);
	                	$reminder->setMinutesbefore($options['minutesbefore'][$key]);
	                	$reminder->save();
	                }
	            }
	          
				
				$Doctor->setId($drid);
				$Doctor->setEnableAppointmentReminderText($options['enable_appointment_reminder_text']);
				$Doctor->setEnableReviewReminderText($options['enable_review_reminder_text']);
				$Doctor->setEnableAppointmentReminderCall($options['enable_appointment_reminder_call']);
				$Doctor->setEnableAppointmentScheduleText($options['enable_appointment_schedule_text']);
				$Doctor->setEnableAppointmentScheduleCall($options['enable_appointment_schedule_call']);
				$Doctor->setEnableAppointmentSchedulingEmail($options['enable_appointment_scheduling_email']);
				$Doctor->setEnableAppointmentReminderEmail($options['enable_appointment_reminder_email']);
				$Doctor->setEnableAppointmentReviewEmail($options['enable_appointment_review_email']);
				
				//echo '<pre>';print_r($Doctor);die;
				$Doctor->saveTurnOnOfSetting();
				$params = array("drid"=>$drid);
				$this->_helper->redirector('master-slot', 'timeslot', "user",$params);
            }
        }
		$Reminders = new Application_Model_DoctorReminder();
        $reminders = $Reminders->fetchAll("drid=".$docObject->getId());
        $this->view->reminders = $reminders;
        $this->view->docObject = $docObject;
        $this->view->form = $form;
        
		$form2 = new User_Form_MyProfile();
		//echo '<pre>';print_r($form);die;
		$form2->setAttrib("enctype", "multipart/form-data");
		$elements2 = $form2->getElements();
		$form2->clearDecorators();
		foreach ($elements2 as $element) {
			$element->removeDecorator('label');
			$element->removeDecorator('row');
			$element->removeDecorator('data');
		}
		if (0 < (int) $drid) {
			//populate form
			$options2['enable_appointment_reminder_text'] = $docObject->getEnableAppointmentReminderText();
			$options2['enable_review_reminder_text'] = $docObject->getEnableReviewReminderText();
			$options2['enable_appointment_reminder_call'] = $docObject->getEnableAppointmentReminderCall();
			$options2['enable_appointment_schedule_text'] = $docObject->getEnableAppointmentScheduleText();
			$options2['enable_appointment_schedule_call'] = $docObject->getEnableAppointmentScheduleCall();
			$options2['enable_appointment_scheduling_email'] = $docObject->getEnableAppointmentSchedulingEmail();
			$options2['enable_appointment_reminder_email'] = $docObject->getEnableAppointmentReminderEmail();
			$options2['enable_appointment_review_email'] = $docObject->getEnableAppointmentReviewEmail();
			$options2['doctor_voice'] = $docObject->getDoctorVoice();
			$form2->populate($options2);
        }
        $this->view->form2 = $form2;
		
    }
 
	
    function masterSlotActionBkp24May2016() {

        $drid = $this->_getParam('drid');
        $request = $this->getRequest();
        $options = $request->getPost();

        $form = new User_Form_MasterSlot();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
            $element->removeDecorator('tag');
        }

        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->find($drid);
        $MasterSlot = new Application_Model_MasterTimeslot();
        if($drid > 0){
            $slotObject = $MasterSlot->fetchAll("doctor_id='{$drid}' AND week_number=1", "id ASC");
            $i = 1;
            $setOptions = array();
            foreach($slotObject as $slots){

                $setOptions['id'.$i] = $slots->getId();
                $setOptions['ischecked'.$i] = $slots->getIsChecked();
                $setOptions['stime'.$i] = $slots->getStartTime();
                $setOptions['etime'.$i] = $slots->getEndTime();
                $setOptions['time'.$i] = $slots->getSlotInterval();
                $i++;
            }
            $setOptions['notificationby'] = $docObject->getNotificationby();
            /*$setOptions['minutesbefore'] = $docObject->getMinutesbefore();
            $setOptions['hoursbefore'] = $docObject->getHoursbefore();
            $setOptions['daybefore'] = $docObject->getDaybefore();*/
            $form->populate($setOptions);
            
        }

        if ($request->isPost()) {
        	//if ($form->isValid($options)) {
                  if ($options) {
                $MasterSlot->delete("doctor_id='{$drid}'");
                $weekDays = array('1'=>'MON','2'=>'TUE','3'=>'WED','4'=>'THU','5'=>'FRI','6'=>'SAT','7'=>'SUN');

                $j = 1;
                $weekNum = 1;
                for($i=1; $i<=7; $i++) {
                	if($options['displayCheck'.$i]){
					 $slots = implode(',', $options['displayCheck'.$i]);
					}else{
					 $slots = '';
					}
					// for time slot 1
					$MasterSlot->setId(null);
					$MasterSlot->setDoctorId($drid);
					$MasterSlot->setSlotDay($weekDays[$j]);
					$MasterSlot->setIsChecked($options['ischecked'.$i]);
					$MasterSlot->setStartTime($options['stime'.$i]);
					$MasterSlot->setEndTime($options['etime'.$i]);
					$MasterSlot->setSlotInterval($options['time'.$i]);
					$MasterSlot->setWeekNumber($weekNum);
					$MasterSlot->setDisplaySlots($slots);
					$MasterSlot->save();
					$j++;
                }

                $MasterSlot2= new Application_Model_MasterTimeslot();
			  	$j = 1;
                $weekNum = 2;
				for($i=1; $i<=7; $i++) {
					if($options['displayCheck'.$i]){
					$slots = implode(',', $options['displayCheck'.$i]);
					}else{
					$slots = '';
					}

					// for time slot 2
					$MasterSlot2->setId(null);
					$MasterSlot2->setDoctorId($drid);
					$MasterSlot2->setSlotDay($weekDays[$j]);
					$MasterSlot2->setIsChecked($options['ischecked'.$i]);
					$MasterSlot2->setStartTime($options['stime'.$i]);
					$MasterSlot2->setEndTime($options['etime'.$i]);
					$MasterSlot2->setSlotInterval($options['time'.$i]);
					$MasterSlot2->setWeekNumber($weekNum);
					$MasterSlot2->setDisplaySlots($slots);
					$MasterSlot2->save();

					$j++;
                }

                //reminder options
                $docObject->setNotificationby($options['notificationby']);
                /*$docObject->setMinutesbefore($options['minutesbefore']);
                $docObject->setHoursbefore($options['hoursbefore']);
                $docObject->setDaybefore($options['daybefore']);*/
                $docObject->save();

                $Reminder = new Application_Model_DoctorReminder();
                $Reminder->delete('drid='.$drid);
                if($options['daybefore']) {
	                foreach($options['daybefore'] as $key=>$value) {
	                	$reminder = new Application_Model_DoctorReminder();
	                	$reminder->setDrid($drid);
	                	$reminder->setDaybefore($value);
	                	$reminder->setHoursbefore($options['hoursbefore'][$key]);
	                	$reminder->setMinutesbefore($options['minutesbefore'][$key]);
	                	$reminder->save();
	                }
	            }
            }
        }
		$Reminders = new Application_Model_DoctorReminder();
        $reminders = $Reminders->fetchAll("drid=".$docObject->getId());
        $this->view->reminders = $reminders;
        $this->view->docObject = $docObject;
        $this->view->form = $form;
		
    }
    function getDoctorSlotsAction() {

    	$usersNs = new Zend_Session_Namespace("members");
        $userid = $usersNs->userId;
        $Doctor = new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id=".$userid);
        $drid = $doctor->getId();
        
        $stime = $this->_getParam('stime');
        $etime = $this->_getParam('etime');
        $intval = $this->_getParam('intval');
        $num = $this->_getParam('num');
        $slotDay = $this->_getParam('day');
        $slots = "";

        $MasterSlot = new Application_Model_MasterTimeslot();
        $masterslot = $MasterSlot->fetchRow("doctor_id='$drid' AND week_number='1' AND slot_day='{$slotDay}'", "id ASC");
        $slotsString = "";
        if($masterslot) {
	        $TimeSlot = new Base_Timeslot();
	        $selectedSlots = $TimeSlot->breakTimeslots($stime, $etime, $intval, null, $masterslot->getDisplaySlots()); //available timeslots
	        //$allSlots = $masterslot->getDisplaySlots();
	        $allSlots = $TimeSlot->breakTimeslots($stime, $etime, $intval);

	        //$allSlots = explode(",", $allSlots);
	        if($allSlots) {
		        foreach($allSlots as $slot) {
		        	$checked = "";
		        	if(in_array($slot, $selectedSlots)) {
		        		 $checked = "checked='checked'";
		        	}
		        	$timetoShow = date("g:i a", strtotime($slot));
		        	$slotsString .= "<input type='checkbox' name='displayCheck{$num}[]' {$checked} value='{$slot}'>{$timetoShow}<br />";
		        }
		    }
        } else { //no timeslots are set, use the default ones
        	$query = "slot_day='" . $slotDay . "' AND doctor_id = -1 AND week_number=1";
			$object = $MasterSlot->fetchRow($query);
			if($object) {
				$TimeSlot = new Base_Timeslot();
				$slotsForDay = $TimeSlot->breakTimeslots($object->getStartTime(), $object->getEndTime(), $object->getSlotInterval());
				foreach($slotsForDay as $slot) {
					$timetoShow = date("g:i a", strtotime($slot));
		        	$slotsString .= "<input type='checkbox' name='displayCheck{$num}[]' checked='checked' value='{$slot}'>{$timetoShow}<br />";
				}
			}
		}

	    $return['slots'] = stripslashes($slotsString);
        $return['num'] = $num;
        $return['id'] = $id;
        echo json_encode($return);
        exit;
     }

     function getDeletedSlotsAction() {
        $id = $this->_getParam('id');
        $stime = $this->_getParam('stime');
        $etime = $this->_getParam('etime');
        $intval = $this->_getParam('intval');
        $num = $this->_getParam('num');
        $slots = "";
        $dbSlotArray = array();
        $MasterSlot = new Application_Model_MasterTimeslot();
        $object = $MasterSlot->find($id);
        if(!empty($object) && $object->getDisplaySlots()!=''){
            $dbSlotArray = explode(',', $object->getDisplaySlots());
        }

        $TimeSlot = new Base_Timeslot();
        $slotsArray = $TimeSlot->breakTimeslots($stime, $etime, $intval);

        $str = '';
        foreach($slotsArray as $st){
            $checked = "";
			
            if(!empty($dbSlotArray)){
                if(in_array($st, $dbSlotArray)) {
                	$checked = "";
	            }else{
	                $checked = "checked='checked'";
	            }
	        } else {
	                $checked = "checked='checked'";
	        }
            $str .= "<input type='checkbox' name='displayCheck{$num}[]' {$checked} value='{$st}'>{$st}<br />";
        }
        $return['slots'] = stripslashes($str);
        $return['num'] = $num;
        $return['id'] = $id;
        echo json_encode($return);
        exit;
     }

     function makeSlotsAction() {

        $stime = $this->_getParam('stime');
        $etime = $this->_getParam('etime');
        $intval = $this->_getParam('intval');
        $num = $this->_getParam('num');
        $slots = "";
        $TimeSlot = new Base_Timeslot();
		
        $slotsArray = $TimeSlot->breakTimeslots($stime, $etime, $intval);

        $str = '';
        foreach($slotsArray as $st){
            $checked = "";

            if(!empty($dbSlotArray)){
                if(in_array($st1, $dbSlotArray))$checked = "checked='checked'";
            }else{
                $checked = "checked='checked'";
            }
            $str .= "<input type='checkbox' name='displayCheck{$num}[]' {$checked} value='{$st}'>{$st}<br />";
        }
        $return['slots'] = stripslashes($str);

        $return['num'] = $num;
        echo json_encode($return);
        exit;
     }

     function saveDisplaySlotsAction() {

        $id = $this->_getParam('id');
        $slots = $this->_getParam('slots');
        $stime = $this->_getParam('stime');
        $etime = $this->_getParam('etime');
        $intval = $this->_getParam('intval');
        $MasterSlot = new Application_Model_MasterTimeslot();
        $object = $MasterSlot->find($id);
        if(!empty($object)){
            $slots = trim($slots, ',');
            $object->setStartTime($stime);
            $object->setEndTime($etime);
            $object->setSlotInterval($intval);
            $object->setDisplaySlots($slots);
            $object->save();
        }
        $return['id'] = $id;
        echo json_encode($return);
        exit;
     }

}

?>
