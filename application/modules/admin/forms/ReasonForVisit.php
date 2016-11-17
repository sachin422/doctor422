<?php

class Admin_Form_ReasonForVisit extends Zend_Form {

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

        $this->addElement('text', 'reason', array(
            'label' => 'Reason:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Reason')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $Category = new Application_Model_Category();
        $categories = $Category->getCategories("status=1");
        $this->addElement('select', 'categoryId', array(
            'label' => 'Category:',
            'multiOptions' => $categories,
            'style' => 'width:150px;',
            'TABINDEX' => '2',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));


        

        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'signup',
            'TABINDEX' => '3',
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