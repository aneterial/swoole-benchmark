<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $age
 * @property string $created_at
 * @property string $updated_at
 */
final class User extends Model
{
    /** @var list<string> */
    protected array $fillable = [
        'name',
        'email',
        'age',
        'created_at',
        'updated_at',
    ];
}
