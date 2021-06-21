
<?php
/**
 * This is the FreePBX Big Module Object.
 *
 * This is a very basic interface to the existing 'module_functions' class.
 *
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright 2013-2021 Sangoma Technologies Inc.
 */
class ApiGqlHelper extends \FreePBX_Helpers {
	public function __construct($freepbx = null)
	{
		if ($freepbx == null) {
			throw new \Exception("Not given a FreePBX Object");
		}
		$this->freepbx = $freepbx;
	}

	public function execGqlApi($args) {

		$apiObj = $this->freepbx->Api();

		$module = strtolower($args[0]);
		$action = strtolower($args[1]);
		$track = $args[2];
		$txnId = $args[3];

		$bin =  $this->freepbx->Config()->get('AMPSBIN');
		if($module == 'upgradeall'){
			$action = $module;
			$txnId = $args[2];
			shell_exec($bin.'/fwconsole ma '.$action);
		} else {
			shell_exec($bin . '/fwconsole ma ' . $action . ' ' . $module . ' --' . $track);
		}

		/**
		 * Running below commands as root user
		 */
		$isSysAdminModuleInstalled = 0;
		if ($this->freepbx->Modules->checkStatus("sysadmin")) {
			$isSysAdminModuleInstalled = 1;
			 $this->freepbx->Sysadmin()->ApiHooks()->runModuleSystemHook('api', 'fwconsole-commands', array('chown', $txnId));
			 $this->freepbx->Sysadmin()->ApiHooks()->runModuleSystemHook('api', 'fwconsole-commands', array('reload', $txnId));
		}
	
		$result = shell_exec($bin."/fwconsole ma list|grep ".$module ."|awk '{print $5 $6}'");

		$reason = '';
		$enabled = array('enable','install','upgrade');

		if(!$isSysAdminModuleInstalled){
			$status = "Failed";
			$reason = "Sysadmin module has not been installed, hence failed to run the hook";
		}else if(in_array($action,$enabled) && $result ="|Enabled"){
			$status = "Executed";
		}else if($action == "disable" && $result ="|Disabled"){
			$status = "Executed";
		}else if($action == "uninstall" && $result ="NotInstalled"){
			$status = "Executed";
		}else if($action == "delete" && $result =""){
			$status = "Executed";
		}elseif($action == 'upgradeall'){
			$status = "Executed";
		}else{
			$status = "Failed";
			$reason = "Status could not be found";
		}

		$apiObj->setTransactionStatus($txnId,$status,$reason);
	}
}
