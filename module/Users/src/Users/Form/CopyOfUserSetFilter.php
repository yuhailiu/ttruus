<?php
/* filename : module/Users/src/Users/Form/UserSetFilter.php
 * create by Yuhai at 2014-1-21
*/
namespace Users\Form;

use Zend\InputFilter\InputFilter;

class UserSetFilter extends InputFilter
{

    public function __construct()
    {
        $this->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'EmailAddress',
                    'options' => array(
                        'domain' => true
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'last_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 40
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'first_name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags'
                )
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'max' => 40
                    )
                )
            )
        ));
        
        //not required fields
        $this->add(array(
        		'name' => 'telephone1',
        		'filters' => array(
        				array(
        						'name' => 'StripTags'
        				)
        		),
        		'validators' => array(
        				array(
        						'name' => 'StringLength',
        						'options' => array(
        								'encoding' => 'UTF-8',
        								'max' =>20
        						)
        				)
        		)
        ));
        $this->add(array(
        		'name' => 'telephone2',
        		'filters' => array(
        				array(
        						'name' => 'StripTags'
        				)
        		),
        		'validators' => array(
        				array(
        						'name' => 'StringLength',
        						'options' => array(
        								'encoding' => 'UTF-8',
        								'max' =>20
        						)
        				)
        		)
        ));
        $this->add(array(
        		'name' => 'address',
        		'filters' => array(
        				array(
        						'name' => 'StripTags'
        				)
        		),
        		'validators' => array(
        				array(
        						'name' => 'StringLength',
        						'options' => array(
        								'encoding' => 'UTF-8',
        								'max' => 200
        						)
        				)
        		)
        ));
        $this->add(array(
        		'name' => 'title',
        		'filters' => array(
        				array(
        						'name' => 'StripTags'
        				)
        		),
        		'validators' => array(
        				array(
        						'name' => 'StringLength',
        						'options' => array(
        								'encoding' => 'UTF-8',
        								'max' => 80
        						)
        				)
        		)
        ));
        
    }
}
