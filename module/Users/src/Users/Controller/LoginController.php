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

    public function processAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/login');
        }
        
        $post = $this->request->getPost();
        
        // validate and purify the html
        $util = new MyUtils();
        if (!$util->isValidatePassword($post->password)) {
        	throw new \Exception("the input password isn't validate");
        }
        $post = $util->purifyHtml($post);
        
        $form = $this->getServiceLocator()->get('LoginForm');
        
        $form->setData($post);
        if (! $form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form
            ));
            $model->setTemplate('users/login/index');
            return $model;
        } else {
            // check authentication...
            
            $this->getAuthService()
                ->getAdapter()
                ->setIdentity($this->request->getPost('email'))
                ->setCredential($this->request->getPost('password'));
            
            $result = $this->getAuthService()->authenticate();
            
            if ($result->isValid()) {
                
                // get the user id
                $userTable = $this->getServiceLocator()->get('UserTable');
                $user = $userTable->getUserByEmail($this->request->getPost('email'));
                
                $this->getAuthService()
                    ->getStorage()
                    ->write($user->id);
                
                return $this->redirect()->toRoute('users/login', array(
                    'action' => 'confirm'
                ));
            } else {
                $model = new ViewModel(array(
                    'error' => true,
                    'form' => $form
                ));
                $model->setTemplate('users/login/index');
                
                return $model;
            }
        }
    }

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
    
    /**
     * Return a confirm email and a randomPassword form
     * @return \Zend\View\Model\ViewModel
     */
    public function resetPasswordAction()
    {
        $form = $this->getServiceLocator()->get('ConfirmEmailForm');
        $randomPasswordForm = $this->getServiceLocator()->get('RandomPasswordForm');
        $viewModel = new ViewModel(array(
        	'form' => $form,
            'randomPasswordForm' => $randomPasswordForm,
        ));
        return $viewModel;
    }
    
    /**
     * generate a random password and send it to user's email box
     * 
     * @return \Zend\Stdlib\ResponseInterface
     */
    
    public function genRandomPasswordAction()
    {
        $post = $this->request->getPost();
        $email = $post->email;
        $message = "I am in genRand";
        
        $utils = new MyUtils();
        
        $flag = $utils->isValidateEmail($email);
        
        if ($flag) {
        	//gen a password 
        	//save the password to DB
        	//mail the password to mailbox
            $to = "l.yuhai@gmail.com";
            $subject = "Test mail";
            $message = "Hello! This is a simple email message.";
            $from = "l.yuhai@sap.com";
            $headers = "From: $from";
            mail($to,$subject,$message,$headers);
            $message = $message."Mail Sent from sap.";
        }else {
            $message = $message."It is not a validate email.";
        }
        
        $phpNative = array(
        	'message' => $message
        );
        
        $json = Json::encode($phpNative);
        
        // Directly return the Response
        $result = $json;
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
        
        throw new \Exception("I am in genRandomPassword.".$post->email);
    }
   
}
