<?php

class User_Form_Doctor extends Zend_Form {

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
 $category=new Application_Model_Category();
        $arrcategory=$category->getCategories();
         
        $this->addElement('select', 'category_id',array(
            'label'      => 'Category:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			 'style' => 'width:150px;',
		   	'required'   => true,
        	'decorators' => $this->elementDecorators,
			 'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Category')))
            ),
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrcategory
        	
        ));
        $this->addElement('text', 'fname', array(
            'label' => 'Title:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Title')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		 $this->addElement('text', 'company', array(
            'label' => 'Office Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '100',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter office name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		 $this->addElement('file', 'company_logo', array(
            'label' => 'Company Logo [400px/234px]:',
			 'class' => 'form',
            'decorators' => $this->fileDecorators
        ))->getElement('company_logo')->addValidator('Extension', false, 'png,gif,jpeg,png');

		 $this->addElement('file', 'company_icon', array(
            'label' => 'Company Icon [80px/80px]:',
            'decorators' => $this->fileDecorators
        ))->getElement('company_icon')->addValidator('Extension', false, 'png,gif,jpeg,png');

        

       $this->addElement('text', 'assign_phone', array(
            'label' => 'Phone:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '20',
           
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
 $this->addElement('text', 'actual_phone', array(
            'label' => 'Actual Phone:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',
           
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
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter street')))
            ),
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
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter city')))
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
				 $this->addElement('text', 'geocode', array(
            'label' => 'Zoom map (+) Click on your location:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
				  $this->addElement('textarea', 'office_hour', array(
            'label' => 'Office Hours:',
            'cols' => 40,
            'rows' => 10,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
				   $this->addElement('text', 'language', array(
            'label' => 'Language:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		$this->addElement('textarea', 'education', array(
            'label' => 'Education/Training:',
            'cols' => 50,
            'rows' => 10,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('text', 'creditlines', array(
            'label' => 'Creditlines Accepted:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',
			  'maxlength' => '100',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		   $this->addElement('textarea', 'awards', array(
            'label' => 'Awards:',
            'cols' => 25,
            'rows' => 5,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		     $this->addElement('textarea', 'association', array(
            'label' => 'Association:',
            'cols' => 40,
            'rows' => 10,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
			  $this->addElement('checkbox', 'featured', array(
	            'label'      => 'Featured:',
	        	'TABINDEX'=>'20',
	        	'class' =>'checkbox',
	        	'decorators' => $this->elementDecorators,	
	        	'uncheckedValue'=>'',
	        	
	            'filters'    => array('StringTrim'),
	        ));
				 $membership=new Application_Model_Membership();
        $arrMembership=$membership->getMembership();
         
        $this->addElement('select', 'membership_level',array(
            'label'      => 'Membership Level:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			 'style' => 'width:150px;',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrMembership
        	
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
				$this->addElement('text', 'county', array(
            'label' => 'County:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
				$this->addElement('text', 'gallery', array(
            'label' => 'Gallery:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '10',
					 'maxlength' => '10',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
				$this->addElement('text', 'website', array(
            'label' => 'Website:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '30',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
				 $insurancecompany=new Application_Model_InsuranceCompany();
        $arrInsurancecompany=$insurancecompany->getInsurancecompanies();
         
        $this->addElement('Multiselect', 'doctor_insurance[]',array(
            'label'      => 'Select Insurance:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:250px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrInsurancecompany
        	
        ));
		$ReasonForVisit=new Application_Model_ReasonForVisit();
        $arrReasonForVisit=$ReasonForVisit->getReasonForVisit();
         
        $this->addElement('Multiselect', 'doctor_reason_for_visit[]',array(
            'label'      => 'Select Reason to Visit:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:250px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrReasonForVisit
        	
        ));
		$Associations=new Application_Model_Association();
        $arrAssociations=$Associations->getAssociations();
         
        $this->addElement('Multiselect', 'doctor_association[]',array(
            'label'      => 'Select Association:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:300px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrAssociations
        	
        ));
		
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'save',
            'TABINDEX' => '20',
            'ignore' => true,
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