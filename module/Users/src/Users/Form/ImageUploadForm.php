<?php
// filename : module/Users/src/Users/Form/RegisterForm.php
namespace Users\Form;

use Zend\Form\Form;

class ImageUploadForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Upload');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'photoUploadForm');
        $this->setAttribute('enctype','multipart/form-data');
        
               
        $this->add(array(
            'name' => 'imageupload',
            'attributes' => array(
                'type'  => 'file',
                'id' => 'up',
            ),
        )); 
        
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => '提交更新',
                'id' => 'submit-button'
            ),
        )); 
    }
}
