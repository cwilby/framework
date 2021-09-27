<?php

namespace Illuminate\Tests\Database;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model as Eloquent;
use PHPUnit\Framework\TestCase;

class DatabaseEloquentMorphOneThroughIntegrationTest extends TestCase
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
        $this->schema()->create('titles', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('wrestlers', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('tag_teams', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('title_championships', function ($table) {
            $table->id();
            $table->foreignId('title_id')->constrained();
            $table->morphs('champion');
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
        $this->schema()->drop('titles');
        $this->schema()->drop('wrestlers');
        $this->schema()->drop('tag_teams');
        $this->schema()->drop('title_championships');
    }

    public function testEagerLoadingLoadsRelatedModelsCorrectly()
    {
        $this->seedData();
        $title = MorphOneThroughTestTitle::with('currentChampion')->first();

        $this->assertInstanceOf(MorphOneThroughTestWrestler::class, $title->currentChampion);
        $this->assertSame('Wrestler A', $title->currentChampion->name);
    }

    /**
     * Helpers...
     */
    protected function seedData()
    {
        MorphOneThroughTestTitle::create(['id' => 1, 'name' => 'Title A']);
        MorphOneThroughTestWrestler::create(['id' => 1, 'name' => 'Wrestler A']);
        MorphOneThroughTestTagTeam::create(['id' => 1, 'name' => 'Tag Team A']);
        MorphOneThroughTestTitleChampionship::create(['id' => 1, 'title_id' => 1, 'champion_id' => 1, 'champion_type' => 'App\Models\Champion']);
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

/**
 * Eloquent Models...
 */
class MorphOneThroughTestTitle extends Eloquent
{
    protected $table = 'titles';
    protected $guarded = [];

    public function currentChampion()
    {
        return $this->morphOneThrough(MorphOneThroughTestTitleChampionship::class, 'champion_id');
    }
}

/**
 * Eloquent Models...
 */
class MorphOneThroughTestTitleChampionship extends Eloquent
{
    protected $table = 'title_championships';
    protected $guarded = [];

    public function title()
    {
        return $this->belongsTo(MorphOneThroughTestTitle::class, 'title_id');
    }
}

class MorphOneThroughTestWrestler extends Eloquent
{
    protected $table = 'wrestlers';
    protected $guarded = [];
}

/**
 * Eloquent Models...
 */
class MorphOneThroughTestTagTeam extends Eloquent
{
    protected $table = 'tag_teams';
    protected $guarded = [];
}
