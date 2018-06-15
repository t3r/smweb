<?php
require_once 'autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();
require 'view/header.php';
?>
<h1>How to contribute</h1>

<p>
<b>Foreword:</b> The instructions on this page are presented in a pretty elaborate, 
detailed way that might look a bit complicated at first glance.

Please don't get this wrong - contributing to the repository is pretty
simple, especially through the use of our web forms. We experienced that almost
every individual in such a large crew of contributors has, needless to say,
a different background. So we just try to give detailed recommendations in order
to avoid misunderstandings.
</p>

<div class="paragraph_bloc">
    <h2>Contents</h2>
    <ul class="body">
    <li class="toclevel-1"><a href="#positions"><span class="toctext">Contributing positions</span></a></li>
    <li class="toclevel-1"><a href="#models"><span class="toctext">Contributing models</span></a>
        <ul class="detail">
            <li class="toclevel-1"><a href="#items"><span class="toctext">Submission items</span></a></li>
            <li class="toclevel-1"><a href="#tips"><span class="toctext"><font color="red">Models best practice</font></span></a></li>
            <li class="toclevel-1"><a href="#contact"><span class="toctext"><font color="blue">Upload facilities</font></span></a></li>
        </ul>
    </li>
    <li class="toclevel-1"><a href="#thumbnails"><span class="toctext">Contributing thumbnails</span></a></li>
    <li class="toclevel-1"><a href="#offset"><span class="toctext">Understanding offset</span></a></li>
    </ul>
</div>

<!--</table>
<script type="text/javascript">
if (window.showTocToggle) { var tocShowText = "show"; var tocHideText = "hide"; showTocToggle(); }
</script>
</td></tr>-->

<div class="paragraph_bloc" id="positions">
    <h2><a>Contributing positions</a></h2>

    <div class="body">
        If you wish to contribute positions for the many shared models that are
        already available then these are best submitted:
        <ul>
        <li>directly through <a href="submission/index.php">our friendly web forms</a> for unitary addition, edition, deletion. <strong>Please use them in priority as they make the work of maintainers much easier.</strong></li>
        <li>either the <a href="http://wiki.flightgear.org/File_Formats#.2A.stg">STG format used in the scenery</a> and by the <a href="http://wiki.flightgear.org/Howto:_Place_3D_objects_with_the_UFO">UFO scenery editor</a> and directly copy/pasted (new objects positions only) in <a href="submission/index.php">our mass import webform</a>.
        If you have data available in other formats please try to convert them into the STG format first. You can find help on the <a href="http://www.flightgear.org/forums">forums</a> to do so.</li>
        </ul>

        <!--<h3>Extra options for .stg submissions (currently unsupported by our webform)</h3>
        <p>You can help speed the import process by including all the details about your scenery in an stg file. This can be processed automatically and is by far the quickest way to get your model locations into the database.</p>
        <p>There's an example file here: <a href="example.stg">example.stg</a></p>
        <p>Currently supported comments are:</p>
        <ul>
          <li>#country: - defines the country in which the objects reside.</li>
          <li>#submitter: - your name</li>
          <li>#desc: - description to be used for the following objects</li>
        </ul>-->
    </div>
</div>

<div class="paragraph_bloc" id="models">
    <h2><a>Contributing models</a></h2>
    <div class="body">
        <p>If you wish to help populate the world with interesting static objects (yes, we really are aiming for total world domination here :-) then we'll need the following details:</p>

        <h3 id="items"><a>Submission items</a></h3>
        <h4>Mandatory submission items</h4>
        <ul>
            <li><strong>Model placement</strong>:
                <ul class="detail">
                  <li><strong>Position</strong> (if appropriate; either lon/lat, or Ordnance Survey grid - other grids can be added on request).</li>
                  <li><strong>Heading</strong> (if appropriate).</li>
                  <li><strong>Ground elevation</strong> (if known to the author) - in any case, report if the model has to be sunk into the ground (what we call offset) in order to display properly !!</li>
                  <li><b> -> </b>....  or just simply submit the respective .stg-line with your model.</li>
                </ul>
            </li>
            <li><strong>Full name of author (yes you have to be known to us before submitting a 3D model.</strong></li>
            <li><strong>Email address of author</strong> (if not already known, will not be published, just as a reference).</li>
            <li>A notice which tells us that your submission is covered by the <strong>GNU GPL v2</strong> (if not already known).
                <ul class="detail">
                  <li>The nature of the FlightGear project does not allow us to accept submissions that don't comply with the GPL.</li>
                </ul>
            </li>
            <li>Always tell us how to <strong>name the model</strong> (like 'Tour Eiffel - Paris - France').</li>
            <li><strong>A 320x240 thumbnail</strong> containing an advantageous view on the model/object as (JPEG) image - this is preferred for a nice representation of your artwork.</li>
            <li><strong>Country</strong> in which the model is located (if known to the author).</li>
            <li>Additional <strong>short comment on the author</strong>.</li>
            <li>Details are available <a href="app.php?c=AddModel&amp;a=form">here</a>.</li>
        </ul>

        <h3 id="tips"><a><font color="red">Models best practice:</font> To save you and us from avoidable and unnecessary extra work:</a></h3>
        <ul>
            <li>Never group different, detached buildings into a single geometry file.</li>
            <li>Never put surface materials (tarmac, grass, roads, parkings, ...) or trees into Scenery Model geometries.</li>
        </ul>

        <div class="conclusion"><b> -> </b> The reason is simple: Depending on the angle of view, the
                    operating system, the graphics card and driver, the underlying
                    terrain slope, various people might be seeing rendering
                    artifacts.  Therefore: Please don't !</div>
        <ul>
            <li>For groupings of individual models choose a distinct, corresponding position for each of them, never mount multiple models into a single position.</li>
            <li>Textures should be in PNG format - older models which used rgb textures have been updated. <strong>The textures dimensions have to be a power of two: eg 128x256.</strong></li>
            <li>Apron, taxiway, runway or other airport pavements are being maintained at <a href="http://data.x-plane.com/">XPlane Robin Peel's airport database</a>.</li>
            <li><strong>Always choose reasonable (meaningful, descriptive) filenames for your models</strong>. At urban areas having a geometry 'tower.ac' or a texture just named 'red.png' or 'concrete.png' might prove not to be unique ;-)</li>
            <li>As a rule of thumb, try to let even a detailed, <strong>single model not exceed 1/2 MByte in total size</strong>, otherwise the simulation will face hard times when approaching densely packed areas. A typical, single office building usually can be done at (far!) less than 100 kByte;</li>
            <li><strong>Avoid spaces in file- and/or directory names.</strong></li>
            <li>As a general rule, do <strong>not</strong> try to (mis)use 3D models as a substitute for unfinished airfield layout or land cover. Do <strong>not</strong> place your models at incorrect positions just because the current land cover shapes do not match.</li>
            <li>Feel invited to send us an early version of your model even if it still has unfinished details. It's always possible to update the respective metadata entry with a refined model - especially when the placement of the model doesn't change any more.</li>
        </ul>
        <b> -> </b> The better your submission complies with these recommendations, the quicker we'll have it imported into the repository.


        <h3 id="contact"><a><font color="blue">Upload facilities</font></a></h3>
        <ul>
            <li>Our dedicated webform <a href="app.php?c=AddModel&amp;a=form">here.</a></li>
        </ul>
    </div>
</div>

<div class="paragraph_bloc" id="thumbnails">
    <h2><a>Contributing thumbnails</a></h2>

    <p class="body">
    A noticeable amount of model submissions are missing a thumbnail. If you
    like to take some snapshots for us, go ahead, look at the Model Browser
    pages, pick those models which lack a thumbnail and create a nice view on
    the respective model. JPEG's of 320x240 make our overview.<br/>
    Models are easily identified by their numeric id when you click on the
    thumbnail in the Browser view.<br/>
    You can find <a href="modelsnothumb.php">here the models without thumbnail</a>.
    </p>
</div>

<div class="paragraph_bloc" id="offset">
    <h2><a>Understanding offset</a></h2>

    <p class="body">
    Quite a lot of people are not using the offset parameter, or don't understand why it
    sits for. The picture below is there to give a small overview of its use, with a
    usual example within FG.
    <p class="center"><img src="/img/understanding_offset.png" alt="Understanding offset"/></p>
    In this example, we use a chimney. The chimney available as shared objects are quite high by default, in order to be
    useable in a lot of situation. Imagine if we had to create one chimney of a given height per situation.
    <ul>
        <li>The model shown has an (example) overall height of 200 meters.</li>
        <li>Unfortunately, you want it to have an elevation of 150 meters Above Ground Level (AGL) to fit your situations.</li>
        <li>So you have to "sink" your object in the ground of 150-200=-50 meters (the AGL elevation minus the model height).</li>
        <li>This -50 meter is <strong>the offset</strong> you have to give in our webforms, while the elevation is the ground level elevation (eg 100 meters in our example).</li>
        <li>Please NEVER USE "hand crafted" elevation (in that case, you could think about putting 50m as elevation, and 0 as an offset),
        because the elevation of each object in our database is regularly re-computed to match the best Data Elevation Model (DEM) available. At that occasion, the elevation of
        this object would be readjusted and replaced by 100 meters (or 102, or whatever, if the DEM or computation gets more precise). You would then see
        your object sitting on the ground, <strong>but with 200 meters as object AGL elevation</strong>! That's why the offset is so important.
        </li>
        <li>Chimneys are objects where offsets are frequently used, but it's also the case if you want to put an object above another one.
        For instance, <a href="app.php?c=Objects&amp;a=view&amp;id=3294183">this Mercedes star</a> (no ad intended here) sits on top of <a href="app.php?c=Objects&amp;a=view&amp;id=3294182">this building</a>.
        So the star and the building have the same ground elevation (227.0 meters above MSL), but the star has an offset of 48 meters. 48 meters is the positive offset,
        corresponding to the height of the building on which it sits. During the next elevation computation, elevation may become another one (228 or 230), but the offset
        will stay the same and the star will always be sitting on its building, and the submitter has just nothing to change!
        </li>
        <li>
        For those wondering, the offset is applied on export by TerraSync, which computes the (easy) operation ELEV+OFFSET (whether offset is positive or negative) and puts the result into the STG file.
        </li>
    </ul>
</div>
<?php require 'view/footer.php';?>
