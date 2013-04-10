<?php 

/**
 * Block model
 * @author Francis Genet
 * @package Number_manager
 */
class models_block extends models_model {
    private $_id;
    private $_size;
    private $_start_number;
    private $_end_number;

    // === Setter ===

    public function set_size($size) {
        $this->_size = $size;
    }

    public function set_start_number($start_number) {
        $this->_start_number = $start_number;
    }

    public function set_end_number($end_number) {
        $this->_end_number = $end_number;
    }

    // === Getter ===

    public function get_size($size) {
        return $this->_size;
    }

    public function get_start_number($start_number) {
        return $this->_start_number;
    }

    public function get_end_number($end_number) {
        return $this->_end_number;
    }

    // ==============

    function __construct($provider) {
        parent::__construct($provider);
    }

    function __destruct() {
        parent::__destruct();
    }
}
    
 ?>