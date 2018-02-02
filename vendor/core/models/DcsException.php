<?php
namespace Dcs\Vendor\Core\Models;

use Exception;

class DcsException extends Exception {
    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct("DCS: ".$message, $code, $previous);
    }
    static public function doThrow($message = "", $code = 0, Exception $previous = null) {
    throw new DcsException($message, $code, $previous);
    }
  }
