<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class LoginForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('Login');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'email',
				'required' => 'required',
                'id' => 'Email',
                'placeholder' => $user_labels[email],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $user_labels[email],
            ),
        )); 
        
	   $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'Passwd',
                'placeholder' => $user_labels[password]
            ),
            'options' => array(
                'label' => $user_labels[password],
            ),
        )); 


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $user_labels[login],
                'id' => 'submit-button',
                'class' => 'rc-button rc-button-submit'
            ),
        )); 
    }
}
