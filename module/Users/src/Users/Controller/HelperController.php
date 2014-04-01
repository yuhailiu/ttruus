<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Json\Json;
use Users\Model\Orgnization;
use Zend\Paginator\Paginator;
use Users\Model\RequestJoin;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Validator\EmailAddress;
use Users\Model\UserOrg;
use Users\Model\UserInfo;
use Users\Model\InviteJoin;

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

    public function invitePeopleJoinOrgAction()
    {
        // authorized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // validate the post data, get the org id
        $post = $this->getRequest()->getPost();
        $flag = $this->isValidateInvitePost($post);
        
        if (! $flag) {
            return $this->returnJson(array(
                'flag' => $flag,
                'message' => 'invalidate data'
            ));
        }
        
        // get org member by id
        try {
            $members = $this->getMembersByOrgid($post->orgId);
        } catch (\Exception $e) {
            return $this->returnJson(array(
            		'flag' => $flag,
            		'message' => 'invalidate org'
            ));
        }
        $emails = array();
        foreach ($members as $member){
            array_push($emails, $member['email']);
        }

        $message = "";
        // insert the people into invite table if not members
        foreach ($post as $key => $value){
            //get the email string
            $subKey = substr($key, 0, 8);
            if ($subKey == 'invite_e') {
            	if(!in_array($value, $emails)){
            	    //insert the relative $email and orgId to table
            	    $inviteJoin = new InviteJoin();
            	    $inviteJoin->status = 0;
            	    $inviteJoin->invite_org_id = $post->orgId;
            	    $inviteJoin->inviter = $value;
            	    $additioninfoName = 'invite_addition_info'.substr($key, 12);
            	    $inviteJoin->addition_info = $post->$additioninfoName;
            	    try {
            	    	$this->insertOrUpdateInviteJoin($inviteJoin);
            	    } catch (\Exception $e) {
            	        $message = $message."can't insert user--".$value."orgId--".$post->orgId;
            	    }
            	}else{
            	    $message = $message.$value." has been the member--";
            	}
            }
        }
        
        // update the invite table
        return $this->returnJson(array(
        		'flag' => true,
                'message' => $message
        ));
    }
    
    protected function insertOrUpdateInviteJoin(InviteJoin $inviteJoin)
    {
        if($inviteJoin->status != 0){
            throw new \Exception("add update code here"); 
        }else{
            $sql = "INSERT into invite_join
            (invite_org_id, inviter, addition_info, status)
            VALUES ($inviteJoin->invite_org_id, '$inviteJoin->inviter',
            '$inviteJoin->addition_info', 0)";
        }
        $adpater = $this->getAdapter();
        $adpater->query($sql)->execute();
    }
    
    protected function isValidateInvitePost($post)
    {
        $flag = false;
        $id = (int) $post->orgId;
        if ($id) {
        	$emailValidate = new EmailAddress();
        	foreach ($post as $key => $value) {
        		// get the first letter of key
        		$subKey = substr($key, 0, 8);
        		if ($subKey == 'invite_e') {
        			if ($emailValidate->isValid($value)) {
        				$flag = true;
        			} else {
        				$flag = false;
        				break;
        			}
        		}
        		if($subKey == 'invite_a'){
        			if(MyUtils::isValidateAddress($value)){
        				$flag = true;
        			}else {
        				$flag = false;
        				break;
        			}
        		}
        	}
        }
        return $flag;
    }

    public function deleteUserFromOrgAction()
    {
        // authrized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the OrgId from post
        $post = $this->getRequest()->getPost();
        $orgId = (int) $post->orgId;
        
        // valiate email and orgId
        $validate = new EmailAddress();
        if (! $orgId || ! $validate->isValid($post->userEmail)) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt find the orgId or invalidate email"
            ));
        }
        
        // delete the relationship from user_org
        try {
            $this->deleteUserOrgByEmailAndOrgId($post->userEmail, $orgId);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt delete the record"
            ));
        }
        // return the result
        return $this->returnJson(array(
            'flag' => true,
            'message' => "have deleted the record"
        ));
    }

    protected function deleteUserOrgByEmailAndOrgId($email, $orgId)
    {
        $sql = "DELETE FROM user_org
                WHERE org_id = $orgId and user_email = '$email'";
        print_r($sql);
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }

    /**
     * get creater email, then get the userInfo
     *
     * @param int $orgId            
     * @return \Users\Model\UserInfo
     */
    protected function getCreaterInfoByOrgid($orgId)
    {
        $sql = "SELECT * from userInfo
        WHERE email = (SELECT org_creater_email FROM orgnization WHERE id = $orgId)";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        $row = $rows->current();
        $userInfo = MyUtils::exchangeDataToObject($row, new UserInfo());
        
        return $userInfo;
    }

    /**
     * get user_email from user_org, then get the userInfo
     *
     * @param int $orId            
     * @return \Zend\Db\ResultSet\ResultSet
     */
    protected function getMembersByOrgid($orgId)
    {
        $sql = "SELECT * from userInfo JOIN user_org 
            ON userInfo.email = user_org.user_email
            WHERE user_org.org_id = $orgId LIMIT 0,50";
        $adapter = $this->getAdapter();
        return $adapter->query($sql)->execute();
    }

    public function updateUserOrgAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get requester email, orgId and decision
        $post = $this->getRequest()->getPost();
        
        // validate the email and decision
        $validate = new EmailAddress();
        if (! $validate->isValid($post->requester)) {
            return $this->returnJson(array(
                'flag' => false
            ));
        } else {
            $requester = $post->requester;
        }
        $decision = (int) $post->decision;
        $orgId = (int) $post->orgId;
        
        if ($decision == 1) {
            // update user_org table if it's agree
            try {
                $this->saveUserOrg($requester, $orgId);
            } catch (\Exception $e) {
                // close the exception
                MyUtils::writelog("can not save UserOrg");
            }
        }
        
        try {
            // update request_join table with decision
            $this->updateRequest_Join($requester, $orgId, $decision);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false
            ));
        }
        
        // update pendingNo in session
        $_SESSION['pendingNo'] --;
        
        // return json true
        return $this->returnJson(array(
            'flag' => true
        ));
    }

    /**
     * when the reponder agree, add the relationship in user_org table
     *
     * @param string $requester
     *            email of requester
     * @param int $orgId
     *            reponder
     * @return boolean
     */
    protected function saveUserOrg($requester, $orgId)
    {
        // create the userOrg
        $userOrg = new UserOrg();
        $userOrg->org_id = $orgId;
        $userOrg->user_email = $requester;
        
        // save it to table
        $userOrgTable = $this->getServiceLocator()->get('UserOrgTable');
        $userOrgTable->saveUserOrg($userOrg);
        return true;
    }

    /**
     * set the status in the relative requester
     *
     * @param string $requester
     *            email
     * @param int $orgId            
     * @param int $decision
     *            status field in the table
     */
    protected function updateRequest_Join($requester, $orgId, $decision)
    {
        // prepare the sql
        $sql = "update request_join SET `status` = '$decision' 
            WHERE requester='$requester' and request_org_id = '$orgId'";
        
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
        return true;
    }

    public function showPendingRequestAction()
    {
        // get the pending request by email
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the reponder org by $email
        $orgnizationTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgnizationTable->getOrgnizationByCreaterEmail($email);
        } catch (\Exception $e) {
            $org = null;
        }
        
        // create pending request view
        $view = new ViewModel(array(
            'org' => $org
        ));
        return $view;
    }

    public function getJsonOrgMembersAction()
    {
        // authrized
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the OrgId from post
        $post = $this->getRequest()->getPost();
        
        $orgId = (int) $post->orgId;
        if (! $orgId) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt find the orgId"
            ));
        }
        
        // get creater info
        try {
            $creater = $this->getCreaterInfoByOrgid($orgId);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt get creater"
            ));
        }
        
        // get paginator of user's info of the org
        try {
            $paginator = $this->getPaginatorOfMembersByOrgId($orgId);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt get member page"
            ));
        }
        
        // init the current page
        $currentPage = (int) $post->currentPage;
        if ($currentPage == null) {
            $currentPage = 1;
        }
        
        // get current items by current page no
        $paginator->setCurrentPageNumber($currentPage);
        $items = $paginator->getCurrentItems();
        // print_r($items);
        
        // prepare the array
        $result = array(
            'flag' => true,
            'totalPages' => $paginator->count(),
            'items' => $items,
            'currentPage' => $currentPage,
            'creater' => $creater,
            'orgId' => $orgId
        );
        
        // return a Json response
        return $this->returnJson($result);
    }

    public function getJsonPendingRequestAction()
    {
        // get the pending request by email
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $post = $this->getRequest()->getPost();
        
        // get the paginator of requestJoin
        try {
            $paginator = $this->getPaginatorOfRJoinByEmail($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt get page"
            ));
        }
        
        // reset the pendingNo in session
        $_SESSION['pendingNo'] = $paginator->getTotalItemCount();
        
        // init the current page
        $currentPage = (int) $post->currentPage;
        if ($currentPage == null) {
            $currentPage = 1;
        }
        
        // get current items by orgname and page no
        $paginator->setCurrentPageNumber($currentPage);
        $items = $paginator->getCurrentItems();
        
        // prepare the array
        $result = array(
            'flag' => true,
            'totalPages' => $paginator->count(),
            'items' => $items,
            'currentPage' => $currentPage
        );
        
        // return a Json response
        return $this->returnJson($result);
    }

    public function joinOrgAction()
    {
        // autthrize the request and get the $email
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the org id and additionInfo from post
        $post = $this->request->getPost();
        $additionInfo = $post->additionInfo;
        $request_org_id = (int) $post->id;
        
        // validate the data
        if (! MyUtils::isValidateAddress($additionInfo)) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "invalidate additionInfo"
            ));
        }
        
        // insert the data to request_join db
        try {
            $this->insertRequest($email, $request_org_id, $additionInfo);
        } catch (\Exception $e) {
            MyUtils::writelog("can't insert request into table" . $e);
            return $this->returnJson(array(
                'flag' => false,
                'message' => "failed to insert request into table.$e"
            ));
        }
        // return json result to web
        return $this->returnJson(array(
            'flag' => true,
            'message' => "have inserted the request to table"
        ));
    }

    /**
     * if there is a same request, update it, otherwise inserte a new one
     *
     * @param string $email            
     * @param int $request_org_id            
     * @param string $additionInfo            
     */
    protected function insertRequest($email, $request_org_id, $additionInfo)
    {
        // check the exsite request from same user to same org
        $requestJoinTable = $this->getServiceLocator()->get('RequestJoinTable');
        try {
            $requestJoin = $requestJoinTable->getRequestJoinByEmailOrgId($email, $request_org_id);
        } catch (\Exception $e) {
            $requestJoin = new RequestJoin();
            $data = array(
                'requester' => $email,
                'request_org_id' => $request_org_id
            );
            $requestJoin->exchangeArray($data);
        }
        
        $requestJoin->addition_info = $additionInfo;
        $requestJoinTable->saveRequestJoin($requestJoin);
    }

    public function searchOrgByNameAction()
    {
        // authorize the request
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the search name
        $post = $this->request->getPost();
        $orgName = $post->org_name;
        
        // validate orgName and currentPage
        if (! MyUtils::isValidateName($orgName)) {
            return $this->returnJson(false);
        }
        
        // get the paginater by org name and email
        try {
            $paginator = $this->getPageByOrgnameExcludeEmail($orgName, $email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cannt get page"
            ));
        }
        
        // init the current page
        $currentPage = (int) $post->currentPage;
        if ($currentPage == null) {
            $currentPage = 1;
        }
        
        // get current items by orgname and page no
        $paginator->setCurrentPageNumber($currentPage);
        $items = $paginator->getCurrentItems();
        
        // prepare the array
        $result = array(
            'flag' => true,
            'totalPages' => $paginator->count(),
            'items' => $items,
            'currentPage' => $currentPage,
            'orgName' => $orgName
        );
        
        // return a Json response
        return $this->returnJson($result);
    }

    /**
     * get paginator by orgname, defaultItemCountPerPage is 5
     *
     * @param string $orgName            
     * @return \Zend\Paginator\Paginator
     */
    protected function getPageByOrgnameExcludeEmail($orgName, $email, $DICP = 5)
    {
        // get the result array
        $orgnizationTable = $this->getServiceLocator()->get('OrgnizationTable');
        $orgs = $orgnizationTable->getOrgByNameExcludeEmail($orgName, $email);
        $array = array();
        $i = 1;
        foreach ($orgs as $org) {
            $array[$i] = $org;
            $i ++;
        }
        
        // create the paginator and return it
        $paginator = new Paginator(new ArrayAdapter($array));
        $paginator->setDefaultItemCountPerPage($DICP);
        return $paginator;
    }

    protected function getPaginatorOfMembersByOrgId($orgId, $DICP = 10)
    {
        // get members
        $members = $this->getMembersByOrgid($orgId);
        $array = array();
        $i = 1;
        foreach ($members as $m) {
            $array[$i] = $m;
            $i ++;
        }
        
        // create the paginator and return it
        $paginator = new Paginator(new ArrayAdapter($array));
        $paginator->setDefaultItemCountPerPage($DICP);
        return $paginator;
    }

    /**
     *
     * @param string $email            
     * @param int $DICP
     *            default value is 5
     * @return \Zend\Paginator\Paginator
     */
    protected function getPaginatorOfRJoinByEmail($email, $DICP = 5)
    {
        // get requestJoins
        $requestJoinTable = $this->getServiceLocator()->get('RequestJoinTable');
        $requestJoins = $requestJoinTable->getPendingRequestJoinByEmail($email);
        
        $array = array();
        $i = 1;
        foreach ($requestJoins as $reJ) {
            $array[$i] = $reJ;
            $i ++;
        }
        
        // create the paginator and return it
        $paginator = new Paginator(new ArrayAdapter($array));
        $paginator->setDefaultItemCountPerPage($DICP);
        return $paginator;
    }

    /**
     * get org by id and change the org to Json and return
     *
     * @return Json response
     */
    public function getOrgAction()
    {
        // authorize the request
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get org id from param
        $id = $_GET['id'];
        $id = (int) $id;
        // get org from orgtable by id
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgTable->getOrgnizationById($id);
        } catch (\Exception $e) {
            return $this->returnJson(false);
        }
        
        // return Json org
        return $this->returnJson($org);
    }

    public function indexAction()
    {
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get org by email
        $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
        try {
            $org = $orgTable->getOrgnizationByCreaterEmail($email);
        } catch (\Exception $e) {
            $org = null;
        }
        
        // get image upload form
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        
        // get the OrgSetForm and send it to index page
        $orgSetform = $this->getServiceLocator()->get('OrgSetForm');
        $viewModel = new ViewModel(array(
            'form' => $form,
            'org' => $org,
            'orgSetForm' => $orgSetform
        ));
        return $viewModel;
    }

    public function processAction()
    {
        // authorize the request
        require 'module/Users/src/Users/Tools/AuthUser.php';
        $post = $this->request->getPost();
        
        // prepare the result array
        $result = array();
        
        // validate the post data
        if ($this->isValidateSetData($post)) {
            // save the data to DB;
            // get the relative org
            $orgTable = $this->getServiceLocator()->get('OrgnizationTable');
            $org = $orgTable->getOrgnizationById($post->id);
            
            // is there any update
            if ($this->isAnyUpdate($org, $post)) {
                // update org with the new data
                $org = $this->exchangeOrgWithPost($org, $post);
                
                // save the new org to db
                try {
                    $orgTable->saveOrgnization($org);
                } catch (\Exception $e) {
                    // write the error to log
                    MyUtils::writelog("can't save the org to DB" . $e);
                    // failed to save the date to db
                    $result = array(
                        'flag' => false,
                        'message' => "can't save the org to DB"
                    );
                }
                // success with update confirm message
                $result = array(
                    'flag' => true,
                    'message' => "the org has been update in DB"
                );
            } else {
                // success without any update
                $result = array(
                    'flag' => true,
                    'message' => "success without update DB"
                );
            }
        } else {
            // failed validation
            $result = array(
                'flag' => false,
                'message' => "find invalidated data in the post"
            );
        }
        // return the result
        return $this->returnJson($result);
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

    /**
     * change Org by post data
     *
     * @param Orgnization $org            
     * @param Post $post            
     * @return Orgnization $org
     */
    protected function exchangeOrgWithPost($org, $post)
    {
        foreach ($post as $key => $value) {
            if ('submit' != $key) {
                $org->$key = $value;
            }
        }
        $org->org_LM = null;
        return $org;
    }

    /**
     * return false if find any invalidate data
     *
     * @param Post $post            
     * @return boolean
     */
    protected function isValidateSetData($post)
    {
        if (! MyUtils::isValidateName($post->org_name)) {
            return false;
        }
        if (! MyUtils::isValidateAddress($post->org_address)) {
            return false;
        }
        if (! MyUtils::isValidateAddress($post->org_des)) {
            return false;
        }
        if ($post->org_website) {
            if (! MyUtils::isValidateWebsite($post->org_website)) {
                return false;
            }
        }
        if (! MyUtils::isValidateTel($post->org_tel)) {
            return false;
        }
        return true;
    }

    /**
     * if there are any differenc in the two object, return true
     *
     * @param Orgnization $org            
     * @param Post $post            
     * @return boolean
     */
    protected function isAnyUpdate($org, $post)
    {
        $post['submit'] = null;
        foreach ($post as $key => $value) {
            if ($value != $org->$key) {
                
                return true;
            }
        }
        return false;
    }
}
