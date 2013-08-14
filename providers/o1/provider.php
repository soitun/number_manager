<?php 

class providers_o1_provider extends providers_aprovider {
    private $_process; // SMS for iris communication, SLK for web api
    private $_ssl;

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

    function __construct($ssl = false, $process = 'SLK') {
        $this->_provider_name = 'o1';
        parent::__construct();

        $this->_ssl = $ssl;
        // Process Would be 'SLK' for basic API calls and 'SMS' for sms
        $this->_process = $process;
    }

    public function create($area_code) {
        $this->_obj_number = new models_number("o1");

        // The data sent to the server
        $post = array(
            'npa' => $area_code,
            'limit' => '500'
        );
        $response = $this->_send('json', '/Dids/search', $post);

        // Just making sure that the table exist
        $this->_obj_number->create_db('US_' . $area_code);

        if ($response != false) {
            foreach ($response->dids as $did) {
                // Location is something like 'Stockton, CA'
                $location_exp = explode(',', $did->did->location);
                $city = trim($location_exp[0]);
                $state = trim($location_exp[1]);
                $this->_obj_number->set_number('1' . $did->did->tn);
                $this->_obj_number->set_city($city);
                $this->_obj_number->set_state($state);
                $this->_obj_number->set_number_identifier($did->did->tn);
                $this->_obj_number->insert();
            }
        }
    }

    public function update() {
        return;
    }
}