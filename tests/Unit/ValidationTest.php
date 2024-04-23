<?php

use Tnapf\Validation\FilterVar;
use Tnapf\Validation\MaxLength;
use Tnapf\Validation\MinLength;
use Tnapf\Validation\Regex;

use function Tnapf\Validation\validateArray;
use function Tnapf\Validation\validateModel;

it('validates an array based model', function () {
    $badData = [
        'name' => 'John$$',
        'email' => 'malformed-email',
    ];
    $goodData = [
        'name' => 'John Doe',
        'email' => 'jd@example.com',
    ];

    $validators = [
        'name' => [
            new Regex('Name must only container letters.', '/^[a-zA-Z ]+$/'),
            new MaxLength('Name cannot exceed {max} characters.', 255),
            new MinLength('Name is required.', 0),
        ],
        'email' => [
            new FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL),
            new MaxLength('Email cannot exceed {max} characters.', 255),
            new MinLength('Email is required.', 0),
        ],
    ];

    $errors = validateArray($validators, $badData);
    expect($errors)->toBe([
        'name' => ['Name must only container letters.'],
        'email' => ['Email is not valid.'],
    ]);

    $errors = validateArray($validators, $goodData);
    expect($errors)->toBe([]);
});

it('validates an object model', function () {
    $model = new class () {
        #[Regex('Name must only container letters.', '/^[a-zA-Z ]+$/')]
        #[MaxLength('Name cannot exceed {max} characters.', 255)]
        #[MinLength('Name is required.', 0)]
        public string $name;

        #[FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL)]
        #[MaxLength('Email cannot exceed {max} characters.', 255)]
        #[MinLength('Email is required.', 0)]
        public string $email;
    };

    $badData = [
        'name' => 'John$$',
        'email' => 'malformed-email',
    ];

    $errors = validateModel($model, $badData);

    expect($errors)->toBe([
        'name' => ['Name must only container letters.'],
        'email' => ['Email is not valid.'],
    ]);

    $goodData = [
        'name' => 'John Doe',
        'email' => 'jd@example.com',
    ];

    $errors = validateModel($model, $goodData);

    expect($errors)->toBe([]);

    $model = new class () {
        #[Regex('Name must only container letters.', '/^[a-zA-Z ]+$/')]
        #[MaxLength('Name cannot exceed {max} characters.', 255)]
        #[MinLength('Name is required.', 0)]
        public string $name;

        #[FilterVar('Email is not valid.', FILTER_VALIDATE_EMAIL)]
        #[MaxLength('Email cannot exceed {max} characters.', 255)]
        #[MinLength('Email is required.', 0)]
        public string $email;
    };

    // Good model data
    $model->name = 'John Doe';
    $model->email = 'jd@example.com';

    $errors = validateModel($model, []);

    expect($errors)->toBe([]);

    // Bad model data
    $model->name = 'John$$';
    $model->email = 'malformed-email';

    $errors = validateModel($model, []);

    expect($errors)->toBe([
        'name' => ['Name must only container letters.'],
        'email' => ['Email is not valid.'],
    ]);
});
