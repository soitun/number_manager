<?php 

/**
 * O1 SDK (Still not sure if I can really called that a SDK)
 * @author Francis Genet
 * @package Number_manager_api
 */
class providers_o1_provider {
    private $_process;
    private $_ssl;
    private $_settings;

    function __construct($ssl = false, $process = 'SLK') {
        $general_settings = helper_settings::get_instance();
        $this->_settings = $general_settings->providers->{ENVIRONMENT}->o1;

        $this->_ssl = $ssl;
        $this->_process = $process;
    }

    private function _send($format, $uri, $post = array()) {
        $date = gmdate('r');

        if (count($post) > 0) {
            $method = 'POST';
        } else {
            $method = 'GET';
        }

        $uri = $uri . '/out/' . $format;
        $hmac = hash_hmac('sha1', $this->_createHashString($method, $uri, $date, $post), $this->_settings->auth_secret);
        $url = (($this->_ssl === true) ? 'https://' : 'http://') . $this->_settings->api_url . '/' . ltrim($uri, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Date: '.$date,
            'Authorization: ' . $this->_process . ' ' . $this->_settings->auth_key . ':' . base64_encode($hmac)
        ));

        if (count($post) > 0) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_http_build_query($post));
        }

        ob_start();
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();

        $info = curl_getinfo($ch);
        if ($info['http_code'] == 200) {
            if ($format == 'json')
                return json_decode($response);
            else 
                return (array)simplexml_load_string($response);
    
        } else
            return false;
    }

    private function _createHashString($method, $uri, $date, $post = array()) {
        if (count($post) == 0) {
            return $method . ' ' . $uri . "\n\n"
                   . 'Date:' . $date . "\n";
        } else {
            return $method . ' ' . $uri. "\n\n"
                   . 'Date:' . $date . "\n"
                   . $this->_http_build_query($post);
        }
    }

    private function _http_build_query($post) {
        $newPost = array();
        foreach ($post as $key => $val) {
            $newPost[] = $key . '=' . rawurlencode($val);
        }
        return implode('&', $newPost);
    }

    public function check_status($number, $country) {
        
    }

    public function order($request_data, $identifier) {
        $identifier = array("4152341247");
        foreach ($this->_settings->profiles as $profile) {
            $post = array(
                'sip_profile' => $profile,  // refer to /Dids/getprofiles to get this sip_profile
                'dids' => $identifier
            );

            print_r($post);

            $response = $this->_send('json', '/Dids/activate', $post);

            if (!empty($response)) {
                return $response;
            }
        }
    }
}
