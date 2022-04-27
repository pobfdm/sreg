<?php
class reg 
{
	
	//--Edit to fit your needs
	var	$DSN =  'sqlite:users.sqlite' ;    
	var	$DBUSER = '';
	var $DBPASS = '';
	 
	//---Location credentials in db
	var $DBTABLE   = 'users';					// name table containing users
	var $FIELDUSER = 'user';					// name field containing the username
	var $FIELDPASS = 'pass';					// name field containing the password (cripted with md5sum)
	var $FIELDID   = 'id';						// name field containing the id
	
	
	var $sendMail=true;
	var $registered_OK = '<h2>Successfully registered</h2><p>check your email to complete the procedure.</p>';
	var $activate_OK = '<h2>Successfully activate</h2><p>your account is now active.</p>';
	
	var $registered_ERROR = '<h2>Error</h2><p>account creation procedure failed</p>';
	var $activate_ERROR = '<h2>Error</h2><p>account activation procedure failed.</p>';
	
	var $userExists_ERROR='<h2>Error</h2><p>choose another username please</p>';
	
	var $name_ERROR='<h2>Error</h2><p>error in your name</p>';
	var $user_ERROR='<h2>Error</h2><p>error in your email</p>';
	var $pass_ERROR='<h2>Error</h2><p>error in your password</p>';
	
	
	var $emailSubject="Complete your registration";
	var $emailBody="Click on the link below to complete the registration.";
	var $urlActivation='http://localhost:8080/sreg.php?regcode=';
	
	var $emailFrom='account@freemedialab.org';
	
	// DO NOT EDIT
	protected  $checkFields_ERROR='';
	
	public function __construct()
	{
		
	}
	
	public function do($h='',$b='',$f='')
	{
		$this->header($h);
		$this->body($b);
		$this->footer($f);
	}
	
	public function header($m='')
	{
		
		if ($m=='')
		{
			echo "<!DOCTYPE html>
			<html>
			<head>
				<title>Register</title>
			</head>
			<body>
			";
		}else{
			echo $m;
		}
		
	}
	public function footer($m='')
	{
		if ($m=='')
		{
			echo "</body><html>";
		}else{
			echo $m;
		}	
	}
	
	public function body($m='')
	{
		
		if (isset($_GET["regcode"]))
		{
			$REGCODE=$_GET["regcode"];
			$table=$this->DBTABLE;
			
			$sql =  " UPDATE $table set
				enable = 1 
				where regcode = :regcode ; " ; 											
				
			$db = new PDO($this->DSN, $this->DBUSER, $this->DBPASS);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $db->prepare($sql);
			$stmt->bindParam(":regcode",$REGCODE);
		 
			if (!$stmt->execute())
			{ 
				echo $this->activate_ERROR;
			}else{
				echo $this->activate_OK;	
			}
			return ;
		}
		
		
		
		
		
		if (isset($_POST["btnRegister"]))
		{
			if ($this->userExists($_POST["user"])==true)
			{
				echo $this->userExists_ERROR;
				return;
			}
			
			if ($this->checkFields($_POST["name"],$_POST["user"],$_POST["password"])==false)
			{
				echo $this->checkFields_ERROR;
				return ;	
			}
			
			
			try
			{
				$USER=$_POST["user"];
				$PASSWORD=md5($_POST["password"]);
				$str=rand();
				$REGCODE=sha1(date("F j, Y, g:i a").$str);
				$NAME=$_POST["name"];
				$table=$this->DBTABLE;
				
				$dbh = new PDO($this->DSN, $this->DBUSER, $this->DBPASS);
				$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$sql="INSERT INTO $table (id,name,user,pass,regcode,enable)VALUES (null,:NAME,:USER,:PASSWORD,:REGCODE,0); ";
				
				$stmt = $dbh->prepare($sql);
				$stmt->bindParam(":NAME",$NAME);
				$stmt->bindParam(":USER",$USER);
				$stmt->bindParam(":PASSWORD",$PASSWORD);
				$stmt->bindParam(":REGCODE",$REGCODE);
				
				if (! $stmt->execute() )
				{ 
					echo $this->registered_ERROR;
				}else{
					echo $this->registered_OK;
					$this-> sendMail($USER,$REGCODE);
				}
			}
		 
			catch (PDOException $myerror)
			{
				print "Database error: <br>" . $myerror->getMessage() . "<br/>";
			}
			
			
			return ;
		
		}
		
		
		
		if ($m=='')
		{
			echo '<form class="mx-1 mx-md-4" method="post" action="'.$_SERVER['SCRIPT_NAME'].'">

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="text" id="name" name="name" class="form-control" />
                      <label class="form-label" for="name">Your Name</label>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="email" id="user"  name="user" class="form-control" />
                      <label class="form-label" for="user">Your Email</label>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="password" id="password" name="password" class="form-control" />
                      <label class="form-label" for="password">Password</label>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-key fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="password" id="repassword" name="repassword" class="form-control" />
                      <label class="form-label" for="repassword">Repeat your password</label>
                    </div>
                  </div>

                  <div class="d-flex justify-content-center mx-4 mb-3 mb-lg-4">
                    <button type="submit" class="btn btn-primary btn-lg" name="btnRegister" id="btnRegister" >Register</button>
                  </div>
                </form>';
		}else{
			echo $m;
		}		
	}


	protected function sendMail($email,$regcode)
	{
		
		$from=$this->emailFrom;
		if ($this->sendMail)
		{
			
			$header = "From:$from \r\n";
			$header .= "MIME-Version: 1.0\r\n";
			$header .= "Content-type: text/html\r\n";
			
			
			$message=$this->emailBody . '<br><br><a href="'. $this->urlActivation . $regcode .'">'.$this->urlActivation . $regcode.'</a>' ;
			mail($email, $this->emailSubject, $message,$header);
		}
	}
	
	
	protected function checkEmail($str) 
	{
		if (filter_var($str, FILTER_VALIDATE_EMAIL)) 
		{
		  return true;
		}else{
		  return false;
	   }
   
   }
	
	protected function checkFields($name, $user, $pass)
	{
		if (strlen($name)<=4)
		{
			$this->checkFields_ERROR=$this->checkFields_ERROR . $this->name_ERROR;
			return false;
		}
		
		if (strlen($pass)<=5)
		{
			$this->checkFields_ERROR=$this->checkFields_ERROR . $this->pass_ERROR;
			return false;
		}
		
		if (!$this->checkEmail($user))
		{
			$this->checkFields_ERROR=$this->checkFields_ERROR . $this->user_ERROR;
			return false;
		}
			
		return true;
	}
	
	protected function userExists($user)
	{
		$table=$this->DBTABLE;
		$sql =  " Select user from $table where user = :USER ; " ; 											
			
		$db = new PDO($this->DSN, $this->DBUSER, $this->DBPASS);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":USER", $user);
		$stmt->execute();
		
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if( ! $row)
		{
			return false; 
		}
			return true;
		}

}


?>
