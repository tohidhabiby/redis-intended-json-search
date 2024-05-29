This Symfony bundle is designed as a solution for storing and searching data using a Redis stack and JSON format. It provides the capability to store data as JSON in Redis and enables searching and retrieving data through various interfaces.

Not only does this bundle offer features such as data storage and retrieval, but it also enhances its performance and usability by supporting JSON indentation.

If you are not familiar with the redis stack let me make it simple for you:
- `index` is as same as `table` in RDBs and the `Index` classes that you will create are as same as `model` or `entity` in Laravel or Symfony.
- `createIndex` and `dropIndex` functions are the same as `up` and `down` functions in migrations.
- And this package is created to be an ORM as same as eloquent or doctrine.

## Requirements
- PHP 8.0 and above.
- PHP-Redis extension.
- Redis Stack Server7.2 or above.

## Installation
- `composer require tohidhabiby/redis-intended-json-search`
- Add `TohidHabiby\RedisIntendedJsonSearch\TohidHabibyRedisIntendedJsonSearchBundle::class` into your `config/bundles.php` or add it as a service into your service provider.
- Add Redis Stack details into your environments (`REDIS_HOST`, `REDIS_PORT`, `REDIS_TIMEOUT`, `REDIS_PERSISTENT_ID`, `REDIS_RETRY_INTERVAL`, `REDIS_READ_TIMEOUT`)

### Usage
* Index

At the beginning you must create an index for each type of entity which you want to store it.
It must be implemented by `TohidHabiby\RedisIntendedJsonSearch\Indices\IndexInterface`.
To connect and run queries on the index documents, for each index you must create a repository.
* Repository

Repositories must be extended by `TohidHabiby\RedisIntendedJsonSearch\Repositories\AbstractRepository`.
Indices must have an attribute which connect them to their repository
`#[Index(BlogRepository::class)]`.
* Field Type

You can define any property to these indices, but they must define as a public property and if you want to run a query on them you must define the type file.
You can see the field types in the `src/FieldTypes` directory.
- if you want to store array of numbers or strings you can use `NumericField` and `TextField` definition attributes.

*** If you want to store an object inside another object, you must use one of the following field types:
- `IndexField`
- `CollectionField` : to define a collection of objects.

## Builder Function Explanation
- `createIndex(IndexInterface $index, ?int $childDeepLevel = 1): bool` : to create an index you can use this function, you must define an index first, then you can run query on it (it looks like a migration in RDB) ([Reference](https://redis.io/docs/interact/search-and-query/indexing/)).
- `select(array $columns): BuilderInterface` : to select only some special columns ([Reference](https://redis.io/docs/interact/search-and-query/query/exact-match/)).
- `save(IndexInterface $index): mixed` : to store a document into an index.
- `sortBy(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface` : to sort your query result ([Reference](https://redis.io/docs/interact/search-and-query/query/range/)).
- `aggregateSort(FieldTypeInterface $field, ?string $direction = 'asc'): BuilderInterface` : it's a very useful function and using this function is a little bit tricky ([Reference](https://redis.io/docs/interact/search-and-query/query/aggregation/))
- `getInfo(IndexInterface $index): array` : to check an index is exists and get information about it.
- `limit(int $limit, ?int $offset = 0): BuilderInterface` : to limit the result.
- `whereBetweenNumbers(FieldTypeInterface $fieldType, int|float|null $min = null, int|float|null $max = null): BuilderInterface` : this function uses to find a documents which the mentioned column is between a maximum and minimum value ([Reference](https://redis.io/docs/interact/search-and-query/advanced-concepts/query_syntax/))
- `groupBy(array $fields): BuilderInterface` : for the group by a query on a column, it must be used with aggregation function ([Reference](https://redis.io/docs/interact/search-and-query/query/aggregation/)). 
- `get(): array` : to get the result as an array.
- `getIndexList(): array` : to get the list of exists indices.
- `getDocument(IndexInterface $index): string|bool` : to get a document with it's ID.
- `dropIndex(IndexInterface $index): bool` : to drop an exists index.
- `whereInNumbers(FieldTypeInterface $field, array $values): BuilderInterface` : to check a number is exists in a numeric array.
- `whereInArrayText(FieldTypeInterface $field, array $values): BuilderInterface` : to check a text is exists in a string array.
- `deleteDocument(IndexInterface $index): bool` : to delete a document from an index.
- `whereTextFieldLike(FieldTypeInterface $field, string $value): BuilderInterface` : it's similar to `LIKE` query in SQL, consider that Redis Stack doesn't cover all the characters. If you want to search some specific strings, it's better to store them in another property and with someway encode them(for example base64) then search the encoded string.
- `max(FieldTypeInterface $field): mixed` : to get a maximum value of a numeric property in an index.
- `min(FieldTypeInterface $field): mixed` : to get a minimum value of a numeric property in an index.
- `getFirst(): array` : get the first object in the result.

## Repository Functions Explanation
- `fill(array $data): RepositoryInterface` : to prepare a simple document with an array.
- `fillWithRelations(array $data): RepositoryInterface` : if you want to have an entity into another one you must define two different indices, and whenever you want to have the parent object, you must implement this function into your created repository and create the child object into it (check the sample code).
- `saveWithRelations(array $data): IndexInterface` : as same as the previous function and this function store the document into the index.
- `getDocumentById(int $id): array|bool` : no need to more explanation.
- `getFieldByName(string $name): FieldTypeInterface` : to fetch a field as an object that defined into the index.
- `getRepositoryByIndexClass(string $className): RepositoryInterface` : to get another repositor by it's index class name.
- The other function that you can see in the `TohidHabiby\RedisIntendedJsonSearch\Repositories\AbstractRepository` class, call the related function from the Builder, which I have already explained them.


