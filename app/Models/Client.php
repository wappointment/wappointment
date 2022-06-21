<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;
use Wappointment\ClassConnect\Carbon;

class Client extends Model
{
    use SoftDeletes, CanBook, CanBookLegacy;

    protected $table = 'wappo_clients';
    public $generatingOrder = true;
    protected $fillable = [
        'name', 'email', 'options'
    ];
    protected $casts = [
        'options' => 'array',
    ];

    protected $appends = ['avatar'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function hasActiveBooking($staff_id)
    {
        $start_at_string = Carbon::now('UTC')->format(WAPPOINTMENT_DB_FORMAT);
        $appointments_query = Appointment::where('client_id', $this->id)
            ->where('start_at', '>=', $start_at_string);

        if ((int)Settings::get('max_active_per_staff')) {
            $appointments_query->where('staff_id', $staff_id);
        }
        return $appointments_query->count();
    }

    public function getEmailAttribute($value)
    {
        return sanitize_email($value);
    }

    public function generateEditKey($start_at)
    {
        return md5($this->id . $start_at);
    }

    public function getFirstName()
    {
        return (strpos($this->name, ' ')) !== false ? substr($this->name, 0, strpos($this->name, ' ')) : $this->name;
    }

    public function getAvatarAttribute()
    {
        return get_avatar_url($this['email'], ['size' => 40]);
    }

    public function getLastName()
    {
        return (strpos($this->name, ' ')) !== false ? substr($this->name, strpos($this->name, ' ')) : '';
    }

    public function getEmailForDotcom()
    {
        return $this->email;
    }
    public function getNameForDotcom()
    {
        return $this->name;
    }

    public function getPhone()
    {
        return empty($this->options['phone']) ? '' : $this->options['phone'];
    }

    public function getSkype()
    {
        return empty($this->options['skype']) ? '' : $this->options['skype'];
    }

    public function getTimezone($defaultTz = 'UTC')
    {
        return empty($this->options['tz']) ? $defaultTz : $this->options['tz'];
    }

    public function getCustomField($tag = false)
    {
        return empty($tag) || empty($this->options[$tag['key']]) ? '' : $this->options[$tag['key']];
    }

    protected function getRealDuration($service)
    {
        return ((int) $service['duration'] + (int) Settings::get('buffer_time')) * 60;
    }

    public function mailableAddress()
    {
        return [$this->email => sanitize_text_field($this->name)];
    }

    public function getOrder()
    {
        $pendingOrder = Order::where('client_id', $this->id)->pending()->first();

        if (empty($pendingOrder)) {
            $pendingOrder = Order::create([
                'client_id' => $this->id,
                'transaction_id' => uniqid('onsite_' . $this->id),
                'tax_percent' => $this->getTaxPercentage()
            ]);
        }
        return $pendingOrder;
    }

    public function getTaxPercentage()
    {
        return !empty($this->client->options['tax_percent']) ? $this->client->options['tax_percent'] : Settings::get('tax');
    }

    public function generateOrder($ticket, $slots = 1)
    {
        if (!$this->generatingOrder) {
            return null;
        }
        //if pending order already exist, just get that one
        $pendingOrder = $this->getOrder();

        $pendingOrder->add($ticket, $slots);
        $ticket->recordOrderReference($pendingOrder);
        $pendingOrder->refreshTotal();
        $pendingOrder->load('prices');
        return $pendingOrder;
    }
}
