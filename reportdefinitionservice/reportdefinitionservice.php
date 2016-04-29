<?php
/*********************************************************************
* Class representing the ReportDefinitionService of the Google Adwords
* API. This class extends the core AdwordsApi class and provides 
* functionality for creating various reports.
*
* PHP Version 5.3
*
* @package		Adwords API Reporting
* @category		Adwords Reporting Web Services
* @author		  Vipan Balrai
**********************************************************************/
class ReportDefinitionService extends AdwordsApi
{
	/***************************************************************
	* Holds the soap client object created by the constructor for 
	* this web service.
	*
	* @access	protected
	* @var		object soapclient object
	****************************************************************/
	protected $soapClient = NULL;

	/***************************************************************
	* Store end point URL for making ad hoc reporting request to 
	* get the report data
	*
	* @access	protected
	* @var		string
	****************************************************************/
	protected $adhocPostUrl = 'api/adwords/reportdownload/v201109';

	/***************************************************************
	* Store the xml selector data for report download via ad hoc 
	* method.
	*
	* @access	protected
	* @var		string XML data
	****************************************************************/
	protected $__rdxml = NULL;

	/***************************************************************
	* Registers the supported date range types for reporting 
	* selector of the web service
	*
	* @access	protected
	* @var		array
	****************************************************************/
	protected $dateRangeTypes = array(
		'TODAY', 'YESTERDAY', 'LAST_7_DAYS', 'THIS_WEEK_SUN_TODAY',
		'THIS_WEEK_MON_TODAY', 'LAST_WEEK', 'LAST_14_DAYS', 'LAST_30_DAYS',
		'LAST_BUSINESS_WEEK', 'LAST_WEEK_SUN_SAT', 'THIS_MONTH', 'LAST_MONTH',
		'ALL_TIME', 'CUSTOM_DATE'
	);

	/***************************************************************
	* Registers the supported format of the downloaded report
	*
	* @access	protected
	* @var		array
	****************************************************************/
	protected $reportFormats = array( 'CSV', 'XML' );
	
	/***************************************************************
	* Registers the supported operators for the predicates of the 
	* web service to filter the report data
	*
	* @access	protected
	* @var		array
	****************************************************************/
	protected $operators = array(
		'EQUALS', 'NOT_EQUALS', 'IN', 'NOT_IN', 'GREATER_THAN',
		'GREATER_THAN_EQUALS', 'LESS_THAN', 'LESS_THAN_EQUALS',
		'STARTS_WITH', 'STARTS_WITH_IGNORE_CASE', 'CONTAINS',
		'CONTAINS_IGNORE_CASE', 'DOES_NOT_CONTAIN', 
		'DOES_NOT_CONTAIN_IGNORE_CASE'
	);
	
	/***************************************************************
	* Constructor method of the class. Switches between live and 
	* sandbox URLs, initialize the ReportDefinitionService and 
	* creates SoapClient object for the selected web service.
	*
	* @access	public
	****************************************************************/
	public function __construct( $path = NULL )
	{
		$this->path = !empty( $path ) ? $path : NULL;
		$this->loadConfig( );
				
		if ( $this->sandbox == true )
		{
			$wsdlUrl = $this->_server['sandbox'];
		}
		else
		{
			$wsdlUrl = $this->_server['live'];
		}

		$this->_serviceName = 'ReportDefinitionService';
		$wsdl = $wsdlUrl . $this->_serviceToWsdlMapping[ $this->_serviceName ][0] . '?wsdl';

		$options = array(
			'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
			'encoding' => 'utf-8',
			'trace'	   => TRUE
		);
		
		$this->loadConfig();
		
		$this->soapClient = new SoapClient( $wsdl, $options );
		
		$headers = new SoapHeader( $this->_serviceToWsdlMapping[ $this->_serviceName ][1], 
			'RequestHeader', array(
				'authToken'			=> $this->getAuthToken(),
				'userAgent'			=> $this->userAgent,
				'clientCustomerId'	=> $this->clientCustomerId,
				'developerToken'	=> $this->developerToken
			) 
		);

		$this->soapClient->__setSoapHeaders( $headers );

	}
	
	/***************************************************************
	* Executes the CURL post request against the ad hoc URL for 
	* report downloads and receives the result
	*
	* @access	protected
	****************************************************************/
	protected function execCurl() 
	{			
		if ( $this->sandbox == true )
		{
			$server = $this->_server['sandbox'];
		}
		else
		{
			$server = $this->_server['live'];
		}

		$url = $server . $this->adhocPostUrl;
		
		$headers = array(
			'Content-type: application/x-www-form-urlencoded',
			'Authorization: GoogleLogin auth=' . $this->getAuthToken(),
			'developerToken: ' . $this->developerToken,
			'clientCustomerId: ' . $this->clientCustomerId,
			'returnMoneyInMicros: true',
						
		);

		$data = '__rdxml=' . urlencode( $this->__rdxml );
		
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		
		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error = curl_error( $ch );
		curl_close( $ch );
		
		if( $httpCode == 200 || $httpCode == 403 )
		{
			return $response;
		}
		else
		{
			if ( !empty( $error ) )
			{
				throw new exception( $httpCode . ' - ' . $error );
			}
			else
			{
				throw new exception( $httpCode . ' - Unknow error occurred while post operation.' ); 
			}
		}
	}
	
	/***************************************************************
	* This is the main handler of all the report methods of the 
	* class. Depending on the method selection it prepares the 
	* report selector and creates the report.
	*
	* @param	string $method Reporting method to execute
	* @param	array $params Parameters passed to the report method
	*
	* @access	protected
	* @returns	string XML or CSV
	****************************************************************/
	public function __call( $method, $params = NULL )
	{
		$method = preg_replace( '/^get/', '', $method, 1, $count );

		$fields = array(
			'ReportImpressions' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Clicks", "Impressions"
			),
			'ReportClicks' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Clicks"
			),
			'ReportCpc' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "AverageCpc"
			),
			'ReportCtr' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Ctr"
			),
			'ReportTotalCost' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Amount", "Cost"
			),
			'ReportNumberConversions' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Conversions"
			),
			'ReportCostPerConversions' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "CostPerConversion"
			),
			'ReportAllInOne' => array(
				"Date", "DayOfWeek", "Month", "MonthOfYear", "Week", "Year",
				"Name", "CampaignName", "Status", "Clicks", "Impressions", "Ctr",
				"AverageCpc", "Cost", "AveragePosition", "Conversions", 
				"CostPerConversion", "ConversionRate"
			),
			'ReportCustomFields' => array()

		);
		
		if ( !array_key_exists( $method, $fields ) || $count <= 0 )
		{
			throw new exception( 'Unknown method call.' );
		}

		if ( $method == 'ReportCustomFields' ) 
		{
			if ( empty( $params[5] ) )
			{
				throw new exception( 'Fields must be specified for custom 
				reports operation' );
			}
			else if ( is_array( $params[5] ) )
			{
				foreach ( $params[5] as $field )
				{
					$fields['ReportCustomFields'][] = $field;
				}
			}
			else
			{
				throw new exception( 'Fields must be a list for custom 
				reports operation' );
			}
		}
		
		if ( isset( $params[4] ) 
			 && !in_array( $params[4], $this->reportFormats ) )
		{
			throw new exception( 'Unknown report format requested ' 
				. $params[4] );
		}
		
		if ( isset( $params[3] ) 
			 && !in_array( $params[3], $this->dateRangeTypes ) )
		{
			throw new exception( 'Unknown report date range type requested ' 
				. $params[3] );
		}
		
		$predicates = NULL;
		if ( !empty( $params[0] ) && is_array( $params[0] ) )
		{
			$predicates = array();
			foreach ( $params[0] as $value )
			{
				if ( !empty( $value['field'] ) 
					 && !empty( $value['operator'] )
					 && ( is_array( $value['values'] ) 
						  && !empty( $value['values'] ) 
					    )
				)
				{
					if ( !in_array( $value['operator'], $this->operators ) )
					{
						throw new exception('Unknow predicate operator requested ' . 
							$value['operator'] );
					}

					$predicates[] = array(
						'field' => $value['field'],
						'operator' => $value['operator'],
						'values' => $value['values']
					);
				}
			}
			if ( empty( $predicates ) )
			{
				$predicates = NULL;
			}
		
		}
		
		$dateRange = NULL;
		$rangeType = isset( $params[3] ) ? $params[3] : 'LAST_7_DAYS';
		if ( !empty( $params[1]['min'] )
			 && !empty( $params[1]['max'] )
			 && $rangeType == 'CUSTOM_DATE'
		)
		{
			$dateRange = array( 
				'min' => date( "Ymd", strtotime( $params[1]['min'] ) ), 
				'max' => date( "Ymd", strtotime( $params[1]['max'] ) ) 
			);
		}

		$ordering = NULL;
		if ( !empty( $params[2] ) && is_array( $params[2] ) )
		{
			$ordering = array();
			foreach ( $params[2] as $value )
			{
				if ( !empty( $value['field'] )
					 && !empty( $value['sortOrder'] )
				)
				{
					if ( $value['sortOrder'] != 'ASCENDING'
						 && $value['sortOrder'] != 'DESCENDING'
					)
					{
						throw new exception( 'Unknown sort order requested ' 
							. $value['sortOrder'] );
					}
					$ordering[] = array(
						'field' => $value['field'],
						'sortOrder' => $value['sortOrder']
					);
				}
			}
			if ( empty( $ordering ) )
			{
				$ordering = NULL;
			}
		}
		
		$selector = new ReportDefinitionService_Selector();
		$selector->prepareSelector( $fields[$method], $predicates, $dateRange, $ordering );
				
		$reportDefinition = new ReportDefinitionService_ReportDefinition();
		$reportDefinition->selector = $selector;
		$reportDefinition->reportName = $method;
		$reportDefinition->reportType = 'CAMPAIGN_PERFORMANCE_REPORT';
		$reportDefinition->dateRangeType = $rangeType;
		$reportDefinition->downloadFormat = !empty( $params[4] ) 
											? $params[4] : 'XML';
		$reportDefinition->includeZeroImpressions = 1;
		$reportDefinition->hasAttachment = 0;
		
		$this->__rdxml = $reportDefinition->__toXml( $selector );
		$report = $this->execCurl();
		return $report;
	}
	
	/***************************************************************
	* Converter method to convert the report from XML to Object
	*
	* @param	string $string XML string that needs to be converted
	*
	* @access	public
	* @return	object[]
	****************************************************************/
	public function xmlToObject( $string )
	{
		$rows = array();
		$attr = array();
		$columns = array();
		$xml = simplexml_load_string( $string );
		$total = count( $xml->table->row );

		foreach ( $xml->table->columns as $column )
		{
			foreach ( $column as $key => $value)
			{
				$columns[(string) $value['name']] = (string) $value['display'];
			}
		}
		$rows['headers'] = $columns;

		for( $i=0; $i < $total; $i++ )
		{
			foreach( $xml->table->row[$i]->attributes() as $name => $value )
			{
				$attr[(string) $name] = (string) $value;
			}
			$rows[] = (object) $attr;
		}
		return $rows;
	}
	
	/***************************************************************
	* Converter method to convert the report from CSV to Object
	*
	* @param	string $string CSV string that needs to be converted
	*
	* @access	public
	* @return	object[]
	****************************************************************/
	public function csvToObject( $string )
	{
		$csv = explode( "\n", $string );
		$rows = array();
		
		$headerKeys = str_getcsv( preg_replace( '/[.\s()-]/', '', $csv[1] ) );
		$headers = str_getcsv( $csv[1] );
		foreach ( $headerKeys as $key=>$value )
		{
			$rows['temp'][$value] = $headers[$key];
		}
		
		$rows['headers'] = (object) $rows['temp'];
		unset( $rows['temp'] );

		$totalRowsToProcess = count( $csv ) - 2;
		for ( $i = 2; $i < $totalRowsToProcess; $i++ )
		{
			$row = str_getcsv( $csv[$i] );
			foreach ( $headerKeys as $key => $value )
			{
				$temp[$value] = $row[$key];
			}
			$rows[] = (object) $temp;
		}

		return $rows;
	}
						
}