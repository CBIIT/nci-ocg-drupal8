<?php

namespace Drupal\Tests\lb_everywhere\Unit;

use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\lb_everywhere\LBEverywhereRegionRepository;
use Drupal\lb_everywhere\Plugin\DisplayVariant\LBEverywhereDisplayVariant;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\lb_everywhere\Plugin\DisplayVariant\LBEverywhereDisplayVariant
 *
 * @group lb_everywhere
 */
class LBEverywhereDisplayVariantTest extends UnitTestCase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $display = $this->prophesize(LayoutEntityDisplayInterface::class);
    $section = $this->prophesize(Section::class);
    $section->toRenderArray([])->willReturn(['#markup' => 'The Rendered Section']);
    $sections[] = $section->reveal();

    $section_storage = $this->prophesize(SectionStorageInterface::class);
    $section_storage->getSections()->willReturn($sections);
    $section_storage->getContextValue('display')->willReturn($display->reveal());
    $section_storage->getContextsDuringPreview()->willReturn([]);
    $section_storages['sidebar_first'] = $section_storage->reveal();

    $cache_contexts_manager = $this->prophesize(CacheContextsManager::class);
    $cache_contexts_manager->assertValidTokens(['url.query_args', 'user.permissions'])->willReturn(TRUE);

    $block_repository = $this->prophesize(BlockRepositoryInterface::class);
    $cacheable_metadata = [];
    $block_repository->getVisibleBlocksPerRegion($cacheable_metadata)->willReturn([]);

    $block_view_builder = $this->prophesize(EntityViewBuilderInterface::class);

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getViewBuilder('block')->willReturn($block_view_builder->reveal());
    $entity_type_manager->getDefinition('block')->willReturn(new EntityType(['id' => 'block']));

    $region_repository = $this->prophesize(LBEverywhereRegionRepository::class);
    $region_repository->getSectionStorages('test_theme', Argument::type(CacheableMetadata::class))->willReturn($section_storages);
    $region_repository->getSectionStorages('empty_theme', Argument::type(CacheableMetadata::class))->willReturn([]);

    $tempstore_repository = $this->prophesize(LayoutTempstoreRepositoryInterface::class);
    $tempstore_repository->get(Argument::type(SectionStorageInterface::class))->willReturnArgument(0);

    $entity_form_builder = $this->prophesize(EntityFormBuilderInterface::class);
    $entity_form_builder->getForm($display->reveal(), 'layout_builder', Argument::type('array'))->willReturn(['#markup' => 'The Section Form']);

    $context_repository = $this->prophesize(ContextRepositoryInterface::class);
    $context_repository->getAvailableContexts()->willReturn([]);

    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->themeManager = $this->prophesize(ThemeManagerInterface::class);
    $this->currentUser = $this->prophesize(AccountInterface::class);

    $route_match = $this->prophesize(RouteMatchInterface::class);
    $section_storage_manager = $this->prophesize(SectionStorageManagerInterface::class);

    $container = new ContainerBuilder();
    $container->set('cache_contexts_manager', $cache_contexts_manager->reveal());
    $container->set('block.repository', $block_repository->reveal());
    $container->set('entity_type.manager', $entity_type_manager->reveal());
    $container->set('request_stack', $this->requestStack->reveal());
    $container->set('theme.manager', $this->themeManager->reveal());
    $container->set('lb_everywhere.region_repository', $region_repository->reveal());
    $container->set('layout_builder.tempstore_repository', $tempstore_repository->reveal());
    $container->set('entity.form_builder', $entity_form_builder->reveal());
    $container->set('current_user', $this->currentUser->reveal());
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('context.repository', $context_repository->reveal());
    $container->set('current_route_match', $route_match->reveal());
    $container->set('plugin.manager.layout_builder.section_storage', $section_storage_manager->reveal());
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::build
   *
   * @dataProvider providerTestBuild
   */
  public function testBuild($expected, array $query_parameters, $has_permission, $theme_name = 'test_theme') {
    $this->requestStack->getCurrentRequest()->willReturn(new Request($query_parameters));
    $this->themeManager->getActiveTheme()->willReturn(new ActiveTheme(['name' => $theme_name]));
    $this->currentUser->hasPermission('configure any layout')->willReturn($has_permission);

    $display_variant = LBEverywhereDisplayVariant::create(\Drupal::getContainer(), [], '', []);

    $result = $display_variant->build();
    $this->assertEquals($expected, $result);
  }

  /**
   * Provides test data for ::testBuild().
   */
  public function providerTestBuild() {
    $expected = [
      '#cache' => [
        'tags' => [
          'block_list',
        ],
        'contexts' => [
          'url.query_args',
          'user.permissions',
        ],
        'max-age' => -1,
      ],
      'content' => [
        'system_main' => [],
        'messages' => [
          '#weight' => -1000,
          '#type' => 'status_messages',
          '#include_fallback' => TRUE,
        ],
      ],
    ];

    $data = [];
    $data['empty theme'] = [
      $expected,
      [],
      TRUE,
      'empty_theme',
    ];
    $rendered_expected = $expected + [
      'sidebar_first' => [
        'lbeverywhere' => [
          ['#markup' => 'The Rendered Section'],
        ],
      ],
    ];
    $data['default, with perms'] = [
      $rendered_expected,
      [],
      TRUE,
    ];
    $data['layout mode, with perms'] = [
      $expected + [
        'sidebar_first' => [
          'lbeverywhere' => [
            [
              '#type' => 'link',
              '#url' => Url::fromRoute('<current>', [], ['query' => ['mode' => 'layout', 'region' => 'sidebar_first']]),
              '#title' => 'Enable Layout Builder for the <em class="placeholder">Sidebar First</em> region',
              '#attributes' => [
                'class' => [
                  'visually-hidden',
                ],
                'data-layout-builder-region' => TRUE,
              ],
            ],
            [
              '#markup' => 'The Rendered Section',
            ],
          ],
          '#attributes' => [
            'class' => [
              'region__select-mode',
            ],
          ],
        ],
      ],
      ['mode' => 'layout'],
      TRUE,
    ];
    $data['unexpected mode, with perms'] = [
      $rendered_expected,
      ['mode' => 'garbage'],
      TRUE,
    ];
    $data['layout mode, valid region, with perms'] = [
      $expected + [
        'sidebar_first' => [
          'lbeverywhere' => [
            '#markup' => 'The Section Form',
          ],
        ],
      ],
      ['mode' => 'layout', 'region' => 'sidebar_first'],
      TRUE,
    ];
    $data['unexpected region, with perms'] = [
      $rendered_expected,
      ['mode' => 'layout', 'region' => 'garbage'],
      TRUE,
    ];
    $data['default, no perms'] = [
      $rendered_expected,
      [],
      FALSE,
    ];
    $data['layout mode, no perms'] = [
      $rendered_expected,
      ['mode' => 'layout'],
      FALSE,
    ];
    $data['unexpected mode, no perms'] = [
      $rendered_expected,
      ['mode' => 'garbage'],
      FALSE,
    ];
    $data['layout mode, valid region, no perms'] = [
      $rendered_expected,
      ['mode' => 'layout', 'region' => 'sidebar_first'],
      FALSE,
    ];
    $data['unexpected region, no perms'] = [
      $rendered_expected,
      ['mode' => 'layout', 'region' => 'garbage'],
      FALSE,
    ];
    return $data;
  }

}

namespace Drupal\lb_everywhere\Plugin\DisplayVariant;

if (!function_exists('system_region_list')) {
  function system_region_list($theme_name) {
    return [
      'sidebar_first' => 'Sidebar First',
    ];
  }
}
