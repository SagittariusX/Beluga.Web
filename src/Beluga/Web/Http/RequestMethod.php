<?php
/**
 * This file defines {@see \Beluga\Web\Http\RequestMethod}.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @subpackage     Http
 * @version        0.1.0
 */


namespace Beluga\Web\Http;


/**
 * Hypertext Transfer Protocol (HTTP) Method Registry
 *
 * @package Beluga\Web\Http
 */
interface RequestMethod
{

   // RFC7231
   const GET      = 'GET';
   const POST     = 'POST';
   const HEAD     = 'HEAD';
   const PUT      = 'PUT';
   const DELETE   = 'DELETE';
   const CONNECT  = 'CONNECT';
   const OPTIONS  = 'OPTIONS';
   const TRACE    = 'TRACE';

   // RFC3253
   const BASELINE = 'BASELINE';

   // RFC2068
   const LINK     = 'LINK';
   const UNLINK   = 'UNLINK';

   // RFC3253
   const MERGE = 'MERGE';
   const BASELINECONTROL = 'BASELINE-CONTROL';
   const MKACTIVITY = 'MKACTIVITY';
   const VERSIONCONTROL = 'VERSION-CONTROL';
   const REPORT = 'REPORT';
   const CHECKOUT = 'CHECKOUT';
   const CHECKIN = 'CHECKIN';
   const UNCHECKOUT = 'UNCHECKOUT';
   const MKWORKSPACE = 'MKWORKSPACE';
   const UPDATE = 'UPDATE';
   const LABEL = 'LABEL';

   // RFC3648
   const ORDERPATCH = 'ORDERPATCH';

   // RFC3744
   const ACL = 'ACL';

   // RFC4437
   const MKREDIRECTREF = 'MKREDIRECTREF';
   const UPDATEREDIRECTREF = 'UPDATEREDIRECTREF';

   // RFC4791
   const MKCALENDAR = 'MKCALENDAR';

   // RFC4918
   const PROPFIND = 'PROPFIND';
   const LOCK = 'LOCK';
   const UNLOCK = 'UNLOCK';
   const PROPPATCH = 'PROPPATCH';
   const MKCOL = 'MKCOL';
   const COPY = 'COPY';
   const MOVE = 'MOVE';

   // RFC5323
   const SEARCH = 'SEARCH';

   // RFC5789
   const PATCH = 'PATCH';

   // RFC5842
   const BIND = 'BIND';
   const UNBIND = 'UNBIND';
   const REBIND = 'REBIND';

}

