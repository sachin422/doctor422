<?php

class Admin_Form_InsurancePlan extends Zend_Form {

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
		$company = new Application_Model_InsuranceCompany();
        $company = $company->fetchAll('1','id ASC');
		$stt=array();
		$stt['']="Select Company";
		foreach($company as $sts)
		{
			$stt[$sts->id]=$sts->company;
		}
		
  		    $this->addElement('select', 'insurance_company_id', array(
            'label' => 'Company Type',
            'style' => 'width:150px;',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $stt,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please choose company type')))
            )
        ));
		
 		$this->addElement('text', 'plan', array(
            'label' => 'Plan:',
            'required' => true,
            'TABINDEX' => '2',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
       

               

        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'signup',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'Save',
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