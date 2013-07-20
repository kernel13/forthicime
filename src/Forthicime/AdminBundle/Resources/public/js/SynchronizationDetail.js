

function SynchronizationDetail(synchId)
{
	var __self = this;
	this.__synchId = synchId;


	this.Run = Run;
	this.DisplaySynchronizationDetail = DisplaySynchronizationDetail;
	this.BuildHtml = BuildHtml;
	this.GetSynchId = GetSynchId;
	this.filter = filter;
	this.UpdateItemVisibility = UpdateItemVisibility;
	this.IsSuccess = IsSuccess;
	this.ShowSuccess = ShowSuccess;
	this.IsError = IsError;
	this.ShowError = ShowError;

	function GetSynchId()
	{
		return this.__synchId;	
	}

	function Run()
	{
		if($("#document>div").length > 2)
		{
			$("#document>div:first-child").animate({width: '0'}, function(){
				__self.DisplaySynchronizationDetail();
	
				$.getJSON("/forthicime/admin/synchronizationDetail/medecin/" + __self.__synchId, function(data, textStatus, jqXHR){					
					$( "#tabs" ).tabs({ active: 0 });
					$("#ui-tabs-1").html(__self.BuildHtml(data));	
					__self.filter();
				});	
			});
		} else {
			__self.DisplaySynchronizationDetail();			
			$.getJSON("/forthicime/admin/synchronizationDetail/medecin/" + __self.__synchId, function(data, textStatus, jqXHR){					
				  	$( "#tabs" ).tabs({ active: 0 });
					$("#ui-tabs-1").html(__self.BuildHtml(data));	
					__self.filter();
				});
		}	
	}
 
	function DisplaySynchronizationDetail()
	{
		if($("#document>div").length > 2)
		{

			$("#document>div:first-child").remove();

			$(".content>h3").first().css("font-size", "12pt");
			$(".synchronization table tr").each(function(index, element){
					$(element).children().last().remove();
					$(element).children().last().remove();
					$(element).children().last().remove();
					$(element).children().last().remove();
			});

			$("#document>div:first-child").animate({width: '200px'}, function(){
				$("#document>div:first-child").removeClass("span3");
			});

			$("#document>div:last-child").removeClass("span3");
			$("#document>div:last-child").addClass("span9");
							
			$("#synchronizationDetail").hide();
			$("#synchronizationDetail").fadeIn('slow');
		}
	}

	function BuildHtml(data)
	{
		 var html = "<table class='table table-striped'>";
			 html = html + "<tr>";
			 html = html + "<th>ID</th>";
			 html = html + "<th>Action</th>";
			 html = html + "<th>Code Retour</th>";
			 html = html + "<th>Table</th>";
			 html = html + "</tr>";
		for(var item in data){
			 html = html + "<tr>";
			 html = html + "<td>" + data[item].id + "</td>";
			 html = html + "<td>" + data[item].command + "</td>";
			 html = html + "<td>" + data[item].returnCode + "</td>";
			 html = html + "<td>" + data[item].tableName + "</td>";
			 html = html + "</tr>";
		}

		html = html + "</table>";

		return html;
	}

	function filter()
	{

		// Ajout
		if(!$('#sync_filter_form input[type=checkbox]').eq(0).is(':checked'))
		{			
			$("#synchronizationDetail table tr").each(function(index, element){				
				if($(this).children("td").eq(1).text() == "Ajout")									
					$(this).hide();
			});
		}
		else
		{
			$("#synchronizationDetail table tr").each(function(index, element){
				if($(this).children("td").eq(1).text() == "Ajout")	
					__self.UpdateItemVisibility(this);
			});
		}

		// Modification
		if(!$('#sync_filter_form input[type=checkbox]').eq(1).is(':checked'))
		{
			$("#synchronizationDetail table tr").each(function(index, element){
				if($(this).children("td").eq(1).text() == "Modif")					
					$(this).hide();
			});
		}
		else
		{
			$("#synchronizationDetail table tr").each(function(index, element){
				if($(this).children("td").eq(1).text() == "Modif")					
					__self.UpdateItemVisibility(this);
			});
		}

		// Supprime
		if(!$('#sync_filter_form input[type=checkbox]').eq(2).is(':checked'))
		{
			$("#synchronizationDetail table tr").each(function(index, element){
				if($(this).children("td").eq(1).text() == "Supprime")					
					$(this).hide();
			});
		}
		else
		{
			$("#synchronizationDetail table tr").each(function(index, element){
				if($(this).children("td").eq(1).text() == "Supprime")					
					__self.UpdateItemVisibility(this);
			});
		}
	}

	function UpdateItemVisibility(item)
	{
		if(__self.IsSuccess(item) && __self.ShowSuccess())				
			$(item).show();
		else if(__self.IsError(item) && __self.ShowError())
			$(item).show();
		else		
			$(item).hide();	
	}

	function IsSuccess(elem)
	{
		return $(elem).children("td").eq(2).text() == 0;
	}

	function ShowSuccess()
	{
		return $('#sync_filter_form input[type=checkbox]').eq(3).is(':checked');
	}

	function IsError(elem)
	{
		return $(elem).children("td").eq(2).text() != 0;
	}

	function ShowError()
	{
		return $('#sync_filter_form input[type=checkbox]').eq(4).is(':checked');
	}
}