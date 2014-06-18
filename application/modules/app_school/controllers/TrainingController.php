<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/DescriptionDBAccess.php';
require_once 'models/training/TrainingSubjectDBAccess.php';
require_once 'models/training/TrainingDBAccess.php';
require_once 'models/training/TeacherTrainingDBAccess.php';
require_once 'models/training/StudentTrainingDBAccess.php';
require_once 'models/app_school/assignment/AssignmentTempDBAccess.php';
require_once 'models/UserAuth.php';

class TrainingController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        $this->DB_TRAINING = TrainingDBAccess::getInstance();
        $this->DB_TRAINING_SUBJECT = TrainingSubjectDBAccess::getInstance();
        $this->DB_STUDENT_TRAINING = StudentTrainingDBAccess::getInstance();
        $this->DB_ASSIGNMENT_TEMP = AssignmentTempDBAccess::getInstance();

        $this->objectId = null;
        $this->trainingId = null;
        $this->assignmentId = null;
        $this->target = null;
        $this->objectData = array();
        $this->facette = null;
        $this->subjectId = null;
        $this->setId = null;
        $this->date = null;


        if ($this->_getParam('setId'))
            $this->setId = $this->_getParam('setId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('date'))
            $this->date = $this->_getParam('date');

        if ($this->_getParam('trainingId')) {

            $this->trainingId = $this->_getParam('trainingId');
            $this->objectData = $this->DB_TRAINING->getTrainingDataFromId($this->objectId);
            $this->facette = TrainingDBAccess::findTrainingFromId($this->objectId);
        }
        if ($this->_getParam('assignmentId'))
            $this->assignmentId = $this->_getParam('assignmentId');

        $this->studentId = $this->_getParam('studentId');

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_TRAINING->getTrainingDataFromId($this->objectId);
            $this->facette = TrainingDBAccess::findTrainingFromId($this->objectId);
        }

        $this->target = $this->_getParam('target');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->URL_PROGRAM = $this->UTILES->buildURL('training/program', array());
        $this->view->URL_LEVEL = $this->UTILES->buildURL('training/level', array());
        $this->view->URL_TERM = $this->UTILES->buildURL('training/term', array());
        $this->view->URL_CLASS = $this->UTILES->buildURL('training/class', array());
    }

    public function subjectresulttrainingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->trainingId = $this->trainingId;
        $this->view->studentId = $this->studentId;
        $this->view->subjectId = $this->subjectId;

        $this->_helper->viewRenderer('/evaluation/subjectresulttraining');
    }

    public function programAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function subjectAction() {

        $this->view->objectId = $this->objectId;
    }

    public function teacherselectionAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;

        $this->_helper->viewRenderer('/teacher/selection');
    }

    public function addprogramAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->objectId = $this->objectId;
    }

    public function levelAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;
    }

    public function studentlistAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->target = $this->target;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;

        $this->view->URL_STUDENT_MONITOR = $this->UTILES->buildURL('student/studentmonitor'
                , array("trainingId" => $this->objectId, "target" => "TRAINING")
        );

        $this->_helper->viewRenderer('/student/list');
    }

    public function termAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;

        $this->view->URL_STUDENT_LIST = $this->UTILES->buildURL('training/studentlist'
                , array("objectId" => $this->objectId, "target" => "TERM")
        );

        $this->view->URL_TEACHER_LIST = $this->UTILES->buildURL('training/teacherlist'
                , array("objectId" => $this->objectId, "target" => "TERM")
        );

        $this->view->URL_SUBJECT_LIST = $this->UTILES->buildURL('training/subjectlist'
                , array("objectId" => $this->objectId, "target" => "TERM")
        );
    }

    public function classAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;

        $this->view->URL_STUDENT_LIST = $this->UTILES->buildURL('training/studentlist'
                , array("objectId" => $this->objectId, "target" => "CLASS")
        );

        $this->view->URL_TEACHER_LIST = $this->UTILES->buildURL('training/teacherlist'
                , array("objectId" => $this->objectId, "target" => "CLASS")
        );

        $this->view->URL_STUDENT_IMPORT = $this->UTILES->buildURL('student/importxls'
                , array("trainingId" => $this->objectId, "target" => "TRAINING")
        );

        $this->view->URL_SCHEDULE = $this->UTILES->buildURL('schedule/byclass'
                , array("trainingId" => $this->objectId, "target" => "TRAINING")
        );

        $this->view->URL_ASSESSMENT = $this->UTILES->buildURL('training/trainingassessment'
                , array("objectId" => $this->objectId)
        );
    }

    public function trainingassessmentAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->objectId = $this->objectId;
        $this->view->trainingId = $this->trainingId;
        $this->view->TEACHER_SELECTION = $this->UTILES->buildURL(
                'training/teacherselection'
                , array("objectId" => $this->objectId));
        //@veasna
        $this->view->URL_SHOW_TRANSCRIPT_TRAINING = $this->UTILES->buildURL(
                'training/evaluationstudenttraining'
                , array("objectId" => $this->objectId)
        );
        //
    }

    //@veasna
    public function evaluationstudenttrainingAction() {

        $this->view->trainingId = $this->trainingId;
        $this->view->objectId = $this->objectId;
        $this->view->assignmentId = $this->assignmentId;
        $this->view->subjectId = $this->subjectId;
        $this->view->URL_SUBJECT_SCORE_ENTER = $this->setUrlSubjectScoresEnter();
        $this->view->camIds = "";
        $this->view->camIds .= "&subjectId=" . $this->subjectId . "";
        $this->view->camIds .= "&trainingId=" . $this->trainingId . "";
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer('/evaluation/list');
    }

    public function setUrlSubjectScoresEnter() {

        return UTILES::createUrl('training/subjectscoreenter', array(
                    "trainingId" => $this->trainingId, "subjectId" => $this->subjectId)
        );
    }

    public function subjectscoreenterAction() {

        $data = explode("_", $this->setId);
        $this->assignmentId = isset($data[0]) ? $data[0] : "";
        $this->date = isset($data[1]) ? $data[1] : $this->date;
        $this->_helper->viewRenderer('/evaluation/subjectscoreenter');
        $this->view->assignmentId = $this->assignmentId;
        $this->view->date = $this->date;
        $this->view->trainingId = $this->trainingId;
        $this->view->subjectId = $this->subjectId;
    }

    public function trainingtranscriptAction() {
        //error_log("subjectId=".$this->subjectId);
        switch (UserAuth::getUserType()) {
            case "SUPERADMIN":
            case "ADMIN":
            case 'INSTRUCTOR':
            case 'TEACHER':
                $this->_helper->viewRenderer('/evaluation/trainingtranscript');
                break;
            case 'STUDENT':
                $this->_helper->viewRenderer('/evaluation/trainingshowitem');
                break;
        }
    }

    public function scoremonitortrainingAction() {
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("evaluation/scoremonitor");
    }

    public function trainingtranscriptshowitemAction() {
        $this->_helper->viewRenderer('/evaluation/trainingtranscript');
    }

    public function trainingtranscriptassignmentAction() {
        $this->_helper->viewRenderer('/evaluation/trainingtranscriptassignment');
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
    }

    //
    public function teacherlismaintAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer('/teacher/main');
    }

    public function teacherlistAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;

        $this->view->TEACHER_SELECTION = $this->UTILES->buildURL(
                'training/teacherselection'
                , array("objectId" => $this->objectId));

        $this->_helper->viewRenderer('/teacher/list');
    }

    public function subjectlistAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;

        $this->view->SHOW_LONGTEXT = $this->UTILES->buildURL('training/showlongtext', array());

        $this->_helper->viewRenderer('/subject/list');
    }

    public function showallsubjectAction() {

        $this->view->objectId = $this->objectId;
        $this->view->subjectId = $this->subjectId;
        $this->view->facette = TrainingSubjectDBAccess::findTrainingSubject($this->objectId);
        $this->_helper->viewRenderer('/subject/main');
    }

    public function showsubjectAction() {

        $this->view->objectId = $this->objectId;
        $this->view->subjectId = $this->subjectId;
        $this->view->facette = TrainingSubjectDBAccess::findTrainingSubject($this->objectId);
        $this->_helper->viewRenderer('/subject/show');
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function teachertrainingsAction() {

        $this->view->URL_STUDENT_SUBJECT_ASSIGNMENT = $this->UTILES->buildURL('training/studentsubjectassignment', array());

        $this->view->URL_SCHEDULE = $this->UTILES->buildURL('schedule/byclass'
                , array("target" => "TRAINING")
        );

        $this->_helper->viewRenderer('/teacher/trainings');
    }

    public function classtransferAction() {
        
    }

    public function studenttrainingsAction() {

        $this->view->URL_STUDENT_TRAINING = $this->UTILES->buildURL('training/studenttraining'
                , array()
        );

        $this->_helper->viewRenderer('/student/trainings');
    }

    public function assessmentbystudentAction() {
        $this->view->studentId = $this->studentId;
        $this->view->objectId = $this->objectId;
    }

    public function studenttrainingAction() {

        $this->studentId = $this->_getParam('studentId');
        $this->objectId = $this->_getParam('objectId');

        $facette = StudentTrainingDBAccess::getLinkStudentAndTraining($this->studentId, $this->objectId);

        $this->view->URL_STUDENT_TRAINING_INFORMATION = $this->UTILES->buildURL('training/studenttraininginfo'
                , array("objectId" => $this->objectId, "studentId" => $this->studentId)
        );

        $this->view->URL_STUDENT_ATTENDANCE = $this->UTILES->buildURL('attendance/bystudent'
                , array('studentId' => $this->studentId, 'trainingId' => $this->objectId, 'target' => 'training')
        );

        $this->view->URL_STUDENT_DISCIPLINE = $this->UTILES->buildURL('discipline/bystudent'
                , array('studentId' => $this->studentId, 'trainingId' => $this->objectId, 'target' => 'training')
        );

        $this->view->URL_STUDENT_TRAINING_DESCRIPTION = $this->UTILES->buildURL('training/description/'
                , array("objectId" => $this->objectId, "studentId" => $this->studentId)
        );

        $this->view->URL_TRACKING_CONTENT = $this->UTILES->buildURL('dataset/ckeditor/'
                , array("object" => "studenttraining", "field" => "TRACKING_CONTENT", "objectId" => $facette->ID)
        );

        $this->view->URL_TRAINING_ASSESSMENT = "";

        $this->_helper->viewRenderer('/student/training');
    }

    public function studenttraininginfoAction() {

        $this->view->studentId = $this->studentId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer('/student/traininginfo');
    }

    public function descriptionAction() {

        $this->view->studentId = $this->studentId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer('/student/description');
    }

    public function studentsubjectassignmentAction() {

        $this->_helper->viewRenderer('/student/subjectassignment');
    }

    public function scoremanagementAction() {
        
    }

    public function scoremonitorAction() {

        $this->view->objectId = $this->objectId;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING->jsonLoadObject($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadStudentTraining":
                $jsondata = StudentTrainingDBAccess::jsonLoadStudentTraining($this->REQUEST->getPost('trainingId'), $this->REQUEST->getPost('studentId'));
                break;

            case "jsonUnassignedSubjectsByTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonUnassignedSubjectsByTraining($this->REQUEST->getPost());
                break;

            case "jsonStudentTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentTrainingDBAccess::jsonStudentTraining($this->REQUEST->getPost());
                break;

            case "jsonStudentTeacherTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentTrainingDBAccess::jsonStudentTeacherTraining($this->REQUEST->getPost());
                break;

            case "jsonStudentByStudentTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentTrainingDBAccess::jsonStudentByStudentTraining($this->REQUEST->getPost());
                break;

            case "jsonLoadTeachersBySubjectTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonLoadTeachersBySubjectTraining($this->REQUEST->getPost());
                break;

            case "jsonSubjectAssignmentTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonSubjectAssignmentTraining($this->REQUEST->getPost());
                break;

            case "jsonTeacherTraining":
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonTeacherTraining($this->REQUEST->getPost());
                break;

            case "loadStudentTraining":
                $jsondata = StudentTrainingDBAccess::loadStudentTraining($this->REQUEST->getPost('chooseId'));
                break;

            case "jsonListStudentInSchool":
                $jsondata = StudentTrainingDBAccess::jsonListStudentInSchool($this->REQUEST->getPost());
                break;

            /* case "jsonStudentSubjectAssignment":
              $jsondata = StudentTrainingDBAccess::jsonStudentSubjectAssignment($this->REQUEST->getPost());
              break;
             */

            case "jsonStudentSubjectAssignment":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonStudentSubjectAssignment($this->REQUEST->getPost());
                break;

            case "jsonStudentSubjectTraining":
                $jsondata = StudentTrainingDBAccess::jsonStudentSubjectTraining($this->REQUEST->getPost());
                break;

            case "jsonStudentTrainingAssessment";
                $jsondata = StudentTrainingDBAccess::jsonStudentTrainingAssessment($this->REQUEST->getPost());
                break;

            case "jsonAssessemntByTrainingSubjects";
                $jsondata = StudentTrainingDBAccess::jsonAssessemntByTrainingSubjects($this->REQUEST->getPost());
                break;

            case "listStudentTrainings";
                $jsondata = StudentTrainingDBAccess::listStudentTrainings($this->REQUEST->getPost());
                break;

            case "loadStudentTrainingDescripton";
                $jsondata = StudentTrainingDBAccess::loadStudentTrainingDescripton($this->REQUEST->getPost());
                break;

            case "loadTrainingSubject";
                $jsondata = TrainingSubjectDBAccess::loadTrainingSubject($this->REQUEST->getPost('objectId'));
                break;
            //@veasna                                                                            
            case "loadTrainingAssignement";
                $jsondata = TrainingSubjectDBAccess::findTrainingAssignmentStudent($this->REQUEST->getPost('objectId'), $this->REQUEST->getPost('studentId'));
                break;
            case "jsonTeacherByStudentTraining":
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonTeacherByStudentTraining($this->REQUEST->getPost());
                break;

            case "jsonSubjectResultTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonSubjectResultTraining($this->REQUEST->getPost());
                break;

            case "jsonListStudentsClassPerformanceTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonListStudentsClassPerformanceTraining($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonActionAddTraining":
            case "jsonSaveObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = TrainingDBAccess::jsonSaveTraining($this->REQUEST->getPost());
                break;

            case "actionRemoveStudentTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentTrainingDBAccess::actionRemoveStudentTraining($this->REQUEST->getPost());
                break;

            case "actionStudentToTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentTrainingDBAccess::actionStudentToTraining($this->REQUEST->getPost());
                break;

            case "removenode":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING->jsonRemoveTraining($this->REQUEST->getPost('objectId'));
                break;

            case "jsonReleaseObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING->jsonReleaseTraining($this->REQUEST->getPost('objectId'));
                break;

            case "jsonAddSubjectToTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonAddSubjectToTraining($this->REQUEST->getPost());
                break;

            case "jsonRemoveSubjectFromTraining":
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonRemoveSubjectFromTraining($this->REQUEST->getPost('objectId'));
                break;

            case "actionSubjectTrainingTeacherClass":
                $jsondata = $this->DB_TRAINING_SUBJECT->actionSubjectTrainingTeacherClass($this->REQUEST->getPost());
                break;

            case "jsonAddAssignmentToTraining":
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonAddAssignmentToTraining($this->REQUEST->getPost());
                break;

            case "actionTrainingStudentAssignment":
                $jsondata = $this->DB_STUDENT_TRAINING->actionTrainingStudentAssignment($this->REQUEST->getPost());
                break;
            /* case "actionTrainingStudentAssignment":
              $jsondata = StudentTrainingDBAccess::actionTrainingStudentAssignment($this->REQUEST->getPost());
              break;
             */
            case "actionStudentTrainingTransfer":
                $jsondata = StudentTrainingDBAccess::actionStudentTrainingTransfer($this->REQUEST->getPost());
                break;

            case "actionStudentTrainingDescription":
                $jsondata = StudentTrainingDBAccess::actionStudentTrainingDescription($this->REQUEST->getPost());
                break;

            case "saveTrainingSubject":
                $jsondata = TrainingSubjectDBAccess::saveTrainingSubject($this->REQUEST->getPost());
                break;

            case "actionStudentTraining":
                $jsondata = StudentTrainingDBAccess::actionStudentTraining($this->REQUEST->getPost());
                break;

            case "jsonActionStudentSubjectAssessmentTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionStudentSubjectAssessmentTraining($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteAllScoresAssignmentTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionDeleteAllScoresAssignmentTraining($this->REQUEST->getPost());
                break;

            case "jsonActionContentTeacherScoreInputDateTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionContentTeacherScoreInputDateTraining($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteSingleScoreTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionDeleteSingleScoreTraining($this->REQUEST->getPost());
                break;

            case "jsonActionCalculationAssessmentTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionCalculationAssessmentTraining($this->REQUEST->getPost());
                break;

            case "jsonActionDeleteAllScoresSubjectTraining":
                $jsondata = $this->DB_STUDENT_TRAINING->jsonActionDeleteAllScoresSubjectTraining($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllTrainings":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = TrainingDBAccess::jsonTreeAllTrainings($this->REQUEST->getPost());
                break;

            case "jsonTreeAssignedSubjectsByTraining":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_TRAINING_SUBJECT->jsonTreeAssignedSubjectsByTraining($this->REQUEST->getPost());
                break;

            case "jsonTreeTeacherTrainings":
                $jsondata = TeacherTrainingDBAccess::jsonTreeTeacherTrainings($this->REQUEST->getPost());
                break;

            case "jsonTreeStudentTrainings":
                $jsondata = StudentTrainingDBAccess::jsonTreeStudentTrainings($this->REQUEST->getPost());
                break;

            case "jsonTreeAssignmentsBySubjctTraining":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonTreeAssignmentsBySubjctTraining($this->REQUEST->getPost());
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