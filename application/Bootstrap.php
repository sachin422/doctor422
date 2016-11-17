<?php
define('PAID_SEARCH', "'Platinuum','Golde','Silverr'");
define('NO_SILVER', "'Platinum','Gold'");
define('BRONZE_SEARCH', "'Bronze'");

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	public function _initCache()
	{
		$frontendOptions = array(
				'lifeTime' => 720,
				'debug_header' => true,
				'regexps' => array(
					'^/' => array(
						'cache' => true,
						'cache_with_cookie_variables' => true,
					)
				)
			);
		 
			$backendOptions = array(
				'cache_dir' => PUBLIC_PATH.'/cache/'
			);
		 
			$cache = Zend_Cache::factory(
				'Page',
				'File',
				$frontendOptions,
				$backendOptions
			);
		 
			$cache->start();
	}
	

	protected function __initSession() {
		ini_set('session.gc_maxlifetime', 480);  // set session max lifetime to 45 seconds
		Zend_Session::start();
	}
	
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
		$view->setEncoding('UTF-8');
        $view->doctype('XHTML1_STRICT');
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');

    }
    
    
    protected function _initAutoload()
    {
    	/**/
		$autoloader = new Zend_Application_Module_Autoloader(
	 	array(
                'namespace' => 'Admin',
                'basePath' => APPLICATION_PATH . '/modules/admin'
       	 )
		);
		
    	
    }
    
    
    

    protected function _initRegistry()
	{
	    $this->bootstrap('db');
	    $db = $this->getResource('db');
		
	    $db->setFetchMode(Zend_Db::FETCH_OBJ);
	    Zend_Registry::set('db', $db);
	    
	   
	    	
	    $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/'.APPLICATION_INI, APPLICATION_ENV);
	    Zend_Registry::set('siteurl', $config->gd->siteurl);
	    Zend_Registry::set('domain', $config->gd->domain);
	    Zend_Registry::set('cdn_uri', $config->gd->cdn_uri);
	   
	}
	
	protected function _initViewHelpers(){
		$this->bootstrap('layout');
		$layout=$this->getResource('layout');
		$view=$layout->getView();
		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		$view->addHelperPath('Base/View/Helper/', 'Base_View_Helper');
		require_once APPLICATION_PATH.'/configs/lang/en.php';
		$view->lang = $lang;
			
		
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		ZendX_JQuery::enableView($view);

	}
	
	protected function _initNavigation()
	{
            
		$this->bootstrap('layout');
		$layout=$this->getResource("layout");
		$view=$layout->getView();
		
		$config=new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation/navigation.xml','nav');
		$navigation= new Zend_Navigation($config);
		$view->navigation($navigation);
		$form=$view->form;
	
	}
	
	protected function _initPlugin()
	{
		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Base_Plugin_Action());

	}
	
}

