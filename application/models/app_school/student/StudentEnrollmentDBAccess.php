<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.02.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'models/app_school/BuildData.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentEnrollmentDBAccess extends StudentDBAccess {

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

	/**
	* registrationRecord
	*/
	public function registrationRecord($params) {

		$transfer = isset($params["transfer"]) ? $params["transfer"] : "0";

		$studentObject = self::findStudentFromId($params["objectId"]);
		$objectClass = AcademicDBAccess::findGradeFromId($params["classId"]);
		$objectTraining = TrainingDBAccess::findTrainingFromId($params["trainingId"]);

		$name = addText($params["lastname"]) . ", " . addText($params["firstname"]);

		if (isset($params["firstname"]))
			$STUDENT_DATA['FIRSTNAME'] = addText($params["firstname"]);
		if (isset($params["lastname"]))
			$STUDENT_DATA['LASTNAME'] = addText($params["lastname"]);
		if (isset($params["gender"]))
			$STUDENT_DATA['GENDER'] = $params["gender"];
		if (isset($params["datebirth"]))
			$STUDENT_DATA['DATE_BIRTH'] = $params["datebirth"];
		if (isset($params["birth_place"]))
			$STUDENT_DATA['BIRTH_PLACE'] = addText($params["birth_place"]);
		if (isset($params["address"]))
			$STUDENT_DATA['ADDRESS'] = addText($params["address"]);
		if (isset($params["postcode_zipcode"]))
			$STUDENT_DATA['POSTCODE_ZIPCODE'] = addText($params["postcode_zipcode"]);
		if (isset($params["town_city"]))
			$STUDENT_DATA['TOWN_CITY'] = addText($params["town_city"]);
		if (isset($params["country"]))
			$STUDENT_DATA['COUNTRY'] = addText($params["country"]);
		if (isset($params["country_province"]))
			$STUDENT_DATA['COUNTRY_PROVINCE'] = addText($params["country_province"]);
		if (isset($params["phone"]))
			$STUDENT_DATA['PHONE'] = addText($params["phone"]);
		if (isset($params["email"]))
			$STUDENT_DATA['EMAIL'] = addText($params["email"]);
		if (isset($params["backgroundinfo"]))
			$STUDENT_DATA['BACKGROUND_INFO'] = addText($params["backgroundinfo"]);
		if (isset($params["parnentinfo"]))
			$STUDENT_DATA['PARENT_INFO'] = addText($params["parnentinfo"]);
		if (isset($params["medicalinfo"]))
			$STUDENT_DATA['MEDICAL_INFO'] = addText($params["medicalinfo"]);
		if (isset($params["academicType"]))
			$STUDENT_DATA['ACADEMIC_TYPE'] = addText($params["academicType"]);
		if (isset($params["studentschoolId"]))
			$STUDENT_DATA['STUDENT_SCHOOL_ID'] = addText($params["studentschoolId"]);
		if (isset($params["religion"]))
			$STUDENT_DATA['RELIGION'] = addText($params["religion"]);
		if (isset($params["ethnic"]))
			$STUDENT_DATA['ETHNIC'] = addText($params["ethnic"]);
		if (isset($params["nationality"]))
			$STUDENT_DATA['NATIONALITY'] = addText($params["nationality"]);

		$STUDENT_DATA['NAME'] = $name;

		if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
			$STUDENT_DATA['STATUS'] = 1;
			$ENROLLMENT_DATA['STATUS'] = 1;
		}

		$password = '123'; //@Math Man
		if (Zend_Registry::get('SCHOOL')->SET_DEFAULT_PASSWORD) {
			$STUDENT_DATA['PASSWORD'] = md5("123-D99A6718-9D2A-8538-8610-E048177BECD5");
		} else { //@Math Man
			$password = createpassword();
			$STUDENT_DATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
		}

		if (!$studentObject) {

			$STUDENT_DATA['ID'] = $params["objectId"];
			$STUDENT_DATA['CODE'] = createCode();
			$STUDENT_DATA['ROLE'] = "STUDENT";
			$STUDENT_DATA['LOGINNAME'] = createCode();
			$STUDENT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
			$STUDENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
			$STUDENT_DATA['TS'] = time();

			self::dbAccess()->insert('t_student', $STUDENT_DATA);

			//@Math Man
			if (isset($params["email"])) {
				$result = SchoolDBAccess::getSchool();
				$sendTo = addText($params["email"]);
				$recipientName = addText($params["lastname"]) . " " . addText($params["firstname"]);
				if ($result->DISPLAY_POSITION_LASTNAME == 1)
					$recipientName = addText($params["firstname"]) . " " . addText($params["lastname"]);
				$subject_email = $result->ACCOUNT_CREATE_SUBJECT;
				$content_email = $result->SALUTATION_EMAIL . ' ' . $recipientName . ',' . "\r\n";
				$content_email .= "\r\n" . $result->ACCOUNT_CREATE_NOTIFICATION."\r\n";
				$content_email .= SCHOOL . ': ' . $result->NAME . "\r\n";
				$content_email .= WEBSITE. ': http://' . $_SERVER['SERVER_NAME']. "\r\n";
				$content_email .= LOGINNAME . ': ' . $STUDENT_DATA['LOGINNAME'] . "\r\n";
				$content_email .= PASSWORD . ': ' . $password . "\r\n";
				$content_email .= "\r\n" . $result->SIGNATURE_EMAIL . "\r\n";
				$headers = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
				if($result->SMS_DISPLAY){
					$headers .= 'From:'.$result->SMS_DISPLAY. "\r\n" .
					'Reply-To:'.$result->SMS_DISPLAY . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
				}else{
					$headers .= 'From: noreply@camemis.com'. "\r\n" .
					'Reply-To: noreply@camemis.com' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
				}
				mail($sendTo, '=?utf-8?B?'.base64_encode($subject_email).'?=', $content_email, $headers);
			}
			/////////////////////////////////

			$newstudentObject = self::findStudentFromId($params["objectId"]);

			if ($objectTraining) {
				$STUDENT_TRAINING['STATUS'] = 1;
				$STUDENT_TRAINING['STUDENT'] = $newstudentObject->ID;
				$STUDENT_TRAINING['PROGRAM'] = $objectTraining->PROGRAM;
				$STUDENT_TRAINING['LEVEL'] = $objectTraining->LEVEL;
				$STUDENT_TRAINING['TERM'] = $objectTraining->TERM;
				$STUDENT_TRAINING['TRAINING'] = $objectTraining->ID;

				self::dbAccess()->insert('t_student_training', $STUDENT_TRAINING);
			}
            //@veasna
			if ($objectClass) {
                
                if($objectClass->EDUCATION_SYSTEM){
                    if ($newstudentObject) {
                        switch ($objectClass->OBJECT_TYPE) {
                    
                            case "SCHOOLYEAR":
                                $ENROLLMENT_DATA['CAMPUS_ID'] = $objectClass->CAMPUS_ID;
                                $ENROLLMENT_DATA['SCHOOLYEAR_ID'] = $objectClass->SCHOOL_YEAR;    
                                break;
                            case "SUBJECT":
                                $ENROLLMENT_DATA['CREDIT_ACADEMIC_ID'] = $objectClass->ID;
                                $ENROLLMENT_DATA['CAMPUS_ID'] = $objectClass->CAMPUS_ID;
                                $ENROLLMENT_DATA['SCHOOLYEAR_ID'] = $objectClass->SCHOOL_YEAR;   
                                $ENROLLMENT_DATA['SUBJECT_ID'] = $objectClass->SUBJECT_ID;
                                break;
                            case "CLASS":
                                $ENROLLMENT_DATA['CREDIT_ACADEMIC_ID'] = $objectClass->PARENT;
                                $ENROLLMENT_DATA['CAMPUS_ID'] = $objectClass->CAMPUS_ID;
                                $ENROLLMENT_DATA['SCHOOLYEAR_ID'] = $objectClass->SCHOOL_YEAR;   
                                $ENROLLMENT_DATA['SUBJECT_ID'] = $objectClass->SUBJECT_ID;
                                $ENROLLMENT_DATA['CLASS_ID'] = $objectClass->ID;
                                break;
                        }   

                        $ENROLLMENT_DATA['STUDENT_ID'] = $newstudentObject->ID;
                        $ENROLLMENT_DATA['TRANSFER'] = $transfer;
                        $ENROLLMENT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                        $ENROLLMENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                        self::dbAccess()->insert('t_student_schoolyear_subject', $ENROLLMENT_DATA); 
                    }    
                }else{
				    if ($newstudentObject) {

					    $ENROLLMENT_DATA['STUDENT'] = $newstudentObject->ID;
					    $ENROLLMENT_DATA['CAMPUS'] = $objectClass->CAMPUS_ID;
					    $ENROLLMENT_DATA['GRADE'] = $objectClass->GRADE_ID;

					    if ($objectClass->OBJECT_TYPE == "CLASS") {
						    $ENROLLMENT_DATA['CLASS'] = $objectClass->ID;
					    }

					    $ENROLLMENT_DATA['TRANSFER'] = $transfer;
					    $ENROLLMENT_DATA['SCHOOL_YEAR'] = $objectClass->SCHOOL_YEAR;
					    $ENROLLMENT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
					    $ENROLLMENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
					    self::dbAccess()->insert('t_student_schoolyear', $ENROLLMENT_DATA); 
				    }
                }
			} 

		}
		return array("success" => true);
	}
}

?>
