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

    public function start_transaction() {
        $this->_db->beginTransaction();
    }

    public function commit() {
        $this->_db->commit();
    }

    public function rollback() {
        $this->_db->rollBack();
    }

    public function insert() {
        $this->_size = ($this->_end_number - $this->_start_number) + 1;
        try {
            $stmt = $this->_db->prepare("INSERT INTO `blocks`(`size`, `start_number`, `end_number`, `provider`) VALUES(?, ?, ?, ?)");
            $stmt->execute(array($this->_size, $this->_start_number, $this->_end_number, $this->_provider));
        } catch (PDOException $e) {
            return true;
        }

        return true;
    }

    public function delete_like_number($number) {
        $like = $number . '%';

        try {
            $stmt = $this->_db->prepare("DELETE FROM `blocks` WHERE `start_number` LIKE ?");
            $stmt->execute(array($like));
        } catch (PDOException $e) {
            echo $e->getMessage() . "\n";
            return false;
        }

        return true;
    }
}
    
 ?>