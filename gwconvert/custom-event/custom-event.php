<?php
require 'vendor/autoload.php';

use Qlync\Common;
use Qlync\VideoFile;
use Qlync\Database;
use Qlync\Svw;
use Qlync\FileNotFoundException;
use Qlync\DeviceNotFoundException;

try {
    $svw = new Svw();
}
catch (FileNotFoundException $e) {
    Common::error("SVW files not found. Please change the '--document-root' argument.");
}

try {
    $videofile = new VideoFile();
}
catch (FileNotFoundException $e) {
    Common::error("Video file not found. Please change the '--video-file' argument.");
}

try {
    $videofile->save();
}
catch (DeviceNotFoundException $e) {
    Common::error($e->getMessage() . " Please change the '--mac-addr' argument.");
}
catch (Exception $e) {
    Common::error($e->getMessage());
}

?>
