<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Headers;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;
use Users\Model\User;
use Users\Model\UserTable;
use Users\Model\Upload;
use Users\Model\ImageUpload;
use Users\Model\ImageUploadTable;
use ZendGData\ClientLogin;
use ZendGData\Photos;

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
        if ($config instanceof Traversable) {
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
    	$userTable = $this->getServiceLocator()->get('UserTable');
    	$user_id = (int)$this->getAuthService()
    	->getStorage()
    	->read();
    	//$user = $userTable->getUserByEmail($user_email);
    	$user = $userTable->getUser($user_id);
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
    
    			//Verify is this a empty submit
    			// file uploaded & is valid
    			if ($adapter->isUploaded() && $adapter->isValid()) {
    
    				// Set the system name with the user information for the file
    				$sysName = 'headphoto'.$user->id;
    				$adapter->addFilter('Rename', $sysName, $uploadFile['name']);
    
    				// Save the update photo
    				if ($adapter->receive($uploadFile['name'])) {
    
    					$user->filename = $sysName;
    					$user->thumbnail = $this->generateThumbnail($sysName);
    					$userTable = $this->getServiceLocator()->get('UserTable');
    					$userTable->saveUser($user);
    					
     					//$fileclass = new \stdClass();
     					//$fileclass->userid= $user->id;
     					$datas = array('userid' => $user->id);
    					//$datas= {'userid'=>$user->id};
    					$response = $this->getEvent()->getResponse();
    					$response->setContent(json_encode($datas));
    					
    					return $response;
    				}
    			}
    		}
    	}
    }
    
    
    
    public function generateThumbnail($imageFileName)
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

    public function deletePhoto($id)
    {
        // $uploadId = $this->params()->fromRoute('id');
        // $uploadTable = $this->getServiceLocator()
        // ->get('ImageUploadTable');
        // $upload = $uploadTable->getUpload($uploadId);
        $id = (int) $id;
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUser($id);
        $uploadPath = $this->getFileUploadLocation();
        // Remove File
        unlink($uploadPath . "/" . $user->filename);
        unlink($uploadPath . "/" . $user->thumbnail);
        
        // Delete Records
        // $uploadTable->deleteUpload($uploadId);
        
        // return $this->redirect()->toRoute('users/media');
    }

    public function viewAction()
    {
        $userId = $this->params()->fromRoute('id');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUser($userId);
//         $uploadTable = $this->getServiceLocator()->get('ImageUploadTable');
//         $upload = $uploadTable->getUpload($uploadId);
        
        $viewModel = new ViewModel(array(
            'user' => $user
        ));
        return $viewModel;
    }

    public function showImageAction()
    {
        $userId = (int)$this->params()->fromRoute('id');
        $userTable = $this->getServiceLocator()->get('UserTable');
        $user = $userTable->getUser($userId);
        
        //sleep(5);
        
        // Fetch Configuration from Module Config
        $uploadPath = $this->getFileUploadLocation();
        
        if ($this->params()->fromRoute('subaction') == 'thumb') {
            $filename = $uploadPath . "/" . $user->thumbnail;
        } else {
            $filename = $uploadPath . "/" . $user->filename;
        }
        
        $file = file_get_contents($filename);
        
        // Directly return the Response
        $response = $this->getEvent()->getResponse();
        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment;filename="' . $user->filename . '"'
        )
        );
        $response->setContent($file);
        
        return $response;
    }

    public function getGooglePhotos()
    {
        $adapter = new \Zend\Http\Client\Adapter\Curl();
        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => false
            )
        ));
        
        $httpClient = new \ZendGData\HttpClient();
        
        $httpClient->setAdapter($adapter);
        
        $client = \ZendGData\ClientLogin::getHttpClient(self::GOOGLE_USER_ID, self::GOOGLE_PASSWORD, \ZendGData\Photos::AUTH_SERVICE_NAME, $httpClient);
        
        $gp = new \ZendGData\Photos($client);
        
        $gAlbums = array();
        
        try {
            $userFeed = $gp->getUserFeed(self::GOOGLE_USER_ID);
            foreach ($userFeed as $userEntry) {
                
                $albumId = $userEntry->getGphotoId()->getText();
                $gAlbums[$albumId]['label'] = $userEntry->getTitle()->getText();
                
                $query = $gp->newAlbumQuery();
                $query->setUser(self::GOOGLE_USER_ID);
                $query->setAlbumId($albumId);
                
                $albumFeed = $gp->getAlbumFeed($query);
                
                foreach ($albumFeed as $photoEntry) {
                    
                    $photoId = $photoEntry->getGphotoId()->getText();
                    if ($photoEntry->getMediaGroup()->getContent() != null) {
                        $mediaContentArray = $photoEntry->getMediaGroup()->getContent();
                        $photoUrl = $mediaContentArray[0]->getUrl();
                    }
                    
                    if ($photoEntry->getMediaGroup()->getThumbnail() != null) {
                        $mediaThumbnailArray = $photoEntry->getMediaGroup()->getThumbnail();
                        $thumbUrl = $mediaThumbnailArray[0]->getUrl();
                    }
                    
                    $albumPhoto = array();
                    $albumPhoto['id'] = $photoId;
                    $albumPhoto['photoUrl'] = $photoUrl;
                    $albumPhoto['thumbUrl'] = $thumbUrl;
                    
                    $gAlbums[$albumId]['photos'][] = $albumPhoto;
                }
            }
        } catch (App\HttpException $e) {
            echo "Error: " . $e->getMessage() . "<br />\n";
            if ($e->getResponse() != null) {
                echo "Body: <br />\n" . $e->getResponse()->getBody() . "<br />\n";
            }
            // In new versions of Zend Framework, you also have the option
            // to print out the request that was made. As the request
            // includes Auth credentials, it's not advised to print out
            // this data unless doing debugging
            // echo "Request: <br />\n" . $e->getRequest() . "<br />\n";
        } catch (App\Exception $e) {
            echo "Error: " . $e->getMessage() . "<br />\n";
        }
        
        return $gAlbums;
    }

    public function getYoutubeVideos()
    {
        $adapter = new \Zend\Http\Client\Adapter\Curl();
        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_SSL_VERIFYPEER => false
            )
        ));
        
        $httpClient = new \ZendGData\HttpClient();
        $httpClient->setAdapter($adapter);
        
        $client = \ZendGData\ClientLogin::getHttpClient(self::GOOGLE_USER_ID, self::GOOGLE_PASSWORD, \ZendGData\YouTube::AUTH_SERVICE_NAME, $httpClient);
        
        $yt = new \ZendGData\YouTube($client);
        $yt->setMajorProtocolVersion(2);
        $query = $yt->newVideoQuery();
        $query->setOrderBy('relevance');
        $query->setSafeSearch('none');
        $query->setVideoQuery('Zend Framework');
        
        // Note that we need to pass the version number to the query URL function
        // to ensure backward compatibility with version 1 of the API.
        $videoFeed = $yt->getVideoFeed($query->getQueryUrl(2));
        
        $yVideos = array();
        foreach ($videoFeed as $videoEntry) {
            $yVideo = array();
            $yVideo['videoTitle'] = $videoEntry->getVideoTitle();
            $yVideo['videoDescription'] = $videoEntry->getVideoDescription();
            $yVideo['watchPage'] = $videoEntry->getVideoWatchPageUrl();
            $yVideo['duration'] = $videoEntry->getVideoDuration();
            $videoThumbnails = $videoEntry->getVideoThumbnails();
            
            $yVideo['thumbnailUrl'] = $videoThumbnails[0]['url'];
            $yVideos[] = $yVideo;
        }
        return $yVideos;
    }
}    

