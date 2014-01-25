<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Users\Form\LoginForm;
use Users\Form\LoginFilter;
use Users\Model\User;
use Users\Model\UserTable;
use Zend\Validator\Regex;

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
 
        $this->layout('layout/myaccount');
        $userTable = $this->getServiceLocator()->get('UserTable');
        
        $user_id = (int)$this->getAuthService()
            ->getStorage()
            ->read();
        
        // check empty and verify
        if (! $user_id) {
            return $this->redirect()->toRoute('users/login');
        }
        $user = $userTable->getUser($user_id);
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $userSetForm = $this->getServiceLocator()->get('UserSetForm');
        
        $viewModel = new ViewModel(array(
            'user' => $user,
            'form' => $form,
            'userSetForm' => $userSetForm
        ));
        return $viewModel;
    }

    public function processAction()
    {
        if (!$this->request->isPost()) {
        	return $this->redirect()->toRoute('users/setting');
        }
        
        $post = $this->request->getPost();
        
        //Purify html
        $post = $this->purifyHtml($post);
        
        //get the relative table and form
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUser($post->id);
        
        $form = $this->getServiceLocator()->get('UserSetForm');
        
        $form->setData($post);
        
        //validate the telephone no
        $flag = $this->validateTel($post->telephone1) ? $this->validateTel($post->telephone2) : false;
        
        
        if (!$form->isValid() || !$flag) {

            return $this->redirect()->toRoute('users/setting', array(
            		'action' => 'index',
            ));
//             $model = new ViewModel(array(
//             		'error' => true,
//             		'userSetForm'  => $form,
//             ));
//             $model->setTemplate('users/login/index');
//             return $model;
            
        }
        //$data = $form->getData();
        //exchange data
        $user = $this->exchangeArray($user, $post);
        
        // Save user
		$this->getServiceLocator()->get('UserTable')->saveUser($user);
		
        return $this->redirect()->toRoute('users/login', array(
            'action' => 'confirm'
        ));
    }
    
    protected function validateTel($tel){
        $flag = true;
        
        if ($tel){
        	$validator = new Regex(array('pattern' => '/(^(\d{3,4}-)?\d{7,8})$|(1[0-9][0-9]{9})$|(^(\d{3,4}-)?\d{7,8}-)/'));
        	$flag = $validator->isValid($tel);
        }
        
        return $flag;
    }
    
    /*
     * @param array 
     * return array
     */
    protected function purifyHtml($post){
        require_once 'vendor/htmlpurifier-4.6.0/library/HTMLPurifier.auto.php';
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        foreach ($post as $key => $value) {
        	$post[$key] = $purifier->purify($post[$key]);
        }
        return $post;
    }
    protected function exchangeArray($user, $data)
    {
        $user->id		= (isset($data['id'])) ? $data['id'] : $user->id;
		$user->first_name		= (isset($data['first_name'])) ? $data['first_name'] : $user->first_name;
		$user->last_name		= (isset($data['last_name'])) ? $data['last_name'] : $user->last_name;
		$user->email	= (isset($data['email'])) ? $data['email'] : $user->email;
		$user->password	= (isset($data['password'])) ? $data['password'] : $user->password;
		$user->filename	= (isset($data['filename'])) ? $data['filename'] : $user->filename;
		$user->thumbnail	= (isset($data['thumbnail'])) ? $data['thumbnail'] : $user->thumbnail;
		$user->create_time =  (isset($data['create_time'])) ? $data['create_time'] : $user->create_time;
		$user->last_modify = (isset($data['last_modify'])) ? $data['last_modify'] : $user->last_modify;
		$user->sex	= (isset($data['sex'])) ? $data['sex'] : $user->sex;
		$user->telephone1 =  (isset($data['telephone1'])) ? $data['telephone1'] : $user->telephone1;
		$user->telephone2 =  (isset($data['telephone2'])) ? $data['telephone2'] : $user->telephone2;
		$user->address =  (isset($data['address'])) ? $data['address'] : $user->address;
		$user->title =  (isset($data['title'])) ? $data['title'] : $user->title; 

		return $user;
    }

    public function confirmAction()
    {}
}
