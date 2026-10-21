<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/** Lee la planilla como arrays sin procesar (la interpretación la hace quien la usa) */
class RawSheetImport implements ToArray
{
    public function array(array $array): void
    {
    }
}
