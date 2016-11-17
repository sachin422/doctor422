<?php
/**
 * IndexController
 * 
 * @author
 * @version 
 */
class User_IndexController extends Base_Controller_Action {
    /**
     * The default action - show the home page
     */
    public function preDispatch() {
        parent::preDispatch();
        $usersNs = new Zend_Session_Namespace("members");
        $userid = $usersNs->userId;
		date_default_timezone_set('America/Los_Angeles');
        if ($usersNs->userType == 'doctor') {
            $this->_helper->layout->setLayout('doctorpanel');
            //add menu counters
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
        } else {
            $this->_helper->layout->setLayout('patientpanel');
        }        
        $notes= array();
        $Notification = new Application_Model_Notification();
        $notifications = $Notification->fetchAll("userid = ".$userid." AND active='1'");
        if($notifications) {
        	$i = 0;
        	foreach($notifications as $notification) {
        		$notes[$i]['id'] = $notification->getId();
        		$i++;
        	}        	
        }
        $this->view->notifications = $notes;
    }
    public function indexAction() {
        $usersNs = new Zend_Session_Namespace("members");
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		$hours = $settings->settingValue('hours');
		if($hours) {
			$this->view->timeformat = "%I:%M %P";
		} else {
			$this->view->timeformat = "%H:%M";
		}

		
        if (isset($usersNs->userType) && $usersNs->userType == 'doctor') {
            $this->_forward('doctor-dashboard', 'index', 'user');
        } elseif (isset($usersNs->userType) && $usersNs->userType = 'patient') {
            $this->_forward('patient-dashboard', 'index', 'user');
        } else {
            $this->_helper->redirector('index', 'index', "default");
            exit();
        }
        $this->_helper->viewRenderer->setNoRender(false);
    }
	
    public function cancelAction() {
        $options = array();
        $id = $this->_getParam('id');
		
        $Appointment = new Application_Model_Appointment();
        $appObject = $Appointment->fetchRow("id='{$id}'");
		
        $Doctor = new Application_Model_Doctor();
        $docobj = $Doctor->fetchRow("id='{$appObject->getDoctorId()}'");
        $User = new Application_Model_User();
        $userobj = $User->find($docobj->getUserId());
        /* -----------cancel Appointent Patient/Doctor Email ------------ */
        $options['ptname'] = $appObject->getFname()." ".$appObject->getLname();
        $options['dname'] = $docobj->getFname();
        $options['drid'] = $docobj->getId();
        $the_date = $appObject->getAppointmentDate();
        $the_date = explode('-', $the_date);
		$new_date = $the_date[2] . '/' . $the_date[1] . '/' . $the_date[0];
		$options['datetime'] = $new_date . " " . $appObject->getAppointmentTime();
        $options['daddress'] = $docobj->getStreet() . "<br>" . $docobj->getCity() . ", " . $docobj->getCountry() . " " . $docobj->getZipcode();
        $options['site_url'] = "http://" . $_SERVER['HTTP_HOST'];
        $options['pemail'] = $appObject->getEmail();
        $options['demail'] = $userobj->getEmail();
        $options['pphone'] = $appObject->getPhone();
        $options['pzip'] = $appObject->getZipcode();
        $options['page'] = $appObject->getAge();
        if ($appObject->getGender() == "m") {
            $options['pgender'] = $this->view->lang[117];
        } else {
            $options['pgender'] = $this->view->lang[118];
        }
        if ($appObject->getPatientStatus() == "e") {
			$options['pStatus'] = $this->view->lang[930];
		} else {
			$options['pStatus'] = $this->view->lang[931];
		}
		$reason_id = $appObject->getReasonForVisit();
		
        if($reason_id>0) {
			$Reason = new Application_Model_ReasonForVisit();
			$resobj = $Reason->fetchRow("id='{$appObject->getReasonForVisit()}'");
			$options['reason_for_visit'] = $resobj->getReason();
		} else {
			$options['reason_for_visit'] = $appObject->getNeeds();
		}
        /* -----------send Appointent patient/Doctor Email ------------ */
        $approval = $appObject->getApprove();
        $appObject->setApprove(2);
        $appObject->setCancelledBy(3); // 3 for patient cancelled
        $appObject->save();
        $Mail_New = new Base_Mail('UTF-8');
        $Mail_New1 = new Base_Mail('UTF-8');
        //$Mail_New2 = new Base_Mail('UTF-8');
        if ($approval != 1 && $approval != 3) {
            $Mail_New->sendCancelAppointmentPatientMailEnquiry($appObject);
           // $Mail_New2->sendCancelAppointmentAdminMailEnquiry($options);
        } else {
            $Mail_New->sendCancelAppointmentPatientMailEnquiry($appObject);
            $Mail_New1->sendCancelAppointmentDoctorMailEnquiry($appObject);
           // $Mail_New2->sendCancelAppointmentAdminMailEnquiry($options);
        }
        $mag = base64_encode("Appointment cancelled");
        $this->_helper->redirector('index', 'index', "user", Array('msg' => $msg));
    }
	
	/* Show Patient Appointments*/
	
	public function patientAppointmentAction() {
	
		$this->view->headTitle('Patient Appointments');
        $usersNs = new zend_Session_Namespace("members");
        $Patient = new Application_Model_Patient();
        $docPatient = $Patient->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();

		$upcomingWhere = "deleted!=1 AND user_id={$usersNs->userId} AND DATEDIFF(NOW(),appointment_date)<=0 AND approve!=2";
        $pastWhere = "deleted!=1 AND user_id={$usersNs->userId} AND DATEDIFF(NOW(),appointment_date)>0 AND approve!=2";		
		$cancellWhere = "deleted!=1 AND user_id={$usersNs->userId} AND approve=2";
        
        $this->view->upcomingObject = $Appointment->fetchAll($upcomingWhere, "appointment_date ASC");
        $this->view->pastObject = $Appointment->fetchAll($pastWhere, "appointment_date DESC");
		$this->view->cancelObjects = $Appointment->fetchAll($cancellWhere, "appointment_date DESC");

		$this->view->Patient = $docPatient;
		
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		
    }

    public function doctorAppointmentAction() {
		//echo date('Y-m-d');die;
		//date_default_timezone_set('America/Los_Angeles');
		//echo date('Y-m-d H:i:s');die;
		$this->view->headTitle('Doctor Appointments');
        $usersNs = new zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();
        $id = $this->_getParam('id');
       
		if($id==''){
			$this->_helper->redirector('doctor-appointment', 'index', "user", Array('id' => 'upcoming'));
		}
		
		$upcomingWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND DATEDIFF(NOW(),appointment_date)<=0 AND approve!=2";
        $pastWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND DATEDIFF(NOW(),appointment_date)>0 AND approve!=2";	$cancellWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND approve=2";
        
		if($id=='upcoming'){
        	$this->view->color='diff1';
	    	$this->view->upcomingObject = $Appointment->fetchAll($upcomingWhere, "ADDTIME(appointment_date, appointment_time )  asc");
	    }else if($id=='past'){
	      $this->view->color='diff2';
          $this->view->pastObject = $Appointment->fetchAll($pastWhere, "ADDTIME(appointment_date, appointment_time ) DESC");
        }else if($id=='cancell'){
			$this->view->color='diff3';
			$this->view->cancelObjects = $Appointment->fetchAll($cancellWhere, "ADDTIME(appointment_date, appointment_time ) DESC");
	    }
        $this->view->doctor = $doctor;

		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		
		$form = new User_Form_DoctorPatient();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        $this->view->form = $form;
    }
	public function doctorDashboardAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
//		echo '<pre>';print_r($docObject);die;
		//error_log(print_r($docObject, true));
        $Appointment = new Application_Model_Appointment();
        $where = "doctor_id='{$docObject->getId()}' AND (MONTH(appointment_date)='" . date('n') . "' AND YEAR(appointment_date)='" . date('Y') . "' AND deleted!=1)";
        $object = $Appointment->fetchAll($where, "appointment_date ASC");
		
		$sort_by = $this->_getParam('sort');
			
		$date  = date('Y-m-d'); 
		
		//$startofMonth = date('Y-m-d', strtotime("begining of current month"));
		$startofMonth = date('Y-m-d', strtotime("first day of this month"));
		//$EndofMonth = date('Y-m-d', strtotime("last day of this month"));
		$EndofMonth = date('Y-m-d', strtotime("+7 days"));
		/********************To fetch the new appointments of a doctor*****************************/
		//$newappObject   = $Appointment->fetchAll("appointment_date>='$date' AND doctor_id='
		//{$docObject->getId()}' AND approve =0 ");
		
		//$sentappObject   = $Appointment->fetchAll("appointment_date>='{$startofMonth}'  AND appointment_date<='{$date}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL");
		
		$sentappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL");
		
		$this->view->sentappObject = $sentappObject;
		
		/********************To fetch the pending appointments of a doctor***************************/
		
		//$cancelappObject   = $Appointment->fetchAll("appointment_date>='{$startofMonth}' AND appointment_date<='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=2");
		
		$pendingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL AND (api_inbound_reply IS NULL || api_inbound_reply = '') AND manual_confirm !=1");
		$this->view->pendingappObject = $pendingappObject;

		/********************To fetch the confirmed appointments of a doctor*************************/
		//$rescheduledappObject   = $Appointment->fetchAll("appointment_date>='{$startofMonth}' AND appointment_date<='{$date}' AND doctor_id='{$docObject->getId()}' AND rescheduled=1");
		
		$confirmedappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND (api_inbound_reply=1 || manual_confirm=1)");
		
		$this->view->confirmedappObject = $confirmedappObject;
		
		/********************To fetch the upcoming appointments of a doctor*************************/
		//echo $sort_by;die;
		if($sort_by=='sent'){
			//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL", "ADDTIME( appointment_date, appointment_time ) asc");
			
			$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL", "ADDTIME( appointment_date, appointment_time ) asc");
		}else if($sort_by=='confirm'){
			//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND api_inbound_reply=1", "ADDTIME( appointment_date, appointment_time ) asc");
			$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND (api_inbound_reply=1 || manual_confirm=1)", "ADDTIME( appointment_date, appointment_time ) asc");
		}else if($sort_by=='pending'){
			//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL AND (api_inbound_reply IS NULL || api_inbound_reply = '')", "ADDTIME( appointment_date, appointment_time ) asc");
			$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND approve=1 AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL AND (api_inbound_reply IS NULL || api_inbound_reply = '') AND manual_confirm !=1", "ADDTIME( appointment_date, appointment_time ) asc");
		}else{
			$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1", "ADDTIME( appointment_date, appointment_time ) asc");
			//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND approve=1", "ADDTIME( appointment_date, appointment_time ) asc");
		}
		
		//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND approve=1", "ADDTIME( appointment_date, appointment_time ) asc");
		
		//$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1", "ADDTIME( appointment_date, appointment_time ) asc");
		
		$this->view->upcomingappObject = $upcomingappObject;
		
		//if($docObject->getId()==1122947){
			//echo "<pre>";print_r($pendingappObject);die;
		//}
		
		$Tasks = new Application_Model_DoctorTask();
		$tasks = $Tasks->fetchAll("doctor_id=".$docObject->getId(), "id DESC");
		$this->view->tasks = $tasks;
		
		$this->view->docObject = $docObject;
        $this->view->object = $object;
		/*Weekly Review star rating*/
		$doctor_review = new Application_Model_DoctorReview();
		/*$docreviewweekly=$doctor_review->getRecommendationReviewsWeekly($docObject->id);
		$this->view->weekly=$docreviewweekly;*/	
					
		/*Today starrating*/

		$docreviewtoday=$doctor_review->getRecommendationReviewsToday($docObject->id);				
		$this->view->Today=$docreviewtoday;		

	   /*Weekly Review star rating*/
		$previous_week = strtotime("0 week +1 day");
		$start_week = strtotime("last sunday midnight",$previous_week);
		$end_week = strtotime("next saturday",$start_week);
		$start_week = date("Y-m-d",$start_week);
		$end_week = date("Y-m-d",$end_week);		
		$this->view->weekly=$doctor_review->getAverageRecommendationReviews($start_week,$end_week,$docObject->id);			
				
		
		
    }
	
    /* Show Doctor Appointments*/
	
	public function doctorAppointmentBackupAction() {
	
		$this->view->headTitle('Doctor Appointments');
        $usersNs = new zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();
	
        $id = $this->_getParam('id');
	
		$upcomingWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND DATEDIFF(NOW(),appointment_date)<=0 AND approve!=2";
        $pastWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND DATEDIFF(NOW(),appointment_date)>0 AND approve!=2";		
		$cancellWhere = "deleted!=1 AND doctor_id={$doctor->getId()} AND approve=2";
              /*
		$this->view->upcomingObject = $Appointment->fetchAll($upcomingWhere, "ADDTIME( appointment_date, appointment_time ) asc ");
       		 $this->view->pastObject = $Appointment->fetchAll($pastWhere, "ADDTIME( appointment_date, appointment_time ) desc ");
		$this->view->cancelObjects = $Appointment->fetchAll($cancellWhere, "ADDTIME( appointment_date, appointment_time ) desc ");
             */
              
        if($id==''){
            $this->view->color='diff1';
            $this->view->upcomingObject = $Appointment->fetchAll($upcomingWhere, "appointment_date ASC");
        }else if($id=='past'){
          $this->view->color='diff2';
          $this->view->pastObject = $Appointment->fetchAll($pastWhere, "appointment_date DESC");
        }else if($id=='cancell'){
        $this->view->color='diff3';
        $this->view->cancelObjects = $Appointment->fetchAll($cancellWhere, "appointment_date DESC");
        }

        $this->view->doctor = $doctor;

		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		
		$form = new User_Form_DoctorPatient();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        $this->view->form = $form;
    }
	
   public function doctorDashboardBackupAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
		//echo '<pre>';print_r($docObject);die;
		//error_log(print_r($docObject, true));
        $Appointment = new Application_Model_Appointment();
        $where = "doctor_id='{$docObject->getId()}' AND (MONTH(appointment_date)='" . date('n') . "' AND YEAR(appointment_date)='" . date('Y') . "' AND deleted!=1)";
        $object = $Appointment->fetchAll($where, "appointment_date ASC");
        
		$date  = date('Y-m-d'); 
		//$startofMonth = date('Y-m-d', strtotime("begining of current month"));
		$startofMonth = date('Y-m-d', strtotime("first day of this month"));

		$EndofMonth = date('Y-m-d', strtotime("last day of this month"));
		/********************To fetch the new appointments of a doctor*****************************/

		//$newappObject   = $Appointment->fetchAll("appointment_date>='$date' AND doctor_id='

		//{$docObject->getId()}' AND approve =0 ");

		

		$sentappObject   = $Appointment->fetchAll("appointment_date>='{$date}'  AND appointment_date<='{$EndofMonth}'  AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL");

		

		$this->view->sentappObject = $sentappObject;

		

		/********************To fetch the upcoming appointments of a doctor*****************************/

		$upcomingappObject   = $Appointment->fetchAll("appointment_date>='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=1", "ADDTIME( appointment_date, appointment_time )  asc");

		$this->view->upcomingappObject = $upcomingappObject;

		

		/********************To fetch the pending appointments of a doctor*****************************/

		

		//$cancelappObject   = $Appointment->fetchAll("appointment_date>='{$startofMonth}' AND appointment_date<='{$date}' AND doctor_id='{$docObject->getId()}' AND approve=2");

		

		$pendingappObject   = $Appointment->fetchAll("appointment_date>='{$date}'  AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_outbound_sid IS NOT NULL AND (api_inbound_reply IS NULL || api_inbound_reply = '')");

		$this->view->pendingappObject = $pendingappObject;



		/********************To fetch the confirmed appointments of a doctor*****************************/

		//$rescheduledappObject   = $Appointment->fetchAll("appointment_date>='{$startofMonth}' AND appointment_date<='{$date}' AND doctor_id='{$docObject->getId()}' AND rescheduled=1");

		

		$confirmedappObject   = $Appointment->fetchAll("appointment_date>='{$date}'  AND appointment_date<='{$EndofMonth}' AND doctor_id='{$docObject->getId()}' AND (appointments.approve = 1 || appointments.approve = 3) AND cancelled_by = '0' AND api_inbound_reply=1");

		

		$this->view->confirmedappObject = $confirmedappObject;		

		$Tasks = new Application_Model_DoctorTask();
		$tasks = $Tasks->fetchAll("doctor_id=".$docObject->getId(), "id DESC");
		$this->view->tasks = $tasks;
		
		$this->view->docObject = $docObject;
        $this->view->object = $object;
		/*Weekly Review star rating*/
		$doctor_review = new Application_Model_DoctorReview();
		/*$docreviewweekly=$doctor_review->getRecommendationReviewsWeekly($docObject->id);
		$this->view->weekly=$docreviewweekly;*/	
					
		/*Today starrating*/

		$docreviewtoday=$doctor_review->getRecommendationReviewsToday($docObject->id);				
		$this->view->Today=$docreviewtoday;		

	   /*Weekly Review star rating*/
		$previous_week = strtotime("0 week +1 day");
		$start_week = strtotime("last sunday midnight",$previous_week);
		$end_week = strtotime("next saturday",$start_week);
		$start_week = date("Y-m-d",$start_week);
		$end_week = date("Y-m-d",$end_week);		
		$this->view->weekly=$doctor_review->getAverageRecommendationReviews($start_week,$end_week,$docObject->id);			
				
		
		
    }
	
	
    /* Make unfavourite to Favourite Doctor */
	public function favouriteDoctorAction(){
		$doctor_id = $_REQUEST['doctor_id'];
		$usersNs = new Zend_Session_Namespace("members");
		$patientModel = new Application_Model_Patient();
        $patient = $patientModel->fetchRow("user_id='{$usersNs->userId}'");
		$patient_id = $patient->getId();
		$res = new stdClass();
		$res->result = false;
		if ($doctor_id && $patient_id) {		
			$favorite = new Application_Model_PatientFavoriteDoctor();			
			$favRows = $favorite->fetchAll( array("doctor_id='{$doctor_id}'", "patient_id='{$patient_id}'"));
			if(count($favRows)){ // are there one or multiple existing entries?
				foreach($favRows AS $favorite){
					$favorite->setFavoriteStatus("Favorite");
					$favorite->setUpdateTime(time());
					$favorite->save();
				}
			}else{ // no entries, create a new entry
				$favorite->setPatientId($patient_id);
				$favorite->setDoctorId($doctor_id);
				$favorite->setFavoriteStatus("Favorite");
				$favorite->setCreateTime(time());
				$favorite->setUpdateTime(time());
				$favorite->save();		
			}			
			
			$Mail = new Base_Mail("UTF-8");
			$Mail->sendDoctorFavorite($doctor_id);
			
			$res->result = true;
			$res->msg = 'Added to favorites';
		}else{
			$res->msg = 'DO NOT HAVE PATIENT AND/OR DOCTOR ID';
		}
		echo json_encode($res);
		die;  
    }
		
	/* Un-Favorite Doctor */
	public function unfavouriteDoctorAction(){
		$res = new stdClass();
		$res->result = false;
		$doctor_id=$_REQUEST['doctor_id'];
		if ($doctor_id) {
			$favModel = new Application_Model_PatientFavoriteDoctor();
			$favRows = $favModel->fetchAll("doctor_id='{$doctor_id}'");
			foreach($favRows AS $favorite){ // unfavorite all entries
				$favorite->setFavoriteStatus("UnFavorite");
				$favorite->setUpdateTime(time());
				$favorite->save();
			}
			$res->result = true;
			$res->msg = 'Removed from favorites';			
		}else{
			$res->msg = 'NO doctor_id PRESENT';
		}  
		echo json_encode($res);
		die;          
    }
	
	/* Un-Favourite doctor (method for patient dashboard / favourites view) */
	public function patientFavouriteDoctorAction() {
		//PatientFavoriteDoctor
		$this->view->headTitle('Favorite Doctor');
		$usersNs1 = new zend_Session_Namespace("members");
	    $favoutiteDoc1 = new Application_Model_PatientFavoriteDoctor();
		if(isset($_REQUEST["favourite_id"])){
			//echo $usersNs1->userId;
			$favoutiteDoc1->setId($_REQUEST["favourite_id"]);
			$status="UnFavorite";
			$favoutiteDoc1->setFavoriteStatus($status);
			$favoutiteDoc1->setDoctorId($_REQUEST['doctor_id']);
			$favoutiteDoc1->setPatientId($usersNs1->userId);
			$favoutiteDoc1->setCreateTime(time());
			$favoutiteDoc1->setUpdateTime(time());
			$favoutiteDoc1->save();
		}
        $usersNs = new zend_Session_Namespace("members");
        $patientModel = new Application_Model_Patient();
        $patient = $patientModel->fetchRow("user_id='{$usersNs->userId}'");
		$patient_id = $patient->getId();
        $favoutiteDoc = new Application_Model_PatientFavoriteDoctor();
		
		$condition = "favorite_status='Favorite' AND patient_id='{$patient_id}'";
       
        $pastObject = $favoutiteDoc->fetchAll($condition, "id DESC");
		//print_r($pastObject);die;
       
      
        $this->view->pastObject = $pastObject;
       
		
		
		
    }
	
	public function patientDashboardAction() {
	
		$this->view->headTitle('Patient Dashboard');
        $usersNs = new zend_Session_Namespace("members");
        $Patient = new Application_Model_Patient();
        $docPatient = $Patient->fetchRow("user_id='{$usersNs->userId}'");
        /* $Appointment = new Application_Model_Appointment();
		$upcomingWhere = "deleted!=1 AND user_id={$usersNs->userId} AND DATEDIFF(NOW(),appointment_date)<=0 AND  approve!=2";
        $pastWhere = "deleted!=1 AND (user_id={$usersNs->userId} AND DATEDIFF(NOW(),appointment_date)>0) OR (user_id={$usersNs->userId} AND approve=2)";
        $upcomingObject = $Appointment->fetchAll($upcomingWhere, "appointment_date DESC");
        $pastObject = $Appointment->fetchAll($pastWhere, "appointment_date DESC");
        $this->view->upcomingObject = $upcomingObject;
        $this->view->pastObject = $pastObject;*/
        $this->view->Patient = $docPatient;
		
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		
		
		$usersNss = new zend_Session_Namespace("members");
        
        $favoutiteDocs = new Application_Model_PatientFavoriteDoctor();
		$patientModel = new Application_Model_Patient();
        $patient = $patientModel->fetchRow("user_id='{$usersNss->userId}'");
		$patient_id = $patient->getId();
		$conditions = "favorite_status='Favorite' AND patient_id={$patient_id}";
        $favourites = $favoutiteDocs->fetchAll($conditions, "id DESC");
        $this->view->favourites = $favourites;


        $Appointment = new Application_Model_Appointment();
        /*$where = "user_id='{$usersNs->userId}' AND (MONTH(appointment_date)='" . date('n') . "' AND YEAR(appointment_date)='" . date('Y') . "' AND deleted!=1)";
        $object = $Appointment->fetchAll($where, "appointment_date ASC");*/
        
		$date  = date('Y-m-d'); 
		/********************To fetch the new appointments of the patient*****************************/
		$newappObject   = $Appointment->fetchAll("appointment_date>='$date' AND user_id='{$usersNs->userId}' AND approve!=2", "appointment_date DESC");
		$this->view->newappObject = $newappObject;
		
		/********************To fetch the latest past appointments of the patient*****************************/
		$pastAppointments = $Appointment->fetchAll("appointment_date<'$date' AND user_id='{$usersNs->userId}' AND approve=1", "appointment_date DESC");
		$this->view->pastAppointments = $pastAppointments;

    }
    public function appointmentDetailAction() {
        $id = $this->_getParam('id');
        $Appointment = new Application_Model_Appointment();
        $Insurance = new Application_Model_InsuranceCompany();
        $appObject = $Appointment->fetchRow("id='{$id}' AND deleted!=1");
        if ($appObject) {
            $insuranceObject = $Insurance->fetchRow("id='{$appObject->getInsurance()}'");
            $appStatus = $Appointment->getAppointmentStatus("id='{$id}'");
            $Doctor = new Application_Model_Doctor();
            $docObject = $Doctor->fetchRow("id='{$appObject->getDoctorId()}'");
            $Patient = new Application_Model_Patient();
            $reasonForVisit = new Application_Model_ReasonForVisit();
            $visitObject = $reasonForVisit->getMyResonForVisit("id='{$appObject->getReasonForVisit()}'");
            $patObject = $Patient->fetchRow("user_id='{$appObject->userId}'");
            $appGender = $Appointment->getFullGender("id='{$id}'");
            $this->view->profileImage = $docObject->getImage();
            $this->view->docObject = $docObject;
            $this->view->patObject = $patObject;
            $this->view->appGender = $appGender;
            $this->view->appStatus = $appStatus;
            $this->view->visitObject = $visitObject;
            $this->view->insuranceObject = $insuranceObject;
        }
		
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		$hours = $settings->settingValue('hours');
		if($hours) {
			$this->view->timeformat = "%I:%M %P";
		} else {
			$this->view->timeformat = "%H:%M";
		}
        $this->view->appObject = $appObject;
    }
    
     public function doctorPatientEditAction() {
    	$path = "images/patient_image/";
        $userNs = new Zend_Session_Namespace("members");
        $id = $this->_getParam('patuserid');
        $form = new User_Form_Patient();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        
        $Patient = new Application_Model_Patient();
        $User = new Application_Model_User();
		if (0 < (int) $id) {            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
            $this->view->id = $patObject->getId();
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
	     
	        $request = $this->getRequest();
	        $options = $request->getPost();
	        $userObject= array();
	        if ($request->isPost()) {
	        	//error_log("saving patient");
	            $email = trim($options['email']);
	            if($email){
					$userObject = $User->fetchRow("id!='{$patObject->getuserId()}' AND email='{$email}'");
				}
	            
	            if(is_object($userObject)) {
	            	//error_log("problem1");
	                $form->setErrorMessages(array($this->view->lang[391]));
	                $emailerror = 1;
	            }else {
	            	//error_log("no email problem");
	                $emailerror = 0;
	            }
				if ($emailerror < 1) {
		            //echo "<pre>";print_r($options);die;

					//error_log("no problem");
					$msg = $this->view->lang[546];
	                $patObject->setName($options['first_name']." ".$options['last_name']);
	                $last_updated = strtotime("now");
	                $patObject->setStreet($options['street']);
	                $patObject->setCity($options['city']);
	                $patObject->setState($options['state']);
	                $patObject->setZipcode($options['zipcode']);
	                $patObject->setLastUpdated($last_updated);
	                $patObject->setMonthDob($options['month_dob']);
				    $patObject->setDateDob($options['date_dob']);
			        $patObject->setYearDob($options['year_dob']);                
			        $patObject->setEnableCommunication($options['enable_communication']);
			        $patObject->setCommunicationViaPhone($options['communication_via_phone']);
			        $patObject->setCommunicationViaText($options['communication_via_text']);
			        $patObject->setCommunicationViaEmail($options['communication_via_email']);
	                $dob['year'] = $options['year_dob'];
	                $dob['month'] = $options['month_dob'];
	                $dob['day'] = $options['date_dob'];
					$age = $patObject->getAge($dob);	                                
	                $patObject->setAge($age);
	                $patObject->setPhone($options['phone']);
	                $patObject->setMobile($options['mobile']);
	                //$patObject->setGender($options['gender']);
	                //$patObject->setInsuranceCompanyId($options['insurance']);
					
					//profile picture
					/* ------------------END COMPANY LOGO ------------------ */
					$upload = new Zend_File_Transfer_Adapter_Http();
					$path = "images/patient_image/";
					$upload->setDestination($path);
					try {
					    $upload->receive();
					} catch (Zend_File_Transfer_Exception $e) {
					    $e->getMessage();
					}
					//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
					$upload->setOptions(array('useByteString' => false));
					$file_name = $upload->getFileName('profileimage');
					if (!empty($file_name)) {
					    $imageArray = explode(".", $file_name);
					    $ext = strtolower($imageArray[count($imageArray) - 1]);
					    $target_file_name = "pat_" . time() . ".{$ext}";
					    
					    $targetPath = $path . $target_file_name;
					    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
					    $filterFileRename->filter($file_name);
					    /* ------------------ THUMB --------------------------- */
					    $image_name = $target_file_name;
					    $newImage = $path . $image_name;
					    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
					    $thumb->resize(400, 234);
					    $thumb->save($newImage);
					    
				        $del_image = $path . $patObject->getProfileimage();			        
				        if (file_exists($del_image))unlink($del_image);
				        $small_del_image = $path ."thumb1_". $patObject->getProfileimage();;
				        if (file_exists($small_del_image))unlink($small_del_image);
				        
				        $patObject->setProfileimage($image_name);
					    /* ------------------ END THUMB ------------------------ */
					}
					/* ------------------END COMPANY LOGO ------------------ */



	               
	                $patObject->save();
	                $userObjectsave = $User->fetchRow("id='{$patObject->getUserId()}'");
	                $userObjectsave->setFirstName($options['first_name']);
	                $userObjectsave->setLastName($options['last_name']);
	                $userObjectsave->save();

					if (!empty($options['email'])) {
						//error_log("checking email");
					    $objUser = $User->fetchRow("email ='{$options['email']}' AND id !={$id}");
					    if (!empty($objUser)) {
					    	//error_log("problem2");
					        $form->getElement('email')->setErrors(array("email already exists"));
					        $emailerror = 1;
					        $this->view->emailerror = "Email is used by another user";
					    } else { //can use that email
							///error_log("valid form, saving email");
	                		$userObjectsave->setEmail($options['email']);
	                		$userObjectsave->save();
	                		$this->view->msg = $this->view->lang[546];


	                		if ($options ['password'] != '') {
				            	if(md5($options ['oldPassword']) == $userObjectsave->getPassword() ) {
				                	$userObjectsave->setPassword(md5($options ['password']));
				                	$userObjectsave->save();
				                } else {
				                	$this->view->msg = $this->view->lang[941];
				                	$emailerror = 1;
				                }
				            }
						}
					}
				}
			}
            
            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
           
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
            $options['id'] = $patObject->getId();
            $options['first_name'] = $userObject->getFirstName();
            $options['last_name'] = $userObject->getLastName();
            $options['street'] = $patObject->getStreet();
            $options['city'] = $patObject->getCity();
            $options['state'] = $patObject->getState();
            $options['zipcode'] = $patObject->getZipcode();
            //$options['age'] = $patObject->getAge();
            $options['month_dob'] = $patObject->getMonthDob();
			$options['date_dob'] = $patObject->getDateDob();
			$options['year_dob'] = $patObject->getYearDob();
            $options['phone'] = $patObject->getPhone();
            $options['mobile'] = $patObject->getMobile();
            //$options['gender'] = $patObject->getGender();
            //$options['insurance'] = $patObject->getInsuranceCompanyId();
            $options['user_id'] = $id;
            $options['email'] = $userObject->getEmail();
    		$options['enable_communication']  = $patObject->getEnableCommunication();  
    		$options['communication_via_phone']  = $patObject->getCommunicationViaPhone();  
    		$options['communication_via_text']  = $patObject->getCommunicationViaText();  
    		$options['communication_via_email']  = $patObject->getCommunicationViaEmail();  
    		
    		$this->view->communication_via_phone = $patObject->getCommunicationViaPhone();  
			$this->view->communication_via_text = $patObject->getCommunicationViaText();  
    		$this->view->communication_via_email = $patObject->getCommunicationViaEmail();  
    		
    		//echo '<pre>';print_r($patObject);die;
            /*$insuranceCompId = $patObject->getInsuranceCompanyId();
            $Insurance = new Application_Model_InsuranceCompany();
            $insurances = $Insurance->fetchAll("status=1", "company ASC");
			
            $this->view->insurances = $insurances;
            
            $this->view->insuranceCompId = $insuranceCompId;
            $this->view->insuranceCompId = $insuranceCompId;*/
            $this->view->profileimage =  $patObject->getImage();
            $form->populate($options);
        }
		$this->view->form = $form;
	}


    public function doctorPatientEditActionBkp1feb2016() {
    	$path = "images/patient_image/";
        $userNs = new Zend_Session_Namespace("members");
        $id = $this->_getParam('patuserid');
        $form = new User_Form_Patient();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        
        $Patient = new Application_Model_Patient();
        $User = new Application_Model_User();
		if (0 < (int) $id) {            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
            $this->view->id = $patObject->getId();
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
	     
	        $request = $this->getRequest();
	        $options = $request->getPost();
	        if ($request->isPost()) {
	        	//error_log("saving patient");
	            $email = trim($options['email']);
	            $userObject = $User->fetchRow("id!='{$patObject->getuserId()}' AND email='{$email}'");
	            if (is_object($userObject)) {
	            	//error_log("problem1");
	                $form->setErrorMessages(array($this->view->lang[391]));
	                $emailerror = 1;
	            } else {
	            	//error_log("no email problem");
	                $emailerror = 0;
	            }
				if ($emailerror < 1) {
					//error_log("no problem");
	                $msg = $this->view->lang[546];
	                
	                $patObject->setName($options['first_name']." ".$options['last_name']);
	                $last_updated = strtotime("now");
	                $patObject->setStreet($options['street']);
	                $patObject->setCity($options['city']);
	                $patObject->setState($options['state']);
	                $patObject->setZipcode($options['zipcode']);
	                $patObject->setLastUpdated($last_updated);
	                $patObject->setMonthDob($options['month_dob']);
			$patObject->setDateDob($options['date_dob']);
			$patObject->setYearDob($options['year_dob']);                
			$patObject->setEnableCommunication($options['enable_communication']);
	                $dob['year'] = $options['year_dob'];
	                $dob['month'] = $options['month_dob'];
	                $dob['day'] = $options['date_dob'];
			$age = $patObject->getAge($dob);	                                
	                $patObject->setAge($age);
	                $patObject->setPhone($options['phone']);
	                $patObject->setMobile($options['mobile']);
	                //$patObject->setGender($options['gender']);
	                //$patObject->setInsuranceCompanyId($options['insurance']);
					
					//profile picture
					/* ------------------END COMPANY LOGO ------------------ */
					$upload = new Zend_File_Transfer_Adapter_Http();
					$path = "images/patient_image/";
					$upload->setDestination($path);
					try {
					    $upload->receive();
					} catch (Zend_File_Transfer_Exception $e) {
					    $e->getMessage();
					}
					//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
					$upload->setOptions(array('useByteString' => false));
					$file_name = $upload->getFileName('profileimage');
					if (!empty($file_name)) {
					    $imageArray = explode(".", $file_name);
					    $ext = strtolower($imageArray[count($imageArray) - 1]);
					    $target_file_name = "pat_" . time() . ".{$ext}";
					    
					    $targetPath = $path . $target_file_name;
					    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
					    $filterFileRename->filter($file_name);
					    /* ------------------ THUMB --------------------------- */
					    $image_name = $target_file_name;
					    $newImage = $path . $image_name;
					    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
					    $thumb->resize(400, 234);
					    $thumb->save($newImage);
					    
				        $del_image = $path . $patObject->getProfileimage();			        
				        if (file_exists($del_image))unlink($del_image);
				        $small_del_image = $path ."thumb1_". $patObject->getProfileimage();;
				        if (file_exists($small_del_image))unlink($small_del_image);
				        
				        $patObject->setProfileimage($image_name);
					    /* ------------------ END THUMB ------------------------ */
					}
					/* ------------------END COMPANY LOGO ------------------ */



	               
	                $patObject->save();
	                $userObjectsave = $User->fetchRow("id='{$patObject->getUserId()}'");
	                $userObjectsave->setFirstName($options['first_name']);
	                $userObjectsave->setLastName($options['last_name']);
	                $userObjectsave->save();

					if (!empty($options['email'])) {
						//error_log("checking email");
					    $objUser = $User->fetchRow("email ='{$options['email']}' AND id !={$id}");
					    if (!empty($objUser)) {
					    	//error_log("problem2");
					        $form->getElement('email')->setErrors(array("email already exists"));
					        $emailerror = 1;
					        $this->view->emailerror = "Email is used by another user";
					    } else { //can use that email
							///error_log("valid form, saving email");
	                		$userObjectsave->setEmail($options['email']);
	                		$userObjectsave->save();
	                		$this->view->msg = $this->view->lang[546];


	                		if ($options ['password'] != '') {
				            	if(md5($options ['oldPassword']) == $userObjectsave->getPassword() ) {
				                	$userObjectsave->setPassword(md5($options ['password']));
				                	$userObjectsave->save();
				                } else {
				                	$this->view->msg = $this->view->lang[941];
				                	$emailerror = 1;
				                }
				            }
						}
					}
				}
			}
            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
            $options['id'] = $patObject->getId();
            $options['first_name'] = $userObject->getFirstName();
            $options['last_name'] = $userObject->getLastName();
            $options['street'] = $patObject->getStreet();
            $options['city'] = $patObject->getCity();
            $options['state'] = $patObject->getState();
            $options['zipcode'] = $patObject->getZipcode();
            //$options['age'] = $patObject->getAge();
            $options['month_dob'] = $patObject->getMonthDob();
			$options['date_dob'] = $patObject->getDateDob();
			$options['year_dob'] = $patObject->getYearDob();
            $options['phone'] = $patObject->getPhone();
            $options['mobile'] = $patObject->getMobile();
            //$options['gender'] = $patObject->getGender();
            //$options['insurance'] = $patObject->getInsuranceCompanyId();
            $options['user_id'] = $id;
            $options['email'] = $userObject->getEmail();
    		 $options['enable_communication']  = $patObject->getEnableCommunication();        
            /*$insuranceCompId = $patObject->getInsuranceCompanyId();
            $Insurance = new Application_Model_InsuranceCompany();
            $insurances = $Insurance->fetchAll("status=1", "company ASC");
			
            $this->view->insurances = $insurances;
            
            $this->view->insuranceCompId = $insuranceCompId;
            $this->view->insuranceCompId = $insuranceCompId;*/
            $this->view->profileimage =  $patObject->getImage();
            $form->populate($options);
        }
		$this->view->form = $form;
	}

	public function patientEditAction() {
    	$path = "images/patient_image/";
        $userNs = new Zend_Session_Namespace("members");
        $id = $userNs->userId;
        $form = new User_Form_Patient();
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        
        $Patient = new Application_Model_Patient();
        $User = new Application_Model_User();
		if (0 < (int) $id) {            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
	     
	        $request = $this->getRequest();
	        $options = $request->getPost();
	        if ($request->isPost()) {
	        	
	        	//error_log("saving patient");
	            $email = trim($options['email']);
	            $userObject = $User->fetchRow("id!='{$patObject->getuserId()}' AND email='{$email}'");
	            if (is_object($userObject)) {
	            	//error_log("problem1");
	                $form->setErrorMessages(array($this->view->lang[391]));
	                $emailerror = 1;
	            } else {
	            	//error_log("no email problem");
	                $emailerror = 0;
	            }
				if ($emailerror < 1) {
					//error_log("no problem");
	                $msg = $this->view->lang[546];
	                
	                $patObject->setName($options['first_name']." ".$options['last_name']);
	                $last_updated = strtotime("now");
	                $patObject->setStreet($options['street']);
	                $patObject->setCity($options['city']);
	                $patObject->setState($options['state']);
	                $patObject->setZipcode($options['zipcode']);
	                $patObject->setLastUpdated($last_updated);
	                $patObject->setMonthDob($options['month_dob']);
					$patObject->setDateDob($options['date_dob']);
					$patObject->setYearDob($options['year_dob']);                
	                $dob['year'] = $options['year_dob'];
	                $dob['month'] = $options['month_dob'];
	                $dob['day'] = $options['date_dob'];
					$age = $patObject->getAge($dob);	                                
	                $patObject->setAge($age);
	                $patObject->setPhone($options['phone']);
	                $patObject->setMobile($options['mobile']);
	                //$patObject->setGender($options['gender']);
	                //$patObject->setInsuranceCompanyId($options['insurance']);
					
					//profile picture
					/* ------------------END COMPANY LOGO ------------------ */
					$upload = new Zend_File_Transfer_Adapter_Http();
					$path = "images/patient_image/";
					$upload->setDestination($path);
					try {
					    $upload->receive();
					} catch (Zend_File_Transfer_Exception $e) {
					    $e->getMessage();
					}
					//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
					$upload->setOptions(array('useByteString' => false));
					$file_name = $upload->getFileName('profileimage');
					if (!empty($file_name)) {
					    $imageArray = explode(".", $file_name);
					    $ext = strtolower($imageArray[count($imageArray) - 1]);
					    $target_file_name = "pat_" . time() . ".{$ext}";
					    
					    $targetPath = $path . $target_file_name;
					    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
					    $filterFileRename->filter($file_name);
					    /* ------------------ THUMB --------------------------- */
					    $image_name = $target_file_name;
					    $newImage = $path . $image_name;
					    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
					    $thumb->resize(400, 234);
					    $thumb->save($newImage);
					    
					    if($patObject->getProfileimage()) {
					        $del_image = $path . $patObject->getProfileimage();			        
					        if (file_exists($del_image))unlink($del_image);
					        $small_del_image = $path ."thumb1_". $patObject->getProfileimage();;
					        if (file_exists($small_del_image))unlink($small_del_image);
					    }
				        
				        $patObject->setProfileimage($image_name);
					    /* ------------------ END THUMB ------------------------ */
					}
					/* ------------------END COMPANY LOGO ------------------ */



	               
	                $patObject->save();
	                $userObjectsave = $User->fetchRow("id='{$patObject->getUserId()}'");
	                $userObjectsave->setFirstName($options['first_name']);
	                $userObjectsave->setLastName($options['last_name']);
	                $userObjectsave->save();

					if (!empty($options['email'])) {
						//error_log("checking email");
					    $objUser = $User->fetchRow("email ='{$options['email']}' AND id !={$id}");
					    if (!empty($objUser)) {
					    	//error_log("problem2");
					        $form->getElement('email')->setErrors(array("email already exists"));
					        $emailerror = 1;
					        $this->view->emailerror = "Email is used by another user";
					    } else { //can use that email
							///error_log("valid form, saving email");
	                		$userObjectsave->setEmail($options['email']);
	                		$userObjectsave->save();
	                		$this->view->msg = $this->view->lang[546];


	                		if ($options ['password'] != '') {
				            	if(md5($options ['oldPassword']) == $userObjectsave->getPassword() ) {
				                	$userObjectsave->setPassword(md5($options ['password']));
				                	$userObjectsave->save();
				                } else {
				                	$this->view->msg = $this->view->lang[941];
				                	$emailerror = 1;
				                }
				            }
						}
					}
				}
			}
            
            $patObject = $Patient->fetchRow("user_id='{$id}'");
            $userObject = $User->fetchRow("id='{$patObject->getuserId()}'");
            $options['id'] = $patObject->getId();
            $options['first_name'] = $userObject->getFirstName();
            $options['last_name'] = $userObject->getLastName();
            $options['street'] = $patObject->getStreet();
            $options['city'] = $patObject->getCity();
            $options['state'] = $patObject->getState();
            $options['zipcode'] = $patObject->getZipcode();
            //$options['age'] = $patObject->getAge();
            $options['month_dob'] = $patObject->getMonthDob();
			$options['date_dob'] = $patObject->getDateDob();
			$options['year_dob'] = $patObject->getYearDob();
            $options['phone'] = $patObject->getPhone();
            $options['mobile'] = $patObject->getMobile();
            //$options['gender'] = $patObject->getGender();
            //$options['insurance'] = $patObject->getInsuranceCompanyId();
            $options['user_id'] = $id;
            $options['email'] = $userObject->getEmail();
            
            /*$insuranceCompId = $patObject->getInsuranceCompanyId();
            $Insurance = new Application_Model_InsuranceCompany();
            $insurances = $Insurance->fetchAll("status=1", "company ASC");
			
            $this->view->insurances = $insurances;
            
            $this->view->insuranceCompId = $insuranceCompId;
            $this->view->insuranceCompId = $insuranceCompId;*/
            $this->view->profileimage =  $patObject->getImage();
            $form->populate($options);
        }
		$this->view->form = $form;
	}
    public function appointmentAction() {
        $tab = $this->_getParam('tab');
        $today = $this->_getParam('today');
        $Calendar = new Zend_Session_Namespace("calendar");
        if ($today != '') {
            $Calendar->TODAY = $today;
        } else {
            $Calendar->TODAY = time();
        }
        $this->view->tab = $tab;
    }
    public function ajaxAppointmentAction() {
        $this->_helper->layout->disableLayout();
        $tab = $this->_getParam('tab');
        $today = $this->_getParam('today');
        $Calendar = new Zend_Session_Namespace("calendar");
        if ($today != '') {
            $Calendar->TODAY = $today;
        } else {
            $Calendar->TODAY = time();
        }
        $this->view->tab = $tab;
        $return['daily'] = $this->view->render('index/daily.phtml');
        $return['weekly'] = $this->view->render('index/weekly.phtml');
        $return['monthly'] = $this->view->render('index/monthly.phtml');
        $return['tab'] = $tab;
		$settings = new Admin_Model_GlobalSettings();
		$this->view->hours = $settings->settingValue('hours');
        echo Zend_Json::encode($return);
        exit();
    }
    public function accountDetailsAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $id = $usersNs->userId;
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');
        $emailerror = 0;
        $form = new User_Form_AccountDetails();
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->fetchRow("user_id={$id}");
            $options['id'] = $id;
            $options['fname'] = $object->getFname();
            $modelUser = new Application_Model_User();
            $objectUser = $modelUser->find($id);
            $options['email'] = $objectUser->getEmail();
			$options['email2'] = $objectUser->getEmail2();
            $options['email3'] = $objectUser->getEmail3();
			//$options['email_used'] = $objectUser->getEmailUsed();
			$options['username'] = $objectUser->getUsername();
            $form->populate($options);
        }
        $request = $this->getRequest();
        $options = $request->getPost();
        if ($request->isPost()) {
            $flag = 1;
            $modelUser = new Application_Model_User();
            if(!empty($options['email'])){
				$objUser = $modelUser->fetchRow("(email ='{$options['email']}' || email2 ='{$options['email']}' || email3 ='{$options['email']}') AND id !={$id}");
                //$objUser = $modelUser->fetchRow("email ='{$options['email']}' AND id !={$id}");
                if(!empty($objUser)) {
					$flag = 0;
                    $form->getElement('email')->setErrors(array("email already exists"));
                    $emailerror = 1;
                    $this->view->emailerror = "";
                }
            }
            if(!empty($options['email2'])){
                $objUser3 = $modelUser->fetchRow("(email ='{$options['email2']}' || email2 ='{$options['email2']}' || email3 ='{$options['email2']}') AND id !={$id}");
                if(!empty($objUser3)){
					$flag = 0;
                    $form->getElement('email2')->setErrors(array("second email already exists"));
                    $emailerror = 1;
                    $this->view->emailerror = "";
                }
            }
            
		 			
            if(!empty($options['email3'])){
                $objUser5 = $modelUser->fetchRow("(email ='{$options['email3']}' || email2 ='{$options['email3']}' || email3 ='{$options['email3']}') AND id !={$id}");
                if (!empty($objUser5)){
					$flag = 0;
                    $form->getElement('email3')->setErrors(array("third email already exists"));
                    $emailerror = 1;
                    $this->view->emailerror = "";
                }
            }
            if ($form->isValid($options) && trim($flag)==1) { 
				
                $objectUser->setEmail($options['email']);
                $objectUser->setEmail2($options['email2']);
				$objectUser->setEmail3($options['email3']);
				//$objectUser->setEmailUsed($options['email_used']);
                $objectUser->save();
                $this->view->msg = $this->view->lang[546];
                
                if ($options ['password'] != '') {
                	if(md5($options ['oldPassword']) == $objectUser->getPassword() ) {
                    	$objectUser->setPassword(md5($options ['password']));
                    	$objectUser->save();
                    } else {
                    	$this->view->msg = $this->view->lang[941];
                    	$emailerror = 1;
                    }
                }
            } else {
                if ($emailerror == 1) {
                    $this->view->emailerror = $this->view->lang[391];
                }
             
                $form->reset();
                $form->populate($options);
            }
        }
        $this->view->form = $form;
    }
    public function editAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $id = $usersNs->userId;
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');
        $StringObj = new Base_String();
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->find($id);
            $modelDoctorInsurance = new Application_Model_DoctorInsurance();
            $ArrDoctorInsurance = $modelDoctorInsurance->getDoctorinsurance("doctor_id={$id}");
            $modelDoctorReasonForVisit = new Application_Model_DoctorReasonForVisit();
            $ArrDoctorReasonForVisit = $modelDoctorReasonForVisit->getDoctorReasonForVisit("doctor_id={$id}");
            $modeldoctor_association = new Application_Model_DoctorAssociation();
            $ArrDoctorAssociation = $modeldoctor_association->getDoctorAssociation("doctor_id={$id}");
            
            $options['id'] = $id;
            $options['user_id'] = $object->getUserId();
            $options['category_id'] = $object->getCategoryId();
            $options['fname'] = $object->getFname();
            $options['company'] = $object->getCompany();
            $options['street'] = $object->getStreet();
            $options['zipcode'] = $object->getZipcode();
            $options['city'] = $object->getCity();
            $options['country'] = $object->getCountry();
            $options['office_hour'] = $object->getOfficeHours();
            $options['education'] = $object->getEducation();
            $options['creditlines'] = $object->getCreditlines();
            $options['assign_phone'] = $object->getAssignPhone();
            $options['actual_phone'] = $object->getActualPhone();
            $options['awards'] = $object->getAwards();
            $options['about'] = $object->getAbout();
            
			
			$modelAffil = new Application_Model_HospitalAffiliation();
			$options['state_for_affiliate'] = $modelAffil->getAllAffiliation("doctor_id={$id}");
        }
        $request = $this->getRequest();
        $options = $request->getPost();
 
        if ($request->isPost()) {
         
        }
        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }
     public function officeinfoAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $id = $usersNs->userId;
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');
        $form = new User_Form_Officeinfo();
        $form->setAttrib('enctype', 'multipart/form-data');
        //Hiding the auto population of forms
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        $form->getElement('doctor_reason_for_visit2')->setRegisterInArrayValidator(false);
        $form->getElement('doctor_reason_for_visit')->setRegisterInArrayValidator(false);
        $StringObj = new Base_String();
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->fetchRow("user_id = {$id}");
            $modelDoctorInsurance = new Application_Model_DoctorInsurance();
            $ArrDoctorInsurance = $modelDoctorInsurance->getDoctorinsurance("doctor_id={$object->getId()}");
            $modelDoctorReasonForVisit = new Application_Model_DoctorReasonForVisit();
            $ArrDoctorReasonForVisit = $modelDoctorReasonForVisit->getDoctorReasonForVisit("doctor_id={$object->getId()}");
            $modeldoctor_association = new Application_Model_DoctorAssociation();
            $ArrDoctorAssociation = $modeldoctor_association->getDoctorAssociation("doctor_id={$object->getId()}");
			$selectedreason = $modelDoctorReasonForVisit->getDoctorReasonForVisitForDoctorEdit("doctor_id={$object->getId()}", null, 1);
            if (empty($selectedreason))
                $selectedreason = 0;
            //For all association it should not conatain that is alredy selected
            $ReasonforvisitModel = new Application_Model_ReasonForVisit();
            $docCategory = new Application_Model_DoctorCategory();
            $selectedcategory = $docCategory->getDoctorCategories("doctor_id={$object->getId()}", null, 1);
            if (empty($selectedcategory))
                $selectedcategory = 0;
            $modelReasonForVisit = new Application_Model_ReasonForVisit();
            $ArrallDoctorReasonForVisit = $modelReasonForVisit->getReasonForVisit("id not in({$selectedreason}) and category_id in ({$selectedcategory})");
            $form->getElement('doctor_reason_for_visit2')->setMultiOptions($ArrallDoctorReasonForVisit);
            $options['id'] = $id;
            $options['user_id'] = $object->getUserId();
            $options['fname'] = stripslashes($object->getFname());
            $options['company'] = stripslashes($object->getCompany());
			$options['assign_phone'] = stripslashes($object->getAssignPhone());
			$options['area'] = stripslashes($object->getArea());
            $options['street'] = stripslashes($object->getStreet());
            $options['zipcode'] = stripslashes($object->getZipcode());
            $options['city'] = stripslashes($object->getCity());
            
			$options['office'] = stripslashes($object->getOffice());
            $options['language'] = stripslashes($object->getLanguage());
            $options['office_hours'] = stripslashes($object->getOfficeHours());
            $options['state'] = $object->getState();
			
			//assistants			
			$docAssist = new Application_Model_DoctorAssistant();
			$ArrDoctorAssistant = $docAssist->getDoctorAssistantForDoctorEdit("doctor_id={$object->getId()} ");
            $selectedassist = $docAssist->getDoctorAssistantForDoctorEdit("doctor_id={$object->getId()}", null, 1);
            if (empty($selectedassist))
                $selectedassist = 0;
           
            //On Edit page we should only those association that is for same category that is selected by doctor
			$Assistant = new Application_Model_Assistant();
            $ArrallDoctorAssist = $Assistant ->getAssistants("id not in ({$selectedassist})");
            
            
            //For all association it should not contain that is alredy selecte
            $form->getElement('doctor_assistant')->setMultiOptions($ArrDoctorAssistant);
            $form->getElement('doctor_assistant2')->setMultiOptions($ArrallDoctorAssist);
			
            $form->populate($options);
        }
        $request = $this->getRequest();
        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {
                $msg = urlencode(base64_encode($this->view->lang[546]));
                if (0 < (int) $id) {
                    $options['id'] = $id;
                    //$object->setCategoryId($options['category_id']);
                    $object->setCompany(stripslashes($options['company']));
					$object->setArea(stripslashes($options['area']));
					$object->setAssignPhone(stripslashes($options['assign_phone']));
					$upload = new Zend_File_Transfer_Adapter_Http();
                    $path = "images/doctor_image/";
                    $upload->setDestination($path);
                    try {
                        $upload->receive();
                    } catch (Zend_File_Transfer_Exception $e) {
                        $e->getMessage();
                    }
                    //        echo "<pre>";print_r($upload->getFileName('logo'));exit;
                    $upload->setOptions(array('useByteString' => false));
					$object->setCompany(stripslashes($options['company']));
                    $object->setStreet(stripslashes($options['street']));
                    $object->setZipcode($options['zipcode']);
                    $object->setCity(stripslashes($options['city']));
                    
                    $object->setLanguage($options['language']);
                    $object->setOfficeHours(stripslashes($options['office_hours']));
                    $object->setState($options['state']);
					//save area info to autocomplete table
					$db = Zend_Registry::get("db");
					$sql = "SELECT name FROM autocomplete WHERE name='".$options['state']."'";
					$result = $db->fetchAll($sql);
					if(empty($result)) {
					$result = $db->insert('autocomplete', array('name'=>$options['state']));
					}
					$sql = "SELECT name FROM autocomplete WHERE name='".$options['city']."'";
					$result = $db->fetchAll($sql);
					if(empty($result)) {
					$result = $db->insert('autocomplete', array('name'=>$options['city']));
					}
					$sql = "SELECT name FROM autocomplete WHERE name='".$options['area']."'";
					$result = $db->fetchAll($sql);
					if(empty($result)) {
					$result = $db->insert('autocomplete', array('name'=>$options['area']));
										}
					// end of autocomplete insertion
                    $object->save();
                    $modelDoctorReasonForVisit->delete("doctor_id={$object->getId()}");
                    if (@count($options['doctor_reason_for_visit']) > 0) {
                        $modelDoctorReasonForVisit = new Application_Model_DoctorReasonForVisit();
                        foreach ($options['doctor_reason_for_visit'] as $key => $value) {
                            if ($value != 0) {
                                $modelDoctorReasonForVisit->setDoctorId($object->getId());
                                $modelDoctorReasonForVisit->setReasonId($value);
                                $modelDoctorReasonForVisit->save();
                            }
                        }
                    }
					
					//assistants
					$modelDoctorAssistant = new Application_Model_DoctorAssistant();
                    $modelDoctorAssistant->delete("doctor_id={$object->getId()}");
                    if (@count($options['doctor_assistant']) > 0) {
                      foreach ($options['doctor_assistant'] as $key => $value) {
                            if ($value != 0) {
                                $modelDoctorAssistant->setDoctorId($object->getId());
                                $modelDoctorAssistant->setAssistantId($value);
                                $modelDoctorAssistant->save();
                            }
                        }
                    }
                    //$this->_helper->redirector('officeinfo', 'index', "user", Array('msg' => $msg, 'page' => $page));
                } else {
                    $model = new Application_Model_Doctor($options);
                    $model->save();
                }
                $mailOptions ['doctor_name'] = $object->getFname();
                $seoUrl = $this->view->seoUrl('/profile/index/id/'.$object->getId());
                $mailOptions ['doctor_url'] = Zend_Registry::get('siteurl').substr($seoUrl, 1,  strlen($seoUrl));
                $Mail = new Base_Mail('UTF-8');
                $Mail->doctorUpdateProfileMail($mailOptions);
                $this->_helper->redirector('officeinfo', 'index', "user", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }
        $this->view->form = $form;
        $this->view->msg = base64_decode(urldecode($this->_getParam('msg', '')));
    }
    public function paymentAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $id = $usersNs->userId;
        $form = new User_Form_Payment();
        $form->setAttrib("enctype", "multipart/form-data");
        $form->getElement('doctor_insurance2')->setRegisterInArrayValidator(false);
        $form->getElement('doctor_insurance')->setRegisterInArrayValidator(false);
		
		
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->fetchRow("user_id = {$id}");
            $options['id'] = $id;
            $options['user_id'] = $object->getUserId();
            
        }
        $request = $this->getRequest();
        $options = $request->getPost();
        if ($request->isPost()) {
		  /*
          * This is done to remove the zend form error for doctor_plan input
          * the data actually selected and set from the javascript not from zend form object
          *
          */
            
            if ($form->isValid($options)) {
                $msg = urlencode(base64_encode($this->view->lang[546]));
                if (0 < (int) $id) {
                    $options['id'] = $id;
                    
                    $modelDoctorInsurance = new Application_Model_DoctorInsurance();
                    $modelDoctorInsurance->delete("doctor_id={$object->getId()}");
                    if (@count($options['doctor_insurance']) > 0) {
                        foreach ($options['doctor_insurance'] as $key => $value) {
                            if ($value != 0) {
                                $modelDoctorInsurance->setDoctorId($object->getId());
                                $modelDoctorInsurance->setInsuranceId($value);
                                $modelDoctorInsurance->save();
                            }
                        }
                    }
                    $object->save();                    
                } else {
                    $model = new Application_Model_Doctor($options);
                    $model->save();
                }
                $mailOptions ['doctor_name'] = $object->getFname();
                $seoUrl = $this->view->seoUrl('/profile/index/id/'.$object->getId());
                $mailOptions ['doctor_url'] = Zend_Registry::get('siteurl').substr($seoUrl, 1,  strlen($seoUrl));
                $Mail = new Base_Mail('UTF-8');
                $this->_helper->redirector('payment', 'index', "user", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }
        $this->view->form = $form;
        $this->view->msg = base64_decode(urldecode($this->_getParam('msg', '')));
    }
    public function personalAction() {
        $usersNs = new Zend_Session_Namespace("members");
        $path = "images/doctor_image/";
        $id = $usersNs->userId;
        $form = new User_Form_Personal();
        $form->setAttrib("enctype", "multipart/form-data");
        $form->getElement('doctor_affiliation2')->setRegisterInArrayValidator(false);
        $form->getElement('doctor_affiliation')->setRegisterInArrayValidator(false);
        $form->getElement('doctor_association2')->setRegisterInArrayValidator(false);
        $form->getElement('doctor_association')->setRegisterInArrayValidator(false);
        $form->getElement('category_id')->setRegisterInArrayValidator(false);
       
        $elements = $form->getElements();
        $form->clearDecorators();
        foreach ($elements as $element) {
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->fetchRow("user_id = {$id}");
            $docCategory = new Application_Model_DoctorCategory();
			$selectedcategory = $docCategory->getDoctorCategories("doctor_id={$object->getId()}", null, 1);
			if (empty($selectedcategory))
				$selectedcategory = 0;
			$onjdocCategory = $docCategory->getDoctorCategories("doctor_id={$object->getId()}");
			$form->getElement('category_id')->setMultiOptions($onjdocCategory);
			
			$category = new Application_Model_Category();
			$arrallcategory = $category->getCategories("id not in ({$selectedcategory})");
            $form->getElement('category_id2')->setMultiOptions($arrallcategory);
            $company_logo = $object->getCompanyLogo();
            if (!empty($company_logo) && file_exists($path . $company_logo))
                $this->view->doctor_headshot = "/" . $path . $company_logo;
            else
                $this->view->doctor_headshot = "";
            $this->view->id = $object->getId();
            $this->view->defaultAffiliateState = "AL";
            $options['id'] = $id;
            $options['user_id'] = $object->getUserId();
            $options['text_award'] = stripcslashes($object->getTextAward());
			$options['fname'] = stripslashes($object->getFname());
			$options['specialty_title'] = stripslashes($object->getSpecialtyTitle());
			$options['about'] = stripslashes($object->getAbout());
			$options['text_award'] = stripslashes($object->getTextAward());
            $options['education'] = stripcslashes($object->getEducation());
            $options['category_id'] = stripcslashes($object->getCategoryId());
            $modeldoctor_association = new Application_Model_DoctorAssociation();
            $modAssoc = new Application_Model_Association();
            $ArrDoctorAssociation = $modeldoctor_association->getDoctorAssociationForDoctorEdit("doctor_id={$object->getId()} ");
            $selectedassoc = $modeldoctor_association->getDoctorAssociationForDoctorEdit("doctor_id={$object->getId()}", null, 1);
            if (empty($selectedassoc))
                $selectedassoc = 0;
            $docCategory = new Application_Model_DoctorCategory();
            $selectedcategory = $docCategory->getDoctorCategories("doctor_id={$object->getId()}", null, 1);
            if (empty($selectedcategory))
                $selectedcategory = 0;
            $ArrallDoctorAssociation = $modAssoc->getAssociations("id not in ({$selectedassoc}) AND category_id in ({$selectedcategory})");
            //For all association it should not conatain that is alredy selecte
            $form->getElement('doctor_association')->setMultiOptions($ArrDoctorAssociation);
            $form->getElement('doctor_association2')->setMultiOptions($ArrallDoctorAssociation);
            //Lets populate all the Hospital Affiliation
            if (isset($options['doctor_affiliation']))
                $DoctorHospitalAffiliationStr = implode(",", $options['doctor_affiliation']);
            else
                $DoctorHospitalAffiliationStr='';
            if ($DoctorHospitalAffiliationStr == ''
                )$DoctorHospitalAffiliationStr = '0';
            $modelHA = new Application_Model_HospitalAffiliation();
            $arrDoctorHospitalAffiliation = $modelHA->getAllAffiliation("id IN ({$DoctorHospitalAffiliationStr})");
            $form->getElement('doctor_affiliation')->setMultiOptions($arrDoctorHospitalAffiliation);
            $form->reset();
            $form->populate($options);
            $form->populate($options);
        }
        $request = $this->getRequest();
        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {
				$string = $this->view->lang[546];
                $msg = base64_encode($string);
				$msg = urlencode($msg);
                if (0 < (int) $id) {
                    $options['id'] = $id;
                    $upload = new Zend_File_Transfer_Adapter_Http();
                    $upload->setDestination($path);
                    try {
                        $upload->receive();
                    } catch (Zend_File_Transfer_Exception $e) {
                        $e->getMessage();
                    }
                    $upload->setOptions(array('useByteString' => false));
                    $file_name = $upload->getFileName('company_logo');
                    if (!empty($file_name)) {
                        $imageArray = explode(".", $file_name);
                        $ext = strtolower($imageArray[count($imageArray) - 1]);
                        $target_file_name = "doc_" . time() . ".{$ext}";
                        $targetPath = $path . $target_file_name;
                        $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
                        $filterFileRename->filter($file_name);
                        /* ------------------ THUMB --------------------------- */
                        $image_name = $target_file_name;
                        $newImage = $path . $image_name;
                        $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
                        $thumb->resize(400, 234);
                        $thumb->save($newImage);
                        if (0 < (int) $id) {
                            $del_image = $path . $object->getCompanylogo();
                            if (file_exists($del_image)
                                )unlink($del_image);
                            $small_del_image = $path . "thumb1_" . $object->getCompanylogo();
                            ;
                            if (file_exists($small_del_image)
                                )unlink($small_del_image);
                            $object->setCompanylogo($image_name);
                        }else {
                            $options['company_logo'] = $image_name;
                        }
                        /* ------------------ END THUMB ------------------------ */
                    }
                    /* ------------------END COMPANY LOGO ------------------ */
                    $object->setEducation(stripslashes($options['education']));
                    $object->setTextAward(stripslashes($options['text_award']));
					$object->setSpecialtyTitle(stripslashes($options['specialty_title']));
					$object->setTextAward(stripslashes($options['text_award']));
					$object->setFname(stripslashes($options['fname']));
					$object->setAbout(stripslashes($options['about']));
					//$object->setCategoryId($options['category_id']);
                    $modeldoctor_association = new Application_Model_DoctorAssociation();
                    $modeldoctor_association->delete("doctor_id={$object->getId()}");
                    if (count($options['doctor_association']) > 0) {
                        foreach ($options['doctor_association'] as $key => $value) {
                            if ($value != 0) {
                                $modeldoctor_association->setDoctorId($object->getId());
                                $modeldoctor_association->setAssociationId($value);
                                $modeldoctor_association->save();
                            }
                        }
                    }
                    $modelDoctorHospitalAffiliation = new Application_Model_DoctorHospitalAffiliation();
                    $modelDoctorHospitalAffiliation->delete("doctor_id={$object->getId()}");
                    if (@count($options['doctor_affiliation']) > 0) {
                        foreach ($options['doctor_affiliation'] as $key => $value) {
                            if ($value != 0) {
                                $modelDoctorHospitalAffiliation->setDoctorId($object->getId());
                                $modelDoctorHospitalAffiliation->setAffiliationId($value);
                                $modelDoctorHospitalAffiliation->save();
                            }
                        }
                    }
                    $category_id = $this->_getParam("catid");
                    $this->view->category_id = $category_id;
                    $object->save();
                    if (@count($options['category_id']) > 0) {
                        $modelDoctorCat = new Application_Model_DoctorCategory();
                        $modelDoctorCat->delete("doctor_id={$object->getId()}");
                        
                        foreach ($options['category_id'] as $key => $value) {
                            if ($value != 0) {
                                $modelDoctorCat->setDoctorId($object->getId());
                                $modelDoctorCat->setCategoryId($value);
                                $modelDoctorCat->save();
                            }
                        }
                        
                    }
                } else {
                    $model = new Application_Model_Doctor($options);
                    $model->save();
                }
                $mailOptions ['doctor_name'] = $object->getFname();
                $seoUrl = $this->view->seoUrl('/profile/index/id/'.$object->getId());
                $mailOptions ['doctor_url'] = Zend_Registry::get('siteurl').substr($seoUrl, 1,  strlen($seoUrl));
                $Mail = new Base_Mail('UTF-8');
                $Mail->doctorUpdateProfileMail($mailOptions);
                $this->_helper->redirector('personal', 'index', "user", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }
        $modelDoctorHospitalAffiliation = new Application_Model_DoctorHospitalAffiliation();
        $arrall_affiliation = $modelDoctorHospitalAffiliation->getMyHospitalAffiliate("doctor_id={$object->getId()}");
        $form->getElement('doctor_affiliation')->setMultiOptions($arrall_affiliation);
        $this->view->form = $form;
        $this->view->msg = base64_decode(urldecode($this->_getParam('msg', '')));
    }
    public function hospitalaffiliateAction() {
        $this->_helper->layout->disableLayout();
        $state = $this->_getParam('val');
        $doctor_id = $this->_getParam('doctor_id');
        $model = new Application_Model_DoctorHospitalAffiliation();
        $arr_docAffiliate = $model->getMyHospitalAffiliate("doctor_id={$doctor_id}");
        if ($arr_docAffiliate) {
            $arkeys = array_keys($arr_docAffiliate);
            $str_affiliated_id = implode(",", $arkeys);
        }
        if (empty($str_affiliated_id))
            $str_affiliated_id = 0;
        $model = new Application_Model_HospitalAffiliation();
        $state_affiliation = $model->getAllAffiliation("state='{$state}' AND id not in($str_affiliated_id)");
        $this->view->affiliations = $state_affiliation;
    }
    public function timeslotAction() {
        
    }
    public function calendarMoveAction() {
        $calday = $this->_getParam('calday');
        $PHPCalendar = new Base_PHPCalendar();
        $PHPCalendar->initCalendar($calday);
        exit();
    }
    public function viewAppointmentAction() {
	
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
		$hours = $settings->settingValue('hours');
		if($hours) {
			$this->view->timeformat = "%I:%M %P";
		} else {
			$this->view->timeformat = "%H:%M";
		}
        $appid = $this->_getParam('appid');
        $tab = $this->_getParam('tab');
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();
        $object = $Appointment->fetchRow("id={$appid} AND doctor_id={$docObject->getId()} AND deleted!=1");
        $this->view->tab = $tab;
        $this->view->object = $object;
    }
    public function deleteAppointmentAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $appid = $this->_getParam('appid');
        $tab = $this->_getParam('tab');
        $type = $this->_getParam('type'); // 1- approve, 2-decline and 3-delete
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $User = new Application_Model_User();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();
        $object = $Appointment->fetchRow("id={$appid} AND doctor_id={$docObject->getId()}");
        if ($object) {
            $object->setDeleted(1);
            $object->save();
        
			$objDoctor = $Doctor->find($object->getDoctorId());
			$objUser = $User->find($objDoctor->getUserId());
			if (!empty($docObject)) {
				$options ['doctor'] = $docObject->getFname();
				$options ['office'] = $docObject->getCompany();
				$options ['phone'] = $docObject->getActualPhone();
				$options['address1'] = $docObject->getStreet() . "<br>" . $docObject->getCity() . ", " . $docObject->getCountry() . " " . $docObject->getZipcode();
				$options['address2'] = "";
			}
			$options['name'] = $object->getFname()." ".$object->getLname();
			$options['email'] = $objUser->getEmail();
			$options['page'] = $object->getAge();
			$options['pemail'] = $object->getEmail();                        
			$options['pStatus'] = $object->getPatientStatus();                        
			if ($object->getGender() == "m") {
				$options['pgender'] = "Male";
			} else {
				$options['pgender'] = "Female";
			}
			if ($object->getPatientStatus() == "e") {
				$options['pStatus'] = "Existing";
			} else {
				$options['pStatus'] = "New";
			}
			$options ['time'] = $object->getAppointmentTime();
			
			$options ['date'] = $object->getAppointmentDate();
					
       
			$options ['PTPhone'] = $object->getPhone();
			$Mail = new Base_Mail('UTF-8');
			
			$object->setCancelledBy(2);// 2 for doctor cancelled
			$Mail->sendAdministratorAppointmentCancelDoctorMail($options, 1);
			$Mail1 = new Base_Mail('UTF-8');
			$Mail1->sendCancelAppointmentAdminMailEnquiry($options);
		}
		
    	$this->_helper->redirector('index', 'index', "user");
    }
    public function confirmDeclineCancelAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $appid = $this->_getParam('appid');
        $tab = $this->_getParam('tab');
        $type = $this->_getParam('type'); // 1- approve, 2-decline and 3-delete
        $Calendar = new Zend_Session_Namespace("calendar");
        if ($Calendar->TODAY
            )$today = $Calendar->TODAY;
        else
            $today = time();
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $User = new Application_Model_User();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $Appointment = new Application_Model_Appointment();
        $object = $Appointment->fetchRow("id={$appid} AND doctor_id={$docObject->getId()}");
        if ($object) {
            switch ($type) {
                //case 2://Now Cancelling the appointment from doctors end
                
                case 1:
                case -1:
                case 2:
				case 3:
                    $objDoctor = $Doctor->find($object->getDoctorId());
                    $objUser = $User->find($objDoctor->getUserId());
                    if (!empty($docObject)) {
                        $options ['doctor'] = $docObject->getFname();
                        $options ['office'] = $docObject->getCompany();
                        $options ['phone'] = $docObject->getActualPhone();
                        $options['address1'] = $docObject->getStreet() . "<br>" . $docObject->getCity() . ", " . $docObject->getCountry() . " " . $docObject->getZipcode();
                        $options['address2'] = "";
                    }
                    if (!empty($objUser)) {
						
                        $options['name'] = $object->getFname()." ".$object->getLname();
						$options['email'] = $objUser->getEmail();
						$options['page'] = $object->getAge();
						$options['pemail'] = $object->getEmail();                        
						$options['pStatus'] = $object->getPatientStatus();                        
						if ($object->getGender() == "m") {
							$options['pgender'] = "Male";
						} else {
							$options['pgender'] = "Female";
						}
						if ($object->getPatientStatus() == "e") {
							$options['pStatus'] = "Existing";
						} else {
							$options['pStatus'] = "New";
						}
                        $options ['time'] = $object->getAppointmentTime();
                        $options ['date'] = $object->getAppointmentDate();
					 
       
                        $options ['PTPhone'] = $object->getPhone();
                    }
                    $Mail = new Base_Mail('UTF-8');
                    if ($type == 1) {
                       // $Mail->sendAdministratorAppointmentApprovalDoctorMail($options, "");
                        $Mail1 = new Base_Mail('UTF-8');
                        $Mail1->sendAdministratorAppointmentApprovalDoctorMail($options, "1");
                    } elseif ($type == -1) {
                        //$Mail->sendAdministratorAppointmentDeclineDoctorMail($options, "");
                    } elseif ($type == 2) {
                        $object->setCancelledBy(2);// 2 for doctor cancelled
                        $object->setDeleted(1);
                        //$Mail->sendAdministratorAppointmentDeclineDoctorMail($options, "");
                        $Mail1 = new Base_Mail('UTF-8');
                        $Mail1->sendAdministratorAppointmentDeclineDoctorMail($options, 1);
                    } else {
						$object->setCancelledBy(2);// 2 for doctor cancelled
						$object->setDeleted(1);
                        $Mail->sendAdministratorAppointmentCancelDoctorMail($options, 1);
                        $Mail1 = new Base_Mail('UTF-8');
                        $Mail1->sendCancelAppointmentAdminMailEnquiry($options);
					}
                    break;
            }
            $object->setApprove($type);
            $object->save();
        }
        $this->_helper->redirector('appointment', 'index', "user", Array('today' => $today, 'tab' => $tab));
    }
    public function createAppointmentAction() {
        $return = array();
        $return['err'] = 0;
        $name = $this->_getParam('name');
        $zipcode = $this->_getParam('zipcode');
        $phone = $this->_getParam('phone');
        $email = $this->_getParam('email');
        $age = $this->_getParam('age');
        $gender = $this->_getParam('gender');
        $status = $this->_getParam('status');
        $appointmentTime = $this->_getParam('appointment_time');
        $appointmentDate = $this->_getParam('appointment_date');
        $needs = $this->_getParam('needs');
        $reason = $this->_getParam('reason');
        $insuranceCompany = $this->_getParam('insurance_company');
        
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid = $docObject->getId();
        $Auth = new Base_Auth_Auth();
        $password = $Auth->passwordGenerator();
        $Appointment = new Application_Model_Appointment();
        $appObject = $Appointment->fetchRow("appointment_date='$appointmentDate' AND appointment_time='$appointmentTime' AND doctor_id='{$drid}'");
        if (!empty($appObject)) {
            $return['err'] = 1;
            $return['msg'] = "Appointment already booked for this time slot. \n Please book for another time slot";
            echo Zend_Json::encode($return);
            exit();
        }
        $userId = 0;
        $User = new Application_Model_User();
        $userObject = $User->fetchRow("email='{$email}'");
        if ($userObject
            )$userId = $userObject->getId();
        if (!$userId) {
            $User->setEmail($email);
            $User->setUsername($email);
            $User->setFirstName($name);
            $User->setLastName('');
            $User->setUserLevelId(3); // for patient
            $User->setSendEmail(1);
            $User->setLastVisitDate(time());
            $User->setStatus('active');
            $User->setPassword(md5($password));
            $userId = $User->save();
            if (!$userId) {
                $return['err'] = 1;
                $return['msg'] = "There is some error, you can't register yet.";
            } else {
                $Patient = new Application_Model_Patient();
                $Patient->setUserId($userId);
                $Patient->setName($name);
                $Patient->setZipcode($zipcode);
                $Patient->setAge($age);
                $Patient->setGender($gender);
                $Patient->setPhone($phone);
                $Patient->setLastUpdated(time());
                $Patient->setFirstLogin(0);
                $patientId = $Patient->save();
                if (!$patientId) {
                    $return['err'] = 1;
                    $return['msg'] = "You are not registered as patient, please contact to site administratot.";
                }
            }
        } else {
			$password = $this->view->lang[673];
		}
        if ($return['err'] == 1) {
            echo Zend_Json::encode($return);
            exit();
        }
        /* ------------------------Start Insert Appointment ------------------------------ */
        $Appointment->setUserId($userId);
        $Appointment->setFname($name);
        $Appointment->setZipcode($zipcode);
        $Appointment->setPhone($phone);
        $Appointment->setEmail($email);
        $Appointment->setAge($age);
        $Appointment->setGender($gender);
        $Appointment->setPatientStatus($status);
        $Appointment->setAppointmentDate($appointmentDate);
        $Appointment->setAppointmentTime($appointmentTime);
        $Appointment->setBookingDate(time());
        $Appointment->setDoctorId($drid);
        $Appointment->setReasonForVisit($reason);
        $Appointment->setNeeds($needs);
        $Appointment->setInsurance($insuranceCompany);
        $Appointment->setAppointmentType('0');
        $Appointment->setStatus(1);
        $appointmentId = $Appointment->save();
        /* ------------------------End Insert Appointment ------------------------------ */
        $DoctorPatient = new Application_Model_DoctorPatient();
		$docpat = $DoctorPatient->fetchRow("patient_id = ".$patientId." AND doctor_id =".$drid);
		if(!$docpat) { //doesn't exist, add the patient
			$DoctorPatient->setDoctorId($drid);
			$DoctorPatient->setPatientId($patientId);
			$DoctorPatient->save();
		}

        if (!$appointmentId) {
            $return['err'] = 1;
            $return['msg'] = "You are registered for this site, but your appointment is not posted on the site, Please contact to site administrator.";
        }
        /* ------------------------Start Appointment Email ------------------------------ */
        $options = array();
		$options['email'] = $email;
		$options['password'] = $password;
		$options['name'] = $name;
		$options['date'] = $appointmentDate;
		$options['time'] = $appointmentTime;
		$options['address1'] = $docObject->getStreet(). "<br>" . $docObject->getCity() . ", " . $docObject->getCountry() . " " . $docObject->getZipcode();
		$options['address2'] = "";
		$options['office'] = $docObject->getOffice();
		$options['phone'] = $docObject->getAssignPhone();
		$options['doctor'] = $docObject->getFname();
        $Mail = new Base_Mail('UTF-8');
//XXX should probably change emails here
        if ($status == 'n') {
            $Mail->sendNewPatientAppointmentMail($Appointment);
        } else {
            $Mail->sendPatientAppointmentMail($Appointment);
        }
        $AdminMail = new Base_Mail('UTF-8');
        $AdminMail->sendAdministratorAppointmentBookinhotmail($Appointment); // email to site administrator
        /* ------------------------End Appointment Email ------------------------------ */
        $return['app_id'] = $appointmentId;
        echo Zend_Json::encode($return);
        exit();
    }
    public function deleteimageAction() {
        $id = $this->_getParam("doctor_id");
        if (0 < (int) $id) {
            $model = new Application_Model_Doctor();
            $object = $model->find($id);
            $path = "images/doctor_image/";
            $del_image = $path . $object->getCompanylogo();
            if (file_exists($del_image)
                )unlink($del_image);
            $small_del_image = $path . "thumb1_" . $object->getCompanylogo();
            ;
            if (file_exists($small_del_image)
                )unlink($small_del_image);
            $object->setCompanylogo("");
            $object->save();
            die("pz wait");
        }
    }
    
	public function doctorReviewAction() {
		$settings = new Admin_Model_GlobalSettings();
		$model = new Application_Model_DoctorReview();
		$usersNs = new Zend_Session_Namespace("members");
		$where = null;

		$sort_val = $this->_getParam('sort', 1);
		//$sort = "id DESC";
                $sort = "added_on DESC";
		$this->view->sort = $sort_val;
		if($sort_val == "rating") {
			$sort = "vote DESC";
		}
		
		$adminModeration = $settings->settingValue('admin_moderation');
		if($adminModeration=="true") {
			$where = "admin_approved=1 AND doctor_id={$usersNs->doctorId}";
		} else {
			$where = "doctor_id={$usersNs->doctorId}";
		}
		$page_size = $settings->settingValue('pagination_size');
		$page = $this->_getParam('page', 1);
		$pageObj = new Base_Paginator();
		$paginator = $pageObj->fetchPageData($model, $page, $page_size, $where, $sort);
		$this->view->total = $pageObj->getTotalCount();
		$this->view->paginator = $paginator;
		$modeldoctorreview = new Application_Model_DoctorReview();
		$this->view->viewreviewobject = $modeldoctorreview;
		$this->view->doctorId=$usersNs->doctorId;
		$this->view->msg = base64_decode($this->_getParam('msg', ''));
		$reviewM = new Application_Model_DoctorReview();
		$object = $reviewM->fetchAll("doctor_id='{$usersNs->doctorId}'");
		// echo '<pre>';print_r($object);die;
		$this->view->totalreview=$object;
		/* Insert reply of doctor*/
		$request = $this->getRequest();
		$options = $request->getPost();
		// print_r($options);
		if ($request->isPost()) {
			$reviewN = new Application_Model_DoctorReview();
			$reviewN->setId($options['review_id']);
			$reviewN->setDocterReply($options['doctor_reply']);
			$reviewN->setUserId($options['user_id']);
			$reviewN->setDoctorId($options['doctor_id']);
			$reviewN->setReview($options['review']);
			$reviewN->setUsername($options['username']);
			$reviewN->setEmail($options['email']);
			$reviewN->setAddedOn($options['added_on']);
			$reviewN->setVote($options['vote']);
			$reviewN->setRecommendation($options['recommendation']);
			$reviewN->setBedside($options['bedside']);
			$reviewN->setWaittime($options['waittime']);
			$reviewN->setAppointmentId($options['appointment_id']);
			$reviewN->setStatus($options['status']);
			// echo '<pre>';print_r($reviewN);die;
			$reviewN->save();
			//echo "test";print_r($options);die;
			$Mail = new Base_Mail('UTF-8');
			$Mail->sendPatientReviewReplyNotification($options);

		}
		$msg= $this->_getParam("msg");
		
		if($msg) {
			$this->view->msg = base64_decode($msg);
		}
	}
		
	public function requestPatientReviewAction()
	{
				$msg="";
				$newUser = true;
				$request = $this->getRequest();
				$options = $request->getPost();
				if ($request->isPost()) {
					$User = new Application_Model_User();
					$Patient = new Application_Model_Patient();
					$patid = 0;
					$userId = 0;
					$options['new'] = false;
					$userv = $User->fetchRow("email='".$options['email']."'");
					if($userv) {
						//$msg = base64_encode("<div style='color:#EF422D;'>Patient Already Exist</div>");
						//$this->_helper->redirector('doctor-review', 'index', "user", Array('page' => '1', 'msg' => $msg));
						//print_r($userv);die;
						$userId = $userv->getId();
						$patient = $Patient->fetchRow("user_id = ".$userId);
						if($patient) {
							$patid = $patient->getId();
						} else {
							//error_log("existing user, but not patient");
						}
						$newUser = false;
					} else { //new user
						$options['new'] = true;
						$User->setEmail($options['email']);
						$User->setUsername($options['email']);
						$User->setFirstName($options['first_name']);
						$User->setLastName($options['last_name']);
						$User->setUserLevelId(3); // for patient
						$User->setSendEmail(1);
						$User->setLastVisitDate(time());
						$User->setStatus('active');
						$options['password'] = "doctors".rand(1000, 9999);
						$User->setPassword(md5($options['password']));
						$User->save();
						$user = $User->fetchRow("email = '".$options['email']."'");
						$userId = $user->getId();
						if ($userId) {
							$name = $options['first_name']." ".$options['last_name'];
							$message = $options['message'];
							$Patient->setName($name);
							$Patient->setUserId($userId);
							//$Patient->setPatientMessage($message);
							$Patient->setLastUpdated(time());
							$Patient->setInsuranceCompanyId(0);
							$Patient->setInsurancePlanId(0);
							$Patient->setGender("");
							$Patient->setPhone("");
							$Patient->setLastUpdated(time());
							$Patient->setMonthDob("");
							$Patient->setDateDob("");
							$Patient->setYearDob("");
							$Patient->setFirstLogin(0);
							
							//echo '<pre>';print_r($Patient);die;
							$patid = $Patient->save();

							$userNs = new Zend_Session_Namespace("members");
        					$userid = $userNs->userId;
        					$Doctor = new Application_Model_Doctor();
        					$doctor = $Doctor->fetchRow("user_id = ".$userid);
							$DoctorPatient = new Application_Model_DoctorPatient();
							$docpat = $DoctorPatient->fetchRow("patient_id = ".$patid." AND doctor_id =".$doctor->getId());
							if(!$docpat) { //doesn't exist, add the patient
								$DoctorPatient->setDoctorId($doctor->getId());
								$DoctorPatient->setPatientId($patid);
								$DoctorPatient->save();
							}
							$msg .= "<div style='color:#EF422D;'>Patient Created Successfully</div>";
						}
					}
					//if(!$newUser) {
						//Code to send Mail
						$options['patid'] = $patid;
						$options['userid'] = $userId;
						$Mail = new Base_Mail('UTF-8');
						$Mail->sendPatientRequestReview($options);
						$msg .= "<div style='color:#EF422D;'>Review Requested</div>";
					//}
				}
				$msg = base64_encode($msg);
				$this->_helper->redirector('doctor-review', 'index', "user", Array('msg' => $msg));
			}	

	public function remindPatientReviewAction() {
		$this->_helper->layout->disableLayout();
		$msg['error']=1;
		$request = $this->getRequest();
		$options = $request->getPost();
		if ($request->isPost()) {
			//error_log($options["appid"]);
			$Appointment= new Application_Model_Appointment();
			$appointment = $Appointment->find($options["appid"]);

			$Mail = new Base_Mail('UTF-8');
			$Mail->sendReviewReminder($appointment);
			$msg['message'] = "Review reminder sent";
			$msg['error'] = 0;
		}
		 echo Zend_Json::encode($msg);
        exit();
	}		
			
	
	/*Patient Review Action*/
	public function patientReviewAction() {
		$settings = new Admin_Model_GlobalSettings();
		$usersNs = new Zend_Session_Namespace("members");					
		$Patient = new Application_Model_Patient();
		//echo $usersNs->userId;die;

		$where = null;
		$adminModeration = $settings->settingValue('admin_moderation');
		if($adminModeration=="true") {
			$where = "admin_approved=1 AND user_id={$usersNs->userId}";
		} else {
			$where = "user_id={$usersNs->userId}";
		}
		$model = new Application_Model_DoctorReview();
		$page_size = $settings->settingValue('pagination_size');
		$page = $this->_getParam('page', 1);
		$pageObj = new Base_Paginator();

		$sort_val = $this->_getParam('sort', 1);
		$sort = "id DESC";
		$this->view->sort = $sort_val;
		if($sort_val == "rating") {
			$sort = "vote DESC";
		}

		$paginator = $pageObj->fetchPageData($model, $page, $page_size, $where, $sort);
		$this->view->total = $pageObj->getTotalCount();
		$this->view->paginator = $paginator;


		$reviewM = new Application_Model_DoctorReview();		
		$object = $reviewM->fetchAll("user_id='{$usersNs->userId}' AND vote = 5");	
		$this->view->starsCount5 = count($object);
		$object = $reviewM->fetchAll("user_id='{$usersNs->userId}' AND vote = 4");	
		$this->view->starsCount4 = count($object);
		$object = $reviewM->fetchAll("user_id='{$usersNs->userId}' AND vote = 3");	
		$this->view->starsCount3 = count($object);
		$object = $reviewM->fetchAll("user_id='{$usersNs->userId}' AND vote = 2");	
		$this->view->starsCount2 = count($object);
		$object = $reviewM->fetchAll("user_id='{$usersNs->userId}' AND vote = 1");	
		$this->view->starsCount1 = count($object);
		//echo "<pre>";print_r($object);echo $usersNs->userId;die;	
		$this->view->reviewdata=$object; 		

		$modeldoctorreview = new Application_Model_DoctorReview();
		$this->view->viewreviewobject = $modeldoctorreview;
	
	}
	 	
	 
     public function publishAction() {
        $ids = $this->_getParam('id');
        $page = $this->_getParam('page');
        $usersNs = new Zend_Session_Namespace("members");
        
        $idArray = explode(',', $ids);
        $model = new Application_Model_DoctorReview();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("id={$id} AND doctor_id='{$usersNs->doctorId}'");
            if($object){
                $object->setStatus('1');
                $object->save();
            }
        }
        $publish = base64_encode($this->view->lang[584]);
        $this->_helper->redirector('doctor-review', 'index', "user", Array('page' => $page, 'msg' => $publish));
    }
	
	public function featuredAction() {
        $ids = $this->_getParam('id');
        $page = $this->_getParam('page');
        $usersNs = new Zend_Session_Namespace("members");
        
        $idArray = explode(',', $ids);
        $model = new Application_Model_DoctorReview();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("id={$id} AND doctor_id='{$usersNs->doctorId}'");
            if($object){
                $object->setStatus('1');
				$object->setAdminApproved('1');
                $object->save();
            }
        }
        $publish = base64_encode($this->view->lang[584]);
        $this->_helper->redirector('doctor-review', 'index', "user", Array('page' => $page, 'msg' => $publish));
    }
    public function unfeaturedAction() {
        $ids = $this->_getParam('id');
        $page = $this->_getParam('page');
        $usersNs = new Zend_Session_Namespace("members");
        
        $idArray = explode(',', $ids);
        $model = new Application_Model_DoctorReview();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("id={$id} AND doctor_id='{$usersNs->doctorId}'");
            if($object){
				$object->setAdminApproved('0');
                $object->save();
            }
        }
        $publish = base64_encode($this->view->lang[584]);
        $this->_helper->redirector('doctor-review', 'index', "user", Array('page' => $page, 'msg' => $publish));
    }
    public function unpublishAction() {
        $ids = $this->_getParam('id');
        $page = $this->_getParam('page');
        $usersNs = new Zend_Session_Namespace("members");
        $idArray = explode(',', $ids);
        $model = new Application_Model_DoctorReview();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("id={$id} AND doctor_id='{$usersNs->doctorId}'");
            if($object){
                $object->setStatus(0);
                $object->save();
            }
        }
        $publish = base64_encode($this->view->lang[583]);
        $this->_helper->redirector('doctor-review', 'index', "user", Array('page' => $page, 'msg' => $publish));
    }
    public function newAppointmentAction(){
        $usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid = $docObject->getId();
        $this->view->reasonforvisit = $Doctor->getReasonForVisit($drid);
        $this->view->app_time = $this->_getParam('time');
        $this->view->app_date = $this->_getParam('date');
        $User = new Application_Model_User();
        $this->view->months = $User->listAllMonths();
        $this->view->days = $User->listAllDates();
        $this->view->years = $User->listAllYear();
        $this->view->insurancedataArr = $Doctor->getInsuranceCompany();
        $this->view->drid = $drid;
        $this->view->date = $date;
        $this->view->time = $time;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $name = $this->_getParam('name');
            $surname = $this->_getParam('lastname');
            $zipcode = $this->_getParam('zipcode');
            $phone = $this->_getParam('phone');
            $email = $this->_getParam('email');
            $notes = $this->_getParam('notes');
            $year = $this->_getParam('year');
            $month = $this->_getParam('month');
            $day = $this->_getParam('day');
            $age = $this->birthday($year.'-'.$month.'-'.$day);
            $gender = $this->_getParam('gender');
            $status = 'e';
            $appointmentTime = $this->_getParam('appointment_time');
            $appointmentDate = $this->_getParam('appointment_date');
            $needs = $this->_getParam('needs');
            $reason = $this->_getParam('reason_to_visit');
            $insuranceCompany = $this->_getParam('insurance_company');
            $paying = $this->_getParam('paying');
            $send_email = $this->_getParam('send_email');
            
            /******Validation *************/
            $this->view->name = $name;
            $this->view->surname = $surname;
            $this->view->zipcode = $zipcode;
            $this->view->phone = $phone;
            $this->view->email = $email;
            $this->view->notes = $notes;
            $this->view->year = $year;
            $this->view->month = $month;
            $this->view->day = $day;
            $this->view->gender = $gender;
            $this->view->needs = $needs;
            $this->view->reason_to_visit = $reason;
            $this->view->insuranceCompany = $insuranceCompany;
            $this->view->paying = $paying;
            $this->view->send_email = $send_email;
            
            if(trim($name) == '' || trim($surname) == ''){
                $return['err'] = 1;
                $return['msg'] = "Name and surname are required fields";
                $this->view->return = $return;
                return;
            }
            
            /*******Validation *********/
            
            
            $Auth = new Base_Auth_Auth();
            $password = $Auth->passwordGenerator();
            $Appointment = new Application_Model_Appointment();
            $appObject = $Appointment->fetchRow("appointment_date='$appointmentDate' AND appointment_time='$appointmentTime' AND doctor_id='{$drid}'");
            if (!empty($appObject)) {
                $return['err'] = 1;
                $return['msg'] = "Appointment already booked for this time slot. \n Please book for another time slot";
                $this->view->return = $return;
                return;
            }
            $userId = 0;
            $User = new Application_Model_User();
            $userObject = $User->fetchRow("email='{$email}'");
            if ($userObject
                )$userId = $userObject->getId();
            if (!$userId) {
				$status = 'n';
                $User->setEmail($email);
                $User->setUsername($email);
                $User->setFirstName($name);
                $User->setLastName($surname);
                $User->setUserLevelId(3); // for patient
                $User->setSendEmail(1);
                $User->setLastVisitDate(time());
                $User->setStatus('active');
                $User->setPassword(md5($password));
                $userId = $User->save();
                if (!$userId) {
                    $return['err'] = 1;
                    $return['msg'] = "There is some error, you can't register yet.";
                } else {
                    $Patient = new Application_Model_Patient();
                    $Patient->setUserId($userId);
                    $Patient->setName($name);
                    $Patient->setZipcode($zipcode);
                    $Patient->setAge($age);
                    $Patient->setMonthDob($month);
                    $Patient->setDateDob($day);
                    $Patient->setYearDob($year);
                    $Patient->setGender($gender);
                    $Patient->setPhone($phone);
                    $Patient->setLastUpdated(time());
                    $Patient->setFirstLogin(0);
					$Patient->setCommunicationViaPhone(1);
			        $Patient->setCommunicationViaText(1);
			        $Patient->setCommunicationViaEmail(1);
                    $patientId = $Patient->save();
                    if (!$patientId) {
                        $return['err'] = 1;
                        $return['msg'] = "You are not registered as patient, please contact to site administratot.";
                    }
                }
            }
            if ($return['err'] == 1) {
                $this->view->return = $return;
                return;
            }
            /* ------------------------Start Insert Appointment ------------------------------ */
            $Appointment->setUserId($userId);
            $Appointment->setFname($name);
            $Appointment->setLname($surname);
            $Appointment->setZipcode($zipcode);
            $Appointment->setPhone($phone);
            $Appointment->setEmail($email);
            $Appointment->setAge($age);
            $Appointment->setGender($gender);
            $Appointment->setPatientStatus($status);
            $Appointment->setAppointmentDate($appointmentDate);
            $Appointment->setAppointmentTime($appointmentTime);
            $Appointment->setBookingDate(time());
            $Appointment->setDoctorId($drid);
            $Appointment->setReasonForVisit($reason);
            $Appointment->setNeeds($needs);
            $Appointment->setFirstVisit(1);
            $Appointment->setInsurance($insuranceCompany);
            $Appointment->setAppointmentType('0');
            $Appointment->setMonthDob($month);
            $Appointment->setDateDob($day);
            $Appointment->setYearDob($year);
            $Appointment->setNotes($notes);
            $appointmentId = $Appointment->save();
            $Appointment1 = new Application_Model_Appointment();
            $appObject = $Appointment1->fetchRow("id='{$appointmentId}'");
            $appObject->setApprove(1);
            $appObject->save();
            /* ------------------------End Insert Appointment ------------------------------ */
            if (!$appointmentId) {
                $return['err'] = 1;
                $return['msg'] = "You are registered for this site, but your appointment is not posted on the site, Please contact to site administrator.";
            }
            /* ------------------------Start Appointment Email ------------------------------ */
            
            $options = array();
            $options['email'] = $email;
            $options['password'] = $password;
            $options['name'] = $name." ".$surname;
            $options['date'] = $appointmentDate;
            $fulldate = explode("-",$appointmentDate);
            $options['fulldate'] = $fulldate['2']."-".$fulldate["0"]."-".$fulldate["1"];
            $options['time'] = $appointmentTime;
            if($options["am"] == 1) {
				if($options["hour"]!=12) {
					$hours = $options["hour"]+12;
				} else {
					$hours = $options["hour"];	
				}
			} else {
				$hours = $options["hour"];
			}
			$time = $hours.":".$options["minutes"];
			$options['time'] = $options["hour"].":".$options["minutes"];
			if($options["am"] == 0) {
				$options['time'].=" am";
			} else {
				$options['time'].=" pm";
			}
            
			$options['address1'] = $docObject->getStreet(). "<br>" . $docObject->getCity() . ", " . $docObject->getCountry() . " " . $docObject->getZipcode();
			$options['address2'] = "";
            $options['doctor'] = $docObject->getFname();
            $options['drid'] = $docObject->getId();
            $Mail = new Base_Mail('UTF-8');
//	 $Mail->sendLongReminder($appObject);
           
 if ($status == 'n') {
                if($this->_getParam('send_email') == '1'){
                    $Mail->sendPatientAppointmentBookingRegistrationMail($appObject, $password);
                }
            } else {
                if($this->_getParam('send_email') == '1'){
                    $Mail->sendPatientAppointmentBookinhotmail($appObject);
                }
            }
            $Mail->sendLongReminder($appObject);
            //$AdminMail = new Base_Mail('UTF-8');
            //$AdminMail->sendAdministratorAppointmentBookinhotmail($appObject); // email to site administrator
            /* ------------------------End Appointment Email ------------------------------ */
            $return['app_id'] = $appointmentId;
            $this->view->return = $return;
            $this->_helper->redirector("appointment", "index", "user");
        }
    }
	//calculate years of age (input string: YYYY-MM-DD)
    private function birthday ($birthday){
        list($year,$month,$day) = explode("-",$birthday);
        $year_diff  = date("Y") - $year;
        $month_diff = date("m") - $month;
        $day_diff   = date("d") - $day;
        if ($day_diff < 0 || $month_diff < 0)
            $year_diff--;
        return $year_diff;
    }
	
	//To save the doctors tasks
	public function taskAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	    $usersNs   = new Zend_Session_Namespace("members");
        $Doctor    = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid 	   = $docObject->getId();
		$tasks     = $this->_getParam('task');
		$db 	   = Zend_Registry::get("db");
		$db->insert('doctor_tasks', array('doctor_id'=>$drid, 'doctor_task'=>$tasks));
		$id        = $db->lastInsertId();
		$sql 	   = "SELECT doctor_task FROM doctor_tasks WHERE id='".$id."'";
		$newtask   = $db->fetchRow($sql);
		$response  = "<div class='taskbox radius' id='taskbox'><div class='container-fluid'><div class='row'><div class='col-sm-1'><input name='' type='checkbox' value=''></div><div class='col-sm-10'>".$newtask->doctor_task."</div><div class='col-sm-1'><a href='javascript:void(0);' id='".$id."' onclick='delete_task(this.id);'><img src='/images/user/cross-btn.png' alt='Cross'></a></div></div></div></div>"; 
		echo $response;
	}
	
	/***********************************To delete the doctor's tasks start here***********************/ 
	public function deleteTaskAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	    $usersNs     = new Zend_Session_Namespace("members");
        $Doctor      = new Application_Model_Doctor();
        $doctorTask  = new Application_Model_DoctorTask();
		$docObject   = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid 	     = $docObject->getId();
		$taskid      = $this->_getParam('taskid');
		$doctorTask->delete("id={$taskid}");
	}
	/***********************************To delete the doctor's tasks end here****************************/
	
	
    //***********************************To get doctor's twitter stream***********************/ 
	public function twitterStreamAction(){			
		$this->_helper->layout->disableLayout();
		
		$settings = new Admin_Model_GlobalSettings();
		
		require_once(APPLICATION_PATH.'/../library/Base/Twitter.php');
		
		$oauth_token = $settings->settingValue('twitter_oauth_token');
		$oauth_token_secret = $settings->settingValue('twitter_oauth_token_secret');
		$consumer_key = $settings->settingValue('twitter_consumer_key');
		$consumer_secret = $settings->settingValue('twitter_consumer_secret');
		
		$usersNs   = new Zend_Session_Namespace("members");
        $Doctor    = new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
		$screen_name = $doctor->getTwittername();
		if(!$screen_name || strlen(trim($screen_name)) <= 0){
			$screen_name = 'doctors'; // per default show doctors stream
		}
				
		$tweet_count = $settings->settingValue('tweet_count');	
		if(!$tweet_count || strlen(trim($tweet_count)) <= 0){
			$tweet_count = 3;
		}
		
		$settings = array(
			'oauth_token' => $oauth_token,
			'oauth_token_secret' => $oauth_token_secret,
			'consumer_key' => $consumer_key,
			'consumer_secret' => $consumer_secret
		);
		
		$twitter = new Base_Twitter($settings);
		echo $twitter->getTwitterStream($screen_name, $tweet_count, false);		
		die;
    }
	
	
    //***********************************To get doctor's twitter follower count***********************/ 
	public function twitterFollowerAction(){			
		$this->_helper->layout->disableLayout();
		
		$settings = new Admin_Model_GlobalSettings();
		
		require_once(APPLICATION_PATH.'/../library/Base/Twitter.php');
		
		$oauth_token = $settings->settingValue('twitter_oauth_token');
		$oauth_token_secret = $settings->settingValue('twitter_oauth_token_secret');
		$consumer_key = $settings->settingValue('twitter_consumer_key');
		$consumer_secret = $settings->settingValue('twitter_consumer_secret');
		
		$usersNs   = new Zend_Session_Namespace("members");
        $Doctor    = new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
		$screen_name = $doctor->getTwittername();
		if(!$screen_name || strlen(trim($screen_name)) <= 0){
			$screen_name = 'doctors'; // per default show doctors stream
		}
		
		$settings = array(
			'oauth_token' => $oauth_token,
			'oauth_token_secret' => $oauth_token_secret,
			'consumer_key' => $consumer_key,
			'consumer_secret' => $consumer_secret
		);
		
		$twitter = new Base_Twitter($settings);
		echo $twitter->getTwitterFollowerCount($screen_name);
		die;
    }
	/***********************************to show the doctor referal step one*******************************/ 
						
	public function doctorReferalStep01Action() {
		$Category 	= new Application_Model_Category(); //To fetch all the categories of a doctor
		$categoryObject = $Category->fetchAll();
		$this->view->categoryObject = $categoryObject;
		
		$usersNs   = new Zend_Session_Namespace("members");
        $Doctor    = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid 	   = $docObject->getId();
		
		$Referral  = new Application_Model_DoctorDoctorReffral(); //
		$where 	   = "doctor_id_to ={$drid}";
		$referralObject = $Referral->fetchAll($where, "appointment_id DESC");
		$this->view->refferald=$referralObject;
	}
	
	public function doctorReferalStep02Action() {
		
		$result = array();
		
		$catId = $this->_getParam('speciality');
		$zipcode   = $this->_getParam('zip_code');
		$lat   = $this->_getParam('lat');
		$lon   = $this->_getParam('lon');
				
		$db = Zend_Registry::get('db');
		
		$usersNs = new Zend_Session_Namespace("members");
		$result = $this->radiusSearch($lat, $lon, $catId, $usersNs->userId);
		$this->view->result = $result;
		$this->_helper->viewRenderer('index/doctor-referal-step02', null, true);
	}
	
	public function doctorReferalStep03Action() {
		
		$doctorid = $this->_getParam('id');
		//error_log("doctorid ".$doctorid);
		$patient  = $this->_getParam('patient');
			
		if($patient!="") {
			$usersNs   = new Zend_Session_Namespace("members");
			$Doctor    = new Application_Model_Doctor();
			$docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
			$db = Zend_Registry::get('db');
			$query= "SELECT DISTINCT name, zipcode, user_id FROM patients";
			$where =" WHERE id IN (SELECT patient_id FROM doctor_patient WHERE doctor_id = ".$docObject->getId().") AND ( patients.name Like '%$patient%')";
			//error_log($query.$where);
			$queryByMembership = $query.$where." ORDER BY name ASC";
			$select  = $db->query($queryByMembership); 
			$patient = $select->fetchAll();
			$this->view->patient = $patient;
		}
		
		/*Time Slot*/
		//error_log($doctorid);
		$Doctor = new Application_Model_Doctor();
        $profileobject = $Doctor->find($doctorid);
        $this->view->profiledata = $profileobject;
        $profileImage = $profileobject->getImage();
        $this->view->profileImage = $profileImage;
		/*Time Slot*/
		
		
		$this->view->result = $doctorid;
		$this->_helper->viewRenderer('index/doctor-referal-step03', null, true);
	}	
	
	
	
	public function doctorReferalStep04Action() {
		
		$doctorid = $this->_getParam('id');
		//echo $doctorid;
		$patient  = $this->_getParam('patient');
		 $request = $this->getRequest();
		 $usersNs   = new Zend_Session_Namespace("members");
        $Doctor    = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
        $drid 	   = $docObject->getId();		
		$docObjectd = $Doctor->fetchRow("id=".$doctorid);		
		
		$this->view->doctordetail=$docObjectd;
        $options = $request->getPost();		
		
		/*------------------------Start Insert Appointment ------------------------------*/
		 if ($request->isPost()) {
			 
			$User = new Application_Model_User();
			$user = $User->fetchRow("id=".$options['patient_id']);
			$Patient = new Application_Model_Patient();
			$patientu = $Patient->fetchRow("user_id=".$options['patient_id']);
			// echo '<pre>';print_r($options);die;
			$age = $User->getAge(array('month'=>$patientu->monthDob,'day'=>$patientu->dateDob,'year'=>$patientu->yearDob));
			$datetime=explode("-",$options['patient_slot']);
			$appdate=$datetime['0'].'-'.$datetime['1'].'-'.$datetime['2'];
			$timeToStore = date("H:i", strtotime($datetime['3']));
		
		
       $Appointment = new Application_Model_Appointment();
	   
        $Appointment->setUserId($options['patient_id']);
        $Appointment->setFname($user->firstName);
		$Appointment->setLname($user->lastName);
        $Appointment->setZipcode($patientu->zipcode);
        $Appointment->setPhone($patientu->phone);
        $Appointment->setEmail($user->email);
        $Appointment->setAge($age);
        $Appointment->setGender($patientu->gender);
        $Appointment->setFirstVisit('1');
        $Appointment->setPatientStatus('n');
        $Appointment->setAppointmentDate($appdate);	
        $Appointment->setAppointmentTime($timeToStore);
        $Appointment->setBookingDate(time());
        $Appointment->setDoctorId($doctorid);
        $Appointment->setReasonForVisit('0');
        $Appointment->setNeeds('others reason');
        $Appointment->setInsurance($patientu->insuranceCompanyId);
        $Appointment->setPlan($patientu->insurancePlanId);
        $Appointment->setMonthDob($patientu->monthDob);
        $Appointment->setDateDob($patientu->dateDob);
        $Appointment->setYearDob($patientu->yearDob);
        $Appointment->setAppointmentType('1');
        $Appointment->setCancelledBy('0');
        $Appointment->setOnbehalf('0');
        $Appointment->setRescheduled(0);
        $appointmentId = $Appointment->save();
		//$insert_id = $this->db->getLastId();
		//echo $appointmentId;die;
		
		 $DoctorDoctorReffral = new Application_Model_DoctorDoctorReffral();
		 
		 $DoctorDoctorReffral->setDoctorIdFrom($drid);
		 $DoctorDoctorReffral->setDoctorIdTo($doctorid);
		 $DoctorDoctorReffral->setAppointmentId($appointmentId);
		 $DoctorDoctorReffral->setRefferalText($options['refferal-text']);
		 $DoctorDoctorReffral->setRefferedPatientId($options['patient_id']);
		 $DoctorDoctorReffral->setCreateTime(time());
		 $DoctorDoctorReffral->setUpdateTime(time());
		 $doc_reff=$DoctorDoctorReffral->save();
		//echo $doc_reff;die;
		 
		 }
		// $DoctorDoctorReffrald = new Application_Model_DoctorDoctorReffral();
		   $docref = $DoctorDoctorReffral->fetchRow("id=".$doc_reff);
		   $patientr = $Patient->fetchRow("user_id=".$docref->refferedPatientId);
		   $appointmentr = $Appointment->find($docref->appointmentId);
		//   echo '<pre>';print_r($appointmentr);die;
		   $this->view->patientr=$patientr;
		   $this->view->docref=$docref;
		   $this->view->appointmentr=$appointmentr;
		 //  $Appointment = new Application_Model_Appointment();
		//$lastInsertId = $this->getAdapter()->lastInsertId();
       // echo $lastInsertId;die;
        //$appointmentId = 1;
        /*------------------------End Insert Appointment ------------------------------*/

        /***** send email to doctor *****/
        $Mail = new Base_Mail('UTF-8');
		$sent = $Mail->sendReferralDoctor($appointmentId, $drid);
		/***** send email to patient *****/
        $Mail = new Base_Mail('UTF-8');
		$sent = $Mail->sendReferralPatient($appointmentId, $drid);

        $DoctorPatient = new Application_Model_DoctorPatient();
		$docpat = $DoctorPatient->fetchRow("patient_id = ".$patientu->getId()." AND doctor_id =".$drid);
		if(!$docpat) { //doesn't exist, add the patient
			$DoctorPatient->setDoctorId($drid);
			$DoctorPatient->setPatientId($patientu->getId());
			$DoctorPatient->save();
		}
	}	
	 //getsNotifications
    public function getNotificationsAction() {
    	$this->_helper->layout->disableLayout();
    	$userNs = new Zend_Session_Namespace("members");
        $userid = $userNs->userId;
        $notes= array();
        $Notification = new Application_Model_Notification();
        $notifications = $Notification->fetchAll("userid = ".$userid." AND active='1'");
        if($notifications) {
        	$i = 0;
        	foreach($notifications as $notification) {
        		$notes[$i]['link'] = $notification->getLink();
        		$notes[$i]['title'] = $notification->getTitle();
        		$notes[$i]['content'] = $notification->getContent();
        		$notes[$i]['published'] = $notification->getPublished();
        		$notes[$i]['id'] = $notification->getId();
        		$i++;
        	}        	
        }
        echo Zend_Json::encode($notes);
        exit();
    }
    public function readNotificationAction() {
    	$notificationId = $this->_getParam('notificationid');
    	$this->_helper->layout->disableLayout();
    	$Notification = new Application_Model_Notification();
    	$notification = $Notification->find($notificationId);
    	$message["error"] = 1;
    	if($notification) {
    		$notification->setActive(0);
    		$notification->save();
    		$message["error"] = 0;
    	}
    	echo Zend_Json::encode($message); 
    	exit();
    }
	
	
	/*Start Doctor Statictics*/
	
	public function doctorStaticticsAction(){
		
		$usersNs = new Zend_Session_Namespace("members");
        $Doctor = new Application_Model_Doctor();
        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
		$previous_week = strtotime("0 week +1 day");
		$start_week = strtotime("last sunday midnight",$previous_week);
		$end_week = strtotime("next saturday",$start_week);
		$start_week = date("Y-m-d",$start_week);
		$end_week = date("Y-m-d",$end_week);
		/*Refferal Section*/
		
		/*Current week refferal*/	
		$Doctorref = new Application_Model_DoctorDoctorReffral();
		$docobj = $Doctorref->fetchAll("doctor_id_to='".$docObject->getId()."' and DATE(FROM_UNIXTIME(create_time)) between '".$start_week."' and '".$end_week."'");
		
		if($docobj!=""){
			$this->view->refferal=count($docobj);
			}else{
			$this->view->refferal='00';	
		}
	  
	    
		/*Current week refferal*/
		/*Current Month Refferal*/
		$docmon = $Doctorref->fetchAll("doctor_id_to='".$docObject->getId()."' and month( DATE( FROM_UNIXTIME( create_time ) ) ) =".date('n'));	
		if($docmon){	
			$this->view->month=count($docmon);
		}else{
			$this->view->month='00';	
		}	
		/*Current Month Refferal*/
		
		/*Current Year Refferal*/
		$docyear = $Doctorref->fetchAll("doctor_id_to='".$docObject->getId()."' and year( DATE( FROM_UNIXTIME( create_time ) ) ) =".date('Y'));	
		if($docyear){	
			$this->view->year=count($docyear);
			}else{
			$this->view->year='00';	
		}	
		
		/*Current Year Refferal*/
		
		/*Total Refferal*/
		$totalR = $Doctorref->fetchAll("doctor_id_to='".$docObject->getId()."'");	
		if($totalR){
			$this->view->totalrefferal=count($totalR);
			}else{
			$this->view->totalrefferal='000';	
		}	
		
		/*Total Refferal*/
		
		
		
		/*Review Section*/
		
		/*Current week Review*/	
		$reviewM = new Application_Model_DoctorReview();	
		$docRW = $reviewM->fetchAll("doctor_id='".$docObject->getId()."' and DATE(FROM_UNIXTIME(added_on)) between '".$start_week."' and '".$end_week."'");
		
		if($docRW){
			$this->view->weekR=count($docRW);
		}else{
			$this->view->weekR='00';	
		}
	  
	    /*Current Week Review*/	
			
		$docRM = $reviewM->fetchAll("doctor_id='".$docObject->getId()."' and month(DATE(FROM_UNIXTIME(added_on)))=".date('n'));
		
		if($docRM){
			$this->view->monthr=count($docRM);
		}else{
			$this->view->monthr='00';	
		}
		
		
		/*Current Year Review*/	
			
		$docRY = $reviewM->fetchAll("doctor_id='".$docObject->getId()."' and year(DATE(FROM_UNIXTIME(added_on)))=".date('Y'));
		
		if($docRY){
			$this->view->yearr=count($docRY);
		}else{
			$this->view->yearr='00';	
		}
		
		/*Total Review*/	
			
		$totalr = $reviewM->fetchAll("doctor_id='".$docObject->getId()."'");
		
		if($totalr){
			$this->view->totalr=count($totalr);
		}else{
			$this->view->totalr='000';	
		}
		
		/*Patient Reffered*/
		$paientR = $Doctorref->fetchAll("doctor_id_from='".$docObject->getId()."'");	
	
		if($paientR){
			$this->view->paientReffered=count($paientR);
		}else{
			$this->view->paientReffered='000';	
		}	
			
		/********************To fetch the new appointments of a doctor*****************************/
		$date  = date('Y-m-d'); 
		$Appointment = new Application_Model_Appointment();
		$newappObject   = $Appointment->fetchAll("appointment_date>='$date' AND doctor_id='{$docObject->getId()}' AND approve=0");
		$this->view->newappObject = count($newappObject);
		
		/********************To fetch the upcoming appointments of a doctor*****************************/
		$upcomingappObject   = $Appointment->fetchAll("appointment_date>='$date' AND doctor_id='{$docObject->getId()}' AND approve=1");
		$this->view->upcomingappObject = $upcomingappObject;
		
		/********************To fetch the cancel appointments of a doctor*****************************/
		$cancelappObject   = $Appointment->fetchAll("appointment_date>='$date' AND doctor_id='{$docObject->getId()}' AND approve=2");
		$this->view->cancelappObject = count($cancelappObject);
		
		/*Fetch statictic value*/
		$patObject = new Application_Model_Goalstatic();
		$staticvalue = $patObject->fetchRow("doctor_id=".$docObject->getId());
		
		$this->view->statictics=$staticvalue;
		/*Appointment Currentweek*/
		
		
		$docAppCW = $Appointment->fetchAll("doctor_id='".$docObject->getId()."' and appointment_date between '".$start_week."' and '".$end_week."'");
		if($docAppCW){
			$this->view->appointmentWeekly=count($docAppCW);
		}else{
			$this->view->appointmentWeekly='00';	
		}
		
		
		/*Current Month Appointment*/	
			
		$docAppM = $Appointment->fetchAll("doctor_id='".$docObject->getId()."' and month(appointment_date)=".date('n'));
		
		if($docAppM){
			$this->view->AppointmentMonthly=count($docAppM);
		}else{
			$this->view->AppointmentMonthly='00';	
		}
		
		
		/*Current Year Appointment*/	
			
		$docAppY = $Appointment->fetchAll("doctor_id='".$docObject->getId()."' and year(appointment_date)=".date('Y'));
		
		if($docAppY){
			$this->view->AppointmentYearly=count($docAppY);
		}else{
			$this->view->AppointmentYearly='00';	
		}
		
		/*Total appointment*/	
			
		$totalApp = $Appointment->fetchAll("doctor_id='".$docObject->getId()."'");
		
		if($totalApp){
			$this->view->AppointmentTotal=count($totalApp);
		}else{
			$this->view->AppointmentTotal='000';	
		}
		
		/*Confirmed Appointment*/	
			
		$docApprove = $Appointment->fetchAll("doctor_id='".$docObject->getId()."' and approve=1");
		
		if($docApprove){
			$this->view->ApproveAppointment=count($docApprove);
		}else{
			$this->view->ApproveAppointment='000';	
		}
		
	}
	
	/*End Doctor Statictics*/
	
	/*Doctor can set goal*/
	
	public function goalAction()
	{
			$usersNs = new Zend_Session_Namespace("members");
			$Doctor = new Application_Model_Doctor();
			$docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
			
			$patObject = new Application_Model_Goalstatic();
			$staticvalue = $patObject->fetchRow("doctor_id=".$docObject->getId());
		//	print_r($staticvalue);die;
			$this->view->statictics=$staticvalue;
		   
			 $request = $this->getRequest();
			 $options=$request->getPost();
		 if(!$staticvalue)
		 {
			if ($request->isPost()) {
				
				$patObject->setDoctorId($docObject->getId());	
				$patObject->setRefferalGoalWeekly($options['refferal_goal_weekly']);
				$patObject->setRefferalGoalMonthly($options['refferal_goal_monthly']);
				$patObject->setRefferalGoalYearly($options['refferal_goal_yearly']);
				$patObject->setReviewGoalWeekly($options['review_goal_weekly']);
				$patObject->setReviewGoalMonthly($options['review_goal_monthly']);
				$patObject->setReviewGoalYearly($options['review_goal_yearly']);
				$patObject->setAppointmentGoalWeekly($options['appointment_goal_weekly']);
				$patObject->setAppointmentGoalMonthly($options['appointment_goal_monthly']);
				$patObject->setAppointmentGoalYearly($options['appointment_goal_yearly']);
				$patObject->setDuebillsGoalWeekly($options['duebills_goal_weekly']);
				$patObject->setDuebillsGoalMonthly($options['duebills_goal_monthly']);
				$patObject->setDuebillsGoalYearly($options['duebills_goal_yearly']);
				$patObject->setCreateTime(time());
				$patObject->setUpdateTime(time());
			
				$patObject->save();
				$this->_helper->redirector('doctor-statictics', 'index', "user");
				
				}
		
		 }
		 
		 else
		 {
             if ($request->isPost()) {
				$patObject->setId($staticvalue->id);
				$patObject->setDoctorId($docObject->getId());	
				$patObject->setRefferalGoalWeekly($options['refferal_goal_weekly']);
				$patObject->setRefferalGoalMonthly($options['refferal_goal_monthly']);
				$patObject->setRefferalGoalYearly($options['refferal_goal_yearly']);
				$patObject->setReviewGoalWeekly($options['review_goal_weekly']);
				$patObject->setReviewGoalMonthly($options['review_goal_monthly']);
				$patObject->setReviewGoalYearly($options['review_goal_yearly']);
				$patObject->setAppointmentGoalWeekly($options['appointment_goal_weekly']);
				$patObject->setAppointmentGoalMonthly($options['appointment_goal_monthly']);
				$patObject->setAppointmentGoalYearly($options['appointment_goal_yearly']);
				$patObject->setDuebillsGoalWeekly($options['duebills_goal_weekly']);
				$patObject->setDuebillsGoalMonthly($options['duebills_goal_monthly']);
				$patObject->setDuebillsGoalYearly($options['duebills_goal_yearly']);
				$patObject->setCreateTime(time());
				$patObject->setUpdateTime(time());			
				$patObject->save();
				
				$this->_helper->redirector('doctor-statictics', 'index', "user");
			
				}
		 }
	}	
	/*Doctor can set goal*/
	
	private function getCoordinates($address){
 
		$address = str_replace(" ", "+", $address); // replace all the white space with "+" sign to match with google search pattern
		 
		$url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address=$address";
		 
		$response = file_get_contents($url);
		 
		$json = json_decode($response,TRUE); //generate array object from the response from the web
		 
		return ($json['results'][0]['geometry']['location']['lat'].",".$json['results'][0]['geometry']['location']['lng']);
		 
	}

	private function getTimezone($geocode) {
		$url = "https://maps.googleapis.com/maps/api/timezone/json?location=".$geocode."&timestamp=".time()."&sensor=false";		 
		$response = file_get_contents($url);		 
		$json = json_decode($response,TRUE); //generate array object from the response from the web		 
		$timezone = array('name'=> $json['timeZoneName'], "id"=>$json['timeZoneId']);
		return $timezone;
	}	
	
	
	public function myProfileAction() {
		$usersNs = new Zend_Session_Namespace("members");
		$path = "images/doctor_image/";
		$userid = $usersNs->userId;
		$form = new User_Form_MyProfile();
		$form->setAttrib("enctype", "multipart/form-data");
		$elements = $form->getElements();
		$form->clearDecorators();
		foreach ($elements as $element) {
			$element->removeDecorator('label');
			$element->removeDecorator('row');
			$element->removeDecorator('data');
		}
		if (0 < (int) $userid) {
			$this->view->defaultAffiliateState = "AL";
			$User = new Application_Model_User();
			$user = $User->find($userid);
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$this->view->doctor = $doctor;
			//$DoctorInsurance = new Application_Model_DoctorInsurance();
			$DoctorCategory = new Application_Model_DoctorCategory();
			$DoctorHospitalAffiliation = new Application_Model_DoctorHospitalAffiliation();
			//save start
			$request = $this->getRequest();
			if ($request->isPost()) {
				$user->setFirstName($this->_getParam('firstname'));
				$user->setLastName($this->_getParam('lastname'));
				$user->save();
				
				
				$doctor->setFname($this->_getParam('firstname')." ".$this->_getParam('lastname'));
				$doctor->setSpecialtyTitle($this->_getParam('specialty_title'));
				$doctor->setStreet($this->_getParam('street'));
				$doctor->setCity($this->_getParam('city'));
				$doctor->setState($this->_getParam('state'));
				$doctor->setZipcode($this->_getParam('zipcode'));
				$doctor->setAssignPhone($this->_getParam('phone'));
				$doctor->setActualPhone($this->_getParam('actualphone'));
				$doctor->setHospital($this->_getParam('hospital'));
				$doctor->setOfficeHours($this->_getParam('working_hours'));
				
				$geocode = $this->getCoordinates($this->_getParam('street')." ".$this->_getParam('city')." ".$this->_getParam('state')." ".$this->_getParam('zipcode'));
				$doctor->setGeocode($geocode);
				$doctor->setAbout($this->_getParam('about'));
				
				$languages = $this->_getParam('languages');
				$langs = implode(",", $languages);
				$doctor->setLanguage($langs);
				
				$educations = $this->_getParam('education');
				$education = implode(",", $educations);
				$doctor->setEducation($education);
				
				$certifications = $this->_getParam('certification');
				$certification = implode(",", $certifications);
				$doctor->setCertification($certification);

				$awards = $this->_getParam('text_award');
				$award = implode(",", $awards);
				$doctor->setTextAward($award);
				
				//error_log(print_r($this->getTimezone($geocode), true));
				$timezoneData = $this->getTimezone($geocode);
				$doctor->setTimezone($timezoneData["name"]);
				$doctor->setTimezoneId($timezoneData["id"]);
				//profile picture
				/* ------------------END COMPANY LOGO ------------------ */
				$upload = new Zend_File_Transfer_Adapter_Http();
				$path = "images/doctor_image/";
				$upload->setDestination($path);
				try {
				    $upload->receive();
				} catch (Zend_File_Transfer_Exception $e) {
				    $e->getMessage();
				}
				//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
				$upload->setOptions(array('useByteString' => false));
				$file_name = $upload->getFileName('company_logo');
				if (!empty($file_name)) {
				    $imageArray = explode(".", $file_name);
				    $ext = strtolower($imageArray[count($imageArray) - 1]);
				    $target_file_name = "doc_" . time() . ".{$ext}";
				    
				    $targetPath = $path . $target_file_name;
				    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
				    $filterFileRename->filter($file_name);
				    /* ------------------ THUMB --------------------------- */
				    $image_name = $target_file_name;
				    $newImage = $path . $image_name;
				    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
				    $thumb->resize(400, 234);
				    $thumb->save($newImage);
				    
				    if($doctor->getCompanylogo()) {
				        $del_image = $path . $doctor->getCompanylogo();			        
				        if (file_exists($del_image))unlink($del_image);
				        $small_del_image = $path ."thumb1_". $doctor->getCompanylogo();;
				        if (file_exists($small_del_image))unlink($small_del_image);
				    }
			        
			        $doctor->setCompanylogo($image_name);
				    /* ------------------ END THUMB ------------------------ */
				}
				/* ------------------END COMPANY LOGO ------------------ */
				/* insurances */
				/*$DoctorInsurance->delete("doctor_id = ".$doctor->getId());
				$selectedInsureances = $this->_getParam('insurance');
				foreach($selectedInsureances as $ins) {
					$insur = new Application_Model_DoctorInsurance();
					$insur->setDoctorId($doctor->getId());
					$insur->setInsuranceId($ins);
					$insur->save();
				}*/
				/* categories */
				$DoctorCategory->delete("doctor_id = ".$doctor->getId());
				$selectedCategories = $this->_getParam('category');				
				foreach($selectedCategories as $selectedcat) {
					$cat = new Application_Model_DoctorCategory();
					$cat->setDoctorId($doctor->getId());
					$cat->setCategoryId($selectedcat);
					$cat->save();
				}
				/* hospital affiliation */
				/*$DoctorHospitalAffiliation->delete("doctor_id = ".$doctor->getId());
				$selectedDoctorHospitalAffiliation = $this->_getParam('hospital');	
				if($selectedDoctorHospitalAffiliation) {	
					foreach($selectedDoctorHospitalAffiliation as $selectedHosp) {
						$hosp = new Application_Model_DoctorHospitalAffiliation();
						$hosp->setDoctorId($doctor->getId());
						$hosp->setAffiliationId($selectedHosp);
						$hosp->save();
					}
				}*/
				$doctor->save();

				$seoUrl = new Application_Model_SeoUrl();
				$seoUrl->delete("actual_url='/profile/index/id/". $doctor->getId()."'");
				$newSeourl = $seoUrl->retrieveSeoUrl('/profile/index/id/' . $doctor->getId());
				$doctor->setSeoUrl($newSeourl);
				$doctor->save();

			}
			//save end
			//populate form
			$this->view->doctor_headshot = $doctor->getImage();
			$this->view->id = $doctor->getId();
			$options['id'] = $id;
			$options['firstname'] = $user->getFirstName();
			$options['lastname'] = $user->getLastName();
			$options['specialty_title'] = $doctor->getSpecialtyTitle();
			$options['street'] = $doctor->getStreet();
			$options['city'] = $doctor->getCity();
			$options['state'] = $doctor->getState();
			$options['zipcode'] = $doctor->getZipcode();
			$options['phone'] = $doctor->getAssignPhone();
			$options['actualphone'] = $doctor->getActualPhone();
			$options['about'] = $doctor->getAbout();
			$options['timezone'] = $doctor->getTimezone();
			$options['hospital'] = $doctor->getHospital();
			$options['working_hours'] = $doctor->getOfficeHours();
			//$doctorInsurances = $DoctorInsurance->getDoctorinsuranceForDoctorEdit("doctor_id = ".$doctor->getId());
			//$Insurance = new Application_Model_InsuranceCompany();
			//$allInsuranceValues = $Insurance->getInsurancecompanies();
			//$form->buildRepeaterSelect($doctorInsurances, $allInsuranceValues, 'insurance');
			$doctorCategories = $DoctorCategory->getDoctorCategories("doctor_id = ".$doctor->getId());
			$Category = new Application_Model_Category();
			$allCategoryValues = $Category->getCategories();
			$form->buildRepeaterSelect($doctorCategories, $allCategoryValues, 'category');
			//hospital affiliation
            /*$doctorAffiliation = $DoctorHospitalAffiliation->getMyHospitalAffiliate("doctor_id={$doctor->getId()}");
            if(!$doctorAffiliation) {
            	$doctorAffiliation = array();
            }
            $HospitalAffiliation = new Application_Model_HospitalAffiliation();
            if($doctor->getState()) {
	            $allHospitalValues = $HospitalAffiliation->getAllAffiliation("state = '".$doctor->getState()."'");
	        } else {
	        	$allHospitalValues = array();
	        }
            $form->buildRepeaterSelect($doctorAffiliation, $allHospitalValues, 'hospital');*/
           
			$languages = $doctor->getLanguage();
			$langs = explode(",", $languages);
			$form->buildRepeater($langs, 'languages');
			$education = $doctor->getEducation();
			$educations = explode(",", $education);
			$form->buildRepeater($educations, 'education');
			$certification = $doctor->getCertification();
			$certifications = explode(",", $certification);
			$form->buildRepeater($certifications, 'certification');
			$award = $doctor->getTextAward();
			$awards = explode(",", $award);
			$form->buildRepeater($awards, 'text_award');
			$form->populate($options);
        }
        $this->view->form = $form;
        $this->view->msg = base64_decode(urldecode($this->_getParam('msg', '')));
    }
   

	 function uploadmediaAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	$Doctor = new Application_Model_Doctor();
		$doctor = $Doctor->fetchRow("user_id = {$userid}");
		$doctorId=$doctor->getId();
		//save start
		$request = $this->getRequest();
		if ($request->isPost()) {
			$doctor->setId($doctorId);
			
			/* ------------------END Audio file ------------------ */
			$upload = new Zend_File_Transfer_Adapter_Http();
			
			$path = "images/doctorvoice/";
			$upload->setDestination($path);
			try {
				$upload->receive();
			} catch (Zend_File_Transfer_Exception $e) {
				$e->getMessage();
			}
			//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
			$upload->setOptions(array('useByteString' => false));
			$file_name = $upload->getFileName('doctor_voice');
			if (!empty($file_name)) {
				$imageArray = explode(".", $file_name);
				$ext = strtolower($imageArray[count($imageArray) - 1]);
				
				$allowed =  array('mp3','wav','mpeg','wave','x-wav','aiff','x-aifc','x-aiff','x-gsm','gsm','ulaw');
				
				if(in_array($ext,$allowed)){
					$target_file_name = "media_" . time() . ".{$ext}";
					$targetPath = $path . $target_file_name;
					$filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
					$filterFileRename->filter($file_name);
					/* ------------------ THUMB --------------------------- */
					$image_name = $target_file_name;
				
					if($doctor->getDoctorVoice()) {
						$del_media = $path . $doctor->getDoctorVoice();			        
						if(file_exists($del_media))unlink($del_media);
					}
					
					$doctor->setDoctorVoice($image_name);
				}else{
					if($doctor->getDoctorVoice()) {
						$del_media = $path . $doctor->getDoctorVoice();			        
						if(file_exists($del_media))unlink($del_media);
					}
					$doctor->setDoctorVoice('');
				}
				/* ------------------ END THUMB ------------------------ */
			}else{
				if($doctor->getDoctorVoice()) {
					$del_media = $path . $doctor->getDoctorVoice();			        
					if(file_exists($del_media))unlink($del_media);
				}
				$doctor->setDoctorVoice('');
			}
			$doctor->saveDoctorVoice();
		}
		$params = array("drid"=>$doctorId);
		$this->_helper->redirector('master-slot', 'timeslot', "user",$params);
	}
    
	public function myProfileActionBkp1feb2016() {
		$usersNs = new Zend_Session_Namespace("members");
		$path = "images/doctor_image/";
		$userid = $usersNs->userId;
		$form = new User_Form_MyProfile();
		$form->setAttrib("enctype", "multipart/form-data");
		$elements = $form->getElements();
		$form->clearDecorators();
		foreach ($elements as $element) {
			$element->removeDecorator('label');
			$element->removeDecorator('row');
			$element->removeDecorator('data');
		}
		if (0 < (int) $userid) {
			$this->view->defaultAffiliateState = "AL";
			$User = new Application_Model_User();
			$user = $User->find($userid);
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$this->view->doctor = $doctor;
			//$DoctorInsurance = new Application_Model_DoctorInsurance();
			$DoctorCategory = new Application_Model_DoctorCategory();
			$DoctorHospitalAffiliation = new Application_Model_DoctorHospitalAffiliation();
			
			//save start
			$request = $this->getRequest();
			if ($request->isPost()) {
				$user->setFirstName($this->_getParam('firstname'));
				$user->setLastName($this->_getParam('lastname'));
				$user->save();
				
				
				$doctor->setFname($this->_getParam('firstname')." ".$this->_getParam('lastname'));
				$doctor->setSpecialtyTitle($this->_getParam('specialty_title'));
				$doctor->setStreet($this->_getParam('street'));
				$doctor->setCity($this->_getParam('city'));
				$doctor->setState($this->_getParam('state'));
				$doctor->setZipcode($this->_getParam('zipcode'));
				$doctor->setAssignPhone($this->_getParam('phone'));
				$doctor->setActualPhone($this->_getParam('actualphone'));
				$doctor->setHospital($this->_getParam('hospital'));
				$doctor->setOfficeHours($this->_getParam('working_hours'));
				
				$geocode = $this->getCoordinates($this->_getParam('street')." ".$this->_getParam('city')." ".$this->_getParam('state')." ".$this->_getParam('zipcode'));
				$doctor->setGeocode($geocode);
				$doctor->setAbout($this->_getParam('about'));
				
				$languages = $this->_getParam('languages');
				$langs = implode(",", $languages);
				$doctor->setLanguage($langs);
				
				$educations = $this->_getParam('education');
				$education = implode(",", $educations);
				$doctor->setEducation($education);
				
				$certifications = $this->_getParam('certification');
				$certification = implode(",", $certifications);
				$doctor->setCertification($certification);

				$awards = $this->_getParam('text_award');
				$award = implode(",", $awards);
				$doctor->setTextAward($award);
				
				//error_log(print_r($this->getTimezone($geocode), true));
				$timezoneData = $this->getTimezone($geocode);
				$doctor->setTimezone($timezoneData["name"]);
				$doctor->setTimezoneId($timezoneData["id"]);
				//profile picture
				/* ------------------END COMPANY LOGO ------------------ */
				$upload = new Zend_File_Transfer_Adapter_Http();
				$path = "images/doctor_image/";
				$upload->setDestination($path);
				try {
				    $upload->receive();
				} catch (Zend_File_Transfer_Exception $e) {
				    $e->getMessage();
				}
				//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
				$upload->setOptions(array('useByteString' => false));
				$file_name = $upload->getFileName('company_logo');
				if (!empty($file_name)) {
				    $imageArray = explode(".", $file_name);
				    $ext = strtolower($imageArray[count($imageArray) - 1]);
				    $target_file_name = "doc_" . time() . ".{$ext}";
				    
				    $targetPath = $path . $target_file_name;
				    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
				    $filterFileRename->filter($file_name);
				    /* ------------------ THUMB --------------------------- */
				    $image_name = $target_file_name;
				    $newImage = $path . $image_name;
				    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
				    $thumb->resize(400, 234);
				    $thumb->save($newImage);
				    
				    if($doctor->getCompanylogo()) {
				        $del_image = $path . $doctor->getCompanylogo();			        
				        if (file_exists($del_image))unlink($del_image);
				        $small_del_image = $path ."thumb1_". $doctor->getCompanylogo();;
				        if (file_exists($small_del_image))unlink($small_del_image);
				    }
			        
			        $doctor->setCompanylogo($image_name);
				    /* ------------------ END THUMB ------------------------ */
				}
				/* ------------------END COMPANY LOGO ------------------ */
				/* insurances */
				/*$DoctorInsurance->delete("doctor_id = ".$doctor->getId());
				$selectedInsureances = $this->_getParam('insurance');
				foreach($selectedInsureances as $ins) {
					$insur = new Application_Model_DoctorInsurance();
					$insur->setDoctorId($doctor->getId());
					$insur->setInsuranceId($ins);
					$insur->save();
				}*/
				/* categories */
				$DoctorCategory->delete("doctor_id = ".$doctor->getId());
				$selectedCategories = $this->_getParam('category');				
				foreach($selectedCategories as $selectedcat) {
					$cat = new Application_Model_DoctorCategory();
					$cat->setDoctorId($doctor->getId());
					$cat->setCategoryId($selectedcat);
					$cat->save();
				}
				/* hospital affiliation */
				/*$DoctorHospitalAffiliation->delete("doctor_id = ".$doctor->getId());
				$selectedDoctorHospitalAffiliation = $this->_getParam('hospital');	
				if($selectedDoctorHospitalAffiliation) {	
					foreach($selectedDoctorHospitalAffiliation as $selectedHosp) {
						$hosp = new Application_Model_DoctorHospitalAffiliation();
						$hosp->setDoctorId($doctor->getId());
						$hosp->setAffiliationId($selectedHosp);
						$hosp->save();
					}
				}*/
				$doctor->save();

				$seoUrl = new Application_Model_SeoUrl();
				$seoUrl->delete("actual_url='/profile/index/id/". $doctor->getId()."'");
				$newSeourl = $seoUrl->retrieveSeoUrl('/profile/index/id/' . $doctor->getId());
				$doctor->setSeoUrl($newSeourl);
				$doctor->save();

			}
			//save end
			//populate form
			$this->view->doctor_headshot = $doctor->getImage();
			$this->view->id = $doctor->getId();
			$options['id'] = $id;
			$options['firstname'] = $user->getFirstName();
			$options['lastname'] = $user->getLastName();
			$options['specialty_title'] = $doctor->getSpecialtyTitle();
			$options['street'] = $doctor->getStreet();
			$options['city'] = $doctor->getCity();
			$options['state'] = $doctor->getState();
			$options['zipcode'] = $doctor->getZipcode();
			$options['phone'] = $doctor->getAssignPhone();
			$options['actualphone'] = $doctor->getActualPhone();
			$options['about'] = $doctor->getAbout();
			$options['timezone'] = $doctor->getTimezone();
			$options['hospital'] = $doctor->getHospital();
			$options['working_hours'] = $doctor->getOfficeHours();
			//$doctorInsurances = $DoctorInsurance->getDoctorinsuranceForDoctorEdit("doctor_id = ".$doctor->getId());
			//$Insurance = new Application_Model_InsuranceCompany();
			//$allInsuranceValues = $Insurance->getInsurancecompanies();
			//$form->buildRepeaterSelect($doctorInsurances, $allInsuranceValues, 'insurance');
			$doctorCategories = $DoctorCategory->getDoctorCategories("doctor_id = ".$doctor->getId());
			$Category = new Application_Model_Category();
			$allCategoryValues = $Category->getCategories();
			$form->buildRepeaterSelect($doctorCategories, $allCategoryValues, 'category');
			//hospital affiliation
            /*$doctorAffiliation = $DoctorHospitalAffiliation->getMyHospitalAffiliate("doctor_id={$doctor->getId()}");
            if(!$doctorAffiliation) {
            	$doctorAffiliation = array();
            }
            $HospitalAffiliation = new Application_Model_HospitalAffiliation();
            if($doctor->getState()) {
	            $allHospitalValues = $HospitalAffiliation->getAllAffiliation("state = '".$doctor->getState()."'");
	        } else {
	        	$allHospitalValues = array();
	        }
            $form->buildRepeaterSelect($doctorAffiliation, $allHospitalValues, 'hospital');*/
           
			$languages = $doctor->getLanguage();
			$langs = explode(",", $languages);
			$form->buildRepeater($langs, 'languages');
			$education = $doctor->getEducation();
			$educations = explode(",", $education);
			$form->buildRepeater($educations, 'education');
			$certification = $doctor->getCertification();
			$certifications = explode(",", $certification);
			$form->buildRepeater($certifications, 'certification');
			$award = $doctor->getTextAward();
			$awards = explode(",", $award);
			$form->buildRepeater($awards, 'text_award');
			$form->populate($options);
        }
        $this->view->form = $form;
        $this->view->msg = base64_decode(urldecode($this->_getParam('msg', '')));
    }
    public function socialAction(){
    	$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	if (0 < (int) $userid) {
			$User = new Application_Model_User();
			$user = $User->find($userid);
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$this->view->doctor = $doctor;
			
			$this->view->doctorId = $doctor->getId(); 
			$profileImage = $doctor->getImage();
			$form = new User_Form_DoctorSocial();
			$elements = $form->getElements();
			$form->clearDecorators();
			foreach ($elements as $element) {
				$element->removeDecorator('label');
				$element->removeDecorator('row');
				$element->removeDecorator('data');
			}
			$options['twittername'] = $doctor->getTwittername();
			$options['facebookLikesPage'] = $doctor->getFacebookLikesPage();
			$options['googlePlusPage'] = $doctor->getGooglePlusPage();
			$options['yelpFollowupPage'] = $doctor->getYelpFollowupPage();
			$form->populate($options);
			$this->view->form = $form;
		}
    }
	public function saveSocialAction() {
		$this->_helper->layout->disableLayout();
        $usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	if (0 < (int) $userid) {
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$request = $this->getRequest();
			$options = $request->getPost();
			$messages = array();
			if($options['twittername']) {
				$doctor->setTwittername($options['twittername']);
				$messages[] = "Twitter saved successfully";
			}
			if($options['facebookLikesPage']) {
				$doctor->setFacebookLikesPage($options['facebookLikesPage']);
				$messages[] =  "Page (Facebook) saved successfully";
			}
			if($options['googlePlusPage']) {
				$doctor->setGooglePlusPage($options['googlePlusPage']);
				$messages[] =  "Page (Google+) saved successfully";
			}
			if($options['yelpFollowupPage']) {
				$doctor->setYelpFollowupPage($options['yelpFollowupPage']);
				$messages[] =  "Page (Yelp Followup) saved successfully";
			}
			$res = $doctor->save();
			$return["message"] = implode(', ', $messages);
		} else {
			$return["message"] = "Doctor not logged in";
		}
		echo Zend_Json::encode($return);
		exit;
    }
    
    public function patientListAction(){
    	$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	
    	if (0 < (int) $userid) {
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$Patient = new Application_Model_Patient();
			$DoctorPatient = new Application_Model_DoctorPatient();
			$this->view->selectedKeyword='';
			$this->view->selectedKeywordPagination='';

			$searchField = $this->_getParam('searchname');
			
			$keyword=$this->_getParam('key');
        
			if($keyword){
				$searchField="";
				$this->view->selectedKeyword=$keyword;
			}
			
			if($searchField){
				$this->view->selectedKeywordPagination=$searchField;
			}
			
			$db = Zend_Registry::get('db');
			if($searchField) {
				if(preg_match("/[a-zA-Z]/", $searchField)) {
					if(strpos($searchField, " ") !== false){
						$strWhere = "(CONCAT(u.first_name,' ',u.last_name)  LIKE '%".trim($searchField)."%') AND dp.doctor_id = ".$doctor->getId();
					}else{
						$strWhere = "(CONCAT(u.first_name,u.last_name,u.email)  LIKE '%".trim($searchField)."%') AND dp.doctor_id = ".$doctor->getId();
					};
				}else{
					$strWhere = "(CONCAT(REPLACE( REPLACE( REPLACE( REPLACE(p.phone,' ','' ) ,'(','' ) , ')', '' ) , '-', '' ),REPLACE( REPLACE( REPLACE( REPLACE(p.mobile,' ','' ) ,'(','' ) , ')', '' ) , '-', '' )) LIKE '".preg_replace("/[^0-9]/","",$searchField)."%') AND dp.doctor_id = ".$doctor->getId();
				}
				$select = $db->select()
                 ->distinct()
                ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
				->join(array('p' => 'patients'),
                        'p.id = dp.patient_id',
                         array('name'))
				->join(array('u' => 'user'),
                        'u.id = p.user_id',
                         array('email'))      
				  ->where($strWhere);
			}else if($keyword){
				$strWhere = "(p.name LIKE '".$keyword."%') AND dp.doctor_id = ".$doctor->getId();
				$select = $db->select()
                 ->distinct()
                ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
				->join(array('p' => 'patients'),
                        'p.id = dp.patient_id',
						array('name'))
				->where($strWhere);
			} else {
				$strWhere = "dp.doctor_id = ".$doctor->getId();
				$select = $db->select()
                 ->distinct()
                 ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
                 ->where($strWhere);

			//	$doctorPatients = $DoctorPatient->fetchAll("doctor_id=".$doctor->getId());
			}
			$stmt = $db->query($select);
			$result = $stmt->fetchAll();
			//echo '<pre>';print_r($result);die;
			$patients = array();
			if($result) {
				foreach($result as $doctorpatient) {
					//error_log(print_r($doctorpatient, true));
					$patient = $Patient->find($doctorpatient->patient_id);
					if($patient) {
						$patients[] = $patient;
					}
				}
			}
/*
			$Appointment = new Application_Model_Appointment();
			$appointments = $Appointment->fetchAll("doctor_id=".$doctor->getId());
			$patients = array();
			if($appointments) {
				foreach($appointments as $appointment) {
					$patient = $Patient->fetchRow("user_id = ".$appointment->getUserId());
					if($patient && !in_array($patient, $patients)) {
						$patients[] = $patient;
					}
				}
			}*/
			$settings = new Admin_Model_GlobalSettings();
			$page_size = $settings->settingValue('pagination_size');
			$page = $this->_getParam('page', 1);
			$pageObj = new Base_Paginator();
			$paginator = $pageObj->arrayPaginator($patients, $page, $page_size);
			//echo "<pre>";print_r($paginator);die;
			$this->view->total = $pageObj->getTotalCount();
			$this->view->paginator = $paginator;
			if($page*$page_size < $pageObj->getTotalCount()){
				$nextPage = intval($page)+1;
				$nextUrl = 'page='.$nextPage;
				$this->view->nextUrl = $nextUrl;
			}
			if($page!= 1){
				$prevPage = intval($page)-1;
				$prevUrl = 'page='.$prevPage;
				$this->view->prevUrl = $prevUrl;
			}
		
			$form = new User_Form_DoctorPatient();
	        $elements = $form->getElements();
	        $form->clearDecorators();
	        foreach ($elements as $element) {
	            $element->removeDecorator('label');
	            $element->removeDecorator('row');
	            $element->removeDecorator('data');
	        }
	        $this->view->form = $form;
		}
    }
	

    public function patientListActionBkp26May2016(){
    	$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	if (0 < (int) $userid) {
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$Patient = new Application_Model_Patient();
			$DoctorPatient = new Application_Model_DoctorPatient();
			$this->view->selectedKeyword='';

			$searchField = $this->_getParam('searchname');
			
			$keyword=$this->_getParam('key');
        
			if($keyword){
				$searchField="";
			}
			
			$this->view->selectedKeyword=$keyword;
			$db = Zend_Registry::get('db');
			if($searchField) {
				$strWhere = "(p.name LIKE '%".$searchField."%' OR REPLACE( REPLACE( REPLACE( REPLACE(phone,' ','' ) ,'(','' ) ,  ')',  '' ) ,  '-',  '' )='".preg_replace("/[^0-9]/","",$searchField)."' OR REPLACE( REPLACE( REPLACE( REPLACE(mobile,  ' ','' ) ,  '(',  '' ) ,  ')',  '' ) ,  '-',  '' )='".preg_replace("/[^0-9]/","",$searchField)."'  OR u.email LIKE '%".$searchField."%') AND dp.doctor_id = ".$doctor->getId();
				$select = $db->select()
                 ->distinct()
                ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
				->join(array('p' => 'patients'),
                        'p.id = dp.patient_id',
                         array('name'))
				->join(array('u' => 'user'),
                        'u.id = p.user_id',
                         array('email'))      
				  ->where($strWhere);
			}else if($keyword){
				$strWhere = "(p.name LIKE '".$keyword."%') AND dp.doctor_id = ".$doctor->getId();
				$select = $db->select()
                 ->distinct()
                ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
				->join(array('p' => 'patients'),
                        'p.id = dp.patient_id',
						array('name'))
				->where($strWhere);
			} else {
				$strWhere = "dp.doctor_id = ".$doctor->getId();
				$select = $db->select()
                 ->distinct()
                 ->from(array('dp' => 'doctor_patient'),
                 		array('dp.patient_id', 'dp.doctor_id'))
                 ->where($strWhere);

			//	$doctorPatients = $DoctorPatient->fetchAll("doctor_id=".$doctor->getId());
			}
			$stmt = $db->query($select);
			$result = $stmt->fetchAll();
			$patients = array();
			if($result) {
				foreach($result as $doctorpatient) {
					//error_log(print_r($doctorpatient, true));
					$patient = $Patient->find($doctorpatient->patient_id);
					if($patient) {
						$patients[] = $patient;
					}
				}
			}
/*
			$Appointment = new Application_Model_Appointment();
			$appointments = $Appointment->fetchAll("doctor_id=".$doctor->getId());
			$patients = array();
			if($appointments) {
				foreach($appointments as $appointment) {
					$patient = $Patient->fetchRow("user_id = ".$appointment->getUserId());
					if($patient && !in_array($patient, $patients)) {
						$patients[] = $patient;
					}
				}
			}*/
			$settings = new Admin_Model_GlobalSettings();
			$page_size = $settings->settingValue('pagination_size');
			$page = $this->_getParam('page', 1);
			$pageObj = new Base_Paginator();
			$paginator = $pageObj->arrayPaginator($patients, $page, $page_size);
			$this->view->total = $pageObj->getTotalCount();
			$this->view->paginator = $paginator;
			if($page*$page_size < $pageObj->getTotalCount()){
				$nextPage = intval($page)+1;
				$nextUrl = 'page='.$nextPage;
				$this->view->nextUrl = $nextUrl;
			}
			if($page!= 1){
				$prevPage = intval($page)-1;
				$prevUrl = 'page='.$prevPage;
				$this->view->prevUrl = $prevUrl;
			}

			$form = new User_Form_DoctorPatient();
	        $elements = $form->getElements();
	        $form->clearDecorators();
	        foreach ($elements as $element) {
	            $element->removeDecorator('label');
	            $element->removeDecorator('row');
	            $element->removeDecorator('data');
	        }
	        $this->view->form = $form;
		}
    }
	public function newPatientAppointmentAction() {	
		//date_default_timezone_set('America/Los_Angeles');
			

	    $this->_helper->layout->disableLayout();
        $usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
    	if(0 < (int) $userid){
			$Doctor = new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id = {$userid}");
			$request = $this->getRequest();
			$options = $request->getPost();

			//doctor did not give an email. An automated one should be generated.
			if($options['email'] == ""){ //create dummy unique email for empty emails
				$options['email'] = "patient".time().rand(1,1000)."@doctors.com";
				while(!$this->canCreateUser($options['email'])){
					$options['email'] = "patient".time().rand(1,1000)."@doctors.com";       
				}
			}
			
			if($options['email']) { 
				//what email should be sent
				$User = new Application_Model_User();
				$user = $User->fetchRow("email = '".$options['email']."'");
				$rehotmail = true;
				if($user) {
					$rehotmail = false;
				}
				$password = "doctors".rand(1000, 9999);
				$options['password'] = $password;
				$userid = $this->createPatient($options, $password);
				if($userid) {
					//error_log($options["hour"].":".$options["minutes"]." ".$options["am"] );
					if($options["am"] == 1) {
						if($options["hour"]!=12) {
							$hours = $options["hour"]+12;
						} else {
							$hours = $options["hour"];	
						}
					} else {
						$hours = $options["hour"];
					}
					$time = $hours.":".$options["minutes"];
					$options['time'] = $options["hour"].":".$options["minutes"];
					if($options["am"] == 0) {
						$options['time'].=" am";
					} else {
						$options['time'].=" pm";
					}
					//error_log($options['time']);
					//create the appointment
					$Appointment = new Application_Model_Appointment();
					$Appointment->setUserId($userid);
					$Appointment->setFname($options['name']);
					$Appointment->setLname($options['lastname']);
					$Appointment->setZipcode("");
					$Appointment->setPhone($options['phone']);
					$Appointment->setEmail($options['email']);
					$Appointment->setAge("");
					$Appointment->setGender("");
					$Appointment->setFirstVisit(0);
					$Appointment->setPatientStatus(1);
					$fulldate = explode("-",$options["fulldate"]);
					$Appointment->setAppointmentDate($fulldate['2']."-".$fulldate["0"]."-".$fulldate["1"]);	
					$Appointment->setAppointmentTime($time);
					$Appointment->setBookingDate(time());
					$Appointment->setDoctorId($doctor->getId());
					$Appointment->setReasonForVisit($options["procedure"]);
					$Appointment->setNeeds($options["procedure"]);
					$Appointment->setInsurance(0);
					$Appointment->setPlan(0);
					$Appointment->setMonthDob("");
					$Appointment->setDateDob("");
					$Appointment->setYearDob("");
					$Appointment->setAppointmentType('1');
					$Appointment->setCancelledBy('0');
					$Appointment->setOnbehalf(0);
                    $Appointment->setRescheduled(0);
					$appointmentId = $Appointment->save();

					$Appointment->setId($appointmentId);

					//set the approval. Needs to be done on existing appointment to accept this
					$Appointment->setApprove(1);
					$Appointment->save();
					
					$Patient = new Application_Model_Patient();
					$patient = $Patient->fetchRow("user_id = ".$userid);
					$options["patid"] = $patient->getId();
					$options["drid"] = $doctor->getId();
					//email
					$Mail = new Base_Mail('UTF-8');		    
					$options["uid"] = $userid;
  //                                     $Mail->sendLongReminder($Appointment);
//	
				if($rehotmail){ //new user
						$Mail->sendPatientAppointmentBookingRegistrationMail($Appointment, $password);
						$options['first_name'] = $options['name'];
						$options['last_name'] = $options['lastname'];
						$Mail->sendPatientMedicalHistoryMail($options);
					}else{ //existing user
						$Mail->sendPatientAppointmentBookinhotmail($Appointment);
					}
					$appointmentData = $Appointment->find($appointmentId);
					$Mail->sendLongReminder($appointmentData);
					
					//doctor patient
					$DoctorPatient = new Application_Model_DoctorPatient();
					$docpat = $DoctorPatient->fetchRow("patient_id = ".$patient->getId()." AND doctor_id =".$doctor->getId());
					if(!$docpat) { //doesn't exist, add the patient
						$DoctorPatient->setDoctorId($doctor->getId());
						$DoctorPatient->setPatientId($patient->getId());
						$DoctorPatient->save();
					}

					//add doctor to patient's favourites
					$Fav = new Application_Model_PatientFavoriteDoctor();
					$Fav->setDoctorId($doctor->getId());
					$Fav->setPatientId($patient->getId());
					$Fav->setFavoriteStatus("Favorite");
					$Fav->setCreateTime(time());
					$Fav->setUpdateTime(time());
					$Fav->save();

					$return["message"] = "ok";
				} else {
					//error_log("no email");
				}
			}else{
				$return["message"] = "patient not created nor found";
			}
		}else{
			$return["message"] = "doctor not logged in";
		}
		echo Zend_Json::encode($return);
		exit;
	}
	
	private function createPatient($options, $password=false){
		$User = new Application_Model_User();
		$email = $options["email"];
		if(true === $User->isExist("email='{$email}'")) {
			$user = $User->fetchRow("email='{$email}'");
			if($user->getUserLevelId() == 3) {
				return $user->getId();
			} else {
				return false;
			}
		}else{
			$email = $options["email"];
			$firstname = $options["name"];
			$lastname = $options["lastname"];
			$User->setEmail($email);
			$User->setUsername($email);
			$User->setFirstName($firstname);
			$User->setLastName($lastname);
			$User->setUserLevelId(3); // for patient
			$User->setSendEmail(1);
			$User->setLastVisitDate(time());
			$User->setStatus('active');
			if(!$password){
				$password = "doctors".rand(1000, 9999);
			}
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
				$Patient->setPhone($options["phone"]);
				$Patient->setMobile($options["mobile"]);
				$Patient->setInsuranceCompanyId("");
				$Patient->setMonthDob("");
				$Patient->setDateDob("");
				$Patient->setYearDob("");
				$Patient->setLastUpdated(time());
				$Patient->setFirstLogin(0);
				$Patient->setStreet($options["street"]);
				$Patient->setCity($options["city"]);
				$Patient->setZipcode($options["zipcode"]);
				$Patient->setState($options["state"]);
				$Patient->setCommunicationViaPhone(1);
				$Patient->setCommunicationViaText(1);
				$Patient->setCommunicationViaEmail(1);
				$patientId = $Patient->save();
				if(!$patientId){
					error_log("Problem with registration from Appointment. Code: RegErr2 ".$Patient);
					return false;
				} else {
					//connect with doctor
			        $usersNs = new Zend_Session_Namespace("members");
			    	$userid = $usersNs->userId;
			    	if (0 < (int) $userid) {
						$Doctor = new Application_Model_Doctor();
						$doctor = $Doctor->fetchRow("user_id = {$userid}");
						$DoctorPatient = new Application_Model_DoctorPatient();
						$docpat = $DoctorPatient->fetchRow("patient_id = ".$patientId." AND doctor_id =".$doctor->getId());
						if(!$docpat) { //doesn't exist, add the patient
							$DoctorPatient->setDoctorId($doctor->getId());
							$DoctorPatient->setPatientId($patientId);
							$DoctorPatient->save();
						}
					}
				}
			}
		}
		return $userId;
	}
	
	public function doctorPatientDetailsAction(){
		$patid = $this->getRequest()->getParam('patid');
		$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;    	

       $Doctor = new Application_Model_Doctor();

       $doctor = $Doctor->fetchRow("user_id = {$userid}");

        $docid=$doctor->getId() ;		

		$Patient = new Application_Model_Patient();
		$CommunicationHistory = new Application_Model_CommunicationHistory();
		$patient = $Patient->find($patid);
		$this->view->patient = $patient;
		$this->view->communicationHistory = array();
		if($patient) {
			$patient_user_id = trim($patient->getUserId());
			$db = Zend_Registry::get('db');
			$User = new Application_Model_User();
			$user = $User->find($patient->getUserId());
			$this->view->user = $user;
			$patientrecord= "SELECT count(*) as patient_count FROM referrals_patient WHERE  doctor_id=$docid and patients_id=$patid";
			$patientrecordno  = $db->query($patientrecord) ; 
			$this->view->patientrecordno1 =  $patientrecordno ;
			if($patient_user_id){
				$communication_history = $CommunicationHistory->fetchAll("(receiver_user_id={$patient_user_id} && sender_user_id={$userid}) || (receiver_user_id={$userid} && sender_user_id={$patient_user_id})","id DESC",10);
				$this->view->communicationHistory = $communication_history;
				
				$total_communication_history = $CommunicationHistory->fetchAll("(receiver_user_id={$patient_user_id} && sender_user_id={$userid}) || (receiver_user_id={$userid} && sender_user_id={$patient_user_id})");
				$this->view->totalCommunicationHistory = count($total_communication_history);
			}
	}
		$usersNs = new Zend_Session_Namespace("members");
    	$userid = $usersNs->userId;
            	
		$Doctor = new Application_Model_Doctor();
		$doctor = $Doctor->fetchRow("user_id = {$userid}");
		$Appointment = new Application_Model_Appointment();
		$date  = date('Y-m-d'); 
		$this->view->nextAppointment = $Appointment->fetchRow("appointment_date>='$date' AND doctor_id='{$doctor->getId()}' AND user_id=".$user->getId()." AND approve=1", "appointment_date ASC");
		$this->view->lastAppointment = $Appointment->fetchRow("appointment_date<='$date' AND doctor_id='{$doctor->getId()}' AND user_id=".$user->getId()." AND approve=1", "appointment_date ASC");
		$Review = new Application_Model_DoctorReview();
		$reviews = $Review->fetchAll("doctor_id = ".$doctor->getId()." AND user_id=".$user->getId());
		$this->view->reviews = $reviews;
		$form = new User_Form_DoctorPatient();
		$elements = $form->getElements();
		$form->clearDecorators();
		foreach ($elements as $element) {
			$element->removeDecorator('label');
			$element->removeDecorator('row');
			$element->removeDecorator('data');
		}
		$this->view->form = $form;
		
	}
	
	
	function loadMoreCommunicationHistoryAction(){
		$pstTimezone = new DateTimeZone('PST'); 
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$usersNs = new Zend_Session_Namespace("members");
		$userid = $usersNs->userId;
		$pid =  $this->_getParam('pid');
		$last_id =  $this->_getParam('last_id');
		$Doctor = new Application_Model_Doctor();
		$User = new Application_Model_User();
		$CommunicationHistory = new Application_Model_CommunicationHistory();
		
		$html='';
		if(is_numeric($last_id) && is_numeric($pid)){
			$communication_history = $CommunicationHistory->fetchAll("(receiver_user_id={$pid} && sender_user_id={$userid} AND id < {$last_id}) || (receiver_user_id={$userid} && sender_user_id={$pid} AND id < {$last_id})","id DESC",10);
			if(count($communication_history) > 0){
				foreach($communication_history as $history) { 
						
					$sender = $User->find(trim($history->getSenderUserId()));
					if($sender){
						$sender_name = $sender->getFirstName().' '.$sender->getLastName();
					}else{
						$sender_name = 'NA';
					}
					$receiver = $User->find(trim($history->getReceiverUserId()));
					if($receiver){
						$receiver_name = $receiver->getFirstName().' '.$receiver->getLastName();
					}else{
						$receiver_name = 'NA';
					}
					
					 $datetime = $history->getSentAt();
							
					 if(trim($history->getSentAt())=='UTC'){
						 $myDateTime = new DateTime($datetime, $pstTimezone);
						 $offset = $pstTimezone->getOffset($myDateTime);
						 $time = strtotime($datetime);
						 $time = $time + $offset;  //we use + instead of using - sign because it already returns with - offset
						 $converted_date = date("Y-m-d H:i:s", $time);
					 }else{
						 $converted_date = $datetime;
					 }	
					
					$content = $history->getMessage();
				    libxml_use_internal_errors(true);
					$dom = new DOMDocument();
					$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.$content);
					$dom->formatOutput = true;

					foreach($dom->getElementsByTagName('a') as $item){
						//Remove width attr if its there
						$item->removeAttribute('href');
						//Get the sytle attr if its there
						$style = $item->getAttribute('style');
						//Set style appending existing style if necessary, 123px could be your $width var
						$item->setAttribute('style','width:100px;'.$style);
					}
					
					//remove unwanted doctype ect
					$ret = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|head))[^>]*>\s*~i', '', $dom->saveHTML());
					$new_html =  trim(str_replace('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">','',$ret));
				
					$html .='<tr class="gradeX" id="'.trim($history->getId()).'" data-pid="'.trim($pid).'"><td>'.ucwords($sender_name).'</td><td>'.ucwords($receiver_name).'</td><td>'.$history->getType().'</td><td>'.date('F d, Y g:i A',strtotime($converted_date)).'</td><td class="center" style="text-align:center;"><button data-modal_id="'.trim($history->getId()).'" class="btn btn-info view_msg_info"><i class="fa fa-eye"></i> View</button></td></tr>';
				 }
				$key=1;
			}else{
				$key=2;
			}
		}else{
			$key=2;
		}	
		$result = array('key'=>$key,'html'=>$html);
		echo json_encode($result);
	}

	
	public function doctorQuestionsAction(){
		$Article = new Application_Model_Article();
        $object = $Article->find(21);
        $this->view->object = $object;
        $form = new User_Form_Support();
        $this->view->msg = "";
        $this->view->form = $form;
	}
	public function patientQuestionsAction(){
		$Article = new Application_Model_Article();
        $object = $Article->find(22);
        $this->view->object = $object;
        $form = new User_Form_Support();
        $this->view->msg = "";
        $this->view->form = $form;
	}
	public function contactSupportAction() {		
		$this->_helper->layout->disableLayout();
		$this->view->msg = "sending";
		$form = new User_Form_Support();
        $request = $this->getRequest();
        $options = $request->getParams();
		
		if ($form->isValid($request->getPost())) {
		    $Mail = new Base_Mail('UTF-8');
		    $sent = $Mail->sendSupport($options);
		    if($sent) {
		    	$this->view->msg = "Message was sent successfully. Thank you for contacting us, we will contact you soon.";
		    } else  {
		    	$this->view->msg = "Message was not sent. Please try again.";
		    }
		} else {
			$this->view->msg = "Message was not sent. Please check the fields.";
		}
	}
	
	public function patientInformationSteponeAction(){
		
		$id = $this->_getParam("id");
		
        $form=new Admin_Form_PatientMedicalHistory();
        $elements = $form->getElements();
		$usersNs = new Zend_Session_Namespace("members");
        $Patient_history = new Application_Model_PatientHistory();
	
        $patient_history = $Patient_history->fetchRow("patient_id='{$usersNs->userId}'");
		$User = new Application_Model_User();
		$userinfo = $User->fetchRow("id='{$usersNs->userId}'");
	//	echo $usersNs->userId;
		//echo '<pre>';print_r($userinfo);die;
		$this->view->userinfo=$userinfo;
		
        $form->clearDecorators();
        foreach ($elements as $element){
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
		
		$this->view->patient_history=$patient_history;
        $this->view->form = $form;
		
	}

  /*Patient history Step two*/

	     public function patientInformationSteptwoAction(){
			
				$request = $this->getRequest();
				$options = $request->getPost();
				$usersNs = new Zend_Session_Namespace("members");
				$physician_details = new Application_Model_PhysicianDetails();
				$physician_details = $physician_details->fetchRow("patient_id='{$usersNs->userId}'");
				$this->view->physician_details=$physician_details;
				$Patient_history = new Application_Model_PatientHistory();	
				$patient_history = $Patient_history->fetchRow("patient_id='{$usersNs->userId}'");
				
			
	     if ($request->isPost()) {
		
		//  $authNamespaceone->pHistoryOne
		   $authNamespaceone = new Zend_Session_Namespace('one');
		   $authNamespaceone->pHistoryOne=$options;
		   if($patient_history){
		  // echo '<pre>';print_r($options);die;
			$physician=$options['physician'];
			$month_dob=$options['month_dob'];
			$date_dob=$options['date_dob'];
			$year_dob=$options['year_dob'];
			$date_of_birth=$year_dob.'-'.$month_dob.'-'.$date_dob;
			$datevisit=$options['date_of_visit'];
				$datev=explode("-",$datevisit);
				//$date_dob=$authNamespaceone->pHistoryOne['date_dob'];
				//$year_dob=$authNamespaceone->pHistoryOne['year_dob'];
				$date_of_visit=$datev[2].'-'.$datev[1].'-'.$datev[0];
			$patient=$options['patient'];
			$address=$options['address'];
			$telephone_day=$options['telephone_day'];
			$telephone_evening=$options['telephone_evening'];
			$fax=$options['fax'];
			$email=$options['email'];
			$social_security_no=$options['social_security_no'];
			$birth_place=$options['birth_place'];
			$employed=$options['employed'];
			$retired=$options['retired'];
			$occupation=$options['occupation'];
			$self=$options['self'];
			$other_person=$options['other_person'];
			$maritalstatus=$options['maritalstatus'];
			$case_of_emergency=$options['case_of_emergency'];
			$contact_person_name=$options['contact_person_name'];
			$contact_person_address=$options['contact_person_address'];
			$contact_person_telephone_day=$options['contact_person_telephone_day'];
			$contact_person_telephone_evening=$options['contact_person_telephone_evening'];
			$relation_ship_to_you=$options['relation_ship_to_you'];
			
			$ids=$patient_history->id;
				$idArray = explode(',', $ids);
				$model = new Application_Model_PatientHistory();
				foreach ($idArray as $id) {
				
				$object = $model->fetchRow("id={$id}");
				if($object){
				$object->setPatientId($usersNs->userId);
				$object->setPhysicianName($physician);
				$object->setDateOfVisit($date_of_visit);
				$object->setPatientName($patient);
				$object->setPatientAddress($address);
				$object->setTelephoneDay($telephone_day);
				$object->setTelephoneEvening($telephone_evening);
				$object->setFax($fax);
				$object->setEmaiId($email);
				$object->setSocialSecurityNumber($social_security_no);
				$object->setDob($date_of_birth);
				$object->setBirthPlace($birth_place);
				$object->setEmployed($employed);
				$object->setRetired($retired);
				$object->setOccupation($occupation);
				$object->setSelf($self);
				$object->setOtherPerson($other_person);
				$object->setMaritalstatus($maritalstatus);
				$object->setCaseOfEmergency($case_of_emergency);
				$object->setContactPersonName($contact_person_name);
				$object->setContactPersonAddress($contact_person_address);
				$object->setContactPersonTelephoneDay($contact_person_telephone_day);
				$object->setContactPersonTelephoneEvening($contact_person_telephone_evening);
				$object->setRelationshipToYou($relation_ship_to_you);
				$object->setCreateTime(time());
				$object->setUpdateTime(time());
				
				$object->save();
				$msg="Step One data has been saved";
				$this->view->msg=$msg;
					}
				}
			
			}else
			{
			
				
				//echo '<pre>';print_r($authNamespaceone->pHistoryOne);die;
				$physician=$authNamespaceone->pHistoryOne['physician'];
				$datevisit=$authNamespaceone->pHistoryOne['date_of_visit'];
				$datev=explode("-",$datevisit);
				//$date_dob=$authNamespaceone->pHistoryOne['date_dob'];
				//$year_dob=$authNamespaceone->pHistoryOne['year_dob'];
				$date_of_visit=$datev[2].'-'.$datev[1].'-'.$datev[0];
				$patient=$authNamespaceone->pHistoryOne['patient'];
				$address=$authNamespaceone->pHistoryOne['address'];
				$telephone_day=$authNamespaceone->pHistoryOne['telephone_day'];
				$telephone_evening=$authNamespaceone->pHistoryOne['telephone_evening'];
				$fax=$authNamespaceone->pHistoryOne['fax'];
				$email=$authNamespaceone->pHistoryOne['email'];
				$social_security_no=$authNamespaceone->pHistoryOne['social_security_no'];
				$birth_place=$authNamespaceone->pHistoryOne['birth_place'];
				$employed=$authNamespaceone->pHistoryOne['employed'];
				$retired=$authNamespaceone->pHistoryOne['retired'];
				$occupation=$authNamespaceone->pHistoryOne['occupation'];
				$self=$authNamespaceone->pHistoryOne['self'];
				$other_person=$authNamespaceone->pHistoryOne['other_person'];
				$maritalstatus=$authNamespaceone->pHistoryOne['maritalstatus'];
				$case_of_emergency=$authNamespaceone->pHistoryOne['case_of_emergency'];
				$contact_person_name=$authNamespaceone->pHistoryOne['contact_person_name'];
				$contact_person_address=$authNamespaceone->pHistoryOne['contact_person_address'];
				$contact_person_telephone_day=$authNamespaceone->pHistoryOne['contact_person_telephone_day'];
				$contact_person_telephone_evening=$authNamespaceone->pHistoryOne['contact_person_telephone_evening'];
				$relation_ship_to_you=$authNamespaceone->pHistoryOne['relation_ship_to_you'];
				//echo "testashu";
					
				$patient_info_one = new Application_Model_PatientHistory();			
				$patient_info_one->setPatientId($usersNs->userId);
				$patient_info_one->setPhysicianName($physician);
				$patient_info_one->setDateOfVisit($date_of_visit);
				$patient_info_one->setPatientAddress($address);
				$patient_info_one->setPatientName($patient);
				$patient_info_one->setTelephoneDay($telephone_day);
				$patient_info_one->setTelephoneEvening($telephone_evening);
				$patient_info_one->setFax($fax);
				$patient_info_one->setEmaiId($email);
				$patient_info_one->setSocialSecurityNumber($social_security_no);
				$patient_info_one->setDob($date_of_visit);
				$patient_info_one->setBirthPlace($birth_place);
				$patient_info_one->setEmployed($employed);
				$patient_info_one->setRetired($retired);
				$patient_info_one->setOccupation($occupation);
				$patient_info_one->setSelf($self);
				$patient_info_one->setOtherPerson($other_person);
				$patient_info_one->setMaritalstatus($maritalstatus);
				$patient_info_one->setCaseOfEmergency($case_of_emergency);
				$patient_info_one->setContactPersonName($contact_person_name);
				$patient_info_one->setContactPersonAddress($contact_person_address);
				$patient_info_one->setContactPersonTelephoneDay($contact_person_telephone_day);
				$patient_info_one->setContactPersonTelephoneEvening($contact_person_telephone_evening);
				$patient_info_one->setRelationshipToYou($relation_ship_to_you);
				$patient_info_one->setCreateTime(time());
				$patient_info_one->setUpdateTime(time());
				
				$patient_info_one->save();
					
				
				
		
			
			}
			
		   
				
			}
		}
		
 /*Patient history step three*/
		
		public function patientInformationStepthreeAction(){
			
			$request = $this->getRequest();
			$options = $request->getPost();
			$usersNs = new Zend_Session_Namespace("members");
			$physician_detailss = new Application_Model_PhysicianDetails();
			$physician_details = $physician_detailss->fetchRow("patient_id='{$usersNs->userId}'");
		
			$this->view->physician_details=$physician_details;
		//	echo '<pre>';print_r($physician_details);die;
				
		if ($request->isPost()) {
			
			
			$authNamespace = new Zend_Session_Namespace('two');
			$authNamespace->pHistoryTwo=$options;
			if($physician_details)
			{
			
				foreach($options['physian'] as $physician)
				{
					if($physician){
						$Rphysician.=$physician.',';
					}
				}
				foreach($options['speciality'] as $speciality)
				{	
					if($speciality){
						$Rspeciality.=$speciality.',';
					}
				}
				foreach($options['Address'] as $Address)
				{
					if($Address){
						$RAddress.=$Address.',';
					}
				}
				foreach($options['Telephone'] as $Telephone)
				{
					if($Telephone){
						$RTelephone.=$Telephone.',';
					}
				}
				foreach($options['Receive_Report'] as $Receive_Report)
				{
					if($Receive_Report){
						$RReceive_Report.=$Receive_Report.',';
					}
				}
		//	echo '<pre>';print_r($Physician_Details);die;
			$ids=$physician_details->id;
				
				$idArray = explode(',', $ids);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
				$object->setPatientId($usersNs->userId);
				$object->setPhysicianName($Rphysician);
				$object->setSpeciality($Rspeciality);
				$object->setAddress($RAddress);
				$object->setTelephone($RTelephone);
				$object->setReceiveReport($RReceive_Report);
				$object->setCreateTime(time());
				$object->setUpdateTime(time());
				$object->save();
					 }
				  }
			  
			}
			
			
			
				
			}
		
		}
		
		
  /* patient history step four*/
		
		public function patientInformationStepfourAction(){
			
			$request = $this->getRequest();
			$options = $request->getPost();
			$usersNs = new Zend_Session_Namespace("members");
			$prescription_details = new Application_Model_PatientPrescription();
			$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->prescription_details=$prescription_details;
			$physician_detailss = new Application_Model_PhysicianDetails();
			$physician_details = $physician_detailss->fetchRow("patient_id='{$usersNs->userId}'");
			
		if ($request->isPost()) {
			
		   
		   $authNamespace = new Zend_Session_Namespace('three');
		   $authNamespace->pHistoryThree=$options;
		if($physician_details)
			{
			//echo '<pre>';print_r($options);die;
			$ids=$physician_details->id;
				
				$idArray = explode(',', $ids);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){				
						$object->setPhysicianCare($options['care']);
						$object->setReasonPhysicianCare($options['msg']);			
						$object->save();
					 }
				  }
			
			
			}
		    
				
			}
		
		}
		
		
  /* patient history step five*/
		
		public function patientInformationStepfiveAction(){
			
			$request = $this->getRequest();
			$options = $request->getPost();
			$usersNs = new Zend_Session_Namespace("members");
			$prescription_details = new Application_Model_PatientPrescription();
			$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->prescription_details=$prescription_details;
			
		if ($request->isPost()) {
			//echo '<pre>';print_r($options);die;
			
			$authNamespace = new Zend_Session_Namespace('four');
			$authNamespace->pHistoryFour=$options;
			
			if($prescription_details){
			
				foreach($options['Name_of_Supplement'] as $Name_of_Supplement)
				{
					if($Name_of_Supplement){
						$RName_of_Supplement.=$Name_of_Supplement.',';
					}
				}
				
				foreach($options['Dosage'] as $Dosage)
				{
					if($Dosage){
						$RDosage.=$Dosage.',';
					}
				}
				foreach($options['Freqency'] as $Freqency)
				{
					if($Freqency){
						$RFreqency.=$Freqency.',';
					}
				}
				foreach($options['Side_Effects'] as $Side_Effects)
				{
					if($Side_Effects){
						$RSide_Effects.=$Side_Effects.',';
					}
				}
				
				
									
				$ids=$prescription_details->id;				
				$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$patient_info_four = $model->fetchRow("id={$id}");
						if($patient_info_four){
				$patient_info_four->setPatientId($usersNs->userId);
				$patient_info_four->setMedication($options['medications']);
				$patient_info_four->setNameOfSupplement($RName_of_Supplement);
				$patient_info_four->setDosage($RDosage);
				$patient_info_four->setFreqency($RFreqency);
				$patient_info_four->setSideEffects($RSide_Effects);				
				$patient_info_four->setCreateTime(time());
				$patient_info_four->setUpdateTime(time());
				$prescriptionid=$patient_info_four->save();
					}
				}
			 
			}	
		}
		
		}
   /*patient history step six*/
		
		public function patientInformationStepsixAction(){
			
			$request = $this->getRequest();
			$options = $request->getPost();
			$usersNs = new Zend_Session_Namespace("members");
			$prescription_details = new Application_Model_PatientPrescription();
			$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->prescription_details=$prescription_details;
			
		if ($request->isPost()) {
			
			
			$authNamespace = new Zend_Session_Namespace('five');
			$authNamespace->pHistoryFive=$options;
			
			foreach($options['Supplement'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($options['Dossage'] as $Dossage)
				{
					if($Dossage){
						$RDossage.=$Dossage.',';
					}
				}
				foreach($options['Frequency'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($options['Any_Side_Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				if($prescription_details){
					$ids=$prescription_details->id;
					$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
						$object->setNonPrescription($options['non-prescription']);
						$object->setNonPrescriptionSupplement($RSupplement);
						$object->setNonPrescriptionDosage($RDossage);
						$object->setNonPrescriptionFreqency($RFrequency);
						$object->setNonPrescriptionSideEffects($RAny_Side_Effects);
						$object->save();
							}
						}
					
				}
	
							
			}
		
		}
		
		
   /*patient history step seven*/
		
		
		public function patientInformationStepsevenAction(){
			
			$request = $this->getRequest();
			$options = $request->getPost();
			$usersNs = new Zend_Session_Namespace("members");
			$prescription_details = new Application_Model_PatientPrescription();
			$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->prescription_details=$prescription_details;
			
		if ($request->isPost()) {
			
			
			$authNamespace = new Zend_Session_Namespace('six');
			$authNamespace->pHistorySix=$options;
			
				foreach($options['Name_of_Supplementt'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($options['Dosagge'] as $Dossage)
				{
						$RDossage.=$Dossage.',';
				}
				foreach($options['Freqenccy'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($options['Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				if($prescription_details){
					$ids=$prescription_details->id;
					$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
					//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
						$object->setMedicines($options['medicines']);
						$object->setVitaminSupplement($RSupplement);
						$object->setVitaminDosage($RDossage);
						$object->setVitaminFreqency($RFrequency);
						$object->setVitaminSideEffects($RAny_Side_Effects);
						$object->save();
						}
					}
					
				}
			
		
				
			}
		
		}
		
		/*patient history step eight*/
				
				
		public function patientInformationStepeightAction(){
				
				$request = $this->getRequest();
				$options = $request->getPost();
				
				$usersNs = new Zend_Session_Namespace("members");
				$allergic_subtances = new Application_Model_AllergicSubstances();
				$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
				$this->view->allergic_subtances=$allergic_subtances;
				$prescription_details = new Application_Model_PatientPrescription();
				$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
				$this->view->prescription_details=$prescription_details;
				
			if ($request->isPost()) {
				
			
				$authNamespace = new Zend_Session_Namespace('seven');
				$authNamespace->pHistorySeven=$options;
				
				
				foreach($options['Name_of_Medication'] as $Name_of_Medication)
					{
						if($Name_of_Medication){
							$RName_of_Medication.=$Name_of_Medication.',';
						}
					}
					
					foreach($options['Reaction'] as $Reaction)
					{
						if($Reaction){
							$RReaction.=$Reaction.',';
						}
					}
					
					if($prescription_details){
						$ids=$prescription_details->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_PatientPrescription();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){
						//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
							$object->setBadReactionSuppliment($options['badreaction']);
							$object->setBadReactionSupplimentName($RName_of_Medication);
							$object->setReaction($RReaction);
							$object->save();
							}
						}
						
					}
				
				
			
			}
		}

		/*patient history step nine*/
				
				
				public function patientInformationStepnineAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$allergic_subtances = new Application_Model_AllergicSubstances();
					$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->allergic_subtances=$allergic_subtances;
					
				if ($request->isPost()) {
					
				
					$authNamespace = new Zend_Session_Namespace('eight');
					$authNamespace->pHistoryEight=$options;	
								
						
						if($allergic_subtances){
						foreach($options['Name_of_Medications'] as $Name_of_Medications)
						{
							if($Name_of_Medications){
								$RName_of_Medications.=$Name_of_Medications.',';
							}
						}
						
						foreach($options['Reactions'] as $Reactions)
						{
							if($Reactions){
								$RReactions.=$Reactions.',';
							}
						}
						
						$substances=$options['substances'];
							$ids=$allergic_subtances->id;					
							$idArray = explode(',', $ids);
								$model = new Application_Model_AllergicSubstances();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){							
									
								$object->setPatientId($usersNs->userId);
								$object->setAllergicSubstancesStatus($substances);
								$object->setNameOfMedication($RName_of_Medications);
								$object->setReaction($RReactions);							
								$object->setCreateTime(time());
								$object->setUpdateTime(time());
								$object->save();	
									}
								}
						}
						
					}
				
				}
				
		   /*patient history step ten*/
				
				
				public function patientInformationSteptenAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$allergic_subtances = new Application_Model_AllergicSubstances();
					$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->allergic_subtances=$allergic_subtances;
					
				if ($request->isPost()) {
					
					
					$authNamespace = new Zend_Session_Namespace('nine');
					$authNamespace->pHistoryNine=$options;
					
					if($allergic_subtances){
				
							$ids=$allergic_subtances->id;
							$idArray = explode(',', $ids);
								$model = new Application_Model_AllergicSubstances();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
								if($object){								
									$object->setCurrentWeight($options['weight']);
									$object->setHeight($options['height']);
									$object->setLeastWeighed($options['weighed']);
									$object->setMostWeighed($options['most_weighed']);
									$object->setWeightGain($options['gain']);
									$object->setWeightLoss($options['loss']);
									$object->setSleepTime($options['average']);
									$object->setFrequentlyTired($options['tired']);
									$object->setTroubleSleeping($options['sleeping']);
									$object->setYesExplain($options['explain']);
									$object->setRecentFevers($options['condition']);
									$object->setUseAutomobile($options['automobiles']);						
									$object->save();
									}
								}
								}
					
						
					}
				
				}
				
				
		    /*patient history step eleven*/
				
				
				public function patientInformationStepelevenAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					
					
				if ($request->isPost()) {
					
					
					$authNamespace = new Zend_Session_Namespace('ten');
					$authNamespace->pHistoryTen=$options;
				
						
					}
				
				}
				
				
			/*patient history step twelve*/
				
				
				public function patientInformationSteptwelveAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$allergic_subtances = new Application_Model_AllergicSubstances();
					$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->allergic_subtances=$allergic_subtances;
					
				if ($request->isPost()) {
					
				
					$authNamespace = new Zend_Session_Namespace('ten');
					$authNamespace->pHistoryTen=$options;
					 foreach($options['Condition'] as $Condition)
							{
								if($Condition){
									$RCondition.=$Condition.',';
								}
							}
							
							foreach($options['Year'] as $Year)
							{
								if($Year){
									$RYear.=$Year.',';
								}
							}
							foreach($options['Where_Treated'] as $Where_Treated)
							{
								if($Where_Treated){
									$RWhere_Treated.=$Where_Treated.',';
								}
							}
						   
						   
						  
							   
							   	if($allergic_subtances){
							$ids=$allergic_subtances->id;
							$idArray = explode(',', $ids);
								$model = new Application_Model_AllergicSubstances();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){					
									$object->setMajorIllness($options['surgeries']);
									$object->setIllnessCondition($RCondition);
									$object->setIllnessYear($RYear);					
									$object->setIllnessWhereTreated($RWhere_Treated);						
									$object->save();
									}
								 }
								}
						
					}
				
				}
				
				
			/*patient history step thirteen*/
				
				
				public function patientInformationStepthirteenAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$allergic_subtances = new Application_Model_AllergicSubstances();
					$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->allergic_subtances=$allergic_subtances;
					
				if ($request->isPost()) {
					
					
					$authNamespace = new Zend_Session_Namespace('twelve');
					$authNamespace->pHistoryTwelve=$options;
					
					if($allergic_subtances){
							$ids=$allergic_subtances->id;
							$idArray = explode(',', $ids);
								$model = new Application_Model_AllergicSubstances();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){					
									$object->setCurrentDiet($options['diet']);
									$object->setParticularFood($options['intolerance']);
									$object->save();
									}
								}
								
							   }
					
						
					}
				
				}
				
				
			/*patient history step fourteen*/
				
				
				public function patientInformationStepfourteenAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$family_medical_history = new Application_Model_FamilyMedicalHistory();
					$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->family_medical_history=$family_medical_history;
					$allergic_subtances = new Application_Model_AllergicSubstances();
					$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
					
				if ($request->isPost()) {
					
					
					$authNamespace = new Zend_Session_Namespace('thirteen');
					$authNamespace->pHistoryThirteen=$options;
					
					
					 if($allergic_subtances){
							$ids=$allergic_subtances->id;
							$idArray = explode(',', $ids);
								$model = new Application_Model_AllergicSubstances();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){					
									$object->setExerciseRegularly($options['exercise']);
									$object->setTypeOfExercise($options['often']);
									$object->setPhysicalActivity($options['physical']);
									$object->setPhysicalActivityContent($options['message']);
									$object->save();
									}
								}
								}
					
						
					}
				
				}
				
				
		    /*patient history step fifteen*/
				
				
				public function patientInformationStepfifteenAction(){
					
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$family_medical_history = new Application_Model_FamilyMedicalHistory();
					$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->family_medical_history=$family_medical_history;
					
				if ($request->isPost()) {
					
				
					$authNamespace = new Zend_Session_Namespace('fourteen');
					$authNamespace->pHistoryFourteen=$options;
				
					
						foreach($options['Sisters_Brothers_living'] as $Sisters_Brothers_living)
							{
								if($Sisters_Brothers_living){
								$RSisters_Brothers_living.=$Sisters_Brothers_living.',';
								}
							}
							
							foreach($options['Sisters_Brothers_Deceased'] as $Sisters_Brothers_Deceased)
							{
								if($Sisters_Brothers_Deceased){
								$RSisters_Brothers_Deceased.=$Sisters_Brothers_Deceased.',';
								}
							}
							foreach($options['Sisters_Brothers_Age'] as $Sisters_Brothers_Age)
							{
								if($Sisters_Brothers_Age){
								$RSisters_Brothers_Age.=$Sisters_Brothers_Age.',';
								}
							}
							foreach($options['Sisters_Brothers_Major_Illnesses'] as $Sisters_Brothers_Major_Illnesses)
							{
								if($Sisters_Brothers_Major_Illnesses){
								$RSisters_Brothers_Major_Illnesses.=$Sisters_Brothers_Major_Illnesses.',';
								}
							}
							foreach($options['Aunts_Uncles_living'] as $Aunts_Uncles_living)
							{
								if($Aunts_Uncles_living){
								$RAunts_Uncles_living.=$Aunts_Uncles_living.',';
								}
							}
							
							foreach($options['Aunts_Uncles_Deceased'] as $Aunts_Uncles_Deceased)
							{
								if($Aunts_Uncles_Deceased){
								$RAunts_Uncles_Deceased.=$Aunts_Uncles_Deceased.',';
								}
							}
							foreach($options['Aunts_Uncles_Age'] as $Aunts_Uncles_Age)
							{
								if($Aunts_Uncles_Age){
								$RAunts_Uncles_Age.=$Aunts_Uncles_Age.',';
								}
							}
							foreach($options['Aunts_Uncles_Major_Illnesses'] as $Aunts_Uncles_Major_Illnesses)
							{
								if($Aunts_Uncles_Major_Illnesses){
								$RAunts_Uncles_Major_Illnesses.=$Aunts_Uncles_Major_Illnesses.',';
								}
							}
							foreach($options['Children_living'] as $Children_living)
							{
								if($Children_living){
								$RChildren_living.=$Children_living.',';
								}
							}
							
							foreach($options['Children_living_Deceased'] as $Children_living_Deceased)
							{
								if($Children_living_Deceased){
								$RChildren_living_Deceased.=$Children_living_Deceased.',';
								}
							}
							foreach($options['Children_living_Age'] as $Children_living_Age)
							{
								if($Children_living_Age){
								$RChildren_living_Age.=$Children_living_Age.',';
								}
							}
							foreach($options['Children_living_Major_Illnesses'] as $Children_living_Major_Illnesses)
							{
								if($Children_living_Major_Illnesses){
								$RChildren_living_Major_Illnesses.=$Children_living_Major_Illnesses.',';
								}
							}				
							if($family_medical_history)
							{
								$ids=$family_medical_history->id;
								$idArray = explode(',', $ids);
								$model = new Application_Model_FamilyMedicalHistory();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){					
							
							$object->setPatientId($usersNs->userId);					
							$object->setMotherLiving($options['Mother_living']);
							$object->setMotherDiceased($options['Mother_Deceased']);
							$object->setMotherAge($options['Mother_Age']);
							$object->setMotherMajorIllness($options['Mother_Major_Illnesses']);					
							$object->setFatherLiving($options['Father_living']);
							$object->setFatherDiceased($options['Father_Deceased']);
							$object->setFatherAge($options['Father_Age']);						
							$object->setFatherMajorIllness($options['Father_Major_Illnesses']);
							$object->setMaternalGrandmotherLiving($options['Maternal_Grandmother_living']);
							$object->setMaternalGrandmotherDiceased($options['Maternal_Grandmother_Deceased']);
							$object->setMaternalGrandmotherAge($options['Maternal_Grandmother_Age']);
							$object->setMaternalGrandmotherMajorIllness($options['Maternal_Grandmother_Major_Illnesses']);
							$object->setMaternalGrandfatherLiving($options['Maternal_Grandfather_living']);
							$object->setMaternalGrandfatherDiceased($options['Maternal_Grandfather_Deceased']);
							$object->setMaternalGrandfatherAge($options['Maternal_Grandfather_Age']);
							$object->setMaternalGrandfatherMajorIllness($options['Maternal_Grandfather_Major_Illnesses']);
							$object->setPaternalGrandmotherLiving($options['Maternal_Grandfather_living']);
							$object->setPaternalGrandmotherDiceased($options['Maternal_Grandfather_Deceased']);
							$object->setPaternalGrandmotherAge($options['Maternal_Grandfather_Age']);
							$object->setPaternalGrandmotherMajorIllness($options['Maternal_Grandfather_Major_Illnesses']);
							$object->setPaternalGrandfatherLiving($options['Paternal_Grandmother_living']);
							$object->setPaternalGrandfatherDiceased($options['Paternal_Grandmother_Deceased']);
							$object->setPaternalGrandfatherAge($options['Paternal_Grandmother_Age']);
							$object->setPaternalGrandfatherMajorIllness($options['Paternal_Grandmother_Major_Illnesses']);
							$object->setSistersBrothersLivings($RSisters_Brothers_living);
							$object->setSistersBrothersDiceased($RSisters_Brothers_Deceased);
							$object->setSistersBrothersAge($Sisters_Brothers_Age);
							$object->setSistersBrothersMajorIllness($RSisters_Brothers_Major_Illnesses);
							$object->setAuntsUnclesLiving($RAunts_Uncles_living);
							$object->setAuntsUnclesDiceased($RAunts_Uncles_Deceased);
							$object->setAuntsUnclesAge($RAunts_Uncles_Age);
							$object->setAuntsUnclesMajorIllness($RAunts_Uncles_Major_Illnesses);
							$object->setChildrenLiving($RChildren_living);
							$object->setChildrenDiceased($RChildren_living_Deceased);
							$object->setChildrenAge($RChildren_living_Age);
							$object->setChildrenMajorIllness($RChildren_living_Major_Illnesses);					
							$object->setCreateTime(time());
							$object->setUpdateTime(time());
						
							$object->save();
									}
								}
								
							}
						
					}
				
				}
				
				
			/* patient history step sixteen */
				
				
				public function patientInformationStepsixteenAction() {
				
					$request = $this->getRequest();
					$options = $request->getPost();
					$usersNs = new Zend_Session_Namespace("members");
					$family_medical_history = new Application_Model_FamilyMedicalHistory();
					$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
					$this->view->family_medical_history=$family_medical_history;
				   
				if($request->isPost()) {
					
				    $authNamespace = new zend_Session_Namespace('fifteen');
					$authNamespace->pHistoryFifteen=$options;
					
						if($family_medical_history)
							{
								$ids=$family_medical_history->id;
								
								$idArray = explode(',', $ids);
								$model = new Application_Model_FamilyMedicalHistory();
									foreach ($idArray as $id) {
									$object = $model->fetchRow("id={$id}");
									if($object){					
									$object->setPneumoniaVaccine($options['Pneumonia_Vaccine']);
									$object->setPneumoniaVaccineYear($options['Pneumonia_Vaccine_year']);
									$object->setInflueza($options['Influeza']);
									$object->setInfluezaYear($options['Influeza_year']);
									$object->setTuberculin($options['Tuberculin']);
									$object->setTuberculinYear($options['Tuberculin_skin_test_year']);
									$object->setBcg($options['BCG']);
									$object->setBcgYear($options['BCG_year']);
									$object->setDiptheria($options['Diptheria']);
									$object->setDiptheriaYear($options['Diptheria_year']);
									$object->setMeasles($options['Measles']);
									$object->setMeaslesYear($options['Rubella_year']);
									$object->setHepatitis($options['Hepatitis_A']);
									$object->setHepatitisYear($options['Hepatitis_A_year']);
									$object->save();
									}
								}
							}
				   }
		       }

		/* patient history step seventeen */
		
		public function patientInformationStepseventeenAction() {
			
			$request = $this->getRequest();
			$options = $request->getPost();	
			$usersNs = new Zend_Session_Namespace("members");
			$family_medical_history = new Application_Model_FamilyMedicalHistory();
			$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->family_medical_history=$family_medical_history;
					
		
			
		if($request->isPost()) {			
			$authNamespace = new zend_Session_Namespace('sixteen');
			$authNamespace->pHistorySixteen=$options;

		   if($family_medical_history)
					{
						$ids=$family_medical_history->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setTestedChickenPox($options['Chicken_Pox']);
							$object->setTestedTuberculosis($options['Tuberculosis']);
							$object->setTestedHiv($options['HIV']);
							$object->setTestedHepatitis($options['Hepatitis']);
							$object->setTestedVenereal($options['Venereal']);
							$object->setType($options['type']);
							$object->setSpecify($options['Specify']);
							$object->save();
							}
						}
						
					}

			
			}
	  }
	
		   
	
		
		/*Set Flag for the patinet Tour*/
		public function firstLoginPatientAction(){
		
		$usersNs   = new Zend_Session_Namespace("members");
		$Patient    = new Application_Model_Patient();
		$ids= $usersNs->userId;
		
		$idArray = explode(',', $ids);
        $model = new Application_Model_Patient();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("user_id='{$usersNs->userId}'");
            if($object){
               // $object->setStatus('1');
				$object->setfirstLogin('1');
                $object->save();
            }
        }
		die('here');
	
		}

		/*To Upgrade plan of subscription*/
		public function upgradePlanAction()
		{
			$usersNs = new Zend_Session_Namespace("members");
	        $Doctor = new Application_Model_Doctor();
			$User = new Application_Model_User();
	        $docObject = $Doctor->fetchRow("user_id='{$usersNs->userId}'");
			$userObject = $User->fetchRow("id='{$usersNs->userId}'");
			
			
			
			$first_name=$userObject->firstName;
			$last_name=$userObject->firstName;
			$address=$docObject->street;
			$city=$docObject->city;
			$state=$docObject->state;
			$country='United State';
			$zip=$docObject->zipcode;
			$billing_address=$docObject->street;
			$billing_city=$docObject->city;
			$billing_state=$docObject->state;
			$billing_country='United State';
			$billing_zip=$docObject->zipcode;
			$email=$userObject->email;
			
			$settings = new Admin_Model_GlobalSettings();
			$premium_monthly_upgrade = $settings->settingValue('premium_monthly_upgrade');
			$premium_annual_upgrade = $settings->settingValue('premium_annual_upgrade');
			$this->view->premium_upgrade=$premium_monthly_upgrade.'?first_name='.$first_name.'&last_name='.$last_name.'&address='.$address.'&city='.$city.'&state='.$state.'&country='.$country.'&zip='.$zip.'&billing_address='.$billing_address.'&billing_city='.$billing_city.'&billing_state='.$billing_state.'&billing_country='.$billing_country.'&billing_zip='.$billing_zip.'&email='.$email.'';
			
			$this->view->yearly=$premium_annual_upgrade.'?first_name='.$first_name.'&last_name='.$last_name.'&address='.$address.'&city='.$city.'&state='.$state.'&country='.$country.'&zip='.$zip.'&billing_address='.$billing_address.'&billing_city='.$billing_city.'&billing_state='.$billing_state.'&billing_country='.$billing_country.'&billing_zip='.$billing_zip.'&email='.$email.'';
			
			$customer_premium_id= $this->_getParam('customer_premium_id');
			$yearly= $this->_getParam('plan');
			if($yearly=="yearly"){
			$expiry=strtotime('+12 month', time());
			$subscription_type="Yearly";
			}else
			{
			$expiry=strtotime('+1 month', time());
			$subscription_type="Monthly";
			}
			
			if($customer_premium_id)
			{      
				
					$ids=$docObject->id;
					//echo $ids;die;
					$idArray = explode(',', $ids);
						$model = new Application_Model_Doctor();
							foreach ($idArray as $id) {
							//echo $id;die;
								$object = $model->fetchRow("id=".$id);
							if($object){
								$object->setStatus('1');
								
								$object->setSubscriptionType($subscription_type);	
								$object->setMembershipLevel("Premium");									
								$object->setMembershipLevelNo('3');				
								$object->setExpiration($expiry);
								$object->setCustomerId($customer_premium_id);
								$object->save();
							}
					}
								
				$this->_helper->redirector('upgrade-plan', 'index', "user");		              
	              
			}
		
			$this->view->membership=$docObject;		
			
		
		}
		/*This function to set the Dotor Tour Flag*/
	public function firstLoginAction()
	{
		$usersNs   = new Zend_Session_Namespace("members");
		$Doctor    = new Application_Model_Doctor();
		$ids= $usersNs->userId;
		
		$idArray = explode(',', $ids);
        $model = new Application_Model_Doctor();
        foreach ($idArray as $id) {
            $object = $model->fetchRow("user_id='{$usersNs->userId}'");
            if($object){
               // $object->setStatus('1');
				$object->setfirstLogin('1');
                $object->save();
            }
        }
		die('here');
	
	}

	public function formatPhone($phone){
		$newPhone = $phone;
		if(str_word_count($phone) != 1) { 
			$numbers = str_split($phone);
			$newPhone = "(".$numbers[0].$numbers[1].$numbers[2].") ".$numbers[3].$numbers[4].$numbers[5]." ".$numbers[6].$numbers[7].$numbers[8];
		}
		return $newPhone;
	}

	public function approveAppointmentAction() {
		$this->_helper->layout->disableLayout();
		$appointid = $this->getRequest()->getParam('appointid');
		$Appointment = new Application_Model_Appointment();
		$appointment = $Appointment->find($appointid);
		if($appointment) {
			$appointment->setApprove(1);
			$appointment->save();
			$return['err'] = 0;
			$Mail = new Base_Mail('UTF-8');
			$Mail->sendPatientAppointmentApprovedMail($appointment);
		} else {
			$return['err'] = 1;
		}
        echo Zend_Json::encode($return);
        exit();
	}
	public function declineAppointmentAction() {
		$this->_helper->layout->disableLayout();
		$appointid = $this->getRequest()->getParam('appointid');
		$appoint = explode("_", $appointid);
		$Appointment = new Application_Model_Appointment();
		$appointment = $Appointment->find($appoint[1]);
		if($appointment) {
			$appointment->setApprove(2);
			$appointment->setCancelledBy(3);
			$appointment->save();
			$return['err'] = 0;

			$Mail = new Base_Mail('UTF-8');
			$Mail->sendCancelAppointmentPatientMailEnquiry($appointment);
		} else {
			$return['err'] = 1;
		}
        echo Zend_Json::encode($return);
        exit();
	}

	public function patientDeclineAppointmentAction() {
		$this->_helper->layout->disableLayout();
		$appointid = $this->getRequest()->getParam('appointid');
		$appoint = explode("_", $appointid);
		$Appointment = new Application_Model_Appointment();
		$appointment = $Appointment->find($appoint[1]);
		if($appointment) {
			$appointment->setApprove(2);
			$appointment->setCancelledBy(1);
			$appointment->save();
			$return['err'] = 0;
			
			$Mail = new Base_Mail('UTF-8');
			$Mail->sendCancelAppointmentDoctorMailEnquiry($appointment);
		} else {
			$return['err'] = 1;
		}
        echo Zend_Json::encode($return);
        exit();
	}
	private function radiusSearch($lat, $long, $catId, $userid) {
    	$result = array();
		$db = Zend_Registry::get('db');
		if($lat !="" && $long !="") {
			$alt_where= "SELECT DISTINCT id, '' as countt, (6371 * ACOS( COS( RADIANS(".$lat.") ) * COS( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',1) ) ) * COS( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',-1) ) - RADIANS(".$long.") ) + SIN( RADIANS(".$lat.") ) * SIN( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',1)) ) )) AS distance FROM doctors WHERE STATUS=1 ";
			
			if($catId!=0) {
				$alt_where .= " AND ( id in (SELECT doctor_id FROM doctor_categories WHERE category_id = ".$db->quote($catId).") ) ";
			}
			if($userid) {
				$alt_where .= " AND user_id != ".$userid." ";
			}
			
			$distance = 60;
			
			$queryGlobal = $alt_where." HAVING distance < ".$distance." ORDER BY distance ASC, membership_level DESC, fname ASC";
			//error_log($queryGlobal);
			$select = $db->query($queryGlobal);
			$result = $select->fetchAll();
		}
		return $result; 
	}

	/* Function used to assign insurance to Doctor*/
	public function insuranceAction()
	{
		$model = new Application_Model_InsuranceCompany();
		$company = $model->fetchAll();	
		$this->view->company=$company;
	}
	/* Function used to assign insurance plan to Doctor*/
	public function insuranceplanAction()
	{
		
		$company_id=$this->_getParam('company_id');
		$plan = new Application_Model_InsurancePlan();
		$company_paln = $plan->fetchAll("insurance_company_id='{$company_id}'");
		$this->view->company_paln=$company_paln;
		
		$model = new Application_Model_InsuranceCompany();
		$company = $model->fetchRow("id='{$company_id}'");	
		
		$usersNs   = new Zend_Session_Namespace("members");
		$Doctor    = new Application_Model_Doctor();
		$doctor = $Doctor ->fetchRow("user_id='{$usersNs->userId}'");
		
		$doctor_insurance_plan= new Application_Model_DoctorInsurancePlan();
		$doctor_insurance_plans = $doctor_insurance_plan ->fetchAll("doctor_id='{$doctor->id}' and insurance_company_id='{$company_id}'");
		
		$this->view->company=$company->company;
		$this->view->insurance_company_id=$company->id;
		$this->view->doctor_insurance_plans=$doctor_insurance_plans;
		
		$request = $this->getRequest();
        $options = $request->getPost();
		
        if ($request->isPost()) {
			$options['doctor_id']=$doctor->id;
							
			$doctor_insurance= new Application_Model_DoctorInsurance();
			$insurance_comp = $doctor_insurance ->fetchAll("doctor_id='{$options['doctor_id']}' and insurance_id=".$options['insurance_company_id']);
					
			if(!$insurance_comp){
				$doctor_insurance->setDoctorId($options['doctor_id']);
				$doctor_insurance->setInsuranceId($options['insurance_company_id']);
				$doctor_insurance->save();
			}
			
			$doctor_insurance_plan= new Application_Model_DoctorInsurancePlan();
			$insurance_plan = $doctor_insurance_plan ->fetchAll("doctor_id='{$options['doctor_id']}' and insurance_company_id=".$options['insurance_company_id']);
			
			if($insurance_plan)
			{
				foreach($insurance_plan as $plans)
				{		 
					$doctor_insurance_plan->delete($plans->id);
				}
			}
		
			foreach($options['plan'] as $paln)
			{
				$doctor_insurance_plan->setDoctorId($options['doctor_id']);
				$doctor_insurance_plan->setPlanId($paln);
				$doctor_insurance_plan->setInsuranceCompanyId($options['insurance_company_id']);
				$doctor_insurance_plan->save();
			}			
			
			$this->redirect("/user/index/insurance");
			
		}
		//echo $company_id;
		
	}
	
	/*Function for the Patient History Full information*/
		public function patientInformationFullAction(){
			
			//$usersNs = new Zend_Session_Namespace("members");
			$msg=$this->_getParam('e');
			
			$this->view->msg=base64_decode($msg);
			
				$request = $this->getRequest();
				$options = $request->getPost();	
				$usersNs = new zend_Session_Namespace("members");			
				$authNamespacefourteen = new zend_Session_Namespace('fourteen');	
				$authNamespacefifteen = new zend_Session_Namespace('fifteen');	
				$authNamespacesixteen = new zend_Session_Namespace('sixteen');
				$authNamespaceseventeen = new zend_Session_Namespace('seventeen');
				$Patient_history = new Application_Model_PatientHistory();	
				$patient_history = $Patient_history->fetchRow("patient_id='{$usersNs->userId}'");	
				$Physician_Details = new Application_Model_PhysicianDetails();	
				$Physician_Details = $Physician_Details->fetchRow("patient_id='{$usersNs->userId}'");	
				$prescription_details = new Application_Model_PatientPrescription();
				$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
				$allergic_subtances = new Application_Model_AllergicSubstances();
				$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
				$family_medical_history = new Application_Model_FamilyMedicalHistory();
				$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
				//echo '<pre>';print_r($patient_history);die;
				
				
					if($request->isPost()) {

							 if($family_medical_history)
						{
							$ids=$family_medical_history->id;
							$idArray = explode(',', $ids);
							$model = new Application_Model_FamilyMedicalHistory();
								foreach ($idArray as $id) {
								$object = $model->fetchRow("id={$id}");
								if($object){					
								$object->setChestXRayDate($options['Chest_Date']);
								$object->setChestXRayResult($options['Chest_Result']);
								$object->setCholesterolLevelDate($options['Cholesterol_date']);
								$object->setCholesterolLevelResult($options['Cholesterol_result']);
								$object->setTriglycerideLevelDate($options['Triglyceride_date']);
								$object->setTriglycerideLevelResult($options['Triglyceride_result']);
								$object->setOtherLipidDataDate($options['Lipid_date']);
								$object->setOtherLipidDataResult($options['Lipid_result']);
								$object->setColonoscopyDate($options['Colonoscopy_date']);
								$object->setColonoscopyResult($options['Colonoscopy_result']);
								$object->setMammogramDate($options['Mammogram_date']);
								$object->setMammogramResult($options['Mammogram_result']);
								$object->setPapTestDate($options['Pap_date']);
								$object->setPapTestResult($options['Pap_result']);
								$object->setBoneDensityTestDate($options['Density_date']);
								$object->setBoneDensityTestResult($options['Density_result']);
								$object->save();
								}
							}
						}
					
				$authNamespaceseventeen->pHistorySeventeen=	$options;
				$authNamespacetwo = new zend_Session_Namespace('two');
				$authNamespacefour = new zend_Session_Namespace('four');		
				$authNamespaceone = new zend_Session_Namespace('one');
		
			/*Save data of step two and three*/
			
			
			if($authNamespacetwo->pHistoryTwo)
			{
				
				
				foreach($authNamespacetwo->pHistoryTwo['physian'] as $physician)
				{
					if($physician){
						$Rphysician.=$physician.',';
					}
				}
				foreach($authNamespacetwo->pHistoryTwo['speciality'] as $speciality)
				{	
					if($speciality){
						$Rspeciality.=$speciality.',';
					}
				}
				foreach($authNamespacetwo->pHistoryTwo['Address'] as $Address)
				{
					if($Address){
						$RAddress.=$Address.',';
					}
				}
				foreach($authNamespacetwo->pHistoryTwo['Telephone'] as $Telephone)
				{
					if($Telephone){
						$RTelephone.=$Telephone.',';
					}
				}
				foreach($authNamespacetwo->pHistoryTwo['Receive_Report'] as $Receive_Report)
				{
					if($Receive_Report){
						$RReceive_Report.=$Receive_Report.',';
					}
				}
				if($Physician_Details){				
				$ids=$Physician_Details->id;
				
				$idArray = explode(',', $ids);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
				$object->setPatientId($usersNs->userId);
				$object->setPhysicianName($Rphysician);
				$object->setSpeciality($Rspeciality);
				$object->setAddress($RAddress);
				$object->setTelephone($RTelephone);
				$object->setReceiveReport($RReceive_Report);
				$object->setCreateTime(time());
				$object->setUpdateTime(time());
				$object->save();
					 }
				  }
				
				
				}else{
					
				$patient_info_two = new Application_Model_PhysicianDetails();			
				$patient_info_two->setPatientId($usersNs->userId);
				$patient_info_two->setPhysicianName($Rphysician);
				$patient_info_two->setSpeciality($Rspeciality);
				$patient_info_two->setAddress($RAddress);
				$patient_info_two->setTelephone($RTelephone);
				$patient_info_two->setReceiveReport($RReceive_Report);
				$patient_info_two->setCreateTime(time());
				$patient_info_two->setUpdateTime(time());
				$physicianid=$patient_info_two->save();
				}
				
				$authNamespacethree = new zend_Session_Namespace('three');
				//print_r($authNamespacethree->pHistoryThree);die;
				
			}
			$authNamespacethree = new zend_Session_Namespace('three');	
			if($authNamespacethree->pHistoryThree)
			{
				if($Physician_Details){
				$ids=$Physician_Details->id;
				$idArray = explode(',', $ids);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
						$object->setPhysicianCare($authNamespacethree->pHistoryThree['care']);
						$object->setReasonPhysicianCare($authNamespacethree->pHistoryThree['msg']);
						$object->save();
					 }
				  }

			}else{
				
				$ids=$physicianid;
				if($ids){
				$idArray = explode(',', $ids);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
						$object->setPhysicianCare($authNamespacethree->pHistoryThree['care']);
						$object->setReasonPhysicianCare($authNamespacethree->pHistoryThree['msg']);
						$object->save();
					 }
				  }
			   }else{
						$modelthree = new Application_Model_PhysicianDetails();
						$modelthree->setPhysicianCare($authNamespacethree->pHistoryThree['care']);
						$modelthree->setReasonPhysicianCare($authNamespacethree->pHistoryThree['msg']);
						$physicianid=$modelthree->save();
			   }
			}
				
			}
					
				
		
			if($authNamespaceone->pHistoryOne)
			{
				//echo '<pre>';print_r($authNamespaceone->pHistoryOne);die;
				$physician=$authNamespaceone->pHistoryOne['physician'];
				$datevisit=$authNamespaceone->pHistoryOne['date_of_visit'];
				$datev=explode("-",$datevisit);
				//$date_dob=$authNamespaceone->pHistoryOne['date_dob'];
				//$year_dob=$authNamespaceone->pHistoryOne['year_dob'];
				$date_of_visit=$datev[2].'-'.$datev[1].'-'.$datev[0];
				$patient=$authNamespaceone->pHistoryOne['patient'];
				$address=$authNamespaceone->pHistoryOne['address'];
				$telephone_day=$authNamespaceone->pHistoryOne['telephone_day'];
				$telephone_evening=$authNamespaceone->pHistoryOne['telephone_evening'];
				$fax=$authNamespaceone->pHistoryOne['fax'];
				$email=$authNamespaceone->pHistoryOne['email'];
				$social_security_no=$authNamespaceone->pHistoryOne['social_security_no'];
				$birth_place=$authNamespaceone->pHistoryOne['birth_place'];
				$employed=$authNamespaceone->pHistoryOne['employed'];
				$retired=$authNamespaceone->pHistoryOne['retired'];
				$occupation=$authNamespaceone->pHistoryOne['occupation'];
				$self=$authNamespaceone->pHistoryOne['self'];
				$other_person=$authNamespaceone->pHistoryOne['other_person'];
				$maritalstatus=$authNamespaceone->pHistoryOne['maritalstatus'];
				$case_of_emergency=$authNamespaceone->pHistoryOne['case_of_emergency'];
				$contact_person_name=$authNamespaceone->pHistoryOne['contact_person_name'];
				$contact_person_address=$authNamespaceone->pHistoryOne['contact_person_address'];
				$contact_person_telephone_day=$authNamespaceone->pHistoryOne['contact_person_telephone_day'];
				$contact_person_telephone_evening=$authNamespaceone->pHistoryOne['contact_person_telephone_evening'];
				$relation_ship_to_you=$authNamespaceone->pHistoryOne['relation_ship_to_you'];
				if($patient_history){
					//echo $patient_history->id;die; 
				$ids=$patient_history->id;
				$idArray = explode(',', $ids);
				$model = new Application_Model_PatientHistory();
				foreach ($idArray as $id) {
				
				$object = $model->fetchRow("id={$id}");
				if($object){
				$object->setPatientId($usersNs->userId);
				$object->setPhysicianName($physician);
				$object->setDateOfVisit($date_of_visit);
				$object->setPatientName($patient);
				$object->setPatientAddress($address);
				$object->setTelephoneDay($telephone_day);
				$object->setTelephoneEvening($telephone_evening);
				$object->setFax($fax);
				$object->setEmaiId($email);
				$object->setSocialSecurityNumber($social_security_no);
				$object->setDob($date_of_visit);
				$object->setBirthPlace($birth_place);
				$object->setEmployed($employed);
				$object->setRetired($retired);
				$object->setOccupation($occupation);
				$object->setSelf($self);
				$object->setOtherPerson($other_person);
				$object->setMaritalstatus($maritalstatus);
				$object->setCaseOfEmergency($case_of_emergency);
				$object->setContactPersonName($contact_person_name);
				$object->setContactPersonAddress($contact_person_address);
				$object->setContactPersonTelephoneDay($contact_person_telephone_day);
				$object->setContactPersonTelephoneEvening($contact_person_telephone_evening);
				$object->setRelationshipToYou($relation_ship_to_you);
				$object->setCreateTime(time());
				$object->setUpdateTime(time());
				
				$object->save();
					}
				}
				
				}else{ //echo "testashu";
					
				$patient_info_one = new Application_Model_PatientHistory();			
				$patient_info_one->setPatientId($usersNs->userId);
				$patient_info_one->setPhysicianName($physician);
				$patient_info_one->setDateOfVisit($date_of_visit);
				$patient_info_one->setPatientAddress($address);
				$patient_info_one->setPatientName($patient);
				$patient_info_one->setTelephoneDay($telephone_day);
				$patient_info_one->setTelephoneEvening($telephone_evening);
				$patient_info_one->setFax($fax);
				$patient_info_one->setEmaiId($email);
				$patient_info_one->setSocialSecurityNumber($social_security_no);
				$patient_info_one->setDob($date_of_visit);
				$patient_info_one->setBirthPlace($birth_place);
				$patient_info_one->setEmployed($employed);
				$patient_info_one->setRetired($retired);
				$patient_info_one->setOccupation($occupation);
				$patient_info_one->setSelf($self);
				$patient_info_one->setOtherPerson($other_person);
				$patient_info_one->setMaritalstatus($maritalstatus);
				$patient_info_one->setCaseOfEmergency($case_of_emergency);
				$patient_info_one->setContactPersonName($contact_person_name);
				$patient_info_one->setContactPersonAddress($contact_person_address);
				$patient_info_one->setContactPersonTelephoneDay($contact_person_telephone_day);
				$patient_info_one->setContactPersonTelephoneEvening($contact_person_telephone_evening);
				$patient_info_one->setRelationshipToYou($relation_ship_to_you);
				$patient_info_one->setCreateTime(time());
				$patient_info_one->setUpdateTime(time());
				
				$patient_info_one->save();
					
				}
				
			}	
			
			/*Save data of step Four*/
			
			
			if($authNamespacefour->pHistoryFour)
			{
			
				
				
				foreach($authNamespacefour->pHistoryFour['Name_of_Supplement'] as $Name_of_Supplement)
				{
					if($Name_of_Supplement){
						$RName_of_Supplement.=$Name_of_Supplement.',';
					}
				}
				
				foreach($authNamespacefour->pHistoryFour['Dosage'] as $Dosage)
				{
					if($Dosage){
						$RDosage.=$Dosage.',';
					}
				}
				foreach($authNamespacefour->pHistoryFour['Freqency'] as $Freqency)
				{
					if($Freqency){
						$RFreqency.=$Freqency.',';
					}
				}
				foreach($authNamespacefour->pHistoryFour['Side_Effects'] as $Side_Effects)
				{
					if($Side_Effects){
						$RSide_Effects.=$Side_Effects.',';
					}
				}
				
				if($prescription_details){
						
					
				$ids=$prescription_details->id;				
				$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$patient_info_four = $model->fetchRow("id={$id}");
						if($patient_info_four){
				$patient_info_four->setPatientId($usersNs->userId);
				$patient_info_four->setMedication($authNamespacefour->pHistoryFour['medications']);
				$patient_info_four->setNameOfSupplement($RName_of_Supplement);
				$patient_info_four->setDosage($RDosage);
				$patient_info_four->setFreqency($RFreqency);
				$patient_info_four->setSideEffects($RSide_Effects);				
				$patient_info_four->setCreateTime(time());
				$patient_info_four->setUpdateTime(time());
				$prescriptionid=$patient_info_four->save();
					}
				}
				
				
				}else{
					
					
				$patient_info_four = new Application_Model_PatientPrescription();			
				$patient_info_four->setPatientId($usersNs->userId);
				$patient_info_four->setMedication($authNamespacefour->pHistoryFour['medications']);
				$patient_info_four->setNameOfSupplement($RName_of_Supplement);
				$patient_info_four->setDosage($RDosage);
				$patient_info_four->setFreqency($RFreqency);
				$patient_info_four->setSideEffects($RSide_Effects);				
				$patient_info_four->setCreateTime(time());
				$patient_info_four->setUpdateTime(time());
				$prescriptionid=$patient_info_four->save();	
					
				}					
				
				
				
			}
				/*Save data of step Five*/
			
			$authNamespacefive = new zend_Session_Namespace('five');	
			if($authNamespacefive->pHistoryFive)
			{
				
				
				
				foreach($authNamespacefive->pHistoryFive['Supplement'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($authNamespacefive->pHistoryFive['Dossage'] as $Dossage)
				{
					if($Dossage){
						$RDossage.=$Dossage.',';
					}
				}
				foreach($authNamespacefive->pHistoryFive['Frequency'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($authNamespacefive->pHistoryFive['Any_Side_Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				if($prescription_details){
					$ids=$prescription_details->id;
					$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
						$object->setNonPrescription($authNamespacefive->pHistoryFive['non-prescription']);
						$object->setNonPrescriptionSupplement($RSupplement);
						$object->setNonPrescriptionDosage($RDossage);
						$object->setNonPrescriptionFreqency($RFrequency);
						$object->setNonPrescriptionSideEffects($RAny_Side_Effects);
						$object->save();
					}
				}
					
				}else{
					
				
				$ids=$prescriptionid;
				if($ids){
				$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
						$object->setNonPrescription($authNamespacefive->pHistoryFive['non-prescription']);
						$object->setNonPrescriptionSupplement($RSupplement);
						$object->setNonPrescriptionDosage($RDossage);
						$object->setNonPrescriptionFreqency($RFrequency);
						$object->setNonPrescriptionSideEffects($RAny_Side_Effects);
						$object->save();
					}
				}
				
					}else{
						$modelfive = new Application_Model_PatientPrescription();
						$modelfive->setNonPrescription($authNamespacefive->pHistoryFive['non-prescription']);
						$modelfive->setNonPrescriptionSupplement($RSupplement);
						$modelfive->setNonPrescriptionDosage($RDossage);
						$modelfive->setNonPrescriptionFreqency($RFrequency);
						$modelfive->setNonPrescriptionSideEffects($RAny_Side_Effects);
						$prescriptionid=$modelfive->save();
				 
					}
					
				}	
				
				
			}
			
			
				/*Save data of step Six*/
			
			$authNamespacesix = new zend_Session_Namespace('six');	
			if($authNamespacesix->pHistorySix)
			{
								
				
				foreach($authNamespacesix->pHistorySix['Name_of_Supplementt'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($authNamespacesix->pHistorySix['Dosagge'] as $Dossage)
				{
						$RDossage.=$Dossage.',';
				}
				foreach($authNamespacesix->pHistorySix['Freqenccy'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($authNamespacesix->pHistorySix['Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				if($prescription_details){
					$ids=$prescription_details->id;
					$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
					//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
						$object->setMedicines($authNamespacesix->pHistorySix['medicines']);
						$object->setVitaminSupplement($RSupplement);
						$object->setVitaminDosage($RDossage);
						$object->setVitaminFreqency($RFrequency);
						$object->setVitaminSideEffects($RAny_Side_Effects);
						$object->save();
						}
					}
					
				}else{
				
				$ids=$prescriptionid;
				if($ids){
				$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
					//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
						$object->setMedicines($authNamespacesix->pHistorySix['medicines']);
						$object->setVitaminSupplement($RSupplement);
						$object->setVitaminDosage($RDossage);
						$object->setVitaminFreqency($RFrequency);
						$object->setVitaminSideEffects($RAny_Side_Effects);
						$object->save();
						}
					}
					
				}else{
						$modelsix = new Application_Model_PatientPrescription();
						$modelsix->setMedicines($authNamespacesix->pHistorySix['medicines']);
						$modelsix->setVitaminSupplement($RSupplement);
						$modelsix->setVitaminDosage($RDossage);
						$modelsix->setVitaminFreqency($RFrequency);
						$modelsix->setVitaminSideEffects($RAny_Side_Effects);
						$prescriptionid=$modelsix->save();
				}
					
				}
			}
				 
				 
				 
				 	/*Save data of step Seven*/
			
			$authNamespaceseven = new zend_Session_Namespace('seven');	
			if($authNamespaceseven->pHistorySeven)
			{
							
				
				foreach($authNamespaceseven->pHistorySeven['Name_of_Medication'] as $Name_of_Medication)
				{
					if($Name_of_Medication){
						$RName_of_Medication.=$Name_of_Medication.',';
					}
				}
				
				foreach($authNamespaceseven->pHistorySeven['Reaction'] as $Reaction)
				{
					if($Reaction){
						$RReaction.=$Reaction.',';
					}
				}
				
				if($prescription_details){
					$ids=$prescription_details->id;
					$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
					//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
						$object->setBadReactionSuppliment($authNamespaceseven->pHistorySeven['badreaction']);
						$object->setBadReactionSupplimentName($RName_of_Medication);
						$object->setReaction($RReaction);
						$object->save();
						}
					}
					
				}else{
				$ids=$prescriptionid;
				if($ids){
				$idArray = explode(',', $ids);
					$model = new Application_Model_PatientPrescription();
						foreach ($idArray as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
					//	$object->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
						$object->setBadReactionSuppliment($authNamespaceseven->pHistorySeven['badreaction']);
						$object->setBadReactionSupplimentName($RName_of_Medication);
						$object->setReaction($RReaction);
						$object->save();
						}
					}
					
				}else{
						$modelseven = new Application_Model_PatientPrescription();
						$modelseven->setBadReactionSuppliment($authNamespaceseven->pHistorySeven['badreaction']);
						$modelseven->setBadReactionSupplimentName($RName_of_Medication);
						$modelseven->setReaction($RReaction);
						$prescriptionid=$modelseven->save();

					}
				}
				
				}
			
			
			
				 	/*Save data of step eight*/
			
			$authNamespaceeight = new zend_Session_Namespace('eight');	
			$authNamespacenine = new zend_Session_Namespace('nine');	
			$authNamespaceten = new zend_Session_Namespace('ten');		
			$authNamespacetwelve = new zend_Session_Namespace('twelve');
			$authNamespacethirteen = new zend_Session_Namespace('thirteen');
			
			if($authNamespaceeight->pHistoryEight)
			{
				
				
					
				foreach($authNamespaceeight->pHistoryEight['Name_of_Medications'] as $Name_of_Medications)
				{
					if($Name_of_Medications){
						$RName_of_Medications.=$Name_of_Medications.',';
					}
				}
				
				foreach($authNamespaceeight->pHistoryEight['Reactions'] as $Reactions)
				{
					if($Reactions){
						$RReactions.=$Reactions.',';
					}
				}
				
				$substances=$authNamespaceeight->pHistoryEight['substances'];
				
				if($allergic_subtances){
					$ids=$allergic_subtances->id;					
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){			
							
								
						$object->setPatientId($usersNs->userId);
						$object->setAllergicSubstancesStatus($substances);
						$object->setNameOfMedication($RName_of_Medications);
						$object->setReaction($RReactions);							
						$object->setCreateTime(time());
						$object->setUpdateTime(time());
						$object->save();	
							}
						}
				}else{
				$patient_info_eight = new Application_Model_AllergicSubstances();			
				$patient_info_eight->setPatientId($usersNs->userId);
				$patient_info_eight->setAllergicSubstancesStatus($substances);
				$patient_info_eight->setNameOfMedication($RName_of_Medications);
				$patient_info_eight->setReaction($RReactions);							
				$patient_info_eight->setCreateTime(time());
				$patient_info_eight->setUpdateTime(time());
				$allergiid=$patient_info_eight->save();	
				}
				
			}
				
					if($authNamespacenine->pHistoryNine){
						
						if($allergic_subtances){
					$ids=$allergic_subtances->id;
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
						if($object){		
							
							$object->setCurrentWeight($authNamespacenine->pHistoryNine['weight']);
							$object->setHeight($authNamespacenine->pHistoryNine['height']);
							$object->setLeastWeighed($authNamespacenine->pHistoryNine['weighed']);
							$object->setMostWeighed($authNamespacenine->pHistoryNine['most_weighed']);
							$object->setWeightGain($authNamespacenine->pHistoryNine['gain']);
							$object->setWeightLoss($authNamespacenine->pHistoryNine['loss']);
							$object->setSleepTime($authNamespacenine->pHistoryNine['average']);
							$object->setFrequentlyTired($authNamespacenine->pHistoryNine['tired']);
							$object->setTroubleSleeping($authNamespacenine->pHistoryNine['sleeping']);
							$object->setYesExplain($authNamespacenine->pHistoryNine['explain']);
							$object->setRecentFevers($authNamespacenine->pHistoryNine['condition']);
							$object->setUseAutomobile($authNamespacenine->pHistoryNine['automobiles']);						
							$object->save();
							}
						}
						}else{
					$ids=$allergiid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){
						
							
							$object->setCurrentWeight($authNamespacenine->pHistoryNine['weight']);
							$object->setHeight($authNamespacenine->pHistoryNine['height']);
							$object->setLeastWeighed($authNamespacenine->pHistoryNine['weighed']);
							$object->setMostWeighed($authNamespacenine->pHistoryNine['most_weighed']);
							$object->setWeightGain($authNamespacenine->pHistoryNine['gain']);
							$object->setWeightLoss($authNamespacenine->pHistoryNine['loss']);
							$object->setSleepTime($authNamespacenine->pHistoryNine['average']);
							$object->setFrequentlyTired($authNamespacenine->pHistoryNine['tired']);
							$object->setTroubleSleeping($authNamespacenine->pHistoryNine['sleeping']);
							$object->setYesExplain($authNamespacenine->pHistoryNine['explain']);
							$object->setRecentFevers($authNamespacenine->pHistoryNine['condition']);
							$object->setUseAutomobile($authNamespacenine->pHistoryNine['automobiles']);						
							$object->save();
							}
						}
						}else{
							$modelnine = new Application_Model_AllergicSubstances();
							$modelnine->setCurrentWeight($authNamespacenine->pHistoryNine['weight']);
							$modelnine->setHeight($authNamespacenine->pHistoryNine['height']);
							$modelnine->setLeastWeighed($authNamespacenine->pHistoryNine['weighed']);
							$modelnine->setMostWeighed($authNamespacenine->pHistoryNine['most_weighed']);
							$modelnine->setWeightGain($authNamespacenine->pHistoryNine['gain']);
							$modelnine->setWeightLoss($authNamespacenine->pHistoryNine['loss']);
							$modelnine->setSleepTime($authNamespacenine->pHistoryNine['average']);
							$modelnine->setFrequentlyTired($authNamespacenine->pHistoryNine['tired']);
							$modelnine->setTroubleSleeping($authNamespacenine->pHistoryNine['sleeping']);
							$modelnine->getYesExplain($authNamespacenine->pHistoryNine['explain']);
							$modelnine->setRecentFevers($authNamespacenine->pHistoryNine['condition']);
							$modelnine->setUseAutomobile($authNamespacenine->pHistoryNine['automobiles']);						
							$allergiid=$modelnine->save();
						}
					}
				   }
				   
				  if($authNamespaceten->pHistoryTen['Condition']){ 
				   foreach($authNamespaceten->pHistoryTen['Condition'] as $Condition)
					{
						if($Condition){
							$RCondition.=$Condition.',';
						}
					}
					}
					
					if($authNamespaceten->pHistoryTen['Year']){
					foreach($authNamespaceten->pHistoryTen['Year'] as $Year)
					{
						if($Year){
							$RYear.=$Year.',';
						}
					}
				}
				if($authNamespaceten->pHistoryTen['Where_Treated'])
				{
					foreach($authNamespaceten->pHistoryTen['Where_Treated'] as $Where_Treated)
					{
						if($Where_Treated){
							$RWhere_Treated.=$Where_Treated.',';
						}
					}
				   
				}
				   
				   if($authNamespaceten->pHistoryTen){
					   
					   	if($allergic_subtances){
					$ids=$allergic_subtances->id;
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setMajorIllness($authNamespaceten->pHistoryTen['surgeries']);
							$object->setIllnessCondition($RCondition);
							$object->setIllnessYear($RYear);					
							$object->setIllnessWhereTreated($RWhere_Treated);						
							$object->save();
							}
						 }
						}else{
					   
					  if($ids){ 
					$ids=$allergiid;
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setMajorIllness($authNamespaceten->pHistoryTen['surgeries']);
							$object->setIllnessCondition($RCondition);
							$object->setIllnessYear($RYear);					
							$object->setIllnessWhereTreated($RWhere_Treated);						
							$object->save();
							}
						}
					  }else{
							$modelten = new Application_Model_AllergicSubstances();
							$modelten->setMajorIllness($authNamespaceten->pHistoryTen['surgeries']);
							$modelten->setIllnessCondition($RCondition);
							$modelten->setIllnessYear($RYear);					
							$modelten->setIllnessWhereTreated($RWhere_Treated);						
							$allergiid=$modelten->save();
					  }
					}
				   }
				   
				   if($authNamespacetwelve->pHistoryTwelve){
					   if($allergic_subtances){
					$ids=$allergic_subtances->id;
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setCurrentDiet($authNamespacetwelve->pHistoryTwelve['diet']);
							$object->setParticularFood($authNamespacetwelve->pHistoryTwelve['intolerance']);
							$object->save();
							}
						}
						
					   }else{
						   
					$ids=$allergiid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setCurrentDiet($authNamespacetwelve->pHistoryTwelve['diet']);
							$object->setParticularFood($authNamespacetwelve->pHistoryTwelve['intolerance']);
							$object->save();
							}
						}
					}else{
							$modeltwelve = new Application_Model_AllergicSubstances();
							$modeltwelve->setCurrentDiet($authNamespacetwelve->pHistoryTwelve['diet']);
							$modeltwelve->setParticularFood($authNamespacetwelve->pHistoryTwelve['intolerance']);
							$allergiid=$modeltwelve->save();						
					}
				}
				   }
				   
				   if($authNamespacethirteen->pHistoryThirteen){
					    if($allergic_subtances){
					$ids=$allergic_subtances->id;
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setExerciseRegularly($authNamespacethirteen->pHistoryThirteen['exercise']);
							$object->setTypeOfExercise($authNamespacethirteen->pHistoryThirteen['often']);
							$object->setPhysicalActivity($authNamespacethirteen->pHistoryThirteen['physical']);
							$object->setPhysicalActivityContent($authNamespacethirteen->pHistoryThirteen['message']);
							$object->save();
							}
						}
						}else{
					$ids=$allergiid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_AllergicSubstances();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setExerciseRegularly($authNamespacethirteen->pHistoryThirteen['exercise']);
							$object->setTypeOfExercise($authNamespacethirteen->pHistoryThirteen['often']);
							$object->setPhysicalActivity($authNamespacethirteen->pHistoryThirteen['physical']);
							$object->setPhysicalActivityContent($authNamespacethirteen->pHistoryThirteen['message']);
							$object->save();
							}
						}
					}else{ 	
							$modelthirteen = new Application_Model_AllergicSubstances();
							$modelthirteen->setExerciseRegularly($authNamespacethirteen->pHistoryThirteen['exercise']);
							$modelthirteen->setTypeOfExercise($authNamespacethirteen->pHistoryThirteen['often']);
							$modelthirteen->setPhysicalActivity($authNamespacethirteen->pHistoryThirteen['physical']);
							$modelthirteen->setPhysicalActivityContent($authNamespacethirteen->pHistoryThirteen['message']);
							$allergiid=$modelthirteen->save();
						
					}
						}
				  }
				
			
			
			/*Save data of step eight nine ten eleven */
			/*Save data of step fourteen fifteen sixteen and seventeen */
			
		
				 //echo '<pre>';print_r($authNamespacefifteen->pHistoryFifteen);die;
					if($authNamespacefourteen->pHistoryFourteen){
						

					foreach($authNamespacefourteen->pHistoryFourteen['Sisters_Brothers_living'] as $Sisters_Brothers_living)
					{
						if($Sisters_Brothers_living){
						$RSisters_Brothers_living.=$Sisters_Brothers_living.',';
						}
					}
					
					foreach($authNamespacefourteen->pHistoryFourteen['Sisters_Brothers_Deceased'] as $Sisters_Brothers_Deceased)
					{
						if($Sisters_Brothers_Deceased){
						$RSisters_Brothers_Deceased.=$Sisters_Brothers_Deceased.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Sisters_Brothers_Age'] as $Sisters_Brothers_Age)
					{
						if($Sisters_Brothers_Age){
						$RSisters_Brothers_Age.=$Sisters_Brothers_Age.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Sisters_Brothers_Major_Illnesses'] as $Sisters_Brothers_Major_Illnesses)
					{
						if($Sisters_Brothers_Major_Illnesses){
						$RSisters_Brothers_Major_Illnesses.=$Sisters_Brothers_Major_Illnesses.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Aunts_Uncles_living'] as $Aunts_Uncles_living)
					{
						if($Aunts_Uncles_living){
						$RAunts_Uncles_living.=$Aunts_Uncles_living.',';
						}
					}
					
					foreach($authNamespacefourteen->pHistoryFourteen['Aunts_Uncles_Deceased'] as $Aunts_Uncles_Deceased)
					{
						if($Aunts_Uncles_Deceased){
						$RAunts_Uncles_Deceased.=$Aunts_Uncles_Deceased.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Aunts_Uncles_Age'] as $Aunts_Uncles_Age)
					{
						if($Aunts_Uncles_Age){
						$RAunts_Uncles_Age.=$Aunts_Uncles_Age.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Aunts_Uncles_Major_Illnesses'] as $Aunts_Uncles_Major_Illnesses)
					{
						if($Aunts_Uncles_Major_Illnesses){
						$RAunts_Uncles_Major_Illnesses.=$Aunts_Uncles_Major_Illnesses.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Children_living'] as $Children_living)
					{
						if($Children_living){
						$RChildren_living.=$Children_living.',';
						}
					}
					
					foreach($authNamespacefourteen->pHistoryFourteen['Children_living_Deceased'] as $Children_living_Deceased)
					{
						if($Children_living_Deceased){
						$RChildren_living_Deceased.=$Children_living_Deceased.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Children_living_Age'] as $Children_living_Age)
					{
						if($Children_living_Age){
						$RChildren_living_Age.=$Children_living_Age.',';
						}
					}
					foreach($authNamespacefourteen->pHistoryFourteen['Children_living_Major_Illnesses'] as $Children_living_Major_Illnesses)
					{
						if($Children_living_Major_Illnesses){
						$RChildren_living_Major_Illnesses.=$Children_living_Major_Illnesses.',';
						}
					}				
					if($family_medical_history)
					{
						$ids=$family_medical_history->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
					
					$object->setPatientId($usersNs->userId);					
					$object->setMotherLiving($authNamespacefourteen->pHistoryFourteen['Mother_living']);
					$object->setMotherDiceased($authNamespacefourteen->pHistoryFourteen['Mother_Deceased']);
					$object->setMotherAge($authNamespacefourteen->pHistoryFourteen['Mother_Age']);
					$object->setMotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Mother_Major_Illnesses']);

					$object->setFatherLiving($authNamespacefourteen->pHistoryFourteen['Father_living']);
					$object->setFatherDiceased($authNamespacefourteen->pHistoryFourteen['Father_Deceased']);
					$object->setFatherAge($authNamespacefourteen->pHistoryFourteen['Father_Age']);
					$object->setFatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Father_Major_Illnesses']);

					$object->setMaternalGrandmotherLiving($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_living']);
					$object->setMaternalGrandmotherDiceased($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Deceased']);
					$object->setMaternalGrandmotherAge($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Age']);
					$object->setMaternalGrandmotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Major_Illnesses']);

					$object->setMaternalGrandfatherLiving($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_living']);
					$object->setMaternalGrandfatherDiceased($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Deceased']);
					$object->setMaternalGrandfatherAge($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Age']);
					$object->setMaternalGrandfatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Major_Illnesses']);
									
									
					$object->setPaternalGrandmotherLiving($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_living']);
					$object->setPaternalGrandmotherDiceased($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Deceased']);
					$object->setPaternalGrandmotherAge($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Age']);
					$object->setPaternalGrandmotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Major_Illnesses']);	

					$object->setPaternalGrandfatherLiving($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_living']);
					$object->setPaternalGrandfatherDiceased($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Deceased']);
					$object->setPaternalGrandfatherAge($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Age']);
					$object->setPaternalGrandfatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Major_Illnesses']);
					$object->setSistersBrothersLivings($RSisters_Brothers_living);
					$object->setSistersBrothersDiceased($RSisters_Brothers_Deceased);
					$object->setSistersBrothersAge($Sisters_Brothers_Age);
					$object->setSistersBrothersMajorIllness($RSisters_Brothers_Major_Illnesses);
					$object->setAuntsUnclesLiving($RAunts_Uncles_living);
					$object->setAuntsUnclesDiceased($RAunts_Uncles_Deceased);
					$object->setAuntsUnclesAge($RAunts_Uncles_Age);
					$object->setAuntsUnclesMajorIllness($RAunts_Uncles_Major_Illnesses);
					$object->setChildrenLiving($RChildren_living);
					$object->setChildrenDiceased($RChildren_living_Deceased);
					$object->setChildrenAge($RChildren_living_Age);
					$object->setChildrenMajorIllness($RChildren_living_Major_Illnesses);					
					$object->setCreateTime(time());
					$object->setUpdateTime(time());
				
					$object->save();
							}
						}
						
					}else{
					$patient_info_fourteen = new Application_Model_FamilyMedicalHistory();
					$patient_info_fourteen->setPatientId($usersNs->userId);
					
					$patient_info_fourteen->setMotherLiving($authNamespacefourteen->pHistoryFourteen['Mother_living']);					
					$patient_info_fourteen->setMotherDiceased($authNamespacefourteen->pHistoryFourteen['Mother_Deceased']);
					$patient_info_fourteen->setMotherAge($authNamespacefourteen->pHistoryFourteen['Mother_Age']);
					$patient_info_fourteen->setMotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Mother_Major_Illnesses']);

					$patient_info_fourteen->setFatherLiving($authNamespacefourteen->pHistoryFourteen['Father_living']);
					$patient_info_fourteen->setFatherDiceased($authNamespacefourteen->pHistoryFourteen['Father_Deceased']);
					$patient_info_fourteen->setFatherAge($authNamespacefourteen->pHistoryFourteen['Father_Age']);
					$patient_info_fourteen->setFatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Father_Major_Illnesses']);

					$patient_info_fourteen->setMaternalGrandmotherLiving($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_living']);
					$patient_info_fourteen->setMaternalGrandmotherDiceased($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Deceased']);
					$patient_info_fourteen->setMaternalGrandmotherAge($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Age']);
					$patient_info_fourteen->setMaternalGrandmotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Maternal_Grandmother_Major_Illnesses']);

					$patient_info_fourteen->setMaternalGrandfatherLiving($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_living']);
					$patient_info_fourteen->setMaternalGrandfatherDiceased($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Deceased']);
					$patient_info_fourteen->setMaternalGrandfatherAge($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Age']);
					$patient_info_fourteen->setMaternalGrandfatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Maternal_Grandfather_Major_Illnesses']);
									
									
					$patient_info_fourteen->setPaternalGrandmotherLiving($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_living']);
					$patient_info_fourteen->setPaternalGrandmotherDiceased($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Deceased']);
					$patient_info_fourteen->setPaternalGrandmotherAge($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Age']);
					$patient_info_fourteen->setPaternalGrandmotherMajorIllness($authNamespacefourteen->pHistoryFourteen['Paternal_Grandmother_Major_Illnesses']);	

					$patient_info_fourteen->setPaternalGrandfatherLiving($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_living']);
					$patient_info_fourteen->setPaternalGrandfatherDiceased($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Deceased']);
					$patient_info_fourteen->setPaternalGrandfatherAge($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Age']);
					$patient_info_fourteen->setPaternalGrandfatherMajorIllness($authNamespacefourteen->pHistoryFourteen['Paternal_Grandfather_Major_Illnesses']);
					$patient_info_fourteen->setSistersBrothersLivings($RSisters_Brothers_living);
					$patient_info_fourteen->setSistersBrothersDiceased($RSisters_Brothers_Deceased);
					$patient_info_fourteen->setSistersBrothersAge($Sisters_Brothers_Age);
					$patient_info_fourteen->setSistersBrothersMajorIllness($RSisters_Brothers_Major_Illnesses);
					$patient_info_fourteen->setAuntsUnclesLiving($RAunts_Uncles_living);
					$patient_info_fourteen->setAuntsUnclesDiceased($RAunts_Uncles_Deceased);
					$patient_info_fourteen->setAuntsUnclesAge($RAunts_Uncles_Age);
					$patient_info_fourteen->setAuntsUnclesMajorIllness($RAunts_Uncles_Major_Illnesses);
					$patient_info_fourteen->setChildrenLiving($RChildren_living);
					$patient_info_fourteen->setChildrenDiceased($RChildren_living_Deceased);
					$patient_info_fourteen->setChildrenAge($RChildren_living_Age);
					$patient_info_fourteen->setChildrenMajorIllness($RChildren_living_Major_Illnesses);					
					$patient_info_fourteen->setCreateTime(time());
					$patient_info_fourteen->setUpdateTime(time());
					//echo $RChildren_living_Major_Illnesses;
					//echo '<pre>';print_r($patient_info_fourteen);
					$familyid=$patient_info_fourteen->save();	
					}					
					}
					
					if($authNamespacefifteen->pHistoryFifteen){
						
						if($family_medical_history)
					{
						$ids=$family_medical_history->id;
						
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setPneumoniaVaccine($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine']);
							$object->setPneumoniaVaccineYear($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine_year']);
							$object->setInflueza($authNamespacefifteen->pHistoryFifteen['Influeza']);
							$object->setInfluezaYear($authNamespacefifteen->pHistoryFifteen['Influeza_year']);
							$object->setTuberculin($authNamespacefifteen->pHistoryFifteen['Tuberculin']);
							$object->setTuberculinYear($authNamespacefifteen->pHistoryFifteen['Tuberculin_skin_test_year']);
							$object->setBcg($authNamespacefifteen->pHistoryFifteen['BCG']);
							$object->setBcgYear($authNamespacefifteen->pHistoryFifteen['BCG_year']);
							$object->setDiptheria($authNamespacefifteen->pHistoryFifteen['Diptheria']);
							$object->setDiptheriaYear($authNamespacefifteen->pHistoryFifteen['Diptheria_year']);
							$object->setMeasles($authNamespacefifteen->pHistoryFifteen['Measles']);
							$object->setMeaslesYear($authNamespacefifteen->pHistoryFifteen['Rubella_year']);
							$object->setHepatitis($authNamespacefifteen->pHistoryFifteen['Hepatitis_A']);
							$object->setHepatitisYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_A_year']);
							$object->setHepatitisB($authNamespacefifteen->pHistoryFifteen['Hepatitis_B']);
							$object->setHepatitisBYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_B_year']);
							$object->save();
							}
						}
					}else{
						
					$ids=$familyid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setPneumoniaVaccine($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine']);
							$object->setPneumoniaVaccineYear($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine_year']);
							$object->setInflueza($authNamespacefifteen->pHistoryFifteen['Influeza']);
							$object->setInfluezaYear($authNamespacefifteen->pHistoryFifteen['Influeza_year']);
							$object->setTuberculin($authNamespacefifteen->pHistoryFifteen['Tuberculin']);
							$object->setTuberculinYear($authNamespacefifteen->pHistoryFifteen['Tuberculin_skin_test_year']);
							$object->setBcg($authNamespacefifteen->pHistoryFifteen['BCG']);
							$object->setBcgYear($authNamespacefifteen->pHistoryFifteen['BCG_year']);
							$object->setDiptheria($authNamespacefifteen->pHistoryFifteen['Diptheria']);
							$object->setDiptheriaYear($authNamespacefifteen->pHistoryFifteen['Diptheria_year']);
							$object->setMeasles($authNamespacefifteen->pHistoryFifteen['Measles']);
							$object->setMeaslesYear($authNamespacefifteen->pHistoryFifteen['Rubella_year']);
							$object->setHepatitis($authNamespacefifteen->pHistoryFifteen['Hepatitis_A']);
							$object->setHepatitisYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_A_year']);
						    $object->setHepatitisB($authNamespacefifteen->pHistoryFifteen['Hepatitis_B']);
							$object->setHepatitisBYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_B_year']);
							$object->save();
							}
						}
					}else{
							$modelfifteen = new Application_Model_FamilyMedicalHistory();
							$modelfifteen->setPneumoniaVaccine($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine']);
							$modelfifteen->setPneumoniaVaccineYear($authNamespacefifteen->pHistoryFifteen['Pneumonia_Vaccine_year']);
							$modelfifteen->setInflueza($authNamespacefifteen->pHistoryFifteen['Influeza']);
							$modelfifteen->setInfluezaYear($authNamespacefifteen->pHistoryFifteen['Influeza_year']);
							$modelfifteen->setTuberculin($authNamespacefifteen->pHistoryFifteen['Tuberculin']);
							$modelfifteen->setTuberculinYear($authNamespacefifteen->pHistoryFifteen['Tuberculin_skin_test_year']);
							$modelfifteen->setBcg($authNamespacefifteen->pHistoryFifteen['BCG']);
							$modelfifteen->setBcgYear($authNamespacefifteen->pHistoryFifteen['BCG_year']);
							$modelfifteen->setDiptheria($authNamespacefifteen->pHistoryFifteen['Diptheria']);
							$modelfifteen->setDiptheriaYear($authNamespacefifteen->pHistoryFifteen['Diptheria_year']);
							$modelfifteen->setMeasles($authNamespacefifteen->pHistoryFifteen['Measles']);
							$modelfifteen->setMeaslesYear($authNamespacefifteen->pHistoryFifteen['Rubella_year']);
							$modelfifteen->setHepatitis($authNamespacefifteen->pHistoryFifteen['Hepatitis_A']);
							$modelfifteen->setHepatitisYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_A_year']);
							$modelfifteen->setHepatitisB($authNamespacefifteen->pHistoryFifteen['Hepatitis_B']);
							$modelfifteen->setHepatitisBYear($authNamespacefifteen->pHistoryFifteen['Hepatitis_B_year']);
							$familyid=$modelfifteen->save();
						
					}
					}
				   }
				   
				   if($authNamespacesixteen->pHistorySixteen){
					   
					   if($family_medical_history)
					{
						$ids=$family_medical_history->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setTestedChickenPox($authNamespacesixteen->pHistorySixteen['Chicken_Pox']);
							$object->setTestedTuberculosis($authNamespacesixteen->pHistorySixteen['Tuberculosis']);
							$object->setTestedHiv($authNamespacesixteen->pHistorySixteen['HIV']);
							$object->setTestedHepatitis($authNamespacesixteen->pHistorySixteen['Hepatitis']);
							$object->setTestedVenereal($authNamespacesixteen->pHistorySixteen['Venereal']);
							$object->setType($authNamespacesixteen->pHistorySixteen['type']);
							$object->setSpecify($authNamespacesixteen->pHistorySixteen['Specify']);
							$object->save();
							}
						}
						
					}else{
						
					$ids=$familyid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setTestedChickenPox($authNamespacesixteen->pHistorySixteen['Chicken_Pox']);
							$object->setTestedTuberculosis($authNamespacesixteen->pHistorySixteen['Tuberculosis']);
							$object->setTestedHiv($authNamespacesixteen->pHistorySixteen['HIV']);
							$object->setTestedHepatitis($authNamespacesixteen->pHistorySixteen['Hepatitis']);
							$object->setTestedVenereal($authNamespacesixteen->pHistorySixteen['Venereal']);
							$object->setType($authNamespacesixteen->pHistorySixteen['type']);
							$object->setSpecify($authNamespacesixteen->pHistorySixteen['Specify']);
							$object->save();
							}
						}
					}else{
							$modelsixteen = new Application_Model_FamilyMedicalHistory();
							$modelsixteen->setTestedChickenPox($authNamespacesixteen->pHistorySixteen['Chicken_Pox']);
							$modelsixteen->setTestedTuberculosis($authNamespacesixteen->pHistorySixteen['Tuberculosis']);
							$modelsixteen->setTestedHiv($authNamespacesixteen->pHistorySixteen['HIV']);
							$modelsixteen->setTestedHepatitis($authNamespacesixteen->pHistorySixteen['Hepatitis']);
							$modelsixteen->setTestedVenereal($authNamespacesixteen->pHistorySixteen['Venereal']);
							$modelsixteen->setType($authNamespacesixteen->pHistorySixteen['type']);
							$modelsixteen->setSpecify($authNamespacesixteen->pHistorySixteen['Specify']);
							$familyid=$modelsixteen->save();
						
					}
					}
				  }
				  
				   if($authNamespaceseventeen->pHistorySeventeen){
					   if($family_medical_history)
					{
						$ids=$family_medical_history->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setChestXRayDate($authNamespaceseventeen->pHistorySeventeen['Chest_Date']);
							$object->setChestXRayResult($authNamespaceseventeen->pHistorySeventeen['Chest_Result']);
							$object->setCholesterolLevelDate($authNamespaceseventeen->pHistorySeventeen['Cholesterol_date']);
							$object->setCholesterolLevelResult($authNamespaceseventeen->pHistorySeventeen['Cholesterol_result']);
							$object->setTriglycerideLevelDate($authNamespaceseventeen->pHistorySeventeen['Triglyceride_date']);
							$object->setTriglycerideLevelResult($authNamespaceseventeen->pHistorySeventeen['Triglyceride_result']);
							$object->setOtherLipidDataDate($authNamespaceseventeen->pHistorySeventeen['Lipid_date']);
							$object->setOtherLipidDataResult($authNamespaceseventeen->pHistorySeventeen['Lipid_result']);
							$object->setColonoscopyDate($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_date']);
							$object->setColonoscopyResult($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_result']);
							$object->setMammogramDate($authNamespaceseventeen->pHistorySeventeen['Mammogram_date']);
							$object->setMammogramResult($authNamespaceseventeen->pHistorySeventeen['Mammogram_result']);
							$object->setPapTestDate($authNamespaceseventeen->pHistorySeventeen['Pap_date']);
							$object->setPapTestResult($authNamespaceseventeen->pHistorySeventeen['Pap_result']);
							$object->setBoneDensityTestDate($authNamespaceseventeen->pHistorySeventeen['Density_date']);
							$object->setBoneDensityTestResult($authNamespaceseventeen->pHistorySeventeen['Density_result']);
							$object->save();
							}
						}
					}else{
						
					$ids=$familyid;
					if($ids){
					$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							if($object){					
							$object->setChestXRayDate($authNamespaceseventeen->pHistorySeventeen['Chest_Date']);
							$object->setChestXRayResult($authNamespaceseventeen->pHistorySeventeen['Chest_Result']);
							$object->setCholesterolLevelDate($authNamespaceseventeen->pHistorySeventeen['Cholesterol_date']);
							$object->setCholesterolLevelResult($authNamespaceseventeen->pHistorySeventeen['Cholesterol_result']);
							$object->setTriglycerideLevelDate($authNamespaceseventeen->pHistorySeventeen['Triglyceride_date']);
							$object->setTriglycerideLevelResult($authNamespaceseventeen->pHistorySeventeen['Triglyceride_result']);
							$object->setOtherLipidDataDate($authNamespaceseventeen->pHistorySeventeen['Lipid_date']);
							$object->setOtherLipidDataResult($authNamespaceseventeen->pHistorySeventeen['Lipid_result']);
							$object->setColonoscopyDate($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_date']);
							$object->setColonoscopyResult($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_result']);
							$object->setMammogramDate($authNamespaceseventeen->pHistorySeventeen['Mammogram_date']);
							$object->setMammogramResult($authNamespaceseventeen->pHistorySeventeen['Mammogram_result']);
							$object->setPapTestDate($authNamespaceseventeen->pHistorySeventeen['Pap_date']);
							$object->setPapTestResult($authNamespaceseventeen->pHistorySeventeen['Pap_result']);
							$object->setBoneDensityTestDate($authNamespaceseventeen->pHistorySeventeen['Density_date']);
							$object->setBoneDensityTestResult($authNamespaceseventeen->pHistorySeventeen['Density_result']);
							$object->save();
							}
						}
					}else{
							$modelseventeen = new Application_Model_FamilyMedicalHistory();
							$modelseventeen->setChestXRayDate($authNamespaceseventeen->pHistorySeventeen['Chest_Date']);
							$modelseventeen->setChestXRayResult($authNamespaceseventeen->pHistorySeventeen['Chest_Result']);
							$modelseventeen->setCholesterolLevelDate($authNamespaceseventeen->pHistorySeventeen['Cholesterol_date']);
							$modelseventeen->setCholesterolLevelResult($authNamespaceseventeen->pHistorySeventeen['Cholesterol_result']);
							$modelseventeen->setTriglycerideLevelDate($authNamespaceseventeen->pHistorySeventeen['Triglyceride_date']);
							$modelseventeen->setTriglycerideLevelResult($authNamespaceseventeen->pHistorySeventeen['Triglyceride_result']);
							$modelseventeen->setOtherLipidDataDate($authNamespaceseventeen->pHistorySeventeen['Lipid_date']);
							$modelseventeen->setOtherLipidDataResult($authNamespaceseventeen->pHistorySeventeen['Lipid_result']);
							$modelseventeen->setColonoscopyDate($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_date']);
							$modelseventeen->setColonoscopyResult($authNamespaceseventeen->pHistorySeventeen['Colonoscopy_result']);
							$modelseventeen->setMammogramDate($authNamespaceseventeen->pHistorySeventeen['Mammogram_date']);
							$modelseventeen->setMammogramResult($authNamespaceseventeen->pHistorySeventeen['Mammogram_result']);
							$modelseventeen->setPapTestDate($authNamespaceseventeen->pHistorySeventeen['Pap_date']);
							$modelseventeen->setPapTestResult($authNamespaceseventeen->pHistorySeventeen['Pap_result']);
							$modelseventeen->setBoneDensityTestDate($authNamespaceseventeen->pHistorySeventeen['Density_date']);
							$modelseventeen->setBoneDensityTestResult($authNamespaceseventeen->pHistorySeventeen['Density_result']);
							$familyid=$modelseventeen->save();
					}
					}
					$this->redirect('/user/index/patient-information-full');
				   }
				  $Auth=new Base_Auth_Auth();
				  $Auth->doClearsesion;
				//$this->redirect('/user/index/patient-information-stepseventeen');
				}
			
			 $form=new Admin_Form_PatientMedicalHistory();
				 
			$elements = $form->getElements();
			$form->clearDecorators();
			foreach ($elements as $element){
				$element->removeDecorator('label');
				$element->removeDecorator('row');
				$element->removeDecorator('data');
			}

			$this->view->form = $form;
		
			$this->view->patient_history=$patient_history;
			
			$physician_details = new Application_Model_PhysicianDetails();
			$physician_details = $physician_details->fetchRow("patient_id='{$usersNs->userId}'");
			$this->view->physician_details=$physician_details;			
			
			$this->view->prescription_details=$prescription_details;			
			
			$this->view->allergic_subtances=$allergic_subtances;			
			
			$this->view->family_medical_history=$family_medical_history;
			
			
		
		
		}
		
		public function patientInformationSuccessAction() {
		
				$request = $this->getRequest();
				$options = $request->getPost();
				$usersNs = new Zend_Session_Namespace("members");
				$Patient_history = new Application_Model_PatientHistory();
				$patient_history = $Patient_history->fetchRow("patient_id='{$usersNs->userId}'");
				
				$physician_details = new Application_Model_PhysicianDetails();
				$physician_details = $physician_details->fetchRow("patient_id='{$usersNs->userId}'");
				
				$prescription_details = new Application_Model_PatientPrescription();
				$prescription_details = $prescription_details->fetchRow("patient_id='{$usersNs->userId}'");
				
				$allergic_subtances = new Application_Model_AllergicSubstances();
				$allergic_subtances = $allergic_subtances->fetchRow("patient_id='{$usersNs->userId}'");
				
				$family_medical_history = new Application_Model_FamilyMedicalHistory();
				$family_medical_history = $family_medical_history->fetchRow("patient_id='{$usersNs->userId}'");
				
				if ($request->isPost()) {
						
						if($options['profile_holder']=="profile_holder")
						{	
							
							$ids=$patient_history->id;
							 $idArray = explode(',', $ids);
							 $model = new Application_Model_PatientHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
							$month_dob=$options['month_dob'];
							$date_dob=$options['date_dob'];
							$year_dob=$options['year_dob'];
							$date_of_birth=$year_dob.'-'.$month_dob.'-'.$date_dob;
							$object->setProfileHolderFirstName($options['profile_holder_first_name']);
							$object->setProfileHolderLastName($options['profile_holder_last_name']);
							$object->setProfileHolderDob($date_of_birth);
							$object->setStatus(1);
							$object->save();
							}
						  
						}
						
						if($options['update_data']=="update_data"){
						
						$physician=$options['physician'];
						$month_dob=$options['month_dob'];
						$date_dob=$options['date_dob'];
						$year_dob=$options['year_dob'];
						$date_of_birth=$year_dob.'-'.$month_dob.'-'.$date_dob;
						$datevisit=$options['date_of_visit'];
						$datev=explode("-",$datevisit);							
						$date_of_visit=$datev[2].'-'.$datev[1].'-'.$datev[0];
						$patient=$options['patient'];
						$address=$options['address'];
						$telephone_day=$options['telephone_day'];
						$telephone_evening=$options['telephone_evening'];
						$fax=$options['fax'];
						$email=$options['email'];
						$social_security_no=$options['social_security_no'];
						$birth_place=$options['birth_place'];
						$employed=$options['employed'];
						$retired=$options['retired'];
						$occupation=$options['occupation'];
						$self=$options['self'];
						$other_person=$options['other_person'];
						$maritalstatus=$options['maritalstatus'];
						$case_of_emergency=$options['case_of_emergency'];
						$contact_person_name=$options['contact_person_name'];
						$contact_person_address=$options['contact_person_address'];
						$contact_person_telephone_day=$options['contact_person_telephone_day'];
						$contact_person_telephone_evening=$options['contact_person_telephone_evening'];
						$relation_ship_to_you=$options['relation_ship_to_you'];
						
						$ids1=$patient_history->id;
							$idArray1 = explode(',', $ids1);
							$model = new Application_Model_PatientHistory();
							foreach ($idArray1 as $id) {
							
							$object = $model->fetchRow("id={$id}");
							if($object){
							$object->setPatientId($usersNs->userId);
							$object->setPhysicianName($physician);
							$object->setDateOfVisit($date_of_visit);
							$object->setPatientName($patient);
							$object->setPatientAddress($address);
							$object->setTelephoneDay($telephone_day);
							$object->setTelephoneEvening($telephone_evening);
							$object->setFax($fax);
							$object->setEmaiId($email);
							$object->setSocialSecurityNumber($social_security_no);
							$object->setDob($date_of_birth);
							$object->setBirthPlace($birth_place);
							$object->setEmployed($employed);
							$object->setRetired($retired);
							$object->setOccupation($occupation);
							$object->setSelf($self);
							$object->setOtherPerson($other_person);
							$object->setMaritalstatus($maritalstatus);
							$object->setCaseOfEmergency($case_of_emergency);
							$object->setContactPersonName($contact_person_name);
							$object->setContactPersonAddress($contact_person_address);
							$object->setContactPersonTelephoneDay($contact_person_telephone_day);
							$object->setContactPersonTelephoneEvening($contact_person_telephone_evening);
							$object->setRelationshipToYou($relation_ship_to_you);
							$object->setCreateTime(time());
							$object->setUpdateTime(time());
							
							$object->save();
						//echo "<pre>";print_r($options	);die;
						
						}
					}
					
					/*Physical details*/
					
						foreach($options['physian'] as $physician)
				{
					if($physician){
						$Rphysician.=$physician.',';
					}
				}
				foreach($options['speciality'] as $speciality)
				{	
					if($speciality){
						$Rspeciality.=$speciality.',';
					}
				}
				foreach($options['Address'] as $Address)
				{
					if($Address){
						$RAddress.=$Address.',';
					}
				}
				foreach($options['Telephone'] as $Telephone)
				{
					if($Telephone){
						$RTelephone.=$Telephone.',';
					}
				}
				foreach($options['Receive_Report'] as $Receive_Report)
				{
					if($Receive_Report){
						$RReceive_Report.=$Receive_Report.',';
					}
				}
							
				$ids2=$physician_details->id;
				
				$idArray2 = explode(',', $ids2);
					$model = new Application_Model_PhysicianDetails();
						foreach ($idArray2 as $id) {
						$object = $model->fetchRow("id={$id}");
						if($object){
				$object->setPatientId($usersNs->userId);
				$object->setPhysicianName($Rphysician);
				$object->setSpeciality($Rspeciality);
				$object->setAddress($RAddress);
				$object->setTelephone($RTelephone);
				$object->setReceiveReport($RReceive_Report);
				$object->setPhysicianCare($options['care']);
				$object->setReasonPhysicianCare($options['msg']);
				$object->setCreateTime(time());
				$object->setUpdateTime(time());
				$object->save();
					 }
				  }				
					
				  
				/*Prescription Details*/  
				
				foreach($options['Name_of_Supplement'] as $Name_of_Supplement)
				{
					if($Name_of_Supplement){
						$RName_of_Supplement.=$Name_of_Supplement.',';
					}
				}
				
				foreach($options['Dosage'] as $Dosage)
				{
					if($Dosage){
						$RDosage.=$Dosage.',';
					}
				}
				foreach($options['Freqency'] as $Freqency)
				{
					if($Freqency){
						$RFreqency.=$Freqency.',';
					}
				}
				foreach($options['Side_Effects'] as $Side_Effects)
				{
					if($Side_Effects){
						$RSide_Effects.=$Side_Effects.',';
					}
				}
				
				foreach($options['Supplement'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($options['Dossage'] as $Dossage)
				{
					if($Dossage){
						$RDossage.=$Dossage.',';
					}
				}
				foreach($options['Frequency'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($options['Any_Side_Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				foreach($options['Name_of_Supplementt'] as $Supplement)
				{
					if($Supplement){
						$RSupplement.=$Supplement.',';
					}
				}
				
				foreach($options['Dosagge'] as $Dossage)
				{
						$RDossage.=$Dossage.',';
				}
				foreach($options['Freqenccy'] as $Frequency)
				{
					if($Frequency){
						$RFrequency.=$Frequency.',';
					}
				}
				foreach($options['Effects'] as $Any_Side_Effects)
				{
					if($Any_Side_Effects){
						$RAny_Side_Effects.=$Any_Side_Effects.',';
					}
				}
				
				
						
				foreach($options['Name_of_Medication'] as $Name_of_Medication)
				{
					if($Name_of_Medication){
						$RName_of_Medication.=$Name_of_Medication.',';
					}
				}
				
				foreach($options['Reaction'] as $Reaction)
				{
					if($Reaction){
						$RReaction.=$Reaction.',';
					}
				}
				
				$ids3=$prescription_details->id;				
				$idArray3 = explode(',', $ids3);
					
						foreach ($idArray3 as $id) {
						$model = new Application_Model_PatientPrescription();
						$patient_info_four = $model->fetchRow("id={$id}");
						if($patient_info_four){
				$patient_info_four->setPatientId($usersNs->userId);
				$patient_info_four->setMedication($options['medications']);
				$patient_info_four->setNameOfSupplement($RName_of_Supplement);
				$patient_info_four->setDosage($RDosage);
				$patient_info_four->setFreqency($RFreqency);
				$patient_info_four->setSideEffects($RSide_Effects);		
				$patient_info_four->setNonPrescription($options['non-prescription']);
				$patient_info_four->setNonPrescriptionSupplement($RSupplement);
				$patient_info_four->setNonPrescriptionDosage($RDossage);
				$patient_info_four->setNonPrescriptionFreqency($RFrequency);
				$patient_info_four->setNonPrescriptionSideEffects($RAny_Side_Effects);		
			//	$patient_info_four->setNonPrescription($authNamespacethree->pHistoryFive['non-prescription']);
				$patient_info_four->setMedicines($options['medicines']);
				$patient_info_four->setVitaminSupplement($RSupplement);
				$patient_info_four->setVitaminDosage($RDossage);
				$patient_info_four->setVitaminFreqency($RFrequency);
				$patient_info_four->setVitaminSideEffects($RAny_Side_Effects);		
				$patient_info_four->setNonPrescription($options['non-prescription']);
				$patient_info_four->setBadReactionSuppliment($options['badreaction']);
				$patient_info_four->setBadReactionSupplimentName($RName_of_Medication);
				$patient_info_four->setReaction($RReaction);
				$patient_info_four->setCreateTime(time());
				$patient_info_four->setUpdateTime(time());
				
				$patient_info_four->save();
					}
				}
	
					
				/*allergic substances*/	
				
				foreach($options['Name_of_Medications'] as $Name_of_Medications)
				{
					if($Name_of_Medications){
						$RName_of_Medications.=$Name_of_Medications.',';
					}
				}
				
				foreach($options['Reactions'] as $Reactions)
				{
					if($Reactions){
						$RReactions.=$Reactions.',';
					}
				}	

					
					 foreach($options['Condition'] as $Condition)
				{
					if($Condition){
						$RCondition.=$Condition.',';
					}
				}
				
				foreach($options['Year'] as $Year)
				{
					if($Year){
						$RYear.=$Year.',';
					}
				}
				foreach($options['Where_Treated'] as $Where_Treated)
				{
					if($Where_Treated){
						$RWhere_Treated.=$Where_Treated.',';
					}
				}	
				
				
				$substances=$options['substances'];
				
				
				$ids4=$allergic_subtances->id;					
				$idArray4 = explode(',', $ids4);
					$model = new Application_Model_AllergicSubstances();
					foreach ($idArray4 as $id) {
					$object = $model->fetchRow("id={$id}");
						
						if($object){								
					$object->setPatientId($usersNs->userId);
					$object->setAllergicSubstancesStatus($substances);
					$object->setNameOfMedication($RName_of_Medications);
					$object->setReaction($RReactions);
					$object->setCurrentWeight($options['weight']);
					$object->setHeight($options['height']);
					$object->setLeastWeighed($options['weighed']);
					$object->setMostWeighed($options['most_weighed']);
					$object->setWeightGain($options['gain']);
					$object->setWeightLoss($options['loss']);
					$object->setSleepTime($options['average']);
					$object->setFrequentlyTired($options['tired']);
					$object->setTroubleSleeping($options['sleeping']);
					$object->setYesExplain($options['explain']);
					$object->setRecentFevers($options['condition']);
					$object->setUseAutomobile($options['automobiles']);	
					$object->setMajorIllness($options['surgeries']);
					$object->setIllnessCondition($RCondition);
					$object->setIllnessYear($RYear);	
					$object->setCurrentDiet($options['diet']);
					$object->setParticularFood($options['intolerance']);					
					$object->setIllnessWhereTreated($RWhere_Treated);	
					$object->setExerciseRegularly($options['exercise']);
					$object->setTypeOfExercise($options['often']);
					$object->setPhysicalActivity($options['physical']);
					$object->setPhysicalActivityContent($options['message']);					
					$object->setCreateTime(time());
					$object->setUpdateTime(time());
					$object->save();	
						}
					}
					
				/*Family Medical History*/
						foreach($options['Sisters_Brothers_living'] as $Sisters_Brothers_living)
					{
						if($Sisters_Brothers_living){
						$RSisters_Brothers_living.=$Sisters_Brothers_living.',';
						}
					}
					
					foreach($options['Sisters_Brothers_Deceased'] as $Sisters_Brothers_Deceased)
					{
						if($Sisters_Brothers_Deceased){
						$RSisters_Brothers_Deceased.=$Sisters_Brothers_Deceased.',';
						}
					}
					foreach($options['Sisters_Brothers_Age'] as $Sisters_Brothers_Age)
					{
						if($Sisters_Brothers_Age){
						$RSisters_Brothers_Age.=$Sisters_Brothers_Age.',';
						}
					}
					foreach($options['Sisters_Brothers_Major_Illnesses'] as $Sisters_Brothers_Major_Illnesses)
					{
						if($Sisters_Brothers_Major_Illnesses){
						$RSisters_Brothers_Major_Illnesses.=$Sisters_Brothers_Major_Illnesses.',';
						}
					}
					foreach($options['Aunts_Uncles_living'] as $Aunts_Uncles_living)
					{
						if($Aunts_Uncles_living){
						$RAunts_Uncles_living.=$Aunts_Uncles_living.',';
						}
					}
					
					foreach($options['Aunts_Uncles_Deceased'] as $Aunts_Uncles_Deceased)
					{
						if($Aunts_Uncles_Deceased){
						$RAunts_Uncles_Deceased.=$Aunts_Uncles_Deceased.',';
						}
					}
					foreach($options['Aunts_Uncles_Age'] as $Aunts_Uncles_Age)
					{
						if($Aunts_Uncles_Age){
						$RAunts_Uncles_Age.=$Aunts_Uncles_Age.',';
						}
					}
					foreach($options['Aunts_Uncles_Major_Illnesses'] as $Aunts_Uncles_Major_Illnesses)
					{
						if($Aunts_Uncles_Major_Illnesses){
						$RAunts_Uncles_Major_Illnesses.=$Aunts_Uncles_Major_Illnesses.',';
						}
					}
					foreach($options['Children_living'] as $Children_living)
					{
						if($Children_living){
						$RChildren_living.=$Children_living.',';
						}
					}
					
					foreach($options['Children_living_Deceased'] as $Children_living_Deceased)
					{
						if($Children_living_Deceased){
						$RChildren_living_Deceased.=$Children_living_Deceased.',';
						}
					}
					foreach($options['Children_living_Age'] as $Children_living_Age)
					{
						if($Children_living_Age){
						$RChildren_living_Age.=$Children_living_Age.',';
						}
					}
					foreach($options['Children_living_Major_Illnesses'] as $Children_living_Major_Illnesses)
					{
						if($Children_living_Major_Illnesses){
						$RChildren_living_Major_Illnesses.=$Children_living_Major_Illnesses.',';
						}
					}
					
					$ids=$family_medical_history->id;
						$idArray = explode(',', $ids);
						$model = new Application_Model_FamilyMedicalHistory();
							foreach ($idArray as $id) {
							$object = $model->fetchRow("id={$id}");
					if($object){					
					
					$object->setPatientId($usersNs->userId);					
					
					
					
					$object->setMotherLiving($options['Mother_living']);
					$object->setMotherDiceased($options['Mother_Deceased']);
					$object->setMotherAge($options['Mother_Age']);
					$object->setMotherMajorIllness($options['Mother_Major_Illnesses']);

					$object->setFatherLiving($options['Father_living']);
					$object->setFatherDiceased($options['Father_Deceased']);
					$object->setFatherAge($options['Father_Age']);
					$object->setFatherMajorIllness($options['Father_Major_Illnesses']);

					$object->setMaternalGrandmotherLiving($options['Maternal_Grandmother_living']);
					$object->setMaternalGrandmotherDiceased($options['Maternal_Grandmother_Deceased']);
					$object->setMaternalGrandmotherAge($options['Maternal_Grandmother_Age']);
					$object->setMaternalGrandmotherMajorIllness($options['Maternal_Grandmother_Major_Illnesses']);

					$object->setMaternalGrandfatherLiving($options['Maternal_Grandfather_living']);
					$object->setMaternalGrandfatherDiceased($options['Maternal_Grandfather_Deceased']);
					$object->setMaternalGrandfatherAge($options['Maternal_Grandfather_Age']);
					$object->setMaternalGrandfatherMajorIllness($options['Maternal_Grandfather_Major_Illnesses']);


					$object->setPaternalGrandmotherLiving($options['Paternal_Grandmother_living']);
					$object->setPaternalGrandmotherDiceased($options['Paternal_Grandmother_Deceased']);
					$object->setPaternalGrandmotherAge($options['Paternal_Grandmother_Age']);
					$object->setPaternalGrandmotherMajorIllness($options['Paternal_Grandmother_Major_Illnesses']);	

					$object->setPaternalGrandfatherLiving($options['Paternal_Grandfather_living']);
					$object->setPaternalGrandfatherDiceased($options['Paternal_Grandfather_Deceased']);
					$object->setPaternalGrandfatherAge($options['Paternal_Grandfather_Age']);
					$object->setPaternalGrandfatherMajorIllness($options['Paternal_Grandfather_Major_Illnesses']);
					
					
					
					
					
					
					$object->setSistersBrothersLivings($RSisters_Brothers_living);
					$object->setSistersBrothersDiceased($RSisters_Brothers_Deceased);
					$object->setSistersBrothersAge($Sisters_Brothers_Age);
					$object->setSistersBrothersMajorIllness($RSisters_Brothers_Major_Illnesses);
					$object->setAuntsUnclesLiving($RAunts_Uncles_living);
					$object->setAuntsUnclesDiceased($RAunts_Uncles_Deceased);
					$object->setAuntsUnclesAge($RAunts_Uncles_Age);
					$object->setAuntsUnclesMajorIllness($RAunts_Uncles_Major_Illnesses);
					$object->setChildrenLiving($RChildren_living);
					$object->setChildrenDiceased($RChildren_living_Deceased);
					$object->setChildrenAge($RChildren_living_Age);
					$object->setChildrenMajorIllness($RChildren_living_Major_Illnesses);	
					$object->setPneumoniaVaccine($options['Pneumonia_Vaccine']);
					$object->setPneumoniaVaccineYear($options['Pneumonia_Vaccine_year']);
					$object->setInflueza($options['Influeza']);
					$object->setInfluezaYear($options['Influeza_year']);
					$object->setTuberculin($options['Tuberculin']);
					$object->setTuberculinYear($options['Tuberculin_skin_test_year']);
					$object->setBcg($options['BCG']);
					$object->setBcgYear($options['BCG_year']);
					$object->setDiptheria($options['Diptheria']);
					$object->setDiptheriaYear($options['Diptheria_year']);
					$object->setMeasles($options['Measles']);
					$object->setMeaslesYear($options['Rubella_year']);
					$object->setHepatitis($options['Hepatitis_A']);
					$object->setHepatitisYear($options['Hepatitis_A_year']);
					$object->setHepatitisB($options['Hepatitis_B']);
					$object->setHepatitisBYear($options['Hepatitis_B_year']);
					$object->setTestedChickenPox($options['Chicken_Pox']);
					$object->setTestedTuberculosis($options['Tuberculosis']);
					$object->setTestedHiv($options['HIV']);
					$object->setTestedHepatitis($options['Hepatitis']);
					$object->setTestedVenereal($options['Venereal']);
					$object->setType($options['type']);
					$object->setSpecify($options['Specify']);
					$object->setChestXRayDate($options['Chest_Date']);
					$object->setChestXRayResult($options['Chest_Result']);
					$object->setCholesterolLevelDate($options['Cholesterol_date']);
					$object->setCholesterolLevelResult($options['Cholesterol_result']);
					$object->setTriglycerideLevelDate($options['Triglyceride_date']);
					$object->setTriglycerideLevelResult($options['Triglyceride_result']);
					$object->setOtherLipidDataDate($options['Lipid_date']);
					$object->setOtherLipidDataResult($options['Lipid_result']);
					$object->setColonoscopyDate($options['Colonoscopy_date']);
					$object->setColonoscopyResult($options['Colonoscopy_result']);
					$object->setMammogramDate($options['Mammogram_date']);
					$object->setMammogramResult($options['Mammogram_result']);
					$object->setPapTestDate($options['Pap_date']);
					$object->setPapTestResult($options['Pap_result']);
					$object->setBoneDensityTestDate($options['Density_date']);
					$object->setBoneDensityTestResult($options['Density_result']);
					$object->setCreateTime(time());
					$object->setUpdateTime(time());
				
					$object->save();
							}
						}
						
					
					
					
				}
			}
			
			
		}
		
		public function exportpdfAction()
		{ 	$this->_helper->layout->disableLayout();
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
 
			//whole TCPDF's settings goes here

			$htmlcontent = $this->view->render('index/pdf.phtml');
			
			// output the HTML content
			$pdf->writeHTML($htmlcontent, true, 0, true, 0);
			
		   $pdf->addPage();
			//echo '<pre>';print_r($pdf);die;
			$pdf->Output("pdf-name.pdf", 'D');
		die;
		}
		
		/*Share Patient History Information*/
		
		public function sharepatientinfoAction()
		{
			
			$usersNs = new Zend_Session_Namespace("members");	
			$request = $this->getRequest();
			$options = $request->getPost();
			if ($request->isPost()) {
				$User = new Application_Model_User();
				$doctorUser = $User->fetchRow("user_level_id='2' and email='".$options['email']."'");
				if($doctorUser){
			
					$Doctor = new Application_Model_Doctor();
					$doctor = $Doctor->fetchRow("user_id=".$doctorUser->getId());
					
					$Patient = new Application_Model_Patient();
					$patient = $Patient->fetchRow("user_id=".$usersNs->userId);
					$doctorPatient = new Application_Model_DoctorPatient();
					$patientIdD = $doctorPatient->fetchRow("doctor_id=".$doctor->getId()." and patient_id=".$patient->getId());
					
					if($patientIdD){
						$options ['doctor_name']=$options['doctor_name'];
						$options ['user_id']=$usersNs->userId;
					    $options ['patient_name']=$patient->name;
					    $options ['doctor_id'] = $doctor->getId();
						//print_r($options);die;
						$Mail = new Base_Mail('UTF-8');
						$Mail->sendPatientinfoToDoctorMail($options);
						$msg="Your medical history pdf file has been sent to the doctor";
					}elseif(!$patientIdD) {
						$msg="Patient is not associated with this doctor";
					}
				} else {
					$msg="Your email id does not exist";
				}
				$this->_helper->redirector('patient-information-full', 'index', "user", array('e'=>base64_encode($msg)));
			}
			die;
		}
		
		public function sharepdfAction()
		{ 	$this->_helper->layout->disableLayout();
			$usersNs = new Zend_Session_Namespace("members");		
			require_once('tcpdf/tcpdf.php');
			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
			
			$this->view->userid=base64_decode($this->_getParam('userid'));
			
			$Doctor = new Application_Model_Doctor();
			$doctorid = $Doctor->fetchRow("user_id=".$usersNs->userId);
			//whole TCPDF's settings goes here
			$Patient = new Application_Model_Patient();
			$patient = $Patient->fetchRow("user_id=".base64_decode($this->_getParam('userid')));
			
			$doctorPatient = new Application_Model_DoctorPatient();
			if($patient)
			{
			$patientIdD = $doctorPatient->fetchRow("doctor_id=".$doctorid->getId()." and patient_id=".$patient->getId());
			}
	
			if($patientIdD){
			$htmlcontent = $this->view->render('index/shareinfo.phtml');
			
			
			// output the HTML content
			$pdf->writeHTML($htmlcontent, true, 0, true, 0);
			
			$pdf->addPage();
			//echo '<pre>';print_r($pdf);die;
			$pdf->Output("share.pdf", 'D');
			}else{
			echo '<script>alert("This patient is not associated with you")</script>';
			}
		die;
		}
		

		public function massUploadPatientsAction() {
			$form = new User_Form_PatientsUpload();
			$form->setAttrib("enctype", "multipart/form-data");
			$elements = $form->getElements();
			$form->clearDecorators();
			
        	$this->view->form = $form;
			$this->view->message = "Please upload a coma separated CSV file with these columns: <br>
			First Name, Last Name, Email, Mobile Phone, Street, City, Zipcode, State<br>The name is a mandatory field. User are checked if already in the system based on their email only.";
			$request = $this->getRequest();
			if ($request->isPost()) {
				$this->view->message ="";
				
				$upload = new Zend_File_Transfer_Adapter_Http();
				$path = "csvs/";
				$upload->setDestination($path);
				try {
				    $upload->receive();
				} catch (Zend_File_Transfer_Exception $e) {
				    $e->getMessage();
				}
				$upload->setOptions(array('useByteString' => false));
				$file_name = $upload->getFileName('patients');
				if (!empty($file_name)) {

				   	$handle = fopen($file_name,"r");
			        $patient = array();
			        if(!$handle) {
			            $this->view->message = "Please upload a valid csv file.";
			        } else {
			            $this->view->errorFileMessage = false;
			            $count=0;
			            //loop through the csv file and insert into database
			            $i = 1;
						while ($data = fgetcsv($handle,0,",")) {
							if($data[0]) {
				                $patient['fname'] = $data[0];
				                $patient['lname'] = $data[1];
				                $patient['email'] = $data[2];
				                $patient['phone'] = $data[3];
				                $patient['street'] = $data[4];
				                $patient['city'] = $data[5];
				                $patient['zipcode'] = $data[6];
				                $patient['state'] = $data[7];

				                $outcome = $this->createImportedPatient($patient);
				                if($outcome) {
				                	$this->view->message .= "Patient ".$patient['fname']." ".$patient['lname']." imported.<br/>";
				                } else {
				                	$this->view->message .= "Patient ".$patient['fname']." ".$patient['lname']." was not imported.<br/>";
				                }
				            } else {
				            	$this->view->message .="Name missing for row ".$i.". The patient was not imported.<br/>";
				            }
				            $i++;
			            }
			        }
				}
			}
		}

		private function createImportedPatient($patient) {

			$error = 0;
			$errArray = array();
			$Patient = new Application_Model_Patient();
			$userId = 0;

			$usersNs = new Zend_Session_Namespace("members");
			$Doctor =  new Application_Model_Doctor();
			$doctor = $Doctor->fetchRow("user_id=".$usersNs->userId);
			$patient["doctorid"] = $doctor->getId();

			//no email, create a new one
			if($patient['email'] == "")  {
				//doctor did not give an email. An automated one should be generated.
				if($patient['email'] == "") { //create dummy unique email for empty emails
					$patient['email'] = "patient".time().rand(1,1000)."@doctors.com";
					while(!$this->canCreateUser($email)){
						$patient['email'] = "patient".time().rand(1,1000)."@doctors.com";       
					}
				}
			}

			if($patient['email'] != "") { //only insert patients with email
				if( $this->canCreateUser($patient['email']) ) {
				//create user
					$User = new Application_Model_User();
					$User->setEmail($patient['email']);
					$User->setUsername($patient['email']);
					$User->setFirstName($patient['fname']);
					$User->setLastName($patient['lname']);

					$User->setUserLevelId(3); // for patient
					$User->setLastVisitDate(time());
					$User->setStatus('active'); 
					$patient["password"] = time();
					$User->setPassword(md5($patient["password"]));
					$userId = $User->save();

	        		//create patient
	                $name = $patient['fname']." ".$patient['lname'];
	                $Patient->setName($name);
	                $Patient->setUserId($userId);
	                $Patient->setZipcode($patient["zipcode"]);
					$Patient->setAge("");
					$Patient->setGender("");
					$Patient->setPhone($patient["phone"]);
					$Patient->setMobile($patient["phone"]);
					$Patient->setInsuranceCompanyId("");
					$Patient->setMonthDob("");
					$Patient->setDateDob("");
					$Patient->setYearDob("");
					$Patient->setLastUpdated(time());
					$Patient->setFirstLogin(0);
					$Patient->setStreet($patient["street"]);
					$Patient->setCity($patient["city"]);
					$Patient->setZipcode($patient["zipcode"]);
					$Patient->setState($patient["state"]);
					$Patient->setProfileImage("");
					$patientId = $Patient->save();

					//save the patient to doctor's patients
					$DoctorPatient = new Application_Model_DoctorPatient();
					$DoctorPatient->setDoctorId($doctor->getId());
					$DoctorPatient->setPatientId($patid);
					$DoctorPatient->save();

					//save doctor to patient's favorites
					$favorite = new Application_Model_PatientFavoriteDoctor();
					$favorite->setPatientId($patientId);
					$favorite->setDoctorId($doctor->getId());
					$favorite->setFavoriteStatus("Favorite");
					$favorite->setCreateTime(time());
					$favorite->setUpdateTime(time());
					$favorite->save();

					//send message to new patient
					$Mail = new Base_Mail('UTF-8');
					$Mail->sendPatientMassRegistrationMail($patient);
					return true;
				} else {					
				//email already used
					$User = new Application_Model_User();
					$user = $User->fetchRow('email="'.$patient["email"].'"');
					if($user->getUserLevelId() == 3) {
						$patientModel = $Patient->fetchRow("user_id=".$user->getId());

						//save the patient to doctor's patients
						$DoctorPatient = new Application_Model_DoctorPatient();
						$DoctorPatient->setDoctorId($doctor->getId());
						$DoctorPatient->setPatientId($patientModel->getId());
						$DoctorPatient->save();

						return true;

					} else {
						//not a patient
					}
				}
			}
			return false;
		}

                 //For Patient Referrals

           public function referralsPatientAction() {

        $usersNs = new Zend_Session_Namespace("members");
		$Doctor =  new Application_Model_Doctor();
		$doctor = $Doctor->fetchRow("user_id=".$usersNs->userId);
		$docid = $doctor->getId();
        $db = Zend_Registry::get('db');
        $query= "select * from patients inner join referrals_patient ON referrals_patient.patients_id=patients.id where referrals_patient.doctor_id=".$docid;
        $patientreferralList  = $db->query($query);
   

        $patientReferralListForPagination = $patientreferralList->fetchAll();
      
       $this->view->referralspatientList =  $patientReferralListForPagination ;
        
        $settings = new Admin_Model_GlobalSettings();
        // echo $page_size = $settings->settingValue('pagination_size');
        $page_size =10;
       
		$page = $this->_getParam('page', 1);
		$pageObj = new Base_Paginator();
		$paginator = $pageObj->arrayPaginator($patientReferralListForPagination, $page, $page_size);
		$this->view->total = $pageObj->getTotalCount();
		$this->view->paginator = $paginator;
		   //echo  $pageObj->getTotalCount() ; die ;
			if($page*$page_size < $pageObj->getTotalCount()){
				$nextPage = intval($page)+1;
				$nextUrl = 'page='.$nextPage;
				
			
				$this->view->nextUrl = $nextUrl;
			}
			if($page!= 1){
				$prevPage = intval($page)-1;
				$prevUrl = 'page='.$prevPage;
           
				$this->view->prevUrl = $prevUrl;
			}

		

       
          $monthrecord= 'SELECT count(*) as month_record FROM referrals_patient WHERE MONTH(enterdate) = MONTH(CURDATE()) and doctor_id='.$docid;
      
        $monthrecordno  = $db->query($monthrecord) ; 
        $this->view->monthrecordno =  $monthrecordno ;
        $previous_monthrecord= 'SELECT count(*) as month_record FROM referrals_patient WHERE MONTH(enterdate) = MONTH(CURDATE()-INTERVAL 1 MONTH) and doctor_id='.$docid;
    
        $previous_monthrecordno  = $db->query($previous_monthrecord) ; 
        $this->view->previous_monthrecordno =  $previous_monthrecordno ;
       $yearrecord= 'SELECT count(*) as year_record FROM referrals_patient WHERE YEAR(enterdate) =YEAR(CURDATE()) and doctor_id='.$docid;

       $yearrecordno  = $db->query($yearrecord)  ; 
       $this->view->yearrecordno =  $yearrecordno ;
       $mostrecentrecord= "select * from referrals_patient where doctor_id =".$docid." order by id desc limit 2";
       $form = new Application_Form_Patientregistration();
        $mostrecentrecorddetail  = $db->query($mostrecentrecord) ; 
        $this->view->mostrecentrecorddetail =  $mostrecentrecorddetail ;

         $request = $this->getRequest();
				if ($request->isPost()) {
				$referralpatientdata=$request->getPost();
				$options['referralname'] = $referralpatientdata['referralname'];
				$options['referralemail'] = $referralpatientdata['referralemail'];
			    $options['referraltext'] = $referralpatientdata['referraltext'];
			  
			    $query= "select * from patients inner join referrals_patient ON referrals_patient.patients_id=patients.id where referrals_patient.doctor_id=".$docid." and referrals_patient.email='".$options['referralemail']."'";
                $particularReferringPatientDetail  = $db->query($query);

//                $result = $particularReferringPatientDetail->fetchAll();
               
                foreach($particularReferringPatientDetail as $referingDetail)
                {
                   $referralPatientName=$referingDetail->name ;  

                }
               $options['referralpatientnametext'] = $referralPatientName ;
            //   print_r($options);



                    
			
        // die ;


				$usersNs = new Zend_Session_Namespace("members");
			    $Doctor =  new Application_Model_Doctor();
			    $doctor = $Doctor->fetchRow("user_id=".$usersNs->userId);
			    $options['doctor_id'] = $doctor->getId();
			    $Mail = new Base_Mail('UTF-8');
				$Mail->NewPatientReferrralsEmail($options);
				$this->view->msg = "<div style='color:#EF422D;'>Mail Sent Successfully .</div>";
                }

				
	}
   
             
	private function canCreateUser($email){
		$User = new Application_Model_User();
		$user = $User->fetchRow('email="'.$email.'"');
		//error_log(print_r($user, true));
		if($user == null){ //No User exists in database
			return true;
		} else {
			return false;
		}
	}
	
	
	function sendDueReminderAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$usersNs = new Zend_Session_Namespace("members");
		$userid = $usersNs->userId;
		$options = $this->_request->getPost();
		$Doctor = new Application_Model_Doctor();
		$User = new Application_Model_User();
		$PaymentReminder = new Application_Model_PaymentReminder();
		$Patient = new Application_Model_Patient();
		$CommunicationHistory = new Application_Model_CommunicationHistory();
		$Mail = new Base_Mail('UTF-8');
		
		
		if(!empty($options['patient_id']) && !empty($options['due_amount'])){
			$patient = $Patient->find(trim($options['patient_id']));
			$doctor  = $Doctor->fetchRow("user_id=".trim($userid));
			$isAllowdSms = $patient->getCommunicationViaText(); 
			
			if(!empty($patient) && !empty($doctor)){
				$patient_name = $patient->getName();
				$patient_id = trim($patient->getId());
				$patuserid = trim($patient->getUserId());
				if($patient_name ==''){
					$patient_name = 'Patient';
				}
				$patient_mobile = $patient->getMobile();
				$doctorprefix = substr($doctor->getFullName(),0,2);
				if(strtolower($doctorprefix)=='dr'){
					$doctor_name =  $doctor->getFullName(); 
				}else{
					$doctor_name = 'Dr. '.$doctor->getFullName(); 
				}
				$today = date('Y-m-d H:i:s');
				if(!empty($patient_mobile) && trim($isAllowdSms) !=1){
					$PaymentReminder->setUserId(trim($userid));
					$PaymentReminder->setPatientId($patient_id);
					$PaymentReminder->setAmount(trim($options['due_amount']));
					$PaymentReminder->setCreatedAt($today);
					$last_id = $PaymentReminder->save();
					if($last_id){
						$message = "Hello ".$patient_name.",\r\nthis is a friendly reminder of your outstanding balance in the amount of $".trim($options['due_amount']).". Please remit payment to the office as soon as possible.\r\nThank you! ".$doctor_name."";
						$CommunicationHistory->setType("Payment Due Reminder");
						$CommunicationHistory->setSentAt(date("Y-m-d H:i:s"));
						$CommunicationHistory->setSenderUserId($userid);
						$CommunicationHistory->setReceiverUserId($patuserid);
						$CommunicationHistory->setMessage($message);
						$CommunicationHistory->setTimezone('PST');
						$CommunicationHistory->setCreatedAt(date("Y-m-d H:i:s"));
						$CommunicationHistory->save();
						$Mail->sendSMS($patient_mobile, $message);
					}
					$key = 1;
				}else{
					$key = 2;
				}
			}
		}else{
			$key = 2;
		}

		$result = array('key'=>$key);
		echo json_encode($result);
	}
	
	
	function templateMessageAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$usersNs = new Zend_Session_Namespace("members");
		$CommunicationHistory = new Application_Model_CommunicationHistory();
		$userid = $usersNs->userId;
		$id =  $this->_getParam('id');
		$result = array();
		
		$history = $CommunicationHistory->find(trim($id));
		
		$new_html ='';
		if(!empty($history)){
			$content = $history->getMessage();
			libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$dom->loadHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.$content);
			$dom->formatOutput = true;
			foreach($dom->getElementsByTagName('a') as $item){
				//Remove width attr if its there
				$item->removeAttribute('href');
				//Get the sytle attr if its there
				$style = $item->getAttribute('style');
				//Set style appending existing style if necessary, 123px could be your $width var
				$item->setAttribute('style','width:100px;'.$style);
			}
			
			//remove unwanted doctype ect
			$ret = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|head))[^>]*>\s*~i', '', $dom->saveHTML());
			$new_html =  trim(str_replace('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">','',$ret));
			$key = 1;
		}else{
			$key =2;
		}
		
		$result = array('html'=>$new_html,'key'=>$key);
		echo json_encode($result);
	}
	
	
	function confirmAppointmentManuallyAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id =  $this->_getParam('id');
		$Appointment = new Application_Model_Appointment();
		$key = 0;
		if($id !=''){
			if(is_numeric($id)) {
				$Appointment->confirmAppointmentManually($id);
				$key = 1;
			}
		}
		$result = array("key"=>$key);
		echo json_encode($result);
	}
	
	function unconfirmAppointmentManuallyAction(){
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$id =  $this->_getParam('id');
		$Appointment = new Application_Model_Appointment();
		$key = 0;
		if($id !=''){
			if(is_numeric($id)) {
				$Appointment->unconfirmAppointmentManually($id);
				$key = 1;
			}
		}
		$result = array("key"=>$key);
		echo json_encode($result);
	}



		
	
}// end class
