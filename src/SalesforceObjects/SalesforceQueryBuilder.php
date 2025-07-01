<?php

namespace Flinty916\LaravelSalesforce\SalesforceObjects;

use Flinty916\LaravelSalesforce\Service\SalesforceClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SalesforceQueryBuilder
{
    protected string $object;
    protected array $fields = ['Id'];
    protected array $whereClauses = [];
    protected array $orWhereClauses = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected Collection|null $records = null;
    protected int|null $total = null;
    protected string|null $nextPage = null;

    public function __construct(string $object)
    {
        $this->object = $object;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function where(string|array $field, string $operator = null, mixed $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $condition) {
                [$f, $op, $val] = $condition;
                $this->whereClauses[] = [$f, $op, $val];
            }
        } else {
            $this->whereClauses[] = [$field, $operator, $value];
        }
        return $this;
    }

    public function orWhere(string|array $field, string|null $operator = null, mixed $value = null): self
    {
        if (is_array($field)) {
            foreach ($field as $condition) {
                [$f, $op, $val] = $condition;
                $this->orWhereClauses[] = [$f, $op, $val];
            }
        } else {
            $this->orWhereClauses[] = [$field, $operator, $value];
        }
        return $this;
    }

    public function orderBy(string $field, string $direction = "asc"): self
    {
        $this->orderBy[] = [$field, strtoupper($direction)];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function get(): self
    {
        $client = app(SalesforceClient::class);
        $response = $client->get('/query/', ['q' => $this->buildSoql()]);

        if ($response->records && $response->totalSize != 0)
            $this->records = collect($response->records);
        $this->total = $response->totalSize;

        return $this;
    }

    public function records(): Collection
    {
        return $this->records;
    }

    public function total(): int
    {
        return $this->total;
    }

    protected function buildSoql(): string
    {
        $select = implode(', ', $this->fields);
        $query = "SELECT {$select} FROM {$this->object}";

        if (!empty($this->whereClauses)) {
            $conditions = collect($this->whereClauses)->map(
                fn($w) =>
                "{$w[0]} {$w[1]} '" . addslashes($w[2]) . "'"
            )->implode(' AND ');
            $query .= " WHERE {$conditions}";
        }

        if (!empty($this->orWhereClauses)) {
            $ors = collect($this->orWhereClauses)->map(
                fn($w) =>
                "{$w[0]} {$w[1]} '" . addslashes($w[2]) . "'"
            )->implode(' OR ');

            $query .= empty($this->whereClauses)
                ? " WHERE {$ors}"
                : " OR {$ors}";
        }

        if (!empty($this->orderBy)) {
            $order = collect($this->orderBy)->map(fn($o) => "{$o[0]} {$o[1]}")->implode(', ');
            $query .= " ORDER BY {$order}";
        }

        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
        }

        return $query;
    }
}
