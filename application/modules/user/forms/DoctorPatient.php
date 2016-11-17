<?php

class User_Form_DoctorPatient extends Zend_Form {

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

	public function init() {
		$this->setMethod('post');

		$arrHours = array();
        for($i=0; $i<=12; $i++) {
        	$arrHours[$i] = $i;
        }
        $this->addElement('select', 'hour', array(
            'label' => '',
            'class' => '',
            'TABINDEX' => '8',
            'style'=>'',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrHours,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Hour')))
            )
        ));
        $arrMin = array(
        	"00"=>"00",
        	"05"=>"05", 
        	"10"=>"10", 
        	"15"=>"15", 
        	"20"=>"20", 
        	"25"=>"25", 
        	"30"=>"30", 
        	"35"=>"35", 
        	"40"=>"40", 
        	"45"=>"45",
        	"50"=>"50",
        	"55"=>"55");
        $this->addElement('select', 'minutes', array(
            'label' => '',
            'class' => '',
            'TABINDEX' => '8',
            'style'=>'',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrMin,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Minutes')))
            )
        ));
        $arrAm = array("am", "pm");
        $this->addElement('select', 'am', array(
            'label' => '',
            'class' => '',
            'TABINDEX' => '8',
            'style'=>'',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrAm,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must select Am or Pm')))
            )
        ));

        $this->addElement('text', 'time', array(
            'label' => 'Time:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'fulldate', array(
            'label' => 'Date:',
            'required' => true,
            'TABINDEX' => '1',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'name', array(
            'label' => 'Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in the name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'lastname', array(
            'label' => 'Lastname:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in the lastname')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

         $this->addElement('text', 'phone', array(
            'label' => 'Telephone:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
        
        $this->addElement('text', 'mobile', array(
            'label' => 'Mobile:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'label' => 'Email:',
            'required' => false,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
 

        $this->addElement('text', 'procedure', array(
            'label' => 'Procedure:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'street', array(
            'label' => 'Street',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$this->addElement('text', 'city', array(
            'label' => 'City',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
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
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        ));
		$thisElement = $this->getElement('state');
		$thisElement->setMultiOptions($allStates);
		$thisElement->setValue($index);


        $this->addElement('text', 'zipcode', array(
            'label' => 'ZIP code',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'form',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your zip code')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
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
