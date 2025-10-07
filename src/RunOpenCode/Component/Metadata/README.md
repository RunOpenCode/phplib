Metadata Component
==================

_This is a **read-only** repository of mono-repository sub-split
from [https://github.com/RunOpenCode/phplib](https://github.com/RunOpenCode/phplib). Do not send PR or report issues
against this repository, use the one referenced with previously given URL._

Metadata is a small utility library which allows us to search for metadata (attributes) in classes/properties/methods.
Instead of using PHP's native reflection API and manual traversal, this library uses caching and is optimized for
performance.

In general, this library is helpful in providing, in example, these questions:

- Does this class have specific attribute? Give me that attribute instance.
- Does this class have property/method with specific attribute? Give me that property/method and attribute instance.
- Give me all properties/methods with specific attribute.
- etc.

Using this library, it is trivial to implement various metadata-based features, such as serialization, validation,
mapping, etc. Doctrine behaviors, such as `Timestampable`, `Blameable`, etc. can be implemented using this library
as well, with less boilerplate code.

## Resources

- [Report issues and suggest features](https://github.com/RunOpenCode/phplib/issues)
- [Send pull requests](https://github.com/RunOpenCode/phplib/pulls)
- [Changelog](https://github.com/RunOpenCode/phplib/blob/master/CHANGELOG)
- [License](https://github.com/RunOpenCode/phplib/blob/master/LICENSE)
