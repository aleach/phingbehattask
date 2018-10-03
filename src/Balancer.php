<?php

namespace Phing\Behat;

/**
 * Class Balancer.
 *
 * @package Phing\Behat
 */
class Balancer extends \Task {

  /**
   * Number of containers.
   *
   * @var int
   */
  private $containers;

  /**
   * Behat root directory.
   *
   * @var string
   */
  private $root;

  /**
   * Destination directory where individual behat.yml files will be created.
   *
   * @var string
   */
  private $destination;

  /**
   * Full path to behat.yml file to be imported.
   *
   * @var string
   */
  private $import;

  /**
   * An array of filter profile.
   *
   * @var FilterProfile[]
   */
  protected $filterProfiles = [];

  /**
   * Create the filter profile.
   *
   * @return FilterProfile
   *   The created filter profile.
   */
  public function createFilterProfile() {
    $num = array_push($this->filterProfiles, new FilterProfile());

    return $this->filterProfiles[$num - 1];
  }

  /**
   * Set number of containers..
   *
   * @param string $containers
   *    Number of containers.
   */
  public function setContainers($containers) {
    $this->containers = $containers;
  }

  /**
   * Set Behat root directory.
   *
   * @param string $root
   *    Behat root directory.
   */
  public function setRoot($root) {
    $this->root = $root;
  }

  /**
   * Set destination directory.
   *
   * @param string $destination
   *    Destination directory.
   */
  public function setDestination($destination) {
    $this->destination = $destination;
  }

  /**
   * Set full path to import behat.yml.
   *
   * @param string $import
   *    Import behat.yml full path.
   */
  public function setImport($import) {
    $this->import = $import;
  }

  /**
   * Get the filter profiles specified.
   *
   * @return array
   *    An array of filterProfiles.
   */
  public function getFilterProfiles() {
    $filterProfiles = [];

    foreach ($this->filterProfiles as $filterProfile) {
      $filterProfiles[$filterProfile->getName()] = $filterProfile->getText();
    }

    return $filterProfiles ? $filterProfiles : ['' => []];
  }

  /**
   * Main callback.
   */
  public function main() {
    if (!is_dir($this->destination)) {
      throw new \InvalidArgumentException("{$this->destination} is not a valid directory.");
    }

    $filterProfiles = $this->getFilterProfiles();
    $files = $this->scanDirectory($this->root, '/.feature/');

    $filteredFiles = $this->getFilteredFiles($filterProfiles, $files);
    foreach ($filteredFiles as $profile => $filteredFiles) {
      foreach ($this->getContainers($filteredFiles) as $key => $container) {
        $content = $this->generateBehatYaml($container, $profile);
        $this->createFiles($key, $content, $profile);
      }
    }
  }

  /**
   * Get feature files for the given profiles.
   *
   * This function checks each feature file for the tags specified
   * in the filterProfile options.
   *
   * @param array $filterProfiles
   *    The array of filters for each profile.
   * @param array $files
   *    List of all feature files with their absolute path.
   *
   * @return array
   *    An array of feature files for each profile.
   */
  public function getFilteredFiles($filterProfiles, $files) {
    $filteredFiles = [];

    foreach ($filterProfiles as $profile => $filters) {
      $filteredFiles[$profile] = [];

      // Empty tag, all files.
      if ($filters === []) {
        $filteredFiles[$profile] = $files;
        continue;
      }

      foreach ($files as $file) {
        foreach($filters as $filter) {
          if (strpos(file_get_contents($file), '@' . $filter) !== false) {
            $filteredFiles[$profile][$file] = $file;
          }
        }
      }
      $filteredFiles[$profile] = array_values($filteredFiles[$profile]);
    }

    return $filteredFiles;
  }

  /**
   * Scan and divide feature files into containers.
   *
   * @param string $root
   *    Root directory to scan.
   *
   * @return array
   *    List of feature files divided into containers.
   */
  public function getContainers($files) {
    $size = ceil(count($files) / $this->containers);

    return array_chunk($files, $size);
  }

  /**
   * Recursively scan a directory.
   *
   * @param string $dir
   *   The base directory, without trailing slash.
   * @param string $mask
   *   The preg_match() regular expression of the files to find.
   *
   * @return array
   *    List of files with their absolute path.
   */
  public function scanDirectory($dir, $mask) {
    $depth = 0;
    $files = [];
    if (is_dir($dir) && $handle = opendir($dir)) {
      while (FALSE !== ($filename = readdir($handle))) {
        if (!preg_match('/(\.\.?|CVS)$/', $filename) && $filename[0] != '.') {
          $uri = "$dir/$filename";
          if (is_dir($uri)) {
            $files = array_merge($this->scanDirectory($uri, $mask), $files);
          }
          elseif ($depth >= 0 && preg_match($mask, $filename)) {
            $files[] = $uri;
          }
        }
      }
      closedir($handle);
    }

    return $files;
  }

  /**
   * Generate behat.yml files.
   *
   * @param array $container
   *    Array of feature file locations.
   * @param string $profile
   *    If filtered, the profile name, otherwise an empty string.
   *
   * @return string
   *    Return behat.yaml file.
   */
  public function generateBehatYaml($container, $profile) {
    if ($profile === '') {
      $profile = 'default';
    }
    $features = implode("\n        - ", $container);

    return <<<YAML
imports:
  - {$this->import}
{$profile}:
  suites:
    default:
      paths:
        - {$features}
YAML;

  }

  /**
   * Wrapper around file_put_contents().
   *
   * @param string $number
   *    Behat configuration file number.
   * @param string $content
   *    Behat configuration file content.
   * @param string $profile
   *    If filtered, the profile of the current filter, otherwise an empty string.
   */
  protected function createFiles($number, $content, $profile) {
    $filename = "/behat.{$number}.yml";
    if ($profile !== '') {
      $filename = "/behat.{$profile}.{$number}.yml";
    }

    file_put_contents($this->destination . $filename, $content);
  }

}
