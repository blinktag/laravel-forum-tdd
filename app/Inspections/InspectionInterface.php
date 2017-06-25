<?php

namespace App\Inspections;

interface InspectionInterface
{
    public function detect($body);
}
