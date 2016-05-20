# WordPress Router

This plugin will really only be helpful to developers. It allows you to more easily declare routes and have them execute callbacks that you define in your own theme/plugin code. From there, you can either take over the rendering of the page entirely, or pass back content to be rendered using the active theme's default page template.

## What the developer needs to know

You can define routes in terms of the URL you want your code to be called for. You can also specify an HTTP method (if none is supplied then `GET` is assumed).

If you would like a `GET` request to `/test/` to execute the function `mytest()` then you would use this

```
add_action('mswwprouter_add_route', function() {
    // Note, the 3rd parameter is optional. If it is not passed, then `GET` is assumed.
    MswWpRouter::addRoute('test', 'mytest', 'GET');
});
```

You can pass it any [PHP Callable](http://php.net/manual/en/language.types.callable.php). This means that methods on your objects are fair game as a callback.

In terms of what you return, the router accommodates several cases:

* Boolean value -- If `FALSE` is returned, then WordPress will display the active theme's 404 page. If `TRUE` is returned, then an empty page is going to be rendered (using the default page template for your active WordPress theme).
* String value -- If a string is returned, then the page title is set to an empty string, and whatever string you returned will be in place of the content.
* Array value -- Returning an array allows you to control the title and content of the page that will be displayed to the user. An example of a valid returned array is:

```
Array (
    [title] => 'mytitle'
    [content] => 'mycontent'
)
```

It is worth noting that your callback can do whatever it wishes, and then simply exit if for instance you wanted to render JSON and prevent the WordPress page template being rendered.

See `plugin-example.php` for two simple code examples of how the router can be called.

## Copyright and License

Copyright (C) 2016 Matthew Walters.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
