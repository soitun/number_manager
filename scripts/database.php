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
        // Bandwidth
        while (!feof($this->_file)) {
            $current_area_code = trim(fgets($this->_file));
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->create($current_area_code);
        }
    }

    public function update() {
        // Bandwidth
        while (!feof($this->_file)) {
            $current_area_code = trim(fgets($this->_file));
            $objBandwidth = new providers_bandwidth_provider();
            $objBandwidth->update($current_area_code);
        }
    }
}