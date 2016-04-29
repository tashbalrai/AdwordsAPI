<?php
require_once 'adwordsapi.php';

if  ( isset( $_POST['submit'] ) )
{
	$reportType = trim( $_POST['report'] );
	$campaign = trim( $_POST['campaign'] );
	$rangeType = trim( $_POST['range_type'] );
	$startDate = trim( $_POST['start_date'] );
	$endDate = trim( $_POST['end_date'] );
	$format = trim( $_POST['format'] );
	
	try
	{
		$a = new AdwordsApi();
		$reportDef = $a->getReportDefinitionService();
		
		$conditions = null;
		if ( !empty( $campaign ) )
		{
			$conditions = array(
				array(
					'field' => 'CampaignId',
					'operator' => 'IN',
					'values' => array( $campaign )
				)
			);
		}
		
		if ( empty( $rangeType ) )
		{
			$rangeType = null;
		}
		
		$range = array( 'min' => $startDate, 'max' => $endDate );

		$sort = array(
			array(
				'field' => 'CampaignName',
				'sortOrder' => 'ASCENDING'
			)
		);

		if ( empty( $format ) )
		{
			$format = 'XML';
		}
				
		switch( strtolower( $reportType ) )
		{
			case "impressions":
				$result = $reportDef->getReportImpressions( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "clicks":
				$result = $reportDef->getReportClicks( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "cpc":
				$result = $reportDef->getReportCpc( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "ctr":
				$result = $reportDef->getReportCtr( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "cpconversion":
				$result = $reportDef->getReportCostPerConversions( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "nc":
				$result = $reportDef->getReportNumberConversions( $conditions, $range, $sort, $rangeType, $format );
				break;
			case "tc":
				$result = $reportDef->getReportTotalCost( $conditions, $range, $sort, $rangeType, $format );
		}
		
		if ( strtolower( $format ) == 'xml' )
		{
			$data = $reportDef->xmlToObject( $result );
		}
		else
		{
			$data = $reportDef->csvToObject( $result );
		}

	}
	catch( exception $e )
	{
		print_r($e);
		exit;
	}
}
?>

<html>
<head>
<style>
.comment{
	color:green;
}
.code{
	background:#F5F5F5;
	border:2px solid black;
}
.result{
	color: blue;
}
</style>
</head>
<body>
<h1>Single Campaign Report Example</h1>
<h3 style="color:red">These reports are coming from SANDBOX and these campaign are not actual and the data is test data. Data is coming 0 or nil because we are running reports on sandbox account. On live it will fetch the original data.</h3>
<br/>
<form method="post" action="single_campaign.php">
Report Type:
<select name="report">
	<option value="impressions">Impressions Report</option>
	<option value="clicks">Clicks</option>
	<option value="cpc">Cost Per Click</option>
	<option value="ctr">Click Thru Rate</option>
	<option value="cpconversion">Cost Per Conversion</option>
	<option value="nc">Number of Conversions</option>
	<option value="tc">Total Cost</option>
</select>
Select Campaign:
<select name="campaign">
	<option value="453875">Campaign Fifth #1321257955</option>
	<option value="453869">Campaign First #1321257812</option>
	<option value="453877">Campaign Seventh #1321258075</option>
	<option value="453872">Campaign Third #1321257904</option>
	<option value="446325">Interplanetary Cruise #1320739876</option>
	<option value="446419">Interplanetary Cruise #1320745249</option>
	<option value="">All</option>
</select>
<br/>
<script type="text/javascript">
function showdate( obj )
{
	if(obj.value == 'CUSTOM_DATE')
	{
		document.getElementById('date').style.display = 'block';
	} else {
		document.getElementById('date').style.display = 'none';
	}
}
</script>
Date Range Type:
<select name="range_type" onchange="showdate(this)">
	<option value="CUSTOM_DATE">Custom Date Range</option>
	<option value="LAST_7_DAYS">Last 7 Days</option>
	<option value="THIS_WEEK_SUN_TODAY">This Week Sun-Today</option>
	<option value="THIS_WEEK_MON_TODAY">This Week Mon-Today</option>
	<option value="LAST_WEEK">Last Week</option>
	<option value="LAST_14_DAYS">Last 14 Days</option>
	<option value="LAST_30_DAYS">Last 30 Days</option>
	<option value="LAST_BUSINESS_WEEK">Last Business Week</option>
	<option value="LAST_WEEK_SUN_SAT">Last Week Sun-Sat</option>
	<option value="THIS_MONTH">This Month</option>
	<option value="LAST_MONTH">Last Month</option>
	<option value="ALL_TIME">All Times</option>
</select>
<div id="date">
	Start Date(YYYYMMDD): <input type="text" name="start_date" />
	End Date(YYYYMMDD): <input type="text" name="end_date" />
</div>
Result Format:
XML <input type="radio" name="format" value="XML" checked="checked"/> / CSV<input type="radio" name="format" value="CSV"/>
<br/><input type="submit" name="submit" value="Generate Report"/>
</form>
<?php if ( !empty( $data ) ):?>
<br/>Object Parsed Data In Table Format:
<table border="1">
	<tr>
	<?php foreach ( $data['headers'] as $key => $name ): ?>
		<th>
		<?php 
		$headers[] = $key;
		echo $name;
		?>
		</th>
	<?php endforeach; ?>
	</tr>
	<?php for ( $i=0; $i < ( count($data) - 1 ); $i++): ?>
	<tr>
		<?php foreach ( $headers as $value ):?>
		<td><?php echo $data[$i]->{$value}?></td>
		<?php endforeach; ?>
	</tr>
	<?php endfor; ?>
</table>
<?php endif;?>
<?php if ( !empty( $result ) ):?>
<br/><br/>Raw Results Format <?php echo strtoupper($format)?>:
<span class="result"><pre><?php echo wordwrap(htmlentities($result),100)?></pre></span>
<?php endif;?>
<?php if ( !empty( $data ) ):?>
<br/><br/>Object Format:
<span class="result"><pre><?php print_r( $data )?></pre></span>
<?php endif;?>
<br/>
<div class="code">
	<pre>
	<span class="comment">//Include the API class</span>
	require_once 'adwordsapi.php';

	if  ( isset( $_POST['submit'] ) )
	{
		$reportType = trim( $_POST['report'] );
		$campaign = trim( $_POST['campaign'] );
		$rangeType = trim( $_POST['range_type'] );
		$startDate = trim( $_POST['start_date'] );
		$endDate = trim( $_POST['end_date'] );
		$format = trim( $_POST['format'] );
		
		try
		{
			<span class="comment">//Create object of the Adwords API class</span>
			$a = new AdwordsApi();
			<span class="comment">//fetch the report definition object to run the reports</span>
			$reportDef = $a->getReportDefinitionService();
			
			$conditions = null;
			if ( !empty( $campaign ) )
			{
				<span class="comment">/*Put condition to get filtered reports. 
				You can pass as many conditions to the reporting api and it will fetch the result for you according to 
				the specified conditions. If you want to get a single campaign then you can pass that condition to 
				the API just like i have done below.
				The field will specify the field name on which you want to filter the reports.
				The operator is the operation you wanted to perform on a certain field specified in the field index.
				The values is the list of values to evaluate against a given field.
				*/
				</span>
				$conditions = array(
					array(
						'field' => 'CampaignId',
						'operator' => 'IN',
						'values' => array( $campaign )
					)
				);
			}
			
			if ( empty( $rangeType ) )
			{
				$rangeType = null;
			}
			<span class="comment">/*Pass the start and end date to the API to filter the 
			reports according to the time frame specified. 
			Min is for specifying the minimum date range and max is for the maximum date range.*/</span>
			$range = array( 'min' => $startDate, 'max' => $endDate );
			
			<span class="comment">/*if you are selecting multiple campaign then you can sort the report.
			you need to pass an array of sort orders. 
			Field is the field name to sort and the sortOrder is to specify which sort order 
			is required on it ASCENDING or DESCENDING*/
			</span>
			$sort = array(
				array(
					'field' => 'CampaignName',
					'sortOrder' => 'ASCENDING'
				)
			);

			<span class="comment">/*We support two types of report result format one is 
			in XML you will get xml string as a result and another is the CSV. 
			you can pass which format you prefer for the report result. 
			Then report will be generated in that format.*/</span>
			if ( empty( $format ) )
			{
				$format = 'XML';
			}
					
			switch( strtolower( $reportType ) )
			{
				case "impressions":
					<span class="comment">//Get the Impressions Report</span>
					$result = $reportDef->getReportImpressions( $conditions, $range, $sort, $rangeType, $format );
				case "clicks":
					<span class="comment">//Get the clicks report</span>
					$result = $reportDef->getReportClicks( $conditions, $range, $sort, $rangeType, $format );
				case "cpc":
					<span class="comment">//Get the click per cost report</span>
					$result = $reportDef->getReportCpc( $conditions, $range, $sort, $rangeType, $format );
				case "ctr":
					<span class="comment">//Get the click thru rate report</span>
					$result = $reportDef->getReportCtr( $conditions, $range, $sort, $rangeType, $format );
				case "cpconversion":
					<span class="comment">//Get the cost per converion report</span>
					$result = $reportDef->getReportCostPerConversion( $conditions, $range, $sort, $rangeType, $format );
				case "nc":
					<span class="comment">//Get the number of conversions report</span>
					$result = $reportDef->getReportNumberConverions( $conditions, $range, $sort, $rangeType, $format );
				case "tc":
					<span class="comment">//Get the total cost report</span>
					$result = $reportDef->getReportTotalCost( $conditions, $range, $sort, $rangeType, $format );
			}
			
			<span class="comment">/*you can get the result in either XML or CSV format to further 
			assist developer we have create some helper functions. 
			You can easily convert your XML or CSV result in array of object. 
			Our helper function will parse the report result and will return you easy interface for accessing the results.*/</span>
			if ( strtolower( $format ) == 'xml' )
			{
				<span class="comment">//Convert XML results to object</span>
				$data = $reportDef->xmlToObject( $result );
			}
			else
			{
				<span class="comment">//Convert CSV results to object</span>
				$data = $reportDef->csvToObject( $result );
			}

		}
		catch( exception $e )
		{
			print_r($e);
			exit;
		}
	}
	</pre>
</div>

</body>
</html>