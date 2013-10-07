<?php 

class scripts_database {
    private $_file = null;
    private $_settings = null;

    function __construct($file_path) {
        $this->_file = fopen($file_path, 'r');
        if (!$this->_file)
            die("Could not load the file\n");

        // Load the settings
        $objSettings = new helper_settings();
        $this->_settings = $objSettings->get_settings();
    }

    function __destruct() {
        fclose($this->_file);
    }

    public function create() {
        // Bandwidth old
        $objBandwidthOld = new providers_bandwidthold_provider();
        $objBandwidthOld->create();

        /*while (!feof($this->_file)) {
            $current_area_code = trim(fgets($this->_file));

            // Bandwidth
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->create($current_area_code);

            // O1
            $objO1 = new providers_o1_provider();
            $objO1->create($current_area_code);
        }*/
    }

    public function create_tollfree() {
        // Bandwidth
        while (!feof($this->_file)) {
            /*$current_area_code = trim(fgets($this->_file));
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->sync_tollfree($current_area_code);*/
        }
    }

    public function update() {
        // Bandwidth old
        $current_area_code = trim(fgets($this->_file));
        $objBandwidthOld = new providers_bandwidthold_provider();
        $objBandwidthOld->update();

        /*while (!feof($this->_file)) {
            $current_area_code = trim(fgets($this->_file));

            // Bandwidth
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->update($current_area_code);

            // O1
            $objO1 = new providers_o1_provider();
            $objO1->update($current_area_code);
        }*/
    }

    public function update_tollfree() {
        // Bandwidth
        while (!feof($this->_file)) {
            /*$current_area_code = trim(fgets($this->_file));
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->sync_tollfree($current_area_code, true);*/
        }
    }
}