<?php
/*
* A Very Basic Database Object
*
*/
class AppDB extends mysqli {
    private static $instance;
	public $TB_PRFX = TB_PRFX;

    final private function __construct() {
        parent::init();

        if (!parent::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
            throw new Exception('Setting MYSQLI_INIT_COMMAND failed');
        }

        if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            throw new Exception('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }

        if (!parent::real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
            throw new Exception('Connect Error (' . mysqli_connect_errno() . ') '
                    . mysqli_connect_error());
        }
    }
    final public static function GetInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>