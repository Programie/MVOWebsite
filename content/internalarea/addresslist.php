<script type="text/html" id="addresslist_groupbox_template">
	{{#.}}
		<input type="checkbox" id="addresslist_groupcheckbox_{{name}}" data-name="{{name}}" checked/><label for="addresslist_groupcheckbox_{{name}}">{{title}}</label>
	{{/.}}
</script>

<script type="text/html" id="addresslist_tbody_template">
	{{#.}}
		<tr class="addresslist-row {{#groups}}addresslist-group-{{.}} {{/groups}}" data-id="{{id}}">
			<td class="no-print"><input type="checkbox"/></td>
			<td>{{firstName}}</td>
			<td>{{lastName}}</td>
			<td><a href="mailto:{{email}}">{{email}}</a></td>
			<td>
				{{#phoneNumbers}}
					<div>{{category}} ({{subCategory}}): {{number}}</div>
				{{/phoneNumbers}}
			</td>
		</tr>
	{{/.}}
</script>

<h1>Adressenliste</h1>

<fieldset>
	<legend>Gruppen</legend>
	<div id="addresslist_groupbox"></div>
</fieldset>

<table id="addresslist_table" class="table {sortlist: [[2,0],[1,0]]}">
	<thead>
		<tr>
			<th class="no-print" data-sorter="false"><input type="checkbox" id="addresslist_table_checkall"/></th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Email</th>
			<th>Telefon</th>
		</tr>
	</thead>
	<tbody id="addresslist_tbody"></tbody>
</table>

<fieldset id="addresslist_sendmessage" class="no-print">
	<legend>Nachricht senden</legend>

	<form action="/internalarea/addresslist" method="post" enctype="multipart/form-data">
		<textarea id="addresslist_sendmessage_text" name="addresslist_sendmessage_text" rows="15" cols="15"></textarea>

		<fieldset id="addresslist_sendmessage_attachments">
			<legend>Anh&auml;nge</legend>
		</fieldset>

		<input type="hidden" id="addresslist_sendmessage_sendcopy" name="sendCopy"/>
		<input type="hidden" id="addresslist_sendmessage_recipients" name="recipients"/>
		<input type="hidden" name="sendToken" value="<?php echo TokenManager::getSendToken("addresslist_sendmessage", true); ?>"/>
		<input type="submit" value="Senden"/>
	</form>
</fieldset>

<div id="addresslist_sendmessage_confirm" title="Nachricht senden">
	<p id="addresslist_sendmessage_confirm_text1"></p>
	<ul id="addresslist_sendmessage_confirm_recipients"></ul>

	<p id="addresslist_sendmessage_confirm_text2"><b>Anh&auml;nge:</b></p>
	<ul id="addresslist_sendmessage_confirm_attachments"></ul>

	<input id="addresslist_sendmessage_confirm_sendcopy" type="checkbox"/><label for="addresslist_sendmessage_confirm_sendcopy">Eine Kopie an mich senden</label>
</div>