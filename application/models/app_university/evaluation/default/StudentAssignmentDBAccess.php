<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.10.2012
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/evaluation/default/AssessmentConfig.php';
require_once 'models/app_university/assignment/AssignmentDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once setUserLoacalization();

class StudentAssignmentDBAccess {

    //
    public $data = Array();
    //
    public $assignmentObject = null;
    //
    public $classSubject = null;
    //
    public $classObject = null;
    //
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct($academicId = false, $subjectId = false, $assignmentId = false) {

        $this->DB_ASSIGNMENT = AssignmentDBAccess::getInstance();
        $this->academicId = $academicId;
        $this->subjectId = $subjectId;
        $this->assignmentId = $assignmentId;
    }

    public function __get($name) {
        if (Array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __isset($name) {
        return Array_key_exists($name, $this->data);
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function getCurrentTerm() {

        return AcademicDBAccess::getNameOfSchoolTermByDate(
                        $this->date
                        , $this->academicId);
    }

    public function getClassSubject() {

        if ($this->subjectId && $this->getClassObject()) {
            return SubjectDBAccess::findSubjectClass($this->subjectId, $this->academicId);
        }
    }

    public function getClassObject() {
        return AcademicDBAccess::findGradeFromId($this->academicId);
    }

    public function getAssignmentObject() {
        return AssignmentDBAccess::findAssignmentJoinCategory($this->assignmentId);
    }

    public function listStudentsByClass($globalSearch = false) {
        return StudentAcademicDBAccess::getQueryStudentEnrollment($this->academicId, $globalSearch, false);
    }

    public function listSubjects() {

        return GradeSubjectDBAccess::getAllEvaluationSubjects($this->academicId, $this->term);
    }

    public function listAssignmentsByClass() {

        return $this->DB_ASSIGNMENT->getListAssignmentsForAssessment($this->academicId, $this->subjectId);
    }

    ////////////////////////////////////////////////////////////////////////////
    //TEACHER ENTER SCORE FOR STUDENT (VERSION JSON)
    //Parameter: $scoreInput, studentId, $academicId, $subjectId
    //$assignmentId, $subjectId, $date
    ////////////////////////////////////////////////////////////////////////////
    public function jsonSaveStudentScoreSubjectAssignment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $setnewValue = isset($params["setnewValue"]) ? $params["setnewValue"] : "";
        if ($setnewValue) {
            $this->scoreInput = $setnewValue;
        } else {
            $this->scoreInput = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        }

        $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->date = isset($params["date"]) ? $params["date"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";

        $this->assignmenObject = $this->getAssignmentObject();
        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();
        $this->maxScore = $this->classSubject ? $this->classSubject->SCORE_MAX : "";
        $this->schoolyearId = $this->classObject ? $this->classObject->SCHOOL_YEAR : "";
        $this->educationType = $this->classObject ? $this->classObject->EDUCATION_TYPE : "";
        $this->scoreType = $this->classSubject ? $this->classSubject->SCORE_TYPE : "";
        $this->teacherId = Zend_Registry::get('USER')->ID;

        if ($this->date) {
            $explode = explode('-', $this->date);
            $this->year = $explode[0];
            $this->month = $explode[1];
        }

        $ERROR = 0;

        if ($this->assignmenObject) {
            if ($this->scoreType == 1) {
                if ($this->scoreInput <= $this->maxScore) {
                    $ERROR = 0;
                } else {
                    $ERROR = 1;
                }
            } else {
                $ERROR = 0;
            }
        } else {
            $ERROR = 1;
        }

        if (!$ERROR) {
            $this->setStudentScoreSubjectAssignment();
        }

        $SCHORE_DATE = $this->getCountScoreInputDate();

        return Array(
            "success" => true
            , "ERROR" => $ERROR
            , "SCHORE_DATE" => $SCHORE_DATE
        );
    }

    protected function calculatePoints() {
        $result = "";
        if ($this->scoreType == 1) {
            if ($this->classObject->EVALUATION_TYPE) {
                $result = ($this->scoreInput * $this->calculateCoefficient()) / 100;
            } else {
                $result = $this->scoreInput * $this->calculateCoefficient();
            }
        }

        return $result;
    }

    protected function calculateCoefficient() {

        if ($this->scoreType == 1) {
            return $this->assignmenObject->COEFF_VALUE ? $this->assignmenObject->COEFF_VALUE : 1;
        }
    }

    protected function checkStudentScoreSubjectAssignment() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_assignment", Array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT_ID = '" . $this->subjectId . "'");
        $SQL->where("CLASS_ID = '" . $this->academicId . "'");
        $SQL->where("STUDENT_ID = '" . $this->studentId . "'");
        $SQL->where("SCORE_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function setStudentScoreSubjectAssignment() {

        $SAVEDATA = Array();

        $SAVEDATA["POINTS"] = $this->scoreInput;
        $SAVEDATA["CALCULATED_POINTS"] = $this->calculatePoints();
        if ($this->calculateCoefficient())
            $SAVEDATA["COEFF_VALUE"] = $this->calculateCoefficient();

        if ($this->checkStudentScoreSubjectAssignment()) {
            $WHERE[] = "ASSIGNMENT_ID = '" . $this->assignmentId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $this->subjectId . "'";
            $WHERE[] = "STUDENT_ID = '" . $this->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $this->academicId . "'";
            $WHERE[] = "SCORE_DATE = '" . $this->date . "'";
            self::dbAccess()->update('t_student_assignment', $SAVEDATA, $WHERE);
        } else {

            $SAVEDATA["ASSIGNMENT_ID"] = $this->assignmentId;
            $SAVEDATA["GUID"] = camemisId();
            $SAVEDATA["STUDENT_ID"] = $this->studentId;
            $SAVEDATA["SUBJECT_ID"] = $this->subjectId;
            $SAVEDATA["CLASS_ID"] = $this->academicId;
            $SAVEDATA["SCORE_DATE"] = $this->date;
            $SAVEDATA["SCORE_TYPE"] = $this->scoreType;
            $SAVEDATA["TERM"] = $this->term;
            $SAVEDATA["MONTH"] = $this->month;
            $SAVEDATA["YEAR"] = $this->year;
            $SAVEDATA["TEACHER_ID"] = $this->teacherId;
            $SAVEDATA["CREATED_DATE"] = getCurrentDBDateTime();
            $SAVEDATA["CREATED_BY"] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert("t_student_assignment", $SAVEDATA);
            $this->addScoreDate();
        }
    }

    /*     * *******************************************************************
     * List Teacher score assignment...
     * ********************************************************************** */

    public function jsonListStudentsScoreEnter($encrypParams, $isJson = true) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? $params["start"] : 0;
        $limit = isset($params["limit"]) ? $params["limit"] : 100;
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $this->assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";
        $this->date = isset($params["date"]) ? $params["date"] : "";
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();
        $this->assignmenObject = $this->getAssignmentObject();
        $this->scoreType = $this->classSubject ? $this->classSubject->SCORE_TYPE : "";

        $data = Array();

        $entries = StudentAcademicDBAccess::getQueryStudentEnrollment(
                        $this->academicId
                        , $globalSearch
                        , false
        );

        if ($entries) {

            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $STUDENT_SCORE = $this->loadStudentSubjectAssignmentScore();

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                if ($this->scoreType == 1) {
                    if ($STUDENT_SCORE) {
                        if ($STUDENT_SCORE->POINTS == 0) {
                            $data[$i]["SCORE"] = 0;
                        } else {
                            $data[$i]["SCORE"] = $STUDENT_SCORE->POINTS;
                        }
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    } else {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                } else {
                    if ($STUDENT_SCORE) {
                        $data[$i]["SCORE"] = $STUDENT_SCORE->POINTS;
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    } else {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                }

                $data[$i]["SHORT"] = $this->assignmenObject ? setShowText($this->assignmenObject->SHORT) : "---";

                $i++;
            }
        }

        $a = Array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson) {
            return Array("success" => true, "totalCount" => sizeof($data), "rows" => $a);
        } else {
            return $data;
        }
    }

    /*     * *******************************************************************
     * Grid: List Student Assignments...
     * ********************************************************************** */

    public function jsonListStudentSubjectAssignments($params) {

        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;

        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";

        $data = Array();
        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);

        $result = $this->getSQLStudentAssignment(
                $this->studentId
                , $this->subjectId
                , $this->term
                , false
        );

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i]["ASSIGNMENT"] = $value->ASSIGNMENT_NAME;
                $data[$i]["POINTS"] = $value->POINTS;
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $data[$i]["TEACHER_COMMENT"] = setShowText($value->TEACHER_COMMENTS);
                $i++;
            }
        }


        $a = Array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return Array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function loadStudentSubjectAssignmentScore() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_assignment", Array("*"));
        $SQL->where("ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT_ID = '" . $this->subjectId . "'");
        $SQL->where("CLASS_ID = '" . $this->academicId . "'");
        $SQL->where("STUDENT_ID = '" . $this->studentId . "'");
        $SQL->where("SCORE_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function getCountScoreInputDate() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", Array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT_ID = '" . $this->subjectId . "'");
        $SQL->where("CLASS_ID = '" . $this->academicId . "'");
        $SQL->where("SCORE_INPUT_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    protected function addScoreDate() {

        if (!$this->getCountScoreInputDate()) {
            $SAVEDATA = Array();
            $SAVEDATA["ASSIGNMENT_ID"] = $this->assignmentId;
            $SAVEDATA["SUBJECT_ID"] = $this->subjectId;
            $SAVEDATA["CLASS_ID"] = $this->academicId;
            $SAVEDATA["SCORE_INPUT_DATE"] = $this->date;
            $SAVEDATA["TERM"] = $this->term;
            self::dbAccess()->insert("t_student_score_date", $SAVEDATA);
        }
    }

    public function jsonActionDeleteSingleScore($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $date = isset($params["date"]) ? $params["date"] : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";

        $WHERE = Array();
        $WHERE[] = self::dbAccess()->quoteInto('STUDENT_ID = ?', $studentId);
        $WHERE[] = self::dbAccess()->quoteInto('ASSIGNMENT_ID = ?', $assignmentId);
        $WHERE[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $subjectId);
        $WHERE[] = self::dbAccess()->quoteInto('CLASS_ID = ?', $academicId);
        $WHERE[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_assignment', $WHERE);

        return Array("success" => true);
    }

    public function jsonActionTeacherAssignmentComment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $comment = isset($params["name"]) ? addText($params["name"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $date = isset($params["date"]) ? $params["date"] : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";

        $SAVEDATA['TEACHER_COMMENTS'] = addText($comment);

        $WHERE = Array();
        $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
        $WHERE[] = "CLASS_ID = '" . $academicId . "'";
        $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";
        $WHERE[] = "ASSIGNMENT_ID = '" . $assignmentId . "'";
        $WHERE[] = "SCORE_DATE = '" . $date . "'";
        self::dbAccess()->update('t_student_assignment', $SAVEDATA, $WHERE);

        return Array(
            "success" => true
        );
    }

    public function jsonAcitonModifyDate($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $newdate = isset($params["MODIFY_DATE"]) ? $params["MODIFY_DATE"] : "";
        $setIds = explode("_", $setId);
        $assignmentId = isset($setIds[0]) ? $setIds[0] : "";
        $olddate = isset($setIds[1]) ? $setIds[1] : "";
        $TERM_NAME = AcademicDBAccess::getNameOfSchoolTermByDate($newdate, $classId);
        $CHECK_ERROR = ($TERM_NAME == "TERM_ERROR") ? true : false;

        $ACTION_ERROR = true;
        if (!$CHECK_ERROR && $olddate) {
            $ACTION_ERROR = false;
            $date = new DateTime($newdate);
            $FIRST = "UPDATE t_student_assignment";
            $FIRST .= " SET";
            $FIRST .= " TERM='" . $TERM_NAME . "'";
            $FIRST .= " ,SCORE_DATE='" . setDate2DB($newdate) . "'";
            $FIRST .= " ,MONTH='" . $date->format('m') . "'";
            $FIRST .= " ,YEAR='" . $date->format('Y') . "'";
            $FIRST .= " WHERE";
            $FIRST .= " ASSIGNMENT_ID = '" . $assignmentId . "'";
            $FIRST .= " AND SUBJECT_ID = '" . $subjectId . "'";
            $FIRST .= " AND CLASS_ID = '" . $academicId . "'";
            $FIRST .= " AND SCORE_DATE = '" . $olddate . "'";
            self::dbAccess()->query($FIRST);

            $SECOND = "UPDATE t_student_score_date";
            $SECOND .= " SET";
            $SECOND .= " TERM='" . $TERM_NAME . "'";
            $SECOND .= " ,SCORE_INPUT_DATE='" . setDate2DB($newdate) . "'";
            $SECOND .= " WHERE";
            $SECOND .= " ASSIGNMENT_ID = '" . $assignmentId . "'";
            $SECOND .= " AND SUBJECT_ID = '" . $subjectId . "'";
            $SECOND .= " AND CLASS_ID = '" . $academicId . "'";
            $SECOND .= " AND SCORE_INPUT_DATE = '" . $olddate . "'";
            self::dbAccess()->query($SECOND);
        }

        if (!$ACTION_ERROR) {
            return Array("success" => true);
        } else {
            $errors["MODIFY_DATE"] = "No term Date!";
            return array(
                "success" => false
                , "errors" => $errors
            );
        }
    }

    public function jsonActionDeleteAllScoresAssignment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $date = isset($params["date"]) ? $params["date"] : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";

        $WHERE_A = Array();
        $WHERE_A[] = self::dbAccess()->quoteInto('ASSIGNMENT_ID = ?', $assignmentId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $subjectId);
        $WHERE_A[] = self::dbAccess()->quoteInto('CLASS_ID = ?', $academicId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_assignment', $WHERE_A);

        $WHERE_B = Array();
        $WHERE_B[] = self::dbAccess()->quoteInto('ASSIGNMENT_ID = ?', $assignmentId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $subjectId);
        $WHERE_B[] = self::dbAccess()->quoteInto('CLASS_ID = ?', $academicId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SCORE_INPUT_DATE = ?', $date);
        self::dbAccess()->delete('t_student_score_date', $WHERE_B);

        return Array("success" => true);
    }

    public function jsonActionDeleteAllScoresSubject($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $WHERE = Array();
        $WHERE[] = "CLASS_ID ='" . $academicId . "'";
        $WHERE[] = "SUBJECT_ID ='" . $subjectId . "'";

        self::dbAccess()->delete('t_student_assignment', $WHERE);
        self::dbAccess()->delete('t_student_subject_assessment', $WHERE);
        self::dbAccess()->delete('t_student_score_date', $WHERE);

        return Array(
            "success" => true
        );
    }

    public function getAllAssignmentsInScoreInputDate($subjectId = false, $assignmentId = false, $term = false, $setIncludeInValuation = false, $studentId = false) {

        $SELECTION_A = Array(
            'ASSIGNMENT_ID'
            , 'SUBJECT_ID'
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_assignment"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', array("COEFF_VALUE"));

        if ($studentId)
            $SQL->where("A.STUDENT_ID = '" . $studentId . "'");

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'");
        } else {
            if ($this->assignmentId)
                $SQL->where("A.ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        } else {
            if ($this->subjectId)
                $SQL->where("A.SUBJECT_ID = '" . $this->subjectId . "'");
        }

        if ($this->academicId)
            $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");

        if ($setIncludeInValuation) {
            switch ($setIncludeInValuation) {
                case 1:
                    $SQL->where("A.TERM = '" . $this->term . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
                    break;
                case 2:
                    $SQL->where("A.TERM = '" . $this->term . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (2)");
                    break;
            }
        } else {
            if ($this->month && $this->year) {
                $SQL->where("A.MONTH = '" . $this->month . "'");
                $SQL->where("A.YEAR= '" . $this->year . "'");
                $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
            } else {
                if ($term) {
                    $SQL->where("A.TERM = '" . strtoupper($term) . "'");
                }
            }
        }
        $SQL->group("B.ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function getTotalCountScoreInputDate() {

        $result = $this->getAllAssignmentsInScoreInputDate(false, false, false);
        return $result ? count($result) : 0;
    }

    protected function checkPermissionAverageAssignment($studentId, $subjectId, $term, $assignmentId) {

        $COUNT = count($this->$this->getSQLStudentAssignment(
                        $studentId
                        , $subjectId
                        , $term
                        , $assignmentId
                        , false));

        if ($this->getTotalCountScoreInputDate() == $COUNT) {
            return true;
        } else {
            return false;
        }
    }

    public function getSQLStudentAssignment($studentId, $subjectId, $term, $assignmentId, $setIncludeInValuation = false) {

        $SELECTION_A = Array(
            "POINTS"
            , "CREATED_DATE"
            , "TEACHER_COMMENTS"
            , "CALCULATED_POINTS"
        );

        $SELECTION_B = Array(
            "COEFF_VALUE"
            , "NAME AS ASSIGNMENT_NAME"
            , "EVALUATION_TYPE"
            , "INCLUDE_IN_EVALUATION"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_assignment"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', $SELECTION_B);

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        }

        if ($studentId) {
            $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        }
        if ($this->academicId) {
            $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");
        }

        if ($setIncludeInValuation) {
            switch ($setIncludeInValuation) {
                case 1:
                    $SQL->where("A.TERM = '" . $term . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
                    break;
                case 2:
                    $SQL->where("A.TERM = '" . $term . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (2)");
                    break;
            }
        } else {
            if ($this->month && $this->year) {
                $SQL->where("A.MONTH = '" . $this->month . "'");
                $SQL->where("A.YEAR = '" . $this->year . "'");
                $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
            } else {

                if ($term) {
                    $SQL->where("A.TERM = '" . strtoupper($term) . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (2)");
                }
            }
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getSQLAvgStudentAssignment($studentId, $subjectId, $term, $assignmentId) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_assignment"), Array('AVG(POINTS) AS AVG'));
        $SQL->joinLeft(Array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', Array());

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        }

        if ($studentId) {
            $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        }
        if ($this->academicId) {
            $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");
        }

        if ($this->month && $this->year) {
            $SQL->where("A.MONTH = '" . $this->month . "'");
            $SQL->where("A.YEAR = '" . $this->year . "'");
        } else {
            if ($term) {
                $SQL->where("A.TERM = '" . strtoupper($term) . "'");
            }
        }

        $SQL->group('A.ASSIGNMENT_ID');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->AVG : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getImplodeStudentAssignment($studentId, $subjectId, $term, $assignmentId) {

        $data = Array();
        $entries = $this->getSQLStudentAssignment(
                $studentId
                , $subjectId
                , $term
                , $assignmentId);
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $value->POINTS;
            }
        }
        return $data ? implode('|', $data) : "---";
    }

    protected function studentAvgAllAssignmentsBySubject($studentId, $subjectId, $term, $assignmentId, $setIncludeInValuation = false) {

        $result = "";
        $SUM_COUNT = "";
        $SUM_CALCULATED = "";

        $entries = $this->getAllAssignmentsInScoreInputDate(
                $subjectId
                , $assignmentId
                , $term
                , $setIncludeInValuation
                , $studentId
        );

        if ($this->classObject->EVALUATION_TYPE == 0) {
            if ($entries) {
                foreach ($entries as $value) {
                    if ($value->COEFF_VALUE) {
                        $AVG = $this->getSQLAvgStudentAssignment(
                                $studentId
                                , $subjectId
                                , $term
                                , $value->ASSIGNMENT_ID);
                        if (is_numeric($AVG)) {
                            $SUM_CALCULATED += $AVG * $value->COEFF_VALUE;
                            $SUM_COUNT += $value->COEFF_VALUE;
                        }
                    }
                }
                if ($SUM_COUNT)
                    $result = $SUM_CALCULATED / $SUM_COUNT;
            }
        } elseif ($this->classObject->EVALUATION_TYPE == 1) {
            if ($this->check100Percent(
                            $studentId
                            , $subjectId
                            , $term
                            , $assignmentId)
            ) {

                if ($entries) {
                    foreach ($entries as $value) {
                        if ($value->COEFF_VALUE) {
                            $AVG = $this->getSQLAvgStudentAssignment(
                                    $studentId
                                    , $subjectId
                                    , $term
                                    , $value->ASSIGNMENT_ID);
                            if (is_numeric($AVG)) {
                                $SUM_CALCULATED += ($AVG * $value->COEFF_VALUE) / 100;
                            }
                        }
                    }

                    $result = $SUM_CALCULATED;
                }
            } else {
                if ($entries) {
                    foreach ($entries as $value) {
                        if ($value->COEFF_VALUE) {
                            $AVG = $this->getSQLAvgStudentAssignment(
                                    $studentId
                                    , $subjectId
                                    , $term
                                    , $value->ASSIGNMENT_ID);
                            if (is_numeric($AVG)) {
                                $SUM_CALCULATED += $AVG * $value->COEFF_VALUE;
                                $SUM_COUNT += $value->COEFF_VALUE;
                            }
                        }
                    }
                    $result = $SUM_CALCULATED / $SUM_COUNT;
                }
            }
        }

        return setRound($result);
    }

    protected function check100Percent($subjectId, $term, $assignmentId) {

        $calculate = 0;
        $entries = $this->getAllAssignmentsInScoreInputDate(
                $subjectId
                , $assignmentId
                , $term
        );
        if ($entries) {
            foreach ($entries as $value) {
                if ($value->COEFF_VALUE) {
                    $calculate +=$value->COEFF_VALUE;
                }
            }
        }
        return ($calculate == 100) ? true : false;
    }

    public static function findScoreInputDate($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_score_date"), Array('CONTENT', 'SCORE_INPUT_DATE'));
        $SQL->joinLeft(Array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', Array('NAME', 'SHORT'));
        $SQL->where("A.ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonActionTeacherScoreInputDate($params) {

        $setId = isset($params["setId"]) ? $params["setId"] : "";

        if (isset($params["CONTENT"]))
            $SAVEDATA['CONTENT'] = addText($params["CONTENT"]);
        $WHERE[] = "ID = '" . $setId . "'";
        self::dbAccess()->update('t_student_score_date', $SAVEDATA, $WHERE);

        return Array(
            "success" => true
        );
    }

    public function jsonLoadTeacherScoreInputDate($Id) {

        $facette = self::findScoreInputDate($Id);

        $data = Array();

        if ($facette) {
            $data["NAME"] = $facette->NAME;
            $data["SHORT"] = $facette->SHORT;
            $data["SCORE_INPUT_DATE"] = getShowDate($facette->SCORE_INPUT_DATE);
        }

        return Array(
            "success" => true
            , "data" => $data
        );
    }

    public function getCountScoreEnterByStudent($studentId, $subjectId, $term, $setIncludeInValuation) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_assignment"), Array("COUNT(*) AS C"));
        $SQL->joinLeft(Array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', Array());

        if ($subjectId) {
            $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        }

        if ($studentId) {
            $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        }
        if ($this->academicId) {
            $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");
        }

        if ($setIncludeInValuation) {
            switch ($setIncludeInValuation) {
                //FIRST_SEMESTER
                case 1:
                    $SQL->where("A.TERM = '" . strtoupper($term) . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
                    break;
                //SECOND_SEMESTER
                case 2:
                    $SQL->where("A.TERM = '" . strtoupper($term) . "'");
                    $SQL->where("B.INCLUDE_IN_EVALUATION IN (2)");
                    break;
            }
        } else {
            if ($this->month && $this->year) {
                $SQL->where("A.MONTH = '" . $this->month . "'");
                $SQL->where("A.YEAR = '" . $this->year . "'");
                $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
            } else {
                if ($term) {
                    $SQL->where("A.TERM = '" . strtoupper($term) . "'");
                }
            }
        }

        $SQL->group("A.STUDENT_ID");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL->__toString());
        return $result ? $result->C : 0;
    }

}

?>