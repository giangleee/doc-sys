<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class OfficeImport implements ToModel
{
    use Importable;

    public function model(array $row)
    {
        return $row;
    }
}
