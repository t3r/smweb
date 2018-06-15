        </div>
    </div>
    <p class="center">
<?php
        // What's the last GIT version of the website and when was it last
        // updated?
        $filename = '/srv/sceneryweb/.git/refs/heads/master';
        if (file_exists($filename)) {
            $result = file_get_contents($filename);
            echo "Version&nbsp;".substr($result,0,7)."&nbsp;-&nbsp;" .
                 date("F d Y H:i", filemtime($filename)) ."&nbsp;-&nbsp;";
        }
?>
        <a href="https://sourceforge.net/p/flightgear/sceneryweb/commit_browser">Version info</a> - 
        <a href="../../TOBEDONE">Volunteer?</a> - <a href="../../README">Readme</a> - 
        <a href="../../LICENCE">License</a> - <a href="../../VERSION">History</a> - 
<!--
        <a href="http://sphere.telascience.org/webalizer/">Web statistics</a>
-->
    </p>
</body>
</html>
