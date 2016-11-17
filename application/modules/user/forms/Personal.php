<?php

class User_Form_Personal extends Zend_Form {

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
 
		$this->addElement('text', 'fname', array(
            'label' => 'Name:',
            'required' => true,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'Please fill in your title')))
            ),
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('text', 'specialty', array(
            'label' => 'Specialization:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

		$category = new Application_Model_Category();
        $arrcategory = $category->getCategories();
        
        $this->addElement('Multiselect', 'category_id', array(
                    'label' => 'Select Category:',
                    'class' => 'select',
                    'TABINDEX' => '6',
                    'multiple' => 'true',
                    'style' => 'width:300px;',
                    'size' => '10',
                    'required' => true,
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
                ));

        $this->addElement('Multiselect', 'category_id2', array(
                    'label' => 'Select Category:',
                    'class' => 'select',
                    'TABINDEX' => '6',
                    'multiple' => 'true',
                    'style' => 'width:300px;',
                    'size' => '10',
                    'required' => false,
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
                    'MultiOptions' => $arrcategory
                ));
		
		
		
		$this->addElement('text', 'specialty_title', array(
            'label' => 'Specialty title (e.g. PhD, Dr):',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
			 'size' => '50',
			'maxlength' => '255',
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
		
		$this->addElement('textarea', 'about', array(
            'label' => 'About:',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
	   'cols' => '71',
	    'rows' => '8',
           'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
         $this->addElement('textarea', 'education', array(
            'label' => 'Education/Training:',
            'cols' => 71,
            'rows' => 8,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));

        //$Associations=new Application_Model_Association();
        //$arrAssociations=$Associations->getAssociations();

        /*$this->addElement('Multiselect', 'doctor_association',array(
            'label'      => 'Select Association:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:300px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),


        ));*/

         $this->addElement('Multiselect', 'doctor_association',array(
            'label'      => 'Select Reason to Visit:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:250px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),


        ));


        $this->addElement('Multiselect', 'doctor_association2',array(
            'label'      => 'Select Association:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			 'style' => 'width:300px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim')


        ));


        $Affiliation = new Application_Model_HospitalAffiliation();

        $arrAffiliation = array();
        $arrAffiliationstates = $Affiliation->GetAllStates();

        $this->addElement('select', 'state_for_affiliate', array(
            'label' => 'State:',
            'class' => 'select',
            'onchange' => 'getaffiliation(this.value)',
            'TABINDEX' => '6',
            'value' => 'AL',
            'style' => 'width:150px;',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'validators' => array(
                array('NotEmpty', true, array('messages' => array('isEmpty' => 'You have to choose a state')))
            ),
            'filters' => array('StringTrim'),
            'MultiOptions' => $arrAffiliationstates
        ));


        $award_element = $this->addElement('Multiselect', 'doctor_affiliation', array(
                    'label' => 'Select Affiliation:',
                    'class' => 'select',
                    'TABINDEX' => '6',
                    'multiple' => 'true',
                    'style' => 'width:250px;',
                    'size' => '10',
                    'required' => false,
                    'decorators' => $this->elementDecorators,
                    'filters' => array('StringTrim'),
                    'MultiOptions' => $arrAffiliation
                        )
        );
		$award_element =  $this->addElement('Multiselect', 'doctor_affiliation2',array(
            'label' => 'Select Affiliation:',
        	'class' =>'select',
        	'TABINDEX'=>'6',
			'multiple'=>'true',
			'style' => 'width:300px;',
			'size' => '10',
		   	'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
			'MultiOptions'=>$arrAffiliation
			)
		);
		
		$this->addElement('textarea', 'text_award', array(
            'label' => 'Text Award:',
            'cols' => 71,
            'rows' => 8,
            'class' => 'form',
            'TABINDEX' => '2',
            'required' => false,
            'decorators' => $this->elementDecorators,
            'filters' => array('StringTrim'),
        ));
        /* $this->addElement('file', 'company_logo', array(
            'label' => 'Company Logo [400px/234px]:',
			 'class' => 'form',
            'decorators' => $this->fileDecorators
        ))->getElement('company_logo')->addValidator('Extension', false, 'png,gif,jpeg,png');

	*/




        

		
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'class' => 'myButton',
            'TABINDEX' => '20',
            'ignore' => true,
            'onclick'=>"selectAll(document.getElementById('doctor_association'));selectAll(document.getElementById('doctor_affiliation'));selectAll(document.getElementById('category_id'));",
            'label' => 'save',
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