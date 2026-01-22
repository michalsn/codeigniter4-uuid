<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;

class Project3Model extends Model
{
    use HasUuid;

    protected array $casts = [
        'id' => 'uuid[v7,bytes]',
    ];
    protected $table            = 'projects3';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['name', 'description'];
    protected $useTimestamps    = true;
    protected $validationRules  = [
        'name'        => 'required|min_length[3]',
        'description' => 'required',
    ];
}
