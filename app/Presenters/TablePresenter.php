<?php

/**
 * Class TablePresenter
 */
namespace App\Presenters;

class TablePresenter
{
    private $table = [];

    public function __construct()
    {}

    public function initialize()
    {
        $this->table = [];
    }

    public function set_th($value)
    {
        $this->table['th'] = $value;
        return $this;
    }

    public function set_td($row_number, $value)
    {
        $this->table['td'][$row_number] = $value;
        return $this;
    }

    public function get_view() {
        return view('cms.parts.table')->with(['table' => $this->table]);
    }
}