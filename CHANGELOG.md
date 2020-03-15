# Rinvex Attributes Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v4.0.0] - 2020-03-15
- Upgrade to Laravel v7.1.x & PHP v7.4.x

## [v3.0.3] - 2020-03-13
- Significant performance improvement by bypassing Eloquent model magic when attaching attribute relationships to models.
- Tweak TravisCI config
- Add migrations autoload option to the package
- Tweak service provider `publishesResources`
- Remove indirect composer dependency
- Drop using global helpers
- Update StyleCI config

## [v3.0.2] - 2019-12-18
- Fix wrong code sample in readme
- Fix `migrate:reset` args as it doesn't accept --step

## [v3.0.1] - 2019-09-24
- Add missing laravel/helpers composer package

## [v3.0.0] - 2019-09-23
- Upgrade to Laravel v6 and update dependencies

## [v2.1.1] - 2019-06-03
- Enforce latest composer package versions

## [v2.1.0] - 2019-06-02
- Update composer deps
- Drop PHP 7.1 travis test
- Refactor migrations and artisan commands, and tweak service provider publishes functionality

## [v2.0.0] - 2019-03-03
- Rename environment variable QUEUE_DRIVER to QUEUE_CONNECTION
- Require PHP 7.2 & Laravel 5.8
- Apply PHPUnit 8 updates
- Tweak and simplify FormRequest validations
- Fix MySQL / PostgreSQL json column compatibility

## [v1.0.1] - 2018-12-22
- Update composer dependencies
- Add PHP 7.3 support to travis
- Fix MySQL / PostgreSQL json column compatibility

## [v1.0.0] - 2018-10-01
- Enforce Consistency
- Support Laravel 5.7+
- Rename package to rinvex/laravel-attributes

## [v0.0.7] - 2018-09-22
- Fix wrong package name in autoload for testing
- Enforce consistency
- Update PHPUnit options
- Add attribute model factory
- Update PHPUnit options

## [v0.0.6] - 2018-06-20
- Add integration test suites
- Enforce snake_case slugs (fix #51)
- Making entity_id nullable for now (fix #57)

## [v0.0.5] - 2018-06-18
- Update composer dependencies
- Define polymorphic relationship parameters explicitly
- Drop default attribute types registration
- Use global helper functions instead of class based methods
- [WIP] Implement per resource attributes

## [v0.0.4] - 2018-02-18
- Update composer dependencies
- Update supplementary files
- Tweak setEntitiesAttribute mutator
- Add PublishCommand to artisan
- Move slug auto generation to the custom HasSlug trait
- Add missing composer dependencies
- GetMorphClass the right way
- Remove useless scopes
- Fix polymorphic class maps
- Register blade extension for @attributes
- Refactor attribute types registration
- Fix attributable cache issues
- Add PHPUnitPrettyResultPrinter
- Add slug unique index
- Move frontend stuff to cortex/attributes from rinvex/laravel-attributes
- Tweak and fix entity attributes retrieval
- Require PHP v7.1.3
- Fix entities issue and tweak some features
- Remove fillable relation rules
- Fix entity custom primary id issue (fix #30, #26)
- Typehint method returns
- Drop useless model contracts (models already swappable through IoC)
- Sort attributes on retrieval
- Add Laravel v5.6 support
- Simplify IoC binding
- Add force option to artisan commands
- Return eloquent collection always from getEntityAttributes method for compatibility
- Check if attribute_entity database table exists before querying entity attributes
- Drop Laravel 5.5 support

## [v0.0.3] - 2017-09-09
- Fix many issues and apply many enhancements
- Rename package rinvex/laravel-attributes from rinvex/attributable

## [v0.0.2] - 2017-06-29
- Enforce consistency
- Add Laravel 5.5 support
- Update validation rules
- Fix order column datatype
- Tweak collection flag column
- Change integer column length
- Tweak model event registration
- Add required flag column to attributes
- Fix wrong slug generation method order
- Rename sorting column from order to sort_order
- Replace wrong isCollection method with correct corresponding attribute is_collection

## v0.0.1 - 2017-04-08
- Rename package to "rinvex/attributable" from "rinvex/sparse" based on 715a831

[v4.0.0]: https://github.com/rinvex/laravel-attributes/compare/v3.0.3...v4.0.0
[v3.0.3]: https://github.com/rinvex/laravel-attributes/compare/v3.0.2...v3.0.3
[v3.0.2]: https://github.com/rinvex/laravel-attributes/compare/v3.0.1...v3.0.2
[v3.0.1]: https://github.com/rinvex/laravel-attributes/compare/v3.0.0...v3.0.1
[v3.0.0]: https://github.com/rinvex/laravel-attributes/compare/v2.1.1...v3.0.0
[v2.1.1]: https://github.com/rinvex/laravel-attributes/compare/v2.1.0...v2.1.1
[v2.1.0]: https://github.com/rinvex/laravel-attributes/compare/v2.0.0...v2.1.0
[v2.0.0]: https://github.com/rinvex/laravel-attributes/compare/v1.0.1...v2.0.0
[v1.0.1]: https://github.com/rinvex/laravel-attributes/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/rinvex/laravel-attributes/compare/v0.0.7...v1.0.0
[v0.0.7]: https://github.com/rinvex/laravel-attributes/compare/v0.0.6...v0.0.7
[v0.0.6]: https://github.com/rinvex/laravel-attributes/compare/v0.0.5...v0.0.6
[v0.0.5]: https://github.com/rinvex/laravel-attributes/compare/v0.0.4...v0.0.5
[v0.0.4]: https://github.com/rinvex/laravel-attributes/compare/v0.0.3...v0.0.4
[v0.0.3]: https://github.com/rinvex/laravel-attributes/compare/v0.0.2...v0.0.3
[v0.0.2]: https://github.com/rinvex/laravel-attributes/compare/v0.0.1...v0.0.2
