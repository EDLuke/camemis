<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 30.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'MyConfig.php';
require_once 'AdminUserDBAccess.php';

class AdminUserAuth {

    const SOCHEATA_KENNUNG = "ab52f83d57746e65f1b03c08b12273a1";

    public static function identify() {

        $registry = Zend_Registry::getInstance();

        if (isset($registry["SESSIONID"])) {

            $DB_USER = new AdminUserDBAccess(false, $registry["SESSIONID"]);
            $facette = $DB_USER->getUserBySessionId();

            if ($facette) {
                if (MyConfig::SOCHEATA_KENNUNG == $facette->ID) {
                    Zend_Registry::set('PROVIDER', true);
                } else {
                    Zend_Registry::set('PROVIDER', false);
                }
            }
        } else {
            exit("CAMEMIS: Access denied");
        }

        $isRun = $DB_USER->checkUserConstraints();

        if ($isRun && $facette->ID) {
            return true;
        } else {
            return false;
        }
    }

    protected function isSocheataAnmelden($value) {

        $check = md5($value . "-D99A6718-9D2A-8538-8610-E048177BECD5");

        if (self::SOCHEATA_KENNUNG == $check) {
            return true;
        } else {
            return false;
        }
    }

    public static function loginDialog() {
        //Your session has expired, please log in again.
        $js = "
            <script>
            window.location.href='/expired';
            </script>
        ";

        print$js;
    }

    static function getACLValue() {

        $key = null;
        $registry = Zend_Registry::getInstance();
        $SESSION_OBJECT = isset($registry["SESSION"]) ? $registry["SESSION"] : null;

        if ($SESSION_OBJECT) {
            $key = $SESSION_OBJECT->KEY;
        }
        
        $USER_PERMISSIONS = array();

        $USER_PERMISSIONS["SETTINGS"] = false;
        $USER_PERMISSIONS["LOCAL"] = false;
        $USER_PERMISSIONS["CAMEMIS_HELP"] = false;

        switch ($key) {
            case "ab52f83d57746e65f1b03c08b12273a1":
                $USER_PERMISSIONS["SETTINGS"] = true;
                $USER_PERMISSIONS["LOCAL"] = true;
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;
            case "922fc023b6773bff30d795cb0ceef228":
                $USER_PERMISSIONS["SETTINGS"] = false;
                $USER_PERMISSIONS["LOCAL"] = false;
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;
            case "9f310171e75044a2acd51218e5ac6637":
                $USER_PERMISSIONS["SETTINGS"] = false;
                $USER_PERMISSIONS["LOCAL"] = false;
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;

            case "3719eeccb85bef9164b5aa9afc7091c1":
                $USER_PERMISSIONS["SETTINGS"] = false;
                $USER_PERMISSIONS["LOCAL"] = false;
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;

            case "e28b62065d86cbff5eb478e97c7b2c2b":
                $USER_PERMISSIONS["SETTINGS"] = false;
                $USER_PERMISSIONS["LOCAL"] = false;
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;
            case "d2b6e666fe09a7604b7d2efd1e4335b2":
                $USER_PERMISSIONS["CAMEMIS_HELP"] = true;
                break;
        }

        return $USER_PERMISSIONS;
    }

    static function actionPermint($httpRequest, $permitValue) {

        $data = self::getACLValue();

        if ($permitValue) {

            if (isset($data["" . $permitValue . ""])) {
                if (!$data["" . $permitValue . ""]) {
                    $httpRequest->setControllerName('error');
                    $httpRequest->setActionName('expired');
                    $httpRequest->setDispatched(false);
                }
            } else {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('expired');
                $httpRequest->setDispatched(false);
            }
        } else {
            $httpRequest->setControllerName('error');
            $httpRequest->setActionName('expired');
            $httpRequest->setDispatched(false);
        }
    }

}

?>