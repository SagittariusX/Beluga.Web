<?php
/**
 * This file defines the {@see \Beluga\Web\MailAddress} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


use \Beluga\Type;


/**
 * This class defines an email address.
 *
 * @since      v0.1
 * @property-read string             $user
 * @property-read \Beluga\Web\Domain $domain
 */
class MailAddress
{


   // <editor-fold desc="// = = = =   P R O T E C T E D   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The mail address user part (every thing before the first @)
    *
    * @var string
    */
   protected $_userPart;

   /**
    * The domain part of the mail address.
    *
    * @var \Beluga\Web\Domain
    */
   protected $_domainPart;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param string         $userPart   The mail address user part (every thing before the first @)
    * @param \Beluga\Web\Domain $domainPart The domain part of the mail address.
    */
   private function __construct( $userPart, Domain $domainPart )
   {

      $this->_userPart   = $userPart;
      $this->_domainPart = $domainPart;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Magic getter.
    *
    * @param  string $name
    * @return mixed
    */
   public function __get( $name )
   {

      switch ( \strtolower( $name ) )
      {

         case 'user':
         case 'userpart':
            return $this->_userPart;

         case 'domain':
         case 'domainpart':
            return $this->_domainPart;

         default:
            return false;

      }

   }

   /**
    * Returns the mail address user part (every thing before the first @)
    *
    * @return string
    */
   public function getUserPart() : string
   {

      return $this->_userPart;

   }

   /**
    * Returns the domain part of the mail address.
    *
    * @return \Beluga\Web\Domain
    */
   public function getDomainPart()
   {

      return $this->_domainPart;

   }

   /**
    * Magic method to support casting to string.
    *
    * @return string
    */
   public function __toString()
   {

      return $this->_userPart . '@' . $this->_domainPart->toString();

   }

   /**
    * Checks if the defined value is equal to current mail address value.
    *
    * @param  mixed   $value  The value to check against.
    * @param  boolean $strict Can only be equal if value is of type {@see \Beluga\Web\MailAddress}
    * @return boolean
    */
   public function equals( $value, $strict = false )
   {

      if ( \is_null( $value ) )
      {
         return false;
      }

      if ( $value instanceof MailAddress )
      {
         return
            ( $value->_userPart == $this->_userPart )
            &&
            ( ( (string) $value->_domainPart ) === ( (string) $this->_domainPart ) );
      }

      if ( $strict )
      {
         return false;
      }

      $val = null;

      if ( \is_string( $value ) )
      {
         if ( false !== ( $val = MailAddress::Parse( $value ) ) )
         {
            return
               ( $val->_userPart === $this->_userPart )
               &&
               ( ( (string) $val->_domainPart ) === ( (string) $this->_domainPart ) );
         }
         return false;
      }

      if ( $value instanceof Domain )
      {
         return ( ( (string) $value ) === ( (string) $this->_domainPart ) );
      }

      $typeInfo = new Type( $value );
      if ( ! $typeInfo->hasAssociatedString() )
      {
         return false;
      }

      if ( false === ($val = MailAddress::Parse( $typeInfo->getStringValue(), false, false, true ) ) )
      {
         return false;
      }

      return
         ( $val->_userPart === $this->_userPart )
         &&
         ( ( (string) $val->_domainPart ) === ( (string) $this->_domainPart ) );

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Parses a string with an e-mail address to a {@see \Beluga\Web\MailAddress} instance.
    *
    * @param  string  $mailAddressString The e-mail address string.
    * @param  boolean $requireTLD        Must the mail address contain an TLD to be parsed as valid? (default=true)
    * @param  boolean $requireKnownTLD   Must the mail address contain an known TLD to be parsed as valid? (default=true)
    * @param  boolean $allowReserved     Are reserved hosts/domains/TLDs allowed to be parsed as valid? (default=false)
    * @return \Beluga\Web\MailAddress|bool returns the MailAddress instance, or FALSE if parsing fails.
    */
   public static function Parse(
      $mailAddressString, $requireTLD = true, $requireKnownTLD = true, $allowReserved = false )
   {

      if ( ! \is_string( $mailAddressString ) )
      {
         // If $mailAddressString is not a string, dont handle it…
         return false;
      }

      if ( false === ( $firstAtIndex = \Beluga\strPos( $mailAddressString, '@' ) ) )
      {
         // If $mailAddressString do not contain the @ char, parsing fails
         return false;
      }

      // Get the user part string
      $user   = \strtolower( \substr( $mailAddressString, 0, $firstAtIndex ) );
      // Get the domain part string
      $domain = \strtolower( \substr( $mailAddressString, $firstAtIndex + 1 ) );

      if ( ! \preg_match( '~^[a-zäÄöÖüÜ_][a-zäÄöÖüÜß0-9_.%+-]*$~i', $user ) )
      {
         // If the user part uses invalid characters, parsing fails
         return false;
      }

      $_domain = Domain::Parse( $domain, $requireTLD && $requireKnownTLD );

      if ( ! ( $_domain instanceof Domain ) )
      {
         // If the domain part is initially wrong, parsing fails
         return false;
      }

      if ( $requireTLD && ! $_domain->HasTLD )
      {
         // If a TLD is required but not defined, parsing fails
         return false;
      }

      if ( ! $allowReserved && $_domain->IsReserved )
      {
         // If the domain part points to a reserved domain name or TLD, parsing fails if this is forbidden
         return false;
      }

      // All is fine, return the resulting MailAddress instance.
      return new MailAddress( $user, $_domain );

   }

   /**
    * Extracts all e-mail addresses from inside the defined $string.
    *
    * @param  string $string The string to parse.
    * @return \Beluga\Web\MailAddress[] Return the found mail addresses as a \Beluga\Web\MailAddress array.
    */
   public static function ExtractAllFromString( $string )
   {

      // Init the resulting addresses array
      $addresses = [];
      $matches   = null;

      // Find some rough mail address definitions
      if ( ! \preg_match_all( '~[a-zäÄöÖüÜß0-9%_.+-]+@[]+[a-z0-9_.-]+~i', $string, $matches ) )
      {
         return $addresses;
      }

      foreach ( $matches[ 0 ] as $match )
      {
         $address = MailAddress::Parse( $match, false, false, true );
         if ( ! ( $address instanceof MailAddress ) )
         {
            continue;
         }
         $addresses[] = $address;
      }

      return $addresses;

   }

   // </editor-fold>


}

