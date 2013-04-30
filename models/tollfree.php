<?php

/**
 * Number model
 * @author Francis Genet
 * @package Number_manager
 */
class models_tollfree extends models_model {
    private $_id;
    private $_number;
    private $_last_update;
    private $_db_name;

    // === Setter ===

    public function set_number($number) {
        $this->_number = $number;
    }

    public function set_last_update($last_update) {
        $this->_last_update = $last_update;
    }

    public function set_provider($provider) {
        $this->_provider = $provider;
    }

    public function set_or_create_db($db_name, $create = true) {
        $this->_db_name = $db_name;

        if ($create) {
            try {
                $query = "CREATE TABLE IF NOT EXISTS `" . $db_name . "` (
                  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `number` bigint(20) unsigned NOT NULL,
                  `provider` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `number` (`number`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";

                $stmt = $this->_db->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                return false;
            }
        }
    }

    // === Getter ===

    public function get_number($number) {
        return $this->_number;
    }

    public function get_last_update($last_update) {
        return $this->_last_update;
    }

    public function get_provider() {
        return $this->_provider;
    }

    // ==============

    function __construct($provider) {
        parent::__construct($provider);
    }

    function __destruct() {
        parent::__destruct();
    }

    public function start_transaction() {
        $this->_db->beginTransaction();
    }

    public function commit() {
        $this->_db->commit();
    }

    public function rollback() {
        $this->_db->rollBack();
    }

    // Adding number in DB
    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `" . $this->_db_name . "` (`number`, `provider`) VALUES(?, ?)");
            $stmt->execute(array($this->_number, $this->_provider));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function truncate() {
        $query = "TRUNCATE TABLE `" . $this->_db_name . "`";
        $this->_db->query($query);
    }
}

 ?>