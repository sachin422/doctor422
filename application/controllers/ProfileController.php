<?php

class ProfileController extends Base_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        /* $uri=$this->_request->getPathInfo();
          $activeNav=$this->view->navigation()->findByUri($uri);
          $activeNav->active=true; */
    }

     public function preDispatch() {
        parent::preDispatch();
        $this->_helper->layout->setLayout('doctor');
    }

    public function indexAction() {
		$id = $this->_getParam('id');
		//echo $id;die;
		$isAjax = $this->_getParam('ajax');
		if($isAjax) {
			$this->_helper->layout->disableLayout();
		}
		$this->view->isAjax = $isAjax;
		$allAwards = array();
		$request = $this->getRequest();
				          
		//Fetch Profile Data
		$Doctor = new Application_Model_Doctor();
		$profileobject = $Doctor->fetchRow("status=1 AND id=$id");
		//print_r($profileobject);die;
		$profileobject->getUrl();
		if(empty($profileobject)){
			$this->_redirect('/404');
		}
		
		//print_r($profileobject->userId);die;
		/*$patientFavDoctorModel = new Application_Model_PatientFavoriteDoctor();
		$doctorId = $profileobject->getId();
		$object1 = $patientFavDoctorModel->fetchRow("doctor_id=".$doctorId);
			
		$this->view->favoritedata = $object1->favoriteStatus;
		$this->view->favoriteid = $object1->id;
		$this->view->doctorid = $object1->doctorId;
		$this->view->patientid = $object1->patientId;


*/
		$usersNs = new Zend_Session_Namespace("members");
		$this->view->isDocLoggedin = false;
		$this->view->isPatLoggedin = false;
		$this->view->favStatus = "unfav";
		if($usersNs) {
			$User = new Application_Model_User();
			$loggedinUser = $User->find($usersNs->userId);
			if($loggedinUser && $loggedinUser->getUserLevelId() == 2) { //logged in user, doctor
				$this->view->isDocLoggedin = true;
			} else { //patient logged in
				$Patient = new Application_Model_Patient();
				$patient = $Patient->find($usersNs->patientId);
				if($patient) {
					$patientFavDoctorModel = new Application_Model_PatientFavoriteDoctor();
					$fav = $patientFavDoctorModel->fetchRow("doctor_id=".$profileobject->getId()." AND patient_id=".$patient->getId());
					if($fav) {
						$this->view->favStatus = $fav->getFavoriteStatus();
					}
					$this->view->isPatLoggedin = true;
				} 
			}
		}


		$this->view->profiledata = $profileobject;
		$profileImage = "/images/doctor_image/" . $profileobject->getCompanylogo();
		if (!file_exists(getcwd() . $profileImage) || $profileobject->getCompanylogo()=='')$profileImage = "/images/doctor_image/png.png";
		$this->view->profileImage = $profileImage;
		$this->view->logo = $profileobject->getCompanylogo();

		//Fetch Category Data
		$DocCategory = new Application_Model_DoctorCategory();
		$categoryArr = $DocCategory->getDoctorCategories("doctor_id='{$id}'");
               
		//Fetch Category Data
		$DocExtraCategory = new Application_Model_DoctorExtraCategory();
		$categoryExtraArr = $DocExtraCategory->getDoctorCategories("doctor_id='{$id}'");
		asort($categoryArr);
		$this->view->categorydata = $categoryArr;
		//extracategory
		asort($categoryExtraArr);
		$this->view->categoryExtradata = $categoryExtraArr;

		//Fetch Insurance Accepted
		$modeldoctor_insurance = new Application_Model_DoctorInsurance();
		$ArrDoctorInsurance=$modeldoctor_insurance->getDoctorinsurance("doctor_id={$id}");
		$InsuranceCompany = new Application_Model_InsuranceCompany();
		$model_hospital_affiliation =new Application_Model_DoctorHospitalAffiliation();
		$arrdoctorHA = $model_hospital_affiliation->getDoctorHospitalAffiliate("doctor_id={$id}");
				
		$this->view->hospitalAffiliation =$arrdoctorHA;
		
		$insurancedata = array();
		$petinsurancedata = array();
		foreach($ArrDoctorInsurance as $key=>$value) {
			$insuranceobject = $InsuranceCompany->find($value);
			if($insuranceobject) {
				if($insuranceobject->typec=="Regular") {
					$insurancedata[$insuranceobject->getId()] = $insuranceobject->getCompany();
				} else if($insuranceobject->typec=="Pet") {
					$petinsurancedata[$insuranceobject->getId()] = $insuranceobject->getCompany();
				}
			}
		}
		asort($insurancedata);
		$this->view->insurancedataArr = $insurancedata;
		$this->view->petinsurancedata = $insurancedata;
		$planSelected = false;

		$association = array();
		$DocAssociation = new Application_Model_DoctorAssociation();
		$assObject = $DocAssociation->fetchAll("doctor_id='{$id}'");
		if(!empty($assObject)){
			$array = array();
			foreach($assObject as $ass){
				$array[] = $ass->getAssociationId();
			}
			$str = implode(",",$array);

			$Association = new Application_Model_Association();
			$association = $Association->fetchAll("id IN ($str)");
		}
		$staticAwards = array();
		$allAwards = array();
		$award_id = 0;
	  
		$DocAward = new Application_Model_DoctorAward();
		$awardObject = $DocAward->fetchAll("doctor_id='{$id}'");

		if(!empty($awardObject)) {
			$arawardid = array();
			$staticawardid = array();
			$str_award=0;
			$str_statis_award=0;
			foreach($awardObject as $award) {
				$static_awards=array(244,245,246,247,248,249);
				$award_id =$award->getAwardId();
			   
				if(in_array($award_id,$static_awards))
					$staticawardid[] = $award->getAwardId();
				
			}
                       

			if(count($staticawardid)>0)
				$str_statis_award =implode(", ",$staticawardid);

			$Award = new Application_Model_Award();
			if(empty($str_statis_award))
				$str_statis_award = 0;
			$staticAwards = $Award->fetchAll("id in ({$str_statis_award})");
		}
		$this->view->associations = $association;
	  
		$this->view->textAward = $profileobject->getTextAward();
		$this->view->staticAwards = $staticAwards;
		
		/* review */
		/*$modeldoctorreview = new Application_Model_DoctorReview();
		$this->view->viewreviewobject = $modeldoctorreview; 
		$request = $this->getRequest();		 
		$reviewobject = $modeldoctorreview->fetchAll("status=1 and doctor_id={$id}", "admin_approved DESC",'1','0');
		$this->view->reviewobjectdata = $reviewobject;*/
		
		
		$modeldoctorreviewV = new Application_Model_DoctorReview();
		$this->view->viewreviewobject = $modeldoctorreviewV;
		/*$request = $this->getRequest();		 */
		$query = "status=1 and doctor_id={$id}";
		//error_log($query);
		$reviewobjectV = $modeldoctorreviewV->fetchAll($query, array("admin_approved DESC", "added_on DESC"));
		$this->view->reviewobjectdataV = $reviewobjectV;
		/* /review */
		
		$categoryobject = array();
		$DoctorCategories = new Application_Model_DoctorCategory();
		$DoctorCatObject = $DoctorCategories->fetchAll("doctor_id={$profileobject->getId()}");
		if(!empty($DoctorCatObject)){
			$Category = new Application_Model_Category();
			foreach($DoctorCatObject as $DoctorCatObj){
				$categoryobject = $Category->find($DoctorCatObj->getCategoryId());
				if($categoryobject)break;
			}
		}
    }
	
	/* review */
	public function addReviewAction(){
		$request = $this->getRequest();
		$return['flag'] = 0;

		if ($request->isPost()) {
			$options = $request->getPost();
			//echo $options['waittime'];die;
			if($options['appointment_id']) {
				$Appointment = new Application_Model_Appointment();
				$app = $Appointment->find($options['appointment_id']);
				if($app) {
					$userId = $app->getUserId();
				} else {
					$userId = "";
				}
			} else {
				$userId = $options['user_id'];
			}

			$Review = new Application_Model_DoctorReview();
		        $Mail = new Base_Mail('UTF-8');

			$Review->setUserId($userId);
			$Review->setVote($options['vote']);
			$Review->setRecommendation($options['recommendation']);
			$Review->setBedside($options['bedside']);
			$Review->setWaittime($_REQUEST['waittime']);
			$Review->setDoctorId($options['drid']);
			$Review->setTitle($options['revTitle']);
			$Review->setReview($options['sobireview']);
			$Review->setUsername($options['uname']);
			$Review->setEmail($options['umail']);
			$Review->setAppointmentId($options['appointment_id']);
			$Review->setIp($_SERVER['REMOTE_ADDR']);
			$Review->setAddedOn(time());

			//$Review->save();
			$ReviewId = $Review->save();
			if($ReviewId > 0 && $userId > 0){
				
				if($options['appointment_id']){

						$currentReview = $Review->fetchAll("appointment_id={$options['appointment_id']} AND doctor_id={$options['drid']} AND mail_sent =1");

					}else{

						

						$currentReview = $Review->fetchAll("id={$ReviewId} AND doctor_id={$options['drid']} AND mail_sent =1");

					}

				$isSent = count($currentReview);
							
				if($isSent < 1){
					$content = $options['sobireview'];
					$result = $Mail->sendReviewMailToDoctor($options['appointment_id'],$options['drid'],$userId,$content);
					//echo $result;
					$total_review = trim($options['vote']);
					if(!empty($userId) && $total_review >= 4){
						//$Mail->sendMessageYelp($userId,$options['drid']);
					}
					if($result==1){
						$Review->setId($ReviewId);
	
						$Review->setMailSent(1);

						$Review->save();
					}
				}else{
					  $Review->setId($ReviewId);
	        	                        $Review->setMailSent(1);
	
                                                $Review->save();

				}

			}

			
			$array = $Review->getRatingReviews($options['drid']);
			$return['msg'] = '<b>Thanks for your review ...</b>';
			$return['image'] = $array['image'];
			$return['votes'] = $array['votes'];
			$return['rating'] = '1';
			$return['flag'] = 1;
		} else {
			$return['msg'] = '<b>There is some problem. Please try later...</b>';
		}

        //add a notification about this request to the doctor
        $notification = new Application_Model_Notification();
        $notification->setTitle("Patient Review");
        $notification->setContent("You have a new review from a patient.");
        $notification->setActive(1);
        $notification->setPublished(time());
        $notification->setLink("/user/index/doctor-review");
        $Doctor =  new Application_Model_Doctor();
        $doctor = $Doctor->find($options['drid']);
        $User = new Application_Model_User();
        $user = $User->find($doctor->getUserId());
		$notification->setUserid($user->getId());
        $notification->save();

        echo json_encode($return);exit();
    }
	/* /review */
	
    public function ratingImageAction() {
		$Review = new Application_Model_DoctorReview();
		$image = $Review->ratingImage($this->_getParam('vote'));
		$return['image'] = $image;
		$return['vote'] = $this->_getParam('vote');
       
        echo json_encode($return);exit();
    }

    public function setReasonforvisitAction(){
		$reasonNamespace = new Zend_Session_Namespace('reason');
		$reasonNamespace->reasonforvisit = $this->_getParam('reason');
		die('1');
    }

    public function timeslotpanelAction(){
    	$post = array();
        $post['drid']  = $this->_getParam('drid');
        $post['start_date'] = $this->_getParam('start_date');
        $post['disp'] = $this->_getParam('disp');// dispaly 'more...' link
        $post['type'] = 1; // type '0' for doctor listing page.

        $Search = new Base_Timeslot();
        $Search->getAppointmentAvailabilityPanel($post);
    }

	public function timeslotAction(){
        $post = array();
        $post['drid']       = $this->_getParam('drid');
        $post['start_date'] = $this->_getParam('start_date');
        $post['disp'] = $this->_getParam('disp');// dispaly 'more...' link
        $post['type'] = 1; // type '0' for doctor listing page.

        $Search = new Base_Timeslot();
        $Search->getAppointmentAvailabilityDoctor($post);
    }

	public function showIcalAction(){
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
        $Doctor =  new Application_Model_Doctor();
        $doctor = $Doctor->fetchRow("user_id=".$id);
        $docId = $doctor->getId();
        //Fetch Profile Data
        $Appointment = new Application_Model_Appointment();
        $object = $Appointment->fetchAll("doctor_id={$docId} AND deleted!=1");
        $ical = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN"."\r\n";
        if (!empty($object)) {
            foreach ($object as $obj) {
                
                $date = $obj->getAppointmentDate();
                $start_time = strtotime($date.' '.$obj->getAppointmentTime());
                $interval = $this->getInterval($docId, $date);
                $end_time = strtotime("+$interval minutes", $start_time);
                
                $dtstart = gmdate('Ymd', $start_time).'T'. gmdate('His', $start_time) . "Z"; // converts to UTC time
                $dtend = gmdate('Ymd', $end_time).'T'. gmdate('His', $end_time) . "Z"; // converts to UTC time
                $gender = $obj->getGender();
                if($gender == 'm') {$pret='Mr';} else {$pret='Mrs';}
                $summary = 'Appointment with '.$pret. ' '.$obj->getLname().' '.$obj->getFname().', Age: '.$obj->getAge().', email:'.$obj->getEmail().', Reason for visit: '.$obj->getNeeds();
                $ical .= "BEGIN:VEVENT
UID:" . md5($obj->getId()) . "@doctors.com
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:" . $dtstart . "
DTEND:" . $dtend . "
SUMMARY:" . $summary . "
END:VEVENT"."\r\n";
            }
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
	
    public function showTimeslotAction(){
        $this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
        //Fetch Profile Data
        $Doctor = new Application_Model_Doctor();
        $profileobject = $Doctor->find($id);
        $this->view->profiledata = $profileobject;

        $profileImage = "/images/doctor_image/" . $profileobject->getCompanylogo();
        if (!file_exists(getcwd() . $profileImage) || $profileobject->getCompanylogo()=='')$profileImage = "/images/doctor_image/png.png";
        $this->view->profileImage = $profileImage;
        
    }

    public function viewAllInsurancesAction() {
		$this->_helper->layout->disableLayout();
        $id = $this->_getParam('id');
        //Fetch Profile Data
        $componies = array();
        $db = Zend_Registry::get('db');
		$query = "SELECT c.id comp_id, c.company FROM doctor_insurance di, insurance_companies c
                    WHERE di.insurance_id= c.id AND di.doctor_id='{$id}' ORDER BY company";
        $select = $db->query($query);
        $insurances = $select->fetchAll();
        if(count($insurances)){
            foreach($insurances as $ins){
                $query = "SELECT p.id plan_id, p.plan FROM doctor_insurance_plan dp, insurance_plans p, insurance_companies c
                    WHERE dp.plan_id=p.id AND p.insurance_company_id= c.id AND dp.doctor_id='{$id}' AND c.id={$ins->comp_id} ORDER BY p.plan";
                $select = $db->query($query);
                $plans = $select->fetchAll();
                if(count($plans)){
                    foreach($plans as $p){
                        $componies[$ins->company][] = $p->plan;
                    }
                }else{
                    $componies[$ins->company] = array();
                }
            }
        }
        $this->view->componies = $componies;
    }
	
	public function testAction(){
        $drid = $this->_getParam('drid');
        die("testing for timeslot: $drid");
    }

    public function widgetProfileAction() {
    	$this->_helper->layout->setLayout('widget');
        $drid = $this->_getParam('drid');
        $Doctor = new Application_Model_Doctor();
        $doctor = $Doctor->find($drid);
        $this->view->doctorId = $drid;
        if($doctor) {
	        $profileImage = "/images/doctor_image/" . $doctor->getCompanylogo();
			if (!file_exists(getcwd() . $profileImage) || $doctor->getCompanylogo()=='')$profileImage = "/images/doctor_image/png.png";
			$this->view->profileImage = $profileImage;
		}

		$modeldoctorreviewV = new Application_Model_DoctorReview();
		$reviewobjectV = $modeldoctorreviewV->getAverageReviews($drid);
		$this->view->review = $reviewobjectV;
    }

    public function widgetReviewsFrameAction() {
    	$this->_helper->layout->setLayout('widget');
        $drid = $this->_getParam('drid');
        if($drid) {
	        $Doctor = new Application_Model_Doctor();
	        $doctor = $Doctor->find($drid);
	        $this->view->doctorId = $drid;
	        $this->view->doctor = $doctor;

			$where = "doctor_id={$drid} AND status=1";
			$model = new Application_Model_DoctorReview();
			$page_size = 15;
			$page = $this->_getParam('page', 1);
			$pageObj = new Base_Paginator();

			$sort_val = $this->_getParam('sort', 1);
			//$sort = "id DESC";
                        $sort = "added_on DESC";
			$this->view->sort = $sort_val;
			if($sort_val == "rating") {
				$sort = "vote DESC";
			}

			$paginator = $pageObj->fetchPageData($model, $page, $page_size, $where, $sort);
			$this->view->total = $pageObj->getTotalCount();
			$this->view->paginator = $paginator;


			$reviewM = new Application_Model_DoctorReview();
			$this->view->averageReviews = $reviewM->getAverageReviews($drid);
			$object = $reviewM->fetchAll("doctor_id='{$drid}' AND recommendation = 5 AND status=1");	
			$this->view->starsCount5 = count($object);
			$object = $reviewM->fetchAll("doctor_id='{$drid}' AND recommendation = 4 AND status=1");	
			$this->view->starsCount4 = count($object);
			$object = $reviewM->fetchAll("doctor_id='{$drid}' AND recommendation = 3 AND status=1");	
			$this->view->starsCount3 = count($object);
			$object = $reviewM->fetchAll("doctor_id='{$drid}' AND recommendation = 2 AND status=1");	
			$this->view->starsCount2 = count($object);
			$object = $reviewM->fetchAll("doctor_id='{$drid}' AND recommendation = 1 AND status=1");	
			$this->view->starsCount1 = count($object);
			
		} else {
			$this->view->review = null;
		}
    }
    
    public function widgetReviewAction() {
    	$this->_helper->layout->disableLayout();
    	$drid = $this->_getParam('drid');
        $this->view->doctorId = $drid;
    }
    public function widgetReviewsAction() {
    	$this->_helper->layout->disableLayout();
    	$return["callback"] = $this->_getParam('drid');
        
        $this->view->json = json_encode($return);
    }
}// end class
