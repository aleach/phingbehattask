<?php

namespace Phing\Behat\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use function bovigo\assert\assert;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\hasKey;

/**
 * Class BalancerTest.
 *
 * @package Phing\Behat\Tests
 */
class BalancerTest extends TestCase {

  /**
   * Containers generation test.
   *
   * @dataProvider featureFilesProvider
   */
  public function testContainersGeneration($containers, $files, $expected, $filterprofiles = ['' => []]) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial();

    /** @var \Phing\Behat\Balancer $balancer */
    $scanFiles = $balancer->scanDirectory(__DIR__ . '/files', '/.feature/');
    assert(sort($scanFiles), equals(sort($files)));

    $balancer->setContainers($containers);
    $actual = [];
    foreach ($balancer->getFilteredFiles($filterprofiles, $files) as $profile => $filteredFiles) {
      $actual[$profile] = [];
      foreach ($balancer->getContainers($filteredFiles) as $key => $container) {
        $actual[$profile][$key] = $container;
      }
    }
    if ($profile === '') {
      $actual = $actual[$profile];
    }

    assert($actual, equals($expected));
  }

  /**
   * YAML generation test.
   *
   * @dataProvider featureFilesProvider
   */
  public function testYamlGeneration($containers, $files, $expected, $filterprofiles = ['' => []]) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial();

    /** @var \Phing\Behat\Balancer $balancer */
    $scanFiles = $balancer->scanDirectory(__DIR__ . '/files', '/.feature/');

    $balancer->setContainers($containers);
    $balancer->setImport('behat.import.yml');

    foreach ($balancer->getFilteredFiles($filterprofiles, $scanFiles) as $profile => $filteredFiles) {
      foreach ($balancer->getContainers($filteredFiles) as $container) {
        $content = $balancer->generateBehatYaml($container, $profile);
        if ($profile === '') {
          $profile = 'default';
        }

        $parsed = Yaml::parse($content);

        assert($parsed, hasKey($profile)->and(hasKey('imports')));
        assert($parsed['imports'], equals(['behat.import.yml']));
        assert($parsed[$profile]['suites']['default']['paths'], equals($container));
      }
    }

  }

  /**
   * Data provider for balancer tests.
   *
   * @return array
   *    Test arguments.
   */
  public function featureFilesProvider() {
    return [
      // Case 1.
      [
        'containers' => 3,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          [
            __DIR__ . '/files/feature-1.feature',
            __DIR__ . '/files/feature-2.feature',
            __DIR__ . '/files/feature-3.feature',
          ],
          [
            __DIR__ . '/files/feature-4.feature',
            __DIR__ . '/files/feature-5.feature',
            __DIR__ . '/files/feature-6.feature',
          ],
          [
            __DIR__ . '/files/feature-7.feature',
          ],
        ],
      ],
      // Case 2.
      [
        'containers' => 1,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          [
            __DIR__ . '/files/feature-1.feature',
            __DIR__ . '/files/feature-2.feature',
            __DIR__ . '/files/feature-3.feature',
            __DIR__ . '/files/feature-4.feature',
            __DIR__ . '/files/feature-5.feature',
            __DIR__ . '/files/feature-6.feature',
            __DIR__ . '/files/feature-7.feature',
          ],
        ],
      ],
      // Case 3.
      [
        'containers' => 1,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          'default' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'one' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
            ],
          ],
          'onetwo' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
            ],
          ],
          'three' => [
            [
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
        ],
        'filterprofiles' => [
          'default' => ['one', 'two', 'three'],
          'one' => ['one'],
          'onetwo' => ['one', 'two'],
          'three' => ['three'],
        ],
      ],
      // Case 4.
      [
        'containers' => 2,
        'files' => [
          __DIR__ . '/files/feature-1.feature',
          __DIR__ . '/files/feature-2.feature',
          __DIR__ . '/files/feature-3.feature',
          __DIR__ . '/files/feature-4.feature',
          __DIR__ . '/files/feature-5.feature',
          __DIR__ . '/files/feature-6.feature',
          __DIR__ . '/files/feature-7.feature',
        ],
        'expected' => [
          'default' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
              __DIR__ . '/files/feature-3.feature',
            ],
            [
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
          'one' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-4.feature',
            ],
            [
              __DIR__ . '/files/feature-6.feature',
            ],
          ],
          'onetwo' => [
            [
              __DIR__ . '/files/feature-1.feature',
              __DIR__ . '/files/feature-2.feature',
            ],
            [
              __DIR__ . '/files/feature-4.feature',
              __DIR__ . '/files/feature-6.feature',
            ],
          ],
          'three' => [
            [
              __DIR__ . '/files/feature-3.feature',
              __DIR__ . '/files/feature-6.feature',
            ],
            [
              __DIR__ . '/files/feature-7.feature',
            ],
          ],
        ],
        'filterprofiles' => [
          'default' => ['one', 'two', 'three'],
          'one' => ['one'],
          'onetwo' => ['one', 'two'],
          'three' => ['three'],
        ],
      ],
    ];
  }

}
