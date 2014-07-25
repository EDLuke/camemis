<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.August.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class AcademicDateDBAccess {

    protected $data = array();
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

    public function getAcademicDatetDataFromId($Id) {

        $data = array();

        $result = self::findAcademicDateFromId($Id);

        if ($result) {

            $data["SCHOOLYEAR_DATE"] = getShowDate($result->START) . " - " . getShowDate($result->END);

            $data["ID"] = $result->ID;
            $data["TERM_NUMBER"] = $result->TERM_NUMBER;
            $data["SORT"] = $result->SORT;
            $data["STATUS"] = $result->STATUS;
            $data["NAME"] = setShowText($result->NAME);
            $data["START"] = getShowDate($result->START);
            $data["END"] = getShowDate($result->END);

            $data["IS_CURRENT_YEAR"] = $this->isCurrentSchoolyear($result->ID);
            $data["IS_PAST_YEAR"] = $this->isPastSchoolyear($result->ID);

            $data["SEMESTER1_START"] = getShowDate($result->SEMESTER1_START);
            $data["SEMESTER1_END"] = getShowDate($result->SEMESTER1_END);

            $data["SEMESTER2_START"] = getShowDate($result->SEMESTER2_START);
            $data["SEMESTER2_END"] = getShowDate($result->SEMESTER2_END);

            $data["TERM1_START"] = getShowDate($result->TERM1_START);
            $data["TERM1_END"] = getShowDate($result->TERM1_END);

            $data["TERM2_START"] = getShowDate($result->TERM2_START);
            $data["TERM2_END"] = getShowDate($result->TERM2_END);

            $data["TERM3_START"] = getShowDate($result->TERM3_START);
            $data["TERM3_END"] = getShowDate($result->TERM3_END);

            $data["QUARTER1_START"] = getShowDate($result->QUARTER1_START);
            $data["QUARTER1_END"] = getShowDate($result->QUARTER1_END);

            $data["QUARTER2_START"] = getShowDate($result->QUARTER2_START);
            $data["QUARTER2_END"] = getShowDate($result->QUARTER2_END);

            $data["QUARTER3_START"] = getShowDate($result->QUARTER3_START);
            $data["QUARTER3_END"] = getShowDate($result->QUARTER3_END);

            $data["QUARTER4_START"] = getShowDate($result->QUARTER4_START);
            $data["QUARTER4_END"] = getShowDate($result->QUARTER4_END);

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
        }

        return $data;
    }

    public static function findAcademicDateFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE";
        if (is_numeric($Id)) {
            $SQL .= " INDEX_ID = '" . $Id . "'";
        } else {
            $SQL .= " ID = '" . $Id . "'";
        }

        return self::dbAccess()->fetchRow($SQL);
    }

    //@Sea Peng 19.12.2013
    public static function findSemesterDateByClass($academicId, $schoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from("t_grade", array("*"));

        if ($academicId)
            $SQL->where("ID = ?",$academicId);
        if ($schoolyearId)
            $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getAllAcademicDatesQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $node = isset($params["node"]) ? addText($params["node"]) : "";

        $facette = self::findAcademicDateFromId($node);

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate AS A";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }
        if (!$facette) {
            $SQL .= " AND PARENT='0'";
        } else {
            $SQL .= " AND PARENT='" . $facette->INDEX_ID . "'";
        }

        $SQL .= " ORDER BY A.SORT DESC";
        return self::dbAccess()->fetchAll($SQL);
    }

    public function loadAcademicDateFromId($Id) {
        $result = self::findAcademicDateFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getAcademicDatetDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        self::dbAccess()->delete("t_academicdate", "ID = '" . $removeId . "'");

        return array("success" => true);
    }

    public function updateAcademicDate($params) {

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SORT"]))
            $SAVEDATA['SORT'] = addText($params["SORT"]);

        if (isset($params["TERM_NUMBER"]))
            $SAVEDATA['TERM_NUMBER'] = addText($params["TERM_NUMBER"]);

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $params["objectId"]);
        self::dbAccess()->update('t_academicdate', $SAVEDATA, $WHERE);
        return array("success" => true);
    }

    public function createOnlyItem($params) {

        $NAME = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $TERM_NUMBER = isset($params["TERM_NUMBER"]) ? addText($params["TERM_NUMBER"]) : "";
        $SORT = isset($params["SORT"]) ? addText($params["SORT"]) : "";

        if ($NAME) {
            $SAVEDATA['NAME'] = $NAME;
            $SAVEDATA['TERM_NUMBER'] = $TERM_NUMBER;
            $SAVEDATA['SORT'] = $SORT;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['ID'] = generateGuid();
            self::dbAccess()->insert('t_academicdate', $SAVEDATA);
        }

        return array("success" => true);
    }

    //@veasna
    public function selectBoxSchoolyearNowFuture() {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND START<NOW() AND NOW()<END";
        $SQL .= " OR START>NOW() AND NOW()<END";
        $SQL .= " AND STATUS=1";
        $SQL .= " ORDER BY SORT DESC";
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();
        $i = 0;

        $data[$i]["ID"] = "0";
        $data[$i]["NAME"] = "[---]";
        if ($result)
            foreach ($result as $value) {

                $data[$i + 1]["ID"] = $value->ID;
                $data[$i + 1]["NAME"] = $value->NAME;

                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    // 

    public static function getAllSchoolyear() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("STATUS='1'");
        $SQL->order("START ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function allSchoolyearCombo() {
        //error_log($SQL->__toString());
        $result = self::getAllSchoolyear();
        $data = array();
        $i = 0;

        $data[$i]["ID"] = "0";
        $data[$i]["NAME"] = "[---]";
        if ($result)
            foreach ($result as $value) {

                $data[$i + 1]["ID"] = $value->ID;
                $data[$i + 1]["NAME"] = $value->NAME;

                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function getComboDataLastSchoolyear($search = false) {

        $data = array();

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("NAME IS NOT NULL");
        $SQL->where("DATEDIFF(END, NOW()) < 0");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if (!$search)
            $data[0] = "['0','[---]']";
        if ($result) {
            foreach ($result as $value) {
                $name = $value->NAME;
                $data[$i + 1] = "[\"$value->ID\",\"$name\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public static function getComboDataNextSchoolyear($search = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("NAME IS NOT NULL");
        $SQL->where("DATEDIFF(END, NOW()) > 0");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        $i = 0;
        if (!$search)
            $data[0] = "['0','[---]']";
        if ($result) {
            foreach ($result as $value) {
                $name = $value->NAME;
                $data[$i + 1] = "[\"$value->ID\",\"$name\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    /**
     * Combo academic date
     */
    public function AcademicDateCombo() {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND STATUS=1";
        $SQL .= " AND NOW()<=END";
        $SQL .= " ORDER BY SORT DESC";

        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data[$i] = "['0','[---]']";
        if ($result) {
            foreach ($result as $value) {
                $name = $value->NAME;
                $data[$i + 1] = "[\"$value->ID\",\"$name\"]";
                $i++;
            }
        }

        return "[" . implode(",", $data) . "]";
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $facette = self::findAcademicDateFromId($objectId);
        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_academicdate";
        $SQL .= " SET";

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SQL .= " STATUS=1";
                $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
            case 1:
                $newStatus = 0;
                $SQL .= " STATUS=0";
                $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $objectId . "'";

        self::dbAccess()->query($SQL);

        return array("success" => true, "status" => $newStatus);
    }

    public function jsonTreeAllAcademicDate($params) {

        $data = array();
        $result = $this->getAllAcademicDatesQuery($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {
                //$isCurrentYear = $this->isCurrentSchoolyear($value->ID);
                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = stripslashes($value->NAME);
                if ($value->STATUS) {
                    $data[$i]['iconCls'] = "icon-green";
                } else {
                    $data[$i]['iconCls'] = "icon-red";
                }
                $data[$i]['leaf'] = true;
                $i++;
            }

        return $data;
    }

    public static function getListSchoolyearByGradeId() {

        $data = array();

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND STATUS=1";
        $SQL .= " AND DISPLAY=1";
        $SQL .= " ORDER BY SORT DESC";

        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
            foreach ($result as $value) {
                $data[] = "['" . $value->ID . "', '" . $value->NAME . "']";
            }

        return $data;
    }

    public function isCurrentSchoolyear($Id) {

        $facette = self::loadCurrentSchoolyear($Id);

        if ($facette) {
            return true;
        } else {
            return false;
        }
    }

    public static function loadLastSchoolyear() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("NAME IS NOT NULL");
        $SQL->where("DATEDIFF(END, DATE(NOW())) < 0");
        $SQL->limitPage(0, 1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function loadCurrentSchoolyear($Id = false, $date = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("NAME IS NOT NULL");

        if ($Id) {
            $SQL->where("ID = ?",$Id);
        }

        if ($date) {
            $SQL->where("DATEDIFF(END,'" . $date . "') > 0");
        } else {
            $SQL->where("CAST(NOW() AS DATE) BETWEEN START AND END");
        }

        $SQL->limitPage(0, 1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function findCurrentSchoolyear($id = false) {

        $result = $this->loadCurrentSchoolyear();

        if ($result) {
            if ($id == true)
                return $result->ID;
            else
                return $result->NAME;
        }
    }

    public function isPastSchoolyear($Id) {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ID ='" . $Id . "'";
        $SQL .= " AND DATEDIFF(END, DATE(NOW())) < 0";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? 1 : 0;
    }

    public static function getSchoolyearByGradeId($gradeId) {

        $data = array();

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND GRADE_ID = '" . $gradeId . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
            foreach ($result as $value) {

                $data[$value->SCHOOL_YEAR] = $value->SCHOOL_YEAR;
            }

        return $data;
    }

    public static function findYearByEndDate($enddate) {

        $SQL = "";
        $SQL .= " SELECT YEAR(END) AS YEAR";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND END='" . $enddate . "'";
        $SQL .= " ORDER BY YEAR(START) ASC LIMIT 0,1";
        return self::dbAccess()->fetchRow($SQL);
    }

    //sea peng
    public static function findAcademicBetweenDate($date) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate");
        $SQL->where("START < '" . $date . "'");
        $SQL->where("END > '" . $date . "'");
        //error_log($SQL->__toString());

        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getNextSchoolyearFromLast($Id) {

        $facette = self::findAcademicDateFromId($Id);

        $result = null;

        if ($facette) {
            $dateEndObject = self::findYearByEndDate($facette->END);
            $SQL = "";
            $SQL .= " SELECT *";
            $SQL .= " FROM t_academicdate";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND YEAR(START)=" . $dateEndObject->YEAR . "";
            $SQL .= " ORDER BY YEAR(START) ASC LIMIT 0,1";
            //echo $SQL;
            $result = self::dbAccess()->fetchRow($SQL);
        }

        return $result;
    }

    private function getYearlevel($schoolyearId) {
        $SQL = "SELECT YEAR_LEVEL AS C";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ID = '" . $schoolyearId . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return isset($result) ? $result->C : 0;
    }

    public function getSchoolYearBetween($schoolyearIdStart, $schoolyearIdEnd) {
        $yearLevel1 = $this->getYearlevel($schoolyearIdStart);
        $yearLevel2 = $this->getYearlevel($schoolyearIdEnd);
        if ($yearLevel1 > $yearLevel2) {
            $tmp = $yearLevel1;
            $yearLevel1 = $yearLevel2;
            $yearLevel2 = $tmp;
        }
        $SQL = "
            SELECT ID AS SCHOOL_YEAR
            FROM t_academicdate AS A
            WHERE YEAR_LEVEL BETWEEN " . $yearLevel1 . " AND " . $yearLevel2;

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            foreach ($result as $value) {
                $data[] = "'" . $value->SCHOOL_YEAR . "'";
            }
        return $data;
    }

    protected function getClassBySchoolyearId($schoolyearId, $campusId) {

        $SQL = "
            SELECT A.ID AS CLASS_ID, A.NAME AS CLASS_NAME, B.NAME AS GRADE_NAME
            FROM  t_grade AS A
            LEFT JOIN t_grade AS B ON B.ID=A.GRADE_ID
            WHERE 1=1 
            AND A.SCHOOL_YEAR ='" . $schoolyearId . "'
            AND A.CAMPUS_ID ='" . $campusId . "'
            AND A.OBJECT_TYPE =  'CLASS'
            ";
        $SQL .= " ORDER BY A.NAME";
        //echo $SQL;
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            foreach ($result as $value) {

                $data[$value->CLASS_ID] = $value->CLASS_NAME . " (" . $value->GRADE_NAME . ")";
            }

        return $data;
    }

    public function getRadioBoxClassBySchoolyearId($schoolyearId, $campusId) {

        $entries = $this->getClassBySchoolyearId($schoolyearId, $campusId);

        $data = array();
        $i = 0;
        if ($entries)
            foreach ($entries as $key => $value) {

                $js = "{";
                $js .= "fieldLabel: ''";
                $js .= ",xtype: 'radio'";
                $js .= ",id: '" . $key . "'";
                $js .= ",disabled: false";
                $js .= ",boxLabel: '" . $value . "'";
                $js .= ",name: 'CLASS'";
                $js .= ",inputValue: '" . $key . "'";
                $js .= "}";
                $data[$key] = $js;

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public static function getListLastSchoolyear() {

        $facette = self::loadCurrentSchoolyear();

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND STATUS=1";
        if ($facette)
            $SQL .= " AND YEAR_LEVEL<'" . $facette->YEAR_LEVEL . "'";
        $SQL .= " ORDER BY SORT DESC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function actionDateline($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : 0;

        $UPDATE_VALUES['MODIFY_DATE'] = getCurrentDBDateTime();
        $UPDATE_VALUES['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        switch ($actionType) {
            case 1:
                $SAVEDATA['START'] = isset($params["START"]) ? setDate2DB($params["START"]) : "";
                $SAVEDATA['END'] = isset($params["START"]) ? setDate2DB($params["END"]) : "";
                break;
            case 12:
                $SAVEDATA['SEMESTER1_START'] = isset($params["SEMESTER1_START"]) ? setDate2DB($params["SEMESTER1_START"]) : "";
                $SAVEDATA['SEMESTER1_END'] = isset($params["SEMESTER1_END"]) ? setDate2DB($params["SEMESTER1_END"]) : "";
                break;
            case 22:
                $SAVEDATA['SEMESTER2_START'] = isset($params["SEMESTER2_START"]) ? setDate2DB($params["SEMESTER2_START"]) : "";
                $SAVEDATA['SEMESTER2_END'] = isset($params["SEMESTER2_END"]) ? setDate2DB($params["SEMESTER2_END"]) : "";
                break;
            case 13:
                $SAVEDATA['TERM1_START'] = isset($params["TERM1_START"]) ? setDate2DB($params["TERM1_START"]) : "";
                $SAVEDATA['TERM1_END'] = isset($params["TERM1_END"]) ? setDate2DB($params["TERM1_END"]) : "";
                break;
            case 23:
                $SAVEDATA['TERM2_START'] = isset($params["TERM2_START"]) ? setDate2DB($params["TERM2_START"]) : "";
                $SAVEDATA['TERM2_END'] = isset($params["TERM2_END"]) ? setDate2DB($params["TERM2_END"]) : "";
                break;
            case 33:
                $SAVEDATA['TERM3_START'] = isset($params["TERM3_START"]) ? setDate2DB($params["TERM3_START"]) : "";
                $SAVEDATA['TERM3_END'] = isset($params["TERM3_END"]) ? setDate2DB($params["TERM3_END"]) : "";
                break;
            case 14:
                $SAVEDATA['QUARTER1_START'] = isset($params["QUARTER1_START"]) ? setDate2DB($params["QUARTER1_START"]) : "";
                $SAVEDATA['QUARTER1_END'] = isset($params["QUARTER1_END"]) ? setDate2DB($params["QUARTER1_END"]) : "";
                break;
            case 24:
                $SAVEDATA['QUARTER2_START'] = isset($params["QUARTER2_START"]) ? setDate2DB($params["QUARTER2_START"]) : "";
                $SAVEDATA['QUARTER2_END'] = isset($params["QUARTER2_END"]) ? setDate2DB($params["QUARTER2_END"]) : "";
                break;
            case 34:
                $SAVEDATA['QUARTER3_START'] = isset($params["QUARTER3_START"]) ? setDate2DB($params["QUARTER3_START"]) : "";
                $SAVEDATA['QUARTER3_END'] = isset($params["QUARTER3_END"]) ? setDate2DB($params["QUARTER3_END"]) : "";
                break;
            case 44:
                $SAVEDATA['QUARTER4_START'] = isset($params["QUARTER4_START"]) ? setDate2DB($params["QUARTER4_START"]) : "";
                $SAVEDATA['QUARTER4_END'] = isset($params["QUARTER4_END"]) ? setDate2DB($params["QUARTER4_END"]) : "";
                break;
        }
        $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
        self::dbAccess()->update('t_academicdate', $SAVEDATA, $WHERE);
        return array("success" => true);
    }

    public static function findSchoolyearByCurrentDate() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("DATE(NOW())>=START AND DATE(NOW())<=END");
        $SQL->order("PARENT DESC"); //@veasna
        $SQL->limitPage(0, 1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    ////////////////////////////////////////////////////////////////////
    //@Sea Peng
    ////////////////////////////////////////////////////////////////////
    public static function findSchoolyearByDate($date) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("'" . $date . "' BETWEEN START AND END");
        $SQL->order("PARENT DESC");
        $SQL->limitPage(0, 1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //////////////////////////////////////////////////////////////////

    public static function findSchoolyearByClassId($classObj) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("STATUS=1");
        $SQL->where("ID='" . $classObj->SCHOOL_YEAR . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findSchoolyearByStartEnd($start, $stop) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        $SQL->where("('" . $start . "' BETWEEN START AND END) OR ('" . $stop . "' BETWEEN START AND END)");
        $SQL->limit(1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

}

?>