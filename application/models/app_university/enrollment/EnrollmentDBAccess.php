<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 11.11.2012
// Date: 17:38
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentStatusDBAccess.php'; //@veasna
//require_once 'models/app_university/evaluation/default/StudentSubjectAssessment.php'; 
require_once 'models/training/TrainingDBAccess.php'; //@veasna
require_once 'models/training/StudentTrainingDBAccess.php'; //@veasna

require_once setUserLoacalization();

class EnrollmentDBAccess extends StudentAcademicDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function jsonListStudentLastSchoolyear($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $nextSchoolyearId = isset($params["nextSchoolyearId"]) ? addText($params["nextSchoolyearId"]) : "";

        $result = self::getSQLStudentEnrollment($params);
        //
        if ($nextSchoolyearId == $params['schoolyearId']) {
            $nextSchoolyearId = " ";
        }


        $nextRsult = self::getSQLAllStudentInSchoolyear($nextSchoolyearId);
        $CHECK_DATA = array();
        if ($nextRsult) {
            foreach ($nextRsult as $value) {
                $CHECK_DATA[$value->STUDENT] = $value->STUDENT;
            }
        }

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (!in_array($value->STUDENT_ID, $CHECK_DATA)) {

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = $value->CODE_ID;
                    $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                    $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                    $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["GRADE_CLASS"] = setShowText($value->CLASS_NAME);
                    $data[$i]["CLASS"] = $value->CLASS;

                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                    //                        
                    ++$i;
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

    public static function jsonListStudentNextSchoolyear($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSQLStudentEnrollment($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->STUDENT_CODE;
                $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["GRADE_CLASS"] = setShowText($value->CLASS_NAME);

                ++$i;
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

    //@veasna
     public static function findAssignmentByClassAndTempId($classId,$subjectId,$tmpId){
        
        $SQL = self::dbSelectAccess();
        $SQL->from('t_assignment', array('*'));
        $SQL->where("CLASS = ?",$classId);
        $SQL->where("SUBJECT = ?",$subjectId);
        $SQL->where("TEMP_ID = '" . $tmpId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL->__toString());
        return $result;        
    }
    
    public static function updateStudentChangeClass($studentId, $oldAcademicId, $newAcademicId) {
        
        //find student assignment in old class
        $SQL = self::dbSelectAccess();
        $SQL->from(array('A' => 't_student_assignment'), array('*'));
        $SQL->joinLeft(array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', array('TEMP_ID'));
        $SQL->where("A.STUDENT_ID = ?",$studentId);
        $SQL->where("A.CLASS_ID = '" . $oldAcademicId . "'");
        //error_log($SQL->__toString());
        $myresult = self::dbAccess()->fetchAll($SQL);
        if($myresult){
            foreach($myresult as $value){
                $assignmentObject = self::findAssignmentByClassAndTempId($newAcademicId,$value->SUBJECT_ID,$value->TEMP_ID);
                if($assignmentObject){
                   self::dbAccess()->update('t_student_assignment', array('CLASS_ID' => $newAcademicId,'ASSIGNMENT_ID'=>$assignmentObject->ID,'TRANSFER'=>1), array('ID=?' =>$value->ID,'STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));        
                }        
            }    
        }
        
        self::dbAccess()->update('t_student_subject_assessment', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_schoolyear_subject', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_attendance', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_learning_performance', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_notes', array('CLASS' => $newAcademicId), array('STUDENT=?' => $studentId, 'CLASS=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_subject', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_subject_assessment', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
        self::dbAccess()->update('t_student_subject_assessment', array('CLASS_ID' => $newAcademicId), array('STUDENT_ID=?' => $studentId, 'CLASS_ID=?' => $oldAcademicId));
    }

    public static function addStudentHistoryAcademic($params) {

        $SAVEDATA['REF_ID'] = isset($params['REF_ID']) ? addText($params["REF_ID"]) : '';
        $SAVEDATA['CAMPUS'] = isset($params['CAMPUS']) ? addText($params["CAMPUS"]) : '';
        $SAVEDATA['GRADE'] = isset($params['GRADE']) ? addText($params["GRADE"]) : '';
        $SAVEDATA['SCHOOLYEAR'] = isset($params['SCHOOLYEAR']) ? addText($params["SCHOOLYEAR"]) : '';
        $SAVEDATA['CLASS'] = isset($params['CLASS']) ? addText($params["CLASS"]) : '';
        $SAVEDATA['TYPE'] = isset($params['TYPE']) ? addText($params["TYPE"]) : '';
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

        self::dbAccess()->insert('t_student_academic_history', $SAVEDATA);
    }

    public static function transferStudentToGradeSchoolyear($params) {

        $SQLIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $selectedStudents = explode(",", $SQLIds);
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $gradeLeftId = isset($params["gradeLeftId"]) ? $params["gradeLeftId"] : "";
        $schoolyearLeftId = isset($params["schoolyearLeftId"]) ? addText($params["schoolyearLeftId"]) : "";

        $SAVEDATA = array();
        $selectedCount = 0;
        $error = 0;
        if (!$schoolyearId) {
            $error = 1;
        } elseif (!$gradeId) {
            $error = 2;
        } elseif (!$classId) {
            $error = 3;
        }

        if (!$error) {

            $academicObject = AcademicDBAccess::findGradeFromId($classId);
            $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($schoolyearId);

            if ($selectedStudents) {
                foreach ($selectedStudents as $studentId) {
                    ///check student have in the class and then update his class
                    $checkSt = StudentAcademicDBAccess::checkStudentEnrolledTraditionalSystem($studentId, $gradeLeftId,$schoolyearId);

                    ///

                    if (isset($academicObject) && isset($schoolyearObject)) {

                        if ($checkSt) {

                            //add history
                            $par['REF_ID'] = $checkSt->ID;
                            $par['CAMPUS'] = $checkSt->CAMPUS;
                            $par['GRADE'] = $checkSt->GRADE;
                            $par['SCHOOLYEAR'] = $checkSt->SCHOOL_YEAR;
                            $par['CLASS'] = $checkSt->CLASS;
                            $par['TYPE'] = 'TRANSFER';

                            self::addStudentHistoryAcademic($par);

                            $SAVEDATA["CLASS"] = $academicObject->ID;
                            $SAVEDATA["CAMPUS"] = $academicObject->CAMPUS_ID;
                            $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
                            $WHERE['STUDENT = ?'] = $studentId;
                            $WHERE['GRADE = ?'] = $gradeLeftId;
                            $WHERE['SCHOOL_YEAR = ?'] = $schoolyearId;
                            self::dbAccess()->update('t_student_schoolyear', $SAVEDATA, $WHERE);

                            //new update
                            self::updateStudentChangeClass($studentId, $checkSt->CLASS, $academicObject->ID);
                        } else {
                            $SAVEDATA["STUDENT"] = $studentId;
                            $SAVEDATA["CAMPUS"] = $academicObject->CAMPUS_ID;
                            $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
                            $SAVEDATA["CLASS"] = $academicObject->ID;
                            $SAVEDATA["SCHOOL_YEAR"] = $schoolyearId;
                            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                            self::dbAccess()->insert('t_student_schoolyear', $SAVEDATA);
                            $obejctId = self::dbAccess()->lastInsertId();

                            //add history
                            $oldYear = self::findEnrollmentCountGeneralByStudentId($studentId, $gradeLeftId, $schoolyearLeftId);
                            $par['REF_ID'] = $obejctId;
                            $par['CAMPUS'] = $oldYear->CAMPUS;
                            $par['GRADE'] = $oldYear->GRADE;
                            $par['SCHOOLYEAR'] = $oldYear->SCHOOL_YEAR;
                            $par['CLASS'] = $oldYear->CLASS;
                            $par['TYPE'] = ($oldYear->GRADE == $gradeId) ? 'DOWNGRADE' : 'UPGRADE';

                            self::addStudentHistoryAcademic($par);
                        }
                    }
                    $selectedCount++;
                }
            }
        }

        return array(
            "success" => true
            , "error" => $error
            , "selectedCount" => $selectedCount
        );
    }

    public static function addStudentToNewGradeSchoolyear($params) {

        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : 0;

        $SAVEDATA = array();

        $error = 0;
        if (!$schoolyearId) {
            $error = 1;
        } elseif (!$gradeId) {
            $error = 2;
        } elseif (!$academicId) {
            $error = 3;
        }

        if (!$error) {
            if ($studentId && $newValue && $academicId) {

                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($schoolyearId);

                if (isset($academicObject) && isset($schoolyearObject)) {
                    $SAVEDATA["STUDENT"] = $studentId;
                    $SAVEDATA["CAMPUS"] = $academicObject->CAMPUS_ID;
                    $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
                    $SAVEDATA["CLASS"] = $academicObject->ID;
                    $SAVEDATA["SCHOOL_YEAR"] = $schoolyearId;
                    $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                    $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                    self::dbAccess()->insert('t_student_schoolyear', $SAVEDATA);
                }
            }
        }

        return array(
            "success" => true
            , "error" => $error
        );
    }

    //@veasna
    public static function getAllStudentsHistory($params) {

        $search = isset($params['query']) ? addText($params["query"]) : '';
        $studentSchoolId = isset($params['STUDENT_SCHOOL_ID']) ? addText($params["STUDENT_SCHOOL_ID"]) : '';
        $code = isset($params['CODE']) ? addText($params["CODE"]) : '';
        $lastName = isset($params['LASTNAME']) ? addText($params["LASTNAME"]) : '';
        $firstName = isset($params['FIRSTNAME']) ? addText($params["FIRSTNAME"]) : '';
        $gender = isset($params['GENDER']) ? addText($params["GENDER"]) : '';
        $startDate = isset($params['START_DATE']) ? addText($params["START_DATE"]) : '';
        $endDate = isset($params['END_DATE']) ? addText($params["END_DATE"]) : '';
        $choose_type = isset($params['CHOOSE_OPTION']) ? addText($params["CHOOSE_OPTION"]) : '';


        $SELECTION_A = array(
            "CAMPUS AS CAMPUS"
            , "GRADE AS OLD_GRADE"
            , "SCHOOLYEAR AS OLD_SCHOOLYEAR"
            , "CLASS AS OLD_CLASS"
            , "TYPE AS TYPE"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
        );
        $SELECTION_B = array(
            "STUDENT AS STUDENT_ID"
            , "GRADE AS NEW_GRADE"
            , "CLASS AS NEW_CLASS"
            , "SCHOOL_YEAR AS NEW_SCHOOL_YEAR"
            , "CAMPUS AS NEW_CAMPUS"
        );
        $SELECTION_C = array(
            "CODE AS CODE_ID"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
            , "DATE_BIRTH AS DATE_BIRTH"
        );
        $SELECTION_D = array(
            "NAME AS CLASS_NAME"
            , "TITLE AS TITLE"
        );

        $SQL = self::dbSelectAccess();
        $SQL->from(array('A' => 't_student_academic_history'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.REF_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_student'), 'C.ID=B.STUDENT', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_grade'), 'A.CLASS=D.ID', $SELECTION_D);

        if ($choose_type) {
            $SQL->where("A.TYPE = '" . $choose_type . "'");
        }

        if ($studentSchoolId)
            $SQL->where("C.STUDENT_SCHOOL_ID LIKE ?","" . $studentSchoolId . "%");

        if ($code)
            $SQL->where("C.CODE LIKE ?","" . $code . "%");

        if ($lastName)
            $SQL->where("C.LASTNAME LIKE ?","" . $lastName . "%");

        if ($firstName)
            $SQL->where("C.FIRSTNAME LIKE ?","" . $firstName . "%");

        if ($gender) {
            $SQL->where("C.GENDER = '" . $gender . "'");
        }

        if ($startDate && $endDate) {
            $SQL->where("'" . setDate2DB($startDate) . "' <= A.CREATED_DATE AND A.CREATED_DATE <= '" . setDate2DB($endDate) . "'");
        }

        if ($search) {
            $SQL->where("C.FIRSTNAME LIKE '" . $search . "%' OR C.LASTNAME LIKE '" . $search . "%' OR C.CODE LIKE '" . $search . "%' OR C.STUDENT_SCHOOL_ID LIKE '" . $search . "%'");
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY C.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY C.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY C.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function jsonAllStudentsHistory($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getAllStudentsHistory($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $oldGrade = explode('&raquo;', $value->TITLE);
                $object="";
                if($value->NEW_CLASS)
                $object = AcademicDBAccess::findClass($value->NEW_CLASS);
                $newGrade = $object?explode('&raquo;', $object->TITLE):array();

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->CODE_ID;
                $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["FROM_CLASS_NAME"] = setShowText($value->CLASS_NAME);
                $data[$i]["FROM_GRADE"] = isset($oldGrade[1])?$oldGrade[1]:'';
                $data[$i]["CURRENT_CLASS_NAME"] = $object?setShowText($object->NAME):'---';
                $data[$i]["CURRENT_GRADE"] = $newGrade?$newGrade[1]:'';
                $data[$i]["TYPE"] = $value->TYPE;
                $data[$i]["CREATE_DATE"] = getShowDate($value->CREATED_DATE);
                ++$i;
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

    //

    public static function deletestudentFromNewGradeSchoolyear($params) {

        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : 0;

        $entries = StudentAcademicDBAccess::getQueryStudentEnrollment(false, false, $studentId);
        $count = sizeof($entries);

        if ($count > 1) {
            if ($studentId && $schoolyearId && $newValue) {
                self::dbAccess()->delete(
                        't_student_schoolyear'
                        , array(
                    "STUDENT='" . $studentId . "'"
                    , "SCHOOL_YEAR='" . $schoolyearId . "'"
                        )
                );
            }
        }

        return array(
            "success" => true
        );
    }
    
    
    ///@veasna
    public static function jsonListStudentTraining($params, $isJson = true) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selectedTrainingId = isset($params["selectedTrainingId"]) ? addText($params["selectedTrainingId"]) : "";
        $resultRows = StudentTrainingDBAccess::sqlStudentTraining($globalSearch, $trainingId, $studentId);
        $checkStudent=array();
        if($selectedTrainingId){
            $selectedStudentTraining = StudentTrainingDBAccess::sqlStudentTraining($globalSearch, $selectedTrainingId, $studentId);  
            if($selectedStudentTraining){
                foreach($selectedStudentTraining as $student){
                    $checkStudent[]=$student->STUDENT_ID;   
                }
            }      
        }
        $data =array();
        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {
                if($selectedTrainingId){
                    if (in_array($value->STUDENT_ID, $checkStudent))
                    continue;   
                }
                $data[$i]["ID"] = $value->STUDENT_ID;

                $data[$i]["STATUS_KEY"] = $value->STATUS_SHORT;
                $data[$i]["BG_COLOR"] = $value->STATUS_COLOR;
                $data[$i]["BG_COLOR_FONT"] = $value->STATUS_COLOR_FONT;

                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $data[$i]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME);
                $data[$i]["TERM_NAME"] = getShowDate($value->START_DATE) . '-' . getShowDate($value->END_DATE);
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson == true)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        } 
    }
    
    public function classTransferTraining($oldClass,$newClass,$studentId,$transferType){
        
        $SAVEDATA = array();
        $studentTrainingOld = StudentTrainingDBAccess::sqlStudentTrainingRow($oldClass->ID, $studentId);
        if($studentTrainingOld){
            /////////////////////////
            //add new student record 
            /////////////////////////
            $SAVEDATA["STUDENT"] = $studentId;
            $SAVEDATA['PROGRAM'] = $newClass->PROGRAM? $newClass->PROGRAM : '';
            $SAVEDATA['LEVEL'] = $newClass->LEVEL? $newClass->LEVEL : '';
            $SAVEDATA['TERM'] = $newClass->TERM? $newClass->TERM : '';
            $SAVEDATA['TRAINING'] = $newClass->ID? $newClass->ID : '';
            
            $SAVEDATA['FROM_PROGRAM'] = $studentTrainingOld->PROGRAM? $studentTrainingOld->PROGRAM : '';
            $SAVEDATA['FROM_LEVEL'] = $studentTrainingOld->LEVEL? $studentTrainingOld->LEVEL : '';
            $SAVEDATA['FROM_TERM'] = $studentTrainingOld->TERM? $studentTrainingOld->TERM : '';
            $SAVEDATA['FROM_TRAINING'] = $studentTrainingOld->TRAINING? $studentTrainingOld->TRAINING : '';
            
            $SAVEDATA['TRANSFER_FROM'] = $studentTrainingOld->OBJECT_ID? $studentTrainingOld->OBJECT_ID : '';
            if($transferType){
                switch(strtoupper($transferType)){
                    case 'TRANSFER':
                        $SAVEDATA['TRANSFER_TYPE'] = 1;
                        ////////////////////////////////////////
                        //update keep history student transfer
                        ///////////////////////////////////////  
                        $WHERE['ID = ?'] = $studentTrainingOld->OBJECT_ID;
                        self::dbAccess()->update('t_student_training', array('IS_TRANSFER'=>1), $WHERE);  
                        break; 
                    case 'UPGRADE':
                        $SAVEDATA['TRANSFER_TYPE'] = 2;
                        break;
                    case 'DOWNGRADE':
                        $SAVEDATA['TRANSFER_TYPE'] = 3;
                        break;   
                }    
            }
            
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_student_training', $SAVEDATA);
               
        }
      
    }
    
    public function transferStudentTraining($params){
        
        $oldTrainingId = isset($params['oldTrainingId'])?addText($params['oldTrainingId']):'';
        $newTrainingId = isset($params['newTrainingId'])?addText($params['newTrainingId']):'';
        $studentIds = isset($params['selectionIds'])?addText($params['selectionIds']):'';
        $transferType = isset($params['transferType'])?addText($params['transferType']):'';
        $selectedCount = 0;
        $error='';
        if($oldTrainingId){
            $oldClass = TrainingDBAccess::findTrainingFromId($oldTrainingId);    
        }
        if($newTrainingId){
            
            $newClass = TrainingDBAccess::findTrainingFromId($newTrainingId); 
            foreach(explode(',',$studentIds) as $value){
                $this->classTransferTraining($oldClass,$newClass,$value,$transferType); 
                $selectedCount++;           
            }        
        }else{
            $error = 'Select New Class';    
        }
        
        if($error){
            return array(
                "success" => true
                , "error" => $error
                , "selectedCount" => $selectedCount
            );    
        }else{
            return array(
                "success" => true
                , "error" => $error
                , "selectedCount" => $selectedCount
            );
        }
    }   
    
    
    public function getStudentTrainingHistory($params){
        
        $search = isset($params['query']) ? addText($params["query"]) : '';
        $studentSchoolId = isset($params['STUDENT_SCHOOL_ID']) ? addText($params["STUDENT_SCHOOL_ID"]) : '';
        $code = isset($params['CODE']) ? addText($params["CODE"]) : '';
        $lastName = isset($params['LASTNAME']) ? addText($params["LASTNAME"]) : '';
        $firstName = isset($params['FIRSTNAME']) ? addText($params["FIRSTNAME"]) : '';
        $gender = isset($params['GENDER']) ? addText($params["GENDER"]) : '';
        $startDate = isset($params['START_DATE']) ? addText($params["START_DATE"]) : '';
        $endDate = isset($params['END_DATE']) ? addText($params["END_DATE"]) : '';
        $choose_type = isset($params['CHOOSE_OPTION']) ? addText($params["CHOOSE_OPTION"]) : '';
        
        $SELECTION_A = array(
            "PROGRAM AS FROM_PROGRAM"
            , "LEVEL AS FROM_LEVEL"
            , "TERM AS FROM_TERM"
            , "TRAINING AS FROM_TRAINING"     
        );
                
        $SELECTION_B = array(
            "PROGRAM AS CURRENT_PROGRAM"
            , "LEVEL AS CURRENT_LEVEL"
            , "TERM AS CURRENT_TERM"
            , "TRAINING AS CURRENT_TRAINING"  
            , "TRANSFER_TYPE" 
            , "CREATED_DATE" 
            , "CREATED_BY" 
        );
        
        $SELECTION_C = array(
            "ID AS STUDENT_ID"
            , "CODE"
            , "FIRSTNAME"
            , "LASTNAME"
            , "GENDER"
            , "DATE_BIRTH"
            , "STATUS_SHORT"
            , "STATUS_COLOR"
            , "STATUS_COLOR_FONT"
        );
        
        $SQL = self::dbSelectAccess();
        $SQL->from(array('A' => 't_student_training'), $SELECTION_A);
        $SQL->join(array('B' => 't_student_training'),'A.ID=B.TRANSFER_FROM',$SELECTION_B);
        $SQL->join(array('C' => 't_student'), 'A.STUDENT=C.ID', $SELECTION_C);
        //$SQL->where("A.IS_TRANSFER = ?",1);
        if ($choose_type) {
            $SQL->where("B.TRANSFER_TYPE = '" . $choose_type . "'");
        }

        if ($studentSchoolId)
            $SQL->where("C.STUDENT_SCHOOL_ID LIKE ?","" . $studentSchoolId . "%");

        if ($code)
            $SQL->where("C.CODE LIKE ?","" . $code . "%");

        if ($lastName)
            $SQL->where("C.LASTNAME LIKE ?","" . $lastName . "%");

        if ($firstName)
            $SQL->where("C.FIRSTNAME LIKE ?","" . $firstName . "%");

        if ($gender) {
            $SQL->where("C.GENDER = '" . $gender . "'");
        }

        if ($startDate && $endDate) {
            $SQL->where("'" . setDate2DB($startDate) . "' <= B.CREATED_DATE AND B.CREATED_DATE <= '" . setDate2DB($endDate) . "'");
        }

        if ($search) {
            $SQL->where("C.FIRSTNAME LIKE '" . $search . "%' OR C.LASTNAME LIKE '" . $search . "%' OR C.CODE LIKE '" . $search . "%' OR C.STUDENT_SCHOOL_ID LIKE '" . $search . "%'");
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY C.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY C.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY C.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
            
    }
    
    public function jsonAllStudentsTrainingHistory($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->getStudentTrainingHistory($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["GENDER"]= getGenderName($value->GENDER);
                
                if($value->CURRENT_TERM){
                    $term=TrainingDBAccess::findTrainingFromId($value->CURRENT_TERM);
                    $data[$i]["TERM"] = getShowDate($term->START_DATE).'-'.getShowDate($term->END_DATE);
                }
                if($value->CURRENT_TRAINING){
                    $currentTraining=TrainingDBAccess::findTrainingFromId($value->CURRENT_TRAINING);
                    $data[$i]["CURRENT_CLASS"] = setShowText($currentTraining->NAME);    
                }
                if($value->CURRENT_LEVEL){
                    $currentLevel=TrainingDBAccess::findTrainingFromId($value->CURRENT_LEVEL);
                    $data[$i]["CURRENT_LEVEL"] = setShowText($currentLevel->NAME);    
                }
                if($value->FROM_LEVEL){
                    $fromLevel=TrainingDBAccess::findTrainingFromId($value->FROM_LEVEL);
                    $data[$i]["PREVIOUS_LEVEL"] = setShowText($fromLevel->NAME);  
                }
                
                if($value->FROM_TRAINING){
                    $fromTraining=TrainingDBAccess::findTrainingFromId($value->FROM_TRAINING);
                    $data[$i]["PREVIOUS_CLASS"] = setShowText($fromTraining->NAME);  
                }
                
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                if($value->TRANSFER_TYPE){
                    switch($value->TRANSFER_TYPE){
                        case'1':
                            $data[$i]["TYPE"] = TRANSFER;
                            break;
                        case'2':
                            $data[$i]["TYPE"] = UPGRADE;
                            break;
                        case'3':
                            $data[$i]["TYPE"] = DOWNGRADE;
                            break;   
                    }
                }
                
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = $value->CREATED_BY;
                ++$i;
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
}

?>