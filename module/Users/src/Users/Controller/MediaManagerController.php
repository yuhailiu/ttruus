<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Tools\MyUtils;
use Zend\Validator\EmailAddress;
use Zend\Stdlib\ArrayUtils;

class MediaManagerController extends AbstractActionController
{

    protected $storage;

    protected $authservice;

    protected $photos;

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        
        return $this->authservice;
    }

    public function getFileUploadLocation()
    {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        if ($config instanceof \Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! empty($config['module_config']['image_upload_location'])) {
            return $config['module_config']['image_upload_location'];
        } else {
            return FALSE;
        }
    }

    public function indexAction()
    {
        $uploadTable = $this->getServiceLocator()->get('ImageUploadTable');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $userEmail = $this->getAuthService()
            ->getStorage()
            ->read();
        $user = $userTable->getUserByEmail($userEmail);
        
        $viewModel = new ViewModel(array(
            'myUploads' => $uploadTable->getUploadsByUserId($user->id)
        ));
        
        return $viewModel;
    }

    public function processUploadAction()
    {
        $email = $this->getAuthService()
            ->getStorage()
            ->read();
        $validate = new EmailAddress();
        if (! $email || ! $validate->isValid($email)) {
            return $this->redirect()->toRoute('users/login');
        }
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $uploadFile = $this->params()->fromFiles('imageupload');
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                // Fetch Configuration from Module Config
                $uploadPath = $this->getFileUploadLocation();
                
                // Prepare to save Uploaded file
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                $adapter->setDestination($uploadPath);
                
                // Limit the extensions to jpg,bmp,gif,jpeg and png files
                $adapter->addValidator('Extension', false, 'jpg, png, bmp, gif, jpeg');
                
                // Limit the size of all files to be uploaded to maximum 2MB and mimimum 20 bytes
                $adapter->addValidator('FilesSize', false, array(
                    'min' => 20,
                    'max' => '2MB'
                ));
                
                // Verify is this a empty submit
                // file uploaded & is valid
                if ($adapter->isUploaded() && $adapter->isValid()) {
                    $sysName = "defaultphoto.jpg";
                    // get userinfo by email
                    $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
                    try {
                        $userInfo = $userInfoTable->getUserInfoByEmail($email);
                    } catch (\Exception $e) {
                        MyUtils::writelog("can't get userinfor from upload controller" . $e);
                        return false;
                    }
                    
                    // if the user has filename use it, otherwise create a new one
                    if ('defaultphoto.jpg' == $userInfo->filename || null == $userInfo->filename) {
                        // Set the system name with Unix time add a 3 bit digitals
                        $sysName = mktime() . rand(100, 999);
                    } else {
                        // keep the original
                        $sysName = $userInfo->filename;
                    }
                    
                    $adapter->addFilter('Rename', $sysName, $uploadFile['name']);
                    
                    // Save the update photo
                    if ($adapter->receive($uploadFile['name'])) {
                        
                        // genarate the thumbfile and give the name to userInfo
                        $userInfo->thumbnail = $this->generateThumbnail($sysName);
                        
                        // update the DB if it's a new file
                        if ($sysName != $userInfo->filename) {
                            $userInfo->filename = $sysName;
                            try {
                                $userInfoTable->updateUserInfo($userInfo);
                            } catch (\Exception $e) {
                                MyUtils::writelog("can't write userinfo to DB from upload controller" . $e);
                                return false;
                            }
                        }
                        
                        // return json response
                        $data = array(
                            'email' => $email
                        );
                        // $datas= {'userid'=>$user->id};
                        $response = $this->getEvent()->getResponse();
                        $response->setContent(json_encode($data));
                        
                        return $response;
                    }
                }
            }
        }
    }

    /**
     * generate thumbfile from image
     *
     * @param string $imageFileName            
     * @return string thumbfile name
     */
    protected function generateThumbnail($imageFileName)
    {
        $path = $this->getFileUploadLocation();
        $sourceImageFileName = $path . '/' . $imageFileName;
        $thumbnailFileName = 'tn_' . $imageFileName;
        
        $imageThumb = $this->getServiceLocator()->get('WebinoImageThumb');
        $thumb = $imageThumb->create($sourceImageFileName, $options = array());
        $thumb->resize(130, 130);
        $thumb->save($path . '/' . $thumbnailFileName);
        
        return $thumbnailFileName;
    }

    public function uploadAction()
    {
        $form = $this->getServiceLocator()->get('ImageUploadForm');
        $viewModel = new ViewModel(array(
            'form' => $form
        ));
        return $viewModel;
    }


    /**
     * get id from Http
     *
     * @return Response with pic
     */
    public function showImageAction()
    {
        $email = $this->params()->fromRoute('email');
        // $email = $_GET['email'];
        $validation = new EmailAddress();
        // if it's validate user request, then go
        if (! $this->getAuthService()
            ->getStorage()
            ->read() || ! $validation->isValid($email)) {
            return $this->returnResponse(false);
        }
        
        // Fetch Configuration from Module Config
        $uploadPath = $this->getFileUploadLocation();
        
        // if subaction is logo,bind the logo path
        if ($this->params()->fromRoute('subaction') == 'logo') {
            // add the logo's filename to the $filename
            // $filename = $uploadPath . "/" . logo . $userId;
            $filename = $uploadPath . "/" . logo;
        } else {
            
            // check the filename from table and bind it
            $userInfoTable = $this->getServiceLocator()->get('UserInfoTable');
            try {
                $userInfo = $userInfoTable->getUserInfoByEmail($email);
            } catch (\Exception $e) {
                return $this->returnResponse("can't find the userInfo of $email");
            }
            
            // bind the thumbnail path
            if ($this->params()->fromRoute('subaction') == 'thumb') {
                $filename = $uploadPath . "/" . $userInfo->thumbnail;
            } else {
                $filename = $uploadPath . "/" . $userInfo->filename;
            }
        }
        
        $file = file_get_contents($filename);
        if ($file) {
            return $this->returnFileResponse($file);
            ;
        } else {
            return $this->returnResponse("can't open the file");
        }
    }

    /**
     * put the file to reponse
     *
     * @param File $file            
     * @return response
     * @author yuhai liu
     */
    protected function returnFileResponse($file)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename="' . $user->filename . '"'
        ));
        $response->setContent($file);
        
        return $response;
    }

    /**
     * put the mix var to reponse
     *
     * @param mix $data            
     * @return response
     */
    protected function returnResponse($data)
    {
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->setContent($data);
        return $response;
    }
}    

