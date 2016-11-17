<?php

class Application_Form_Patientregistration extends Zend_Form {

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


        $this->addElement('text', 'first_name', array(
            'label' => 'First Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'preg-txt',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => $this->view->lang[383])))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

         $this->addElement('text', 'last_name', array(
          'label' => 'Last Name:',
          'required' => true,
          'TABINDEX' => '2',
          'class' => 'preg-txt',
          'validators' => array(
          array('NotEmpty', true, array('messages' => array('isEmpty' => $this->view->$lang[386])))
          ),
          'decorators' => $this->elementDecorators,
          'filters' => array('StringTrim'),
          )); 
         
        
           $this->addElement('text', 'phone_no', array(

            'label' => 'Phone  No:',

            'required' => true,

            'TABINDEX' => '1',

            'class' => 'preg-txt',

            'validators' => array(

                array('NotEmpty', true, array('messages' => array('isEmpty' => $this->view->lang[383])))

            ),

            'decorators' => $this->elementDecorators,

            'filters' => array('StringTrim'),

        ));

         $this->addElement('text', 'imp_notes', array(

            'label' => 'Important  Notes:',

            'required' => true,

            'TABINDEX' => '1',

            'class' => 'preg-txt',

            'validators' => array(

                array('NotEmpty', true, array('messages' => array('isEmpty' => $this->view->lang[383])))

            ),

            'decorators' => $this->elementDecorators,

            'filters' => array('StringTrim'),

        ));

        $this->addElement('hidden', 'patient_id', array(

            'label' => '',

            'required' => true,

            'TABINDEX' => '10',

            'class' => 'preg-txt',

            'decorators' => $this->elementDecorators,

            'filters' => array('StringTrim'),

        ));

         $this->addElement('hidden', 'doctor_id', array(

            'label' => '',

            'required' => true,

            'TABINDEX' => '10',

            'class' => 'preg-txt',

            'decorators' => $this->elementDecorators,

            'filters' => array('StringTrim'),

            'validators' => array(

                array('validator' => 'StringLength', 'options' => array(6, 20))

            )

        ));

        $this->addElement('text', 'code', array(
            'label' => 'Code:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => '',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('text', 'email', array(
            'label' => 'Email:',
            'required' => true,
            'TABINDEX' => '8',
            'class' => 'preg-txt',
            'validators' => array(
                'EmailAddress',
                array('Db_NoRecordExists', true, array(
                        'table' => 'user',
                        'field' => 'email',
                        'messages' => $this->view->$lang[391]
                ))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        $this->addElement('password', 'password', array(
                    'label' => '',
                    'autocomplete' => "off",
                    'required' => true,
                    'TABINDEX' => '9',
                    'class' => 'preg-txt',
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
                    'validators' => array(
                        array('NotEmpty', true, array('messages' => array('isEmpty' => $this->view->$lang[392]))),
                        array('validator' => 'StringLength', 'options' => array(6, 20))
                    ),
                ))
                ->getElement('password')
                ->addValidator('IdenticalField', false, array('confirmPassword', 'Confirm Password'));
        ;



        // Add an password element
        $this->addElement('password', 'confirmPassword', array(
            'label' => '',
            'required' => true,
            'TABINDEX' => '10',
            'class' => 'preg-txt',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(6, 20))
            )
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

?>
