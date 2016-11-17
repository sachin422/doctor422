<?php

class XmlsitemapController extends Base_Controller_Action {

    private $_dom;
    private $_main;

    public function preDispatch() {
      
        parent::preDispatch();
        set_time_limit(0);
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $this->_helper->layout->setLayout('frontend');
    }

    private function initXmlDom(){
       
        $this->_dom = new DOMDocument("1.0","UTF-8");
        header("Content-Type: text/plain");
        $this->_main = $this->_dom->createElement("urlset");
        $this->_dom->appendChild($this->_main);

        $xmlns_xsi = $this->_dom->createAttribute("xmlns:xsi");
        $this->_main->appendChild($xmlns_xsi);
        $xmlns_xsiValue = $this->_dom->createTextNode("http://www.w3.org/2001/XMLSchema-instance");
        $xmlns_xsi->appendChild($xmlns_xsiValue);

        $xsi = $this->_dom->createAttribute("xsi:schemaLocation");
        $this->_main->appendChild($xsi);
        $xsiValue = $this->_dom->createTextNode("http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd");
        $xsi->appendChild($xsiValue);

        $xmlns = $this->_dom->createAttribute("xmlns");
        $this->_main->appendChild($xmlns);
        $xmlnsValue = $this->_dom->createTextNode("http://www.sitemaps.org/schemas/sitemap/0.9");
        $xmlns->appendChild($xmlnsValue);

    }


    public function doctor1Action() {

        $sitemap_file = "sitemap-doctor1.xml";
        $names = array();
        $names[] = $sitemap_file;
        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $count = 1;
        $page = 0;
        $this->doctor1Recursive(&$count, &$page);
    }

    public function doctor1Recursive($offest, $page) {

        $where = "status=1 AND state='CA' AND fname!='' AND membership_level IN ('Bronze')";
        $SeoUrl = new Application_Model_SeoUrl();
        $Doctor = new Application_Model_Doctor();
        $object = $Doctor->fetchAll($where, "fname ASC", 5000, ($offest-1));
       $sitemap_file = "sitemap-doctor1.xml";
        if($object){
//            $i = 1;
//            $j = 1;
            foreach($object as $doc){
                $offest++;
                $seo_url = $SeoUrl->fetchRow("actual_url='/profile/index/id/{$doc->getId()}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/profile/index/id/{$doc->getId()}");
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);

                if($offest%10000==0){
                    $this->_dom->save($sitemap_file);
                    $sitemap_file = "sitemap-doctor1-{$page}.xml";
                    $names[] = $sitemap_file;
                    $page++;
                    $this->initXmlDom();

                }
//                $i++;
            }
        }
        $object = $Doctor->fetchAll($where, "fname ASC", 5000, ($offest-1));
        if(count($object)>0){
            
            $this->doctor1Recursive($offest, $page);
        }else{
            $this->_dom->save($sitemap_file);
            prexit($names);
            break;
        }
        
    }

    public function doctor2Action() {

        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $where = "status=100 AND membership_level IN ('silvy', 'bronzy')";
        $SeoUrl = new Application_Model_SeoUrl();
        $Doctor = new Application_Model_Doctor();
        $object = $Doctor->fetchAll($where, "fname ASC");
        if($object){
            foreach($object as $doc){
                $seo_url = $SeoUrl->fetchRow("actual_url='/profile/index/id/{$doc->getId()}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/profile/index/id/{$doc->getId()}");
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
            }
        }



                        $sitemap_file = "";
                     
                        $this->_dom->save($sitemap_file);

        die('sitemap-doctor2.xml');
    }
    public function doctor3Action() {

        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $where = "status=1000 AND fname!='' AND membership_level IN ('Platinumm', 'Golde')";
        $SeoUrl = new Application_Model_SeoUrl();
        $Doctor = new Application_Model_Doctor();
        $object = $Doctor->fetchAll($where, "fname ASC");
        if($object){
            foreach($object as $doc){
                $seo_url = $SeoUrl->fetchRow("actual_url='/profile/index/id/{$doc->getId()}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/profile/index/id/{$doc->getId()}");
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
            }
        }



                        $sitemap_file = "";
                        $sitemap_file = "sitemap-doctor3.xml";
                        $this->_dom->save($sitemap_file);

        die('sitemap-doctor3.xml');
    }

    public function specialtyAction() {

        
        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $SeoUrl = new Application_Model_SeoUrl();
        $Category = new Application_Model_Category();
        $object = $Category->fetchAll('status=1', 'name');
        if($object){
            foreach($object as $cat){
                $seo_url = $SeoUrl->fetchRow("actual_url='/search/?category={$cat->getId()}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/search/?category=".$cat->getId());
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
            }
        }



                $sitemap_file = "";
                $sitemap_file = "sitemap-specialty.xml";
                $this->_dom->save($sitemap_file);

        die('sitemap-specialty.xml');
    }

    public function cityAction() {

        $sitemap_file = "sitemap-city.xml";
        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $SeoUrl = new Application_Model_SeoUrl();
        $db = Zend_Registry::get('db');
        $select = $db->query("SELECT city FROM doctorr WHERE status=1 AND city!='' GROUP BY city ORDER BY city ASC");
        $object = $select->fetchAll();
        if($object){
            $i = 1;
            $j = 1;
            foreach($object as $city){
                $seo_url = $SeoUrl->fetchRow("actual_url='/search/?search1={$city->city}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/search/?search1=".$city->city);
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
                if($i%50000==0){
                    $this->_dom->save($sitemap_file);
                    $sitemap_file = "sitemap-city-{$j}.xml";
                    $j++;
                    $this->initXmlDom();
                     
                }
                $i++;
            }
        }



           // $sitemap_file = "";
//            $sitemap_file = "sitemap-city.xml";
            $this->_dom->save($sitemap_file);
        prexit($sitemap_file);
    }

     public function specialtyCityAction() {
       
        $sitemap_file = "sitemap-specialty-city.xml";
        $names = array();
        $names[] = $sitemap_file;
        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $SeoUrl = new Application_Model_SeoUrl();
        $db = Zend_Registry::get('db');
        $select = $db->query("SELECT 'category_id','category_name', 'city' FROM cache_categories_city");
        $object = $select->fetchAll();
        if($object){
            $i = 1;
            $j = 1;
            foreach($object as $obj){
                $seo_url = $SeoUrl->fetchRow("actual_url='/search/?category={$obj->category_id}&search1={$obj->city}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/search/?category={$obj->category_id}&search1={$obj->city}");
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
                if($i%40000==0){
                    
                    $this->_dom->save($sitemap_file);
                    $sitemap_file = "sitemap-specialty-city-{$j}.xml";
                    $names[] = $sitemap_file;
                    $j++;
                    $this->initXmlDom();

                }
                $i++;
            }
        }

        $this->_dom->save($sitemap_file);
        prexit($names);

    }

    public function specialtyZipcodeAction() {


        $names = array();
        $sitemap_file = "sitemap-specialty-zip.xml";
        $names[] = $sitemap_file;
        $this->initXmlDom();
        $root = $this->_dom->createElement("url");
	$this->_main->appendChild($root);

	//######### Site url node ##########
	$item = $this->_dom->createElement("loc");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("http://www.siteurl.org/");
	$item->appendChild($text);

        $item = $this->_dom->createElement("changefreq");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("daily");
	$item->appendChild($text);

	$item = $this->_dom->createElement("priority");
	$root->appendChild($item);
	$text = $this->_dom->createTextNode("1.0");
	$item->appendChild($text);/**/
        ////////////////////////////////////////////////
        $SeoUrl = new Application_Model_SeoUrl();
        $db = Zend_Registry::get('db');
        $select = $db->query("SELECT `category_id`, `category_name`, `zipcode` FROM cache_categories_zipcode");
        $object = $select->fetchAll();
        if($object){
            $i = 1;
            $j = 1;
            
            foreach($object as $obj){
                $seo_url = $SeoUrl->fetchRow("actual_url='/search/?category={$obj->category_id}&search1={$obj->zipcode}'");
                $root = $this->_dom->createElement("url");
                $this->_main->appendChild($root);

                $item = $this->_dom->createElement("loc");
                $root->appendChild($item);
                if($seo_url){
                    $text = $this->_dom->createTextNode("http://www.siteurl.org".$seo_url->getSeoUrl());
                }else{
                    $text = $this->_dom->createTextNode("http://www.siteurl.org/search/?category={$obj->category_id}&search1={$obj->zipcode}");
                }
                $item->appendChild($text);

                $item = $this->_dom->createElement("changefreq");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("daily");
                $item->appendChild($text);

                $item = $this->_dom->createElement("priority");
                $root->appendChild($item);
                $text = $this->_dom->createTextNode("0.7");
                $item->appendChild($text);
                if($i%40000==0){
                    $this->_dom->save($sitemap_file);
                    $sitemap_file = "sitemap-specialty-zip-{$j}.xml";
                    $names[] = $sitemap_file;
                    $j++;
                    $this->initXmlDom();

                }
                $i++;
            }
        }



        $this->_dom->save($sitemap_file);
        prexit($names);

    }


    


    public function insuranceDentistAction() {

        $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();

        $db = Zend_Registry::get('db');

        $select = $db->select()
                        ->from(array('insurance_companies'),
                                array('id','company'))
                ->where("id IN (SELECT DISTINCT insurance_company_id FROM insurance_plans
                        WHERE status=1 AND plany_type='d') AND status=1")
                ->order ("company ASC");


        $pageObj = new Base_Paginator();
        $paginator = $pageObj->DbSelectPaginator($select, $page, 500);

        $this->view->total = $paginator->getCurrentItemCount();
        $this->view->paginator = $paginator;

    }
    public function insuranceOtherAction() {

        $settings = new Admin_Model_GlobalSettings();
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();

        $db = Zend_Registry::get('db');
        $dentistCategory = "'Dentist','Endodontist','Periodontist','Prosthodontist','Oral and Maxillofacial Surgeon','Orthodontist'";
        $select = $db->select()
                        ->from(array('i'=>'insurance_companies'),
                                array('id','company'))
                ->join(array('c' => 'categories'),
                        '',
                        array('id AS category_id', 'name'))
                ->where("i.id IN (SELECT DISTINCT insurance_company_id FROM insurance_plans
                        WHERE status=1 AND plan_type='g') AND i.status=1 AND c.name NOT IN ($dentistCategory) AND c.status=1")
                ->order ("c.name ASC")
                ->order ("i.company ASC");


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
}