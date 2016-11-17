<?php

/**
 * This is the user form.  It is in its own directory in the application 
 * structure because it represents a "composite asset" in your application.  By 
 * "composite", it is meant that the form encompasses several aspects of the 
 * application: it handles part of the display logic (view), it also handles 
 * validation and filtering (controller and model).  
 *
 * @uses       Zend_Form
 * @package    QuickStart
 * @subpackage Form
 */
class Admin_Form_User extends Zend_Form {

    /**
     * init() is the initialization routine called when Zend_Form objects are 
     * created. In most cases, it make alot of sense to put definitions in this 
     * method, as you can see below.  This is not required, but suggested.  
     * There might exist other application scenarios where one might want to 
     * configure their form objects in a different way, those are best 
     * described in the manual:
     *
     * @see    http://framework.zend.com/manual/en/zend.form.html
     * @return void
     */
    public $elementDecorators = array(
        'ViewHelper',
        'Errors',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array('Label', array('tag' => 'td')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );
    public $fileDecorators = array(
        array('File'),
        array('Errors'),
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

        $this->addElementPrefixPath('Base_Validate', 'Base/Validate/', 'validate');
        $this->setName('frmRegistration');
        $FName = new Zend_Form_Element_Text('firstName');
        $FName->setLabel('First Name')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->clearDecorators()
                ->addDecorators($this->elementDecorators);

        $LName = new Zend_Form_Element_Text('lastName');
        $LName->setLabel('Last Name')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->clearDecorators()
                ->addDecorators($this->elementDecorators);

        $model = new Application_Model_UserLevel();
        $arrUserLevel = $model->getUserLevel();

        $userlevel = new Zend_Form_Element_Select('userLevelId');
        $userlevel->setAttrib('id', 'userLevelId')
                ->setAttrib('style', 'width:100%')
                ->setLabel('User Level')
                ->setRequired(true)
                ->addMultiOptions($arrUserLevel)
                ->clearDecorators()
                ->addDecorators($this->elementDecorators)

        ;

        $submit = new Zend_Form_Element_Submit('submitbutton');
        $submit->setAttrib('id', 'submitbutton')
                ->setLabel('Submit')
                ->clearDecorators()
                ->addDecorators($this->buttonDecorators)
        ;




        $this->addElements(array($FName, $LName, $userlevel, $submit));
    }

    public function loadDefaultDecorators() {
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
    }

}