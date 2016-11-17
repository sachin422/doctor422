<?php

class User_Form_MasterSlot extends Zend_Form {

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
       // $this->addElementPrefixPath('Base_Validate', 'Base/Validate/', 'validate');

       $times = array(
            "00:00"=>"12:00 am", "00:30"=>"12:30 am",
            "01:00"=>"1:00 am", "01:30"=>"01:30 am",
            "02:00"=>"2:00 am", "02:30"=>"2:30 am",
            "03:00"=>"3:00 am", "03:30"=>"3:30 am",
            "04:00"=>"4:00 am", "04:30"=>"4:30 am",
            "05:00"=>"5:00 am", "05:30"=>"5:30 am",
            "06:00"=>"6:00 am", "06:30"=>"6:30 am",
            "07:00"=>"7:00 am", "07:30"=>"7:30 am",
            "08:00"=>"8:00 am", "08:30"=>"8:30 am",
            "09:00"=>"9:00 am", "09:30"=>"9:30 am",
            "10:00"=>"10:00 am", "10:30"=>"10:30 am",
            "11:00"=>"11:00 am", "11:30"=>"11:30 am",
            "12:00"=>"2:00 pm", "12:30"=>"12:30 pm",
            "13:00"=>"1:00 pm", "13:30"=>"1:30 pm",
            "14:00"=>"2:00 pm", "14:30"=>"2:30 pm",
            "15:00"=>"3:00 pm", "15:30"=>"3:30 pm",
            "16:00"=>"4:00 pm", "16:30"=>"4:30 pm",
            "17:00"=>"5:00 pm", "17:30"=>"5:30 pm",
            "18:00"=>"6:00 pm", "18:30"=>"6:30 pm",
            "19:00"=>"7:00 pm", "19:30"=>"7:30 pm",
            "20:00"=>"8:00 pm", "20:30"=>"8:30 pm",
            "21:00"=>"9:00 pm", "21:30"=>"9:30 pm",
            "22:00"=>"10:00 pm", "22:30"=>"10:30 pm",
            "23:00"=>"11:00 pm", "23:30"=>"11:30 pm"
        );
        $stime = "09:00";
        $etime = "17:00";
        $interval = "30";
       $timeIntervals = array('10'=>'10 Minutes',
                              '15'=>'15 Minutes',
                              '30'=>'30 Minutes',
                              '45'=>'45 Minutes',
                              '60'=>'60 Minutes');

       $this->addElement('hidden', 'id1', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
       
        $this->addElement('checkbox', 'ischecked1', array(
            'label' => '',
            'class' =>'working_day',
            'required' => false,
            'TABINDEX' => '1',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime1',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'1',
            'TABINDEX'=>'2',
            'style'=>'width:75px;',
            'value'=>$stime,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));
        
        $this->addElement('select', 'etime1',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'1',
			'style'=>'width:75px;',
            'TABINDEX'=>'3',
            'value'=>$etime,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time1',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'1',
            'TABINDEX'=>'4',
            'value'=>$interval,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        
        /*$this->addElement('textarea', 'display_slots1', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        
// 2
        $this->addElement('hidden', 'id2', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));

        $this->addElement('checkbox', 'ischecked2', array(
            'label' => '',            
            'class' =>'working_day',
            'required' => false,
            'TABINDEX' => '5',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime2',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'2',
            'TABINDEX'=>'6',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime2',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'2',
            'TABINDEX'=>'7',
			'style'=>'width:75px;',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time2',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'2',
            'TABINDEX'=>'8',
			'style'=>'width:90px;',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots2', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        // 3
        $this->addElement('hidden', 'id3', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked3', array(
            'label' => '',
            'required' => false,
            'class' =>'working_day',
            'TABINDEX' => '9',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime3',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'3',
            'TABINDEX'=>'10',
			'style'=>'width:75px;',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime3',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'3',
            'TABINDEX'=>'11',
			'style'=>'width:75px;',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time3',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'3',
			'style'=>'width:90px;',
            'TABINDEX'=>'12',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots3', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //4
        $this->addElement('hidden', 'id4', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked4', array(
            'label' => '',
            'required' => false,
            'class' =>'working_day',
            'TABINDEX' => '13',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime4',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'4',
            'TABINDEX'=>'14',
			'style'=>'width:75px;',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime4',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'4',
            'TABINDEX'=>'15',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time4',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'4',
			'style'=>'width:90px;',
            'TABINDEX'=>'16',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots4', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        // 5
        $this->addElement('hidden', 'id5', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked5', array(
            'label' => '',
            'required' => false,
            'class' =>'working_day',
            'TABINDEX' => '17',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime5',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'5',
			'style'=>'width:75px;',
            'TABINDEX'=>'18',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime5',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'5',
			'style'=>'width:75px;',
            'TABINDEX'=>'19',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time5',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'5',
			'style'=>'width:90px;',
            'TABINDEX'=>'20',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots5', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //6
        $this->addElement('hidden', 'id6', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked6', array(
            'label' => '',
            'required' => false,
            'class' =>'working_day',
            'TABINDEX' => '21',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime6',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'6',
            'TABINDEX'=>'22',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime6',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'6',
            'TABINDEX'=>'23',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time6',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'6',
            'TABINDEX'=>'24',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots6', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //7

        $this->addElement('hidden', 'id7', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked7', array(
            'label' => '',
            'required' => false,
            'class' =>'working_day',
            'TABINDEX' => '25',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime7',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'7',
			'style'=>'width:75px;',
            'TABINDEX'=>'26',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime7',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'7',
            'TABINDEX'=>'27',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time7',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'7',
            'TABINDEX'=>'28',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));

       /*$this->addElement('textarea', 'display_slots7', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/



        // Timeslot 2
        // 1
        $this->addElement('hidden', 'id8', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked8', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '29',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime8',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'8',
            'TABINDEX'=>'30',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime8',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'8',
            'TABINDEX'=>'31',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time8',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'8',
            'TABINDEX'=>'32',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots8', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //2
        $this->addElement('hidden', 'id9', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked9', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '33',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime9',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'9',
            'TABINDEX'=>'34',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime9',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'9',
            'TABINDEX'=>'35',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time9',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'9',
            'TABINDEX'=>'36',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots9', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //3
        $this->addElement('hidden', 'id10', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked10', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '37',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime10',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'10',
            'TABINDEX'=>'38',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime10',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'10',
            'TABINDEX'=>'39',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time10',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'10',
            'TABINDEX'=>'40',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots10', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //4
        $this->addElement('hidden', 'id11', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked11', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '41',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime11',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'11',
            'TABINDEX'=>'42',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime11',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'11',
            'TABINDEX'=>'43',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time11',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'11',
            'TABINDEX'=>'44',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots11', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //5
        $this->addElement('hidden', 'id12', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked12', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '45',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime12',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'12',
            'TABINDEX'=>'46',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime12',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'12',
            'TABINDEX'=>'47',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time12',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'12',
            'TABINDEX'=>'48',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots12', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //6
        $this->addElement('hidden', 'id13', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked13', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '49',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime13',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'13',
            'TABINDEX'=>'50',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime13',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'13',
            'TABINDEX'=>'51',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time13',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'13',
            'TABINDEX'=>'52',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));
        /*$this->addElement('textarea', 'display_slots13', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/
        //7
        $this->addElement('hidden', 'id14', array(
            'label' => '',
            'required' => false,
            'filters' => array('StringTrim')
        ));
        $this->addElement('checkbox', 'ischecked14', array(
            'label' => '',
            'required' => false,
            'TABINDEX' => '53',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime14',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'14',
            'TABINDEX'=>'54',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime14',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:75px;',
            'rel' =>'14',
            'value'=>$etime,
            'TABINDEX'=>'55',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time14',array(
            'label'      => '',
            'class' =>'select change_slots',
			'style'=>'width:90px;',
            'rel' =>'14',
            'TABINDEX'=>'56',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$timeIntervals
        ));


        /*$this->addElement('textarea', 'display_slots14', array(
            'label' => '',
            'rows' => '10',
            'cols' => '8',
            'required' => false,
            'filters' => array('StringTrim')
        ));*/


		$arrnotifications = array('1'=>"Email and Text message", '2'=>"Email");
		$this->addElement('radio', 'notificationby',array(
            'class' =>'form',
        	'TABINDEX'=>'6',
            'separator'=>'&nbsp;',
			'required'   => false,
        	'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
        	'MultiOptions'=>$arrnotifications
        ));
        
       /* $daybeforeArray = array('0'=>"00 Day", '1'=>"01 Day");
        $this->addElement('select', 'daybefore', array(
            'label'      => '',
            'class' =>'small',
            'rel' =>'14',
            'TABINDEX'=>'56',
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$daybeforeArray
        ));

        $hoursbeforeArray = array('0'=>"00 Hours", '1'=>"01 Hours", '2'=>"02 Hours", '3'=>"03 Hours", '4'=>"04 Hours");
        $this->addElement('select', 'hoursbefore', array(
            'label'      => '',
            'class' =>'small',
            'rel' =>'14',
            'TABINDEX'=>'56',
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$hoursbeforeArray
        ));

        $minutesbeforeArray = array('1'=>"00 Minutes", '15'=>"15 Minutes", '30'=>"30 Minutes", '45'=>"45 Minutes");
        $this->addElement('select', 'minutesbefore', array(
            'label'      => '',
            'class' =>'small',
            'rel' =>'14',
            'TABINDEX'=>'56',
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$minutesbeforeArray
        ));
*/
        
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
