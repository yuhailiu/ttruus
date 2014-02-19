<?php
namespace Users\Model;

use Zend\Text\Table\Row;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class UserTable
{

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * save user to users table, can't update
     * 
     * @param User $user
     */
    public function saveUser(User $user)
    {
        $data = array(
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'password' => $user->password,
            'captcha' => $user->captcha,
            'failedTimes' => $user->failedTimes
        );
        $this->tableGateway->insert($data);
    }
    
    /**
     * update user to user table
     */
    public function updateUser(User $user)
    {
        
    }

    /**
     * Get all users
     *
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * Get User account by Email
     *
     * @param string $userEmail            
     * @throws \Exception
     * @return Row
     */
    public function getUserByEmail($email)
    {
        $rowset = $this->tableGateway->select(array(
            'email' => $email
        ));
        $row = $rowset->current();
        if (! $row) {
            throw new \Exception("Could not find row $email");
        }
        return $row;
    }

    /**
     * Delete User account by $email
     *
     * @param string $email            
     */
    public function deleteUser($email)
    {
        $this->tableGateway->delete(array(
            'email' => $email
        ));
    }

    /**
     * change the password
     * 
     * @param string $email            
     * @param string $password            
     */
    public function updatePasswordByEmail($email, $password)
    {
        $password = md5($password);
        $this->tableGateway->update(array(
            'password' => $password
        ), array(
            'email' => $email
        ));
    }

    /**
     *
     * @param string $email            
     * @param int $captcha
     *            throw exception if false
     */
    public function updateCaptchaByEmail($email, $captcha)
    {
        try {
            $this->tableGateway->update(array(
                'captcha' => $captcha
            ), array(
                'email' => $email
            ));
        } catch (\Exception $e) {
            throw new \SqlException($e);
        }
    }

    /**
     * increase 1 every failed login, if the times is over 10, throw exception
     * 
     * @param int $id            
     * @param int $failedTimes            
     * @throws \Exception
     */
    public function updateFailedTimesByEmail($email, $failedTimes)
    {
        try {
            $this->tableGateway->update(array(
                'failedTimes' => $failedTimes
            ), array(
                'email' => $email
            ));
        } catch (\Exception $e) {
            throw new \Exception("failed to update failedTimes");
        }
    }
}
