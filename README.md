# Tnapf/Validation

Data validation plugin for Tnapf.

# Installation

```
composer require tnapf/validation
```

## Array Structured Validations

```php
<?php

use Tnapf\Validation\FilterVar;
use Tnapf\Validation\MaxLength;
use Tnapf\Validation\MinLength;
use Tnapf\Validation\Regex;
use function Tnapf\Validation\validateArray;

$data = [
    'name' => 'John Doe$$',
    'email' => 'malformed-email'
];

$errors = validateArray([
    'name' => [
        new Regex('Name must only container letters.', '/^[a-zA-Z ]+$/'),
        new MaxLength('Name cannot exceed {max} characters.', 255),
        new MinLength('Name is required.', 0)
    ],
    'email' => [
        new FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL),
        new MaxLength('Email cannot exceed {max} characters.', 255),
        new MinLength('Email is required.', 0)
    ],
], $data);

var_dump($errors);
```

## Object Structured Validations

```php
$errors = validateModel(new class {
    #[Regex('Name must only container letters.', '/^[a-zA-Z ]+$/')]
    #[MaxLength('Name cannot exceed {max} characters.', 255)]
    #[MinLength('Name is required.', 0)]
    public string $name;

    #[FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL)]
    #[MaxLength('Email cannot exceed {max} characters.', 255)]
    #[MinLength('Email is required.', 0)]
    public string $email;
}, [
    'name' => 'John Doe$$',
    'email' => 'malformed-email'
]);

var_dump($errors);

// or you can pass in a prefilled object

$model = new class {
    #[Regex('Name must only container letters.', '/^[a-zA-Z ]+$/')]
    #[MaxLength('Name cannot exceed {max} characters.', 255)]
    #[MinLength('Name is required.', 0)]
    public string $name;

    #[FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL)]
    #[MaxLength('Email cannot exceed {max} characters.', 255)]
    #[MinLength('Email is required.', 0)]
    public string $email;
};

$model->name = 'John Doe$$';
$model->email = 'malformed-email';

$errors = validateModel($model, []);

var_dump($errors);
```

## Getting validators from models

```php
use function Tnapf\Validation\getValidators;

$validators = getValidators(new class {
    #[Regex('Name must only container letters.', '/^[a-zA-Z ]+$/')]
    #[MaxLength('Name cannot exceed {max} characters.', 255)]
    #[MinLength('Name is required.', 0)]
    public string $name;

    #[FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL)]
    #[MaxLength('Email cannot exceed {max} characters.', 255)]
    #[MinLength('Email is required.', 0)]
    public string $email;
});
```