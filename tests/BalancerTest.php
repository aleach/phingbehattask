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
  public function testContainersGeneration($containers, $files, $expected) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial()->shouldReceive('scanDirectory')->andReturn($files);

    /** @var \Phing\Behat\Balancer $balancer */
    $balancer->setContainers($containers);
    $actual = $balancer->getContainers('foo');
    assert($actual, equals($expected));
  }

  /**
   * YAML generation test.
   *
   * @dataProvider featureFilesProvider
   */
  public function testYamlGeneration($containers, $files, $expected) {
    $balancer = \Mockery::mock('\Phing\Behat\Balancer');
    $balancer->makePartial()->shouldReceive('scanDirectory')->andReturn($files);

    /** @var \Phing\Behat\Balancer $balancer */
    $balancer->setContainers($containers);
    $balancer->setImport('behat.import.yml');

    foreach ($balancer->getContainers('foo') as $container) {

      $content = $balancer->generateBehatYaml($container);
      $parsed = Yaml::parse($content);

      assert($parsed, hasKey('default')->and(hasKey('imports')));
      assert($parsed['imports'], equals(['behat.import.yml']));
      assert($parsed['default']['suites']['default']['paths'], equals($container));
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
          'feature-1.feature',
          'feature-2.feature',
          'feature-3.feature',
          'feature-4.feature',
          'feature-5.feature',
          'feature-6.feature',
          'feature-7.feature',
        ],
        'expected' => [
          [
            'feature-1.feature',
            'feature-2.feature',
            'feature-3.feature',
          ],
          [
            'feature-4.feature',
            'feature-5.feature',
            'feature-6.feature',
          ],
          [
            'feature-7.feature',
          ],
        ],
      ],
      // Case 2.
      [
        'containers' => 1,
        'files' => [
          'feature-1.feature',
          'feature-2.feature',
          'feature-3.feature',
        ],
        'expected' => [
          [
            'feature-1.feature',
            'feature-2.feature',
            'feature-3.feature',
          ],
        ],
      ],
    ];
  }

}
