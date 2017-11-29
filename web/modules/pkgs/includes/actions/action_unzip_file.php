<?php
extract($_POST);
/*

descriptor type
  unzip file from python
        descriptor type
        {
            "step" : intnb,
            "action" : "action_unzip_file",
            "filename" : "namefile",
            "pathdirectorytounzip" : "pathdirextract",
            "@resultcommand" : ""
        }
        filename if current directory or pathfilename
        optionnel
            @resultcommand list files
            10@lastlines 10 last lines
            10@firstlines 10 first lines
            succes
            error
            goto
        """

echo "<pre>";
    print_r( $_POST );
echo "</pre>";*/
$waiting =  (isset($waiting))? $waiting : 10;/*
$goto =  (isset($goto))? $goto : "END_SUCCESS";*/
?>
<div class="header">
    <h1>Unzip File</h1>
</div>
<div class="content">
    <div>
        <input type="hidden" name="action" value="action_unzip_file" />
        <input type="hidden" name="step" />
        <input type="hidden" name="codereturn" value=""/>
    <table>
        <tr>
            <th width="16%">Step label:</th>
            <th width="25%">
                <input id="laction" type="text" name="actionlabel" value="<?php echo (isset($actionlabel))? $actionlabel : uniqid(); ?>"/>
            </th>
            <th></th>
            <th></th>
        </tr>

        <tr>
            <th width="16%">Zip file:</th>
            <th width="25%">
                <input type="text" name="filename" value="<?php echo (isset($$filename))? $filename : ""; ?>"/>
            </th>
            <th></th>
            <th></th>
        </tr>

        <tr>
            <?php
        $resultlist = array(
                            array('label' => '10 first lines of result','value' => "10@firstlines"),
                            array('label' => '20 first lines of result','value' => "20@firstlines"),
                            array('label' => '30 first lines of result','value' => "30@firstlines"),
                            array('label' => 'Complete results','value' => "@resultcommand"),
                            array('label' => '10 last lines of result','value' => "10@lastlines"),
                            array('label' => '20 last lines of result','value' => "20@lastlines"),
                            array('label' => '30 last lines of result','value' => "30@lastlines"),
                            array('label' => '20 last lines of result','value' => "2@lastlines"),
                            array('label' => 'The last line of result','value' => "1@lastlines"),
        );
        $posibleresultname = array(
                                    "10@firstlines",
                                    "20@firstlines",
                                    "30@firstlines",
                                    "@resultcommand",
                                    "10@lastlines",
                                    "20@lastlines",
                                    "30@lastlines",
                                    "2@lastlines",
                                    "1@lastlines"
        );
        $options = "";
        $boolselected = false;
        // search in $Post if input result
        foreach($_POST as $key=>$val){
            if (in_array($key, $posibleresultname)){
                $selectresult = $key;
                $boolselected = true;
                break;
            }
        }
        if (!isset($selectresult)){
            $selectresult = "1@lastlines";
        }

        foreach($resultlist as $selectedbyuser)
        {
            if(isset($selectresult) && $selectedbyuser['value'] == $selectresult)
            {
                $options .= "<option value='".$selectedbyuser['value']."' selected>".$selectedbyuser['label']."</option>";
            }
            else
                $options .= "<option value='".$selectedbyuser['value']."'>".$selectedbyuser['label']."</option>";
        }

        if($boolselected)// and $selectresult != "noneresult"
        {
            echo '
            <td>
                <input type="checkbox" checked onclick="if(jQuery(this).is(\':checked\')){
                                                            jQuery(this).closest(\'td\').next().find(\'select\').prop(\'disabled\',false);
                                                        }
                                                        else{
                                                            jQuery(this).closest(\'td\').next().find(\'select\').prop(\'disabled\',true);
                                                        }" />Return result
            </td>
            <td>
                <select  onchange="jQuery(this).attr(\'name\',jQuery(this).val());" name="'.$selectresult.'">'.$options.'</select>
            </td>
            <td></td><td></td>
            ';

        }
        else{
            echo '
            <td>
                <input type="checkbox" onclick="if(jQuery(this).is(\':checked\')){
                                                    jQuery(this).closest(\'td\').next().find(\'select\').prop(\'disabled\',false);
                                                }
                                                else{
                                                    jQuery(this).closest(\'td\').next().find(\'select\').prop(\'disabled\',true);
                                                }" />Return result
            </td>
            <td>
            <select disabled onchange="jQuery(this).attr(\'name\',jQuery(this).val());"
                name="1@lastlines">'.$options.'</select>
            </td>
            <td></td><td></td>';
        }
        ?>
        </tr>


       <tr>
           <?php
            if(isset($pathdirectorytounzip))
            {
                echo '
                <td>
                    <input type="checkbox" checked onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />Unzip to specified folder
                </td>
                <td>
                    <input " type="text"  value="'.$pathdirectorytounzip.'" name="pathdirectorytounzip"  />
                </td><td></td><td></td>';
            }
            else{
                echo '
                <td>
                    <input type="checkbox"  onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />Unzip to specified folder
                </td>
                <td>
                    <input type="text" value="" disabled name="pathdirectorytounzip"  />
                </td><td></td><td></td>';
            }
            ?>
        </tr>
        <tr>
        <?php
           if(isset($goto))
            {
                echo '<td width="16%">
                    <input type="checkbox" checked
                        onclick="if(jQuery(this).is(\':checked\')){
                                    jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                                }
                                else{
                                    jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                                }" />Go to step
                </td>
                <td width="25%">
                    <input type="text"  value="'.$goto.'" name="goto"  />
                </td><td></td><td></td>';
            }
            else{
                echo '<td width="16%">
                    <input type="checkbox"
                        onclick="if(jQuery(this).is(\':checked\')){
                                    jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                                }
                                else{
                                    jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                                }" />Go to step
                    </td>
                    <td width="25%">
                         <input type="text" disabled value="" name="goto"  />
                    </td><td></td><td></td>';
            }
            ?>
        </tr>



        <tr>
           <?php
            if(isset($success))
            {
                echo '
                <td>
                    <input type="checkbox" checked onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />On success go to step
                </td>
                <td>
                    <input " type="text"  value="'.$success.'" name="success"  />
                </td></td><td></td><td></td>';
            }
            else{
                echo '
                <td>
                    <input type="checkbox"  onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />On success go to step
                </td>
                <td>
                    <input type="text" value="END_SUCCESS" disabled name="success"  />
                </td></td><td></td><td></td>';
            }
            ?>
        </tr>
        <tr>
            <?php
            if(isset($error))
            {
                echo '
                <td>
                    <input type="checkbox" checked onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />On error go to step
                </td>
                <td>
                    <input " type="text"  value="'.$error.'" name="error"  />
                </td></td><td></td><td></td>';
            }
            else{
                echo '
                <td>
                    <input type="checkbox"  onclick="
                    if(jQuery(this).is(\':checked\')){
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',false);
                    }
                    else{
                        jQuery(this).closest(\'td\').next().find(\'input\').prop(\'disabled\',true);
                    }" />On error go to step
                </td>
                <td>
                    <input type="text" value="END_ERROR" disabled name="error"  />
                </td></td><td></td><td></td>';
            }
            ?>
        </tr>
    </table>
        <!-- Option timeout -->
    </div>
    <input  class="btn btn-primary" type="button" onclick="jQuery(this).parent().parent('li').detach()" value="Delete" />
</div>
