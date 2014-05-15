<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/CAMEMISResizeImage.php';

class CAMEMISUploadDBAccess {

    public $GuId = null;
    public $fileName = null;
    public $fileType = null;
    public $fileSize = null;
    public $fileData = null;
    public $schoolUrl = null;
    public $objectId = null;
    public $fileIndex = null;
    public $fileArea = null;

    function __construct() {
        
    }

    static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function findBlobFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        $SQL->where("FILE_SHOW = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getGuIdFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        $SQL->where("ID = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->GUID : 0;
    }

    //@THORN Visal
    public static function getCountFileFromAcademicId($academicId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_school_upload', 'COUNT(*) AS C');
        if ($academicId)
            $SQL->where("ACADEMIC_ID = '" . $academicId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    //
    public static function getAllFileFromObjectId($objectId, $objectType, $academicId = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        if ($objectId)
            $SQL->where("OBJECT_ID = '" . $objectId . "'");

        if ($objectType)
            $SQL->where("FILE_AREA = '" . $objectType . "'");
        //@THORN Visal
        switch ($objectType) {
            case "ACADEMIC_SUBJECT":
                if ($academicId) {
                    $SQL->where("ACADEMIC_ID = '" . $academicId . "'");
                } else {
                    $SQL->where("ACADEMIC_ID = 0");
                }
                break;
            default:
                if ($academicId)
                    $SQL->where("ACADEMIC_ID = '" . $academicId . "'");
                break;
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getFileFromObjectId($objectId, $fileIndex = false, $fileArea = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        if ($objectId)
            $SQL->where("OBJECT_ID = '" . $objectId . "'");
        if ($fileIndex)
            $SQL->where("FILE_INDEX = '" . $fileIndex . "'");
        if ($fileArea)
            $SQL->where("FILE_AREA = '" . $fileArea . "'");

        return self::dbAccess()->fetchRow($SQL);
    }

    //@Sea Peng
    public static function getFilesFromObjectId($objectId, $fileIndex = false, $fileArea = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        if ($objectId)
            $SQL->where("OBJECT_ID = '" . $objectId . "'");
        if ($fileIndex)
            $SQL->where("FILE_INDEX = '" . $fileIndex . "'");
        if ($fileArea)
            $SQL->where("FILE_AREA = '" . $fileArea . "'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonUploadFile($params) {

        $username = isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : ""; // using an authentication mechanisim

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $fileArea = isset($params["area"]) ? $params["area"] : "";
        $academicId = isset($params["class"]) ? $params["class"] : ""; //@THORN Visal


        $this->fileArea = strtoupper($fileArea);
        $this->objectId = $objectId;
        $this->academicId = $academicId; //@THORN Visal

        $file_extension = "";

        if (isset($_FILES["uploaded_file_1"]["name"])) {
            $fileIndex = 1;
            $fileName = $_FILES["uploaded_file_1"]["name"];
            $fileType = $_FILES["uploaded_file_1"]["type"];
            $fileSize = intval($_FILES["uploaded_file_1"]["size"]);
            $fileData = $_FILES["uploaded_file_1"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_1"]['name']);
            $file_extension = $path_info["extension"];
        }

        if (isset($_FILES["uploaded_file_2"]["name"])) {
            $fileIndex = 2;
            $fileName = $_FILES["uploaded_file_2"]["name"];
            $fileType = $_FILES["uploaded_file_2"]["type"];
            $fileSize = intval($_FILES["uploaded_file_2"]["size"]);
            $fileData = $_FILES["uploaded_file_2"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_2"]['name']);
            $file_extension = $path_info["extension"];
        }

        if (isset($_FILES["uploaded_file_3"]["name"])) {
            $fileIndex = 3;
            $fileName = $_FILES["uploaded_file_3"]["name"];
            $fileType = $_FILES["uploaded_file_3"]["type"];
            $fileSize = intval($_FILES["uploaded_file_3"]["size"]);
            $fileData = $_FILES["uploaded_file_3"]["tmp_name"];
            $path_info = pathinfo($_FILES["uploaded_file_3"]['name']);
            $file_extension = $path_info["extension"];
        }

        if (!ctype_alnum($username) || !preg_match('/^(?:[a-z0-9_-]|\.(?!\.))+$/iD', $fileName)) {
            $error = true;
        }

        $error = false;
        $success = false;
        $errorFileType = true;
        $errorFileSize = true;

        $responseText = "";

        if ($file_extension) {
            if (checkPermittedFileFormat($file_extension)) {
                $errorFileType = false;
            } else {
                $responseText = FILE_TYPE_NOT_PERMITTED;
            }
        } else {
            $errorFileType = true;
        }

        if (!file_exists($fileData)) {
            $errorFileType = true;
        }

        if ($fileSize <= 10485760) {
            $errorFileSize = false;
        } else {
            $responseText = FILE_SIZE_MAX_PERMITTED;
        }

        if (!$errorFileType && !$errorFileSize) {
            $error = false;
            $success = true;
        }

        if (!$error) {

            $SAVEDATA = array();
            $this->fileIndex = $fileIndex;
            $this->fileName = $fileName;
            $this->fileType = $fileType;
            $this->fileSize = $fileSize;
            $this->fileData = $fileData;

            //@THORN Visal
            switch ($this->fileArea) {
                case "SCHOOL_DOCUMENT":
                    $ext = getFileExtension($this->fileName);
                    $newFileName = camemisId();

                    $SAVEDATA["FILE_NAME"] = addText($this->fileName);
                    $SAVEDATA["FILE_SIZE"] = addText($this->fileSize);
                    $SAVEDATA["FILE_TYPE"] = addText($this->fileType);
                    $SAVEDATA["FILE_SHOW"] = addText($newFileName . "." . $ext);
                    $SAVEDATA["FILE_INDEX"] = addText($this->fileIndex);
                    $SAVEDATA["FILE_AREA"] = $this->fileArea;
                    $SAVEDATA["OBJECT_ID"] = $this->objectId;
                    $SAVEDATA["ACADEMIC_ID"] = $this->academicId; // Visal THORN
                    $SAVEDATA["SCHOOL_URL"] = Zend_Registry::get('SERVER_NAME');
                    $SAVEDATA["POST_DATE"] = date("Y-m-d");
                    $SAVEDATA["USER_ID"] = Zend_Registry::get('USER')->ID;

                    self::dbAccess()->insert('t_school_upload', $SAVEDATA);
                    break;
                case "ACADEMIC_SUBJECT":
                    $ext = getFileExtension($this->fileName);
                    $newFileName = camemisId();

                    $SAVEDATA["FILE_NAME"] = addText($this->fileName);
                    $SAVEDATA["FILE_SIZE"] = addText($this->fileSize);
                    $SAVEDATA["FILE_TYPE"] = addText($this->fileType);
                    $SAVEDATA["FILE_SHOW"] = addText($newFileName . "." . $ext);
                    $SAVEDATA["FILE_INDEX"] = addText($this->fileIndex);
                    $SAVEDATA["FILE_AREA"] = $this->fileArea;
                    $SAVEDATA["OBJECT_ID"] = $this->objectId;
                    $SAVEDATA["ACADEMIC_ID"] = $this->academicId; // Visal THORN
                    $SAVEDATA["SCHOOL_URL"] = Zend_Registry::get('SERVER_NAME');
                    $SAVEDATA["POST_DATE"] = date("Y-m-d");
                    $SAVEDATA["USER_ID"] = Zend_Registry::get('USER')->ID;

                    self::dbAccess()->insert('t_school_upload', $SAVEDATA);
                    break;
                default:
                    //@Sea Peng 10.07.2013
                    self::deleteFileFromObjectId(
                            $this->objectId
                            , $this->fileIndex
                            , $this->fileArea);
                    //

                    $ext = getFileExtension($this->fileName);
                    $newFileName = camemisId();

                    $SAVEDATA["FILE_NAME"] = addText($this->fileName);
                    $SAVEDATA["FILE_SIZE"] = addText($this->fileSize);
                    $SAVEDATA["FILE_TYPE"] = addText($this->fileType);
                    $SAVEDATA["FILE_SHOW"] = addText($newFileName . "." . $ext);
                    $SAVEDATA["FILE_INDEX"] = addText($this->fileIndex);
                    $SAVEDATA["FILE_AREA"] = $this->fileArea;
                    $SAVEDATA["OBJECT_ID"] = $this->objectId;
                    $SAVEDATA["ACADEMIC_ID"] = $this->academicId; // Visal THORN
                    $SAVEDATA["SCHOOL_URL"] = Zend_Registry::get('SERVER_NAME');
                    $SAVEDATA["POST_DATE"] = date("Y-m-d");
                    $SAVEDATA["USER_ID"] = Zend_Registry::get('USER')->ID;

                    self::dbAccess()->insert('t_school_upload', $SAVEDATA);
                    break;
            }

            switch ($this->fileArea) {
                case "SCHOOL_LOGO":
                    try {
                        $image = new CAMEMISResizeImage($this->fileData);
                        $image->resize(array("width" => 150))->saveToFile(self::getMyFolder() . $newFileName . "");
                    } catch (NotAnImageException $e) {
                        printf("FILE PROVIDED IS NOT AN IMAGE, FILE PATH: %s", $this->fileData);
                    }

                    break;
                case "PERSONAL_IMAGE":
                    try {
                        $image = new CAMEMISResizeImage($this->fileData);
                        $image->resize(array("width" => 150))->saveToFile(self::getMyFolder() . $newFileName . "");
                    } catch (NotAnImageException $e) {
                        printf("FILE PROVIDED IS NOT AN IMAGE, FILE PATH: %s", $this->fileData);
                    }

                    break;
                default:
                    if (file_exists($this->fileData)) {
                        move_uploaded_file($this->fileData, self::getMyFolder() . $newFileName . "." . $ext);
                    }

                    break;
            }
        }
    }

    //@Sea Peng
    public static function deleteFileFromObjectId($objectId, $fileIndex = false, $fileArea = false) {

        $FILE_OBJECT = self::getFilesFromObjectId(
                        $objectId
                        , $fileIndex
                        , $fileArea
        );

        if ($FILE_OBJECT) {

            foreach ($FILE_OBJECT as $OBJECT) {
                self::setUnlink($OBJECT->FILE_SHOW);
                self::dbAccess()->delete('t_school_upload', array(
                    "OBJECT_ID='" . $OBJECT->OBJECT_ID . "'"
                    , "FILE_SHOW='" . $OBJECT->FILE_SHOW . "'"));
            }
        }
    }

    public static function deleteFile($blobId) {

        $BLOB_OBJECT = self::findBlobFromId($blobId);

        if ($BLOB_OBJECT) {

            self::setUnlink($blobId);
            self::dbAccess()->delete('t_school_upload', array("FILE_SHOW='" . $blobId . "'"));
        }
    }

    public static function setUnlink($file) {
        if (file_exists(self::getMyFolder() . $file . "")) {
            unlink(self::getMyFolder() . $file . "");
        }
    }

    public static function deleteAllFiles($objectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        $SQL->where("OBJECT_ID = '" . $objectId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                if ($value->FILE_SHOW)
                    self::deleteFile($value->FILE_SHOW);
            }
        }
    }

    public static function findLastId() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        $SQL->order('ID DESC');
        $SQL->limitPage(0, 1);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : 0;
    }

    public static function getMyFolder() {
        $folder = "";
        $explode = explode(".", $_SERVER['SERVER_NAME']);
        if (is_array($explode)) {
            $folder = "users/" . $explode[0] . "/attachment/";
        }

        return $folder;
    }

    public static function jsonAttachmentList($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $object = isset($params["object"]) ? $params["object"] : "";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array('*'));
        $SQL->where("OBJECT_ID = '" . $objectId . "'");
        $SQL->where("FILE_AREA = '" . strtoupper($object) . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;

        $data = array();
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->FILE_SHOW;
                $data[$i]["FILENAME"] = $value->FILE_NAME;
                $data[$i]["UPLOAD_FILE"] = $value->FILE_SHOW;
                $data[$i]["FILETYPE"] = $value->FILE_TYPE;
                $data[$i]["SIZE"] = self::bytesToSize($value->FILE_SIZE);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function bytesToSize($bytes, $precision = 2) {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision) . ' KB';
        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision) . ' MB';
        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision) . ' GB';
        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $precision) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

}

?>