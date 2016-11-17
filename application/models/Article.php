<?php

class Application_Model_Article {

    protected $_id;
    protected $_summary;
    protected $_title;
    protected $_content;
    protected $_published;
    protected $_featured;
    protected $_insertTime;
    protected $_updateTime;
    protected $_displayTime;
    protected $_publishedTime;
    protected $_unpublishedTime;
    protected $_createdBy;
    protected $_category;
    protected $_mapper;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = 'set' . $name;
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        return $this->$method();
    }

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

    public function setMapper($mapper) {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper() {
        if (null === $this->_mapper) {
            $this->setMapper(new Application_Model_ArticleMapper());
        }
        return $this->_mapper;
    }

    public function setId($id) {
        $this->_id = (int) $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }

    public function getSummary() {
        return $this->_summary;
    }

    public function setSummary($summary) {
        $this->_summary = (string) $summary;
        return $this;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function setTitle($title) {
        $this->_title = (string) $title;
        return $this;
    }

    public function getContent() {
        return $this->_content;
    }

    public function setContent($content) {
        $this->_content = (string) $content;
        return $this;
    }

    public function getPublished() {
        return $this->_published;
    }

    public function setPublished($published) {
        $this->_published = (int) $published;
        return $this;
    }

    
    public function getFeatured() {
        return $this->_featured;
    }

    public function setFeatured($featured) {
        $this->_featured = (int) $featured;
        return $this;
    }

    public function getInsertTime() {
        return $this->_insertTime;
    }

    public function setInsertTime($insertTime) {
        $this->_insertTime = (int) $insertTime;
        return $this;
    }

    public function getUpdateTime() {
        return $this->_updateTime;
    }

    public function setUpdateTime($updateTime) {
        $this->_updateTime = (int) $updateTime;
        return $this;
    }


    public function setDisplayTime($displayTime)
    {
        $this->_displayTime = (int) $displayTime;
        return $this;
    }

    public function getDisplayTime()
    {
        return $this->_displayTime;
    }

     public function setPublishedTime($publishedTime)
    {
        $this->_publishedTime = (int) $publishedTime;
        return $this;
    }

    public function getPublishedTime()
    {
        return $this->_publishedTime;
    }

     public function setUnpublishedTime($unpublishedTime)
    {
        $this->_unpublishedTime = (int) $unpublishedTime;
        return $this;
    }


    public function getUnpublishedTime()
    {
        return $this->_unpublishedTime;
    }

    public function getCreatedBy()
    {
        return $this->_createdBy;
    }

   public function setCreatedBy($createdBy)
    {
        $this->_createdBy = (string) $createdBy;
        return $this;
    }

     public function setCategory($category)
    {
        $this->_category = (int) $category;
        return $this;
    }

   public function getCategory()
    {
        return $this->_category;
    }


    /* ----Data Manupulation functions ---- */

    private function setModel($row) {
        $model = new Application_Model_Article();
        $model->setId($row->id)
                ->setSummary($row->summary)
                ->setTitle($row->title)
                ->setContent($row->content)
                ->setPublished($row->published)
                ->setFeatured($row->featured)
                ->setInsertTime($row->insert_time)
                ->setUpdateTime($row->update_time)
                ->setDisplayTime($row->display_time)
                ->setPublishedTime($row->published_time)
                ->setUnpublishedTime($row->unpublished_time)
                ->setCreatedBy($row->created_by)
                ->setCategory($row->category)
        ;

        return $model;
    }

    public function save() {
        $data = array(
            'summary' => $this->getSummary(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'featured' => $this->getFeatured(),
            'display_time' => $this->getDisplayTime(),
            'published_time' => $this->getPublishedTime(),
            'unpublished_time' => $this->getUnpublishedTime(),
            'created_by' => $this->getCreatedBy(),
            'category' => $this->getCategory()
        );
        if (null === ($id = $this->getId())) {
            unset($data['id']);
            $data['insert_time'] = time();
            $data['update_time'] = time();
            $data['published'] = 1;
            return $this->getMapper()->getDbTable()->insert($data);
        } else {
            $data['update_time'] = time();
            $data['published'] = $this->getPublished();
            $this->getMapper()->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function find($id) {
        $result = $this->getMapper()->getDbTable()->find($id);
        if (0 == count($result)) {
            return false;
        }

        $row = $result->current();
        $res = $this->setModel($row);
        return $res;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null) {
        $resultSet = $this->getMapper()->getDbTable()->fetchAll($where, $order, $count, $offset);
        $entries = array();
        foreach ($resultSet as $row) {
            $res = $this->setModel($row);
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

    public function isExist($where) {
        $res = $this->fetchRow($where);

        if ($res === false) {
            return false;
        } else {
            return true;
        }
    }

}