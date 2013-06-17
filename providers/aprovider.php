<?php 

abstract class providers_aprovider {
    protected $_obj_block;
    protected $_settings;

    function __construct() {
        $general_settings = helper_settings::get_instance();
        $this->_settings = $general_settings->providers->{ENVIRONMENT}->bandwidth;
    }

    // The numbers in the array must be sorted from lowest to highest
    protected function _insert_block($arr_numbers) {
        // Blocks
        $this->_obj_block->set_start_number($arr_numbers[0]);
        $previous_number = null;
        for ($i=0; $i < count($arr_numbers); $i++) { 
            $current = (int)substr($arr_numbers[$i], -4);
            $next = isset($arr_numbers[$i+1]) ? (int)substr($arr_numbers[$i+1], -4) : null;

            if($next) {
                if($next == $current + 1) {
                    continue;
                } else {
                    $this->_obj_block->set_end_number($arr_numbers[$i]);
                    if ($this->_obj_block->insert()) {
                        $this->_obj_block->set_start_number($arr_numbers[$i+1]);
                        continue;
                    } else 
                        exit('Could not save a block');
                }
            } else {
                $this->_obj_block->set_end_number($arr_numbers[$i]);
                if ($this->_obj_block->insert()) {
                    continue;
                } else 
                    exit('Could not save a block');
            }
        }
    }
}