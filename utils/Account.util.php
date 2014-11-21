<?php
require_once "../../classes/DB.class.php";
require_once '../../libs/aes-sec/AES.php';

class AccountUtils {
	// Furnction used to check balance of the
	// email id mentioned in the argument
	public static function checkBalance ($emailId) {

		$isActive = "";
		$balance = "";

		$db = DB::getInstance();
		$db->connect();

		$return = $db->select("USERS", "emailId = '$emailId'");

		if (array_key_exists("isActive", $return)) {
			$isActive = $return["isActive"];
		}

		if ($isActive == 0) {
			return $balance;
		}
		else {
			$return = $db->select("ACCOUNTS", "userId = '$emailId'");
			return $return["balance"];
		}

	}

	// Furnction used to check balance of the
	// IBAN mentioned in the argument
	public static function checkBalanceIBAN ($iban) {

		$isActive = "";
		$balance = "";

		$db = DB::getInstance();
		$db->connect();

		$return = $db->select("ACCOUNTS", "accountNo = '$iban'");

		if (is_array($return) && isset($return["balance"]))
			return $return["balance"];

		return false;

	}

	// Used to check whether the user has right to 
	// access the tan provided for the transaction
	public static function checkTANValidity_old ($emailId, $tanNo) {

		$db = DB::getInstance();
		$db->connect();

		$sql = "SELECT * FROM TANS WHERE isActive = 1 AND userId = '$emailId' ORDER BY id ASC LIMIT 1";

		$row = mysql_fetch_object(mysql_query($sql));

		if ($tanNo != $row->no) {
			return false;
		}
		else
			return true;
	} 

	// Used to check whether the user has right to 
	// access the tan provided for the transaction
	public static function checkTANValidity ($emailId, $tanNo) {
		
		$imputText = $tanNo;
		$imputKey = $emailId;
		$blockSize = 256;

		$aes = new AES($imputText, $imputKey, $blockSize);

		$dec = $aes->decrypt();
		$subs = bcsub (self::num ($emailId), $dec);
		echo $subs;

		if (preg_match("/(\d+)/", $dec)) {
			if ($subs != self::num ($emailId))
				if (self::is_prime(bcsub (self::num ($emailId), $dec))) {
					return true;
				}
		}

		return false;
	}

	private static function num($text)
    {
	    $num = null;

	    for ($i = 0; $i < strlen($text); $i++)
	    {
	    	$num =$num.ord($text[$i]);
	    }

	    return ($num);
    }

	// Returns IBAN from Email ID
	public static function getIBANFromEmail ($email) {

		$db = DB::getInstance();
		$db->connect();

		$return = $db->select("ACCOUNTS", "userId = '$email'");

		if (is_array($return) && isset($return["accountNo"]))
			return $return["accountNo"];

		return false;

	}

	// Returns Email ID from IBAN
	public static function getEmailFromIBAN ($iban) {

		$db = DB::getInstance();
		$db->connect();

		$return = $db->select("ACCOUNTS", "accountNo = '$iban'");

		if (is_array($return) && isset($return["userId"]))
			return $return["userId"];

		return false;

	}

	private static function is_prime($number){
			if ($number < 0) {
				$number = abs ($number);
			}
			$limit = round(bcsqrt($number));
			
			$counter = 2;

			while ($counter <= $limit){

				if (bcmod($number, $counter) == 0){
					return true;
				}

				$counter ++;
			}
			return false;
		}
}

?>