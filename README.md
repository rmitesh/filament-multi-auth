## Filament Multi Auth Demo

#### Requirement

- PHP v8.2
- Node v16.14.2
- NPM v7.20.6

#### Installation

Install PHP dependency.
```bash
composer install
```

Copy .env file
```bash
cp .env.example .env
```

Generate Application Key
```bash
php artisan key:generate
```
Run Mingration
```bash
php artisan migrate
```

### How it works

#### Create Model and migrate
Create a new model. for this demo I have created `Employee` with basic fields. I have customize the login with username and password.
- name
- email
- username
- password

Extends `Employee` model with `Authenticatable` class and implements with `FilamentUser`

```php
<?php

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable implements FilamentUser
{
    use HasFactory;

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // you can add your own logic to access Employee users.
    }
}
```

Now, create a new guard in the `config/auth.php`

```php
<?php

return [

    'guards' => [
        // ...

        'web:employees' => [
            'driver' => 'session',
            'provider' => 'employees',
        ],
    ],

    'providers' => [
        // ...
        
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
    ],
];

```

> If you want to reset password for new guard then you can add details in the `passwords` section

#### Create Filament Panel

Now, create a new filament panel, here I have created as `Employee`.
It will registered in the `app\Providers\Filament` directory as `EmployeePanelProvider` class.

To add login page and guard for the employee

```php
return $panel
    ->id('employee')
    ->path('employee')
    ->login()
    ->authGuard('web:employees')
```

##### Create Custom Login page.

Create custom Filament Page or Livewire page `EmployeeLogin` and add in the `->login(EmployeeLogin::class)` in the `EmployeePanelProvider`
> For business logic, checkout the <a href="app/Livewire/EmployeeLogin.php">EmployeeLogin.php</a> file.

#### Create Middleware

Create custom middleware called `EmployeeAuthenticate` and register in the `EmployeePanelProvider` panel.
> For business logic, checkout the <a href="app/Http/Middleware/EmployeeAuthenticate.php">EmployeeAuthenticate.php</a> file.

```php
->authMiddleware([
    EmployeeAuthenticate::class,
]);
```
And Finally it's ready to access.
