<?php

/**
 * Form class for leave list
 */
class LeaveCalendarForm extends sfForm {

    const MODE_MY_LEAVE_CALENDAR = 'my_leave_calendar';
    const MODE_ADMIN_CALENDAR = 'default_calendar';

    private $mode;
    private $list = null;

    private $employeeList;
    private $leavePeriodService;
    private $companyStructureService;

    public function getCompanyStructureService() {
        if (is_null($this->companyStructureService)) {
            $this->companyStructureService = new CompanyStructureService();
            $this->companyStructureService->setCompanyStructureDao(new CompanyStructureDao());
        }
        return $this->companyStructureService;
    }

    public function setCompanyStructureService(CompanyStructureService $companyStructureService) {
        $this->companyStructureService = $companyStructureService;
    }


    public function getLeavePeriodService() {
        if (is_null($this->leavePeriodService)) {
            $leavePeriodService = new LeavePeriodService();
            $leavePeriodService->setLeavePeriodDao(new LeavePeriodDao());
            $this->leavePeriodService = $leavePeriodService;
        }
        return $this->leavePeriodService;
    }
    
    public function __construct($mode) {

        $this->mode = $mode;        
        parent::__construct(array(), array());
    }

    public function configure() {

        $widgets = array();
        $labels = array();
        $validators = array();
        $defaults = array();
        
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N', 'OrangeDate'));
        $inputDatePattern = sfContext::getInstance()->getUser()->getDateFormat();

	if($this->mode != self::MODE_MY_LEAVE_CALENDAR){

		$widgets['calMonth'] = new ohrmWidgetMonthDropDown();
		$labels['calMonth'] = __('Month');
		$monthChoices = $widgets['calMonth']->getValidValues();
            	$validators['calMonth'] = new sfValidatorChoice(array('choices'=> $monthChoices, 'required' => false));

		$widgets['calYear'] = new ohrmWidgetYearDropDown();
	        $labels['calYear'] = __('Year');
		$yearChoices = $widgets['calYear']->getValidValues();
                $validators['calYear'] = new sfValidatorChoice(array('choices'=> $yearChoices, 'required' => false));	
        
        	// Set default from/to to current leave period.
	
	        $defaults['calMonth'] = intval(date('m'))-1;
	        $defaults['calYear'] = date('Y');
       
	}
        
        // Leave Statuses
        $leaveStatusChoices = Leave::getStatusTextList();     
        
        if ($this->mode == self::MODE_MY_LEAVE_CALENDAR) {
            $defaultStatuses = array_keys($leaveStatusChoices);
        } else {
            $defaultStatuses = array_keys(Leave::getPendingLeaveStatusList());
	    //$defaultStatuses = array_keys($leaveStatusChoices);
        }
        
        $widgets['chkSearchFilter'] = new ohrmWidgetCheckboxGroup(
                array('choices' => $leaveStatusChoices,
                      'show_all_option' => true,
                      'default' => $defaultStatuses));
            
        $labels['chkSearchFilter'] = 'Show Leave with Status';
        $defaults['chkSearchFilter'] = $defaultStatuses;

        $validators['chkSearchFilter'] = new sfValidatorChoice(
                array('choices' => array_keys($leaveStatusChoices), 
                      'required' => false, 'multiple' => true));


        if ($this->mode != self::MODE_MY_LEAVE_CALENDAR) {

            $requiredPermissions = array(
                BasicUserRoleManager::PERMISSION_TYPE_ACTION => array('view_leave_list'));
            
            $widgets['txtEmployee'] = new ohrmWidgetEmployeeNameAutoFill(
                    array('loadingMethod'=>'ajax',
                          'requiredPermissions' => $requiredPermissions));
            
            $labels['txtEmployee'] = __('Employee');
            $validators['txtEmployee'] = new ohrmValidatorEmployeeNameAutoFill();
            
            $widgets['cmbSubunit'] = new ohrmWidgetSubUnitDropDown();    
            $labels['cmbSubunit'] = __('Sub Unit');
            $subUnitChoices = $widgets['cmbSubunit']->getValidValues();
            $validators['cmbSubunit'] = new sfValidatorChoice(array('choices'=> $subUnitChoices, 'required' => false));
            
            // TODO check cmbWithTerminated if searching for terminated employee
            $widgets['cmbWithTerminated'] = new sfWidgetFormInputCheckbox(array('value_attribute_value' => 'on'));
            $labels['cmbWithTerminated'] =  __('Include Past Employees');
            $validators['cmbWithTerminated'] =  new sfValidatorBoolean(array('true_values' => array('on'), 'required' => false));                        
        }       
        
        $this->setWidgets($widgets);
        $this->getWidgetSchema()->setLabels($labels);
        $this->setvalidators($validators);
        $this->setDefaults($defaults);
        
        $this->getWidgetSchema()->setNameFormat('leaveList[%s]');
	
    }
    

    /**
     * Formats the title of the leave list according to the mode
     *
     * @return string Title of the leave list
     */
    public function getTitle() {

        if ($this->mode === self::MODE_MY_LEAVE_CALENDAR) {
            $title = __('My Leave Calendar');
        } else {
            $title = __('Leave Calendar');
        }

        return $title;
    }

    /**
     * Returns the set of action buttons associated with each mode of the leave list
     *
     * @return array Array of action buttons as instances of ohrmWidegetButton class
     */
    public function getSearchActionButtons() {
        return array(
            'btnSearch' => new ohrmWidgetButton('btnSearch', 'Search', array()),
            'btnReset' => new ohrmWidgetButton('btnReset', 'Reset', array('class' => 'reset')),
        );
    }

    public function setList($list) {
        $this->list = $list;
    }

    public function getList() {
        return $this->list;
    }

    public function getEmployeeList() {
        return $this->employeeList;
    }

    public function setEmployeeList($employeeList) {
        $this->employeeList = $employeeList;
    }    

    public function getActionButtons() {

        $actionButtons = array();
        
        //if (!empty($this->list)) {
        //    $actionButtons['btnSave'] = new ohrmWidgetButton('btnSave', "Save", array('class' => 'savebutton'));
        //}

        return $actionButtons;
    }
    
    public function getJavaScripts() {
        $javaScripts = parent::getJavaScripts();
        $javaScripts[] = plugin_web_path('orangehrmLeavePlugin', 'js/viewLeaveListSuccess.js');
        
        return $javaScripts;
    }
    
    public function getStylesheets() {
        $styleSheets = parent::getStylesheets();        
        return $styleSheets;        
    }

    public function setValue($field, $value) {
      $this->values[$field] = $value; // set the value for this request
      $this->taintedValues[$field] = $value; // override the value entered by the user
      $this->resetFormFields(); // force a refresh on the field schema
  }    

}
