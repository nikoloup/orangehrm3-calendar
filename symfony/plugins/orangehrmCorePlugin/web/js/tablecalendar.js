//Tablecalendar.js



//Main function
function tableCalendar()
{
	init();
	
	//Get day information
	this.cMonth = window.month+1;
	this.cYear = window.year;

	if(mode==0)
	{
		renderMonth(this.cYear, this.cMonth);
	}
	else
	{
		renderYear(this.cYear);
	}

}


function init()
{
	this.empTotal = Object.keys(tableData).length;
}

function renderMonth(year, month)
{
	//Update current year, month, date
	this.cMonth = month;
	this.cYear = year;

	//Get total number of days in month
	var daysTotal = new Date(year, month, 0).getDate();

	//Initialize general (monthly) coloring array to 9 (NaN)
	//CA starts at cell 1, NOT 0 (cell 0 is ignored for convenience)
        var ca = new Array(daysTotal);
        for(var i=1; i<=daysTotal; i++)
        {
                ca[i] = 9;
        }

	//Get Holidays and Weekends
	var ca = parseHolidaysAndWeekendsAndCDay(year, month, daysTotal, ca);
	
	//Build Table HTML
	var tableHTML = '';	

	tableHTML += '<table>';
	tableHTML += buildHeaderHTMLMonth(daysTotal, ca);
	tableHTML += buildBodyHTMLMonth(daysTotal, year, month, ca);
	tableHTML += '</table>';
	tableHTML += '</br>';
	tableHTML += this.empTotal+' employees were retrieved';

	document.getElementById('table_calendar').innerHTML = tableHTML;

}

function renderYear(year)
{
        //Update current year
        this.cYear = year;

        //Build Table HTML
        var tableHTML = '';

        tableHTML += '<table>';
        tableHTML += buildHeaderHTMLYear();
        tableHTML += buildBodyHTMLYear(year);
        tableHTML += '</table>';
	tableHTML += '</br>';
        tableHTML += this.empTotal+' employees were retrieved';

        document.getElementById('table_calendar').innerHTML = tableHTML;

}

function nextMonth()
{
	if(this.cMonth!=12)
	{
		this.cMonth++;
	}
	else
	{
		this.cMonth = 1;
		this.cYear++;
	}
	renderMonth(this.cYear, this.cMonth);
}

function nextYear()
{
        this.cYear++;        
        renderYear(this.cYear);
}

function prevMonth()
{
	
        if(this.cMonth!=1)
        {
                this.cMonth--;
        }
        else
        {
                this.cMonth = 12;
                this.cYear--;
        }       
        renderMonth(this.cYear, this.cMonth);
}

function prevYear()
{
        this.cYear--;
        renderYear(this.cYear);
}


function parseHolidaysAndWeekendsAndCDay(year, month, daysTotal, hw)
{
	//Code 1 -> Weekend
	//Code 2 -> Holiday
	//Code 3 -> Today
	//Holiday overrides Weekend
	//Today overrides everything except leaves and presences


	//Get Day of 1st Day
	var day = new Date(year,month-1,1).getDay();

	//Calculate the weekends
	var dayc = 1;
	var diff = 6-day;
	if(diff==0)
	{
		hw[1] = 1;
		hw[2] = 1;
		dayc += 7;
	}
	else if(diff==6)
	{
		hw[1] = 1;
		dayc += 6;
	}
	else
	{
		dayc += diff;
	}
	while(dayc<=daysTotal)
	{
		hw[dayc] = 1;
		if (dayc+1 <= daysTotal) hw[dayc+1] = 1; 
		dayc += 7;
	}

	//Calculate the holidays
	for(var i=0; i<holidayData.length; i++)
	{
		var tdate = holidayData[i].date;
		var res = tdate.split("-");
		if(year==parseInt(res[0]) && month==parseInt(res[1]))
		{
			var tday = parseInt(res[2]);
			var length = parseInt(holidayData[i].length);
			var cnt = 0;
			hw[tday] = 2;
			tday++;
			while(cnt<length && tday<=totalDays)
			{
				hw[tday] = 2;
				cnt++;
				tday++;	
			}
			
		}
	}

	//Color the current day
	var tdate = new Date(); 
	if(tdate.getMonth()==month-1 && tdate.getFullYear()==year) hw[tdate.getDate()] = 3;

	//Return result
	return hw;
	
}

function parseEmployeeLeaves(days, year, month, cap, employeeObj)
{
        if(typeof employeeObj[year]!='undefined' && typeof employeeObj[year][month]!='undefined')
        {
        	//Algorithm:
                //1.For each employee, check if he has any leaves in this month
                //2.If he does, increase days until a leave starts
                //      3.When leave starts, color cells until leave ends
                //4.If he has more leaves, go to 2. Else continue with next employee

                //Note: OrangeHRM returns leaves in DESC order, from furthest away to nearest
                //To fix this start reading leave object in reverse order

                var flag = false; //If we are coloring a leave
                var leaves = employeeObj[year][month]; //leaves object
                var leavesNr = Object.keys(leaves).length; //Total leaves
                var dayp = 1; //Day pointer
                var leavep = leavesNr-1; //Leaves pointer
                while (dayp<=days && leavep!=-1)
                {
                       	if(!flag)
                        {
                        	if(leaves[leavep]['start']==dayp)
                                {
                                	flag = true;
                                } 
                        }
                        if(flag)
                        {
				//tsipizic fix for today
                        	//if(cap[dayp]==9)
                        	if(cap[dayp]==9 || cap[dayp] == 3)
				{
					cap[dayp] = getColor(leaves[leavep]['color']); //Get leave color
					cap[dayp] = cap[dayp]+'?'+leaves[leavep]['color']; //Get leave text (first letter)
				}	
                                if(leaves[leavep]['end']==dayp)
                                {
                                	flag = false;
                                        leavep--;
                                }
 			}
                        dayp++;
                }
	}

	return cap;
}

function parseEmployeePresences(year, month, cap, employeeObj)
{
	var dayArr;
	var nr;
	if(typeof employeeObj.presence!='undefined' && typeof employeeObj.presence[year]!= 'undefined' && typeof employeeObj.presence[year][month]!='undefined')
	{
		//Split presence string in array
		dayArr = employeeObj['presence'][year][month].split(",");

		//Last cell will be empty, so length-1
		for(var i=0; i<dayArr.length-1; i++)
		{
			nr = parseInt(dayArr[i]);
                        //tsipizic fix for today
                        //if(cap[nr]==9)
                        if(cap[nr]==9)
			{
				cap[nr] = 0; //Mark presence
			}
			else if(cap[nr]==3)
			{
				cap[nr] = 4;
			}
		}
	}

	return cap;
}

function buildHeaderHTMLMonth(days,hw)
{
	var html = '';
	html += '<tr>';
	html += '<th class="firstCol">Όνομα Υπαλλήλου</th>';
	for(var i=1; i<=days; i++)
	{
		if(hw[i]==1)
		{
			html += '<th class="weekend">'+i+'</th>';
		}
		else if(hw[i]==2)
		{
			html += '<th class="holiday">'+i+'</th>';
		}
		else if(hw[i]==3)
		{
			html += '<th class="today">'+i+'</th>';
		}
		else
		{
			html += '<th>'+i+'</th>';
		}
	}
	html += '</tr>';
	return html;
}


function buildHeaderHTMLYear()
{
        var html = '';
	var daysArr = new Array("M","T","W","T","F","S","S");
        html += '<tr>';
        html += '<th class="firstCol">Month</th>';
        for(var i=0; i<42; i++)
        {
        	html += '<th>'+daysArr[i%7]+'</th>';
        }
        html += '</tr>';
        return html;
}

function buildRowHTMLMonth(days, year, month, cap, employeeObj)
{
	var html = '';
	html += '<tr>';
	html += '<td class="firstCol">'+'<a href="../pim/viewPersonalDetails/empNumber/'+employeeObj.id+'" >' + employee + '</a></td>';
	for(var i=1; i<cap.length; i++)
	{
		if(cap[i]==0)
		{
			html += '<td title="Presence" style="color:'+getColor('Presence')+'; background-color:white;">&#9679;</td>';
		}
		else if(cap[i]==1)
		{
			html += '<td title="Weekend" style="background-color:'+getColor('Weekend')+';"></td>';
		}
		else if(cap[i]==2)
		{
			html += '<td title="Holiday" style="background-color:'+getColor('Holiday')+';"></td>';
		}
		else if(cap[i]==3)
		{
			html += '<td></td>';
		}
		else if(cap[i]==4)
		{
			html += '<td title="Presence" style="color:'+getColor('Presence')+'; background-color:white;">&#9679;</td>';
		}
		else if(cap[i]==9)
		{
			html += '<td style="background-color:'+getColor('Absence')+';"></td>';
		}
		else
		{
			var tmp = cap[i].split("?");
			html += '<td title="'+tmp[1]+'" style="background-color:'+tmp[0]+'; color:'+calcTextColor(tmp[0])+';">'+tmp[1].charAt(0)+'</td>';
		}
	}
	return html;
}

function buildRowHTMLYear(days, year, month, cap, employeeObj)
{
	var day;
	var offset;
	var count = 0;

	//Get day of first day of month
	day = new Date(year,month-1,1).getDay();
	
	//Calculate offset
	if(day==0)
	{
		offset = 6;
	}
	else
	{
		offset = day-1;
	}

	//Row start
        var html = '';
        html += '<tr>';
        html += '<td class="firstCol">'+window.month_names[month-1]+'</td>';

	//Paint offset
	for(var i=1; i<=offset; i++)
	{
		html += '<td class="dayOffset">&nbsp;</td>';
	}

	//Normal Month
        for(var i=1; i<cap.length; i++)
        {
                if(cap[i]==0)
                {
                        html += '<td data-day="'+i+'" title="Presence" style="color:'+getColor('Presence')+'; background-color:white;">&#9679;</td>';
                }
                else if(cap[i]==1)
                {
                        html += '<td data-day="'+i+'" title="Weekend" style="background-color:'+getColor('Weekend')+';"></td>';
                }
                else if(cap[i]==2)
                {
                        html += '<td data-day="'+i+'" title="Holiday" style="background-color:'+getColor('Holiday')+';"></td>';
                }
                else if(cap[i]==3)
                {
                        html += '<td data-day="'+i+'" class="today-year"></td>';
                }
		else if(cap[i]==4)
                {
                        html += '<td data-day="'+i+'" class="today-year" style="color:'+getColor('Presence')+'; background-color:white;">&#9679;</td>';
                }
                else if(cap[i]==9)
                {
                        html += '<td data-day="'+i+'" style="background-color:'+getColor('Absence')+';"></td>';
                }
                else
                {
                        var tmp = cap[i].split("?");
                        html += '<td data-day="'+i+'" title="'+tmp[1]+'" style="background-color:'+tmp[0]+'; color:'+calcTextColor(tmp[0])+';">'+tmp[1].charAt(0)+'</td>';
                }
        }

	//Get last cell
	count = offset + cap.length;
	for(var i=count; i<=42; i++)
	{
		html += '<td class="dayOffset"></td>';
	}

        return html;
}

var employee;
function buildBodyHTMLMonth(days, year, month, ca)
{
	//Initialize html string and personal coloring array
	var html='';
	var cap; 
	
	//tsipizic sort by employee name
	var names = [];
	for(var n in tableData){
	       	names.push(n);
	}
	names.sort();

	//for (employee in tableData)
	for (i=0;i<names.length;i++)
	{
		employee = names[i];

		cap = ca.slice();		
		
		var employeeObj = tableData[employee];

		//Parse Leaves
		cap = parseEmployeeLeaves(days, year, month, cap, employeeObj);
	
		//Parse Presences
		cap = parseEmployeePresences(year, month, cap, employeeObj);

		//Build Row
		html += buildRowHTMLMonth(days, year, month, cap, employeeObj);
	}

	return html;
}

function buildBodyHTMLYear(year)
{
	//Initialize html string and personal coloring array
	var html ='';
	var employeeObj;

	if(this.empTotal>0)
	{
	
		//Repeat for all 12 months
		for(var month=1; month<=12; month++)
		{
			//Get total number of days in month
        		var daysTotal = new Date(year, month, 0).getDate();

        		//Initialize general (monthly) coloring array to 9 (NaN)
        		//CA starts at cell 1, NOT 0 (cell 0 is ignored for convenience)
        		var ca = new Array(daysTotal);
        		for(var i=1; i<=daysTotal; i++)
        		{
        		        ca[i] = 9;
        		}

        		//Get Holidays and Weekends
        		ca = parseHolidaysAndWeekendsAndCDay(year, month, daysTotal, ca);

			//Get employeeObj (unique)
			var name;
			for(var n in tableData){
				name = n;
			}
			employeeObj = tableData[name];

			//Parse Leaves
                	ca = parseEmployeeLeaves(daysTotal, year, month, ca, employeeObj);

                	//Parse Presences
                	ca = parseEmployeePresences(year, month, ca, employeeObj);

                	//Build Row
                	html += buildRowHTMLYear(daysTotal, year, month, ca, employeeObj);
		
		}
	}

	return html;


}
