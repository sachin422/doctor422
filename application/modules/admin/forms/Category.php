<?php

class Admin_Form_Category extends Zend_Form {

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
            'label' => 'Category Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Category Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));


        $this->addElement('textarea', 'description', array(
            'label' => 'Description:',
            'cols' => 40,
            'rows' => 3,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
 		$this->addElement('text', 'metatitle', array(
            'label' => 'Meta Title:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
        $this->addElement('textarea', 'metadescription', array(
            'label' => 'Meta Description:',
            'cols' => 40,
            'rows' => 3,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
        $this->addElement('textarea', 'metakeywords', array(
            'label' => 'Meta Keywords:',
            'cols' => 40,
            'rows' => 3,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('file', 'icon', array(
		    'label' => 'Icon:',
		    'class' => 'form',
		    'decorators' => $this->fileDecorators
		))->getElement('icon')->addValidator('Extension', false, 'png,gif,jpeg,png');


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