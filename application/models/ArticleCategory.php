<?php
/**
 * User model
 *
 * Utilizes the Data Mapper pattern to persist data. Represents a single
 * user entry.
 *
 * @uses       Application_Model_ArticleCategory
 * @package    Directory
 * @subpackage Model
 */

class Application_Model_ArticleCategory {

    /**
     * @var int
     */
    protected $_id;
    protected $_name;
    protected $_description;
    protected $_published;

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

    /**
     * Set entry id
     *
     * @param  int $id
     * @return Application_Model_ArticleCategory
     */
    public function setId($id) {
        $this->_id = (int) $id;
        return $this;
    }

    /**
     * Retrieve entry id
     *
     * @return null|int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return Directory_Model_DirectoryCategory
     */
    public function setName($name) {
        $this->_name = (string) $name;
        return $this;
    }


    /**
     * Get organisationName
     *
     * @return null|string
     */
    public function getName() {
        return $this->_name;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function setDescription($description) {
        $this->_description = (string) $description;
        return $this;
    }
    public function getPublished() {
        return $this->_published;
    }

    public function setPublished($published) {
        $this->_published = (int) $published;
        return $this;
    }

    public function setMapper($mapper) {
        $this->_mapper = $mapper;
        return $this;
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
            $this->setMapper(new Application_Model_ArticleCategoryMapper());
        }
        return $this->_mapper;
    }


    private function setModel($row) {
        $model=new Application_Model_ArticleCategory();
        $model->setId($row->id)
                ->setName($row->name)
                ->setDescription($row->description)
                ->setPublished($row->published);
        return $model;
    }

    /**
     * Save the current entry
     *
     * @return void
     */
    public function save() {

        $data = array(
                'name'   => $this->getName(),
                'description'   => $this->getDescription()
        );

        if (null === ($id = $this->getId())) {
            unset($data['id']);
            $data['published'] = 1;
            return $this->getMapper()->getDbTable()->insert($data);
        } else {
            $data['published'] = $this->getPublished();
            return $this->getMapper()->getDbTable()->update($data, array('id = ?' => $id));
        }

    }

    /**
     * Find an entry
     *
     * Resets entry state if matching id found.
     *
     * @param  int $id
     * @return User_Model_User
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

    public function fetchRow($where=null, $order=null) {
        $row = $this->getMapper()->getDbTable()->fetchRow($where, $order);

        if(!empty($row)) {
            $res=$this->setModel($row);
            return $res;
        }
        else {
            return false;
        }

    }


    public function delete($where) {
        return $this->getMapper()->getDbTable()->delete($where);
    }

    public function getCategories($where=null, $option=null) {
        $obj=new Application_Model_ArticleCategory();
        $entries=$obj->fetchAll($where);
        $arrCountry=array();
        if(!is_null($option))
            $arrCountry['']=$option;
        $arrCountry[]= 'Select Category';
        foreach($entries as $entry) {
            $arrCountry[$entry->getId()]=$entry->getName();
        }
        return $arrCountry;
    }
}

?>