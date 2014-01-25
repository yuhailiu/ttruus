<?php
namespace Users\Model;

use Zend\Text\Table\Row;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Captcha\Dumb;

class UserTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function saveUser(User $user)
    {
        $data = array(
            'email'         => $user->email,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'password'      => $user->password,
            'filename'      => $user->filename,
            'thumbnail'     => $user->thumbnail,
            'create_time'   => $user->create_time,
            'last_modify'   => null,
            'sex'           => $user->sex,
            'telephone1'    => $user->telephone1,
            'telephone2'    => $user->telephone2,
            'address'       => $user->address,
            'title'         => $user->title,
        );

        $id = (int)$user->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getUser($id)) {
            	if (empty($data['password'])) {
            		unset($data['password']);
            	}
            	//throw new \Exception("date first_name is ". $data['email']);
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('User ID does not exist');
            }
        }
    }
    
    /**
     * Get all users
     * @return ResultSet
     */
    public function fetchAll()
    {
    	$resultSet = $this->tableGateway->select();
    	return $resultSet;
    }
    
    /**
     * Get User account by UserId
     * @param string $id
     * @throws \Exception
     * @return Row
     */    
    public function getUser($id)
    {
    	$id  = (int) $id;
    	$rowset = $this->tableGateway->select(array('id' => $id));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $id");
    	}
    	return $row;
    }
    
    /**
     * Get User account by Email
     * @param string $userEmail
     * @throws \Exception
     * @return Row
     */
    public function getUserByEmail($userEmail)
    {
    	$rowset = $this->tableGateway->select(array('email' => $userEmail));
    	$row = $rowset->current();
    	if (!$row) {
    		throw new \Exception("Could not find row $userEmail");
    	}
    	return $row;
    }
    
    /**
     * Delete User account by UserId
     * @param string $id
     */
    public function deleteUser($id)
    {
    	$this->tableGateway->delete(array('id' => $id));
    }

}
