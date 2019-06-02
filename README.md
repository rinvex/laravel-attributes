# Rinvex Attributes

**Rinvex Attributes** is a robust, intelligent, and integrated Entity-Attribute-Value model (EAV) implementation for Laravel Eloquent, with powerful underlying for managing entity attributes implicitly as relations with ease. It utilizes the power of Laravel Eloquent, with smooth and seamless integration.

[![Packagist](https://img.shields.io/packagist/v/rinvex/laravel-attributes.svg?label=Packagist&style=flat-square)](https://packagist.org/packages/rinvex/laravel-attributes)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/rinvex/laravel-attributes.svg?label=Scrutinizer&style=flat-square)](https://scrutinizer-ci.com/g/rinvex/laravel-attributes/)
[![Code Climate](https://img.shields.io/codeclimate/github/rinvex/laravel-attributes.svg?label=CodeClimate&style=flat-square)](https://codeclimate.com/github/rinvex/laravel-attributes)
[![Travis](https://img.shields.io/travis/rinvex/laravel-attributes.svg?label=TravisCI&style=flat-square)](https://travis-ci.org/rinvex/laravel-attributes)
[![StyleCI](https://styleci.io/repos/87620509/shield)](https://styleci.io/repos/87620509)
[![License](https://img.shields.io/packagist/l/rinvex/laravel-attributes.svg?label=License&style=flat-square)](https://github.com/rinvex/laravel-attributes/blob/develop/LICENSE)


## Credits notice

This package is a rewritten fork of [IsraelOrtuno](https://github.com/IsraelOrtuno)'s awesome [EAV Package](https://github.com/IsraelOrtuno/Eavquent), original credits goes to him. It's been widely rewritten, with same core concepts as it's fundamentally good in our opinion. The main differences in this fork include:

- Huge boost of performance utilizing [rinvex/laravel-cacheable](https://github.com/rinvex/laravel-cacheable)
- Serialize and deserialize the entity with it's relations
- Laravel integrated without framework-agnostic overhead complexity
- Attributes could be attached to none, one, or more entities through pivot table
- Attributes are sortable, sluggable, translatable, grouped, and most exciting cacheable
- Entity attributes are treated more naturally like normal **attributes**, in every possible Eloquent way
- Entity attributes are also treated more naturally like normal **relations**, in every possible Eloquent way


## Table of contents

- [Introduction](#introduction)
    - [Basics](#basics)
    - [The Performance Loss](#the-performance-loss)
    - [The gained flexibility](#the-gained-flexibility)
    - [More Technical Details](#more-technical-details)
- [Installation](#installation)
- [Usage](#usage)
    - [Add EAV to eloquent model](#add-eav-to-eloquent-model)
    - [Register your types](#register-your-types)
    - [Core types](#core-types)
    - [Register your entities](#register-your-entities)
    - [Create new attribute](#create-new-attribute)
    - [Manage attribute entities](#manage-attribute-entities)
    - [Assigning values](#assigning-values)
    - [Querying models](#querying-models)
    - [Eager loading](#eager-loading)
- [Changelog](#changelog)
- [Support](#support)
- [Contributing & Protocols](#contributing--protocols)
- [Security Vulnerabilities](#security-vulnerabilities)
- [About Rinvex](#about-rinvex)
- [License](#license)


## Introduction

### Basics

#### Preface

EAV Definition From [Wikipedia](https://en.wikipedia.org/wiki/Entity%E2%80%93attribute%E2%80%93value_model):

Entity–attribute–value model (EAV) is a data model to encode, in a space-efficient manner, entities where the number of attributes (properties, parameters) that can be used to describe them is potentially vast, but the number that will actually apply to a given entity is relatively modest.

#### Entity

An entity represents a real model which needs to extend its attributes dynamically. Example: models such as `Product`, `Customer` or `Company` are likely to be entities.

In this case an entity will be represented by an Eloquent model.

#### Attribute

The attribute act as the "column" we would like to add to an entity. An attribute gets a slug such as `price`, `cities` or `colors` to get identified and will be attached to an entity. It will also play very closely with a data type instance which will cast or format its value when writing or reading from database.

This attribute will also be responsible of defining some default behaviour like data validation or default values.

#### Value

This is responsible of storing data values related to a certain attribute and to a particular entity instance (row).

In **Rinvex Attributes** implementation, a Value instance will represent the content of an attribute related to a particular entity instance.

Values are stored in different tables based on their data type. String values will be stored in a table called (by default) `attribute_varchar_values`, while integer values would use `attribute_integer_values` instead, and so on. Both tables' columns are identical except the data type of the `content` column which is adapted to the data type they store.

### The performance loss

EAV modeling is known for its lack of performance. It is also known for its complexity in terms of querying data if compared with the cost of querying any other horizontal structure. This paradigm has been tagged as anti-pattern in many articles and there is a lot of polemic about whether it should be used.

Since we are storing our entity, attribute and value in different tables, it's required to perform multiple queries to perform any operation. This means if we have 4 attributes registered for an entity, the package will perform at least 5 queries:

```php
select * from `companies`
select * from `attribute_varchar_values` where `attribute_id` = '1' and `attribute_varchar_values`.`entity_id` in ('1', '2', '3', '4', '5') and `eav_attribute_varchar_values`.`entity_type` = 'App\Models\Company'
select * from `attribute_varchar_values` where `attribute_id` = '2' and `attribute_varchar_values`.`entity_id` in ('1', '2', '3', '4', '5') and `eav_attribute_varchar_values`.`entity_type` = 'App\Models\Company'
select * from `attribute_varchar_values` where `attribute_id` = '3' and `attribute_varchar_values`.`entity_id` in ('1', '2', '3', '4', '5') and `eav_attribute_varchar_values`.`entity_type` = 'App\Models\Company'
select * from `attribute_varchar_values` where `attribute_id` = '4' and `attribute_varchar_values`.`entity_id` in ('1', '2', '3', '4', '5') and `eav_attribute_varchar_values`.`entity_type` = 'App\Models\Company'
```

**But, there's Good News!** **Rinvex Attributes** utilizes **Rinvex Cacheable** which caches model results transparently, and may reduce these queries to only one or even ZERO queries! Yes, it's possible and already implemented by default!!

### The gained flexibility

However, despite the performance issues, EAV provides a very high flexibility. It let us have dynamic attributes that can be added / removed at any time without affecting database structure. It also helps when working with columns that will mainly store `NULL` values.

Considering you accept the lack of performance EAV comes with, the package has been developed with flexibility in mind so at least you can fight that performance issue. Performance could be improved by loading all the entity related values in a single query and letting a bit of PHP logic organize them into relationships but decided not to, in favor of making database querying more flexible.

As explained below, this package loads the entity values as if they were custom Eloquent relationships. Is for this reason we can easily query through them as if they were a regular Eloquent relation.

Loading values as relationships will let us load only those values we may require for a certain situation, leaving some others just unloaded. It will also let us make use of the powerful Eloquent tools for querying relations so we could easily filter the entities we are fetching from database based on conditions we will directly apply to the values content.

### More technical details

#### `Rinvex\Attributes\Traits\Attributable`

This trait is the most important and let the other classes play together.

It has the responsibility of handling the interactions within the entity. This trait performs the `set` and `get` operations of the EAV attributes, calls the `RelationBuilder` class which adds the relation methods to the `$entityAttributeRelations` array. These relations may be called as usual as we are overriding the magic method `__call` looking for these calls. It’s responsible for setting the event listeners for saving and deleting, add the global scope and fetch the attributes related to this entity.

When trying to access an entity attribute, if it corresponds to an EAV attribute, this trait contains the logic for providing its value, create a new value instance, update collections or any other set/get interaction.

When reading values there are not too much things to check, if the value exists, we'll just format and provide it, otherwise we'll return null or empty collections.

When setting values it gets a little bit more complex. We have 3 things to consider at the moment when setting values:

- Setting a single value which does not exist in database so we have to create the new model instance and relate to attribute and entity.
- Update the content for an existing single value model (database row).
- Replace an existing (or empty) collection of values with a new one so we have to trash the previous stored values (delete from database).

It also overrides few entity methods such as `bootIfNotBooted`, `relationsToArray`, `setRelation`, `getRelationValue` to provide a smooth and seamless integration with Eloquent models in every possible way. It wires everything together.

```php
// To build entity relations for every instance
bootIfNotBooted();

// To include attributes as relations when converting to array/json
relationsToArray();

// To link entity & attribute to value collections (multi-valued attributes)
setRelation()

// To let Eloquent use our attribute relations as part of the model
getRelationValue()
```

#### `Rinvex\Attributes\Support\RelationBuilder`

This class creates the Eloquent relations to the attribute values based on their type. If they are multi-valued, it will provide a `hasMany` relation, otherwise just a `hasOne`. This class creates closures that return this kind of relations and may be called straight from the entity model. These closures are stored in `$entityAttributeRelations` property in the `\Rinvex\Attributes\Traits\Attributable` trait.


## Installation

1. Install the package via composer:
    ```shell
    composer require rinvex/laravel-attributes
    ```

2. Publish resources (migrations and config files):
    ```shell
    php artisan rinvex:publish:attributes
    ```

3. Execute migrations via the following command:
    ```shell
    php artisan rinvex:migrate:attributes
    ```

4. Done!


## Usage

### Add EAV to eloquent model

**Rinvex Attributes** has been specially made for Eloquent and simplicity has been taken very serious as in any other Laravel related aspect. To add EAV functionality to your Eloquent model just use the `\Rinvex\Attributes\Traits\Attributable` trait like this:

```php
class Company extends Model
{
    use \Rinvex\Attributes\Traits\Attributable;
}
```

That's it, we only have to include that trait in our Eloquent model!

### Core types

```php
\Rinvex\Attributes\Models\Type\Text::class
\Rinvex\Attributes\Models\Type\Boolean::class
\Rinvex\Attributes\Models\Type\Integer::class
\Rinvex\Attributes\Models\Type\Varchar::class
\Rinvex\Attributes\Models\Type\Datetime::class
```

### Register your types

**Rinvex Attributes** does NOT register any types by default as this is considered implementation details, so it's up to you to register the core types listed above, or extend them and only register your custom types.

```php
use Rinvex\Attributes\Models\Attribute;

Attribute::typeMap([
    'varchar' => Rinvex\Attributes\Models\Type\Varchar,
    // ...
    'custom' => \Path\To\Your\Type::class,
]);
```

> **Note**: While you can register your custom types from anywhere in your application, it's recommended to do so in your service provider's `boot` method.

### Register your entities

```php
// Push your entity fully qualified namespace
app('rinvex.attributes.entities')->push(\Path\To\Your\Entity::class);

// Or push the morph class alias if any
app('rinvex.attributes.entities')->push('entity');
```

You can call the `'rinvex.attributes.entities'` service from anywhere in your application, and anytime in the request lifecycle (preferred inside the `boot` method of a service provider). It's a singleton object, holds a pure Laravel [Collection](https://laravel.com/docs/master/collections).

### Create new attribute

Like any normal Eloquent model you can create attributes as follows:

```php
app('rinvex.attributes.attribute')->create([
    'slug' => 'size',
    'type' => 'varchar',
    'name' => 'Product Size',
    'entities' => ['App\Models\Company', 'App\Models\Product'],
]);
```

### Manage attribute entities

Whenever you need to get entities attached to a specific attribute, you can do as follows:

```php
$attribute = app('rinvex.attributes.attribute')->find(1);

// Get attribute entities collection
$attribute->entities

// Get attribute entities query builder
$attribute->entities();

// Delete attached attribute entities
$attribute->entities()->delete();

// Attach attribute entities
$attribute->entities()->createMany([
    [...],
    [...],
    [...],
]);

// Alternative way of attaching attribute entities
$attribute->fill([
    'entities' => ['App\Models\Company', 'App\Models\Product'],
])->save();

// Get all attribute values of type varchar
$values = $attribute->values('varchar')->get();
```

### Assigning values

You can treat your newly created custom attributes like normal ones, yeah! All attributes are created equal!! :smile:

You need a proof? OK, see the following examples where we suppose `price` to be a custom attribute we just created & linked to our `\App\Models\Product` model:

```php
// Single value assignment
$product = \App\Models\Product::find(1);
$product->price = 123;
$product->save();

// Mass assignment
$product = \App\Models\Product::find(1);
$product->fill(['price' => 123])->save();
```

Yes, just like that. Easy! You can work with custom attributes like normal attributes, no difference. All the good stuff you know about eloquent applies here too, whether you are updating single field, mass assigning, creating, or updating, **it just works!**

#### `Rinvex\Attributes\Support\ValueCollection`

**Rinvex Attributes** let you register multi-valued attributes. In order to make playing with collections easier, we have included a new collection type which just extends `Illuminate\Database\Eloquent\Collection` and provide some extra functionality. This class let us add and remove values from the attribute. What it basically does is to let the user play with a collection class without having to worry about creating Value model instances. A bit of code will help here:

```php
// This is how it works
$entity->cities->add('Alexandria');

// And this is what you would have to do without this collection:
$value = new Varchar(['content' => 'Alexandria', 'attribute_id' => 1, 'entity_type' => 'App\Models\Company', 'entity_id' => 1]);
$entity->cities->push($value);

// You could also pass an array
$entity->cities->add(['Alexandria', 'Cairo']);
```

Collections may get improved and add more features but enough for the moment. Value base model replaces the Eloquent method`newCollection` method in order to return this type of collections when playing with multi-valued attributes.

### Querying models

**Rinvex Attributes** tries to do everything in the same way Eloquent would normally do. When loading a model it internally creates a regular relationship for every entity attribute. This means we can query filtering by our registered attribute values like we would normally do when querying Eloquent relationships:

```php
// Cities is an entity attribute
$companies = Company::whereHas('Cities', function (\Illuminate\Database\Eloquent\Builder $builder) {
    $builder->where('content', 'Alexandria');
})->get();
```

Or simply use the builtin query scope as follows:

```php
$companies = Company::hasAttribute('Cities', 'Alexandria')->get();
```

And of course you can fetch entity attributes as normal Eloquent attributes, or as raw relations:

```php
$company = Company::find(1);

// Get entity attributes
$company->cities;

// Get entity raw relation
$company->cities();
```

### Eager loading

**Rinvex Attributes** takes into account the powerful Eloquent eager loading system. When accessing an entity attribute in an Eloquent model, it will be loaded just in time as Eloquent does when working with relationships. However we can work with **Rinvex Attributes** using Eloquent eager loading for better performance and to avoid the n+1 query problem.

**Rinvex Attributes** has a special relationship name reserved for loading all the registered attributes. This relationship is called `eav`. When using `eav` for loading values, it will load all the attributes related to the entity we are playing with, as if you explicitly included all relations in the `$with` model property.

#### Lazy eager loading

Again, as any regular Eloquent relationship we can decide when to load our attributes. Do it as if you were normally loading a relationship:

```php
$company->load('eav');
$company->load('cities', 'colors');
```

#### Autoloading with $with

Eloquent ships with a `$with` which accepts an array of relationships that should be eager loaded. We can use it as well:

```php
namespace App\Models;

use Rinvex\Attributes\Traits\Attributable;

class Company extends Model
{
    use Attributable;

    // Eager loading all the registered attributes
    protected $with = ['eav'];

    // Or just load a few of them
    protected $with = ['cities', 'colors'];
}
```

> **Note:** If your model eager loads `eav` relation, and it's been queued for notification sending, this may cause some issues since the `eav` relation is being evaluated through a global scope, while the `SerializesAndRestoresModelIdentifiers` trait which is used for queued notifications unserialize the queued models WITHOUT global scopes, so you will get "Call to undefined relationship [eav] on model [App\Models\Company]" exception.


## Changelog

Refer to the [Changelog](CHANGELOG.md) for a full history of the project.


## Support

The following support channels are available at your fingertips:

- [Chat on Slack](https://bit.ly/rinvex-slack)
- [Help on Email](mailto:help@rinvex.com)
- [Follow on Twitter](https://twitter.com/rinvex)


## Contributing & Protocols

Thank you for considering contributing to this project! The contribution guide can be found in [CONTRIBUTING.md](CONTRIBUTING.md).

Bug reports, feature requests, and pull requests are very welcome.

- [Versioning](CONTRIBUTING.md#versioning)
- [Pull Requests](CONTRIBUTING.md#pull-requests)
- [Coding Standards](CONTRIBUTING.md#coding-standards)
- [Feature Requests](CONTRIBUTING.md#feature-requests)
- [Git Flow](CONTRIBUTING.md#git-flow)


## Security Vulnerabilities

If you discover a security vulnerability within this project, please send an e-mail to [help@rinvex.com](help@rinvex.com). All security vulnerabilities will be promptly addressed.


## About Rinvex

Rinvex is a software solutions startup, specialized in integrated enterprise solutions for SMEs established in Alexandria, Egypt since June 2016. We believe that our drive The Value, The Reach, and The Impact is what differentiates us and unleash the endless possibilities of our philosophy through the power of software. We like to call it Innovation At The Speed Of Life. That’s how we do our share of advancing humanity.


## License

This software is released under [The MIT License (MIT)](LICENSE).

(c) 2016-2019 Rinvex LLC, Some rights reserved.
