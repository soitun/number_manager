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

    public function get_by_number($pattern) {
        $like = $pattern . '%';
        $db_name = $country . '_' . $area_code;

        if (!$limit && !$offset)
            $query = "SELECT * FROM `toll_free` WHERE `number` LIKE ?";

        $stmt = $this->_db->prepare($query);
        $stmt->execute(array($like));

        if ($stmt->rowCount())
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        else
            return false;
    }

    // Adding number in DB
    public function insert() {
        try {
            $stmt = $this->_db->prepare("INSERT INTO `toll_free` (`number`, `provider`) VALUES(?, ?)");
            $stmt->execute(array($this->_number, $this->_provider));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }

    public function delete_like_number($number) {
        $like = $number . '%';

        try {
            $stmt = $this->_db->prepare("DELETE FROM `" . $this->_db_name . "` WHERE `number` LIKE ?");
            $stmt->execute(array($like));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }
}

 ?>