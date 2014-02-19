<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;
use Users\Model\User;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Users\Form\UserForm;
use Users\Model\UserTable;
use Users\Model\UserInfo;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli;

class RegisterController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->getServiceLocator()->get('RegisterForm');
        $viewModel = new ViewModel(array(
            'form' => $form
        ));
        return $viewModel;
    }

    protected function returnResponse($result)
    {
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
    }

    /**
     * @return response true or false
     */
    public function processAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/register');
        }
        $post = $this->request->getPost();
        
        // get MyUtils instance
        $utils = new MyUtils();
        
        // validate the user name and password
        if (! $utils->isValidatePassword($post->password)) {
            return $this->returnResponse(false);
        }
        
        $flag_name = $utils->isValidateName($post['first_name']) ? $utils->isValidateName($post['last_name']) : false;
        
        $form = $this->getServiceLocator()->get('RegisterForm');
        
        $form->setData($post);
        
        // $email = $this->request->getPost('email');
        
        if (! $form->isValid() || ! $flag_name) {
            return $this->returnResponse(false);
        } else {
            //get Connection and begin a transaction
            //$dbConection = new Connection();
            //$dbConection->beginTransaction();
            
            // create user and init userInfo
            try {
                $this->createUser($form->getData());
                $this->initUserInfo($post->email);
                
                //write the firstname in session
                session_start();
                $_SESSION['username'] = $post->first_name;
                //commit the connection
                //$dbConection->commit();
            } catch (\Exception $e) {
                
                //rollback the transaction
                //$dbConection->rollback();
                MyUtils::writelog("error when register user" . $e);
                // return false
                return $this->returnResponse(false);
            }
            return $this->returnResponse(true);
        }
    }

    public function confirmAction()
    {
        $viewModel = new ViewModel();
        return $viewModel;
    }

    protected function createUser(array $data)
    {
        $user = new User();
        $user->exchangeArray($data);
        $user->setPassword($data['password']);
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userTable->saveUser($user);
        
        return true;
    }

    protected function initUserInfo($email)
    {
        //create a userInfo with email
        $userInfo = new UserInfo();
        $data = array(
            'email' => $email
        );
        $userInfo->exchangeArray($data);
        
        //save it to table
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        $userInfoTable->saveUserInfo($userInfo);
        return true;
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
                $result = "false";
            } catch (\Exception $e) {
                
                $result = "true";
            }
        } else {
            // email is invalid; return false the reasons
            $result = "true";
        }
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($result);
        
        return $response;
    }
}
