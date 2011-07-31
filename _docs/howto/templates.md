Templates and Helpers
=====================

If you choose to use the library views package it includes a PHP-language templating engine, the core class of which is T_Template_File. The class loads a text file and executes it as PHP, making available any set class attributes.

    [path/to/template.tpl]
    <html>
    <head>
      <title><?= $title=htmlentities($this->title); ?></title>
    </head>
    <body>
      <h1><?= $title; ?></h1>
    </body>
    </html>

If short_tags is *not* enabled in php.ini, the template files are compiled before execution, so it is safe and portable to use the succinct short tag notation in your templates.

    <?php
    // create the template
    $tpl = new T_Template_File('path/to/template.tpl');
    $tpl->title = 'Some Title';

    // render with either
    $content = $tpl->__toString();
    // or to send to the output buffer
    $tpl->toBuffer();
    ?>

The attributes can be any variable, and you can nest templates by setting the attribute of one as another. Note that attributes are set on a per-template basis and the attributes set on the parent template are not available in the nested child templates.

    <?php
    // e.g. nested templates
    $tpl = new T_Template_File('master.tpl');
    $tpl->title = 'some title';

    $tpl->primary = new T_Template_File('primary.tpl');
    $tpl->primary->content = 'some content';

    $tpl->sidebar = new T_Template_File('sidebar.tpl');
    ?>

Helpers
-------

Template helpers are functions that can be used in your templates to either process information, or to provide access to values across a number of templates. A helper can be any valid PHP callback: either a function name, an array of object and method name, or in PHP5.3 a lambda function or closure.

    [path/to/template.tpl]
    <html>
    <head>
      <title><?= $title=$this->escape($this->title); ?></title>
      <link rel="stylesheet"
            href="<?= $this->escape($this->root()->getUrl()); ?>/screen.css" />
    </head>
    <body>
      <h1><?= $title; ?></h1>
    </body>
    </html>

    <?php
    $env = new T_Environment_Http;

    // create and set attributes
    $tpl = new T_Template_File('path/to/template.tpl');
    $tpl->title = 'Some Title';

    // define helpers
    $tpl->addHelper(array($env,'getAppRoot'),'root')
        ->addHelper(array(new T_Filter_Xhtml,'transform'),'escape');

    // render
    $tpl->toBuffer();
    ?>

The scope of these helpers is important: the helper function is available in the template it is defined upon, and **in any nested template** (i.e. that has been set as an attribute). While the helper functions are available in the child templates, they can be explicitally overwritten by setting another helper of the same name on the child template (i.e. it works like an inheritance tree).

In-Built Helpers
----------------

A number of in-built library helpers are available, and if you wish you can define you own global helpers by extending T_Template_File and adding class methods.

### Buffer Helper

When nested child templates are rendered in a template they can be echo-ed because they implement the __toString() PHP magic method. However, a limitation of PHP means exceptions cannot be thrown within the __toString() method which makes it's use undesirable. Instead, a toBuffer() method is available on all views. The globally available buffer() template helper is simply a shortcut which checks whether the value passed to it is a view (in which case it calls toBuffer), or if not it echos the input. It can be regarded as an exception-safe way to echo any attribute in a template.

    <?php
    $tpl = new T_Template_File('some/template.tpl');
    $tpl->title = 'Some Title';
    $tpl->content = new T_Template_File('content.tpl');
    ?>

    [some/template.tpl]
    <h1><? $this->buffer($this->title); ?></h1>
    <? $this->buffer($this->content); ?>

### Partial Helper

A nested template can be set as a template attribute when building the template itself, but sometimes it is more convenient (and encapsulates the template itself better) if the template file nests templates itself.

    [tpl/dir/partial.tpl]
    <?= $this->product->getName(); ?>
    <span class="price">(<?= $this->product->getPrice()); ?>)</span>

    [tpl/dir/list.tpl]
    <ul>
    <? foreach ($this->products as $p) : ?>
      <li><? $this->partial('partial',array('product'=>$p)); ?></li>
    <? endforeach; ?>
    <ul>

    <?php
    $tpl = new T_Template_File('tpl/dir/list.tpl');
    $tpl->products = $products;
    ?>

Note that:

* The first argument is normally the path of the template file **relative to the current file** without the extension (the same extension as the current template is assumed). Therefore just a template name would pick up a template in the same directory as the current one, with the same extension.
* If you want to render a template in a totally different location or with a different extension, the 1st argument can also be the full path to a template.
* The helpers of the parent template are available in the partial template, but the attributes are not. Attributes for the partial template can be defined in key-value array pairs in the 2nd argument to the helper.

### Loop Helper

The loop helper is a extension of the partial helper and is a shortcut when you need to repeatedly render a partial template in an iterative loop.

    [tpl/dir/loop.tpl]
    <<?= $this->tag; ?>>
    <?= $this->product->getName(); ?>
    <span class="price">(<?= $this->product->getPrice()); ?>)</span>
    </<?= $this->tag; ?>>

    [tpl/dir/list.tpl]
    <ul>
    <? $this->loop('loop','product',$this->products,
                        array('tag'=>'li')); ?>
    <ul>

    <?php
    $tpl = new T_Template_File('tpl/dir/list.tpl');
    $tpl->products = $products;
    ?>

Like the partial helper, the loop helper by default assumes the same extension as the current template and evaluates the template name relative to it's own path, although it also accepts a full filepath string.

### Placeholder Helper

Unlike the buffer, partial and loop which are global helpers that are always available, if you wish to use the placeholder helper you need to explicitally enable it on a template:

    <?php
    $tpl = new T_Template_File('master.tpl');
    $p = new T_Template_Helper_Placeholder();
    $tpl->addHelper(array($p,'get'),'placeholder');
    ?>

The placeholder template allows you to pass information between different template files, and a typical use case would be for example to build a sidebar or similar as you render the rest of a template tree.

    [master.tpl]
    <div id="primary">
    <? $this->buffer($this->content); ?>
    </div>
    <div id="secondary">
    <?= $this->placeholder('secondary'); ?>
    </div>

    [content.tpl]
    <p>This is some main content.</p>
    <? $this->placeholder('secondary')->append(); ?>
    <p>This ends up in the sidebar.</p>
    <? $this->placeholder('secondary')->stop(); ?>

    <?php
    $tpl = new T_Template_File('master.tpl');
    $tpl->content = new T_Template_File('content.tpl');
    ?>

Note that:

* Placeholder content capture can be started either with append() (to append, as used in the example above), prepend() (to prepend), or capture() (to replace any existing content), and must always be finished with the stop() method.
* The placeholder system uses template output buffering to make sure that all placeholder content is included, whether the placeholder is placed in a template before or after content is added. Thus the order of the placeholder placement and content addition does not matter.

Locating Your Templates
-----------------------

The T_Template_File class expects a path to a template file in its constructor that is either absolute, relative to the current directory or somewhere on the include path (i.e. includable). You can handle locating your template files yourself, or you can use the registration and location abilities of the library [/how-to/environments environment].

    <?php
    // bootstrap
    $env = new T_Environment_Http;
    $env->addRule(new T_Find_FileInDir('tpl/dir/','tpl'));

    // ... elsewhere ...
    $tpl = new T_Template_File($env->find('master','tpl'));
      // loads the tpl/dir/master.tpl template
    ?>

The environment maintains a stack of rules, where the last rule added takes precendence over previous rules. If you have multiple templates with the same name, it is possible to override existing templates by adding extra rules to the environment that will look in other template directories first.
