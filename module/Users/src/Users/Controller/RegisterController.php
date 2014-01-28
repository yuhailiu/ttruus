<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;
use Users\Model\User;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;

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

    public function processAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/register');
        }
        
        $post = $this->request->getPost();
        
        // purify the $post
        $purifyHtml = new MyUtils();
        $post = $purifyHtml->purifyHtml($post);
        
        // validate the user name
        $utils = new MyUtils();
        $flag_name = $utils->isValidateName($post['first_name']) ? $utils->isValidateName($post['last_name']) : false;
        
        $form = $this->getServiceLocator()->get('RegisterForm');
        
        $form->setData($post);
        
        // $email = $this->request->getPost('email');
        
        if (! $form->isValid() || ! $flag_name) {
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form
            ));
            $model->setTemplate('users/register/index');
            return $model;
        } else {
            $this->createUser($form->getData());
            return $this->redirect()->toRoute('users/register', array(
                'action' => 'confirm'
            ));
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
