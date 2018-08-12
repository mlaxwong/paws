<?php
namespace paws\console\controllers;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\console\controllers\BaseMigrateController;

class MigrateController extends BaseMigrateController
{
    const MAX_NAME_LENGTH = 180;

    public $migrationTable = '{{%migration}}';
    
    public $templateFile = '@yii/views/migration.php';
    
    public $generatorTemplateFiles = [
        'create_table' => '@yii/views/createTableMigration.php',
        'drop_table' => '@yii/views/dropTableMigration.php',
        'add_column' => '@yii/views/addColumnMigration.php',
        'drop_column' => '@yii/views/dropColumnMigration.php',
        'create_junction' => '@yii/views/createTableMigration.php',
    ];
    
    public $useTablePrefix = false;
    
    public $fields = [];
    
    public $db = 'db';
    
    public $comment = '';

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationTable', 'db'], // global for all actions
            $actionID === 'create'
                ? ['templateFile', 'fields', 'useTablePrefix', 'comment']
                : []
        );
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'C' => 'comment',
            'f' => 'fields',
            'p' => 'migrationPath',
            't' => 'migrationTable',
            'F' => 'templateFile',
            'P' => 'useTablePrefix',
            'c' => 'compact',
        ]);
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id !== 'create') $this->db = Instance::ensure($this->db, Connection::className());
            return true;
        }

        return false;
    }

    protected function createMigration($class)
    {
        $this->includeMigrationFile($class);

        return Yii::createObject([
            'class' => $class,
            'db' => $this->db,
            'compact' => $this->compact,
        ]);
    }

    protected function getMigrationHistory($limit)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }
        $query = (new Query())
            ->select(['version', 'apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC]);

        if (empty($this->migrationNamespaces)) {
            $query->limit($limit);
            $rows = $query->all($this->db);
            $history = ArrayHelper::map($rows, 'version', 'apply_time');
            unset($history[self::BASE_MIGRATION]);
            return $history;
        }

        $rows = $query->all($this->db);

        $history = [];
        foreach ($rows as $key => $row) {
            if ($row['version'] === self::BASE_MIGRATION) {
                continue;
            }
            if (preg_match('/m?(\d{6}_?\d{6})(\D.*)?$/is', $row['version'], $matches)) {
                $time = str_replace('_', '', $matches[1]);
                $row['canonicalVersion'] = $time;
            } else {
                $row['canonicalVersion'] = $row['version'];
            }
            $row['apply_time'] = (int) $row['apply_time'];
            $history[] = $row;
        }

        usort($history, function ($a, $b) {
            if ($a['apply_time'] === $b['apply_time']) {
                if (($compareResult = strcasecmp($b['canonicalVersion'], $a['canonicalVersion'])) !== 0) {
                    return $compareResult;
                }

                return strcasecmp($b['version'], $a['version']);
            }

            return ($a['apply_time'] > $b['apply_time']) ? -1 : +1;
        });

        $history = array_slice($history, 0, $limit);

        $history = ArrayHelper::map($history, 'version', 'apply_time');

        return $history;
    }

    protected function createMigrationHistoryTable()
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);
        $this->stdout("Creating migration history table \"$tableName\"...", Console::FG_YELLOW);
        $this->db->createCommand()->createTable($this->migrationTable, [
            'version' => 'varchar(' . static::MAX_NAME_LENGTH . ') NOT NULL PRIMARY KEY',
            'apply_time' => 'integer',
        ])->execute();
        $this->db->createCommand()->insert($this->migrationTable, [
            'version' => self::BASE_MIGRATION,
            'apply_time' => time(),
        ])->execute();
        $this->stdout("Done.\n", Console::FG_GREEN);
    }

    protected function addMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->insert($this->migrationTable, [
            'version' => $version,
            'apply_time' => time(),
        ])->execute();
    }

    protected function truncateDatabase()
    {
        $db = $this->db;
        $schemas = $db->schema->getTableSchemas();

        // First drop all foreign keys,
        foreach ($schemas as $schema) {
            if ($schema->foreignKeys) {
                foreach ($schema->foreignKeys as $name => $foreignKey) {
                    $db->createCommand()->dropForeignKey($name, $schema->name)->execute();
                    $this->stdout("Foreign key $name dropped.\n");
                }
            }
        }

        // Then drop the tables:
        foreach ($schemas as $schema) {
            $db->createCommand()->dropTable($schema->name)->execute();
            $this->stdout("Table {$schema->name} dropped.\n");
        }
    }

    protected function removeMigrationHistory($version)
    {
        $command = $this->db->createCommand();
        $command->delete($this->migrationTable, [
            'version' => $version,
        ])->execute();
    }

    private $_migrationNameLimit;

    protected function getMigrationNameLimit()
    {
        if ($this->_migrationNameLimit !== null) {
            return $this->_migrationNameLimit;
        }

        if ($this->action->id !== 'create') {
            $tableSchema = $this->db->schema ? $this->db->schema->getTableSchema($this->migrationTable, true) : null;
            if ($tableSchema !== null) {
                return $this->_migrationNameLimit = $tableSchema->columns['version']->size;
            }
        }

        return static::MAX_NAME_LENGTH;
    }

    protected function generateMigrationSourceCode($params)
    {
        $parsedFields = $this->parseFields();
        $fields = $parsedFields['fields'];
        $foreignKeys = $parsedFields['foreignKeys'];

        $name = $params['name'];

        $templateFile = $this->templateFile;
        $table = null;
        if (preg_match('/^create_junction(?:_table_for_|_for_|_)(.+)_and_(.+)_tables?$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['create_junction'];
            $firstTable = $matches[1];
            $secondTable = $matches[2];

            $fields = array_merge(
                [
                    [
                        'property' => $firstTable . '_id',
                        'decorators' => 'integer()',
                    ],
                    [
                        'property' => $secondTable . '_id',
                        'decorators' => 'integer()',
                    ],
                ],
                $fields,
                [
                    [
                        'property' => 'PRIMARY KEY(' .
                            $firstTable . '_id, ' .
                            $secondTable . '_id)',
                    ],
                ]
            );

            $foreignKeys[$firstTable . '_id']['table'] = $firstTable;
            $foreignKeys[$secondTable . '_id']['table'] = $secondTable;
            $foreignKeys[$firstTable . '_id']['column'] = null;
            $foreignKeys[$secondTable . '_id']['column'] = null;
            $table = $firstTable . '_' . $secondTable;
        } elseif (preg_match('/^add_(.+)_columns?_to_(.+)_table$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['add_column'];
            $table = $matches[2];
        } elseif (preg_match('/^drop_(.+)_columns?_from_(.+)_table$/', $name, $matches)) {
            $templateFile = $this->generatorTemplateFiles['drop_column'];
            $table = $matches[2];
        } elseif (preg_match('/^create_(.+)_table$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['create_table'];
            $table = $matches[1];
        } elseif (preg_match('/^drop_(.+)_table$/', $name, $matches)) {
            $this->addDefaultPrimaryKey($fields);
            $templateFile = $this->generatorTemplateFiles['drop_table'];
            $table = $matches[1];
        }

        foreach ($foreignKeys as $column => $foreignKey) {
            $relatedColumn = $foreignKey['column'];
            $relatedTable = $foreignKey['table'];
            // Since 2.0.11 if related column name is not specified,
            // we're trying to get it from table schema
            // @see https://github.com/yiisoft/yii2/issues/12748
            if ($relatedColumn === null) {
                $relatedColumn = 'id';
                try {
                    $this->db = Instance::ensure($this->db, Connection::className());
                    $relatedTableSchema = $this->db->getTableSchema($relatedTable);
                    if ($relatedTableSchema !== null) {
                        $primaryKeyCount = count($relatedTableSchema->primaryKey);
                        if ($primaryKeyCount === 1) {
                            $relatedColumn = $relatedTableSchema->primaryKey[0];
                        } elseif ($primaryKeyCount > 1) {
                            $this->stdout("Related table for field \"{$column}\" exists, but primary key is composite. Default name \"id\" will be used for related field\n", Console::FG_YELLOW);
                        } elseif ($primaryKeyCount === 0) {
                            $this->stdout("Related table for field \"{$column}\" exists, but does not have a primary key. Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                        }
                    }
                } catch (\ReflectionException $e) {
                    $this->stdout("Cannot initialize database component to try reading referenced table schema for field \"{$column}\". Default name \"id\" will be used for related field.\n", Console::FG_YELLOW);
                }
            }
            $foreignKeys[$column] = [
                'idx' => $this->generateTableName("idx-$table-$column"),
                'fk' => $this->generateTableName("fk-$table-$column"),
                'relatedTable' => $this->generateTableName($relatedTable),
                'relatedColumn' => $relatedColumn,
            ];
        }

        return $this->renderFile(Yii::getAlias($templateFile), array_merge($params, [
            'table' => $this->generateTableName($table),
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
            'tableComment' => $this->comment,
        ]));
    }

    protected function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }

        return '{{%' . $tableName . '}}';
    }

    protected function parseFields()
    {
        $fields = [];
        $foreignKeys = [];

        foreach ($this->fields as $index => $field) {
            $chunks = preg_split('/\s?:\s?/', $field, null);
            $property = array_shift($chunks);

            foreach ($chunks as $i => &$chunk) {
                if (strncmp($chunk, 'foreignKey', 10) === 0) {
                    preg_match('/foreignKey\((\w*)\s?(\w*)\)/', $chunk, $matches);
                    $foreignKeys[$property] = [
                        'table' => isset($matches[1])
                            ? $matches[1]
                            : preg_replace('/_id$/', '', $property),
                        'column' => !empty($matches[2])
                            ? $matches[2]
                            : null,
                    ];

                    unset($chunks[$i]);
                    continue;
                }

                if (!preg_match('/^(.+?)\(([^(]+)\)$/', $chunk)) {
                    $chunk .= '()';
                }
            }
            $fields[] = [
                'property' => $property,
                'decorators' => implode('->', $chunks),
            ];
        }

        return [
            'fields' => $fields,
            'foreignKeys' => $foreignKeys,
        ];
    }

    protected function addDefaultPrimaryKey(&$fields)
    {
        foreach ($fields as $field) {
            if (false !== strripos($field['decorators'], 'primarykey()')) {
                return;
            }
        }
        array_unshift($fields, ['property' => 'id', 'decorators' => 'primaryKey()']);
    }
}
