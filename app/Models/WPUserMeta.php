<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;

class WPUserMeta extends Model
{
    protected $table = 'usermeta';

    protected $fillable = [
        'user_id', 'meta_key', 'meta_value'
    ];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        if (is_multisite()) {
            $this->connection = 'ms';
        }
    }

    /**
     * TODO most likely to remove
     *
     * @return void
     */
    public static function getUserIdWithRoles()
    {
        $roles = Settings::get('calendar_roles');
        $wp_capabilities = [];
        foreach ($roles as $role) {
            $wp_capabilities[] = serialize([$role => true]);
        }

        global $wpdb;
        $site_prefix = $wpdb->get_blog_prefix();

        $result_user_ids = self::select('user_id')
            ->where('meta_key', $site_prefix . 'capabilities')
            ->where(function ($query) use ($roles) {
                for ($i = 0; $i < count($roles); $i++) {
                    $query->orwhere('meta_value', 'like', '%' . $roles[$i] . '%');
                }
            })->get();

        $user_ids = [];
        foreach ($result_user_ids->toArray() as $array) {
            $user_ids[] = $array['user_id'];
        }
        return $user_ids;
    }
}
