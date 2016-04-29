<?php
/*************************************************************************
*	Adwords API reporting main class. This class is use to load the config
*	values from file load them in to the class properties and This class
*	also implement the Google ClientLogin API to get the authorization
*	tokens for the authorization headers required for Adwords Services.
*
*	PHP Version 5.3
*	
*	@package	Adwords API
*	@category	Adwords Web Services Using Soap
*	@author		Vipan Balrai
**************************************************************************/
class AdwordsApi
{
	
	/*****************************************************************************
	* Records the web services supported by the Adwords Web service client package
	* maintain the end point of the services and their namespaces.
	* @var array
	* @access protected
	******************************************************************************/
	protected $_serviceToWsdlMapping = array(
		'CampaignService' => array(
			'api/adwords/cm/v201109/CampaignService',
			'https://adwords.google.com/api/adwords/cm/v201109'
		),
		'ReportDefinitionService' => array(
			'api/adwords/cm/v201109/ReportDefinitionService',
			'https://adwords.google.com/api/adwords/cm/v201109'
		)
	);
	
	/*****************************************************************************
	* Stores the web service name in use
	* @var string
	* @access protected
	******************************************************************************/
	protected $_serviceName = NULL;

	/*****************************************************************************
	* Tracks the servers urls for live and sandbox
	* @var array
	* @access protected
	******************************************************************************/
	protected $_server = array(
		'sandbox' => 'https://adwords-sandbox.google.com/',
		'live' => 'https://adwords.google.com/'
	);

	/*****************************************************************************
	* Version number of the service in use
	* @var string
	* @access protected
	******************************************************************************/
	protected $version = '201109';

	/*****************************************************************************
	* Stores the authorization token used by the web services to authorize a client
	* @var string
	* @access protected
	******************************************************************************/
	protected $authToken = NULL;

	/*****************************************************************************
	* A Unique literal to identify the user agent making requests to the web services
	* @var string
	* @access protected
	******************************************************************************/
	protected $userAgent = 'PHP5 SOAP - AdWordsAPI - Reports Module';

	/*****************************************************************************
	* Developer token needed by the web services to authorize a request its format 
	* is <client_id>+<email_address>++<currency_code>
	* @var string
	* @access protected
	******************************************************************************/
	protected $developerToken = NULL;

	/*****************************************************************************
	* Client customer id of the client who is making request
	* @var string
	* @access protected
	******************************************************************************/
	protected $clientCustomerId = NULL;
	
	/*****************************************************************************
	* Email address of the google account used to make requests
	* @var string
	* @access protected
	******************************************************************************/
	protected $email = NULL;
	
	/*****************************************************************************
	* Password for the email address of google account.
	* @var string
	* @access protected
	******************************************************************************/
	protected $password = NULL;

	/*****************************************************************************
	* Flag to tell if the mode is set to sandbox or is live
	* @var boolean
	* @access protected
	******************************************************************************/
	protected $sandbox = FALSE;

	/*****************************************************************************
	* This is the path to the configuration file; if specified it has to be an 
	* absolute path
	* @var string
	* @access protected
	******************************************************************************/
	protected $path = NULL;
	
	
	/*****************************************************************************
	* The constructor of the class used to initialize the object. Accepts one 
	* parameter for configuration file path. Registers a class auto loader.
	*
	* @param string $path Absolute path to the config.ini file default is null. 
	*					  If left default then config.ini file will be loaded 
	*					  from the current working path
	*
	* @access public
	******************************************************************************/
	public function __construct( $path = NULL )
	{
		spl_autoload_register( array( $this, 'loader' ) );
		$this->path = $path;
	}

	/*****************************************************************************
	* Class auto loader function. This function throws exceptions.
	*
	* @param string $class Class name to load 
	*
	* @access private
	******************************************************************************/
	private function loader( $class )
	{
		$libPath	= dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
		$classPath	= $libPath .	strtolower($class) . '.php';
		
		$path = explode( '_', $class );
		if ( count( $path ) > 1 )
		{
			$classFolder = $libPath . strtolower($path[0]) 
				. DIRECTORY_SEPARATOR;
			$classFolder .=  strtolower($path[1]) . '.php';	
		}
		else
		{
			$classFolder = $libPath . strtolower($class) . DIRECTORY_SEPARATOR;
			$classFolder .=  strtolower($class) . '.php';
		}
				
		if ( file_exists( $classPath ) )
		{
			require_once( $classPath );
		}
		else if ( file_exists( $classFolder ) )
		{
			require_once( $classFolder );
		}
		else
		{
			throw new exception( 'Unable to load class ' . $class );
		}
	}
	
	/*****************************************************************************
	* Magic funtion to provide an easy access to all the services configured with 
	* this Adwords API. If you add a new web sevice to it then we can get the 
	* object of that web service by using the following format
	*
	*	$object = new AdwordsApi();
	*	$service = $object->get<ServiceName>();
	*
	*	<ServiceName> Should be the same as registered in the services to 
	*	wsdl mapping property of the class
	*
	* @param string $service Name of the service requested
	* @param array $params Parameters passed to the service. Default is null.
	* @access public
	* @return Object Object of the service requested
	******************************************************************************/
	public function __call( $service, $params = NULL )
	{
		$service = preg_replace( '/^get/', '', $service, 1, $count );

		if ( !array_key_exists( $service, $this->_serviceToWsdlMapping ) )
		{
			throw new exception( "Service or method not found." );
		}

		if ( $count <= 0 )
		{
			throw new exception( "Service or method not found." );
		}
		
		$serviceObject = new $service( $this->path );
		return $serviceObject;
	}

	/*****************************************************************************
	* Load the config.ini file and parse it. Load all the configuration values in
	* to the class properties
	*
	* @access protected
	******************************************************************************/
	protected function loadConfig() {
		
		if ( !is_null( $this->path ) ) 
		{
			$file = DIRECTORY_SEPARATOR . trim( $this->path, 
				DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'config.ini';
		}
		else
		{
			$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.ini';
		}
		
		$data = parse_ini_file( $file );
		
		if ( empty( $data['email'] ) )
		{
			throw new exception( 'Config data email is missing' );
		}

		if ( empty( $data['password'] ) )
		{
			throw new exception( 'Config data password is missing' );
		}

		if ( empty( $data['developerToken'] ) )
		{
			throw new exception( 'Config data developer token is missing' );
		}

		if ( empty( $data['clientCustomerId'] ) )
		{
			throw new exception( 'Config data client customer id is missing' );
		}

		$this->email			= $data['email'];
		$this->password			= $data['password'];
		$this->developerToken	= $data['developerToken'];
		$this->clientCustomerId = $data['clientCustomerId'];
		$this->sandbox			= (bool) $data['sandbox'];
	}
	
	/*****************************************************************************
	* Provides the AuthToken string for Web Services authorization headers
	*
	* @access public
	* @return string AuthToken string.
	******************************************************************************/
	public function getAuthToken() 
	{
		if ( !empty( $this->authToken ) )
			return $this->authToken;

		$response	= $this->login();
		$fields		= $this->parseResponse( $response );

		if ( array_key_exists( 'Error', $fields ) ) 
		{
			$error = $fields['Error'];
			if ( array_key_exists( 'Info', $fields ) ) 
			{
				$error .= ': ' . $fields['Info'];
			}
			
			$url = array_key_exists( 'Url', $fields ) ? $fields['Url'] : NULL;
			
			throw new exception( $error, $url );
		} 
		else if ( !array_key_exists( 'Auth', $fields ) ) 
		{
		  throw new exception( 'Unknown error occurred.' );
		} 
		else 
		{
		  $this->authToken = $fields['Auth'];
		  return $this->authToken;
		}
	}
	
	/*****************************************************************************
	* Make a call to ClientLogin API of the google account service and get the 
	* authorization tokens. It throws exception
	*
	* @access private
	* @return array Response of the ClientLogin Call
	******************************************************************************/
	private function login() 
	{
		$postUrl	= 'https://www.google.com/accounts/ClientLogin';
		$postVars	= http_build_query( array(
			'accountType'	=> 'HOSTED_OR_GOOGLE',
			'Email'			=> $this->email,
			'Passwd'		=> $this->password,
			'service'		=> 'adwords',
			'source'		=> 'PHP5'
			),
			NULL, '&');

		$ch = curl_init( $postUrl );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postVars );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE );
		curl_setopt( $ch, CURLOPT_HEADER, FALSE );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_ENCODING, 'gzip' );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'curl, gzip' );

		$response	= curl_exec( $ch );
		$httpCode	= curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$error		= curl_error( $ch );
		curl_close( $ch );

		if ( !empty($error) ) 
		{
		  throw new Exception($error);
		} 
		else if ($httpCode != 200 && $httpCode != 403) 
		{
		  throw new Exception($httpCode);
		}
		return $response;
	}

	/*****************************************************************************
	* Parses the response of the login method.
	*
	* @param array $response ClientLogin API reponse 
	*
	* @access private
	* @return array Parsed result of the ClientLogin
	******************************************************************************/
	private function parseResponse( $response ) 
	{
		$result	= array();
		$lines	= explode( "\n", $response );
		foreach ( $lines as $line ) 
		{
			$parts			= explode('=', $line, 2);
			$key			= isset($parts[0]) ? $parts[0] : NULL;
			$value			= isset($parts[1]) ? $parts[1] : NULL;
			$result[$key]	= $value;
		}
		return $result;
	}
}