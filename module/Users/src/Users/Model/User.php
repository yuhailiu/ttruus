<?php
namespace Users\Model;

class User
{
    public $email;
    public $password;
    public $captcha;
    public $failedTimes;
    
    

    public function setPassword($clear_password)
    {
        $this->password = md5($clear_password);
    }

	function exchangeArray($data)
	{
		$this->email	= (isset($data['email'])) ? $data['email'] : null;
		$this->password	= (isset($data['password'])) ? $data['password'] : null;
		$this->captcha =  (isset($data['captcha'])) ? $data['captcha'] : null;
		$this->failedTimes =  (isset($data['failedTimes'])) ? $data['failedTimes'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
