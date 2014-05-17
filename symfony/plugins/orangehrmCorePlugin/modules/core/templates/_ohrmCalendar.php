<?php
//nikoloup
//Include Calendar and css

//JS Globals
echo "<script type=\"text/javascript\">";
echo "var eventData = ".htmlspecialchars_decode($events).";";
echo "var tableData = ".htmlspecialchars_decode($table_events).";";
echo "var holidayData = ".htmlspecialchars_decode($holidays).";";
echo "var colorCodes = ".htmlspecialchars_decode($color_codes).";";
echo "var reqMonth = ".htmlspecialchars_decode($reqMonth).";";
echo "var reqYear = ".htmlspecialchars_decode($reqYear).";";
echo "</script>";

//CSS
echo stylesheet_tag('../orangehrmCorePlugin/css/fullcalendar');
echo stylesheet_tag('../orangehrmCorePlugin/css/calendar_gen');

//JS
echo javascript_include_tag('../orangehrmCorePlugin/js/fullcalendar.min.js');
echo javascript_include_tag('../orangehrmCorePlugin/js/tablecalendar.js');
echo javascript_include_tag('../orangehrmCorePlugin/js/calsInit.js');
echo javascript_include_tag('../orangehrmCorePlugin/js/jscolor/jscolor.js');

//Legend (HTML)
echo htmlspecialchars_decode($legend);

