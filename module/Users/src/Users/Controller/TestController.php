<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Users\Model\User;
use Users\Model\UserInfo;
use Zend\Db\Adapter\Adapter;
use Users\Model\Orgnization;
use Users\src\Users\Tools\MyDbselect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Validator\EmailAddress;
use Users\Model\RequesJoinTable;
use Users\Model\RequestJoin;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Users\Model\UserOrg;

class TestController extends AbstractActionController
{

    protected $flag;

    public function indexAction()
    {
        //create the userOrg
        $userOrg = new UserOrg();
        $userOrg->org_id = 23;
        $userOrg->user_email = 'ning.meng@gmail.com';
        
        //save it to table
        $userOrgTable = $this->getServiceLocator()->get('UserOrgTable');
        print "__________";
        $userOrgTable->saveUserOrg($userOrg);
        
        
        $view = new ViewModel();
        return $view;
    }
    
    public function getAdapter()
    {
    	if (!$this->adapter) {
    		$sm = $this->getServiceLocator();
    		$this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
    	}
    	return $this->adapter;
    }
    

    /**
     */
    public function getOrg()
    {
        $data = array(
            'id' => 1,
            'org_name' => 'test',
            'org_creater_email' => 'test'
        );
        $org = new Orgnization();
        $org->exchangeArray($data);
        return $org;
    }

    /**
     *
     * @return \Users\Model\User
     */
    public function getUser()
    {
        $data = array(
            'email' => 'test@test.com',
            'first_name' => 'test',
            'last_name' => 'last',
            'password' => 'lyh1023nm'
        );
        $user = new User();
        $user->exchangeArray($data);
        return $user;
    }

    /**
     *
     * @return \Users\Model\UserInfo
     */
    public function getUserInfo()
    {
        $data = array(
            'email' => 'test10@test.com',
            'sex' => 1
        );
        $userInfo = new UserInfo();
        $userInfo->exchangeArray($data);
        return $userInfo;
    }
}