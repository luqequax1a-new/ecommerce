<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Get all addresses for this user
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }
    
    /**
     * Get active addresses for this user
     */
    public function activeAddresses(): HasMany
    {
        return $this->addresses()->active();
    }
    
    /**
     * Get billing addresses for this user
     */
    public function billingAddresses(): HasMany
    {
        return $this->activeAddresses()->billing();
    }
    
    /**
     * Get shipping addresses for this user
     */
    public function shippingAddresses(): HasMany
    {
        return $this->activeAddresses()->shipping();
    }
    
    /**
     * Get default billing address
     */
    public function defaultBillingAddress()
    {
        return $this->addresses()->active()->billing()->defaultBilling()->first();
    }
    
    /**
     * Get default shipping address
     */
    public function defaultShippingAddress()
    {
        return $this->addresses()->active()->shipping()->defaultShipping()->first();
    }
    
    /**
     * Get address book (all active addresses with location info)
     */
    public function getAddressBook()
    {
        return CustomerAddress::getAddressBook($this->id);
    }
}
