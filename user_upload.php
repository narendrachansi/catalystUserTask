<?php 
class UploadCSVDataToDB{
	public $arguments;
	public $filename;
	public $dbUsername='root'; 
	public $dbPassword='';
	public $dbHostname='localhost';
	public $dbName='userDb';
	public $dbTableName='users';
	public $isDryRun;
	public $isFile;
	public $isCreateTable;
	public $isHelp;
	public $isUsername;
	public $isPassword;
	public $isHostname;
	public $userData;
	
	// constructor to set 
	 function __construct($argv) {
	    $this->arguments = $argv;
	 }	
	 
	 // read line from console
	function readInputFilename( $prompt = '' )
	{
	    echo $prompt;
	    return rtrim( fgets( STDIN ), "\n" );
	}
	
		// constructor to set 
	 function __construct($argv) {
	    $this->arguments = $argv;
	 }	
	 
	 // read line from console
	function readInputFilename( $prompt = '' )
	{
	    echo $prompt;
	    return rtrim( fgets( STDIN ), "\n" );
	}
	
	function getFilename(){
		return $this->filename;
	}
	
	function setFilename($file){
		$this->filename = $file;
	} 
	
	function getDbUsername(){
		return $this->dbUsername;
	}
	
	function setDbUsername($username){
		$this->dbUsername = $username;
	} 
	
	function getDbPassword(){
		return $this->dbPassword;
	}
	
	function setDbPassword($password){
		$this->dbPassword = $password;
	} 
	
	function getDbHostname(){
		return $this->dbHostname;
	}
	
	function setDbHostname($hostname){
		$this->dbHostname = $hostname;
	}  
} 
?>