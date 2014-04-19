<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/subject/SubjectHomeworkDBAccess.php';
require_once setUserLoacalization();

class HomeworkController extends Zend_Controller_Action {

    protected $OBJECT_DATA;
    protected $objectId;
    protected $roleAdmin = array("SYSTEM");

    public function init()
    {

        if (!UserAuth::identify())
        {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->UTILES = Utiles::getInstance();
        $this->REQUEST = $this->getRequest();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds'))
        {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->target = null;

        $this->classId = null;

        $this->trainingId = null;

        $this->subjectId = null;

        $this->objectId = null;

        $this->object = null;

        $this->teacherId = null;

        $this->studentId = null;

        $this->homeworkId = null;

        $this->schoolyearId = null;

        $this->facette = null;

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        if ($this->_getParam('object'))
            $this->object = $this->_getParam('object');

        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');

        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId');

        if ($this->_getParam('homeworkId'))
            $this->homeworkId = $this->_getParam('homeworkId');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');
    }

    public function homeworkmaintrainingAction()
    {
        $this->view->trainingId = $this->trainingId;
        $this->view->teacherId = $this->teacherId;
        $this->_helper->viewRenderer("maintraining");
    }

    public function homeworklisttrainingAction()
    {
        $this->view->trainingId = $this->trainingId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer("listtraining");
    }

    public function showitemtrainingAction()
    {

        $this->view->trainingId = $this->trainingId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->homeworkId = $this->homeworkId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        switch (UserAuth::getUserType())
        {
            case 'INSTRUCTOR':
            case 'TEACHER':
                if ($this->objectId == 'new')
                {
                    $this->_helper->viewRenderer("showitemtraining");
                }
                else
                {
                    $this->_helper->viewRenderer("showitemwithouttraining");
                }
                break;
            case "SUPERADMIN":
            case "ADMIN":
                $this->_helper->viewRenderer("showitemtrainingadmin");
                break;
            case 'STUDENT':
                $this->_helper->viewRenderer("studenttraininghomework");
                break;
        }
    }

    public function admintrainingshowitemlistAction()
    {
        $this->view->trainingId = $this->trainingId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->homeworkId = $this->homeworkId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("studenttraininghomework");
    }

    public function teachertrainingshowitemAction()
    {
        $this->view->trainingId = $this->trainingId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("teachertrainingshowitem");
    }

    /* =========================== */

    public function homeworkshowgradesubjectAction()
    {
        $this->view->objectId = $this->objectId;
        $this->view->object = $this->object;
        //$facette = SubjectHomeworkDBAccess::getGradeSubjectHomework($this->objectId);
        $this->_helper->viewRenderer("list");
    }

    public function homeworkmainAction()
    {
        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->_helper->viewRenderer("main");
    }

    public function homeworklistAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->object = $this->object;
        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->_helper->viewRenderer("list");
    }

    public function showitemAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->homeworkId = $this->homeworkId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->view->teacherId = $this->teacherId;
        ///@Sor Veasna
        $this->view->academicObject = AcademicDBAccess::findGradeFromId($this->classId);

        ///

        switch (UserAuth::getUserType())
        {
            case 'INSTRUCTOR':
            case 'TEACHER':
                if ($this->objectId == 'new')
                {
                    $this->_helper->viewRenderer("showitem");
                }
                else
                {
                    if (Zend_Registry::get('USER')->ID == $this->teacherId)
                    {
                        $this->_helper->viewRenderer("showitemwithout");
                    }
                    else
                    {
                        $this->_helper->viewRenderer("showitemadmin");
                    }
                }
                break;
            case "SUPERADMIN":
            case "ADMIN":
                $this->_helper->viewRenderer("showitemadmin");
                break;
            case 'STUDENT':
                $this->_helper->viewRenderer("studenthomework");
                break;
        }
    }

    public function showitemwithoutteacherAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("showitem");
    }

    public function teachershowitemAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        ///@Sor Veasna
        $this->view->academicObject = AcademicDBAccess::findGradeFromId($this->classId);

        ///
        $this->_helper->viewRenderer("teachershowitem");
    }

    public function studentshowitemlistAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->homeworkId = $this->homeworkId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer("studentshowitemlist");
    }

    public function adminshowitemlistAction()
    {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->homeworkId = $this->homeworkId;
        $this->facette = SubjectHomeworkDBAccess::findSubjectHomeworkFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("studenthomework");
    }

    public function studentinfoAction()
    {
        $this->view->studentId = $this->studentId;
        $this->view->objectId = $this->objectId;
        $this->facette = SubjectHomeworkDBAccess::findStudentInfoSubjectHomeworkFromId($this->studentId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("studentinfo");
    }

    public function jsonloadAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonLoadAllSubjectHomeworks":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadAllSubjectHomeworks($this->REQUEST->getPost());
                break;

            case "jsonLoadSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadSubjectHomework($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadStudentHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadStudentHomework($this->REQUEST->getPost('objectId'), $this->REQUEST->getPost('studentId'));
                break;

            case "jsonLoadStudentSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadStudentSubjectHomework($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentHomeworkInfo":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadStudentHomeworkInfo($this->REQUEST->getPost('studentId'));
                break;

            case "jsonLoadAllSubjectTrainingHomeworks":
                $jsondata = SubjectHomeworkDBAccess::jsonLoadAllSubjectTrainingHomeworks($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonAddSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonAddSubjectHomework($this->REQUEST->getPost());
                break;

            case "jsonDeleteSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonDeleteSubjectHomework($this->REQUEST->getPost('objectId'));
                break;

            case "jsonReleaseSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonReleaseSubjectHomework($this->REQUEST->getPost());
                break;

            case "jsonAddStudentHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonAddStudentHomework($this->REQUEST->getPost());
                break;

            case "jsonRemoveStudentSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonRemoveStudentSubjectHomework($this->REQUEST->getPost());
                break;

            case "jsonAddTrainingSubjectHomework":
                $jsondata = SubjectHomeworkDBAccess::jsonAddTrainingSubjectHomework($this->REQUEST->getPost());
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