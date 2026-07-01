<?php
/**
* This file is created by Really Simple Security
*/

if (defined("SHORTINIT") && SHORTINIT) return;

$base_path = dirname(__FILE__);
if( file_exists( $base_path . "/rsssl-safe-mode.lock" ) ) {
    if ( ! defined( "RSSSL_SAFE_MODE" ) ) {
        define( "RSSSL_SAFE_MODE", true );
    }
    return;
}

if ( isset($_GET["rsssl_header_test"]) && (int) $_GET["rsssl_header_test"] ===  879450958 ) return;

if ( defined("RSSSL_HEADERS_ACTIVE" ) ) return;
define( "RSSSL_HEADERS_ACTIVE", true );
if ( file_exists( "/home/hxonxjashosting/domains/reviewking.info/wp-content/firewall.php" ) ) {
    require_once "/home/hxonxjashosting/domains/reviewking.info/wp-content/firewall.php";
}

//RULES START

if ( !headers_sent() ) {
if ( !function_exists("rsssl_is_ssl" ) ) {
  function rsssl_is_ssl() {
    if (    ( isset($_SERVER["HTTPS"]) && ("on" === $_SERVER["HTTPS"] || "1" === $_SERVER["HTTPS"]) )
    || (isset($_ENV["HTTPS"]) && ("on" === $_ENV["HTTPS"]))
    || (isset($_SERVER["SERVER_PORT"]) && ( "443" === $_SERVER["SERVER_PORT"] ) )
    || (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "1") !== false))
    || (isset($_SERVER["HTTP_X_FORWARDED_SSL"]) && (strpos($_SERVER["HTTP_X_FORWARDED_SSL"], "on") !== false))
    || (isset($_SERVER["HTTP_CF_VISITOR"]) && (strpos($_SERVER["HTTP_CF_VISITOR"], "https") !== false))
    || (isset($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_CLOUDFRONT_FORWARDED_PROTO"], "https") !== false))
    || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strpos($_SERVER["HTTP_X_FORWARDED_PROTO"], "https") !== false))
    || (isset($_SERVER["HTTP_X_PROTO"]) && (strpos($_SERVER["HTTP_X_PROTO"], "SSL") !== false))
    ) {
      return true;
    }
    return false;
  }
}
if ( rsssl_is_ssl() ) header("Strict-Transport-Security: max-age=63072000; includeSubDomains;preload");
header("X-XSS-Protection: 0");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-Frame-Options: SAMEORIGIN");

if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
} else {
    header('X-Powered-By: ');
}

header("Content-Security-Policy: frame-ancestors 'self' ; upgrade-insecure-requests;");

}
