<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string|null $role_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser whereRoleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTypeUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RoleTypeUser extends Model
{
    use HasFactory;

    // Specify the table if it does not follow Laravel's naming convention
    protected $table = 'role_type_users';

    // Specify the fillable fields
    protected $fillable = ['role_type'];
}
