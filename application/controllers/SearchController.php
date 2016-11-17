<?php
class SearchController extends Base_Controller_Action {


   public function preDispatch() {
        parent::preDispatch();
        $this->_helper->layout->setLayout('doctor');
                
    }

	public function indexAction() {

		$this->view->metaTitle = "";
		$this->view->metaDescription = "";
		$this->view->metaKeywords = "";
		$this->view->description = "";

        $lat = trim($this->_getParam('lat'));
        $lon = trim($this->_getParam('lon'));
        $this->view->lat = $lat;
        $this->view->lon = $lon;

		$selectedCategory = $this->_getParam($this->view->lang[933]);

        $selectedinsurance = $this->_getParam($this->view->lang[935]);
        $insurance_plan = $this->_getParam("insurance_plan");
        
        $reasonid = $this->_getParam('reason');
        $sobi2doctorname = addslashes(trim($this->_getParam('doctorname')));
        $area = trim($this->_getParam($this->view->lang[934]));
        $state = trim($this->_getParam('st'));
        $start_date = $this->_getParam('start_date');
 
        $reasons = array();
        $linkArray = array();
        $insuranceCompany = array();
        
        $this->view->selectedCategory = $selectedCategory;
        
        $this->view->selectedinsurance = $selectedinsurance;
        $this->view->insurance_plan = $insurance_plan;
        $this->view->reasonid = $reasonid;
        $this->view->doctorname = stripslashes(stripslashes($sobi2doctorname));
        $this->view->area = stripslashes(stripslashes($area));
        $this->view->start_date = $start_date;
        $this->view->isReasontoVisit = 1;

		//plugin on off
		$settings = new Admin_Model_GlobalSettings();
		//$this->view->rev = $settings->settingValue('rev_plugin');
        
        // fetch category
        $Category = new Application_Model_Category();
        $categories = $Category->fetchAll("status=1", "name ASC");
        $this->view->categories = $categories;

        if($selectedCategory) {
	        $selectedCat = $Category->fetchRow("name='".$selectedCategory."'");
	        $catid = $selectedCat->getId();
			$this->view->catid = $catid;
	        $this->view->metaTitle .= $selectedCat->getMetatitle();
			$this->view->metaDescription .= $selectedCat->getMetadescription();
			$this->view->metaKeywords .= $selectedCat->getMetakeywords();
			$this->view->description .= $selectedCat->getDescription();
	    } else {
	    	$this->view->metaTitle .= $this->view->lang[950];
			$this->view->metaDescription .= $this->view->lang[951];
			$this->view->metaKeywords .= $this->view->lang[952];
	    }

        // fetch insurance companies
		$insuranceid = null;
		$planid = null;
        $Insurance = new Application_Model_InsuranceCompany();
		if($selectedinsurance != "") {
			if($selectedinsurance != $this->view->lang[936]) {
				$selectedinsurance = str_replace("+", " ", $selectedinsurance);
				$tempinsurance = $Insurance->fetchRow("company = '".$selectedinsurance."'");
				$insuranceid = $tempinsurance->getId();

				if($insurance_plan != "") {
					$selectedinsurance_plan = str_replace("+", " ", $insurance_plan);
					$Plan = new Application_Model_InsurancePlan();
					$plan = $Plan->fetchRow("plan='".$selectedinsurance_plan."' AND insurance_company_id=".$tempinsurance->getId());
					$planid = $plan->getId();
				}

				$this->view->metaTitle .= " ".$this->view->lang[953]." ".$tempinsurance->getMetatitle();
				$this->view->metaDescription .= " ".$this->view->lang[953]." ".$tempinsurance->getMetadescription();
				$this->view->metaKeywords .= ", ".$tempinsurance->getMetakeywords();
				$this->view->description .= " ".$tempinsurance->getDescription();
			} else {
				$insuranceid = -1;
			}
		}

        $insurances = $Insurance->fetchAll(null,"company ASC" );
        $this->view->insurances = $insurances;
		
		//get patient insurance company
		
        $this->view->insuranceCompany = $insuranceCompany;
        
        if($reasonid > 0){
            $linkArray['reason'] = $reasonid;
            $Reasonfor = new Application_Model_ReasonForVisit();
            $reason = $Reasonfor->find($reasonid);

			$this->view->metaTitle .= " ".$reason->getMetatitle();
			$this->view->metaDescription .= " ".$this->view->lang[954]." ".$reason->getMetadescription();
			$this->view->metaKeywords .= ", ".$reason->getMetakeywords();
			$this->view->description .= " ".$reason->getDescription();

        }
        if($area != ''){
            $linkArray['area'] = $area;
            $this->view->metaTitle .= " ".$this->view->lang[955]." ".$area;
			$this->view->metaDescription .= " ".$this->view->lang[955]." ".$area;
			$this->view->metaKeywords .= ", ".$area;
        }
        if($state != ''){
            $linkArray['st'] = $state;
        }
        if($sobi2doctorname!=''){
            $linkArray['doctorname'] = $sobi2doctorname;
        }
        
        // fetch reason for visits
        if($catid>0) {
            $linkArray['category'] = $catid;
            $Reason = new Application_Model_ReasonForVisit();
            $reasons = $Reason->fetchAll("category_id='{$catid}' AND status=1", "reason ASC");
		}
        $this->view->reasons = $reasons;
       
		//$searchResults = $this->orderedSearch( $area, $catid, $insuranceid, stripslashes($sobi2doctorname), $reasonid);
		//error_log($insuranceid);
		$searchResults = $this->radiusSearch( $lat, $lon, $catid, $insuranceid, $planid);
	   
	   
	   
        if(isset($searchResults['other']) && count($searchResults['other']) >0){
            $this->view->otherStates = $searchResults['other'];
        }
        if(isset($searchResults['selected']) && $searchResults['selected']!=''){
            $this->view->selectedStates = $searchResults['selected'];
        }
		$this->view->linkArray = $linkArray;
        
		 
        if(count($searchResults) > 0){
            $model = new Application_Model_Doctor();

            $page_size = 15; //$settings->settingValue('pagination_size');
            $page = $this->_getParam('page', 1);

            /*$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($searchResults ['sIds']));
            $paginator->setCurrentPageNumber($page);*/

            $pageObj = new Base_Paginator();
            $paginator = $pageObj->arrayPaginator($searchResults, $page, $page_size);

            //$paginator = $pageObj->fetchPageData($model, $page, $page_size, $where);
            $this->view->total = $pageObj->getTotalCount();
            $this->view->paginator = $paginator;
            $this->view->searchResults = $searchResults;

            $paginationUrl = "";
			if($this->view->lat != '') $paginationUrl .= 'lat='.$this->view->lat;
			if($this->view->lon != '') $paginationUrl .= '&amp;lon='.$this->view->lon;
			if($this->view->area != '') $paginationUrl .= '&amp;'.$this->view->lang[934].'='.$this->view->area;
			if($this->view->catid != '') $paginationUrl .= '&amp;'.$this->view->lang[935].'='.$this->view->selectedinsurance;
			if($this->view->insuranceid != '') $paginationUrl .= '&amp;'.$this->view->lang[935].'='.$this->view->selectedinsurance;
			if($this->view->doctorname != '') $paginationUrl .= '&amp;doctorname='.$this->view->doctorname;
			if($this->view->reasonid != '') $paginationUrl .= '&amp;reason='.$this->view->reasonid;				
			$this->view->paginationUrl = $paginationUrl;

			if($page*$page_size < $pageObj->getTotalCount()){				
				$nextPage = intval($page)+1;		
				$this->view->nextUrl = $paginationUrl.'&amp;page='.$nextPage;
			}
			if($page!= 1){
				$prevPage = intval($page)-1;
				$this->view->prevUrl = $paginationUrl.'&amp;page='.$prevPage;
			}
		
        }else{
            $this->view->total = 0;
        }
		
		$sitename = $settings->settingValue('meta_title');
        $this->view->metaTitle .= " - ".$sitename;
    }// end function

    

	public function autosuggestAction(){
        $this->_helper->layout->disableLayout();
        $q = strtolower($this->_getParam('q'));
        if (!$q) return;
        $db = Zend_Registry::get('db');
        $query = "SELECT name FROM autocomplete WHERE name LIKE ".$db->quote($q.'%')." ORDER BY name ASC";
        $select = $db->query($query);
        $docObject = $select->fetchAll();
		foreach($docObject as $obj){
			echo $obj->name."\n";
		}		
        exit();
    }

	public function categoryautosuggestAction(){
        $this->_helper->layout->disableLayout();
        $q = strtolower($this->_getParam('q'));
        if (!$q) return;
        $db = Zend_Registry::get('db');
        $query = "SELECT name FROM categories WHERE status=1 AND name LIKE ".$db->quote($q.'%')." ORDER BY name ASC";
        $select = $db->query($query);
        $docObject = $select->fetchAll();
		foreach($docObject as $obj){
			echo $obj->name."\n";
		}		
        exit();
    }
	
    public function timeslotAction(){

        $post = array();
        $post['drid']       = $this->_getParam('drid');
        $post['start_date'] = $this->_getParam('start_date');
        $post['type'] = 0; // type '0' for doctor listing page.

        $Search = new Base_Timeslot();
        $Search->getAppointmentAvailability($post);
    }
	
    public function insuranceAction() {
        
        $this->_helper->layout->disableLayout();
        $drids = trim($this->_getParam('drids'));
        $comp_id = $this->_getParam('comp_id');
        $DoctorInsurance = new Application_Model_DoctorInsurance();
        if($comp_id > 0){
            $Company = new Application_Model_InsuranceCompany();
            $insuranceCompany = $Company->find($comp_id);
        }
        $returnArray = array();
        $dridArray = explode(' ', $drids);
        if(count($dridArray)){
            foreach($dridArray as $drid){
                if($comp_id > 0){
                    $object = $DoctorInsurance->fetchRow("doctor_id={$drid} AND insurance_id={$comp_id}");
                    if(!empty($object)){
                        $returnArray[$drid] = "<div class=\"in-network\">In Network</div>
        <img width=\"125\" alt=\"{$insuranceCompany->getCompany()}\" src=\"/images/insurance/{$insuranceCompany->getLogo()}\">";
                    }else{
                        $returnArray[$drid] = "<strong>Out of network.</strong><br />Please contact the Doctor's office to see if they file paperwork.";
                    }
                }elseif($comp_id==-1){
                    $returnArray[$drid] = "<span class='na'>N/A</span>";
                }else{
                    $returnArray[$drid] = "Please enter your insurance at the top of the page.";
                }
            }
        }
        echo Zend_Json::encode($returnArray);
        exit();
    }
	
	/*insurance plan ajax*/
	public function iplanajaxAction()
	{
		 $company_name=$this->_getParam('insurance');
		 $insurance_company = new Application_Model_InsuranceCompany();
		 $company = $insurance_company->fetchAll("company='{$company_name}'");
		 $company_id=$company[0]->id;
		 $insurance_plan = new Application_Model_InsurancePlan();
		 $insurance_plan1 = $insurance_plan->fetchAll("insurance_company_id='{$company_id}'");
		 //print_r($insurance_plan);die;
		 echo '<select name="insurance_plan" class="dentist plans" id="insurance_plan">';
		 
		 
		 echo '<option value="">Insurance Plan</option>';
		 foreach($insurance_plan1 as $plan)
		 {
		  echo '<option value="'.$plan->plan.'">'.$plan->plan.'</option>';
		 }
		
		 echo '</select>';
		 echo die;
		// $this->view->insurance_plan1=$insurance_plan1;	
	
	}
	
	public function orderedSearch($area, $catId, $company, $name, $reason, $gender=null) {
		$result = array();
		
		$db = Zend_Registry::get('db');
		
		if($area!=""){$area = $db->quote($area);}
		if($catId!=""){$catId = $db->quote($catId);}
		if($company!=""){$company = $db->quote($company);}
		if($name!=""){
			$name = str_replace(" ", "%", $name);
			$name = $db->quote('%'.$name.'%');}
		if($reason!=""){$reason = $db->quote($reason);}
		
		$query= "SELECT DISTINCT id FROM doctors WHERE status=1";
		$where ="";

		if($area!=""){
			$where.=" AND ( area = $area OR city = $area OR country= $area OR state= $area OR zipcode = $area)";
		}
		
		if($catId!="") {
			$where .=" AND ( id in (SELECT doctor_id FROM doctor_categories WHERE category_id = $catId) )";
		}
		
		if($company!="") {
			$where .=" AND ( id in (SELECT doctor_id FROM doctor_insurance WHERE insurance_id = $company) )";
		}
		
		if($name!="") {
			$where .=" AND fname LIKE $name ";
		}
		
		if($reason!="") {
			$where .=" AND ( id in (SELECT doctor_id FROM doctor_reason_for_visit WHERE reason_id = $reason) )";
		}

		if($gender == $this->view->lang[117]) {
			$where .=" AND gender = 'm' ";
		} 

		if($gender == $this->view->lang[118]) {
			$where .=" AND gender = 'f' ";
		} 
		
		$queryByMembership = $query.$where." ORDER BY FIELD(membership_level, 'Premium', 'Intermediate', 'Free') ASC, fname ASC";
		$select = $db->query($queryByMembership);
        $result = $select->fetchAll();
		
		return $result; 
	}

	function radiusSearch($lat, $long, $catId, $company, $planid, $notIn ="") {
		$result = array();
		$db = Zend_Registry::get('db');
		
		if($lat !="" && $long !="") {
			$alt_where= "SELECT DISTINCT id, '' as countt, (6371 * ACOS( COS( RADIANS(".$lat.") ) * COS( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',1) ) ) * COS( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',-1) ) - RADIANS(".$long.") ) + SIN( RADIANS(".$lat.") ) * SIN( RADIANS( SUBSTRING_INDEX(doctors.geocode, ',',1)) ) )) AS distance FROM doctors WHERE STATUS=1 ";
			if($notIn != "") {
				$area = $notIn;
				$alt_where .= " AND area != ".$area." AND city != ".$area." AND country != ".$area." AND state != ".$area." AND street NOT LIKE ".$area." AND area_en != ".$area." AND city_en != ".$area." AND street_en NOT LIKE ".$area." AND country_en != ".$area." AND state_en != ".$area." ";
			}
			if($catId!="") {
				$alt_where .= " AND ( id in (SELECT doctor_id FROM doctor_categories WHERE category_id = ".$db->quote($catId).") ) ";
			}
			if($company!="") {
				$alt_where .=" AND ( id in (SELECT doctor_id FROM doctor_insurance WHERE insurance_id = $company) )";
			}
			if($company!="") {
				$alt_where .=" AND ( id in (SELECT doctor_id FROM doctor_insurance WHERE insurance_id = $company) )";
			}
			if($planid!="") {
				$alt_where .=" AND ( id in (SELECT doctor_id FROM doctor_insurance_plan WHERE plan_id = $planid) )";
			}
			$distance = 60;
			
			$queryGlobal = $alt_where." HAVING distance < ".$distance." ORDER BY distance ASC, membership_level DESC, fname ASC";
			//error_log($queryGlobal);
			$select = $db->query($queryGlobal);
			$result = $select->fetchAll();
		}
		return $result; 
	}

}// end class

function removeEmptyArrayNode($var) {
	return trim($var);
}