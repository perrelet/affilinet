<?php

namespace Affilinet;

abstract class Obj {
	
	public function get ($property) {
		
		if (property_exists($this, $property)) return $this->$property;
		return null;
		
	}
	
}