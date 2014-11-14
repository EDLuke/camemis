<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 11.05.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

class CAMEMISLoginLanguage {

    static function getLoginLanguage($tokenId) {

        if ($tokenId) {

            $DB_ACCESS = Zend_Registry::get('DB_ACCESS');

            $SQL_BENUTZER = "SELECT A.SYSTEM_LANGUAGE AS SYSTEM_LANGUAGE";
            $SQL_BENUTZER .= " FROM t_staff AS A";
            $SQL_BENUTZER .= " LEFT JOIN t_sessions AS B ON B.MEMBERS_ID=A.ID";
            $SQL_BENUTZER .= " WHERE";
            $SQL_BENUTZER .= " B.ID = '" . $tokenId . "'";
            $RESULT_BENUTZER = $DB_ACCESS->fetchRow($SQL_BENUTZER);

            $SQL_ELTERN = "SELECT A.SYSTEM_LANGUAGE AS SYSTEM_LANGUAGE";
            $SQL_ELTERN .= " FROM t_student AS A";
            $SQL_ELTERN .= " LEFT JOIN t_sessions AS B ON B.MEMBERS_ID=A.ID";
            $SQL_ELTERN .= " WHERE";
            $SQL_ELTERN .= " B.ID = '" . $tokenId . "'";
            $RESULT_ELTERN = $DB_ACCESS->fetchRow($SQL_ELTERN);

            if ($RESULT_BENUTZER) {
                if ($RESULT_BENUTZER->SYSTEM_LANGUAGE) {
                    return $RESULT_BENUTZER->SYSTEM_LANGUAGE;
                } else {
                    return strtoupper(Zend_Registry::get('SYSTEM_LANGUAGE'));
                }
            }

            if ($RESULT_ELTERN) {
                if ($RESULT_ELTERN->SYSTEM_LANGUAGE) {
                    return $RESULT_ELTERN->SYSTEM_LANGUAGE;
                } else {
                    return strtoupper(Zend_Registry::get('SYSTEM_LANGUAGE'));
                }
            }

            if (!$RESULT_BENUTZER && !$RESULT_ELTERN) {
                return strtoupper(Zend_Registry::get('SYSTEM_LANGUAGE'));
            }
        }
    }

}

?>