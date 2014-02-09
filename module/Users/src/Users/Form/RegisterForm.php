<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{

    public function __construct($name = null)
    {
        //get the labal information
        require 'module/Users/view/users/utils/user_label.php';
        
        parent::__construct('registerForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'last_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'last_name',
                'placeholder' => $user_labels[lastNamePrompt]
            ),
            'options' => array(
                'label' => $user_labels[lastName]
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'first_name',
                'placeholder' => $user_labels[firstNamePrompt],
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => $user_labels[firstName]
            )
        ));
        
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'email',
                'placeholder' => '...@ttruus.com'
            ),
            'options' => array(
                'label' => $user_labels[email]
            )
        ));
        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'password',
                'placeholder' => $user_labels[passwordPrompt]
            ),
            'options' => array(
                'label' => $user_labels[password]
            )
        ));
        
        $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'placeholder' => $user_labels[confirmPassword]
            ),
            'options' => array(
                'label' => $user_labels[confirmPassword]
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => $user_labels[register],
                'id' => 'submit-button'
            )
            
        ));
    }
}
