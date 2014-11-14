<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class AdminJsModulURL {

    protected $utiles = null;

    public function __construct() {
        $this->utiles = Utiles::getInstance();
    }

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new AdminJsModulURL();
        }
        return $me;
    }

    public function loadCamemisModulURL() {

        $str = "";
        $str .= $this->getModulURL();
        return $str;
    }

    protected function getModulURL() {
        $str = "";
        $str .= "<script>";
        $str .= "var URL_JSONLOAD_REPORTING = \"" . $this->utiles->buildURL('reporting/jsonload', false, true) . "\";";
        $str .= "var URL_JSONSAVE_REPORTING = \"" . $this->utiles->buildURL('reporting/jsonsave', false, true) . "\";";
        $str .= "var URL_JSONTREE_REPORTING = \"" . $this->utiles->buildURL('reporting/jsontree', false, true) . "\";";
        $str .= "var URL_JSONLIST_REPORTING = \"" . $this->utiles->buildURL('reporting/jsonlist', false, true) . "\";";
        $str .= "var URL_JSONPDF_REPORTING = \"" . $this->utiles->buildURL('reporting/jsonpdf', false, true) . "\";";

        $str .= "var URL_JSONLOAD_MAIN = \"" . $this->utiles->buildURL('main/jsonload', false, true) . "\";";
        $str .= "var URL_JSONSAVE_MAIN = \"" . $this->utiles->buildURL('main/jsonsave', false, true) . "\";";
        $str .= "var URL_JSONTREE_MAIN = \"" . $this->utiles->buildURL('main/jsontree', false, true) . "\";";
        $str .= "var URL_JSONLIST_MAIN = \"" . $this->utiles->buildURL('main/jsonlist', false, true) . "\";";
        $str .= "var URL_JSONPDF_MAIN = \"" . $this->utiles->buildURL('main/jsonpdf', false, true) . "\";";
        
        $str .= "var URL_JSONLOAD_REMOTE_DATASET = \"" . $this->utiles->remotedURL('dataset/remote', false, true) . "\";";
        $str .= "</script>";

        return $str;
    }
}

?>