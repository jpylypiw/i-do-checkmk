<?php
function loader($class)
{
    $file = '';

    if ($class === 'i_do_checkmk\Check_MK_API')
        $file = 'class-check-mk.php';

    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');