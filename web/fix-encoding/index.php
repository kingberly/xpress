<pre>
<?php
require 'vendor/autoload.php';

use Qlync\Config;
use Qlync\Database;
use Qlync\Table;

$config = Config::get();
echo 'Config: ' . print_r($config, true) . "\n";
$old = new Database(false);
$new = new Database(true);

foreach($config['tables'] as $tableName => $excludes) {
    echo "Table: $tableName\n";
    $table = new Table($tableName, $old, $excludes);
    $oldStmt = $table->query($old);
    $newStmt = $table->updateBegin($new);

    try {
        while ($row = $oldStmt->fetch(PDO::FETCH_ASSOC)) {
            $table->updateOne($newStmt, $row);
        }

        $table->updateFinish($new);
    } catch (Exception $e) {
        $table->updateAbort($new);
        throw $e;
    }

}

?>
</pre>
