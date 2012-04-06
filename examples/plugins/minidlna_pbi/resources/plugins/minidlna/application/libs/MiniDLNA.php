<?php

class FreeNAS_Lib_MiniDLNA {

    public $ARCH = null;
    public $BASE = null;
    public $CONF = null;
    public $RCOPTIONS = array(
        'port' => array(
            'opt' => '-d',
            ),
    );
    const CONTROL = "/usr/local/bin/sudo /usr/local/etc/rc.d/minidlna";

    function __construct() {

        $this->ARCH = php_uname("m");
        $this->BASE = "/usr/pbi/minidlna-" . $this->ARCH;
        $this->CONF = $this->BASE . "/etc/minidlna.conf";
        $this->RCCONF = $this->BASE . "/etc/rc.conf";

    }

    public function writeConf($obj) {

        $fp = fopen($this->RCCONF, "w");
        if($obj->getEnabled() == true)
            fwrite($fp, "minidlna_enable=\"YES\"\n");
        else
            fwrite($fp, "minidlna_enable=\"NO\"\n");
        fclose($fp);

        $fp = fopen($this->CONF, "w");
        fwrite($fp, sprintf("media_dir=%s\n", $obj->getMediaDir()));
        fwrite($fp, sprintf("port=%d\n", $obj->getPort()));
        if($obj->getInotify())
            fwrite($fp, "inotify=yes\n");
        else
            fwrite($fp, "inotify=no\n");
        fclose($fp);

        shell_exec("/usr/local/bin/sudo " . $this->BASE . "/tweak-rcconf");

    }

    public function status() {

        $desc = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        );
        $proc = proc_open("/usr/bin/pgrep minidlna", $desc, $pipes);
        $pids = stream_get_contents($pipes[1]);
        $retval = proc_close($proc);
        $status = 'STOPPED';
        $pid = null;

        if($retval == 0) {
            $status = "RUNNING";
            $pid = explode("\n", $pids);
            $pid = $pid[0];
        }

        return array(
            'status' => $status,
            'pid' => $pid,
        );

    }

    public function start() {

        $desc = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
           2 => array("pipe", "STDOUT"),  // stdout is a pipe that the child will write to
        );
        $proc = proc_open(self::CONTROL . " start", $desc, $pipes);
        $stdout = stream_get_contents($pipes[1]);
        $retval = proc_close($proc);
        return $stdout;

    }

    public function stop() {

        $desc = array(
           0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
           1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
           2 => array("pipe", "STDOUT"),  // stdout is a pipe that the child will write to
        );
        $proc = proc_open(self::CONTROL . " stop", $desc, $pipes);
        $stdout = stream_get_contents($pipes[1]);
        $retval = proc_close($proc);
        return $stdout;

    }

}

?>