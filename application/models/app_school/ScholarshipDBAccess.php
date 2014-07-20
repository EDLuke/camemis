<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 09.05.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class ScholarshipDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function findObjectFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_scholarship', array('*'));
        $SQL->where("ID = ?", $Id);

        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadScholarship($Id) {

        $result = self::findObjectFromId($Id);
        $data = array();
        if ($result) {

            $data["NAME"] = $result->NAME;
            $data["SCHOLARSHIP_VALUE"] = $result->SCHOLARSHIP_VALUE;
            $data["CONTENT"] = $result->CONTENT;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    //enrollment student scholarship 

    public static function getSQLStudentEnrollmentScholarship($params) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $globalSearch = isset($params["globalSearch"]) ? addText($params["globalSearch"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $scholarshipType = isset($params["scholarshipType"]) ? addText($params["scholarshipType"]) : "";
        $campusId = isset($params["campus"]) ? $params["campus"] : "";
        $classId = isset($params["classId"]) ? $params["classId"] : "";
        $gradeId = isset($params["gradeId"]) ? $params["gradeId"] : "";
        $trainingId = isset($params["trainingId"]) ? $params["trainingId"] : "";
        $programId = isset($params["programId"]) ? $params["programId"] : "";
        $levelId = isset($params["levelId"]) ? $params["levelId"] : "";
        $termId = isset($params["termId"]) ? $params["termId"] : "";
        $trainingclassId = isset($params["trainingclassId"]) ? $params["trainingclassId"] : "";

        if ($academicId) {
            $academicObject = AcademicDBAccess::findGradeFromId($academicId);
            switch ($academicObject->OBJECT_TYPE) {
                case "CAMPUS":
                    $campusId = $academicObject->ID;
                    break;
                case "GRADE":
                    $gradeId = $academicObject->ID;
                    break;
                case "SCHOOLYEAR":
                    $gradeSchoolyearId = $academicObject->ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
                case "SUBJECT":
                    $gradeSubjectId = $academicObject->ID;
                    break;
                case "CLASS":
                    $classId = $academicObject->ID;
                    break;
            }
        }

        if ($trainingId) {
            $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);

            switch ($trainingObject->OBJECT_TYPE) {
                case "PROGRAM":
                    $programId = $trainingObject->ID;
                    break;
                case "LEVEL":
                    $levelId = $trainingObject->ID;
                    break;
                case "TERM":
                    $termId = $trainingObject->ID;
                    break;
                case "CLASS":
                    $trainingclassId = $trainingObject->ID;
                    break;
            }
        }

        $SELECTION_A = array(
            "ID AS STUDENT_SCOLARSHIP_ID"
            , "CAMPUS AS CAMPUS"
            , "SCHOOLYEAR AS T_SCOLARSHIP_SCHOOLYEAR"
            , "TERM AS T_SCOLARSHIP_TERM"
        );

        $SELECTION_B = array(
            "ID AS STUDENT_ID"
            , "CODE AS CODE_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
            , "EMAIL AS EMAIL"
            , "PHONE AS PHONE"
            , "DATE_BIRTH AS DATE_BIRTH"
            , "SMS_SERVICES AS SMS_SERVICES"
            , "MOBIL_PHONE AS MOBIL_PHONE"
            , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
            , "CONCAT(LASTNAME,' ',FIRSTNAME) AS FULL_NAME"
            , "CREATED_DATE AS CREATED_DATE"
        );
        $SELECTION_C = array(
            "NAME AS SCHOLARSHIP"
            , "SCHOLARSHIP_VALUE AS SCHOLARSHIP_VALUE"
        );
        $SELECTION_D = array(
            "NAME AS SCHOOL_YEAR_NAME"
            , "YEAR_LEVEL AS YEAR_LEVEL"
        );
        $SELECTION_E = array(
            "ID AS ENROLL_ID"
            , "SORTKEY AS SORTKEY"
            , "SCHOOL_YEAR AS SCHOOL_YEAR"
            , "CLASS AS CLASS"
            , "GRADE AS GRADE"
            , "STATUS AS SCHOOLYEAR_STATUS"
            , "PRESENTATIVE AS PRESENTATIVE"
            , "TRANSFER AS TRANSFER"
        );

        $SELECT_F = array(
            'ID AS STUDENT_TRAINING_ID'
        );

        $SELECT_G = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE'
            , 'END_DATE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_scholarship'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student'), 'B.ID=A.STUDENT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_scholarship'), 'A.SCHOLARSHIP=C.ID', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_academicdate'), 'D.ID=A.SCHOOLYEAR', $SELECTION_D);
        if ($academicId) {
            if ($academicObject->EDUCATION_SYSTEM) {
                $SQL->joinLeft(array('E' => 't_student_schoolyear_subject'), 'B.ID=E.STUDENT_ID', array());
            } else {
                $SQL->joinLeft(array('E' => 't_student_schoolyear'), 'B.ID=E.STUDENT', $SELECTION_E);
            }
        }

        if ($trainingId) {
            $SQL->joinLeft(array('F' => 't_student_training'), 'B.ID=F.STUDENT', $SELECT_F);
            $SQL->joinLeft(array('G' => 't_training'), 'G.ID=F.TRAINING', $SELECT_G);
        }

        if ($campusId) {
            if ($academicObject->EDUCATION_SYSTEM) {
                $SQL->where("E.CAMPUS_ID= '" . $campusId . "'");
            } else {
                $SQL->where("E.CAMPUS= '" . $campusId . "'");
            }
        }
        if ($classId) {
            if ($academicObject->EDUCATION_SYSTEM) {
                $SQL->where("E.CLASS_ID= '" . $classId . "'");
            } else {
                $SQL->where("E.CLASS= '" . $classId . "'");
            }
        }
        if ($gradeId)
            $SQL->where("E.GRADE='" . $gradeId . "'");
        ////
        if ($programId)
            $SQL->where("F.PROGRAM = '" . $programId . "'");

        if ($termId)
            $SQL->where("A.TERM = '" . $termId . "'");

        if ($levelId)
            $SQL->where("F.LEVEL = '" . $levelId . "'");

        if ($trainingclassId)
            $SQL->where("F.TRAINING = '" . $trainingclassId . "'");
        ///

        if ($studentId)
            $SQL->where("B.ID='" . $studentId . "'");

        if ($schoolyearId)
            $SQL->where("A.SCHOOLYEAR = ?",$schoolyearId);

        if ($studentSchoolCode)
            $SQL->where("B.STUDENT_SCHOOL_ID LIKE '%" . $studentSchoolCode . "%'");

        if ($code)
            $SQL->where("B.CODE LIKE '%" . $code . "%'");

        if ($firstname)
            $SQL->where("B.FIRSTNAME LIKE '%" . $firstname . "%'");

        if ($lastname)
            $SQL->where("B.LASTNAME LIKE '%" . $lastname . "%'");

        if ($code)
            $SQL->where("B.CODE LIKE '" . $code . "%'");

        if ($gender)
            $SQL->where("B.GENDER = '" . $gender . "'");

        if ($scholarshipType)
            $SQL->where("A.SCHOLARSHIP = '" . $scholarshipType . "'");

        if ($globalSearch) {
            $SEARCH = " ((B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        if ($academicId)
            $SQL->where("A.SCHOOLYEAR != ''");
        if ($trainingId)
            $SQL->where("A.TERM != ''");
        $SQL->group('A.ID');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //traning student scholarship
    public static function getSQLStudentTrainingScholarship($params) {

        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $scholarshipType = isset($params["scholarshipType"]) ? addText($params["scholarshipType"]) : "";
        $programId = isset($params["programId"]) ? addText($params["programId"]) : "";
        $termId = isset($params["termId"]) ? addText($params["termId"]) : "";
        $levelId = isset($params["levelId"]) ? addText($params["levelId"]) : "";

        $SELECT_A = array(
            'CODE'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE'
            , 'END_DATE'
        );

        $SELECT_D = array(
        );

        $SELECT_E = array(
            'NAME AS SCHOLARSHIP_TYPE'
            , 'CONTENT AS SCHOLARSHIP_TYPE_CONTENT'
        );

        $SELECT_F = array(
            'NAME AS SCHOLARSHIP'
            , 'CONTENT AS SCHOLARSHIP_CONTENT'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('D' => 't_student_scholarship'), $SELECT_D);
        $SQL->joinLeft(array('A' => 't_student'), 'A.ID=D.STUDENT', $SELECT_A);
        $SQL->joinLeft(array('E' => 't_scholarship'), 'D.SCHOLARSHIP=E.ID', $SELECT_E);
        $SQL->joinLeft(array('F' => 't_scholarship'), 'E.PARENT=F.ID', $SELECT_F);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);

        if ($studentSchoolCode)
            $SQL->where("A.STUDENT_SCHOOL_ID LIKE '%" . $studentSchoolCode . "%'");

        if ($code)
            $SQL->where("A.CODE LIKE '%" . $code . "%'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '%" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '%" . $lastname . "%'");

        if ($gender)
            $SQL->where("A.GENDER = '" . $gender . "'");

        if ($scholarshipType)
            $SQL->where("D.SCHOLARSHIP = '" . $scholarshipType . "'");

        if ($programId)
            $SQL->where("B.PROGRAM = '" . $programId . "'");

        if ($termId)
            $SQL->where("B.TERM = '" . $termId . "'");

        if ($levelId)
            $SQL->where("B.LEVEL = '" . $levelId . "'");

        $SQL->where("D.TERM != 0");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //

    protected function getAllScholarshipQuery($params) {

        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from('t_scholarship', array('*'));

        if ($parentId) {
            $SQL->where("PARENT = ?", $parentId);
        } else {
            $SQL->where("PARENT='0'");
        }

        if ($globalSearch) {
            $SQL->where("NAME '" . $globalSearch . "%'");
        }

        $SQL->order("NAME DESC");

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_scholarship", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?", $Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonTreeAllScholarship($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";

        $result = $this->getAllScholarshipQuery($params);

        $i = 0;
        $data = array();

        if ($result) {
            foreach ($result as $value) {
                if (self::checkChild($value->ID)) {
                    $data[$i]['leaf'] = false;
                    $data[$i]['id'] = $value->ID;
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['parentId'] = $value->PARENT;
                } else {
                    $data[$i]['leaf'] = true;
                    $data[$i]['id'] = $value->ID;
                    $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->SCHOLARSHIP_VALUE . "%)";
                    $data[$i]['cls'] = "nodeTextBlue";

                    if ($value->PARENT == 0) {
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                    } else {
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                    }

                    $data[$i]['parentId'] = $value->PARENT;
                }
                $i++;
            }
        }
        return $data;
    }

    public static function jsonSaveSchoolarship($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';

        $SAVEDATA = array();
        if ($objectId == 'new') {

            $SAVEDATA['NAME'] = isset($params["NAME"]) ? addText($params["NAME"]) : "";
            $SAVEDATA['PARENT'] = isset($params["parent"]) ? addText($params["parent"]) : 0;
            $SAVEDATA['SCHOLARSHIP_VALUE'] = isset($params["SCHOLARSHIP_VALUE"]) ? addText($params["SCHOLARSHIP_VALUE"]) : 0;
            $SAVEDATA['CONTENT'] = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";

            self::dbAccess()->insert('t_scholarship', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {

            $SAVEDATA['NAME'] = isset($params["NAME"]) ? addText($params["NAME"]) : "";
            $SAVEDATA['SCHOLARSHIP_VALUE'] = isset($params["SCHOLARSHIP_VALUE"]) ? addText($params["SCHOLARSHIP_VALUE"]) : 0;
            $SAVEDATA['CONTENT'] = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_scholarship', $SAVEDATA, $WHERE);
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function deleteParendsChilesTee($id) {

        $SQL_DELETE = "DELETE FROM t_scholarship";
        $SQL_DELETE .= " WHERE";
        $SQL_DELETE .= " ID IN (" . $id . ")";
        //error_log($SQL);
        self::dbAccess()->query($SQL_DELETE);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_scholarship", array("*"));
        $SQL->where("PARENT IN (" . $id . ")");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            $idchilearr = array();
            foreach ($result as $values) {
                $idchilearr[] = $values->ID;
            }
            $chileID = implode(",", $idchilearr);

            return self::deleteParendsChilesTee($chileID);
        } else {

            return 0;
        }
    }

    public static function jsonRemoveScholarship($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        self::deleteParendsChilesTee($removeId);
        return array("success" => true);
    }

    public static function addStudentSchoolar($params) {

        $compusId = isset($params["compusId"]) ? addText($params["compusId"]) : "0";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "0";
        $scholarship = isset($params["CHOOSE_SCHOLARSHIP"]) ? addText($params["CHOOSE_SCHOLARSHIP"]) : "0";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "0";
        $check = self::checkStudentScholarship($studentId, $schoolyearId, $compusId, false);
        $SAVEDATA = array();

        if ($check) {

            $SAVEDATA['SCHOLARSHIP'] = $scholarship;
            if ($scholarship) {
                $SAVEDATA['SCHOLARSHIP_STATUS'] = 0;
            } else {
                $SAVEDATA['SCHOLARSHIP_STATUS'] = 1;
            }

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $check->ID);
            self::dbAccess()->update('t_student_scholarship', $SAVEDATA, $WHERE);
        } else {

            $SAVEDATA['STUDENT'] = $studentId;
            $SAVEDATA['CAMPUS'] = $compusId;
            $SAVEDATA['SCHOLARSHIP'] = $scholarship;
            $SAVEDATA['SCHOOLYEAR'] = $schoolyearId;

            self::dbAccess()->insert('t_student_scholarship', $SAVEDATA);
        }
    }

    public static function actionStudentSchoolar($params) {
        self::addStudentSchoolar($params);
        return array("success" => true);
    }

    public static function getStudentScholarship($studentId, $scholarshipId = false, $schoolyear = false) {

        $SELECTION_A = array();

        $SELECTION_B = array(
            "ID AS SCHOLARSHIP_ID"
            , "NAME AS NAME"
            , "CONTENT AS CONTENT"
            , "SCHOLARSHIP_VALUE AS SCHOLARSHIP_VALUE"
            , "PARENT AS PARENT"
        );

        $SELECTION_C = array(
            "NAME AS PARENT_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_scholarship'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_scholarship'), 'A.SCHOLARSHIP=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_scholarship'), 'B.PARENT=C.ID', $SELECTION_C);

        $SQL->where("A.STUDENT = '" . $studentId . "'");
        if ($scholarshipId)
            $SQL->where("A.SCHOLARSHIP  = '" . $scholarshipId . "'");
        if ($schoolyear)
            $SQL->where("A.SCHOOLYEAR = '" . $schoolyear . "'");

        $SQL->where("SCHOLARSHIP_STATUS=0");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkStudentScholarship($student, $schoolyear, $compus, $check_status = true) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_scholarship', array('*'));
        $SQL->where("STUDENT='" . $student . "'");
        $SQL->where("SCHOOLYEAR ='" . $schoolyear . "'");
        $SQL->where("CAMPUS  ='" . $compus . "'");
        if ($check_status)
            $SQL->where("SCHOLARSHIP_STATUS=0");

        return self::dbAccess()->fetchRow($SQL);
    }

    //
}

?>