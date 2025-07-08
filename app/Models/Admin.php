<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'adminname',
        'adminpassword',
    ];

    protected $hidden = [
        'adminpassword',
    ];

    // Override the password field for authentication
    public function getAuthPassword()
    {
        return $this->adminpassword;
    }

    // Generate admin ID
    public static function generateAdminId($name)
    {
        $year = date('Y');
        $prefix = strtoupper(substr($name, 0, 3));
        $random = rand(100, 999);
        return $year . $prefix . $random;
    }
}
