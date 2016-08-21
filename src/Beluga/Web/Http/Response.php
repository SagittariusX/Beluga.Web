<?php
/**
 * This file defines {@see \Beluga\Web\Http\Response}.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @subpackage     Http
 * @version        0.1.0
 */


namespace Beluga\Web\Http;


class Response
{


   // <editor-fold desc="// = = = =   P U B L I C   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The response code of the cURL request.
    *
    * @var integer
    */
   public $code;

   /**
    * The the raw body of the cURL response.
    *
    * @var string
    */
   public $rawBody;

   /**
    * The body from cURL response.
    *
    * @var mixed
    */
   public $body;

   /**
    * The headers from cURL response. Keys=Header-Names value=Header-Values
    *
    * @var array
    */
   public $headers;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init's a new instance.
    *
    * @param integer $code      The response code of the cURL request.
    * @param string  $raw_body  The the raw body of the cURL response.
    * @param string  $headers   The raw header string from cURL response.
    * @param array   $json_args The arguments to pass to json_decode function.
    */
   public function __construct( $code, string $raw_body, string $headers, array $json_args = [] )
   {

      $this->code    = $code;
      $this->headers = $this->parseHeaders( $headers );
      $this->rawBody = $raw_body;
      $this->body    = $raw_body;

      // make sure raw_body is the first argument
      \array_unshift( $json_args, $raw_body );
      $json = \call_user_func_array( '\\json_decode', $json_args );

      if ( json_last_error() === \JSON_ERROR_NONE )
      {
         $this->body = $json;
      }

   }

   // </editor-fold>


   private function parseHeaders( $raw_headers )
   {
      if ( \function_exists( 'http_parse_headers' ) )
      {
         return \http_parse_headers( $raw_headers );
      }
      else
      {
         $key     = '';
         $headers = array();
         foreach ( \explode( "\n", $raw_headers ) as $h )
         {
            $hn = \explode( ':', $h, 2 );

            if ( isset( $hn[ 1 ] ) )
            {
               if ( ! isset( $headers[ $hn[ 0 ] ] ) )
               {
                  $headers[ $hn[ 0 ] ] = \trim( $hn[ 1 ] );
               }
               else if ( \is_array( $headers[ $hn[ 0 ] ] ) )
               {
                  $headers[ $hn[ 0 ] ] = \array_merge(
                     $headers[ $hn[ 0 ] ],
                     array( \trim( $hn[ 1 ] ) )
                  );
               }
               else
               {
                  $headers[ $hn[ 0 ] ] = \array_merge(
                     array( $headers[ $hn[ 0 ] ] ),
                     array( \trim( $hn[ 1 ] ) )
                  );
               }

               $key = $hn[ 0 ];
            }
            else
            {
               if ( \substr( $hn[ 0 ], 0, 1 ) == "\t" )
               {
                  $headers[ $key ] .= "\r\n\t" . \trim( $hn[ 0 ] );
               }
               else if ( ! $key )
               {
                  $headers[ 0 ] = \trim( $hn[ 0 ] );
               }
            }
         }

         return $headers;

      }

   }


}


