<?php
class CampaignService extends AdwordsApi
{
	protected $soapClient = NULL;

	public function __construct( $path = NULL )
	{
		$this->loadConfig( );
		
		if ( $this->sandbox == true )
		{
			$wsdlUrl = $this->_server['sandbox'];
		}
		else
		{
			$wsdlUrl = $this->_server['live'];
		}

		$this->_serviceName = 'CampaignService';
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
}