<?php

class Admin_Form_MasterSlot extends Zend_Form {

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
            "00:00"=>"00:00", "00:30"=>"00:30",
            "01:00"=>"01:00", "01:30"=>"01:30",
            "02:00"=>"02:00", "02:30"=>"02:30",
            "03:00"=>"03:00", "03:30"=>"03:30",
            "04:00"=>"04:00", "04:30"=>"04:30",
            "05:00"=>"05:00", "05:30"=>"05:30",
            "06:00"=>"06:00", "06:30"=>"06:30",
            "07:00"=>"07:00", "07:30"=>"07:30",
            "08:00"=>"08:00", "08:30"=>"08:30",
            "09:00"=>"09:00", "09:30"=>"09:30",
            "10:00"=>"10:00", "10:30"=>"10:30",
            "11:00"=>"11:00", "11:30"=>"11:30",
            "12:00"=>"12:00", "12:30"=>"12:30",
            "13:00"=>"13:00", "13:30"=>"13:30",
            "14:00"=>"14:00", "14:30"=>"14:30",
            "15:00"=>"15:00", "15:30"=>"15:30",
            "16:00"=>"16:00", "16:30"=>"16:30",
            "17:00"=>"17:00", "17:30"=>"17:30",
            "18:00"=>"18:00", "18:30"=>"18:30",
            "19:00"=>"19:00", "19:30"=>"19:30",
            "20:00"=>"20:00", "20:30"=>"20:30",
            "21:00"=>"21:00", "21:30"=>"21:30",
            "22:00"=>"22:00", "22:30"=>"22:30",
            "23:00"=>"23:00", "23:30"=>"23:30"
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
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));
        
        $this->addElement('select', 'etime1',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'1',
            'TABINDEX'=>'3',
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time1',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'1',
            'TABINDEX'=>'4',
            'value'=>$interval,
            'decorators' => $this->elementDecorators,
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
            'required' => false,
            'TABINDEX' => '5',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime2',array(
            'label'      => '',
            'class' =>'select change_slots',
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
            'value'=>$etime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'time3',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'3',
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
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime4',array(
            'label'      => '',
            'class' =>'select change_slots',
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
            'TABINDEX' => '17',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime5',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'5',
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
            'TABINDEX' => '21',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime6',array(
            'label'      => '',
            'class' =>'select change_slots',
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
            'TABINDEX' => '25',
            'class' => 'form',
            'uncheckedValue'=>'',
            'filters' => array('StringTrim')
        ));


        $this->addElement('select', 'stime7',array(
            'label'      => '',
            'class' =>'select change_slots',
            'rel' =>'7',
            'TABINDEX'=>'26',
            'value'=>$stime,
            'decorators' => $this->elementDecorators,
            'filters'    => array('StringTrim'),
            'MultiOptions'=>$times
        ));

        $this->addElement('select', 'etime7',array(
            'label'      => '',
            'class' =>'select change_slots',
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