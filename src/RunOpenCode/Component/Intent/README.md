Intent Component
================

_This is a **read-only** repository of mono-repository sub-split
from [https://github.com/RunOpenCode/phplib](https://github.com/RunOpenCode/phplib). Do not send PR or report issues
against this repository, use the one referenced with previously given URL._

A library for temporary storing intents (messages/commands/states) which needs to be preserved between stateless
requests.

Password reset is a typical example: user requests a password reset, receives an email containing a link and, at some
point in time, clicks on that link in order to complete the process which has been started within some earlier request.
Session storage is not always an option here, since the request which completes the use case may originate from a
different browser, a different device, or even a different machine.

This library allows you to store any serializable object into a persistent storage and to retrieve it later by using a
randomly generated identifier which you may safely put into an URL:

- **Store any serializable object** and retrieve it later by using its identifier.
- **Time to live** is defined per intent, after which intent is no longer available and is removed from the storage.
- **Deferred availability** allows you to store an intent which becomes available at some moment in the future.
- **Invalidated on read** by default, so a single intent may be used only once, which is a sane default for one time
  links.
- **Doctrine Dbal and PSR-6 storages** are provided out of the box, while other storages may be added with ease.

For usage within Symfony applications, see [Intent Bundle](https://github.com/RunOpenCode/intent-bundle).

## Resources

- [Report issues and suggest features](https://github.com/RunOpenCode/phplib/issues)
- [Send pull requests](https://github.com/RunOpenCode/phplib/pulls)
- [Changelog](https://github.com/RunOpenCode/phplib/blob/master/CHANGELOG)
- [License](https://github.com/RunOpenCode/phplib/blob/master/LICENSE)
