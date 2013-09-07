<?php

require_once('api/lib/Database.class.php');

$db = new Database();

$termResults = $db->execute('SELECT DISTINCT Term FROM section');

?>

<html>
<head>
	<title>Term Master Schedule</title>
	<link rel="stylesheet" type="text/css" href="screen.css">
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
				filters = document.getElementsByClassName('filter'),
				timeFilters = document.getElementsByClassName('filter-time'),
				templates = {};

			$('script[type="text/html"]').each(function () {
				templates[this.id] = this.innerHTML;
			});

			function filterResults(results) {
				function filter(result) {
					var selectedTimes = [];

					for (var i = 0; i < timeFilters.length; i++) {
						if (timeFilters[i].checked) {
							selectedTimes.push(timeFilters[i].id);
						}
					}

					for (var i = 0; i < result.Times.length; i++) {
						for (var j = 0; j < result.Times[i].Day.length; j++) {
							if (selectedTimes.indexOf(result.Times[i].Day[j]) === -1) {
								return false;
							}
						}
					}

					for (var i = 0; i < filters.length; i++) {
						filter = new RegExp(filters[i].value, 'i');
						field = filters[i].id;
						if (!filter.test(result[field])) {
							return false;
						}
					}

					return true;
				}

				return results.filter(filter);
			}

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
				resultsContainer.innerHTML = Mustache.to_html(
					templates.ResultsTemplate,
					{ Results: results.slice(0, resultsPerPage) }, 
					templates
				);
			}

			resultsContainer.innerHTML = templates.LoadingTemplate;
			TMS.Get({ term: term.value }).done(function (results) {
				function rerender () {
					renderResults(filterResults(results));
				}

				results.sort(sortResults);

				for (var i = 0; i < filters.length; i++) {
					filters[i].addEventListener('keyup', rerender);
				}

				for (var i = 0; i < timeFilters.length; i++) {
					timeFilters[i].addEventListener('change', rerender);
				}

				renderResults(results);
			}).fail(function () {
				resultsContainer.innerHTML = '';
				alert('Could not load sections. Please try again.');
			});
		});
	</script>
</head>

<body>
	<h1 class="heading">Term Master Schedule</h1>

	<div class="highlight tbl-tab">
		Term:
		<select id="term">
			<?php foreach ($termResults as $result) { 
				echo "<option value=\"{$result['Term']}\">{$result['Term']}</option>";
			} ?>
		</select>
	</div>

	<table class="tbl">
		<thead class="tbl-label">
			<tr class="tbl-label-tr">
				<td width="7%">Subject</td>
				<td width="7%">Number</td>
				<td width="10%">Type</td>
				<td width="7%">Section</td>
				<td width="7%">CRN</td>
				<td width="22%">Title</td>
				<td width="20%">Time</td>
				<td width="20%">Instructor</td>
			</tr>
			<tr class="tbl-label-tr">
				<td><input id="Subject" class="filter" type="text" size="4" /></td>
				<td><input id="Number" class="filter" type="text" size="3" /></td>
				<td><input id="Type" class="filter" type="text" size="8" /></td>
				<td></td>
				<td></td>
				<td><input id="Title" class="filter" type="text" /></td>
				<td>
					<label for="M"><input class="filter-time" type="checkbox" id="M" checked />M</label>
					<label for="T"><input class="filter-time" type="checkbox" id="T" checked />T</label>
					<label for="W"><input class="filter-time" type="checkbox" id="W" checked />W</label>
					<label for="R"><input class="filter-time" type="checkbox" id="R" checked />R</label>
					<label for="F"><input class="filter-time" type="checkbox" id="F" checked />F</label>
				</td>
				<td></td>
			</tr>
		</thead>
		<tbody id="results">
		</tbody>
	</table>
</body>

<script id="LoadingTemplate" type="text/html">
	<tr class="tbl-tr-empty">
		<td colspan="100">Loading...</td>
	</tr>
</script>

<script id="ResultsTemplate" type="text/html">
	{{^Results}}
	<tr class="tbl-tr tbl-tr-empty">
		<td colspan="100">
			<strong>No courses found.</strong> Try changing your filters or selecting a different term.
		</td>
	</tr>
	{{/Results}}
	{{#Results}}
		{{> CourseSectionTemplate}}
	{{/Results}}
</script>

<script id="CourseSectionTemplate" type="text/html">
	<tr class="tbl-tr">
		<td>{{Subject}}</td>
		<td>{{Number}}</td>
		<td>{{Type}}</td>
		<td>{{Section}}</td>
		<td>{{CRN}}</td>
		<td>{{Title}}</td>
		<td>
			{{^Times}}TBD{{/Times}}
			{{#Times}}
				<span class="l-tr">
					<span class="l-td">{{Day}}</span> <span class="l-td">{{Time}}</span>
				</span>
			{{/Times}}
		</td>
		<td>{{Instructor}}</td>
	</tr>
</script>


</html>