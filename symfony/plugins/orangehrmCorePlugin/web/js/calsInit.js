//Generic initialization and synchronization code
$(document).ready(function(){

//Determine mode
if(Object.keys(tableData).length==1)
{
	window.mode = 1; //Single employee view - yearly
	window.empname = Object.keys(tableData)[0];
}
else if(Object.keys(tableData).length>1)
{
	window.mode = 0; //Normal view - monthly
}
else
{
	return;
}

//Load Colors
if(localStorage)
{
	for(var i=0; i<localStorage.length; i++)
	{
		var key = localStorage.key(i);  
        	var val = localStorage.getItem(key);
		colorCodes[key] = val;
	}
}

//Replace colors in eventData variable
replaceColorsInitial();

//Build Legend
buildLegend();

//Attach Legend Listener
$('#legend').click(function(){
	$('#dialogColor').modal();
});


//Build Color Dialog
buildColorDialog();

//Attach Save button Listener
$('#colorSave').click(function(){
	saveColorChanges();
	$('#dialogColor').modal('hide');
	location.reload();
});

//Initialize global month & year variables to requested
window.month = parseInt(reqMonth-1);
window.year = parseInt(reqYear);

//Initialize view variable
window.view = 1; //1: Table Calendar, 2: Full Calendar

//Initialize month names variable
window.month_names = new Array("January","February","March","April","May","June","July","August","September","October","November","December");

//Initialize color changes variable
window.colorChanges = new Object();

//Write Date Header
if(window.mode==0)
{
        $('#dateHeader').html(window.month_names[window.month]+' '+window.year);
}
else
{
        $('#dateHeader').html(window.year+' - '+window.empname);
}


//Start FullCalendar code
if(window.mode==0)
{
	$('#calendar').fullCalendar({
		weekends:false,
		aspectRatio:3,
		eventClick: function(calEvent, jsEvent, view) { window.location = './viewLeaveRequest/id/' + calEvent.lReqId; },
		events: eventData,
		header: false
	});
}
else
{
	$('#calendar').fullCalendar({
                eventClick: function(calEvent, jsEvent, view) { window.location = './viewLeaveRequest/id/' + calEvent.lReqId; },
                events: eventData,
                header: false,
		defaultView: 'year'
        });
}

//Start tableCalendar code
tableCalendar();

//Switch views so both get rendered
$('#calendar').toggle();
$('#table_calendar').toggle();

//Attach listener to switch button
$('#switchButton').click(function(){
	$('#calendar').toggle();
	$('#table_calendar').toggle();
	//Synchronize views
	if(window.view==1)
	{
		//We're switching to Full Calendar
		$('#calendar').fullCalendar('gotoDate', window.year, window.month);
		window.view = 2;
	}
	else
	{
		//We're switching to Table Calendar
		if(window.mode==0)
		{
			renderMonth(window.year, window.month+1);
		}
		else
		{
			renderYear(window.year);
		}
		window.view = 1;
	}
});

//Attach listener for header date replacement
$('#table_calendar td').hover(function(){
	if($(this).attr('data-day')!=undefined)
	{
		var $el = $(this).closest('table').find('th').eq($(this).index());
		window.tmp = $el.html();
		$el.html('<span>'+$(this).attr('data-day')+'</span>');
		$el.css('background-color','white');
	}
}, function(){
	if($(this).attr('data-day')!=undefined)
	{
		var $el = $(this).closest('table').find('th').eq($(this).index());
		$el.html(window.tmp);
		$el.css('background-color','#F28C38');
		window.tmp = '';
	}
});

}); //End initialization function

//Synchronized navigation functions
function universal_next(){
	if(window.mode==0)
	{
		/*
		if(window.view==1)
		{
			//Navigate Table Calendar
			nextMonth();
		}
		else
		{
			$('#calendar').fullCalendar('next');
		}
		window.month+=1;
		if(window.month==12)
		{
			window.month = 0;
			window.year++;
		}
		$('#dateHeader').html(window.month_names[window.month]+' '+window.year);
		*/
		if(window.month==11)
		{
			document.getElementById('leaveList_calMonth').selectedIndex=0;
			document.getElementById('leaveList_calYear').selectedIndex=window.year++;
		}
		else
		{
			document.getElementById('leaveList_calMonth').selectedIndex=(window.month+2);
		}
		document.getElementById('btnSearch').click();
	}
	else
	{
		if(window.view==1)
		{
			//Navigate Table Calendar
			nextYear();
		}
		else
		{
			$('#calendar').fullCalendar('next');
		}
		window.year++;
		$('#dateHeader').html(window.year+' - '+window.empname);
	}
}

function universal_prev(){
	if(window.mode==0)
	{
		/*
        	if(window.view==1)
        	{
                	//Navigate Table Calendar
                	prevMonth();
        	}
        	else
        	{
                	$('#calendar').fullCalendar('prev');
        	}
        	window.month-=1;
        	if(window.month==-1)
        	{
                	window.month = 11;
                	window.year--;
        	}
		$('#dateHeader').html(window.month_names[window.month]+' '+window.year);
		*/
                if(window.month==0)
                {
                        document.getElementById('leaveList_calMonth').selectedIndex=11;
                        document.getElementById('leaveList_calYear').selectedIndex=(window.year-1);
                }
                else
                {
                        document.getElementById('leaveList_calMonth').selectedIndex=(window.month);
                }
                document.getElementById('btnSearch').click();
	}
	else
	{
		
                if(window.view==1)
                {
                        //Navigate Table Calendar
                        prevYear();
                }
		else
		{
			$('#calendar').fullCalendar('prev');
                }
		window.year--;
		$('#dateHeader').html(window.year+' - '+window.empname);
	}
}
	
//Get color function
function getColor(typename)
{
	return colorCodes[typename];
}

//Collect color changes
function colorChangesCollect(key, value)
{
	window.colorChanges[key] = value;
}

//Save color changes
function saveColorChanges(){
	if(localStorage)
	{
		$.each(window.colorChanges, function(index, value){
			localStorage[index] = '#'+value;
		});
	}
	else
	{
		alert('Error : Your browser does not support localStorage');
	}
}

//Build Legend
function buildLegend()
{
	var text = '';
	var count = 0;
	text += '</br>';
	text += '<h2>Others:</h2>';
	$.each(colorCodes, function(index, value){
		if(count==0) text += '<span style="color:'+value+';">&#9679;</span> '+index+' ';
	       	else text += '<span style="color:'+value+'; font-size: 1.8em;">■</span> '+index+' ';
        	if(count==3) text+= '</br></br><h2>Leave Types:</h2>';
        	count++;
	});
	$('#legend').html(text);
}

//Build Color Dialog
function buildColorDialog()
{
	var text = '<table>';
	$.each(colorCodes, function(index, value){
        	text += '<tr><td><label>'+index+': </label></td><td><input name="'+index+'" class="color" value="'+value.slice(1)+'" onchange="colorChangesCollect(this.name, this.value)"/></td>';
	});
	text += '</table>';
	$('#dialogColor div.modal-body').html(text);
}

//Replace Colors
function replaceColorsInitial()
{
	$.each(eventData, function(index,value){
        	eventData[index].color = getColor(eventData[index].color);
	});
}

//Calculate text color
function calcTextColor(hex)
{
	var r = parseInt(hex.substring(1,3), 16);
	var g = parseInt(hex.substring(3,5), 16);
	var b = parseInt(hex.substring(5,7), 16);

	if ((((r * 299) + (g * 587) + (b * 114)) / 1000) < 130) return 'white';
	else return 'black';
}

