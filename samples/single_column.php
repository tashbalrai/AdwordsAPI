<?php
require_once 'adwordsapi.php';
if ( isset( $_POST['submit'] ) )
{
	try
	{
	$a = new AdwordsApi();
	$reportDef = $a->getReportDefinitionService();

	$range = array( 'min' => '20110201', 'max' => '20110901' );
	$sort = array(
		array(
			'field' => 'CampaignName',
			'sortOrder' => 'ASCENDING'
		)
	);
	
	$conditions = null;
	$campaign = trim( $_POST['campaign'] );
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

	$column = trim( $_POST['field'] );

	$result = $reportDef->getReportCustomFields( $conditions, $range, $sort, "LAST_7_DAYS", 'XML', 
		array( $column ) 
	);
	$data = $reportDef->xmlToObject( $result );

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
<h1>Single Fields Example</h1>
<h3 style="color:red">These reports are coming from SANDBOX and these campaign are not actual and the data is test data. Data is coming 0 or nil because we are running reports on sandbox account. On live it will fetch the original data.</h3>
<form method = "post" action="single_column.php">
	<em>These fields are just for demonstration actually there could be more fields/columns.</em><br/>
	Select The Column:
	<select name="field">
		<option value="CampaignId">Campaign ID</option>
		<option value="CampaignName">Campaign Name</option>
		<option value="AverageCpc">Average CPC</option>
		<option value="Clicks">Clicks</option>
		<option value="Amount">Budget</option>
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
	<br/><input type="submit" name="submit" value="Generate Report"/>
</form>
	<?php if ( !empty( $data ) ):?>
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
	<?php endif; ?>
<?php if ( !empty( $result ) ):?>
<br/><br/>Raw Results Format:
<span class="result"><pre><?php echo wordwrap(htmlentities($result),100)?></pre></span>
<?php endif;?>
<?php if ( !empty( $data ) ):?>
<br/><br/>Object Format:
<span class="result"><pre><?php print_r( $data )?></pre></span>
<?php endif;?>
<br/>
	<br/>
<div class="code">
	<pre>
	<span class="comment">//Include the API class</span>
	require_once 'adwordsapi.php';
	if ( isset( $_POST['submit'] ) )
	{
		try
		{
		$a = new AdwordsApi();
		$reportDef = $a->getReportDefinitionService();

		$range = array( 'min' => '20110201', 'max' => '20110901' );
		$sort = array(
			array(
				'field' => 'CampaignName',
				'sortOrder' => 'ASCENDING'
			)
		);
		
		$conditions = null;
		$campaign = trim( $_POST['campaign'] );
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

		$column = trim( $_POST['field'] );

		$result = $reportDef->getReportCustomFields( $conditions, $range, $sort, "LAST_7_DAYS", 'XML', 
			array( $column ) 
		);
		$data = $reportDef->xmlToObject( $result );

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