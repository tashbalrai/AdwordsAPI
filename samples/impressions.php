<?php
require_once 'adwordsapi.php';
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

$result = $reportDef->getReportImpressions( NULL, $range, $sort, "CUSTOM_DATE", 'XML' );
$data = $reportDef->xmlToObject( $result );

}
catch( exception $e )
{
	print_r($e);
	exit;
}
?>

<html>
<body>
<h1>Impressions Report Example</h1>
<h3 style="color:red">These reports are coming from SANDBOX and these campaign are not actual and the data is test data. Data is coming 0 or nil because we are running reports on sandbox account. On live it will fetch the original data.</h3>
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
		<?php for ( $i=0; $i < ( count($data) - 2 ); $i++): ?>
		<tr>
			<?php foreach ( $headers as $value ):?>
			<td><?php echo $data[$i]->{$value}?></td>
			<?php endforeach; ?>
		</tr>
		<?php endfor; ?>
	</table>
</body>
</html>