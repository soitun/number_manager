<?php 

abstract class providers_aprovider {
    protected $_obj_block;
    protected $_settings;
    protected $_constants;
    protected $_provider_name;

    function __construct() {
        // Settings
        $general_settings = helper_settings::get_instance();
        $this->_settings = $general_settings->providers->{ENVIRONMENT}->{$this->_provider_name};

        // Constants
        $constants = helper_settings::get_instance('constants.json');
        $this->_constants = $constants->{$this->_provider_name};

        $this->_obj_block = new models_block($this->_provider_name);
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