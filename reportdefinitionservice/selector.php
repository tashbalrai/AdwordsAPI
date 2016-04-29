<?php
/*********************************************************************
* Selector for ReportDefinitionService Web Service
*
* PHP Version 5.3
*
* @package		Adwords API Reporting
* @category		Adwords Reporting Web Services Selector
* @author		  Vipan Balrai
**********************************************************************/
class ReportDefinitionService_Selector {
	/*******************************************************************
	* Holds the fields that needs to be fetched from the service
	*
	* @access public
	* @var string[]
	*******************************************************************/
	public $fields;

	/*******************************************************************
	* Holds the conditions to filter the output of the service
	*
	* @access public
	* @var string[]
	*******************************************************************/
	public $predicates;

	/*******************************************************************
	* Holds the min and max date range for the time frame filter
	*
	* @access public
	* @var string[]
	*******************************************************************/
	public $dateRange;

	/*******************************************************************
	* Holds the sorting criteria for the service output
	*
	* @access public
	* @var string[]
	*******************************************************************/
	public $ordering;

	/*******************************************************************
	* Holds the start and the no# results for the pagination
	*
	* @access public
	* @var string[]
	*******************************************************************/
	public $paging;

	/*******************************************************************
	* Default initializer constructor of the selector
	*
	* @param array $fields		default to null
	* @param array $predicates	default to null
	* @param array $dateRange	default to null
	* @param array $ordering	default to null
	* @param array $paging		default to null
	*
	* @access public
	*******************************************************************/
	public function __construct( $fields = NULL, $predicates = NULL, 
		$dateRange = NULL, $ordering = NULL, $paging = NULL ) 
	{
		$this->fields		= $fields;
		$this->predicates	= $predicates;
		$this->dateRange	= $dateRange;
		$this->ordering		= $ordering;
		$this->paging		= $paging;
	}
	
	/*******************************************************************
	* Prepares the selector for web service call
	*
	* @param array $fields		required
	* @param array $predicates	default to null
	* @param array $dateRange	default to null
	* @param array $ordering	default to null
	* @param array $paging		default to null
	*
	* @access public
	*******************************************************************/
	public function prepareSelector( $fields = NULL, $predicates = NULL, 
		$dateRange = NULL, $ordering = NULL, $paging = NULL )
	{
		if ( is_null( $fields ) )
		{
			throw new exception("Selector fields are required.");
		}
		
		$predicateList = $dateRangeObject = $orderList = $pageObject = NULL;

		if ( is_array( $predicates ) && count( $predicates ) > 0 )
		{
			$predicateList = array();
			foreach ( $predicates as $predicate )
			{
				if ( !empty( $predicate['field'] )  
					 && !empty( $predicate['operator'] ) 
					 && !empty( $predicate['values'] ) 
				)
				{
					
					$predicateObject			= new stdClass();
					$predicateObject->field		= $predicate['field'];
					$predicateObject->operator	= $predicate['operator'];
					$predicateObject->values	= $predicate['values'];
					$predicateList[]			= $predicateObject;
				}
			}

			if ( count( $predicateList ) <= 0 )
			{
				$predicateList = NULL;
			}
		}
		
		if ( is_array( $dateRange ) && count( $dateRange ) > 0 )
		{
			$dateRangeObject = NULL;
			if ( !empty( $dateRange['min'] )  
				 && !empty( $dateRange['max'] ) 
			)
			{
				$dateRangeObject		= new stdClass();
				$dateRangeObject->min	= $dateRange['min'];
				$dateRangeObject->max	= $dateRange['max'];
			}

		}

		if ( is_array( $ordering ) && count( $ordering ) > 0 )
		{
			$orderList = array();
			foreach ( $ordering as $ord )
			{
				if ( !empty( $ord['field'] )  
					 && !empty( $ord['sortOrder'] ) 
				)
				{
					$orderObject			= new stdClass();
					$orderObject->field		= $ord['field'];
					$orderObject->sortOrder	= $ord['sortOrder'];
					$orderList[]			= $orderObject;
				}
			}

			if ( count( $orderList ) <= 0 )
			{
				$orderList = NULL;
			}
		}

		if ( is_array( $paging ) && count( $paging ) > 0 )
		{
			$pageObject = NULL;
			if ( $paging['startIndex'] >= 0  
				 && $paging['numberResults'] >= 0 
			)
			{
				$pageObject					= new stdClass();
				$pageObject->startIndex		= $paging['startIndex'];
				$pageObject->numberResults	= $paging['numberResults'];
			}
		}

		
		$this->fields		= $fields;
		$this->predicates	= $predicateList;
		$this->dateRange	= $dateRangeObject;
		$this->ordering		= $orderList;
		$this->paging		= $pageObject;
	}

	/*******************************************************************
	* Converts the selector object to XML for ad hoc web service.
	*
	* @param string $dateRangeType	Default to null
	*
	* @access public
	* @return XML
	*******************************************************************/
	public function __toXml( $dateRangeType = NULL )
	{
		$xml = "";
		if ( is_null( $this->fields )  || !is_array( $this->fields ) )
		{
			throw new exception("Selector fields are required.");
		}

		if ( !empty( $this->fields ) )
		{
			$fieldsXML = "";
			foreach ( $this->fields as $field )
			{
				$fieldsXML .= '<fields>' . $field . '</fields>';
			}

			$xml .= $fieldsXML;
		}

		if ( !empty( $this->predicates ) )
		{
			$predicatesXML = "";
			foreach ( $this->predicates as $predicate )
			{
				if ( !empty( $predicate->field )  
					 && !empty( $predicate->operator ) 
					 && !empty( $predicate->values ) 
				)
				{
					$predicatesXML .= '<predicates>';
					$predicatesXML .= '<field>' . $predicate->field 
						. '</field>';
					$predicatesXML .= '<operator>' . $predicate->operator 
						. '</operator>';

					if ( is_array( $predicate->values ) )
					{
						foreach ( $predicate->values as $value )
						{
							$predicatesXML .= '<values>' . $value . '</values>';
						}
					}
					else
					{
						$predicatesXML .= '<values>' . $predicate->values . '</values>';
					}

					$predicatesXML .= '</predicates>';
				}
			}
			$xml .= $predicatesXML;
		}
		
		if ( !empty( $this->dateRange ) && $dateRangeType == 'CUSTOM_DATE' )
		{
			$dateRangeXML = "";
			if ( !empty( $this->dateRange->min )  
				 && !empty( $this->dateRange->max ) 
			)
			{
				$dateRangeXML .= '<dateRange>';
				$dateRangeXML .= '<min>' . $this->dateRange->min . '</min>';
				$dateRangeXML .= '<max>' . $this->dateRange->max . '</max>';
				$dateRangeXML .= '</dateRange>';
			}
			$xml .= $dateRangeXML;
		}
		
		if ( is_array( $this->ordering ) && !empty( $this->ordering ) )
		{
			$orderXML = "";
			foreach ( $this->ordering as $order )
			{
				if ( !empty( $order->field )  
					 && !empty( $order->sortOrder ) 
				)
				{
					$orderXML .= '<ordering>';
					$orderXML .= '<field>' . $order->field . '</field>';
					$orderXML .= '<sortOrder>' . $order->sortOrder . '</sortOrder>';
					$orderXML .= '</ordering>';

				}
			}
			$xml .= $orderXML;
		}

		if ( !empty( $this->paging ) )
		{
			$pageXML = "";
			if ( $this->paging->startIndex >= 0  
				 && $this->paging->numberResults >= 0 
			)
			{
				$pageXML .= '<paging>';
				$pageXML .= '<startIndex>' . $this->paging->startIndex . '</startIndex>';
				$pageXML .= '<numberResults>' . $this->paging->numberResults . '</numberResults>';
				$pageXML .= '</paging>';
			}
			$xml .= $pageXML;
		}
	return '<selector>' . $xml . '</selector>';	
	}
}