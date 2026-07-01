<?php
/**
* This file is created by Really Simple Security to test the CSP header length
* It will not load during regular wordpress execution
*/


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
header("X-REALLY-SIMPLE-SSL-TEST: %1C%0E%1AU%A6%29.%40A%0D%FCF%BE%C0k%B1%98d%1F%11%06%DEl%5D%C9%E6GH%BC%7E%F9%F2%29L%A5%92%D4%1BP%B6%E30%A8%07%17%BA%10%B5%3D%19%5B%83%0F%E3Afl%D0%B2%E9%10%CE%00%0B%86%CA%A3%C8%E76%F0%C4%B0%97N%D1%2BF%AAu%DBV%99%EC%1A%B7%24%C3%A9%DD%06%ADb%E0%C0%C7%08XC%94%B9i%ED%1Bh%9A%06%CC%1DX%F9%BE%DB%D9%D8-%2F%CA%1A%F0%8E%14v%0B%C8x%FA7%13%03%D0F%3F%A1%CF%F3Pc%1A%BB%9C%A5.%04e%D7%08H%CC%89%86J%08I%87%8F%CEt5%D3%C1M%F5e%09%C9%1D%AC%C7%E9%09%D2%3EE%F9%95%E1%B6c%C7%C9%2FV%D2w%BD%06%D2%026%AD%C8%07%82%85%97%0BG%A");

 echo '<html><head><meta charset="UTF-8"><META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW"></head><body>Really Simple Security headers test page</body></html>';