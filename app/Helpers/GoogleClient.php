<?php

namespace App\Helpers;

use App\Models\GoogleDriveFolder;

class GoogleClient
{
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri = 'https://developers.google.com/oauthplayground';
    protected $scopes = array(
        'https://www.googleapis.com/auth/drive',
        'https://www.googleapis.com/auth/drive.file',
        'https://www.googleapis.com/auth/drive.appdata',
        'https://www.googleapis.com/auth/drive.photos.readonly',
    );
    protected $client;
    protected $service;
    protected $refresh_token;
    protected $access_token;

    /**
     *  Construct an easy to use Google API client.
     */
    public function __construct()
    {
        $this->client_id = env('GOOGLE_CLIENT_ID');
        $this->client_secret = env('GOOGLE_CLIENT_SECRET');
        $this->refresh_token = env('GOOGLE_DRIVE_REFRESH_TOKEN');
        $this->access_token = env('GOOGLE_DRIVE_ACCESS_TOKEN');

        $this->client = new \Google_Client();
        $this->client->setClientId($this->client_id);
        $this->client->setClientSecret($this->client_secret);
        $this->client->setRedirectUri($this->redirect_uri);
        $this->client->setAccessType('offline');
        $this->client->setScopes($this->scopes);
        $this->client->setAccessToken($this->access_token);
        $this->client->refreshToken($this->refresh_token);
    }

    /**
     *   Check if the user is logged in or not
     */
    public function isLoggedIn()
    {
        if (isset($_SESSION[$this->access_token])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  Authenticate the client and set access token and refresh after login
     *  @param string $code redirected code
     */
    public function authenticate($code)
    {
        $this->client->authenticate($code);
        $_SESSION[$this->access_token] = $this->client->getAccessToken();
        $_SESSION[$this->refresh_token] =  $this->client->getRefreshToken();
    }

    /**
     *  To set access token explicitely
     *  @param string $accessToken access token
     */
    public function setAccessToken($accessToken)
    {
        $this->client->setAccessToken($accessToken);
    }

    /**
     *  To get authentication URL if not in session
     *  @return string
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     *  Returns the google client object
     *  @return Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     *  Initilize drive services object
     */
    public function initDriveService()
    {
        $this->service = new \Google_Service_Drive($this->client);
    }

    /**
     *  Create folder at google drive
     *  @param string $parentId parent folder id or root where folder will be created
     *  @param string $folderName folder name to create
     *  @return string id of created folder
     */
    public function createFolder($folderName, $organization, $creatorId, $parentId = null)
    {
        if ($parentId) {
            $gdFolder = GoogleDriveFolder::find($parentId);
            $parentFolder = $gdFolder->folder_id;
        } else {
            $parentFolder = env('GOOGLE_DRIVE_ROOT_FOLDER_ID');
        }

        // Setting File Matadata
        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            'name' => $folderName,
            'parents' => array($parentFolder),
            'mimeType' => 'application/vnd.google-apps.folder'
        ));

        // Creating Folder with given Matadata and asking for ID field as result
        $file = $this->service->files->create($fileMetadata, array('fields' => 'id'));

        $folder = new GoogleDriveFolder();
        $folder->organization_id = $organization->id;
        $folder->parent_id = $parentId;
        $folder->folder_id = $file->id;
        $folder->created_by_id = $creatorId;
        $folder->name = $folderName;
        $folder->save();

        return $folder;
    }

    public function renameFolder($folderId, $newName)
    {
        $folder = $this->service->files->get($folderId, array("fields" => "name"));
        $folder->setName($newName);
        $updatedFolder = $this->service->files->update($folderId, $folder, array("fields" => "name"));

        return $updatedFolder ? 'updated' : 'failed to update';
    }

    public function transferFolder($folderId, $newParentId, $oldParentId)
    {
        $file = new \Google_Service_Drive_DriveFile([
            'fields' => 'name, parents'
        ]);

        try {
            $file = $this->service->files->update($folderId, $file, [
                'addParents' => $newParentId,
                'removeParents' => $oldParentId,
                'fields' => 'id, parents',
            ]);
            return $file->id;
        } catch (Exception $e) {
            return 'An error occurred while updating the file parent folder: ' . $e->getMessage();
        }
    }

    public function deleteFolder($folderId)
    {
        $deleted = $this->service->files->delete($folderId);

        return 'successfully deleted';
    }

    public function shareFolder($folderId, $email)
    {
        $permission = new \Google_Service_Drive_Permission();
        $permission->setType('user');
        $permission->setRole('writer');
        $permission->setEmailAddress($email);

        $result = $this->service->permissions->create($folderId, $permission);

        return $result->id;
    }

    public function removeAccess($folderId, $permissionId)
    {
        $this->service->permissions->delete($folderId, $permissionId);

        return;
    }

    public function checkFolder($folderName)
    {
        $q = "mimeType='application/vnd.google-apps.folder' and trashed=false and name='" . $folderName . "'";
        $results = $this->service->files->listFiles(array('q' => $q));

        if (count($results->getFiles()) == 0) {
            return;
        } else {
            return $results->getFiles()[0]->getId();
        }
    }

    /**
     * ///////////////////////////////
     * // Start file functions here //
     * //////////////////////////////
     */

    /**
     *  Get the list of files or folders or both from given folder or root
     *  @param string $search complete or partial name of file or folder to search
     *  @param string $parentId parent folder id or root from which the list of
                                files or folders or both will be generated
     *  @param string $type='all' file or folder
     *  @return array list of files or folders or both from given parent directory
     */
    public function listFilesFolders($parentId, $type = 'files')
    {
        $query = '';
        // Checking if search is empty the use 'contains' condition if search is empty (to get all files or folders).
        // Otherwise use '='  condition
        //$condition = $search != '' ? '=' : 'contains';

        // Search all files and folders otherwise search in root or  any folder
        $query .= $parentId != 'all' ? "'" . $parentId . "' in parents" : "";

        // Check if want to search files or folders or both
        switch ($type) {
            case "files":
                $query .= $query != '' ? ' and ' : '';
                $query .= "mimeType != 'application/vnd.google-apps.folder'";
                break;

            case "folders":
                $query .= $query != '' ? ' and ' : '';
                $query .= "mimeType = 'application/vnd.google-apps.folder'";
                break;
            default:
                $query .= "";
                break;
        }

        // Make sure that not list trashed files
        $query .= $query != '' ? ' and trashed = false' : 'trashed = false';
        $optParams = array('q' => $query, 'pageSize' => 1000);

        // Returns the list of files and folders as object
        $results = $this->service->files->listFiles($optParams);

        // Return false if nothing is found
        if (count($results->getFiles()) == 0) {
            return array();
        }

        // Converting array to object
        $result = array();
        foreach ($results->getFiles() as $file) {
            $fileContent = $this->service->files->get($file->getId(), array("fields" => "webContentLink"))->getWebContentLink();;
            $url = $fileContent->webContentLink;
            $url = str_replace("export=download", "export=view", $url);

            $result[$file->getId()] = [
                'name' => $file->getName(),
                'url' => $url
            ];
        }
        return $result;
    }



    /**
     *  Upload file to given folder
     *  @param string $parentId parent folder id or root where folder will be upload
     *  @param string $filePath file local path of file which will be upload
     *  @param string $fileName file name of the uploaded copy at google drive
     *  @return string id of uploaded file
     */
    public function uploadFile($parentId, $content, $name, $mime)
    {
        // Creating file matadata
        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            'name' => $name,
            'parents' => array($parentId)
        ));

        // Uploading file and getting uploaded file ID as result
        $file = $this->service->files->create($fileMetadata, array(
            'data' => $content,
            'mimeType' => $mime,
            'uploadType' => 'multipart',
            'fields' => 'id'
        ));

        // Returning file id of newly uploaded file
        return $file->id;
    }

    public function renameFile($fileId, $newName)
    {
        $file = new \Google_Service_Drive_DriveFile();
        $file->setName($newName);

        $updatedFile = $this->service->files->update($fileId, $file, array("fields" => "name"));;

        return $updatedFile->getName();
    }

    public function transferFile($fileId, $newParentId, $oldParentId)
    {
        $file = new \Google_Service_Drive_DriveFile([
            'fields' => 'name, parents'
        ]);

        try {
            $file = $this->service->files->update($fileId, $file, [
                'addParents' => $newParentId,
                'removeParents' => $oldParentId,
                'fields' => 'id, parents',
            ]);
            return $file->id;
        } catch (Exception $e) {
            return 'An error occurred while updating the file parent folder: ' . $e->getMessage();
        }
    }

    public function deleteFile($fileId)
    {
        $this->service->files->delete($fileId);

        return 'successfully deleted';
    }

    public function shareFile($fileId, $email)
    {
        $permission = new \Google_Service_Drive_Permission();
        $permission->setType('user');
        $permission->setRole('writer');
        $permission->setEmailAddress($email);

        // Add the permission to the file
        $result = $this->service->permissions->create($fileId, $permission);

        return $result->id;
    }

    public function removeFileAccess($fileId, $permissionId)
    {
        $this->service->permissions->delete($fileId, $permissionId);

        return;
    }

    public function generateUrl($fileId)
    {
        $fileContent = $this->service->files->get($fileId, array("fields" => "webContentLink"))->getWebContentLink();;
        $url = $fileContent;
        $url = str_replace("export=download", "export=view", $url);

        return $url;
    }
}
