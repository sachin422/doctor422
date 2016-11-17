<?php
// root file
ini_set('display_errors', 0); 
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));
	
defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

 
    defined('LIBRARY_PATH')
    || define('LIBRARY_PATH', realpath(dirname(__FILE__) . '/library'));
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
//echo "<pre>";print_r($_SERVER);exit;
if(isset($_SERVER['HTTP_HOST'])){
    $HTTP_HOST = $_SERVER['HTTP_HOST'];
}else{
    $HTTP_HOST = '';
}

        define('APPLICATION_INI', "application.ini");
     
//define('APPLICATION_INI', "application_domain.ini");
//die(APPLICATION_INI);
// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/'.APPLICATION_INI
);

 	
/*$application->bootstrap()
            ->run();
*/


$arrR=explode("?", $_SERVER['REQUEST_URI']);
//var_dump($arrR);;
if($arrR[0]=="/search/"){
	/*---Cache Start---*/
	$time=60*60*24*1;
	$uri =$HTTP_HOST.$_SERVER['REQUEST_URI'];
	$key=md5($uri);
	//$key=md5($_SERVER['REQUEST_URI']);
	$frontendOptions = array('lifeTime' =>$time,'automatic_serialization' => true); // 7200 seconds
	$backendOptions = array('cache_dir' => PUBLIC_PATH.'/cache/');
	$cache = Zend_Cache::factory('Output', 'File', $frontendOptions, $backendOptions);
	//$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
	
	if (!$cache->start($key) ){
		
		$application->bootstrap()
				->run();	
		$cache->end();
		
	} 
	/*---Cache End---*/
}else{
    $application->bootstrap()
            ->run();	
}




function prexit($array){
    if($_SERVER['HTTP_HOST']=='localhost.patient.com'){
        echo "<pre>";print_r($array);exit;
    }
}
function pre($array){
    if($_SERVER['HTTP_HOST']=='localhost.patient.com'){
        echo "<pre>";print_r($array);
    }
}

/** Zend_Application */
require_once APPLICATION_PATH.'/configs/lang/config.php';

$db = Zend_Registry::get('db');
	$profiler = $db->getProfiler();
	if($profiler->getEnabled()){
		echo "<!--\n======== PROFILER INFORMATION ========\n\n";
		$totalTime    = $profiler->getTotalElapsedSecs();
		$queryCount   = $profiler->getTotalNumQueries();
		$longestTime  = 0;
		$longestQuery = '';
		 
		foreach ($profiler->getQueryProfiles() as $query) {
			if ($query->getElapsedSecs() > $longestTime) {
				$longestTime  = $query->getElapsedSecs();
				$longestQuery = $query->getQuery();
			}
		}
		 
		echo 'Executed ' . $queryCount . ' queries in ' . $totalTime .' seconds' . "\n";
		echo 'Average query length: ' . ( $queryCount > 0 ? $totalTime / $queryCount : 0) .' seconds' . "\n";
		echo 'Queries per second: ' . ( $totalTime > 0 ? $queryCount / $totalTime : 0) . "\n";
		echo 'Longest query length: ' . $longestTime . "\n";
		echo "Longest query: " . $longestQuery . "\n\n";
	 
		$profile = '';
		foreach($profiler -> getQueryProfiles() as $query) {
			$elapsedTime = str_pad($query -> getElapsedSecs(), 21, ' ')	;
			$profile .=  'Time: ' . $elapsedTime . "\t". ' Query: ' . $query -> getQuery() . "\n";
		} 
		echo $profile;
		echo '-->';
	}
