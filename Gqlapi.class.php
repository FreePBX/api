<?php

//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013-2015 Sangoma Technologies Inc.
//
namespace FreePBX\modules;
include __DIR__."/vendor/autoload.php";

use FreePBX\modules\Gqlapi\includes\Graphqlapi;

class Gqlapi implements \BMO {
	private $classes = [];
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
	}

	/* Assorted stubs to validate the BMO Interface */
	public function install() {

	}

	public function uninstall() {

	}

	public function backup() {
	}
	public function restore($config) {
	}


	public function ajaxRequest($req, &$setting) {
		switch($req) {
			case "api":
				$setting['authenticate'] = false;
				$setting['allowremote'] = true;
				return true;
			break;
		}
	}

	public function ajaxHandler(){

	}

	public function ajaxCustomHandler() {
		switch($_REQUEST['command']) {
			case "api":
				$gql = new Graphqlapi($this->freepbx);
				$gql->execute();
				return true;
			break;
		}
		return false;
	}


}
