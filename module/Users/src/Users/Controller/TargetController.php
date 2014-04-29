<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Users\Tools\MyUtils;
use Zend\Validator\Date;
use Zend\Validator\EmailAddress;
use Users\Model\Target;

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
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        return $this->returnJson(array(
            'webpage' => 'index'
        ));
    }

    public function insertTargetAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        // validate the input data
        $target = new Target();
        $target->target_name = MyUtils::isValidateName($_GET['target_name']) ? $_GET['target_name'] : - 1;
        $target->parent_target_id = (int) $_GET['parent_target_id'];
        $end_date = date('Y-m-d', strtotime($_GET['target_end_time']));
        $date = new Date();
        $target->target_end_time = $date->isValid($end_date) ? $end_date : - 1;
        $target->target_content = MyUtils::isValidateAddress($_GET['target_content']) ? $_GET['target_content'] : - 1;
        $target->target_creater = $email;
        $validateEmail = new EmailAddress();
        $target->receiver = $validateEmail->isValid($_GET['receiver']) ? $_GET['receiver'] : - 1;
        // if the receiver is the owner, status is 7, otherwise is 2
        if ($email == $target->receiver) {
            // receiver is the owner
            $target->target_status = 7;
        } else {
            // receiver is a helper
            $target->target_status = 2;
        }
        foreach ($target as $item) {
            if ($item == - 1) {
                return $this->returnJson(array(
                    'flag' => false,
                    'message' => 'invalidate data'
                ));
            }
        }
        
        // insert the target
        try {
            // get the auto increamental id
            $id = $this->insertTarget($target);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => "cant insert the target"
            ));
        }
        
        $target->target_id = $id->getGeneratedValue();
        // return result
        return $this->returnJson(array(
            'flag' => true,
            'target' => $target
        ));
    }

    /**
     *
     * @param Target $target            
     */
    protected function insertTarget(Target $target)
    {
        $sql = "insert into target (target_name, target_creater, target_end_time, 
            target_content, target_status, parent_target_id, receiver) 
            values ('$target->target_name', '$target->target_creater', '$target->target_end_time',
            '$target->target_content', '$target->target_status', '$target->parent_target_id', '$target->receiver')";
        // excute
        $adapter = $this->getAdapter();
        $id = $adapter->query($sql)->execute();
        return $id;
    }

    public function getTargetByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get id
        $target_id = (int) $_GET['target_id'];
        
        // validate the target id
        if (! $target_id) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target id'
            ));
        }
        
        // get subtargets
        try {
            $target = $this->getTargetById($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get sub targets'
            ));
        }
        // return subtargets
        return $this->returnJson(array(
            'flag' => true,
            'target' => $target
        ));
    }
    
    
    /**
     * 
     * @param unknown $target_id
     * @return multitype:
     */
    protected function getTargetById($target_id)
    {
        $sql = "select * from target
            where target_id = '$target_id'";
        $adapter = $this->getAdapter();
        
        $row = $adapter->query($sql)->execute();
        $row = $row->current();
        
        $unixTime = strtotime($row[target_end_time]);
        $row[target_end_time] = date('Y-m-d', $unixTime);
        
        return $row;
    }
    
    public function getSubTargetsByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get id
        $target_id = (int) $_GET['target_id'];
        
        // validate the target id
        if (! $target_id) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'invalidate target id'
            ));
        }
        
        // get subtargets
        try {
            $subTargets = $this->getSubTargetsById($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get sub targets'
            ));
        }
        // return subtargets
        return $this->returnJson(array(
            'flag' => true,
            'subTargets' => $subTargets
        ));
    }

    protected function getSubTargetsById($id)
    {
        $sql = "select * from target
            where parent_target_id = '$id' ORDER BY target_end_time";
        $adapter = $this->getAdapter();
        
        $rows = $adapter->query($sql)->execute();
        
        // push the result to a sub_targets array
        $subTargets = array();
        foreach ($rows as $row) {
            // format the time "Y-m-d"
            $unixTime = strtotime($row[target_end_time]);
            $row[target_end_time] = date('Y-m-d', $unixTime);
            
            array_push($subTargets, $row);
        }
        
        return $subTargets;
    }

    public function updateStatusByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get id and status
        $target_id = (int) $_POST['target_id'];
        $target_status = (int) $_POST['target_status'];
        if ($target_id) {
            // update the status with id and creater or receiver
            try {
                $this->updateStatusById($target_id, $target_status, $email);
            } catch (\Exception $e) {
                return $this->returnJson(array(
                    false
                ));
            }
            
            return $this->returnJson(array(
                true
            ));
        } else {
            return $this->returnJson(array(
                false
            ));
        }
    }

    /**
     * update status by id and make sure the right authorization
     *
     * @param int $target_id            
     * @param int $target_status            
     * @param string $email
     *            creater or receiver
     */
    protected function updateStatusById($target_id, $target_status, $email)
    {
        $sql = "UPDATE target set target_status = '$target_status'
            where target_id = '$target_id' 
            and (target_creater = '$email' or receiver = '$email')";
        
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }
    
    // insert comment by targetId
    public function addCommentByTargetIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get target id and comment and who
        $who = (int) $_POST['who'];
        $target_id = (int) $_POST['target_id'];
        $comment = $_POST['comment'];
        
        // validate the comment
        if (MyUtils::isValidateAddress($comment) && $comment) {
            // insert the comment into table
            try {
                $this->insertCommentByTargetId($comment, $target_id, $who);
            } catch (\Exception $e) {
                return $this->returnJson(array(
                    flag => false
                ));
            }
            // if success return true to web
            return $this->returnJson(array(
                flag => true
            ));
        } else {
            // return false to web
            return $this->returnJson(array(
                flag => false
            ));
        }
    }

    /**
     * insert comment
     *
     * @param string $comment            
     * @param int $target_id            
     * @param int $who            
     */
    protected function insertCommentByTargetId($comment, $target_id, $who)
    {
        // sql
        $sql = "INSERT into `comment` (target_id , comment , who)
            VALUES ('$target_id', '$comment', '$who') ";
        
        // excute
        $adapter = $this->getAdapter();
        $adapter->query($sql)->execute();
    }
    
    // get targets by email which is getten in Session, both receiver and creater
    public function getAgreeTargetsAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        // get targets by emaill
        try {
            $targets = $this->getAgreeTargets($email);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                false
            ));
        }
        
        // return the Json results
        return $this->returnJson($targets);
    }

    /**
     * get agree targets where the creater or receiver is the email
     *
     * @param unknown $email            
     * @return array
     */
    protected function getAgreeTargets($email)
    {
        $sql = "SELECT * from target 
            where parent_target_id IN (SELECT target_id from target 
            where target_creater = '$email' 
            and (target_status in ('2', '3','4','7','8','9'))
            and parent_target_id = '0')
            or (target_creater = '$email' and (target_status in ('2', '3','4','7','8','9')) and parent_target_id = '0') 
            or (receiver = '$email' and  (target_status in ('2', '3','4','7','8','9')))
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
        
        // sort the targets by target and sub-target
        $sortedTargets = $this->sortTargetsBySubtarget($array, $email);
        
        return $sortedTargets;
    }

    /**
     * get the $targets from table by endtime, sort them by subtarget
     *
     * @param
     *            $targets
     * @return sortedTargets
     */
    protected function sortTargetsBySubtarget($targets, $email)
    {
        $i = 1;
        $sortedTargets = array();
        foreach ($targets as $target) {
            // if it's a maintarget
            if (! $target['parent_target_id']) {
                // push it to sortedTargets
                $sortedTargets[$i] = $target;
                $i ++;
                
                // put the relative subTargets to the target
                foreach ($targets as $subTarget) {
                    if ($target['target_id'] == $subTarget['parent_target_id']) {
                        $sortedTargets[$i] = $subTarget;
                        $i ++;
                    }
                }
                // shared targets
            } elseif ($target['receiver'] == $email and $target['target_creater'] != $email) {
                $sortedTargets[$i] = $target;
                $i ++;
            }
        }
        return $sortedTargets;
    }

    public function getCommentsOfSubAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $target_id = (int) $_GET['target_id'];
        
        // get comments by target id
        try {
            $comments = $this->getCommentsOfSub($target_id);
        } catch (\Exception $e) {
            return $this->returnJson(array(
                'flag' => false,
                'message' => 'cant get comments'
            ));
        }
        
        // return comments in Json format
        return $this->returnJson($comments);
    }

    /**
     *
     * @param unknown $target_id            
     * @return unknown
     */
    protected function getCommentsOfSub($target_id)
    {
        $sql = "select * from `comment`
            where target_id = '$target_id' or target_id = (
            SELECT parent_target_id from target
            where target_id = '$target_id')
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

    public function getCommentsByIdAction()
    {
        // authrize user
        require 'module/Users/src/Users/Tools/AuthUser.php';
        
        $target_id = (int) $_GET['target_id'];
        
        // get comments by target id
        $comments = $this->getCommentsById($target_id);
        
        // return comments in Json format
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
}
