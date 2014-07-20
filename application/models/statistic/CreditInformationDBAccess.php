<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 30/12/2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/academic/AcademicDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/AcademicDateDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/training/TrainingDBAccess.php";
require_once "models/CamemisTypeDBAccess.php";
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CreditInformationDBAccess {

    public $DB_ACCESS = null;
    public $_TOSTRING = null;
    public $SELECT = null;
    public $dataforjson = null;
    public $data = null;
    public $DB_GRADE = null;
    public $DB_STUDENT = null;
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getAllSubjectsByGradeSchoolyear($gradeSchoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', array('*'));
        $SQL->where("PARENT='" . $gradeSchoolyearId . "'");
        $SQL->where("OBJECT_TYPE='SUBJECT'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function countStudentCreditInformationByStatus($creditStatus, $subjectId, $gradeSchoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear_subject'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CREDIT_ACADEMIC_ID=B.ID', array());

        $SQL->where("B.PARENT = '" . $gradeSchoolyearId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("A.CREDIT_STATUS = '" . $creditStatus . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentCreditInformationByStatus($creditStatus, $gradeSchoolyearId) {

        $subjects = self::getAllSubjectsByGradeSchoolyear($gradeSchoolyearId);

        $RESULT = "[";
        if ($subjects) {
            $i = 0;
            foreach ($subjects as $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value->NAME . "'";
                $RESULT .= ",'y':" . self::countStudentCreditInformationByStatus($creditStatus, $value->SUBJECT_ID, $gradeSchoolyearId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getDataSetCreditInformation($gradeSchoolyearId) {
        $CREDIT_STATUS = array("0" => "Ongoing", "1" => "Incompleted", "2" => "Completed");
        $i = 0;
        $DATASET = "[";
        foreach ($CREDIT_STATUS as $key => $value) {
            $VALUES = self::getStudentCreditInformationByStatus($key, $gradeSchoolyearId);
            $DATASET .=$i ? "," : "";
            $DATASET .= "{'key' : '" . $value . "','values':" . $VALUES . "}";
            $i++;
        }
        $DATASET .= "]";

        return $DATASET;
    }

}

?>
