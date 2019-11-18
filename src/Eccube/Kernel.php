<?php
/**
 * Created by PhpStorm.
 * User: takabayashi
 * Date: 2019/11/18
 * Time: 17:14
 */
declare(strict_types=1);

namespace Eccube\Skeleton\Eccube;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Eccube\DependencyInjection\Compiler\WebServerDocumentRootPass;
use Eccube\Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Eccube\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class Kernel extends BaseKernel
{
    protected function build(ContainerBuilder $container)
    {
        parent::build($container);
        // DocumentRootをルーティディレクトリを再設定する.
        $container->addCompilerPass(new WebServerDocumentRootPass('%kernel.project_dir%/public/'));
    }

    protected function addEntityExtensionPass(ContainerBuilder $container)
    {
        $projectDir = $container->getParameter('kernel.project_dir');

        // Eccube
        $paths = ['%eccube_src_dir%/Eccube/Entity'];
        $namespaces = ['Eccube\\Entity'];
        $reader = new Reference('annotation_reader');
        $driver = new Definition(AnnotationDriver::class, [$reader, $paths]);
        $driver->addMethodCall('setTraitProxiesDirectory', [$projectDir.'/app/proxy/entity']);
        $container->addCompilerPass(new DoctrineOrmMappingsPass($driver, $namespaces, []));

        // Customize
        $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver(
            ['Customize\\Entity'],
            ['%kernel.project_dir%/app/Customize/Entity']
        ));

        // Plugin
        $pluginDir = $projectDir.'/app/Plugin';
        $finder = (new Finder())
            ->in($pluginDir)
            ->sortByName()
            ->depth(0)
            ->directories();
        $plugins = array_map(function ($dir) {
            return $dir->getBaseName();
        }, iterator_to_array($finder));

        foreach ($plugins as $code) {
            if (file_exists($pluginDir.'/'.$code.'/Entity')) {
                $container->addCompilerPass(DoctrineOrmMappingsPass::createAnnotationMappingDriver(
                    ['Plugin\\'.$code.'\\Entity'],
                    ['%kernel.project_dir%/app/Plugin/'.$code.'/Entity']
                ));
            }
        }
    }

    protected function loadEntityProxies()
    {
        $files = Finder::create()
            ->in($this->getProjectDir().'/app/proxy/entity/')
            ->name('*.php')
            ->files();
        foreach ($files as $file) {
            require_once $file->getRealPath();
        }
    }
}
