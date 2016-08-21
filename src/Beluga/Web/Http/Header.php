<?php
/**
 * This file defines the {@see \Beluga\Web\Http\Header} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @subpackage     Http
 * @version        0.1.0
 */


namespace Beluga\Web\Http;


use \Beluga\IO\MimeTypeTool;
use \Beluga\TypeTool;


/**
 * This is a class with some static methods for easier send HTTP headers.
 *
 * @since v0.1
 */
abstract class Header
{


   // <editor-fold desc="// = = = =   C L A S S   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * MIME type of plain text resources.
    */
   const MIME_TEXT = 'text/plain';

   /**
    * MIME type of HTML resources.
    */
   const MIME_HTML = 'text/html';

   /**
    * MIME type of XHTML resources.
    */
   const MIME_XHTML = 'application/xhtml+xml';

   /**
    * MIME type of PDF resources.
    */
   const MIME_PDF = 'application/pdf';

   /**
    * MIME type of GIF image resources.
    */
   const MIME_GIF = 'image/gif';

   /**
    * MIME type of JPEG image resources.
    */
   const MIME_JPEG = 'image/jpeg';

   /**
    * MIME type of png image resources.
    */
   const MIME_PNG = 'image/png';

   /**
    * MIME type of ICON image resources.
    */
   const MIME_ICO = 'image/x-icon';

   /**
    * MIME type of JavaScript resources.
    */
   const MIME_JS = 'text/javascript';

   /**
    * MIME type of CSS resources.
    */
   const MIME_CSS = 'text/css';

   /**
    * MIME type of ATOM feed resources.
    */
   const MIME_ATOMFEED = 'application/atom+xml';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Sends, if the user agent accepts it, a 'application/xhtml+xml' Content-Type header. Otherwise it
    * sends a 'text/html' Content-Type header. The XHTML content type is only send, if the client (browser) sends the
    * HTTP-Accept header that must contain 'application/xhtml+xml'.
    *
    * @param  string $charset The charset to send. (default=NULL)
    */
   public static function SendXHtmlContentType( string $charset = null )
   {

      if ( ! isset( $_SERVER[ 'HTTP_ACCEPT' ] ) )
      {
         static::SendContentType( static::MIME_HTML, $charset );
      }
      else
      {
         if ( !\preg_match( '~application/xhtml\+xml~', \strtolower( $_SERVER[ 'HTTP_ACCEPT' ] ) ) )
         {
            static::SendContentType( static::MIME_HTML, $charset );
         }
         else
         {
            static::SendContentType( static::MIME_XHTML, $charset );
         }
      }

   }

   /**
    * Sends the 'text/plain' Content-Type header.
    *
    * @param  string $charset The charset to send. (default=NULL)
    */
   public static function SendTextContentType( string $charset = null )
   {

      static::SendContentType( static::MIME_TEXT, $charset );

   }

   /**
    * Sends the 'text/html' Content-Type header.
    *
    * @param  string $charset The charset to send. (default=NULL)
    */
   public static function SendHtmlContentType( string $charset = null )
   {

      static::SendContentType( static::MIME_HTML, $charset );

   }

   /**
    * Sends the defined Content-Type header with a optional chaset.
    *
    * @param  string $mimetype The MIME type.
    * @param  string $charset  The optional charset to send. (default='utf-8')
    */
   public static function SendContentType( string $mimetype = 'text/html', string $charset = 'utf-8' )
   {

      if ( empty( $charset ) )
      {
         \header( 'Content-Type: ' . $mimetype );
      }
      else
      {
         \header( 'Content-Type: ' . $mimetype . '; charset=' . $charset );
      }

   }

   /**
    * Sends all HTTP headers required to sending a file download to current client.
    *
    * Caching will be deactivated.
    *
    * @param  string $file     Full path to download file. You can also use a file name, but if no file size is
    *                          defined the 'Content-Length' HTTP header is not send. (It means the client download
    *                          processbar can not show the right ready state percentage.
    * @param  int    $filesize A optional file size definition. If undefined and $file is not a accessible file
    *                          path, the 'Content-Length' HTTP header is not send. (default=NULL)
    */
   public static function SendDownloadHeader( string $file, int $filesize = null )
   {

      // Disable all caching mechanisms
      \header( 'Expires: 0' );
      \header( 'Cache-Control: private' );
      \header( 'Pragma: cache' );

      // The next header let the client download the file
      \header( 'Content-Disposition: attachment; filename="' . \rawurlencode( \basename( $file ) ) . '"' );

      // No we must send the associated content type
      static::SendContentType( MimeTypeTool::GetByFileName( $file ), null );

      // If no file size is defined but the file exists, getting the size.
      if ( empty( $filesize ) && \file_exists( $file ) )
      {
         $filesize = \filesize( $file );
      }

      // If a usable file size is known send the 'Content-Length' HTTP header
      if ( ! empty( $filesize ) )
      {
         \header( 'Content-Length: ' . $filesize );
      }

   }

   /**
    * Send a none standards conform refresh header. Most browser supports it, BUT THERE IS NO GUARANTEE that they
    * also handle it in newer releases!
    *
    * @param string  $url         The redirection URL.
    * @param int     $waitSeconds How many seconds should be waited before the redirect is executed? (default=3)
    * @param boolean $exitHere    Exit after sending the header? (default=false)
    */
   public static function SendRedirect( $url, int $waitSeconds = 3, bool $exitHere = false )
   {

      \header( "refresh:{$waitSeconds};url={$url}" );

      if ( $exitHere ) { exit; }

   }

   /**
    * Sends a valid HTTP Location header for doing a redirect.
    *
    * If you dont want to send some other HTTP headers after this header, DONT CHANGE the $exitHere parameter
    * to FALSE.
    *
    * @param string  $path        e.g.: /test/probe.php (Must be a absolute URL path!)
    * @param string  $host        Optional host definition. If not defined here, $_SERVER[ 'HTTP_HOST' ] is used.
    * @param boolean $exitHere    Exit after sending the header? (default=true)
    */
   public static function SendLocation( string $path, string $host = null, bool $exitHere = true )
   {

      if ( empty( $host ) || ! \is_string( $host ) )
      {
         if ( ! isset( $_SERVER[ 'HTTP_HOST' ] ) )
         {
            exit( 'Could not send a Location header if no host is defined!' );
         }
         $host = $_SERVER[ 'HTTP_HOST' ];
      }

      \header( 'Location: http://' . $host . '/' . \ltrim( $path, '/' ) );

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Sends a 300 Multiple Choice HTTP-Header.
    *
    * The requested resource is available by different type. The answer should contains a list of the
    * accepted types. If $newLocation is set to a valid URL an location was send to the address of the server
    * preferred representation.
    *
    * @param string  $newLocation Optionally a absolute URI we will redirect by a Location HTTP header
    * @param boolean $exitHere    Exit after sending the header? Its only used if no Location header is send.
    *                             If a Location header is send its always exited after sending! (default=false)
    */
   public static function SendMultipleChoice( string $newLocation = null, bool $exitHere = false )
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 300 Multiple Choice" );

      if ( !empty( $newLocation ) )
      {
         \header( "Location: {$newLocation}" );
         exit();
      }

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Sends a 400 "Bad Request" HTTP header. (The request message uses a invalid format)
    *
    * @param boolean $exitHere Exit after sending the header? (default=true)
    */
   public static function SendBadRequest( bool $exitHere = true )
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 400 Bad Request" );

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Returns the supported HTTP version, defined by current client. If undefined $$defaultVersion is returned.
    *
    * @param  string $defaultVersion Is used if currently the client dont define the required value.
    * @return string
    */
   public static function GetSupportedHttpVersion( string $defaultVersion = 'HTTP/1.0' )
   {

      if ( isset( $_SERVER[ 'SERVER_PROTOCOL' ] ) )
      {
         // Getting the supported HTTP type from client, if its defined.
         return $_SERVER[ 'SERVER_PROTOCOL' ];
      }

      return $defaultVersion;

   }

   /**
    * Sends a 403 Forbidden HTTP header. (The client dont have the rights to do this request)
    *
    * @param boolean $exitHere Exit after sending the header? (default=true)
    */
   public static function SendForbidden( bool $exitHere = true )
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 403 Forbidden" );

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Sends a 405 "Method Not Allowed" HTTP header. This header should be only send, if e.g. the request method
    * was GET, but only POST is allowed.
    *
    * You must define the allowed request types like 'GET, POST'. Usable types are:
    *
    * - <b>GET</b>
    * - <b>POST</b>
    * - <b>HEAD</b>
    * - <b>PUT</b>
    * - <b>DELETE</b>
    * - <b>TRACE</b>
    * - <b>CONNECT</b>
    * - <b>OPTIONS</b>
    *
    * @param string|array $allowedMethods All allowed request methods, separated by a comma + optional empty char(s)
    * @param boolean $exitHere       Exit after sending the header? (default=true)
    */
   public static function SendMethodNotAllowed( $allowedMethods = 'GET', bool $exitHere = true )
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 405 Method Not Allowed" );

      $methods = \is_array( $allowedMethods ) ? \join( ', ', $allowedMethods ) : $allowedMethods;

      if ( ! empty( $methods ) )
      {
         \header( "Allow: {$allowedMethods}" );
      }

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Sends a 410 "Gone" HTTP header. This header should be only send, if the requested resource no longer exists
    * and is finally removed.
    *
    * @param boolean $exitHere       Exit after sending the header? (default=true)
    */
   public static function SendGone( bool $exitHere = true )
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 410 Gone" );

      if ( $exitHere )
      {
         exit;
      }

   }

   /**
    * Sends a 404 "Page not Found" HTTP header.
    */
   public static function SendError404()
   {

      // Define the supported HTTP type
      $pType = static::GetSupportedHttpVersion();

      \header( "{$pType} 404 Not Found" );

   }

   /**
    * Sends a "Cache-Control" HTTP header.
    *
    * @param boolean $doCache        Use caching?
    * @param boolean $mustRevalidate Should be checked on each request, if a used cache is valid?
    */
   public static function SendCacheControl( bool $doCache = false, bool $mustRevalidate = true )
   {

      \header(
         'Cache-Control: '
            . ( $doCache ? 'cache' : 'no-cache' )
            . ( $mustRevalidate ? ', must-revalidate' : '' )
      );

   }

   /**
    * Sends a "Expires" HTTP header.
    *
    * @param int|\DateTimeInterface|string $expireStamp Optional Timestamp
    */
   public static function SendExpires( $expireStamp = null )
   {

      if ( \is_null( $expireStamp ) || empty( $expireStamp ) )
      {
         $expireStamp = \time();
      }

      if ( \is_int( $expireStamp ) )
      {
         \header( 'Expires: ' . \date( 'D, d M Y H:i:s O', $expireStamp ) );
      }
      else if ( $expireStamp instanceof \DateTimeInterface )
      {
         \header( 'Expires: ' . $expireStamp->format( 'D, d M Y H:i:s O' ) );
      }
      else if ( TypeTool::IsInteger( $expireStamp ) )
      {
         \header( 'Expires: ' . \date( 'D, d M Y H:i:s O', \intval( $expireStamp ) ) );
      }
      else if ( TypeTool::IsStringConvertible( $expireStamp, $str ) )
      {
         $stamp = \strtotime( $str );
         if ( $stamp > 0 )
         {
            \header( 'Expires: ' . \date( 'D, d M Y H:i:s O', $stamp ) );
         }
      }

   }

   /**
    * Sends a P3P Cookie Header, It make older IE versions better usable in case of cookie usage.
    */
   public static function SendP3pCookieHeader()
   {

      \header( 'P3P: CP="NOI NID ADMa OUR IND UNI COM NAV"' );

   }

   // </editor-fold>


}

