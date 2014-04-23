<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Users\Tools\MyUtils;
use Zend\Json\Json;
use Users\Model\Orgnization;
use Zend\Paginator\Paginator;
use Users\Model\RequestJoin;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Validator\EmailAddress;

class HelperController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected $adapter;

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    public function getAdapter()
    {
        if (! $this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }
    
    public function getHelpersByOwnerAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        //Owner's email
        $emailValidation = new EmailAddress();
        $email = $emailValidation->isValid($_GET[email]) ? $_GET[email] : $email;
        
        //get helper by email
        try {
            $helpers = $this->getHelpersByEmail($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'message' => 'cant get helpers',
                'flag' => false,
            ));
        }
                
        //pagenate the result
        //return the helper 
        
        return $this->returnJson(array(
            'flag' => true,
            'helpers' => $helpers	
        ));  
    }
    
    /**
     * 
     * @param string $email
     * @return helpers array:
     */
    protected function getHelpersByEmail($email)
    {
        $sql = "select * from userInfo
            where email in
            (SELECT helper from relationship
            where owner = '$email'
            ) ORDER BY first_name";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        //push the result to a helpers array
        $helpers = array();
        foreach ($rows as $row){
            array_push($helpers, $row);
        }
        
        return $helpers;
    }
    
    /**
     * change array to Json response
     *
     * @param array $result
     * @return \Zend\Stdlib\ResponseInterface
     */
    protected function returnJson($result)
    {
    	$json = Json::encode($result);
    	$response = $this->getEvent()->getResponse();
    	$response->setContent($json);
    
    	return $response;
    }
    
}
