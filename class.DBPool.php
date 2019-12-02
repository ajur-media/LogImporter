<?php
/**
 * User: Arris
 *
 * Class DBPool
 * Namespace: AJUR\LogEstimator
 *
 * Date: 02.12.2019, time: 4:50
 */

namespace AJUR\LogEstimator;

use Arris\DB;
use function Arris\DBC as DBCAlias;

/**
 * Class DBPool
 * 
 */
class DBPool {
    private $pool_max_size = 0;
    private $pool = [];

    private $db_table = '';
    private $db_columns = [];

    /**
     * DBPool constructor.
     * @param int $pool_max_size
     * @param string $db_table
     * @param array $db_columns
     */
    public function __construct(int $pool_max_size, string $db_table, array $db_columns)
    {
        $this->pool_max_size = $pool_max_size;
        $this->db_table = $db_table;
        $this->db_columns = $db_columns;
    }

    /**
     * @param array $dataset
     * @throws \Exception
     */
    public function push(array $dataset)
    {
        if ($this->pool_max_size === count($this->pool)) {
            $this->commit();
        }
        $this->pool[] = $dataset;
    }

    /**
     * @throws \Exception
     */
    public function commit()
    {
        self::PDO_InsertRange($this->db_table, $this->pool, $this->db_columns);
        $this->pool = [];
    }

    /**
     * @param string $tableName
     * @param array $rows
     * @param array $db_columns
     * @throws \Exception
     */
    private static function PDO_InsertRange(string $tableName, array $rows, array $db_columns)
    {
        if (empty($rows)) return;

        // Get column list
        $columnList = array_keys($rows[0]);
        $numColumns = count($columnList);
        $columnListString = implode(",", $columnList);

        // Generate pdo param placeholders
        $placeHolders = [];

        foreach($rows as $row)
        {
            $placeHolders[] = "(?".str_repeat(",?", count($db_columns) - 1). ")";
        }

        $placeHolders = implode(",", $placeHolders);

        // Construct the query
        $sql = "INSERT INTO {$tableName} ({$columnListString}) VALUES {$placeHolders}";
        $stmt = DBCAlias()->prepare($sql);

        $j = 1;
        foreach($rows as $row)
        {
            for($i = 0; $i < $numColumns; $i++)
            {
                $stmt->bindParam($j, $row[$columnList[$i]]);
                $j++;
            }
        }

        $stmt->execute();
    }
}