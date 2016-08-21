<?php
/**
 * This file defines the {@see \Beluga\Web\TopLevelDomain} class.
 *
 * @author         SagittariusX <unikado+sag@gmail.com>
 * @copyright  (c) 2016, SagittariusX
 * @package        Beluga\Web
 * @since          2016-08-20
 * @version        0.1.0
 */


namespace Beluga\Web;


/**
 * This class defines a TopLevelDomain part of a host name.
 *
 * <code>
 * third-level-domain-label  .  second-level-domain-label  .  top-level-domain-label  .  root-label
 * www                       .  example                    .  com
 * </code>
 *
 * The TLD from example above is <b>com</b> or <b>com.</b> (last is fully qualified)
 *
 * For some validation reasons the class can get &amp; store some testing states, informing about TopLevelDomain details.
 *
 * All this states can be accessed via dynamic readonly properties.
 *
 * @since         v0.1.0
 * @property-read boolean $IsFullyQualified Is the TLD defined in full qualified manner? It means, is the root-label
 *                                          (always a empty string separated by a dot) defined? e.g.: 'com.'
 * @property-read boolean $IsCountry        Is the current TopLevelDomain value a known COUNTRY TopLevelDomain?
 *                                          Country TopLevelDomains are: 'cz', 'de', 'en', 'nl', etc.
 *                                          and also localized country TopLevelDomains (xn--…).
 * @property-read boolean $IsGeneric        Is the current TopLevelDomain value a known GENERIC TopLevelDomain?
 *                                          Generic TopLevelDomains are: 'com', 'edu', 'gov', 'int', 'mil', 'net',
 *                                          'org' and also the associated localized TopLevelDomains (xn--…).
 * @property-read boolean $IsGeographic     Is the current TopLevelDomain value a known GEOGRAPHIC TopLevelDomain?
 *                                          Geographic TopLevelDomains are: 'asia', 'berlin', 'london', etc.
 * @property-read boolean $IsLocalized      Is the current TopLevelDomain value a known LOCALIZED UNICODE TopLevelDomain?
 *                                          Localized TopLevelDomains are already starting with 'xn--'.
 * @property-read boolean $IsReserved       Is the current TopLevelDomain value a known RESERVED TopLevelDomain?
 *                                          Reserved TopLevelDomains are: 'arpa', 'test', 'example' and 'tld'
 * @property-read boolean $IsKnown          Returns if the TLD is a generally known, registered TLD.
 * @property-read boolean $IsDouble         Id the TLD a double TLD like co.uk?
 */
final class TopLevelDomain
{


   // <editor-fold desc="// = = = =   P R I V A T E   S T A T I C   F I E L D S   = = = = = = = = = = = = = = = = = =">

   private static $KNOWN_FORMAT     = 'xn--[a-z\d]{3,24}|[a-z]{2,12}|wow64';

   private static $KNOWN_GENERIC    = 'com|edu|gov|int|mil|net|org';

   private static $KNOWN_RESERVED   = 'arpa|example|test|tld';

   private static $KNOWN_COUNTRY    = "a[cdefgilmnoqrstuwxz]|b[abmnorstvwyzd-j]|c[acdrf-ik-ou-z]|d[ejkmoz]|e[cegrstu]|f[ijkmor]|g[abdefghilmnpqrstuwy]|h[kmnrtu]|i[delmnoqrst]|j[emop]|k[eghimnqrwz]|l[abcikrstuvy]|m[acdeghk-z]|n[acefgilopruz]|om|p[aefghklmnrstwy]|qa|r[eosuw]|s[xyza-eg-or-v]|t[cdfghrstvwzj-p]|u[agksyz]|v[aceginu]|w[fs]|y[etu]|z[amrw]";

   private static $KNOWN_LC_COUNTRY = "xn--(3e0b707e|45brj9c|54b7fta0cc|80ao21a|90a(is|3ac)|clchc0ea0b2g2a9gcd|d1alf|fiq(s8|z9)s|fpcrj9c3d|fzc2c9e2c|gecrj9c|h2brj9c|j1amh|j6w193g|kpr(w13d|y57d)|l1acc|lgbbat1ad8j|mgb(2ddes|9awbf|a3a4f16a|aam7a8h|ai9azgqp6j|ayh7gpa|bh1a71e|c0a9azcg|erp4a5d4ar|pl2fh|tx2b|x4cd0ab|xkc2al3hye2a)|node|o3cw4h|ogbpf8fl|p1ai|pgbs0dh|s9brj9c|wgb(h1c|l6a)|xkc2dl3a5ee0h|yfro4i67o|ygbi2ammx|y9a3aq)";

   private static $KNOWN_LC_GENERIC = 'xn--(3ds443g|55qx5d|6frz82g|6qq986b3xl|80asehdb|80aswg|c1avg|czr694b|czru2d|d1acj3b|fiq228c5hs|i1b6b1a6a2e|io0a7i|ngbc5azd|nqv7f|mgbab2bd|q9jyb4c|rhqv96g|ses554g)';

   private static $KNOWN_GEOGRAPHIC = 'asia|bayern|berlin|brussels|budapest|bzh|cat|cologne|cymru|hamburg|kiwi|koeln|london|moscow|nagoya|nyc|okinawa|paris|ruhr|saarland|tirol|tokyo|vegas|vlaanderen|wales|wien|yokohama|москва|xn--80adxhks';

   private static $DOUBLE_TLDS      = '((co|or)\.at|(com|nom|org)\.es|(ac|co|gov|ltd|me|net|nic|nhs|org|plc|sch)\.uk|(biz|com|info|net|org)\.pl|(com|net|org)\.vc|(com|org)\.au|(com|tv|net)\.br)';

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   F I E L D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * The TopLevelDomain value string.
    *
    * @var string
    */
   private $value;

   /**
    * All state info about current TopLevelDomain.
    *
    * @var array
    */
   private $states;

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Init a new instance.
    *
    * @param  string  $tldValue The TopLevelDomain string value, to associate the instance with.
    */
   private function __construct( $tldValue )
   {

      $this->value = $tldValue;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Returns the the TopLevelDomain string value of this instance. If its defined as a fully qualified TLD
    * (it must ends with a dot) its returned with the trailing dot, otherwise without it.
    *
    * @return string
    */
   public function __toString()
   {

      // If value is null, or not a string, return a empty string ''. otherwise the value is returned.
      return $this->value . ( $this->getFullyQualifiedState() ? '.' : '' );

   }

   /**
    * Returns always the fully qualified TLD. A fully qualified TLD always ends with a dot (like 'com.')
    *
    * @return string
    */
   public function toFullyQualifiedString()
   {

      return $this->value . '.';

   }

   /**
    * Returns always the NOT fully qualified TLD, also if a fully qualified TLD is used.
    *
    * @return string
    */
   public function toString()
   {

      return $this->value;

   }

   /**
    * Is the current TopLevelDomain value a known GENERIC TopLevelDomain? Generic TopLevelDomains are:
    * 'com', 'edu', 'gov', 'int', 'mil', 'net', 'org'. Includes also some localized generic TLDs.
    *
    * @return boolean
    */
   public function getGenericState()
   {

      return $this->states[ 'GENERIC' ];

   }

   /**
    * Is the current TopLevelDomain value a known RESERVED TopLevelDomain? Reserved TLDs are: 'arpa' and 'tld'
    *
    * @return boolean
    */
   public function getReservedState()
   {

      return $this->states[ 'RESERVED' ];

   }

   /**
    * Is the current TopLevelDomain value a known COUNTRY TopLevelDomain? Country TLDs are: 'cz', 'de', 'en', etc.
    * Includes also some localized country TLDs.
    *
    * @return boolean
    */
   public function getCountryState()
   {

      return $this->states[ 'COUNTRY' ];

   }

   /**
    * Is the current TopLevelDomain value a known GEOGRAPHIC TopLevelDomain? Geographic TopLevelDomains are:
    * 'asia', 'berlin', 'london', etc.
    *
    * @return boolean
    */
   public function getGeographicState()
   {

      return $this->states[ 'GEOGRAPHIC' ];

   }

   /**
    * Is the current TopLevelDomain value a known LOCALIZED UNICODE TopLevelDomain? Localized TLDs are already
    * starting with 'xn--'.
    *
    * It can be combined with Generic or a Country TLD.
    *
    * @return boolean
    */
   public function getLocalizedState()
   {

      return $this->states[ 'LOCALIZED' ];

   }

   /**
    * Is the current TopLevelDomain value a TLD defined in full qualified manner? It means, is the root-label
    * (always a empty string separated by a dot) defined? e.g.: 'com.'.
    *
    * @return boolean
    */
   public function getFullyQualifiedState()
   {

      return $this->states[ 'FULLYQUALIFIED' ];

   }

   /**
    * Is the current TopLevelDomain value a public known, registered TLD?
    *
    * @return boolean
    */
   public function getKnownState()
   {

      return $this->states[ 'KNOWN' ];

   }

   /**
    * Returns the dynamic property values, described by {@see \Beluga\Web\TopLevelDomain} documentation.
    *
    * @param  string $name The name of the required dynamic property value.
    * @return boolean
    */
   public function __get( $name )
   {

      switch ( \strtolower( $name ) )
      {

         case 'fullqualified':
         case 'fullyqualified':
         case 'isfullyqualified':
            return $this->states[ 'FULLYQUALIFIED' ];

         case 'country':
         case 'iscountry':
            return $this->states[ 'COUNTRY' ];

         case 'generic':
         case 'isgeneric':
            return $this->states[ 'GENERIC' ];

         case 'geographic':
         case 'isgeographic':
            return $this->states[ 'GEOGRAPHIC' ];

         case 'localized':
         case 'islocalized':
            return $this->states[ 'LOCALIZED' ];

         case 'reserved':
         case 'isreserved':
            return $this->states[ 'RESERVED' ];

         case 'known':
         case 'isknown':
            return $this->states[ 'KNOWN' ];

         case 'double':
         case 'isdouble':
            return $this->states[ 'DOUBLE' ];

      }

      return false;

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P U B L I C   S T A T I C   M E T H O D S   = = = = = = = = = = = = = = = = = =">

   /**
    * Parses the defined TLD string to a {@see \Beluga\Web\TopLevelDomain} instance. On error it returns FALSE.
    *
    * @param  string  $tld            The TLD to parse.
    * @param  boolean $allowOnlyKnown Are only known main TLDs allowed to be a parsed as a TLD?
    * @return \Beluga\Web\TopLevelDomain|bool  Returns the resulting \Beluga\Web\TopLevelDomain instance or FALSE.
    */
   public static function Parse( $tld, $allowOnlyKnown = false )
   {

      if ( empty( $tld ) || ! \is_string( $tld ) )
      {
         // NULL values or none string or empty values will always return FALSE
         return false;
      }

      // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
      $regex = '~^('
             . static::$DOUBLE_TLDS
             . '|'
             . static::$KNOWN_FORMAT
             . ')\.?$~i';

      if ( $allowOnlyKnown )
      {
         // init the extended regexp, if only known TLDs are accepted
         $regex = '~^('
                . static::$DOUBLE_TLDS
                . '|'
                . static::$KNOWN_GENERIC
                . '|'
                . static::$KNOWN_COUNTRY
                . '|'
                . static::$KNOWN_GEOGRAPHIC
                . '|'
                . static::$KNOWN_LC_COUNTRY
                . '|'
                . static::$KNOWN_LC_GENERIC
                . '|'
                . static::$KNOWN_RESERVED
                . ')\.?$~i';
      }

      if ( ! \preg_match( $regex, $tld ) )
      {
         // $tld have no valid TLD defined
         return false;
      }

      // Init the TLD instance with extracted TLD value
      $tldInstance         = new TopLevelDomain( $tld );
      // Init the states
      $tldInstance->states = static::initStates();

      // Find the corresponding states and return the TLD instance
      return static::parseForStates( $tldInstance );

   }

   /**
    * Extracts the TopLevelDomain from defined host name string.
    *
    * @param  string  $hostString     The Host name string value reference to parse. After parsing, a maybe
    *                                 defined TopLevelDomain is removed from this variable.
    * @param  boolean $allowOnlyKnown Are only known main TLDs allowed to be a parsed as a TLD?
    * @return \Beluga\Web\TopLevelDomain|bool Returns the resulting \Beluga\Web\TopLevelDomain instance or FALSE.
    */
   public static function ParseExtract( &$hostString, $allowOnlyKnown = false )
   {

      if ( empty( $hostString ) || ! \is_string( $hostString ) )
      {
         // NULL values or none string values will always return FALSE
         return false;
      }

      // This is the default regexp, used if its not required to be a known TLD, only a valid format is required
      $regex   = '~^(.+?)\.(((' . static::$DOUBLE_TLDS . ')|' . static::$KNOWN_FORMAT . ')\.?)$~i';

      if ( $allowOnlyKnown )
      {
         // init the extended regexp, if only known TLDs are accepted
         $regex = '~^(.+)\.(('
                . static::$DOUBLE_TLDS
                . '|'
                . static::$KNOWN_GENERIC
                . '|'
                . static::$KNOWN_COUNTRY
                . '|'
                . static::$KNOWN_GEOGRAPHIC
                . '|'
                . static::$KNOWN_LC_COUNTRY
                . '|'
                . static::$KNOWN_LC_GENERIC
                . '|'
                . static::$KNOWN_RESERVED
                . ')\.?)$~i';
      }

      $matches = null;

      if ( ! \preg_match( $regex, $hostString, $matches ) )
      {
         // $hostString have no valid TLD defined
         return false;
      }

      // Reassign the host string without the TLD
      $hostString = $matches[ 1 ];

      // Init the TLD instance with extracted TLD value
      $tldInstance         = new TopLevelDomain( $matches[ 2 ] );
      // Init the states
      $tldInstance->states = static::initStates();

      // Find the corresponding states and return the TLD instance
      return static::parseForStates( $tldInstance );

   }

   /**
    * Returns if the defined string ends with a substring, defined by characters, usable as a TLD.
    *
    * @param  string $stringToCheck
    * @return boolean
    */
   public static function EndsWithValidTldString( $stringToCheck ) : bool
   {

      return (bool) \preg_match(
         '~\.(' . static::$DOUBLE_TLDS . '|' . static::$KNOWN_FORMAT . ')\.?$~i',
         $stringToCheck
      );

   }

   // </editor-fold>


   // <editor-fold desc="// = = = =   P R I V A T E   M E T H O D S   = = = = = = = = = = = = = = = = = = = = = = = =">

   private static function initStates()
   {

      // Init the states with the default values
      return [
         'DOUBLE'         => false,
         'GENERIC'        => false,
         'RESERVED'       => false,
         'COUNTRY'        => false,
         'GEOGRAPHIC'     => false,
         'LOCALIZED'      => false,
         'FULLYQUALIFIED' => false,
         'KNOWN'          => false
      ];

   }

   private static function parseForStates( TopLevelDomain $tld )
   {

      if ( \Beluga\strEndsWith( $tld->value, '.' ) )
      {
         // Its a full qualified TLD, ending with a dot, remember it…
         $tld->states[ 'FULLYQUALIFIED' ] = true;
         // remove the trailing dot
         $tld->value = \substr( $tld->value, 0, -1 );
      }

      // Next we check if the TopLevelDomain is a known generic TopLevelDomain
      if ( \preg_match( '~^(' . static::$DOUBLE_TLDS . ')$~i', $tld->value ) )
      {
         $tld->states[ 'DOUBLE' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Next we check if the TopLevelDomain is a known generic TopLevelDomain
      if ( ! $tld->states[ 'DOUBLE' ] && \preg_match( '~^(' . static::$KNOWN_GENERIC . ')$~i', $tld->value ) )
      {
         $tld->states[ 'GENERIC' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Next we check if the TopLevelDomain is a known RESERVED TopLevelDomain
      if ( ! $tld->states[ 'DOUBLE' ] && ! $tld->states[ 'GENERIC' ]
          && \preg_match( '~^(' . static::$KNOWN_RESERVED . ')$~i', $tld->value ) )
      {
         $tld->states[ 'RESERVED' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Now we check if the TopLevelDomain is a known GEOGRAPHIC TopLevelDomain
      if ( ! $tld->states[ 'DOUBLE' ] && ! $tld->states[ 'GENERIC' ] && ! $tld->states[ 'RESERVED' ]
        && \preg_match( '~^(' . static::$KNOWN_GEOGRAPHIC . ')$~i', $tld->value ) )
      {
         $tld->states[ 'GEOGRAPHIC' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Now we check if the TopLevelDomain is a known LOCALIZED GENERIC TopLevelDomain
      if ( ! $tld->states[ 'DOUBLE' ] && ! $tld->states[ 'GENERIC' ] && ! $tld->states[ 'RESERVED' ]
        && ! $tld->states[ 'GEOGRAPHIC' ] && \preg_match( '~^(' . static::$KNOWN_LC_GENERIC . ')$~i', $tld->value ) )
      {
         $tld->states[ 'GENERIC' ]   = true;
         $tld->states[ 'LOCALIZED' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Now we check if the TopLevelDomain is a known LOCALIZED COUNTRY TopLevelDomain
      if ( ! $tld->states[ 'DOUBLE' ] && ! $tld->states[ 'GENERIC' ] && ! $tld->states[ 'RESERVED' ]
        && ! $tld->states[ 'GEOGRAPHIC' ] && ! $tld->states[ 'LOCALIZED' ]
        && \preg_match( '~^(' . static::$KNOWN_LC_COUNTRY . ')$~i', $tld->value ) )
      {
         $tld->states[ 'COUNTRY' ]   = true;
         $tld->states[ 'LOCALIZED' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      // Next we check if the TopLevelDomain is a known COUNTRY TopLevelDomain
      if ( \preg_match( '~^(' . static::$KNOWN_COUNTRY . ')$~i', $tld->value ) )
      {
         $tld->states[ 'COUNTRY' ] = true;
         $tld->states[ 'KNOWN' ] = true;
      }

      return $tld;

   }

   // </editor-fold>


}

