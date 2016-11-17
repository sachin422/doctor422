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
class Application_Form_Appointment extends Zend_Form {

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
    public $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
        array(array('label' => 'HtmlTag'), array('tag' => 'td', 'placement' => 'prepend')),
        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    );

    public function init() {


        $this->addElement('text', 'name', array(
            'label' => 'Name',
            'class' => 'inputbox',
            'TABINDEX' => '1',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
        $this->addElement('text', 'lastname', array(
            'label' => 'Last Name',
            'class' => 'inputbox',
            'TABINDEX' => '1',
            'required' => true,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Last Name')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'label' => 'Your email address',
            'TABINDEX' => '2',
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
            'label' => 'Phone',
            'class' => 'inputbox',
            'TABINDEX' => '3',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Phone')))
            ),
            'filters' => array('StringTrim'),
        ));



        // Add an last name element
        $this->addElement('text', 'zipcode', array(
            'label' => 'Zipcode',
            'class' => 'inputbox',
            'TABINDEX' => '4',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Zipcode')))
            ),
            'filters' => array('StringTrim'),
        ));


        // Add an last name element
        $this->addElement('text', 'age', array(
            'label' => 'Age',
            'class' => 'inputbox',
            'TABINDEX' => '5',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You must enter Age')))
            ),
            'filters' => array('StringTrim'),
        ));
        $gender = array('m' => 'Male', 'f' => 'Female');
        $this->addElement('radio', 'gender', array(
            'label' => '',
            'class' => 'inputbox',
            'separator' => "&nbsp;&nbsp;",
            'TABINDEX' => '6',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $gender
        ));


        $gender = array('n' => 'New', 'e' => 'Existing');
        $this->addElement('radio', 'gender', array(
            'label' => '',
            'separator' => "&nbsp;&nbsp;",
            'class' => 'inputbox',
            'TABINDEX' => '6',
            'required' => true,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $gender
        ));

        $Reason = new Application_Model_ReasonForVisit();
        $arrayReason = $Reason->getReasonForVisit("status='1'", array('0' => 'Other'));

        $this->addElement('select', 'reason_for_visit', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '6',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrayReason
        ));

        // Add an last name element
        $this->addElement('textarea', 'needs', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '5',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $Company = new Application_Model_InsuranceCompany();
        $arrayCompany = $Reason->get("status='1'");

        $this->addElement('select', 'insurance', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '6',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrayCompany
        ));


        $this->addElement('select', 'plan', array(
            'label' => '',
            'class' => 'inputbox',
            'TABINDEX' => '6',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'MultiOptions' => array('' => 'Select My Insurance Plan')
        ));

        $this->addElement('submit', 'cancel', array(
            'required' => false,
            'class' => 'button',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'Cancel',
            'decorators' => $this->buttonDecorators,
        ));
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'button',
            'TABINDEX' => '20',
            'ignore' => true,
            'label' => 'Register',
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