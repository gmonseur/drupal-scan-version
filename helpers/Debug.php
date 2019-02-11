<?php

// ------------------------------------------------------------------------

/**
 * Debug Helpers
 */

// ------------------------------------------------------------------------

/**
 * Trace
 *
 * Retourne un print_r formaté
 *
 * @param array $array
 */

if (!function_exists('trace')) {
    function trace($var, $isOpen = false, $mode_debug = MODE_DEBUG)
    {
        if($mode_debug) {
            $debug = debug_backtrace();
            $lUniqId = uniqid(md5(rand()));

            echo '<div style="margin-left: 10px">
                <div>
                    <a style="display:block;padding:4px;text-decoration:none;outline:none" href="javascript:void(0);" onClick="var div = document.getElementById(\'printr_' . $lUniqId . '\');
                    if (div.style.display!=\'none\') div.style.display = \'none\'; else div.style.display = \'block\';">
                        <p style="color:orange;cursor:pointer"><span> [-]</span> <strong>' . $debug[0]['file'] . ' </strong> ---line.' . $debug[0]['line'] . '</p>
                    </a>
                </div>
                <div style="display:' . ($isOpen ? 'block' : 'none') . ';" id="printr_' . $lUniqId . '">
                    <pre>';
            echo @print_r($var);
            echo '</pre>
                    </div>
              </div>';
        }
    }
}

/**
 * write_log
 *
 * @param $path
 * @param $data
 * @param $mode
 * @return bool
 */
if (!function_exists('write_log')) {
    function write_log($path, $data, $mode = 'a')
    {
        if (!$fp = @fopen($path, $mode)) {
            return FALSE;
        }

        $message = date("d/m/Y H:i:s") . ': ' . $data . '(' . $_SERVER['PHP_SELF'] . ')';
        $message .= "\r\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        return TRUE;
    }
}


