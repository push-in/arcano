<?php

declare(strict_types=1);

namespace Arcano\ORM\Command\Database;

use Arcano\Database\Database;
use Arcano\Database\DatabaseInterface;
use Arcano\Database\Query\InsertQuery;
use Arcano\Database\Query\ReturningInterface;
use Arcano\ORM\Command\StoreCommand;
use Arcano\ORM\Command\Traits\ErrorTrait;
use Arcano\ORM\Command\Traits\MapperTrait;
use Arcano\ORM\Heap\State;
use Arcano\ORM\MapperInterface;
use Arcano\ORM\Schema\GeneratedField;

/**
 * Insert data into associated table and provide lastInsertID promise.
 */
final class Insert extends StoreCommand
{
    use ErrorTrait;
    use MapperTrait;

    /**
     * @param non-empty-string $table
     * @param string[] $primaryKeys
     * @param non-empty-string|null $pkColumn
     * @param array<non-empty-string, int> $generated
     */
    public function __construct(
        DatabaseInterface $db,
        string $table,
        State $state,
        ?MapperInterface $mapper,
        private array $primaryKeys = [],
        private ?string $pkColumn = null,
        private array $generated = [],
    ) {
        parent::__construct($db, $table, $state);
        $this->mapper = $mapper;
    }

    public function isReady(): bool
    {
        return true;
    }

    public function hasData(): bool
    {
        return match (true) {
            $this->columns !== [],
            $this->state->getData() !== [],
            $this->hasGeneratedFields() => true,
            default => false,
        };
    }

    public function getStoreData(): array
    {
        if ($this->appendix !== []) {
            $this->state->setData($this->appendix);
            $this->appendix = [];
        }
        $data = $this->state->getData();
        return \array_merge($this->columns, $this->mapper?->mapColumns($data) ?? $data);
    }

    /**
     * Insert data into associated table.
     */
    public function execute(): void
    {
        $state = $this->state;
        $returningFields = [];

        if ($this->appendix !== []) {
            $state->setData($this->appendix);
        }

        $uncasted = $data = $state->getData();

        // filter PK null values
        foreach ($this->primaryKeys as $key) {
            if (!isset($uncasted[$key])) {
                unset($uncasted[$key]);
            }
        }
        // unset db-generated fields if they are null
        foreach ($this->generated as $column => $mode) {
            if (($mode & GeneratedField::ON_INSERT) === 0x0) {
                continue;
            }

            $returningFields[$column] = $mode;
            if (!isset($uncasted[$column])) {
                unset($uncasted[$column]);
            }
        }
        $uncasted = $this->prepareData($uncasted);

        $values = \array_merge($this->columns, $uncasted);

        if ($this->pkColumn !== null && $returningFields === []) {
            $returningFields[$this->primaryKeys[0]] ??= $this->pkColumn;
        }

        // Cached statements need the concrete database; other implementations get the builder.
        $insert = $this->db instanceof Database ? null : $this->db->insert($this->table)->values($values);
        $returning = [];
        if ($returningFields !== [] && ($insert === null
            ? StatementPlans::returns($this->db, $this->table)
            : $insert instanceof ReturningInterface)) {
            // Map generated fields to columns. Fields that are not declared in the schema columns
            // are dropped from $returningFields by reference, so both arrays stay aligned.
            $columns = $this->mapper->mapColumns($returningFields);
            // Array of [field name => column name]
            $returning = \array_combine(\array_keys($returningFields), \array_keys($columns));
        }

        if ($returning !== []) {
            // A non-empty returning list can only be built through the mapper above.
            \assert($this->mapper !== null);

            $insertID = $this->runInsert($insert, $values, \array_values($returning));

            if (\count($returning) === 1) {
                $field = \array_key_first($returning);
                $state->register($field, $this->mapper->cast([$field => $insertID])[$field]);
            } else {
                foreach ($this->mapper->cast($insertID) as $field => $value) {
                    $state->register($field, $value);
                }
            }
        } else {
            $insertID = $this->runInsert($insert, $values, \array_values($returning));

            if ($insertID !== null && \count($this->primaryKeys) === 1) {
                $fpk = $this->primaryKeys[0]; // first PK
                if (!isset($data[$fpk])) {
                    $state->register(
                        $fpk,
                        $this->mapper === null ? $insertID : $this->mapper->cast([$fpk => $insertID])[$fpk],
                    );
                }
            }
        }

        $state->updateTransactionData();

        parent::execute();
    }

    private function runInsert(?InsertQuery $insert, array $values, array $returning): mixed
    {
        if ($insert === null) {
            [$done, $result] = StatementPlans::insert($this->db, $this->table, $values, $returning);
            if ($done) {
                return $result;
            }
            $insert = $this->db->insert($this->table)->values($values);
        }
        $returning === [] or $insert->returning(...$returning);

        return $insert->run();
    }

    public function register(string $key, mixed $value): void
    {
        $this->state->register($key, $value);
    }

    /**
     * Has fields that weren't provided but will be generated by the database or PHP.
     */
    private function hasGeneratedFields(): bool
    {
        if ($this->generated === []) {
            return false;
        }

        $data = $this->state->getData();

        foreach ($this->generated as $field => $mode) {
            if (($mode & (GeneratedField::ON_INSERT | GeneratedField::BEFORE_INSERT)) === 0x0) {
                continue;
            }

            if (!isset($data[$field])) {
                return true;
            }
        }

        return false;
    }
}
