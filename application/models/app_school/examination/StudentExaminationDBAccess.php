<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 27.11.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentExaminationDBAccess extends ExaminationDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new StudentExaminationDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function jsonUnassignedStudentExamination($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findExamFromId($objectId);

        $data = array();

        if ($facette) {
            $result = StudentAcademicDBAccess::getQueryStudentEnrollment(
                            false
                            , $globalSearch
                            , $facette->SCHOOLYEAR_ID
                            , false
                            , $facette->GRADE_ID
            );

            $i = 0;
            if ($result) {
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $objectId
                                    , false
                    );

                    if (!$CHECK) {
                        $data[$i]["ID"] = $value->STUDENT_ID;
                        $data[$i]["CODE"] = $value->STUDENT_CODE;
                        $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                        $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                        $data[$i]["CURRENT_CLASS"] = setShowText($value->CLASS_NAME);
                        ++$i;
                    }
                }
            }
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

    public static function getQueryAssignedStudentExamination($params, $limitCount = false) {

        $globalSearch = isset($params["globalSearch"]) ? addText($params["globalSearch"]) : "";
        $examId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $roomId = isset($params["roomId"]) ? addText($params["roomId"]) : "";

        $SELECTION_A = array(
            "ID AS STUDENT_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
        );

        $SELECTION_C = array(
            "ID AS ROOM_ID"
            , "NAME AS ROOM_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_examination'), 'A.ID=B.STUDENT_ID', array("EXAM_CODE AS EXAM_CODE"));
        $SQL->joinLeft(array('C' => 't_room'), 'C.ID=B.ROOM_ID', $SELECTION_C);

        if ($examId)
            $SQL->where('B.EXAM_ID = ?', $examId);

        if ($roomId) {
            $SQL->where('B.ROOM_ID = ?', $roomId);
        }

        if ($globalSearch) {

            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        if ($limitCount) {
            $SQL->limit($limitCount);
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //List students in Exam...
    public static function jsonAssignedStudentExamination($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getQueryAssignedStudentExamination($params, false);

        $data = array();
        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["EXAM_CODE"] = $value->EXAM_CODE;
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);
                ++$i;
            }
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

    public static function jsonRemoveAllStudentsFromExamination($Id) {
        if ($Id)
            self::dbAccess()->delete('t_student_examination', array("EXAM_ID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

    public static function jsonActionRemoveStudentFromExamination($params) {

        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        if ($newValue)
            self::dbAccess()->delete('t_student_examination', array("STUDENT_ID='" . $studentId . "'", "EXAM_ID='" . $objectId . "'"));

        return array(
            "success" => true
        );
    }

    public static function jsonActionAllStudentsToExamination($Id) {

        $facette = self::findExamFromId($Id);

        if ($facette) {
            $result = StudentAcademicDBAccess::getQueryStudentEnrollment(
                            false
                            , false
                            , $facette->SCHOOLYEAR_ID
                            , false
                            , $facette->GRADE_ID
            );

            $i = 0;
            if ($result) {
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $Id
                                    , false
                    );

                    if (!$CHECK) {
                        $SAVEDATA['EXAM_CODE'] = createCodeExam();
                        $SAVEDATA['STUDENT_ID'] = $value->STUDENT_ID;
                        $SAVEDATA['EXAM_ID'] = $Id;
                        self::dbAccess()->insert('t_student_examination', $SAVEDATA);
                    }
                }
            }
        }

        return array(
            "success" => true
        );
    }

    ////$veasna
    public static function jsonActionChooseStudentTmpManually($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $facette = self::findExamFromId($objectId);

        $selectedCount = 0;

        if ($selectionIds && $facette) {
            $selectedStudents = explode(",", $selectionIds);
            if ($selectedStudents) {
                foreach ($selectedStudents as $studentId) {

                    $CHECK = self::checkStudentByExam(
                                    $studentId
                                    , $objectId
                                    , false
                    );

                    if (!$CHECK) {
                        $CHECK_EXAM_CODE = self::findStudentExamCode($studentId, '6', $facette->SCHOOLYEAR_ID);
                        if ($CHECK_EXAM_CODE) {
                            if ($CHECK_EXAM_CODE[0]->EXAM_CODE) {
                                $SAVEDATA['EXAM_CODE'] = $CHECK_EXAM_CODE[0]->EXAM_CODE;
                            }
                        } else {
                            $SAVEDATA['EXAM_CODE'] = createCodeExam();
                        }

                        //$SAVEDATA['EXAM_CODE'] = createCodeExam();
                        $SAVEDATA['STUDENT_ID'] = $studentId;
                        $SAVEDATA['EXAM_ID'] = $objectId;
                        self::dbAccess()->insert('t_student_examination', $SAVEDATA);
                        ++$selectedCount;
                    }
                }
            }
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    ///


    public static function jsonActionChooseStudentManually($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $facette = self::findExamFromId($objectId);

        $selectedCount = 0;

        if ($selectionIds && $facette) {
            $selectedStudents = explode(",", $selectionIds);
            if ($selectedStudents) {
                foreach ($selectedStudents as $studentId) {

                    $CHECK = self::checkStudentByExam(
                                    $studentId
                                    , $objectId
                                    , false
                    );

                    if (!$CHECK) {
                        $SAVEDATA['EXAM_CODE'] = createCodeExam();
                        $SAVEDATA['STUDENT_ID'] = $studentId;
                        $SAVEDATA['EXAM_ID'] = $objectId;
                        self::dbAccess()->insert('t_student_examination', $SAVEDATA);
                        ++$selectedCount;
                    }
                }
            }
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public static function getStudentExam($studentId, $examId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_examination", array("*"));
        $SQL->where("STUDENT_ID = ?", $studentId);
        $SQL->where("EXAM_ID = ?",$examId);
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkStudentByExam($studentId, $examId, $roomId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_examination", array("C" => "COUNT(*)"));

        if ($studentId)
            $SQL->where("STUDENT_ID = ?", $studentId);
        if ($examId)
            $SQL->where("EXAM_ID = ?",$examId);
        if ($roomId) {
            $SQL->where("ROOM_ID = ?",$roomId);
        }

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///////////////////////////////////////////////////
    //EXAM ROOM...
    ///////////////////////////////////////////////////
    public static function jsonAssignedStudentExamRoom($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findExamFromId($objectId);
        $parentObject = self::findExamParentFromId($objectId);

        $data = array();

        if ($parentObject && $facette) {

            $searchParams["globalSearch"] = $globalSearch;
            $searchParams["objectId"] = $parentObject->GUID;
            $searchParams["roomId"] = $facette->ROOM_ID;
            $result = self::getQueryAssignedStudentExamination($searchParams);

            $data = array();
            if ($result) {

                $i = 0;
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $parentObject->GUID
                                    , $facette->ROOM_ID
                    );

                    if ($CHECK) {
                        $data[$i]["ID"] = $value->STUDENT_ID;
                        $data[$i]["EXAM_CODE"] = $value->EXAM_CODE;
                        $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                        $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                        $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                        $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                        $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);
                        ++$i;
                    }
                }
            }
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

    public static function jsonActionRemoveStudentFromExamRoom($params) {

        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $facette = self::findExamFromId($objectId);
        $parentObject = self::findExamParentFromId($objectId);

        if ($newValue && $facette && $parentObject) {
            $SAVEDATA['ROOM_ID'] = "";
            $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
            $WHERE[] = "EXAM_ID = '" . $parentObject->GUID . "'";
            self::dbAccess()->update('t_student_examination', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    public static function jsonRemoveAllStudentsFromExamRoom($objectId) {

        $parentObject = self::findExamParentFromId($objectId);
        $facette = self::findExamFromId($objectId);
        $where = array();
        $where[] = "ROOM_ID ='" . $facette->ROOM_ID . "'";
        $where[] = "EXAM_ID ='" . $parentObject->GUID . "'";
        self::dbAccess()->update("t_student_examination", array('ROOM_ID' => '0'), $where);
        return array(
            "success" => true
        );
    }

    public static function jsonUnassignedStudentExamRoom($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findExamFromId($objectId);
        $parentObject = self::findExamParentFromId($objectId);

        $data = array();

        if ($parentObject && $facette) {

            $searchParams["globalSearch"] = $globalSearch;
            $searchParams["objectId"] = $parentObject->GUID;
            $searchParams["roomId"] = "";
            $searchParams["objectId"] = $parentObject->GUID;
            $result = self::getQueryAssignedStudentExamination(
                            $searchParams
                            , false
            );

            $data = array();
            if ($result) {

                $i = 0;
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $parentObject->GUID
                                    , $facette->ROOM_ID
                    );

                    if (!$CHECK) {
                        $data[$i]["ID"] = $value->STUDENT_ID;
                        $data[$i]["CODE"] = $value->STUDENT_CODE;
                        $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                        $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);

                        ++$i;
                    }
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function jsonActionChooseStudentIntoRoom($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";

        $facette = self::findExamFromId($objectId);
        $COUNT = $facette->COUNT ? $facette->COUNT : 1;

        $parentObject = self::findExamParentFromId($objectId);
        $facette = self::findExamFromId($objectId);

        $selectedCount = 1;

        if ($selectionIds && $facette) {

            $selectedCount = self::CountStudentExamRoom($parentObject->GUID, $facette->ROOM_ID); //@veasna

            $selectedStudents = explode(",", $selectionIds);
            if ($selectedStudents) {

                foreach ($selectedStudents as $studentId) {

                    $STUDENT_EXAM = self::getStudentExam($studentId, $parentObject->GUID);

                    if ($selectedCount < $COUNT) {
                        if ($STUDENT_EXAM->ROOM_ID == 0) {
                            $where = array();
                            $where['STUDENT_ID'] = "'". $studentId ."'";
                            $where['EXAM_ID']    = "'". $parentObject->GUID ."'";
                            self::dbAccess()->update("t_student_examination", array('ROOM_ID' => "'". $facette->ROOM_ID ."'"), $where);

                            ++$selectedCount;
                        }
                    }
                }
            }
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    ////
    public static function findStudentTmp($training = false, $campus) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_temp', array('*'));
        $SQL->where('TYPE = ?', 'ENROLL');
        $SQL->where('CAMPUS = ?', $campus);
        if ($training) {
            $SQL->where('TRAINING = 1');
        } else {
            $SQL->where('ACADEMIC_TYPE = 1');
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonUnassignedStudentTmpExamination($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicId = isset($params["academicId"]) ? $params["academicId"] : "";

        $facette = self::findExamFromId($objectId);

        $data = array();

        if ($facette) {
            //$result = self::findStudentTmp(false,$academicId);
            if ($academicId) {
                $result = self::findStudentEnrollByCampusId($academicId);
                $i = 0;
                if ($result) {
                    foreach ($result as $value) {

                        $CHECK = self::checkStudentByExam(
                                        $value->ID
                                        , $objectId
                                        , false
                        );

                        if (!$CHECK) {
                            $data[$i]["ID"] = $value->ID;
                            $data[$i]["CODE"] = $value->CODE;
                            $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                            $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);

                            ++$i;
                        }
                    }
                }
            }
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

    //new find student exam code
    public static function findStudentExamCode($studentID, $examType, $schoolyearID) {

        $SELECTION_A = array(
            "EXAM_CODE"
            , "ROOM_ID"
        );

        $SELECTION_B = array(
            "NAME AS EXAM_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_examination'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_examination'), 'A.EXAM_ID=B.GUID', $SELECTION_B);
        $SQL->where('B.EXAM_TYPE = ?', $examType);
        $SQL->where('B.SCHOOLYEAR_ID = ?', $schoolyearID);
        $SQL->where('A.STUDENT_ID = ?', $studentID);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //

    public static function jsonActionAllStudentsTmpToExamination($Id, $academicId) {

        $facette = self::findExamFromId($Id);

        if ($facette) {
            //$result = self::findStudentTmp(false,$academicId);
            $result = self::findStudentEnrollByCampusId($academicId);
            if ($result) {
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->ID
                                    , $Id
                                    , false
                    );

                    if (!$CHECK) {
                        $CHECK_EXAM_CODE = self::findStudentExamCode($value->ID, '6', $facette->SCHOOLYEAR_ID);
                        if ($CHECK_EXAM_CODE) {
                            if ($CHECK_EXAM_CODE[0]->EXAM_CODE) {
                                $SAVEDATA['EXAM_CODE'] = $CHECK_EXAM_CODE[0]->EXAM_CODE;
                            }
                        } else {
                            $SAVEDATA['EXAM_CODE'] = createCodeExam();
                        }

                        $SAVEDATA['STUDENT_ID'] = $value->ID;
                        $SAVEDATA['EXAM_ID'] = $Id;
                        self::dbAccess()->insert('t_student_examination', $SAVEDATA);
                    }
                }
            }
        }

        return array(
            "success" => true
        );
    }

    public static function getQueryAssignedStudentTmpExamination($params, $limitCount = false) {

        $globalSearch = isset($params["globalSearch"]) ? addText($params["globalSearch"]) : "";
        $examId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $roomId = isset($params["roomId"]) ? addText($params["roomId"]) : "";

        $SELECTION_A = array(
            "ID AS STUDENT_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "CONCAT(LASTNAME,' ',FIRSTNAME) AS FULL_NAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
            , "DATE_BIRTH"
            , "PHONE"
            , "EMAIL"
        );

        $SELECTION_C = array(
            "ID AS ROOM_ID"
            , "NAME AS ROOM_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_temp'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_examination'), 'A.ID=B.STUDENT_ID', array("EXAM_CODE AS EXAM_CODE", "ROOM_ID AS HAVE_ROOM", "POINTS"));
        $SQL->joinLeft(array('C' => 't_room'), 'C.ID=B.ROOM_ID', $SELECTION_C);

        $SQL->where('A.TYPE = ?', 'ENROLL');

        if ($examId)
            $SQL->where('B.EXAM_ID = ?', $examId);

        if ($roomId) {
            $SQL->where('B.ROOM_ID = ?', $roomId);
        }

        if ($globalSearch) {

            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        if ($limitCount) {
            $SQL->limit($limitCount);
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //List students in Exam...
    public static function jsonAssignedStudentTmpExamination($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getQueryAssignedStudentTmpExamination($params, false);

        $data = array();
        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["EXAM_CODE"] = $value->EXAM_CODE;
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);
                $data[$i]["POINTS"] = $value->POINTS;
                ++$i;
            }
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

    public static function jsonUnassignedStudentTmpExamRoom($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findExamFromId($objectId);
        $parentObject = self::findExamParentFromId($objectId);

        $data = array();

        if ($parentObject && $facette) {

            $searchParams["globalSearch"] = $globalSearch;
            $searchParams["objectId"] = $parentObject->GUID;
            $searchParams["roomId"] = "";
            $searchParams["objectId"] = $parentObject->GUID;
            $result = self::getQueryAssignedStudentTmpExamination(
                            $searchParams
                            , false
            );

            $data = array();
            if ($result) {

                $i = 0;
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $parentObject->GUID
                                    , $facette->ROOM_ID
                    );

                    if (!$CHECK) {
                        if ($value->HAVE_ROOM == 0) {
                            $data[$i]["ID"] = $value->STUDENT_ID;
                            $data[$i]["CODE"] = $value->STUDENT_CODE;
                            $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                            $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);

                            ++$i;
                        }
                    }
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function jsonAssignedStudentTmpExamRoom($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findExamFromId($objectId);
        $parentObject = self::findExamParentFromId($objectId);

        $data = array();

        if ($parentObject && $facette) {

            $searchParams["globalSearch"] = $globalSearch;
            $searchParams["objectId"] = $parentObject->GUID;
            $searchParams["roomId"] = $facette->ROOM_ID;
            $result = self::getQueryAssignedStudentTmpExamination($searchParams);

            $data = array();
            if ($result) {

                $i = 0;
                foreach ($result as $value) {

                    $CHECK = self::checkStudentByExam(
                                    $value->STUDENT_ID
                                    , $parentObject->GUID
                                    , $facette->ROOM_ID
                    );

                    if ($CHECK) {
                        $data[$i]["ID"] = $value->STUDENT_ID;
                        $data[$i]["EXAM_CODE"] = $value->EXAM_CODE;
                        $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                        $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                        $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                        $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                        $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);

                        $data[$i]["POINTS"] = ($value->POINTS) ? $value->POINTS : '0';
                        ++$i;
                    }
                }
            }
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

    public static function CountStudentExamRoom($examID, $roomId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_examination', array("C" => "COUNT(*)"));
        $SQL->where('EXAM_ID = ?', $examID);
        $SQL->where('ROOM_ID = ?', $roomId);
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function findStudentEnrollByCampusId($campusId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_temp', array("*"));
        $SQL->where('TYPE = ?', 'ENROLL');
        $SQL->where('CAMPUS = ?', $campusId);
        //error_log($SQL->__toString());

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findStudentEnrollBigerorEqualScore($averag, $campusId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_temp', array("*"));
        $SQL->where('TYPE = ?', 'ENROLL');
        $SQL->where('CAMPUS = ?', $campusId);
        $SQL->where('ENROLL_AVG >= ?', $averag);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findStudentEnrollSmallerScore($averag, $campusId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_temp', array("*"));
        $SQL->where('TYPE = ?', 'ENROLL');
        $SQL->where('CAMPUS = ?', $campusId);
        $SQL->where('ENROLL_AVG < ?', $averag);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    ///
}

?>