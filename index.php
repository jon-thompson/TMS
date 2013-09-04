<?php

require_once('api/lib/Database.class.php');

$db = new Database();

$termResults = $db->execute('SELECT DISTINCT Term FROM section');

?>

<html>
<head>
	<title>Term Master Schedule</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="vendor/mustache.js"></script>
	<script type="text/javascript">
		var TMS = (function () {
			var apiUrl = 'api/';

			function get(args) {
				return $.ajax({
					url: apiUrl + 'get.php',
					dataType: 'json',
					data: args
				});
			}

			return {
				Get: get
			};
		})();
	</script>
	<script type="text/javascript">
		$(function () {
			var resultsContainer = document.getElementById('results'),
				resultsPerPage = 20,
				term = document.getElementById('term'),
				templates = {};

			$('script[type="text/html"]').each(function () {
				templates[this.id] = this.innerHTML;
			});

			function sortResults (a, b) {
				if (a.Subject < b.Subject) {
					return -1;
				}
				else if (a.Subject > b.Subject) {
					return 1;
				}

				if (a.Number - b.Number !== 0) {
					return a.Number - b.Number;
				}

				if (isNaN(a.Section) && isNaN(b.Section)) {
					if (a.Section < b.Section) {
						return -1;
					}
					else if (a.Section > b.Section) {
						return 1;
					}
					else {
						return 0;
					}
				}

				if (isNaN(a.Section)) {
					return -1;
				}

				if (isNaN(b.Section)) {
					return 1;
				}

				return a.Section - b.Section;
			}

			function renderResults (results) {
				results.sort(sortResults);
				resultsContainer.innerHTML = Mustache.to_html(
					templates.ResultsTemplate,
					{ Results: results.slice(0, resultsPerPage) }, 
					templates
				);
			}

			resultsContainer.innerHTML = templates.LoadingTemplate;
			TMS.Get({ term: term.value }).done(renderResults).fail(function () {
				resultsContainer.innerHTML = '';
				alert('Could not load sections. Please try again.');
			});
		});
	</script>
</head>

<body>
	<h1>Term Master Schedule</h1>

	<p>
		Term:
		<select id="term">
			<?php foreach ($termResults as $result) { 
				echo "<option value=\"{$result['Term']}\">{$result['Term']}</option>";
			} ?>
		</select>
	</p>

	<table>
		<thead>
			<tr>
				<td>Subject</td>
				<td>Number</td>
				<td>Type</td>
				<td>Section</td>
				<td>CRN</td>
				<td>Title</td>
				<td>Time</td>
				<td>Instructor</td>
			</tr>
			<tr>
				<td><input type="text" /></td>
				<td><input type="text" /></td>
				<td></td>
				<td></td>
				<td></td>
				<td><input type="text" /></td>
				<td>
					<label for="M"><input type="checkbox" id="M" />M</label>
					<label for="T"><input type="checkbox" id="T" />T</label>
					<label for="W"><input type="checkbox" id="W" />W</label>
					<label for="R"><input type="checkbox" id="R" />R</label>
					<label for="F"><input type="checkbox" id="F" />F</label>
				</td>
				<td></td>
			</tr>
		</thead>
		<tbody id="results">
		</tbody>
	</table>
</body>

<script id="LoadingTemplate" type="text/html">
	<tr>Loading...</tr>
</script>

<script id="ResultsTemplate" type="text/html">
	{{#Results}}
		{{> CourseSectionTemplate}}
	{{/Results}}
</script>

<script id="CourseSectionTemplate" type="text/html">
	<tr>
		<td>{{Subject}}</td>
		<td>{{Number}}</td>
		<td>{{Type}}</td>
		<td>{{Section}}</td>
		<td>{{CRN}}</td>
		<td>{{Title}}</td>
		<td></td>
		<td>{{Instructor}}</td>
	</tr>
</script>


</html>