## Phing Behat Task

A Behat task for [Phing](http://www.phing.info/). This task enable usage of Behat commands in Phing build scripts.

Phing provides tools for usual tasks for PHP projects (phplint, jslint, VCS checkouts, files copy or merge, packaging, upload, etc.). Integration of Behat in Phing is particularly useful when building and testing Drupal projects in a continuous integration server such as [Jenkins](http://jenkins-ci.org/), [Travis](https://travis-ci.org/) or [Continuous PHP](https://continuousphp.com/).
 
## Installation

Installation must be done through [Composer](https://getcomposer.org/):

```
  composer require drupol/phingbehattask
```

or by editing your composer.json file and add in the right section:

```json
{
  "require": {
    "drupol/phingbehattask": "dev-master"
  }
}
```

## Usage

To use the Behat task in your build file,  it must be made available to Phing so that the buildfile parser is aware a correlating XML element and it's parameters. This is done by adding a `<taskdef>` task to your build file:

```xml
  <taskdef name="behat" classname="\Phing\Behat\Task" />
```

```xml
  <behat bin="${project.basedir}/vendor/behat/behat/bin/behat" haltonerror="yes" colors="yes" verbose="${behat.options.verbosity}">
    <option name="config=">${project.basedir}/tests/behat.yml</option> 
  </behat>
```

See the [Phing documentation](http://www.phing.info/docs/guide/stable/chapters/appendixes/AppendixB-CoreTasks.html#TaskdefTask) for more information on the `<taskdef>` task.
