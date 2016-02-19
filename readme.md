# WordPress Router

This plugin will really only be helpful to developers. It abstracts much of the WordPress Rewrite API away from you to allow you to more easily declare routes and have them execute callbacks that you define in your own theme/plugin code.

## What the developer needs to know

You can define routes in terms of the URL you want your code to be called for.  You can also specify an HTTP method (if none is supplied then `GET` is assumed).

If you would like a `GET` request to `/test/` to execute the function `mytest()` then you would use this

```
$PragRouter->addRoute('test', 'mytest');
```

You can pass it any [PHP Callable](http://php.net/manual/en/language.types.callable.php).  This means that methods on your objects are fair game as a callback.

In terms of what you return, the router accommodates several cases:

* Boolean value -- If `TRUE` is returned then an empty page is going to be rendered (using the default page template for your active WordPress theme). If `FALSE` is returned, then WordPress will display a 404 page.
* String value -- If a string is returned, then the page title is set to an empty string, and whatever string you returned will be in place of the content.
* Array value -- Returning an array allows you to control the title and content of the page that will be displayed to the user.  An example of a returned array is

```
Array (
    [title] => mytitle
    [content] => mycontent
)
```

It is worth noting that your callback can do whatever it wishes, and then simply exit if for instance you wanted to render JSON and then prevent the WordPress page template being rendered.

See `plugin-example.php` for two simple code examples of how the router can be called.

## Copyright and License

This project is licensed under the [GNU GPL](http://www.gnu.org/licenses/old-licenses/gpl-2.0.html), version 2 or later.

Copyright &copy; 2016 [Matt Walters](http://www.mattwalters.net).
