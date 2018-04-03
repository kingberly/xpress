<?php

namespace Qlync;

use Ulrichsg\Getopt\Option;
use Ulrichsg\Getopt\Getopt;
use \InvalidArgumentException;
use \Exception;

class Common {
    protected static $getopt = null;

    public static function get_options() {
        $getopt = self::get_getopt();
        try {
            $getopt->parse();
            $options = $getopt->getOptions();

            if (!array_key_exists('v', $options)
                    || !array_key_exists('m', $options)
                    || !array_key_exists('t', $options)) {
                throw new InvalidArgumentException(
                    'You must specify a video file, device MAC address, and its time.');
            }

            return $options;
        }
        catch (Exception $e) {
            self::error('Error: ' . $e->getMessage());
        }
    }

    protected static function get_getopt() {
        if (!self::$getopt) {
            self::$getopt = new Getopt(array(
                (new Option('v', 'video-file', Getopt::REQUIRED_ARGUMENT))
                    ->setDescription('Video file to upload.'),
                (new Option('m', 'mac-addr', Getopt::REQUIRED_ARGUMENT))
                    ->setDescription('Device MAC address. (A1B2C3D4E5F6)')
                    ->setValidation(function($value) {return preg_match('/^[A-F0-9]{12}$/', $value);}),
                (new Option('t', 'time', Getopt::REQUIRED_ARGUMENT))
                    ->setDescription('14 digits time code for this video. (YYYYMMDDhhmmss)')
                    ->setValidation(function($value) {return preg_match('/^[0-9]{14}$/', $value);}),
                (new Option('r', 'document-root', Getopt::REQUIRED_ARGUMENT))
                    ->setDescription('iSAT web server document root. (default: /var/www/SAT-CLOUDNVR/)')
                    ->setDefaultValue('/var/www/SAT-CLOUDNVR/'),
            ));
        }
        return self::$getopt;
    }

    public static function error($message) {
        echo $message . "\n\n";
        echo self::get_getopt()->getHelpText();
        exit(1);
    }
}

?>
