<?php

namespace Wappointment\WP;

use Wappointment\System\Helpers;

class Readme
{
    public function getVersions()
    {
        $readme_file = Helpers::pluginPath() . 'readme.txt';
        if (is_readable($readme_file)) {
            return self::getChangelog(file_get_contents($readme_file), 1);
        }
    }

    private static function getChangeLogSection($readme_file)
    {
        // Extract changelog section of the readme.txt
        preg_match('/== Changelog ==(.*?)(\n==|$)/is', $readme_file, $changelog);

        if (empty($changelog[1])) {
            throw new \WappointmentException('Changelog section cannot be located.');
        }

        return $changelog[1];
    }

    private static function getChangesPerVersion($changelog)
    {
        $changesPerVersion = preg_split('/\n(?=\=)/', trim($changelog), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($changesPerVersion)) {
            throw new \WappointmentException('Changelog section is empty.');
        }
        return $changesPerVersion;
    }

    private static function getArrayChanges($changesPerVersion)
    {
        $versionsChanges = [];

        foreach ($changesPerVersion as $versionChange) {
            preg_match('/=(.*?)=(.*)/s', $versionChange, $versionAndChanges);

            if (empty($versionAndChanges[1]) || empty($versionAndChanges[2])) {
                return false;
            }

            $versionsChanges[] = [
                'version' => trim($versionAndChanges[1]),
                'changes' => preg_split('/(^|\n)[\* ]*/', trim($versionAndChanges[2]), -1, PREG_SPLIT_NO_EMPTY),
            ];
        }
        return $versionsChanges;
    }

    private static function getChangelog($readme_file)
    {
        $changeLogSection = self::getChangeLogSection($readme_file);

        $changesPerVersion = self::getChangesPerVersion($changeLogSection);

        return self::getArrayChanges($changesPerVersion);
    }
}
