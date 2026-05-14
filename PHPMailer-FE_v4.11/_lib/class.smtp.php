<?php
/**
 * Minimal SMTP class stub for compatibility.
 *
 * This file is provided so the mailer does not fail when the SMTP class
 * is referenced by the old PHPMailer-FE code. It does not implement a
 * full SMTP client. If SMTP sending is required, replace this stub with
 * a complete SMTP implementation.
 */

if (!class_exists('SMTP')) {
  class SMTP {
    public $do_debug = 0;

    public function Connected() {
      return false;
    }

    public function Connect($host, $port = null, $timeout = null) {
      return false;
    }

    public function Hello($host) {
      return true;
    }

    public function Authenticate($username, $password) {
      return false;
    }

    public function Mail($from) {
      return false;
    }

    public function Recipient($to) {
      return false;
    }

    public function Data($data) {
      return false;
    }

    public function Reset() {
      return true;
    }

    public function Quit() {
      return true;
    }

    public function Close() {
      return true;
    }
  }
}
