<?php

declare(strict_types=1);

namespace CorepulseCacheBundle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Installer extends SettingsStoreAwareInstaller
{
    private array $tablesToInstall = [
        'corepulse_caches' => 'CREATE TABLE `corepulse_caches` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `url` varchar(190) DEFAULT NULL,
              `query` text DEFAULT NULL,
              `tags` text DEFAULT NULL,
              `type` varchar(190) DEFAULT NULL,
              `active` int(2) DEFAULT NULL,
              `createAt` timestamp NULL DEFAULT current_timestamp(),
              `updateAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8mb4;'
    ];

    protected ?Schema $schema = null;

    public function __construct(
        protected BundleInterface $bundle,
        protected Connection $db
    ) {
        parent::__construct($bundle);
    }

    protected function addPermissions(): void
    {
        // $db = \Pimcore\Db::get();

        // foreach (self::USER_PERMISSIONS as $permission) {
        //     $db->insert('users_permission_definitions', [
        //         $db->quoteIdentifier('key') => $permission,
        //         $db->quoteIdentifier('category') => self::USER_PERMISSIONS_CATEGORY,
        //     ]);
        // }
    }

    protected function removePermissions(): void
    {
        // $db = \Pimcore\Db::get();

        // foreach (self::USER_PERMISSIONS as $permission) {
        //     $db->delete('users_permission_definitions', [
        //         $db->quoteIdentifier('key') => $permission,
        //     ]);
        // }
    }

    public function install(): void
    {
        // $this->installClasses();
        // $this->addPermissions();
        $this->installTables();
        parent::install();
    }

    private function installTables(): void
    {
        foreach ($this->tablesToInstall as $name => $statement) {
            if ($this->getSchema()->hasTable($name)) {
                $this->output->write(sprintf(
                    '     <comment>WARNING:</comment> Skipping table "%s" as it already exists',
                    $name
                ));

                continue;
            }

            $this->db->executeQuery($statement);
        }
    }

    private function uninstallTables(): void
    {
        foreach (array_keys($this->tablesToInstall) as $table) {
            if (!$this->getSchema()->hasTable($table)) {
                $this->output->write(sprintf(
                    '     <comment>WARNING:</comment> Not dropping table "%s" as it doesn\'t exist',
                    $table
                ));

                continue;
            }

            $this->db->executeQuery("DROP TABLE IF EXISTS $table");
        }
    }

    public function uninstall(): void
    {
        $this->removePermissions();
        $this->uninstallTables();

        parent::uninstall();
    }

    protected function getSchema(): Schema
    {
        return $this->schema ??= $this->db->createSchemaManager()->introspectSchema();
    }

    public function installClasses()
    {
        // $sourcePath = __DIR__.'/../install/class_source';

        // self::installClass('User', $sourcePath.'/class_User_export.json');
        // self::installClass('Role', $sourcePath.'/class_Role_export.json');
    }


    public static function installClass($classname, $filepath)
    {
        $class = \Pimcore\Model\DataObject\ClassDefinition::getByName($classname);
        if (!$class) {
            $class = new \Pimcore\Model\DataObject\ClassDefinition();
            $class->setName($classname);
            $class->setGroup('VuetifyManagement');

            $json = file_get_contents($filepath);

            $success = \Pimcore\Model\DataObject\ClassDefinition\Service::importClassDefinitionFromJson($class, $json);
            if (!$success) {
                Logger::err("Could not import $classname Class.");
            }
        }
    }
}
