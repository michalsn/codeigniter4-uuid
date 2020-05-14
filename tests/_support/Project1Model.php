<?php

namespace Tests\Support;

use Michalsn\Uuid\UuidModel;

class Project1Model extends UuidModel
{
    protected $table      = 'projects1';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['name', 'description', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'description' => 'required',
    ];
}