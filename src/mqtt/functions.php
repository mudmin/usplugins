<?php
if(!function_exists('mqtt')) {
	function mqtt($id,$topic,$message){
    err('calling this one');
		//id is the server id in the mqtt_settings.php
		$db = DB::getInstance();
		$query = $db->query("SELECT * FROM mqtt WHERE id = ?",array($id));
		$count=$query->count();
		if($count > 0){
			$server = $query->first();

			$host = $server->server;
			$port = $server->port;
			$username = $server->username;
			$password = $server->password;

			$mqtt = new phpMQTT($host, $port, "ClientID".rand());

			if ($mqtt->connect(true,NULL,$username,$password)) {
				$mqtt->publish($topic,$message, 0);
				$mqtt->close();
			}else{
				echo "Fail or time out";
			}
		}else{
			echo "Server not found. Please check your id.";
		}
	}
}
