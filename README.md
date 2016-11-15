## Phing Behat Task

A Behat task for [Phing](http://www.phing.info/). This task enable usage of Behat commands in Phing build scripts.

Phing provides tools for usual tasks for PHP projects (phplint, jslint, VCS checkouts, files copy or merge, packaging, upload, etc.). Integration of Behat in Phing is particularly useful when building and testing Drupal projects in a continuous integration server such as [Jenkins](http://jenkins-ci.org/), [Travis](https://travis-ci.org/) or [Continuous PHP](https://continuousphp.com/).
 
## Installation

Installation must be done through [Composer](https://getcomposer.org/):

```
  composer require drupal/phingbehattask
```

or by editing your composer.json file and add in the right section:

```json
{
  "require": {
    "drupal/phingbehattask": "1.0.3"
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://git.drupal.org/project/phingdrushtask.git"
    }
  ]
}
```


## Usage

To use the Drush task in your build file,  it must be made available to Phing so that the buildfile parser is aware a correlating XML element and it's parameters. This is done by adding a `<taskdef>` task to your build file:

```xml
  <taskdef name="drush" classname="\Phing\Drush\Task" />
```

See the [Phing documentation](http://www.phing.info/docs/guide/stable/chapters/appendixes/AppendixB-CoreTasks.html#TaskdefTask) for more information on the `<taskdef>` task.

Base Drush options are mapped to attribute of the Drush task. Parameters are wrapped in elements. Value of a parameter is defined by the text child of the element. Options are mapped to elements with a name attribute. Value of an option can either be in the value attribute of the element or as text child (like params).

The Drush command for installing Drupal:

`drush site-install --yes --locale=uk --site-name =${sitename} expert`

... would be written in a Phing build file as:

```xml
  <drush command="site-install" assume="yes">
    <option name="locale">uk</option>
    <option name="site-name" value="${sitename}" />
    <param>expert</param>
  </drush> 
```

You may also use Phing properties to configure Drush...

```xml
  <property name="drush.assume" value="yes"/>

  <drush command="site-install">
    <option name="locale">uk</option>
    <option name="site-name" value="${sitename}" />
    <param>expert</param>
  </drush> 
```

It's also possible to add the attributes `escape` and/or `quote` in the ```<param>``` tag.

```xml
<propery name="drush.assume" value="yes"/>
<propery name="drush.root" value="${website.drupal.dir}"/>

<drush command="site-install">
    <option name="db-url" value="${drupal.db.url}"/>
    <option name="site-name" value="${website.site.name}"/>
    <option name="account-name" value="${drupal.admin.username}"/>
    <option name="account-pass" value="${drupal.admin.password}"/>
    <option name="account-mail" value="${drupal.admin.email}"/>
    <param>${website.profile.name}</param>
    <!-- Disable sending of e-mails during installation. -->
    <param escape="no" quote="yes">install_configure_form.update_status_module='array(FALSE,FALSE)'</param>
</drush>
```

More sample usages are provided in the template build script at [reload.github.io/phing-drupal-template](https://reload.github.io/phing-drupal-template/).
