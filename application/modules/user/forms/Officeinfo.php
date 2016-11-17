<?php

class User_Form_Officeinfo extends Zend_Form {

    public $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    public $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array(array('label' => 'HtmlTag'), array('tag' => 'td', 'placement' => 'prepend')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    public $fileDecorators = array(
        array('File'),
        array('Errors'),
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );

    public function init() {

        $this->setMethod('post');
 
		 $this->addElement('text', 'company', array(
            'label' => 'Office Name:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '100',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

                 $this->addElement('text', 'street', array(
            'label' => 'Street:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '40',
           'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must fill in an address')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		 $this->addElement('text', 'area', array(
            'label' => 'Area:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '40',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
			  $this->addElement('text', 'city', array(
            'label' => 'City:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '40',
           'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must fill in a city')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
	  $this->addElement('text', 'state', array(
            'label' => 'State:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',

            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
			    $this->addElement('text', 'zipcode', array(
            'label' => 'Zip:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '10',
			'maxlength' => '10',
           'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter zip')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

            $this->addElement('text', 'assign_phone', array(
            'label' => 'Phone:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '20',

            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
            

              
		
		
        $this->addElement('textarea', 'about', array(
            'label' => 'About:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
	   'cols' => '60',
	    'rows' => '10',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

	 $this->addElement('textarea', 'office_hours', array(
            'label' => 'Office Hours:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
	     'cols' => '60',
	    'rows' => '10',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

         $ReasonForVisit=new Application_Model_ReasonForVisit();
        $arrReasonForVisit=$ReasonForVisit->getReasonForVisit();

        $this->addElement('Multiselect', 'doctor_reason_for_visit',array(
            'label'      => 'Select Reason to Visit:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:250px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),


        ));

        $this->addElement('Multiselect', 'doctor_reason_for_visit2',array(
            'label'      => 'Select Reason to Visit:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:250px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),


        ));
 
		$assistants_element = $this->addElement('Multiselect', 'doctor_assistant', array(
                    'label' => 'Select Assistant:',
                    'class' => 'select',
                    'TABINDEX' => '6',
                    'multiple' => 'true',
                    'style' => 'width:300px;',
                    'size' => '10',
                    'required' => false,
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
					'registerInArrayValidator' => false
					)
        );

        $assistants_element2 = $this->addElement('Multiselect', 'doctor_assistant2', array(
                    'label' => 'Select Assistant:',
                    'class' => 'select',
                    'TABINDEX' => '6',
                    'multiple' => 'true',
                    'style' => 'width:300px;',
                    'size' => '10',
                    'required' => false,
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
					'registerInArrayValidator' => false
				)
        );
		
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'save',
            'TABINDEX' => '20',
            'ignore' => true,
            'onclick'=>"selectAll(document.getElementById('doctor_reason_for_visit')); selectAll(document.getElementById('doctor_assistant'));",
            'label' => '',
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