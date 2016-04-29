<?php
/*********************************************************************
* ReportDefinition for ReportDefinitionService Web Service
*
* PHP Version 5.3
*
* @package		Adwords API Reporting
* @category		Adwords Reporting Web Services ReportDefinition
* @author		  Vipan Balrai
**********************************************************************/
class ReportDefinitionService_ReportDefinition
{
	/**
	* @access public
	* @var integer
	*/
	public $id;

	/**
	* @access public
	* @var Selector
	*/
	public $selector;

	/**
	* @access public
	* @var string
	*/
	public $reportName;

	/**
	* @access public
	* @var tnsReportDefinitionReportType
	*/
	public $reportType;

	/**
	* @access public
	* @var boolean
	*/
	public $hasAttachment;

	/**
	* @access public
	* @var tnsReportDefinitionDateRangeType
	*/
	public $dateRangeType;

	/**
	* @access public
	* @var tnsDownloadFormat
	*/
	public $downloadFormat;

	/**
	* @access public
	* @var string
	*/
	public $creationTime;

	/**
	* @access public
	* @var boolean
	*/
	public $includeZeroImpressions;

	/*******************************************************************
	* Default initializer constructor of the selector
	*
	* @param integer	$id				default to null
	* @param object		$selector		default to null
	* @param string		$reportName		default to null
	* @param string		$reportType		default to null
	* @param boolean	$hasAttachment	default to null
	* @param string		$dateRangeType	default to null
	* @param string		$downloadFormat	default to null
	* @param datetime	$creationTime	default to null
	* @param boolean	$includeZeroImpressions	default to null
	*
	* @access public
	********************************************************************/
	public function __construct( $id = NULL, $selector = NULL, 
		$reportName = NULL, $reportType = NULL, $hasAttachment = NULL, 
		$dateRangeType = NULL, $downloadFormat = NULL, $creationTime = NULL, 
		$includeZeroImpressions = NULL ) 
	{
		$this->id						= $id;
		$this->selector					= $selector;
		$this->reportName				= $reportName;
		$this->reportType				= $reportType;
		$this->hasAttachment			= $hasAttachment;
		$this->dateRangeType			= $dateRangeType;
		$this->downloadFormat			= $downloadFormat;
		$this->creationTime				= $creationTime;
		$this->includeZeroImpressions	= $includeZeroImpressions;
	}
	
	/*******************************************************************
	* Converts the ReportDefinition object to XML for ad hoc web service.
	*
	* @param object $selector	required
	*
	* @access public
	* @return XML
	*******************************************************************/
	public function __toXml( $selector )
	{
		$xml = '';
		$xml .= '<reportDefinition>';
		$xml .= $selector->__toXml( $this->dateRangeType );
		$xml .= '<reportName>' . $this->reportName . '</reportName>';
		$xml .= '<reportType>' . $this->reportType . '</reportType>';
		$xml .= '<hasAttachment>' . $this->hasAttachment . '</hasAttachment>';
		$xml .= '<dateRangeType>' . $this->dateRangeType . '</dateRangeType>';
		$xml .= '<downloadFormat>' . $this->downloadFormat . '</downloadFormat>';
		$xml .= '<includeZeroImpressions>' . $this->includeZeroImpressions . '</includeZeroImpressions>';
		$xml .= '</reportDefinition>';
		
		return $xml;
	}
}