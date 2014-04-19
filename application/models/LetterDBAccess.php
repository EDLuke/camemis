<?

///////////////////////////////////////////////////////////
// @Math Man Web Application Developer
// Date: 21.02.2014
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/CAMEMISUploadDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentSearchDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentStatusDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffStatusDBAccess.php";
require_once setUserLoacalization();

class LetterDBAccess {

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

	public static function dbSelectAccess() {
		return self::dbAccess()->select();
	}

	public function actionLetter($params) {
		$objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
		$name = isset($params["NAME"]) ? addText($params["NAME"]) : "";
		$number = isset($params["NUMBER"]) ? addText($params["NUMBER"]) : "";
        $from = isset($params["FROM_TEXT"]) ? addText($params["FROM_TEXT"]) : "";
        $to = isset($params["TO_TEXT"]) ? addText($params["TO_TEXT"]) : "";
		$type = isset($params["type"]) ? addText($params["type"]) : "";
		$letterDate = isset($params["DATE"]) ? setDate2DB($params["DATE"]) : "";
		$letterType = isset($params["LETTER_TYPE"]) ? $params["LETTER_TYPE"] : "";
		$comment = isset($params["COMMENT"]) ? $params["COMMENT"] : "";

		//$CHECK_FUTURE_DATE = timeDifference($letterDate, getCurrentDBDate());

		$errors = array();

		if ($number)
			$SAVEDATA["NUMBER"] = addText($number);

		if ($from)
            $SAVEDATA["FROM_ID"] = addText($from);
    
		if ($name)
			$SAVEDATA["SUBJECT"] = addText($name);

		if ($letterType)
			$SAVEDATA["lETTER_TYPE"] = $letterType;

		if ($letterDate)
			$SAVEDATA["DATE"] = $letterDate;

		if ($comment)
            $SAVEDATA["COMMENT"] = addText($comment);
            
        if ($number)
            $SAVEDATA["NUMBER"] = addText($number);
            
        if ($to)
            $SAVEDATA["SEND_TO"] = addText($to);
            
        if ($type)
			$SAVEDATA["TYPE"] = addText($type);

		if ($objectId != "new") {
			/*$CHECK_NUMBER = $this->checkLetterNumber($number, $objectId);
			if (!$CHECK_NUMBER)
				$errors["NUMBER"] = LETTER_NUMBER_EXISTS;*/

			$SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
			$SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->ID;
			$WHERE = "ID = " . $objectId;
			//if ($CHECK_FUTURE_DATE && $CHECK_NUMBER) {
				self::dbAccess()->update('t_letter', $SAVEDATA, $WHERE);
			//}
		} else {
			$SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
			$SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
			//if ($CHECK_FUTURE_DATE) {
				self::dbAccess()->insert('t_letter', $SAVEDATA);
				$objectId = self::dbAccess()->lastInsertId();
			//}
		}

		/*if (!$CHECK_FUTURE_DATE) {
			$errors["DATE"] = CHECK_DATE_PAST;
		}*/

		if ($errors) {
			return array(
				"success" => false
				, "errors" => $errors
				, "objectId" => $objectId
			);
		} else {
			return array(
				"success" => true
				, "errors" => $errors
				, "objectId" => $objectId
			);
		}
	}

	/**
	* Save recipients list
	* 
	* @param mixed $params
	* @return array
	*/
	public function addPersonToLetter($params) {
		$objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
		$selectionIds = isset($params["selectionIds"]) ? $params["selectionIds"] : "";
		$selectedCount = 0;

		if ($selectionIds) {
			$selectedStudents = explode(",", $selectionIds);
			if ($selectedStudents) {
				foreach ($selectedStudents as $studentId) {
					$SAVEDATA['LETTER_ID'] = $objectId;
					$SAVEDATA['PERSON_ID'] = $studentId;

					self::dbAccess()->insert('t_letter_person', $SAVEDATA);
					++$selectedCount;
				}
			}
		}

		return array(
			"success" => true
			, 'selectedCount' => $selectedCount
		);
	}

	/**
	* Set active or deactive item
	* 
	* @param integer $Id
	* @return array
	*/
	public function releaseLetter($Id) {

		$facette = self::findLetterFromId($Id);

		$status = $facette->STATUS;

		switch ($status) {
			case 0:
				$newStatus = 1;
				$SAVEDATA["STATUS"] = 1;
				//$SAVEDATA["ENABLED_DATE"] = getCurrentDBDateTime();
				//$SAVEDATA["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
				break;
			case 1:
				$newStatus = 0;
				$SAVEDATA["STATUS"] = 0;
				//$SAVEDATA["DISABLED_DATE"] = getCurrentDBDateTime();
				//$SAVEDATA["DISABLED_BY"] = Zend_Registry::get('USER')->CODE;
				break;
		}

		$WHERE = "ID = " . $Id;

		self::dbAccess()->update('t_letter', $SAVEDATA, $WHERE);

		return array("success" => true, "status" => $newStatus);
	}

	/**
	* Remove an item from letter
	* 
	* @param integer $Id
	* @return array
	*/
	public function removeLetter($Id) {
		if ($Id) {
			self::dbAccess()->delete('t_letter', array("ID = ?" => $Id));

			//remove recipient person
			self::dbAccess()->delete('t_letter_person', array("LETTER_ID = ?" => $Id));

			//remove attached file
			CAMEMISUploadDBAccess::deleteFileFromObjectId($Id, false, "LETTER");
		}

		return array("success" => true);
	}

	/**
	* Remove recipient person
	* 
	* @param mixed $params
	* @return array
	*/
	public function removePersonLetter($params) {
		$letterId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
		$personId = isset($params["removeId"]) ? addText($params["removeId"]) : "";

		self::dbAccess()->delete('t_letter_person', array("LETTER_ID = ?" => $letterId, "PERSON_ID = ?" => "{$personId}"));

		return array(
			"success" => true
		);
	}

	/**
	* Display letter as form updated
	* 
	* @param mixed $params
	* @return mixed array
	*/
	public function loadLetter($Id) {

		$result = $this->getLetterFromId($Id);

		if ($result) {
			$data = array(
				"success" => true
				, "data" => $result
			);
		} else {
			$data = array(
				"success" => true
				, "data" => array()
			);
		}
		return $data;
	}

	/**
	* Select all recipient persons
	* 
	* @param mixed $params
	*/
	public static function sqlPersonLetter($params) {
		$objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
		$personType = isset($params["personType"]) ? addText($params["personType"]) : "";
		if ($personType == "staff")
			$t_person = "t_staff";
		else
			$t_person = "t_student";

		$SQL = self::dbAccess()->select();
		$SQL->from(array("LP" => "t_letter_person"), array('*'));
		$SQL->join(array("S" => "{$t_person}"), 'S.ID = LP.PERSON_ID', array('*'));
		$SQL->where("LP.LETTER_ID = ?", $objectId);
		//error_log($SQL);
		return self::dbAccess()->fetchAll($SQL);
	}

	/**
	* Render recipient person
	* 
	* @param mixed $params
	* @return mixed array
	*/
	public function loadPersonLetter($params) {
		$start = isset($params["start"]) ? $params["start"] : "0";
		$limit = isset($params["limit"]) ? $params["limit"] : "50";

		$data = array();
		$i = 0;

		/////////////////////////////////
		// Recipient as Staff
		/////////////////////////////////
		$params["personType"] = "staff";
		$result_staff = self::sqlPersonLetter($params);

		if ($result_staff) {
			foreach ($result_staff as $value) {
				$data[$i]["ID"] = $value->ID;
				$data[$i]["CODE"] = setShowText($value->CODE);
				$data[$i]["GENDER"] = getGenderName($value->GENDER);

				if (!SchoolDBAccess::displayPersonNameInGrid()) {
					$data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
				} else {
					$data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
				}

				$STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->ID);
				$data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
				$data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
				$data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

				$i++;
			}
		}

		/////////////////////////////////
		// Recipient as Student
		/////////////////////////////////
		$params["personType"] = "student";
		$result_student = self::sqlPersonLetter($params);

		if ($result_student) {
			foreach ($result_student as $value) {
				$data[$i]["ID"] = $value->ID;
				$data[$i]["CODE"] = setShowText($value->CODE);
				$data[$i]["GENDER"] = getGenderName($value->GENDER);

				if (!SchoolDBAccess::displayPersonNameInGrid()) {
					$data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
				} else {
					$data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
				}

				$STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
				$data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
				$data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
				$data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

				$i++;
			}
		}

		$record = array();
		for ($i = $start; $i < $start + $limit; $i++) {
			if (isset($data[$i]))
				$record[] = $data[$i];
		}

		return array(
			"success" => true
			, "totalCount" => sizeof($data)
			, "rows" => $record
		);
	}

	/**
	* Check duplicated letter number
	* 
	* @param string $number
	* @param integer $Id
	* @return boolean
	*/
	public function checkLetterNumber($number, $Id) {
		$SQL = self::dbAccess()->select();
		$SQL->from("t_letter", array("ID", "NUMBER"));
		$SQL->where("NUMBER = ?", "{$number}");
		$result = self::dbAccess()->fetchRow($SQL);

		if ($result) {
			if ($Id == $result->ID)
				return true;
			else
				return false;
		} else {
			return true;
		}
	}

	/**
	* Render letter by Id
	* 
	* @param integer $Id
	* @return array
	*/
	public function getLetterFromId($Id) {
		$result = self::findLetterFromId($Id);

		$data = array();

		if ($result) {

			$data["ID"] = $result->ID;
			$data["STATUS"] = $result->STATUS;
			$data["NAME"] = $result->SUBJECT;
			$data["NUMBER"] = $result->NUMBER;
			$data["DATE"] = getShowDate($result->DATE);
			$data["LETTER_TYPE"] = $result->LETTER_TYPE;
            $data["COMMENT"] = $result->COMMENT;

            /*$from = StaffDBAccess::findStaffFromId($result->FROM_ID);

                if ($from)
                    $data["FROM_TEXT_ID"] = setShowText($from->ID);
                    if (!SchoolDBAccess::displayPersonNameInGrid())
                        $data["FROM_TEXT"] = setShowText($from->LASTNAME) . " " . setShowText($from->FIRSTNAME);
                    else
                        $data["FROM_TEXT"] = setShowText($from->FIRSTNAME) . " " . setShowText($from->LASTNAME);*/
                        
            
            $data["FROM_TEXT"] = setShowText($result->FROM_ID); //@Visal
            $data["TO_TEXT"] = setShowText($result->SEND_TO); //@Visal
            
			
            
			$data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
			$data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
			//$data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
			//$data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

			$data["CREATED_BY"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
			$data["MODIFY_BY"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
			//$data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
			//$data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
		}

		return $data;
	}

	/**
	* Get single letter by Id
	* 
	* @param integer $Id
	* @return array dataset
	*/
	public static function findLetterFromId($Id) {
		$SQL = self::dbAccess()->select()
		->from(array("L" => "t_letter"), array('*'))
        ->joinLeft(array('C' => 't_staff'), 'L.CREATED_BY = C.ID', array('C.ID AS STAFF_C_ID', 'C.FIRSTNAME AS FIRSTNAME', 'C.CODE AS CODE','C.LASTNAME AS LASTNAME'))
        ->joinLeft(array('M' => 't_staff'), 'L.MODIFY_BY = M.ID', array('M.ID AS STAFF_M_ID', 'M.FIRSTNAME AS FIRSTNAME', 'M.CODE AS CODE','M.LASTNAME AS LASTNAME'))
		->where("L.ID = ?", $Id);
        //error_log($SQL);
		return self::dbAccess()->fetchRow($SQL);
	}

	/**
	* Retrieve all letter by params
	* 
	* @param mixed $params
	* @return array dataset
	*/
	public function getAllLetterQuery($params) {
		//$globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

		$code = isset($params["code"]) ? addText($params["code"]) : "";
		$firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
		$lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
		$name = isset($params["name"]) ? addText($params["name"]) : "";
		$number = isset($params["number"]) ? addText($params["number"]) : "";
		$type = isset($params["type"]) ? addText($params["type"]) : "";
		$startDate = isset($params["startDate"]) ? $params["startDate"] : "";
		$endDate = isset($params["endDate"]) ? $params["endDate"] : "";
		/*$personId = isset($params["personId"]) ? $params["personId"] : "";
		$personType = isset($params["personType"]) ? $params["personType"] : "";
		if ($personType) {
			switch ($personType) {
				case "staff":
					break;
					$t_person = "t_staff";
				default:
					$t_person = "t_student";
					break;
			}
		}

		$SELECT_LETTER = array(
			"ID" => "L.ID"
			, "NUMBER" => "L.NUMBER"
			, "SUBJECT" => "L.SUBJECT"
			, "DATE" => "L.DATE"
            , "FROM_ID" => "L.FROM_ID"
			, "SEND_TO" => "L.SEND_TO"
			, "LETTER_TYPE" => "L.TYPE"
			, "COMMENT" => "L.COMMENT"
			, "CREATED_BY" => "L.CREATED_BY"
			, "CREATED_DATE" => "L.CREATED_DATE"
		);

		$SELECT_STAFF = array(
			"CODE" => "S.CODE"
			, "FIRSTNAME" => "S.FIRSTNAME"
			, "LASTNAME" => "S.LASTNAME"
			, "FIRSTNAME_LATIN" => "S.FIRSTNAME_LATIN"
			, "LASTNAME_LATIN" => "S.LASTNAME_LATIN"
		);*/

		/*$SELECT_CAMEMIS_TYPE = array(
			"OBJECT_TYPE" => "C.OBJECT_TYPE"
            ,"NAME" => "C.NAME"
		);*/

		$SQL = self::dbAccess()->select();
		$SQL->distinct();
		$SQL->from(array("L" => "t_letter"), array('*'));
		//switch (UserAuth::getUserType()) {
			/*case "SUPERADMIN":
			case "ADMIN":
				$SQL->join(array("S" => "t_staff"), "S.ID = L.FROM_ID", $SELECT_STAFF);
				break;*/
			/*case "TEACHER":
			case "STUDENT":
				//$SQL->join(array("LP" => "t_letter_person"), "LP.LETTER_ID = L.ID", array());
				$SQL->join(array("S" => "{$t_person}"), "S.ID = LP.PERSON_ID", $SELECT_STAFF);
			default:
				break;
		}*/
        $SQL->joinLeft(array("B" => "t_camemis_type"), "B.ID = L.LETTER_TYPE", array('B.ID AS CAME_ID', 'B.NAME AS LETTER_NAME'));
        $SQL->joinLeft(array("C" => "t_staff"), "C.ID = L.CREATED_BY", array('C.ID AS STAFF_ID', 'C.FIRSTNAME AS FIRSTNAME', 'C.LASTNAME AS LASTNAME'));
		//$SQL->joinLeft(array("S" => "t_staff"), "S.ID= L.CODE", array('*'));
		/*$SQL->where("C.OBJECT_TYPE = ?", "LETTER_TYPE");*/

		if ($code)
			$SQL->where("C.CODE = LIKE ?", "{$code}%");

		if ($firstname)
			$SQL->where("C.FIRSTNAME LIKE ?", "{$firstname}%");

		if ($lastname)
			$SQL->where("C.LASTNAME LIKE ?", "{$lastname}%");
        
		if ($name)
			$SQL->where("L.SUBJECT LIKE ?", "{$name}%");

		if ($number)
			$SQL->where("L.NUMBER LIKE ?", "{$number}%");

		if ($type)
			$SQL->where("L.LETTER_TYPE = ?", $type);

		if ($startDate && $endDate) {
			$SQL->where("L.DATE >= ?", $startDate);
			$SQL->where("L.DATE <= ?", $endDate);
		}

		//if ($personId)
			//$SQL->where("LP.PERSON_ID = ?", "{$personId}");

		/*if ($globalSearch) {
			$SQL->where("L.NAME LIKE ?", "{$globalSearch}%");
			$SQL->where("L.FROM_ID LIKE ?", "{$globalSearch}%");
			$SQL->orWhere("C.FIRSTNAME LIKE ?", "{$globalSearch}%");
			$SQL->orWhere("C.LASTNAME LIKE ?", "{$globalSearch}%");
			$SQL->orWhere("C.CODE LIKE ?", "{$globalSearch}%");
		}*/

		/*switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
			default:
				$SQL->order("L.NAME ASC");
				break;
			case "1":
				$SQL->order("S.LASTNAME DESC");
				break;
			case "2":
				$SQL->order("S.FIRSTNAME DESC");
				break;
		}*/
		//error_log($SQL);
		return self::dbAccess()->fetchAll($SQL);
	}

	/**
	* Display letter
	* 
	* @param mixed $params
	* @return array
	*/
	public function showListByLetter($params, $isJson = true) {
		$start = $params["start"] ? $params["start"] : "0";
		$limit = $params["limit"] ? $params["limit"] : "50";

		$result = $this->getAllLetterQuery($params);

		$data = array();
		$i = 0;

		if ($result) {
			foreach ($result as $value) {
				$data[$i]["ID"] = $value->ID;
                /*switch (UserAuth::getUserType()) {
                case "TEACHER":
                case "STUDENT":
				    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                    $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                    $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                    $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                    default:
                    break;
                }*/
				
				/*$from = StaffStatusDBAccess::findStaffFromId($value->FROM_ID);

      
                    if ($from)
                        if (!SchoolDBAccess::displayPersonNameInGrid())
                            $data["FROM"] = setShowText($from->LASTNAME) . " " . setShowText($from->FIRSTNAME);
                        else
                            $data["FROM"] = setShowText($from->FIRSTNAME) . " " . setShowText($from->LASTNAME);*/
                $data[$i]["FROM"] = $value->FROM_ID;
                $data[$i]["TYPE"] = $value->TYPE;
                $data[$i]["TO"] = $value->SEND_TO;
				$data[$i]["NUMBER"] = $value->NUMBER;
				$data[$i]["SUBJECT"] = $value->SUBJECT;
				$data[$i]["LETTER_TYPE"] = $value->LETTER_NAME;
				$data[$i]["DATE"] = $value->DATE;
                $data[$i]["CREATED_BY"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
				$data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
				$i++;
			}
		}

		$record = array();
		for ($i = $start; $i < $start + $limit; $i++) {
			if (isset($data[$i]))
				$record[] = $data[$i];
		}


		if ($isJson) {
			return array(
				"success" => true
				, "totalCount" => sizeof($data)
				, "rows" => $record
			);
		} else {
			return $data;
		}
	}

	/**
	* Get all students or staffs -> render in grid
	* 
	* @param mixed $params
	*/
	public function showAllStudentsOrStaffs($params) {
		$start = isset($params["start"]) ? $params["start"] : "0";
		$limit = isset($params["limit"]) ? $params["limit"] : "50";
		$objectId = isset($params["objectId"]) ? $params["objectId"] : "";
		$personType = isset($params["personType"]) ? addText($params["personType"]) : "";
		$type = isset($params["type"]) ? addText($params["type"]) : "";

		$data = array();
		$i = 0;

		if ($personType == "staff") {
			$DB_STAFF = StaffDBAccess::getInstance();
			$result = $DB_STAFF->queryAllStaffs($params);
			$chooseId = "STAFF_ID";
		} else {
			$result = StudentSearchDBAccess::queryAllStudents($params);
			$chooseId = "STUDENT_ID";
		}

		if ($result) {
			foreach ($result as $value) {
				//$personRecipients = self::checkAssignedPersonLetter($value->ID, $objectId, $type);
				//if (!$personRecipients) {
					$data[$i]['ID'] = $value->ID;
					$data[$i]['CODE'] = $value->CODE;
					if (!SchoolDBAccess::displayPersonNameInGrid()) {
						$data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
					}else{
						$data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
					}

					$data[$i]["GENDER"] = getGenderName($value->GENDER);
					$data[$i]["DATE_BIRTH"] = $value->DATE_BIRTH;
					$data[$i]["STATUS"] = $value->STATUS;

					if ($personType == "staff") {
						$STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->$chooseId);
					} else {
						$STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->$chooseId);
					}

					$data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
					$data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
					$data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
					$i++;
				//}
			}
		}

		$record = array();
		for ($i = $start; $i < $start + $limit; $i++) {
			if (isset($data[$i]))
				$record[] = $data[$i];
		}

		return array(
			"success" => true
			, "totalCount" => sizeof($data)
			, "rows" => $record
		);
	}

	/**
	* Check if person is already a recipient of a letter
	* 
	* @param string $personId
	* @param string $letterId
	* @param string $type
	* @return integer
	*/
	public static function checkAssignedPersonLetter($personId, $letterId, $type) {
		$SQL = self::dbAccess()->select();
		if ($type == "sender") {
			$SQL->from("t_letter", array("C" => "COUNT(*)"));
			$SQL->where("FROM_ID = ?", "{$personId}");
		} else {
			$SQL->from("t_letter_person", array("C" => "COUNT(*)"));
			$SQL->where("LETTER_ID = ?", $letterId);
			$SQL->where("PERSON_ID = ?", "{$personId}");
		}
		//error_log($SQL);
		$result = self::dbAccess()->fetchRow($SQL);
		return $result ? $result->C : 0;
	}

}

?>