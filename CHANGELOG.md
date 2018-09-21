# Rinvex Attributes Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


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
- Move frontend stuff to cortex/attributes from rinvex/attributes
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
- Rename package rinvex/attributes from rinvex/attributable

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

[v0.0.7]: https://github.com/rinvex/attributes/compare/v0.0.6...v0.0.7
[v0.0.6]: https://github.com/rinvex/attributes/compare/v0.0.5...v0.0.6
[v0.0.5]: https://github.com/rinvex/attributes/compare/v0.0.4...v0.0.5
[v0.0.4]: https://github.com/rinvex/attributes/compare/v0.0.3...v0.0.4
[v0.0.3]: https://github.com/rinvex/attributes/compare/v0.0.2...v0.0.3
[v0.0.2]: https://github.com/rinvex/attributes/compare/v0.0.1...v0.0.2
