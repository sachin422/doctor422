<?php

class Admin_ArticleController extends Base_Controller_Action {

    public function indexAction() {
        $this->view->title = "Admin Panel- List Article";
        $this->view->headTitle("Admin Panel");

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Article();

        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size);
        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;

        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }

    //add and edit
    public function editAction() {
        $id = $this->_getParam('id');
        
        $form = new Admin_Form_Article();

        if (0 < (int) $id) {
            $model = new Application_Model_Article();
            $object = $model->find($id);

            $options['id'] = $id;
            $options['title'] = stripslashes($object->getTitle());
            $options['summary'] = stripslashes($object->getSummary());
            $options['content'] = stripslashes($object->getContent());
            $options['category'] = $object->getCategory();

            if ($object->getPublishedTime() != '' || $object->getPublishedTime() != 0) {
                $options['publishedTime'] = date('Y-m-d H:i', $object->getPublishedTime());
                $this->view->PublishedTime = $object->getPublishedTime();
            } else {
                $options['publishedTime'] = '';
            }
            if ($object->getUnpublishedTime() != '' || $object->getUnpublishedTime() != 0) {
                $options['unpublishedTime'] = date('Y-m-d H:i', $object->getUnpublishedTime());
                $this->view->UnpublishedTime = $object->getUnpublishedTime();
            } else {
                $form->getElement('unpublishedTime')->setValue('');
                $options['unpublishedTime'] = '';
            }
            if ($object->getDisplayTime() != '' || $object->getDisplayTime() != 0) {
                $options['displayTime'] = date('Y-m-d', $object->getDisplayTime());
                $this->view->DisplayTime = $object->getDisplayTime();
            } else {
                $options['displayTime'] = '';
            }
            $form->populate($options);
        }

        $request = $this->getRequest();

        $elements = $form->getElements();
        foreach ($elements as $element) {
            $element->removeDecorator('data');
            $element->removeDecorator('label');
            $element->removeDecorator('table');
            $element->removeDecorator('row');
        }
        

        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {
                
                ($options['displayTime'] != '') ? $options['displayTime'] = strtotime($options['displayTime']) : $options['displayTime'] = '';
                ($options['publishedTime'] != '') ? $options['publishedTime'] = strtotime($options['publishedTime']) : $options['publishedTime'] = '';
                ($options['unpublishedTime'] != '') ? $options['unpublishedTime'] = strtotime($options['unpublishedTime']) : $options['unpublishedTime'] = '';
                if (0 < (int) $id) {
					$options['id'] = $id;
					$object->setOptions($options);
					$object->save();
                }else{
                    $model = new Application_Model_Article($options);
                    $model->save();
					$options['id'] = $model->getId();
                }
					
				$SeoUrl = new Application_Model_SeoUrl();
                $SeoObject = $SeoUrl->fetchRow("actual_url='/index/page/id/{$options['id']}'");
				if(!$options['theurl']) {
					$options['theurl'] = $options['title'];
				}
				$options['theurl'] = urlencode($options['theurl']);

                if($SeoObject) {
				    $SeoObject->setSeoUrl('/'.trim($options['theurl']));
				} else {
                    $SeoObject = new Application_Model_SeoUrl();
                    $SeoObject->setSeoUrl('/'.trim($options['theurl']));
                    $SeoObject->setActualUrl("/index/page/id/{$options['id']}");
                    $SeoObject->setUrlType('1');
                    $SeoObject->setCreateDate(time());
                    $SeoObject->setStatus('1');
                    $SeoObject->setMetaTitle('');
                    $SeoObject->setMetaKeywords('');
                    $SeoObject->setMetaDescription('');
                }
                $SeoObject->save();

				
				
                $msg = base64_encode("Article has been saved successfully!");
                $this->_helper->redirector('index', 'article', "admin", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }

        $this->view->form = $form;
    }

    public function publishAction() {
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');

        $model = new Application_Model_Article();
        $object = $model->find($id);
        if ($object->getPublished() == 1) {
            $object->setPublished(0);
            $publish = "Article unpublished successfully";
        } else {
            if ($object->getDisplayTime() == 0) {
                $object->setDisplayTime($object->getInsertTime());
            }
            $object->setPublished(1);
            $publish = "Article published successfully";
        }
        $object->save();
        return $this->_helper->redirector('index', 'article', "admin", Array('page' => $page));
    }

    public function featuredAction() {

        $id = $this->_getParam('id');
        $catid = $this->_getParam('catid');

        $model = new Application_Model_Article();
        $Article = $model->find($id);
        if ($Article->getFeatured() == 1) {
            $Article->setFeatured(0);
        } else {
            $Article->setFeatured(1);
        }
        $Article->save();
        return $this->_helper->redirector('index', 'article', "admin", Array('catid' => $catid));
    }

   
  
    public function deleteAction() {
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');

        $objModelArticle = new Application_Model_Article();
        $article = $objModelArticle->find($id);

        $article->delete("id={$id}");
        // delete after article delete
        $msg = base64_encode("Article has been deleted successfully!");
        $this->_helper->redirector('index', 'article', "admin", Array('msg' => $msg,'page'=>$page));

    }



     public function categoryAction() {
        $this->view->title = "patient Admin Panel- List Article";
        $this->view->headTitle("patient Admin Panel");

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_ArticleCategory();

        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size);
        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;

        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }

    public function deleteCategoryAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $objModelArticle = new Application_Model_ArticleCategory();
        foreach($idArray as $id){
            $article = $objModelArticle->find($id);
            $article->delete("id={$id}");
        }
        // delete after article delete
        $msg = base64_encode("Record(s) has been deleted successfully!");
        $this->_helper->redirector('category', 'article', "admin", Array('msg' => $msg,'page'=>$page));

    }
    
     public function publishCategoryAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_ArticleCategory();
        foreach($idArray as $id){
            $object = $model->find($id);
            $object->setPublished('1');
            $object->save();
        }

        $publish = base64_encode("Record(s) published successfully");
        $this->_helper->redirector('category', 'article', "admin", Array('page' => $page,'msg'=>$publish));
    }

    public function unpublishCategoryAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');

        $idArray = explode(',', $ids);
        $model = new Application_Model_ArticleCategory();
        foreach($idArray as $id){
            $object = $model->find($id);
            $object->setPublished(0);
            $object->save();
        }
        $publish = base64_encode("Record(s) unpublished successfully");
        $this->_helper->redirector('category', 'article', "admin", Array('page' => $page,'msg'=>$publish));
    }

    public function addEditCategoryAction() {
        $id = $this->_getParam('id');
        $page = $this->_getParam('page');
        $this->view->page = $this->_getParam('page');

        $form = new Admin_Form_ArticleCategory();

        if (0 < (int) $id) {
            $model = new Application_Model_ArticleCategory();
            $object = $model->find($id);

            $options['id'] = $id;
            $options['name'] = $object->getName();
            $options['description'] = $object->getDescription();

            $form->populate($options);
        }

        $request = $this->getRequest();

        $options = $request->getPost();
        if ($request->isPost()) {
            if ($form->isValid($options)) {

                $msg = base64_encode("Record(s) has been save successfully!");
                if (0 < (int) $id) {
                $options['id'] = $id;
                $object->setOptions($options);
                $object->save();
                $this->_helper->redirector('category', 'article', "admin", Array('msg' => $msg,'page'=>$page));
                }else{
                    $model = new Application_Model_ArticleCategory($options);
                    $model->save();
                }
                $this->_helper->redirector('category', 'article', "admin", Array('msg' => $msg));
            } else {
                $form->reset();
                $form->populate($options);
            }
        }

        $this->view->form = $form;
    }
}

?>