<?php
/**
 * IndexController
 * 
 * @author
 * @version 
 */
class User_PatientextController extends Base_Controller_Action {
    /**
     * The default action - show the home page
     */
    public function preDispatch() {
        parent::preDispatch();
        $usersNs = new Zend_Session_Namespace("members");
        $userid = $usersNs->userId;
    }  
    public function insertAction()
    {  
/*
$jsondata= '[{"first_name":"tactxabctestpks","last_name":"tactxabctestpks","phone":"2323232323","gendar":"M","age":"","zipcode":"22222","date_dob":"10","month_dob":"10","year_dob":"2010","mobile_no":"","state":"SD","patient_id":"777","api_key":"46d004cc-7944-11e5-8ba2-001851b60c86","unique_id":"222","email":"shiks@hotmail.com","address_new":"Ds Sd","city":"Asd","extpatinetid_char_chk":""}]';
*/
/*$jsondata= '[{"first_name":"Allison","last_name":"Stern","phone":"","gendar":"","age":"","zipcode":"92131","date_dob":"25","month_dob":"2","year_dob":"1998","mobile_no":"","state":"CA","patient_id":"5776","api_key":"46d118ab-7944-11e5-8ba2-001851b60c86","unique_id":"5776","email":"","address_new":"10731 Oak Bend Dr","city":"San Diego","extpatinetid_char_chk":""}}';
*/  

/*$jsondata= '[{"first_name":"Dylan","last_name":"Tran","phone":"858-547-3955","gendar":"","age":"","zipcode":"92131","date_dob":"11","month_dob":"10","year_dob":"2001","mobile_no":"858-547-3955","state":"CA","patient_id":"1008","api_key":"46d118ab-7944-11e5-8ba2-001851b60c86","unique_id":"1008","email":"gtran@san.rr.com","address_new":"11268 Arborside Way","city":"San Diego","extpatinetid_char_chk":""}]';
*/
/*
$jsondata= '[{"first_name":"Katy","last_name":"Philyaw","phone":"858-578-7524","gendar":"","age":"","zipcode":"92131","date_dob":"3","month_dob":"8","year_dob":"1957","mobile_no":"858-578-7524","state":"CA","patient_id":"1111","api_key":"46d118ab-7944-11e5-8ba2-001851b60c86","unique_id":"1111","email":"philyawkt@aol.com","address_new":"10271 Rue St. Jacques","city":"San Diego","extpatinetid_char_chk":""}]';
*/
/*
$jsondata= '[{"first_name":"Rahul","last_name":"Test","phone":"","gendar":"M","age":"","zipcode":"92626","date_dob":"1/11/1994 12:00:00 AM","month_dob":"1","year_dob":"1994","mobile_no":"(818) 926-5387","state":"CA","patient_id":"45010","api_key":"46d004cc-7944-11e5-8ba2-001851b60c86","unique_id":"45010","email":"rahul.test@hotmail.com","address_new":"","city":"Costa Mesa","extpatinetid_char_chk":"yes"}]';
 */
 
  $jsondata = file_get_contents('php://input');
  $jsondata = base64_decode($jsondata);

//       echo $jsondata;

//  echo "shashi";     print_r($jsondata) ;
      // require('demo.php');
       $jsonarray=json_decode($jsondata);
//          print_r();
         for($i=0;$i<(count($jsonarray));$i=$i+1)
         {
	try {
        $options= (array)$jsonarray[$i] ;
        $usersNs = new Zend_Session_Namespace("members");
        $userid = $usersNs->userId;
          $Patient = new Application_Model_Patient();
          $User = new Application_Model_User();
          $api_key  = $options['api_key'];
          $Doctor = new Application_Model_Doctor();
          $doctor = $Doctor->fetchRow("api_key = '{$api_key}'");
          $doctor_id = $doctor->getId();
          $patient_id  = $options['patient_id'];

         $extpatinetid_char_chk  = $options['extpatinetid_char_chk'];

          if($extpatinetid_char_chk<>'yes'){
         //    echo "in the insert patient for integer external patient id" ;
      //   die ;

          $db = Zend_Registry::get('db');  
          $query = "SELECT  p.id, u.username as email from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctor_id."' and p.externalpatientid='".$patient_id."' join user u on p.user_id=u.id";
                $select = $db->query($query);
          $result = $select->fetchAll();
          $email = $result['0']->email;
          $updatepatientid = $result['0']->id ;
          //internal_patient_id =row(id)
          //$patObject = $Patient->fetchRow("externalpatientid='$patient_id'");
          $patObject = $Patient->fetchRow("id='$updatepatientid'");
          if (is_object($result['0'])) {      // uppate update bracket

             $patObject->setName($options['first_name']." ".$options['last_name']);
            $last_updated = strtotime("now");
            $patObject->setStreet($options['address_new']);
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
            $patObject->setMobile($options['mobile_no']);  
            $patObject->setCommunicationViaPhone(1);
			$patObject->setCommunicationViaText(1);
			$patObject->setCommunicationViaEmail(1);
            $patObject->savepatient();
            $userObjectsave = $User->fetchRow("id='{$patObject->getUserId()}'");
    //Modified on 16 Nov 2015
     // $userObjectsave->setEmail($email);
  ///
            $userObjectsave->setFirstName($options['first_name']);
            $userObjectsave->setLastName($options['last_name']);
             $patient_id=$userObjectsave->save();
             $userid = $userObjectsave->getId()  ;

//           if (!empty($options['email'])) {   
            //error_log("checking email");
  //            $objUser = $User->fetchRow("email ='{$options['email']}' AND id !={$userid}");
    //          if (!empty($objUser)) {
      //          //error_log("problem2");
        //          $form->getElement('email')->setErrors(array("email already exists"));
            //      $emailerror = 1;
          //        $this->view->emailerror = "Email is used by another user";
          //    } else { //can use that email
              ///error_log("valid form, saving email");
                      $userObjectsave->setEmail($email);
                      $userObjectsave->save();
            //          $this->view->msg = $this->view->lang[546];

              /*        if ($options ['password'] != '') {
                      if(md5($options ['oldPassword']) == $userObjectsave->getPassword() ) {
                          $userObjectsave->setPassword(md5($options ['password']));
                          $userObjectsave->save();
                        } else {
                          $this->view->msg = $this->view->lang[941];
                          $emailerror = 1;
                        }
                    }
            }
          
          } */

              
          } else {   

                             // insert bracket
         
          
            $Doctor = new Application_Model_Doctor();
            $doctor = $Doctor->fetchRow("api_key = '{$api_key}'");
            $doctor_id = $doctor->getId();
            $request = $this->getRequest();
     // if($options['email'] == "")  {
        
        //doctor did not give an email. An automated one should be generated.
        if($options['email'] == "") { //create dummy unique email for empty emails
          $options['email'] = "patient".time().rand(1,1000)."@doctors.com";
        //  while(!$this->canCreateUser($email)){
            $options['email'] = "patient".time().rand(1,1000)."@doctors.com";       
          }
       // }
      if($options['email']<>'') { 
        //what email should be sent
        $User = new Application_Model_User();
        $user = $User->fetchRow("email = '".$options['email']."'");
        $rehotmail = true;
        if($user) {
          $rehotmail = false;
        }
        $password = "doctors".rand(1000, 9999);
        $options['password'] = $password;
        $options['doctor_id']=$doctor->getId();
        $userid = $this->createPatientinsert($options, $password);
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
          $Patient = new Application_Model_Patient();
          $patient = $Patient->fetchRow("user_id = ".$userid);
          //$options["patid"] = $patient->getId();
          $options["drid"] = $doctor->getId();
          /*//email
         // $Mail = new Base_Mail('UTF-8');       
          $options["uid"] = $userid;
          if($rehotmail) { //new user
            //$Mail->sendPatientAppointmentBookingRegistrationMail($Appointment, $password);
            $options['first_name'] = $options['name'];
            $options['last_name'] = $options['lastname'];
        //    $Mail->sendPatientMedicalHistoryMail($options);
          } else { //existing user
          //  $Mail->sendPatientAppointmentBookinhotmail($Appointment);
          }*/
         //doctor patient
       
         // $DoctorPatient = new Application_Model_DoctorPatient();
         // $docpat = $DoctorPatient->fetchRow("patient_id = ".$patient_id." AND doctor_id =".$doctor->getId());
         // if(!$docpat) { //doesn't exist, add the patient
         //   $DoctorPatient->setDoctorId($doctor->getId());
          //  $DoctorPatient->setPatientId($patient_id);
          //  $DoctorPatient->save();
          //}
          //add doctor to patient's favourites
          /*$Fav = new Application_Model_PatientFavoriteDoctor();
          $Fav->setDoctorId($doctor->getId());
          $Fav->setPatientId($patient->getId());
          $Fav->setFavoriteStatus("Favorite");
          $Fav->setCreateTime(time());
          $Fav->setUpdateTime(time());
          $Fav->save();*/
          //$return["message"] = "ok";
     //   } else {
          //error_log("no email");
       // }
      } else {
        //$return["message"] = "patient not created nor found";
      }
    } else {
      //$return["message"] = "doctor not logged in";
    }
    //echo Zend_Json::encode($return);
         }
      }else{
//echo "test";
      
          $db = Zend_Registry::get('db');  
          $query = "SELECT  p.id, u.username as email from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctor_id."' and p.charexternalpatientid='".$patient_id."' join user u on p.user_id=u.id";

 
    
          $select = $db->query($query);
          $result = $select->fetchAll();
          $email = $result['0']->email;
          $updatepatientid = $result['0']->id ;
          //internal_patient_id =row(id)
          //$patObject = $Patient->fetchRow("externalpatientid='$patient_id'");
          $patObject = $Patient->fetchRow("id='$updatepatientid'");
          if (is_object($result['0'])) {      // uppate update bracket
          $patObject->setName($options['first_name']." ".$options['last_name']);
            $last_updated = strtotime("now");
            $patObject->setStreet($options['address_new']);
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
            $patObject->setMobile($options['mobile_no']);  
			$patObject->setCommunicationViaPhone(1);
			$patObject->setCommunicationViaText(1);
			$patObject->setCommunicationViaEmail(1);
            $patObject->savepatient();
            $userObjectsave = $User->fetchRow("id='{$patObject->getUserId()}'");
    //Modified on 16 Nov 2015
     // $userObjectsave->setEmail($email);
  ///
            $userObjectsave->setFirstName($options['first_name']);
            $userObjectsave->setLastName($options['last_name']);
             $patient_id=$userObjectsave->save();
             $userid = $userObjectsave->getId()  ;

//           if (!empty($options['email'])) {   
            //error_log("checking email");
  //            $objUser = $User->fetchRow("email ='{$options['email']}' AND id !={$userid}");
    //          if (!empty($objUser)) {
      //          //error_log("problem2");
        //          $form->getElement('email')->setErrors(array("email already exists"));
            //      $emailerror = 1;
          //        $this->view->emailerror = "Email is used by another user";
          //    } else { //can use that email
              ///error_log("valid form, saving email");
                      $userObjectsave->setEmail($email);
                      $userObjectsave->save();
//echo "test";
            //          $this->view->msg = $this->view->lang[546];

              /*        if ($options ['password'] != '') {
                      if(md5($options ['oldPassword']) == $userObjectsave->getPassword() ) {
                          $userObjectsave->setPassword(md5($options ['password']));
                          $userObjectsave->save();
                        } else {
                          $this->view->msg = $this->view->lang[941];
                          $emailerror = 1;
                        }
                    }
            }
          
          } */

              
          } else { 
    
            $Doctor = new Application_Model_Doctor();
            $doctor = $Doctor->fetchRow("api_key = '{$api_key}'");
            $doctor_id = $doctor->getId();
       $request = $this->getRequest();
     // if($options['email'] == "")  {
        
        //doctor did not give an email. An automated one should be generated.
        if($options['email'] == "") { //create dummy unique email for empty emails
          $options['email'] = "patient".time().rand(1,1000)."@doctors.com";
        //  while(!$this->canCreateUser($email)){
            $options['email'] = "patient".time().rand(1,1000)."@doctors.com";       
          }
       // }
      if($options['email']<>'') { 
        //what email should be sent
        $User = new Application_Model_User();
        $user = $User->fetchRow("email = '".$options['email']."'");
        $rehotmail = true;
        if($user) {
          $rehotmail = false;
        }
        $password = "doctors".rand(1000, 9999);
        $options['password'] = $password;
        $options['doctor_id']=$doctor->getId();
        $userid = $this->createPatientinsertVarchar($options, $password);
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
          $Patient = new Application_Model_Patient();
          $patient = $Patient->fetchRow("user_id = ".$userid);
          //$options["patid"] = $patient->getId();
          $options["drid"] = $doctor->getId();
          /*//email
         // $Mail = new Base_Mail('UTF-8');       
          $options["uid"] = $userid;
          if($rehotmail) { //new user
            //$Mail->sendPatientAppointmentBookingRegistrationMail($Appointment, $password);
            $options['first_name'] = $options['name'];
            $options['last_name'] = $options['lastname'];
        //    $Mail->sendPatientMedicalHistoryMail($options);
          } else { //existing user
          //  $Mail->sendPatientAppointmentBookinhotmail($Appointment);
          }*/
         //doctor patient
       
         // $DoctorPatient = new Application_Model_DoctorPatient();
         // $docpat = $DoctorPatient->fetchRow("patient_id = ".$patient_id." AND doctor_id =".$doctor->getId());
         // if(!$docpat) { //doesn't exist, add the patient
         //   $DoctorPatient->setDoctorId($doctor->getId());
          //  $DoctorPatient->setPatientId($patient_id);
          //  $DoctorPatient->save();
          //}
          //add doctor to patient's favourites
          /*$Fav = new Application_Model_PatientFavoriteDoctor();
          $Fav->setDoctorId($doctor->getId());
          $Fav->setPatientId($patient->getId());
          $Fav->setFavoriteStatus("Favorite");
          $Fav->setCreateTime(time());
          $Fav->setUpdateTime(time());
          $Fav->save();*/
          //$return["message"] = "ok";
     //   } else {
          //error_log("no email");
       // }
      } else {
        //$return["message"] = "patient not created nor found";
      }
    } else {
      //$return["message"] = "doctor not logged in";
    }
    //echo Zend_Json::encode($return);
         }






      }
      }
 catch (Exception $e) {
}
       }
      echo "success" ;
      die();
     } 
    
    private function createPatientinsertVarchar($options, $password=false) {
    $User = new Application_Model_User();

      $email = $options["email"];
      $firstname = $options["first_name"] ;
      $lastname = $options["last_name"];
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
        $Patient->setGender($options["gendar"]);
        $Patient->setPhone($options["phone"]);
        $Patient->setMobile($options["mobile_no"]);
        $Patient->setInsuranceCompanyId("");
        $Patient->setMonthDob("");
        $Patient->setDateDob("");
        $Patient->setYearDob("");
        $Patient->setExternalVarcharPatientId($options["patient_id"]);
        $Patient->setLastUpdated(time());
        $Patient->setFirstLogin(0);
        $Patient->setStreet($options["address_new"]);
        $Patient->setCity($options["city"]);
        $Patient->setZipcode($options["zipcode"]);
        $Patient->setState($options["state"]);
        $Patient->setCommunicationViaPhone(1);
		$Patient->setCommunicationViaText(1);
		$Patient->setCommunicationViaEmail(1);
        $patientId = $Patient->save();
 
          if(isset($patientId)){
              $Doctor = new Application_Model_Doctor();
              $api_key =$options['api_key'] ;
              $DoctorPatient = new Application_Model_DoctorPatient();
              $DoctorPatient->setDoctorId($options["doctor_id"]);
              $DoctorPatient->setPatientId($patientId);
              $DoctorPatient->save();
         }

        if(!$patientId){
          error_log("Problem with registration from Appointment. Code: RegErr2 ".$Patient);
          return false;
        } else {
      }
    }
    return json_encode($json);
  }
    private function createPatientinsert($options, $password=false) {
    $User = new Application_Model_User();

      $email = $options["email"];
      $firstname = $options["first_name"] ;
      $lastname = $options["last_name"];
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
        $Patient->setGender($options["gendar"]);
        $Patient->setPhone($options["phone"]);
        $Patient->setMobile($options["mobile_no"]);
        $Patient->setInsuranceCompanyId("");
        $Patient->setMonthDob("");
        $Patient->setDateDob("");
        $Patient->setYearDob("");
        $Patient->setExternalPatientId($options["patient_id"]);
        $Patient->setLastUpdated(time());
        $Patient->setFirstLogin(0);
        $Patient->setStreet($options["address_new"]);
        $Patient->setCity($options["city"]);
        $Patient->setZipcode($options["zipcode"]);
        $Patient->setState($options["state"]);
        $Patient->setCommunicationViaPhone(1);
		$Patient->setCommunicationViaText(1);
		$Patient->setCommunicationViaEmail(1);
        $patientId = $Patient->save();
 
          if(isset($patientId)){
              $Doctor = new Application_Model_Doctor();
              $api_key =$options['api_key'] ;
              $DoctorPatient = new Application_Model_DoctorPatient();
              $DoctorPatient->setDoctorId($options["doctor_id"]);
              $DoctorPatient->setPatientId($patientId);
              $DoctorPatient->save();
         }

        if(!$patientId){
          error_log("Problem with registration from Appointment. Code: RegErr2 ".$Patient);
          return false;
        } else {
      }
    }
    return json_encode($json);
  }

  public function insertAppointmentAction()
    {  
 /*
$jsondata  = '[{"api_key":"46d004cc-7944-11e5-8ba2-001851b60c86","appointment_id":"22078","appointment_date":"10/21/2014 12:00:00 AM","gender":"M","appointment_time":"10:30:00","external_patient_id":"11p","first_name":"Clarice","last_name":"Dalusong","zipcode":"91709","phone":"(909) 525-7566","email":"clarice.coronado@yahoo.com","patient_status":"e","approve":"0","cancelled_by":"","booking_date":"","appointment_type":"0","extappointmentid":"2345","unique_id":"22078","address_new":"","charextappointmentchk":"no","charextpatientidchk":"yes"}]';

 */
/*
 $jsondata  = '[{"api_key":"46d10d89-7944-11e5-8ba2-001851b60c86","appointment_id":"26530","appointment_date":"1/9/2016 12:00:00 AM","gender":"M","appointment_time":"08:00:00","external_patient_id":"RODMI000","first_name":"Miguel","last_name":"Rodriguez","zipcode":"91706","phone":"(626) 417-7344","email":"","patient_status":"e","approve":"0","cancelled_by":"","booking_date":"","appointment_type":"0","extappointmentid":"26530","unique_id":"26530","address_new":"","charextappointmentchk":"no","charextpatientidchk":"yes"}]';
*/
/*

$jsondata  = '[{"api_key":"46d004cc-7944-11e5-8ba2-001851b60c86","appointment_id":"91025","appointment_date":"4/18/2016 06:00:00 AM","gender":"M","appointment_time":"06:00:00","external_patient_id":"45006","first_name":"test shyam","last_name":"Camson","zipcode":"91706","phone":"(307) 459-0850","email":"","patient_status":"e","approve":"0","cancelled_by":"","booking_date":"","appointment_type":"0","extappointmentid":"2165382","unique_id":"2165382","address_new":"","charextappointmentchk":"no","charextpatientidchk":"no"}]';

/*$jsondata  = '[{"api_key":"46d118ab-7944-11e5-8ba2-001851b60c86","appointment_id":"40529","appointment_date":"4/4/2016 12:00:00 AM","gender":"","appointment_time":"00:00:00","external_patient_id":"282","first_name":"Sid","last_name":"Shapiro","zipcode":"92131","phone":"858-578-6253","email":"sidcat@earthlink.net","patient_status":"e","approve":"0","cancelled_by":"","booking_date":"4/4/2016 12:00:00 AM","appointment_type":"0","extappointmentid":"282","unique_id":"282","address_new":"10957 Elderwood Lane","charextappointmentchk":"no","charextpatientidchk":""}]';
i
*/



$jsondata  = 
'[{"api_key":"46d004cc-7944-11e5-8ba2-001851b60c86","appointment_id":"45152","appointment_date":"2016-05-26","gender":"F","appointment_time":"23:35","external_patient_id":"45010","first_name":"Rahul","last_name":"Test","zipcode":"92626","phone":"(949) 424-8919","email":"rahul.test4511@mailinator.com","patient_status":"e","approve":"0","cancelled_by":"","booking_date":"26/5/2016 23:35:00 AM","appointment_type":"0","extappointmentid":"4515222","unique_id":"4515222","address_new":"2250 Vanguard Way ","charextappointmentchk":"","charextpatientidchk":"","confirmation_status":""}]';





 // $jsondata = file_get_contents('php://input');
 // $jsondata = base64_decode($jsondata);
    // print_r($jsondata);  die ;
	$jsonarray=json_decode($jsondata);
   for($i=0;$i<(count($jsonarray));$i=$i+1)
         {
          //echo 'test22222aaaa'.$i;
//echo  $options['appointment_date'];
//echo	 $time = strtotime($options['appointment_date']);
//echo	$options['appointment_date'] = date('Y-m-d',$time);
//die();
//	echo $newformat;

         $options= (array)$jsonarray[$i] ;
        $options['appointment_date'];
        $time = strtotime($options['appointment_date']);
	$options['appointment_date'] = date('Y-m-d',$time);
//die();

         $first_name=$options['first_name'] ;
         $api_key   = $options['api_key'];
         $last_name = $options['last_name'] ;
         $zipcode  = $options['zipcode'] ;
         $phone   =  $options['phone'] ;
         $email = $options['email'] ;
         $age    =  $options['age'] ;
         $gender = $options['gender'] ;
         $reasonforvisit =  $options['reasonforvisit'] ;
         $need = $options['need'] ;
         $appointmentid = $options['appointment_id'] ;
         $appointmentdate = $options['appointment_date'] ;
         $appointmenttime = $options['appointment_time'] ;
         $externalpatientid = $options['external_patient_id'] ;  
		 $charextappointmentchk = $options['charextappointmentchk'] ;  
		 $charextpatientidchk = $options['charextpatientidchk'];  
		$confirmation_status = isset($options['confirmation_status']) ? $options['confirmation_status'] :'';

  if( $options['approve']=="0")
  {
    $appointment_status = 1;
  } 
  else
  {
    $appointment_status = 2;
  
  }
//echo  $appointment_status;
 /*  code for fetching doctor id     start  
                    */
//
         $Doctor = new Application_Model_Doctor();
         $doctor = $Doctor->fetchRow("api_key = '{$api_key}'");
     
     //print_r($doctor) ;//die();
      $doctorid =$doctor->getId();
      //print_r($doctorid) ;die();

 /*  code for fetching doctor id     end                   */
 /* code for fetching userid  start                   */
       $Patient = new Application_Model_Patient();
       $db = Zend_Registry::get('db');  

  if($i==0)
  { 
    $query = "update appointments set approve=2 where appointment_date>=now() and externalappointmentid>".$appointmentid." and doctor_id='".$doctorid."'";
    $updateDB = $db->query($query);
  }

       //$query = "SELECT  p.id from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctorid."' and p.externalpatientid='".$externalpatientid."'";
      //echo $charextappointmentchk ;
      //echo   $charextpatientidchk  ;

           if($charextappointmentchk<>'yes'){
           $query = "SELECT  p.id, u.username as email from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctorid."' and p.externalpatientid='".$externalpatientid."' join user u on p.user_id=u.id";
      }
        if($externalpatientid<>''){

          if($charextpatientidchk<>'yes'){
           $query = "SELECT  p.id, u.username as email from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctorid."' and p.externalpatientid='".$externalpatientid."' join user u on p.user_id=u.id";
           }
            if($charextpatientidchk=='yes'){  
       $query = "SELECT  p.id, u.username as email from patients p join doctor_patient dp ON p.id=dp.patient_id and dp.doctor_id='".$doctorid."' and p.charexternalpatientid='".$externalpatientid."' join user u on p.user_id=u.id";
           }

       $select = $db->query($query);
       $result = $select->fetchAll();
       $updatepatientid = $result['0']->id ;
   if($email=='' || empty($email))
        {
                $email =  $result['0']->email;
        }

     //  echo  $updatepatientid ;
//       $patObject = $Patient->fetchRow("id='$updatepatientid'");
  //     $userid=  $patObject->getUserId();
       }
$patObject = $Patient->fetchRow("id='$updatepatientid'");
//       $userid=  $patObject->getUserId();

  /* code for fetching userid  end    */
	if(!empty($patObject)) {
	 $userid=  $patObject->getUserId();

         $externalappointmentid= $options['extappointmentid'] ;
          $options  = json_decode($insertDetail) ; 
          $fulldate = explode("-",$options->appointment_date);
          $datecurrent =  $fulldate['2']."-".$fulldate["1"]."-".$fulldate["0"] ;      
          $Appointment = new Application_Model_Appointment();
          $Appointment->setUserId($userid); //Fetch from Patient table User Id field based on external patient id
          $Appointment->setFname($first_name);
          $Appointment->setLname($last_name);
          $Appointment->setZipcode($zipcode);
          $Appointment->setPhone($phone);
          $Appointment->setEmail($email);
          $Appointment->setAge($age);
          $Appointment->setGender($gender);
          $Appointment->setFirstVisit(0);
          $Appointment->setPatientStatus(1);
          $fulldate = explode("-",$options->fulldate);
          $Appointment->setAppointmentDate($appointmentdate); 
          $Appointment->setAppointmentTime($appointmenttime);
          $Appointment->setBookingDate(time());
           
            
//         echo $appointmentdate; 
              //doctor_id write here   
       
         $Appointment->setDoctorId($doctorid);
          $Appointment->setReasonForVisit($reasonforvisit);
          $Appointment->setNeeds($need);
          $Appointment->setInsurance(0);
          $Appointment->setPlan(0);
          $Appointment->setMonthDob("");
          $Appointment->setDateDob("");
          $Appointment->setYearDob("");
          $Appointment->setAppointmentType('1');
          $Appointment->setCancelledBy('0');
          $Appointment->setOnbehalf(0);
          $Appointment->setRescheduled(0);
          if($charextappointmentchk<>'yes'){
           $Appointment->setExternalAppoinmentId($externalappointmentid);
           }
            if($charextappointmentchk=='yes'){
           $Appointment->setCharExternalAppoinmentId($externalappointmentid);
           }                 
           if($charextappointmentchk<>'yes'){
            $appointmentObject = $Appointment->fetchRow("externalappointmentid='$externalappointmentid' and user_id='$userid'");
            }
            if($charextappointmentchk=='yes'){
         $appointmentObject = $Appointment->fetchRow("charexternalappointmentid='$externalappointmentid' and user_id='$userid'");
           }
           
              /***code for confirmed appointment***/
           
           
           if($confirmation_status=='1' && empty($appointmentObject)){
			    date_default_timezone_set('UTC');
				$mobile = $patObject->getMobile();

			    $call_date = date('Y-m-d');  ///dummy use because of api entry and to track confirmation
			    $call_time = date('H:i:s');  ///dummy use because of api entry and to track confirmation
			    $mobile_no = preg_replace("/[^0-9]/", '',$mobile);
			    $Appointment->setCalledStatus('Y');
			    $Appointment->setApiOutboundSid('via_api');
			    $Appointment->setApiOutboundTo($mobile);
			    $Appointment->setApiOutboundDate($call_date);
				$Appointment->setApiOutboundTime($call_time);
				$Appointment->setApiInboundSid('via_api');
			    $Appointment->setApiInboundDate($call_date);
			    $Appointment->setApiInboundTime($call_time);
			    $Appointment->setApiInboundReply(1);
			    $Appointment->setMobile($mobile_no);
			    $Appointment->setCallType('text');
			    $Appointment->setDeliveryStatus('sent');
				$Appointment->setTextMessageReply('C');
				$Appointment->setReplyToDoctor(1);
		   }
          
           
 		 date_default_timezone_set('America/Los_Angeles');
         /***End code for confirmed aappointment***/
 
          if(is_object($appointmentObject)) {   
			$appointmentupdateid =   $appointmentObject->getId();
			$Appointment->setId($appointmentupdateid);
			$Appointment->setApprove($appointment_status);
			
			date_default_timezone_set('UTC');
		    if($confirmation_status=='1' && !empty($appointmentObject->getApiOutboundSid()) && empty($appointmentObject->getApiInboundSid())){
				$mobile = $patObject->getMobile();
				$call_date = date('Y-m-d');  ///dummy use because of api entry and to track confirmation
				$call_time = date('H:i:s');  ///dummy use because of api entry and to track confirmation
				$mobile_no = preg_replace("/[^0-9]/", '',$mobile);
				$Appointment->setCalledStatus('Y');
				$Appointment->setApiOutboundSid($appointmentObject->getApiOutboundSid());
				$Appointment->setApiOutboundTo($appointmentObject->getApiOutboundTo());
				$Appointment->setApiOutboundTime($appointmentObject->getApiOutboundTime());
				$Appointment->setApiOutboundDate($appointmentObject->getApiOutboundDate());
				$Appointment->setApiInboundSid('via_api');
				$Appointment->setApiInboundDate($call_date);
				$Appointment->setApiInboundTime($call_time);
				$Appointment->setApiInboundReply(1);
				$Appointment->setMobile($mobile_no);
				$Appointment->setCallType('text');
				$Appointment->setDeliveryStatus('sent');
				$Appointment->setTextMessageReply('C');
				$Appointment->setReplyToDoctor(1);
			}else{
				$Appointment->setApiOutboundSid($appointmentObject->getApiOutboundSid());
				$Appointment->setApiOutboundTo($appointmentObject->getApiOutboundTo());
				$Appointment->setApiOutboundTime($appointmentObject->getApiOutboundTime());
				$Appointment->setApiOutboundDate($appointmentObject->getApiOutboundDate());
				$Appointment->setApiInboundSid($appointmentObject->getApiInboundSid());
				$Appointment->setApiInboundDate($appointmentObject->getApiInboundDate());
				$Appointment->setApiInboundTime($appointmentObject->getApiInboundTime());
				$Appointment->setApiInboundReply($appointmentObject->getApiInboundReply());
				$Appointment->setMobile($appointmentObject->getMobile());
				$Appointment->setCallType($appointmentObject->getCallType());
				$Appointment->setDeliveryStatus($appointmentObject->getDeliveryStatus());
				$Appointment->setTextMessageReply($appointmentObject->getTextMessageReply());
				$Appointment->setReplyToDoctor($appointmentObject->getReplyToDoctor());
			}
		
			date_default_timezone_set('America/Los_Angeles');

            $appointmentId = $Appointment->saveAppointment();
          }
         // echo '<pre>';print_r($Appointment);die;
          //set the approval. Needs to be done on existing appointment to accept this
          $Appointment->setApprove($appointment_status);
          $Appointment->saveAppointment();
	}
         }
         echo "success" ;
         die ;
      }
      //  field reasonforvisit ,need , gender ,age , 
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
   
        
  
  
}// end class


