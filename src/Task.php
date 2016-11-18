<?php

namespace Phing\Behat;

/**
 * A Behat task for Phing.
 */
class Task extends \ExecTask {

  /**
   * The source file from XML attribute.
   *
   * @var \PhingFile
   */
  protected $file;

  /**
   * All fileset objects assigned to this task.
   *
   * @var array
   */
  protected $filesets = array();

  /**
   * Path the the Behat executable.
   *
   * @var \PhingFile
   */
  protected $bin = 'behat';

  /**
   * Optional path(s) to execute.
   *
   * @var null
   */
  protected $path = NULL;

  /**
   * Specify config file to use.
   *
   * @var null
   */
  protected $config = NULL;

  /**
   * Only execute features which match part of the given name or regex.
   *
   * @var null
   */
  protected $name = NULL;

  /**
   * Only execute features or scenarios with tags matching following filter.
   *
   * @var null
   */
  protected $tags = NULL;

  /**
   * Only execute the features with actor role matching a wildcard.
   *
   * @var null
   */
  protected $role = NULL;

  /**
   * Specify config profile to use.
   *
   * @var null
   */
  protected $profile = NULL;

  /**
   * Only execute a specific suite.
   *
   * @var null
   */
  protected $suite = NULL;

  /**
   * Passes only if all tests are explicitly passing.
   *
   * @var bool
   */
  protected $strict = FALSE;

  /**
   * Increase verbosity of exceptions.
   *
   * @var bool
   */
  protected $verbose = FALSE;

  /**
   * Force ANSI color in the output.
   *
   * @var bool
   */
  protected $colors = TRUE;

  /**
   * Invokes formatters without executing the tests and hooks.
   *
   * @var bool
   */
  protected $dryRun = FALSE;

  /**
   * Stop processing on first failed scenario.
   *
   * @var bool
   */
  protected $haltonerror = FALSE;

  /**
   * All Behat options to be used to create the command.
   *
   * @var Option[]
   */
  protected $options = array();

  /**
   * Set the path to the Behat executable.
   *
   * @param \PhingFile $str
   *   The behat executable file.
   */
  public function setBin(\PhingFile $str) {
    $this->bin = $str;
  }

  /**
   * Set the path to features to test.
   *
   * @param string $path
   *   The path to features.
   */
  public function setPath($path) {
    $this->path = $path;
  }

  /**
   * Sets the Behat config file to use.
   *
   * @param string $config
   *   The config file.
   */
  public function setConfig($config) {
    $this->config = $config;
  }

  /**
   * Sets the name of tests to run.
   *
   * @param string $name
   *   The feature name to match.
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Sets the test tags to use.
   *
   * @param string $tags
   *   The tag(s) to match.
   */
  public function setTags($tags) {
    $this->tags = $tags;
  }

  /**
   * Sets the role able to run tests.
   *
   * @param string $role
   *   The actor role to match.
   */
  public function setRole($role) {
    $this->role = $role;
  }

  /**
   * Set the profile to use for tests.
   *
   * @param string $profile
   *   The profile to use.
   */
  public function setProfile($profile) {
    $this->profile = $profile;
  }

  /**
   * Set the test suite to use.
   *
   * @param string $suite
   *   The suite to use.
   */
  public function setSuite($suite) {
    $this->suite = $suite;
  }

  /**
   * Sets the flag if strict testing should be enabled.
   *
   * @param bool $strict
   *   Behat strict mode.
   */
  public function setStrict($strict) {
    $this->strict = \StringHelper::booleanValue($strict);
  }

  /**
   * Sets the flag if a verbose output should be used.
   *
   * @param bool $verbose
   *   Use verbose output.
   */
  public function setVerbose($verbose) {
    $this->verbose = $verbose;
  }

  /**
   * Either force ANSI colors on or off.
   *
   * @param bool $colors
   *   Use ANSI colors.
   */
  public function setColors($colors) {
    $this->colors = \StringHelper::booleanValue($colors);
  }

  /**
   * Invokes test formatters without running tests against a site.
   *
   * @param bool $dryrun
   *   Run without testing.
   */
  public function setDryRun($dryrun) {
    $this->dryRun = \StringHelper::booleanValue($dryrun);
  }

  /**
   * Sets the flag if test execution should stop in the event of a failure.
   *
   * @param bool $stop
   *   If all tests should stop on first failure.
   */
  public function setHaltonerror($stop) {
    $this->haltonerror = \StringHelper::booleanValue($stop);
  }

  /**
   * Options of the Behat command.
   *
   * @return Option
   *   The created option.
   */
  public function createOption() {
    $num = array_push($this->options, new Option());
    return $this->options[$num - 1];
  }

  /**
   * Checks if the Behat executable exists.
   *
   * @param \PhingFile $bin
   *   The path to Behat.
   *
   * @return bool
   *   True if exists, False otherwise.
   */
  protected function behatExists(\PhingFile $bin) {
    if (!$bin->exists() || !$bin->isFile()) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    // Get default properties from project.
    $properties_mapping = array(
      'bin' => 'behat.bin',
      'color' => 'behat.color',
      'dry-run' => 'behat.dry-run',
      'name' => 'behat.name',
      'profile' => 'behat.profile',
      'suite' => 'behat.suite',
      'verbose' => 'behat.verbose',
    );

    foreach ($properties_mapping as $class_property => $behat_property) {
      if (!empty($this->getProject()->getProperty($behat_property))) {
        // TODO: We should use a setter here.
        $this->{$class_property} = $this->getProject()->getProperty($behat_property);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function main() {
    $command = array();

    // The Behat binary command.
    $command[] = $this->bin->getAbsolutePath();

    if ($this->path) {
      if (!file_exists($this->path)) {
        throw new \BuildException(
          'ERROR: the "' . $this->path . '" path does not exist.',
          $this->getLocation()
        );
      }
    }

    $command[] = !empty($this->path) ? $this->path : '';

    if ($this->config) {
      if (!file_exists($this->config)) {
        throw new \BuildException(
          'ERROR: the "' . $this->config . '" config file does not exist.',
          $this->getLocation()
        );
      }

      $option = new Option();
      $option->setName('config');
      $option->addText($this->config);
      $this->options[] = $option;
    }

    if ($this->name) {
      $option = new Option();
      $option->setName('name');
      $option->addText($this->name);
      $this->options[] = $option;
    }

    if ($this->tags) {
      $option = new Option();
      $option->setName('tags');
      $option->addText($this->tags);
      $this->options[] = $option;
    }

    if ($this->role) {
      $option = new Option();
      $option->setName('role');
      $option->addText($this->role);
      $this->options[] = $option;
    }

    if ($this->profile) {
      $option = new Option();
      $option->setName('profile');
      $option->addText($this->profile);
      $this->options[] = $option;
    }

    if ($this->suite) {
      $option = new Option();
      $option->setName('suite');
      $option->addText($this->suite);
      $this->options[] = $option;
    }

    if ($this->strict) {
      $option = new Option();
      $option->setName('strict');
      $this->options[] = $option;
    }

    if ($this->verbose !== FALSE) {
      $option = new Option();
      $option->setName('verbose');
      $option->addText($this->verbose);
      $this->options[] = $option;
    }

    if (!$this->colors) {
      $option = new Option();
      $option->setName('no-colors');
      $this->options[] = $option;
    }

    if ($this->dryRun) {
      $option = new Option();
      $option->setName('dry-run');
      $this->options[] = $option;
    }

    if ($this->haltonerror) {
      $option = new Option();
      $option->setName('stop-on-failure');
      $this->options[] = $option;
    }

    foreach ($this->options as $option) {
      $command[] = $option->toString();
    }

    $this->command = implode(' ', $command);

    if (!$this->isApplicable()) {
      return;
    }

    $this->prepare();
    $this->buildCommand();
    list($return, $output) = $this->executeCommand();
    $this->cleanup($return, $output);

    if ($this->haltonerror && $return != 0) {
      $outloglevel = $this->logOutput ? \Project::MSG_INFO : \Project::MSG_VERBOSE;
      foreach ($output as $line) {
        $this->log($line, $outloglevel);
      }

      // Throw an exception if Behat fails.
      throw new \BuildException("Behat exited with code $return");
    }

    return $return != 0;
  }

}
