<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
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
            'role' => UserRole::class // Assuming role is an enum, adjust as necessary
        ];
    }

    /**
     * Kullanıcının admin olup olmadığını kontrol eden yardımcı fonksiyon.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Kullanıcının yönetici olup olmadığını kontrol eden yardımcı fonksiyon.
     *
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isEditor(): bool
    {
        return $this->role === UserRole::EDITOR;
    }
    public function isAuthor(): bool
    {
        return $this->role === UserRole::AUTHOR;
    }
    public function isContributors(): bool
    {
        return $this->role === UserRole::CONTRIBUTORS;
    }
    public function isModerator(): bool
    {
        return $this->role === UserRole::MODERATOR;
    }
    public function isMember(): bool
    {
        return $this->role === UserRole::MEMBER;
    }
    public function isSubscriber(): bool
    {
        return $this->role === UserRole::SUBSCRIBER;
    }
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }
}
