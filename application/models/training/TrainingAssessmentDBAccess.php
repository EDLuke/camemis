<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.08.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/training/TrainingDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/finance/StudentFeeDBAccess.php";
require_once "models/training/TrainingSubjectDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once 'models/assessment/AssessmentConfig.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/assignment/AssignmentTempDBAccess.php"; //@CHHE Vathana
require_once setUserLoacalization();

class TrainingAssessmentDBAccess extends StudentTrainingDBAccess {

    public $data = array();
    //
    public $assignmentObject = null;
    //
    public $trainingSubject = null;
    //
    public $subjectId = null;
    public $trainingObject = null;

    static function getInstance()
    {

        return new TrainingAssessmentDBAccess();
    }

    public function __construct($trainingId = false, $subjectId = false, $assignmentId = false)
    {

        $this->DB_ASSIGNMENT = AssignmentTempDBAccess::getInstance();
        $this->trainingId = $trainingId;
        $this->subjectId = $subjectId;
        $this->assignmentId = $assignmentId;
    }

    public static function getListTrainingSubjects($trainingId)
    {
        return TrainingSubjectDBAccess::sqlAssignedSubjectsByTraining(array("trainingId" => $trainingId));
    }

    public function getTrainingSubject()
    {

        if ($this->subjectId && $this->getTrainingObject())
        {
            return TrainingSubjectDBAccess::findSubjectTraining($this->subjectId, $this->getTrainingObject());
        }
    }

    public function getTrainingAssignment()
    {
        return self::getTrainingSubjectAssignment(
                        $this->trainingObject->EVALUATION_TYPE
                        , $this->trainingObject->PARENT
                        , $this->subjectId
                        , $this->assignmentId
        );
    }

    public function getStudentTrainingScoreAssignment($studentId, $trainingId, $subjectId, $assignmentId, $date)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_training_assignment"), array("*"));
        $SQL->joinLeft(array('B' => 't_training_subject'), 'A.ASSIGNMENT=B.ASSIGNMENT', array("MAX_POSSIBLE_SCORE"));
        $SQL->where("B.OBJECT_TYPE = 'ITEM'");
        if ($assignmentId)
            $SQL->where("A.ASSIGNMENT = '" . $assignmentId . "'");
        if ($subjectId)
            $SQL->where("A.SUBJECT = '" . $subjectId . "'");
        if ($trainingId)
            $SQL->where("A.TRAINING = '" . $trainingId . "'");
        if ($studentId)
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        if ($date)
            $SQL->where("A.SCORE_DATE = '" . $date . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function getScoreListTrainingSubject($subjectId, $trainingObject, $listAssignments)
    {

        $data = array();
        $entries = $this->listStudentsByTraining();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $this->getStudentTrainingSubjectAverage(
                        $value->STUDENT_ID
                        , $subjectId
                        , $trainingObject
                        , $listAssignments);
            }
        }
        return $data;
    }

    protected function getScoreListTrainingPerformance()
    {

        $data = array();
        $entries = $this->listStudentsByTraining();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = self::getStudentAVGAllSubjects($value->STUDENT_ID, $this->trainingId);
            }
        }
        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //ACTION...
    ////////////////////////////////////////////////////////////////////////////

    public function jsonActionDeleteSingleScoreTraining($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $date = isset($params["date"]) ? addText($params["date"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $WHERE = array();
        $WHERE[] = self::dbAccess()->quoteInto('STUDENT = ?', $studentId);
        $WHERE[] = self::dbAccess()->quoteInto('ASSIGNMENT = ?', $assignmentId);
        $WHERE[] = self::dbAccess()->quoteInto('SUBJECT = ?', $subjectId);
        $WHERE[] = self::dbAccess()->quoteInto('TRAINING = ?', $trainingId);
        $WHERE[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_training_assignment', $WHERE);

        return array("success" => true);
    }

    public function jsonActionDeleteAllScoresAssignmentTraining($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $date = isset($params["date"]) ? addText($params["date"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $WHERE_A = array();
        $WHERE_A[] = self::dbAccess()->quoteInto('ASSIGNMENT = ?', $assignmentId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SUBJECT= ?', $subjectId);
        $WHERE_A[] = self::dbAccess()->quoteInto('TRAINING = ?', $trainingId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_training_assignment', $WHERE_A);

        $WHERE_B = array();
        $WHERE_B[] = self::dbAccess()->quoteInto('ASSIGNMENT_ID = ?', $assignmentId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $subjectId);
        $WHERE_B[] = self::dbAccess()->quoteInto('TRAINING_ID = ?', $trainingId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SCORE_INPUT_DATE = ?', $date);
        self::dbAccess()->delete('t_student_score_date', $WHERE_B);

        return array("success" => true);
    }

    public function jsonActionDeleteAllScoresSubjectTraining($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $WHERE = array();
        $WHERE[] = "TRAINING_ID ='" . $trainingId . "'";
        $WHERE[] = "SUBJECT_ID ='" . $subjectId . "'";

        $WHERE_A = array();
        $WHERE_A[] = "TRAINING ='" . $trainingId . "'";
        $WHERE_A[] = "SUBJECT='" . $subjectId . "'";

        self::dbAccess()->delete('t_student_training_assignment', $WHERE_A);
        self::dbAccess()->delete('t_student_subject_training_assessment', $WHERE);
        self::dbAccess()->delete('t_student_score_date', $WHERE);

        return array(
            "success" => true
        );
    }

    public function jsonActionPublishTrainingSubjectAssessment($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubjectObject = $this->getTrainingSubject();
        $this->trainingSubject = $this->trainingSubjectObject->SUBJECT;
        $this->scoreType = $this->trainingSubjectObject ? $this->trainingSubjectObject->SCORE_TYPE : "";

        $entries = $this->getListStudentsSubjectResultTraining(true);

        $stdClass = (object) array(
                    "trainingId" => $this->trainingId
                    , "subjectId" => $this->subjectId
        );

        if ($entries)
        {
            for ($i = 0; $i < count($entries); $i++)
            {
                $stdClass->studentId = isset($entries[$i]["STUDENT_ID"]) ? $entries[$i]["STUDENT_ID"] : "";
                $stdClass->subjectAssessment = isset($entries[$i]["ASSESSMENT_ID"]) ? $entries[$i]["ASSESSMENT_ID"] : "";
                $stdClass->subjectValue = isset($entries[$i]["AVG"]) ? $entries[$i]["AVG"] : "";
                $stdClass->subjectRank = isset($entries[$i]["RANK"]) ? $entries[$i]["RANK"] : "";
                self::setStudentTrainingSubjectAssessment($stdClass);
            }
        }

        return array(
            "success" => true
        );
    }

    public function jsonActionPublishTrainingPerformance($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $this->trainingObject = $this->getTrainingObject();
        $entries = $this->getListTrainingPerformance(true);

        $stdClass = (object) array(
                    "trainingId" => $this->trainingId
        );

        if ($entries)
        {
            for ($i = 0; $i < count($entries); $i++)
            {
                $stdClass->studentId = isset($entries[$i]["STUDENT_ID"]) ? $entries[$i]["STUDENT_ID"] : "";
                $stdClass->performanceAssessment = isset($entries[$i]["ASSESSMENT_ID"]) ? $entries[$i]["ASSESSMENT_ID"] : "";
                $stdClass->performanceValue = isset($entries[$i]["AVERAGE"]) ? $entries[$i]["AVERAGE"] : "";
                $stdClass->performanceRank = isset($entries[$i]["RANK"]) ? $entries[$i]["RANK"] : "";
                self::setStudentTrainingPerformance($stdClass);
            }
        }

        return array("success" => true);
    }

    public function jsonActionStudentSubjectAssessmentTraining($encrypParams, $noJson = false)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "trainingId" => $trainingId
                    , "subjectId" => $subjectId
        );

        $stdClass->subjectAssessment = isset($params["comboValue"]) ? addText($params["comboValue"]) : "";
        $stdClass->subjectValue = isset($params["avg"]) ? addText($params["avg"]) : "";
        $stdClass->subjectRank = isset($params["rank"]) ? addText($params["rank"]) : "";

        self::setStudentTrainingSubjectAssessment($stdClass);

        return array(
            "success" => true, "data" => array()
        );
    }

    public function jsonStudentTrainingSubjectAssignment($params, $isJson = true)
    {

        $params = Utiles::setPostDecrypteParams($params);

        $start = isset($params["start"]) ? (int) $params["start"] : 0;
        $limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $this->assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $this->date = isset($params["date"]) ? addText($params["date"]) : "";
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->trainingObject = $this->getTrainingObject();
        $this->evaluationType = $this->trainingObject->EVALUATION_TYPE;
        $this->trainingSubject = $this->getTrainingSubject();
        $this->assignmentObject = $this->getTrainingAssignment();
        $this->scoreType = $this->trainingSubject ? $this->trainingSubject->SCORE_TYPE : "";

        switch ($this->evaluationType)
        {
            case 1:
                $this->scoreMaxe = $this->assignmentObject ? $this->assignmentObject->MAX_POSSIBLE_SCORE : "0";
                break;
            default:
                $this->scoreMaxe = $this->trainingSubject ? $this->trainingSubject->SCORE_MAX : "0";
                break;
        }

        $data = array();

        if ($this->listStudentsByTraining())
        {

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listStudentsByTraining() as $value)
            {

                $this->studentId = $value->STUDENT_ID;

                $STUDENT_SCORE = $this->getStudentTrainingScoreAssignment(
                        $value->STUDENT_ID
                        , $this->trainingId
                        , $this->subjectId
                        , $this->assignmentId
                        , $this->date);

                $data[$i]["POINTS_POSSIBLE"] = $this->scoreMaxe ? $this->scoreMaxe : "---";

                if ($this->scoreType == 1)
                {
                    if ($STUDENT_SCORE)
                    {
                        if ($STUDENT_SCORE->SCORE == 0)
                        {
                            $data[$i]["SCORE"] = 0;
                        }
                        else
                        {
                            $data[$i]["SCORE"] = $STUDENT_SCORE->SCORE;
                        }
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    }
                    else
                    {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                }
                else
                {
                    if ($STUDENT_SCORE)
                    {
                        $data[$i]["SCORE"] = $STUDENT_SCORE->SCORE;
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    }
                    else
                    {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson)
        {
            return array("success" => true, "totalCount" => sizeof($data), "rows" => $a);
        }
        else
        {
            return $data;
        }
    }

    public function getListTrainingPerformance($isJson = false)
    {
        $data = array();

        $listSubjects = self::getListTrainingSubjects($this->trainingId);
        $scoreList = $this->getScoreListTrainingPerformance();

        if ($this->listStudentsByTraining())
        {
            $i = 0;

            $data = $this->listStudentsData();

            foreach ($this->listStudentsByTraining() as $value)
            {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $AVERAGE = self::getStudentAVGAllSubjects($value->STUDENT_ID, $this->trainingId);
                $assessmentObject = self::getAssessment(false, $AVERAGE);
                $data[$i]["AVERAGE"] = $AVERAGE;

                $data[$i]["ASSESSMENT"] = $assessmentObject->GRADING;
                $data[$i]["ASSESSMENT_ID"] = $assessmentObject->ASSESSMENT_ID;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);

                if ($listSubjects)
                {
                    foreach ($listSubjects as $v)
                    {
                        $STUDENT_ASSESSMENT = self::getStudentTrainingSubjectAssessment(
                                        $value->STUDENT_ID
                                        , $v->SUBJECT_ID
                                        , $this->trainingId);

                        $data[$i]["SUB_" . $v->SUBJECT_ID . ""] = $STUDENT_ASSESSMENT ? $STUDENT_ASSESSMENT->SUBJECT_VALUE : "---";
                    }
                }
                $i++;
            }
        }

        if ($isJson)
        {
            return $data;
        }
        else
        {
            $a = array();
            for ($i = $this->start; $i < $this->start + $this->limit; $i++)
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
    }

    public function getListStudentsSubjectResultTraining($isJson = false)
    {

        $data = array();
        $listAssignments = self::getTrainingListAssignmentScoreDate($this->trainingId, $this->subjectId, "ASSIGNMENT");
        $listAssignmentsScoreDate = self::getTrainingListAssignmentScoreDate($this->trainingId, $this->subjectId,false);

        $scoreList = $this->getScoreListTrainingSubject(
                $this->subjectId
                , $this->trainingObject
                , $listAssignments);

        if ($this->listStudentsByTraining())
        {
            $i = 0;

            $data = $this->listStudentsData();

            foreach ($this->listStudentsByTraining() as $value)
            {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $AVERAGE = $this->getStudentTrainingSubjectAverage(
                        $value->STUDENT_ID
                        , $this->trainingSubject
                        , $this->trainingObject
                        , $listAssignments
                );
                $data[$i]["AVG"] = $AVERAGE;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);

                $STUDENT_ASSESSMENT = self::getStudentTrainingSubjectAssessment(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , $this->trainingId);

                $assessmentObject = self::getAssessment($STUDENT_ASSESSMENT, $AVERAGE);

                $data[$i]["ASSESSMENT"] = $assessmentObject->GRADING;
                $data[$i]["ASSESSMENT_ID"] = $assessmentObject->ASSESSMENT_ID;

                if ($listAssignmentsScoreDate)
                {
                    foreach ($listAssignmentsScoreDate as $v)
                    {
                        $STUDENT_SCORE = $this->getStudentTrainingScoreAssignment(
                                $value->STUDENT_ID
                                , $this->trainingId
                                , $this->subjectId
                                , $v->ID
                                , $v->SCORE_INPUT_DATE);

                        switch ($this->trainingObject->EVALUATION_TYPE)
                        {
                            case 1:
                                $data[$i]["A_" . $v->OBJECT_ID . ""] = $STUDENT_SCORE ? $STUDENT_SCORE->SCORE . "/" . $STUDENT_SCORE->MAX_POSSIBLE_SCORE : "---";
                                break;
                            default:
                                $data[$i]["A_" . $v->OBJECT_ID . ""] = $STUDENT_SCORE ? $STUDENT_SCORE->SCORE : "---";
                                break;
                        }
                    }
                }

                $i++;
            }
        }

        if ($isJson)
        {
            return $data;
        }
        else
        {
            $a = array();
            for ($i = $this->start; $i < $this->start + $this->limit; $i++)
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
    }

    public function jsonTrainingPerformance($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->start = isset($params["start"]) ? (int) $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        return $this->getListTrainingPerformance();
    }

    public function jsonSubjectResultTraining($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->start = isset($params["start"]) ? (int) $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubjectObject = $this->getTrainingSubject();
        $this->trainingSubject = $this->trainingSubjectObject->SUBJECT;
        $this->scoreType = $this->trainingSubjectObject ? $this->trainingSubjectObject->SCORE_TYPE : "";

        return $this->getListStudentsSubjectResultTraining();
    }

    public function checkStudentAssignmentTraining($studentId, $subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_student_training_assignment"), array("COUNT(*) AS C"));
        $SQL->joinLeft(array('B' => 't_assignment_temp'), 'A.ASSIGNMENT=B.ID', array());

        if ($subjectId)
        {
            $SQL->where("A.SUBJECT = ?", $subjectId);
        }

        if ($studentId)
        {
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        }
        if ($this->trainingId)
        {
            $SQL->where("A.TRAINING = '" . $this->trainingId . "'");
        }
        $SQL->group("A.STUDENT");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL->__toString());
        return $result ? $result->C : 0;
    }

    public function getAVGTrainingSubjectAssignment($studentId, $trainingId, $subjectId, $assignmentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_student_training_assignment"), array('AVG(SCORE) AS AVG_SCORE'));
        $SQL->joinLeft(array('B' => 't_training_subject'), 'A.ASSIGNMENT=B.ASSIGNMENT', array('AVG(MAX_POSSIBLE_SCORE) AS AVG_MAX_POSSIBLE_SCORE'));
        $SQL->where("B.OBJECT_TYPE = 'ITEM'");
        if ($assignmentId)
        {
            $SQL->where("A.ASSIGNMENT = '" . $assignmentId . "'");
        }

        $SQL->where("A.SUBJECT= '" . $subjectId . "'");
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->where("A.TRAINING = '" . $trainingId . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function getStudentTrainingSubjectAverage($studentId, $subjectId, $trainingObject, $listAssignments)
    {
        $result = "";
        $COEFF_VALUE = "";
        $SUM_AVG = "";
        $SUM_COEFF_VALUE = "";

        $CHECK = $this->checkStudentAssignmentTraining($studentId, $subjectId);
        if ($CHECK)
        {
            if ($listAssignments)
            {
                foreach ($listAssignments as $value)
                {
                    $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                    $avgObject = $this->getAVGTrainingSubjectAssignment(
                            $studentId
                            , $trainingObject->ID
                            , $subjectId
                            , $value->ID);
                    if ($avgObject)
                    {
                        switch ($trainingObject->EVALUATION_TYPE)
                        {
                            case 1:
                                $result += ($avgObject->AVG_SCORE / $avgObject->AVG_MAX_POSSIBLE_SCORE) * 100 * $COEFF_VALUE / 100;
                                break;
                            default:
                                $SUM_COEFF_VALUE += $COEFF_VALUE;
                                $SUM_AVG += $avgObject->AVG_SCORE * $COEFF_VALUE;
                                break;
                        }
                    }
                }

                if (!$trainingObject->EVALUATION_TYPE)
                {
                    $result = $SUM_AVG / $SUM_COEFF_VALUE;
                }

                return displayRound($result);
            }
        }
        else
        {
            return "---";
        }
    }

    public function actionTrainingStudentAssignment($params)
    {

        $params = Utiles::setPostDecrypteParams($params);

        $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $this->field = isset($params["field"]) ? addText($params["field"]) : "";

        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->date = isset($params["date"]) ? addText($params["date"]) : "";

        switch ($this->field)
        {
            case "TEACHER_COMMENTS":
                $this->teacherComment = isset($params["newValue"]) ? addText($params["newValue"]) : "";
                $this->scoreInput = "";
                break;
            default:
                $this->teacherComment = "";
                $this->scoreInput = isset($params["newValue"]) ? addText($params["newValue"]) : "";
                break;
        }

        $this->trainingObject = $this->getTrainingObject();
        $this->assignmentObject = $this->getTrainingAssignment();
        $this->trainingSubject = $this->getTrainingSubject();

        $this->assignmentCoeff = $this->assignmentObject->COEFF_VALUE ? $this->assignmentObject->COEFF_VALUE : 1;
        $this->evaluationType = $this->assignmentObject->COEFF_VALUE ? $this->assignmentObject->COEFF_VALUE : 0;

        $this->maxScore = $this->trainingSubject ? $this->trainingSubject->SCORE_MAX : "";
        $this->scoreType = $this->trainingSubject ? $this->trainingSubject->SCORE_TYPE : "";
        $this->teacherId = Zend_Registry::get('USER')->ID;

        $this->setStudentScoreSubjectAssignment();

        return array(
            "success" => true
            , "SCHORE_DATE" => $this->getCountScoreInputDate()
        );
    }

    protected function setStudentScoreSubjectAssignment()
    {

        $SAVEDATA = array();

        if ($this->teacherComment)
            $SAVEDATA["TEACHER_COMMENTS"] = $this->teacherComment;

        if ($this->scoreInput)
            $SAVEDATA["SCORE"] = $this->scoreInput;

        if ($this->checkStudentScoreSubjectAssignment())
        {
            $WHERE[] = "ASSIGNMENT = '" . $this->assignmentId . "'";
            $WHERE[] = "SUBJECT= '" . $this->subjectId . "'";
            $WHERE[] = "STUDENT = '" . $this->studentId . "'";
            $WHERE[] = "TRAINING= '" . $this->trainingId . "'";
            $WHERE[] = "SCORE_DATE = '" . $this->date . "'";
            self::dbAccess()->update('t_student_training_assignment', $SAVEDATA, $WHERE);
        }
        else
        {
            $SAVEDATA["COEFF_VALUE"] = $this->assignmentCoeff;
            $SAVEDATA["EVALUATION_TYPE"] = $this->evaluationType;
            $SAVEDATA["ASSIGNMENT"] = $this->assignmentId;
            $SAVEDATA["STUDENT"] = $this->studentId;
            $SAVEDATA["SUBJECT"] = $this->subjectId;
            $SAVEDATA["TRAINING"] = $this->trainingId;
            $SAVEDATA["SCORE_DATE"] = $this->date;
            $SAVEDATA["SCORE_TYPE"] = $this->scoreType;
            $SAVEDATA["TEACHER"] = $this->teacherId;
            self::dbAccess()->insert("t_student_training_assignment", $SAVEDATA);
            $this->addScoreDate();
        }
    }

    protected function setStudentTrainingPerformance($stdClass)
    {
        $SAVEDATA = array();

        if (isset($stdClass->performanceValue))
            $SAVEDATA["PERFORMANCE_VALUE"] = $stdClass->performanceValue;
        if (isset($stdClass->performanceRank))
            $SAVEDATA["RANK"] = $stdClass->performanceRank;
        if (isset($stdClass->performanceAssessment))
            $SAVEDATA["ASSESSMENT_ID"] = $stdClass->performanceAssessment;
        if (isset($stdClass->teacherComment))
            $SAVEDATA["TEACHER_COMMENT"] = $stdClass->teacherComment;

        $SAVEDATA["PUBLISHED_DATE"] = getCurrentDBDateTime();
        $SAVEDATA["PUBLISHED_BY"] = Zend_Registry::get('USER')->CODE;

        if (self::getStudentTrainingPerformance($stdClass->studentId, $stdClass->trainingId))
        {
            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "TRAINING_ID = '" . $stdClass->trainingId . "'";
            self::dbAccess()->update('t_student_training_performance', $SAVEDATA, $WHERE);
        }
        else
        {
            if ($stdClass->studentId && $stdClass->trainingId)
            {
                $SAVEDATA["STUDENT_ID"] = $stdClass->studentId;
                $SAVEDATA["TRAINING_ID"] = $stdClass->trainingId;
                self::dbAccess()->insert("t_student_training_performance", $SAVEDATA);
            }
        }
    }

    protected function checkStudentScoreSubjectAssignment()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training_assignment", array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT = '" . $this->subjectId . "'");
        $SQL->where("TRAINING= '" . $this->trainingId . "'");
        $SQL->where("STUDENT= '" . $this->studentId . "'");
        $SQL->where("SCORE_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function getCountScoreInputDate()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT_ID= '" . $this->subjectId . "'");
        $SQL->where("TRAINING_ID = '" . $this->trainingId . "'");
        $SQL->where("SCORE_INPUT_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    protected function addScoreDate()
    {

        if (!$this->getCountScoreInputDate())
        {
            $SAVEDATA = array();
            $SAVEDATA["ASSIGNMENT_ID"] = $this->assignmentId;
            $SAVEDATA["SUBJECT_ID"] = $this->subjectId;
            $SAVEDATA["TRAINING_ID"] = $this->trainingId;
            $SAVEDATA["SCORE_INPUT_DATE"] = $this->date;
            self::dbAccess()->insert("t_student_score_date", $SAVEDATA);
        }
    }

    public static function getTrainingListAssignmentScoreDate($trainingId, $subjectId, $setGroup)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), array("ID", "SHORT"));
        $SQL->joinLeft(array('B' => 't_training_subject'), 'A.ID=B.ASSIGNMENT', array("COEFF_VALUE"));
        $SQL->joinLeft(array('C' => 't_student_score_date'), 'A.ID=C.ASSIGNMENT_ID', array("ID AS OBJECT_ID", "SCORE_INPUT_DATE"));
        $SQL->where("B.OBJECT_TYPE = 'ITEM'");
        $SQL->where("C.SUBJECT_ID = ?", $subjectId);
        $SQL->where("C.TRAINING_ID = ?", $trainingId);
        $SQL->order('A.SORTKEY ASC');

        switch ($setGroup)
        {
            case "ASSIGNMENT":
                $SQL->group("C.ASSIGNMENT_ID");
                break;
        }
        
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getAssignmentCountScoreDate($assignmentId, $trainingId, $subjectId)
    {
        $SQL = UserAuth::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array());
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.TRAINING_ID = ?", $trainingId);
        $SQL->where("B.ASSIGNMENT_ID = ?", $assignmentId);
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        
        //error_log($SQL->__toString());
        return $result ? $result->C : 0;
    }

    protected static function setStudentTrainingSubjectAssessment($stdClass)
    {
        $SAVEDATA = array();

        if (isset($stdClass->subjectValue))
            $SAVEDATA["SUBJECT_VALUE"] = $stdClass->subjectValue;
        if (isset($stdClass->subjectRank))
            $SAVEDATA["RANK"] = $stdClass->subjectRank;
        if (isset($stdClass->subjectAssessment))
            $SAVEDATA["ASSESSMENT_ID"] = $stdClass->subjectAssessment;
        if (isset($stdClass->teacherComment))
            $SAVEDATA["TEACHER_COMMENT"] = $stdClass->teacherComment;

        $SAVEDATA["PUBLISHED_DATE"] = getCurrentDBDateTime();
        $SAVEDATA["PUBLISHED_BY"] = Zend_Registry::get('USER')->CODE;

        if (self::getStudentTrainingSubjectAssessment($stdClass->studentId, $stdClass->subjectId, $stdClass->trainingId))
        {
            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
            $WHERE[] = "TRAINING_ID = '" . $stdClass->trainingId . "'";
            self::dbAccess()->update('t_student_subject_training_assessment', $SAVEDATA, $WHERE);
        }
        else
        {
            if ($stdClass->studentId && $stdClass->subjectId && $stdClass->trainingId)
            {
                $SAVEDATA["STUDENT_ID"] = $stdClass->studentId;
                $SAVEDATA["SUBJECT_ID"] = $stdClass->subjectId;
                $SAVEDATA["TRAINING_ID"] = $stdClass->trainingId;
                self::dbAccess()->insert("t_student_subject_training_assessment", $SAVEDATA);
            }
        }
    }

    protected static function getStudentTrainingPerformance($studentId, $trainingId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training_performance", array("*"));
        $SQL->where("STUDENT_ID = '" . $studentId . "'");
        $SQL->where("TRAINING_ID = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function getStudentTrainingSubjectAssessment($studentId, $subjectId, $trainingId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_training_assessment", array("*"));
        $SQL->where("STUDENT_ID = '" . $studentId . "'");
        $SQL->where("SUBJECT_ID= '" . $subjectId . "'");
        $SQL->where("TRAINING_ID = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function getStudentAVGAllSubjects($studentId, $trainingId)
    {

        $SELECTION = array(
            "AVG(SUBJECT_VALUE) AS AVG_SUBJECT_VALUE");
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_training_assessment", $SELECTION);
        $SQL->where("STUDENT_ID = '" . $studentId . "'");
        $SQL->where("TRAINING_ID = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $AVG_SUBJECT_VALUE = "";
        $output = "---";

        if ($result)
        {
            $AVG_SUBJECT_VALUE = $result->AVG_SUBJECT_VALUE ? $result->AVG_SUBJECT_VALUE : "";

            if ($AVG_SUBJECT_VALUE)
            {
                $output = displayRound($AVG_SUBJECT_VALUE);
            }
        }

        return $output;
    }

    protected static function getAssessment($object, $average)
    {
        $data = array();
        if ($object)
        {
            $data["ASSESSMENT_ID"] = $object->ASSESSMENT_ID;
            $data["GRADING"] = AssessmentConfig::makeGrade($object->ASSESSMENT_ID, false);
        }
        else
        {
            $assessmentId = AssessmentConfig::calculateGradingScale($average, "training");
            $data["ASSESSMENT_ID"] = $assessmentId;
            $data["GRADING"] = AssessmentConfig::makeGrade($assessmentId, false);
        }

        return (object) $data;
    }

}

?>