<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Model\User;
use Zend\Validator\Regex;
use Users\Tools\MyUtils;
use Zend\Json\Json;

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
        // $this->layout('layout/myaccount');
        // check is it a league user, false return to login page
        $email = $this->getAuthService()
            ->getStorage()
            ->read();
        // check empty and verify
        if (! $email) {
            return $this->redirect()->toRoute('users/login');
        }
        
        // get user and user info by email
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try {
            $user = $userTable->getUserByEmail($email);
            $userInfo = $userInfoTable->getUserInfoByEmail($email);
        } catch (\Exception $e) {
            MyUtils::writelog("can't get user or info in setting controller" . $e);
            return $this->redirect()->toRoute('users/setting');
        }
        
        // get image upload form
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        // get user info set form
        $userSetForm = $this->getServiceLocator()->get('UserSetForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'userInfo' => $userInfo,
            'form' => $form,
            'userSetForm' => $userSetForm
        ));
        return $viewModel;
    }

    public function changePasswordAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByEmail($email);
        $form = $this->getServiceLocator()->get('ChangePasswordForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
        return $viewModel;
    }

    /**
     * change an array to Json and return response
     *
     * @param array $array            
     * @return \Zend\Stdlib\ResponseInterface
     */
    protected function returnJson($result)
    {
        $json = Json::encode($result);
        $response = $this->getEvent()->getResponse();
        $response->setContent($json);
        
        return $response;
    }

    /**
     * change the password when the user can provide the old password
     *
     * @return confirm page if success or back when false
     */
    public function processPasswordAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $post = $this->request->getPost();
        
        // Is validate password?
        $util = new MyUtils();
        if (! $util->isValidatePassword($post->password)) {
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
            $result = array(
                'flag' => false,
                'message' => "the form is invalidate"
            );
            return $this->returnJson($result);
        } else {
            
            // get the relative table and form
            $userTable = $this->getServiceLocator()->get('UserTable');
            $user = $userTable->getUserByEmail($email);
            
            // compare the old password, if false return to error
            $old_password = md5($post->old_password);
            if ($user->password != $old_password) {
                $result = array(
                    'flag' => false,
                    'message' => "the password is invalidate"
                );
                return $this->returnJson($result);
                
                // return $this->showError('old password isn\'t right');
            } else {
                
                try {
                    // Update the password by id
                    $userTable->updatePasswordByEmail($email, $post->password);
                    $result = array(
                        'flag' => true,
                        'message' => "the password has been updated"
                    );
                    return $this->returnJson($result);
                    // return $this->redirect()->toRoute('users/setting', array(
                    // 'action' => 'passwordConfirm'
                    // ));
                } catch (\Exception $e) {
                    MyUtils::writelog("can't write usertable in settingController", $e);
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
        require 'module/Users/src/Users/Tools/AuthUser.php';
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/setting');
        }
        
        $post = $this->request->getPost();
        
        $form = $this->getServiceLocator()->get('UserSetForm');
        
        $form->setData($post);
        
        // validate the telephone no
        $flag = MyUtils::isValidateTel($post->telephone1) ? MyUtils::isValidateTel($post->telephone2) : false;
        
        // validate the user name
        $utils = new MyUtils();
        $flag_name = $utils->isValidateName($post['first_name']) ? $utils->isValidateName($post['last_name']) : false;
        $flag_address = $utils->isValidateAddress($post['address']);
        
        if (! $form->isValid() || ! $flag || ! $flag_name || ! $flag_address) {
            $result = array(
                'flag' => false,
                'message' => "the form is invalidate"
            );
            return $this->returnJson($result);
        }
        $userTable = $this->serviceLocator->get('userTable');
        $user = $userTable->getUserByEmail($email);
        
        //if first name and last name have been changed, update it
        if ($user->first_name != $post->first_name || $user->last_name != $post->last_name) {
            $user->first_name = $post->first_name;
            $user->last_name = $post->last_name;
            $userTable->updateUser($user);
        }
        
        // get userInfo by email and update it
        $userInfoTable = $this->serviceLocator->get('userInfoTable');
        $userInfo = $userInfoTable->getUserInfoByEmail($email);
        $userInfo->sex = $post->sex;
        $userInfo->telephone1 = $post->telephone1;
        $userInfo->telephone2 = $post->telephone2;
        $userInfo->address = $post->address;
        $userInfo->title = $post->title;
        
        $userInfoTable->updateUserInfo($userInfo);
        $result = array(
        		'flag' => true,
        		'message' => "userinfo has been updated"
        );
        return $this->returnJson($result);
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
     * get user by the authorized email
     *
     * @return User
     */
    protected function getUser($email)
    {
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUserByEmail($email);
        return $user;
    }

    /**
     * If setting is successful, show confirm message and
     * close the window in 5 seconds
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function passwordConfirmAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $user = $this->getUser($email);
        $viewModel = new ViewModel(array(
            'user' => $user
        ));
        return $viewModel;
    }
}

