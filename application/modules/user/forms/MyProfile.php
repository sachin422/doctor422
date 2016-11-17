<?php

class User_Form_MyProfile extends Zend_Form {

    public $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        array('Label', array('tag' => 'div')),
        array(array('row' => 'HtmlTag'), array('tag' => 'div')),
    );
    public $repeaterDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'elemetContainer')),
    );
    public $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        array(array('label' => 'HtmlTag'), array('tag' => 'div', 'placement' => 'prepend')),
        array(array('row' => 'HtmlTag'), array('tag' => 'div')),
    );
    public $fileDecorators = array(
        array('File'),
        array('Errors'),
        array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        array('Label', array('tag' => 'div')),
        array(array('row' => 'HtmlTag'), array('tag' => 'div')),
    );

   public function buildRepeater(array $data, $elemname) {
		$i=0;
		if($data) {
			foreach($data as $index=>$value){
				$elementName = $elemname.$i;//(string) ($index);
				$subForm = $this->getSubForm($elemname);
				$subForm->addElement('text', $elementName, array(
			            'label' => $elemname.':',
			            'required' => true,
			            'TABINDEX' => '1',
			            'class' => 'form',
						 'size' => '50',
						'maxlength' => '255',
			            'decorators' => $this->repeaterDecorators,
			            'filters' => array('StringTrim'),
			        ));
				$thisElement = $subForm->getElement($elementName);
				$thisElement->setValue($value);
				$thisElement->removeDecorator('label');
				$i++;
			}
		} else {
			$elementName = $elemname.$i;//(string) ($index);
			$subForm = $this->getSubForm($elemname);

			$subForm->addElement('text', $elementName, array(
	            'label' => $elemname.':',
	            'required' => true,
	            'TABINDEX' => '1',
	            'class' => 'form',
				 'size' => '50',
				'maxlength' => '255',
	            'decorators' => $this->repeaterDecorators,
	            'filters' => array('StringTrim'),
	        ));
			$thisElement = $subForm->getElement($elementName);
			$thisElement->setValue("");
			$thisElement->removeDecorator('label');
			$i++;
		}
	}

	public function buildRepeaterSelect(array $data, $allValues, $elemname) {
		$i=0;
		if($data) {
			foreach($data as $index=>$value){
				$elementName = $elemname.$i;//(string) ($index);
				$subForm = $this->getSubForm($elemname);

				$subForm->addElement('Select', $elementName, array(
					'label'      => 'Select '.$elemname.':',
					'class' =>'select', 
		        	'id' => $elemname.$i,
				   	'required'   => false,
		        	'decorators' => $this->repeaterDecorators,
		            'filters'    => array('StringTrim'),
		        ));
				$thisElement = $subForm->getElement($elementName);
				$thisElement->setMultiOptions($allValues);
				$thisElement->setValue($index);
				$thisElement->removeDecorator('label');
				$i++;
			}
		} else {
			$elementName = $elemname.$i;//(string) ($index);
			$subForm = $this->getSubForm($elemname);

			$subForm->addElement('Select', $elementName, array(
				'label'      => 'Select '.$elemname.':',
				'class' =>'select', 
	        	'id' => $elemname.$i,
			   	'required'   => false,
	        	'decorators' => $this->repeaterDecorators,
	            'filters'    => array('StringTrim'),
	        ));
			$thisElement = $subForm->getElement($elementName);
			$thisElement->setMultiOptions($allValues);
			$thisElement->setValue($index);
			$thisElement->removeDecorator('label');
			$i++;
		}
	}

    public function init() {

        $this->setMethod('post');

        $Timezone = new Application_Model_Timezone();
        $allTimezones =  $Timezone->getAllTimezones();
        //$allTimezones = array();
		$this->addElement('text', 'timezone', array(
			'label'      => 'Timezone:',
			'class' =>'select', 
			'id' => 'timezone',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        ));
		/*$thisElement = $this->getElement('timezone');
		$thisElement->setMultiOptions($allTimezones);
		$thisElement->setValue($index);
*/
		$this->addElement('file', 'company_logo', array(
            'label' => 'Profile Picture:',
            'class' => 'form',
            'decorators' => $this->fileDecorators
        ))->getElement('company_logo')->addValidator('Extension', false, 'png,gif,jpeg,png');
        
         $this->addElement('file', 'doctor_voice', array(
            'label' => 'Doctor recorded mp3/wav file :',
            'decorators' => $this->fileDecorators
        ))->getElement('doctor_voice')->addValidator('Extension', false, 'mp3,wav');

        $this->addElement('text', 'firstname', array(
            'label' => 'Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 //'size' => '50',
			'style' =>'width:67%;float:right;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'lastname', array(
            'label' => 'Last Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			// 'size' => '50',
			'style' =>'width:67%;float:right;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
			'maxlength' => '255',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your Last name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('text', 'specialty_title', array(
            'label' => 'Title:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 //'size' => '50',
			'style' =>'width:75%;float:right;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'specialty', array(
            'label' => 'Specialization:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'street', array(
            'label' => 'Street:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'city', array(
            'label' => 'Street:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		/*$this->addElement('text', 'state', array(
            'label' => 'Street:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));*/

        $States = new Application_Model_States();
        $allStates = $States->getStates();
        //$allTimezones = array();
		$this->addElement('Select', 'state', array(
			'label'      => 'Select state:',
			'class' =>'select', 
			'id' => 'state',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        ));
		$thisElement = $this->getElement('state');
		$thisElement->setMultiOptions($allStates);
		$thisElement->setValue($index);

		$this->addElement('text', 'zipcode', array(
            'label' => 'Street:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'phone', array(
            'label' => 'Phone:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		$this->addElement('text', 'actualphone', array(
            'label' => 'Mobile:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('textarea', 'about', array(
            'label' => 'About:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'cols' => '71',
            'rows' => '8',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('textarea', 'working_hours', array(
            'label' => 'Working Hours:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('textarea', 'hospital', array(
            'label' => 'Hospital:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
        
        
        $this->addElement('checkbox', 'enable_appointment_reminder_text', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
		$this->addElement('checkbox', 'enable_review_reminder_text', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_appointment_reminder_call', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_review_reminder_call', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_appointment_schedule_text', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_appointment_schedule_call', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
		$this->addElement('checkbox', 'enable_appointment_scheduling_email', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_appointment_reminder_email', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'enable_appointment_review_email', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'appointment_yelp_text', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));

		

		/*$insurance = new Zend_Form_SubForm();
		$elemname = 'insurance';
		$this->addSubForm($insurance, $elemname);*/
	//	$this->buildRepeaterSelect(array(), array(), $elemname);


		$category = new Zend_Form_SubForm();
		$elemname = 'category';
		$this->addSubForm($category, $elemname);
		$this->buildRepeaterSelect(array(), array(), $elemname);

		/*$hopsitals = new Zend_Form_SubForm();
		$elemname = 'hospital';
		$this->addSubForm($hopsitals, $elemname);
		$this->buildRepeaterSelect(array(), array(), $elemname);*/
		

		$languages = new Zend_Form_SubForm();
		$elemname = 'languages';
		$this->addSubForm($languages, $elemname);
		$this->buildRepeater(array(), $elemname);

		$education = new Zend_Form_SubForm();
		$elemname = 'education';
		$this->addSubForm($education, $elemname);
		$this->buildRepeater(array(), $elemname);

		$certification = new Zend_Form_SubForm();
		$elemname = 'certification';
		$this->addSubForm($certification, $elemname);
		$this->buildRepeater(array(), $elemname);

		$awards = new Zend_Form_SubForm();
		$elemname = 'text_award';
		$this->addSubForm($awards, $elemname);
		$this->buildRepeater(array(), $elemname);
		
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'myButton',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'save',
            'decorators' => $this->buttonDecorators,
        ));
    }

    public function loadDefaultDecorators() {
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}
