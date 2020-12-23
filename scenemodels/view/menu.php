<script type="text/javascript" src="/static/inc/js/menu.js"></script>

<ul id="csstopmenu">
    <li class="mainitems" style="border-left-width: 1px">
        <div class="headerlinks"><a href="/">Home</a></div>
        <ul class="submenus">
            <li><a href="/app.php?c=News&amp;a=display">News</a></li>
        </ul>
    </li>
    <li class="mainitems">
        <div class="headerlinks"><a href="/app.php?c=Plain&a=contribute">Contribute</a></div>
        <ul class="submenus">
            <li><a href="/app.php?c=AddObjects&amp;a=form">Add a new object position</a></li>
            <li><a href="/app.php?c=AddObjects&amp;a=massiveform">Massive import of objects</a></li>
            <li><a href="/app.php?c=DeleteObjects&amp;a=findform">Delete an object position</a></li>
            <li><a href="/app.php?c=UpdateObjects&amp;a=findform">Update object geodata</a></li>
            <li class="separator"></li>
            <li><a href="/app.php?c=AddModel&amp;a=form">Add a new model</a></li>
            <li><a href="/app.php?c=UpdateModel&amp;a=selectModelForm">Update a model</a></li>
        </ul>
    </li>
    <li class="mainitems">
        <div class="headerlinks"><a href="/app.php?c=Models&amp;a=browseRecent">Models</a></div>
        <ul class="submenus">
            <li><a href="/app.php?c=Models&amp;a=browse">Browse all</a></li>
<?php
            // TODO Compute this group list in controllers
            $modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();
            $groups = $modelDaoRO->getModelsGroups();
            
            foreach ($groups as $group) {
                $name = preg_replace('/&/',"&amp;", $group->getName());
                $name = preg_replace('/ /',"&nbsp;", $name);
                echo "<li><a href=\"/app.php?c=Models&amp;a=browse&amp;shared=".$group->getId()."\">".$name."</a></li>";
            }
?>
        </ul>
    </li>
    <li class="mainitems">
        <div class="headerlinks"><a href="/app.php?c=Objects&amp;a=search">Objects</a></div>
    </li>
    <li class="mainitems">
        <div class="headerlinks"><a href="/app.php?c=Authors&amp;a=browse">Authors</a></div>
    </li>
    <!--li class="mainitems">
        <div class="headerlinks"><a href="/download.php">Download</a></div>
        <ul class="submenus">
            <li><a href="/objects_download.php">Download latest scenery objects</a></li>
            <li><a href="/scenery_download.php">Download scenery objects &amp; terrain</a></li>
            <li><a href="http://sourceforge.net/projects/flightgear/files/scenery/GlobalObjects.tgz/download">Global objects</a></li>
            <li><a href="http://sourceforge.net/projects/flightgear/files/scenery/SharedModels.tgz/download">Shared models</a></li>
        </ul>
    </li-->
    <li class="mainitems">
        <div class="headerlinks"><a href="/app.php?c=Plain&a=statistics">Statistics</a></div>
        <ul class="submenus">
            <li><a href="static/map/index.html">Coverage</a></li>
            <!--li><a href="/stats/">Access</a></li-->
        </ul>
    </li>
    <!--li class="mainitems">
        <div class="headerlinks"><a href="/rss/">RSS</a></div>
    </li-->
    <li class="mainitems">
        <div class="headerlinks"><a href="https://scenery2.flightgear.org/#tsstatus">TS-Status</a></div>
    </li>
    <!--li class="mainitems">
        <div class="headerlinks"><a href="/jenkins/">Build Bot</a></div>
    </li-->
</ul>

<div id="clearmenu" style="clear: left"></div>
