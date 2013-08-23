 
function ChartBart(url)
{
	var __self = this;
	this.__data = [];
	this.__url = url;
	this.__year = "";

	this.__googleLoaded = false;

	this.drawChart = drawChart;
	this.__drawChart = __drawChart;
	this.__getData = __getData;


	// Public method

	//
	//	drawChart
	//
	function drawChart(year)
	{
		__self.__year = year;
		$("#chart_div").empty();
		__self.__getData(year);

		if(__self.__googleLoaded){
			__self.__drawChart();
		}else{
			// Google callback
			google.setOnLoadCallback(__self.__drawChart);
			google.load("visualization", "1", {packages:["corechart"]});  	
		}
			    				
	}

	// Private method

	//
	//	__drawChart
	//
	function __drawChart() 
	{	
		__self.__googleLoaded = true;
		__self.__data.unshift(['Year', 'Nombre de connections', 'Nombre d\'analyses visualisés']);

		var data = google.visualization.arrayToDataTable(__self.__data);

	    var options = {
	      title: "Activitées: Année " + __self.__year,
	      hAxis: {title: 'Mois', titleTextStyle: {color: 'red'}}
	    };

	    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
	    chart.draw(data, options);			 
	}

	//
	//	__getData
	//
	function __getData(year)
	{
		jQuery.ajax({
	         url:    __self.__url,
	         data: 	{ 'year': year},
	         success: function(data, status, jqXHR) {
	         			if(status == "success")
	                     	__self.__data = data;
	                     else
	                     	__self.__data = [];
	                  },
	         async:   false,
	         dataType: "JSON"
    	}); 		 
	}


}

  
  	


	