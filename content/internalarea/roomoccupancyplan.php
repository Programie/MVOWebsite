<?php
	$allowEdit = Constants::$accountManager->hasPermission("roomoccupancyplan.edit");
?>
<h1>Proberaumbelegungsplan</h1>

<div id="roomoccupancyplan_calendar"/>

<div id="roomoccupancyplan_edit">
	<form id="roomoccupancyplan_edit_form">
		<label for="roomoccupancyplan_edit_title">Titel:</label>
		<input type="text" id="roomoccupancyplan_edit_title"/>
		
		<label for="roomoccupancyplan_edit_reservedby">Reserviert von:</label>
		<input type="text" id="roomoccupancyplan_edit_reservedby"/>
		
		<label for="roomoccupancyplan_edit_date">Datum:</label>
		<input type="text" id="roomoccupancyplan_edit_date" class="date" placeholder="TT.MM.JJJJ"/>
		
		<label for="roomoccupancyplan_edit_time_start">Zeit:</label>
		<div>
			<input type="text" id="roomoccupancyplan_edit_time_start" class="time" placeholder="HH:MM"/>
			<span>bis</span>
			<input type="text" id="roomoccupancyplan_edit_time_end" class="time" placeholder="HH:MM"/>
		</div>
		
		<input type="checkbox" id="roomoccupancyplan_edit_weekly"/>
		<label for="roomoccupancyplan_edit_weekly">W&ouml;chentlich wiederholen bis:</label>
		<input type="text" id="roomoccupancyplan_edit_endrepeat" class="date" placeholder="TT.MM.JJJJ"/>
	</form>
</div>

<script type="text/javascript">
	$("#roomoccupancyplan_calendar").fullCalendar(
	{
		allDayDefault : false,
		allDaySlot : false,
		axisFormat : "HH:mm",
		buttonText :
		{
			day : "Tag",
			month : "Monat",
			today : "Heute",
			week : "Woche"
		},
		columnFormat :
		{
			day : "dddd",
			month : "dddd",
			week : "ddd, dd.MM."
		},
		dayNames : ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
		dayNamesShort : ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
		editable : <?php echo (int) $allowEdit;?>,
		eventClick : function(event, jsEvent, view)
		{
			<?php echo $allowEdit ? "" : "return;"?>
			$("#roomoccupancyplan_edit_title").val(event.title);
			$("#roomoccupancyplan_edit_reservedby").val(event.reservedBy);
			$("#roomoccupancyplan_edit_date").datepicker("setDate", event.start);
			$("#roomoccupancyplan_edit_time_start").timepicker("setDate", event.start);
			$("#roomoccupancyplan_edit_time_end").timepicker("setDate", event.end);
			$("#roomoccupancyplan_edit_weekly").prop("checked", event.weekly);
			$("#roomoccupancyplan_edit_weekly").trigger("change");
			if (event.endRepeat)
			{
				$("#roomoccupancyplan_edit_endrepeat").datepicker("setDate", event.endRepeat);
			}
			else
			{
				$("#roomoccupancyplan_edit_endrepeat").val("");
			}
			$("#roomoccupancyplan_edit").dialog("option", "title", "Eintrag bearbeiten");
			$("#roomoccupancyplan_edit").dialog("open");
			alert("Das Bearbeiten von Eintr\u00e4gen steht derzeit noch nicht zur Verf\u00fcgung!");
		},
		eventDrop : function(event, dayDelta, minuteDelta)
		{
			$.ajax(
			{
				type : "POST",
				url : "/internalarea/roomoccupancyplan/moveevent",
				data :
				{
					roomoccupancyplan_eventid : event.id,
					roomoccupancyplan_daydelta : dayDelta,
					roomoccupancyplan_minutedelta : minuteDelta
				},
				error : function(jqXhr, textStatus, errorThrown)
				{
					noty(
					{
						type : "error",
						text : "Fehler beim Speichern der &Auml;nderung!"
					});
				},
				success : function(data, status, jqXhr)
				{
					if (data == "ok")
					{
						noty(
						{
							type : "success",
							text : "&Auml;nderung gespeichert"
						});
						$("#roomoccupancyplan_calendar").fullCalendar("refetchEvents");
					}
					else
					{
						noty(
						{
							type : "error",
							text : "Fehler beim Speichern der &Auml;nderung!"
						});
					}
				}
			});
		},
		eventResize : function(event, dayDelta, minuteDelta)
		{
			$.ajax(
			{
				type : "POST",
				url : "/internalarea/roomoccupancyplan/resizeevent",
				data :
				{
					roomoccupancyplan_eventid : event.id,
					roomoccupancyplan_minutedelta : minuteDelta
				},
				error : function(jqXhr, textStatus, errorThrown)
				{
					noty(
					{
						type : "error",
						text : "Fehler beim Speichern der &Auml;nderung!"
					});
				},
				success : function(data, status, jqXhr)
				{
					if (data == "ok")
					{
						noty(
						{
							type : "success",
							text : "&Auml;nderung gespeichert"
						});
						$("#roomoccupancyplan_calendar").fullCalendar("refetchEvents");
					}
					else
					{
						noty(
						{
							type : "error",
							text : "Fehler beim Speichern der &Auml;nderung!"
						});
					}
				}
			});
		},
		events : "/internalarea/roomoccupancyplan/getevents",
		firstDay : 1,
		firstHour : 12,
		header :
		{
			left : "prev,next title",
			center : "",
			right : "today month,agendaWeek,agendaDay"
		},
		monthNames : ["Januar", "Februar", "M\u00e4rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
		monthNamesShort : ["Jan", "Feb", "M\u00e4r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
		selectable : <?php echo (int) $allowEdit;?>,
		selectHelper : true,
		select : function(start, end, allDay)
		{
			$("#roomoccupancyplan_edit_title").val("");
			$("#roomoccupancyplan_edit_reservedby").val("");
			$("#roomoccupancyplan_edit_date").datepicker("setDate", start);
			if (allDay)
			{
				$("#roomoccupancyplan_edit_time_start").val("");
				$("#roomoccupancyplan_edit_time_end").val("");
			}
			else
			{
				$("#roomoccupancyplan_edit_time_start").timepicker("setDate", start);
				$("#roomoccupancyplan_edit_time_end").timepicker("setDate", end);
			}
			$("#roomoccupancyplan_edit_weekly").prop("checked", false);
			$("#roomoccupancyplan_edit_weekly").trigger("change");
			$("#roomoccupancyplan_edit_endrepeat").val("");
			$("#roomoccupancyplan_edit").dialog("option", "title", "Eintrag bearbeiten");
			$("#roomoccupancyplan_edit").dialog("open");
		},
		slotMinutes : 15,
		theme : true,
		timeFormat :
		{
			agenda : "HH:mm{ - HH:mm}",
			"" : "HH:mm{ - HH:mm}"
		},
		titleFormat :
		{
			day : "dddd, dd.MM.yyyy",
			month : "MMMM yyyy",
			week : "dd.[ MMM][ yyyy]{ '&#8212;' dd. MMM yyyy}"
		},
		unselectCancel : "#roomoccupancyplan_edit",
		weekNumberTitle : "KW"
	});
	
	$("#roomoccupancyplan_edit").dialog(
	{
		autoOpen : false,
		closeText : "Schlie&szlig;en",
		height : "auto",
		minWidth : 350,
		modal : true,
		buttons :
		{
			"OK" : function()
			{
				if ($("#roomoccupancyplan_edit_title").val())
				{
					if ($("#roomoccupancyplan_edit_date").val())
					{
						if ($("#roomoccupancyplan_edit_time_start").val())
						{
							if ($("#roomoccupancyplan_edit_time_end").val())
							{
								$.ajax(
								{
									type : "POST",
									url : "/internalarea/roomoccupancyplan/editevent",
									data :
									{
										roomoccupancyplan_eventid : $("#roomoccupancyplan_edit_id").val(),
										roomoccupancyplan_title : $("#roomoccupancyplan_edit_title").val(),
										roomoccupancyplan_reservedby : $("#roomoccupancyplan_edit_reservedby").val(),
										roomoccupancyplan_date : $("#roomoccupancyplan_edit_date").val(),
										roomoccupancyplan_starttime : $("#roomoccupancyplan_edit_time_start").val(),
										roomoccupancyplan_endtime : $("#roomoccupancyplan_edit_time_end").val(),
										roomoccupancyplan_weekly : $("#roomoccupancyplan_edit_weekly").prop("checked") ? 1 : 0,
										roomoccupancyplan_endrepeat : $("#roomoccupancyplan_edit_endrepeat").val()
									},
									error : function(jqXhr, textStatus, errorThrown)
									{
										noty(
										{
											type : "error",
											text : "Fehler beim Speichern der &Auml;nderung!"
										});
									},
									success : function(data, status, jqXhr)
									{
										switch (data)
										{
											case "invalid date":
												alert("Ung\u00fcltiges Datum!");
												break;
											case "ok":
												noty(
												{
													type : "success",
													text : "&Auml;nderung gespeichert"
												});
												$("#roomoccupancyplan_calendar").fullCalendar("refetchEvents");
												$("#roomoccupancyplan_edit").dialog("close");
												break;
											default:
												noty(
												{
													type : "error",
													text : "Fehler beim Speichern der &Auml;nderung!"
												});
												break;
										}
									}
								});
							}
							else
							{
								alert("Keine Endzeit angegeben!");
							}
						}
						else
						{
							alert("Keine Startzeit angegeben!");
						}
					}
					else
					{
						alert("Kein Datum angegeben!");
					}
				}
				else
				{
					alert("Kein Titel angegeben!");
				}
			},
			"Abbrechen" : function()
			{
				$(this).dialog("close");
			}
		}
	});
	
	$("#roomoccupancyplan_edit_weekly").change(function()
	{
		$("#roomoccupancyplan_edit_endrepeat").prop("disabled", !this.checked);
	});
</script>