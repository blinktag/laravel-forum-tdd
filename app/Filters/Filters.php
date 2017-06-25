<?php

namespace App\Filters;

use Illuminate\Http\Request;

abstract class Filters
{

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var Request
     */
    protected $request;

    protected $builder;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        $this->builder = $builder;

        /*foreach($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($this->request->$filter);
            }
        }*/

        $this->getFilters()
            ->filter(function($filter){
                return method_exists($this, $filter);
            })
            ->each(function($filter, $value) {
                $this->$filter($value);
            });

        return $this->builder;

    }

    public function getFilters()
    {
        return collect($this->request->intersect($this->filters))->flip();
    }
}
