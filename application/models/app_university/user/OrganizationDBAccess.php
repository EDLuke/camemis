<?php

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 27.02.2011
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'models/app_university/staff/StaffDBAccess.php';
    require_once 'models/app_university/SessionAccess.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class OrganizationDBAccess {
        const T_ORGANIZATION = "t_organization";
        const T_USER_ORGANIZATION = "t_user_organization";
        const T_STAFF = "t_staff";

        protected $data = array();
        protected $out = array();

        public function __construct() {

            $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
            $this->SELECT = $this->DB_ACCESS->select();
            $this->_TOSTRING = $this->SELECT->__toString();
        }

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
        
        public function getOrganizationDataFromId($Id) {

            $result = $this->findOrganizationFromId($Id);
            $data = array();
            if ($result) {
                $data["ID"] = $result->ID;
                $data["STATUS"] = $result->STATUS;
                $data["NAME"] = setShowText($result->NAME);
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            }

            return $data;
        }

        public function findOrganizationFromId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM " . self::T_ORGANIZATION . "";
            $SQL .= " WHERE";
            $SQL .= " ID = '" . $Id . "'";
            return self::dbAccess()->fetchRow($SQL);
        }

        public function allOrganizations($params) {

            $start = $params["start"] ? (int) $params["start"] : "0";
            $limit = $params["limit"] ? (int) $params["limit"] : "50";

            $entries = $this->getAllOrganizationQuery($params);
            $data = array();
            $i = 0;
            if ($entries)
                foreach ($entries as $value) {

                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["NAME"] = $value->NAME;
                    $data[$i]["STATUS"] = $value->STATUS;

                    $i++;
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

        public function getAllOrganizationsQuery($params) {

            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

            $SQL = "";
            $SQL .= " SELECT DISTINCT";
            $SQL .= " A.ID AS ID";
            $SQL .= " ,A.STATUS AS STATUS";
            $SQL .= " ,A.NAME AS NAME";
            $SQL .= " FROM " . self::T_ORGANIZATION . " AS A";
            $SQL .= " WHERE 1=1";

            if ($globalSearch) {
                $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
                $SQL .= " ) ";
            }

            $SQL .= " ORDER BY A.NAME";
            //echo $SQL;
            return self::dbAccess()->fetchAll($SQL);
        }

        public function loadOrganizationFromId($Id) {

            $result = $this->findOrganizationFromId($Id);

            if ($result) {
                $o = array(
                    "success" => true
                    , "data" => $this->getOrganizationDataFromId($Id)
                );
            } else {
                $o = array(
                    "success" => true
                    , "data" => array()
                );
            }
            return $o;
        }

        public function updateOrganization($params) {

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
            $SAVEDATA['NAME'] = addText($params["NAME"]);
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

            $ROW = self::dbAccess()->quoteInto('ID =?', $objectId);
            self::dbAccess()->update(self::T_ORGANIZATION, $SAVEDATA, $ROW);
            return array("success" => true);
        }

        public function addOrganization($params) {

            $name = isset($params["name"]) ? addText($params["name"]) : 0;

            if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                $SAVEDATA['STATUS'] = 1;
            }
            $SAVEDATA['NAME'] = addText($params["name"]);

            if ($name)
                self::dbAccess()->insert(self::T_ORGANIZATION, $SAVEDATA);

            return array("success" => true);
        }

        public function jsonTreeAllOrganizations($params) {

            $userId = isset($params["userId"]) ? addText($params["userId"]) : "";
            $result = $this->getAllOrganizationsQuery($params);

            $data = array();
            $i = 0;
            if ($result)
                foreach ($result as $value) {

                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);

                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['leaf'] = true;
                    if($userId){

                        if($this->loadUserOrganization($value->ID, $userId)){
                            $data[$i]['checked'] = true;
                        }else{
                            $data[$i]['checked'] = false;
                        }

                        $data[$i]['iconCls'] = "icon-application_form_magnify_link";  
                    }else{
                        switch ($value->STATUS) {
                            case 0:
                                $data[$i]['iconCls'] = "icon-red";
                                break;
                            case 1:
                                $data[$i]['iconCls'] = "icon-green";
                                break;
                        }
                    }
                    $i++;
            }

            return $data;
        }

        public function releaseOrganization($params) {

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

            $facette = $this->findOrganizationFromId($objectId);

            $status = $facette->STATUS;

            $SQL = "";
            $SQL .= "UPDATE ";
            $SQL .= " " . self::T_ORGANIZATION . "";
            $SQL .= " SET";

            switch ($status) {
                case 0:
                    $newStatus = 1;
                    $SQL .= " STATUS=1";
                    $STAFF_DATA["STATUS"] = 1;

                    break;
                case 1:
                    $newStatus = 0;
                    $SQL .= " STATUS=0";
                    $STAFF_DATA["STATUS"] = 0;

                    break;
            }

            $SQL .= " WHERE";
            $SQL .= " ID='" . $objectId . "'";
            self::dbAccess()->query($SQL);

            return array("success" => true, "status" => $newStatus);
        }

        public function removeOrganization($params) {

            $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            $check = $this->checkUserOrganization($removeId);

            if (!$check) {

                $this->deleteOrganizationFromId($removeId);
            }

            return array(
                "success" => true
            );
        }

        public function checkUserOrganization($Id) {

            $SQL = "SELECT COUNT(*) AS C";
            $SQL .= " FROM " . self::T_USER_ORGANIZATION . "";
            $SQL .= " WHERE";
            $SQL .= " ORGANIZATION_ID = '" . $Id . "'";

            $result = self::dbAccess()->fetchRow($SQL);

            return $result ? $result->C : 0;
        }

        public function loadUserOrganization($Id, $userId) {

            $SQL = "SELECT *";
            $SQL .= " FROM " . self::T_USER_ORGANIZATION . "";
            $SQL .= " WHERE";
            $SQL .= " ORGANIZATION_ID = '" . $Id . "'";
            $SQL .= " AND USER_ID = '" . $userId . "'";
            //echo $SQL;
            return self::dbAccess()->fetchRow($SQL);
        }

        public function deleteOrganizationFromId($Id) {
            self::dbAccess()->delete(self::T_ORGANIZATION, " ID='" . $Id . "'");
        }

        public function actionUserOrganization($params) {

            $Id = isset($params["Id"]) ? addText($params["Id"]) : "";
            $staffId = isset($params["setId"]) ? addText($params["setId"]) : "";
            $checked = isset($params["checked"]) ? addText($params["checked"]) : "";

            $msg = ACTION_SUCCESSFULLY_SAVED;

            if($checked){
                $SAVEDATA["ORGANIZATION_ID"] = $Id;
                $SAVEDATA["USER_ID"] = $staffId;
                self::dbAccess()->insert('t_user_organization', $SAVEDATA);
            }else{
                $condition = array("USER_ID = '" . $staffId . "'", "ORGANIZATION_ID = '" . $Id . "'");
                self::dbAccess()->delete('t_user_organization', $condition);
            }

            return array("success" => true, "msg"=>$msg);
        }

        public function allOrganizationsComboData() {

            $result = $this->getAllOrganizationsQuery(false);

            $data[0] = "[\"0\",\"[---]\"]";
            $i = 0;
            if ($result)
                foreach ($result as $value) {

                    $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                    $i++;
            }

            return "[" . implode(",", $data) . "]";
        }
    }

?>