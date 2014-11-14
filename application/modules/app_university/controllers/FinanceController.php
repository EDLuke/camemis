<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 06.08.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'models/UserAuth.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/app_university/finance/FinanceDescriptionDBAccess.php';
require_once 'models/app_university/finance/IncomeDBAccess.php';
require_once 'models/app_university/finance/ExpenseDBAccess.php';
require_once 'models/app_university/finance/IncomeCategoryDBAccess.php';
require_once 'models/app_university/finance/ExpenseCategoryDBAccess.php';
require_once 'models/app_university/finance/StaffFeePaymentDBAccess.php';
require_once 'models/app_university/finance/TaxDBAccess.php';
require_once 'models/app_university/finance/FeeDBAccess.php';
require_once 'models/app_university/finance/FeeOptionDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/finance/StudentPaymentSettingDBAccess.php';
require_once 'models/app_university/finance/StudentFeePrepaidDBAccess.php'; //@veasna
require_once 'models/app_university/academic/AcademicLevelDBAccess.php'; //@veasna

class FinanceController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->fee = null;
        $this->paymentId = null;
        $this->objectId = null;
        $this->schoolyear = null; //@veasna

        if ($this->_getParam('schoolyear'))
            $this->schoolyear = $this->_getParam('schoolyear');

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        if ($this->_getParam('paymentId'))
            $this->paymentId = $this->_getParam('paymentId');

        if ($this->_getParam('fee'))
            $this->fee = $this->_getParam('fee');
    }

    public function indexAction() {
        
    }

    public function incomeAction() {

        $this->_helper->viewRenderer("income/index");
    }

    public function expenseAction() {

        $this->_helper->viewRenderer("expense/index");
    }

    public function feeAction() {

        $this->_helper->viewRenderer("fee/index");
    }

    public function studentmainAction() {

        $this->_helper->viewRenderer("cash/student/index");
    }

    public function staffmainAction() {

        $this->_helper->viewRenderer("cash/staff/index");
    }

    public function feeenrollmentAction() {

        $this->_helper->viewRenderer("fee/enrollment/index");
    }

    public function feeservicesAction() {

        $this->_helper->viewRenderer("fee/services/index");
    }

    public function feecourseAction() {

        $this->_helper->viewRenderer("fee/course/index");
    }

    public function incomecategoryAction() {

        $this->_helper->viewRenderer("category/income/index");
    }

    public function expensecategoryAction() {

        $this->_helper->viewRenderer("category/expense/index");
    }

    public function settingAction() {

        $this->_helper->viewRenderer("setting/index");
    }

    public function descriptionAction() {

        $this->_helper->viewRenderer("description/index");
    }

    public function showdesciptionAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = FinanceDescriptionDBAccess::findObjectFromId($this->objectId);
        $this->_helper->viewRenderer("description/show");
    }

    public function showincomecategoryAction() {

        $this->view->objectId = $this->objectId;
        $this->view->check = IncomeCategoryDBAccess::checkUsedIncomeCat($this->objectId);//@veasna
        $this->view->facette = IncomeCategoryDBAccess::findObjectFromId($this->objectId);
        $this->_helper->viewRenderer("category/income/show");
    }

    public function showexpensecategoryAction() {

        $this->view->objectId = $this->objectId;
        $this->view->check =  ExpenseCategoryDBAccess::checkUsedExpenseCat($this->objectId);
        $this->view->facette = ExpenseCategoryDBAccess::findObjectFromId($this->objectId);
        $this->_helper->viewRenderer("category/expense/show");
    }
    
    //////////////
    public function studentprepaidAction(){
        
        $this->_helper->viewRenderer("cash/student/studentprepaid/listgeneral");    
    }
    
    public function studentprepaidgeneralAction(){
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/student/studentprepaid/listgeneral");    
    }
    public function studentprepaidtrainingAction(){
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/student/studentprepaid/listtraining");    
    }
    
    public function showprepaidAction(){
        $this->view->objectId = $this->objectId;
        $this->view->facette=StudentFeePrepaidDBAccess::findFeePrePaymentById($this->objectId);
        $this->view->campusId= ($this->_getParam('campusId'))?$this->_getParam('campusId'):'';
        $this->_helper->viewRenderer("cash/student/studentprepaid/showgeneral");
            
    }
    
    public function showprepaidtrainingAction(){
        $this->view->objectId = $this->objectId;
        $this->view->facette=StudentFeePrepaidDBAccess::findFeePrePaymentById($this->objectId);
        $this->view->program= ($this->_getParam('program'))?$this->_getParam('program'):'';
        $this->_helper->viewRenderer("cash/student/studentprepaid/showtraining");
            
    }
    /////////////

    ////////////////////////////////////////////////////////////////////////////
    //TAX
    ////////////////////////////////////////////////////////////////////////////
    public function showtaxAction() {

        $this->view->objectId = $this->objectId;
        $this->view->check = TaxDBAccess::checkUsedTax($this->objectId); 
        $this->view->facette = TaxDBAccess::findObjectFromId($this->objectId);
        $this->_helper->viewRenderer("category/tax/show");
    }

    public function taxAction() {

        $this->_helper->viewRenderer("category/tax/index");
    }

    ////////////////////////////////////////////////////////////////////////////
    //EXPENSE...
    ////////////////////////////////////////////////////////////////////////////
    public function showexpenseAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = ExpenseDBAccess::findExpenseFromId($this->objectId);
        $this->_helper->viewRenderer("expense/show");
    }

    ////////////////////////////////////////////////////////////////////////////
    //INCOME...
    ////////////////////////////////////////////////////////////////////////////
    public function showincomeAction() {

        $this->view->objectId = $this->objectId;
        $income=IncomeDBAccess::findIncomeFromId($this->objectId);
        $this->view->facette =$income;
        if($income) 
        $this->view->tax=IncomeCategoryDBAccess::findAllTaxByCategory($income->INCOME_CATEGORY);
        $this->_helper->viewRenderer("income/show");
    }

    ////////////////////////////////////////////////////////////////////////////
    //FEE...    
    ////////////////////////////////////////////////////////////////////////////
    //@veasna
    public function showfeegradeAction() {
        $this->view->objectId = $this->objectId;
        $facette = FeeDBAccess::findFeeFromId($this->objectId);
        $this->view->facette = $facette;

        $this->view->URL_STUDENT_FEE_GRADE = $this->UTILES->buildURL(
                'finance/studentfeegrade', array(
            "objectId" => $this->objectId
            , "schoolyear" => $this->view->facette->SCHOOLYEAR
        ));

        $this->view->URL_STUDENT_FEE_GRADE_REMINDER = $this->UTILES->buildURL(
                'finance/studentfeegradereminder', array("objectId" => $this->objectId, "schoolyear" => $this->view->facette->SCHOOLYEAR));

        if ($this->objectId != 'new') {
            $this->_helper->viewRenderer("fee/enrollmentsetting/feegrade");
        } else {
            $this->_helper->viewRenderer("fee/enrollment/show");
        }
    }

    public function studentfeegradeAction() {
        $this->view->schoolyear = $this->schoolyear;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("fee/enrollmentsetting/liststudentsfeegrade");
    }

    public function studentfeegradereminderAction() {
        $this->view->schoolyear = $this->schoolyear;
        $this->view->objectId = $this->objectId;
        $this->view->URL_STUDENT_SEND_REMINDER = $this->UTILES->buildURL(
                'finance/studentfeesendreminder', array());
        $this->_helper->viewRenderer("fee/enrollmentsetting/liststudentsreminder");
    }

    public function studentfeesendreminderAction() {
        $objectName = $this->_getParam('objectName');
        $this->view->str_recipient_id = $this->_getParam('objectId');
        $this->view->feeId = $this->_getParam('fee');
        $objectNameArray = explode(",", $objectName);
        $this->view->objectName = implode("\\n", $objectNameArray);

        $this->_helper->viewRenderer("fee/enrollmentsetting/sendremindstudent");
    }

    //

    public function showfeeenrollmentAction() {
        $this->view->objectId = $this->objectId;
        
        $this->view->incomeFee = IncomeDBAccess::findIncomeFromFeeId($this->objectId);//@veasna
        $this->view->facette = FeeDBAccess::findFeeFromId($this->objectId);
        $this->_helper->viewRenderer("fee/enrollment/show");
    }

    public function showfeeservicesAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = FeeDBAccess::findFeeFromId($this->objectId);
        $this->_helper->viewRenderer("fee/services/show");
    }

    public function searchfeeenrollmentAction() {
        $this->_helper->viewRenderer("fee/enrollment/searchresult");
    }

    public function searchfeeservicesAction() {
        $this->_helper->viewRenderer("fee/services/searchresult");
    }

    public function searchfeecourseAction() {
        $this->_helper->viewRenderer("fee/course/searchresult");
    }

    //@veasna
    public function showfeetrainingAction() {
        $this->view->objectId = $this->objectId;
        $facette = FeeDBAccess::findFeeFromId($this->objectId);
        $this->view->facette = $facette;

        $this->view->URL_STUDENT_FEE_TRAINING = $this->UTILES->buildURL(
                'finance/studentfeetraining', array("objectId" => $this->objectId));

        $this->view->URL_STUDENT_FEE_TRAINING_REMINDER = $this->UTILES->buildURL(
                'finance/studentfeetrainingereminder', array("objectId" => $this->objectId));

        if ($this->objectId != 'new') {
            $this->_helper->viewRenderer("fee/coursesetting/feetraining");
        } else {
            $this->_helper->viewRenderer("fee/course/show");
        }
    }

    public function studentfeetrainingAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("fee/coursesetting/liststudentsfeetraining");
    }

    public function studentfeetrainingereminderAction() {

        $this->view->objectId = $this->objectId;
        $this->view->URL_STUDENT_SEND_REMINDER = $this->UTILES->buildURL(
                'finance/studentfeesendreminder', array());
        $this->_helper->viewRenderer("fee/coursesetting/liststudentstraingreminder");
    }

    //

    public function showfeecourseAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = FeeDBAccess::findFeeFromId($this->objectId);
        $this->_helper->viewRenderer("fee/course/show");
    }

    public function feesearchcoursection() {

        $this->_helper->viewRenderer("fee/course/searchresult");
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT

    public function studentservicesAction() {
        //objectId = StudentId
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/student/studentservices");
    }

    public function deletepaymentsAction() {

        $this->view->objectId = $this->objectId;
        $this->view->fee = $this->fee;
        $this->view->type=$this->_getParam('type');
        $this->_helper->viewRenderer("cash/student/payment/delete");
    }

    public function studentaccounthistoryAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);

        $this->view->URL_STUDENT_INVOICES_GENERAL = $this->UTILES->buildURL(
                'finance/studentinvoicesgeneral', array(
            "objectId" => $this->objectId
                )
        );

        $this->view->URL_STUDENT_INVOICES_SERVICES_EDU = $this->UTILES->buildURL(
                'finance/studentinvoicesservices', array(
            "objectId" => $this->objectId
                )
        );

        $this->view->URL_STUDENT_INVOICES_SERVICES_TRAINING = $this->UTILES->buildURL(
                'finance/studentinvoicesservicestraining', array(
            "objectId" => $this->objectId
            , "services" => '1'
                )
        );

        $this->view->URL_STUDENT_INVOICES_TRAINING = $this->UTILES->buildURL(
                'finance/studentinvoicestraining', array(
            "objectId" => $this->objectId
            , "services" => '0'
                )
        );

        $this->view->URL_STUDENT_PAYMENTS = $this->UTILES->buildURL(
                'finance/studentpayments', array(
            "objectId" => $this->objectId
                )
        );

        $this->_helper->viewRenderer("cash/student/history");
    }

    public function studentinvoicesgeneralAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);
        $this->_helper->viewRenderer("cash/student/invoices/general");
    }

    public function studentinvoicesservicesAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);
        $this->_helper->viewRenderer("cash/student/invoices/service");
    }

    public function studentinvoicesservicestrainingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);
        $this->_helper->viewRenderer("cash/student/invoices/servicetraining");
    }

    public function studentinvoicestrainingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);
        $this->_helper->viewRenderer("cash/student/invoices/training");
    }

    public function studentpaymentsAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = StudentDBAccess::findStudentFromId($this->objectId);
        $this->_helper->viewRenderer("cash/student/payments");
    }

    public function studentpaymentgeneralAction() {
        //$this->view->objectId = $this->objectId;
        //$this->_helper->viewRenderer("cash/student/payment/general");
        $this->view->objectId = $this->objectId;
        $object=StudentFeePrepaidDBAccess::findStudentPrePayment($this->objectId);
        $this->view->studentPrePaymentId=$object?$object->ID:'';
        $this->view->feePrePaymentId=$object?$object->FEE_PREPAYMENT_ID:'';
        $payment_type=($this->_getParam('payment_type'))?$this->_getParam('payment_type'):'';
       
        if($payment_type=='PRE_PAYMENT'){
            $this->_helper->viewRenderer("cash/student/studentprepaid/studentprepaymentgeneral");
            
        }else{
            if($object){
                $this->_helper->viewRenderer("cash/student/studentprepaid/studentprepaymentgeneral");   
            }else{
                $this->_helper->viewRenderer("cash/student/payment/general");
            }
        }
    }

    public function studentpaymentserviceAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/student/payment/service");
    }

    public function studentpaymenttrainingAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/student/payment/training");
    }

    ////////////////////////////////////////////////////////////////////////////
    //STAFF
    public function staffaccounthistoryAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = StaffDBAccess::findStaffFromId($this->objectId);
        $this->_helper->viewRenderer("cash/staff/history");
    }

    public function staffinvoicesAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/staff/invoices");
    }

    public function staffpaymentsessionAction() {
        $this->view->objectId = $this->objectId;
        $this->view->paymentId = $this->paymentId;
        $this->_helper->viewRenderer("cash/staff/paymentsession");
    }

    public function staffpaymentsalaryAction() {
        $this->view->objectId = $this->objectId;
        $this->view->paymentId = $this->paymentId;
        $this->_helper->viewRenderer("cash/staff/paymentsalary");
    }

    public function staffaccountlogsAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/staff/logs");
    }

    public function financedescriptionAction() {

        $this->_helper->viewRenderer("setting/description/list");
    }

    public function showdescriptionAction() {
        $this->view->objectId = $this->objectId;   
        $this->view->check=FinanceDescriptionDBAccess::checkUsedFinanceDescription($this->objectId);
        $this->view->facette = FinanceDescriptionDBAccess::findObjectFromId($this->objectId);
        $this->_helper->viewRenderer("setting/description/show");
    }

    public function printpaidAction() {
        //PaymentId
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/print/paid");
    }

    public function printfeeAction() {
        //PaymentId
        $this->view->fee = $this->fee;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/print/fee");
    }
    
    //@veasna
    public function printprepayfeeAction() {
        //PaymentId
        $this->view->fee = $this->fee;
        $this->view->objectId = $this->objectId;
        $this->view->fee_prepay=($this->_getParam('feeprepay'))?$this->_getParam('feeprepay'):'';
        $this->_helper->viewRenderer("cash/print/prepayfee");
    }
    //

    public function printfeestaffAction() {
        //PaymentId
        $this->view->paymentId = $this->paymentId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/print/feestaff");
    }

    public function printinvoicesAction() {
        //StudentId
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("cash/print/invoices");
    }

    ////////////////////////////////////////////////////////////////////////////
    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "searchCashStudent":
                $jsondata = StudentFeeDBAccess::searchCashStudent($this->REQUEST->getPost());
                break;
            case "loadIncome":
                $jsondata = IncomeDBAccess::loadIncome($this->REQUEST->getPost('objectId'));
                break;

            case "loadExpense":
                $jsondata = ExpenseDBAccess::loadExpense($this->REQUEST->getPost('objectId'));
                break;

            case "jsonSearchIncome":
                $jsondata = IncomeDBAccess::jsonSearchIncome($this->REQUEST->getPost());
                break;

            case "jsonSearchExpense":
                $jsondata = ExpenseDBAccess::jsonSearchExpense($this->REQUEST->getPost());
                break;
            ////////////////////////////////////////////////////////////////////
            //Fee
            ////////////////////////////////////////////////////////////////////
            case "jsonSearchFee":
                $jsondata = FeeDBAccess::jsonSearchFee($this->REQUEST->getPost());
                break;

            case "jsonLoadFee":
                $jsondata = FeeDBAccess::jsonLoadFee($this->REQUEST->getPost('objectId'));
                break;

            case "jsonListStudentInvoicesGeneral":
                $jsondata = StudentFeeDBAccess::jsonListStudentInvoicesGeneral($this->REQUEST->getPost());
                break;

            case "jsonListStudentInvoicesTraining":
                $jsondata = StudentFeeDBAccess::jsonListStudentInvoicesTraining($this->REQUEST->getPost());
                break;

            case "jsonListStudentPayments":
                $jsondata = StudentFeeDBAccess::jsonListStudentPayments($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentPayment":
                $jsondata = StudentFeeDBAccess::jsonLoadStudentPayment(
                                $this->REQUEST->getPost('objectId')
                                , $this->REQUEST->getPost('fee')
                                , $this->REQUEST->getPost('paymentId')
                );
                break;

            case "jsonLoadStudentPaymentTraining":
                $jsondata = StudentFeeDBAccess::jsonLoadStudentPaymentTraining(
                                $this->REQUEST->getPost('objectId')
                                , $this->REQUEST->getPost('trainingId'));
                break;

            case "jsonLoadStaffPayment":
                $jsondata = StaffFeePaymentDBAccess::jsonLoadStaffPayment($this->REQUEST->getPost('paymentId'));
                break;

            case "searchStaffPayment":
                $jsondata = StaffFeePaymentDBAccess::searchStaffPayment($this->REQUEST->getPost());
                break;

            case "jsonListStaffInvoices":
                $jsondata = StaffFeePaymentDBAccess::jsonListStaffInvoices($this->REQUEST->getPost());
                break;

            case "jsonListStudentFeeServices":
                $jsondata = StudentFeeDBAccess::jsonListStudentFeeServices($this->REQUEST->getPost());
                break;
            //@veasna fee option load
            case "jsonLoadAllFeeOptions":
                $jsondata = FeeOptionDBAccess::jsonLoadAllFeeOptions($this->REQUEST->getPost());
                break;    
            //fee for prepayment
            case "jsonLoadStudentPrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonLoadStudentPrePayment($this->REQUEST->getPost());
                break;
            
             case "jsonSearchFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonSearchFeePrePayment($this->REQUEST->getPost());
                break;
                
             case "jsonLoadFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonLoadFeePrePayment($this->REQUEST->getPost());
                break;
            ///@veasna studentfeegrade
            case "jsonLoadStudentFeeGrade":
                $jsondata = StudentPaymentSettingDBAccess::jsonLoadStudentFeeGrade($this->REQUEST->getPost());
                break;
            //training    
            case "jsonLoadStudentFeeTraining":
                $jsondata = StudentPaymentSettingDBAccess::jsonLoadStudentFeeTraining($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentFeeReminder":
                $jsondata = StudentPaymentSettingDBAccess::jsonLoadStudentFeeReminder($this->REQUEST->getPost());
                break;
            //
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "saveIncome":
                $jsondata = IncomeDBAccess::saveIncome($this->REQUEST->getPost());
                break;

            case "saveExpense":
                $jsondata = ExpenseDBAccess::saveExpense($this->REQUEST->getPost());
                break;

            case "removeIncome":
                $jsondata = IncomeDBAccess::removeIncome($this->REQUEST->getPost('objectId'));
                break;

            case "removeExpense":
                $jsondata = ExpenseDBAccess::removeExpense($this->REQUEST->getPost('objectId'));
                break;

            case "jsonSaveFee":
                $jsondata = FeeDBAccess::jsonSaveFee($this->REQUEST->getPost());
                break;

            case "removeFee":
                $jsondata = FeeDBAccess::removeFee($this->REQUEST->getPost('objectId'));
                break;
                    
            // @veasna
            case "removeFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::removeFeePrePayment($this->REQUEST->getPost('objectId'));
                break;
                
            case "removeStudentFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::removeStudentFeePrePayment($this->REQUEST->getPost('objectId'));
                break;
            
            case "jsonReleaseFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonReleaseFeePrePayment($this->REQUEST->getPost());
                break;
            //

            case "actionStudentFeeServices":
                $jsondata = StudentFeeDBAccess::actionStudentFeeServices($this->REQUEST->getPost());
                break;

            case "jsonActionGradeGeneralToFee":
                $jsondata = FeeDBAccess::jsonActionGradeGeneralToFee($this->REQUEST->getPost());
                break;

            case "jsonActionGradeTrainingToFee":
                $jsondata = FeeDBAccess::jsonActionGradeTrainingToFee($this->REQUEST->getPost());
                break;

            case "jsonReleaseFee":
                $jsondata = FeeDBAccess::jsonReleaseFee($this->REQUEST->getPost());
                break;

            case "jsonActionStudentPayment":
                $jsondata = StudentFeeDBAccess::jsonActionStudentPayment($this->REQUEST->getPost());
                break;

            case "removeStudentPayment":
                $jsondata = StudentFeeDBAccess::removeStudentPayment($this->REQUEST->getPost());
                break;

            case "jsonActionStudentPaymentTraining":
                $jsondata = StudentFeeDBAccess::jsonActionStudentPaymentTraining($this->REQUEST->getPost());
                break;

            case "jsonActionStaffPayment":
                $jsondata = StaffFeePaymentDBAccess::jsonActionStaffPayment($this->REQUEST->getPost());
                break;

            case "removeStaffPayment":
                $jsondata = StaffFeePaymentDBAccess::removeStaffPayment($this->REQUEST->getPost('paymentId'));
                break;
            //$veasna
            case "jsonActionSaveFeeOptions":
                $jsondata = FeeOptionDBAccess::jsonActionSaveFeeOptions($this->REQUEST->getPost());
                break;
                
            case "jsonSaveFeePrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonSaveFeePrePayment($this->REQUEST->getPost());
                break;
                
             case "jsonActionStudentPrePayment":
                $jsondata = StudentFeePrepaidDBAccess::jsonActionStudentPrePayment($this->REQUEST->getPost());
                break;
            //
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeGradeGeneralByFee":
                $jsondata = FeeDBAccess::jsonTreeGradeGeneralByFee($this->REQUEST->getPost());
                break;
            case "jsonTreeGradeTrainingByFee":
                $jsondata = FeeDBAccess::jsonTreeGradeTrainingByFee($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');

        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>