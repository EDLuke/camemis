<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 20.11.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'models/app_school/BuildData.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/student/StudentAcademicDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/subject/GradeSubjectDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentPreRequisiteCourseDBAccess {

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

	public static function getSqlSubjectStudent($studentId,$subjectId){


		$SELECTION_A = array(
			"ID AS ID"
			, "TRANSFER AS TRANSFER"
			, "TRANSFER_DESCRIPTION AS TRANSFER_DESCRIPTION"
			, "CREATED_BY AS CREATED_BY"
			, "CREATED_DATE AS CREATED_DATE"
			, "CREDIT_STATUS AS CREDIT_STATUS"
			, "CREDIT_STATUS_DESCRIPTION AS CREDIT_STATUS_DESCRIPTION"
			, "CREDIT_STATUS_BY AS CREDIT_STATUS_BY"
			, "CREDIT_STATUS_DATED AS CREDIT_STATUS_DATED"
		);
		$SELECTION_B = array(
			"ID AS CLASS_ID"
			, "GUID AS GUID"
			, "CAMPUS_ID AS CAMPUS_ID"
			, "TITLE AS TITLE"
			, "EDUCATION_SYSTEM AS EDUCATION_SYSTEM"
			, "SCHOOL_YEAR AS SCHOOL_YEAR"
		);
		$SQL = self::dbAccess()->select();
		$SQL->from(array('A' => 't_student_schoolyear_subject'), $SELECTION_A);
		$SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS_ID=B.ID', $SELECTION_B);
		$SQL->where("A.STUDENT_ID = '".$studentId."'");
		$SQL->where("A.SUBJECT_ID = '".$subjectId."'");
		//error_log($SQL);
		$result = self::dbAccess()->fetchRow($SQL);
		return $result;

	}

	public static function jsonListStudentPreRequisiteCourse($params){
		$start = isset($params["start"]) ? (int) $params["start"] : "0";
		$limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

		$academicId=isset($params["academicId"]) ? (int) $params["academicId"] : "";
		$academicObject=AcademicDBAccess::findGradeFromId($academicId);
		$schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : "";
		$subjectId = isset($params["requisiteId"]) ? addText($params["requisiteId"]) : "";
		$studentId = isset($params["studentId"]) ? addText($params["studentId"]) : ""; //@Man

		$preReqParams["schoolyear"] = $schoolyear;
		$preReqParams["academicId"] = $academicObject->CAMPUS_ID;
		$preReqParams["requisiteId"] = $subjectId;

		$studentParams['academicId'] = $academicId;

		$result = GradeSubjectDBAccess::getPreRequisiteByGradeSubject($preReqParams);

		$data = array();
		$i = 0;
		switch (UserAuth::getUserType()) { //@Man 03.02.2014
			case "STUDENT":
				if ($result){
					foreach ($result as $value) {
						$subjectObject = SubjectDBAccess::findSubjectFromId($value);
						$object = self::getSqlSubjectStudent($studentId, $value);
						$data[$i]["ID"] = $value;
						$data[$i]["SUBJECT_NAME"] = $subjectObject->NAME;
						$data[$i]["STUDENT_NAME"] = "";
						$data[$i]["STUDIED"] = $object ? iconYESNO(1) : iconYESNO(0);
						$data[$i]["PASS"] = "---";
						$data[$i]["ACADEMIC"] = $object?$object->TITLE ? $object->TITLE :'---':iconYESNO(0);
						if($object){ 
							switch($object->CREDIT_STATUS){
								case '0':
									$data[$i]["STATUS"] = "Ongoing";
									break;
								case '1':
									$data[$i]["STATUS"] = "Incompleted";
									break;
								case '2':
									$data[$i]["STATUS"] = "Completed";
									break;
							}
						}else{
							$data[$i]["STATUS"] = "---";
						}

						$STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($studentId);
						$data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : " ";
						$data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
						$data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

						++$i;
					}
				}
				break;

			default:
				$enrolledStudents=StudentAcademicDBAccess::getSQLStudentEnrollment($studentParams);
				if ($result){
					foreach ($result as $value) {
						$subjectObject=SubjectDBAccess::findSubjectFromId($value);
						foreach ($enrolledStudents as $student){
							$object=self::getSqlSubjectStudent($student->STUDENT_ID, $value);
							$data[$i]["ID"] =$value;
							$data[$i]["SUBJECT_NAME"] =$subjectObject->NAME;
							$data[$i]["STUDENT_NAME"] = $student->LASTNAME . " " . $student->FIRSTNAME;
							$data[$i]["STUDIED"] = $object?iconYESNO(1):iconYESNO(0);
							$data[$i]["PASS"] = "---";
							$data[$i]["ACADEMIC"] = $object?$object->TITLE?$object->TITLE:'---':iconYESNO(0);
							if($object){ 
								switch($object->CREDIT_STATUS){
									case '0':
										$data[$i]["STATUS"] = "Ongoing";    
										break;
									case '1':
										$data[$i]["STATUS"] = "Incompleted";
										break;
									case '2':
										$data[$i]["STATUS"] = "Completed";
										break;
								}
							}else{
								$data[$i]["STATUS"] = "---";    
							}

							$STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($student->STUDENT_ID);
							$data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : " ";
							$data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
							$data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

							++$i;
						}
					}
				}
				break;
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
