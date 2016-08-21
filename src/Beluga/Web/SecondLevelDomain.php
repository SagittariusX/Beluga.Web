<?php
/**
 * This file defines the {@see \Beluga\Web\SecondLevelDomain} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


use \Beluga\ArrayHelper;


/**
 * This class defines a second level domain (SLD). Its defined by the second-level-domain-label and a optional
 * top-level-domain-label. If the TLD label is defined it must be separated from SLD label by a dot. If the TLD
 * is defined it can be defined as a fully qualified TLD
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The second level domain from example above is <b>example.com</b> or <b>example.com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about
 * SecondLevelDomain details.
 *
 * All this states can be accessed via dynamic readonly properties.
 *
 * @since         v0.1.0
 * @property-read string                 $HostName          The host name element string.
 * @property-read \Beluga\Web\TopLevelDomain $TLD              The TopLevelDomain element.
 * @property-read boolean                $IsFullyQualified  Is the SLD defined in full qualified manner? It means, is
 *                                                          the root-label (always a empty string, separated by a
 *                                                          dot) defined? e.g.: 'example.com.'
 * @property-read boolean                $HasTLD            Returns if a usable TLD is defined
 * @property-read boolean                $IsCountry         Is the current TLD value (if defined) a known COUNTRY
 *                                                          TLD? Country TopLevelDomains are: 'cz', 'de', etc. and
 *                                                          also localized country TopLevelDomains (xn--…).
 * @property-read boolean                $IsGeneric         Is the current TLD value (if defined) a known GENERIC
 *                                                          TLD? Generic TopLevelDomains are: 'com', 'edu', 'gov',
 *                                                          'int', 'mil', 'net', 'org' and also the associated
 *                                                          localized TopLevelDomains (xn--…).
 * @property-read boolean                $IsGeographic      Is the current TLD value (if defined) a GEOGRAPHIC TLD?
 *                                                          Geographic TLDs are: 'asia', 'berlin', 'london', etc.
 * @property-read boolean                $IsLocalized       Is the current TLD (if defined) a known LOCALIZED
 *                                                          UNICODE TLD? Localized TLDs are starting with 'xn--'.
 * @property-read boolean                $IsReserved        Is the current TLD or SLD value a known RESERVED name?
 *                                                          Reserved TopLevelDomains are 'arpa', 'test', 'example'
 *                                                          and 'tld'. Reserved SLDs are example.(com|net|org)
 * @property-read boolean                $HasKnownTLD       Returns if the TLD is a generally known, registered TLD.!
 * @property-read boolean                $IsLocal           Is the SLD (Hostname or TLD or both) representing a local
 *                                                          SLD(-Element)?
 * @property-read boolean                $HasDoubleTLD      Returns if the TLD is a known double TLD like co.uk.
 * @property-read boolean                $IsUrlShortener    Is the SLD pointing to a known public URL shortener
 *                                                          service? They are used to shorten or hide some long or
 *                                                          bad URLs.
 * @property-read boolean                $IsDynamic         Is the SLD pointing to a known public dynamic DNS service
 * @property-read boolean                $HasHostName       Returns if a host name is defined.
 */
final class SecondLevelDomain
{


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The host name element string.
    *
    * @var string
    */
   private $_hostName;

   /**
    * The TopLevelDomain element.
    *
    * @var \Beluga\Web\TopLevelDomain
    */
   private $_tld;

   /**
    * A array of states (Detail Information about the Domain)
    *
    * @var array
    */
   private $states;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = =">

   private static $UrlShorteners = [
      'bit.do', 't.co', 'lnkd.in', 'db.tt', 'qr.ae', 'adf.ly', 'goo.gl', 'bitly.com', 'cur.lv', 'tinyurl.com',
      'ow.ly', 'bit.ly', 'adcrun.ch', 'ity.im', 'q.gs', 'viralurl.com', 'is.gd', 'vur.me', 'bc.vc', 'twitthis.com',
      'u.to', 'j.mp', 'buzurl.com', 'cutt.us', 'u.bb', 'yourls.org', 'crisco.com', 'x.co', 'prettylinkpro.com',
      'viralurl.biz', 'adcraft.co', 'virl.ws', 'scrnch.me', 'filoops.info', 'vurl.bz', 'vzturl.com', 'lemde.fr',
      'qr.net', '1url.com', 'tweez.me', '7vd.cn', 'v.gd', 'dft.ba', 'aka.gr', 'tr.im', 'tinyarrows.com',
      'adflav.com', 'bee4.biz', 'cektkp.com', 'fun.ly', 'fzy.co', 'gog.li', 'golinks.co', 'hit.my', 'id.tl',
      'linkto.im', 'lnk.co', 'nov.io', 'p6l.org', 'picz.us', 'shortquik.com', 'su.pr', 'sk.gy', 'tota2.com',
      'xlinkz.info', 'xtu.me', 'yu2.it', 'zpag.es'
   ];

   // see: http://dnslookup.me/dynamic-dns/
   private static $DynDnsServices = '~^(.+\.wow64|(cable|optus|ddns|evangelion)\.nu|(45z|au2000|user32|darsite|darweb|dns2go|dnsmadeeasy|dnspark|dumb1|dyn(dns|dsl|serv|-access|nip)|thatip|tklapp|weedns|easydns|tzo|easydns4u|etowns|freelancedeveloper|hldns|powerdns|kyed|no-ip|ohflip|oray|servequake|usarmyreserve|wikababa|zerigo|zoneedit|zonomi)\.com|(dtdns|dynamic-dns|dynamic-site|dyns|dynserv|dynup|dyn-access|idleplay|minidns|sytes|tftpd|cjb|8866|xicp|planetdns|tzo)\.net|(afraid|3322|darktech|dhis|dhs|dynserv|dyn-access|irc-chat|planetdns|tzo)\.org|(dnsd|prout)\.be|dyn\.ee|dyn-access\.(de|info|biz)|dynam\.ac|dyn\.ro|my-ho\.st|(dyndns|lir|yaboo)\.dk|(dyns|metadns)\.cx|(homepc|myserver|ods|staticcling|yi|whyi|b0b|xname)\.org|widescreenhd\.tv|planetdns\.(biz|ca)|tzo\.cc)$~i';

   private static $LocalHosts     = '~^(local(host|domain)?)$~';

   private static $ReservedHosts  = '~^(example\.(com|net|org)|speedport\.ip)$~';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = =">

   private function __construct( $hostname, TopLevelDomain $tld = null )
   {

      $this->_hostName = $hostname;

      $this->_tld = $tld;

      $this->states = [
         'RESERVED'  => false,
         'LOCAL'     => false,
         'SHORTENER' => false,
         'DYNAMIC'   => false
      ];

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns the the SecondLevelDomain string value of this instance. If its defined as a fully qualified SLD
    * (it must ends with a dot) its returned with the trailing dot, otherwise without it.
    *
    * @return string
    */
   public function __toString()
   {

      if ( empty( $this->_hostName ) )
      {
         if ( \is_null( $this->_tld ) )
         {
            return '';
         }
         return (string) $this->_tld;
      }

      if ( \is_null( $this->_tld ) )
      {
         return $this->_hostName;
      }

      return $this->_hostName . '.' . $this->_tld;

   }

   /**
    * Returns always the fully qualified SLD. A fully qualified SLD always ends with a dot (like 'example.com.')
    *
    * @return string
    */
   public function toFullyQualifiedString()
   {
      if ( empty( $this->_hostName ) )
      {
         if ( \is_null( $this->_tld ) )
         {
            return '';
         }
         return $this->_tld->toFullyQualifiedString();
      }
      if ( \is_null( $this->_tld ) )
      {
         return $this->_hostName;
      }
      return $this->_hostName . '.' . $this->_tld->toFullyQualifiedString();
   }

   /**
    * Returns always the NOT fully qualified SLD, also if a fully qualified SLD is used.
    *
    * @return string
    */
   public function toString()
   {
      if ( empty( $this->_hostName ) )
      {
         if ( \is_null( $this->_tld ) )
         {
            return '';
         }
         return $this->_tld->toString();
      }
      if ( \is_null( $this->_tld ) )
      {
         return $this->_hostName;
      }
      return $this->_hostName . '.' . $this->_tld->toString();
   }

   /**
    * @inherit-doc
    *
    * @param string $name
    * @return mixed
    */
   public function __get( $name )
   {

      $upperName = \strtoupper( $name );

      switch ( $upperName )
      {

         case 'TLD';
            return $this->_tld;

         case 'HOSTNAME':
            return $this->_hostName;

         case 'ISFULLYQUALIFIED':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsFullyQualified;

         case 'HASTLD':
            if ( \is_null( $this->_tld ) ) { return false; }
            return ( $this->_tld instanceof TopLevelDomain );

         case 'HASHOSTNAME':
            return ! empty( $this->_hostName );

         case 'ISCOUNTRY':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsCountry;

         case 'ISGENERIC':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsGeneric;

         case 'ISGEOGRAPHIC':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsGeographic;

         case 'ISLOCALIZED':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsLocalized;

         case 'ISRESERVED':
            if ( $this->states[ 'RESERVED' ] ) { return true; }
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsReserved;

         case 'HASKNOWNTLD':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsKnown;

         case 'HASDOUBLETLD':
            if ( \is_null( $this->_tld ) ) { return false; }
            return $this->_tld->IsDouble;

         case 'ISLOCAL':
            return $this->states[ 'LOCAL' ];

         case 'ISURLSHORTENER':
            return $this->states[ 'SHORTENER' ];

         case 'ISDYNAMIC':
            return $this->states[ 'DYNAMIC' ];

         default:
            return false;

      }

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Parses the defined Second Level Domain string to a {@see \Beluga\Web\SecondLevelDomain} instance. On error it
    * returns FALSE.
    *
    * @param  string  $sld                The second level domain string, including the optional TLD.
    * @param  boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @return \Beluga\Web\SecondLevelDomain|bool Returns the resulting \Beluga\Web\SecondLevelDomain instance or FALSE.
    */
   public static function Parse( $sld, $allowOnlyKnownTlds = false )
   {

      if ( empty( $sld ) || ! \is_string( $sld ) || \is_numeric( $sld ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      $_sld = $sld;

      if ( false !== ( $_tld =TopLevelDomain::ParseExtract( $_sld, $allowOnlyKnownTlds ) ) )
      {
         $mySLD = new SecondLevelDomain( '', $_tld );
         $mySLD->states[ 'RESERVED' ]  = $_tld->IsReserved;
         $mySLD->states[ 'SHORTENER' ] = \in_array( \strtolower( $sld ), static::$UrlShorteners ) ;
      }
      else
      {
         if ( $allowOnlyKnownTlds && \Beluga\strContains( $sld, '.' ) )
         {
            return false;
         }
         if ( $allowOnlyKnownTlds && ! TopLevelDomain::EndsWithValidTldString( $sld ) )
         {
            return false;
         }
         $mySLD = new SecondLevelDomain( '' );
      }

      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_sld ) )
      {
         return false;
      }

      $mySLD->_hostName = $_sld;

      if ( \preg_match( static::$LocalHosts, $sld ) )
      {
         $mySLD->states[ 'LOCAL' ]     = true;
         $mySLD->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::$DynDnsServices, $sld ) )
      {
         $mySLD->states[ 'DYNAMIC' ]  = true;
      }
      if ( ! $mySLD->states[ 'RESERVED' ] && \preg_match( static::$ReservedHosts, $sld ) )
      {
         $mySLD->states[ 'RESERVED' ]  = true;
      }

      return $mySLD;

   }

   /**
    * Extracts a Second level domain definition from a full host definition like 'www.example.com' => 'example.com'.
    * The rest (Third level label (often called 'Sub domain name')) is returned by $host, if the method returns a
    * valid {@see \Beluga\Web\SecondLevelDomain} instance.
    *
    * @param  string  $host               The full host definition and it returns the resulting third level label
    *                                     (known as sub domain name) if the method returns a new instance
    * @param  boolean $allowOnlyKnownTlds Are only known main TLDs allowed to be a parsed as a TLD?
    * @return \Beluga\Web\SecondLevelDomain|bool Returns the resulting \Beluga\Web\SecondLevelDomain instance or FALSE.
    */
   public static function ParseExtract( &$host, $allowOnlyKnownTlds = false )
   {

      if ( empty( $host ) || ! \is_string( $host ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      $tmp1 = \explode( '.', $host );

      if ( \is_numeric( $tmp1[ \count( $tmp1 ) - 1 ] ) )
      {
         return false;
      }

      $_host = $host;

      if ( false !== ( $_tld = TopLevelDomain::ParseExtract( $_host, $allowOnlyKnownTlds ) ) )
      {
         $mySLD = new SecondLevelDomain( '', $_tld );
         $mySLD->states[ 'RESERVED' ]  = $_tld->IsReserved;
         $tmp = \explode( '.', $_host );
         $mySLD->states[ 'SHORTENER' ] = \in_array(
            \strtolower( $tmp[ \count( $tmp ) - 1 ] . '.' . $_tld ),
            static::$UrlShorteners ) ;
      }
      else
      {

         if ( $allowOnlyKnownTlds && \count( \explode( $host, '.' ) ) > 2 )
         {
            return false;
         }
         if ( $allowOnlyKnownTlds && ! TopLevelDomain::EndsWithValidTldString( $host ) )
         {
            return false;
         }
         $mySLD = new SecondLevelDomain( '' );
      }

      if ( ! \preg_match( '~^[a-z0-9_][a-z.0-9_-]+$~i', $_host ) )
      {
         return false;
      }

      $tmp = \explode( '.', $_host );
      if ( \count( $tmp ) >= 2 )
      {
         $_sld   = $tmp[ \count( $tmp ) - 1 ];
         $_thild = \join( '.', ArrayHelper::Extract( $tmp, 0, \count( $tmp ) - 1 ) );
      }
      else
      {
         $_sld   = $_host;
         $_thild = '';
      }

      $mySLD->_hostName = $_sld;

      $sld = $_sld . ( $mySLD->HasTLD ? ( '.' . $mySLD->_tld->toString() ) : '' );

      if ( \preg_match( static::$LocalHosts, $sld ) )
      {
         $mySLD->states[ 'LOCAL' ]     = true;
         $mySLD->states[ 'RESERVED' ]  = true;
      }
      else if ( \preg_match( static::$DynDnsServices, $sld ) )
      {
         $mySLD->states[ 'DYNAMIC' ]  = true;
      }
      if ( ! $mySLD->states[ 'RESERVED' ] && \preg_match( static::$ReservedHosts, $sld ) )
      {
         $mySLD->states[ 'RESERVED' ]  = true;
      }

      $host = $_thild;

      return $mySLD;

   }

   // </editor-fold>


}

