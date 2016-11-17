<?php
class Admin_AssistantController extends Base_Controller_Action {

    public function indexAction() {
        $this->view->title = "Admin Panel- Salesmen List";
        $this->view->headTitle("Admin Panel");

        $settings = new Admin_Model_GlobalSettings();
        $model = new Application_Model_Assistant();

        $page_size = $settings->settingValue('pagination_size');
        $page = $this->_getParam('page', 1);
        $assistant_name = $this->_getParam("assistant_name");
        $assistant_email = $this->_getParam("assistant_email");
        $strwhere_condition="status='active' OR status='inactive'";
        
        $pageObj = new Base_Paginator();
        $paginator = $pageObj->fetchPageData($model, $page, $page_size, $strwhere_condition, "id DESC");
        $this->view->total = $pageObj->getTotalCount();
        $this->view->paginator = $paginator;
             
        $this->view->msg = base64_decode($this->_getParam('msg', ''));
    }
	
	public function publishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');
        $assistant_name = $this->_getParam("assistant_name");
        $assistant_email = $this->_getParam("assistant_email");
        $idArray = explode(',', $ids);
        $model = new Application_Model_Assistant();
        foreach ($idArray as $id) {
            $object = $model->find($id);
            $object->setStatus('active');
            $object->save();
           
            $User = new Application_Model_User();
            $user = $User->find($object->getUserid());
            $user->setStatus('active');
            $user->save();
        }

        $publish = base64_encode("Record(s) published successfully");
        $this->_helper->redirector('index', 'assistant', "admin", Array('assistant_name'=>$assistant_name,'assistant_email'=>$assistant_email,'page' => $page, 'msg' => $publish));
    }

    public function unpublishAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');
        $assistant_name = $this->_getParam("assistant_name");
        $assistant_email = $this->_getParam("assistant_email");

        $idArray = explode(',', $ids);
        $model = new Application_Model_Assistant();

        foreach ($idArray as $id) {
           
            $object = $model->find($id);
            $object->setStatus('inactive');
            $object->save();
           
            $User = new Application_Model_User();
            $user = $User->find($object->getUserid());
            $user->setStatus('inactive');
            $user->save();
        }

        $publish = base64_encode("Record(s) unpublished successfully");
        $this->_helper->redirector('index', 'assistant', "admin", Array('assistant_name'=>$assistant_name,'assistant_email'=>$assistant_email,'page' => $page, 'msg' => $publish));
    }
 

    public function deleteAction() {
        $ids = $this->_getParam('ids');
        $page = $this->_getParam('page');
        $assistant_name = $this->_getParam("assistant_name");
        $assistant_email = $this->_getParam("assistant_email");
        $idArray = explode(',', $ids);
        $model = new Application_Model_Assistant();
        foreach ($idArray as $id) {
            $object = $model->find($id);
			$object->setStatus('deleted');
            $object->save();
        }
        $msg = base64_encode("Record(s) has been deleted successfully!");
        $this->_helper->redirector('index', 'assistant', "admin", Array('assistant_name'=>$assistant_name,'assistant_email'=>$assistant_email,'page' => $page, 'msg' => $publish));
    }

    public function addEditAction() {
        $id = $this->_getParam("id");
        $this->view->id = $id;
        $form=new Admin_Form_Assistant();
        $elements = $form->getElements();
        $form->clearDecorators();
        $Assistant = new Application_Model_Assistant();
        foreach ($elements as $element){
            $element->removeDecorator('label');
            $element->removeDecorator('row');
            $element->removeDecorator('data');
        }
        if (0 < (int) $id)  { //populate form
           
            $Assistant = $Assistant->fetchRow("id='{$id}'");
       
            $options['id'] = $Assistant->getId();
            $options['name'] = $Assistant->getName();
            $options['telephone'] = $Assistant->getTelephone();
            $User = new Application_Model_User();
            $user = $User->find($Assistant->getuserId());
            $options['email'] = $user->getEmail();
            $options['joindate'] = $Assistant->getJoindate();
            $options['status'] = $Assistant->getStatus();
            $options['address'] = $Assistant->getAddress();
           
           $db = Zend_Registry::get('db');
            $select = $db->select()
                ->from('doctor_assistant', 'count(id) as amount')
                ->where('assistant_id = '.$id);
            $docCount = $db->fetchOne($select);
            $this->view->doctorCount = $docCount;
            $this->view->joindate = date("d-m-Y",strtotime($options['joindate']));
			$DoctorAssistant = new Application_Model_DoctorAssistant();
            $this->view->doctorsList = $DoctorAssistant->getDoctorsByAssistant($id);
           
            $form->populate($options);

        } else {
            $this->view->doctorCount = 0;
            $this->view->joindate = date("d-m-Y");
        }
       
        $request = $this->getRequest();
        $options = $request->getPost();
        if ($request->isPost()) {
            $email=trim($options['email']);
            $User = new Application_Model_User();
            $Users = $User->fetchRow("email='{$email}'");
            if($id) {
                $Assistant = $Assistant->find($id);
            }
            if(is_object($Users) && $Users->getId() != $Assistant->getUserid() ) {
                $form->setErrorMessages(array('This Email already exists'));
                $emailerror=1;
            } else {
                $emailerror=0;
            }

            if (($form->isValid($options) && $emailerror<1)) {
                $msg = "Record has been saved successfully!";
                if($id) {
                    $Assistant = $Assistant->find($id);
                }
                else {
                    /*********Create User *********/
                    $User1 = new Application_Model_User();
                    $User1->setFirstName($options['name']);
                    $User1->SetLastName("");
                    $User1->setPassword(md5('new_ass'));
                    $User1->setUserLevelId(4);
                    $User1->setLastVisitDate(time());
                    $User1->setStatus("inactive");
                    $User1->setEmail($options['email']);
                    $User1->setUsername($options['email']);
                    $user_id = $User1->save();
                   
                   
                    $Assistant = new Application_Model_Assistant();
                    $Assistant->setJoindate(date("Y-m-d", time()));
                    $Assistant->setStatus("inactive");
                    $Assistant->setUserid($user_id);
                }
                $Assistant->setName($options['name']);
                $Assistant->setTelephone($options['telephone']);
                $Assistant->setAddress($options['address']);
                $Assistant->save();
               
                $form->populate($options);
                $this->view->msg=$msg;

                $msg = base64_encode($msg);
                $this->_helper->redirector('index', 'assistant', "admin", Array('msg' => $msg, 'page' => $page));
            } else {
                $form->reset();
                $form->populate($options);
            }
        } else {
           
        }
        $this->view->form = $form;
    }
}
?>