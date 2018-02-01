<?php
namespace Dcs\Vendor\Core\Models;

use Exception;

class DcsException extends Exception {
    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct("DCS: ".$message, $code, $previous);
    }
}
