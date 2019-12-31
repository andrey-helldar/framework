<?php

namespace Illuminate\Tests\Integration\Database;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Cast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EloquentModelCustomCastingTest extends DatabaseTestCase
{
    public function testValues()
    {
        $model = TestModel::create([
            'field_1' => ['f', 'o', 'o', 'b', 'a', 'r'],
            'field_2' => 20,
            'field_3' => '08:19:12',
            'field_4' => null,
            'field_5' => null,
        ]);

        $this->assertSame(['f', 'o', 'o', 'b', 'a', 'r'], $model->toArray()['field_1']);

        $this->assertSame(0.2, $model->toArray()['field_2']);

        $this->assertInstanceOf(DateTimeInterface::class, $model->toArray()['field_3']);

        $this->assertSame('08:19:12', $model->toArray()['field_3']->format('H:i:s'));

        $this->assertSame(null, $model->toArray()['field_4']);

        $this->assertSame('foo', $model->toArray()['field_5']);
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
     * @param string $key
     * @param mixed $value
     *
     * @throws \Exception
     * @return DateTime
     */
    public function fromDatabase($key, $value = null)
    {
        return new DateTime($value);
    }

    public function toDatabase($key, $value = null)
    {
        return is_numeric($value)
            ? DateTime::createFromFormat('H:i:s', $value)->format('H:i:s')
            : $value;
    }
}

class StringCast extends Cast
{
    public function fromDatabase($key, $value = null)
    {
        return str_split($value);
    }

    public function toDatabase($key, $value = null)
    {
        return is_array($value)
            ? implode('', $value)
            : $value;
    }
}

class NumberCast extends Cast
{
    protected $keyType = 'double';

    public function fromDatabase($key, $value = null)
    {
        return $value / 100;
    }

    public function toDatabase($key, $value = null)
    {
        return $value;
    }
}

class NullCast extends Cast
{
    public function fromDatabase($key, $value = null)
    {
        return $value;
    }

    public function toDatabase($key, $value = null)
    {
        return $value;
    }
}

class NullChangedCast extends Cast
{
    public function fromDatabase($key, $value = null)
    {
        return 'foo';
    }

    public function toDatabase($key, $value = null)
    {
        return $value;
    }
}

class AddressCast extends Cast implements Jsonable
{
    public $line_one;

    public $line_two;

    protected $keyType = 'object';

    public function fromDatabase($key, $value = null)
    {
        $this->line_one = $value->line_one;
        $this->line_two = $value->line_two;

        return $this;
    }

    public function toDatabase($key, $value = null)
    {
        $this->line_one = $value->line_one ?? null;
        $this->line_two = $value->line_two ?? null;

        return $this;
    }

    public function toJson($options = 0)
    {
        return [
            'line_one' => $this->line_one,
            'line_two' => $this->line_two,
        ];
    }
}
