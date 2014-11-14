<?php

    ///////////////////////////////////////////////////////////
    // @soda
    ///////////////////////////////////////////////////////////
    
    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class CAMEMISExcelRenderer{

        public $params = array();
        public $columns = array();
        
        static function getInstance() {
            static $camemis;
            if ($camemis == null) {
                $camemis = new CAMEMISExcelRenderer();
            }
            return $camemis;
        }

        public function __construct($params, $columns) {
            $this->params = $params;
            $this->columns = $columns;
            
            $STUDENT_DBACCESS = new StudentDBAccess();
            $STAFF_DBACCESS = new StaffDBAccess();
            $TRAININGSUBJECT_DBACCESS = new TrainingSubjectDBAccess();
            
        }
    
        public static function dbAccess() {
            return Zend_Registry::get('DB_ACCESS');
        }

        public static function dbSelectAccess() {
            return self::dbAccess()->select();
        }
        
        public function getSchoolInformation(){
            $schoolObject = $exportToExcel->getSchoolInfo();
            $SchoolName = $schoolObject->NAME;
            $SchoolAdress = $schoolObject->ADDRESS;
            $schoolInfo = $SchoolName . "\n" . $SchoolAdress;
        } 
        
        public function getDataToExcel(){
            $endCharExcel = "H";
            $filename = "List of Student";
            $info['SCHOOL_INFO'] = $schoolInfo;
            $exportToExcel->excelHeader($info, 1, $endCharExcel);
            $exportToExcel->excelFooter();
            $exportToExcel->save($filename);
        }
        
        public function viewExcel(){
            
            $exportToExcel = new CamemisExportToExcel();
            
            $PARAMETERS = $this->getParameters();   

            $SCHOOL_INFORMATION = $this->getSchoolInformation();

            switch ($this->type) {
                case "EXPORT_STUDENT":
                    $results = StudentAcademicDBAccess::getSQLStudentEnrollment($params);
                    $filds = array("NA", "STUDENT_CODE", "LASTNAME", "FIRSTNAME", "GENDER", "DATE_BIRTH", "MOBIL_PHONE", "EMAIL");
                    $showfilds = array("N°", CODE_ID, LASTNAME, FIRSTNAME, GENDER, DATE_BIRTH, PHONE, EMAIL);
                    break;
                case "EXPORT_TEACHER":
                    $teacher_export = new StaffDBAccess();
                    $results = $teacher_export->jsonAssignedTeachersByClass($params, false);
                    $filds = array("NA", "TEACHER_NAME", "SUBJECT", "CLASS", "TERM", "PHONE", "EMAIL");
                    $showfilds = array("N°", TEACHER, SUBJECT, GRADE_CLASS, TERM, PHONE, EMAIL);
                    break;
                case "EXPORT_STUDENT_YEAR":
                    $student_export_year = new StudentDBAccess();
                    $results = $student_export_year->jsonUnassignedStudents($params);
                    $filds = array("NA", "CODE", "LASTNAME", "FIRSTNAME", "ACADEMIC_TYPE", "GENDER", "DATE_BIRTH", "TRANSFER", "CLASS");
                    $showfilds = array("N°", CODE_ID, LASTNAME, FIRSTNAME, ACADEMIC_TYPE, GENDER, DATE_BIRTH, TRANSFER, GRADE_CLASS);
                    break;
                case "EXPORT_TEACHER_YEAR":
                    $teacher_export_year = new StaffDBAccess();
                    $results = $teacher_export_year->jsonAssignedTeacherANDSchoolyear($params);
                    $filds = array("NA", "CODE", "TEACHER");
                    $showfilds = array("N°", CODE_ID, TEACHER);
                    break;
                case "EXPORT_STUDENT_TRAINNING_YEAR":
                    $results = StudentTrainingDBAccess::jsonStudentTraining($params);
                    $filds = array("NA", "CODE", "STUDENT", "GENDER", "DATE_BIRTH", "PHONE", "EMAIL");
                    $showfilds = array("N°", CODE_ID, FULL_NAME, GENDER, DATE_BIRTH, PHONE, EMAIL);
                    break;
                case "EXPORT_TEACHER_TRAINING_YEAR": 
                    $teacher_exportassign_year = new TrainingSubjectDBAccess();
                    $results = $teacher_exportassign_year->jsonTeacherTraining($params);
                    print_r($results);
                    exit;
                    break;
            }

            $exportToExcel->objExcel($results, $filds, $showfilds, 3);
            $EXPORT_DATA = $this->getDataToExcel();
            
        }
        
    }

    $displayExcelObject = new CAMEMISExcelRenderer($params, $columns);
    $displayExcelObject->viewExcel();
        
?>