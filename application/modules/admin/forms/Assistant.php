<?php

class Admin_Form_Assistant extends Zend_Form {

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
        
		$this->addElement('text', 'name', array(
            'label' => 'Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
        $this->addElement('text', 'telephone', array(
            'label' => 'Phone:',
            'required' => true,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Telephone')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
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
		
		$this->addElement('text', 'address', array(
            'label' => 'Address:',
            'required' => false,
            'TABINDEX' => '3',
            'class' => 'txt-box',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
 
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