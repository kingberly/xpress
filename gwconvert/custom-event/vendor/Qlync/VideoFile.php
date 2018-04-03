<?php
namespace Qlync;

use \DateTime;
use \DateInterval;
use \Exception;

class VideoFile {
    protected $video_file = null;
    protected $mac_addr = null;
    protected $time = null;
    protected $database = null;
    protected $entry = null;

    public function __construct() {
        $options = Common::get_options();
        $video_file = $options['video-file'];
        $mac_addr = $options['mac-addr'];
        $time = $options['time'];

        if (!file_exists($video_file)) {
            throw new FileNotFoundException('Video file not found');
        }

        $this->video_file = $video_file;
        $this->mac_addr = $mac_addr;
        $this->time = $time;
        $this->database = new Database();
    }

    public function save() {
        $this->copy();
        $this->create_entry();
    }

    protected function get_destination() {
        $svw = new Svw();
        $params = $svw->get_params();
        $recording_storage = $params['recording_storage'];

        $entry = $this->database->get_entry($this->mac_addr);
        $date = substr($this->time, 0, 8);
        $filename = $this->get_filename();

        return implode('/', array($recording_storage,
                                  $entry['uid'],
                                  $date,
                                  $filename));
    }

    protected function get_url_path() {
        $svw = new Svw();
        $params = $svw->get_params();
        $recording_storage = $params['recording_storage'];

        $entry = $this->database->get_entry($this->mac_addr);
        $date = substr($this->time, 0, 8);
        $filename = $this->get_filename();

        return implode('/', array('/vod',
                                  $entry['uid'],
                                  $date,
                                  $filename));
    }

    protected function get_filename() {
        $start = $this->time;
        $end = $this->get_end();
        return $start . '_' . $end . '.mp4';
    }

    protected function get_end() {
        $start = $this->time;
        $datetime = new DateTime(
            substr($this->time, 0, 4) . '-' .
            substr($this->time, 4, 2) . '-' .
            substr($this->time, 6, 2) . ' ' .
            substr($this->time, 8, 2) . ':' .
            substr($this->time, 10, 2) . ':' .
            substr($this->time, 12, 2));

        return $datetime->add(new DateInterval('PT1M'))->format('YmdHis');
    }

    protected function copy() {
        $src = $this->video_file;
        $dst = $this->get_destination();

        if (!is_dir(dirname($dst))) {
            mkdir(dirname($dst), 0777, true);
        }
        echo "Copying from $src to $dst\n";
        if (!copy($src, $dst)) {
            throw new Exception("Error copying file from $src to $dst");
        }
    }

    protected function create_entry() {
        $this->database->get_db()->beginTransaction();

        $recording_id = $this->database->create_recording(
            $this->mac_addr,
            $this->time,
            $this->get_end(),
            $this->get_url_path()
        );
        echo "Recording created: $recording_id\n";

        $event_id = $this->database->create_event(
            $this->mac_addr,
            $this->time,
            $recording_id
        );
        echo "Event created: $event_id\n";

        try {
            $this->database->get_db()->commit();
        }
        catch (Exception $e) {
            $this->database->get_db()->rollBack();
            throw $e;
        }
    }
}

?>
