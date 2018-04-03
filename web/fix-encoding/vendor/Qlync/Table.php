<?php
namespace Qlync;

use \PDO;

class Table {
    protected $name = '';
    protected $columns = Array();
    protected $pk = null;

    public function __construct($name, $db, $excludes=Array()) {
        $this->name = $name;
        $this->getSpecs($name, $db, $excludes);
    }

    protected function getSpecs($name, $db, $excludes) {
        $stmt = $db->get_db()->query("SELECT * FROM $name LIMIT 0");
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $col = $stmt->getColumnMeta($i);
            if (in_array('primary_key', $col['flags'])) {
                $this->pk = $col['name'];
            }
            else if ($col['pdo_type'] == PDO::PARAM_STR &&
                     !in_array($col['name'], $excludes)) {
                $name = $col['name'];
                $this->columns[] = $name;
            }
        }
        echo 'Columns: ' . print_r($this->columns, true) . "\n";
        echo 'Primary key: ' . $this->pk . "\n";
    }

    public function query($db) {
        $sql = sprintf('SELECT %s, %s FROM %s WHERE 1',
                       $this->pk,
                       implode(', ', $this->columns),
                       $this->name);
        echo 'Query SQL: ' . $sql . "\n";
        return $db->get_db()->query($sql);
    }

    public function updateBegin($db) {
        $db->get_db()->beginTransaction();
        $update = Array();
        foreach ($this->columns as $col) {
            $update[] = "$col = :$col";
        }
        $sql = sprintf('UPDATE %s SET %s WHERE %s = :%s',
                       $this->name,
                       implode(', ', $update),
                       $this->pk, $this->pk);
        echo 'Update SQL: ' . $sql . "\n";
        return $db->get_db()->prepare($sql);
    }

    public function updateOne($stmt, $row) {
        $params = Array();
        foreach($this->columns as $col) {
            $params[':' . $col] = $row[$col];
        }
        $params[':' . $this->pk] = $row[$this->pk];
        $stmt->execute($params);
    }

    public function updateFinish($db) {
        $db->get_db()->commit();
    }

    public function updateAbort($db) {
        $db->get_db()->rollBack();
    }
}

?>
