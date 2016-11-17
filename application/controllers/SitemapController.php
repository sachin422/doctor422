<?php

class SitemapController extends Base_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();
        set_time_limit(0);
        $this->_helper->layout->setLayout('frontend');
    }

    public function doctorAction() {

        $type = $this->_getParam('type');

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Doctor();
        $title = 'Doctor';
        $where = "status=1 AND fname!=''";
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);

        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('doctors'),
                                array('id', 'fname', 'user_id', 'category_id', 'status'))
                        ->where("$where")
                        ->order("fname ASC");
                        //->order("membership_level_no ASC");

        $pageObj = new Base_Paginator();
        //$paginator = $pageObj->fetchPageData($model, $page, 500, $where, "id ASC");
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);
        $paginator->setPageRange(30);
        //prexit($paginator);
        $this->view->title = $title;
        $this->view->total = $paginator->getCurrentItemCount();


        //$this->view->total = $pageObj->getTotalCount();

        $this->view->paginator = $paginator;
    }

    public function specialtyAction() {

        $Category = new Application_Model_Category();
        $object = $Category->fetchAll('status=1', 'name');
        $this->view->object = $object;
    }

    public function cityAction() {

        $Doctor = new Application_Model_Doctor();
        $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('doctors'),
                                array('city'))
                        ->group ("city")
                        ->order ("city ASC");

        $pageObj = new Base_Paginator();
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);

        $this->view->total = $paginator->getCurrentItemCount();
        $this->view->paginator = $paginator;
    }
	/*sitemap of all the insurances Svelon*/
	public function insuranceCompanyAction() {

        $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();

        $db = Zend_Registry::get('db');
		$select = $db->select()
				->from(array('i'=>'insurance_companies'), array('id','company'))
                ->order ("i.company ASC");

        $pageObj = new Base_Paginator();
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);
        $this->view->total = $paginator->getCurrentItemCount();
        $this->view->paginator = $paginator;

    }
	
	/*sitemap of all the insurances Svelon*/
	public function zipListAction() {

        $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();

        $db = Zend_Registry::get('db');
		$select = $db->select()
				->from(array('i'=>'doctors'), array('zipcode'))
                ->order ("i.zipcode ASC");

        $pageObj = new Base_Paginator();
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);
        $this->view->total = $paginator->getCurrentItemCount();
        $this->view->paginator = $paginator;

    }
	

    public function reasonForVisitAction() {


         $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();

        $db = Zend_Registry::get('db');
        $select = $db->select()
                        ->from(array('v'=>'reason_for_visit'),
                                array('id','reason'))
                ->join(array('c' => 'categories'),
                        'v.category_id=c.id',
                        array('id AS category_id', 'name AS category_name'))
                ->where("v.status=1 AND c.status=1")
                ->order ("v.reason ASC");


        $pageObj = new Base_Paginator();
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);
        $this->view->total = $paginator->getCurrentItemCount();
        $this->view->paginator = $paginator;
    }

    public function doctor1Action() {



        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('doctors'),
                                array('id', 'fname', 'user_id', 'category_id', 'status'))
                        ->where("status=1");

        $pageObj = new Base_Paginator();
        //$paginator = $pageObj->fetchPageData($model, $page, 500, $where, "id ASC");
        $paginator = $pageObj->DbSelectPaginator($select, 0, 690700);
//        echo '<pre>';
//        foreach ($paginator as $pg){
//            print_r($pg);
//        }exit;
        //echo "<pre>";print_r($paginator);exit;
        $this->view->total = $paginator->getCurrentItemCount();
        //$this->view->total = $pageObj->getTotalCount();

        $this->view->paginator = $paginator;
    }

}