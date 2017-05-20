<?php
function loader($class)
{
    $file = '';

    if ($class === 'i_do_checkmk\CheckMkApi')
        $file = 'src/CheckMkApi.php';

    if ($class === 'i_do_checkmk\DependencyCheck')
        $file = 'src/DependencyCheck.php';

    if ($class === 'i_do_checkmk\Tools')
        $file = 'src/Tools.php';

    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');