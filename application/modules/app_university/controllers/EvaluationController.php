<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';

require_once 'models/assessment/jsonEvaluationSubjectAssessment.php';
require_once 'models/assessment/jsonAcademicPerformances.php';
require_once 'models/assessment/jsonEvaluationGradebook.php';

class EvaluationController extends Zend_Controller_Action {

    public function init()
    {

        if (!UserAuth::identify())
        {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds'))
        {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();
        $this->DB_SUBJECT = SubjectDBAccess::getInstance();
        $this->DB_STUDENT = StudentDBAccess::getInstance();
        $this->DB_ASSIGNMENT = AssignmentDBAccess::getInstance();

        $this->setId = null;
        $this->schoolyearId = null;
        $this->academicId = null;
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

        if ($this->_getParam('classId'))
        {
            $this->academicId = $this->_getParam('classId');
        }
        elseif ($this->_getParam('academicId'))
        {
            $this->academicId = $this->_getParam('academicId');
        }

        if ($this->_getParam('objectId'))
        {
            $this->studentId = $this->_getParam('objectId');
            $this->studentObject = StudentDBAccess::findStudentFromId($this->studentId);
        }

        if ($this->_getParam('section'))
            $this->section = $this->_getParam('section');

        if ($this->_getParam('monthyear'))
            $this->monthyear = $this->_getParam('monthyear');

        $this->classObject = AcademicDBAccess::findGradeFromId($this->academicId);

        if ($this->classObject)
        {
            $this->gradeId = $this->classObject->GRADE_ID;
            $this->schoolyearId = $this->classObject->SCHOOL_YEAR;
        }

        if ($this->_getParam('setId'))
            $this->setId = $this->_getParam('setId');

        if ($this->_getParam('subjectId'))
        {
            $this->subjectId = $this->_getParam('subjectId');
            $this->subjectObject = SubjectDBAccess::findSubjectFromId($this->subjectId);
        }

        if ($this->_getParam('assignmentId'))
        {
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

    public function classassignmentAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->assignmentId = $this->assignmentId;

        $this->_helper->viewRenderer('score/assignment');
    }

    public function gradeboookAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;

        $this->_helper->viewRenderer('display/gradebookmain');
    }

    public function gradebookmonthAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('display/gradebookmonth');
    }

    //@Visal
    public function chartAction()
    {
        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('chart');
    }

    public function creditgradebookmonthAction()
    {

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('display/gradebookmonth');
    }

    public function subjectassignmentsAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->URL_CLASS_ASSIGNMENT = UTILES::createUrl('evaluation/classassignment', array(
                    "subjectId" => $this->subjectId, "academicId" => $this->academicId)
        );
        $this->view->camIds .= "&subjectId=" . $this->subjectId . "";
        $this->_helper->viewRenderer('score/subjectassignments');
        switch ($this->classObject->EDUCATION_SYSTEM)
        {
            case 1:
                $this->view->subjectId = $this->classObject->SUBJECT_ID;
                break;
            default:
                $this->view->subjectId = $this->subjectId;
                break;
        }
    }

    public function subjectscoreenterAction()
    {

        $data = explode("_", $this->setId);

        $this->assignmentId = isset($data[0]) ? $data[0] : "";
        $this->date = isset($data[1]) ? $data[1] : $this->date;
        $this->view->assignmentId = $this->assignmentId;

        if ($this->classObject->EDUCATION_SYSTEM)
        {
            $this->view->subjectId = $this->classObject->SUBJECT_ID;
        }
        else
        {
            $this->view->subjectId = $this->subjectId;
        }
        $this->_helper->viewRenderer('score/subjectscoreenter');
        $this->view->date = $this->date;
        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;
        $this->view->subjectObject = $this->subjectObject;

        if ($this->assignmentId)
        {
            $this->view->assignmentObject = AssignmentDBAccess::findAssignmentFromId($this->assignmentId);
        }
    }

    public function subjectscoreenterexportAction()
    {
        $this->view->type = $this->type;
        $this->view->academicId = $this->academicId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;

        switch (strtoupper($this->target))
        {
            case "GENERAL":
                $this->_helper->viewRenderer('export/subjectscoreenterexport');
                break;
            case "TRAINING":
                $this->_helper->viewRenderer('export/subjectscoreentertrainingexport');
                break;
        }
    }

    public function creditsubjectstatusAction()
    {
        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/creditsubjectstatus');
    }

    public function importassignmentxlsAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;

        switch (strtoupper($this->target))
        {
            case "GENERAL":
                $this->_helper->viewRenderer('import/importassignmentxls');
                break;
            case "TRAINING":
                $this->_helper->viewRenderer('import/importassignmentxls');
                break;
        }
    }

    public function jsonimportAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            case "jsonScoreImport":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonScoreImport($this->REQUEST->getPost());
                break;
        }

        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }

    public function subjectresultmonthmainAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/subjectresultmonthmain');
    }

    public function subjectresultmonthAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/subjectresultmonth');
    }

    public function subjectresultsemesterAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/subjectresultsemester');
    }

    public function subjectresultyearAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/subjectresultyear');
    }

    public function subjectscoresummaryAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->_helper->viewRenderer('score/subjectscoresummary');
    }

    public function gradebooktraditionalAction()
    {

        $this->academicId = $this->classObject->GUID;

        $this->view->facette = $this->classObject;
        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;

        $this->_helper->viewRenderer('display/gradebookmain');
    }

    public function gradebookcreditAction()
    {
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer('display/gradebookmain');
    }

    ////////////////////////////////////////////////////////////////////////////
    public function displayyearsubjectAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;

        $this->_helper->viewRenderer('display/displayyearsubject');
    }

    public function displaysemestersubjectAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;
        $this->view->term = $this->term;

        $this->_helper->viewRenderer('display/displaysemestersubject');
    }

    public function displaymonthsubjectAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->view->term = $this->term;
        $this->view->subjectId = $this->subjectId;
        $this->view->monthyear = $this->monthyear;

        $this->_helper->viewRenderer('display/displaymonthsubject');
    }

    public function settingbehaviorAction()
    {
        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->view->term = $this->term;
        $this->view->section = $this->section;
        $this->_helper->viewRenderer('display/settingbehavior');
    }

    public function performancemaincreditAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;

        $this->view->URL_PERFORMANCE_YEAR = $this->UTILES->buildURL('evaluation/performanceyearcredit', array(
            "academicId" => $this->academicId)
        );

        $this->view->URL_PERFORMANCE_SEMESTER = $this->UTILES->buildURL('evaluation/performancesemestercredit', array(
            "academicId" => $this->academicId,
            "semester" => $this->term)
        );

        $this->_helper->viewRenderer('performance/credit/index');
    }

    public function performanceyearcreditAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('performance/credit/displayyearsubject');
    }

    public function performancesemestercreditAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('performance/credit/displaysemestersubject');
    }

    public function performancemaintraditionalAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('performance/index');
    }

    public function performanceyeartraditionalAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&academicId" => $this->academicId)
        );

        $this->_helper->viewRenderer('/performance/displayyear');
    }

    public function performancesemestertraditionalAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&academicId" => $this->academicId)
        );

        $this->_helper->viewRenderer('performance/displaysemester');
    }

    public function performancemonthtraditionalmainAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;
        $this->_helper->viewRenderer('performance/displaymonthmain');
    }

    public function performancemonthtraditionalAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->term = $this->term;

        $this->view->URL_TEACHER_COMMENT = UTILES::createUrl('evaluation/teachercomment', array(
                    "&academicId" => $this->academicId)
        );

        $this->_helper->viewRenderer('performance/displaymonth');
    }

    public function scoreimportAction()
    {
        $this->_helper->viewRenderer('scoreimportxls');
    }

    public function scoreimporttemplateAction()
    {
        $this->_helper->viewRenderer('scoreimporttemplate');
    }

    public function classperformancesAction()
    {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_PERFORMANCES");
        if ($this->classObject)
        {

            switch ($this->classObject->EDUCATION_SYSTEM)
            {
                case 1:
                    $CLASS_PERFORMENCES = $this->UTILES->buildURL('evaluation/performancemaincredit', array(
                        "academicId" => $this->academicId)
                    );
                    break;
                default:
                    $CLASS_PERFORMENCES = $this->UTILES->buildURL('evaluation/performancemaintraditional', array(
                        "academicId" => $this->academicId)
                    );
                    break;
            }

            $this->_redirect($CLASS_PERFORMENCES);
        }
    }

    public function teachercommentAction()
    {

        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;
        $this->view->term = $this->term;
        $this->view->monthyear = $this->monthyear;
        $this->_helper->viewRenderer('comment/editcomment');
    }

    public function setUrlSubjectHomework()
    {

        return $this->UTILES->buildURL('homework', array(
                    "academicId" => $this->academicId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectAssignments()
    {

        return $this->UTILES->buildURL('evaluation/subjectassignments', array(
                    "academicId" => $this->academicId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectAssinments()
    {

        return UTILES::createUrl('evaluation/subjectassignments', array(
                    "academicId" => $this->academicId, "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectScoreImport()
    {

        return UTILES::createUrl('evaluation/importassignments', array(
                    "academicId" => $this->academicId, "subjectId" => $this->subjectId)
        );
    }

    public function setUrlFirstScoreSubject()
    {

        return $this->UTILES->buildURL('evaluation/firstscoresubject', array(
                    "academicId" => $this->academicId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSecondScoreSubject()
    {

        return $this->UTILES->buildURL('evaluation/secondscoresubject', array(
                    "academicId" => $this->academicId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function setUrlSubjectScoreExport()
    {

        return $this->UTILES->buildURL('evaluation/exportassignments', array(
                    "academicId" => $this->academicId,
                    "subjectId" => $this->subjectId)
        );
    }

    public function jsonloadAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            case "jsonCreditSubjectStatus":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonCreditSubjectStatus($this->REQUEST->getPost());
                break;
            case "jsonListStudentSubjectAssignments":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonListStudentSubjectAssignments($this->REQUEST->getPost());
                break;

            case "jsonSubjectResultsByMonth":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectResultsByMonth($this->REQUEST->getPost());
                break;

            case "jsonSubjectResultsByTerm":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectResultsByTerm($this->REQUEST->getPost());
                break;

            case "jsonSubjectResultsByYear":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonSubjectResultsByYear($this->REQUEST->getPost());
                break;

            case "jsonListStudentsTeacherScoreEnter":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonListStudentsTeacherScoreEnter($this->REQUEST->getPost());
                break;

            case "jsonLoadContentTeacherScoreInputDate":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonLoadContentTeacherScoreInputDate($this->REQUEST->getPost());
                break;

            case "jsonStudentGradebookMonth":
                $DB_ACCESS = new jsonEvaluationGradebook();
                $jsondata = $DB_ACCESS->jsonStudentGradebookMonth($this->REQUEST->getPost());
                break;

            case "jsonStudentGradebookTerm":
                $DB_ACCESS = new jsonEvaluationGradebook();
                $jsondata = $DB_ACCESS->jsonStudentGradebookTerm($this->REQUEST->getPost());
                break;

            case "jsonStudentGradebookYear":
                $DB_ACCESS = new jsonEvaluationGradebook();
                $jsondata = $DB_ACCESS->jsonStudentGradebookYear($this->REQUEST->getPost());
                break;

            case "jsonListAcademicPerformanceFromMonth":
                $DB_ACCESS = new jsonAcademicPerformances();
                $jsondata = $DB_ACCESS->jsonListAcademicPerformanceFromMonth($this->REQUEST->getPost());
                break;

            case "jsonListAcademicPerformanceFromTerm":
                $DB_ACCESS = new jsonAcademicPerformances();
                $jsondata = $DB_ACCESS->jsonListAcademicPerformanceFromTerm($this->REQUEST->getPost());
                break;

            case "jsonListAcademicPerformanceFromYear":
                $DB_ACCESS = new jsonAcademicPerformances();
                $jsondata = $DB_ACCESS->jsonListAcademicPerformanceFromYear($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonActionDeleteAllStudentsTeacherScoreEnter":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionDeleteAllStudentsTeacherScoreEnter($this->REQUEST->getPost());
                break;

            case "jsonAcitonSubjectAssignmentModifyScoreDate":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonAcitonSubjectAssignmentModifyScoreDate($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteOneStudentTeacherScoreEnter":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionDeleteOneStudentTeacherScoreEnter($this->REQUEST->getPost());
                break;

            case "jsonActionContentTeacherScoreInputDate":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionContentTeacherScoreInputDate($this->REQUEST->getPost());
                break;

            case "jsonActionStudentSubjectAssessment":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionStudentSubjectAssessment($this->REQUEST->getPost());
                break;

            case "jsonActionStudentAcademicPerformance":
                $DB_ACCESS = new jsonAcademicPerformances();
                $jsondata = $DB_ACCESS->jsonActionStudentAcademicPerformance($this->REQUEST->getPost());
                break;

            case "jsonActionPublishSubjectAssessment":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionPublishSubjectAssessment($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteSubjectScoreAssessment":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionDeleteSubjectScoreAssessment($this->REQUEST->getPost());
                break;

            case "jsonActionTeacherScoreEnter":
                $DB_ACCESS = new jsonEvaluationSubjectAssessment();
                $jsondata = $DB_ACCESS->jsonActionTeacherScoreEnter($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata)
    {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>