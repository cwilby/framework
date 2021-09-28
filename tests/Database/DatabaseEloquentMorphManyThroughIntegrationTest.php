<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUNit\Framework\TestCase;

class DatabaseEloquentMorphManyThroughIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new DB;

        $db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->createSchema();
    }

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function createSchema()
    {
        $this->schema()->create('mechanics', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('customers', function ($table) {
            $table->id();
            $table->foreignId('mechanic_id')->constrained();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('dealerships', function ($table) {
            $table->id();
            $table->foreignId('mechanic_id')->constrained();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('cars', function ($table) {
            $table->id();
            $table->morphs('owner');
            $table->timestamps();
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->schema()->drop('cars');
        $this->schema()->drop('dealerships');
        $this->schema()->drop('customers');
        $this->schema()->drop('mechanics');
    }

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection()
    {
        return Eloquent::getConnectionResolver()->connection();
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema()
    {
        return $this->connection()->getSchemaBuilder();
    }
}
