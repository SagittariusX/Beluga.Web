<?php
/**
 * This file defines {@see \Beluga\Web\Http\Method}.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @subpackage     Http
 * @version        0.1.0
 */


namespace Beluga\Web\Http;


use \Beluga\Web\WebError;


/**
 * Defines a HTTP-Request.
 *
 * <b>Usage</b>
 *
 * first simple example of a post request
 *
 * <code>
 * $postData = [
 *    'field_name1' => 'value 1',
 *    'field_name2' => 'value 2'
 * ];
 * try
 * {
 *    $response = Request::Create()      // Create a new instance
 *       ->timeout( 5 )                               // Set a timeout of 5 seconds
 *       ->header( 'Accept', 'text/html' )            // Adding an single header (alternative use headers())
 *       ->data( $postData )                          // Adding the POST form data
 *       ->sendPOST( 'http://example.com/form.php' )  // Sending the request that returns the response from server.
 *
 *    // You can use the following properties of the response object
 *    $response->code;        // The HTTP response status code (integer)
 *    $response->headers;     // All response headers as an associative array
 *    $response->body;        // This is the parsed response body excluding the headers
 *    $response->rawBody;     // The not parsed body including the headers
 * }
 * catch ( \Beluga\Web\WebError $ex )
 * {
 *    \Beluga\print_h( $ex->getErrorMessage( true ) );
 *    exit;
 * }
 * </code>
 *
 * <b>File Uploads</b>
 *
 * You the form also contains one or more file upload fields, you can simple define it by adding one or more fields
 * with a special value, to $postData
 *
 * <code>
 * $postData = [
 *    'fieldname1' => 'value 1',
 *    'fieldname2' => 'value 2',
 *    'fileupload' => \Beluga\Web\Http\File::add( '/path/to/file.txt', 'text/plain', 'fileupload' )
 * ];
 * </code>
 *
 * <b>Authentication</b>
 *
 * <code>
 * // BASIC AUTH
 * $response = Request::Create()                   // Create a new instance
 *    ->timeout( 5 )                               // Set a timeout of 5 seconds
 *    ->auth( 'user', 'secret' )                   // Set username and password for usage with HTTP BASIC AUTH
 *    ->sendGET( 'http://example.com/' );
 * </code>
 *
 * If you want to use an other auth type, auth() also accepts a 3rd param, where one of the following constants
 * can use as value:
 *
 * - CURLAUTH_BASIC: HTTP Basic authentication. This is the default choice
 * - CURLAUTH_DIGEST: HTTP Digest authentication. as defined in RFC 2617
 * - CURLAUTH_DIGEST_IE: HTTP Digest authentication with an IE flavor. The IE flavor is simply that libcurl will use
 *   a special "quirk" that IE is known to have used before version 7 and that some servers require the client to use.
 * - CURLAUTH_NEGOTIATE: HTTP Negotiate (SPNEGO) authentication. as defined in RFC 4559
 * - CURLAUTH_NTLM: HTTP NTLM authentication. A proprietary protocol invented and used by Microsoft.
 * - CURLAUTH_NTLM_WB: NTLM delegating to winbind helper. Authentication is performed by a separate binary
 *   application. see libcurl docs for more info
 * - CURLAUTH_ANY: This is a convenience macro that sets all bits and thus makes libcurl pick any it finds suitable.
 *   libcurl will automatically select the one it finds most secure.
 * - CURLAUTH_ANYSAFE: This is a convenience macro that sets all bits except Basic and thus makes libcurl pick any it
 *   finds suitable. libcurl will automatically select the one it finds most secure.
 * - CURLAUTH_ONLY: This is a meta symbol. OR this value together with a single specific auth value to force libcurl
 *   to probe for un-restricted auth and if not, only that single auth algorithm is acceptable.
 *
 * If you want call a SSL URL e.g. with an self signed certificate to have to disable the SSL certificate
 * validation by:
 *
 * <code>
 * $response = Request::Create()                   // Create a new instance
 *    ->verifySSLPeer( false )                     // Disable SSL cert validation
 *    ->sendGET( 'https://example.com/' );
 * </code>
 */
class Request
{


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   private $_cookie;
   private $_cookieFile;
   private $_curlOpts;
   private $_defaultHeaders;
   private $_handle;
   private $_jsonOptions;
   private $_socketTimeout;
   private $_verifySSLPeer;
   private $_verifySSLHost;
   private $_auth;
   private $_proxy;
   private $_data;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init's a new instance.
    */
   public function __construct()
   {

      $this->_curlOpts       = [];
      $this->_defaultHeaders = [];
      $this->_jsonOptions    = [];
      $this->_curlOpts       = [];
      $this->_data           = [];
      $this->_verifySSLPeer  = true;
      $this->_verifySSLHost  = true;
      $this->_auth           = [
         'user'  => '',
         'pass'  => '',
         'meth'  => \CURLAUTH_BASIC
      ];
      $this->_proxy          = [
         'port'    => false,
         'tunnel'  => false,
         'address' => false,
         'type'    => \CURLPROXY_HTTP,
         'auth'    => [
            'user'   => '',
            'pass'   => '',
            'meth'   => \CURLAUTH_BASIC
         ]
      ];

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Set the JSON decode mode.
    *
    * @param  boolean $assoc   When TRUE, returned objects will be converted into associative arrays.
    * @param  integer $depth   User specified recursion depth.
    * @param  integer $options Bit mask of JSON decode options. Currently only JSON_BIGINT_AS_STRING is
    *                          supported (default is to cast large integers as floats)
    * @return \Beluga\Web\Http\Request
    */
   public function jsonDecodeOptions( bool $assoc = false, int $depth = 512, $options = 0 )
      : Request
   {

      $this->_jsonOptions = array( $assoc, $depth, $options );

      return $this;

   }

   /**
    * Verify SSL peer?
    *
    * @param  boolean $enabled enable SSL verification, by default is true
    * @return \Beluga\Web\Http\Request
    */
   public function verifySSLPeer( bool $enabled )
      : Request
   {

      $this->_verifySSLPeer = (bool) $enabled;

      return $this;

   }

   /**
    * Verify SSL host?
    *
    * @param  boolean $enabled enable SSL host verification, by default is true
    * @return \Beluga\Web\Http\Request
    */
   public function verifySSLHost( bool $enabled )
      : Request
   {

      $this->_verifySSLHost = (bool) $enabled;

      return $this;

   }

   /**
    * Set a timeout in seconds.
    *
    * @param  integer $seconds timeout value in seconds
    * @return \Beluga\Web\Http\Request
    */
   public function timeout( int $seconds )
      : Request
   {

      $this->_socketTimeout = $seconds;

      return $this;

   }

   /**
    * Sets the data that should be send as request body.
    *
    * It means e.g. for <b>POST requests</b> you have to define an associative array with the form data. The keys
    * are the form field names, the values are the form field values or if the form field is an file upload field
    * the value must be created by {@see \Beluga\Web\Http\File::add()}. Alternatively you can also use an other format
    * like json encode objects, if the URL supports it.
    *
    * On GET or HEAD requests it is an array with the GET-Parameters, etc. pp.
    *
    * @param  array|string $data
    * @return \Beluga\Web\Http\Request
    */
   public function data( $data )
      : Request
   {

      $this->_data = $data;

      return $this;

   }

   /**
    * Set default headers to send on every request
    *
    * @param  array  $headers headers array
    * @return \Beluga\Web\Http\Request
    */
   public function headers( array $headers )
      : Request
   {

      $this->_defaultHeaders = array_merge( $this->_defaultHeaders, $headers );

      return $this;

   }

   /**
    * Set a new default header to send on every request
    *
    * @param string $name header name
    * @param string $value header value
    * @return \Beluga\Web\Http\Request
    */
   public function header( string $name, string $value )
      : Request
   {

      if ( \is_null( $value ) || ! \is_string( $value ) )
      {
         unset( $this->_defaultHeaders[ $name ] );
      }

      $this->_defaultHeaders[ $name ] = $value;

      return $this;

   }

   /**
    * Clear all the default headers
    *
    * @return \Beluga\Web\Http\Request
    */
   public function clearDefaultHeaders()
      : Request
   {

      $this->_defaultHeaders = array();

      return $this;

   }

   /**
    * Set curl options to send on every request
    *
    * @param  array  $options options array
    * @return \Beluga\Web\Http\Request
    */
   public function curlOpts( array $options )
      : Request
   {

      $this->_curlOpts = \array_merge( $this->_curlOpts, $options );

      return $this;

   }

   /**
    * Set a new default header to send on every request
    *
    * @param  string  $name  The header name.
    * @param  string  $value The header value.
    * @return \Beluga\Web\Http\Request
    */
   public function curlOpt( $name, $value )
      : Request
   {

      $this->_curlOpts[ $name ] = $value;

      return $this;

   }

   /**
    * Clear all the default headers.
    *
    * @return \Beluga\Web\Http\Request
    */
   public function clearCurlOpts()
      : Request
   {

      $this->_curlOpts = array();

      return $this;

   }

   /**
    * Set a cookie string for enabling cookie handling
    *
    * @param  string $cookie
    * @return \Beluga\Web\Http\Request
    */
   public function cookie( string $cookie )
      : Request
   {

      $this->_cookie = $cookie;

      return $this;

   }

   /**
    * Set a cookie file path for enabling cookie handling.
    *
    * $cookieFile must be a correct path with write permission
    *
    * @param  string  $cookieFile path to file for saving cookie
    * @return \Beluga\Web\Http\Request
    */
   public function cookieFile( $cookieFile )
      : Request
   {

      $this->_cookieFile = $cookieFile;

      return $this;

   }

   /**
    * Set authentication method to use.
    *
    * @param  string  $username The authentication username.
    * @param  string  $password The authentication password.
    * @param  integer $method   The authentication method.
    * @return \Beluga\Web\Http\Request
    */
   public function auth( string $username = '', string $password = '', $method = \CURLAUTH_BASIC )
      : Request
   {

      $this->_auth[ 'user' ]   = $username;
      $this->_auth[ 'pass' ]   = $password;
      $this->_auth[ 'meth' ] = $method;

      return $this;

   }

   /**
    * Set proxy to use.
    *
    * @param  string  $address The proxy address.
    * @param  integer $port    The proxy port.
    * @param  integer $type    The proxy type (Available options for this are CURLPROXY_HTTP,
    *                          CURLPROXY_HTTP_1_0 CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A and
    *                          CURLPROXY_SOCKS5_HOSTNAME)
    * @param  boolean $tunnel  Enable or Disable tunneling?
    * @return \Beluga\Web\Http\Request
    */
   public function proxy( string $address, int $port = 1080, $type = \CURLPROXY_HTTP, bool $tunnel = false )
      : Request
   {

      $this->_proxy[ 'type' ]    = $type;
      $this->_proxy[ 'port' ]    = $port;
      $this->_proxy[ 'tunnel' ]  = $tunnel;
      $this->_proxy[ 'address' ] = $address;

      return $this;

   }

   /**
    * Set proxy authentication method to use
    *
    * @param  string  $username The authentication username.
    * @param  string  $password The authentication password.
    * @param  integer $method   The authentication method.
    * @return \Beluga\Web\Http\Request
    */
   public function proxyAuth( string $username = '', string $password = '', $method = \CURLAUTH_BASIC )
      : Request
   {

      $this->_proxy[ 'auth' ][ 'user' ]   = $username;
      $this->_proxy[ 'auth' ][ 'pass' ]   = $password;
      $this->_proxy[ 'auth' ][ 'meth' ] = $method;

      return $this;

   }

   /**
    * Send a GET request to a URL
    *
    * @param  string  $url        The URL to send the GET request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendGET( string $url )
      : Response
   {

      return $this->send( RequestMethod::GET, $url );

   }

   /**
    * Send a HEAD request to a URL.
    *
    * @param  string  $url        The  URL to send the HEAD request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendHEAD( string $url )
      : Response
   {

      return $this->send( RequestMethod::HEAD, $url );

   }

   /**
    * Send a OPTIONS request to a URL.
    *
    * @param  string  $url        The URL to send the OPTIONS request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendOPTIONS( string $url )
      : Response
   {

      return $this->send( RequestMethod::OPTIONS, $url );

   }

   /**
    * Send a CONNECT request to a URL.
    *
    * @param  string  $url        The URL to send the CONNECT request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendCONNECT( string $url )
      : Response
   {

      return $this->send( RequestMethod::CONNECT, $url );

   }

   /**
    * Send POST request to a URL.
    *
    * @param  string  $url     The URL to send the POST request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendPOST( string $url )
      : Response
   {

      return $this->send( RequestMethod::POST, $url );

   }

   /**
    * Send DELETE request to a URL.
    *
    * @param  string  $url     The URL to send the DELETE request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendDELETE( string $url )
      : Response
   {

      return $this->send( RequestMethod::DELETE, $url );

   }

   /**
    * Send PUT request to a URL.
    *
    * @param  string  $url     The URL to send the PUT request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendPUT( string $url )
      : Response
   {

      return $this->send( RequestMethod::PUT, $url );

   }

   /**
    * Send PATCH request to a URL.
    *
    * @param  string  $url     The URL to send the PATCH request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendPATCH( string $url )
      : Response
   {

      return $this->send( RequestMethod::PATCH, $url );

   }

   /**
    * Send TRACE request to a URL.
    *
    * @param  string  $url     The URL to send the PATCH request to.
    * @return \Beluga\Web\Http\Response
    */
   public function sendTRACE( string $url )
      : Response
   {

      return $this->send( RequestMethod::TRACE, $url );

   }

   /**
    * Send a cURL request.
    *
    * @param  string  $method  The HTTP method to use. Use one of the constants defined by {@see \Beluga\Web\Http\RequestMethod}
    * @param  string  $url     The URL to send the request to.
    * @throws \Beluga\Web\WebError Is thrown if a cURL error occurs.
    * @return \Beluga\Web\Http\Response
    */
   public function send( string $method, string $url )
      : Response
   {

      // Init the curls session
      $this->_handle = \curl_init();

      // start with default options
      \curl_setopt_array( $this->_handle, $this->_curlOpts );

      if ( $method !== RequestMethod::GET )
      {
         \curl_setopt( $this->_handle, \CURLOPT_CUSTOMREQUEST, $method );
         if ( \is_array( $this->_data ) || ( $this->_data instanceof \Traversable ) )
         {
            \curl_setopt( $this->_handle, \CURLOPT_POSTFIELDS, static::BuildHTTPQuery( $this->_data ) );
         }
         else
         {
            \curl_setopt( $this->_handle, \CURLOPT_POSTFIELDS, $this->_data );
         }
      }
      else if ( \is_array( $this->_data ) )
      {
         if ( \strpos( $url, '?' ) !== false )
         {
            $url .= '&';
         }
         else
         {
            $url .= '?';
         }
         $url .= \urldecode( \http_build_query( static::BuildHTTPQuery( $this->_data ) ) );
      }

      \curl_setopt_array(
         $this->_handle,
         array(
            \CURLOPT_URL            => static::encodeUrl($url),
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_FOLLOWLOCATION => true,
            \CURLOPT_MAXREDIRS      => 10,
            \CURLOPT_HTTPHEADER     => $this->getFormattedHeaders(),
            \CURLOPT_HEADER         => true,
            \CURLOPT_SSL_VERIFYPEER => $this->_verifySSLPeer,
            \CURLOPT_SSL_VERIFYHOST => $this->_verifySSLHost === false ? 0 : 2,
            \CURLOPT_ENCODING       => ''
         )
      );

      if ( ! empty( $this->_socketTimeout ) )
      {
         \curl_setopt( $this->_handle, \CURLOPT_TIMEOUT, $this->_socketTimeout );
      }

      if ( ! empty( $this->_cookie ) )
      {
         \curl_setopt( $this->_handle, \CURLOPT_COOKIE, $this->_cookie );
      }

      if ( ! empty( $this->_cookieFile ) )
      {
         \curl_setopt( $this->_handle, \CURLOPT_COOKIEFILE, $this->_cookieFile );
         \curl_setopt( $this->_handle, \CURLOPT_COOKIEJAR,  $this->_cookieFile );
      }

      if ( ! empty( $this->_auth[ 'user' ] ) )
      {
         \curl_setopt_array(
            $this->_handle,
            array(
               \CURLOPT_HTTPAUTH    => $this->_auth[ 'meth' ],
               \CURLOPT_USERPWD     => $this->_auth[ 'user' ] . ':' . $this->_auth[ 'pass' ]
            )
         );
      }

      if ( $this->_proxy[ 'address' ] !== false )
      {
         \curl_setopt_array(
            $this->_handle,
            array(
               \CURLOPT_PROXYTYPE       => $this->_proxy['type'],
               \CURLOPT_PROXY           => $this->_proxy['address'],
               \CURLOPT_PROXYPORT       => $this->_proxy['port'],
               \CURLOPT_HTTPPROXYTUNNEL => $this->_proxy['tunnel'],
               \CURLOPT_PROXYAUTH       => $this->_proxy['auth']['meth'],
               \CURLOPT_PROXYUSERPWD    => $this->_proxy['auth']['user'] . ':' . $this->_proxy['auth']['pass']
            )
         );
      }

      $response   = \curl_exec(  $this->_handle );
      $error      = \curl_error( $this->_handle );
      $info       = $this->getInfo();

      if ( ! empty( $error ) )
      {
         throw new WebError( 'Web', $error );
      }

      // Split the full response in its headers and body
      $header_size = $info[ 'header_size' ];
      $header      = \substr( $response, 0, $header_size );
      $body        = \substr( $response, $header_size );
      $httpCode    = $info[ 'http_code' ];

      return new Response( $httpCode, $body, $header, $this->_jsonOptions );

   }

   /**
    * Returns the current cURL handle.
    *
    * @return resource
    */
   public function getCurlHandle()
   {

      return $this->_handle;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * This function is useful for serializing multidimensional arrays, and avoid getting
    * the 'Array to string conversion' notice
    *
    * @param  array|object $data
    * @param  mixed        $parent
    * @return array
    */
   public static function BuildHTTPQuery( $data, $parent = false )
      : array
   {

      $result = [];

      if ( \is_object( $data ) )
      {
         $data = \get_object_vars( $data );
      }

      foreach ( $data as $key => $value )
      {

         if ( $parent )
         {
            $new_key = \sprintf( '%s[%s]', $parent, $key );
         }
         else
         {
            $new_key = $key;
         }

         if ( ! ( $value instanceof \CURLFile )
             && ( \is_array( $value ) || \is_object( $value ) ) )
         {
            $result = \array_merge( $result, static::BuildHTTPQuery( $value, $new_key ) );
         }
         else
         {
            $result[ $new_key ] = $value;
         }
      }

      return $result;

   }

   /**
    * Static constructor for using the fluent coding style.
    *
    * @return \Beluga\Web\Http\Request
    */
   public static function Create()
      : Request
   {

      return new self();

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = =">

   private function getInfo( $opt = false )
   {

      if ( $opt )
      {
         $info = \curl_getinfo( $this->_handle, $opt );
      }
      else
      {
         $info = \curl_getinfo( $this->_handle );
      }

      return $info;

   }

   private function getFormattedHeaders()
   {

      $formattedHeaders = [];
      $headers          = \array_change_key_case( $this->_defaultHeaders );

      foreach ( $headers as $k => $v )
      {
         $formattedHeaders[] = static::getHeaderString( $k, $v );
      }

      if ( ! \array_key_exists( 'user-agent', $headers ) )
      {
         $ua = \ini_get( 'user_agent' );
         if ( empty( $ua ) )
         {
            $ua = 'user-agent: Mozilla/5.0 (X11; Linux x86_64; rv:17.0) Gecko/20121202 Firefox/17.0 Iceweasel/17.0.1';
         }
         $formattedHeaders[] = $ua;
      }

      if ( ! \array_key_exists( 'expect', $headers ) )
      {
         $formattedHeaders[] = 'expect:';
      }

      return $formattedHeaders;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = =">

   private static function getArrayFromQueryString( string $query )
      : array
   {

      $query = \preg_replace_callback(
         '/(?:^|(?<=&))[^=[]+/',
         function ( $match )
         {
            return \bin2hex( \urldecode( $match[ 0 ] ) );
         },
         $query
      );

      \parse_str( $query, $values );

      return \array_combine(
         \array_map( 'hex2bin', \array_keys( $values )),
         $values
      );

   }

   private static function encodeUrl( string $url )
      : string
   {

      $url_parsed = \parse_url( $url );

      $scheme = $url_parsed[ 'scheme' ] . '://';
      $host   = $url_parsed[ 'host' ];
      $port   = isset( $url_parsed[ 'port' ] )  ? $url_parsed[ 'port' ]  : null;
      $path   = isset( $url_parsed[ 'path' ] )  ? $url_parsed[ 'path' ]  : null;
      $query  = isset( $url_parsed[ 'query' ] ) ? $url_parsed[ 'query' ] : null;

      if ( $query !== null )
      {
         $query = '?' . \http_build_query( static::getArrayFromQueryString( $query ) );
      }

      if ( $port )
      {
         $port = ':' . \ltrim( $port, ':' );
      }

      $result = $scheme . $host . $port . $path . $query;

      return $result;

   }

   private static function getHeaderString( string $key, $val ) : string
   {

      $key = \trim( \strtolower( $key ) );

      return $key . ': ' . $val;

   }

   // </editor-fold>


}

