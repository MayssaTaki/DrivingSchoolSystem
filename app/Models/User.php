<?php

namespace App\Models;
 use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;     

 protected $table = 'users'; 
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fcm_token'
        
    ];
    

 public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

public function posts()
{
    return $this->hasMany(Post::class);
}



public function sentConversations()
{
    return $this->hasMany(Conversation::class, 'sender_id');
}

public function receivedConversations()
{
    return $this->hasMany(Conversation::class, 'receiver_id');
}

public function messages()
{
    return $this->hasMany(Message::class, 'sender_id');
}




   

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
    public function student()
    {
        return $this->hasOne(Student::class);
    }
    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
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
}
