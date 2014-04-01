<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ConfirmCaptchaForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('ConfirmCaptchaForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'captcha',
            'attributes' => array(
                'type'  => 'text',
				'required' => 'required',
                'id' => 'captcha',
                'class' => 'form-element',
                'placeholder' => $user_labels[captcha],
                'autofocus' => 'autofocus',
                'maxlength' => 6
            ),
            'options' => array(
                'label' => $user_labels[randomPassword],
            ),
        )); 
        
        $this->add(array(
        		'name' => 'submit',
        		'attributes' => array(
        				'type'  => 'submit',
        				'value' => $user_labels[nextStep],
        				'id' => 'captcha-submit',
        				'class' => 'rc-button rc-button-submit'
        		),
        ));
        
        
    }
}
