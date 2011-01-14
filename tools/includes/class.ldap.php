<?php
class Tool_LDAP {
	private $host = "ldap.case.edu";
	private $conn;
	private $resource;
	public function __construct() {
		$this->conn = ldap_connect($this->host) or die("Could not connect to {$ldaphost}");
		$this->resource = ldap_bind($this->conn);	
	}
	public function getValuesFromCas($cas) {
		$result = ldap_search($this->conn,"ou=People,o=cwru.edu,o=isp","uid=".$cas);
		if($entries = ldap_first_entry($this->conn, $result)) {
			$firstName = ldap_get_values($this->conn, $entries, "givenName");
			$surname = ldap_get_values($this->conn, $entries, "SN");
			$mail = ldap_get_values($this->conn, $entries, "mail");
			ldap_free_result($result);
			$return['firstName'] = $firstName[0];
			$return['lastName'] = $surname[0];
			$return['mail'] = $mail[0];
			return $return;
		} else {
			return false;
		}
	}
}
?>