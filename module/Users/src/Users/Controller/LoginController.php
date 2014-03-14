<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Json\Json;

class LoginController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    public function getAuthService()
    {
          return  $this->authservice = $this->getServiceLocator()->get('AuthService');
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
     * @param int $failedTimes            
     * @return Response Json(flag = false, failedTimes)
     */
    protected function returnIndexError($failedTimes)
    {
        $result = array(
            'flag' => false,
            'failedTimes' => $failedTimes
        );
        
        return $this->returnJson($result);
    }

    /**
     * success login
     *
     * @return Reponse Json(flag = true, failedTimes = 0)
     */
    protected function returnLoginSuccess()
    {
        $result = array(
            'flag' => true,
            'failedTimes' => 0
        );
        return $this->returnJson($result);
    }

    /**
     * check password is match email in usertable
     *
     * @param string $password            
     * @param string $email            
     * @return array (boolean flag, int failedTimes)
     */
    protected function isValidateUser($password, $email)
    {
        // check it in the DB
        // get the user 
        $userTable = $this->getServiceLocator()->get('UserTable');
        $failedTimes = 0;
        try {
            $user = $userTable->getUserByEmail($email);
            // if user->failedTimes > 10 then return false,
            // others failed time add 1,then go ahead
            $failedTimes = $user->failedTimes;
            
            //get the FAILED_TIMES
            require 'module/Users/src/Users/Tools/appConfig.php';
            if ($failedTimes > FAILED_TIMES) {
                return array(
                    'flag' => false,
                    'failedTimes' => $failedTimes
                );
            } else {
                if ($user->password == md5($password)) {
                    // write it in AuthService the session has been open here
                    $this->getAuthService()
                        ->getStorage()
                        ->write($email);
                    
                    //save email and username to session
                    $_SESSION['email'] = $user->email;
                    
                    // update failedTimes in DB with 0
                    $userTable->updateFailedTimesByEmail($email, 0);
                    return array(
                        'flag' => true,
                        'failedTimes' => 0
                    );
                    
                } else {
                    
                    // update failedTimes in DB with add 1
                    $userTable->updateFailedTimesByEmail($email, $failedTimes + 1);
                    
                    return array(
                        'flag' => false,
                        'failedTimes' => $failedTimes + 1
                    );
                }
            }
        } catch (\Exception $e) {
            // write a error log
            MyUtils::writelog("error at isValidateUser--" . $e);
            return array(
                'flag' => false,
                'failedTimes' => $failedTimes
            );
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
        if (! $this->request->isPost()) {
            return $this->redirect()->toRoute('users/login');
        }
        
        $post = $this->request->getPost();
        $form = $this->getServiceLocator()->get('LoginForm');
        
        // validate the passoword
        if (! MyUtils::isValidatePassword($post->password)) {
            return $this->returnIndexError(0);
        }
        
        // validate login form
        $form->setData($post);
        if ($form->isValid()) {
            
            // Is validate users
                $result = $this->isValidateUser($post->password, $post->email);
                
            // flag is true return true, others return false and times
            if ($result['flag']) {
                
                // return to success
                return $this->returnLoginSuccess();
            } else {
                
                // return failed with times
                return $this->returnIndexError($result['failedTimes']);
            }
        } else {
            return $this->returnIndexError(0);
        }
    }

    /**
     *get the user id from AuthService
     *
     * @return ViewModel with user_info and org
     */
    public function confirmAction()
    {
        //get the user email, if false return to users/login
        require 'module/Users/src/Users/Tools/AuthUser.php';
            
        $userEmail = $email;
        
        //get tabs id from route
        $tabs = $this->params()->fromRoute('tabs');
        
        $userTable = $this->getServiceLocator()->get('UserTable');
        // if can not get the user redirect to login page
        try {
            $user = $userTable->getUserByEmail($userEmail);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('users/login');
        }
        
        //if can not get the user info, plase null to it
        $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
        try{
            $userInfo = $userInfoTable->getUserInfoByEmail($userEmail);
        } catch (\Exception $e){
            $userInfo = null;
        }
        
        // if can't get org place null to it
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgTable->getOrgnizationByCreaterEmail($email);
        } catch (\Exception $e) {
            $org = null;
        }
        
        //get the pending request
        $requestJoin = $this->getServiceLocator()->get('RequestJoinTable');
        try {
            $rowSet = $requestJoin->getPendingRequestJoinByEmail($email);
            $pendingNo = $rowSet->count();
        } catch (\Exception $e) {
            $pendingNo = 0;
        }
        
        //store the pendingNo to session
        $_SESSION['pendingNo'] = $pendingNo;
        $_SESSION['username'] = $userInfo->first_name;
        $_SESSION['org_id'] = $org->id;
        $_SESSION['org_name'] = $org->org_name;
        
        //get the relative forms
        $orgSearchForm = $this->getServiceLocator()->get('OrgSearchForm');
        $joinOrgForm = $this->getServiceLocator()->get('JoinOrgForm');
        
        
        $this->layout('layout/myaccount');
        $viewModel = new ViewModel(array(
            'user' => $user,
            'userInfo' => $userInfo,
            'org' => $org,
            'orgSearchForm' => $orgSearchForm,
            'joinOrgForm' => $joinOrgForm,
            'tabs' => $tabs,
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
}
