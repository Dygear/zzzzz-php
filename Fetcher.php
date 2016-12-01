<?php
namespace namespaceNameHere;

/**
 * PHP version of defaultnamehere/zzzzz
 */

/**
 * This is just a PHP version of defaultnamehere's zzzzz facebook 'fetcher' app.
 * It pulls inforamtion from the /pull URL and tells you information about your friends online time.
 * It also have information including when they talk to you. That information is sent here via this path as well.
 */
class Fetcher
{
    /**
     * @var private $time float - NEGATIVE startup time for this class.
     */
    private $time;

    /**
     * @var REQUEST_SCHEME string - Can only be https.
     */
    const REQUEST_SCHEME = 'https';

    /**
     * @var REQUEST_AUTHORITY string - The URL that we are going to pull the information from.
     */
    const REQUEST_AUTHORITY = '6-edge-chat.facebook.com';

    /**
     * @var REQUEST_METHOD string - The HTTP method that we are going to use for these requests.
     */
    const REQUEST_METHOD = 'GET';

    /**
     * @var REQUEST_PATH string - The path within the server to get the information we need.
     */
    const REQUEST_PATH = '/pull';

    /**
     * @var REQUEST_HEADERS array - An array of headers to send with every request.
     */
    const REQUEST_HEADERS = [
        'accept' => '*/*',
        'accept-encoding' => 'gzip, deflate',
        'accept-language' => 'en-US,en;q=0.8',
        'cookie' => null,
        'origin' => 'https://www.facebook.com',
        'referer' => 'https://www.facebook.com/',
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36'
    ];

    /**
     * @var REQUEST_QUERY_STRING array - An array that's built into the request URL.
     */
    const REQUEST_QUERY_STRING = [
        'cap' => '8',
        'cb' => 'cfij',
        'channel' => null,
        'clientid' => null,
        'format' => 'json',
        'idle' => '0',
        'isq' => '145653',
        'msgs_recv' => '0',
        'partition' => '-2',
        'qp' => 'y',
        'seq' => '0',
        'state' => 'active',
        'sticky_pool' => 'atn2c06_chat-proxy',
        'sticky_token' => '0',
        'uid' => null,
        'viewer_uid' => null
    ];

    /**
     * @var JSON_PAYLOAD_PREFIX string - Facebook puts this in front of all their JSON to prevent hijacking.
     */
    const JSON_PAYLOAD_PREFIX = "for (;;); ";

    /**
     * @var OFFLINE_STATUS_JSON string -
     */
    const OFFLINE_STATUS_JSON = '{"lat": "offline", "webStatus": "invisible", "fbAppStatus": "invisible", "otherStatus": "invisible", "status": "invisible", "messengerStatus": "invisible"}';

    /**
     * @var ACTIVE_STATUS_JSON string - 
     */
    const ACTIVE_STATUS_JSON = '{"lat": "online", "webStatus": "invisible", "fbAppStatus": "invisible", "otherStatus": "invisible", "status": "active", "messengerStatus": "invisible"}';

    /**
     * @var SLEEP_TIME int - Ammount of time to sleep in seconds between requests.
     */
    const SLEEP_TIME = 1;

    /**
     * @var $secretsFilePath string - Contains the path to the SECRETS.txt file.
     */
    private $secretsFilePath = '';

    /**
     * PHP Constructor for the Fetcher Class
     * 
     * @param string $secretsFilePath Path to your facebook cookie.
     * @return Fetcher Instance
     */
    public function __construct(string $secretsFilePath = 'SECRETS.txt')
    {
        # Start Time
        $this->time = -microtime(true);

        # Set Error Handler to inside this class.
        set_error_handler([$this, 'onError'], E_ALL);
        # Set Exception Handler to inside this class.
        set_exception_handler([$this, 'onException']);

        # If the file doesn't exist, don't do anything.
        if (!file_exists($secretsFilePath)) {
            throw new \Exception('You must provide the SECRETS.txt file location with your facebook cookie.', 1);
            return;
        }

        $this->secretsFilePath = $secretsFilePath;

        # Make sure there is a data log dir for us to save files to.
        if (!file_exists(Graph::LOG_DATA_DIR)) {
            mkdir(Graph::LOG_DATA_DIR);
        }

        # Make sure there is a data log dir for us to save files to.
        if (!file_exists('php')) {
            mkdir('php');
        }

        # Make sure there is a data log dir for us to save files to.
        if (!file_exists('raw')) {
            mkdir('raw');
        }

        $this->init();

        $this->loop();
    }

    /**
     * initializes all of the values.
     */
    private function init()
    {
        // Build all of the information we need to start talking to the server.
        $this->REQUEST_URL = self::REQUEST_SCHEME . '://' . self::REQUEST_AUTHORITY . self::REQUEST_PATH;
        $this->REQUEST_QUERY_STRING = self::REQUEST_QUERY_STRING;
        $this->REQUEST_HEADERS = self::REQUEST_HEADERS;

        # Read the secrets file into our cookie information.
        foreach (file($this->secretsFilePath) as $line) {
            list($key, $val) = explode('=', trim($line), 2);

            // Insert the cookie into the headers array.
            if ($key == 'cookie') {
                $this->REQUEST_HEADERS[$key] = $val;
            }

            // Set our uid
            if ($key == 'uid') {
                $this->uid = $val;
            }

            // Set our client_id
            if ($key == 'client_id') {
                $this->clientid = $val;
            }
        }

        // Finishes our query string.
        $this->REQUEST_QUERY_STRING['channel'] = 'p_' . $this->uid;
        $this->REQUEST_QUERY_STRING['clientid'] = $this->clientid;
        $this->REQUEST_QUERY_STRING['uid'] = $this->uid;
        $this->REQUEST_QUERY_STRING['viewer_uid'] = $this->uid;

        // Set the first values for our interations.
        $this->REQUEST_QUERY_STRING['seq'] = 0; # Simple sequency numbers, just ++ on each interation.
        $this->REQUEST_QUERY_STRING['msgs_recv'] = 0; # The number of messages we've gotten so far. Seems to be in sync with seq.

        // Build the REQUEST HEADERS
        $HEADERS = [];
        foreach ($this->REQUEST_HEADERS as $key => $val) {
            $HEADERS[] = "$key: $val";
        }
        $this->REQUEST_HEADERS = implode("\r\n", $HEADERS);
    }

    private function loop()
    {
        do {
            try {
                $resp = $this->start_request();
                file_put_contents('php/' . time() . '.log', print_r($resp, true));
                sleep(self::SLEEP_TIME);
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        } while (true);
    }

    private function start_request()
    {
        echo '>';
        $resp = $this->make_request();
        if ($resp === null) {
            print("Got error from request, restarting...");
            $this->init();
            return;
        }

        # We got info about which pool/sticky we should be using I think??? Something to do with load balancers?
        if (isset($resp['lb_info'])) {
            $this->REQUEST_QUERY_STRING['sticky_pool'] = $resp['lb_info']['pool'];
            $this->REQUEST_QUERY_STRING['sticky_token'] = $resp['lb_info']['sticky'];
        }

        if (isset($resp['seq'])) {
            $this->REQUEST_QUERY_STRING['seq'] = $resp['seq'];
            $this->REQUEST_QUERY_STRING['msgs_recv'] = $resp['seq'];
        }

        if (isset($resp['ms'])) {
            foreach ($resp['ms'] as $item) {
                # The online/offline info we're looking for.
                if ($item['type'] == 'buddylist_overlay') {
                    # Find the key with all the message details, that one is the UID.
                    foreach ($item['overlay'] as $uid => $val) {
                        if (is_array($val)) {
                            # Log the LAT in this message.
                            $this->_log_lat($uid, $val['la']);

                            # Now log their current status.
                            if (isset($item['overlay'][$uid]['p'])) {
                                file_put_contents("log/{$uid}.txt", time() . '|' . json_encode($item['overlay'][$uid]['p']) . PHP_EOL);
                            }
                        }
                    }
                }
                # This list contains the last active times (lats) of users.
                if (isset($item['buddyList'])) {
                    foreach ($item['buddyList'] as $uid => $buddy) {
                        if (isset($buddy['lat'])) {
                            $this->_log_lat($uid, $buddy['lat']);
                        }
                    }
                }
            }
        }
        echo PHP_EOL . PHP_EOL;
        return $resp;
    }

    private function make_request()
    {
        $raw_response = file_get_contents(
            $this->REQUEST_URL . '?' . http_build_query($this->REQUEST_QUERY_STRING),
            false,
            stream_context_create([
                'http'=> [
                    'method' => self::REQUEST_METHOD,
                    'header' => $this->REQUEST_HEADERS,
                    'timeout' => 60.0,
                ]
            ])
        );

        // Yes, the @ is a hack.
        $response = @gzdecode($raw_response);
        $response = ($response === false) ? $raw_response : $response;

        // Log the raw output.
        file_put_contents('raw/' . time() . '.json', $response);

        try {
            if ($response === null) {
                return null;
            }
            if (substr($response, 0, strlen(self::JSON_PAYLOAD_PREFIX)) == self::JSON_PAYLOAD_PREFIX) {
                $data = substr($response, strlen(self::JSON_PAYLOAD_PREFIX));
                $data = json_decode($data, true);
            } else {
                # If it didn't start with the prefix then something weird is happening.
                # Maybe it's unprotected JSON?
                $data = json_decode($response, true);
            }
        } catch (Expection $e) {
            echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
            return null;
        }

        echo "Response:" . $response;
        return $data;
    }

    private function _log_lat($uid, $lat_time) {
        if (!isset($this->excludes[$uid])) {
            # Now add an online status at the user's LAT.
            $line = $lat_time . '|' . self::ACTIVE_STATUS_JSON . PHP_EOL;
            # Assume the user is currently offline, since we got a lat for them. (This is guaranteed I think.)
            $line .= time() . '|' . self::OFFLINE_STATUS_JSON . PHP_EOL;
            file_put_contents("log/{$uid}.txt", $line, FILE_APPEND);
        }
    }

    public function onError(int $errno , string $errstr, string $errfile = '', int $errline = 0, array $errcontext = []): bool
    {
        echo sprintf(
            '[%12.6f] %s: %d in %s on %d with "%s".' . PHP_EOL,
            $this->time + microtime(true), date('Y-m-d H:i:s'), $errno, $errfile, $errline, $errstr
        );
        return false;
    }

    public function onException(Throwable $ex)
    {
        echo sprintf(
            '[%12.6f] %s: %d in %s on %d with "%s".' . PHP_EOL . 'Backtrace: ' . PHP_EOL . '%s' . PHP_EOL,
            $this->time + microtime(true), date('Y-m-d H:i:s'), $ex->getCode(), $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()
        );
    }

}
