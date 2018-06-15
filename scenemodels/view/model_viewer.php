<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8"> 
    <link rel="stylesheet" href="/css/style.css" type="text/css"/>
    <style type="text/css">
        #canvas {
            height: 100%;
            cursor:move;
            z-index: 10;
        }

        #loading {
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            overflow: hidden;
            display: none;
            background: url('img/loading.gif') no-repeat center center;
            z-index: 100;
        }
    </style>

    <script type="text/javascript" src="inc/hangar/gl-matrix-min.js"></script>
    <script type="text/javascript" src="inc/hangar/polyfill.js"></script>
    <script type="text/javascript" src="inc/hangar/viewer.js"></script>

    <script type="text/javascript">
    var Models = [
        { file: "<?php echo $ac3DFile; ?>"}
    ];

    var canvas, details, loading, viewer, current, gl;

    function launchViewer() {
        details = document.getElementById("details");
        loading = document.getElementById("loading");
        viewer = new HG.Viewer(canvas);
        current = 0;

        resize();
        showModel(Models[current]);
    }

    function onLoad() {
        canvas = document.getElementById("canvas");
        var experimental = false;
        try { gl = canvas.getContext("webgl"); }
        catch (x) { gl = null; }

        if (gl === null) {
            try { gl = canvas.getContext("experimental-webgl"); experimental = true; }
            catch (x) { gl = null; }
        }

        if (gl) {
            // WebGL is supported and enabled
            launchViewer();
        } else if ("WebGLRenderingContext" in window) {
            // WebGL is supported, but not enabled
            window.location = "http://get.webgl.org";
        } else {
            // WebGL is not supported
            window.location = "http://get.webgl.org";
        }
    }

    function resize(){
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;

      window.addEventListener("resize",
        function(event){
          viewer.onResize(window.innerWidth, window.innerHeight);
        }, false);
    };

    function showModel(model){
        loading.style.display = "block";
        // Consider model crashes after 10 seconds
        setTimeout('crashed();', 10000);
        viewer.show(model.file,
                    {callback:onLoaded,
                     texturePath:"<?php echo $texturePrefix; ?>"
                    });
    };

    function onLoaded(){
        loading.style.display = "none";
    };
    
    function crashed() {
        if (loading.style.display === "block") {
            document.body.innerHTML = "This model takes more than 10 seconds to load. " +
                                      "The script has been canceled, to prevent it from crashing.";
        }
    }
    </script>

</head>

<body onload="onLoad();">
    <canvas id="canvas"></canvas>
    <div id="loading"></div>
</body>

</html>
