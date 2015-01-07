<?php

class inc_calendar
{
	var $cal = "CAL_GREGORIAN";
	var $format = "%Y%m%d";
	var $today;
	var $day;
	var $month;
	var $year;
	var $pmonth;
	var $pyear;
	var $nmonth;
	var $nyear;
	var $wday_names = array("Dom","Lun","Mar","Mie","Jue","Vie","Sab");
	
	function inc_calendar()
	{
		$this->day = "1";
		$today = "";
		$month = "";
		$year = "";
		$pmonth = "";
		$pyear = "";
		$nmonth = "";
		$nyear = "";
	}


	function dateNow($month,$year)
	{
		if(empty($month))
			$this->month = strftime("%m",time());
		else
			$this->month = $month;
		if(empty($year))
			$this->year = strftime("%Y",time());	
		else
		$this->year = $year;
		$this->today = strftime("%d",time());		
		$this->pmonth = $this->month - 1;
		$this->pyear = $this->year - 1;
		$this->nmonth = $this->month + 1;
		$this->nyear = $this->year + 1;
	}

	function daysInMonth($month,$year)
	{
		if(empty($year))
			$year = inc_calendar::dateNow("%Y");

		if(empty($month))
			$month = inc_calendar::dateNow("%m");
		
		if($month == 2)
		{
			if(inc_calendar::isLeapYear($year))
			{
				return 29;
			}
			else
			{
				return 28;
			}
		}
		else if($month==4 || $month==6 || $month==9 || $month==11)
			return 30;
		else
			return 31;
	}

	function isLeapYear($year)
	{
      return (($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0); 
	}

	function dayOfWeek($month,$year) 
  { 
		if($month > 2) 
				$month -= 2; 
		else 
		{ 
				$month += 10; 
				$year--; 
		} 
		 
		$day =  ( floor((13 * $month - 1) / 5) + 
						$this->day + ($year % 100) + 
						floor(($year % 100) / 4) + 
						floor(($year / 100) / 4) - 2 * 
						floor($year / 100) + 77); 
		 
		$weekday_number = (($day - 7 * floor($day / 7))); 
		 
		return $weekday_number; 
  }

	function getWeekDay()
	{
		$week_day = inc_calendar::dayOfWeek($this->month,$this->year);
		//return $this->wday_names[$week_day];
		return $week_Day;
	}

	function showThisMonth()
	{
		print '<table cellpadding="2" cellspacing="2" border=1 bordercolor="cccccc">';
		print '<tr><td colspan="7">Mes y Año: <b>'.$this->month ." / " .$this->year .'</b></td></tr>';
		print '<tr>';
		for($i=0;$i<7;$i++)
			print '<td width="40" height="30" bgcolor="#79C143" align="center">'. $this->wday_names[$i]. '</td>';
		print '</tr>';		
		$wday = inc_calendar::dayOfWeek($this->month,$this->year);
		$no_days = inc_calendar::daysInMonth($this->month,$this->year);
		$count = 1;
		print '<tr>';
		for($i=1;$i<=$wday;$i++)
		{
			print '<td align="center" height="25">&nbsp;</td>';
			$count++;
		}
		for($i=1;$i<=$no_days;$i++)
		{
				if($count > 6)
				{
					if($i == $this->today)
					{
						print '<td align="center" height="25" bgcolor="#F7D346"><font color="#008000"><b>' . $i . '</b></font></td></tr>';
					}
					else
					{
						print '<td align="center" height="25"><font color="#000000">' . $i . '</font></td></tr>';
					}
					$count = 0;
				}
				else
				{
					if($i == $this->today)
					{
						print '<td align="center" height="25" bgcolor="#F7D346"><font color="#008000"><b>' . $i . '</b></font></td>';
					}
					else
					{
						print '<td align="center" height="25"><font color="#000000">' . $i . '</font></td>';
					}
				}
				$count++;
		}
		print '</tr></table>';
	} 
}

?>