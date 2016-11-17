<?php
/**
 * Assistant model
 *
 * A single Assistant
 *
 * @package    Directory
 * @subpackage Model
 */

class Application_Model_Assistant {

    /**
     * @var int
     */
    protected $_id;
    protected $_name;
	protected $_telephone;
	protected $_joindate;
	protected $_status;
	protected $_userid;
	protected $_address;
	protected $_mapper;


    /**
     * Constructor
     *
     * @param  array|null $options
     * @return void
     */
    public function __construct(array $options = null) {
      
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Overloading: allow property access
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set($name, $value) {
        $method = 'set' . $name;
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        $this->$method($value);
    }
	
	public function setMapper($mapper) {
        $this->_mapper = $mapper;
        return $this;
    }


    /**
     * Overloading: allow property access
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name) {
        $method = 'get' . $name;
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        return $this->$method();
    }

    /**
     * Set object state
     *
     * @param  array $options
     * @return Directory_Model_DirectoryCategory
     */
    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setId($id) {
        $this->_id = (int) $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }
    public function setName($name) {
        $this->_name = (string) $name;
        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function setTelephone($telephone) {
		$this->_telephone = (string)$telephone;
		return $this;
	}
	
	public function getTelephone(){
		return $this->_telephone;
	}
	
	public function setJoindate($joindate) {
		$this->_joindate = $joindate;
		return $this;
	}
	
	public function getJoindate() {
		return $this->_joindate;
	}
	
	public function setStatus($status) {
		$this->_status = $status;
		return $this;
	}
	
	public function getStatus() {
		return $this->_status;
	}
	
	public function setUserid($userid) {
		$this->_userid = $userid;
		return $this;
	}
	
	public function getUserid() {
		return $this->_userid;
	}
	
	public function setAddress($address) {
		$this->_address = $address;
		return $this;
	}
	
	public function getAddress() {
		return $this->_address;
	}
	
    private function setModel($row) {
		$model=new Application_Model_Assistant();
		$model->setId($row->id)
			->setName($row->name)
			->setTelephone($row->telephone)
			->setJoindate($row->joindate)
			->setStatus($row->status)
			->setUserid($row->userid)
			->setAddress($row->address);
        return $model;
    }

    /**
     * Save the current entry
     *
     * @return void
     */
    public function save() {
		$data = array(
			'name' => $this->getName(),
			'joindate'   => $this->getJoindate(),
			'telephone'   => $this->getTelephone(),                
			'status'   => $this->getStatus(),                
			'userid'   => $this->getUserid(),
			'address'   => $this->getAddress()                
        );

        if (null === ($id = $this->getId())) {
            unset($data['id']);
            return $this->getMapper()->getDbTable()->insert($data);
        } else {
            return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $id));
        }
	}

    /**
     * Find an entry
     *
     * Resets entry state if matching id found.
     *
     * @param  int $id
     * @return User_Model_Area
     */
    public function find($id) {
        $result = $this->getMapper()->getDbTable()->find($id);
        if (0 == count($result)) {
            return false;
        }
        $row = $result->current();
        $res=$this->setModel($row);

        return $res;
    }

    /**
     * Fetch all entries
     *
     * @return array
     */
    public function fetchAll($where=null, $order=null, $count=null, $offset=null) {
        $resultSet = $this->getMapper()->getDbTable()->fetchAll($where, $order, $count, $offset);
        $entries   = array();
        foreach ($resultSet as $row) {
            $res=$this->setModel($row);
            $entries[] = $res;
        }
        return $entries;

    }

     public function fetchRow($where) {
        $row = $this->getMapper()->getDbTable()->fetchRow($where);

        if (!empty($row)) {
            $res = $this->setModel($row);
            return $res;
        } else {
            return false;
        }
    }


    public function delete($where) {
        return $this->getMapper()->getDbTable()->delete($where);
    }

	/**
     * Get data mapper
     *
     * Lazy loads Directory_Model_DirectoryCategoryMapper instance if no mapper registered.
     *
     * @return Directory_Model_DirectoryCategory
     */
    public function getMapper() {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_AssistantMapper());
        }
        return $this->_mapper;
    }
	
	
	public function getAssistants($where=null, $option=null,$forall=null) {
        $obj=new Application_Model_Assistant();
        $entries=$obj->fetchAll($where, 'name ASC');
        $arrAssist=array();
        if(!is_null($option))
            $arrAssist['']=$option;
       
        if(!is_null($forall))
        $arrAssist[-1]= 'All Assistants';
        foreach($entries as $entry) {
            $arrAssist[$entry->getId()]=$entry->getName();
        }
        return $arrAssist;
    }
	
	
}

?>