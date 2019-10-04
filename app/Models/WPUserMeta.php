<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class WPUserMeta extends Model
{
    protected $table = 'usermeta';

    protected $fillable = [
        'user_id', 'meta_key', 'meta_value'
    ];

    public static function getUserIdWithRoles($roles = ['administrator', 'author',  'editor', 'contributor'])
    {
        $wp_capabilities = [];
        foreach ($roles as $role) {
            $wp_capabilities[] = serialize([$role => true]);
        }
        $result_user_ids = self::select('user_id')->where('meta_key', 'wp_capabilities')->whereIn('meta_value', $wp_capabilities)->get();
        $user_ids = [];
        foreach ($result_user_ids->toArray() as $array) {
            $user_ids[] = $array['user_id'];
        }
        return $user_ids;
    }
}
