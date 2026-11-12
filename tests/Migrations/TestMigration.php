<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Migrations\Tests;

use Arcano\Database\DatabaseInterface;
use Arcano\Migrations\Migration;
use Arcano\Migrations\State;
use Arcano\Migrations\TableBlueprint;

class TestMigration extends Migration
{
    public function up(): State
    {
        return $this->getState();
    }

    public function down(): DatabaseInterface
    {
        return $this->database();
    }

    public function getTable(): TableBlueprint
    {
        return $this->table('table');
    }
}
