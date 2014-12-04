<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';
require_once 'models/app_admin/AdminDatabaseDBAccess.php';

class AdminLocalDBAccess {

    function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->DB_DATABASE = new AdminDatabaseDBAccess();
    }

    public function allLocalsQuery($params) {

        $parentId = isset($params["node"]) ? addText($params["node"]) : "0";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_local AS A";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '%" . $globalSearch . "%')";
            $SQL .= " ) ";
        }
        $SQL .= " AND A.PARENT='" . $parentId . "'";
        $SQL .= " AND A.OBJECT_TYPE='FOLDER'";
        $SQL .= " ORDER BY A.SORTKEY ASC";

        //echo $SQL;
        $result = $this->DB_ACCESS->fetchAll($SQL);

        return $result;
    }

    public function getCountSchoolByLocal($local,$template) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_customer";
        $SQL .= " WHERE";
        
        if($local && $template){
            $SQL .= " LOCAL = '" . $local . "'";
            $SQL .= " AND SYSTEM_TEMPLATE = '" . $template . "'";
        }else{
            $SQL .= " LOCAL = '0'";
            $SQL .= " AND SYSTEM_TEMPLATE = '0'";
        }
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result?$result->C:"?";
    }
    
    public function findLocalFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_local";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result;
    }

    public function jsonTreeAllLocals($params) {

        $node = $params["node"];

        if (preg_match("/_/",$node)) {
            $findme = true;
        } else {
            $findme = false;
        }

        if ($node == 0) {
            $result = $this->allLocalsQuery($params);
        } else {
            if ($findme) {

                $explode = explode("_", $node);

                $localId = $explode[0];
                $template = $explode[1];

                $SQL = "";
                $SQL .= " SELECT *";
                $SQL .= " FROM t_customer AS A";
                $SQL .= " WHERE 1=1";
                $SQL .= " AND ACTIVE=1";
                $SQL .= " AND A.LOCAL='" . $localId . "'";
                $SQL .= " AND A.SYSTEM_TEMPLATE='" . $template . "'";
                $result = $this->DB_ACCESS->fetchAll($SQL);

                //echo $SQL;
            } else {
                $SQL = "";
                $SQL .= " SELECT *";
                $SQL .= " FROM t_customer AS A";
                $SQL .= " WHERE 1=1";
                $SQL .= " AND ACTIVE=1";
                $SQL .= " AND A.LOCAL='" . $node . "'";
                $SQL .= " GROUP BY SYSTEM_TEMPLATE";
                $result = $this->DB_ACCESS->fetchAll($SQL);
            }
        }

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {
                if ($node == 0) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-bricks";
                    $data[$i]['cls'] = "nodeTextBold";
                } else {

                    if ($findme) {

                        $data[$i]['id'] = $value->GUID;
                        $data[$i]['text'] = stripslashes($value->URL);
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['leaf'] = true;
                    } else {
                        $data[$i]['id'] = "" . $value->LOCAL . "_" . $value->SYSTEM_TEMPLATE . "";
                        
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['leaf'] = false;
                        
                        switch ($value->SYSTEM_TEMPLATE) {
                            case 1:
                                $data[$i]['text'] = "Primary-School (".$this->getCountSchoolByLocal($value->LOCAL,1).")";
                                $data[$i]['iconCls'] = "icon-star";
                                break;
                            case 2:
                                $data[$i]['text'] = "Secondary-School (".$this->getCountSchoolByLocal($value->LOCAL,2).")";
                                $data[$i]['iconCls'] = "icon-star_blue";
                                break;
                            case 3:
                                $data[$i]['text'] = "High-School (".$this->getCountSchoolByLocal($value->LOCAL,3).")";
                                $data[$i]['iconCls'] = "icon-star_green";
                                break;
                            case 4:
                                $data[$i]['text'] = "Primary+Secondary-School (".$this->getCountSchoolByLocal($value->LOCAL,4).")";
                                $data[$i]['iconCls'] = "icon-star_grey";
                                break;
                            case 5:
                                $data[$i]['text'] = "Secondary+High-School (".$this->getCountSchoolByLocal($value->LOCAL,5).")";
                                $data[$i]['iconCls'] = "icon-star_grey";
                                break;
                            case 6:
                                $data[$i]['text'] = "Prymary+Secondary+High-School (".$this->getCountSchoolByLocal($value->LOCAL,6).")";
                                $data[$i]['iconCls'] = "icon-star_red";
                                break;
                            default:
                                $data[$i]['text'] = "no assignment (".$this->getCountSchoolByLocal(false,false).")";
                                $data[$i]['iconCls'] = "icon-star_red";
                                break;
                        }
                    }
                }

                $i++;
            }
        return $data;
    }

}

?>