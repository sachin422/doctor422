<?php

class Admin_Form_Doctoremail extends Zend_Form {

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
 
        $this->addElement('text', 'patient_name', array(
            'label' => 'Patient Names:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '20',
			'maxlength' => '255',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Patient Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'date_of_call', array(
            'label' => 'Date of Call:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
	    'size' => '20',
	   'maxlength' => '255',
           'readonly'=>true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Date of Call')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'phone', array(
            'label' => 'Phone:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '20',
			'maxlength' => '255',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Phone')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		

	

         $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'signup',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'Send Email',
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