<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Login');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'email',
				'required' => 'required',
                'id' => 'Email',
                'placeholder' => '电子邮件',
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => 'Email',
            ),
        )); 
        
	   $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'Passwd',
                'placeholder' => '密码'
            ),
            'options' => array(
                'label' => 'Password',
            ),
        )); 


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => '登陆',
                'id' => 'submit-button',
                'class' => 'rc-button rc-button-submit'
            ),
        )); 
    }
}
