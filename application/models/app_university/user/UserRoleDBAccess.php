<?php

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 04.07.2010 
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class UserRoleDBAccess {

        protected $data = array();
        protected $out = array();
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

        public static function dbAminAccess() {
            return Zend_Registry::get('ADMIN_DB_ACCESS');
        }

        public static function dbSelectAccess() {
            return self::dbAccess()->select();
        }

        public function getUserRoleDataFromId($Id) {

            $facette = $this->findUserRoleFromId($Id);
            $data = array();
            if ($facette) {
                $data["ID"] = $facette->ID;
                $data["STATUS"] = $facette->STATUS;
                $data["NAME"] = setShowText($facette->NAME);
                $data["SHORT"] = setShowText($facette->SHORT);
                $data["TUTOR"] = $facette->TUTOR;
                $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
                $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
                $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
                $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);

                $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
                $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
                $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
                $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
                $data["NO_DELETE"] = $facette->NO_DELETE;
                $data["REMOVE_STATUS"] = $this->checkRemove($facette->ID);
            }

            return $data;
        }

        public static function findUserRoleFromId($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_memberrole", array("*"));
            $SQL->where("ID = '" . $Id . "'");
            //error_log($SQL->__toString());
            return self::dbAccess()->fetchRow($SQL);
        }

        public function treeAllUserrole($params) {

            $searchAdmin = isset($params["searchAdmin"]) ? addText($params["searchAdmin"]) : "";
            $academicId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            if ($academicId)
                $permissionList = AcademicDBAccess::getListStaffsScorePermission($academicId);

            $parent = $params["node"];
            $params["parent"] = $parent;

            $data = Array();

            if ($parent == 0) {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_memberrole", array("*"));
                if ($searchAdmin) {
                    $SQL->where("ID NOT IN (2,3,4,5)");
                }
                $SQL->where("PARENT = '0'");
                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);

                $i = 0;
                foreach ($result as $value) {
                    $SHORT = $value->SHORT ? setShowText($value->SHORT) : '?';
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['text'] = "(" . $SHORT . ") " . setShowText($value->NAME);
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    switch ($value->ID) {
                        case 4:
                        case 5:
                            $data[$i]['adduser'] = false;
                            $data[$i]['add'] = false;
                            break;
                        default:
                            $data[$i]['adduser'] = true;
                            $data[$i]['add'] = true;
                            break;
                    }
                    $i++;
                }
            } else {

                $CHECK_USER = self::checkUserByRoleId($parent);
                if ($CHECK_USER) {
                    $result = self::getAllUserRoleQuery($params);
                    foreach ($result as $i => $value) {

                        if ($value->USER_ID) {
                            $data[$i]['id'] = "" . $value->USER_ID . "";
                            $data[$i]['text'] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                            $data[$i]['cls'] = "nodeTextBlue";

                            ////////////////////////////////////////////////////////
                            //Permission score entry...
                            ////////////////////////////////////////////////////////
                            if ($searchAdmin) {
                                $data[$i]['iconCls'] = "icon-shape_square_link";
                                $data[$i]['checked'] = (in_array($value->USER_ID, $permissionList)) ? true : false;
                            } else {
                                ////////////////////////////////////////////////////
                                //System User...
                                ////////////////////////////////////////////////////
                                if ($value->USER_STATUS) {
                                    $data[$i]['iconCls'] = "icon-green";
                                } else {
                                    $data[$i]['iconCls'] = "icon-red";
                                }
                            }

                            $data[$i]['add'] = false;
                            $data[$i]['leaf'] = true;
                        }
                        $i++;
                    }
                }

                $CHECK_CHILD = self::checkChild($parent);
                if ($CHECK_CHILD) {
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_memberrole", array("*"));
                    $SQL->where("PARENT = '" . $parent . "'");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchAll($SQL);

                    $i = 0;
                    foreach ($result as $value) {

                        $data[$i]['id'] = "" . $value->ID . "";
                        $data[$i]['cls'] = "nodeTextBoldBlue";
                        $data[$i]['text'] = stripslashes($value->NAME);
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['add'] = false;

                        $i++;
                    }
                }
            }
            return $data;
        }

        public static function getAllUserRoleQuery($params) {

            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
            $parent = isset($params["parent"]) ? addText($params["parent"]) : "0";
            $educationType = isset($params["educationType"]) ? addText($params["educationType"]) : "0";

            $SQL = "";
            $SQL .= " SELECT DISTINCT";
            $SQL .= " A.ID AS ID";
            $SQL .= " ,B.ID AS USER_ID";
            $SQL .= " ,A.TUTOR AS TUTOR";
            $SQL .= " ,A.NAME AS USER_ROLE";
            $SQL .= " ,C.PHONE AS PHONE";
            $SQL .= " ,C.EMAIL AS EMAIL";
            $SQL .= " ,C.FIRSTNAME AS FIRSTNAME";
            $SQL .= " ,C.LASTNAME AS LASTNAME";
            $SQL .= " ,B.LOGINNAME AS LOGINNAME";
            $SQL .= " ,B.STATUS AS USER_STATUS";
            $SQL .= " ,CONCAT('(',C.CODE,') ',C.LASTNAME,' ', C.FIRSTNAME) AS USER_NAME";
            $SQL .= " FROM t_memberrole AS A";
            $SQL .= " LEFT JOIN t_members AS B ON B.ROLE = A.ID";
            $SQL .= " LEFT JOIN t_staff AS C ON C.ID = B.ID";
            $SQL .= " WHERE 1=1";

            if ($parent)
                $SQL .= " AND A.ID = '" . $parent . "'";

            if ($globalSearch) {

                $SQL .= " AND ((C.NAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (C.FIRSTNAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (C.LASTNAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (C.CODE LIKE '" . strtoupper($globalSearch) . "%')";
                $SQL .= " ) ";
            }
            if ($parent == 0)
                $SQL .= " GROUP BY A.ID";
            $SQL .= " ORDER BY A.NAME";

            //echo $SQL;
            return self::dbAccess()->fetchAll($SQL);
        }

        public function loadUserRoleFromId($Id) {
            $result = $this->findUserRoleFromId($Id);

            if ($result) {
                $o = array(
                    "success" => true
                    , "data" => $this->getUserRoleDataFromId($Id)
                );
            } else {
                $o = array(
                    "success" => true
                    , "data" => array()
                );
            }
            return $o;
        }

        public function updateObject($params) {

            $SAVEDATA = array();

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
            $name = isset($params["name"]) ? addText($params["name"]) : '';

            $SAVEDATA['NAME'] = addText($name);
            if (isset($params["short"]))
                $SAVEDATA['SHORT'] = addText($params["short"]);
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            if ($objectId)
                self::dbAccess()->update('t_memberrole', $SAVEDATA, $WHERE);

            return array("success" => true);
        }

        public function createObject($params) {

            $SAVEDATA = array();

            $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";

            if (isset($params["name"])) {
                $name = $params["name"];
            }

            if (isset($params["NAME"])) {
                $name = $params["NAME"];
            }

            if ($name) {

                $SAVEDATA['NAME'] = addText($name);
                $SAVEDATA['GUID'] = generateGuid();
                if ($parentId) {
                    $SAVEDATA['PARENT'] = $parentId;
                }
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                self::dbAccess()->insert('t_memberrole', $SAVEDATA);
            }

            return array("success" => true);
        }

        public function getObjectUserRole($Id) {

            return $this->findUserRoleFromId($Id);
        }

        public static function allUserRole($type = false) {

            $SQL = "";
            $SQL .= " SELECT ID, NAME, PARENT";
            $SQL .= " FROM t_memberrole";
            $SQL .= " WHERE 1=1";
            if ($type)
                $SQL .= " AND TUTOR IN (1,2)";

            return self::dbAccess()->fetchAll($SQL);
        }

        public function removeObject($params) {

            $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            $SQL = "DELETE FROM t_memberrole";
            $SQL .= " WHERE";
            $SQL .= " ID='" . $removeId . "'";
            self::dbAccess()->query($SQL);

            return array("success" => true);
        }

        public function releaseObject($params) {

            $newStatus = 0;

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

            $facette = $this->findUserRoleFromId($objectId);
            $status = $facette->STATUS;

            $SQL = "";
            $SQL .= "UPDATE ";
            $SQL .= " t_memberrole";
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

        public function checkRemove($Id) {

            $facette = $this->findUserRoleFromId($Id);
            $check1 = $this->checkUserByRoleId($Id);
            $check2 = $facette->NO_DELETE;

            if ($check1 || $check2 == 1) {
                echo false;
            } else {
                return true;
            }
        }

        public static function checkChild($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_memberrole", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = '" . $Id . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public function checkUserByRoleId($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_members", array("C" => "COUNT(*)"));
            $SQL->where("ROLE = '" . $Id . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public function findUserRoleByTutor($tutor) {
            /**
            * 1 = Instructor
            * 2 = Teacher
            */
            $SQL = self::dbAccess()->select();
            $SQL->from("t_memberrole", array("*"));
            $SQL->where("TUTOR = '" . $tutor . "'");
            //error_log($SQL->__toString());
            return self::dbAccess()->fetchRow($SQL);
        }

        public function jsonTreeAllRights($params) {

            $data = array();

            $searchParent = isset($params["searchParent"]) ? addText($params["searchParent"]) : "";
            $treeSearch = isset($params["treeSearch"]) ? $params["treeSearch"] : "";
            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            $node = isset($params["node"]) ? addText($params["node"]) : 0;
            $defaultRole = isset($params["key"]) ? addText($params["key"]) : 1;

            switch ($treeSearch) {
                case "treefolder":
                    $SQL = "SELECT * FROM t_school_user_right";
                    $SQL .= " WHERE 1=1";
                    $SQL .= " AND PARENT='0'";
                    $SQL .= " AND DEFAULT_ROLE='" . $defaultRole . "'";
                    $SQL .= " ORDER BY SORTKEY ASC";
                    break;
                case "treeall":
                    $SQL = "SELECT * FROM t_school_user_right";
                    $SQL .= " WHERE 1=1";

                    if ($node == 0) {
                        if ($searchParent) {
                            $SQL .= " AND PARENT='" . $searchParent . "'";
                            if (!Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
                                $SQL .= " AND CONST_RIGHT <> 'GENERAL_EDUCATION'";
                                $SQL .= " AND CONST_RIGHT <> 'EXAMINATION_MANAGEMENT'";
                            }

                            if (!Zend_Registry::get('SCHOOL')->TRAINING_PROGRAMS) {
                                $SQL .= " AND CONST_RIGHT <> 'TRAINING_PROGRAMS'";
                            }

                        } else {
                            $SQL .= " AND PARENT='0'";
                        }
                    } else {
                        $SQL .= " AND PARENT='" . $node . "'";
                        if (!UserAuth::displayRoleGeneralEducation())
                            $SQL .= " AND CONST_RIGHT <> 'GENERAL_EDUCATION'";
                        if (!UserAuth::displayRoleTrainingEducation())
                            $SQL .= " AND CONST_RIGHT <> 'TRAINING_PROGRAMS'";
                    }

                    if ($defaultRole)
                        $SQL .= " AND DEFAULT_ROLE='" . $defaultRole . "'";
                    $SQL .= " ORDER BY SORTKEY ASC";
                    break;
            }

            //error_log($SQL);
            $result = self::dbAminAccess()->fetchAll($SQL);

            if ($result) {

                $i = 0;
                foreach ($result as $value) {

                    switch ($treeSearch) {
                        case "treefolder":
                            $data[$i]['id'] = "" . $value->ID . "";
                            $data[$i]['right'] = "" . $value->USER_RIGHT . "";

                            $isconst = defined(trim($value->CONST_RIGHT)) ? true : false;

                            if ($isconst) {
                                $data[$i]['text'] = constant(trim($value->CONST_RIGHT));
                            } else {
                                $data[$i]['text'] = "No const.... (" . $value->CONST_RIGHT . ") ";
                            }

                            if ($value->CHECKED) {
                                if (self::checkAssignedUserRight($objectId, $value->ID)) {
                                    $data[$i]['checked'] = true;
                                } else {
                                    $data[$i]['checked'] = false;
                                }
                            }

                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['iconCls'] = $value->ICON;
                            $data[$i]['leaf'] = true;
                            $i++;
                            break;
                        case "treeall":
                            $data[$i]['id'] = "" . $value->ID . "";
                            $data[$i]['right'] = "" . $value->USER_RIGHT . "";

                            $isconst = defined(trim($value->CONST_RIGHT)) ? true : false;

                            if ($isconst) {
                                $data[$i]['text'] = constant(trim($value->CONST_RIGHT));
                            } else {
                                $data[$i]['text'] = "No const.... (" . $value->CONST_RIGHT . ") ";
                            }

                            switch ($value->OBJECT_TYPE) {
                                case "FOLDER":
                                    if ($value->CHECKED) {
                                        if (self::checkAssignedUserRight($objectId, $value->ID)) {
                                            $data[$i]['checked'] = true;
                                        } else {
                                            $data[$i]['checked'] = false;
                                        }
                                    }
                                    $data[$i]['leaf'] = false;
                                    $data[$i]['disabled'] = false;
                                    $data[$i]['cls'] = "nodeTextBold";

                                    if ($value->ICON) {
                                        $data[$i]['iconCls'] = $value->ICON;
                                    } else {
                                        $data[$i]['iconCls'] = "icon-folder_key";
                                    }
                                    $data[$i]['objectType'] = "FOLDER";
                                    $data[$i]['parentId'] = $value->PARENT;
                                    $data[$i]['reloadId'] = 0;
                                    break;
                                case "ITEM":
                                    if ($value->CHECKED) {
                                        if (self::checkAssignedUserRight($objectId, $value->ID)) {
                                            $data[$i]['checked'] = true;
                                        } else {
                                            $data[$i]['checked'] = false;
                                        }
                                    }
                                    $data[$i]['leaf'] = true;
                                    $data[$i]['reloadId'] = $value->PARENT;
                                    if ($value->ICON) {
                                        $data[$i]['cls'] = "nodeTextBold";
                                        $data[$i]['iconCls'] = $value->ICON;
                                    } else {
                                        switch ($value->USER_RIGHT) {
                                            case "READ_RIGHT":

                                                $data[$i]['cls'] = "nodeText";
                                                $data[$i]['iconCls'] = "icon-bullet_square_grey";
                                                break;
                                            case "EDIT_RIGHT":
                                                $data[$i]['cls'] = "nodeText";
                                                $data[$i]['iconCls'] = "icon-bullet_square_green";
                                                break;
                                            case "REMOVE_RIGHT":
                                                $data[$i]['cls'] = "nodeText";
                                                $data[$i]['iconCls'] = "icon-bullet_square_red";
                                                break;
                                            case "EXECUTE_RIGHT":
                                                $data[$i]['cls'] = "nodeText";
                                                $data[$i]['iconCls'] = "icon-bullet_square_yellow";
                                                break;
                                        }
                                    }
                                    break;
                            }
                            $i++;
                            break;
                    }
                }
            }

            self::addAutoReadStudentPersonalInformation($objectId);
            self::addAutoReadStaffPersonalInformation($objectId);

            return $data;
        }

        public static function findUserRightFromId($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_school_user_right", array("*"));

            if (is_numeric($Id)) {
                $SQL->where("ID = '" . $Id . "'");
            } else {
                $SQL->where("USER_RIGHT = '" . $Id . "'");
                $SQL->limit(1);
                //error_log($SQL->__toString());
            }
            //error_log($SQL->__toString());
            return self::dbAminAccess()->fetchRow($SQL);
        }

        protected static function callUserRights($Id) {
            switch(UserAuth::getUserType()){
                case "STUDENT":
                case "GUARDIAN":
                    $data = self::getAllUserRights($Id);
                    break;
                default:
                    if (UserAuth::getAddedUserRole()) {
                        $FIRST_DATA = self::getAllUserRights($Id);
                        $SECOND_DATA = self::getAllUserRights(UserAuth::getAddedUserRole());
                        $data = $FIRST_DATA + $SECOND_DATA;
                    } else {
                        $data = self::getAllUserRights($Id);
                    }
                    break;
            }
            return $data;
        }

        protected static function getAllUserRights($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_user_right", array("*"));
            $SQL->where("USERROLE_ID = '" . $Id . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
            $data = array();
            if ($result) {
                foreach ($result as $v) {
                    if ($v->USER_RIGHT)
                        $data[$v->USER_RIGHT] = 1;
                }
            }
            return $data;
        }

        public static function setACLData($Id) {

            $entries = self::callUserRights($Id);

            if (!$entries) {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_user_right", array("*"));
                $SQL->where("USERROLE_ID = '" . $Id . "'");
                //error_log($SQL->__toString());
                $result = self::dbAminAccess()->fetchAll($SQL);

                if ($result) {
                    foreach ($result as $value) {

                        if (!self::checkAssignedUserRight($Id, $value->RIGHT_ID)) {
                            $SQL = "";
                            $SQL .= "INSERT INTO t_user_right";
                            $SQL .= " SET";
                            $SQL .= " USER_RIGHT='" . $value->USER_RIGHT . "'";
                            $SQL .= " ,RIGHT_ID='" . $value->RIGHT_ID . "'";
                            $SQL .= " ,USERROLE_ID='" . $value->USERROLE_ID . "'";
                            $SQL .= " ,PARENT='" . $value->PARENT . "'";
                            self::dbAccess()->query($SQL);
                        }
                    }
                }
            }
        }

        public static function getACLData($Id) {

            return self::callUserRights($Id);
        }

        protected static function addAutoReadStudentPersonalInformation($roleId) {

            $facette = self::findUserRightFromId('PERSONAL_INFORMATION');
            if ($facette) {
                $SAVEDATA["PARENT"] = $facette->PARENT;
                $SAVEDATA["USERROLE_ID"] = $roleId;
                $SAVEDATA["USER_RIGHT"] = 'STUDENT_PERSONAL_INFORMATION_READ_RIGHT';
                $SAVEDATA["RIGHT_ID"] = $facette->ID;

                if (!self::checkAssignedUserRight($roleId, 'STUDENT_PERSONAL_INFORMATION_READ_RIGHT')) {
                    self::dbAccess()->insert('t_user_right', $SAVEDATA);
                }
            }
        }

        protected static function addAutoReadStaffPersonalInformation($roleId) {

            if ($roleId != 4) {
                $facette = self::findUserRightFromId('PERSONAL_INFORMATION');
                if ($facette) {
                    $SAVEDATA["PARENT"] = $facette->PARENT;
                    $SAVEDATA["USERROLE_ID"] = $roleId;
                    $SAVEDATA["USER_RIGHT"] = 'STAFF_PERSONAL_INFORMATION_READ_RIGHT';
                    $SAVEDATA["RIGHT_ID"] = $facette->ID;

                    if (!self::checkAssignedUserRight($roleId, 'STAFF_PERSONAL_INFORMATION_READ_RIGHT')) {
                        self::dbAccess()->insert('t_user_right', $SAVEDATA);
                    }
                }
            }
        }

        protected static function checkAssignedUserRight($roleId, $rightId) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_user_right", array("C" => "COUNT(*)"));
            $SQL->where("USERROLE_ID = '" . $roleId . "'");

            if (is_numeric($rightId)) {
                $SQL->where("RIGHT_ID = '" . $rightId . "'");
            } else {
                $SQL->where("USER_RIGHT = '" . $rightId . "'");
                $SQL->limit(1);
            }

            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public static function jsonActionUserRight($params) {

            $roleId = isset($params["roleId"]) ? addText($params["roleId"]) : "";
            $rightId = isset($params["rightId"]) ? addText($params["rightId"]) : "";
            $checked = $params["checked"];

            $facette = self::findUserRightFromId($rightId);

            if ($checked == "false") {
                //Delete all...
                self::dbAccess()->delete('t_user_right', array(
                    "USERROLE_ID='" . $roleId . "'"
                    , "RIGHT_ID='" . $rightId . "'"
                    )
                );
                $msg = RECORD_WAS_REMOVED;
            } elseif ($checked == "true") {

                $msg = RECORD_WAS_ADDED;
                $SAVEDATA["USERROLE_ID"] = $roleId;
                $SAVEDATA["RIGHT_ID"] = $rightId;

                if ($facette) {

                    $parentObject = self::findUserRightFromId($facette->PARENT);

                    switch ($facette->USER_RIGHT) {
                        case "READ_RIGHT":
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_READ_RIGHT";
                            break;
                        case "EDIT_RIGHT":
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_EDIT_RIGHT";
                            break;
                        case "REMOVE_RIGHT":
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_REMOVE_RIGHT";
                            break;
                        case "EXECUTE_RIGHT":
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_EXECUTE_RIGHT";
                            break;
                        default:
                            $SAVEDATA["USER_RIGHT"] = $facette->USER_RIGHT;
                            break;
                    }
                    $SAVEDATA["PARENT"] = $facette->PARENT;
                    self::addAutoParentRight($roleId, $facette->PARENT);
                }

                if ($rightId) {
                    self::dbAccess()->insert('t_user_right', $SAVEDATA);
                    self::addAutoReadRight($roleId, $rightId);
                }
            }

            return array("success" => true, "msg" => $msg);
        }

        public static function findReadRightId($parentId) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_school_user_right", array("*"));
            $SQL->where("PARENT = '" . $parentId . "'");
            $SQL->where("USER_RIGHT = 'READ_RIGHT'");
            $SQL->where("OBJECT_TYPE = 'ITEM'");
            //error_log($SQL->__toString());
            $result = self::dbAminAccess()->fetchRow($SQL);
            return $result ? $result->ID : 0;
        }

        public static function addAutoParentRight($roleId, $parentId) {

            $facette = self::findUserRightFromId($parentId);
            if ($facette) {
                $SAVEDATA["PARENT"] = $facette->PARENT;
                $SAVEDATA["USERROLE_ID"] = $roleId;
                $SAVEDATA["USER_RIGHT"] = $facette->USER_RIGHT;
                $SAVEDATA["RIGHT_ID"] = $parentId;

                if (!self::checkAssignedUserRight($roleId, $parentId)) {
                    if ($parentId)
                        self::dbAccess()->insert('t_user_right', $SAVEDATA);
                }
            }
        }

        public static function addAutoReadRight($roleId, $rightId) {

            $insert = false;
            $facette = self::findUserRightFromId($rightId);

            if ($facette) {

                $parentObject = self::findUserRightFromId($facette->PARENT);

                if ($parentObject) {
                    $readRightId = self::findReadRightId($facette->PARENT);

                    //error_log($readRightId);
                    if ($readRightId) {
                        if (strpos($facette->USER_RIGHT, "EDIT_RIGHT") !== false) {
                            $insert = true;
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_READ_RIGHT";
                        } elseif (strpos($facette->USER_RIGHT, "REMOVE_RIGHT") !== false) {
                            $insert = true;
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_READ_RIGHT";
                        } elseif (strpos($facette->USER_RIGHT, "EXECUTE_RIGHT") !== false) {
                            $insert = true;
                            $SAVEDATA["USER_RIGHT"] = $parentObject->USER_RIGHT . "_READ_RIGHT";
                        }
                        $SAVEDATA["PARENT"] = $facette->PARENT;
                        $SAVEDATA["USERROLE_ID"] = $roleId;
                        $SAVEDATA["RIGHT_ID"] = $readRightId;

                        if (!self::checkAssignedUserRight($roleId, $readRightId)) {
                            if ($insert)
                                if ($readRightId)
                                    self::dbAccess()->insert('t_user_right', $SAVEDATA);
                        }
                    }
                }
            }
        }

    }

?>