<?php
/**
 * In this file the class '\Beluga\Web\Exception' is defined.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


use \Beluga\BelugaError;


/**
 * This class defines a exception, used as base exception of all web exceptions.
 *
 * It extends from {@see \Beluga\BelugaError}.
 *
 * @since v0.1
 */
class WebError extends BelugaError
{


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init's a new instance.
    *
    * @param string         $package
    * @param string         $message  The error message.
    * @param integer        $code     The optional error code (Defaults to \E_USER_ERROR)
    * @param \Throwable     $previous A optional previous exception
    */
   public function __construct( string $package, $message, int $code = 256, \Throwable $previous = null )
   {

      parent::__construct(
         $package,
         $message,
         $code,
         $previous
      );

   }

   // </editor-fold>


}

