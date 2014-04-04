<?php
/**
 * User: anubis
 * Date: 04.04.14
 * Time: 16:56
 */

namespace bc\memcacher;

class MemcacheDummy {

    public function getversion() {
    }

    public function connect() {
    }

    public function delete() {
    }

    public function flush() {
    }

    public function get() {
        return null;
    }

    public function set() {
        return true;
    }
} 