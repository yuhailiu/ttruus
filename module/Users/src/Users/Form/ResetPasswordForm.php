<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ResetPasswordForm extends Form
{
    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('ResetPasswordForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'password',
                'class' => 'form-element',
                'placeholder' => $user_labels[passwordPrompt],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $user_labels[password],
            ),
        )); 
        
        $this->add(array(
            'name' => 'confirmPassword',
            'attributes' => array(
                'type'  => 'password',
				'required' => 'required',
                'id' => 'confirmPassword',
                'class' => 'form-element',
                'placeholder' => $user_labels[passwordPrompt],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $user_labels[confirmPassword],
            ),
        )); 
        
        $this->add(array(
        		'name' => 'submit',
        		'attributes' => array(
        				'type'  => 'submit',
        				'value' => $user_labels[resetPassword],
        				'id' => 'resetPassword-submit',
        				'class' => 'rc-button rc-button-submit'
        		),
        ));
        
        
    }
}
