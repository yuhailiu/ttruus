<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      
 * @copyright Yuhai Liu
 * @license   
 */

namespace Users;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Users\Model\User;
use Users\Model\UserTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;
use Users\Model\OrgnizationTable;
use Users\Model\Orgnization;


class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
      
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $sharedEventManager = $eventManager->getSharedManager(); // The shared event manager
        
        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, function($e) {
        	$controller = $e->getTarget(); // The controller which is dispatched
        	$controllerName = $controller->getEvent()->getRouteMatch()->getParam('controller');
        
        	if (!in_array($controllerName, array('Users\Controller\Index', 'Users\Controller\Register', 'Users\Controller\Login'))) {
        		$controller->layout('layout/myaccount');
        	}
        });
        
    }
    
    public function getServiceConfig()
    {
        return array(
    			'abstract_factories' => array(),
    			'aliases' => array(),
    			'factories' => array(
    			    // SERVICES
    			    'AuthService' => function($sm) {
    			    	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    			    	$dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'users','email','password', 'MD5(?)');
    			    		
    			    	$authService = new AuthenticationService();
    			    	$authService->setAdapter($dbTableAuthAdapter);
    			    	return $authService;
    			    },
    			    
    			    // DB
    			    'UserTable' =>  function($sm) {
    			    	$tableGateway = $sm->get('UserTableGateway');
    			    	$table = new UserTable($tableGateway);
    			    	return $table;
    			    },
    			    'UserTableGateway' => function ($sm) {
    			    	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    			    	$resultSetPrototype = new ResultSet();
    			    	$resultSetPrototype->setArrayObjectPrototype(new User());
    			    	return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
    			    },
    			    'OrgnizationTable' => function ($sm) {
    			        $tableGateway = $sm->get('OrgnizationGateway');
    			    	$table = new OrgnizationTable($tableGateway);
    			    	return $table;   			        
    			    },
    			    'OrgnizationGateway' => function ($sm) {
    			    	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    			    	$resultSetPrototype = new ResultSet();
    			    	$resultSetPrototype->setArrayObjectPrototype(new Orgnization());
    			    	return new TableGateway('orgnization', $dbAdapter, null, $resultSetPrototype);
    			    },
    				
    						    				
    				// FORMS
    				'LoginForm' => function ($sm) {
    					$form = new \Users\Form\LoginForm();
    					$form->setInputFilter($sm->get('LoginFilter'));
    					return $form;
    				}, 
    				'RegisterForm' => function ($sm) {
    					$form = new \Users\Form\RegisterForm();
    					$form->setInputFilter($sm->get('RegisterFilter'));
    					return $form;
    				},
    				'ImageUploadForm' => function ($sm) {
    					$form = new \Users\Form\ImageUploadForm();
    					$form->setInputFilter($sm->get('ImageUploadFilter'));
    					return $form;
    				},
    				'UserSetForm' => function ($sm) {
    					$form = new \Users\Form\UserSetForm();
    					$form->setInputFilter($sm->get('UserSetFilter'));
    					return $form;
    				},
    				
    				// FILTERS
    				'LoginFilter' => function ($sm) {
    					return new \Users\Form\LoginFilter();
    				},
    				'RegisterFilter' => function ($sm) {
    					return new \Users\Form\RegisterFilter();
    						
    				},
    				'ImageUploadFilter' => function ($sm) {
    					return new \Users\Form\ImageUploadFilter();
    				},
    				'UserSetFilter' => function ($sm) {
    					return new \Users\Form\UserSetFilter();
    				},
    				
    				
    			),
    			'invokables' => array(),
    			'services' => array(),
    			'shared' => array(),
    	);
    }
}
