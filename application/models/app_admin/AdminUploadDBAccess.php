<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/CAMEMISResizeImage.php';

class AdminUploadDBAccess {

    public $GuId = null;
    public $fileName = null;
    public $fileType = null;
    public $fileSize = null;
    public $fileData = null;
    public $schoolUrl = null;
    public $objectId = null;
    public $fileIndex = null;
    public $fileArea = null;

    function __construct()
    {
        
    }

    static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function findBlobFromId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        $SQL->where("FILE_SHOW = ?", $Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getGuIdFromId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        $SQL->where("ID = ?", $Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->GUID : 0;
    }

    public static function getFileFromObjectId($objectId, $fileIndex = false, $fileArea = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        if ($objectId)
            $SQL->where("OBJECT_ID = ?", $objectId);
        if ($fileIndex)
            $SQL->where("FILE_INDEX = '" . $fileIndex . "'");
        if ($fileArea)
            $SQL->where("FILE_AREA = '" . $fileArea . "'");

        return self::dbAccess()->fetchRow($SQL);
    }

    //@Sea Peng
    public static function getFilesFromObjectId($objectId, $fileIndex = false, $fileArea = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        if ($objectId)
            $SQL->where("OBJECT_ID = ?", $objectId);
        if ($fileIndex)
            $SQL->where("FILE_INDEX = '" . $fileIndex . "'");
        if ($fileArea)
            $SQL->where("FILE_AREA = '" . $fileArea . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonUploadFile($params)
    {

        $username = isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : ""; // using an authentication mechanisim

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $fileArea = isset($params["area"]) ? addText($params["area"]) : "";

        $this->fileArea = strtoupper($fileArea);
        $this->objectId = $objectId;

        $file_extension = "";

        if (isset($_FILES["uploaded_file_1"]["name"]))
        {
            $fileIndex = 1;
            $fileName = $_FILES["uploaded_file_1"]["name"];
            $fileType = $_FILES["uploaded_file_1"]["type"];
            $fileSize = intval($_FILES["uploaded_file_1"]["size"]);
            $fileData = $_FILES["uploaded_file_1"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_1"]['name']);
            $file_extension = isset($path_info["extension"]) ? $path_info["extension"] : "";
        }
        elseif (isset($_FILES["uploaded_file_2"]["name"]))
        {
            $fileIndex = 2;
            $fileName = $_FILES["uploaded_file_2"]["name"];
            $fileType = $_FILES["uploaded_file_2"]["type"];
            $fileSize = intval($_FILES["uploaded_file_2"]["size"]);
            $fileData = $_FILES["uploaded_file_2"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_2"]['name']);
            $file_extension = isset($path_info["extension"]) ? $path_info["extension"] : "";
            $oldObject = self::getFileFromObjectId($objectId, $fileArea, 2);
        }
        elseif (isset($_FILES["uploaded_file_3"]["name"]))
        {
            $fileIndex = 3;
            $fileName = $_FILES["uploaded_file_3"]["name"];
            $fileType = $_FILES["uploaded_file_3"]["type"];
            $fileSize = intval($_FILES["uploaded_file_3"]["size"]);
            $fileData = $_FILES["uploaded_file_3"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_3"]['name']);
            $file_extension = isset($path_info["extension"]) ? $path_info["extension"] : "";
        }

        if (!ctype_alnum($username) || !preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $fileName))
        {
            $error = true;
        }

        $error = false;
        $success = false;
        $errorFileType = true;
        $errorFileSize = true;

        $responseText = "";

        if ($file_extension)
        {
            if (checkPermittedFileFormat($file_extension))
            {
                $errorFileType = false;
            }
            else
            {
                $responseText = "File type not permitted";
            }
        }
        else
        {
            $errorFileType = true;
        }

        if (!file_exists($fileData))
        {
            $errorFileType = true;
        }

        if ($fileSize <= 10485760)
        {
            $errorFileSize = false;
        }
        else
        {
            $responseText = "File type not permitted";
        }

        if (!$errorFileType && !$errorFileSize)
        {
            $error = false;
            $success = true;
        }

        if (!$error)
        {

            $entries = self::getFilesFromObjectId($this->objectId, $this->fileIndex, $this->fileArea);
            if ($entries)
            {
                foreach ($entries as $value)
                {
                    self::setUnlink($value->FILE_SHOW);
                    self::dbAccess()->delete('t_help_image', array("FILE_SHOW='" . $value->FILE_SHOW . "'"));
                }
            }

            $SAVEDATA = array();
            $this->fileIndex = $fileIndex;
            $this->fileName = $fileName;
            $this->fileType = $fileType;
            $this->fileSize = $fileSize;
            $this->fileData = $fileData;

            if (file_exists($this->fileData))
            {
                $ext = getFileExtension($this->fileName);
                $newfileName = camemisId();
                $SAVEDATA["FILE_NAME"] = addText($this->fileName);
                $SAVEDATA["FILE_SIZE"] = addText($this->fileSize);
                $SAVEDATA["FILE_TYPE"] = addText($this->fileType);
                $SAVEDATA["FILE_SHOW"] = addText($newfileName . "." . $ext);
                $SAVEDATA["FILE_INDEX"] = addText($this->fileIndex);
                $SAVEDATA["FILE_AREA"] = $this->fileArea;
                $SAVEDATA["OBJECT_ID"] = $this->objectId;
                self::dbAccess()->insert('t_help_image', $SAVEDATA);

                move_uploaded_file($this->fileData, self::getMyFolder() . $newfileName . "." . $ext);
            }
        }
    }

    //@Sea Peng
    public static function deleteFileFromObjectId($objectId, $fileIndex = false, $fileArea = false)
    {

        $FILE_OBJECT = self::getFilesFromObjectId(
                        $objectId
                        , $fileIndex
                        , $fileArea
        );

        if ($FILE_OBJECT)
        {

            foreach ($FILE_OBJECT as $OBJECT)
            {
                self::setUnlink($OBJECT->FILE_SHOW);
                self::dbAccess()->delete('t_help_image', array(
                    "OBJECT_ID='" . $OBJECT->OBJECT_ID . "'"
                    , "FILE_SHOW='" . $OBJECT->FILE_SHOW . "'"));
            }
        }
    }

    public static function deleteFile($blobId)
    {

        $BLOB_OBJECT = self::findBlobFromId($blobId);

        if ($BLOB_OBJECT)
        {
            self::setUnlink($blobId);
            self::dbAccess()->delete('t_help_image', array("FILE_SHOW='" . $blobId . "'"));
        }
    }

    public static function setUnlink($file)
    {
        if (file_exists(self::getMyFolder() . $file . ""))
        {
            unlink(self::getMyFolder() . $file . "");
        }
    }

    public static function deleteAllFiles($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        $SQL->where("OBJECT_ID = ?", $objectId);
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
        {
            foreach ($result as $value)
            {
                if ($value->FILE_SHOW)
                    self::deleteFile($value->FILE_SHOW);
            }
        }
    }

    public static function findLastId()
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        $SQL->order('ID DESC');
        $SQL->limitPage(0, 1);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : 0;
    }

    public static function getMyFolder()
    {
        $folder = "";
        $explode = explode(".", $_SERVER['SERVER_NAME']);
        if (is_array($explode))
        {
            $folder = "users/admin/images/";
        }

        return $folder;
    }

    public static function jsonAttachmentList($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $object = isset($params["object"]) ? addText($params["object"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help_image", array('*'));
        $SQL->where("OBJECT_ID = ?", $objectId);
        $SQL->where("FILE_AREA = '" . strtoupper($object) . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;

        $data = array();
        if ($result)
        {
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->FILE_SHOW;
                $data[$i]["FILENAME"] = $value->FILE_NAME;
                $data[$i]["UPLOAD_FILE"] = $value->FILE_SHOW;
                $data[$i]["FILETYPE"] = $value->FILE_TYPE;
                $data[$i]["SIZE"] = self::bytesToSize($value->FILE_SIZE);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function bytesToSize($bytes, $precision = 2)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte))
        {
            return $bytes . ' B';
        }
        elseif (($bytes >= $kilobyte) && ($bytes < $megabyte))
        {
            return round($bytes / $kilobyte, $precision) . ' KB';
        }
        elseif (($bytes >= $megabyte) && ($bytes < $gigabyte))
        {
            return round($bytes / $megabyte, $precision) . ' MB';
        }
        elseif (($bytes >= $gigabyte) && ($bytes < $terabyte))
        {
            return round($bytes / $gigabyte, $precision) . ' GB';
        }
        elseif ($bytes >= $terabyte)
        {
            return round($bytes / $terabyte, $precision) . ' TB';
        }
        else
        {
            return $bytes . ' B';
        }
    }

}

?>