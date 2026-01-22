<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;

class Project4Model extends Model
{
    use HasUuid;

    protected array $casts = [
        'id'          => 'int',
        'category_id' => 'uuid[v7,bytes]',
    ];
    protected $table           = 'projects4';
    protected $primaryKey      = 'id';
    protected $returnType      = 'array';
    protected $useSoftDeletes  = true;
    protected $allowedFields   = ['category_id', 'name', 'description'];
    protected $useTimestamps   = true;
    protected $validationRules = [
        'name'        => 'required|min_length[3]',
        'description' => 'required',
    ];
}
