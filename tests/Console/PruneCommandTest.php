<?php

namespace Laravel\Telescope\Tests\Console;

use Laravel\Telescope\Database\Factories\EntryModelFactory;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\Tests\FeatureTestCase;

class PruneCommandTest extends FeatureTestCase
{
    public function test_prune_command_will_clear_old_records()
    {
        $recent = EntryModelFactory::new()->create(['created_at' => now()]);

        $old = EntryModelFactory::new()->create(['created_at' => now()->subDays(2)]);

        $this->artisan('telescope:prune')->expectsOutput('1 entries pruned.');

        $this->assertDatabaseHas('telescope_entries', ['uuid' => $recent->uuid]);

        $this->assertDatabaseMissing('telescope_entries', ['uuid' => $old->uuid]);
    }

    public function test_prune_command_can_vary_hours()
    {
        $recent = EntryModelFactory::new()->create(['created_at' => now()->subHours(5)]);

        $this->artisan('telescope:prune')->expectsOutput('0 entries pruned.');

        $this->artisan('telescope:prune', ['--hours' => 4])->expectsOutput('1 entries pruned.');

        $this->assertDatabaseMissing('telescope_entries', ['uuid' => $recent->uuid]);
    }

    public function test_prune_command_can_skip_exception()
    {
        $recent = EntryModelFactory::new()->create(['type' => EntryType::EXCEPTION, 'created_at' => now()->subHours(5)]);

        $this->artisan('telescope:prune', ['--skipException' => true])->expectsOutput('0 entries pruned.');

        $this->artisan('telescope:prune', ['--hours' => 4])->expectsOutput('1 entries pruned.');

        $this->assertDatabaseMissing('telescope_entries', ['uuid' => $recent->uuid]);
    }
}
