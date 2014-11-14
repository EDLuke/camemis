<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/training/TrainingDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once setUserLoacalization();

class TeacherTrainingDBAccess extends TrainingDBAccess {

    static function getInstance() {

        return new TeacherTrainingDBAccess();
    }

    public static function jsonTreeTeacherTrainings($params) {

        $GROUP_BY = "";

        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        $facette = self::findTrainingFromId($node);

        $data = array();

        if ($node == 0) {

            $SQL = "";
            $SQL .= " SELECT C.ID AS PROGRAMM_ID, C.NAME AS PRGRAMM_NAME";
            $SQL .= " FROM t_subject_teacher_training AS A";
            $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";
            $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.PROGRAM";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND A.TEACHER='" . Zend_Registry::get('USER')->ID . "'";
            $SQL .= " GROUP BY C.ID";

            $resultRows = self::dbAccess()->fetchAll($SQL);
        } else {

            $SQL = "";

            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    $SQL .= " SELECT C.ID AS LEVEL_ID, C.NAME AS LEVEL_NAME";
                    $GROUP_BY = " GROUP BY C.ID";
                    break;
                case "LEVEL":
                    $SQL .= " SELECT C.ID AS TERM_ID, C.START_DATE AS START_DATE, C.END_DATE AS END_DATE";
                    $GROUP_BY = " GROUP BY C.ID";
                    break;
                case "TERM":
                    $SQL .= " SELECT C.ID AS CLASS_ID, C.NAME AS CLASS_NAME, C.OBJECT_TYPE AS OBJECT_TYPE";
                    $GROUP_BY = " GROUP BY C.ID";
                    break;
                case "CLASS":
                    $SQL .= " SELECT D.ID AS SUBJECT_ID, D.NAME AS SUBJECT_NAME, B.NAME AS CLASS_NAME";
                    $SQL .= " ,D.SUBJECT_TYPE AS SUBJECT_TYPE";
                    $SQL .= " ,A.TRAINING AS TRAINING_ID";
                    $GROUP_BY = " GROUP BY D.ID";
                    break;
            }

            $SQL .= " FROM t_subject_teacher_training AS A";
            $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";

            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.LEVEL";
                    break;
                case "LEVEL":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.TERM";
                    break;
                case "TERM":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=A.TRAINING";
                    break;
                case "CLASS":
                    $SQL .= " LEFT JOIN t_subject AS D ON D.ID=A.SUBJECT";
                    break;
            }

            $SQL .= " WHERE 1=1";
            switch ($facette->OBJECT_TYPE) {
                case "CLASS":
                    $SQL .= " AND A.TRAINING='" . $node . "'";
                    break;
            }
            $SQL .= " AND A.TEACHER='" . Zend_Registry::get('USER')->ID . "'";
            $SQL .= $GROUP_BY;
            //error_log($SQL);
            $resultRows = self::dbAccess()->fetchAll($SQL);
        }

        if ($node == 0) {
            if ($resultRows) {
                $i = 0;
                foreach ($resultRows as $value) {
                    if ($value->PROGRAMM_ID) {
                        $data[$i]['id'] = "" . $value->PROGRAMM_ID . "";
                        $data[$i]['iconCls'] = "icon-bricks";
                        $data[$i]['text'] = stripslashes($value->PRGRAMM_NAME);
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $i++;
                    }
                }
            }
        } else {
            // error_log("Object Type: " . $facette->OBJECT_TYPE);
            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    $i = 0;
                    foreach ($resultRows as $value) {
                        $data[$i]['id'] = "" . $value->LEVEL_ID . "";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['text'] = stripslashes($value->LEVEL_NAME);
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $i++;
                    }
                    break;
                case "LEVEL":
                    $i = 0;
                    foreach ($resultRows as $value) {
                        if ($value->TERM_ID) {
                            $data[$i]['id'] = $value->TERM_ID;
                            $data[$i]['iconCls'] = "icon-date";
                            $data[$i]['text'] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                            $data[$i]['leaf'] = false;
                            $data[$i]['cls'] = "nodeTextBold";
                            $i++;
                        }
                    }
                    break;
                case "TERM":
                    $i = 0;
                    foreach ($resultRows as $value) {
                        if ($value->CLASS_ID && $value->CLASS_NAME) {
                            $data[$i]['id'] = "" . $value->CLASS_ID . "";
                            $data[$i]['objectType'] = "" . $value->OBJECT_TYPE . "";
                            $data[$i]['iconCls'] = "icon-blackboard";
                            $data[$i]['text'] = stripslashes($value->CLASS_NAME);
                            $data[$i]['leaf'] = false;
                            $data[$i]['cls'] = "nodeTextBold";
                            $i++;
                        }
                    }
                    break;
                case "CLASS":
                    $i = 0;
                    foreach ($resultRows as $value) {
                        $data[$i]['id'] = createCode();
                        $data[$i]['subjectId'] = "" . $value->SUBJECT_ID . "";
                        $data[$i]['objectId'] = "" . $value->TRAINING_ID . "";
                        $data[$i]['classname'] = "" . $value->CLASS_NAME . "";
                        switch ($value->SUBJECT_TYPE) {
                            case 0:
                                $data[$i]['iconCls'] = "icon-star_silver";
                                break;
                            case 1:
                                $data[$i]['iconCls'] = "icon-star";
                                break;
                            case 2:
                                $data[$i]['iconCls'] = "icon-star_blue";
                                break;
                            case 3:
                                $data[$i]['iconCls'] = "icon-star_red";
                                break;
                            default:
                                $data[$i]['iconCls'] = "icon-star";
                                break;
                        }

                        $data[$i]['text'] = stripslashes($value->SUBJECT_NAME);
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $i++;
                    }

                    break;
            }
        }
        return $data;
    }

    public static function getTrainingByTeacher($teacherId) {
        $SQL = "";
        $SQL .= " SELECT * FROM t_subject_teacher_training WHERE TEACHER='" . $teacherId . "'";
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

}

?>