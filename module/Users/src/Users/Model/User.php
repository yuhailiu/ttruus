<?php
namespace Users\Model;

class User
{
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $filename;
    public $thumbnail;
    public $create_time;
    public $last_modify;
    public $sex;
    public $telephone1;
    public $telephone2;
    public $address;
    public $title;
    public $captcha;
    
    

    public function setPassword($clear_password)
    {
        $this->password = md5($clear_password);
    }

	function exchangeArray($data)
	{
		$this->id		= (isset($data['id'])) ? $data['id'] : null;
		$this->first_name		= (isset($data['first_name'])) ? $data['first_name'] : null;
		$this->last_name		= (isset($data['last_name'])) ? $data['last_name'] : null;
		$this->email	= (isset($data['email'])) ? $data['email'] : null;
		$this->password	= (isset($data['password'])) ? $data['password'] : null;
		$this->filename	= (isset($data['filename'])) ? $data['filename'] : 'defaultphoto.jpg';
		$this->thumbnail	= (isset($data['thumbnail'])) ? $data['thumbnail'] : 'defaultphoto.jpg';
		$this->create_time =  (isset($data['create_time'])) ? $data['create_time'] : null;
		$this->last_modify = (isset($data['last_modify'])) ? $data['last_modify'] : null;
		$this->sex	= (isset($data['sex'])) ? $data['sex'] : null;
		$this->telephone1 =  (isset($data['telephone1'])) ? $data['telephone1'] : null;
		$this->telephone2 =  (isset($data['telephone2'])) ? $data['telephone2'] : null;
		$this->address =  (isset($data['address'])) ? $data['address'] : null;
		$this->title =  (isset($data['title'])) ? $data['title'] : null;
		$this->captcha =  (isset($data['captcha'])) ? $data['captcha'] : null;
	}
	
	public function getArrayCopy()
	{
		return get_object_vars($this);
	}	
}
