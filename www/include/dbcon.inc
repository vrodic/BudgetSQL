<?
        if (!pg_pconnect("dbname=budget user=budget")) die ("Db connection failed");
	//pg_set_client_encoding ("LATIN2");
	pg_set_client_encoding ("UTF8");
	
	
	function valtosql($val) {
		$val = trim($val);
		if ($val == "") {
			$ret = "NULL";
		} else {
			$ret ="'$val'";
		}
		return $ret;
	}
	
	
?>