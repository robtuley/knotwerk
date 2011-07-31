Knotwerk PHP Lib
================

This code forms a "full-stack framework": no dependencies are required for a full MVC implementation: URL to controller routing, templated views and a collection of common model functionality. The primary focus is form generation, validation and rendering. 

It has been written as a learning exercise, and is *not recommended for any use other than experimentation*, as it is no longer being actively developed/maintained.

The code consists of a number of loosely coupled "packages", of most interest are:

* `controllers/`: controller chaining mechanism with throwable responses.
* `db/`: super-thin DB access layer.
* `forms/`: comprehensive form handling library with tools to build, validate, protect and render.
* `views/`: php-in-HTML templating mechanism with features such as partials, placeholder, etc.
* `wiki/`: simple wiki-esk text langauge parsing and display engine.

Some limited form examples are in `_demo/`, and further documentation is contained in the root package directory `README.md` and in the top-level `_docs/` directory.
