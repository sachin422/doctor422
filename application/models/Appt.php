<?php



/**

 * User model

 *

 * Utilizes the Data Mapper pattern to persist data. Represents a single

 * user entry.

 *

 * @uses       Application_Model_ArticleCategory

 * @package    Directory

 * @subpackage Model

 */

class Application_Model_Appointment {



    /**

     * @var int

     */

    protected $_id;

    protected $_referenceCode;

    protected $_userId;

    protected $_fname;

    protected $_lname;

    protected $_zipcode;

    protected $_phone;

    protected $_email;

    protected $_age;

    protected $_gender;

    protected $_firstVisit;

    protected $_patientStatus;

    protected $_notes;

    protected $_appointmentDate;

    protected $_appointmentTime;

    protected $_approve;

    protected $_bookingDate;

    protected $_doctorId;

    protected $_reasonForVisit;

    protected $_needs;

    protected $_insurance;

    protected $_plan;

    protected $_appointmentType;

    protected $_updateDate;

    protected $_monthDob;

    protected $_dateDob;

    protected $_yearDob;

    protected $_mailCounterForDoctor;

    protected $_mapper;

    protected $_cancelledBy;

    protected $_calledStatus;

    protected $_deleted;

    protected $_onbehalf;

    protected $_rescheduled;

    protected $_extappointmentid;

    protected $_api_outbound_sid;




    protected $_api_outbound_to; //for patient no

	protected $_apioutboundtime;

	protected $_apioutbounddate;

	protected $_apiinboundsid;

    protected $_apiinboundtime;

    protected $_apiinbounddate;

    protected $_apiinboundreply;

    protected $_mobile;

    protected $_calltype;

    protected $_deliverystatus;

    protected $_apicallstatus;

    protected $_textmessagereply;

    protected $_replytodoctor;
    
    protected $_preventativeMaintenanceOther;
	
	protected $_adaCode;

	protected $_reviewremindersent;	
	
	protected $_manualConfirm;
	
	protected $_latestReply;
	
	protected $_latestReplySid;
	
	protected $_latestReplyDatetime;

	protected $_manualConfirmationDate;	
    
    protected $_charextappointmentid;

    /**

     * Constructor

     *

     * @param  array|null $options

     * @return void

     */

    public function __construct(array $options = null) {

        if (is_array($options)) {

            $this->setOptions($options);

        }

    }



    /**

     * Overloading: allow property access

     *

     * @param  string $name

     * @param  mixed $value

     * @return void

     */

    public function __set($name, $value) {

        $method = 'set' . $name;

        if ('mapper' == $name || !method_exists($this, $method)) {

            throw new Exception('The variable is not valid');

        }

        $this->$method($value);

    }



    /**

     * Overloading: allow property access

     *

     * @param  string $name

     * @return mixed

     */

    public function __get($name) {

        $method = 'get' . $name;

        if ('mapper' == $name || !method_exists($this, $method)) {

            throw new Exception('The variable is not valid');

        }

        return $this->$method();

    }



    /**

     * Set object state

     *

     * @param  array $options

     * @return Directory_Model_DirectoryCategory

     */

    public function setOptions(array $options) {

        $methods = get_class_methods($this);

        foreach ($options as $key => $value) {

            $method = 'set' . ucfirst($key);

            if (in_array($method, $methods)) {

                $this->$method($value);

            }

        }

        return $this;

    }



    /**

     * Set entry id

     *

     * @param  int $id

     * @return Application_Model_ArticleCategory

     */

    public function setId($id) {

        $this->_id = (int) $id;

        return $this;

    }



    public function getId() {

        return $this->_id;

    }



    public function getMailCounterForDoctor() {

        return $this->_mailCounterForDoctor;

    }

    public function setReviewReminderSent($reviewremindersent) {
        $this->_reviewremindersent = (int) $reviewremindersent;
        return $this;
    }

    public function getReviewReminderSent() {
        return $this->_reviewremindersent;
    }

    public function setMailCounterForDoctor($id) {

        $this->_mailCounterForDoctor = (int) $id;

        return $this;

    }



    public function setReferenceCode($referenceCode) {

        $this->_referenceCode = (string) $referenceCode;

        return $this;

    }



    public function getReferenceCode() {

        return $this->_referenceCode;

    }



    public function setUserId($userId) {

        $this->_userId = (int) $userId;

        return $this;

    }



    public function getUserId() {

        return $this->_userId;

    }



    public function setFname($fname) {

        $this->_fname = (string) $fname;

        return $this;

    }



    public function getFname() {

        return $this->_fname;

    }



    public function setLname($lname) {

        $this->_lname = (string) $lname;

        return $this;

    }



    public function getLname() {

        return $this->_lname;

    }



    public function setZipcode($zipcode) {

        $this->_zipcode = (string) $zipcode;

        return $this;

    }



    public function getZipcode() {

        return $this->_zipcode;

    }



    public function setPhone($phone) {

        $this->_phone = (string) $phone;

        return $this;

    }



    public function getPhone() {

        return $this->_phone;

    }



    public function setEmail($email) {

        $this->_email = (string) $email;

        return $this;

    }



    public function getEmail() {

        return $this->_email;

    }



    public function setAge($age) {

        $this->_age = (int) $age;

        return $this;

    }



    public function getAge() {

        return $this->_age;

    }



    public function setGender($gender) {

        $this->_gender = (string) $gender;

        return $this;

    }



    public function getGender() {

        return $this->_gender;

    }



    public function setFirstVisit($firstVisit) {

        $this->_firstVisit = (int) $firstVisit;

        return $this;

    }



    public function getFirstVisit() {

        return $this->_firstVisit;

    }



    public function setPatientStatus($patientStatus) {

        $this->_patientStatus = (string) $patientStatus;

        return $this;

    }



    public function getPatientStatus() {

        return $this->_patientStatus;

    }



    public function setNotes($notes) {

        $this->_notes = (string) $notes;

        return $this;

    }



    public function getNotes() {

        return $this->_notes;

    }



    public function setAppointmentDate($appointmentDate) {

        $this->_appointmentDate = (string) $appointmentDate;

        return $this;

    }



    public function getAppointmentDate() {

        return $this->_appointmentDate;

    }



    public function setAppointmentTime($appointmentTime) {

        $this->_appointmentTime = (string) $appointmentTime;

        return $this;

    }



    public function getAppointmentTime() {

        return $this->_appointmentTime;

    }



    public function setApprove($approve) {

        $this->_approve = (string) $approve;

        return $this;

    }



    public function getApprove() {

        return $this->_approve;

    }



    public function setBookingDate($bookingDate) {

        $this->_bookingDate = (int) $bookingDate;

        return $this;

    }



    public function getBookingDate() {

        return $this->_bookingDate;

    }



    public function setDoctorId($doctorId) {

        $this->_doctorId = (int) $doctorId;

        return $this;

    }



    public function getDoctorId() {

        return $this->_doctorId;

    }



    public function setReasonForVisit($reasonForVisit) {

        $this->_reasonForVisit = (int) $reasonForVisit;

        return $this;

    }



    public function getReasonForVisit() {

        return $this->_reasonForVisit;

    }



    public function setNeeds($needs) {

        $this->_needs = (string) $needs;

        return $this;

    }



    public function getNeeds() {

        return $this->_needs;

    }



    public function setInsurance($insurance) {

        $this->_insurance = (int) $insurance;

        return $this;

    }



    public function getInsurance() {

        return $this->_insurance;

    }



    public function setPlan($plan) {

        $this->_plan = (int) $plan;

        return $this;

    }



    public function getPlan() {

        return $this->_plan;

    }



    public function setAppointmentType($appointmentType) {

        $this->_appointmentType = (int) $appointmentType;

        return $this;

    }



    public function getAppointmentType() {

        return $this->_appointmentType;

    }



    public function setUpdateDate($updateDate) {

        $this->_updateDate = (int) $updateDate;

        return $this;

    }



    public function getUpdateDate() {

        return $this->_updateDate;

    }



    public function setMonthDob($month) {

        $this->_monthDob = (int) $month;

        return $this;

    }



    public function getMonthDob() {

        return $this->_monthDob;

    }



    public function setDateDob($date) {

        $this->_dateDob = (int) $date;

        return $this;

    }



    public function getDateDob() {

        return $this->_dateDob;

    }



    public function setYearDob($year) {

        $this->_yearDob = (int) $year;

        return $this;

    }



    public function getYearDob() {

        return $this->_yearDob;

    }



    public function setCancelledBy($cancelledBy) {

        $this->_cancelledBy = $cancelledBy;

        return $this;

    }



    public function getCancelledBy() {

        return $this->_cancelledBy;

    }



    public function setCalledStatus($calledStatus) {

        $this->_calledStatus = $calledStatus;

        return $this;

    }



    public function getCalledStatus() {

        return $this->_calledStatus;

    }



    public function setDeleted($deleted) {

        $this->_deleted = $deleted;

        return $this;

    }



    public function getDeleted() {

        return $this->_deleted;

    }



    public function setOnbehalf($onbehalf) {

        $this->_onbehalf = $onbehalf;

        return $this;

    }



    public function getOnbehalf() {

        return $this->_onbehalf;

    }



    public function setRescheduled($rescheduled) {

        $this->_rescheduled = $rescheduled;

        return $this;

    }



    public function getRescheduled() {

        return $this->_rescheduled;

    }



    public function setMapper($mapper) {

        $this->_mapper = $mapper;

        return $this;

    }



    //Function to set and get ext AppointmentID

	

    public function setExternalAppoinmentId($extappointmentid){

        $this->_extappointmentid = $extappointmentid ;

        return $this ;  

    }

     public function getExternalAppointmentId(){

          return $this->_extappointmentid ;    

    }
    
    public function setCharExternalAppoinmentId($charextappointmentid){

        $this->_charextappointmentid = $charextappointmentid ;

        return $this ;  

    }

     public function getCharExternalAppointmentId(){

          return $this->_charextappointmentid ;    

    }

    

    /***Extra field for sms**/



    



    



	public function setApiOutboundSid($api_outbound_sid) {



        $this->_api_outbound_sid=$api_outbound_sid ;



        return $this;



    }



  



	public function setApiOutboundTo($api_outbound_to) {



        $this->_api_outbound_to=$api_outbound_to ;



        return $this;



    }



    



    public function setApiOutboundTime($apioutboundtime) {



        $this->_apioutboundtime = $apioutboundtime ;



        return $this;



    }



    



	public function setApiOutboundDate($apioutbounddate) {



        $this->_apioutbounddate = $apioutbounddate;



        return $this;



    }



    



    public function setApiInboundSid($apiinboundsid) {



        $this->_apiinboundsid = $apiinboundsid;



        return $this;



    }



    



	public function setApiInboundTime($apiinboundtime) {



        $this->_apiinboundtime=$apiinboundtime;



        return $this;



    }



    



    public function setApiInboundDate($apiinbounddate) {



        $this->_apiinbounddate=$apiinbounddate;



        return $this;



    }



    



	public function setApiInboundReply($apiinboundreply) {



        $this->_apiinboundreply = $apiinboundreply;



        return $this;



    }



    



    public function setMobile($mobile) {



        $this->_mobile = $mobile;



        return $this;



    }



    



    public function setCallType($calltype) {



        $this->_calltype = $calltype;



        return $this;



    }



    



    public function setDeliveryStatus($deliverystatus) {



        $this->_deliverystatus = $deliverystatus;



        return $this;



    }



    



	public function setApiCallStatus($apicallstatus) {



        $this->_apicallstatus = $apicallstatus;



        return $this;



    }



    



    public function setTextMessageReply($textmessagereply) {



        $this->_textmessagereply = $textmessagereply;



        return $this;



    }



    public function setReplyToDoctor($replytodoctor) {

        $this->_replytodoctor = $replytodoctor;

        return $this;

    }



    



	public function getApiOutboundSid() {



        return $this->_api_outbound_sid;



    }



  



	public function getApiOutboundTo() {



        return $this->_api_outbound_to;



    }



    



    public function getApiOutboundTime() {



        return $this->_apioutboundtime;



    }



    



	public function getApiOutboundDate() {



        return $this->_apioutbounddate;



    }



    



    public function getApiInboundSid() {



        return $this->_apiinboundsid;



    }



    



	public function getApiInboundTime() {



        return $this->_apiinboundtime;



    }



    



    public function getApiInboundDate() {



        return $this->_apiinbounddate;



    }



    



	public function getApiInboundReply() {



        return $this->_apiinboundreply;



    }



    



    public function getMobile() {



        return $this->_mobile;



    }



	



	public function getCallType() {



        return $this->_calltype;



    }



    



    public function getDeliveryStatus() {



        return $this->_deliverystatus;



    }



    



	public function getApiCallStatus() {



        return $this->_apicallstatus;



    }



    



    public function getTextMessageReply() {



        return $this->_textmessagereply;



    }

    

    public function getReplyToDoctor() {



        return $this->_replytodoctor;



    }
    
    	public function getPreventativeMaintenanceOther() {
        return $this->_preventativeMaintenanceOther;
    }
    
	public function setManualConfirm($manualConfirm) {
        $this->_manualConfirm = $manualConfirm;
        return $this;
    }

	public function getManualConfirm() {
        return $this->_manualConfirm;
    }	
    
	public function setLatestReply($latestReply) {
        $this->_latestReply = $latestReply;
        return $this;
    }

	public function getLatestReply() {
        return $this->_latestReply;
    }
	
	public function setLatestReplySid($latestReplySid) {
        $this->_latestReplySid = $latestReplySid;
        return $this;
    }

	public function getLatestReplySid() {
        return $this->_latestReplySid;
    }
    
    
	public function setLatestReplyDatetime($latestReplyDatetime) {
        $this->_latestReplyDatetime = $latestReplyDatetime;
        return $this;
    }

	public function getLatestReplyDatetime() {
        return $this->latestReplyDatetime;
    }

	
	public function setManualConfirmationDate($manualConfirmationDate) {
        $this->_manualConfirmationDate = $manualConfirmationDate;
        return $this;
    }

	public function getManualConfirmationDate() {
        return $this->_manualConfirmationDate;
    }	


    

    /**

     * Get data mapper

     *

     * Lazy loads Directory_Model_DirectoryCategoryMapper instance if no mapper registered.

     *

     * @return Directory_Model_DirectoryCategory

     */

    public function getMapper() {

        if (null === $this->_mapper) {

            $this->setMapper(new Application_Model_AppointmentMapper());

        }

        return $this->_mapper;

    }



    private function setModel($row) {

        $model = new Application_Model_Appointment();

        $model->setId($row->id)

                ->setReferenceCode($row->reference_code)

                ->setUserId($row->user_id)

                ->setFname($row->fname)

                ->setLname($row->lname)

                ->setZipcode($row->zipcode)

                ->setPhone($row->phone)

                ->setEmail($row->email)

                ->setAge($row->age)

                ->setGender($row->gender)

                ->setFirstVisit($row->first_visit)

                ->setPatientStatus($row->patient_status)

                ->setNotes($row->notes)

                ->setAppointmentDate($row->appointment_date)

                ->setAppointmentTime($row->appointment_time)

                ->setApprove($row->approve)

                ->setBookingDate($row->booking_date)

                ->setDoctorId($row->doctor_id)

                ->setReasonForVisit($row->reason_for_visit)

                ->setNeeds($row->needs)

                ->setInsurance($row->insurance)

                ->setPlan($row->plan)

                ->setAppointmentType($row->appointment_type)

                ->setUpdateDate($row->update_date)

                ->setMonthDob($row->month_dob)

                ->setDateDob($row->date_dob)

                ->setYearDob($row->year_dob)

                ->setMailCounterForDoctor($row->mail_counter_for_doctor)

                ->setCancelledBy($row->cancelled_by)

                ->setcalledStatus($row->called_status)

                ->setDeleted($row->deleted)

                ->setOnbehalf($row->onbehalf)

                ->setRescheduled($row->rescheduled)

                ->setExternalAppoinmentId($row->externalappointmentid)
                ->setCharExternalAppoinmentId($row->charexternalappointmentid)

                /***fields for sms code***/



                



                ->setApiOutboundSid($row->api_outbound_sid)



                ->setApiOutboundTo($row->api_outbound_to)



                ->setApiOutboundTime($row->api_outbound_time)



                ->setApiOutboundDate($row->api_outbound_date)



                ->setApiInboundSid($row->api_inbound_sid)



                ->setApiInboundTime($row->api_inbound_time)



                ->setApiInboundDate($row->api_inbound_date)



                ->setApiInboundReply($row->api_inbound_reply)



                ->setMobile($row->mobile)



				->setCallType($row->call_type)



                ->setDeliveryStatus($row->delivery_status)



				->setApiCallStatus($row->api_call_status)



				->setTextMessageReply($row->text_message_reply)

				

				->setReplyToDoctor($row->reply_to_doctor)
				->setReviewReminderSent($row->review_reminder_sent)
				
				->setManualConfirm($row->manual_confirm)
				
				->setManualConfirmationDate($row->manual_confirmation_date)
				
				->setLatestReply($row->latest_reply)
				
				->setLatestReplySid($row->latest_reply_sid)
				
				->getLatestReplyDatetime($row->latest_reply_datetime)
				

        ;

        return $model;

    }



    /**

     * Save the current entry

     *

     * @return void

     */

    public function save() {



        $data = array(

            'reference_code' => $this->getReferenceCode(),

            'user_id' => $this->getUserId(),

            'fname' => $this->getFname(),

            'lname' => $this->getLname(),

            'zipcode' => $this->getZipcode(),

            'phone' => $this->getPhone(),

            'email' => $this->getEmail(),

            'age' => $this->getAge(),

            'gender' => $this->getGender(),

            'first_visit' => $this->getFirstVisit(),

            'patient_status' => $this->getPatientStatus(),

            'notes' => $this->getNotes(),

            'appointment_date' => $this->getAppointmentDate(),

            'appointment_time' => $this->getAppointmentTime(),

            'booking_date' => $this->getBookingDate(),

            'doctor_id' => $this->getDoctorId(),

            'reason_for_visit' => $this->getReasonForVisit(),

            'needs' => $this->getNeeds(),

            'insurance' => $this->getInsurance(),

            'plan' => $this->getPlan(),

            'appointment_type' => $this->getAppointmentType(),

            'month_dob' => $this->getMonthDob(),

            'date_dob' => $this->getDateDob(),

            'year_dob' => $this->getYearDob(),

            'mail_counter_for_doctor' => $this->getMailCounterForDoctor(),

            'cancelled_by' => $this->getCancelledBy(),

            'called_status' => $this->getCalledStatus(),

            'onbehalf' => $this->getOnbehalf(),

            'rescheduled' => $this->getRescheduled(),
			'review_reminder_sent' => $this->getReviewReminderSent()
        );



        if (null === ($id = $this->getId())) {

            unset($data['id']);

            $data['approve'] = 0;

            $data['deleted'] = 0;

            return $this->getMapper()->getDbTable()->insert($data);

        } else {

            $data['approve'] = $this->getApprove();

            $data['deleted'] = $this->getDeleted();

            $data['update_date'] = time();

            return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $id));

        }

    }



    /**

     * Save the ext Appointments

     *

     * @return void

     */

     public function saveAppointment() {

    

        $data = array(

            'reference_code' => $this->getReferenceCode(),

            'user_id' => $this->getUserId(),

            'fname' => $this->getFname(),

            'lname' => $this->getLname(),

            'zipcode' => $this->getZipcode(),

            'phone' => $this->getPhone(),

            'email' => $this->getEmail(),

            'age' => $this->getAge(),

            'gender' => $this->getGender(),

            'first_visit' => $this->getFirstVisit(),

            'patient_status' => $this->getPatientStatus(),

            'notes' => $this->getNotes(),

            'appointment_date' => $this->getAppointmentDate(),

            'appointment_time' => $this->getAppointmentTime(),

            'booking_date' => $this->getBookingDate(),

            'doctor_id' => $this->getDoctorId(),

            'reason_for_visit' => $this->getReasonForVisit(),

            'needs' => $this->getNeeds(),

            'insurance' => $this->getInsurance(),

            'plan' => $this->getPlan(),

            'appointment_type' => $this->getAppointmentType(),

            'month_dob' => $this->getMonthDob(),

            'date_dob' => $this->getDateDob(),

            'year_dob' => $this->getYearDob(),

            'mail_counter_for_doctor' => $this->getMailCounterForDoctor(),

            'cancelled_by' => $this->getCancelledBy(),

            'called_status' => $this->getCalledStatus(),

            'onbehalf' => $this->getOnbehalf(),

            'rescheduled' => $this->getRescheduled(),

            'externalappointmentid' => $this->getExternalAppointmentId(),
            'charexternalappointmentid' => $this->getCharExternalAppointmentId(),
            
			'api_outbound_sid' => $this->getApiOutboundSid(),

            'api_outbound_to' => $this->getApiOutboundTo(),

            'api_outbound_time' => $this->getApiOutboundTime(),

            'api_outbound_date' => $this->getApiOutboundDate(),

            'api_inbound_sid' => $this->getApiInboundSid(),

            'api_inbound_date' => $this->getApiInboundDate(),

            'api_inbound_time' => $this->getApiInboundTime(),

            'api_inbound_reply' => $this->getApiInboundReply(),

            'mobile' => $this->getMobile(),

            'call_type' => $this->getCallType(),

            'delivery_status' => $this->getDeliveryStatus(),

            'text_message_reply' => $this->getTextMessageReply(),
            
            'reply_to_doctor' => $this->getReplyToDoctor()


        );



         // $external_id= 1070 ;




            if (null === ($id = $this->getId())) {

         
            unset($data['id']);

            $data['approve'] = 1;

            $data['deleted'] = 0;
		     return $this->getMapper()->getDbTable()->insert($data);

          

        } else {

    
            $data['approve'] = $this->getApprove();

            $data['deleted'] = $this->getDeleted();

            $data['update_date'] = time();
			  return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $id));

        }

    }



    /**

     * Find an entry

     *

     * Resets entry state if matching id found.

     *

     * @param  int $id

     * @return User_Model_User

     */

    public function find($id) {

        $result = $this->getMapper()->getDbTable()->find($id);

        if (0 == count($result)) {

            return false;

        }



        $row = $result->current();

        $res = $this->setModel($row);



        return $res;

    }



    /**

     * Fetch all entries

     *

     * @return array

     */

    public function fetchAll($where=null, $order=null, $count=null, $offset=null) {

        $resultSet = $this->getMapper()->getDbTable()->fetchAll($where, $order, $count, $offset);

        $entries = array();

        if (!empty($resultSet)) {

            foreach ($resultSet as $row) {

                $res = $this->setModel($row);

                $entries[] = $res;

            }

        }

        return $entries;

    }



    public function fetchRow($where=null, $order=null) {

        $row = $this->getMapper()->getDbTable()->fetchRow($where, $order);



        if (!empty($row)) {

            $res = $this->setModel($row);

            return $res;

        } else {

            return false;

        }

    }



    public function delete($where) {

        return $this->getMapper()->getDbTable()->delete($where);

    }



    public function getAppointmentStatus($where=null, $order=null) {

        $row = $this->getMapper()->getDbTable()->fetchRow($where, $order);

        $return = "";

        if (!empty($row)) {

            switch ($row->approve) {

                case '-1':

                    $return = "not approved yet";

                    break;

                case '0':

                case '3':

                    $return = "new appointment";

                    break;

                case '1':

                    $return = "confirmed";

                    break;

                case '2':

                    $return = "cancelled";

                    break;

            }

            return $return;

        }

        else

            return false;

    }



    public function getNewAppointmentStatus($where=null, $order=null) {

        $row = $this->fetchRow($where, $order);

        $return = "";

        if (!empty($row)) {

            switch ($row->getApprove()) {

                case '-1':

                    $return = "Not approved";

                    break;

                case '0':

                case '3':

                    $return = "waiting approval";

                    break;

                case '1':

                    $return = "approved";

                    break;

                case '2':

                    if ($row->getCancelledBy() == 3)

                        $return = "Cancelled by patient";

                    else

                        $return="Cancelled";



                    break;

            }

            return $return;

        }

        else

            return "";

    }



    public function getFullGender($where=null, $order=null) {

        $row = $this->getMapper()->getDbTable()->fetchRow($where, $order);

        if (!empty($row)) {

            switch ($row->gender) {

                case 'm':

                    $return = "Male";

                    break;

                case 'f':

                    $return = "Female";

                    break;

            }

            return $return;

        }

        else

            return false;

    }



    public function getFullPatientStatus($where=null, $order=null) {

        $row = $this->getMapper()->getDbTable()->fetchRow($where, $order);

        if (!empty($row)) {

            switch ($row->patient_status) {

                case 'n':

                    $return = "New";

                    break;

                case 'e':

                    $return = "Old";

                    break;

            }

            return $return;

        }

        else

            return false;

    }

    

    public function fetchRecordAppointmentReminder($where){

		$db = Zend_Db_Table::getDefaultAdapter();

		$select = $db->select();

		$select->from('appointments', '*')

			->joinLeft('user','user.email=appointments.email',array('email'))

			->joinLeft('patients','patients.user_id=user.id',array('user_id','phone','mobile'))

			->where($where)

			->group('patients.mobile')

			->limit($top);

			

		$resultSet = $db->fetchAll($select);



		$entries = array();

        if (!empty($resultSet)) {

            foreach ($resultSet as $row) {

                $res = $this->setModel($row);

                $entries[] = $res;

            }

        }

        return $entries;

	}


/*
    public function updateSMSValuesSent($sms,$appid=null){



		// echo $sms->sid;die;



		 $time = isset($sms->date_created) ? date('H:i:s',strtotime($sms->date_created)) :'';

		 $date = isset($sms->date_created) ? date('Y-m-d',strtotime($sms->date_created)) :'';

		 $mobile_no = substr(preg_replace("/[^0-9]/", '',$sms->to),1);  //this will treat for contact no also



		 $data = array(

			'called_status' => 'Y',

            'api_outbound_sid' => $sms->sid,

            'api_outbound_to' =>  $sms->to,

            'api_outbound_time' => $time,

            'api_outbound_date' => $date,

			'mobile' => $mobile_no,

			'delivery_status' => $sms->status,

			'call_type' =>'text'

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}



    



	public function updateSMSValuesReplied($sms,$appid=null,$status){

		//echo $status;

		//echo '<pre>';print_r($sms);

		//echo $sms->status;

		//echo 'shyam';

	    //echo $appid;die;



		 $time = isset($sms->date_created) ? date('H:i:s',strtotime($sms->date_created)) :'';

		 $date = isset($sms->date_created) ? date('Y-m-d',strtotime($sms->date_created)) :'';



		 $data = array(

            'api_inbound_sid' => $sms->sid,

            'api_inbound_time' => $time,

			'text_message_reply' => $sms->body,

            'api_inbound_date' => $date,

            'delivery_status' => $status,

            'reply_to_doctor' => 1

        );



		$body = $sms->body;

		$body = strtoupper($body);

		switch($body){

			case 'C':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			case 'c':
				 $confirm = 1;

                                $data +=array('api_inbound_reply'=>$confirm);


				//$cancel = 2;

				//$cancel_by = 1;

				//$data +=array('api_inbound_reply'=>$cancel,'cancelled_by'=>$cancel_by); 

			break;

			default :

				$cancel = 2;

				$cancel_by = 1;

				$data +=array('api_inbound_reply'=>$cancel,'cancelled_by'=>$cancel_by,'text_message_reply' =>$body); 

			break;

		}

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));



	}



    



	public function updateCallValues($call,$appid=null){

		 $time = date('H:i:s');

		 $date = date('Y-m-d');

		 $call_no = substr(preg_replace("/[^0-9]/", '',$call->to),1);  //this will treat for contact no also



		 $data = array(

			'called_status' => 'Y',

            'api_outbound_sid' => $call->sid,

            'api_outbound_time' => $time,

            'api_outbound_date' => $date,

			'mobile' => $call_no,

			'call_type' =>'voice'

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}

	

	public function updateCallValueOnCallFinish($call,$appid=null){

		 $time = date('H:i:s');

		 $date = date('Y-m-d');

		 $call_no = substr(preg_replace("/[^0-9]/", '',$call->to),1);  //this will treat for contact no also



		 $data = array(

			'called_status' => 'Y',

			'mobile' => $call_no,

			'api_call_status' =>$call->status

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}



	public function updateCallRecieverRespond($pressed_key,$appid,$call_status){

		//$data = array('api_call_status' => $call_status);
		 $time = date('H:i:s');

		 $date = date('Y-m-d');

		 $data = array(

			'api_inbound_time' => $time,
			
			'api_inbound_date' =>$date,
			
			'api_call_status' => $call_status

        );



        switch($pressed_key){

			case 1:

				$api_inbound_reply = '1';

				$data +=array('api_inbound_reply' => $api_inbound_reply, 'reply_to_doctor' => 1); 

			break;

			case 2:

				$cancel_by = 1;

				$api_inbound_reply = '2';

				$data +=array('cancelled_by'=>$cancel_by,'api_inbound_reply' =>$api_inbound_reply); 

			break;

			default:

				$data +=array('api_inbound_reply' => 'Got invalid input -'.$pressed_key.''); 

			break;

		}

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));



	}

	function updateDuplicateSmsOrCallDetails($appointment,$appid){

		 $called_status = $appointment->getCalledStatus();
		 $api_outbound_sid = $appointment->getApiOutboundSid();
		 $api_outbound_to = $appointment->getApiOutboundTo();
		 $api_outbound_time = $appointment->getApiOutboundTime();
		 $api_outbound_date = $appointment->getApiOutboundDate();
		 $mobile = $appointment->getMobile();
		 $delivery_status = $appointment->getDeliveryStatus();
		 $call_type = $appointment->getCallType();
		 $api_inbound_sid = $appointment->getApiInboundSid();
		 $api_inbound_time = $appointment->getApiInboundTime();
		 $text_message_reply = $appointment->getTextMessageReply();
		 $reply_to_doctor = $appointment->getReplyToDoctor();
		 $api_inbound_date = $appointment->getApiInboundDate();
		 $api_inbound_reply = $appointment->getApiInboundReply();
		 $cancelled_by = $appointment->getCancelledBy();
		 $api_call_status = $appointment->getApiCallStatus();
	
		 $data = array(
            'called_status' => $called_status,

            'api_outbound_sid' => $api_outbound_sid,

			'api_outbound_to' => $api_outbound_to,

            'api_outbound_time' => $api_outbound_time,

            'api_outbound_date' => $api_outbound_date,

            'mobile' => $mobile,
            
			'delivery_status' => $delivery_status,

            'call_type' => $call_type,

			'api_inbound_sid' => $api_inbound_sid,

            'api_inbound_time' => $api_inbound_time,

            'text_message_reply' => $text_message_reply,

            'api_inbound_date' => $api_inbound_date,
            
			'reply_to_doctor' => $reply_to_doctor,

            'api_inbound_reply' => $api_inbound_reply,

            'cancelled_by' => $cancelled_by,
            
            'api_call_status' => $api_call_status
        );
        
        return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}   */

	
    public function updateSMSValuesSent($sms,$appid){
		date_default_timezone_set('UTC');
		
		$get_time =  isset($sms->date_created) ? substr($sms->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($sms->date_created) ? substr($sms->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));

		$mobile_no = substr(preg_replace("/[^0-9]/", '',$sms->to),1);  //this will treat for contact no also

		 $data = array(

			'called_status' => 'Y',

            'api_outbound_sid' => $sms->sid,

            'api_outbound_to' =>  $sms->to,

            'api_outbound_time' => $time,

            'api_outbound_date' => $date,

			'mobile' => $mobile_no,

			'delivery_status' => $sms->status,

			'call_type' =>'text'

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}

	public function updateSMSValuesReplied($sms,$appid=null,$appoint_date,$numberTo,$status){

		date_default_timezone_set('UTC');
		 
		$get_time = substr($sms->date_created,4);
		if(!empty($get_time)){
			$time = date('H:i:s', strtotime($get_time));
		}else{
			$time = date('H:i:s');
		}
		
		
		$get_date =  substr($sms->date_created,4);
		if(!empty($get_date)){
			$date = date('Y-m-d', strtotime($get_date));
		}else{
			$date = date('Y-m-d');
		}
		

		//echo $date;die;

		 $data = array(

            'api_inbound_sid' => $sms->sid,

            'api_inbound_time' => $time,

			'text_message_reply' => $sms->body,

            'api_inbound_date' => $date,

            'delivery_status' =>  $status,

            'reply_to_doctor' => 1

        );



		$body = $sms->body;

		$body = strtoupper($body);

		switch(trim($body)){

			case 'C':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			case 'c':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			default :

				$cancel = 2;

				//$cancel_by = 1;

				$data +=array('api_inbound_reply'=>$cancel,'text_message_reply' =>$body); 

			break;

		}
		//echo $appid;
		//echo'<pre>';print_r($data);die;

		return $this->getMapper()->getDbTable()->update($data, array('appointment_date = ?' => $appoint_date,'api_outbound_to = ?' =>$numberTo));

	}
	
	
	public function updateSMSValuesRepliedManual($sms,$appid=null,$status){

		date_default_timezone_set('UTC');
		 
		$get_time = substr($sms->date_created,4);
		if(!empty($get_time)){
			$time = date('H:i:s', strtotime($get_time));
		}else{
			$time = date('H:i:s');
		}
		
		
		$get_date =  substr($sms->date_created,4);
		if(!empty($get_date)){
			$date = date('Y-m-d', strtotime($get_date));
		}else{
			$date = date('Y-m-d');
		}
		

		//echo $date;die;

		 $data = array(

            'api_inbound_sid' => $sms->sid,

            'api_inbound_time' => $time,

			'text_message_reply' => $sms->body,

            'api_inbound_date' => $date,

            'delivery_status' =>  $status,

            'reply_to_doctor' => 1

        );



		$body = $sms->body;

		$body = strtoupper($body);

		switch(trim($body)){

			case 'C':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			case 'c':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			default :

				$cancel = 2;

				//$cancel_by = 1;

				$data +=array('api_inbound_reply'=>$cancel,'text_message_reply' =>$body); 

			break;

		}
		//echo $appid;
		//echo'<pre>';print_r($data);die;
		
		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}
	
	
	public function updateSMSValuesRepliedBkp($sms,$appid=null,$status,$appoint_date,$numberTo){

		date_default_timezone_set('UTC');
		 
		$get_time =  isset($sms->date_created) ? substr($sms->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($sms->date_created) ? substr($sms->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));

		//echo $date;die;

		 $data = array(

            'api_inbound_sid' => $sms->sid,

            'api_inbound_time' => $time,

			'text_message_reply' => $sms->body,

            'api_inbound_date' => $date,

            'delivery_status' => $status,

            'reply_to_doctor' => 1

        );



		$body = $sms->body;

		$body = strtoupper($body);

		switch($body){

			case 'C':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			case 'c':

				$confirm = 1;

				$data +=array('api_inbound_reply'=>$confirm); 

			break;

			default :

				$cancel = 2;

				$cancel_by = 1;

				$data +=array('api_inbound_reply'=>$cancel,'cancelled_by'=>$cancel_by,'text_message_reply' =>$body); 

			break;

		}
		//echo $appid;
		//echo'<pre>';print_r($data);die;

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));



	}

	public function updateCallValues($call,$appid=null){

		date_default_timezone_set('UTC');
		 
		$get_time =  isset($call->date_created) ? substr($call->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($call->date_created) ? substr($call->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));

		 $call_no = substr(preg_replace("/[^0-9]/", '',$call->to),1);  //this will treat for contact no also



		 $data = array(

			'called_status' => 'Y',

            'api_outbound_sid' => $call->sid,

            'api_outbound_time' => $time,

            'api_outbound_date' => $date,

			'mobile' => $call_no,

			'call_type' =>'voice'

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}

	public function updateCallValueOnCallFinish($call,$appid=null){

		date_default_timezone_set('UTC');
		
		$get_time =  isset($call->date_created) ? substr($call->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($call->date_created) ? substr($call->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));

		$call_no = substr(preg_replace("/[^0-9]/", '',$call->to),1);  //this will treat for contact no also



		 $data = array(

			'called_status' => 'Y',

			'mobile' => $call_no,

			'api_call_status' =>$call->status

        );

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}

	public function updateCallRecieverRespond($pressed_key,$appid,$call_status,$call){

		date_default_timezone_set('UTC');
		 
		$get_time =  isset($call->date_created) ? substr($call->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($call->date_created) ? substr($call->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));
		
		 

		 $data = array(

			'api_inbound_time' => $time,
			
			'api_inbound_date' =>$date,
			
			'api_call_status' => $call_status

        );


        switch($pressed_key){

			case 1:

				$api_inbound_reply = '1';

				$data +=array('api_inbound_reply' => $api_inbound_reply, 'reply_to_doctor' => 1); 

			break;

			case 2:

				$cancel_by = 1;

				$api_inbound_reply = '2';

				$data +=array('cancelled_by'=>$cancel_by,'api_inbound_reply' =>$api_inbound_reply); 

			break;

			default:

				$data +=array('api_inbound_reply' => 'Got invalid input -'.$pressed_key.''); 

			break;

		}

		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));



	}
	
	function updateDuplicateSmsOrCallDetails($appointment,$appid){

		 $called_status = $appointment->getCalledStatus();
		 $api_outbound_sid = $appointment->getApiOutboundSid();
		 $api_outbound_to = $appointment->getApiOutboundTo();
		 $api_outbound_time = $appointment->getApiOutboundTime();
		 $api_outbound_date = $appointment->getApiOutboundDate();
		 $mobile = $appointment->getMobile();
		 $delivery_status = $appointment->getDeliveryStatus();
		 $call_type = $appointment->getCallType();
		 $api_inbound_sid = $appointment->getApiInboundSid();
		 $api_inbound_time = $appointment->getApiInboundTime();
		 $text_message_reply = $appointment->getTextMessageReply();
		 $reply_to_doctor = $appointment->getReplyToDoctor();
		 $api_inbound_date = $appointment->getApiInboundDate();
		 $api_inbound_reply = $appointment->getApiInboundReply();
		 $cancelled_by = $appointment->getCancelledBy();
		 $api_call_status = $appointment->getApiCallStatus();
	
		 $data = array(
            'called_status' => $called_status,

            'api_outbound_sid' => $api_outbound_sid,

			'api_outbound_to' => $api_outbound_to,

            'api_outbound_time' => $api_outbound_time,

            'api_outbound_date' => $api_outbound_date,

            'mobile' => $mobile,
            
			'delivery_status' => $delivery_status,

            'call_type' => $call_type,

			'api_inbound_sid' => $api_inbound_sid,

            'api_inbound_time' => $api_inbound_time,

            'text_message_reply' => $text_message_reply,

            'api_inbound_date' => $api_inbound_date,
            
			'reply_to_doctor' => $reply_to_doctor,

            'api_inbound_reply' => $api_inbound_reply,

            'cancelled_by' => $cancelled_by,
            
            'api_call_status' => $api_call_status
        );
        
        return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));

	}

	function updateReviewReminderSent($appid){
		 $data = array('review_reminder_sent' => 1);
         return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));
	}
	
	public function confirmAppointmentByEmail($appid=null){
		date_default_timezone_set('UTC');
		$date =  date('Y-m-d');
		$time =   date('H:i:s');

		$data = array(
			'api_inbound_sid' => '',
			'api_inbound_time' => $time,
			'call_type' => 'email',
			'text_message_reply' => 'C',
			'api_inbound_date' => $date,
			'delivery_status' => $status,
			'api_inbound_reply' => 1,
			'reply_to_doctor' => 1
		);
		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));
	}
	
	
	public function confirmAppointmentManually($appid=null){
		$date = date('Y-m-d');
		$data = array(
			'manual_confirm' => 1,
			'manual_confirmation_date' => $date
		);
		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));
	}
	
	public function unconfirmAppointmentManually($appid=null){
		$data = array(
			'manual_confirm' => 0,
			'manual_confirmation_date' => null
		);
		return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $appid));
	}
	
	public function updateLatestReply($sms,$appoint_date,$numberTo,$body){
		
		date_default_timezone_set('UTC');
		
		$get_time =  isset($sms->date_created) ? substr($sms->date_created,4) : date('H:i:s');
		$time = date('H:i:s', strtotime($get_time));
		
		$get_date =  isset($sms->date_created) ? substr($sms->date_created,4) : date('Y-m-d');
		$date = date('Y-m-d', strtotime($get_date));
		 

		$datetime = $date.' '.$time;

		$data = array(
			'latest_reply' => $body,
			'latest_reply_sid' => $sms->sid,
			'latest_reply_datetime' => $datetime
		);
		return $this->getMapper()->getDbTable()->update($data, array('appointment_date = ?' => $appoint_date,'api_outbound_to = ?' =>$numberTo));
	}




}



?>


