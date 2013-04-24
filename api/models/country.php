<?php 

/**
 * Country model
 * @author Francis Genet
 * @package Number_manager_api
 */
class models_country extends models_model {
    private $_id;
    private $_iso_code;
    private $_local;
    private $_toll_free;
    private $_vanity;
    private $_prefix;
    private $_flag_url;
    private $_name;

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function get_countries() {
        $query = "SELECT `iso_code`, `local`, `toll_free`, `vanity`, `prefix`, `flag_url`, `name` FROM `countries`";
        $stmt = $this->_db->query($query);

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }
}

 ?>