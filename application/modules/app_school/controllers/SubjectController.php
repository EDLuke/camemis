<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/subject/SubjectTeacherDBAccess.php';
require_once 'models/app_school/subject/SubjectStudentDBAccess.php';
require_once 'models/app_school/subject/GradeSubjectDBAccess.php';

class SubjectController extends Zend_Controller_Action {

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

		$this->urlEncryp = new URLEncryption();
		$this->view->urlEncryp = $this->urlEncryp;
		if ($this->_getParam('camIds')) {
			$this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
		}

		$this->DB_SUBJECT = SubjectDBAccess::getInstance();
		$this->DB_SUBJECT_TEACHER = SubjectTeacherDBAccess::getInstance();
		$this->DB_GRADE_SUBJECT = GradeSubjectDBAccess::getInstance();

		$this->objectId = null;

		$this->classId = null;

		$this->gradeId = null;

		$this->educationType = null;

		$this->showButtons = null;

		$this->sourceModul = null;

		$this->objectData = array();

		$this->subjectObject = null;

		$this->gradesubjectId = null;

		$this->subjectId = null;

		$this->teacherId = null;

		$this->schoolyearId = null;

		$this->studentId = null; //@Man

		if ($this->_getParam('objectId')) {
			$this->objectId = $this->_getParam('objectId');
			$this->subjectObject = SubjectDBAccess::findSubjectFromId($this->objectId);
			$this->objectData = $this->DB_SUBJECT->getSubjectDataFromId($this->objectId);
		}

		if ($this->_getParam('educationType')) {
			$this->educationType = $this->_getParam('educationType');
		}

		if ($this->_getParam('gradeId')) {
			$this->gradeId = $this->_getParam('gradeId');
		}

		if ($this->_getParam('classId')) {
			$this->classId = $this->_getParam('classId');
		}

		if ($this->_getParam('subjectId')) {
			$this->subjectId = $this->_getParam('subjectId');
		}

		if ($this->_getParam('sourceModul')) {
			$this->sourceModul = $this->_getParam('sourceModul');
		}

		if ($this->_getParam('showButtons')) {
			$this->showButtons = $this->_getParam('showButtons');
		}

		if ($this->_getParam('gradesubjectId')) {
			$this->gradesubjectId = $this->_getParam('gradesubjectId');
		}

		if ($this->_getParam('teacherId')) {
			$this->teacherId = $this->_getParam('teacherId');
		}

		if ($this->_getParam('schoolyearId')) {
			$this->schoolyearId = $this->_getParam('schoolyearId');
		}

		if ($this->_getParam('studentId')) { //@Man
			$this->schoolyearId = $this->_getParam('studentId');
		}
	}

	public function indexAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");
	}

	public function allgeneralsubjectsAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->URL_SHOWITEM = $this->UTILES->buildURL('subject/showitem', array());
		$this->view->URL_ADD_ITEM = $this->UTILES->buildURL('subject/additem', array());
		$this->view->URL_CATEGORY_SHOWITEM = $this->UTILES->buildURL('assignmentcategory/showitem', array());
	}

	public function alltrainingsubjectsAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->TRAINING_SUBJECT = $this->UTILES->buildURL('subject/trainingsubject', array());
		$this->view->ADD_TRAINING_SUBJECT = $this->UTILES->buildURL('subject/addtrainingsubject', array());
	}

	public function addtrainingsubjectAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
	}

	public function additemAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
		$this->view->objectId = $this->objectId;
	}

	public function showitemAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->status = getObjectStatus($this->subjectObject);

		$this->view->objectId = $this->objectId;
		$this->view->objectData = $this->objectData;
		$this->view->db_subject = $this->DB_SUBJECT;
		$this->view->facette = $this->subjectObject;

		$this->_helper->viewRenderer("default/general/show");
	}

	public function showsecondaryAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->facette = $this->subjectObject;
		$this->view->objectId = $this->objectId;
		$this->view->objectData = $this->objectData;
		$this->view->db_subject = $this->DB_SUBJECT;
	}

	public function showprimaryAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->facette = $this->subjectObject;
		$this->view->objectId = $this->objectId;
		$this->view->objectData = $this->objectData;
		$this->view->db_subject = $this->DB_SUBJECT;
	}

	public function trainingsubjectAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");

		$this->view->facette = $this->subjectObject;
		$this->view->objectId = $this->objectId;
		$this->view->objectData = $this->objectData;
		$this->view->db_subject = $this->DB_SUBJECT;

		$this->_helper->viewRenderer("default/training/show");
	}

	public function byclassAction() {

		$this->view->classId = $this->classId;
		$this->view->URL_SHOW_SUBJECT_GRADE = $this->UTILES->buildURL('subject/showgradesubject', array());
	}

	public function gradesubjectAction() {

		//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_READ_RIGHT");
		$this->view->gradesubjectId = $this->gradesubjectId;
		$this->view->subjectId = $this->subjectId;
		$this->view->classId = $this->classId;
		$this->view->teacherId = $this->teacherId;
		$this->_helper->viewRenderer("/gradesubject/main");
	}

	public function subjectdisplaymainAction() {
		$this->_helper->viewRenderer("/display/main");
	}

	public function subjectdisplaymainadminAction() {
		$this->_helper->viewRenderer("/gradesubject/mainadmin");
	}

	public function subjectdisplayAction() {
		$this->_helper->viewRenderer("/display/show");
	}

	public function showgradesubjectAction() {

		$facette = GradeSubjectDBAccess::getGradeSubject($this->gradesubjectId, false, false, false);
		$this->view->gradesubjectId = $facette->ID;
		$this->view->subjectObject = $facette;
		$this->view->facette = $facette;
		$this->_helper->viewRenderer('default/general/bygrade');
	}

	public function jsonloadAction() {

		switch ($this->REQUEST->getPost('cmd')) {

			case "loadObject":
				$jsondata = $this->DB_SUBJECT->loadObject($this->REQUEST->getPost('objectId'));
				break;

			case "loadActionGradeSubject":
				$jsondata = $this->DB_SUBJECT->loadActionGradeSubject($this->REQUEST->getPost());
				break;

			case "loadSubjectByTeacher":
				$jsondata = $this->DB_SUBJECT->loadSubjectByTeacher($this->REQUEST->getPost());
				break;

			case "loadActionTeacherSubjects":
				$jsondata = $this->DB_SUBJECT->loadActionTeacherSubjects($this->REQUEST->getPost());
				break;

			case "loadSubjectGrade":
				$jsondata = $this->DB_GRADE_SUBJECT->loadSubjectGrade($this->REQUEST->getPost());
				break;

			case "jsonSubjectsByClass":
				$jsondata = $this->DB_SUBJECT->jsonSubjectsByClass($this->REQUEST->getPost());
				break;

			case "jsonAllSubjects":
				$jsondata = $this->DB_SUBJECT->allSubjects($this->REQUEST->getPost());
				break;

			case "jsonAllSubjectByClass":
				$jsondata = $this->DB_GRADE_SUBJECT->jsonAllSubjectByClass($this->REQUEST->getPost());
				break;

			case "jsonStudentSubjectCredit":
				$jsondata = $this->DB_GRADE_SUBJECT->jsonStudentSubjectCredit($this->REQUEST->getPost());
				break;

			case "jsonAllStudentBySelectiveSubject":
				$jsondata = $this->DB_GRADE_SUBJECT->jsonAllStudentBySelectiveSubject($this->REQUEST->getPost());
				break;

			case "jsonLearningQuality":
				$jsondata = $this->DB_GRADE_SUBJECT->jsonLearningQuality($this->REQUEST->getPost());
				break;

			case "jsonListStudentsBySubject":
				$this->DB_SUBJECT_STUDENT = SubjectStudentDBAccess::getInstance();
				$jsondata = $this->DB_SUBJECT_STUDENT->jsonListStudentsBySubject($this->REQUEST->getPost());
				break;

			case "jsonLoadTeachersBySubject":
				$jsondata = $this->DB_SUBJECT_TEACHER->jsonLoadTeachersBySubject($this->REQUEST->getPost());
				break;
		}

		if (isset($jsondata))
			$this->setJSON($jsondata);
	}

	public function jsonsaveAction() {

		switch ($this->REQUEST->getPost('cmd')) {

			case "createOnlyItem":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
				$jsondata = $this->DB_SUBJECT->createOnlyItem($this->REQUEST->getPost());
				break;

			case "updateObject":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
				$jsondata = $this->DB_SUBJECT->updateSubject($this->REQUEST->getPost());
				break;

			case "removeObject":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_REMOVE_RIGHT");
				$jsondata = $this->DB_SUBJECT->removeObject($this->REQUEST->getPost());
				break;

			case "jsonAddSubjectToGrade":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
				$jsondata = $this->DB_GRADE_SUBJECT->jsonAddSubjectToGrade($this->REQUEST->getPost());
				break;

			case "releaseObject":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EXECUTE_RIGHT");
				$jsondata = $this->DB_SUBJECT->releaseObject($this->REQUEST->getPost());
				break;

			case "updateSubjectGrade":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
				$jsondata = GradeSubjectDBAccess::updateSubjectGrade($this->REQUEST->getPost());
				break;

			case "removeSubjectGrade":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_REMOVE_RIGHT");
				$jsondata = GradeSubjectDBAccess::removeSubjectGrade($this->REQUEST->getPost("gradesubjectId"));
				break;

			case "actionTeacherSubject":
				$jsondata = $this->DB_SUBJECT->actionTeacherSubject($this->REQUEST->getPost());
				break;

			case "actionStudentSelectiveSubject":
				$jsondata = $this->DB_GRADE_SUBJECT->actionStudentSelectiveSubject($this->REQUEST->getPost());
				break;

			case "actionStudentExemptionSubject":
				$jsondata = $this->DB_GRADE_SUBJECT->actionStudentExemptionSubject($this->REQUEST->getPost());
				break;

			case "actionSubjectTeacherClass":
				//UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING_EDIT_RIGHT");
				$jsondata = $this->DB_SUBJECT_TEACHER->actionSubjectTeacherClass($this->REQUEST->getPost());
				break;

			case "copySubjectAssingmentToClass":
				$jsondata = GradeSubjectDBAccess::copySubjectAssingmentToClass($this->REQUEST->getPost('academicId'), $this->REQUEST->getPost('copyFrom')); //modyfied by @Sor Veasna
				break;

			case "actionStudentSubject":
				$jsondata = SubjectStudentDBAccess::actionStudentSubject($this->REQUEST->getPost());
				break;

				///@veasna
			case "actionPreRequisite2Subject":
				$jsondata = SubjectDBAccess::actionPreRequisite2Subject($this->REQUEST->getPost());
				break;

			case "actionPreRequisite2GradeSubject":
				$jsondata = GradeSubjectDBAccess::actionPreRequisite2GradeSubject($this->REQUEST->getPost());
				break;
				///
		}

		if (isset($jsondata))
			$this->setJSON($jsondata);
	}

	public function jsontreeAction() {

		switch ($this->REQUEST->getPost('cmd')) {

			case "treeAllSubjects":
				$jsondata = $this->DB_SUBJECT->treeAllSubjects($this->REQUEST->getPost());
				break;

			case "treeAllTrainingSubjects":
				$jsondata = $this->DB_SUBJECT->treeAllTrainingSubjects($this->REQUEST->getPost());
				break;

			case "treeGradeSubjectAssignments":
				$jsondata = $this->DB_GRADE_SUBJECT->treeGradeSubjectAssignments($this->REQUEST->getPost());
				break;

			case "treeSubjectsByGrade":
				$jsondata = $this->DB_GRADE_SUBJECT->treeSubjectsByGrade($this->REQUEST->getPost());
				break;

			case "treeSubjectsByClass":
				$jsondata = $this->DB_SUBJECT->treeSubjectsByClass($this->REQUEST->getPost());
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