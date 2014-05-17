<?php

/**

 */
class viewMyLeaveCalendarAction extends viewLeaveCalendarAction {    
    
    protected function getMode() {
       
        $mode = LeaveCalendarForm::MODE_MY_LEAVE_CALENDAR;
        return $mode;
    }
    
    protected function isEssMode() {
       
        return true;
    }

}
