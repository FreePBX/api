<?php

namespace FreePBX\modules\Api\Includes;

use PDO;

class TransactionStatus {
	public function __construct($database) {
		$this->database = $database;
	}

	public function add($status,$moduleName,$eventName) {
		$sql = "INSERT INTO api_asynchronous_transaction_history (`module_name`,`event_name`, `event_status`, `process_start_time`) VALUES (:module_name,:event_name, :event_status, :time)";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":module_name" => $moduleName,
			":event_name" => $eventName,
			":event_status" => $status,
			":time" => time()
		]);
		return $this->database->lastInsertId();
	}

	public function updateStatus($txnId, $status ,$failureReason) {
		$sql = "UPDATE IGNORE api_asynchronous_transaction_history SET event_status = :event_status , failure_reason =:failure_reason, process_end_time =:end_time WHERE `txn_id` = :txn_id";
		$sth = $this->database->prepare($sql);
		return $sth->execute([
			":event_status" => $status,
			":failure_reason" => $failure_reason,
			":end_time" => time(),
			":txn_id" => $txnId
		]);
	}

	public function get($txnId) {
		$sql = "SELECT * FROM api_asynchronous_transaction_history WHERE `txn_id` = :txn_id";
		$sth = $this->database->prepare($sql);
		$sth->execute([
			":txn_id" => $txnId,
		]);
		return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
