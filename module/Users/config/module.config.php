<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      
 * @copyright Copyright (c) 2005-2013 Yuhai liu
 * @license    
 */
return array(
    'controllers' => array(
        'invokables' => array(
            'Users\Controller\Index' => 'Users\Controller\IndexController',
            'Users\Controller\Test' => 'Users\Controller\TestController',
            'Users\Controller\Login' => 'Users\Controller\LoginController',
            'Users\Controller\ResetPassword' => 'Users\Controller\ResetPasswordController',
            'Users\Controller\Register' => 'Users\Controller\RegisterController',
            'Users\Controller\Setting' => 'Users\Controller\SettingController',
            'Users\Controller\MediaManager' => 'Users\Controller\MediaManagerController',
            'Users\Controller\OrgManager' => 'Users\Controller\OrgManagerController',
            'Users\Controller\Task' => 'Users\Controller\TaskController',
            'Users\Controller\Target' => 'Users\Controller\TargetController',
            'Users\Controller\Helper' => 'Users\Controller\HelperController',
            'Users\Controller\Home' => 'Users\Controller\HomeController'
        )
    ),
    'router' => array(
        'routes' => array(
            'task' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/task[/:action]',
                    'constraints' => array(
                    		'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                )
            ),
            'target' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/target[/:action]',
                    'constraints' => array(
                    		'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Target',
                        'action' => 'index'
                    )
                )
            ),
            'helper' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/helper[/:action]',
                    'constraints' => array(
                    		'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Helper',
                        'action' => 'index'
                    )
                )
            ),
            'home' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/home[/:action]',
                    'constraints' => array(
                    		'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Home',
                        'action' => 'index'
                    )
                )
            ),
            'users' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/users',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Users\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                
                'may_terminate' => true,
                'child_routes' => array(
                    'test' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/test[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Test',
                                'action' => 'index'
                            )
                        )
                    ), // end of test
                    'login' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/login[/:action[/:tabs]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Login',
                                'action' => 'index'
                            )
                        )
                    ), // end of login
                    'resetPassword' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/resetPassword[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\ResetPassword',
                                'action' => 'index'
                            )
                        )
                    ), // end of login
                    'register' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/register[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Register',
                                'action' => 'index'
                            )
                        )
                    ), // end of register
                    'setting' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/setting[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\Setting',
                                'action' => 'index'
                            )
                        )
                    ), // end of setting
                    'orgmanager' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/orgmanager[/:action[/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\OrgManager',
                                'action' => 'index'
                            )
                        )
                    ), // end of setting
                    'upload-manager' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/upload-manager[/:action[/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\UploadManager',
                                'action' => 'index'
                            )
                        )
                    ), // end of upload-manager
                    'media' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/media[/:action[/:key[/:subaction]]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'subaction' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Users\Controller\MediaManager',
                                'action' => 'index'
                            )
                        )
                    ) // end of media
                                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Users' => __DIR__ . '/../view'
        ),
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/default-layout.phtml',
            'layout/myaccount' => __DIR__ . '/../view/layout/myaccount-layout.phtml',
            'layout/frame' => __DIR__ . '/../view/layout/frame-layout.phtml'
        )
    ),
    // MODULE CONFIGURATIONS
    'module_config' => array(
        'upload_location' => __DIR__ . '/../data/uploads',
        'image_upload_location' => __DIR__ . '/../data/images',
        'search_index' => __DIR__ . '/../data/search_index'
    )
);
