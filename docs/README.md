# FormatPHP Documentation

## Getting Started

It's probably best to do this in a virtualenv environment, so set one up first:

``` bash
pip install virtualenvwrapper
mkvirtualenv formatphp-docs
cd docs/
workon formatphp-docs
pip install -r requirements.txt
```

## Building the Docs

To build the docs, change to the `docs/` directory, and make sure you're working
on the virtualenv environment created in the last step.

``` bash
cd docs/
workon formatphp-docs
make html
```

Then, to view the docs after building them:

``` bash
open _build/html/index.html
```
