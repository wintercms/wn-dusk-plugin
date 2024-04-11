<?php

namespace Winter\Dusk\Tests\Fixtures\FormTester;

use Winter\Storm\Database\Traits\ArraySource;

class TestModel extends \Winter\Storm\Database\Model
{
    use ArraySource;

    public $records = [
        [
            'id' => 1,
            'name' => 'Test record',
            'description' => 'This is a test record',
            'is_active' => true,
            'type' => 'test',
        ],
        [
            'id' => 2,
            'name' => 'Another record',
            'description' => 'This is another test record',
            'is_active' => false,
            'type' => 'not-test',
        ]
    ];

    public function filterFields($fields, $context = null)
    {
        if (empty($fields->field_a->value)) {
            $fields->field_b->hidden = true;
        }
        if (empty($fields->field_b->value)) {
            $fields->field_c->hidden = true;
        }
    }
}
