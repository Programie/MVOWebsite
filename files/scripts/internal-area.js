var addresslistSendMessageAttachmentFile = 0;

$(function()
{
	$.ajax(
	{
		dataType : "json", success : function(data)
		{
			var phoneNumberCategories =
			{
				fax : "Fax",
				mobile : "Mobil",
				phone : "Telefon"
			};

			var phoneNumberSubCategories =
			{
				business : "Gesch\u00e4ftlich",
				private : "Privat"
			};

			for (var index in data.users)
			{
				var phoneNumbers = data.users[index].phoneNumbers;
				for (var phoneNumbersIndex in phoneNumbers)
				{
					phoneNumbers[phoneNumbersIndex].category = phoneNumberCategories[phoneNumbers[phoneNumbersIndex].category];
					phoneNumbers[phoneNumbersIndex].subCategory = phoneNumberSubCategories[phoneNumbers[phoneNumbersIndex].subCategory];
				}
			}

			$("#addresslist_groupbox").html(Mustache.render($("#addresslist_groupbox_template").html(), data.groups));
			$("#addresslist_tbody").html(Mustache.render($("#addresslist_tbody_template").html(), data.users));

			$("#addresslist_table").tablesorter();

			updateAddressList();
		}, url : "?json"
	});

	$("#addresslist_table_checkall").change(function()
	{
		$(".addresslist-row").find(":visible :checkbox").prop("checked", this.checked);
	});

	$("#addresslist_tbody").on("change", ".addresslist-row :checkbox", updateAddressListCheckAllState);

	$("#addresslist_groupbox").on("change", ":checkbox", updateAddressList);

	$("#addresslist_sendmessage").find("form").submit(function(event)
	{
		// Is set if triggered by submit()
		if (event.isTrigger)
		{
			return;
		}

		event.preventDefault();

		var form = $(this);

		var recipients = [];
		$("#addresslist_sendmessage_confirm_recipients").html("");
		$(".addresslist-row").each(function ()
		{
			var cells = $(this).find("td");
			if ($(this).find(":checkbox").is(":checked"))
			{
				recipients.push($(this).attr("userid"));
				$("#addresslist_sendmessage_confirm_recipients").append("<li>" + cells.eq(1).html() + " " + cells.eq(2).html() + "</li>");
			}
		});

		if (!recipients.length)
		{
			alert("Keine Empf\u00e4nger ausgew\u00e4hlt!");
			return;
		}

		$("#addresslist_sendmessage_recipients").text(recipients.join(","));
		$("#addresslist_sendmessage_confirm_text1").text("Soll die Nachricht jetzt an die folgenden " + recipients.length + " Emp\u00e4nger gesendet werden?");


		$("#addresslist_sendmessage_confirm_attachments").html("");
		$("#addresslist_sendmessage_confirm_text2").hide();
		$(".addresslist_sendmessage_attachments_file").each(function ()
		{
			if ($(this)[0].files.length)
			{
				$("#addresslist_sendmessage_confirm_attachments").append("<li>" + $(this)[0].files[0].name + "</li>");
				$("#addresslist_sendmessage_confirm_text2").show();
			}
		});

		$("#addresslist_sendmessage_confirm").dialog(
		{
			closeText : "Schlie\u00dfen",
			resizable : false,
			modal : true,
			width : "auto",
			maxHeight : 500,
			autoOpen : true,
			buttons :
			{
				"Senden": function()
				{
					document.getElementById("addresslist_sendmessage_sendcopy").value = document.getElementById("addresslist_sendmessage_confirm_sendcopy").checked ? "1" : "0";
					form.submit();
				},
				"Abbrechen": function()
				{
					$(this).dialog("close");
				}
			}
		});
	});

	$("#addresslist_sendmessage_attachments").on("change", ".addresslist_sendmessage_attachments_file", checkAddressListSendMessageAttachmentFields);

	checkAddressListSendMessageAttachmentFields();
});

function addAttachmentFieldToAddresslistSendMessage()
{
	var element = $("<input>");
	element.attr("type", "file");
	element.addClass("addresslist_sendmessage_attachments_file");
	element.attr("id", "addresslist_sendmessage_attachments_file_" + addresslistSendMessageAttachmentFile);

	$("#addresslist_sendmessage_attachments").append(element);
}

function checkAddressListSendMessageAttachmentFields()
{
	var addNew = true;

	$(".addresslist_sendmessage_attachments_file").each(function ()
	{
		if (!$(this)[0].files.length)
		{
			if (!addNew)// Another field is already empty -> Remove this one
			{
				$(this).remove();
			}
			addNew = false;
		}
	});

	if (addNew)
	{
		addAttachmentFieldToAddresslistSendMessage();
	}
}

function updateAddressListCheckAllState()
{
	var checkAllCheckbox = $("#addresslist_table_checkall");

	var checked = 0;
	var unchecked = 0;

	$(".addresslist-row").find(":visible :checkbox").each(function()
	{
		if (this.checked)
		{
			checked++;
		}
		else
		{
			unchecked++;
		}
	});

	if (checked && unchecked)
	{
		checkAllCheckbox.prop("indeterminate", true);
	}
	else
	{
		checkAllCheckbox.prop("indeterminate", false);

		if (checked && !unchecked)
		{
			checkAllCheckbox.prop("checked", true);
		}
		else
		{
			checkAllCheckbox.prop("checked", false);
		}
	}
}

function updateAddressList()
{
	var visibleGroups = [];

	$("#addresslist_groupbox").find(":checkbox").each(function()
	{
		if (this.checked)
		{
			visibleGroups.push($(this).data("name"));
		}
	});

	$(".addresslist-row").each(function()
	{
		var isInGroup = false;

		for (var index in visibleGroups)
		{
			if ($(this).hasClass("addresslist-group-" + visibleGroups[index]))
			{
				isInGroup = true;
				break;
			}
		}

		if (isInGroup)
		{
			$(this).show();
		}
		else
		{
			$(this).hide();
		}
	});

	var tbodyElement = $("#addresslist_tbody");

	tbodyElement.find("tr").removeClass("odd even");
	tbodyElement.find("tr:visible:odd").addClass("odd");
	tbodyElement.find("tr:visible:even").addClass("even");

	updateAddressListCheckAllState();
}