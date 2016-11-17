<?php

class Application_Form_Register extends Zend_Form {

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
        $this->addElementPrefixPath('Base_Validate', 'Base/Validate/', 'validate');



        // Add an first name element
        $this->addElement('text', 'name', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '1',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' =>  $this->lang[379])))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        /*$this->addElement('text', 'username', array(
            'label' => '',
            'autocomplete' => "off",
            'class' => 'inputbox',
            'TABINDEX' => '2',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter username'))),
                array('Db_NoRecordExists', true, array(
                        'table' => 'user',
                        'field' => 'username',
                        'messages' => 'username already exists'
                ))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));*/


        $this->addElement('text', 'email', array(
            'label' => '',
            'TABINDEX' => '3',
            'required' => true,
            'class' => 'inputbox',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress'
            )
        ));


        // Add an last name element
        $this->addElement('text', 'phone', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '4',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' =>  $this->lang[382])))
            ),
            'filters' => array('StringTrim'),
        ));
        // Add an last name element
        $this->addElement('text', 'zipcode', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '5',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' =>  $this->lang[380])))
            ),
            'filters' => array('StringTrim'),
        ));


        $category = new Application_Model_Category();
        $arrCountry = $category->getCategories("status='1'", 'Select Specialty');

        $this->addElement('select', 'category', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '6',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' =>  $this->lang[398])))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrCountry,
            
        ));

        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'submit',
            'TABINDEX' => '9',
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