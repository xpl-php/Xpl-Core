# Xpl

**Generic helper library for PHP.**

This library contains interfaces and classes that I find myself using on many (maybe most) projects.

It focuses on consistency, extensibility, and performance, with the goal of easing development of almost any PHP project.


## Interfaces


### `Xpl\Arrayable`
*Contract for an object that can be converted/cast to a native PHP array.*

###### Methods
`toArray() : array`

----

### `Xpl\Freezable`
*Contract for an object that can be "frozen" in a particular state.*

###### Methods
`freeze()`

`unfreeze()`

`isFrozen() : bool`

----

### `Xpl\Hydratable`
*Contract for an object that can be hydrated (filled) from an iterable.*

###### Methods
`hydrate(iterable $data)`

----

### `Xpl\Immutable`
*Contract for an immutable object.*

###### Methods
_Tagging interface only (no methods)_


## Utility Classes

**Utility classes are defined *abstract* and contain only static methods.**


### `Xpl\Arr`
*Array utilities.*

###### Methods

`from(mixed $var) : array`

`hydrate(array $array, $data) : array`

`isIndexed(array $array) : bool`

`isAssociative(array $array) : bool`

`first(array $array, int $number = null) : mixed`

`last(array $array, int $number = null) : mixed`

`initial(array $array, int $number = 1) : array`

`rest(array $array, int $offset = 1) : array`

`unique(array $array, callable $filter = null) : array`

`map(array $array, callable $callback) : array`

`mapKeys(array $array, callable $callback) : array`

`filter(array $array, callable $callback = null) : array`

`filterKeys(array $array, callable $callback = null, bool $negate = false) : array`

`without(array $array, array $ignore) : array`

`withoutKeys(array $array, array $keys) : array`

`all(array $array, callable $callback) : bool`

`column(array $array, $key) : array`

`mergev(array $arrays) : array`

`keyIndex(array $array, $key) : int|bool`

`get(array $array, string $path, $default = null) : mixed`

`set(array &$array, string $path, $value)`

`exists(array &$array, string $path) : bool`

`delete(array &$array, $path)`

----

### `Xpl\Date`
*Date/time utilities.*

###### Methods
`isValid(DateTimeInterface|string|int $dateTime) : bool`

`time(DateTimeInterface|string|int $time = null) : int`

`make(DateTimeInterface|string|int $time = null, DateTimeZone|string $timezone = null) : DateTimeInterface`

`timezone(DateTimeZone|string $timezone = null) : DateTimeZone`

`format(DateTimeInterface|string|int $dateTime, string $format) : string`

`formatHuman(DateTimeInterface|string|int $dateTime, string $format = null) : string`

`formatSql(DateTimeInterface|string|int $dateTime) : string`

`getMonthName(DateTimeInterface|string|int $dateTime) : string`

---

### `Xpl\Number`
*Number utilities.*

###### Methods

`parse($number) : string`

`parseBytes(string $bytes) : float`

`isMixed($number) : bool`

`natcast($number) : int|float`

`roundsd($number, int $digits) : float`

`format($value, int $precision = 2, bool $use_locale = false) : string`

`formatMoney($value, int $precision = 2) : string`

`humanize($value, int $digits = 4, bool $format = false) : string`

`formatBytes($bytes, bool $binary = true, int $decimals = 2) : string`

----

### `Xpl\Obj`
*Object utilities.*

###### Methods

`from($value) : object`

`isEmpty($object) : bool`

`toArray($object) : array`

`toIterable($object) : iterable`

`hydrate($object, $data)`

`count($object) : int`

`create($class, array $args = null) : object`

`className($class) : string`

`classBasename($class) : string`

`classNamespace($class) : string`

`uses($object, bool $recursive = false) : array`

`get($object, string $path, $default = null) : mixed`

`set($object, string $path, $value)`

`exists($object, string $path) : bool`

`delete($object, string $path)`

----

### `Xpl\Path`
*Path utilities.*

###### Methods

`normalize(string $path) : string`

`stripTrailingSlash(string $path) : string`

`addTrailingSlash(string $path) : string`

`join(...$segments) : string`

`isUrl(string $path) : bool`

`isAbsolute(string $path) : bool`

`isDotFile(string $path) : bool`

`parent(string $path, int $levels = 1) : string`

`filename(string $path) : string`

`extension(string $path) : string`

`relative(string $path, string $base)`

`resolve(string $path) : string`

----

### `Xpl\Str`
*String utilities.*

###### Methods

`startsWith(string $haystack, string $needle, bool $match_case = true) : bool`

`endsWith(string $haystack, string $needle, bool $match_case = true) : bool`

`contains(string $haystack, $needles) : bool`

`containsChars(string $string, string $charmask) : bool`

`isMultibyte(string $string) : bool`

`isJson(string $string) : bool`

`isXml(string $string) : bool`

`toBool(string $string) : bool`

`toAlnum(string $string) : string`

`format(string $string, string $template) : string`

`formatPhoneNumber(string $phone) : string`

`pearCase(string $string) : string`

`snakeCase(string $string) : string`

`studlyCase(string $string) : string`

`camelCase(string $string) : string`

`normalizeLineEndings(string $string) : string`

`collapseWhitespace(string $string) : string`

`stripQuotes(string $string) : string`

`stripControlChars(string $string) : string`

`stripInvalidUnicode(string $string) : string`


## Traits
**Generic traits, mainly focused on properties.**

### `Xpl\Traits\PropertyAccessReadable`

*Allows readable property access via magic methods.*

###### Methods
`__get($key)`

`__isset($key)`

----

### `Xpl\Traits\PropertyAccessWritable`

*Allows writable property access via magic methods.* (Use with caution)

###### Methods
`__set($key, $value)`

`__unset($key)`

----

### `Xpl\Traits\PropertyArrayable`

*Implements `Xpl\Arrayable` using properties.*

###### Methods
`toArray() : array`

----

### `Xpl\Traits\PropertyArrayAccess`

*Implements `ArrayAccess` using properties.*

###### Methods
`offsetGet($offset)`

`offsetExists($offset)`

`offsetSet($offset, $value)`

`offsetUnset($offset)`

----

### `Xpl\Traits\PropertyHydratable`

*Implements `Xpl\Hydratable` using properties.*

###### Methods
`hydrate(iterable $data)`

----

### `Xpl\Traits\PropertySerializable`

*Implements `Serializable` using properties.*

###### Methods
`serialize()`

`unserialize($serial)`

----

### `Xpl\Traits\Singleton`

*Implements the singleton (anti-) design pattern.*

###### Methods
`static instance(...$args) : Singleton`

`protected __construct()`

`private __clone()`

`private __wakeup()`
