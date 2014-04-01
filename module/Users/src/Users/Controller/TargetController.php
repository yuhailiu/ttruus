<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Users\Tools\MyUtils;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;

class TargetController extends AbstractActionController
{

    protected $authservice;

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

    public function indexAction()
    {
        // authrize user
        return $this->returnJson(array(
            'webpage' => 'index'
        ));
    }
    
    // get targets by email which is getten in Session, both receiver and creater
    public function getTargetsAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get targets by emaill
        try {
            $targets = $this->getTargets($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(false));
        }
        
        // return the Json results
        return $this->returnJson($targets);
    }
    
    /**
     * get targets where the creater or receiver is the email
     * 
     * @param unknown $email
     * @return array
     */
    protected function getTargets($email)
    {
        $sql = "SELECT * from target 
            where target_creater = '$email' or receiver = '$email'
            ORDER BY target_end_time";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            $array[$i] = $row;
            $i ++;
        }
        return $array;
    }
    
    public function getCommentsByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $target_id = (int)$_GET['target_id'];
        
        //get comments by target id
        $comments = $this->getCommentsById($target_id);
        
        //return comments in Json format
        return $this->returnJson($comments);
    }
    
    /**
     * get the comments from comment by target id
     * 
     * @param unknown $target_id
     * @return unknown
     */
    protected function getCommentsById($target_id)
    {
        $sql = "SELECT * from `comment`
                WHERE target_id = '$target_id'
                ORDER BY create_time DESC";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
        	$array[$i] = $row;
        	$i ++;
        }
        return $array;
    }
    
    
    // the old version for reference
    public function editTaskAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get the user belong org id and name
        $orgs = $this->getOrgsByEmail($email);
        $view = new ViewModel(array(
            'orgs' => $orgs
        ));
        return $view;
    }

    public function insertOrUpdateTaskAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get post data from web
        // validate the data from post
        $post = $this->getRequest()->getPost();
        $flag = false;
        
        if (MyUtils::isValidateName($post->task_name)) {
            if (MyUtils::isValidateAddress($post->task_content)) {
                $validateReceiver = new EmailAddress();
                if ($validateReceiver->isValid($post->receiver)) {
                    // change the time format to Y-m-d
                    $post->task_begin_time = date('Y-m-d', strtotime($post->task_begin_time));
                    $post->task_end_time = date('Y-m-d', strtotime($post->task_end_time));
                    $validate = new Date();
                    if ($validate->isValid($post->task_begin_time) && $validate->isValid($post->task_end_time)) {
                        $post->org_id = (int) $post->org_id;
                        $post->task_creater = $email;
                        $flag = true;
                    }
                }
            }
        }
        
        if (! $flag) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'data is invalidate'
            ));
        }
        
        // insert the task into mainTask table
        try {
            $mainTaskId = $this->insertOrUpdateTask($post);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'can not update or save the task'
            ));
        }
        
        // return json result
        return $this->returnJson(array(
            'flag' => true,
            'message' => 'the task has been update or save',
            'type' => $post->task_assign,
            'mainTaskId' => $mainTaskId,
            'task_name' => $post->task_name
        ));
    }

    public function insertOrUpdateSubTaskAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get post data from web
        // validate the data from post
        $post = $this->getRequest()->getPost();
        $flag = false;
        
        if (MyUtils::isValidateName($post->sub_task_name)) {
            if (MyUtils::isValidateAddress($post->sub_task_content)) {
                // validate receiver
                $validateReceiver = new EmailAddress();
                if ($validateReceiver->isValid($post->receiver)) {
                    // change the time format to Y-m-d
                    $post->sub_task_begin_time = date('Y-m-d', strtotime($post->sub_task_begin_time));
                    $post->sub_task_end_time = date('Y-m-d', strtotime($post->sub_task_end_time));
                    $validate = new Date();
                    if ($validate->isValid($post->sub_task_begin_time) && $validate->isValid($post->sub_task_end_time)) {
                        $post->task_id = (int) $post->task_id;
                        if ($post->task_id) {
                            $flag = true;
                        }
                    }
                }
            }
        }
        
        if (! $flag) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'data is invalidate'
            ));
        }
        
        // insert the task into subTask table
        try {
            $subTaskId = $this->insertOrUpdateSubTask($post);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'can not update or save the task'
            ));
        }
        
        // return json result
        return $this->returnJson(array(
            'flag' => true,
            'message' => 'the sub task has been update or save'
        ));
    }

    /**
     * insert or update task if there is a task_id in post
     *
     * @param post $data            
     * @throws \Exception
     * @return genertatedValue task_id
     */
    protected function insertOrUpdateTask($data)
    {
        if ($data->task_id) {
            // update the task in table
            throw new \Exception('waiting for your input');
        } else {
            // insert the task in table
            $sql = "INSERT into mainTask
                    (org_id, receiver, task_begin_time, task_end_time, task_content, task_creater, task_name, task_status)
                    VALUES ('$data->org_id', '$data->receiver', '$data->task_begin_time', 
                    '$data->task_end_time', '$data->task_content', '$data->task_creater', '$data->task_name', 1)";
            $adapter = $this->getAdapter();
            $result = $adapter->query($sql)->execute();
            return $result->getGeneratedValue();
        }
    }

    /**
     * insert the sub task by main task id
     *
     * @param post $data            
     * @throws \Exception
     * @return genertatedValue sub_task_id
     */
    protected function insertOrUpdateSubTask($data)
    {
        if ($data->sub_task_id) {
            // update the task in table
            throw new \Exception('waiting for your input');
        } else {
            // insert the task in table
            $sql = "INSERT into subTask
                (task_id, receiver, sub_task_begin_time, sub_task_end_time, sub_task_content, weights, sub_task_name, sub_task_status)
                VALUES ('$data->task_id', '$data->receiver', '$data->sub_task_begin_time', 
                '$data->sub_task_end_time', '$data->sub_task_content', 1, '$data->sub_task_name', 1)";
            $adapter = $this->getAdapter();
            $result = $adapter->query($sql)->execute();
            return $result->getGeneratedValue();
        }
    }

    public function getOrgMemberByOrgIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get orgId from post
        $orgId = (int) $this->request->getPost()->orgId;
        
        // get members by orgId
        try {
            $members = $this->getOrgMemberByOrgId($orgId);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false
            ));
        }
        
        // return the member by Json
        return $this->returnJson(array(
            'flag' => true,
            'members' => $members
        ));
    }

    /**
     *
     * @param int $orgId            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function getOrgMemberByOrgId($orgId)
    {
        $sql = "SELECT * from userInfo JOIN user_org 
                ON userInfo.email = user_org.user_email
                WHERE user_org.org_id = $orgId 
                LIMIT 0,50";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // switch the rows to array
        $i = 1;
        foreach ($rows as $row) {
            $array[$i] = $row;
            $i ++;
        }
        
        return $array;
    }

    /**
     * get current user's belong org by his email
     *
     * @param string $email            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function getOrgsByEmail($email)
    {
        $sql = "select * from orgnization 
            WHERE id IN (SELECT org_id from user_org where user_email = '$email')
            ORDER BY org_name
            LIMIT 0, 20";
        $adapter = $this->getAdapter();
        $rows = $adapter->query($sql)->execute();
        return $rows;
    }
}
