<?php
require 'view/header.php';
?>
<script type="text/javascript" src="inc/js/update_objects.js"></script>

<h1>Choose model to update</h1>
<p>
    Through this form you can update existing models in the FlightGear Scenery Database.
</p>

<form id="formChoice" method="post" action="app.php?c=UpdateModel&amp;a=modelUpdateForm" enctype="multipart/form-data">
<table style="width: 100%;">
    <tr>
        <td><label for="model_group_id">Model's family<em>*</em><span>This is the family name of the object.</span></label></td>
        <td>
            <select id="model_group_id" name="model_group_id" onchange="update_models(); validateTabs();">
                <option value="0">Please select a family</option>
                <option value="0">----</option>
<?php
            foreach ($modelsGroups as $modelsGroup) {
                echo "<option value=\"".$modelsGroup->getId()."\">".$modelsGroup->getName()."</option>";
            }
            echo "</select>";
?>
        </td>
        <td rowspan="3" style="width: 200px">
            <img id="form_objects_thumb" width="200px" src="" alt=""/>
        </td>
    </tr>
    <tr>
        <td><label for="modelId">Model<em>*</em><span>This is the name of the object, ie as it appears in the .stg file.</span></label></td>
        <td id="form_objects">
            <!--Now everything is done via the Ajax stuff, and the results inserted here.-->
        </td>
    </tr>
    <tr>
        <td colspan="2" class="submit">
            <input type="submit" value="Update this model" />
        </td>
    </tr>
</table>
</form>
<?php require 'view/footer.php'; ?>