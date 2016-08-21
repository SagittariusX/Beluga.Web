<?php
/**
 * This file defines the {@see \Beluga\Web\Domain} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


/**
 * Defines a domain. A domain is represented by a sub domain name (3rd+ level domain name label) a second
 * level domain (2nd level domain name label) the TLD (top level domain name label) and a root label.
 *
 * The sub domain name and the root label is always optionally. Second level domain and/or TLD must be defined.
 * (At least only one of it is required!)
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The domain from example above is <b>www.example.com</b> or <b>www.example.com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about
 * Domain details.
 *
 * @property-read string|NULL            $SubdomainName     The name of the optional subdomain (third+ level
 *                                                          domain label). If no subdomain exists it's NULL.
 * @property-read \Beluga\Web\SecondLevelDomain $SecondLevelDomain The contained second level domain part.
 * @property-read \Beluga\Web\SecondLevelDomain $SLD            Shortkey to $SecondLevelDomain.
 * @property-read boolean                $IsIPAddress       Is the current host defined by a IP address?
 * @property-read boolean                $IsIPv4Address     Is the current host defined by a IPv4 address?
 * @property-read boolean                $IsIPv6Address     Is the current host defined by a IPv6 address?
 * @property-read boolean                $IsFullyQualified  Is the host defined in full qualified manner? It
 *                                                          means, is the root-label (always a empty string
 *                                                          separated by a dot) defined? e.g.: 'example.com.'
 * @property-read boolean                $HasTLD            Returns if a usable TLD is defined
 * @property-read boolean                $HasKnownTLD       Returns if the TLD is a generally known, registered TLD.
 * @property-read boolean                $HasDoubleTLD      Returns if the TLD is a known double TLD like co.uk.
 * @property-read boolean                $HasSubDomain      Returns if the current Host uses a subdomain.
 * @property-read boolean                $IsCountry         Is the current TLD value (if defined) a known COUNTRY
 *                                                          TLD? Country TopLevelDomains are: 'cz', 'de', etc.
 *                                                          and also localized country TopLevelDomains (xn--…).
 * @property-read boolean                $IsGeneric         Is the current TLD value (if defined) a known GENERIC
 *                                                          TLD? Generic TopLevelDomains are: 'com', 'edu', 'gov',
 *                                                          'int', 'mil', 'net', 'org' and also the associated
 *                                                          localized TopLevelDomains (xn--…).
 * @property-read boolean                $IsGeographic      Is the current TLD value (if defined) a GEOGRAPHIC
 *                                                          TLD? Geogr. TLDs are: 'asia', 'berlin', 'london', etc.
 * @property-read boolean                $IsLocalized       Is the current TLD (if defined) a known LOCALIZED
 *                                                          UNICODE TLD? Localized TLDs are starting with 'xn--'.
 * @property-read boolean                $IsReserved        Is the SLD (Hostname or TLD or both or ID-Address)
 *                                                          representing a reserved element? Reserved TLDs are
 *                                                          'arpa', 'example', 'test', 'tld'. Reserved SLDs are
 *                                                          'example.(com|net|org)', 'local', 'localhost' and
 *                                                          'localdomain'. Reserved IPv4 Address ranges are:
 *                                                          '0.0.0.0 - 0.255.255.255', '10.0.0.0 - 10.255.255.255',
 *                                                          '127.0.0.0 - 127.255.255.255', '100.64.0.0 - 100.127.255.255',
 *                                                          '169.254.0.0 - 169.254.255.255', '172.16.0.0 - 172.31.255.255',
 *                                                          '192.0.0.0 - 192.0.0.255', '198.51.100.0 - 198.51.100.255',
 *                                                          '192.88.99.0 - 192.88.99.255', '192.168.0.0 - 192.168.255.255',
 *                                                          '198.18.0.0 - 198.19.255.255', '224.0.0.0 - 255.255.255.255'
 * @property-read boolean                $IsLocal           Is the SLD (Hostname or TLD or both) representing a
 *                                                          local SLD(-Element)?
 * @property-read boolean                $IsUrlShortener    Is the SLD pointing to a known public URL shortener
 *                                                          service? They are used to shorten or hide some long or
 *                                                          bad URLs.
 * @property-read boolean                $IsDynamic         Is the SLD pointing to a known public dynamic DNS service
 * @property-read string                 $IPAddress         Returns the IP address, if defined.
 * @TODO          Check state of known reserved and/or local IPv6 addresses must be implemented.
 */
final class Domain
{


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The name of the optional sub domain (third+ level domain label). If no sub domain exists the value is NULL.
    *
    * @var string|NULL
    */
   private $_subdomainName;

   /**
    * The contained second level domain part.
    *
    * @var \Beluga\Web\SecondLevelDomain
    */
   private $_sld;

   /**
    * A array of states (Detail information about the host)
    *
    * @var array
    */
   private $states;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = =">

   private function __construct( $subdomainName, SecondLevelDomain $sld = null )
   {

      $this->_subdomainName = $subdomainName;
      $this->_sld           = $sld;
      $value                = (string) $this;

      $this->states         = [
         'IPV4ADDRESS'   => (bool) \preg_match(
            "~^(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}$~",
            $value
         ),
         'IPV6ADDRESS'   => (bool) \preg_match(
            "~^([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){7}|::[0-9a-fA-F]{1,4}([0-9a-fA-F:.]+)?(/\d{1,3})?|::[0-9a-fA-F]{0,4})(/\d{1,3})?$~",
            $value
         ),
         'LOCAL'      => (bool) \preg_match(
            "~^(127(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|172\.(1[6-9]|2\d|3[01])(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2}||192\.168(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2})$~",
            $value
         ),
         'RESERVED'         => (bool) \preg_match(
            '~^(127(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|(100\.(6[4-9]|[7-9]\d|1([01]\d|2[0-7]))|169\.254|172\.(1[6-9]|2\d|3[01])|192\.168|198\.1[89])(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){2}|192\.0\.[02]\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))|198\.51\.100\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))|192\.88\.99\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5]))))$~',
            $value
         )
      ];

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns the the Domain string value of this instance. If its defined as a fully qualified Domain
    * (it must ends with a dot) its returned with the trailing dot, otherwise without it.
    *
    * @return string
    */
   public function __toString()
   {

      if ( empty( $this->_subdomainName ) )
      {

         if ( \is_null( $this->_sld ) )
         {
            return '';
         }

         return (string) $this->_sld;

      }

      if ( \is_null( $this->_sld ) )
      {
         return $this->_subdomainName;
      }

      return $this->_subdomainName . '.' . (string) $this->_sld;

   }

   /**
    * Returns the fully qualified Domain. A fully qualified Domain always ends with a dot (like 'www.example.com.')
    *
    * @return string
    */
   public function toFullyQualifiedString()
   {

      if ( empty( $this->_subdomainName ) )
      {

         if ( \is_null( $this->_sld ) )
         {
            return '';
         }

         return $this->_sld->toFullyQualifiedString();

      }

      if ( \is_null( $this->_sld ) )
      {
         return $this->_subdomainName;
      }

      return $this->_subdomainName . '.' . $this->_sld->toFullyQualifiedString();

   }

   /**
    * Returns always the NOT fully qualified Domain, also if a fully qualified Domain is used.
    *
    * @return string
    */
   public function toString()
   {

      if ( empty( $this->_subdomainName ) )
      {

         if ( \is_null( $this->_sld ) )
         {
            return '';
         }

         return $this->_sld->toString();

      }

      if ( \is_null( $this->_sld ) )
      {
         return $this->_subdomainName;
      }

      return $this->_subdomainName . '.' . $this->_sld->toString();

   }

   public function __get( $name )
   {

      switch ( \strtolower( $name ) )
      {

         case 'subdomainname':
            return $this->IsIPAddress ? '' : $this->_subdomainName;

         case 'ipaddress':
            return ! $this->IsIPAddress ? '' : (string) $this;

         case 'secondleveldomain':
         case 'sld':
            return $this->_sld;

         case 'isipaddress':
            return $this->states[ 'IPV4ADDRESS' ] || $this->states[ 'IPV6ADDRESS' ];

         case 'isipv4address':
            return $this->states[ 'IPV4ADDRESS' ];

         case 'isipv6address':
            return $this->states[ 'IPV6ADDRESS' ];

         case 'isfullyqualified':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsFullyQualified;

         case 'hastld':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->HasTLD;

         case 'hasdoubletld':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->HasDoubleTLD;

         case 'hasknowntld':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->HasKnownTLD;

         case 'hassubdomain':
            return ! $this->IsIPAddress && ! empty( $this->_subdomainName );

         case 'iscountry':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsCountry;

         case 'isgeneric':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsGeneric;

         case 'isgeographic':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsGeographic;

         case 'islocalized':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsLocalized;

         case 'isreserved':
            if ( $this->states[ 'RESERVED' ] || $this->states[ 'LOCAL' ] ) { return true; }
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsReserved;

         case 'islocal':
            if ( $this->states[ 'LOCAL' ] ) { return true; }
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsLocal;

         case 'isurlshortener':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsUrlShortener;

         case 'isdynamic':
            if ( \is_null( $this->_sld ) ) { return false; }
            return $this->_sld->IsDynamic;

         default:
            return true;

      }

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Parses the defined Domain string to a {@see \Beluga\Web\Domain} instance. On error it returns FALSE.
    *
    * @param  string  $domainString       The domain string, including optional sub domain name, domain name and TLD.
    * @param  boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @return \Beluga\Web\Domain|bool     Returns the resulting \Beluga\Web\Domain instance or FALSE.
    */
   public static function Parse( $domainString, $allowOnlyKnownTlds = false )
   {

      if ( empty( $domainString ) || ! \is_string( $domainString ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      $_domainString = $domainString;

      if ( false === ( $_sld = SecondLevelDomain::ParseExtract( $_domainString, $allowOnlyKnownTlds ) ) )
      {
         if ( ! \preg_match( "~^((\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))(\.(\d{1,2}|1\d{2}|2([0-4]\d|5([0-4]\d|5[0-5])))){3}|([0-9a-fA-F]{1,4}(:[0-9a-fA-F]{1,4}){7}|::[0-9a-fA-F]{1,4}([0-9a-fA-F:.]+)?(/\d{1,3})?|::[0-9a-fA-F]{0,4})(/\d{1,3})?)$~", $domainString ) )
         {
            return false;
         }
         else
         {
            $myDomain = new Domain( $domainString, null );
            return $myDomain;
         }
      }

      if ( ! empty( $_domainString ) )
      {
         if ( ! \preg_match( '~^[a-z0-9][a-z0-9_.-]*$~', $_domainString )
             || \preg_match( '~(\.[^a-z0-9_]|[^a-z0-9_]\.)~', $_domainString )
             || \preg_match( '~[^a-z0-9_]$~', $_domainString )
             || \count( \explode( '.', $_domainString ) ) > 3 )
         {
            return false;
         }
      }
      else { $_domainString = null; }

      if ( $allowOnlyKnownTlds && ! $_sld->HasKnownTLD )
      {
         return false;
      }

      $myDomain = new Domain( $_domainString, $_sld );

      return $myDomain;

   }

   // </editor-fold>


}

