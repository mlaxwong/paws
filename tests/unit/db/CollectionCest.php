<?php
namespace paws\tests\db;

use yii\db\ActiveRecordInterface;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use Paws;
use paws\tests\UnitTester;
use paws\db\Collection;
use paws\db\RecordInterface;
use paws\db\CollectionInterface;
use paws\records\CollectionField;
use paws\records\Collection AS CollectionRecord;
use paws\records\CollectionType;
use paws\records\CollectionValue;

class CollectionCest
{
    public function _before(UnitTester $I)
    {
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function testCreate(UnitTester $I)
    {
        $type = new CollectionType(['name' => 'Article', 'handle' => 'article']);
        $I->assertTrue($type->save());

        $fields = [
            [
                'name' => 'Title',
                'handle' => 'title',
                'config' => json_encode(['string', 'max' => 10]),
            ],
            [
                'name' => 'Content',
                'handle' => 'content',
                'config' => json_encode(['string']),
            ],  
        ];
        foreach ($fields as $index => $field) 
        {
            $fields[$index] = new CollectionField($field);
            $I->assertTrue($fields[$index]->save());
            $type->link('fields', $fields[$index]);
        }
        
        $collection = new Collection(['typeId' => $type->id]);
        $I->assertEquals([
            'id'                    => null,
            'collection_type_id'    => null,
            'created_at'            => null,
            'updated_at'            => null,
            'title'                 => null,
            'content'               => null,
        ], $collection->attributes);

        $values = [
            'title' => 'Testing',
            'content' => 'This is content',
        ];
        foreach ($values as $key => $value) $collection->{$key} = $value;
        $I->assertTrue($collection->save());

        $I->assertNotNull($collection->id);
        foreach ($values as $key => $value) $I->assertEquals($value, $collection->{$key});
    }

    public function testLoad(UnitTester $I)
    {
        $type = new CollectionType(['name' => 'Article', 'handle' => 'article']);
        $I->assertTrue($type->save());

        $fields = [
            [
                'name' => 'Title',
                'handle' => 'title',
                'config' => json_encode([['string', 'max' => 10]]),
            ],
            [
                'name' => 'Content',
                'handle' => 'content',
                'config' => json_encode([['string']]),
            ],  
        ];
        foreach ($fields as $index => $field) 
        {
            $fields[$index] = new CollectionField($field);
            $I->assertTrue($fields[$index]->save());
            $type->link('fields', $fields[$index]);
        }

        $collection = new Collection(['typeId' => $type->id]);
        $I->assertEquals([
            'id'                    => null,
            'collection_type_id'    => null,
            'created_at'            => null,
            'updated_at'            => null,
            'title'                 => null,
            'content'               => null,
        ], $collection->attributes);

        $data = [
            'Collection' => [
                'title' => 'Testing',
                'content' => 'This is content',
            ],
        ];
        $collection->load($data);
        $I->assertEquals(ArrayHelper::merge($collection->attributes, $data['Collection']), $collection->attributes);
    }

    public function testInstanceOfInterfaces(UnitTester $I)
    {
        $collection = new Collection;
        $interfaces = [
            ActiveRecordInterface::class,
            RecordInterface::class,
            CollectionInterface::class,
        ];
        foreach ($interfaces as $interface) $I->assertInstanceOf($interface, $collection);
    }

    public function testCollectionRecord(UnitTester $I)
    {
        // default
        $I->assertEquals('paws\\records\\Collection', Collection::collectionRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))), $testClass::collectionRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\Class', $testClass::collectionRecord());
    }

    public function testCollectionTypeRecord(UnitTester $I) 
    {
        // default
        $I->assertEquals(Collection::collectionRecord() . 'Type', Collection::collectionTypeRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))) . 'Type', $testClass::collectionTypeRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\ClassType', $testClass::collectionTypeRecord());
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
            public static function collectionTypeRecord()
            {
                return 'custom\\collection\\type\\Record';
            }
        };
        $I->assertEquals('custom\\collection\\type\\Record', $testClass::collectionTypeRecord());
    }

    public function testCollectionValueRecord(UnitTester $I) 
    {
        // default
        $I->assertEquals(Collection::collectionRecord() . 'Value', Collection::collectionValueRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))) . 'Value', $testClass::collectionValueRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\ClassValue', $testClass::collectionValueRecord());
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
            public static function collectionValueRecord()
            {
                return 'custom\\collection\\type\\Record';
            }
        };
        $I->assertEquals('custom\\collection\\type\\Record', $testClass::collectionValueRecord());
    }

    public function testCollectionFieldRecord(UnitTester $I) 
    {
        // default
        $I->assertEquals(Collection::collectionRecord() . 'Field', Collection::collectionFieldRecord());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals('paws\\records\\' . Inflector::camelize(StringHelper::basename(get_class($testClass))) . 'Field', $testClass::collectionFieldRecord());

        // customize
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
        };
        $I->assertEquals('this\\is\\testing\\ClassField', $testClass::collectionFieldRecord());
        $testClass = new class extends Collection {
            public static function collectionRecord()
            {
                return 'this\\is\\testing\\Class';
            }
            public static function collectionFieldRecord()
            {
                return 'custom\\collection\\type\\Record';
            }
        };
        $I->assertEquals('custom\\collection\\type\\Record', $testClass::collectionFieldRecord());
    }

    public function testFkCollectionId(UnitTester $I)
    {
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals('collection_id', $testClass::fkCollectionId());
    }

    public function testFkFieldId(UnitTester $I)
    {
        $testClass = new class extends Collection { public static function collectionFieldRecord() { return CollectionField::class; } };
        $I->assertEquals('collection_field_id', $testClass::fkFieldId());
    }

    public function testTypeAttribute(UnitTester $I)
    {
        // default
        $I->assertEquals('collection_type_id', Collection::typeAttribute());

        // extended
        $testClass = new class extends Collection {};
        $I->assertEquals(Inflector::camel2id(StringHelper::basename(get_class($testClass)), '_') . '_type_id', $testClass::typeAttribute());
    }

    public function testGetBaseAttribute(UnitTester $I)
    {
        // default
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals((new CollectionRecord)->attributes(), $testClass->getBaseAttributes());

        // custom
        $testClass = new class extends Collection 
        { 
            public static function collectionRecord() { return CollectionRecord::class; } 
            public function getBaseAttributes() { return ['testing1', 'testing2']; } 
        };
        $I->assertEquals(['testing1', 'testing2'], $testClass->getBaseAttributes());
    }

    public function testGetCustomAttributes(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $customAttributes = ['title', 'content'];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        foreach ($customAttributes as $index => $customAttribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $customAttribute,
                'handle' => $customAttribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
        }
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertNull($testClass->getType());
        $I->assertEquals([], $testClass->getCustomAttributes());

        $testClass->typeId = 1;
        $I->assertNotNull($testClass->getType());
        $I->assertEquals($customAttributes, $testClass->getCustomAttributes());
    }

    public function testAttributes(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $customAttributes = ['title', 'content'];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        foreach ($customAttributes as $index => $customAttribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $customAttribute,
                'handle' => $customAttribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
        }

        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals($testClass->getBaseAttributes(), $testClass->attributes());

        $testClass->typeId = 1;
        $I->assertEquals(ArrayHelper::merge($testClass->getBaseAttributes(), $customAttributes), $testClass->attributes());
    }

    public function testGetType(UnitTester $I)
    {
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'testing',
            'handle' => 'testing',
        ]);
        $testClass = new class extends Collection 
        {
            public $typeId = 1;
            public static function collectionRecord()  { return CollectionRecord::class; } 
        };
        $I->assertEquals(CollectionType::findOne(1), $testClass->getType());
    }

    public function testBaseRules(UnitTester $I)
    {
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals((new CollectionRecord)->rules(), $testClass->baseRules());
    }

    public function testRules(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $customAttributes = [
            [
                'name' => 'title',
                'config' => [
                    ['string', 'max' => 100]
                ],
            ],
            [
                'name' => 'content',
                'config' => [
                    ['safe']
                ],
            ],
        ];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $rules = [];
        foreach ($customAttributes as $index => $customAttribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $customAttribute['name'],
                'handle' => $customAttribute['name'],
                'config' => json_encode($customAttribute['config']),
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);

            $config = $customAttribute['config'];
            foreach ($config as $rule)
            {
                array_unshift($rule, $customAttribute['name']);
                $rules[] = $rule;
            }
        }
        $I->assertEquals($testClass->baseRules(), $testClass->rules());

        $testClass->typeId = 1;
        $I->assertEquals($rules, $testClass->customRules());
        $I->assertEquals(ArrayHelper::merge($testClass->baseRules(), $rules), $testClass->rules());
    }

    public function testPrimaryKey(UnitTester $I)
    {
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals(CollectionRecord::primaryKey(), $testClass::primaryKey());
    }

    public function testInsertValueRecord(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $customAttributes = ['title', 'content'];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        foreach ($customAttributes as $index => $customAttribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $customAttribute,
                'handle' => $customAttribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
        }
        $I->haveRecord(CollectionRecord::class, [
            'id' => 1,
            'collection_type_id' => 1,
        ]);
        $testClass = new class extends Collection 
        { 
            public static function collectionRecord() { return CollectionRecord::class; } 
            public static function collectionFieldRecord() { return CollectionField::class; } 
        };

        $I->assertNotFalse($valueId = $testClass->insertValueRecord(CollectionRecord::findOne(1), CollectionField::findOne(1), 'testing'));
        $I->seeRecord(CollectionValue::class, [
            CollectionValue::primaryKey()[0] => $valueId,
            $testClass::fkCollectionId() => 1,
            $testClass::fkFieldId() => 1,
            'value' => 'testing',
        ]);
    }

    public function testInsert(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $values = [
            'title' => 'Breaking news',
            'content' => 'Mlaxology just listing on stock market',
        ];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        foreach (array_keys($values) as $index => $customAttribute)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $customAttribute,
                'handle' => $customAttribute,
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
        }
        $testClass = new class extends Collection 
        { 
            public $typeId = 1;
            public static function collectionRecord() { return CollectionRecord::class; } 
            public static function collectionFieldRecord() { return CollectionField::class; } 
            public static function typeAttribute() { return 'collection_type_id'; }
            public function getDirtyAttributes($name = null)
            {
                return [
                    'title' => 'Breaking news',
                    'content' => 'Mlaxology just listing on stock market',
                ];
            }
        };
        $I->assertTrue($testClass->insert());

        foreach (array_values($values) as $index => $value)
        {
            $I->seeRecord(CollectionValue::class, [
                $testClass::fkCollectionId() => $testClass->id,
                $testClass::fkFieldId() => $index + 1,
                'value' => $value,
            ]);
        }
    }

    public function testUpdate(UnitTester $I)
    {
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $fields = [
            [
                'name' => 'title',
                'config' => [
                    ['string', 'max' => 100]
                ],
                'value' => 'Breaking news',
            ],
            [
                'name' => 'content',
                'config' => [
                    ['safe']
                ],
                'value' => 'Mlaxology just listing on stock market',
            ],
        ];

        $values = [
            'title' => 'Breaking news',
            'content' => 'Mlaxology just listing on stock market',
        ];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        $I->haveRecord(CollectionRecord::class, [
            'id' => 1,
            'collection_type_id' => 1,
        ]);
        $testClass = new class extends Collection 
        { 
            public static function collectionRecord() { return CollectionRecord::class; } 
            public static function collectionFieldRecord() { return CollectionField::class; } 
            public static function typeAttribute() { return 'collection_type_id'; }
            public function getDirtyAttributes($name = null)
            {
                return [
                    'title' => 'Yeah',
                    'content' => 'Sold out',
                ];
            }
        };
        foreach ($fields as $index => $field)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $field['name'],
                'handle' => $field['name'],
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
            $I->haveRecord(CollectionValue::class, [
                CollectionValue::primaryKey()[0] => $index + 1,
                $testClass::fkCollectionId() => 1,
                $testClass::fkFieldId() => $index + 1,
                'value' => $field['value'],
            ]);
        }
        $collection = $testClass::findOne(1);
        $I->assertEquals('Breaking news', $collection->title);
        $I->assertEquals('Mlaxology just listing on stock market', $collection->content);
        $I->assertGreaterThan(0, $collection->updateInternal());
        $collection = $testClass::findOne(1);
        $I->assertEquals('Yeah', $collection->title);
        $I->assertEquals('Sold out', $collection->content);
    }

    public function testFind(UnitTester $I)
    {
        $testClass = new class extends Collection 
        {
            public static function collectionRecord() { return CollectionRecord::class; }
            public static function collectionFieldRecord() { return CollectionField::class; }
            public static function typeAttribute() { return 'collection_type_id'; }
        };
        $mappingTable = new class extends ActiveRecord { public static function tableName() { return '{{%collection_type_field_map}}'; } };
        $fields = [
            [
                'name' => 'title',
                'config' => [
                    ['string', 'max' => 100]
                ],
                'value' => 'Breaking news',
            ],
            [
                'name' => 'content',
                'config' => [
                    ['safe']
                ],
                'value' => 'Mlaxology just listing on stock market',
            ],
        ];
        $I->haveRecord(CollectionType::class, [
            'id' => 1,
            'name' => 'Article',
            'handle' => 'article',
        ]);
        $I->haveRecord(CollectionRecord::class, [
            'id' => 1,
            'collection_type_id' => 1,
        ]);
        foreach ($fields as $index => $field)
        {
            $I->haveRecord(CollectionField::class, [
                'id' => $index + 1,
                'name' => $field['name'],
                'handle' => $field['name'],
                'config' => json_encode($field['config']),
            ]);
            $I->haveRecord(get_class($mappingTable), [
                'collection_type_id' => 1,
                'collection_field_id' => $index + 1,
            ]);
            $I->haveRecord(CollectionValue::class, [
                CollectionValue::primaryKey()[0] => $index + 1,
                $testClass::fkCollectionId() => 1,
                $testClass::fkFieldId() => $index + 1,
                'value' => $field['value'],
            ]);
        }
        $query = $testClass::find();
        $one = $query->one();
        foreach ($fields as $field) $I->assertEquals($field['value'], $one->{$field['name']});
        $all = $query->all();
        $I->assertEquals(1, count($all));
        foreach ($all as $collection) foreach ($fields as $field) $I->assertEquals($field['value'], $collection->{$field['name']});
    }

    public function testGetDb(UnitTester $I)
    {
        $testClass = new class extends Collection { public static function collectionRecord() { return CollectionRecord::class; } };
        $I->assertEquals(CollectionRecord::getDb(), $testClass::getDb());
    }
}
