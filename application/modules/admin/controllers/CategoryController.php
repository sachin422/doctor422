<?php

class Admin_CategoryController extends Base_Controller_Action {

    public function indexAction() {
        $this->view->title = "Admin Panel- List Category";
        $this->view->headTitle("Admin Panel");

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Category();

        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size, null, "id DESC");
        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;

        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }
	
   
    public function deleteAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');
        $idArray = explode(',', $ids);
        $error = 0;
        $db = Zend_Registry::get('db');
        
        foreach ($idArray as $id) {
        	
	        $select = $db->select()
	                 ->from(array('d' => 'doctors'),
	                        array('fname'))
	                 ->join(array('dc' => 'doctor_categories'),
	                        'd.id = dc.doctor_id'
	                         )
	                 ->join(array('c' => 'categories'),
	                        'dc.category_id = c.id',
	                        array('name as category_name'))
	                ->where("c.id=$id");
	                
			$stmt = $select->query();
			$result = $stmt->fetchAll();
			echo "<pre>";print_r($result);
			if(!empty($result)){
				$error = $error +1;
			}
        }
        
        if($error > 0){
        	echo $msg = base64_encode("Category cannot be deleted because doctors associated to this are still exist.");
        	$this->_helper->redirector('index', 'category', "admin", Array('msg' => $msg, 'page' => $page));
        }else{
        
	        $objModelCategory = new Application_Model_Category();
	        foreach ($idArray as $id) {
	            $object = $objModelCategory->find($id);
	            $object->delete("id={$id}");
	        }
	        // delete after article delete
	        $msg = base64_encode("Record(s) has been deleted successfully!");
	        $this->_helper->redirector('index', 'category', "admin", Array('msg' => $msg, 'page' => $page));
        }
    }

    public function publishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Category();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            $object->setStatus('1');
            $object->save();
        }

        $publish = base64_encode("Record(s) published successfully");
        $this->_helper->redirector('index', 'category', "admin", Array('page' => $page, 'msg' => $publish));
    }

    public function unpublishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Category();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            $object->setStatus(0);
            $object->save();
        }
        $publish = base64_encode("Record(s) unpublished successfully");
        $this->_helper->redirector('index', 'category', "admin", Array('page' => $page, 'msg' => $publish));
    }

    public function addEditAction() {
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');

        $form = new Admin_Form_Category();
		$form->setAttrib('enctype', 'multipart/form-data');

        
        
        if (0 < (int) $id) {
            $model = new Application_Model_Category();
            $object = $model->find($id);
            $options['id'] = $id;
            $options['name'] = $object->getName();
            $options['description'] = $object->getDescription();            
            $options['metadescription'] = $object->getMetadescription();
            $options['metatitle'] = $object->getMetatitle();
            $options['metakeywords'] = $object->getMetakeywords();
            $options['icon'] = $object->getIcon();

            $form->populate($options);
        }

        $request = $this->getRequest();

        
        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {
                $msg = base64_encode("Record has been save successfully!");
                if (0 < (int) $id) {
                    $options['id'] = $id;
                    $object->setId($id);
                    $object->setName($options['name']);
                    $object->setDescription($options['description']);
                    $object->setMetadescription($options['metadescription']);
                    $object->setMetatitle($options['metatitle']);
                    $object->setMetakeywords($options['metakeywords']);

                    //icon
					$upload = new Zend_File_Transfer_Adapter_Http();
					$path = "images/categories/";
					$upload->setDestination($path);
					try {
					    $upload->receive();
					} catch (Zend_File_Transfer_Exception $e) {
					    $e->getMessage();
					}
					$upload->setOptions(array('useByteString' => false));
					$file_name = $upload->getFileName('icon');
					if (!empty($file_name)) {
					    $imageArray = explode(".", $file_name);
					    $ext = strtolower($imageArray[count($imageArray) - 1]);
					    $target_file_name = "cat_" . time() . ".{$ext}";
					    
					    $targetPath = $path . $target_file_name;
					    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath, 'overwrite' => true));
					    $filterFileRename->filter($file_name);
					    
					    $image_name = $target_file_name;
					    /*$newImage = $path . $image_name;
					    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
					    $thumb->resize(400, 234);
					    $thumb->save($newImage);*/
					    
				        $del_image = $path . $object->getIcon();
				        if (file_exists($del_image))unlink($del_image);
				        
				        $object->setIcon($image_name);
					    
					}
					

                    $object->save();
                    $this->_helper->redirector('index', 'category', "admin", Array('msg' => $msg, 'page' => $page));
                } else {
                    $model = new Application_Model_Category($options);
                    $model->save();
                }
                $this->_helper->redirector('index', 'category', "admin", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }

        $this->view->form = $form;
    }


}
?>