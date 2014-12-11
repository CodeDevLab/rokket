<?php

class server {

	protected $id;
	
	protected $sql;
	
	function __construct($id) {
		
		$sql = new sql();
		$sql->result("SELECT * FROM ".sql::table('server')." WHERE id = '".$id."'");
		
		$this->sql = $sql;
		
		$this->id = $this->sql->get('id');
		
	}
	
	public function createControl($data) {
		
		$id = $this->id;
		
		$dir = dir::backup("control/$id/");
		
		if(!is_dir($dir))
    		mkdir($dir);
		
		$file = fopen($dir.'control.sh', 'w');
	
		fwrite($file, $data);
		
		fclose($file);

	}
	
	public function create($array) {
		
		$id = $this->id;
		
		$SSH = rp::get('SSH');
		
		$host = $SSH['ip'];
		$user = $SSH['user'];
		$pass = $SSH['password'];
		
		unset($SSH);
		
		$sftp = new sftp($host, $user, $pass);
		
		$sftp->makedir((string)$id);
		$sftp->chdir((string)$id);
		
		$control = games::replaceControl($this->sql->get('gameID'), $array);
		
		$this->createControl($control);
		
		$dir = dir::backup("control/$id/");
		
		$sftp->put('control.sh', $dir.'control.sh', NET_SFTP_LOCAL_FILE);
		$sftp->chmod(0777, 'control.sh');
		
		return true;
		
	}
	
	public function install() {
		
		$id = $this->id;
		
		$SSH = rp::get('SSH');
		
		$host = $SSH['ip'];
		$user = $SSH['user'];
		$pass = $SSH['password'];
		
		unset($SSH);
		
		$ssh = new ssh($host, $user, $pass);
		
		return $ssh->read();
		
	}
	
	public static function deleteDir($id) {

		$SSH = rp::get('SSH');
		
		$host = $SSH['ip'];
		$user = $SSH['user'];
		$pass = $SSH['password'];
		
		unset($SSH);
		
		$sftp = new sftp($host, $user, $pass);
		
		$sftp->delete((string)$id, true);
		
		$dir = dir::backup("control/$id/");
		self::deleteLocalDir($dir);
		
	}
	
	public static function deleteLocalDir($path) {
		
		if (substr($path, strlen($path) - 1, 1) != '/') {
			$path .= '/';
		}
		$files = glob($path . '*', GLOB_MARK);
		
		foreach ($files as $file) {
			
			if (is_dir($file))
				self::deleteLocalDir($file);
			else
				unlink($file);
				
		}
		rmdir($path);
	}
	
}

?>