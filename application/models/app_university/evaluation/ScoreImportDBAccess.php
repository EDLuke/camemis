<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 07.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once('excel/excel_reader2.php');

require_once 'models/app_university/evaluation/default/StudentAssignmentDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/assignment/AssignmentDBAccess.php';
require_once 'models/app_university/subject/TrainingSubjectDBAccess.php';
require_once 'models/app_university/training/StudentTrainingDBAccess.php';

class ScoreImportDBAccess extends StudentAssignmentDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new ScoreImportDBAccess();
        }
        return $me;
    }

    public function importassignmentXLS($params) {

        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($_FILES["xlsfile"]['tmp_name']);
        $studentcode = new StudentDBAccess();
        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {
            $STUDENT_CODE = isset($xls->sheets[0]['cells'][$i + 3][2]) ? $xls->sheets[0]['cells'][$i + 3][2] : "";
            $SCORE = isset($xls->sheets[0]['cells'][$i + 3][4]) ? $xls->sheets[0]['cells'][$i + 3][4] : "";
            $COMMENT = isset($xls->sheets[0]['cells'][$i + 3][5]) ? $xls->sheets[0]['cells'][$i + 3][5] : "";
            if ($STUDENT_CODE) {
                $students = $studentcode->findStudentFromCodeId($STUDENT_CODE);
                $params['id'] = $students->ID;
                $params['newValue'] = str_replace(",", ".", $SCORE);
                $params['field'] = 'SCORE';

                $this->jsonActionTeacherScoreEnter($params);
                if ($this->jsonActionTeacherScoreEnter($params)) {
                    $params['studentId'] = $students->ID;
                    $params['name'] = $COMMENT;
                    $this->jsonActionTeacherAssignmentComment($params);
                }
            }
        }
    }

    public function importassignmenttrainingXLS($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($_FILES["xlsfile"]['tmp_name']);
        $studentcode = new StudentDBAccess();
        $ENTRIES_ASSIGNMENT = TrainingSubjectDBAccess::getTrainingAssignments($subjectId, $objectId);

        for ($iCol = 1; $iCol <= $xls->sheets[0]['numCols']; $iCol++) {
            $field = $xls->sheets[0]['cells'][3][$iCol];
            foreach ($ENTRIES_ASSIGNMENT as $value) {
                if ($field == $value->NAME) {
                    $col_subject[$value->NAME] = $iCol;
                }
            }
        }

        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {
            $STUDENT_CODE = isset($xls->sheets[0]['cells'][$i + 3][2]) ? $xls->sheets[0]['cells'][$i + 3][2] : "";
            if ($STUDENT_CODE) {
                $students = $studentcode->findStudentFromCodeId($STUDENT_CODE);
                $STUDENT_TRAINING = StudentTrainingDBAccess::sqlStudentTrainingRow($objectId, $students->ID);
                $params['id'] = $STUDENT_TRAINING->OBJECT_ID;
                foreach ($ENTRIES_ASSIGNMENT as $subject) {
                    $params['field'] = "ASSIGNMENT_" . $subject->ID;
                    $SCORE = isset($xls->sheets[0]['cells'][$i + 3][$col_subject[$subject->NAME]]) ? $xls->sheets[0]['cells'][$i + 3][$col_subject[$subject->NAME]] : "";
                    $params['newValue'] = str_replace(",", ".", $SCORE);
                    StudentTrainingDBAccess::actionTrainingStudentAssignment($params);
                }
            }
        }
    }

}

?>