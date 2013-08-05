<?php 

class providers_bandwidthold_provider extends providers_aprovider {
    private $_curl;
    private $_ckfile;
    private $_viewstate;
    private $_zerocheck_obj;

    function __construct() {
        $this->_provider_name = 'bandwidthold';
        parent::__construct();

        // Loading the zerocheck file
        $this->_zerocheck_obj = new helper_settings('assets/' . $this->_provider_name . '/zerocheck.json');

    }

    private function _make_request($url, $verb = "GET", $form_data = "") {
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_ckfile); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->_ckfile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, 155, 900000);  // wait up to 15 minutes for a page to respond
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        if ($verb == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($this->_viewstate && $form_data)
                $form_data = $form_data . "&__VIEWSTATE=" . urlencode($this->_viewstate);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $form_data); 
        }
        curl_setopt($ch, CURLOPT_REFERER, $url);  // Lame hack - pretend we're always coming from the same page.

        $result = curl_exec($ch);

        // If VIEWSTATE is present, always update it and keep it for the next request
        if (preg_match('/VIEWSTATE" value="(.*)"/', $result, $matches))
            $this->_viewstate = $matches[1];

        return $result;
    }

    private function _log_and_setup() {
        $zerocheck = $this->_zerocheck_obj->get_settings();

        /* STEP 1. let’s create a cookie file */
        $this->_ckfile = tempnam('/tmp', 'CURLCOOKIE');

        /* STEP 2. let's login to the bandwidth.com site */
        echo "Logging into bandwidth.com portal...\n";
        $this->_make_request('https://my.bandwidth.com/portal/Login.aspx', 'POST', 'txtEmailAddress=' . urlencode($this->_settings->login) . '&txtPassword=' . urlencode($this->_settings->password) . '&cookieexists=false&__EVENTTARGET=bbLogin%24button');

        /* STEP 3. Initialize bandwidth.com's server-side "session state" by loading the number listing page once */
        echo "Setting up server-side session (know as __VIEWSTATE) on bandwidth.com servers...\n";
        $this->_make_request('https://my.bandwidth.com/portal/Members/NumberManagement/ListRateCenter.aspx');
    }

    private function _download_rate_centers() {
        /* STEP 4. Download each rate center and parse unique Rate Center IDs */
        foreach ($this->_constants->states as $regionID => $state) {
            $id_list = array();

            echo "Now downloading rate centers for $state... ($regionID)\n";
            $fp = fopen (ASSETS_PATH . $this->_provider_name . '/' . $this->_settings->rc_filename . '-' . $state, 'w+');
            $result = $this->_make_request('https://my.bandwidth.com/portal/Members/NumberManagement/ListRateCenter.aspx', 'POST', "__EVENTTARGET=rateCenterSearchControl%24cmdSearch%24button&rateCenterSearchControl%24ddlSearchType=Regions&rateCenterSearchControl%24tbwe_ClientState=&rateCenterSearchControl%24countryRegionControl%24ddlCountry=95fda5f2-fbfa-40a5-95e7-671e51e9d1b4&rateCenterSearchControl%24countryRegionControl%24ddlRegion=$regionID");

            $lines = explode("\n", $result);
            foreach ($lines as $line) {
                if (preg_match('/ListNumbers.aspx\?RateCenterID=(.*)\&amp;RateCenterName=(.*)\'/', $line, $match)) {
                    if (!in_array(strtolower($match[1]), $id_list)) {
                        // Using the UniqueID as the key de-dupes rate centers automatically while making it an array adds a list of cities in that rate center
                        $rateCenters[$match[1]][] = $match[2];
                        // NOTE: This outputs all cities but duplicates Rate Center IDs. Choose wisely.
                        fputs($fp, $match[1] . "|" . $match[2] . "\n");
                        echo "Found a rate center in $state ($regionID)  ----  $match[2] ($match[1])\n";
                        $zerocheck->{$match[1]} = 0;
                        $id_list[] = strtolower($match[1]);
                    }
                }
            }

            fclose($fp);
        }

        // Save zerocheck
        $this->_zerocheck_obj->set_settings($zerocheck);
        $this->_zerocheck_obj->write();
    }

    private function _search_numbers($rate_center, &$numbers, $prefixes = array("")) {
        $quantity = 0;
        foreach ($prefixes as $prefix) {
            //$prefix = substr($prefix, 2, 6);
            if ($prefix) 
                echo " Searching with prefix $prefix\n";

            $number_result = $this->_make_request("https://my.bandwidth.com/portal/Members/NumberManagement/ListNumbers.aspx?RateCenterID=" . $rate_center, "POST", "ddlGateway=anyGateway&ddlNumbertype=LI&ddlNumberIndicator=anyNumberIndicator&ddlStatus=Available&txtSearch=$prefix&ddlSearchType=FullNumber&ngNumbersGeneration%24GenerateOptions=radioRangeReplace&__EVENTTARGET=cmdSearchNumbers%24button&EVENTARGUMENT=&LASTFOCUS=&pager%24ddlGridPagesSize=5000&pager%24ddlGridPagesList=0");

            // This will * theorically * retrieve the number of numbers returned by the current request
            if (preg_match('/Found Telephone Numbers \((\d*)\)/', $number_result, $match)) {

                print_r($match);

                $quantity += $match[1];

                $lines = explode("\n", $number_result);
                foreach ($lines as $line) {
                    if (preg_match('/TelephoneNumberID=(.{36}).*?>(.*)<\/a>/', $line, $match)) {
                        $telID = $match[1];
                        $number = "+" . str_replace("-", "", strip_tags($match[2]));
                        $numbers[$number] = array("telID" => $telID, "RateCenterID" => $rate_center);
                        //echo '+' . $number . ',' . $telID . "\n";
                    }
                }
            }
        }

        return $quantity;
    }

    private function _dowload_numbers($state) {
        $arr_numbers = array();
        $zerocheck = $this->_zerocheck_obj->get_settings();
        $number_obj = new models_number('bandwidthold');

        /* STEP 1. Figure out what rate centers we're going to scan */
        $rate_centers = file(ASSETS_PATH . $this->_provider_name . '/' . $this->_settings->rc_filename . '-' . $state);

        /* STEP 2. Open output file. */
        $fp = fopen (ASSETS_PATH . $this->_provider_name . '/' . $this->_settings->nbr_filename . '-' . $state, "w+");

        /* STEP 3. Begin going through each rate center, one by one */
        $get_all_rows = false;
        foreach ($rate_centers as $rate_center) {
            $numbers = array();
            $quantity = 0;
            $tmp = explode('|', $rate_center);
            $rate_center = trim($tmp[0]);
            $rate_center_name = $tmp[1];
            $rate_center_city = trim(explode(',', $tmp[1])[0]);

            $number_obj->set_city($rate_center_city);
            $number_obj->set_state($state);

            if ($zerocheck->$rate_center <= $this->_settings->max_zerocheck) {
                echo "Increasing size of result list to 5000 that bandwidth.com will return to us for each request...\n";
                $this->_make_request("https://my.bandwidth.com/portal/Members/NumberManagement/ListNumbers.aspx?RateCenterID=$rate_center", "GET");

                echo "Now processing $rate_center rate center...\n";
                $quantity = $this->_search_numbers($rate_center, $numbers);
                echo "  Found $quantity numbers!\n";

                if ($quantity != 0) {
                    if ($quantity == 500) {
                        echo "  NOTICE: This is probably not the full list. Re-running the query with an additional search parameter...\n";
                        // If you get 500 numbers back, there are more to be had! But bandwidth.com won't send them to you :(
                        // Figure out all the prefixes in the numbers list and then run a second query for each prefix to get the full list
                        $prefixes = array();

                        /*foreach ($numbers as $number => $v) {
                            $prefixes[substr($number, 2, 5)] = true;
                        }
                        $prefixes = array_keys($prefixes);*/

                        $quantity = $this->_search_numbers($rate_center, $numbers, $prefixes);
                        echo "  Found $quantity numbers on second try!\n";

                        // Still returning 5000+ numbers? Make another query to split this prefix into tenths
                        // You can work around this - find all the prefixes of the numbers that were just returned and then query each prefix (i.e. if you got 4158867900 go re-run the query for 415886)
                        // If you get back 5000 numbers, you have hit yet another limit of their API. This time we need to get more aggressive - do 10 queries for 4158861, 4158862, 4158863, etc. thru 4158869
                    }

                    foreach ($numbers as $number => $data) {
                        //echo 'Adding number (' . $number . ") to the database\n";
                        $npa = substr(str_replace('+', '', $number), 1, 3);
                        $number_obj->create_db('US_' . $npa);

                        fputs($fp, $number . "," . $data["RateCenterID"] . "," . $data["telID"] . "\n");
                        $number_obj->set_number($number);
                        $number_obj->set_number_identifier($data["telID"]);
                        $number_obj->insert();

                        $arr_numbers[] = $number;
                    }

                    // Sort from lowest to highest
                    $arr_numbers = array_unique($arr_numbers, SORT_NUMERIC);
                    sort($arr_numbers);

                    // And finally inserting the blocks
                    $this->_insert_block($arr_numbers);

                    // We need to reset zerocheck to zero to make sure it will go
                    // through the process next time
                    $zerocheck->$rate_center = 0;
                } else
                    $zerocheck->$rate_center = $zerocheck->$rate_center + 1;

                sleep($this->_settings->wait_timer);
            }
        }

        // Save zerocheck
        $this->_zerocheck_obj->set_settings($zerocheck);
        $this->_zerocheck_obj->write();

        fclose($fp);
    }

    public function create() {
        $this->_log_and_setup();
        $this->_download_rate_centers();
        foreach ($this->_constants->states as $regionID => $state) {
            $this->_dowload_numbers($state);
        }
    }

    public function update() {
        $this->_log_and_setup();
        foreach ($this->_constants->states as $regionID => $state) {
            $this->_dowload_numbers($state);
        }
    }
}