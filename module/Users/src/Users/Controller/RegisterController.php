<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Model\User;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Users\Model\UserInfo;
use Users\Model\Orgnization;

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
        //check the captcha code
        session_start();
        $post = $this->request->getPost();
        $compare = strcasecmp($_SESSION['captcha_id'],$post->captcha);
        if (0 != $compare) {
        	return $this->returnResponse(false);
        }
        
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/register');
        }
        
        
        // get MyUtils instance
        $utils = new MyUtils();
        
        // validate the user name and password and email
        $validate = new EmailAddress();
        if (! $utils->isValidatePassword($post->password) || !$validate->isValid($post->email)) {
            return $this->returnResponse(false);
        }
        
        $flag_name = $utils->isValidateName($post['first_name']) ? $utils->isValidateName($post['last_name']) : false;
        
        $form = $this->getServiceLocator()->get('RegisterForm');
        
        $form->setData($post);
        
        // $email = $this->request->getPost('email');
        
        if (! $form->isValid() || ! $flag_name) {
            return $this->returnResponse(false);
        } else {
            
            // create user and init userInfo
            try {
                $this->createUser($form->getData());
                $this->initUserInfo($form->getData());
                $this->initOrg($post->email);
                
                
                //write the firstname in session
                $_SESSION['username'] = $post->first_name;
            } catch (\Exception $e) {
                
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
    
    /**
     * init userInfo by the $email, default pic
     * 
     * @param string $email
     * @return boolean
     */
    protected function initUserInfo($data)
    {
        //create a userInfo with email
        $userInfo = new UserInfo();
        $userInfo->exchangeArray($data);
        
        //save it to table
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        $userInfoTable->saveUserInfo($userInfo);
        return true;
    }
    
    /**
     * init a default org by email with default logo pic, 
     * but user can get it untill build user's own name
     * default username is "default_org_1023"
     * @param unknown $email
     * @return boolean
     */
    protected function initOrg($email)
    {
        //create a org with email
        $org = new Orgnization();
        $data = array(
            'org_creater_email' => $email,
            'org_logo' => "logo",
            'org_logo_thumbnail' => "tn_logo"
        );
        $org->exchangeArray($data);
        
        //save it to table
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        $orgTable->saveOrgnization($org);
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
