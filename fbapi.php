<?php
namespace namespaceNameHere;

/**
 * PHP version of defaultnamehere/zzzzz
 */

class fbapi
{
    public static function get_user_name($fbid): string
    {
        $resp = self::getFinalUrl("https://www.facebook.com/app_scoped_user_id/$fbid")
        return explode('/', $resp)[-1];
    }


    /**
     * Following code from http://w-shadow.com/blog/2008/07/05/how-to-get-redirect-url-in-php/
     * via http://stackoverflow.com/questions/3799134/how-to-get-final-url-after-following-http-redirections-in-pure-php
     */

    /**
     * getRedirectUrl()
     * Gets the address that the provided URL redirects to,
     * or FALSE if there's no redirect. 
     *
     * @param string $url
     * @return string
     */
    public static function getRedirectUrl($url)
    {
        $redirect_url = null; 

        $url_parts = @parse_url($url);
        if (!$url_parts) {
            return false;
        }
        if (!isset($url_parts['host'])) {
            return false; //can't process relative URLs
        }
        if (!isset($url_parts['path'])) {
            $url_parts['path'] = '/';
        }

        $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
        if (!$sock) {
            return false;
        }

        $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n"; 
        $request .= 'Host: ' . $url_parts['host'] . "\r\n"; 
        $request .= "Connection: Close\r\n\r\n"; 
        fwrite($sock, $request);
        $response = '';

        while(!feof($sock)) {
            $response .= fread($sock, 8192);
        }

        fclose($sock);

        if (preg_match('/^Location: (.+?)$/m', $response, $matches)) {
            if (substr($matches[1], 0, 1) == "/") {
                return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
            } else {
                return trim($matches[1]);
            }
        } else {
            return false;
        }
    }

    /**
     * getAllRedirects()
     * Follows and collects all redirects, in order, for the given URL. 
     *
     * @param string $url
     * @return array
     */
    public static function getAllRedirects($url)
    {
        $redirects = array();
        while ($newurl = self::getRedirectUrl($url)) {
            if (in_array($newurl, $redirects)) {
                break;
            }
            $redirects[] = $newurl;
            $url = $newurl;
        }
        return $redirects;
    }

    /**
     * getFinalUrl()
     * Gets the address that the URL ultimately leads to. 
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     * @return string
     */
    public static function getFinalUrl($url) {
        $redirects = self::getAllRedirects($url);
        if (count($redirects)>0) {
            return array_pop($redirects);
        } else {
            return $url;
        }
    }
}

