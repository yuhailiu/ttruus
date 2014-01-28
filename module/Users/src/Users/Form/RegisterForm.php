<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class RegisterForm extends Form
{

    public function __construct($name = null)
    {
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
                'placeholder' => '姓氏(小于18个字符)'
            ),
            'options' => array(
                'label' => '姓氏'
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'first_name',
                'placeholder' => '名字(小于18个字符)',
                'autofocus' => 'autofocus'
            ),
            'options' => array(
                'label' => '名字'
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
                'label' => '我目前使用的电子邮箱'
            )
        ));
        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'password',
                'placeholder' => '密码(5到22个字符)'
            ),
            'options' => array(
                'label' => '建立密码'
            )
        ));
        
        $this->add(array(
            'name' => 'confirm_password',
            'attributes' => array(
                'type' => 'password',
                'required' => 'required',
                'class' => 'form-element',
                'placeholder' => '确认密码'
            ),
            'options' => array(
                'label' => '确认密码'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => '注册',
                'id' => 'submit-button'
            )
            
        ));
    }
}
