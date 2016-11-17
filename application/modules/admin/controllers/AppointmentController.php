<?php

class Admin_AppointmentController extends Base_Controller_Action {

    public function indexAction() {
        $this->view->title = "Admin Panel- List Appointment";
        $this->view->headTitle("Admin Panel");
        $doctor_name = $this->_getParam("doctor_name");
        $gender = $this->_getParam("gender");
        $status = $this->_getParam("status");
        $approved = $this->_getParam("approved");
        $type = $this->_getParam("type");
        $where = array();
        if ($doctor_name != "") {
            //Check doctor name
            $Doctor = new Application_Model_Doctor();
            $objDoctor = $Doctor->fetchAll("fname like '%{$doctor_name}%'");
            $arDoctorid = array();
            foreach ($objDoctor as $doctor) {
                $arDoctorid[] = $doctor->getId();
            }
            $str_doctorid = "";
            if (count($arDoctorid) > 0)
                $str_doctorid = implode(",", $arDoctorid);



            if (!empty($str_doctorid))
                $where[] = "fname like '%{$doctor_name}%' OR doctor_id in ({$str_doctorid})";
        }
		
		$usersNs = new Zend_Session_Namespace("members"); 
		$Usern = new Application_Model_User();
		$Usern = $Usern->fetchRow("id=$usersNs->userId");
		if($Usern->getUserLevelId() == 4){
			$Assistant = new Application_Model_Assistant();
			$Assistant = $Assistant->fetchRow("userid=$usersNs->userId");
            $DoctorAssistant = new Application_Model_DoctorAssistant();
            $doctorAssistants = $DoctorAssistant->fetchAll('assistant_id='.$Assistant->getId());
            $arDoctorid = array();
            foreach($doctorAssistants as $doctorAssist) {
                $arDoctorid[] = $doctorAssist->getDoctorId();
            }

            $str_doctorid = "";
            if (count($arDoctorid) > 0)
                $str_doctorid = implode(",", $arDoctorid);

            if (!empty($str_doctorid))
                $where[] = "doctor_id in ({$str_doctorid})";
		}

        if ($gender != "a" && !empty($gender)) {
            $where[] = "gender ='{$gender}'";
        }

        if ($status != "a" && !empty($status)) {
            $where[] = "patient_status ='{$status}'";
        }

        if ($approved != "a" && !empty($approved)) {

            $where[] = "approve ={$approved}";
        }
        if ($type != "a" && !empty($type)) {
            $where[] = "appointment_type ={$type}";
        }

        $where[] = "deleted!=1";

        $where_condition = null;
        if (count($where) > 0)
            $where_condition = implode(" AND ", $where);
        // echo "approve=".$approved;
        //die($where_condition);

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Appointment();

        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size, $where_condition, "id DESC");

        $this->view->search_text = $doctor_name;
        $this->view->gender = $gender;
        $this->view->status = $status;
        $this->view->approved = $approved;
        $this->view->type = $type;






        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;

        $this->view->msg = base64_decode($this->_getParam('msg', ''));
		
		$settings = new Admin_Model_GlobalSettings();
		$this->view->dateFormat = $settings->settingValue('date_format');
    }

    public function cancelAction() {
        $options = array();
        $id = $this->_getParam('id');

        $Appointment = new Application_Model_Appointment();
        $appObject = $Appointment->fetchRow("id='{$id}'"); //echo "<pre>";print_r($appObject);exit;

        $Doctor = new Application_Model_Doctor();
        $docobj = $Doctor->fetchRow("id='{$appObject->getDoctorId()}'");

        $User = new Application_Model_User();
        $userobj = $User->find($docobj->getUserId());

        /* -----------cancel Appointent Patient/Doctor Email ------------ */
        $options['ptname'] = $appObject->getFname()." ".$appObject->getLname();
        $options['dname'] = $docobj->getFname();
        $options['drid'] = $docobj->getId();
        $options['datetime'] = $appObject->getAppointmentDate() . " " . $appObject->getAppointmentTime();
        $options['daddress'] = $docobj->getStreet() . "<br>" . $docobj->getCity() . ", " . $docobj->getCountry() . " " . $docobj->getZipcode();
        $options['site_url'] = "http://" . $_SERVER['HTTP_HOST'];
        $options['pemail'] = $appObject->getEmail();
        if ($userobj)$options['demail'] = $userobj->getEmail(); // if doctor has email address
			$options['pphone'] = $appObject->getPhone();
        $options['pzip'] = $appObject->getZipcode();
        $options['page'] = $appObject->getAge();

        if ($appObject->getGender() == "m") {
            $options['pgender'] = "Male";
        } else {
            $options['pgender'] = "Female";
        }
        if ($appObject->getPatientStatus() == "e") {
            $options['pStatus'] = "Existing";
        } else {
            $options['pStatus'] = "New";
        }

        $Reason = new Application_Model_ReasonForVisit();
        $resobj = $Reason->fetchRow("id='{$appObject->getReasonForVisit()}'");
        if ($resobj) {
            $options['reason_for_visit'] = $resobj->getReason();
        } else {
            $options['reason_for_visit'] = $appObject->getNeeds();
        }



        /* -----------send Appointent patient/Doctor Email ------------ */

        $appObject->setApprove(2);
        $appObject->setCancelledBy(1); // 1 for admin cancelled

        $appObject->save();

        $Mail_New = new Base_Mail('UTF-8');
        $Mail_New1 = new Base_Mail('UTF-8');
        $Mail_New2 = new Base_Mail('UTF-8');
        if ($appObject->getApprove() != 1) {
            $Mail_New->sendCancelAppointmentPatientMailEnquiry($options);

            $Mail_New2->sendCancelAppointmentAdminMailEnquiry($options);
        } else {

            $Mail_New->sendCancelAppointmentPatientMailEnquiry($options);
            if ($userobj)$Mail_New1->sendCancelAppointmentDoctorMailEnquiry($options); // if doctor has email address
				$Mail_New2->sendCancelAppointmentAdminMailEnquiry($options);
        }
        $mag = base64_encode("Appointment cancelled");
        $this->_helper->redirector('index', 'appointment', "admin", Array('msg' => $msg));
    }

    public function setascalledAction() {

        $ids = $this->_getParam('ids');

        $page = $this->_getParam('page');
        $doctor_name = $this->_getParam("doctor_name");
        $gender = $this->_getParam("gender");
        $status = $this->_getParam("status");
        $approved = $this->_getParam("approved");
        $type = $this->_getParam("type");

        $idArray = explode(',', $ids);
        $model = new Application_Model_Appointment();

        foreach ($idArray as $id) {
            $object = $model->find($id); //echo "<pre>";print_r($object);
            $object->setCalledStatus('Y');
            $object->save();
        }

        $msg = base64_encode("Appoinment has been set as called.");
        $this->_helper->redirector('index', 'appointment', "admin", Array('page' => $page, "doctor_name" => $doctor_name, "gender" => $gender, "status" => $status, "approved" => $approved, "type" => $type, 'msg' => $msg));
    }

    public function viewdetailAction() {
        $id = $this->_getParam('ids');
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
            $profileImage = "/images/doctor_image/" . $docObject->getCompanyLogo();
            if (!file_exists(getcwd() . $profileImage) || $docObject->getCompanylogo() == '')
                $profileImage = "/images/doctor_image/png.png";
            else
                $profileImage = "/images/doctor_image/" . $docObject->getCompanyLogo();
            $this->view->profileImage = $profileImage;


            $this->view->docObject = $docObject;
            $this->view->patObject = $patObject;
            $this->view->appGender = $appGender;
            $this->view->appStatus = $appStatus;
            $this->view->visitObject = $visitObject;
            $this->view->ids = $id;
            $this->view->insuranceObject = $insuranceObject;
        }
        $this->view->appObject = $appObject;
    }

    public function deleteAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Appointment();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            if ($object) {
                $object->setDeleted(1);
                $object->save();
            }

            //$object->delete("id={$id}");
        }
        // delete after article delete
        $msg = base64_encode("Record(s) has been deleted successfully!");
        $this->_helper->redirector('index', 'appointment', "admin", Array('msg' => $msg, 'page' => $page));
    }

    public function publishAction() {
        $ids = $this->_getParam('ids');

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

            if ($object->getApprove() != 1 && $object->getApprove() != 2) {

                $objDoctor = $Doctor->find($object->getDoctorId());
                $objUser = $User->find($objDoctor->getUserId());

                // echo "<pre>";print_r($objDoctor);
                //echo "<pre>";print_r($objUser);exit;
                $Doctor_name = $objDoctor->getFname();

                $objReasonForVisite = $ReasonForVisite->find($object->getReasonForVisit());
                $options['doctor_email'] = $objUser->getEmail();
                $options['reasonforvisit'] = $objReasonForVisite->getReason();
                $options['doctor_name'] = $Doctor_name;
                $options['pname'] = $object->getFname()." ".$object->getLname();
                $options['phone'] = $object->getPhone();
                $options['email'] = $object->getEmail();
                $options['age'] = $object->getAge();
                $options['zipcode'] = $object->getZipcode();
                $options['day'] = date('l', strtotime($object->getAppointmentDate()));
                $options['date'] = $object->getAppointmentDate();
                $options['time'] = $object->getAppointmentTime(); //echo "<pre>";print_r($objUser);exit;
                $options['gender'] = $object->getFullGender("id={$id}");
                $options['patient_status'] = $object->getFullPatientStatus("id={$id}");

                $object->setApprove('1');
                $mail_counter = $object->getMailCounterForDoctor();
                $insurance_name = "";
                $plan_name = "";
                $insuranceObject = new Application_Model_InsuranceCompany();
                $insurance_id = $object->getInsurance();
                $plan_id = $object->getPlan();
                if ($insurance_id > 0) {
                    $objInsurance = $insuranceObject->find($insurance_id);
                    if ($objInsurance)
                        $insurance_name = $objInsurance->getCompany();
                }

                if ($plan_id > 0) {
                    $ObjectPlan = new Application_Model_InsurancePlan();
                    $objPlan = $ObjectPlan->find($plan_id);
                    if (!empty($objPlan)) {
                        $plan_name = $objPlan->getPlan();
                    }
                }
                $options['insurance'] = $insurance_name;
                $options['plan'] = $plan_name;

                $mail_counter++;
                $object->setMailCounterForDoctor($mail_counter);
                $Mail = new Base_Mail('UTF-8');
                $Mail->sendDoctorAppointmentBookinhotmail($options);
                //$Mail1 = new Base_Mail('UTF-8');
                // $Mail1->sendPatientAppointmentApprovedMail($options);
                $object->save();
            }
        }

        $publish = base64_encode("Record(s) approved successfully");
        $this->_helper->redirector('index', 'appointment', "admin", Array('page' => $page, "doctor_name" => $doctor_name, "gender" => $gender, "status" => $status, "approved" => $approved, "type" => $type, 'msg' => $publish));
    }

    public function sendtodoctorAction() {
        $ids = $this->_getParam('ids');

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
            //echo "id=".$id;
            $object = $model->find($id);

            if ($object->getApprove() != 1 && $object->getApprove() != 2) {

                $objDoctor = $Doctor->find($object->getDoctorId());
                $objUser = $User->find($objDoctor->getUserId());

                // echo "<pre>";print_r($objDoctor);
                //echo "<pre>";print_r($objUser);exit;
                $Doctor_name = $objDoctor->getFname();

                $objReasonForVisite = $ReasonForVisite->find($object->getReasonForVisit());
                $options['doctor_email'] = $objUser->getEmail();
                if ($objReasonForVisite) {
                    $options['reasonforvisit'] = $objReasonForVisite->getReason();
                } else {
                    $options['reasonforvisit'] = $object->getNeeds();
                }
                $options ['office'] = $objDoctor->getCompany();
                $options['doctor_name'] = $options ['doctor'] = $Doctor_name;
                $options['pname'] = $objUser->getFirstName()." ".$objUser->getLastName();
                $options['address1'] = $objDoctor->getStreet() . "<br>" . $objDoctor->getCity() . ", " . $objDoctor->getCountry() . " " . $objDoctor->getZipcode();
                $options['address2'] = "";
                $options ['name'] = $object->getFname()." ".$object->getLname();
                $options ['email'] = $objUser->getEmail();
                $options['phone'] = $object->getPhone();
                $options ['time'] = $object->getAppointmentTime();
                $options ['date'] = $object->getAppointmentDate();
                $options ['PTPhone'] = $object->getPhone();
                $options['email'] = $object->getEmail();
                $options['age'] = $object->getAge();
                $options['zipcode'] = $object->getZipcode();
                $options['day'] = date('l', strtotime($object->getAppointmentDate()));
                $options['date'] = $object->getAppointmentDate();
                $options['time'] = $object->getAppointmentTime(); //echo "<pre>";print_r($objUser);exit;
                $options['gender'] = $object->getFullGender("id={$id}");
                $options['patient_status'] = $object->getFullPatientStatus("id={$id}");

                $object->setApprove('3');
                $mail_counter = $object->getMailCounterForDoctor();
                $insurance_name = "";
                $plan_name = "";
                $insuranceObject = new Application_Model_InsuranceCompany();
                $insurance_id = $object->getInsurance();
                $plan_id = $object->getPlan();
                if ($insurance_id > 0) {
                    $objInsurance = $insuranceObject->find($insurance_id);
                    if ($objInsurance)
                        $insurance_name = $objInsurance->getCompany();
                }

                if ($plan_id > 0) {
                    $ObjectPlan = new Application_Model_InsurancePlan();
                    $objPlan = $ObjectPlan->find($plan_id);
                    if (!empty($objPlan)) {
                        $plan_name = $objPlan->getPlan();
                    }
                }
                $options['insurance'] = $insurance_name;
                $options['plan'] = $plan_name;

                $mail_counter++;
                /* $object->setMailCounterForDoctor($mail_counter); */
                $Mail = new Base_Mail('UTF-8');
                $Mail->sendDoctorAppointmentAssignMail($options);
                $object->save();
            }
        }

        $publish = base64_encode("Appointment sent to doctor successfully");
        $this->_helper->redirector('index', 'appointment', "admin", Array('page' => $page, "doctor_name" => $doctor_name, "gender" => $gender, "status" => $status, "approved" => $approved, "type" => $type, 'msg' => $publish));
    }

    public function unpublishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');
        $doctor_name = $this->_getParam("doctor_name");
        $gender = $this->_getParam("gender");
        $status = $this->_getParam("status");
        $approved = $this->_getParam("approved");
        $type = $this->_getParam("type");
        $idArray = explode(',', $ids);
        $model = new Application_Model_Appointment();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            if ($object->getApprove() != -1 && $object->getApprove() != 2) {
                $object->setApprove(-1);
                $object->save();
            }
        }
        $publish = base64_encode("Record(s) unapproved successfully");
        $this->_helper->redirector('index', 'appointment', "admin", Array('page' => $page, "doctor_name" => $doctor_name, "gender" => $gender, "status" => $status, "approved" => $approved, "type" => $type, 'msg' => $publish));
    }

    public function exportAction() {
        $Export = new Base_Export();
        $Export->getWebAppointmentData();
        die("pz wait");
    }
	
	public function newappointmentAction(){
		$doctorId = $this->_getParam('doctorId');
		$usersNs = new Zend_Session_Namespace("members");
		$Doctor = new Application_Model_Doctor();
		$docObject = $Doctor->fetchRow("id='".$doctorId."'");
		$drid = $docObject->getId();
		$docName = $docObject->getFname();

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
			$status = 'n';
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
			$appObject = $Appointment->fetchRow("appointment_date='$appointmentDate' AND appointment_time='$appointmentTime' AND doctor_id='{$drid}' AND deleted !=0");
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
			
			$currentUser = $User->find($usersNs->userId);
			$Appointment->setChanges("created by ".$currentUser->getFirstName());
			
			$appointmentId = $Appointment->save();
			$Appointment1 = new Application_Model_Appointment();
			$appObject = $Appointment1->fetchRow("id='{$appointmentId}'");
			$appObject->setApprove(1);
			$appObject->save();
			$appObject->setId($appointmentId);
			/* ------------------------End Insert Appointment ------------------------------ */

			if (!$appointmentId) {
				$return['err'] = 1;
				$return['msg'] = "You are registered for this site, but your appointment is not posted on the site, Please contact to site administrator.";
			}
			/* ------------------------Start Appointment Email ------------------------------ */
			
			$options = array();
			$options['email'] = $email;
			$options['password'] = $password;
			$options['name'] = $name." ".$lastname;
			$options['date'] = $appointmentDate;
			$options['time'] = $appointmentTime;
			$options['address1'] = $docObject->getStreet(). "<br>" . $docObject->getCity() . ", " . $docObject->getCountry() . " " . $docObject->getZipcode();
			$options['address2'] = "";
			$options['office'] = $docObject->getOffice();
			$options['phone'] = $docObject->getAssignPhone();
			$options['doctor'] = $docObject->getFname();

			$Mail = new Base_Mail('UTF-8');

			if ($status == 'n') {
				if($this->_getParam('send_email') == '1'){
					$Mail->sendPatientAppointmentBookingRegistrationMail($appObject, $password);
				}
			} else {
				if($this->_getParam('send_email') == '1'){
					$Mail->sendPatientAppointmentBookinhotmail($appObject);
				}
			}
			//$AdminMail = new Base_Mail('UTF-8');
			//$AdminMail->sendAdministratorAppointmentBookinhotmail($appObject); // email to site administrator
			/* ------------------------End Appointment Email ------------------------------ */

			$return['app_id'] = $appointmentId;
			$this->view->return = $return;
			$this->_helper->redirector('index', 'appointment', "admin", Array('page' => $page, "doctor_name" => $doctor_name, 'msg' => $publish));
		}
	}
	

}

?>