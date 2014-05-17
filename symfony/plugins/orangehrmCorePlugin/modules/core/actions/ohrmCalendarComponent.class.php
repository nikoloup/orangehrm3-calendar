<?php
//nikoloup
class ohrmCalendarComponent extends sfComponent {

	protected static $calendarDataLeave;
	protected static $calendarDataPresence;
	protected static $recordCount;
	protected static $reqMonth;
	protected static $reqYear;


	public function execute($request)
	{
		//Get leaves and employees
		$employee = new Employee();

		$event = array();
		$table_event = array();

		$this->calDataLeave = self::$calendarDataLeave;
		$this->setupLeaveTypesAndOthers();
				
		$count = 0;		

		foreach($this->calDataLeave as $leave)
		{
			$dates = $leave->getLeaveStartAndEndDate();
			$empName = $leave->getEmployee()->getFullName();
			$emp_array = $leave->getEmployee()->toArray();
                        $empId = $emp_array['empNumber'];
			$color = $leave->getLeaveType()->getName(); //Will be replaced with actual color client-side
			$lReqId = $leave->getId();
			$status = Leave::getTextForLeaveStatus($leave->getLeaveStatusId());			
			
			//FullCalendar Array Format
			$event[$count]['start'] = $dates[0];
			$event[$count]['end'] = $dates[1];
			
			$event[$count]['color'] = $color;
			
			$event[$count]['title'] = $empName;
			
			$event[$count]['lReqId'] = $lReqId;

			//TableCalendar Array Format

			//Get Dates in appropriate format
			$date_start = array();
			$date_end = array();
			$date_start = explode("-", $dates[0]);	
			$date_end = explode("-", $dates[1]);
			$year_start = intval($date_start[0]);
			$month_start = intval($date_start[1]);
			$year_end = intval($date_end[0]);
			$month_end = intval($date_end[1]);
			$day_start = intval($date_start[2]);
			$day_end = intval($date_end[2]);

			//If not set, add employee id (for link)
			if(!isset($table_event[$empName]['id']))
			{
				$table_event[$empName]['id'] = intval($empId);
			}
		
			//Build array
			$newleave = array(
				'lReqId' => $lReqId,
				'color' => $color, //Same here, will be replaced with actual color client-side
				'status' => $status,
				'start' => $day_start
			);
			if($year_start!=$year_end || $month_start!=$month_end)
			{
				$newleave['end'] = 'end';
			}
			else
			{
				$newleave['end'] = $day_end;
			}
			//Add leave event
			$table_event[$empName][$year_start][$month_start][] = $newleave;

			//Check if leave spans two months or more
			while($year_start!=$year_end || $month_start!=$month_end)
			{
				
				//Increment months until target is reached
				$month_start++;
				if($month_start==13)
				{
					$year_start++;
					$month_start = 0;
				}
				
				//New leave event (Modify newleave - same data)
				$newleave['start'] = 1;
				if($year_start!=$year_end || $month_start!=$month_end) //If stil not there
	                        {
	                                $newleave['end'] = 'end';
	                        }
	                        else
	                        {
	                                $newleave['end'] = $day_end;
	                        }
				$table_event[$empName][$year_start][$month_start][] = $newleave;	
			}
			
			$count++;

		}

		//Parse presences
		
		$this->calDataPresence = self::$calendarDataPresence;
		$first = true; //Flag. True when a new user is encountered
		
		//Read first entry to initialize
		$currentUserId = intval($this->calDataPresence[0]['e_empNumber']);
		$currentUserEntry = null; //NOTE: currentUserEntry is a reference to original event_table
		
		foreach($this->calDataPresence as $presence)
		{

			//Check if a new user is encountered
			if($currentUserId != intval($presence['e_empNumber']))
			{
				$currentUserId = intval($presence['e_empNumber']);
				$first = true;
				unset($currentUserEntry);
				$currentUserEntry = null;
			}

			//Find user in table_event
			if($first)
			{
				//If exists, assign to variable
				foreach($table_event as &$emp)
				{
					if($emp['id'] == $currentUserId)
					{
						$currentUserEntry = &$emp;
						break;
					}
				}

				//If not exists, create user and assign to variable
				if($currentUserEntry == null)
				{
					$name = $presence['e_firstName'].' '.$presence['e_lastName'];
					$table_event[$name] = array();
					$table_event[$name]['id'] = $presence['e_empNumber'];
					$currentUserEntry = &$table_event[$name];
				}
	
				//Create presence array
				$currentUserEntry['presence'] = array();
			
				//Set first flag to false
				$first = false;

			}

			//Get punch in date
			$date = explode(" ",$presence['a_in_date_time']); //Trim time
			$date = explode("-",$date[0]); //Get actual date components
			$date[1] = intval($date[1]);
			
			//Check if subarrays exist
			if($currentUserEntry['presence'][$date[0]] == null)
			{
				$currentUserEntry['presence'][$date[0]] = array();
			}
			if($currentUserEntry['presence'][$date[0]][$date[1]] == null)
			{
				$currentUserEntry['presence'][$date[0]][$date[1]] = ''; //String that holds presences
			}

			$currentUserEntry['presence'][$date[0]][$date[1]] = $currentUserEntry['presence'][$date[0]][$date[1]].$date[2].', ';

		}

		//Get Holidays
		$holidays = array();
		$holidayList = new HolidayService();
		$holidayList = $holidayList->getFullHolidayList();
		foreach($holidayList as $holiday)
		{
			$hol_date = $holiday->getDate();
			if($holiday->getRecurring())
			{
				$hol_date_arr = explode("-",$hol_date);
				$hol_date_arr[0] = date('Y');
				$hol_date = implode("-",$hol_date_arr);
			}
	
			$newholiday = array(
				'date' => $hol_date,
				'length' => $holiday->getLength()
			);
			$holidays[] = $newholiday; 
		}
		
		$this->events = json_encode($event);
		$this->table_events = json_encode($table_event);
		$this->holidays = json_encode($holidays);
		$this->color_codes = json_encode($this->ltao);
		$this->reqMonth = json_encode(self::$reqMonth);
		$this->reqYear = json_encode(self::$reqYear);
			
		return sfView::SUCCESS;
		
	}

	private function getColor($id)
	{
		switch($id)
		{
			case 1:
				$color = "#FF0000";
				break;
			case 2:
				$color = "#FF4500";
				break;
			case 3:
				$color = "#FA8072";
				break;
			case 4:
				$color = "#FFC0CB";
				break;
			case 5:
				$color = "#FFA500";
				break;
			case 6:
				$color = "#FFFF00";
				break;
			case 7:
				$color = "#BDB76B";
				break;
			case 8:
				$color = "#DEB887";
				break;
			case 9:
				$color = "#BC8F8F";
				break;
			case 10:
				$color = "#8B4513";
				break;
			case 11:
				$color = "#556B2F";
				break;
			case 12:
				$color = "#00FF00";
				break;
			case 13:
				$color = "#008000";
				break;
			case 14:
				$color = "#00FFFF";
				break;
			case 15:
				$color = "#008B8B";
				break;
			case 16:
				$color = "#B0C4DE";
				break;
			case 17:
				$color = "#00BFFF";
				break;
			case 18:
				$color = "#1E90FF";
				break;
			case 19:
				$color = "#0000FF";
				break;
			case 20:
				$color = "#191970";
				break;
			case 21:
				$color = "#9370DB";
				break;
			case 22:
				$color = "#9400D3";
				break;
			case 23:
				$color = "#800080";
				break;
			case 24:
				$color = "#C0C0C0";
				break;
			case 25:
				$color = "#708090";
				break;				
			default:
				$color = "#000000";

		}
		return $color;
	}		

	private function setupLeaveTypesAndOthers()
	{
		$count = 1;
		$this->ltao = array();
		$ltDao = new LeaveTypeDao();
		$list = $ltDao->getLeaveTypeList();
		$this->ltao['Presence'] = '#66FF7D';
		$this->ltao['Absence'] = '#FFFFFF';
		$this->ltao['Weekend'] = '#CFCFCF';
		$this->ltao['Holiday'] = '#CFCFCF';
		foreach($list as $lt)
		{
			$this->ltao[$lt->getName()] = $this->getColor($count);
			$count++;
		}
	}

	public static function setCalendarData($calendarDataLeave, $calendarDataPresence)
	{
		self::$calendarDataLeave = $calendarDataLeave;
		self::$calendarDataPresence = $calendarDataPresence;
	}

	public static function setNumberOfRecords($count)
	{
		self::$recordCount = $count;
	}

	public static function setRequestedDate($reqMonth, $reqYear)
	{
		self::$reqMonth = $reqMonth;
		self::$reqYear = $reqYear;		
	}

	public static function getCalendarData()
	{
		return self::$calendarData;
	}

	public static function getRecordCount()
        {
                return self::$recordCount;
        }

}


