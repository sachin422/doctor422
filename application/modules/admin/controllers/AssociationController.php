<?php

class Admin_AssociationController extends Base_Controller_Action {

    public function indexAction() {
        $this->view->title = "Admin Panel- List Association";
        $this->view->headTitle("Admin Panel");
        $cid = $this->_getParam("cid");
        if(0<(int)$cid)
            $where ="category_id=".$cid."";
        else
            $where = null;
        $this->view->cid = $cid;
        

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Association();
        $Category = new Application_Model_Category();
        $categories = $Category->fetchAll('status=1', 'name ASC');
       
        $this->view->categories = $categories;
        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size, $where, "association ASC");
        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;
        
        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }

    public function deleteAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Association();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            if ($object->getLogo() != '') {
                $filename = 'images/association/' . $object->getLogo();
                if (file_exists($filename)) {
                    unlink($filename);
                }
            }

            $object->delete("id={$id}");
        }
        // delete after article delete
        $msg = base64_encode("Record(s) has been deleted successfully!");
        $this->_helper->redirector('index', 'association', "admin", Array('msg' => $msg, 'page' => $page));
    }

    public function publishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Association();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            $object->setStatus('1');
            $object->save();
        }

        $publish = base64_encode("Record(s) published successfully");
        $this->_helper->redirector('index', 'association', "admin", Array('page' => $page, 'msg' => $publish));
    }

    public function unpublishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_Association();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            $object->setStatus(0);
            $object->save();
        }
        $publish = base64_encode("Record(s) unpublished successfully");
        $this->_helper->redirector('index', 'association', "admin", Array('page' => $page, 'msg' => $publish));
    }

    public function addEditAction() {
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');

        $form = new Admin_Form_Association();

        
        
        if (0 < (int) $id) {
            $model = new Application_Model_Association();
            $object = $model->find($id);
            $options['id'] = $id;
            $options['association'] = $object->getAssociation();
           
            $options['logo'] = $object->getLogo();
            $options['categoryId'] = $object->getCategoryId();

            $form->populate($options);
        }

        $request = $this->getRequest();

        
        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {

                $upload = new Zend_File_Transfer_Adapter_Http();
                $path = "images/association/";
                $upload->setDestination($path);
                try {
                    $upload->receive();
                } catch (Zend_File_Transfer_Exception $e) {
                    $e->getMessage();
                }
//        echo "<pre>";print_r($upload->getFileName('logo'));exit;
                $upload->setOptions(array('useByteString' => false));
                $file_name = $upload->getFileName('logo');
                if(!empty($file_name)){
                    $imageArray = explode(".", $file_name);
                    $ext = strtolower($imageArray[count($imageArray) - 1]);
                    $target_file_name = "ass_".time().".{$ext}";
                    $targetPath = $path . $target_file_name;
                    $filterFileRename = new Zend_Filter_File_Rename(array('target' => $targetPath , 'overwrite' => true));
                    $filterFileRename -> filter($file_name);
                    /*------------------ THUMB ---------------------------*/
                    $image_name	=	$target_file_name;
                    $newImage	=	$path . $image_name;

                    $thumb = Base_Image_PhpThumbFactory ::create($targetPath);
                    //$thumb->resize(150, 60);
                    $thumb->save($newImage);
                    if (0 < (int) $id) {
                        $del_image = $path . $object->getLogo();
                        if(file_exists($del_image))unlink($del_image);
                        $object->setLogo($image_name);
                    }else{
                        $options['logo'] = $image_name;
                    }
                    
                    /*------------------ END THUMB ------------------------*/
                }

                $msg = base64_encode("Record has been save successfully!");
                if (0 < (int) $id) {
                    $options['id'] = $id;
                    $object->setId($id);
                    $object->setAssociation($options['association']);
                    $object->setDescription($options['description']);
                    $object->setCategoryId($options['categoryId']);
                    $object->save();
                    $this->_helper->redirector('index', 'association', "admin", Array('msg' => $msg, 'page' => $page));
                } else {
                    //print_r($options);exit;
                    $model = new Application_Model_Association($options);
                    $model->save();
                }
                $this->_helper->redirector('index', 'association', "admin", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }

        $this->view->form = $form;
    }


}
?>