<?php

namespace App\Http\Controllers;

/**
 * Untuk master sederhana tanpa relasi: units, breeds, phases,
 * diseases, suppliers, customers.
 */
abstract class SingleMasterController extends MasterController
{
    protected function fieldColumns(): array
    {
        return array_slice(array_keys($this->fields), 0, 5);
    }
}
