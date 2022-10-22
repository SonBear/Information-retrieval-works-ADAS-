 <?php
    define("PROJECT_ROOT_PATH", _ROOT_);

    // include main configuration file
    require_once PROJECT_ROOT_PATH . "\inc\config.php";

    // include the base controller file
    require_once PROJECT_ROOT_PATH . "\controller\BaseController.php";

    // include the use model file
    require_once PROJECT_ROOT_PATH . "\model\Document.php";
    ?>