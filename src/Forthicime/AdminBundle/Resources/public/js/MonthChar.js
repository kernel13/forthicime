function MonthChart(url)
{
	var __self = this;
	this.__data = [];
	this.__url = url;
	this.__googleLoaded = false;	

	this.drawChart = drawChart;
	this.__drawChart = __drawChart;
	this.__getData = __getData;


	// Public method

	//
	//	drawChart
	//
	function drawChart(year, month)
	{
		$("#month_chart_div").empty();
		__self.__getData(year, month);

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
		__self.__data.unshift(['Jour', 'Nombre de connections', 'Nombre de dossiers visualisés']);

		var data = google.visualization.arrayToDataTable(__self.__data);

	    var options = {
	      title: 'Activités entregistrées',
	      hAxis: {title: 'Jour', titleTextStyle: {color: 'red'}}
	    };

	    var chart = new google.visualization.LineChart(document.getElementById('month_chart_div'));
	    chart.draw(data, options);			 
	}

	//
	//	__getData
	//
	function __getData(year, month)
	{
		jQuery.ajax({
	         url:    __self.__url,
	         data: 	{'year' : year, 'month': month},
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