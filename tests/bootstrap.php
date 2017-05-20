<?php
function loader($class)
{
    $file = '';

    if ($class === 'I_Do_Checkmk\CheckMkApi')
        $file = 'src/CheckMkApi.php';

    if ($class === 'I_Do_Checkmk\DependencyCheck')
        $file = 'src/DependencyCheck.php';

    if ($class === 'I_Do_Checkmk\Tools')
        $file = 'src/Tools.php';

    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');