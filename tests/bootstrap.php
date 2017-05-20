<?php
function loader($class)
{
    $file = '';

    if ($class === 'i_do_checkmk\Check_MK_API')
        $file = 'src/class-check-mk.php';

    if ($class === 'i_do_checkmk\Dependency_Check')
        $file = 'src/class-dependency-check.php';

    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');