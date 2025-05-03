<?php
declare(strict_types=1);

namespace App\Helpers;

class Validators
{
    public static function name(string $name): bool
    {
        return (bool)preg_match("/^[A-Za-z\s'-]{2,60}$/", trim($name));
    }
    
    public static function phone(string $phone): bool
    {
        return (bool)preg_match("/^\d{10}$/", trim($phone));
    }
    
    public static function email(string $email): bool
    {
        $email = trim($email);
        return strlen($email) <= 120
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function password(string $pw): bool
    {
        // Password validation only checks:
        // 1. Minimum length of 8 characters
        // 2. Contains at least one letter
        // 3. Contains at least one number
        // Special characters are allowed and not restricted
        if (strlen($pw) < 8) {
            return false;
        }
        return preg_match('/[A-Za-z]/', $pw)
            && preg_match('/\d/', $pw);
    }
    
    public static function category(string $cat): bool
    {
        return in_array($cat, ['Vegetables', 'Fruits', 'Meat', 'Bakery', 'Dairy'], true);
    }
} 