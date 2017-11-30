<?php

class wn8_var {
	private $_name;

	public function __construct($name) {
        $this->setName($name);
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }
}

class wn8_varGrp {
	private $_name;
	private $_vars = [];

	public function __construct($name) {
		$this->setName($name);
	}

	public function setName($name) {
		$this->_name = $name;
	}

	public function getName() {
		return $this->_name;
	}

	public function getVars() {
		return $this->_vars;
	}

	public function getVar($name) {
		return $this->_vars[$name];
	}

	public function addVar($var) {
		$this->_vars[$var->getName()] = $var;
	}
}

class wn8_varCls {
	private $_name;
	private $_groups = [];

	public function __construct($name) {
		$this->setName($name);
	}

	public function setName($name) {
		$this->_name = $name;
	}

	public function getName() {
		return $this->_name;
	}

	public function getGroups() {
		return $this->_groups;
	}

	public function getGroup($name) {
		return $this->_groups[$name];
	}

	public function addGroup($group) {
		$this->_groups[$group->getName()] = $group;
	}

}

class wn8 {
	private $_name;
	private $_classes = [];

	public function __construct($name) {
		$this->setName($name);
	}

	public function setName($name) {
		$this->_name = $name;
	}

	public function getName() {
		return $this->_name;
	}

	public function getClasses() {
		return $this->_classes;
	}

	public function getClass($name) {
		return $this->_classes[$name];
	}

	public function addClass($class) {
		$this->_classes[$class->getName()] = $class;
	}
}

?>