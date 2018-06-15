<?php
require 'view/header.php';
?>
<style>
td.sizecol {
    text-align: right;
}
</style>
<script type="text/javascript">
function popmap(lat,lon,zoom) {
    popup = window.open("/map?z="+zoom+"&lat="+lat+"&lon="+lon, "map", "height=500,width=500,scrollbars=no,resizable=no");
    popup.focus();
}
</script>

<?php
    
echo "<h1>".htmlspecialchars($modelMetadata->getName())."</h1>";
if (!empty($modelMetadata->getDescription())) {
    echo "<p>".htmlspecialchars($modelMetadata->getDescription())."</p>";
}
?>
<table>
    <tr>
        <td style="width: 320px" rowspan="7"><img src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php print $modelMetadata->getId(); ?>" alt=""/></td>
        <td>File name</td>
        <td>
<?php
            print $modelMetadata->getFilename();
?>
        </td>
    </tr>
    <tr>
        <td>Type</td>
        <td>
            <a href="app.php?c=Models&amp;a=browse&amp;shared=<?php echo $modelMetadata->getModelsGroup()->getId()?>">
                <?php echo $modelMetadata->getModelsGroup()->getName()?>
            </a>
        </td>
    </tr>
    <tr>
        <td>Author</td>
        <td>
<?php
            print "<a href=\"app.php?c=Authors&amp;a=view&amp;id=".$modelMetadata->getAuthor()->getId()."\">".$modelMetadata->getAuthor()->getName()."</a>";
?>
        </td>
    </tr>
    <tr>
        <td>Last updated</td>
        <td><?php print \FormatUtils::formatDateTime($modelMetadata->getLastUpdated()); ?></td>
    </tr>
    <tr>
        <td>Model ID</td>
        <td><?php print $id; ?></td>
    </tr>
    <tr>
        <td>Occurrences</td>
        <td>
<?php
        if ($occurences > 0) {
            echo "<a href=\"app.php?c=Objects&amp;a=search&amp;model=".$id."\">".$occurences;
            echo $occurences > 1 ? " objects" : " object";
            echo "</a>";
        } else {
            echo "0 object";
        }
?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <a href="app.php?c=Models&amp;a=getPackage&amp;id=<?php echo $id; ?>">Download model</a> | <a href="app.php?c=UpdateModel&amp;a=modelUpdateForm&amp;modelId=<?php echo $id; ?>">Update model/info</a>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="3" id="webglTd">
            <div id="webgl" style="resize: vertical; overflow: auto;">
                <a onclick="showWebgl(<?php echo $id; ?>)">Show 3D preview in WebGL</a>
            </div>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="3" id="contentInfo">
            <a id="infoLink" onclick="showModelContentInfo(<?php echo $id; ?>)">Show model content information</a>
            <table id="filesInfos" style="display: none;">
                <tr><th>Filename</th><th>Size</th></tr>
            </table>
        </td>
    </tr>
</table>

<script type="text/javascript">
function showWebgl(id) {
    var objectViewer = document.createElement("object");
    objectViewer.width = "100%";
    objectViewer.height = "99%";
    objectViewer.data = "app.php?c=Models&a=modelViewer&id="+id;
    objectViewer.type = "text/html";
    var webgl = document.getElementById("webgl");
    webgl.innerHTML = "";
    webgl.style.height = "500px";
    webgl.style.textAlign = "center";
    webgl.appendChild(objectViewer);
    document.getElementById("webglTd").innerHTML += "AC3D viewer powered by Hangar - Juan Mellado. Read <a href=\"http://en.wikipedia.org/wiki/Webgl\">here to learn about WebGL</a>.";
}

function showModelContentInfo(id) {
    $("#infoLink").toggle();
    
    $.ajax({
        url: 'app.php?c=Models&a=contentFilesInfos&id='+id,
        context: document.body
    }).done(function(xml) {
        $(xml).find("file").each(function(){
            var name=$(this).find('name').text();
            var size=$(this).find('size').text();
            $("#filesInfos").append("<tr><td><a href='app.php?c=Models&a=getFile&id="+id+"&name="+name+"'>"+name+"</a></td><td class='sizecol'>"+size+"</td></tr>");
        });

        $("#filesInfos").toggle();
    });
}
</script>

<?php
include 'view/footer.php';
?>
