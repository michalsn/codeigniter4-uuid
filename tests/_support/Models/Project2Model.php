<?php

namespace Tests\Support\Models;

use CodeIgniter\Model;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;

class Project2Model extends Model
{
    use HasUuid;

    protected array $casts = [
        'id'          => 'int',
        'category_id' => 'uuid',
    ];
    protected $table           = 'projects2';
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
