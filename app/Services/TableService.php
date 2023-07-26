<?php

/**
 * Class TableService
 */

namespace App\Services;

class TableService
{
    private array $table = [];

    public function __construct()
    {
    }

    public function initialize(): array
    {
        $this->table = [];
    }

    public function setTh($value): TableService
    {
        $this->table['th'] = $value;
        return $this;
    }

    public function setTd($rowNumber, $value): TableService
    {
        $this->table['td'][$rowNumber] = $value;
        return $this;
    }

    public function getView(): string
    {
        return view('cms.parts.table')->with(['table' => $this->table]);
    }
}
