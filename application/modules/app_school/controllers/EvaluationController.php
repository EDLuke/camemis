<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 21.12.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/student/StudentAcademicDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/evaluation/ScoreImportDBAccess.php';
require_once 'models/app_school/evaluation/default/StudentAssignmentDBAccess.php';
require_once 'models/app_school/evaluation/default/StudentSubjectAssessment.php';
require_once 'models/app_school/evaluation/default/StudentTraditionalPerformance.php';

require_once 'models/assessment/jsonEvaluationSubjectAssessment.php';

class EvaluationController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();
        $this->DB_SUBJECT = SubjectDBAccess::getInstance();
        $this->DB_STUDENT = StudentDBAccess::getInstance();
        $this->DB_ASSIGNMENT = AssignmentDBAccess::getInstance();
        $this->DB_STUDENT_ASSIGNMENT = StudentAssignmentDBAccess::getInstance();
        $this->DB_SUBJECT_ASSESSMENT = StudentSubjectAssessment::getInstance();
        $this->DB_CLASS_PERFORMANCE = StudentTraditionalPerformance::getInstance();
        $this->DB_SCORE_IMPORT = ScoreImportDBAccess::getInstance();

        $this->setId = null;
        $this->schoolyearId = null;
        $this->classId = null;
        $this->assignmentId = null;
        $this->date = null;
        $this->term = null;
        $this->monthyear = null;
        $this->studentId = null;
        $this->subjectId = null;
        $this->subjectObject = null;
        $this->studentObject = null;
        $this->classObject = null;
        $this->display = null;
        $this->target = null;
        $this->type = null;
        $this->term = null;
        $this->section = null;

        if ($this->_getParam('objectId')) {
            $this->studentId = $this->_getParam('objectId');
            $this->studentObject = StudentDBAccess::findStudentFromId($this->studentId);
        }

        if ($this->_getParam('section'))
            $this->section = $this->_getParam('section');

        if ($this->_getParam('monthyear'))
            $this->monthyear = $this->_getParam('monthyear');

        if ($this->_getParam('classId')) {
            $this->classId = $this->_getParam('classId');
            $this->classObject = AcademicDBAccess::findGradeFromId($this->classId);
            if ($this->classObject) {
                $this->gradeId = $this->classObject->GRADE_ID;
                $this->schoolyearId = $this->classObject->SCHOOL_YEAR;
            }
        }

        if ($this->_getParam('setId'))
            $this->setId = $this->_getParam('setId');

        if ($this->_getParam('subjectId')) {
            $this->subjectId = $this->_getParam('subjectId');
            $this->subjectObject = SubjectDBAccess::findSubjectFromId($this->subjectId);
        }

        if ($this->_getParam('assignmentId')) {
            $this->assignmentId = $this->_getParam('assignmentId');
        }

        if ($this->_getParam('date'))
            $this->date = $this->_getParam('date');

        if ($this->_getParam('term'))
            $this->term = $this->_getParam('term');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('display'))
            $this->display = $this->_getParam('display');

        if ($this->_getParam('type'))
            $this->type = $this->_getParam('type');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');
    }

    public function classassignmentAction() {

        $this->view->classId = $this->classId;
        $this->view->assignmentId = $this->assignmentId;

        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/assignment');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/assignment');
        }
    }

    public function gradeboookAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;

        if ($this->classObject) {
            if ($this->classObject->EDUCATION_SYSTEM) {
                $this->_helper->viewRenderer('creditsystem/display/gradebookmain');
            } else {
                $this->_helper->viewRenderer('classicsystem/display/gradebookmain');
            }
        }
    }

    public function gradebookchartAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('classicsystem/display/gradebookchart');
    }

    public function gradebookmonthAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('classicsystem/display/gradebookmonth');
    }

    public function creditgradebookmonthAction() {

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('creditsystem/display/gradebookmonth');
    }

    public function subjectassignmentsAction() {

        $this->view->classId = $this->classId;
        $this->view->URL_CLASS_ASSIGNMENT = UTILES::createUrl('evaluation/classassignment', array(
                    "subjectId" => $this->subjectId, "classId" => $this->classId)
        );
        $this->view->camIds .= "&subjectId=" . $this->subjectId . "";

        switch ($this->classObject->EDUCATION_SYSTEM) {
            case 1:
                $this->view->subjectId = $this->classObject->SUBJECT_ID;
                $this->_helper->viewRenderer('creditsystem/score/subjectassignments');
                break;
            default:
                $this->view->subjectId = $this->subjectId;
                $this->_helper->viewRenderer('classicsystem/score/subjectassignments');
                break;
        }
    }

    public function subjectscoreenterAction() {

        $data = explode("_", $this->setId);

        $this->assignmentId = isset($data[0]) ? $data[0] : "";
        $this->date = isset($data[1]) ? $data[1] : $this->date;
        $this->view->assignmentId = $this->assignmentId;

        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->view->subjectId = $this->classObject->SUBJECT_ID;
            $this->_helper->viewRenderer('creditsystem/score/subjectscoreenter');
        } else {
            $this->view->subjectId = $this->subjectId;
            $this->_helper->viewRenderer('classicsystem/score/subjectscoreenter');
        }

        $this->view->date = $this->date;
        $this->view->classId = $this->classId;
        $this->view->term = $this->term;
        $this->view->subjectObject = $this->subjectObject;

        if ($this->assignmentId) {
            $this->view->assignmentObject = AssignmentDBAccess::findAssignmentFromId($this->assignmentId);
        }
    }

    public function subjectmainscoresummaryAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectmainscoresummary');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectmainscoresummary');
        }
    }

    public function subjectscoreenterexportAction() {
        $this->view->type = $this->type;
        $this->view->classId = $this->classId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;

        switch (strtoupper($this->target)) {
            case "GENERAL":
                $this->_helper->viewRenderer('export/general/subjectscoreenterexport');
                break;
            case "TRAINING":
                $this->_helper->viewRenderer('export/training/subjectscoreentertrainingexport');
                break;
        }
    }

    public function importassignmentxlsAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;

        switch (strtoupper($this->target)) {
            case "GENERAL":
                $this->_helper->viewRenderer('import/general/importassignmentxls');
                break;
            case "TRAINING":
                $this->_helper->viewRenderer('import/training/importassignmentxls');
                break;
        }
    }

    public function jsonimportAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->display = $this->display;
        $this->view->monthyear = $this->monthyear;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;

        switch ($this->REQUEST->getPost('cmd')) {
            case "importassignmentXLS":
                $jsondata = $this->DB_SCORE_IMPORT->importassignmentXLS($this->REQUEST->getPost());
                break;
            case "importassignmenttrainingXLS":
                $jsondata = $this->DB_SCORE_IMPORT->importassignmenttrainingXLS($this->REQUEST->getPost());
                break;
        }

        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }

    public function subjectresultmonthmainAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectresultmonthmain');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectresultmonthmain');
        }
    }

    public function subjectresultmonthAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;

        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectresultmonth');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectresultmonth');
        }
    }

    public function subjectresultsemesterAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectresultsemester');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectresultsemester');
        }
    }

    public function subjectresultyearAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectresultyear');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectresultyear');
        }
    }

    public function subjectscoresummaryAction() {

        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;

        if ($this->classObject->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer('creditsystem/score/subjectscoresummary');
        } else {
            $this->_helper->viewRenderer('classicsystem/score/subjectscoresummary');
        }
    }

    public function gradebooktraditionalAction() {

        $this->classId = $this->classObject->GUID;

        $this->view->facette = $this->classObject;
        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;

        $this->_helper->viewRenderer('classicsystem/display/gradebookmain');
    }

    public function gradebookcreditAction() {
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('creditsystem/display/gradebookmain');
    }

    ////////////////////////////////////////////////////////////////////////////
    public function displayyearsubjectAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;

        $this->_helper->viewRenderer('classicsystem/display/displayyearsubject');
    }

    public function displaysemestersubjectAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;
        $this->view->term = $this->term;

        $this->_helper->viewRenderer('classicsystem/display/displaysemestersubject');
    }

    public function displaymonthsubjectAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->term = $this->term;
        $this->view->subjectId = $this->subjectId;
        $this->view->monthyear = $this->monthyear;

        $this->_helper->viewRenderer('classicsystem/display/displaymonthsubject');
    }

    public function settingbehaviorAction() {
        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->term = $this->term;
        $this->view->section = $this->section;
        $this->_helper->viewRenderer('classicsystem/display/settingbehavior');
    }

    public function performancemaincreditAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;

        $this->view->URL_PERFORMANCE_YEAR = $this->UTILES->buildURL('evaluation/performanceyearcredit', array(
            "classId" => $this->classId)
        );

        $this->view->URL_PERFORMANCE_SEMESTER = $this->UTILES->buildURL('evaluation/performancesemestercredit', array(
            "classId" => $this->classId,
            "semester" => $this->term)
        );

        $this->_helper->viewRenderer('classicsystem/performance/credit/index');
    }

    public function performanceyearcreditAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('classicsystem/performance/credit/displayyearsubject');
    }

    public function performancesemestercreditAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('classicsystem/performance/credit/displaysemestersubject');
    }

    public function performancemaintraditionalAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('classicsystem/performance/index');
    }

    public function performanceyeartraditionalAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&classId" => $this->classId)
        );

        $this->_helper->viewRenderer('classicsystem/performance/displayyear');
    }

    public function performancesemestertraditionalAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&classId" => $this->classId)
        );

        $this->_helper->viewRenderer('classicsystem/performance/displaysemester');
    }

    public function performancemonthtraditionalmainAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('classicsystem/performance/displaymonthmain');
    }

    public function performancemonthtraditionalAction() {

        $this->view->classId = $this->classId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&classId" => $this->classId)
        );

        $this->_helper->viewRenderer('classicsystem/performance/displaymonth');
    }

    public function classperformancesAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_PERFORMANCES");
        if ($this->classObject) {

            switch ($this->classObject->EDUCATION_SYSTEM) {
                case 1:
                    $CLASS_PERFORMENCES = $this->UTILES->buildURL('evaluation/performancemaincredit', array(
                        "classId" => $this->classId)
                    );
                    break;
                default:
                    $CLASS_PERFORMENCES = $this->UTILES->buildURL('evaluation/performancemaintraditional', array(
                        "classId" => $this->classId)
                    );
                    break;
            }

            $this->_redirect($CLASS_PERFORMENCES);
        }
    }

    public function teachercommentAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;
        $this->view->term = $this->term;
        $this->view->monthyear = $this->monthyear;
        $this->_helper->viewRenderer('classicsystem/comment/editcomment');
    }

    public function setUrlSubjectHomework() {

        return $this->UTILES->buildURL('homework', array(
                    "classId" => $this->classId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectAssignments() {

        return $this->UTILES->buildURL('evaluation/subjectassignments', array(
                    "classId" => $this->classId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectAssinments() {

        return UTILES::createUrl('evaluation/subjectassignments', array(
                    "classId" => $this->classId, "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectMainScoreSummary() {

        return UTILES::createUrl('evaluation/subjectmainscoresummary', array(
                    "classId" => $this->classId, "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectScoreImport() {

        return UTILES::createUrl('evaluation/importassignments', array(
                    "classId" => $this->classId, "subjectId" => $this->subjectId)
        );
    }

    public function setUrlFirstScoreSubject() {

        return $this->UTILES->buildURL('evaluation/firstscoresubject', array(
                    "classId" => $this->classId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSecondScoreSubject() {

        return $this->UTILES->buildURL('evaluation/secondscoresubject', array(
                    "classId" => $this->classId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectScoreExport() {

        return $this->UTILES->buildURL('evaluation/exportassignments', array(
                    "classId" => $this->classId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonListStudentSubjectAssignments":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonListStudentSubjectAssignments($this->REQUEST->getPost());
                break;

            case "jsonSubjectMonthResult":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectMonthResult($this->REQUEST->getPost());
                break;

            case "jsonSubjectTermResult":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectTermResult($this->REQUEST->getPost());
                break;

            case "jsonSubjectYearResult":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectYearResult($this->REQUEST->getPost());
                break;

            case "jsonListStudentsScoreEnter":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonListStudentsScoreEnter($this->REQUEST->getPost());
                break;

            case "jsonLoadTeacherScoreInputDate":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonLoadTeacherScoreInputDate($this->REQUEST->getPost('setId'));
                break;

            case "jsonLoadStudentLearningResult":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonLoadStudentLearningResult($this->REQUEST->getPost());
                break;

            case "jsonSemesterAssementSummary":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonSemesterAssementSummary($this->REQUEST->getPost());
                break;

            case "jsonYearAssementSummary":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonYearAssementSummary($this->REQUEST->getPost());
                break;

            case "jsonListStudentsMonthClassPerformance":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonListStudentsMonthClassPerformance($this->REQUEST->getPost());
                break;

            case "jsonListStudentsSemesterClassPerformance":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonListStudentsSemesterClassPerformance($this->REQUEST->getPost());
                break;

            case "jsonListStudentsYearClassPerformance":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonListStudentsYearClassPerformance($this->REQUEST->getPost());
                break;

            case "jsonListStudentsSubjectAssessment":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonListStudentsSubjectAssessment($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentAllSubjectAssessment":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonLoadStudentAllSubjectAssessment($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentClassPerformance":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonLoadStudentClassPerformance($this->REQUEST->getPost());
                break;
            ////////////////////////////////////////////////////////////////////
            //CREDIT SYSTEM...
            case "jsonLoadStudentCreditAllSubjectAssessment":
                require_once 'models/app_school/evaluation/default/StudentCreditPerformance.php';
                $DB_PERFORMANCE = StudentCreditPerformance::getInstance();
                $jsondata = $DB_PERFORMANCE->jsonLoadStudentCreditAllSubjectAssessment($this->REQUEST->getPost());
                break;
            ////////////////////////////////////////////////////////////////////
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveStudentScoreSubjectAssignment":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonSaveStudentScoreSubjectAssignment($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteAllScoresAssignment":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonActionDeleteAllScoresAssignment($this->REQUEST->getPost());
                break;

            case "jsonAcitonModifyDate":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonAcitonModifyDate($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteSingleScore":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonActionDeleteSingleScore($this->REQUEST->getPost());
                break;

            case "jsonActionStudenLearningResult":
                $jsondata = StudentSubjectAssessment::jsonActionStudenLearningResult($this->REQUEST->getPost());
                break;

            case "jsonActionTeacherScoreInputDate":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonActionTeacherScoreInputDate($this->REQUEST->getPost());
                break;

            case "jsonActionTeacherAssignmentComment":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonActionTeacherAssignmentComment($this->REQUEST->getPost());
                break;

            case "jsonActionStudentSubjectAssessment":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonActionStudentSubjectAssessment($this->REQUEST->getPost());
                break;

            case "jsonActionStudentClassPerformance":
                $jsondata = $this->DB_CLASS_PERFORMANCE->jsonActionStudentClassPerformance($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteAllScoresSubject":
                $jsondata = $this->DB_STUDENT_ASSIGNMENT->jsonActionDeleteAllScoresSubject($this->REQUEST->getPost());
                break;

            case "jsonSetSubjectAssessment":
                $jsondata = $this->DB_SUBJECT_ASSESSMENT->jsonSetSubjectAssessment($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>