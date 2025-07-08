<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCredential extends Model
{
    use HasFactory;

    protected $table = 'employee_credentials';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id', 'id');
    }
}
