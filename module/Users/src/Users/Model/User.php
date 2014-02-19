<?php
namespace Users\Model;

class User
{
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $create_time;
//     public $last_modify;
//     public $sex;
//     public $telephone1;
//     public $telephone2;
//     public $address;
//     public $title;
    public $captcha;
    public $failedTimes;
    
    

    public function setPassword($clear_password)
    {
        $this->password = md5($clear_password);
    }

	function exchangeArray($data)
	{
		$this->first_name		= (isset($data['first_name'])) ? $data['first_name'] : null;
		$this->last_name		= (isset($data['last_name'])) ? $data['last_name'] : null;
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
