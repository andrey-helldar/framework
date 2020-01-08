<?php

namespace Illuminate\Tests\Integration\Database;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Database\Eloquent\Cast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EloquentModelCustomCastingTest extends DatabaseTestCase
{
    protected $model;

    public function testStringCastable()
    {
        $arr = $this->model->toArray();
        $this->assertInstanceOf(Castable::class, $arr['field_1']);
        $this->assertInstanceOf(StringCast::class, $arr['field_1']);
        $this->assertSame(['f', 'o', 'o', 'b', 'a', 'r'], $arr['field_1']->getValue());
        $this->assertSame(['f', 'o', 'o', 'b', 'a', 'r'], $arr['field_1']->value);

        $this->assertInstanceOf(Castable::class, $this->model->field_1);
        $this->assertInstanceOf(StringCast::class, $this->model->field_1);
        $this->assertSame(['f', 'o', 'o', 'b', 'a', 'r'], $this->model->field_1->getValue());
        $this->assertSame(['f', 'o', 'o', 'b', 'a', 'r'], $this->model->field_1->value);
    }

    public function testNumberCastable()
    {
        $arr = $this->model->toArray();
        $this->assertInstanceOf(Castable::class, $arr['field_2']);
        $this->assertInstanceOf(NumberCast::class, $arr['field_2']);
        $this->assertSame(0.2, $arr['field_2']->value);

        $this->assertInstanceOf(Castable::class, $this->model->field_2);
        $this->assertInstanceOf(NumberCast::class, $this->model->field_2);
        $this->assertSame(0.2, $this->model->field_2->getValue());
        $this->assertSame(0.2, $this->model->field_2->value);
    }

    public function testTimeCastable()
    {
        $arr = $this->model->toArray();
        $this->assertInstanceOf(Castable::class, $arr['field_3']);
        $this->assertInstanceOf(TimeCast::class, $arr['field_3']);
        $this->assertInstanceOf(DateTimeInterface::class, $arr['field_3']->value);
        $this->assertSame('08:19:12', $arr['field_3']->getValue()->format('H:i:s'));
        $this->assertSame('08:19:12', $arr['field_3']->value->format('H:i:s'));

        $this->assertInstanceOf(Castable::class, $this->model->field_3);
        $this->assertInstanceOf(TimeCast::class, $this->model->field_3);
        $this->assertInstanceOf(DateTimeInterface::class, $this->model->field_3->value);
        $this->assertSame('08:19:12', $this->model->field_3->getValue()->format('H:i:s'));
        $this->assertSame('08:19:12', $this->model->field_3->value->format('H:i:s'));
    }

    public function testNullCastable()
    {
        $arr = $this->model->toArray();
        $this->assertInstanceOf(Castable::class, $arr['field_4']);
        $this->assertInstanceOf(NullCast::class, $arr['field_4']);
        $this->assertSame(null, $arr['field_4']->getValue());
        $this->assertSame(null, $arr['field_4']->value);

        $this->assertInstanceOf(Castable::class, $this->model->field_4);
        $this->assertInstanceOf(NullCast::class, $this->model->field_4);
        $this->assertSame(null, $this->model->field_4->getValue());
        $this->assertSame(null, $this->model->field_4->value);
    }

    public function testNullChangedCastable()
    {
        $arr = $this->model->toArray();
        $this->assertInstanceOf(Castable::class, $arr['field_5']);
        $this->assertInstanceOf(NullChangedCast::class, $arr['field_5']);
        $this->assertSame('foo', $arr['field_5']->getValue());
        $this->assertSame('foo', $arr['field_5']->value);

        $this->assertInstanceOf(Castable::class, $this->model->field_5);
        $this->assertInstanceOf(NullChangedCast::class, $this->model->field_5);
        $this->assertSame('foo', $this->model->field_5->getValue());
        $this->assertSame('foo', $this->model->field_5->value);
    }

    public function testChangingValueOfNestedObject()
    {
        $item           = new AddressCast();
        $item->line_one = 'Address Line 1';
        $item->line_two = 'Address Line 2';

        $model = TestModel::create(['field_6' => $item]);

        $this->assertInstanceOf(AddressCast::class, $model->field_6);
        $this->assertInstanceOf(Castable::class, $model->field_6);
        $this->assertSame('Address Line 1', $model->field_6->line_one);
        $this->assertSame('Address Line 2', $model->field_6->line_two);

        $newItem           = new AddressCast();
        $newItem->line_one = 'New Address Line 1';
        $newItem->line_two = 'New Address Line 2';
        $model->field_6    = $newItem;

        $this->assertInstanceOf(Castable::class, $model->field_6);
        $this->assertSame('New Address Line 1', $model->field_6->line_one);
        $this->assertSame('New Address Line 2', $model->field_6->line_two);

        $model->field_6->line_one = 'Modified Line 1';
        $this->assertInstanceOf(Castable::class, $model->field_6);
        $this->assertSame('Modified Line 1', $model->field_6->line_one);
        $this->assertSame('New Address Line 2', $model->field_6->line_two);

        $model->field_6           = new AddressCast();
        $model->field_6->line_one = 'Fresh Line 1';
        $model->field_6->line_two = 'Fresh Line 2';

        $this->assertInstanceOf(Castable::class, $model->field_6);
        $this->assertSame('Fresh Line 1', $model->field_6->line_one);
        $this->assertSame('Fresh Line 2', $model->field_6->line_two);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
        $this->createModel();
    }

    protected function migrate()
    {
        Schema::create('test_model', function (Blueprint $table) {
            $table->increments('id');
            $table->string('field_1')->nullable();
            $table->decimal('field_2')->nullable();
            $table->time('field_3')->nullable();
            $table->string('field_4')->nullable();
            $table->string('field_5')->nullable();
            $table->json('field_6')->nullable();
        });
    }

    protected function createModel()
    {
        $this->model = TestModel::create([
            'field_1' => ['f', 'o', 'o', 'b', 'a', 'r'],
            'field_2' => 20,
            'field_3' => '08:19:12',
            'field_4' => null,
            'field_5' => null,
            'field_6' => [
                'line_one' => 'Address Line 1',
                'line_two' => 'Address Line 2',
            ],
        ]);
    }
}

class TestModel extends Model
{
    public $table = 'test_model';

    public $timestamps = false;

    public $casts = [
        'field_1' => StringCast::class,
        'field_2' => NumberCast::class,
        'field_3' => TimeCast::class,
        'field_4' => NullCast::class,
        'field_5' => NullChangedCast::class,
        'field_6' => AddressCast::class,
    ];

    protected $guarded = ['id'];
}

class TimeCast extends Cast
{
    /**
     * @param  mixed  $value
     * @throws \Exception
     * @return \DateTimeInterface
     */
    public function fromDatabase($value = null)
    {
        return new DateTime($value);
    }

    public function toDatabase($value = null)
    {
        return is_numeric($value)
            ? DateTime::createFromFormat('H:i:s', $value)->format('H:i:s')
            : $value;
    }
}

class StringCast extends Cast
{
    public function fromDatabase($value = null)
    {
        return str_split($value);
    }

    public function toDatabase($value = null)
    {
        return implode('', $value);
    }
}

class NumberCast extends Cast
{
    public static $databaseKeyType = 'double';

    public function fromDatabase($value = null)
    {
        return $value / 100;
    }

    public function toDatabase($value = null)
    {
        return $value;
    }
}

class NullCast extends Cast
{
    public function fromDatabase($value = null)
    {
        return $value;
    }

    public function toDatabase($value = null)
    {
        return $value;
    }
}

class NullChangedCast extends Cast
{
    public function fromDatabase($value = null)
    {
        return 'foo';
    }

    public function toDatabase($value = null)
    {
        return $value;
    }
}

class AddressCast extends Cast
{
    public static $databaseKeyType = 'object';

    public function fromDatabase($value = null)
    {
        return [
            'line_one' => $value->line_one ?? null,
            'line_two' => $value->line_two ?? null,
        ];
    }

    public function toDatabase($value = null)
    {
        return $value;
    }
}
