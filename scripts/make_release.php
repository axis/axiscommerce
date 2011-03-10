<?php
/**
 * Copy project to axis-release folder,
 * skips not needed content
 * fix installation controller logic, .htaccess, layout files.
 */

define('AXIS_ROOT', str_replace('\\', '/', realpath('../')));
define('AXIS_DESTINATION', str_replace('\\', '/', dirname(AXIS_ROOT)) . '/axis-release');

class ReleaseMaker
{
    private $_rules = array(
        // path that will not be copied,
        'exclude' => array(
            'app/code/Axis/Core/controllers/SandboxController.php',
            'app/code/Example',
            'app/design/front/fallback/*',
            'skin/front/fallback/*',
            '.git',
            'doc',
            'tests',
            'app/etc/config.php',
            'js/ext-3.3.1/*',
            'media/*/*',      // skip all content inside every folder inside media
            'var/*/*',
            'var/axis_sample_data_latest.sql',
            '.gitignore',
            '.project',
            '.buildpath',
            'nbproject',
            '.settings'
        ),
        // path that will be copied despite of exclude rules
        'include' => array(
            'js/ext-3.3.1/adapter/jquery/*',
            'js/ext-3.3.1/examples/ux/ux-all.js',
            'js/ext-3.3.1/examples/ux/css/ux-all.css',
            'js/ext-3.3.1/examples/ux/images/*',
            'js/ext-3.3.1/resources/*',
            'js/ext-3.3.1/src/locale/*',
            'js/ext-3.3.1/ext-all.js',
            'js/ext-3.3.1/license.txt',
            'var/minify/*'
        )
    );

    public function __construct()
    {
        foreach ($this->_rules as &$rule) {
            foreach ($rule as &$path) {
                $path = AXIS_ROOT . '/' . $path;
            }
        }
    }

    public function make()
    {
        $this->_removeDir(AXIS_DESTINATION);
        $this->_copy(AXIS_ROOT, AXIS_DESTINATION);
        $this->_setProductionEnvironment();
        $this->_replaceDebugScripts();
        $this->_hideDropAction();
        $this->_closeInstallUrl();
        echo 'DONE!';
    }

    protected function _canCopy($path)
    {
        $path = str_replace('\\', '/', $path);
        if ($this->_hitExclude($path) && !$this->_hitInclude($path)) {
            return false;
        }
        return true;
    }

    protected function _hitExclude($path)
    {
        foreach ($this->_rules['exclude'] as $exclude) {
            if ($path === $exclude) {
                return true;
            } elseif (strstr($exclude, '/*')) {
                $excludeParts = explode('/*', $exclude);
                if (false === strstr($path, $excludeParts[0])) {
                    continue;
                }
                $pathParts = explode('/', str_replace($excludeParts[0], '', $path));
                if (count($pathParts) >= count($excludeParts)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function _hitInclude($path)
    {
        foreach ($this->_rules['include'] as $include) {
            if (0 === strpos($include, $path)) {
                return true;
            } elseif (strstr($include, '/*')
                && 0 === strpos($path, str_replace('/*', '', $include))) {

                return true;
            }
        }
        return false;
    }

    protected function _setProductionEnvironment()
    {
        $htaccess = file_get_contents(AXIS_DESTINATION . '/.htaccess');
        $htaccess = str_replace(
            'SetEnv APPLICATION_ENV development',
            'SetEnv APPLICATION_ENV production',
            $htaccess
        );
        file_put_contents(AXIS_DESTINATION . '/.htaccess', $htaccess);
    }

    protected function _replaceDebugScripts()
    {
        $layout = file_get_contents(AXIS_DESTINATION . '/app/design/admin/default/layouts/layout.phtml');
        $layout = str_replace(
            array('ext-all-debug.js', 'ux-all-debug.js'),
            array('ext-all.js', 'ux-all.js'),
            $layout
        );
        file_put_contents(AXIS_DESTINATION . '/app/design/admin/default/layouts/layout.phtml', $layout);
    }

    protected function _hideDropAction()
    {
        $controller = file_get_contents(AXIS_DESTINATION . '/install/app/controllers/IndexController.php');
        $controller = str_replace("public function dropAction", 'private function _dropAction', $controller);
        file_put_contents(AXIS_DESTINATION . '/install/app/controllers/IndexController.php', $controller);
    }

    protected function _closeInstallUrl()
    {
        $controller = file_get_contents(AXIS_DESTINATION . '/install/app/controllers/IndexController.php');
        $search = <<<PHP
        if ('development' === APPLICATION_ENV) { //@todo remove in release
            return;
        }
PHP;
        $controller = str_replace($search, '', $controller);
        file_put_contents(AXIS_DESTINATION . '/install/app/controllers/IndexController.php', $controller);
    }

    protected function _copy($source, $dest, $folderPermission = 0755, $filePermission = 0644)
    {
        $result = false;

        if (!$this->_canCopy($source)) {
            return $result;
        }

        if (is_file($source)) { # $source is file
            if (is_dir($dest)) { # $dest is folder
                if ($dest[strlen($dest)-1]!='/') { # add '/' if necessary
                    $__dest=$dest."/";
                }
                $__dest .= basename($source);
            } else { # $dest is (new) filename
                $__dest=$dest;
            }
            $parts = pathinfo($__dest);
            if (!is_dir($parts['dirname'])) {
                echo '.';
                mkdir($parts['dirname'], $folderPermission, true);
            }
            $result=copy($source, $__dest);
            chmod($__dest,$filePermission);
        } elseif(is_dir($source)) { # $source is dir
            if (!is_dir($dest)) { # dest-dir not there yet, create it
                echo '.';
                @mkdir($dest, $folderPermission, true);
                chmod($dest, $folderPermission);
            }
            if ($source[strlen($source)-1]!='/') # add '/' if necessary
                $source = $source . "/";
            if ($dest[strlen($dest)-1]!='/') # add '/' if necessary
                $dest = $dest . "/";

            # find all elements in $source
            $result = true; # in case this dir is empty it would otherwise return false
            $dirHandle = opendir($source);
            while ($file = readdir($dirHandle)) { # note that $file can also be a folder
                if($file != "." && $file != "..") { # filter starting elements and pass the rest to this function again
                    # echo "$source$file ||| $dest$file<br />\n";
                    $result = $this->_copy(
                        $source . $file,
                        $dest . $file,
                        $folderPermission,
                        $filePermission
                    );
                }
            }
            closedir($dirHandle);
        } else {
            $result=false;
        }
        return $result;
    }

    protected function _removeDir($path)
    {
        if (is_dir($path)) {
            $path = rtrim($path, '/');
            $dir = dir($path);
            while (false !== ($file = $dir->read())) {
                if ($file != '.' && $file != '..') {
                    (!is_link("$path/$file") && is_dir("$path/$file")) ?
                        $this->_removeDir("$path/$file") : unlink("$path/$file");
                }
            }
            $dir->close();
            rmdir($path);
            return true;
        }
        return false;
    }
}

$maker = new ReleaseMaker();
$maker->make();
