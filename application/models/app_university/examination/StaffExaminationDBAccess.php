<?php

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 27.11.2012
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once 'models/app_university/staff/StaffDBAccess.php';
    require_once setUserLoacalization();

    class StaffExaminationDBAccess extends ExaminationDBAccess{

        static function getInstance() {
            static $me;
            if ($me == null) {
                $me = new StaffExaminationDBAccess();
            }
            return $me;
        }

        public static function dbAccess() {
            return Zend_Registry::get('DB_ACCESS');
        }

        public static function dbSelectAccess() {
            return self::dbAccess()->select();
        }

        public static function getStaffExam($staffId, $examId){

            $SQL = self::dbAccess()->select();
            $SQL->from("t_teacher_examination", array("*"));
            $SQL->where("STAFF_ID = ?",$staffId);
            $SQL->where("EXAM_ID = ?",$examId);
            //error_log($SQL);
            return self::dbAccess()->fetchRow($SQL);
        }

        public static function getQueryAssignedStaffExamination($params, $limitCount = false){

            $globalSearch = isset($params["globalSearch"])?addText($params["globalSearch"]):"";
            $examId = isset($params["objectId"])?addText($params["objectId"]):"";
            $roomId = isset($params["roomId"])?addText($params["roomId"]):"";

            $SELECTION_A = array(
                "ID AS STAFF_ID"
                , "CODE AS STAFF_CODE"
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
            $SQL->from(array('A' => 't_staff'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_teacher_examination'), 'A.ID=B.STAFF_ID', array());
            $SQL->joinLeft(array('C' => 't_room'), 'C.ID=B.ROOM_ID', $SELECTION_C);

            if ($examId)
                $SQL->where('B.EXAM_ID = ?', $examId);

            if ($roomId){
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

            if($limitCount)  {
                $SQL->limit($limitCount);
            }

            //error_log($SQL->__toString());
            return self::dbAccess()->fetchAll($SQL);
        }

        public static function jsonAssignedStaffExamination($params,$isJason=true){

            $data = array();

            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $globalSearch = isset($params["query"])?addText($params["query"]):"";
            $searchParams["isTutor"] = true;
            $searchParams["query"] = $globalSearch;

            $result = self::getQueryAssignedStaffExamination($params, false);

            $i = 0;
            if ($result){
                foreach ($result as $value) {

                    $data[$i]["ID"] = $value->STAFF_ID;
                    $data[$i]["CODE"] = $value->STAFF_CODE;
                    $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                    $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                    $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);

                    ++$i;
                }
            }

            $a = array();
            for ($i = $start; $i < $start + $limit; $i++) {
                if (isset($data[$i]))
                    $a[] = $data[$i];
            }
            if($isJason){
                return array(
                    "success" => true
                    , "totalCount" => sizeof($data)
                    , "rows" => $a
                );
            }else{
                return $data;    
            }
        }

        public static function jsonUnassignedStaffExamination($params){

            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $globalSearch = isset($params["query"])?addText($params["query"]):"";
            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";

            $DB_STAFF = StaffDBAccess::getInstance();
            $searchParams["isTutor"] = true;
            $searchParams["query"] = $globalSearch;

            $data = array();
            $result = $DB_STAFF->queryAllStaffs($searchParams,false,false);

            $i = 0;
            if ($result){
                foreach ($result as $value) {

                    $STAFF_EXAM_OBJECT = self::getStaffExam($value->ID, $objectId);

                    if(!$STAFF_EXAM_OBJECT){

                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["CODE"] = $value->CODE;
                        $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                        $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);

                        ++$i;
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

        public static function jsonActionChooseStaffToExamination($params){

            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";
            $selectionIds = isset($params["selectionIds"])?addText($params["selectionIds"]):"";
            $facette = self::findExamFromId($objectId);

            $selectedCount = 0;

            if($selectionIds && $facette){
                $selectedStudents = explode(",", $selectionIds);
                if($selectedStudents){
                    foreach ($selectedStudents as $staffId) {

                        $STAFF_EXAM_OBJECT = self::getStaffExam($staffId, $objectId);

                        if(!$STAFF_EXAM_OBJECT ){

                            $SAVEDATA['STAFF_ID'] = $staffId;
                            $SAVEDATA['EXAM_ID'] = $objectId;

                            self::dbAccess()->insert('t_teacher_examination', $SAVEDATA);  

                            ++$selectedCount;
                        }
                    }
                }
            }

            return array("success" => true, 'selectedCount' => $selectedCount);
        }

        public static function jsonActionRemoveStaffFromExamination($params){

            $staffId = isset($params["id"])?addText($params["id"]):"";
            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";
            $newValue = isset($params["newValue"])?addText($params["newValue"]):"";
            if ($newValue) self::dbAccess()->delete('t_teacher_examination', array("STAFF_ID='" . $staffId . "'", "EXAM_ID='" . $objectId . "'"));

            return array(
                "success" => true
            );
        }

        public static function checkStaffByExam($staffId, $examId, $roomId) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_teacher_examination", array("C" => "COUNT(*)"));

            if ($staffId) $SQL->where("STAFF_ID = ?",$staffId);
            if ($examId) $SQL->where("EXAM_ID = ?",$examId);
            if ($roomId){
                $SQL->where("ROOM_ID = ?",$roomId);
            }else{
                $SQL->where("ROOM_ID = '0'");
            }
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public static function jsonUnassignedStaffExamRoom($params){

            $globalSearch = isset($params["query"])?addText($params["query"]):"";
            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";

            $facette = self::findExamFromId($objectId);
            $parentObject = self::findExamParentFromId($objectId);

            $data = array();

            if($parentObject && $facette){

                $searchParams["globalSearch"] = $globalSearch;
                $searchParams["objectId"] = $parentObject->GUID; 
                $searchParams["roomId"] = ""; 
                $searchParams["objectId"] = $parentObject->GUID; 
                $result = self::getQueryAssignedStaffExamination(
                    $searchParams
                );

                //print_r($result);
                $data = array();
                if ($result){

                    $i=0;
                    foreach ($result as $value) {

                        $STAFF_EXAM = self::getStaffExam($value->STAFF_ID, $parentObject->GUID);

                        if($STAFF_EXAM->ROOM_ID == 0){

                            $data[$i]["ID"] = $value->STAFF_ID;
                            $data[$i]["CODE"] = $value->STAFF_CODE;
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

        public static function jsonActionChooseStaffIntoRoom($params){

            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";
            $selectionIds = isset($params["selectionIds"])?addText($params["selectionIds"]):"";

            //error_log("ExamId: ".$objectId);

            $parentObject = self::findExamParentFromId($objectId);
            $facette = self::findExamFromId($objectId);

            $selectedCount = 0;

            if($selectionIds && $facette){
                $selectedStaffs = explode(",", $selectionIds);
                if($selectedStaffs){
                    foreach ($selectedStaffs as $staffId) {
                        $STAFF_EXAM = self::getStaffExam($staffId, $parentObject->GUID);
                        if($STAFF_EXAM->ROOM_ID==0){
                            $SQL = "UPDATE t_teacher_examination";
                            $SQL .= " SET ROOM_ID='".$facette->ROOM_ID."'";
                            $SQL .= " WHERE";
                            $SQL .= " STAFF_ID='" . $staffId . "'";
                            $SQL .= " AND EXAM_ID='" . $parentObject->GUID . "'";
                            self::dbAccess()->query($SQL);

                            ++$selectedCount;
                        }
                    }
                }
            }

            return array("success" => true, 'selectedCount' => $selectedCount);
        }

        ////////////////////////////////////////////////////////////////////////
        //EXAM ROOM...
        ////////////////////////////////////////////////////////////////////////
        public static function jsonAssignedStaffExamRoom($params,$isJason=true){

            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

            $globalSearch = isset($params["query"])?addText($params["query"]):"";
            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";

            $facette = self::findExamFromId($objectId);
            $parentObject = self::findExamParentFromId($objectId);

            $data = array();

            if($parentObject && $facette){

                $searchParams["globalSearch"] = $globalSearch;
                $searchParams["objectId"] = $parentObject->GUID; 
                $searchParams["roomId"] = $facette->ROOM_ID; 
                $result = self::getQueryAssignedStaffExamination($searchParams);

                $data = array();
                if ($result){

                    $i=0;
                    foreach ($result as $value) {

                        $CHECK = self::checkStaffByExam(
                            $value->STAFF_ID
                            , $parentObject->GUID
                            , $facette->ROOM_ID
                        );

                        if($CHECK){
                            $data[$i]["ID"] = $value->STAFF_ID;
                            $data[$i]["CODE"] = $value->STAFF_CODE;
                            $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                            $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
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
            if($isJason){
                return array(
                    "success" => true
                    , "totalCount" => sizeof($data)
                    , "rows" => $a
                );
            }else{
                return $data;
            }
        }

        public static function jsonActionRemoveStaffFromExamRoom($params){

            $newValue = isset($params["newValue"])?addText($params["newValue"]):"";
            $staffId = isset($params["id"])?addText($params["id"]):"";
            $objectId = isset($params["objectId"])?addText($params["objectId"]):"";
            $facette = self::findExamFromId($objectId);
            $parentObject = self::findExamParentFromId($objectId);

            if($newValue && $facette && $parentObject){
                $SAVEDATA['ROOM_ID'] = "";
                $WHERE[] = "STAFF_ID = '" . $staffId . "'";
                $WHERE[] = "EXAM_ID = '" . $parentObject->GUID . "'";
                self::dbAccess()->update('t_teacher_examination', $SAVEDATA, $WHERE);
            }

            return array(
                "success" => true
            );
        }
    }
?>