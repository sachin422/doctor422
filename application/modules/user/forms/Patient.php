<?php

class User_Form_Patient extends Zend_Form {

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
        $this->addElementPrefixPath('Base_Validate', 'Base/Validate/', 'validate');
        $InsuranceCompany = new Application_Model_InsuranceCompany();
        $arrCompany = array();
        $arrCompany = $InsuranceCompany->getInsurancecompanies();
        
		$this->addElement('file', 'profileimage', array(
            'label' => 'Profile Picture:',
            'class' => 'form',
            'decorators' => $this->fileDecorators
        ))->getElement('profileimage')->addValidator('Extension', false, 'png,gif,jpeg,png');

        $this->addElement('text', 'first_name', array(
            'placeholder' => 'First Name',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'style' =>'width:65%;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your first name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('text', 'last_name', array(
            'placeholder' => 'Last name',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'style' =>'width:60%;float:right;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your last name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'street', array(
            'placeholder' => 'Street',
            'required' => false,
            'TABINDEX' => '1',
             'style' =>'width:76%;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'class' => 'form',
			 //'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'city', array(
            'placeholder' => 'City',
            'required' => false,
            'TABINDEX' => '1',
             'style' =>'width:83.5%;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'class' => 'form',
			 //'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $States = new Application_Model_States();
        $allStates = $States->getStates();
        //$allTimezones = array();
		$this->addElement('Select', 'state', array(
			'class' =>'select', 
			'id' => 'state',
			 'style' =>'background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        ));
		$thisElement = $this->getElement('state');
		$thisElement->setMultiOptions($allStates);
		$thisElement->setValue($index);


        $this->addElement('text', 'zipcode', array(
            'placeholder' => 'ZIP code',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'form',
             'style' =>'width:90px;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your zip code')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'phone', array(
            'placeholder' => 'Phone',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'mobile', array(
            'placeholder' => 'Mobile',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

	   $this->addElement('checkbox', 'enable_communication', array(
            'required' => false,
            'class' => 'form',
            'decorators' => $this->elementDecorators
        ));
        
		$this->addElement('checkbox', 'communication_via_phone', array(
            'required' => false,
            'class' => 'form',
            'data-toggle' => 'toggle',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'communication_via_text', array(
            'required' => false,
            'class' => 'form',
            'data-toggle' => 'toggle',
            'decorators' => $this->elementDecorators
        ));
        
        $this->addElement('checkbox', 'communication_via_email', array(
            'required' => false,
            'class' => 'form',
            'data-toggle' => 'toggle',
            'decorators' => $this->elementDecorators
        ));


        /*$this->addElement('text', 'fname', array(
            'placeholder' => 'First name',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => '')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));*/

		/*$arrgender=Array('m'=>"Male",'f'=>"Female");
       $this->addElement('radio', 'gender',array(
            'placeholder'      => 'Gender',
        	'class' =>'form',
        	'TABINDEX'=>'6',
                'separator'=>'&nbsp;',
			'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        'MultiOptions'=>$arrgender

        ));*/

		$this->addElement('text', 'email', array(
	           'filters' => array('StringTrim', 'StringToLower'),
	           'validators' => array(
	               array('StringLength', false, array(3, 50)),
	           ),
	           'required' => true,
	           'validators' => array(
	               'EmailAddress'
	           )
	           ,
	           'class' => "form",
	           'placeholder' => 'Email',
	           'size' => '50',
	           'autocomplete' => "off",
	           'decorators' => $this->elementDecorators,
	       ));

       	$this->addElement('password', 'password', array(
	                   'placeholder' => 'Password',
	                   'filters' => array('StringTrim'),
	                   'decorators' => $this->elementDecorators,
	                   'placeholder' => "New Password"
	               ))
	               ->getElement('password')
	               ->addValidator('IdenticalField', false, array('confirmPassword', 'confirm password'));

	       // Add an password element
	       $this->addElement('password', 'confirmPassword', array(
	           'placeholder' => 'Repeat password',
	           'filters' => array('StringTrim'),
	           'decorators' => $this->elementDecorators,
	           'placeholder' => "Confirm Password"
	       ));

	       $this->addElement('password', 'oldPassword', array(
	           'placeholder' => 'Old password',
	           'filters' => array('StringTrim'),
	           'decorators' => $this->elementDecorators,
	           'placeholder' => "Old Password"
	       ));
        
        /*$this->addElement('select', 'insurance',array(
            'placeholder'      => 'Insurrance',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'style' => '',
		   	'required'   => true,
        	'decorators' => $this->elementDecorators,
			'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your insurance')))
            ),
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrCompany

        ));*/

		$User = new Application_Model_User();
        $arrMonth = $User->listAllMonths();
        $arrDay = $User->listAllDates();
        $arrYear = $User->listAllYear();
        
        $this->addElement('select', 'month_dob', array(
            'class' => 'inputbox',
            'style'=>'width100px;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'TABINDEX' => '6',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrMonth,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Month of Birth')))
            )
        ));

        $this->addElement('select', 'date_dob', array(
            'class' => 'inputbox',
            'TABINDEX' => '7',
            'style'=>'width100px;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrDay,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Day of Birth')))
            )
        ));
        $this->addElement('select', 'year_dob', array(
            'class' => 'inputbox',
            'TABINDEX' => '8',
            'style'=>'width100px;background: #f6f6f6;border: 1px solid #d6d6d6;padding: 1%;height: 25px;-webkit-border-radius: 4px;-moz-border-radius: 4px; border-radius: 4px;',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrYear,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Year of Birth')))
            )
        ));

        
        $this->addElement('submit', 'submit', array(
            'required' => true,
            'class' => 'save-btn',
            'TABINDEX' => '20',
            'ignore' => true,
            'placeholder' => '',
            'onClick' => 'return passchecker();',
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
