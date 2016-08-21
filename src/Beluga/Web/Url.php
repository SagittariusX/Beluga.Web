<?php
/**
 * This file defines the {@see \Beluga\Web\Url} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


use \Beluga\ArgumentError;
use \Beluga\TypeTool;


/**
 * Splits a URL string to all usable elements.
 *
 * <code>$Scheme://$AuthUser:$AuthPass@$Domain/$Path?$Query#$Anchor</code>
 *
 *
 * @property string  $Scheme   Gets or sets the current uri scheme, like 'http'. (see SCHEME_* class constants)
 * @property integer $Port     Gets or sets the current uri port number, if defined.
 * @property string  $AuthUser Gets the current uri auth user info (if defined its a security issue)
 * @property string  $AuthPass Gets the current uri auth password info (if defined its a security issue)
 * @property \Beluga\Web\Domain $Domain The whole domain part of the url
 * @property string  $Path     The path part of the URL, always beginning with the '/' sign.
 * @property array   $Query    The query string elements part of the URI, as a associative array.
 * @property string  $Anchor   The Anchor part of the URI, beginning with the '#' character, if defined.
 * @property-read string $QueryString The Query attributes as a string, beginning with the '?' character
 * @since      v1.0.0
 */
class Url
{


   // <editor-fold desc="// = = = =   C L A S S   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The 'http' scheme.
    */
   const SCHEME_HTTP = 'http';

   /**
    * The 'https' scheme.
    */
   const SCHEME_SSL  = 'https';

   /**
    * The 'ftp' scheme.
    */
   const SCHEME_FTP  = 'ftp';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Stores all dynamic accessible class properties.
    *
    * Used keys are:
    *
    * - 'scheme'
    * - 'domain'
    * - 'port'
    * - 'authuser'
    * - 'authpass'
    * - 'query'
    * - 'anchor'
    *
    * @var array
    */
   protected $properties;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * All possible open redirection URLs, contained inside the main URL.
    *
    * @var \Beluga\Web\Url[]
    */
   private $openRedirectionURLs = [];

   /**
    * If open redirection bug usage was found here the result points are stored.
    *
    * @var integer
    */
   private $lastOpenRedirectResultPoints = 0;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Finds all URLs inside a string to check. It returns the following match groups: 1=protocol, 2=host, 3=path+
    */
   private static $URL_FINDER = '~(https?|ftp)://([a-z0-9_.-]+)(/[a-z0-9_./+%?&#]+)?~i';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = = =">

   /**
    * This scheme is used if none is defined. (default='http')
    *
    * @var string
    */
   public static $fallbackScheme = 'http';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R O T E C T E D   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param array $properties
    */
   protected function __construct( array $properties )
   {

      $this->properties = $properties;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns if URL contains some login data usable with AUTHTYPE BASIC. This is a security issue!
    *
    * @return boolean
    */
   public function hasLoginData() : bool
   {

       return ! empty( $this->properties[ 'authuser' ] ) || ! empty( $this->properties[ 'authpass' ] );

   }

   /**
    * returns, if the current URL points to a IP address without using some host name, etc.
    *
    * @return boolean
    */
   public function useIpAddress() : bool
   {

      return $this->properties[ 'domain' ]->IsIPAddress;

   }

   /**
    * Returns if a port is used that points not to default port of current scheme/protocol.
    * If not explicit port is defined it always returns TRUE.
    *
    * @return boolean
    */
   public function useAssociatedPort() : bool
   {

      if ( $this->properties[ 'port' ] < 1 )
      {
         return true;
      }

      switch ( $this->properties[ 'scheme' ] )
      {

         case static::SCHEME_HTTP:
            return ( $this->properties[ 'port' ] === 80);

         case static::SCHEME_SSL:
            return ( $this->properties[ 'port' ] === 443 );

         case static::SCHEME_FTP:
            return ( $this->properties[ 'port' ] === 21 );

         default :
            return false;

      }

   }

   /**
    * Returns, if current URL uses a known web scheme. Known web schemes (protocols) are 'http', 'https' and 'ftp'.
    *
    * @return boolean
    */
   public function hasKnownWebscheme() : bool
   {

      return (bool) \preg_match( '~^(https?|ftp)$~', $this->properties[ 'scheme' ] );

   }

   /**
    * Returns if the current URL points to a URL shortener service.
    *
    * @return boolean
    */
   public function isUrlShortenerAddress() : bool
   {
      return $this->properties[ 'domain' ]->IsUrlShortener;
   }

   /**
    * Gets the real url behind a shortener URL, if current URL points to a URL shortener service.
    *
    * @return \Beluga\Web\Url|bool Returns the real URL, or FALSE.
    */
   public function extractUrlShortenerTarget()
   {

      if ( ! $this->isUrlShortenerAddress() )
      {
         return false;
      }

      try
      {
         $data = \get_headers( (string) $this, 1 );
         if ( ! isset( $data[ 'Location' ] ) )
         {
            return false;
         }
         if ( false === ( $lurl = Url::Parse( $data[ 'Location' ] ) ) )
         {
            return false;
         }
         return $lurl;
      }
      catch ( \Throwable $ex ) { $ex = null; return false; }
   }

   /**
    * Returns, if the current url contains some GET parameter(s) that are able to be used for the
    * "Open Redirection Bug" (OR-Bug).
    *
    * The OR-Bug can be used to redirect from URL currently not known as bad url, to some bader spaming url
    * (or something else)
    *
    * For doing it, its required to have a bad programmed web application that accepts unchecked GET parameter
    * used to define a redirection target URL. Like
    *
    * <code>http://example.com/?redirect=http%3A%2F%2Fexample.net%2Fbadurl</code>
    *
    * If a possible open redirection URL was found, it is stored as a separate \Beluga\Web\Url instance and can
    * be accessed by {@see \Beluga\Web\Url::getOpenRedirectionURLs()}.
    *
    * If it really works as a usable open redirection bug, can only be checked, if a real request is send, to
    * check if the redirection works. If you want to real check it out you can use the
    * {@see \Beluga\Web\Url::checkForOpenRedirect()} method. But its important to read its documentation before!
    *
    * @param  int &$resultPoints returns the max. probability of a URL injection (0-10 and > 4 means its possible)
    * @return boolean
    */
   public function isPossibleOpenRedirect( &$resultPoints ) : bool
   {

      // We are working with result points (> 4 returns TRUE)
      // to getting information about the badness (possibility of open redirect) of a url
      $resultPoints = 0;

      if ( \count( $this->openRedirectionURLs ) > 0 )
      {
         // If there are already some check results use it
         $resultPoints = $this->lastOpenRedirectResultPoints;
         // and return the existing result
         return $resultPoints > 4;
      }

      if ( ! \is_array( $this->properties[ 'query' ] ) || \count( $this->properties[ 'query' ] ) < 1 )
      {
         // If no query parameters are defined every thing is OK and we dont have to do more checks
         return false;
      }

      // Init array to hold some founded param names key) and associated resultpoints
      $founds  = array();
      $highest = 0;

      // OK lets check all GET/query parameters
      foreach ( $this->properties[ 'query' ] as $key => $value )
      {

         if ( ! \is_string( $value ) )
         {
            // The query parameter value is not a string. Go to next one.
            continue;
         }

         if ( ! \preg_match( '~^(https?|ftps?)://~i', $value ) )
         {
            // The query parameter value is not a url, go to next one.
            continue;
         }

         // Getting the URL instance to do some more detailed checks.
         $url = new Url( $value );

         if ( ! ( $url->properties[ 'domain' ] instanceof Domain ) )
         {
            // There is no usable domain defined, go to next one.
            continue;
         }

         // Do some Domain specific checks

         if ( ( ( (string) $url->properties[ 'domain' ] ) === ( (string) $this->properties[ 'domain' ] ) ) )
         {
            // If it points to the same domain its not problem, go to next one.
            continue;
         }

         if ( ( ( (string)$url->properties[ 'domain' ]->SLD ) === ( (string)$this->properties[ 'domain' ]->SLD ) ) )
         {
            // If
            $founds[ $key ] = 4;
         }
         else
         {
            $founds[ $key ] = 5;
         }
         if ( \preg_match( '~^(url|redir|addr|loc)~i', $key ) )
         {
            $founds[ $key ] += 2;
         }
         if ( ! $url->useAssociatedPort() )
         {
            // Make it bad if no associated Port is used
            ++$founds[ $key ];
         }
         if ( $url->properties[ 'domain' ]->IsIPAddress )
         {
            // Make it bad if a IP address is used.
            ++$founds[ $key ];
         }
         if ( $url->hasLoginData() )
         {
            // Make it bader if a login data are defined by url
            ++$founds[ $key ];
         }
         if ( $url->isUrlShortenerAddress() )
         {
            // Make it more bad if url points to a URL shortener service
            $founds[ $key ] += 2;
         }
         if ( $founds[ $key ] > 10 )
         {
            // Normalize to a maximum value of 10.
            $founds[ $key ] = 10;
         }
         if ( $founds[ $key ] > $highest )
         {
            // Remember the highest value
            $highest = $founds[ $key ];
         }
         if ( $founds[ $key ] > 4 )
         {
            $this->openRedirectionURLs[ $key ] = $url;
         }
      }

      if ( $highest > 4 )
      {
         $resultPoints                        = $highest;
         $this->lastOpenRedirectResultPoints = $highest;
         return true;
      }

      $this->lastOpenRedirectResultPoints = 0;

      return false;

   }

   /**
    * Returns all possible open redirection URLs, defined if {@see \Beluga\Web\Url::isPossibleOpenRedirect()}
    * returns TRUE.
    *
    * @return \Beluga\Web\Url[]
    */
   public function getOpenRedirectionURLs()
   {

      return $this->openRedirectionURLs;

   }

   /**
    * Checks, if possible open redirection bug URLs are defined, if one of it its a real open redirection usage.
    *
    * Attention. It sends a real request to each URL. Do'nt use it inside you're main web application because it
    * blocks it as long if it gets a answer. Maybe better use it in cron jobs or inside a very low frequenced area!
    *
    * How it works is easy. All you need is a url and its well known output.
    *
    * If we replace the possible redirection URL inside the current url with the URL where we know the output and
    * it redirects to the url with the known output, the bug is used.
    *
    * @param  string  $urlForTestContents The URL with the known output
    * @param  string  $testContents       The known output of $urlForTestContents (or a regex if $useAsRegex is TRUE)
    * @param  boolean $useAsRegex         Should $testContents be used as a regular expression?
    * @return boolean
    */
   public function checkForOpenRedirect( $urlForTestContents, $testContents, $useAsRegex = false )
   {

      if ( \count( $this->openRedirectionURLs ) < 1 )
      {
         // If no open redirection URLs was found by isPossibleOpenRedirect(…) we are already done here
         return false;
      }

      // Remember the current query parameters
      $oldQuery = $this->properties[ 'query' ];

      // Getting the query keys
      $keys = \array_keys( $this->openRedirectionURLs );

      // Loop the query keys and assign the replacement url to this query
      foreach ( $keys as $key )
      {
         $this->properties[ 'query' ][ $key ] = $urlForTestContents;
      }

      // Adjust get_headers() to send a HEAD request
      \stream_context_set_default(
         array(
            'http' => array( 'method' => 'HEAD' )
         )
      );

      // Getting th URL string to call
      $url = (string) $this;
      // Init state flag
      $handleHeaders = true;

      // OK now we can reassign the origin headers
      $this->properties[ 'query' ] = $oldQuery;

      if ( false === ( $headers = \get_headers( $url, 1 ) ) )
      {
         // If the head request fails get headers from GET request
         \stream_context_set_default(
            array(
               'http' => array( 'method' => 'GET' )
            )
         );
         // Get header by GET request
         if ( false === ( $headers = \get_headers( $url, 1 ) ) )
         {
            $handleHeaders = false;
         }
      }
      else
      {
         // reset get_header to use defaut GET request
         \stream_context_set_default(
            array(
               'http' => array( 'method' => 'GET' )
            )
         );
      }

      if ( $handleHeaders && \count( $headers ) > 0 )
      {

         // There are usable headers in response, handle it

         // Make header keys to lower case
         $headers = \array_change_key_case( $headers, \CASE_LOWER );

         if ( isset( $headers[ 'location' ] ) && ( $urlForTestContents === $headers[ 'location' ] ) )
         {
            // Location header to defined URL is defined. Now we know its a open redirection bug usage
            return true;
         }

         if ( isset( $headers[ 'refresh' ] ) && ( \Beluga\strContains( $headers[ 'refresh' ], $urlForTestContents ) ) )
         {
            // Refresh header to defined URL is defined. Now we know its a open redirection bug usage
            return true;
         }

      }

      // We can not work with headers because they dont gives us the required informations.

      // Get the data from URL to check
      $resultContents = \file_get_contents( $url );

      if ( $useAsRegex )
      {
         try { return (bool) \preg_match( $testContents, $resultContents ); }
         catch ( \Exception $ex ) { $ex = null; }
      }

      $regex = '~<meta\s+http-equiv=(\'|")?refresh(\'|")?\s+content=(\'|")\d+;\s*url='
             . \preg_quote( $url )
             . '~i';
      if ( \preg_match( $regex, $resultContents ) )
      {
         return true;
      }

      return $testContents == $resultContents;

   }

   /**
    * Magic getter
    *
    * @param  string $name Field/Property name.
    * @return string
    */
   public function __get( $name )
   {

      // Switch name to lower case
      $lowerName = \strtolower( $name );

      switch ( $lowerName )
      {

         case 'scheme':
            if ( empty( $this->properties[ $lowerName ] ) )
            {
               $this->properties[ $lowerName ] = static::$fallbackScheme;
            }
            return $this->properties[ $lowerName ];

         case 'port':
            if ( $this->properties[ $lowerName ] > 0 )
            {
               return $this->properties[ $lowerName ];
            }
            if ( empty( $this->properties[ 'scheme' ] ) )
            {
               $this->properties[ 'scheme' ] = static::$fallbackScheme;
            }
            switch ( \strtolower( $this->properties[ 'scheme' ] ) )
            {
               case static::SCHEME_HTTP : return 80;
               case static::SCHEME_SSL  : return 443;
               case static::SCHEME_FTP  : return 21;
               default                : return 0;
            }

         case 'path':
            if ( \strlen( \trim( $this->properties[ $lowerName ] ) ) < 1 )
            {
               return '/';
            }
            if ( ! \Beluga\strStartsWith( $this->properties[ $lowerName ], '/' ) )
            {
               return '/' . $this->properties[ $lowerName ];
            }
            return $this->properties[ $lowerName ];

         case 'querystring':
            if ( \count( $this->properties[ 'query' ] ) < 1 )
            {
               return '';
            }
            return '?' . \http_build_query( $this->properties[ 'query' ] );

         case 'query':
            return $this->properties[ 'query' ];

         case 'anchor':
            if ( \strlen( \trim( $this->properties[ $lowerName ] ) ) < 1 )
            {
               return '';
            }
            if ( !\Beluga\strStartsWith( $this->properties[ $lowerName ], '#' ) )
            {
               return '#' . $this->properties[ $lowerName ];
            }
            return $this->properties[ $lowerName ];

         default:
            return isset( $this->properties[ $lowerName ] ) ? $this->properties[ $lowerName ] : false;

      }

   }

   public function __set( $name, $value )
   {

      // Switch name to lower case
      $lowerName = \strtolower( $name );

      switch ( $lowerName )
      {

         case 'scheme':
            if ( ! \preg_match( '~^[a-z]{3,7}$~i', $value ) )
            {
               throw new ArgumentError( $name, $value, 'Web', 'Invalid URL scheme!' );
            }
            $this->properties[ $lowerName ] = $value;
            break;

         case 'port':
            if ( \is_null( $value ) )
            {
               $this->properties[ $lowerName ] = 0;
               break;
            }
            if ( ! TypeTool::IsInteger( $value ) )
            {
               throw new ArgumentError( $name, $value, 'Web', 'Invalid URL port number!' );
            }
            $intValue = \intval( $value );
            if ( $intValue < 0 )
            {
               $intValue = 0;
            }
            $this->properties[ $lowerName ] = $intValue;
            break;

         case 'authuser':
         case 'authpass':
         case 'authpassword':
            if ( empty( $value ) )
            {
               $this->properties[ $lowerName ] = '';
               break;
            }
            $this->properties[ $lowerName ] = $value;
            break;

         case 'domain':
            if ( ! ( $value instanceof Domain ) )
            {
               throw new ArgumentError( $name, $value, 'Web', 'Invalid URL domain part definition!' );
            }
            $this->properties[ $lowerName ] = $value;
            break;

         case 'path':
            if ( \is_null( $value ) || ! \is_string( $value ) )
            {
               $this->properties[ $lowerName ] = '/';
               break;
            }
            if ( ! \preg_match( '#^[a-z0-9_.:,@%/+*~$-]+$#i', $value ) )
            {
               throw new ArgumentError( $name, $value, 'Web', 'Invalid URL path part definition!' );
            }
            $this->properties[ $lowerName ] = $value;
            break;

         case 'query':
            if ( \is_null( $value ) || ( ! \is_string( $value ) && ! \is_array( $value ) ) )
            {
               $this->properties[ 'query' ] = array();
               break;
            }
            if ( \is_string( $value ) )
            {
               $this->properties[ 'query' ] = $this->parseQuery( $value );
               break;
            }
            $this->properties[ 'query' ] = $value;
            break;

         case 'anchor':
            if ( empty( $value ) )
            {
               $this->properties[ $lowerName ] = '';
               break;
            }
            if ( ! \preg_match( '~^#?[a-z_-][a-z0-9_.-]*$~i', $value ) )
            {
               throw new ArgumentError( $name, $value, 'Web', 'Invalid URL anchor part definition!' );
            }
            $this->properties[ $lowerName ] = '#' . \ltrim( $value, '#' );
            break;

      }

   }

   /**
    * The magic string cast method.
    *
    * @return string
    */
   public function __toString()
   {

      $url = $this->Scheme . '://';

      if ( $this->hasLoginData() )
      {
         if ( ! empty( $this->properties[ 'authuser' ] ) )
         {
            $url .= $this->properties[ 'authuser' ];
         }
         $url .= ':';
         if ( ! empty( $this->properties[ 'authpass' ] ) )
         {
            $url .= $this->properties[ 'authpass' ];
         }
         $url .= '@';
      }

      $url .= $this->properties[ 'domain' ]->toString();

      if ( $this->properties[ 'port' ] > 0 )
      {
         $url .= ( ':' . $this->properties[ 'port' ] );
      }

      $url .= ( $this->Path . $this->Anchor . $this->QueryString );

      return $url;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Finds all valid URLs inside the defined text and returns it as a string array.
    *
    * @param  string $text The text where the URLs should be extracted from
    * @param  array  $ignoreDomains Numeric indicated array, defining domains that should be ignored
    * @return array
    */
   public static function FindAllUrls( $text, array $ignoreDomains = [] )
   {

      $result  = [];
      $matches = null;
      \preg_match_all( static::$URL_FINDER, $text, $matches );

      if ( \count( $matches ) > 0 && \count( $matches[ 0 ] ) > 0 )
      {
         foreach ( $matches[ 0 ] as $match )
         {
            if ( false === ( $url = Url::Parse( $match ) ) )
            {
               continue;
            }
            if ( \in_array( $url->Domain->toString(), $ignoreDomains )
              || \in_array( $url->Domain->SLD->toString(), $ignoreDomains ) )
            {
               continue;
            }
            $result[] = $match;
         }
      }

      \preg_match_all( '~(?<=\A|\s)www\.([a-z0-9][a-z0-9_./+%?&#-]+)~i', $text, $matches );
      if ( \count( $matches ) > 0 && \count( $matches[ 0 ] ) > 0 )
      {
         foreach ( $matches[ 0 ] as $match )
         {
            if ( false === ( $url = Url::Parse( $match ) ) )
            {
               continue;
            }
            if ( \in_array( $url->Domain->toString(), $ignoreDomains )
              || \in_array( $url->Domain->SLD->toString(), $ignoreDomains ) )
            {
               continue;
            }
            $result[] = 'http://' . $match;
          }
      }

      return $result;

   }

   /**
    * Parses a URL string and returns the resulting {@see \Beluga\Web\Url} instance. If parsing fails, it returns
    * boolean FALSE.
    *
    * @param  string $urlString    The URL string to parse
    * @return \Beluga\Web\Url|bool Returns the URL instance, or FALSE if parsing fails.
    */
   public static function Parse( $urlString )
   {

      if ( ! \preg_match( '~^[^:]+://~i', $urlString )
        && ! \preg_match( '~^mailto:[a-z0-9_]~i', $urlString ))
      {
         // $urlString dont starts with a valid scheme => Append the fallback scheme.
         switch ( static::$fallbackScheme )
         {
            case 'mailto':
               if ( false === MailAddress::Parse( $urlString, false, false, true ) )
               {
                  return false;
               }
               $urlString = 'mailto:' . $urlString;
               break;
            default:
               $urlString = static::$fallbackScheme . '://' . $urlString;
               break;
         }
      }

      // Extract the URL information
      $urlInfo = static::getUrlInfos( $urlString );

      if ( ! \is_array( $urlInfo ) || \count( $urlInfo ) < 1 )
      {
         // No arms => no cookies :-(
         return false;
      }

      // Switch the case of the array keys to lower case.
      $objectData = \array_change_key_case( $urlInfo, \CASE_LOWER );

      // The host must be defined!
      if ( empty( $objectData[ 'host' ] ) )
      {
         return false;
      }

      // Init the Properties
      $properties = array(
         'scheme'   => static::$fallbackScheme,
         'port'     => 0,
         'authuser' => '',
         'authpass' => '',
         'path'     => '/',
         'query'    => [],
         'anchor'   => '',
         'domain'   => false
      );

      if ( isset( $objectData[ 'scheme' ] ) )
      {
         $properties[ 'scheme' ] = \strtolower( $objectData[ 'scheme' ] );
      }

      if ( isset( $objectData[ 'port' ] ) )
      {
         $properties[ 'port' ] = \intval( $objectData[ 'port' ] );
      }

      if ( isset( $objectData[ 'user' ] ) )
      {
         $properties[ 'authuser' ] = $objectData[ 'user' ];
      }

      if ( isset( $objectData[ 'pass' ] ) )
      {
         $properties[ 'authpass' ] = $objectData[ 'pass' ];
      }

      if ( isset( $objectData[ 'path' ] ) )
      {
         $properties[ 'path' ] = $objectData[ 'path' ];
      }

      if ( isset( $objectData[ 'query' ] ) )
      {
         $properties[ 'query' ] = static::parseQuery ( $objectData[ 'query' ] );
      }

      if ( isset( $objectData[ 'fragment' ] ) )
      {
         $properties[ 'anchor' ] = $objectData[ 'fragment' ];
      }

      if ( isset( $objectData[ 'host' ] ) )
      {
         $properties[ 'domain' ] = Domain::Parse( $objectData[ 'host' ], false );
      }

      if ( ! ( $properties[ 'domain' ] instanceof Domain ) )
      {
         // if no usable domain is defined, return FALSE
         return false;
      }

      return new Url( $properties );

   }

   /**
    * UTF-8 aware parse_url() replacement.
    *
    * Returned values can use the following keys (all optionally):
    *
    * - scheme: e.g. http
    * - host
    * - port
    * - user
    * - pass
    * - path
    * - query: after the question mark ?
    * - fragment: after the hashmark #
    *
    * @param  string $url
    * @return array|bool Returns the resulting array, or FALSE, if parsing fails
    */
   public static function getUrlInfos( $url )
   {

      // Encode the URL
      $encUrl = \preg_replace_callback(
         '%[^:/@?&=#]+%usD',
         function ( $match )
         {
            return \urlencode( $match[ 0 ] );
         },
         $url
      );

      if ( false === ( $parts = \parse_url( $encUrl ) ) )
      {
         return false;
      }

      foreach( $parts as $name => $value )
      {
         $parts[ $name ] = \urldecode( $value );
      }

      return $parts;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   private static function parseQuery( $query )
   {

      if ( empty( $query ) )
      {
         return [];
      }

      $elements = [];

      \parse_str( $query, $elements );

      if ( ! \is_array( $elements ) )
      {
         return [];
      }

      return $elements;

   }

   // </editor-fold>


}

