<?php
namespace Users\Tools;

use Zend\Validator\Regex;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Zend\Db\Adapter\Adapter;

class MyUtils
{

    public function __construct()
    {}

    /**
     * if only a-z or A-Z or chinese words in 3-18 return true
     *
     * @param String $name            
     * @return boolean
     * @author liuyuhai 2014-1-26
     */
    public static function isValidateName($name)
    {
        $validator = new Regex(array(
            'pattern' => '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9]|[\s])+$/u'
        ));
        $flag = $validator->isValid($name);
        return $flag;
    }

    /**
     * validate address, allow space and comm
     *
     * @param String $address            
     * @return boolean
     */
    public static function isValidateAddress($address)
    {
        $validator = new Regex(array(
            'pattern' => '/^([\x{4e00}-\x{9fa5}]|[a-zA-Z0-9]|[,]|[，]|[.]|[。]|[:]|[：]|[；]|[?]|[？]|[\s]){0,140}$/u'
        ));
        $flag = $validator->isValid($address);
        return $flag;
    }

    /**
     *
     * @param string $password            
     * @return boolean
     */
    public static function isValidatePassword($password)
    {
        $validator = new Regex(array(
            'pattern' => '/^[\@A-Za-z0-9\!\#\$\%\^\&\*\.\~]{5,22}$/u'
        ));
        $flag = $validator->isValid($password);
        return $flag;
    }

    /**
     *
     * @param string $message            
     * @throws \Exception
     */
    public static function writelog($message)
    {
        try {
            $writer = new Stream('data/log/logfile');
            if (! $writer) {
                throw new \Exception('Failed to create writer');
            }
            $logger = new Logger();
            $logger->addWriter($writer);
            date_default_timezone_set('Asia/Shanghai');
            $logger->info($message);
        } catch (\Exception $e) {
            throw new \Exception("can't write the log", $e);
        }
    }

    /**
     * throw exception
     *
     * @return mysqli
     */
    public static function getDB_connection()
    {
        $config = require 'config/autoload/mysqli_config.php';
        $conn = new \mysqli($config['hostname'], $config['username'], $config['password'], $config['dbname']) or die("Error coonecting to Mysql server");
        $conn->query("set names utf8");
        return $conn;
    }

    public static function getBD_adapte()
    {
        $config = require 'config/autoload/mysqli_config.php';
        $adapter = new Adapter(array(
            'driver' => 'Mysqli',
            'database' => $config['dbname'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
        $adapter->query("set names utf8")->execute();
        return $adapter;
    }
    

    /**
     * return false if the telephone no doesn't match '/(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/'
     *
     * @param string $tel            
     * @return boolean
     */
    public static function isValidateTel($tel)
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
     * return false if the website doesn't match /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/
     *
     * @param string $website            
     * @return boolean
     */
    public static function isValidateWebsite($website)
    {
        $validator = new Regex(array(
            'pattern' => '/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/'
        ));
        return $validator->isValid($website);
    }

    /**
     * exchange the data to object and return the new object
     * 
     * @param array $data
     * @param Object $object
     * @return Object
     */
    public static function exchangeDataToObject($data, $object)
    {
        foreach ($object as $key => $value) {
            $object->$key =  (isset($data[$key])) ? $data[$key] : $value;
        }
        
        return $object;
    }
    
    /**
     * exchange an object to an array
     * 
     * @param Object $object
     * @return array $data
     */
    public static function exchangeObjectToData($object)
    {
        foreach ($object as $key => $value){
            $data[$key] = $value;
        }
        
        return $data;
    }
}

?>