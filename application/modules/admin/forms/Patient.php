<?php

class Admin_Form_Patient extends Zend_Form {

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
         //$this->addElementPrefixPath('Base_Validate', 'Base/Validate/', 'validate');
        $InsuranceCompany = new Application_Model_InsuranceCompany();
        $arrCompany = array();
        $arrCompany = $InsuranceCompany->getInsurancecompanies("status='1'");

        $this->addElement('text', 'name', array(
            'label' => 'First Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('text', 'last_name', array(
            'label' => 'Surname:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Surname')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'zipcode', array(
            'label' => 'Zipcode:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'form',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Zipcode')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        /*$this->addElement('text', 'age', array(
            'label' => 'Age:',
            'required' => true,
            'TABINDEX' => '4',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Age')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));*/

         $User = new Application_Model_User();
        $arrMonth = $User->listAllMonths();
        $arrDay = $User->listAllDates();
        $arrYear = $User->listAllYear();
        
        

        $this->addElement('select', 'month_dob', array(
            'label' => '',
            'class' => 'inputbox',
            'style'=>'width:100px',
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
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '7',
            'style'=>'width:100px',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrDay,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Day of Birth')))
            )
        ));
        $this->addElement('select', 'year_dob', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '8',
            'style'=>'width:100px',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrYear,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Year of Birth')))
            )
        ));

        $this->addElement('text', 'phone', array(
            'label' => 'Phone:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Date of Birth')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
$arrgender=Array('m'=>"Male",'f'=>"Female");
       $this->addElement('radio', 'gender',array(
            'label'      => 'Gender:',
        	'class' =>'form',
        	'TABINDEX'=>'6',
                'separator'=>'&nbsp;',
			'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        'MultiOptions'=>$arrgender

        ));

 $this->addElement('text', 'email', array(
            'label' => 'Email:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Email')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
 
$this->addElement('password', 'password', array(
                    'label' => '',
                    'autocomplete' => "off",
                    'required' => false,
                    'TABINDEX' => '7',
                    'class' => 'inputbox',
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim')
                ));



        // Add an password element
        $this->addElement('password', 'confirmPassword', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '8',
            'class' => 'inputbox',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            
        ));
        
       /* $this->addElement('select', 'insurance',array(
            'label'      => 'Insurance Company Name:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			 'style' => 'width:150px;',
		   	'required'   => true,
        	'decorators' => $this->elementDecorators,
			 'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Insurance Company')))
            ),
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrCompany

        ));
		*/

        $this->addElement('submit', 'submit', array(
            'required' => true,
            'class' => 'save-btn',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'Save',
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