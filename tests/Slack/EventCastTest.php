<?php

use App\Slack\Event\Details;
use App\Slack\Event\EventCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Support\DataAttributesCollection;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataPropertyType;
use Spatie\LaravelData\Support\Types\NamedType;

beforeEach(function () {
    test()->dataProperty = new DataProperty(
        name: 'testProperty',
        className: 'TestClass',
        type: new DataPropertyType(
            type: new NamedType(
                name: 'string',
                builtIn: true,
                acceptedTypes: ['string'],
                kind: DataTypeKind::Default,
                dataClass: null,
                dataCollectableClass: null,
                iterableClass: null,
                iterableItemType: null,
                iterableKeyType: null
            ),
            isOptional: false,
            isNullable: false,
            isMixed: false,
            lazyType: null,
            kind: DataTypeKind::Default,
            dataClass: null,
            dataCollectableClass: null,
            iterableClass: null,
            iterableItemType: null,
            iterableKeyType: null
        ),
        validate: true,
        computed: true,
        hidden: false,
        isPromoted: false,
        isReadonly: false,
        morphable: false,
        autoLazy: null,
        hasDefaultValue: false,
        defaultValue: null,
        cast: null,
        transformer: null,
        inputMappedName: null,
        outputMappedName: null,
        attributes: new DataAttributesCollection
    );
});

it('casts expected events', function () {
    expect((new EventCast)->cast(
        test()->dataProperty,
        [
            'type' => 'reaction_added',
            'user' => 'U123ABC456',
            'item' => [
                'type' => 'message',
                'channel' => 'C123ABC456',
                'ts' => '1464196127.000002',
            ],
            'reaction' => 'slightly_smiling_face',
            'item_user' => 'U222222222',
            'event_ts' => '1465244570.336841',
        ],
        [],
        new CreationContext(
            dataClass: 'DataClass',
            mappedProperties: [],
            currentPath: [],
            validationStrategy: ValidationStrategy::Always,
            mapPropertyNames: false,
            disableMagicalCreation: false,
            useOptionalValues: true,
            ignoredMagicalMethods: [],
            casts: null
        )
    ))->toBeInstanceOf(Details::class);
});

it('properly reports uncastables', function () {
    expect((new EventCast)->cast(
        test()->dataProperty,
        [
            'type' => 'non_existent',
            'user' => 'U123ABC456',
            'item' => [
                'type' => 'message',
                'channel' => 'C123ABC456',
                'ts' => '1464196127.000002',
            ],
            'reaction' => 'slightly_smiling_face',
            'item_user' => 'U222222222',
            'event_ts' => '1465244570.336841',
        ],
        [],
        new CreationContext(
            dataClass: 'DataClass',
            mappedProperties: [],
            currentPath: [],
            validationStrategy: ValidationStrategy::Always,
            mapPropertyNames: false,
            disableMagicalCreation: false,
            useOptionalValues: true,
            ignoredMagicalMethods: [],
            casts: null
        )
    ))->toBeInstanceOf(Uncastable::class);
});
