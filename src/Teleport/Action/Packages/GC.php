<?php
/**
 * This file is part of the teleport package.
 *
 * Copyright (c) MODX, LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teleport\Action\Packages;

use Exception;
use MODX\Revolution\Error\modError;
use MODX\Revolution\Transport\modTransportPackage;
use Teleport\Action\Action;
use Teleport\Action\ActionException;
use Teleport\Teleport;
use xPDO\xPDO;

/**
 * Clean-up older versions of installed packages
 *
 * @property \stdClass profile
 * @property bool      preserveZip
 *
 * @package Teleport\Action
 */
class GC extends Action
{
    /**
     * @var array Defines the arguments required for the UserCreate action.
     */
    protected $required = array('profile');

    public function process()
    {
        parent::process();
        try {
            $this->profile = Teleport::loadProfile($this->profile);

            $this->getMODX($this->profile);
            $this->modx->getService('error', modError::class);
            $this->modx->error->message = '';
            $this->modx->setOption(xPDO::OPT_SETUP, true);

            $latestPackages = $this->modx->call(modTransportPackage::class, 'listPackages', array(&$this->modx, 1));
            /** @var modTransportPackage $latestPackage */
            foreach ($latestPackages['collection'] as $latestPackage) {
                $versions = $this->modx->call(modTransportPackage::class, 'listPackageVersions', array(
                    &$this->modx,
                    array(
                        'package_name:LIKE' => $latestPackage->package_name,
                        'signature:!=' => $latestPackage->signature)
                    )
                );
                if (isset($versions['collection']) && $versions['total'] > 0) {
                    $this->request->log("Removing {$versions['total']} outdated package versions for {$latestPackage->package_name}");
                    /** @var modTransportPackage $version */
                    foreach ($versions['collection'] as $version) {
                        $this->request->log("Removing outdated package version {$version->signature} from {$this->profile->name}");
                        $version->removePackage(true, false);
                        $this->removePackageFiles($version->signature);
                    }
                }
            }

            $this->request->log("Completed Removing outdated packages for {$this->profile->name}");
        } catch (Exception $e) {
            throw new ActionException($this, "Error removing outdated packages: {$e->getMessage()}", $e);
        }
    }

    protected function removePackageFiles($signature)
    {
        if (!$this->preserveZip && file_exists(MODX_CORE_PATH . "packages/{$signature}.transport.zip")) {
            if (@unlink(MODX_CORE_PATH . "packages/{$signature}.transport.zip")) {
                $this->request->log('Removing package ' . MODX_CORE_PATH . "packages/{$signature}.transport.zip");
            } else {
                $this->request->log('Error removing package ' . MODX_CORE_PATH . "packages/{$signature}.transport.zip");
            }
        }
        if (is_dir(MODX_CORE_PATH . "packages/{$signature}")) {
            if ($this->modx->getCacheManager()->deleteTree(
                MODX_CORE_PATH . "packages/{$signature}",
                array('deleteTop' => true, 'skipDirs' => false, 'extensions' => array())
            )) {
                $this->request->log('Removing package directory ' . MODX_CORE_PATH . "packages/{$signature}");
            } else {
                $this->request->log('Error removing package directory ' . MODX_CORE_PATH . "packages/{$signature}");
            }
        }
    }
}
