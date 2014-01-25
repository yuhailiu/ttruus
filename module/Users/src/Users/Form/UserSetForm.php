<?php
/*
 * filename : module/Users/src/Users/Form/UserSetForm.php create by Yuhai at 2014-1-21
 */
namespace Users\Form;

use Zend\Form\Form;

class UserSetForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('registerForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
        		'name' => 'id',
        		'attributes' => array(
        				'type' => 'text',
        				'required' => 'required',
        				'id' => 'user_id_value'
        		),
        ));
        
        $this->add(array(
            'name' => 'last_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'last_name'
            ),
            'options' => array(
                'label' => '*姓氏：'
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'attributes' => array(
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-element',
                'id' => 'first_name'
            ),
            'options' => array(
                'label' => '*名字：'
            )
        ));
        
        $this->add(array(
            'name' => 'sex',
            'attributes' => array(
                'type' => 'text',
                'id' => 'sex'
            ),
            'options' => array(
                'label' => '性别：'
            )
        ));
        $this->add(array(
            'name' => 'telephone1',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'telephone1',
                'placeholder' => '例如：139010888xx'
            ),
            'options' => array(
                'label' => '电话1：'
            )
        ));
        $this->add(array(
            'name' => 'telephone2',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'telephone2',
                'placeholder' => '例如：010-656588xx'
            ),
            'options' => array(
                'label' => '电话2：'
            )
        ));
        $this->add(array(
            'name' => 'address',
            'attributes' => array(
                'type' => 'textarea',
                'class' => 'form-element',
                'id' => 'address',
                'rows' => 8,
                'cols' => 60,
                'placeholder' => '例如：北京市 朝阳区 xx大厦 100号, 邮编：1000xx'
                
            ),
            'options' => array(
                'label' => '地址：'
            )
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-element',
                'id' => 'title',
                'placeholder' => '例如：项目经理'
            ),
            'options' => array(
                'label' => '职位：'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => '保存',
                'id' => 'submit-button',
                'style' => 'width: 100px;'
            )
            
        ));
    }
}
