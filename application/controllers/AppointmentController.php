<?php

class AppointmentController extends Base_Controller_Action {


	public function preDispatch() {
        parent::preDispatch();
    }


    public function indexAction() {
		$this->_helper->layout->setLayout('doctor');

		$resceduleId = $this->_getParam('rescheduled');
		$this->view->rescheduled = $resceduleId;

        $drid = $this->_getParam('drid');
		
        //$date = $this->_getParam('date');
        //$time = $this->_getParam('time');
        $Doctor = new Application_Model_Doctor();
        $doctorObject = $Doctor->find($drid);
        $this->view->doctor = $doctorObject;
        $this->view->category = $Doctor->getDoctorCategoryList($drid);

        $error = "";
        $this->view->error = "";
        $Appointment = new Application_Model_Appointment();
        $appointTime = strftime("%I:%M %P", strtotime($time));
        $appObject = $Appointment->fetchRow("appointment_date='$date' AND appointment_time='$appointTime' AND doctor_id='{$drid}' AND deleted!=1");
       // $appTime = strtotime("$date $time");		
        if(!empty($appObject)){
            $error['err'] = 1;
            $error['msg'] = "<li>".$this->view->lang[402]."<li></li>".$this->view->lang[402]."</li><li><a href='javascript:history.back()'>".$this->view->lang[361]."</a></li>";
            $this->view->error = $error;            
            return;
        }
       /* if($appTime<time()) {
        	$error['err'] = 1;
            $error['msg'] = "<li> ".$this->view->lang[400]." </li><li>".$this->view->lang[401]."</li><li><a href='javascript:history.back()'>".$this->view->lang[361]."</a></li>";
         	$this->view->error = $error;            
         	return;
        }*/
        
		//enable or desable SMS plugin
		$settings = new Admin_Model_GlobalSettings();
		$this->view->smsPlugin = $settings->settingValue('sms_plugin');
		
		$hours = $settings->settingValue('hours');
	    if($hours) {
			$this->view->timeformat = "%I:%M %P";
	    } else {
	        $this->view->timeformat = "%H:%M";
	    }
        
		$DocCategory = new Application_Model_DoctorCategory();
        $catObject = $DocCategory->fetchAll("doctor_id='{$drid}'");
        $str = "";
        if($catObject){
            $array = array();
            foreach($catObject as $cat){
                $array[] = $cat->getCategoryId();
            }
            $str = implode(',', $array);
        }
        if($str=='')$catlist = "0";else $catlist = "$str";
       
		$this->view->reasonforvisit = $Doctor->getReasonForVisit($drid);
        $this->view->insurance_companies = $Doctor->getInsuranceCompany();
		
		$modeldoctor_insurance = new Application_Model_DoctorInsurance();
		$ArrDoctorInsurance=$modeldoctor_insurance->getDoctorinsurance("doctor_id={$drid}");
		$InsuranceCompany = new Application_Model_InsuranceCompany();
		foreach($ArrDoctorInsurance as $key=>$value)
		{
			$insuranceobject = $InsuranceCompany->find($value);
			if($insuranceobject)$insurancedata[$insuranceobject->getId()]=$insuranceobject->getCompany();
		}
	//	echo '<pre>';print_r($insurancedata);die;
		$this->view->insurancedataArr = $insurancedata;
		
		
		$reasonNamespace = new Zend_Session_Namespace('company');
        
        $User = new Application_Model_User();
        $this->view->months = $this->listAllMonths();
        $this->view->days = $User->listAllDates();
        $this->view->years = $User->listAllYear();

        $this->view->drid = $drid;
        /*$this->view->date = $date;
        $this->view->time = $time;*/

      
    }// end function
	
	/*insurance plan ajax*/
	public function iplanajaxAction()
	{
		 $company_id=$this->_getParam('company_id');
		 $insurance_plan = new Application_Model_InsurancePlan();
		 $insurance_plan1 = $insurance_plan->fetchAll("insurance_company_id='{$company_id}'");
		 //print_r($insurance_plan);die;
		 echo '<select  name="insurance_plan" id="insurance_plan" tabindex="-1" style="display: none;" class="js-example-basic-single js-states form-control">';
		 
		 echo '<optgroup label="Plan">';
		 echo '<option value="0">Choose your plan</option>';
		 foreach($insurance_plan1 as $plan)
		 {
		  echo '<option value="'.$plan->id.'">'.$plan->plan.'</option>';
		 }
		 echo '</optgroup>';
		 echo '</select>';
		 echo die;
		// $this->view->insurance_plan1=$insurance_plan1;	
	
	}

    public function checkLoginAction() {

        $return = array();
        $usersNs = new Zend_Session_Namespace("members");
        if($usersNs->userId <> '' && $usersNs->userType=='patient'){
            die("1");
        }else{
            die("0");
        }
    }// end function

    public function getPatientDetailsAction() {

        $return = array();
        $usersNs = new Zend_Session_Namespace("members");
        $return['err'] = '0';
        if($usersNs->userId <> '' && $usersNs->userType=='patient'){
            $Patient = new Application_Model_Patient();
            $object = $Patient->fetchRow("user_id='{$usersNs->userId}'");

            $User = new Application_Model_User();
            $user = $User->find($usersNs->userId);
            if(!empty($object)){
                $return['name'] = $object->getName();
                $return['zipcode'] = $object->getZipcode();
                $return['age'] = $object->getAge();
                $return['month'] = $object->getMonthDob();
                $return['day'] = $object->getDateDob();
                $return['year'] = $object->getYearDob();
                $return['gender'] = $object->getGender();
                $return['phone'] = $object->getPhone();

                $return['email'] = $usersNs->userEmail;
                $return['lastname'] = $user->getLastName();
            }
        }else{
            $return['email'] = '';
        }
        echo Zend_Json::encode($return);
        exit;

    }// end function
	
    public function doLoginAction(){
        $Auth = new Base_Auth_Auth();
        $Auth->doLogout();
        $return = array('err'=>'0');
        $loginStatusEmail = true;
        $loginStatusUsername = true;
        $params['email'] = $this->_getParam('username');
        $params['password'] = $this->_getParam('password');
        $params['rememberMe'] = $this->_getParam('rememberMe');

        $loginStatusEmail = $Auth->doLogin($params, 'email');
        if ($loginStatusEmail == false) {
            $loginStatusUsername = $Auth->doLogin($params, 'username');
        }

        if ($loginStatusEmail == false && $loginStatusUsername == false) {
            $return['err'] = 1;
            $return['msg'] = "<li>".$this->view->lang[325]."</li>";
            echo Zend_Json::encode($return);
            exit();
        } else {
            if ($params['rememberMe'] == 1) {
                $Auth->remeberMe(true, $params);
            }
            $this->getPatientDetailsAction();
        }
        
    }

	
    public function createAppointmentAction(){
       
		$return = array();

		$return['err'] = 0;
		$drid = $this->_getParam('drid');
		$name = "";
		$lastname = "";
		//$name = $this->_getParam('name');
		//$lastname = $this->_getParam('lastname');
		//$newemail = $this->_getParam('newemail');
		//$password = $this->_getParam('newpassword');
		$firstVisit = $this->_getParam('first_visit');
		$status = $this->_getParam('status');
		$appointTime = $this->_getParam('appointment_time');		
		$appointmentDate = $this->_getParam('appointment_date');
		$needs = $this->_getParam('needs');
		$reason = $this->_getParam('reason');
		if($reason == "") {
			$reason = "Unspecified";
		}
		$insuranceCompany = $this->_getParam('insurance_company');
		$insurancePlan = $this->_getParam('insurance_plan');
		$onbehalf = $this->_getParam('onbehalf');
		$pname = $this->_getParam('pname');

		$resceduleId = $this->_getParam('resceduleid');

		$appointmentNs = new Zend_Session_Namespace("appointment");
		$appointmentNs->appointmentId = 0;
		$randTime = strtotime($appointmentDate." ".$appointTime);
		$timeToStore = date("H:i", $randTime);
		if($randTime<time()){
		    $return['err'] = 1;
		    $return['msg'] = "<li> ".$this->view->lang[400]." </li><li>".$this->view->lang[401]."</li>";
		    echo Zend_Json::encode($return);
		    exit();
		}
		$Appointment = new Application_Model_Appointment();
		$appObject = $Appointment->fetchRow("appointment_date='$appointmentDate' AND appointment_time='$timeToStore' AND doctor_id='{$drid}' AND deleted!=1");
		if(!empty($appObject)){
		    $return['err'] = 1;
		    $return['msg'] = "<li>".$this->view->lang[402]."<li></li>".$this->view->lang[402]."</li>";
		    echo Zend_Json::encode($return);
		    exit();
		}

        $userId = 0;
        $usersNs = new Zend_Session_Namespace("members");
      	
        if(isset($usersNs->userType) && $usersNs->userType=='patient'){
            $userId = $usersNs->userId;
        } else { //not a patient user
        	$return['err'] = 1;
            $return['msg'] = "<li>Please check your login credentials.</li>";
            echo Zend_Json::encode($return);
            exit();
        }
		$User = new Application_Model_User();
		$user = $User->find($userId);

		$Patient = new Application_Model_Patient();
		$patient = $Patient->fetchRow("user_id = ".$userId);

		if($onbehalf) {
			$name = $pname;
			$lastname = "";
		} else {
			$name = $user->getFirstName();
			$lastname = $user->getLastName();
		}

		if($return['err'] >0){
			echo Zend_Json::encode($return);
			exit();
		}

		/*------------------------Start Insert Appointment ------------------------------*/
		
		
		$Appointment->setUserId($userId);
		$Appointment->setFname($name);
		$Appointment->setLname($lastname);
		$Appointment->setZipcode($patient->getZipcode());
		$Appointment->setPhone($patient->getPhone());
		$Appointment->setEmail($user->getEmail());
		$Appointment->setAge($patient->getAge());
		$Appointment->setGender($patient->getGender());
		$Appointment->setFirstVisit($firstVisit);
        $Appointment->setPatientStatus($status);
        $Appointment->setAppointmentDate($appointmentDate);	
        $Appointment->setAppointmentTime($timeToStore);
        $Appointment->setBookingDate(time());
        $Appointment->setDoctorId($drid);
        $Appointment->setReasonForVisit($reason);
        $Appointment->setNeeds($reason);
        $Appointment->setInsurance($insuranceCompany);
        $Appointment->setPlan($insurancePlan);
        $Appointment->setMonthDob($patient->getMonthDob());
        $Appointment->setDateDob($patient->getDateDob());
        $Appointment->setYearDob($patient->getYearDob());
        $Appointment->setAppointmentType('1');
        $Appointment->setCancelledBy('0');
        $Appointment->setOnbehalf($onbehalf);
        $Appointment->setRescheduled(0);
        $appointmentId = $Appointment->save();
        $Appointment->setId($appointmentId);
        
        if($resceduleId) {
        	$Appointment2 = new Application_Model_Appointment();
        	$rescheduled = $Appointment2->find($resceduleId);
        	if($rescheduled) {
        		$rescheduled->setApprove(2);
        		$rescheduled->setRescheduled(1);
        	}
        }
        //$appointmentId = 1;
        /*------------------------End Insert Appointment ------------------------------*/
        
        if(!$appointmentId){
            $return['err'] = 1;
            $return['msg'] = "<li>".$this->view->lang[403]."</li>";
            echo Zend_Json::encode($return);
            exit();
        }
        
        $appointmentNs->appointmentId = $appointmentId;// update appointment session id

		/*------------------------ Add Patient to doctor's patients ------------------------------*/
		$DoctorPatient = new Application_Model_DoctorPatient();
		$docpat = $DoctorPatient->fetchRow("patient_id = ".$patient->getId()." AND doctor_id =".$drid);
		if(!$docpat) { //doesn't exist, add the patient
			$DoctorPatient->setDoctorId($drid);
			$DoctorPatient->setPatientId($patient->getId());
			$DoctorPatient->save();
		}

        
        /*------------------------Start Appointment Email ------------------------------*/
        $options = array();
        $options['email'] = $email;
         
        $options['name'] = $name;
        $options['lastname'] = $lastname;
        $options['date'] = $appointmentDate;
        $options['time'] = $appointTime;
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->find($drid);
       
        $options['doctor'] = $docObject->getFname();
        $options['office'] = $docObject->getCompany();
        $options['address1'] = $docObject->getStreet();
        $options['address2'] = $docObject->getCity().', '.$docObject->getState().' '.$docObject->getZipcode();
        $options['phone'] = $docObject->getActualPhone();
        $options['membership_level'] = $docObject->getMemberShipLevel();
        $options['PTPhone'] = $phone;


		/*
		$AdminMail = new Base_Mail('UTF-8');
		$AdminMail->sendAdministratorAppointmentBookinhotmail($Appointment); // email to site administrator
		*/

		$Mail = new Base_Mail('UTF-8');
		if($status=='n'){
			//send message to the new patient
			$sent = $Mail->sendNewPatientAppointmentMail($Appointment);
		} else {
			//send message to existing patient
			$sent = $Mail->sendPatientAppointmentMail($Appointment);
		}
		//error_log("send message ".$sent);
		//send message to the doctor
		$DocMail = new Base_Mail('UTF-8');
       	$DocMail->sendDoctorAppointmentMail($Appointment);
       
        
         /*------------------------End Appointment Email ------------------------------*/

        $return['app_id'] = $appointmentId;
		$return['options'] = $options;

		$return['name'] = $name." ".$lastname;
		$return['reasonData'] = $reason;	
		$return['needsData'] = $reason;	
		$return['insuranceCompanyData'] = $insuranceCompany;	
		
        if($userId){
            $return['msg'] = $this->view->lang[405];
        }else{
            $return['msg'] = $this->view->lang[406];
        }
        echo Zend_Json::encode($return);
        exit();
    }


public function showIcalAction(){
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
        //Fetch Profile Data
        $Appointment = new Application_Model_Appointment();
        $appointment = $Appointment->find($id);

        $Doctor = new Application_Model_Doctor();
        $doctor = $Doctor->find($appointment->getDoctorId());

        $ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN"."\r\n";
        if (!empty($appointment)) {
        	$date = $appointment->getAppointmentDate();
            $start_time = strtotime($date.' '.$appointment->getAppointmentTime());
            $interval = $this->getInterval($doctor->getId(), $date);
            $end_time = strtotime("+$interval minutes", $start_time);
            
            $dtstart = gmdate('Ymd', $start_time).'T'. gmdate('His', $start_time) . "Z"; // converts to UTC time
            $dtend = gmdate('Ymd', $end_time).'T'. gmdate('His', $end_time) . "Z"; // converts to UTC time
            $summary = 'Appointment with '.$doctor->getFullName();
            $ical .= "BEGIN:VEVENT
UID:" . md5($appointment->getId()) . "@doctors.com
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:" . $dtstart . "
DTEND:" . $dtend . "
SUMMARY:" . $summary . "
END:VEVENT"."\r\n";
        }

        $ical .= "END:VCALENDAR";
        $this->view->ical = $ical;
        
    }

    public function getInterval($drid, $date){
		$timeslot = new Base_Timeslot();
		$weekNumber = $timeslot->fetchSlotWeek($date);
		$slotDay = strtoupper(date('D', strtotime($date)));
		$MasterSlot = new Application_Model_MasterTimeslot();
        $object = $MasterSlot->fetchRow("doctor_id='$drid' AND week_number='{$weekNumber}' AND is_checked='1' AND slot_day='{$slotDay}'", "id ASC");
		if($object){
			return $object->getSlotInterval();
		}
		else{
			$object = $MasterSlot->fetchRow("doctor_id='-1' AND week_number='{$weekNumber}' AND is_checked='1' AND slot_day='{$slotDay}'", "id ASC");
			return $object->getSlotInterval();
		}
	}
    
public function checkAppointmentStatusAction(){
    $appointmentNs = new Zend_Session_Namespace("appointment");
    $return = array();
    $userId = 0;
    if($appointmentNs->appointmentId){
        $drid = $this->_getParam('drid');
        $email = $this->_getParam('email');
        $Appointment = new Application_Model_Appointment();
        $appObject = $Appointment->fetchRow("id={$appointmentNs->appointmentId} AND doctor_id={$drid} AND email ='{$email}'");
        if(!empty($appObject)){ // if appointment posted
            $usersNs = new Zend_Session_Namespace("members");
            $status = $this->_getParam('status');
            if($status=='e' && (isset($usersNs->userType) && $usersNs->userType=='patient')){
                $userId = $usersNs->userId;
            }elseif($status=='n'){
                $return['email'] = $email;
            }
            $return['app_id'] = $appointmentNs->appointmentId;
            $return['err'] = 0;
            if($userId){
                $return['msg'] = $this->view->lang[405];
            }else{
                $return['msg'] = $this->view->lang[406];
            }
        }else{
            $return['err'] = 1;
            $return['msg'] = "<li>".$this->view->lang[403]."</li>";
        }
    }else{
       $return['err'] = 1;
       $return['msg'] = $this->view->lang[407];
    }
    echo Zend_Json::encode($return);
    exit();
}

public function sendtodoctorAction($ids) {
        $page = $this->_getParam('page');
        $doctor_name = $this->_getParam("doctor_name");
        $gender = $this->_getParam("gender");
        $status = $this->_getParam("status");
        $approved = $this->_getParam("approved");
        $type = $this->_getParam("type");
        $idArray = explode(',', $ids);
        $model = new Application_Model_Appointment();
        $Doctor = new Application_Model_Doctor();
        $User = new Application_Model_User();

        $ReasonForVisite = new Application_Model_ReasonForVisit();

        foreach ($idArray as $id) {
            $object = $model->find($id);

            if($object->getApprove() != 1 && $object->getApprove() != 2){

            $objDoctor = $Doctor->find($object->getDoctorId());
            $objUser = $User->find($objDoctor->getUserId());

            $Doctor_name = $objDoctor->getFname();

            $objReasonForVisite = $ReasonForVisite->find($object->getReasonForVisit());
            $options['doctor_email'] = $objUser->getEmail();
            if($objReasonForVisite){
                $options['reasonforvisit'] = $objReasonForVisite->getReason();
            }else{			
                $options['reasonforvisit'] = $object->getNeeds();
            }
            $options ['office'] = $objDoctor->getCompany();
            $options['doctor_name'] =$options ['doctor']= $Doctor_name;
            $options['pname'] = $objUser->getFirstName()." ".$objUser->getLastName();
            $options['address1'] = $objDoctor->getStreet()."<br>".$objDoctor->getCity().", ".$objDoctor->getCountry()." ". $objDoctor->getZipcode();
            $options['address2'] = "";
            $options ['name'] = $object->getFname();
            $options ['lastname'] = $object->getLname();
			$options ['pname']= $object->getFname().' '.$object->getLname();
            $options ['email'] = $objUser->getEmail();
            $options['phone'] = $object->getPhone();
            $options ['time'] = $object->getAppointmentTime();
            $options ['date'] = $object->getAppointmentDate();
            $options ['PTPhone'] = $object->getPhone();
            $options['email'] = $object->getEmail();
            $options['age'] = $object->getAge();
            $options['dob'] = date('d-M-Y', strtotime("{$object->getDateDob()}-{$object->getMonthDob()}-{$object->getYearDob()}"));
            $options['zipcode'] = $object->getZipcode();
            $options['day'] = date('l',strtotime($object->getAppointmentDate()));
            $options['date'] = $object->getAppointmentDate();
            $options['time'] = $object->getAppointmentTime();
            $options['gender'] = $model->getFullGender("id={$id}");
            $options['patient_status'] = $model->getFullPatientStatus("id={$id}");
 
            $object->setApprove('3');
            $mail_counter=$object->getMailCounterForDoctor();
			$insurance_name="";
			$plan_name="";
			$insuranceObject = new Application_Model_InsuranceCompany();
			$insurance_id = $object->getInsurance();
			$plan_id = $object->getPlan();
			if($insurance_id>0)
			{
				$objInsurance =  $insuranceObject->find($insurance_id);
				if($objInsurance)
					$insurance_name = $objInsurance->getCompany();
			}
 
			$options['insurance'] = $insurance_name;

            $mail_counter++;
			$Mail = new Base_Mail('UTF-8');
           
			$Mail->sendDoctorAppointmentBookinhotmail($object);
           

            $object->save();
        }
	}
}

    public function sendNewAppointmentEmail($id){
        $Appointment = new Application_Model_Appointment();
        $Doctor = new Application_Model_Doctor();
        $User = new Application_Model_User();
        $ReasonForVisit = new Application_Model_ReasonForVisit();
        
        $object = $Appointment->find($id);

        if($object->getApprove() != 1 && $object->getApprove() != 2){

            $objDoctor = $Doctor->find($object->getDoctorId());
            $objUser   = $User->find($objDoctor->getUserId());
            $Doctor_name = $objDoctor->getFname();

            $objReasonForVisit = $ReasonForVisit->find($object->getReasonForVisit());
            $options['doctor_email'] = $objUser->getEmail();
            if($objReasonForVisit){
                $options['reasonforvisit'] = $objReasonForVisit->getReason();
            }else{
                $options['reasonforvisit'] = $object->getNeeds();
            }
            $options ['office'] = $objDoctor->getCompany();
            $options['doctor_name'] =$options ['doctor']= $Doctor_name;
            $options['pname'] = $objUser->getFirstName()." ".$objUser->getLastName();
            $options['address1'] = $objDoctor->getStreet()."<br>".$objDoctor->getCity().", ".$objDoctor->getCountry()." ". $objDoctor->getZipcode();
            $options['address2'] = "";
            $options ['name'] = $options ['pname']= $object->getFname().$object->getLname();
            $options ['email'] = $objUser->getEmail();
            $options['phone'] = $object->getPhone();
            $options ['time'] = $object->getAppointmentTime();
            $options ['date'] = $object->getAppointmentDate();
            $options ['PTPhone'] = $object->getPhone();
            $options['email'] = $object->getEmail();
            $options['age'] = $object->getAge();
            $options['dob'] = date('d-M-Y', strtotime("{$object->getDateDob()}-{$object->getMonthDob()}-{$object->getYearDob()}"));
            $options['zipcode'] = $object->getZipcode();
            $options['day'] = date('l',strtotime($object->getAppointmentDate()));
            $options['date'] = $object->getAppointmentDate();
            $options['time'] = $object->getAppointmentTime();
            $options['gender'] = $Appointment->getFullGender("id={$id}");
            $options['patient_status'] = $Appointment->getFullPatientStatus("id={$id}");

            $object->setApprove('3');
            $mail_counter=$object->getMailCounterForDoctor();
            $insurance_name="";
            $plan_name="";
            $insuranceObject = new Application_Model_InsuranceCompany();
            $insurance_id = $object->getInsurance();
            $plan_id = $object->getPlan();
            if($insurance_id>0)
            {
                $objInsurance =  $insuranceObject->find($insurance_id);
                if($objInsurance)
                 $insurance_name = $objInsurance->getCompany();
            }

            if($plan_id>0)
            {
                $ObjectPlan = new Application_Model_InsurancePlan();
                $objPlan = $ObjectPlan->find($plan_id);
                if(!empty($objPlan))
                {
                  $plan_name = $objPlan->getPlan();
                }
            }
            $options['insurance'] = $insurance_name;
            $options['plan']= $plan_name;

            $mail_counter++;
            $Mail = new Base_Mail('UTF-8');
            $Mail->sendDoctorAppointmentBookinhotmail($options);

            $object->save();
        }

    }
	
	public function checknewmailAction(){ 
		$email = $this->_getParam('newemail');
		$return['err'] = 0;
		
		$User = new Application_Model_User();
		if (true === $User->isExist("email='{$email}'")) {
			$return['err'] = 1;
			$return['msg'] = "<li>".$this->view->lang[391]."</li>";
			echo Zend_Json::encode($return);
			exit();
		}
        echo Zend_Json::encode($return);
        exit();
	}
	
	
	public function registerPatientAction(){        
		$email = $this->_getParam('newemail');
		$password = $this->_getParam('newpassword');
		$firstname = $this->_getParam('newname');
		$lastname = $this->_getParam('newlastname');

		$return['err'] = 0;

		$User = new Application_Model_User();
		if (true === $User->isExist("email='{$email}'")) {
			$return['err'] = 1;
			$return['msg'] = "<li>".$this->view->lang[391]."</li>";
		} else {
			$User->setEmail($email);
			$User->setUsername($email);
			$User->setFirstName($firstname);
			$User->setLastName($lastname);
			$User->setUserLevelId(3); // for patient
			$User->setSendEmail(1);
			$User->setLastVisitDate(time());
			$User->setStatus('active');
			$User->setPassword(md5($password));
			$userId = $User->save();

			if(!$userId){
				$return['err'] = 1;
				$return['msg'] = "<li>".$this->view->lang[403]."</li>";
			}else{
				$Patient = new Application_Model_Patient();
				$Patient->setUserId($userId);
				$Patient->setName($firstname." ".$lastname);
				$Patient->setZipcode("");
				$Patient->setAge("");
				$Patient->setGender("");
				$Patient->setPhone("");
				$Patient->setInsuranceCompanyId("");
				$Patient->setMonthDob("");
				$Patient->setDateDob("");
				$Patient->setYearDob("");
				$Patient->setLastUpdated(time());
				$Patient->setFirstLogin(0);
				$patientId = $Patient->save();
				if(!$patientId){
					$return['err'] = 1;
					$return['msg'] = "<li>".$this->view->lang[404]."</li>";
					error_log("Problem with registration from Appointment. Code: RegErr2 ".$Patient);
				}
			}
		}

		if($return['err'] == 1){
			echo Zend_Json::encode($return);
			exit();
		}
		$Auth = new Base_Auth_Auth();
		$Auth->doLogout();
		$loginStatusEmail = false;
		$params['email'] = $email;
		$params['password'] = $password;
		$loginStatusEmail = $Auth->doLogin($params, 'email');
		
		if ($loginStatusEmail == false && $loginStatusUsername == false) {
			$return['err'] = 1;
			$return['msg'] = "<li>".$this->view->lang[325]."</li>";
			echo Zend_Json::encode($return);
			exit();
		} else {
			if ($params['rememberMe'] == 1) {
			    $Auth->remeberMe(true, $params);
			}
			$this->getPatientDetailsAction();
		}
		$options = array();
		$options['email'] = $email;
		$options['password'] = $password;
		$options['first_name'] = $User->getFirstname();
		$options['last_name'] = $User->getLastname();

		$return['msg'] .= $this->view->lang[408];
		echo Zend_Json::encode($return);
		exit();
	}

    public function checkRegistrationStatusAction(){
        $appointmentNs = new Zend_Session_Namespace("appointment");
        $userNs = new Zend_Session_Namespace("members");
        $return = array('err'=>0);
        if($userNs->userId){
            $return['app_id'] = $appointmentNs->appointmentId;
            $return['msg'] = $this->view->lang[408];
        }else{
            $return['err'] = 1;
            $return['msg'] = '<li>'.$this->view->lang[403].'</li>';
        }
        echo Zend_Json::encode($return);
        exit();
    }
    public function thankyouAction() {
		$this->_helper->layout->setLayout('patient_wide');
        $appid = $this->_getParam('appid');
        $usersNs = new Zend_Session_Namespace("members");
        
        $Appointment = new Application_Model_Appointment();
        $object = $Appointment->fetchRow("id='{$appid}'");
        if(!empty($object)){
            $Doctor = new Application_Model_Doctor();
            $docObject = $Doctor->find($object->getDoctorId());
            $Category = new Application_Model_Category();
            $catObject = $Category->find($docObject->getCategoryId());

            $reason = '';
            if($object->getReasonForVisit() > 0){
                $ReasonForVisit = new Application_Model_ReasonForVisit();
                $reasonObject = $ReasonForVisit->find($object->getReasonForVisit());
                $reason = $reasonObject->getReason();
            }else{
                $reason = $object->getNeeds();
            }

            $insurance_name = "";
            if($object->getInsurance() > 0){
                $ObjInsurance = new Application_Model_InsuranceCompany();
                $insuranceObject = $ObjInsurance->find($object->getInsurance());
               if(is_object($insuranceObject))
                $insurance_name = $insuranceObject->getCompany();
            }else{
                $insurance_name = "";
            }

            $profileImage = "/images/doctor_image/" . $docObject->getCompanylogo();
            if (!file_exists(getcwd() . $profileImage) || $docObject->getCompanylogo()=='')$profileImage = "/images/doctor_image/png.png";
            $this->view->profileImage = $profileImage;

            $this->view->doctor  = $docObject;
            $this->view->catObject  = $catObject;
            $this->view->reason  = $reason;
            $this->view->insurance_name = $insurance_name;
        }
        $this->view->object  = $object;
        

    }// end function
	
	public function smsphoneAction(){
		$phone = trim($this->_getParam('smsphone'));
		if(strlen($phone) != 10)
		{
			$return = array('err'=>1, 'msg' => '<li>'.$this->view->lang[409].'</li>');
			echo Zend_Json::encode($return);
			exit();
		}
		if(!is_numeric($phone))
		{
			$return = array('err'=>1, 'msg' => '<li>'.$this->view->lang[410].'</li>');
			echo Zend_Json::encode($return);
			exit();
		}
		$db = Zend_Registry::get("db");
		$usersNs = new Zend_Session_Namespace("members");
		$memberID = $usersNs->userId;
		$sql = 'SELECT id FROM sms_table where (phone=? or userid =?) and DATEDIFF(DATE(time_sent),  CURDATE())=0 and validated=1';
	
		$result = $db->fetchAll($sql, array(addslashes($phone), $memberID));
		
		$settings = new Admin_Model_GlobalSettings();
		$maxAppoints = $settings->settingValue('max_appoints_per_day');
	            
        if(($maxAppoints !=0) && (count($result) > $maxAppoints)) {
            $return = array('err'=>1, 'msg' => '<li>'.$this->view->lang[411].'</li>');
            echo Zend_Json::encode($return);  
            exit();
        }

		$rand = 123456;
		//uncomment for random code generation
		//$rand = rand(100000, 999999);
		

		//error_log("sms code: ".$rand); 
		$username = 'username';
		$password = 'password';
		$from = 'From';
		$to = '01'.$phone;
		$message = $this->view->lang[412].$rand;
		//TODO: to replace with YOUR SMS service, please uncomment the following lines once done
		//$url = "https://www.yoursmsgateway.com/api/http/send.php?username=$username&password=$password&from=$from&message=$message&to=$to"; //sms sending code!
		//$response = file_get_contents($url);
		$return = array('err'=>0);
		$params = array('phone' => addslashes($phone), 'time_sent' => date('Y-m-d H:i:s'), 'validation_code' => $rand, 'validated' => 0, 'userid' => $memberID);
		$db->insert('sms_table', $params);
		echo Zend_Json::encode($return);
        exit();
	}
	
	public function smscodeAction(){
		$usersNs = new Zend_Session_Namespace("members");
		$memberID = $usersNs->userId;
		$phone = $this->_getParam('smsphone');
		$code = $this->_getParam('smscode');
		$sql = "SELECT validation_code FROM sms_table where (phone='".addslashes($phone)."' or userid ='".$memberID."') and DATEDIFF(DATE(time_sent),  CURDATE())=0 and validated=0";
		
		$db = Zend_Registry::get("db");
		$stmt = $db->query($sql);
		$result = $stmt->fetchAll();
		$vcode = $result[0]->validation_code;
		if($code == $result[0]->validation_code) {
			$return = array('err'=>0);
			$sql = 'UPDATE sms_table set validated=1 where phone=? and DATEDIFF(DATE(time_sent),  CURDATE())=0 and validated=0';
			$stmt = new Zend_Db_Statement_Pdo($db, $sql);
			$stmt->execute(array(addslashes($phone)));
		}
		else {
			$return = array('err'=>1, 'msg' => '<li>'.$this->view->lang[413].'</li>');
		}
		echo Zend_Json::encode($return);
        exit();
	}

	public function approveAppointmentAction() { 
		$this->_helper->layout->setLayout('frontend');
		$appid = $this->_getParam('appid');
		$drid = $this->_getParam('drid');
		
		$this->view->msg = "You have approved your appointment!";

		$Appointment = new Application_Model_Appointment();
		$appointment = $Appointment->find($appid);
		
		$this->view->appointment = $appointment;

		if($appointment->getDoctorId() == $drid) {
			$appointment->setApprove(1);
			$appointment->save();
			$this->view->msg = "Appointment approved.";
			$Mail = new Base_Mail('UTF-8');
			$Mail->sendPatientAppointmentApprovedMail($appointment);
		}

	}

	public function disapproveAppointmentAction() { 
		$this->_helper->layout->setLayout('frontend');
		$appid = $this->_getParam('appid');
		$drid = $this->_getParam('drid');
		
		$this->view->msg = "You have disapproved your appointment!";

		$Appointment = new Application_Model_Appointment();
		$appointment = $Appointment->find($appid);

		$this->view->appointment = $appointment;
		
		if($appointment->getDoctorId() == $drid) {
			$appointment->setApprove(2);			
			$appointment->setCancelledBy(3);
			$appointment->save();
			$this->view->msg = "Appointment was disapproved.";
			$Mail = new Base_Mail('UTF-8');
			$Mail->sendCancelAppointmentPatientMailEnquiry($appointment);
		}

	}

    public function listAllMonths()
    {
        $arMonths = array(
            ''=>$this->view->lang[900],
            '1'=>$this->view->lang[901],
            '2'=>$this->view->lang[902],
            '3'=>$this->view->lang[903],
            '4'=>$this->view->lang[904],
            '5'=>$this->view->lang[905],
            '6'=>$this->view->lang[906],
            '7'=>$this->view->lang[907],
            '8'=>$this->view->lang[908],
            '9'=>$this->view->lang[909],
            '10'=>$this->view->lang[910],
            '11'=>$this->view->lang[911],
            '12'=>$this->view->lang[912]
        );
      return $arMonths;
    }

}// end class