<?php

namespace Modera\Component\SeleniumTools\Behat;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Integrates TestHarness into Behat.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ActorsExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'actors';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('drivers')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('browser')->end()
                            ->scalarNode('host')
                                ->defaultValue('http://localhost:4444/wd/hub')
                            ->end()
                            ->scalarNode('connection_timeout')
                                ->defaultValue(30000)
                            ->end()
                            ->scalarNode('request_timeout')
                                ->defaultValue(15000)
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('video_recorder')
                    ->children()
                        ->scalarNode('host')->end()
                    ->end()
                ->end()
                ->arrayNode('harnesses')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('driver')->end()
                            ->arrayNode('actors')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('base_url')->end()
                                        ->scalarNode('user_email')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $harnessFactory = new Definition(TestHarnessFactory::class, [$config]);
        $container->setDefinition('actors.harness_factory', $harnessFactory);

        $contextInitializer = new Definition(
            TestHarnessAwareContextInitializer::class,
            [new Reference('actors.harness_factory')]
        );
        $contextInitializer->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('actors.context_initializer', $contextInitializer);

        if (isset($config['video_recorder']['host'])) {
            $recorderConfig = $config['video_recorder'];

            if ($recorderConfig['host']) {
                $videoRecordingListener = new Definition(RemoteReportingListener::class, [$recorderConfig]);
                $videoRecordingListener->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
                $container->setDefinition('actors.video_recording_listener', $videoRecordingListener);
            }
        }

        // Importing environmental variables, those later can be referred from behat.yml file. For example,
        // if there's $_SERVER['BEHAT_FOO'] defined, then in behat.yml you can refer to it as "%BEHAT_FOO%".
        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, strlen('BEHAT')) == 'BEHAT') {
                $container->setParameter($key, $value);
            }
        }
    }
}