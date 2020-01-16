<?php
class UploadCSVDataToDB {
	public $arguments;
	public $filename;
	public $dbUsername = 'root';
	public $dbPassword = '';
	public $dbHostname = 'localhost';
	public $dbName = 'userDb';
	public $dbTableName = 'users';
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
		$this -> arguments = $argv;
	}

	// read line from console
	function readInputFilename($prompt = '') {
		echo $prompt;
		return rtrim(fgets(STDIN), "\n");
	}

	function getFilename() {
		return $this -> filename;
	}

	function setFilename($file) {
		$this -> filename = $file;
	}

	function getDbUsername() {
		return $this -> dbUsername;
	}

	function setDbUsername($username) {
		$this -> dbUsername = $username;
	}

	function getDbPassword() {
		return $this -> dbPassword;
	}

	function setDbPassword($password) {
		$this -> dbPassword = $password;
	}

	function getDbHostname() {
		return $this -> dbHostname;
	}

	function setDbHostname($hostname) {
		$this -> dbHostname = $hostname;
	}

	function Upload() {
		// base path
		define('BASE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
		in_array('--dry_run', $this -> arguments) ? $this -> isDryRun = true : $this -> isDryRun = false;
		in_array('--create_table', $this -> arguments) ? $this -> isCreateTable = true : $this -> isCreateTable = false;
		in_array('--help', $this -> arguments) ? $this -> isHelp = true : $this -> isHelp = false;
		in_array('--file', $this -> arguments) ? $this -> isFile = true : $this -> isFile = false;
		in_array('-u', $this -> arguments) ? $this -> isUsername = true : $this -> isUsername = false;
		in_array('-p', $this -> arguments) ? $this -> isPassword = true : $this -> isPassword = false;
		in_array('-h', $this -> arguments) ? $this -> isHostname = true : $this -> isHostname = false;
		if (count($this -> arguments) < 1) {
			echo "This script will only run on command line. Please try in command line to run the script.";
			exit ;
		}

		if ($this -> isHelp) {
			$fw = fopen("php://stdout", "w");
			fprintf($fw, "--file [csv name] %s%s%s%s%s specifies the csv filename that is to be parsed \n", " ", " ", " ", " ", " ");
			fprintf($fw, "--create_table %s%s%s%s%s%s%s%s MYSQL users table is created under database task \n", " ", " ", " ", " ", " ", " ", " ", " ");
			fprintf($fw, "--dry_run %s%s%s%s%s%s%s%s%s%s%s%s%s runs script without inserting any data \n", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");
			fprintf($fw, "--u %s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s specifies username to connect database \n", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");
			fprintf($fw, "--p %s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s specifies password to connect database \n", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");
			fprintf($fw, "--h %s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s%s specifies hostname to connect database \n", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ");
			fclose($fw);
			exit ;
		}

		if ($this -> isFile) {
			/** Set filename from the arguments **/
			$getKey = array_keys($this -> arguments, '--file');
			$this -> setFilename(BASE_PATH . str_replace(array('[', ']'), '', $this -> arguments[++$getKey[0]]));
		} else {
			// input file name
			$this -> setFilename(BASE_PATH . trim($this -> readInputFilename("Enter Users Data file name :")));
		}

		if ($this -> isUsername) {
			/** Set username from the arguments **/
			$getKey = array_keys($this -> arguments, '-u');
			$this -> setDbUsername($this -> arguments[++$getKey[0]]);
		}

		if ($this -> isPassword) {
			/** Set password from the arguments **/
			$getKey = array_keys($this -> arguments, '-p');
			$this -> setDbPassword($this -> arguments[++$getKey[0]]);
		}

		if ($this -> isHostname) {
			/** Set hostname from the arguments **/
			$getKey = array_keys($this -> arguments, '-h');
			$this -> setDbHostname($this -> arguments[++$getKey[0]]);
		}

		// read data from given file
		if (file_exists($this -> getFilename())) {
			$this -> userData = array_map('str_getcsv', file($this -> getFilename()));
			if ($this -> getDbUsername() && $this -> getDbHostname()) {
				$conn = new mysqli($this -> getDbHostname(), $this -> getDbUsername(), $this -> getDbPassword());
				// Check connection
				if ($conn -> connect_error) {
					die("Connection failed: " . $conn -> connect_error);
				}
				if ($this -> isCreateTable) {
					$db_selected = mysqli_select_db($conn, $this -> dbName);
					if (!$db_selected) {
						if (!$this -> isDryRun) {
							$sql = "CREATE DATABASE {$this->dbName}";
							if ($conn -> query($sql) === TRUE) {
								echo "{$this->dbName} database created successfully \n";
							} else {
								die("Error creating database: " . $conn -> error);
							}
							$sql = "CREATE TABLE " . $this -> dbName . "." . $this -> dbTableName . " (
										id INT(6) AUTO_INCREMENT PRIMARY KEY,
										name VARCHAR(30) NOT NULL,
										surname VARCHAR(30) NOT NULL,
										email VARCHAR(50),
										CONSTRAINT constraint_name UNIQUE (email)
										)";
							if ($conn -> query($sql) === TRUE) {
								echo "{$this->dbTableName} table created successfully \n";
							} else {
								die("Error creating table: " . $conn -> error . "\n");
							}
						}
					}
				}
				foreach ($this->userData as $key => $users) {
					if ($key == 0)
						continue;
					if (!filter_var($users[2], FILTER_VALIDATE_EMAIL)) {
						if (!$this -> isDryRun) {
							fwrite(STDOUT, $users[2] . " is not a valid email. Therefore it hasn't been inserted into database. \n");
						} else {
							fwrite(STDOUT, $users[2] . " is not a valid email. \n");
						}
					} else {
						if (!$this -> isDryRun) {
							$username = mysqli_real_escape_string($conn, ucwords(strtolower($users[0])));
							$password = mysqli_real_escape_string($conn, ucwords(strtolower($users[1])));
							$email = mysqli_real_escape_string($conn, strtolower($users[2]));
							$sql = "INSERT INTO " . $this -> dbName . "." . $this -> dbTableName . " (name, surname, email)
									VALUES ('" . $username . "', '" . $password . "', '" . $email . "')";
							if ($conn -> query($sql) === TRUE) {
								fwrite(STDOUT, "{$users[2]} is a valid email. Therefore it has been inserted into database. \n");
							} else {
								echo "Error inserting data: " . $conn -> error . "\n";
							}
						} else {
							fwrite(STDOUT, "Name: " . ucwords(strtolower($users[0])) . "\n");
							fwrite(STDOUT, "Surname: " . ucwords(strtolower($users[1])) . "\n");
							fwrite(STDOUT, $users[2] . " is a valid email. \n\n");
						}
					}
				}
				$conn -> close();
			}
		} else {
			exit($this -> getFilename() . " file not found! Please enter correct file and try again.");
		}
	}

}

if (isset($argv)) {
	$obj = new UploadCSVDataToDB($argv);
	$obj -> Upload();
} else {
	exit("This script will only run on command line. Please try in command line to run the script.");
}
