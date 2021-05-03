<?php

namespace Tests\Support;

use Michalsn\Uuid\UuidModel;

class Project2Model extends UuidModel
{
    protected $uuidFields = ['category_id'];

    protected $table      = 'projects2';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['category_id', 'name', 'description', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'description' => 'required',
    ];
}