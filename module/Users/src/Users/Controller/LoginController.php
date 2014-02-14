<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Users\Form\LoginForm;
use Users\Form\LoginFilter;
use Users\Model\User;
use Users\Model\UserTable;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Json\Json;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class LoginController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    public function logoutAction()
    {
        $this->getAuthService()->clearIdentity();
        
        return $this->redirect()->toRoute('users/login');
    }

    public function indexAction()
    {
        $form = $this->getServiceLocator()->get('LoginForm');
        $viewModel = new ViewModel(array(
            'form' => $form
        ));
        return $viewModel;
    }

    /**
     * fail login
     *
     * @return Response false
     */
    protected function returnIndexError()
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent(false);
        
        return $response;
    }
    
    /**
     * success login
     * 
     * @return Reponse true
     */
    protected function returnLoginSuccess()
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent(true);
        
        return $response;
    }

    /**
     * check password is match email in usertable
     *
     * @param string $password            
     * @param string $email            
     * @return boolean
     */
    protected function isValidateUser($password, $email)
    {
        // check it in the DB
        // get the user id
        $userTable = $this->getServiceLocator()->get('UserTable');
        try {
            $user = $userTable->getUserByEmail($email);
            if ($user->password == md5($password)) {
                // write it in AuthService
                $this->getAuthService()
                    ->getStorage()
                    ->write($user->id);
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            //write a error log
            MyUtils::writelog("error at isValidateUser--".$e);
            return false;
        }
    }

    /**
     * get password and email from page
     *
     * @return login page with error if false
     * @return confirm page with user id
     */
    public function processAction()
    {
        //sleep 10 seconds
        //sleep(10);
        
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/login');
        }
        
        $post = $this->request->getPost();
        $form = $this->getServiceLocator()->get('LoginForm');
        
        // validate the passoword
        if (! MyUtils::isValidatePassword($post->password)) {
            return $this->returnIndexError();
        }
        
        // validate login form
        $form->setData($post);
        if ($form->isValid()) {
            
            // Is validate users
            if ($this->isValidateUser($post->password, $post->email)) {
                // return to success 
                return $this->returnLoginSuccess();
            } else {
                return $this->returnIndexError();
            }
        } else {
            return $this->returnIndexError();
        }
    }

    /**
     *
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|\Zend\View\Model\ViewModel
     */
    public function confirmAction()
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        
        $user_id = (int) $this->getAuthService()
            ->getStorage()
            ->read();
        
        // check empty and verify
        if (! $user_id) {
            return $this->redirect()->toRoute('users/login');
        }
        
        // if can not get the user redirect to login page
        try {
            $user = $userTable->getUser($user_id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('users/login');
        }
        
        // if can't get org place null to it
        try {
            $org = $orgTable->getOrgnizationByCreaterId($user->id);
        } catch (\Exception $e) {
            $org = null;
        }
        $this->layout('layout/myaccount');
        $viewModel = new ViewModel(array(
            'user' => $user,
            'org' => $org
        ));
        return $viewModel;
    }

    public function checkEmailAction()
    {
        $email = $_GET['email'];
        
        // validate the email address
        $validator = new EmailAddress();
        if ($validator->isValid($email)) {
            $userTable = $this->getServiceLocator()->get('UserTable');
            
            try {
                $userTable->getUserByEmail($email);
                $result = "true";
            } catch (\Exception $e) {
                
                $result = "false";
            }
        } else {
            // email is invalid; return false the reasons
            $result = "false";
        }
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
    }
}
