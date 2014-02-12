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
use Zend\Validator\Regex;
use Users\Tools\MyUtils;
use Users\src\Users\Controller\Test;
use Users\src\Users\Controller\MyTest1;
use Users\Model\MyTest;
use Users\Form\UserForm;

class SettingController extends AbstractActionController
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

    public function indexAction()
    {
        $this->layout('layout/myaccount');
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        $user_id = (int) $this->getAuthService()
            ->getStorage()
            ->read();
        
        // check empty and verify
        if (! $user_id) {
            return $this->redirect()->toRoute('users/login');
        }
        $user = $userTable->getUser($user_id);
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $userSetForm = $this->getServiceLocator()->get('UserSetForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'form' => $form,
            'userSetForm' => $userSetForm
        ));
        return $viewModel;
    }

    public function changePasswordAction()
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        $user_id = (int) $this->getAuthService()
            ->getStorage()
            ->read();
        
        // check empty and verify
        if (! $user_id) {
            return $this->redirect()->toRoute('users/login');
        }
        $user = $userTable->getUser($user_id);
        $form = $this->getServiceLocator()->get('ChangePasswordForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
        return $viewModel;
    }
    
    /**
     * change the password when the user can provide the old password
     * 
     * @return confirm page if success or back when false
     */
    public function processPasswordAction()
    {
        $post = $this->request->getPost();
        
        //Is validate password?
        $util = new MyUtils();
        if (!$util->isValidatePassword($post->password)) {
        	return $this->showError("the new password isn't validate");
        }
        
        // Is new password same as the confirm password?
        if ($post->password != $post->confirm_password) {
            return $this->showError("the passwords are not same");
        }
        
        // Is validate form?
        $form = $this->getServiceLocator()->get('ChangePasswordForm');
        $form->setData($post);
        if (! $form->isValid()) {
            $user = new User();
            $user->id = $post->id;
            $model = new ViewModel(array(
                'error' => true,
                'form' => $form,
                'user' => $user
            ));
            $model->setTemplate('users/setting/change-password');
            return $model;
        } else {
            
            // Purify html
            $post = $util->purifyHtml($post);
            
            $id = (int) $post->id;
            
            // get the relative table and form
            $userTable = $this->getServiceLocator()->get('UserTable');
            $user = $userTable->getUser($id);
            
            // compare the old password, if false return to error
            $old_password = md5($post->old_password);
            if ($user->password != $old_password) {
                return $this->showError('old password isn\'t right');
            } else {
                
                try {
                    //Update the password by id
                    $userTable->updatePasswordById($id, $post->password);
                    return $this->redirect()->toRoute('users/setting', array(
                        'action' => 'passwordConfirm'
                    ));
                } catch (\Exception $e) {
                    return $this->showError('error when update DB');
                }
            }
        }
    }

    protected function showError($message)
    {
        $model = new ViewModel(array(
            'error' => true,
            'message' => $message
        // 'user' => $user
                ));
        $model->setTemplate('users/utils/error');
        return $model;
    }

    public function processAction()
    {
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/setting');
        }
        
        $post = $this->request->getPost();
        
        // Purify html
        $purifyHtml = new MyUtils();
        $post = $purifyHtml->purifyHtml($post);
        
        $id = (int) $post->id;
        
        // get the relative table and form
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUser($id);
        
        $form = $this->getServiceLocator()->get('UserSetForm');
        
        $form->setData($post);
        
        // validate the telephone no
        $flag = $this->validateTel($post->telephone1) ? $this->validateTel($post->telephone2) : false;
        
        // validate the user name
        $utils = new MyUtils();
        $flag_name = $utils->isValidateName($post['first_name']) ? $utils->isValidateName($post['last_name']) : false;
        $flag_address = $utils->isValidateAddress($post['address']);
        
        // throw new \Exception("from setting process--tel--".$flag."--name--".$flag_name."--address--".$flag_address);
        
        if (! $form->isValid() || ! $flag || ! $flag_name || ! $flag_address) {
            
            $model = new ViewModel(array(
                'error' => true,
                'user' => $user
            ));
            $model->setTemplate('users/utils/error');
            return $model;
        }
        // $data = $form->getData();
        // exchange data
        $user = $this->exchangeArray($user, $post);
        
        // Save user
        $this->getServiceLocator()
            ->get('UserTable')
            ->saveUser($user);
        
        return $this->redirect()->toRoute('users/login', array(
            'action' => 'confirm'
        ));
    }

    protected function validateTel($tel)
    {
        $flag = true;
        
        if ($tel) {
            $validator = new Regex(array(
                'pattern' => '/(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/'
            ));
            $flag = $validator->isValid($tel);
        }
        
        return $flag;
    }

    /**
     * if has new property in data update the user relative otherwise keep user's original
     *
     * @param User $user            
     * @param Array $data            
     * @return User
     */
    protected function exchangeArray($user, $data)
    {
        $user->id = (isset($data['id'])) ? $data['id'] : $user->id;
        $user->first_name = (isset($data['first_name'])) ? $data['first_name'] : $user->first_name;
        $user->last_name = (isset($data['last_name'])) ? $data['last_name'] : $user->last_name;
        $user->email = (isset($data['email'])) ? $data['email'] : $user->email;
        $user->password = (isset($data['password'])) ? $data['password'] : $user->password;
        $user->filename = (isset($data['filename'])) ? $data['filename'] : $user->filename;
        $user->thumbnail = (isset($data['thumbnail'])) ? $data['thumbnail'] : $user->thumbnail;
        $user->create_time = (isset($data['create_time'])) ? $data['create_time'] : $user->create_time;
        $user->last_modify = (isset($data['last_modify'])) ? $data['last_modify'] : $user->last_modify;
        $user->sex = (isset($data['sex'])) ? $data['sex'] : $user->sex;
        $user->telephone1 = (isset($data['telephone1'])) ? $data['telephone1'] : $user->telephone1;
        $user->telephone2 = (isset($data['telephone2'])) ? $data['telephone2'] : $user->telephone2;
        $user->address = (isset($data['address'])) ? $data['address'] : $user->address;
        $user->title = (isset($data['title'])) ? $data['title'] : $user->title;
        
        return $user;
    }

    /**
     * get user by the authorized id
     *
     * @return User
     */
    protected function getUser()
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        $user_id = (int) $this->getAuthService()
            ->getStorage()
            ->read();
        
        // check empty and verify
        if (! $user_id) {
            return $this->redirect()->toRoute('users/login');
        }
        $user = $userTable->getUser($user_id);
        return $user;
    }
    
    /**
     * If setting is successful, show confirm message and 
     * close the window in 5 seconds
     * @return \Zend\View\Model\ViewModel
     */
    public function passwordConfirmAction()
    {
        $user = $this->getUser();
        $viewModel = new ViewModel(array(
            'user' => $user
        ));
        return $viewModel;
    }
}

